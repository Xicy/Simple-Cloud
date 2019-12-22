<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Foundation\Clients\BTC;
use App\Foundation\Clients\DASH;
use App\Foundation\Markets\Coingecko;
use App\Foundation\Markets\Coinmarketcap;
use App\Models\Coin;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Coin::class, function (Faker $faker) {
    return [
        "name" => $faker->firstName,
        "key" => $faker->lexify("??????????"),
        "symbol" => strtoupper($faker->lexify("???")),
        "gateway" => $faker->randomElement([DASH::class, BTC::class]),
        "rpc_url" => "http://$faker->userName:$faker->password@$faker->ipv4:" . $faker->numberBetween(10000, 32000),
        "market" => $faker->randomElement([Coingecko::class, Coinmarketcap::class]),
        "height" => $faker->numberBetween(1000, 320000),
        "enable" => false,
        "block_time" => $faker->numberBetween(15, 600)
    ];
});
