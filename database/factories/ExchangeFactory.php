<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Foundation\Exchanges\Bitfinex;
use App\Foundation\Exchanges\Binance;
use App\Foundation\Exchanges\CryptoBridge;
use App\Foundation\Exchanges\Digifinex;
use App\Models\Exchange;
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

$data =  [
    [
        "name" => "DigiFinex",
        "website" => "https://www.digifinex.com/",
        "client" => Digifinex::class
    ],
    [
        "name" => "Binance",
        "website" => "https://www.binance.com/",
        "client" => Binance::class
    ],
    [
        "name" => "Bitfinex",
        "website" => "https://www.bitfinex.com/",
        "client" => Bitfinex::class
    ],
    [
        "name" => "CryptoBridge",
        "website" => "https://crypto-bridge.org/",
        "client" => CryptoBridge::class
    ]
];
$factory->define(Exchange::class, function (Faker $faker) use ($data) {
    $exchange = $faker->unique()->randomElement($data);
    return $exchange;
});
