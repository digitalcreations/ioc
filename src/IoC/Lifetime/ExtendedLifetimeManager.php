<?php

namespace DC\IoC\Lifetime;

/**
 * Lifetime manager that produces the same instance throughout its lifetime.
 *
 * @package DC\IoC
 */
class ExtendedLifetimeManager extends LifetimeManager {
    private $instance;
    function resolve()
    {
        if ($this->instance == null) {
            $this->instance = $this->registration->create();
            $this->propertyInjector->inject($this->instance);
        }
        return $this->instance;
    }
}