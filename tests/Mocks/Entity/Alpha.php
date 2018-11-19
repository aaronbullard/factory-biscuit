<?php

namespace FactoryBiscuit\Tests\Mocks\Entity;

class Alpha
{
    private $bar;
    protected $baz;
    private $qux;

    public function __construct($bar, $baz = null, $qux = null)
    {
        $this->bar = $bar;
        $this->baz = $baz;
        $this->qux = $qux;
    }

    public function bar(){ return $this->bar; }
    public function baz(){ return $this->baz; }
}