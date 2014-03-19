<?php

namespace DC\IoC;

class ClassNameRegistration extends Registration {

    /**
     * @var string
     */
    private $className;

    function __construct($className, Container $container)
    {
        $this->className = $className;
        parent::__construct($className, $container);
        $this->withPerResolveLifetime();
    }

    function to($className)
    {
        if (!is_subclass_of($this->className, $className)) {
            throw new \InvalidArgumentException("$this->className does not implement or extend $className");
        }
        return parent::to($className);
    }

    function create()
    {
        $oInjector = new ConstructorInjector($this->container);
        return $oInjector->Construct($this->className);
    }
}