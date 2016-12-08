<?php

namespace DC\IoC\Modules;

/**
 * Module that can be registered with the IoC container with dependency support.
 *
 * @package DC\IoC\Modules
 */
abstract class Module
{
    private $name;
    /**
     * @var array
     */
    private $dependencies;

    protected function __construct($name, array $dependencies = null)
    {
        $this->name = $name;
        $this->dependencies = $dependencies;
    }

    /**
     * @return string
     */
    function getName() { return $this->name; }

    /**
     * @return string[]
     */
    function getDependencies() { return $this->dependencies ?? []; }

    /**
     * @param \DC\IoC\Container $container
     * @return null
     */
    abstract function register(\DC\IoC\Container $container);
}