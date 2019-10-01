<?php

namespace App\Foundation\Clients;

/**
 * Class DASH
 * @package App\Foundation\Clients
 */
class DASH extends Base
{
    public function supply()
    {
        $supply = $this->client->getinfo()["moneysupply"] ?? null;
        if (!$supply)
            $supply = $this->client->getmininginfo()["moneysupply"] ?? null;
        if (!$supply)
            $supply = $this->client->gettxoutsetinfo()["total_amount"] ?? null;
        return $supply;
    }

    public function getBlockAverage()
    {
        $block_last_hash = $this->client->getblockhash($this->client->getblockcount());
        $block_last = $this->client->getblock($block_last_hash);
        $block_previous = $this->client->getblock($block_last["previousblockhash"]);
        return response_data(["last" => $block_last, "previous" => $block_previous, "avarage" => $block_last["time"] - $block_previous["time"]]);
    }
}
