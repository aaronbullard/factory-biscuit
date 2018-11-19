<?php

namespace FactoryBiscuit\Tests;

use FactoryBiscuit\Hydrator;
use FactoryBiscuit\Tests\Mocks\Entity\Foo;

class HydratorTest extends TestCase
{
    /** @test */
    public function it_creates_an_instance()
    {
        $foo = Hydrator::newInstance(Foo::class, [
            'bar' => 'bar',
            'baz' => 'baz'
        ]);

        $this->assertEquals('bar', $foo->bar());
        $this->assertEquals('baz', $foo->baz());
    }

    /** @test */
    public function it_mutates_a_live_instance()
    {
        $foo = new Foo('bar', 'baz');

        Hydrator::mutate($foo, [
            'bar' => 'newBar'
        ]);

        $this->assertEquals('newBar', $foo->bar());
    }

    /** @test */
    public function it_extracts_data()
    {
        $foo = new Foo('bar', 'baz', 'qux');

        $data = Hydrator::extract($foo, ['bar', 'baz', 'qux']);

        $this->assertEquals('bar', $data['bar']);
        $this->assertEquals('baz', $data['baz']);
        $this->assertEquals('qux', $data['qux']);
    }

    /** @test */
    public function it_extracts_all_properties_by_default()
    {
        $foo = new Foo('barValue', 'bazValue', 'quxValue');

        $data = Hydrator::extract($foo);

        $this->assertEquals('barValue', $data['bar']);
        $this->assertEquals('bazValue', $data['baz']);
        $this->assertEquals('quxValue', $data['qux']);
    }

}