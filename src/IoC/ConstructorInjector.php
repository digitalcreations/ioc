<?php

namespace DC\IoC;

class ConstructorInjector extends InjectorBase {
    public function construct($className) {
        $reflectionClass = new \ReflectionClass($className);
        $reflectionConstructor = $reflectionClass->getConstructor();
        if ($reflectionConstructor != null) {
            $reflectionParameters = $reflectionConstructor->getParameters();

            if (count($reflectionParameters) > 0) {
                $arguments = array();
                foreach ($reflectionParameters as $reflectionParameter) {
                    $parameterClass = $reflectionParameter->getClass();
                    if ($parameterClass == null) {
                        $name = $this->getParameterFromPhpDoc($reflectionConstructor, $reflectionParameter->getName());
                    } else {
                        $name = $parameterClass->getName();
                    }
                    if ($name == null) {
                        throw new \InvalidArgumentException("Could not determine type for property $reflectionParameter->getName() while resolving $className");
                    }
                    $name = $this->container->normalizeClassName($name);
                    if (preg_match('/\[\]$/', $name)) {
                        $dependency = $this->container->resolveAll(substr($name, 0, count($name)-3));
                    } else {
                        $dependency = $this->container->resolve($name);
                    }
                    $arguments[] = $dependency;
                }
                return $reflectionClass->newInstanceArgs($arguments);
            }
        }
        return new $className();
    }
}