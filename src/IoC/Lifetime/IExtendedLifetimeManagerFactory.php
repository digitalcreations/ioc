<?php
namespace DC\IoC\Lifetime;

interface IExtendedLifetimeManagerFactory {
    function getLifetimeManagerForKey($classOrInterfaceName, \DC\IoC\Registrations\IRegistrationLookup $registration);
} 