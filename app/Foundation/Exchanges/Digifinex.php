<?php

namespace App\Foundation\Exchanges;

/**
 * Class Digifinex
 * @package App\Foundation\Exchanges
 */
class Digifinex extends Base
{
    private static $url = "https://openapi.digifinex.vip/v3/ticker?symbol=%s";
    private static $urlSymbols = "https://openapi.digifinex.vip/v3/markets";

    protected function getAbstractSymbols()
    {
        $data = file_get_contents(self::$urlSymbols);
        $json = json_decode($data, true);
        return collect($json["data"])->map(function ($e) {
            return explode("_", $e["market"]);
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
         *   "ticker": [
         *     {
         *       "vol": 61790.402,
         *       "change": 1.16,
         *       "base_vol": 578628169.55017,
         *       "sell": 9427.43,
         *       "last": 9427.68,
         *       "symbol": "btc_usdt",
         *       "low": 9195.01,
         *       "buy": 9427.24,
         *       "high": 9533.65
         *     }
         *   ],
         *   "date": 1572338319,
         *   "code": 0
         * }
         */
        $data = file_get_contents(sprintf(self::$url, $symbol));
        $json = json_decode($data, true);
        return ($json["ticker"][0]["sell"] + $json["ticker"][0]["buy"]) / 2;
    }

    protected function getAbstractChange($symbol)
    {
        $data = file_get_contents(sprintf(self::$url, $symbol));
        $json = json_decode($data, true);
        return $json["ticker"][0]["change"];
    }
}
