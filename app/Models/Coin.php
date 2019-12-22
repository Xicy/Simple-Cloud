<?php

namespace App\Models;

use App\Foundation\Clients\Base;
use App\Foundation\Clients\Fiat;
use App\Foundation\Markets\Base as AppBase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Coin
 * @package App\Models
 *
 * @property Base $client
 * @property AppBase $market
 * @property Wallet[] $wallets
 * @property Transaction[] $transactions
 *
 * @property int $id
 * @property string $name
 * @property int $block_time
 * @property int $height
 * @property string|null $key
 * @property string $symbol
 * @property string $gateway
 * @property string|null $rpc_url
 * @property bool $enable
 * 
 * @method Builder enable()
 *
 */
class Coin extends Model
{
    protected $fillable = ["name", "key", 'symbol', "gateway", "rpc_url", 'height', "enable", "block_time"];
    public $timestamps = false;

    public function getRouteKeyName()
    {
        return 'key';
    }

    public function getClientAttribute()
    {
        return new $this->gateway($this->rpc_url);
    }

    public function getMarketAttribute()
    {
        return new $this->attributes["market"]($this->key);
    }

    public function scopeEnable(Builder $query)
    {
        return $query->where('enable', true);
    }

    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }
    
    public function transactions()
    {
        return $this->hasManyThrough(Transaction::class, Wallet::class);
    }

    public function isFiat()
    {
        return $this->gateway === Fiat::class;
    }
}
