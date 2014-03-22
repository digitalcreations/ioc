<?php

namespace DC\IoC;

/**
 * Exposes operations meant for registering objects with the container.
 *
 * @package DC\IoC
 */
interface IRegistration {
    /**
     * What should the object be bound to?
     *
     * Any request for this exact class or interface name will be resolved.
     *
     * @param $classOrInterfaceName string
     * @return IRegistration
     */
    function to($classOrInterfaceName);

    /**
     * Produce the same instance of the object throughout the script's lifetime.
     *
     * @return IRegistration
     */
    function withSingletonLifetime();

    /**
     * Create a new instance every time.
     *
     * @return IRegistration
     */
    function withPerResolveLifetime();

    /**
     * Produce the same instance of the object throughout the container's lifetime.
     *
     * @return IRegistration
     */
    function withContainerLifetime();
}