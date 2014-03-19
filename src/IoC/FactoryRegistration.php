<?php

namespace DC\IoC;

class FactoryRegistration extends Registration {

    /**
     * @var callable
     */
    private $factory;

    function __construct(callable $factory, Container $container)
    {
        $this->factory = $factory;
        parent::__construct(null, $container);
        $this->withPerResolveLifetime();
    }

    function create()
    {
        return call_user_func($this->factory);
    }

    function toSelf()
    {
        throw new \InvalidArgumentException("Callable registrations cannot be bound to self, as their return type is unknown. Specify return type using To().");
    }
}