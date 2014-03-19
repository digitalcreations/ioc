<?php

namespace DC\IoC;

abstract class Registration implements IRegistration, IRegistrationLookup {
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

    function to($className)
    {
        $this->boundAs = $this->container->normalizeClassName($className);
        return $this;
    }

    function toSelf()
    {
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

    function canResolve($className)
    {
        return $this->boundAs == $className;
    }

    function resolve() {
        return $this->lifetimeManager->resolve();
    }
}