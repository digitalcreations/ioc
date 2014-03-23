<?php

namespace DC\IoC\Exceptions;

class MultipleRegistrationsFoundException extends ResolveException {
    function __construct($classOrInterfaceName) {
        parent::__construct("Multiple registrations were found for $classOrInterfaceName");
    }
}