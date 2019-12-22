<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Transaction;
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

$factory->define(Transaction::class, function (Faker $faker) {
    $type =  $faker->randomElement(['deposit', 'deposit', 'withdraw', 'deposit']);
    $created_at = $faker->dateTimeBetween('-50 days', '0 days');
    $data = $type == "deposit" ? [
        "txout" => $faker->numberBetween(1, 30),
        "block_number" => $faker->numberBetween(1000, 10000),
        "exchange" => $faker->boolean(10)
    ] : [
        "address" => $faker->bothify("T*********************************"),
        "txout" => $faker->numberBetween(1, 30),
        "block_hash" => $faker->bothify("****************************************************************"),
        "exchange" => $faker->boolean(10)
    ];
    return [
        'status' => $faker->randomElement(['completed', 'pending', 'completed', 'canceled', 'completed']),
        'amount' => $faker->randomFloat(8, 1, 10),
        'tx_id' => $faker->bothify("****************************************************************"),
        'type' => $type,
        'data' => $data,
        'created_at' => $created_at,
        'updated_at' => $created_at
    ];
});
