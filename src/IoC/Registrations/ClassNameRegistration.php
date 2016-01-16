<?php

namespace DC\IoC\Registrations;

class ClassNameRegistration extends Registration {

    /**
     * @var string
     */
    private $className;
    /**
     * @var \DC\IoC\Injection\ConstructorInjector
     */
    private $constructorInjector;

    /**
     * @var array
     */
    private $parameters = [];

    function __construct($className,
                         \DC\IoC\Lifetime\LifetimeManagerFactory $lifetimeManagerFactory,
                         \DC\IoC\Injection\IConstructorInjector $constructorInjector)
    {
        $this->className = $className;
        parent::__construct($className, $lifetimeManagerFactory);
        $this->withPerResolveLifetime();
        $this->constructorInjector = $constructorInjector;
    }

    protected function getLifetimeManagerKey()
    {
        return $this->boundAs.'-'.$this->className;
    }

    function to($classOrInterfaceName)
    {
        if (!is_subclass_of($this->className, $classOrInterfaceName)) {
            throw new \InvalidArgumentException("$this->className does not implement or extend $classOrInterfaceName");
        }
        return parent::to($classOrInterfaceName);
    }

    function create()
    {
        return $this->constructorInjector->construct($this->parameters);
    }

    /**
     * @inheritdoc
     */
    function withParameters(array $parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }
}