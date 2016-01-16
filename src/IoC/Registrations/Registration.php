<?php

namespace DC\IoC\Registrations;

abstract class Registration implements IRegistrationLookup {
    protected $boundAs;
    /**
     * @var \DC\IoC\Lifetime\ILifetimeManager
     */
    protected $lifetimeManager;
    /**
     * @var \DC\IoC\Lifetime\LifetimeManagerFactory
     */
    private $lifetimeManagerFactory;

    protected function __construct($boundAs,
                                   \DC\IoC\Lifetime\LifetimeManagerFactory $lifetimeManagerFactory = null)
    {
        $this->setBoundAs($boundAs);
        $this->lifetimeManagerFactory = $lifetimeManagerFactory;
    }

    protected function setBoundAs($boundAs) {
        if ($boundAs != null && !class_exists($boundAs) && !interface_exists($boundAs))
        {
            throw new \DC\IoC\Exceptions\InvalidClassOrInterfaceNameException($boundAs);
        }
        $this->boundAs = $boundAs;
    }

    protected function getLifetimeManagerKey() {
        return $this->boundAs;
    }

    public abstract function create();

    function getServiceType()
    {
        return $this->boundAs;
    }

    function to($classOrInterfaceName)
    {
        $this->setBoundAs($classOrInterfaceName);
        return $this;
    }

    function withSingletonLifetime()
    {
        $this->lifetimeManager = $this->lifetimeManagerFactory->getSingletonManager($this->getLifetimeManagerKey(), $this);
        return $this;
    }

    function withPerResolveLifetime()
    {
        $this->lifetimeManager = $this->lifetimeManagerFactory->getPerResolveManager($this);
        return $this;
    }

    function withContainerLifetime()
    {
        $this->lifetimeManager = $this->lifetimeManagerFactory->getContainerManager($this->getLifetimeManagerKey(), $this);
        return $this;
    }

    function resolve() {
        return $this->lifetimeManager->resolve();
    }
}