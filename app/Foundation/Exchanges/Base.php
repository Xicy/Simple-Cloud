<?php

namespace App\Foundation\Exchanges;

use Illuminate\Support\Facades\Cache;

/**
 * Class Base
 * @package App\Foundation\Exchanges
 */
abstract class Base
{
    abstract protected function getAbstractPrice($symbol);
    abstract protected function getAbstractChange($symbol);
    abstract protected function getAbstractSymbols();
    abstract protected function getImplodeSymbol($symbols);

    public function __construct()
    { }

    protected function getClassName()
    {
        $classname = get_class($this);
        if ($pos = strrpos($classname, '\\')) return substr($classname, $pos + 1);
        return $pos;
    }

    protected function getCacheKey($key)
    {
        return strtolower($this->getClassName() . "_" . $key);
    }

    public function getPrice($symbol)
    {
        return Cache::remember($this->getCacheKey($symbol), 600, function () use ($symbol) {
            return floatval($this->getAbstractPrice($symbol));
        });
    }

    public function getChange($symbol)
    {
        return Cache::remember($this->getCacheKey($symbol."_change"), 600, function () use ($symbol) {
            return floatval($this->getAbstractChange($symbol));
        });
    }

    public function getSymbols()
    {
        return Cache::remember($this->getCacheKey("symbols"), 600, function () {
            return $this->getAbstractSymbols();
        });
    }

    public function implodeSymbol($symbols){
        return $this->getImplodeSymbol($symbols);
    }
}
