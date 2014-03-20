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
        $injector = new FunctionInjector($this->container);
        return $injector->run($this->factory);
    }
}