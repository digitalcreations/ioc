<?php

namespace DC\IoC;

class InstanceRegistration extends Registration {

    private $instance;

    function __construct($instance, Container $container)
    {
        $this->instance = $instance;
        parent::__construct(get_class($instance), $container);
        parent::withPerResolveLifetime();
    }

    function to($className)
    {
        if (!is_subclass_of($this->instance, $className)) {
            throw new \InvalidArgumentException("Registered object does not implement or extend $className");
        }
        return parent::to($className);
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