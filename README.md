# Factory Biscuit

Quickly create PHP class instances for testing using Faker and Firehose Hydrator.

## Installation

### Library

```bash
git clone git@github.com:aaronbullard/factory-biscuit.git
```

### Composer

[Install PHP Composer](https://getcomposer.org/doc/00-intro.md)

```bash
composer require aaronbullard/factory-biscuit
```

### Testing

```bash
composer test
```

## Usage

Define your factories
```php
// Factories.php

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
```

Load your factory file
```php
use FactoryBiscuit\Factory;

$factory = new Factory();

$factory->load(__DIR__ . '/Factories.php');
```

## Examples

Create an instance
```php
$foo = $factory->of(Foo::class)->make();

$this->assertInstanceOf(Foo::class, $foo);
```

Create multiple instances
```php
$foos = $factory->of(Foo::class)->times(3)->make();

$this->assertInstanceOf(Foo::class, $foos[0]);
$this->assertCount(3, $foos);
```

Override attributes of an instance
```php
$foo = $factory->of(Foo::class)->make([
    'baz' => 'newBaz'
]);

$this->assertEquals('newBaz', $foo->baz());
```

Create a specially defined instance type
```php
// Create a factory of class Foo with settings of 'colors'
$factory->defineAs(Foo::class, 'colors', function($faker, $factory){
    return [
        'bar' => 'red',
        'baz' => 'green',
        'qux' => 'blue'
    ];
});

$foo = $factory->of(Foo::class, 'colors')->make();

$this->assertInstanceOf(Foo::class, $foo);
$this->assertEquals('red', $foo->bar());
$this->assertEquals('green', $foo->baz());
```

Implement the ManagerRepository interface to persist instances upon creation
```php
// call the 'create' method to create a persisted instance
$user = $factory->of(User::class)->create();

$this->assertInstanceOf(User::class, $user);
$this->assertTrue(
    DB::table('users')->where('id', '=', $user->id)->exists()
);
```

For more examples, see the tests: `tests\FactoryTest.php`