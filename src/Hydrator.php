<?php

namespace FactoryBiscuit;

use ReflectionClass;
use ReflectionProperty;

class Hydrator
{
    protected $classname;

    public function __construct(string $classname)
    {
        $this->classname = $classname;
    }

    public function hydrate(array $data = [])
    {
        $refl = new ReflectionClass($this->classname);

        $instance = $refl->newInstanceWithoutConstructor();

        return static::mutate($instance, $data);
    }

    public static function newInstance($classname, array $data = [])
    {
        $self = new static($classname);

        return $self->hydrate($data);
    }

    public static function mutate($instance, array $data = [])
    {
        $classname = get_class($instance);

        foreach($data as $key => $value){
            $reflProp = new ReflectionProperty($classname, $key);
            $reflProp->setAccessible(true);
            $reflProp->setValue($instance, $value);
        }

        unset($reflProp);
        unset($refl);

        return $instance;
    }

    public static function extract($instance, array $properties = [])
    {   
        $data = [];
        $classname = get_class($instance);

        if(empty($properties)){
            $properties = static::getProperties($instance);
        }

        foreach($properties as $key){
            $reflProp = new ReflectionProperty($classname, $key);
            $reflProp->setAccessible(true);
            $data[$key] = $reflProp->getValue($instance);
        }

        unset($reflProp);
        unset($refl);
        
        return $data;
    }

    public static function getProperties($instance)
    {
        $refl = new ReflectionClass($instance);

        return array_map(function($reflProperty){
            return $reflProperty->getName();
        }, $refl->getProperties());
    }
}