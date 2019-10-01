<?php

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
        /** @var \App\Models\Coin $coin */
        $coin = \App\Models\Coin::create([
            "name" => "Credit",
            "key" => "credit",
            "symbol" => "C",
            "gateway" => \App\Foundation\Clients\Fiat::class,
            "height" => 0,
            "enable" => true,
            "block_time" => 60
        ]);
        $coin->histories()->create(["price" => 1, "change" => 0]);
    }
}
