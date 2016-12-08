<?php

namespace DC\IoC;

/**
 * Simple IoC container.
 *
  * @package DC\IoC
 */
class Container {
    /**
     * @var Registrations\IRegistrationLookup[]
     */
    private $registry = [];

    /**
     * Array with keys matching the registrations. Can be wiped and rebuilt at any time.
     *
     * @var Registrations\IRegistrationLookup[][]
     */
    private $registryLookup;
    /**
     * @var Injection\IPropertyInjector
     */
    private $propertyInjector;
    /**
     * @var Injection\IFunctionInjector
     */
    private $functionInjector;

    /**
     * @var Lifetime\LifetimeManagerFactory
     */
    private $lifetimeManagerFactory;

    /**
     * @var Modules\DependencyResolver
     */
    private $moduleDependencyResolver;

    /**
     * @var \DC\Cache\ICache
     */
    private $cache;

    function __construct(\DC\Cache\ICache $cache = null)
    {
        if ($cache == null) {
            $cache = new RequestCache();
        }
        $this->cache = $cache;

        $this->propertyInjector = new Injection\PropertyInjector($this, $cache);
        $this->functionInjector = new Injection\FunctionInjector($this, $cache);
        $this->moduleDependencyResolver = new Modules\DependencyResolver();

        $this->lifetimeManagerFactory = new Lifetime\LifetimeManagerFactory($this->propertyInjector);
    }

    private function addRegistration(Registrations\Registration $registration) {
        $this->registry[] = $registration;
        $this->registryLookup = null;
    }

    /**
     * All modules must be registered at once for dependency resolution to work correctly.
     *
     * @param array|\DC\IoC\Modules\Module[] $modules
     */
    public function registerModules(array $modules) {
        $order = $this->moduleDependencyResolver->resolveOrder($modules);
        /**
         * @var \DC\IoC\Modules\Module[]
         */
        $ordered = [];

        foreach ($modules as $module) {
            $ordered[$module->getName()] = $module;
        }

        foreach ($order as $name) {
            $ordered[$name]->register($this);
        }
    }

    /**
     * @param string $classOrInterfaceName The class or interface name to find registrations for.
     * @return Registrations\IRegistration[] Matching registrations
     * @throws Exceptions\CannotResolveException
     */
    private function findRegistrations($classOrInterfaceName) {
        if (!isset($this->registryLookup)) {
            $this->registryLookup = [];
            foreach ($this->registry as $registration) {
                $serviceType = $registration->getServiceType();
                if (!isset($this->registryLookup[$serviceType])) {
                    $this->registryLookup[$serviceType] = [];
                }
                $this->registryLookup[$registration->getServiceType()][] = $registration;
            }
        }
        if (isset($this->registryLookup[$classOrInterfaceName])) {
            return $this->registryLookup[$classOrInterfaceName];
        }
        return [];
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
            $registration = new Registrations\FactoryRegistration($o, $this->lifetimeManagerFactory, new Injection\FunctionInjector($this));
        } elseif (is_string($o) && class_exists($o)) {
            $registration = new Registrations\ClassNameRegistration($o, $this->lifetimeManagerFactory, new Injection\ConstructorInjector($o, $this, $this->cache));
        } elseif (is_object($o)) {
            $registration = new Registrations\InstanceRegistration($o);
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
     * @param string $classOrInterfaceName The class or interface name you want to resolve.
     * @throws Exceptions\MultipleRegistrationsFoundException When multiple registrations were found.
     * @throws Exceptions\CannotResolveException When no registrations were found.
     * @return object
     */
    public function resolve($classOrInterfaceName, $context = null)
    {
        $registrations = $this->findRegistrations($classOrInterfaceName);
        if (count($registrations) == 1) {
            $object = $registrations[0]->resolve();
        } else if (count($registrations) > 1) {
            throw new Exceptions\MultipleRegistrationsFoundException($classOrInterfaceName);
        } else if (class_exists($classOrInterfaceName)) {
            $injector = new Injection\ConstructorInjector($classOrInterfaceName, $this, $this->cache);
            $object = $injector->construct();
        } else {
           throw new Exceptions\CannotResolveException($classOrInterfaceName, $context);
        }
        return $object;
    }

    /**
     * Inject properties into an object or invoke a function with parameters injected.
     *
     * @param $object object|callable The object or function to have its properties/arguments injected
     * @return object Returns the object, or the result of the function
     * @throws Exceptions\InjectorException
     */
    public function inject($object) {

        if (is_callable($object)) {
            return $this->functionInjector->run($object);
        }
        else {
            $this->propertyInjector->inject($object);
            return $object;
        }
    }

    /**
     * Resolve all the instances of a registered class or interface.
     *
     * @param $classOrInterfaceName string The class or interface to resolve
     * @return array List of all objects that could be resolved.
     */
    public function resolveAll($classOrInterfaceName)
    {
        return array_map(function($registration) {
            return $registration->resolve();
        }, $this->findRegistrations($classOrInterfaceName));
    }
}