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
        $object = $this->registration->create();
//        $this->propertyInjector->inject($object);
        return $object;
    }
}