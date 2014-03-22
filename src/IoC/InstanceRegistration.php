<?php

namespace DC\IoC;

/**
 * Registers a single instance.
 *
 * @package DC\IoC
 */
class InstanceRegistration extends Registration {

    private $instance;

    function __construct($instance, Container $container)
    {
        $this->instance = $instance;
        parent::__construct(get_class($instance), $container);
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
        throw new InvalidArgumentException("Instance registrations do not have a lifetime");
    }

    function withSingletonLifetime()
    {
        throw new InvalidArgumentException("Instance registrations do not have a lifetime");
    }

    function withContainerLifetime()
    {
        throw new InvalidArgumentException("Instance registrations do not have a lifetime");
    }


}