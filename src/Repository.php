<?php

namespace FactoryBiscuit;

interface Repository
{
    public function save($model): bool;
}