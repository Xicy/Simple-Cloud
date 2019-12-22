<?php

namespace App\Foundation;

use App\Models\Coin;

class Clients
{
    public static $clients;
    private static $selected = null;

    public function __construct()
    {
        foreach (Coin::get() as $coin) {
            $_class = $coin->gateway ?? DASH::class;
            self::$clients[mb_strtolower($coin->key)] = new $_class($coin->rpc_url);
        }
    }

    public function __get($name)
    {
        return self::$clients[mb_strtolower($name)];
    }

    public function select($name)
    {
        $name = mb_strtolower($name);
        if (in_array($name, array_keys(self::$clients))) {
            self::$selected = $name;
            return true;
        }
        return false;
    }

    public function __call($name, $arguments)
    {
        if (!!self::$selected)
            return self::$clients[self::$selected]->$name(...$arguments);
        return false;
    }
}
