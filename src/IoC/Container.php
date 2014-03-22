<?php

namespace DC\IoC;

/**
 * Simple IoC container.
 *
  * @package DC\IoC
 */
class Container {
    /**
     * @var IRegistrationLookup[]
     */
    private $registry = array();
    /**
     * @var ExtendedLifetimeManager[] Life time managers for items with container lifetime.
     */
    private $lifetimeRegistrations = array();

    private function addRegistration(Registration $registration) {
        $this->registry[] = $registration;
    }

    public function normalizeClassName($classOrInterfaceName) {
        if (strpos($classOrInterfaceName, '\\') !== false && $classOrInterfaceName[0] !== '\\') {
            $classOrInterfaceName = '\\' . $classOrInterfaceName;
        }
        return $classOrInterfaceName;
    }

    /**
     * @param $classOrInterfaceName The class or interface name to find registrations for.
     * @return IRegistration[] Matching registrations
     */
    private function findRegistrations($classOrInterfaceName) {
        $classOrInterfaceName = $this->normalizeClassName($classOrInterfaceName);
        return array_values(array_filter($this->registry, function($registration) use ($classOrInterfaceName) {
            return $registration->CanResolve($classOrInterfaceName);
        }));
    }

    /**
     * @param $key string The binding key.
     * @param IRegistrationLookup $registration
     * @return ContainerLifetimeManager
     */
    public function getContainerLifetimeManagerForKey($key, IRegistrationLookup $registration) {
        if (!isset($this->lifetimeRegistrations[$key])) {
            $this->lifetimeRegistrations[$key] = new ExtendedLifetimeManager($registration);
        }
        return $this->lifetimeRegistrations[$key];
    }

    /**
     * Register a class, object or factory function for resolving.
     *
     * @param $o string|object|callable
     * @throws InvalidArgumentException
     * @return IRegistration
     */
    public function register($o)
    {
        if (is_callable($o)) {
            $registration = new FactoryRegistration($o, $this);
        } elseif (is_string($o) && class_exists($o)) {
            $registration = new ClassNameRegistration($o, $this);
        } elseif (is_object($o)) {
            $registration = new InstanceRegistration($o, $this);
        } else if (is_string($o) && strpos($o, '\\') === false) {
            throw new \InvalidArgumentException("Could not find class name $o. Did you mean to use a fully qualified namespace?");
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
     * @return object
     * @throws \InvalidArgumentException When multiple registrations were found, or no registrations where found.
     */
    public function resolve($classOrInterfaceName)
    {
        $registrations = $this->findRegistrations($classOrInterfaceName);
        if (count($registrations) == 1) {
            return $registrations[0]->Resolve();
        } else if (count($registrations) > 1) {
            throw new \InvalidArgumentException("More than 1 registration found for $classOrInterfaceName");
        } else if (class_exists($classOrInterfaceName)) {
            $injector = new ConstructorInjector($this);
            return $injector->construct($classOrInterfaceName);
        } else {
           throw new \InvalidArgumentException("Registration not found for $classOrInterfaceName");
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