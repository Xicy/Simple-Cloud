<?php

namespace App\Foundation\Markets;

use App\Models\Coin;
use Illuminate\Support\Facades\Cache;

/**
 * Class Base
 * @package App\Foundation\Markets
 */
abstract class Base
{
    protected $key;
    abstract protected function getAbstractPrice($pair);

    public function __construct($key)
    {
        $this->key = $key;
    }

    protected function getClassName()
    {
        $classname = get_class($this);
        if ($pos = strrpos($classname, '\\')) return substr($classname, $pos + 1);
        return $pos;
    }

    protected function getCacheKey($pair)
    {
        return strtolower($this->getClassName() . "_" . $this->key . "_" . $pair);
    }

    public function getPrice($pair = "USD")
    {
        return Cache::remember($this->getCacheKey($pair), 600, function () use ($pair) {
            return floatval($this->getAbstractPrice($pair));
        });
    }
}
