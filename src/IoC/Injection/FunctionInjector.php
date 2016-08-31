<?php

namespace DC\IoC\Injection;

use DC\IoC\Exceptions\CannotResolveException;

class FunctionInjector extends InjectorBase implements IFunctionInjector {
    public function run(callable $function, array $parameters = []) {
        $reflectionFunction = new \ReflectionFunction($function);
        $reflectionParameters = $reflectionFunction->getParameters();
        $arguments = array();
        foreach ($reflectionParameters as $reflectionParameter) {
            $parameterClass = $reflectionParameter->getClass();
            $parameterName = $reflectionParameter->getName();

            if (isset($parameters[$parameterName])) {
                $arguments[] = $parameters[$parameterName];
                continue;
            }

            if ($parameterClass == null) {
                $type = $this->getParameterClassFromPhpDoc($reflectionFunction, $reflectionParameter->getName());
            } else {
                $type = '\\'.$parameterClass->getName();
            }
            if ($type == null) {
                if ($reflectionParameter->isDefaultValueAvailable()) {
                    $arguments[] = $reflectionParameter->getDefaultValue();
                    continue;
                }

                throw new \DC\IoC\Exceptions\InjectorException("function", $reflectionParameter->getName(), $type);
            }

            if (preg_match('/\[\]$/', $type)) {
                $dependency = $this->container->resolveAll(substr($type, 0, count($type)-3));
            } else {
                try {
                    $dependency = $this->container->resolve($type, $reflectionFunction->__toString());
                }
                catch (CannotResolveException $e) {
                    if ($reflectionParameter->isDefaultValueAvailable()) {
                        $dependency = $reflectionParameter->getDefaultValue();
                    } else {
                        throw $e;
                    }
                }
            }
            $arguments[] = $dependency;
        }
        return call_user_func_array($function, $arguments);
    }
}