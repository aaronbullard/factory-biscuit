<?php

namespace FactoryBiscuit;

interface ManagerRegistry
{
    public function getManagerForClass(string $class): Repository;
}