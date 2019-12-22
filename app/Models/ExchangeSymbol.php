<?php

namespace App\Models;

use App\Foundation\Exchanges\Base;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class ExchangeSymbol
 * @package App\Models
 * 
 * @property string $symbol
 * @property Base $client
 * @property Collection $client_symbols
 * @property Exchange $exchange
 * @property Coin $pair
 * @property Coin $coin
 * @property float $client_price
 *
 */
class ExchangeSymbol extends Model
{
    public $table = "exchange_symbols";
    protected $fillable = ["symbol"];
    public $timestamps = false;

    public function exchange()
    {
        return $this->belongsTo(Exchange::class);
    }

    public function pair()
    {
        return $this->belongsTo(Coin::class);
    }

    public function coin()
    {
        return $this->belongsTo(Coin::class);
    }

    public function getClientAttribute()
    {
        return new $this->exchange->client();
    }

    public function getClientSymbolsAttribute()
    {
        return $this->client->getSymbols();
    }

    public function getClientPriceAttribute()
    {
        return $this->client->getPrice($this->symbol);
    }

    public function getClientChangeAttribute()
    {
        return $this->client->getChange($this->symbol);
    }
}
