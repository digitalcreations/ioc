<?php
/**
 * Created by PhpStorm.
 * User: Vegard
 * Date: 3/20/14
 * Time: 7:07 PM
 */

namespace DC\IoC\Injection;

use DC\IoC\PHPDocHelper;

abstract class InjectorBase {
    /**
     * @var \DC\IoC\Container
     */
    protected $container;
    /**
     * @var \DC\Cache\ICache
     */
    protected $cache;

    public function __construct(\DC\IoC\Container $container, \DC\Cache\ICache $cache = null) {
        $this->container = $container;
        $this->cache = $cache;
    }

    private $parameterTypes;
    protected function getParameterClasses(\ReflectionFunctionAbstract $reflectionMethod) {
        if (!isset($this->parameterTypes)) {
            $this->parameterTypes = \DC\IoC\PHPDocHelper::getDocumentedTypes($reflectionMethod);
        }
        return $this->parameterTypes;
    }

    protected function getParameterClassFromPhpDoc(\ReflectionFunctionAbstract $reflectionMethod, $parameterName) {
        $types = $this->getParameterClasses($reflectionMethod);
        $parameterName = '$' . ltrim($parameterName, '$');
        if (isset($types[$parameterName])) {
            return $types[$parameterName];
        }
        return null;
    }
} 