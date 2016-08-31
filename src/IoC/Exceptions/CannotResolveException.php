<?php

namespace DC\IoC\Exceptions;


class CannotResolveException extends ResolveException {
    function __construct($classOrInterfaceName, $context = null) {
        $message = "No registrations were found for $classOrInterfaceName";
        if ($context != null) {
            $message .= " while resolving $context";
        }
        parent::__construct($message);
    }
} 