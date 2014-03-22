<?php

namespace DC\IoC;

/**
 * Describes a registration from the point of view of the container.
 *
 * @package DC\IoC
 */
interface IRegistrationLookup extends IRegistration {
    /**
     * Determine if this registration can resolve a given class or interface.
     *
     * @param $classOrInterfaceName string The class or interface name to resolve
     * @return bool True if this can resolve the $classOrInterfaceName
     */
    function canResolve($classOrInterfaceName);

    /**
     * Produce the instance and return it.
     *
     * @return object Created instance.
     */
    function create();

    /**
     * Resolve the instance by consulting the ILifetimeManager, which decides if it is created or a new one is created.
     *
     * @return object Resolved instance.
     */
    function resolve();
}