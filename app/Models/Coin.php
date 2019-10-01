<?php

namespace App\Models;

use App\Foundation\Clients\Base;
use App\Foundation\Clients\Fiat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Coin
 * @package App\Models
 *
 * @property Base $client
 * @property Wallet[] $wallets
 * @property Transaction[] $transactions
 * @property History[] $histories
 * @property History $currency
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
 */
class Coin extends Model
{
    public $timestamps = false;
    protected $fillable = ["name", "key", 'symbol', "gateway", "rpc_url", 'height', "enable", "block_time"];

    public function getRouteKeyName()
    {
        return 'key';
    }

    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }

    public function histories()
    {
        return $this->hasMany(History::class);
    }

    public function currency()
    {
        return $this->hasOne(History::class)->latest();
    }

    public function getClientAttribute()
    {
        return new $this->gateway($this->rpc_url);
    }

    public function scopeNotFiat(Builder $query)
    {
        return $query->where('gateway', '!=', Fiat::class);
    }

    public function scopeEnable(Builder $query)
    {
        return $query->where('enable', true);
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
