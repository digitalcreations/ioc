<?php

namespace DC\IoC\Lifetime;

abstract class LifetimeManager implements ILifetimeManager {
    protected $registration;

    function __construct(\DC\IoC\Registrations\IRegistrationLookup $registration)
    {
        $this->registration = $registration;
    }

    abstract function resolve();
}