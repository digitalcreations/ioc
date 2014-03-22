<?php

namespace DC\IoC;

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
        }
        return $this->instance;
    }
}