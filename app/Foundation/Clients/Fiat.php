<?php

namespace App\Foundation\Clients;

use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

/**
 * Class Fiat
 * @package App\Foundation\Clients
 */
class Fiat extends Base
{
    private $isBatch = false;

    private $batch = [];

    public function __construct()
    {

    }

    public function getblockcount()
    {
        $payload = Transaction::count() + 1;
        if ($this->isBatch) {
            $this->batch[] = $payload;
            return $this;
        }
        return $payload;
    }

    public function getblockhash(int $height)
    {
        $payload = Transaction::query()->orderBy("id")->skip($height - 1)->value("id");
        if ($this->isBatch) {
            $this->batch[] = $payload;
            return $this;
        }
        return $payload;
    }

    public function getblock(string $blockhash, bool $verbose = true)
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::find($blockhash);
        $to_address = $transaction->data["address"] ?? "";

        if ($transaction->type == "withdraw" && $transaction->status == "pending" && ($transaction->data["exchange"] ?? false))
            if ($transaction->wallet->balance >= 0) {
                $tx_id = $this->generate_tx($transaction->wallet->address, $to_address, $transaction->amount * $transaction->coin->currency->price);
                $transaction->update(["status" => "completed", "tx_id" => $tx_id]);
            } else {
                $tx_id = $this->generate_tx($transaction->wallet->address, null, 0);
                $transaction->update(["status" => "canceled", "tx_id" => $tx_id]);
            }

        $payload = [
            "confirmations" => random_int(1000, 10000),
            "tx" => [$tx_id ?? $this->generate_tx($transaction->wallet->address, null, 0)],
        ];
        if ($this->isBatch) {
            $this->batch[] = $payload;
            return $this;
        }
        return $payload;
    }

    private function generate_tx($from, $to, $amount)
    {
        mt_srand((double)microtime() * 10000);
        $charid = md5(uniqid(rand(), true));
        $amount = $this->base32_encode($amount);
        return "$charid.$from.$to.$amount";

    }

    private function base32_encode($d)
    {
        list($t, $b, $r) = array(mb_strtolower("ABCDEFGHIJKLMNOPQRSTUVWXYZ234567"), "", "");

        foreach (str_split($d) as $c)
            $b = $b . sprintf("%08b", ord($c));

        foreach (str_split($b, 5) as $c)
            $r = $r . $t[bindec($c)];

        return ($r);
    }

    public function getnewaddress($account)
    {
        mt_srand((double)microtime() * 10000);
        $payload = md5(uniqid(rand(), true));
        if ($this->isBatch) {
            $this->batch[] = $payload;
            return $this;
        }
        return $payload;
    }

    public function batch()
    {
        $this->isBatch = true;
        $this->batch = [];
        return $this;
    }

    public function send()
    {
        $this->isBatch = false;
        return $this->batch;
    }

    public function sendtoaddress(string $address, float $amount, string $comment = "", string $comment_to = "", bool $subtractfeefromamount = false)
    {
        $tx_id = $this->generate_tx($comment, $address, $amount);

        /** @var Transaction $tx */
        $tx = Transaction::create([
            "status" => "completed",
            "amount" => $amount,
            "type" => "deposit",
            "tx_id" => $tx_id,
            "wallet_id" => $address,
            "data" => [
                "txout" => 0,
                "block_number" => 0,
                "exchange" => true
            ]
        ]);
        $tx->update(["data->block_number" => $tx->id]);

        if ($this->isBatch) {
            $this->batch[] = $tx_id;
            return $this;
        }
        return $tx_id;
    }

    public function getrawtransaction(string $txid, int $verbose = 1)
    {
        list($address_from, $address_to, $amount) = $this->parse_tx($txid);
        $payload = !$address_to ? false : [
            "blockhash" => "",
            "time" => now()->timestamp,
            "txid" => $txid,
            "vout" => [
                [
                    "value" => $amount,
                    "scriptPubKey" => [
                        "addresses" => [$address_to]
                    ],
                    "n" => 0
                ]
            ]
        ];

        if ($this->isBatch) {
            if ($payload !== false)
                $this->batch[] = $payload;
            return $this;
        }

        return $payload;
    }

    private function parse_tx($txid)
    {
        $address = explode('.', $txid, 4);
        $from = $address[1];
        $to = $address[2];
        $amount = floatval($this->base32_decode($address[3]));
        return [$from, $to, $amount, $address[0]];
    }

    private function base32_decode($d)
    {
        list($t, $b, $r) = array(mb_strtolower("ABCDEFGHIJKLMNOPQRSTUVWXYZ234567"), "", "");

        foreach (str_split($d) as $c)
            $b = $b . sprintf("%05b", strpos($t, $c));

        foreach (str_split($b, 8) as $c)
            $r = $r . chr(bindec($c));

        return ($r);
    }
}
