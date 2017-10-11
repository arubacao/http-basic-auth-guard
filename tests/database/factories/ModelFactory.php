<?php
/**
 * This file is part of Http Basic Auth Guard.
 *
 * (c) Christopher Lass <arubacao@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$factory->define(User::class, function (Faker\Generator $faker) {
    return [
        'email' => $faker->safeEmail,
        'password' => password_hash($faker->password, PASSWORD_DEFAULT),
    ];
});
