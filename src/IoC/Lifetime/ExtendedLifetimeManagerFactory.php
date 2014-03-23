<?php

namespace DC\IoC\Lifetime;

/**
 * Factory to produce a singleton ExtendedLifetimeManager
 *
 * @package DC\IoC\Lifetime
 */
class ExtendedLifetimeManagerFactory implements IExtendedLifetimeManagerFactory {

    private $registrations = array();

    /**
     * @param $classOrInterfaceName
     * @param \DC\IoC\Registrations\IRegistrationLookup $registration
     * @return \DC\IoC\Lifetime\ILifetimeManager
     */
    public function getLifetimeManagerForKey($classOrInterfaceName, \DC\IoC\Registrations\IRegistrationLookup $registration) {
        if (!isset($this->registrations[$classOrInterfaceName])) {
            $this->registrations[$classOrInterfaceName] = new ExtendedLifetimeManager($registration);
        }
        return $this->registrations[$classOrInterfaceName];
    }
}