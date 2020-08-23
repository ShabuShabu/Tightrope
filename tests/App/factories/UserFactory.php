<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Support\Str;
use ShabuShabu\Tightrope\Tests\App\User;

$factory->define(User::class, fn(Faker $faker) => [
    'id'                => Str::orderedUuid()->toString(),
    'name'              => $faker->name,
    'email'             => $faker->unique()->safeEmail,
    'password'          => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
    'remember_token'    => Str::random(10),
    'email_verified_at' => now(),
]);

$factory->state(User::class, 'unverified', fn() => [
    'email_verified_at' => null,
]);

$factory->state(User::class, 'trashed', fn() => [
    'deleted_at' => now(),
]);
