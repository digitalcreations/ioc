<?php

namespace DC\IoC\Lifetime;

abstract class LifetimeManager implements ILifetimeManager {
    /**
     * @var \DC\IoC\Registrations\IRegistrationLookup
     */
    protected $registration;
    /**
     * @var \DC\IoC\Injection\IPropertyInjector
     */
    protected $propertyInjector;

    function __construct(\DC\IoC\Registrations\IRegistrationLookup $registration, \DC\IoC\Injection\IPropertyInjector $propertyInjector = null)
    {
        $this->registration = $registration;
        $this->propertyInjector = $propertyInjector;
    }

    abstract function resolve();
}