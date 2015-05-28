<?php

namespace DC\IoC\Registrations;

/**
 * Registers a single instance.
 *
 * @package DC\IoC
 */
class InstanceRegistration extends Registration {

    private $instance;

    function __construct($instance,
                         \DC\IoC\Lifetime\IExtendedLifetimeManagerFactory $containerLifetimeManagerFactory,
                         \DC\IoC\Lifetime\IExtendedLifetimeManagerFactory $singletonLifetimeManagerFactory)
    {
        $this->instance = $instance;
        parent::__construct('\\'.get_class($instance), $containerLifetimeManagerFactory, $singletonLifetimeManagerFactory);
        parent::withPerResolveLifetime();
    }

    /**
     * @param string $classOrInterfaceName
     * @return IRegistration
     * @throws \InvalidArgumentException
     */
    function to($classOrInterfaceName)
    {
        if (!is_subclass_of($this->instance, $classOrInterfaceName)) {
            throw new \InvalidArgumentException("Registered object does not implement or extend $classOrInterfaceName");
        }
        return parent::to($classOrInterfaceName);
    }

    function create()
    {
        return $this->instance;
    }

    function withPerResolveLifetime()
    {
        throw new \DC\IoC\Exceptions\InvalidArgumentException("Instance registrations do not have a lifetime");
    }

    function withSingletonLifetime()
    {
        throw new \DC\IoC\Exceptions\InvalidArgumentException("Instance registrations do not have a lifetime");
    }

    function withContainerLifetime()
    {
        throw new \DC\IoC\Exceptions\InvalidArgumentException("Instance registrations do not have a lifetime");
    }

    /**
     * @inheritdoc
     */
    function withParameters(array $parameters)
    {
        throw new \DC\IoC\Exceptions\InvalidArgumentException("Instance registrations cannot take parameters.");
    }
}