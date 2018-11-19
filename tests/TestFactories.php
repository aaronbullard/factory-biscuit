<?php

use FactoryBiscuit\Tests\Mocks\Entity\Email;

$factory->define(Email::class, function($faker){
    return [
        'email' => $faker->safeEmail
    ];
});