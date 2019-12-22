<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Carbon;

/**
 * Class Node
 * @package App\Models
 *
 * @property User $user
 * @property Exchange $exchange
 * @property ExchangeSymbol $exchangeSymbol
 * @property Coin $coin
 * @property Coin $pairCoin
 * @property User[] $buyers
 * 
 * @property string $remaing_time
 * @property string $status_for_human
 * @property string $created_at_for_human
 * @property string $updated_at_for_human
 * @property string $entry_at_for_human
 * @property string $expiration_at_for_human
 * @property string $exit_at_for_human
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property int $exchange_symbol_id
 * @property string $status
 * @property float $entry_price
 * @property float $exit_price
 * @property Carbon $entry_at
 * @property Carbon $expiration_at
 * @property Carbon $exit_at
 * @property string $blockchain_address
 * @property float $purchase_price
 * @property float $stake
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 */
class Node extends Model
{
    protected $fillable = ["name", "status", "entry_price", "exit_price", "entry_at", "expiration_at", "exit_at", "blockchain_address", "stake", "exchange_symbol_id", "reason"];
    protected $dates = ['entry_at', "expiration_at", "exit_at", "created_at", "updated_at"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function exchange()
    {
        return $this->hasOneThrough(Exchange::class, ExchangeSymbol::class, "id", "id", "exchange_symbol_id", "exchange_id");
    }

    public function exchangeSymbol()
    {
        return $this->belongsTo(ExchangeSymbol::class);
    }

    public function coin()
    {
        return $this->hasOneThrough(Coin::class, ExchangeSymbol::class, "id", "id", "exchange_symbol_id", "coin_id");
    }

    public function pairCoin()
    {
        return $this->hasOneThrough(Coin::class, ExchangeSymbol::class, "id", "id", "exchange_symbol_id", "pair_id");
    }

    public function buyers()
    {
        return $this->belongsToMany(User::class, "node_user")->withPivot("amount", 'coin_id', 'created_at');
    }

    public function getPurchasePriceAttribute()
    {
        $clientPrice = $this->exchangeSymbol->client_price;
        $marketPrice = $this->exchangeSymbol->pair->market->getPrice();
        return  $clientPrice * $marketPrice;
    }

    public function getRemaingTimeAttribute()
    {
        $diff = $this->expiration_at->getTimestamp() - now()->getTimestamp();
        if ($this->exit_at != null || $diff <= 0)
            return "-";
        return $this->expiration_at->diffForHumans(now());
    }

    public function getStatusForHumanAttribute()
    {
        return status_for_human($this->attributes["status"]);
    }

    public function getCreatedAtForHumanAttribute()
    {
        return optional($this->created_at)->format(config("node.datetimeFormat"));
    }
    public function getUpdatedAtForHumanAttribute()
    {
        return optional($this->updated_at)->format(config("node.datetimeFormat"));
    }
    public function getEntryAtForHumanAttribute()
    {
        return optional($this->entry_at)->format(config("node.datetimeFormat"));
    }
    public function getExpirationAtForHumanAttribute()
    {
        return optional($this->expiration_at)->format(config("node.datetimeFormat"));
    }
    public function getExitAtForHumanAttribute()
    {
        return optional($this->exit_at)->format(config("node.datetimeFormat"));
    }
}
