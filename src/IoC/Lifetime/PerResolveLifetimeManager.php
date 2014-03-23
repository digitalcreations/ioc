<?php

namespace DC\IoC\Lifetime;

/**
 * Will call create() on the registration anytime a resolve is attempted.
 *
 * @package DC\IoC
 */
class PerResolveLifetimeManager extends LifetimeManager {

    function resolve()
    {
        return $this->registration->create();
    }
}