<?php

namespace App\Models;

use App\Foundation\Exchanges\Base;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Exchange
 * @package App\Models
 * 
 * @property string $name
 * @property string $website
 * @property string $client
 * 
 * @property ExchangeSymbol[] $symbols
 * @property Base $client
 *
 */
class Exchange extends Model
{
    protected $fillable = ["name", "website", "client"];
    public $timestamps = false;

    public function symbols()
    {
        return $this->hasMany(ExchangeSymbol::class);
    }

    public function getClientAttribute()
    {
        if (isset($this->attributes["client"]))
            return new $this->attributes["client"]();
        return null;
    }
}
