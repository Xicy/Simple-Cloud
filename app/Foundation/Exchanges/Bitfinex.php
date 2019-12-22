<?php

namespace App\Foundation\Exchanges;

/**
 * Class Bitfinex
 * @package App\Foundation\Exchanges
 */
class Bitfinex extends Base
{
    private static $url = "https://api.bitfinex.com/v1/pubticker/%s";
    private static $urlSymbols = "https://api-pub.bitfinex.com/v2/tickers?symbols=ALL";

    protected function getAbstractSymbols()
    {
        $data = file_get_contents(self::$urlSymbols);
        $json = json_decode($data, true);
        return collect($json)->map(function ($e) {
            if (strlen($e[0]) == 7) {
                return [substr($e[0], 1, 3), substr($e[0], 4, 3)];
            }
        })->filter(function ($e) {
            return $e != null;
        });
    }

    protected function getImplodeSymbol($symbols)
    {
        return implode("", $symbols);
    }

    protected function getAbstractPrice($symbol)
    {
        /**
         * {
         *  "mid":"244.755",
         *  "bid":"244.75",
         *  "ask":"244.76",
         *  "last_price":"244.82",
         *  "low":"244.2",
         *  "high":"248.19",
         *  "volume":"7842.11542563",
         *  "timestamp":"1444253422.348340958"
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
        return $json["last_price"] / $json["mid"] * ($json["last_price"] < $json["mid"] ? -1 : 1);
    }
}
