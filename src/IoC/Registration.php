<?php

namespace DC\IoC;

abstract class Registration implements IRegistration {
    /**
     * @var Container
     */
    protected $container;

    protected $boundAs;
    /**
     * @var ILifetimeManager
     */
    protected $lifetimeManager;

    protected function __construct($boundAs, Container $container)
    {
        $this->boundAs = $container->normalizeClassName($boundAs);
        $this->container = $container;
    }

    public abstract function create();

    function to($classOrInterfaceName)
    {
        $this->boundAs = $this->container->normalizeClassName($classOrInterfaceName);
        return $this;
    }

    function withSingletonLifetime()
    {
        $this->lifetimeManager = SingletonLifetimeManagerFactory::getForKey($this->boundAs, $this);
        return $this;
    }

    function withPerResolveLifetime()
    {
        $this->lifetimeManager = new PerResolveLifetimeManager($this);
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