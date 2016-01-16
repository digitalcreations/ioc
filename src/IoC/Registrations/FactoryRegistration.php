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

    /**
     * @var array
     */
    private $parameters = [];

    function __construct(callable $factory,
                         \DC\IoC\Lifetime\LifetimeManagerFactory $lifetimeManagerFactory,
                         \DC\IoC\Injection\IFunctionInjector $functionInjector)
    {
        $this->factory = $factory;
        parent::__construct(null, $lifetimeManagerFactory);
        $this->withPerResolveLifetime();
        $this->functionInjector = $functionInjector;
    }

    protected function getLifetimeManagerKey()
    {
        return $this->boundAs.'-'.spl_object_hash($this->factory);
    }

    function create()
    {
        return $this->functionInjector->run($this->factory, $this->parameters);
    }

    /**
     * @inheritdoc
     */
    function withParameters(array $parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }
}