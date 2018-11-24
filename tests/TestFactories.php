<?php

use Faker\Generator;
use FactoryBiscuit\Factory;
use FactoryBiscuit\Tests\Mocks\Entity\Foo;
use FactoryBiscuit\Tests\Mocks\Entity\Bar;

$factory->define(Foo::class, function(Generator $faker, Factory $factory){
    return [
        'bar' => function() use ($factory){
            return $factory->of(Bar::class)->make();
        },
        'baz' => $faker->word,
        'qux' => $faker->word
    ];
});

$factory->define(Bar::class, function(Generator $faker, Factory $factory){
    return [
        'bar' => $faker->word
    ];
});