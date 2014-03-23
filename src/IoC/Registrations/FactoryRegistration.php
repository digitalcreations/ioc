<?php

namespace DC\IoC\Registrations;

class FactoryRegistration extends Registration {

    /**
     * @var callable
     */
    private $factory;
    /**
     * @var \DC\IoC\Injection\FunctionInjector
     */
    private $functionInjector;

    function __construct(callable $factory,
                         \DC\IoC\Lifetime\IExtendedLifetimeManagerFactory $containerLifetimeManagerFactory,
                         \DC\IoC\Lifetime\IExtendedLifetimeManagerFactory $singletonLifetimeManagerFactory,
                         \DC\IoC\Injection\IFunctionInjector $functionInjector)
    {
        $this->factory = $factory;
        parent::__construct(null, $containerLifetimeManagerFactory, $singletonLifetimeManagerFactory);
        $this->withPerResolveLifetime();
        $this->functionInjector = $functionInjector;
    }

    function create()
    {
        return $this->functionInjector->run($this->factory);
    }
}