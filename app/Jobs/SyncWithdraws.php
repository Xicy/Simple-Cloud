<?php

namespace App\Jobs;

use App\Models\Coin;
use App\Models\Transaction;
use Carbon\Carbon;
use JsonRPC\Exception\ConnectionFailureException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncWithdraws implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $coin;
    private $skip = 6;
    private $min_confirmations = 0;

    /**
     * Create a new job instance.
     * @param Coin|null $coin
     */
    public function __construct(Coin $coin = null)
    {
        $this->coin = $coin;
        $this->queue = "syncWithdraw";
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->coin) {
            $active_coin_ids = DB::table('jobs')->where('id', '!=', $this->job->getJobId())->where('queue', $this->queue)->get('payload')->map(function ($v) {
                return unserialize(json_decode($v->payload, true)["data"]["command"])->coin->id;
            });

            Coin::whereNotIn('id', $active_coin_ids)->where('enable', true)->get()->each(function (Coin $coin) {
                self::dispatch($coin);
            });
            return;
        }

        if ($this->withRpc()) {
            self::dispatch($this->coin)->delay(10);
        }
    }

    private function withRpc()
    {
        try {
            /** @var Transaction[] $pending_transactions */
            $pending_transactions = $this->coin
                ->transactions()
                ->where(["type" => "withdraw", "status" => "pending"])
                ->where("data->exchange", false)
                ->whereNull('tx_id')->get();

            $client = &$this->coin->client;
            foreach ($pending_transactions as $transaction) {
                if ($transaction->amount <= 0) {
                    Log::error("[{$this->coin->id}|{$this->coin->name}|{$transaction->id}] Invalid amount ({$transaction->amount})");
                    $transaction->update(["status" => "canceled"]);
                    continue;
                }

                if (!isset($transaction->data["address"]) || $transaction->data["address"] == "") {
                    Log::error("[{$this->coin->id}|{$this->coin->name}|{$transaction->id}] Address not found in withdraw transaction");
                    continue;
                }

                if ($transaction->wallet->balance < 0) {
                    Log::error("[{$this->coin->id}|{$this->coin->name}|{$transaction->id}] Withdraw canceled because amount({$transaction->amount}) bigger than balance({$transaction->wallet->balance}).");
                    $transaction->update(["status" => "canceled"]);
                    continue;
                }

                DB::transaction(function () use (&$transaction, &$client) {
                    $tx_id = $client->sendtoaddress($transaction->data["address"], $transaction->amount, $transaction->wallet->address, "", true);
                    $transaction->update(["status" => "completed", "tx_id" => $tx_id,]);
                    $tx_raw = $client->getrawtransaction($tx_id, 1);
                    foreach ($tx_raw["vout"] as $vout) {
                        if ($vout["value"] <= 0 || !$vout["scriptPubKey"] || !$vout["scriptPubKey"]["addresses"] || !in_array($transaction->data["address"], $vout["scriptPubKey"]["addresses"])) {
                            continue;
                        }

                        $transaction->update([
                            "data" => [
                                "address" => $transaction->data["address"],
                                "txout" => $vout["n"],
                                "block_hash" => $tx_raw["blockhash"],
                                "exchange" => $transaction->data["exchange"]
                            ],
                            "created_at" => Carbon::parse($tx_raw["time"])
                        ]);
                        break;
                    }
                });

            }
            return true;
        } catch (ConnectionFailureException $e) {
            Log::error("[{$this->coin->id}|{$this->coin->name}] Unable to establish a connection {$this->coin->rpc_url}");
        }
        return false;
    }
}
