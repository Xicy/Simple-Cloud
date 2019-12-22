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
use JsonRPC\Exception\ResponseException;

class SyncWithdraws implements ShouldQueue
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
            $active_coin_ids = DB::table('jobs')->where('id', '!=', $this->job->getJobId())->where('queue', $this->queue)->get('payload')->map(function ($v) {
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

        Log::info("[{$this->coin->id}|{$this->coin->name}] " . get_class($this) . " Started");
        $this->withRpc();
        Log::info("[{$this->coin->id}|{$this->coin->name}] " . get_class($this) . " Ended");
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
                if (!isset($transaction->data["address"]) || $transaction->data["address"] == "") {
                    Log::error("[{$this->coin->id}|{$this->coin->name}|{$transaction->id}] Address not found in withdraw transaction");
                    continue;
                }

                if ($transaction->amount <= 0) {
                    Log::error("[{$this->coin->id}|{$this->coin->name}|{$transaction->id}] Invalid amount ({$transaction->amount})");
                    $transaction->update([
                        "status" => "canceled",
                        "data" => [
                            "reason" => "Invalid amount ({$transaction->amount})",
                            "address" => $transaction->data["address"],
                            "exchange" => $transaction->data["exchange"]
                        ]
                    ]);
                    continue;
                }

                if ($transaction->wallet->balance < 0) {
                    Log::error("[{$this->coin->id}|{$this->coin->name}|{$transaction->id}] Withdraw canceled because amount({$transaction->amount}) bigger than balance({$transaction->wallet->balance}).");
                    $transaction->update([
                        "status" => "canceled",
                        "data" => [
                            "reason" => "Withdraw canceled because amount({$transaction->amount}) bigger than balance({$transaction->wallet->balance}).",
                            "address" => $transaction->data["address"],
                            "exchange" => $transaction->data["exchange"]
                        ]
                    ]);
                    continue;
                }

                if (!$client->validateaddress($transaction->data["address"])["isvalid"]) {
                    Log::error("[{$this->coin->id}|{$this->coin->name}|{$transaction->id}] Withdraw canceled because address not valid (" . $transaction->data["address"] . ").");
                    $transaction->update([
                        "status" => "canceled",
                        "data" => [
                            "reason" => "Withdraw canceled because address not valid (" . $transaction->data["address"] . ").",
                            "address" => $transaction->data["address"],
                            "exchange" => $transaction->data["exchange"]
                        ]
                    ]);
                    continue;
                }

                DB::transaction(function () use (&$transaction, &$client) {
                    Log::info("[{$this->coin->id}|{$this->coin->name}|{$transaction->id}] Withdraw Address:" . $transaction->data["address"] . " Amount: " . $transaction->amount);
                    try {
                        $tx_id = $client->sendtoaddress($transaction->data["address"], (float) $transaction->amount);
                        $transaction->update(["status" => "completed", "tx_id" => $tx_id]);
                        sleep(1);
                        $tx_raw = $client->getrawtransaction($tx_id, 1);
                        foreach ($tx_raw["vout"] as $vout) {
                            if ($vout["value"] <= 0 || !$vout["scriptPubKey"] || !$vout["scriptPubKey"]["addresses"] || !in_array($transaction->data["address"], $vout["scriptPubKey"]["addresses"])) {
                                continue;
                            }

                            $transaction->update([
                                "data" => [
                                    "address" => $transaction->data["address"],
                                    "txout" => $vout["n"],
                                    "block_hash" => $tx_raw["blockhash"] ?? null,
                                    "exchange" => $transaction->data["exchange"],
                                    "data" => $tx_raw
                                ],
                                "created_at" => Carbon::parse($tx_raw["time"] ?? now())
                            ]);
                            break;
                        }
                    } catch (ResponseException $e) {
                        $transaction->update([
                            "status" => "canceled",
                            "data" => [
                                "reason" => $e->getMessage(),
                                "code" => $e->getCode(),
                                "data" => $e->getData(),
                                "address" => $transaction->data["address"],
                                "exchange" => $transaction->data["exchange"]
                            ]
                        ]);
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
