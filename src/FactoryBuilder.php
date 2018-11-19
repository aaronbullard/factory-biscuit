<?php

namespace FactoryBiscuit;

use RuntimeException;
use Faker\Generator as Faker;

class FactoryBuilder
{
    /**
     * Class to create
     *
     * @var string
     */
    protected $class;

    /**
     * Callable that returns key/value attributes
     *
     * @var Callable
     */
    protected $template;

    /**
     * Factory class where other templates are defined for recursive dependencies
     *
     * @var Factory
     */
    protected $factory;

    /**
     * Faker Generator Library
     *
     * @var Faker
     */
    protected $faker;

    /**
     * Number of instances to create
     *
     * @var integer
     */
    protected $times = 1;

    /**
     * Doctrine Manager Registry for persistence.
     * 
     * @var ManagerRegistry
     */
    protected $registry;

    public function __construct(
        $class,
        callable $template,
        Factory $factory,
        Faker $faker,
        ManagerRegistry $registry
    ) {
        $this->class = $class;
        $this->template = $template;
        $this->factory = $factory;
        $this->faker = $faker;
        $this->registry = $registry;
    }

    /**
     * Set the number of instances to create
     *
     * @param integer $number
     * @return FactoryBuilder
     */
    public function times(int $number): FactoryBuilder
    {
        $this->times = $number;

        return $this;
    }

    /**
     * Build the instance with the given attributes to override the default faker values
     *
     * @param array $attributes
     * @return mixed
     */
    protected function build(array $attributes = [])
    {
        $template = $this->template;

        $defaultAttributes = $template($this->faker, $this->factory);

        $data = array_merge($defaultAttributes, $attributes);

        // Execute recursive nested
        $data = array_map(function($prop){
            return is_callable($prop) ? $prop() : $prop;
        }, $data);

        return Hydrator::newInstance($this->class, $data);
    }

    /**
     * Build the instance with the given attributes to override the default faker values.
     * These are not persisted to the database.
     *
     * @param array $attributes
     * @return mixed
     */
    public function make(array $attributes = [])
    {
        if($this->times === 1){
            return $this->build($attributes);
        }

        return array_map(function($i) use ($attributes){
            return $this->build($attributes);
        }, range(1, $this->times));
    }

    /**
     * Build the instance with the given attributes to override the default faker values.
     * These are persisted to the database.
     *
     * @param array $attributes
     * @return mixed
     */
    public function create(array $attributes = [])
    {
        $models = $this->make($attributes);

        if ($this->times === 1) {
            $models = [$models];
        }

        $em = $this->registry->getManagerForClass($this->class);
        if ($em == NULL) {
            throw new RuntimeException(\sprintf('Unable to find entity manager for model: %s', $this->class));
        }

        foreach ($models as $model) {
            $em->save($model);
        }

        return count($models) == 1 ? reset($models) : $models;
    }
}