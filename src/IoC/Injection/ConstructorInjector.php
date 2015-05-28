<?php

namespace DC\IoC\Injection;

class ConstructorInjector extends InjectorBase implements IConstructorInjector {
    public function construct($className, array $parameters = []) {
        $reflectionClass = new \ReflectionClass($className);
        $reflectionConstructor = $reflectionClass->getConstructor();
        if ($reflectionConstructor != null) {
            $reflectionParameters = $reflectionConstructor->getParameters();

            if (count($reflectionParameters) > 0) {
                $arguments = array();
                foreach ($reflectionParameters as $reflectionParameter) {
                    $parameterClass = $reflectionParameter->getClass();
                    $parameterName = $reflectionParameter->getName();

                    if (isset($parameters[$parameterName])) {
                        $arguments[] = $parameters[$parameterName];
                        continue;
                    }

                    if ($parameterClass == null) {
                        $type = $this->getParameterClassFromPhpDoc($reflectionConstructor, $reflectionParameter->getName());
                    } else {
                        $type = '\\'.$parameterClass->getName();
                    }
                    if ($type == null) {
                        throw new \DC\IoC\Exceptions\InjectorException($className, $reflectionParameter->getName(), $type);
                    }
                    if (preg_match('/\[\]$/', $type)) {
                        $dependency = $this->container->resolveAll(substr($type, 0, count($type)-3));
                    } else {
                        $dependency = $this->container->resolve($type);
                    }
                    $arguments[] = $dependency;
                }
                return $reflectionClass->newInstanceArgs($arguments);
            }
        }
        return new $className();
    }
}