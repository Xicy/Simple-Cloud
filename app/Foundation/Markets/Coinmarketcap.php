<?php

namespace App\Foundation\Markets;

/**
 * Class Coingecko
 * @package App\Foundation\Markets
 */
class Coinmarketcap extends Base
{
    private static $url = "https://api.coinmarketcap.com/v1/ticker/%s/";

    protected function getAbstractPrice($pair)
    {
        $data = file_get_contents(sprintf(self::$url, $this->key));
        $json = json_decode($data, true);
        return $json[0]["price_" . strtolower($pair)];
    }
}
