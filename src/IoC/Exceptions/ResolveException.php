<?php
/**
 * Created by PhpStorm.
 * User: Vegard
 * Date: 3/22/14
 * Time: 3:21 PM
 */

namespace DC\IoC\Exceptions;

class ResolveException extends \Exception {
    function __construct($message, $code = 0, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
} 