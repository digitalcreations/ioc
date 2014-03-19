<?php

namespace DC\IoC;

abstract class LifetimeManager implements ILifetimeManager {
    protected $registration;

    function __construct(IRegistrationLookup $registration)
    {
        $this->registration = $registration;
    }

    abstract function resolve();
}