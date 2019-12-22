<?php

$factory->define(App\Models\Role::class, function (Faker\Generator $faker) {
    return [
        "title" => $faker->name,
    ];
});
