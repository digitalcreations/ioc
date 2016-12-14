<?php

namespace DC\IoC\Registrations;
use SebastianBergmann\Exporter\Exception;

/**
 * Registers a single instance.
 *
 * @package DC\IoC
 */
class InstanceRegistration extends Registration {

    private $instance;

    function __construct($instance)
    {
        $this->instance = $instance;
        parent::__construct('\\'.get_class($instance), null);
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
        throw new Exception("Call to create() on instance registration. This method should never be called.");
    }

    function withPerResolveLifetime()
    {
        throw new \InvalidArgumentException("Instance registrations do not have a lifetime");
    }

    function withSingletonLifetime()
    {
        throw new \InvalidArgumentException("Instance registrations do not have a lifetime");
    }

    function withContainerLifetime()
    {
        throw new \InvalidArgumentException("Instance registrations do not have a lifetime");
    }

    /**
     * @inheritdoc
     */
    function withParameters(array $parameters)
    {
        throw new \InvalidArgumentException("Instance registrations cannot take parameters.");
    }

    function resolve()
    {
        return $this->instance;
    }
}