<?php

namespace FactoryBiscuit;

use Faker\Generator as Faker;

class Factory
{
    /**
     * @var Faker
     */
    protected $faker;

    /**
     * @var callable[]
     */
    protected $definitions = [];

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    public function __construct(Faker $faker, ManagerRegistry $registry)
    {
        $this->faker = $faker;
        $this->registry = $registry;
    }

    /**
     * Path to a php file where multiple factory definitions are defined
     * See TestFactories.php for examples
     *
     * @param string $path Full path to file 
     * @return Factory
     */
    public function load(string $path): Factory
    {
        $factory = $this;

        require $path;

        return $this;
    }

    /**
     * Define the key value pair template for your class
     *
     * @param string $class Class name
     * @param callable $template
     * @param string $name Optional state of the class
     * @return Factory
     */
    public function define(string $class, callable $template, $name = 'default'): Factory
    {
        $this->definitions[$class][$name] = $template;

        return $this;
    }

    /**
     * Define a special state of your class factory
     *
     * @param string $class Class name
     * @param string $name Optional state of the class
     * @param callable $template
     * @return Factory
     */
    public function defineAs(string $class, string $name, callable $template): Factory
    {
        $this->define($class, $template, $name);

        return $this;
    }

    /**
     * Method to create the FactoryBuilder for the class and optional state as specified
     *
     * @param string $class Class name
     * @param string $name Optional state of the class
     * @return FactoryBuilder
     */
    public function of(string $class, string $name = 'default'): FactoryBuilder
    {
        $template = $this->definitions[$class][$name];

        return new FactoryBuilder(
            $class,
            $template,
            $this,
            $this->faker,
            $this->registry
        );
    }
}