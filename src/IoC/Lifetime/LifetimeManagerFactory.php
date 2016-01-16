<?php

namespace DC\IoC\Lifetime;


class LifetimeManagerFactory
{
    /**
     * @var \DC\IoC\Injection\IPropertyInjector
     */
    private $propertyInjector;

    public function __construct(\DC\IoC\Injection\IPropertyInjector $propertyInjector)
    {
        $this->propertyInjector = $propertyInjector;
    }

    private $registrations = [];
    private static $singletonRegistrations = [];

    /**
     * @param $registry
     * @param $classOrInterfaceName
     * @param \DC\IoC\Registrations\IRegistrationLookup $registration
     * @return ILifetimeManager
     */
    private function getLifetimeManagerForKey(&$registry, $classOrInterfaceName, \DC\IoC\Registrations\IRegistrationLookup $registration) {
        if (!isset($registry[$classOrInterfaceName])) {
            $registry[$classOrInterfaceName] = new ExtendedLifetimeManager($registration, $this->propertyInjector);
        }
        return $registry[$classOrInterfaceName];
    }

    public function getPerResolveManager(\DC\IoC\Registrations\IRegistrationLookup $registration) {
        return new PerResolveLifetimeManager($registration, $this->propertyInjector);
    }

    public function getSingletonManager($classOrInterfaceName, \DC\IoC\Registrations\IRegistrationLookup $registration) {
        return $this->getLifetimeManagerForKey(self::$singletonRegistrations, $classOrInterfaceName, $registration);
    }

    public function getContainerManager($classOrInterfaceName, \DC\IoC\Registrations\IRegistrationLookup $registration) {
        return $this->getLifetimeManagerForKey($this->registrations, $classOrInterfaceName, $registration);
    }
}