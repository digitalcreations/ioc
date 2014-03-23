<?php

namespace DC\IoC\Exceptions;


class CannotResolveException extends ResolveException {
    function __construct($classOrInterfaceName) {
        parent::__construct("No registrations were found for $classOrInterfaceName");
    }
} 