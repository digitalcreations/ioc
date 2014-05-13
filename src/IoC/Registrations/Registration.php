<?php

namespace DC\IoC\Registrations;

use DC\IoC\Lifetime\PerResolveLifetimeManager;

abstract class Registration implements IRegistrationLookup {
    protected $boundAs;
    /**
     * @var ILifetimeManager
     */
    protected $lifetimeManager;
    /**
     * @var \DC\IoC\Lifetime\IExtendedLifetimeManagerFactory
     */
    private $containerLifetimeManagerFactory;
    /**
     * @var \DC\IoC\Lifetime\IExtendedLifetimeManagerFactory
     */
    private $singletonLifetimeManagerFactory;

    protected function __construct($boundAs,
                                   \DC\IoC\Lifetime\IExtendedLifetimeManagerFactory $containerLifetimeManagerFactory,
                                   \DC\IoC\Lifetime\IExtendedLifetimeManagerFactory $singletonLifetimeManagerFactory)
    {
        $this->containerLifetimeManagerFactory = $containerLifetimeManagerFactory;
        $this->singletonLifetimeManagerFactory = $singletonLifetimeManagerFactory;
        $this->setBoundAs($boundAs);
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

    function to($classOrInterfaceName)
    {
        $this->setBoundAs($classOrInterfaceName);
        return $this;
    }

    function withSingletonLifetime()
    {
        $this->lifetimeManager = $this->singletonLifetimeManagerFactory->getLifetimeManagerForKey($this->getLifetimeManagerKey(), $this);
        return $this;
    }

    function withPerResolveLifetime()
    {
        $this->lifetimeManager = new PerResolveLifetimeManager($this);
        return $this;
    }

    function withContainerLifetime()
    {
        $this->lifetimeManager = $this->containerLifetimeManagerFactory->getLifetimeManagerForKey($this->getLifetimeManagerKey(), $this);
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