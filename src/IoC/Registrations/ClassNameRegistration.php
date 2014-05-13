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

    function __construct($className,
                         \DC\IoC\Lifetime\IExtendedLifetimeManagerFactory $containerLifetimeManagerFactory,
                         \DC\IoC\Lifetime\IExtendedLifetimeManagerFactory $singletonLifetimeManagerFactory,
                         \DC\IoC\Injection\IConstructorInjector $constructorInjector)
    {
        $this->className = $className;
        parent::__construct($className, $containerLifetimeManagerFactory, $singletonLifetimeManagerFactory);
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
        return $this->constructorInjector->construct($this->className);
    }
}