<?php

namespace DC\IoC\Exceptions;

class InvalidClassOrInterfaceNameException extends RegistrationException {

    function __construct($sClassOrInterfaceName)
    {
        parent::__construct("Could not find the class or interface named $sClassOrInterfaceName");
    }
}