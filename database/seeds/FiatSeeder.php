<?php

use App\Foundation\Clients\DASH;
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
        Coin::create([
            "name" => "Simple Software Solutions",
            "key" => "sssolutions",
            "symbol" => "SSS",
            "gateway" => DASH::class,
            "rpc_url" => "http://sdjvndflhbvdflbh:akjfngvklebgvlkfsdbgvlkfds@199.247.7.91:6740",
            //"market" => Coingecko::class,
            "height" => 0,
            "enable" => true,
            "block_time" => 60
        ]);
    }
}
