<?php

namespace FactoryBiscuit;

interface ManagerRegistry
{
    public function getRepositoryForClass(string $class): Repository;
}