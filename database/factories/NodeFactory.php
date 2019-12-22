<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\ExchangeSymbol;
use App\Models\Node;
use App\Models\User;
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

$factory->define(Node::class, function (Faker $faker) {
    $exchangeSymbol = ExchangeSymbol::inRandomOrder()->take(1)->get()[0];
    $clientPrice = $exchangeSymbol->client_price;
    //$marketPrice = $exchangeSymbol->pair->market->getPrice();
    //$purchase_price =   $clientPrice * $marketPrice;

    $entry_price =  $faker->randomFloat(8, $clientPrice * 0.5, $clientPrice);
    $exit_price =  $faker->randomFloat(8, $clientPrice, $clientPrice * 1.5);

    $status = $faker->randomElement(["open", "success", "natural", "closed"]);
    $entry_at = $faker->dateTimeBetween('-50 days', '0 days');
    $expiration_at = $status != "open" ? $faker->dateTimeBetween($entry_at, 'now') : $faker->dateTimeBetween('now', '1 year');
    $exit_at = $status != "open" ? $faker->dateTimeBetween($entry_at, $expiration_at) : null;
    return  [
        "user_id" => function(){ return User::inRandomOrder()->value('id'); },
        "name" => $faker->name,
        "exchange_symbol_id" => $exchangeSymbol->id,
        "status" => $status,
        "entry_price" => $entry_price,
        "exit_price" => $exit_price,
        "entry_at" => $entry_at,
        "expiration_at" => $expiration_at,
        "exit_at" => $exit_at,
        "reason" => null,
        "blockchain_address" => $faker->bothify("T*********************************"),
        //"purchase_price" => $purchase_price,
        "stake" => $faker->randomFloat(0, 100)
    ];
});
