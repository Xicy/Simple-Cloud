<?php

namespace App\Foundation\Exchanges;

/**
 * Class CryptoBridge
 * @package App\Foundation\Exchanges
 */
class CryptoBridge extends Base
{
    private static $url = "https://api.crypto-bridge.org/v2/market/pubticker/%s";
    private static $urlSymbols = "https://api.crypto-bridge.org/v2/market/symbols";

    protected function getAbstractSymbols()
    {
        $data = file_get_contents(self::$urlSymbols);
        $json = json_decode($data, true);
        return collect($json)->map(function ($e) {
            return explode("_", $e);
        })->filter(function ($e) {
            return $e != null;
        });
    }

    protected function getImplodeSymbol($symbols)
    {
        return implode("_", $symbols);
    }

    protected function getAbstractPrice($symbol)
    {
        /**
         * {
         *  "mid": "0.00000436",
         *  "bid": "0.00000433",
         *  "ask": "0.00000440",
         *  "last_price": "0.00000440",
         *  "low": "0.00000440",
         *  "high": "0.00000433",
         *  "volume": "0.0272415",
         *  "percent_change": 0.86,
         *  "timestamp": 1572296358
         * }
         */
        $data = file_get_contents(sprintf(self::$url, $symbol));
        $json = json_decode($data, true);
        return $json["mid"];
    }

    protected function getAbstractChange($symbol)
    {
        $data = file_get_contents(sprintf(self::$url, $symbol));
        $json = json_decode($data, true);
        return $json["percent_change"] * 100;
    }
}
