<?php

namespace FactoryBiscuit\Tests\Mocks\Entity;

class Email
{
    protected $email;

    public function __construct(string $email)
    {
        $this->email = $email;
    }
}