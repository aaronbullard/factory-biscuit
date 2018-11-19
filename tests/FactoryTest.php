<?php

namespace FactoryBiscuit\Tests;

use Mockery;
use Faker\Generator;
use Faker\Factory as Faker;
use FactoryBiscuit\Factory;
use FactoryBiscuit\Repository;
use FactoryBiscuit\ManagerRegistry;
use FactoryBiscuit\Tests\Mocks\Entity\Foo;
use FactoryBiscuit\Tests\Mocks\Entity\Bar;
use FactoryBiscuit\Tests\Mocks\Entity\Alpha;
use FactoryBiscuit\Tests\Mocks\Entity\Email;

class FactoryTest extends TestCase
{
    /**
     * @var Factory
     */
    protected $factory;
  
    public function setUp()
    {
        parent::setUp();

        $faker = Faker::create();
        $this->factory = new Factory($faker, Mockery::mock(ManagerRegistry::class));

        $this->factory->define(Alpha::class, function($faker, $factory){
            return [
                'bar' => function() use ($factory){
                    return $factory->of(Bar::class)->make();
                },
                'baz' => $faker->word,
                'qux' => $faker->word
            ];
        });

        $this->factory->define(Bar::class, function($faker, $factory){
            return [
                'bar' => $faker->word
            ];
        });
    }

    /** @test */
    public function it_makes_an_instance()
    {
        $foo = $this->factory->of(Alpha::class)->make();

        $this->assertInstanceOf(Alpha::class, $foo);
        $this->assertInstanceOf(Bar::class, $foo->bar());
    }

    /** @test */
    public function it_makes_multiple_instances()
    {
        $foos = $this->factory->of(Alpha::class)->times(3)->make();

        $this->assertCount(3, $foos);
        $this->assertInstanceOf(Alpha::class, $foos[0]);
    }

    /** @test */
    public function it_overrides_attributes()
    {
        $foo = $this->factory->of(Alpha::class)->make([
            'baz' => 'newBaz'
        ]);

        $this->assertEquals('newBaz', $foo->baz());
    }

     /** @test */
     public function it_only_resolves_requested_attributes()
     {
         $this->factory->define(Foo::class, function($faker){
            return [
                'bar' => $faker->name,
                'baz' => function(){
                    throw new \Exception("'baz' must be overridden!");
                }
            ];
         });

         $instance = $this->factory->of(Foo::class)->make([
            'baz' => 'Smith'
         ]);

         $this->expectException(\Exception::class, "'baz' must be overridden!");
         $instance = $this->factory->of(Foo::class)->make();
     }

     /** @test */
    public function it_makes_an_instance_with_defined_state()
    {
        $this->factory->defineAs(Alpha::class, 'colors', function($faker, $factory){
            return [
                'bar' => 'red',
                'baz' => 'green',
                'qux' => 'blue'
            ];
        });

        $foo = $this->factory->of(Alpha::class, 'colors')->make();

        $this->assertInstanceOf(Alpha::class, $foo);
        $this->assertEquals('red', $foo->bar());
        $this->assertEquals('green', $foo->baz());
    }

    /**
     * @test
     */
    public function try_to_persist()
    {
        $repo = Mockery::mock(Repository::class);
        $repo->shouldReceive('save')->once();

        $registry = Mockery::mock(ManagerRegistry::class);
        $registry->shouldReceive('getRepositoryForClass')->once()->with(Foo::class)
                 ->andReturn($repo);

        $factory = new Factory(Faker::create(), $registry);
        $factory->define(Foo::class, function(Generator $faker, Factory $factory) {
            return [
                'bar' => $faker->word
            ];
        });

        $foo = $factory->of(Foo::class)->create();
        $this->assertInstanceOf(Foo::class, $foo);
    }

    /** @test */
    public function it_loads_from_a_file()
    {
        $this->factory->load(__DIR__ . '/TestFactories.php');

        $email = $this->factory->of(Email::class)->make();

        $this->assertInstanceOf(Email::class, $email);
    }

}