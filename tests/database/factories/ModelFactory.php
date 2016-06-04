<?php
/**
 * This file is part of Http Basic Auth Guard.
 *  
 * (c) Christopher Lass <arubacao@gmail.com>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->safeEmail,
        'password' => bcrypt(str_random(10)),
    ];
});
