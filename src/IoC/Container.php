<?php

namespace DC\IoC;
use DC\IoC\Injection\ConstructorInjector;
use DC\IoC\Injection\FunctionInjector;

/**
 * Simple IoC container.
 *
  * @package DC\IoC
 */
class Container {
    /**
     * @var Registrations\IRegistrationLookup[]
     */
    private $registry = array();
    /**
     * @var Lifetime\IExtendedLifetimeManagerFactory
     */
    private $containerLifetimeManager;
    /**
     * @var Lifetime\IExtendedLifetimeManagerFactory
     */
    private static $singletonLifetimeManager;

    function __construct()
    {
        $this->containerLifetimeManager = new Lifetime\ExtendedLifetimeManagerFactory();
        if (self::$singletonLifetimeManager == null)
        {
            self::$singletonLifetimeManager = new Lifetime\ExtendedLifetimeManagerFactory();
        }
    }

    private function addRegistration(Registrations\Registration $registration) {
        $this->registry[] = $registration;
    }

    /**
     * @param $classOrInterfaceName The class or interface name to find registrations for.
     * @return Registrations\IRegistration[] Matching registrations
     */
    private function findRegistrations($classOrInterfaceName) {
        return array_values(array_filter($this->registry, function($registration) use ($classOrInterfaceName) {
            return $registration->CanResolve($classOrInterfaceName);
        }));
    }

    /**
     * Register a class, object or factory function for resolving.
     *
     * @param $o string|object|callable The class name, object or factory function to register.
     * @throws \DC\IoC\Exceptions\InvalidClassOrInterfaceNameException When the class name cannot be found (or is not fully qualified).
     * @throws \InvalidArgumentException When the passed object isn't suitable for registration (e.g. passing an array)
     * @return Registrations\IRegistration
     */
    public function register($o)
    {
        if (is_callable($o)) {
            $registration = new Registrations\FactoryRegistration($o, $this->containerLifetimeManager, self::$singletonLifetimeManager, new FunctionInjector($this));
        } elseif (is_string($o) && class_exists($o)) {
            $registration = new Registrations\ClassNameRegistration($o, $this->containerLifetimeManager, self::$singletonLifetimeManager, new ConstructorInjector($this));
        } elseif (is_object($o)) {
            $registration = new Registrations\InstanceRegistration($o, $this->containerLifetimeManager, self::$singletonLifetimeManager);
        } else if (is_string($o) && strpos($o, '\\') === false) {
            throw new Exceptions\InvalidClassOrInterfaceNameException($o);
        } else {
            throw new \InvalidArgumentException("Cannot register $o.");
        }

        $this->addRegistration($registration);
        return $registration;
    }

    /**
     * Resolve a single instance of the class or interface name.
     *
     * This can also look up classes that haven't been registered, but all its dependencies are available via
     * constructor injection.
     *
     * @param $classOrInterfaceName string The class or interface name you want to resolve.
     * @throws Exceptions\MultipleRegistrationsFoundException When multiple registrations were found.
     * @throws Exceptions\CannotResolveException When no registrations were found.
     * @return object
     */
    public function resolve($classOrInterfaceName)
    {
        $registrations = $this->findRegistrations($classOrInterfaceName);
        if (count($registrations) == 1) {
            return $registrations[0]->Resolve();
        } else if (count($registrations) > 1) {
            throw new Exceptions\MultipleRegistrationsFoundException($classOrInterfaceName);
        } else if (class_exists($classOrInterfaceName)) {
            $injector = new Injection\ConstructorInjector($this);
            return $injector->construct($classOrInterfaceName);
        } else {
           throw new Exceptions\CannotResolveException($classOrInterfaceName);
        }
    }

    /**
     * Resolve all the instances of a registered class or interface.
     *
     * @param $classOrInterfaceName The class or interface to resolve
     * @return array List of all objects that could be resolved.
     */
    public function resolveAll($classOrInterfaceName)
    {
        return array_map(function($registration) {
            return $registration->Resolve();
        }, $this->findRegistrations($classOrInterfaceName));
    }
}