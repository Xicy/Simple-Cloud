<?php

use App\Foundation\Clients\Fiat;
use App\Models\Coin;
use Illuminate\Database\Seeder;

class FiatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /** @var Coin $coin */
        $coin = Coin::create([
            "name" => "Credit",
            "key" => "credit",
            "symbol" => "C",
            "gateway" => Fiat::class,
            "height" => 0,
            "enable" => true,
            "block_time" => 60
        ]);
        $coin->histories()->create(["price" => 1, "change" => 0]);
    }
}
