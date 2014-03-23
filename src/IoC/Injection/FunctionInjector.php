<?php

namespace DC\IoC\Injection;

class FunctionInjector extends InjectorBase implements IFunctionInjector {
    public function run(callable $function) {
        $reflectionFunction = new \ReflectionFunction($function);
        $reflectionParameters = $reflectionFunction->getParameters();
        $arguments = array();
        foreach ($reflectionParameters as $reflectionParameter) {
            $parameterClass = $reflectionParameter->getClass();
            if ($parameterClass == null) {
                $type = $this->getParameterClassFromPhpDoc($reflectionFunction, $reflectionParameter->getName());
            } else {
                $type = '\\'.$parameterClass->getName();
            }
            if ($type == null) {
                throw new \DC\IoC\Exceptions\InjectorException("function", $reflectionParameter->getName(), $type);
            }

            if (preg_match('/\[\]$/', $type)) {
                $dependency = $this->container->resolveAll(substr($type, 0, count($type)-3));
            } else {
                $dependency = $this->container->resolve($type);
            }
            $arguments[] = $dependency;
        }
        return call_user_func_array($function, $arguments);
    }
}