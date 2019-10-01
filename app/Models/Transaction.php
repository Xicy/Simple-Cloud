<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Transaction
 * @package App\Models
 * @property Wallet $wallet
 * @property User $user
 * @property Coin $coin
 * @property \DateTime $groupOfDate
 *
 * @property int $id
 * @property string $status completed, pending, canceled
 * @property float $amount
 * @property string|null $tx_id
 * @property string $type withdraw, deposit
 * @property array|null $data
 * @property \DateTime|null $created_at
 * @property \DateTime|null $updated_at
 */
class Transaction extends Model
{
    protected $fillable = ["status", "amount", "tx_id", "wallet_id", "type", "data", "created_at"];
    protected $casts = [
        "data" => "array"
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function getUserAttribute()
    {
        return $this->wallet->user;
    }

    public function getCoinAttribute()
    {
        return $this->wallet->coin;
    }

    public function getGroupOfDateAttribute()
    {
        return $this->created_at->modify('midnight');
    }
}
