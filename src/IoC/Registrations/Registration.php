<?php

namespace DC\IoC\Registrations;

abstract class Registration implements IRegistrationLookup {
    /**
     * @var Container
     */
    protected $container;

    protected $boundAs;
    /**
     * @var ILifetimeManager
     */
    protected $lifetimeManager;

    protected function __construct($boundAs, \Dc\IoC\Container $container)
    {
        $this->container = $container;
        $this->setBoundAs($boundAs);
    }

    protected function setBoundAs($boundAs) {
        if ($boundAs != null && !class_exists($boundAs) && !interface_exists($boundAs))
        {
            throw new \DC\IoC\Exceptions\InvalidClassOrInterfaceNameException($boundAs);
        }
        $this->boundAs = $boundAs;
    }

    public abstract function create();

    function to($classOrInterfaceName)
    {
        $this->setBoundAs($classOrInterfaceName);
        return $this;
    }

    function withSingletonLifetime()
    {
        $this->lifetimeManager = \DC\IoC\Lifetime\SingletonLifetimeManagerFactory::getForKey($this->boundAs, $this);
        return $this;
    }

    function withPerResolveLifetime()
    {
        $this->lifetimeManager = new \DC\IoC\Lifetime\PerResolveLifetimeManager($this);
        return $this;
    }

    function withContainerLifetime()
    {
        $this->lifetimeManager = $this->container->getContainerLifetimeManagerForKey($this->boundAs, $this);
        return $this;
    }

    function canResolve($classOrInterfaceName)
    {
        return $this->boundAs == $classOrInterfaceName;
    }

    function resolve() {
        return $this->lifetimeManager->resolve();
    }
}