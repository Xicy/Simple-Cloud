<?php

namespace App\Foundation\Exchanges;

/**
 * Class Binance
 * @package App\Foundation\Exchanges
 */
class Binance extends Base
{
    private static $url = "https://api.binance.com/api/v3/ticker/price";

    protected function getAbstractSymbols()
    {
        $data = file_get_contents(self::$url);
        $json = json_decode($data, true);
        return collect($json)->map(function ($e) {
            $last = substr($e["symbol"], -4, 4);
            if (in_array($last, ["USDT", "TUSD","USDC","USDS","BUSD"]))
                return [substr($e["symbol"], 0, -4), $last];
            return [substr($e["symbol"], 0, -3), substr($e["symbol"], -3, 3)];
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
         *  "symbol": "BTCUSDT",
         *  "price": "9395.04000000"
         * }
         */
        $data = file_get_contents(self::$url . "?symbol=" . $symbol);
        $json = json_decode($data, true);
        return $json["price"];
    }

    protected function getAbstractChange($symbol)
    {
        return null;
    }
}
