<?php

namespace DC\IoC;

class FunctionInjector extends InjectorBase {
    public function run(callable $function) {
        $reflectionFunction = new \ReflectionFunction($function);
        $reflectionParameters = $reflectionFunction->getParameters();
        $arguments = array();
        foreach ($reflectionParameters as $reflectionParameter) {
            $parameterClass = $reflectionParameter->getClass();
            if ($parameterClass == null) {
                $name = $this->getParameterClassFromPhpDoc($reflectionFunction, $reflectionParameter->getName());
            } else {
                $name = $parameterClass->getName();
            }
            if ($name == null) {
                throw new \InvalidArgumentException("Could not determine type for property $reflectionParameter->getName() while resolving $className");
            }

            if (preg_match('/\[\]$/', $name)) {
                $dependency = $this->container->resolveAll(substr($name, 0, count($name)-3));
            } else {
                $dependency = $this->container->resolve($name);
            }
            $arguments[] = $dependency;
        }
        return call_user_func_array($function, $arguments);
    }
}