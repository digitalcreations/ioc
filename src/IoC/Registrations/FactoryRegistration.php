<?php

namespace DC\IoC\Registrations;

class FactoryRegistration extends Registration {

    /**
     * @var callable
     */
    private $factory;

    function __construct(callable $factory, \DC\IoC\Container $container)
    {
        $this->factory = $factory;
        parent::__construct(null, $container);
        $this->withPerResolveLifetime();
    }

    function create()
    {
        $injector = new \DC\IoC\Injection\FunctionInjector($this->container);
        return $injector->run($this->factory);
    }
}