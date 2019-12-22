<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Coin;
use App\Models\Wallet;
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

$factory->define(Wallet::class, function (Faker $faker) {
    return [
        'address' => $faker->bothify("T*********************************"),
        'coin_id' => function () {
            return Coin::inRandomOrder()->value('id');
        },
    ];
});
