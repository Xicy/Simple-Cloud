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
 * @property string|null $password
 * @property float $balance
 */
class Wallet extends Model
{
    public $timestamps = false;
    protected $fillable = ["address", 'password', 'user_id'];
    protected $hidden = ['coin_id', 'password', 'user_id'];

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
        return (float)($this->transactions()
            ->select(DB::raw('(SUM((CASE WHEN (`type` = "withdraw") THEN -1 ELSE 1 END) * amount)) as balance'))
            ->where('status', '!=', 'canceled')
            ->value("balance") ?: 0);

    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function getLockedAttribute()
    {
        return (float)($this->transactions()
            ->select(DB::raw('(SUM((CASE WHEN (`type` = "withdraw") THEN 1 ELSE 1 END) * amount)) as balance'))
            ->where('status', 'pending')
            ->value("balance") ?: 0);

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
