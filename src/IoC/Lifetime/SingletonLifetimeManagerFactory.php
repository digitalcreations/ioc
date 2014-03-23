<?php

namespace DC\IoC\Lifetime;

/**
 * Factory to produce a singleton ExtendedLifetimeManager
 *
 * @package DC\IoC
 */
class SingletonLifetimeManagerFactory {

    private static $registrations = array();

    /**
     * @param $key
     * @param IRegistrationLookup $registration
     * @return \DC\IoC\Lifetime\ILifetimeManager
     */
    public static function getForKey($key, \DC\IoC\Registrations\IRegistrationLookup $registration) {
        if (!isset(self::$registrations[$key])) {
            self::$registrations[$key] = new ExtendedLifetimeManager($registration);
        }
        return self::$registrations[$key];
    }
}