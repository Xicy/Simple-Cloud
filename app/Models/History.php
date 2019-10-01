<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class History
 * @package App\Models
 * @property Coin $coin
 *
 * @property int $id
 * @property float $price
 * @property float $change
 * @property \DateTime|null $created_at
 * @property \DateTime|null $updated_at
 */
class History extends Model
{
    protected $fillable = ["price", "change", "created_at"];

    public function coin()
    {
        return $this->belongsTo(Coin::class);
    }
}
