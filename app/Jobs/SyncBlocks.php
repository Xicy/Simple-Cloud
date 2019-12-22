<?php

namespace App\Jobs;

use App\Models\Coin;
use App\Models\Wallet;
use Carbon\Carbon;
use JsonRPC\Exception\ConnectionFailureException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncBlocks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $coin = false;
    private $skip = 6;
    private $min_confirmations = 0;

    /**
     * Create a new job instance.
     * @param Coin|null $coin
     */
    public function __construct(Coin $coin = null)
    {
        $this->coin = $coin;
        $this->queue = "default";
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->coin == false) {
            $active_coin_ids = DB::table('jobs')
                ->where('id', '!=', $this->job->getJobId())
                ->where('queue', $this->queue)->get('payload')->map(function ($v) {
                    $payload = json_decode($v->payload, true);
                    if ($payload == self::class)
                        return unserialize($payload["data"]["command"])->coin->id;
                    return 0;
                });

            Coin::whereNotIn('id', $active_coin_ids)->enable()->get()->each(function (Coin $coin) {
                self::dispatch($coin);
            });
            return;
        }

        if ($this->coin->isFiat())
            $this->skip = 1;

        Log::info("[{$this->coin->id}|{$this->coin->name}] " . get_class($this) . " Started");
        try {
            $client = &$this->coin->client;
            $block_count = $client->getblockcount();
            $last_block_height = $block_count - $this->skip;
            $block_height = min(++$this->coin->height, $last_block_height);
            if ($block_height == $last_block_height) {
                Log::info("[{$this->coin->id}|{$this->coin->name}] Skip synchronization. No new blocks detected height: {$block_height}, latest block: {$block_count}");
                self::dispatch($this->coin)->delay($this->coin->block_time);
                return;
            }

            $limitter = ceil(60000 / $this->coin->block_time);
            collect(range($block_height, $last_block_height))->take($limitter)->each(function ($block_height) use (&$client) {
                DB::transaction(function () use (&$client, &$block_height) {
                    $block_hash = $client->getblockhash($block_height);
                    $block = $client->getblock($block_hash, true);

                    $client->batch();
                    array_map(function ($tx) use (&$client) {
                        $client->getrawtransaction($tx, 1);
                    }, $block["tx"]);
                    $tx_infos = $client->send();
                    foreach ($tx_infos as $raw_tx) {
                        foreach ($raw_tx["vout"] as $vout) {

                            if ($vout["value"] <= 0 || !$vout["scriptPubKey"] || !$vout["scriptPubKey"]["addresses"]) {
                                continue;
                            }

                            Wallet::whereIn("address", $vout["scriptPubKey"]["addresses"])->get()->each(function (Wallet $wallet) use (&$vout, &$raw_tx, &$block, &$block_height) {
                                $wallet->transactions()->create([
                                    "status" => "completed",
                                    "amount" => $this->coin->isFiat() ? $vout["value"] / $wallet->coin->market->getPrice() : $vout["value"],
                                    "tx_id" => $raw_tx["txid"],
                                    "type" => "deposit",
                                    "data" => [
                                        "txout" => $vout["n"],
                                        "block_number" => $block_height,
                                        "exchange" => $this->coin->isFiat()
                                    ],
                                    "created_at" => Carbon::parse($raw_tx["time"])
                                ]);
                            });
                        }
                    }
                    $this->coin->height = $block_height;
                    $this->coin->save();
                });
            });
        } catch (ConnectionFailureException $e) {
            Log::error("[{$this->coin->id}|{$this->coin->name}] Unable to establish a connection {$this->coin->rpc_url}");
        }
        Log::info("[{$this->coin->id}|{$this->coin->name}] ".get_class($this)." Ended");

    }
}
