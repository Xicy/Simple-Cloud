<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class Wallet
 * @package App\Models
 * @property Coin $coin
 * @property User $user
 * @property Collection|Transaction[] $transactions
 *
 * @property int $id
 * @property string|null $address
 * @property float $balance
 * @property float $amount
 */
class Wallet extends Model
{
    public $timestamps = false;
    protected $fillable = ["address", 'user_id', 'coin_id'];
    protected $hidden = ['coin_id', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coin()
    {
        return $this->belongsTo(Coin::class);
    }

    public function getBalanceAttribute()
    {
        return (float) ($this->amount); //* $this->coin->market->getPrice();
    }

    public function getAmountAttribute()
    {
        return (float) ($this->transactions()
            ->select(DB::raw('(SUM((CASE WHEN (`type` = "withdraw") THEN -1 ELSE 1 END) * amount)) as balance'))
            ->where('status', '!=', 'canceled')
            ->value('balance') ?: 0);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function withdraw(string $address, float $amount, bool $exchange = null)
    {
        return !!$this->transactions()->create([
            "status" => "pending",
            "amount" => $amount,
            "type" => "withdraw",
            "data" => [
                "address" => $address,
                "exchange" => $exchange ?? $this->coin->isFiat()
            ]
        ]);
    }
}
