<?php

namespace App\Foundation\Markets;

/**
 * Class Coingecko
 * @package App\Foundation\Markets
 */
class Coingecko extends Base
{
    private static $url = "https://api.coingecko.com/api/v3/coins/%s?localization=en&tickers=false&market_data=true&community_data=false&developer_data=false&sparkline=false";

    protected function getAbstractPrice($pair)
    {
        $data = file_get_contents(sprintf(self::$url, $this->key));
        $json = json_decode($data, true);
        return $json["market_data"]["current_price"][strtolower($pair)];
    }
}
