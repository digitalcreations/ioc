<?php

namespace DC\IoC\Exceptions;


class InjectorException extends ResolveException {
    function __construct($classOrInterfaceName, $propertyName, $propertyType, \Exception $innerException = null)
    {
        parent::__construct("Could not resolve property $propertyName of type $propertyType while resolving $classOrInterfaceName", 0, $innerException);
    }
} 