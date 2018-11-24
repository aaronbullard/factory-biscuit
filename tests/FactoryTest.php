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

class FactoryTest extends TestCase
{
    /**
     * @var Factory
     */
    protected $factory;
  
    public function setUp()
    {
        parent::setUp();

        $this->factory = new Factory();

        $this->factory->define(Foo::class, function(Generator $faker, Factory $factory){
            return [
                'bar' => function() use ($factory){
                    return $factory->of(Bar::class)->make();
                },
                'baz' => $faker->word,
                'qux' => $faker->word
            ];
        });

        $this->factory->define(Bar::class, function(Generator $faker, Factory $factory){
            return [
                'bar' => $faker->word
            ];
        });
    }

    /** @test */
    public function it_makes_an_instance()
    {
        $foo = $this->factory->of(Foo::class)->make();

        $this->assertInstanceOf(Foo::class, $foo);
        $this->assertInstanceOf(Bar::class, $foo->bar());
    }

    /** @test */
    public function it_makes_multiple_instances()
    {
        $foos = $this->factory->of(Foo::class)->times(3)->make();

        $this->assertInstanceOf(Foo::class, $foos[0]);
        $this->assertCount(3, $foos);
    }

    /** @test */
    public function it_overrides_attributes()
    {
        $foo = $this->factory->of(Foo::class)->make([
            'baz' => 'newBaz'
        ]);

        $this->assertEquals('newBaz', $foo->baz());
    }

     /** @test */
     public function it_only_resolves_requested_attributes()
     {
         $this->factory->define(Foo::class, function(Generator $faker){
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
        $this->factory->defineAs(Foo::class, 'colors', function($faker, $factory){
            return [
                'bar' => 'red',
                'baz' => 'green',
                'qux' => 'blue'
            ];
        });

        $foo = $this->factory->of(Foo::class, 'colors')->make();

        $this->assertInstanceOf(Foo::class, $foo);
        $this->assertEquals('red', $foo->bar());
        $this->assertEquals('green', $foo->baz());
    }

    /** @test */
    public function it_persists_on_creation()
    {
        // Mock repo
        $repo = Mockery::mock(Repository::class);
        $repo->shouldReceive('save')->once();

        // Mock ManagerRegistry
        $registry = Mockery::mock(ManagerRegistry::class);
        $registry->shouldReceive('getRepositoryForClass')->once()->with(Foo::class)
                 ->andReturn($repo);

        // Create factory instance
        $factory = new Factory(Faker::create(), $registry);

        // Define Foo::class
        $factory->define(Foo::class, function(Generator $faker, Factory $factory) {
            return [
                'bar' => $faker->word
            ];
        });

        $foo = $factory->of(Foo::class)->create();
        $this->assertInstanceOf(Foo::class, $foo);
    }

    /** @test */
    public function throws_exception_if_no_manager_registry_available_when_using_create()
    {
        $this->expectException(\RuntimeException::class);
        $this->factory->of(Foo::class)->create();
    }

    /** @test */
    public function it_loads_from_a_file()
    {
        $factory = new Factory();

        $factory->load(__DIR__ . '/TestFactories.php');

        $foo = $factory->of(Foo::class)->make();

        $this->assertInstanceOf(Foo::class, $foo);
    }

}