<?php

namespace DC\IoC;

class ConstructorInjector {
    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container) {

        $this->container = $container;
    }

    public function Construct($className) {
        $reflectionClass = new \ReflectionClass($className);
        $reflectionConstructor = $reflectionClass->getConstructor();
        if ($reflectionConstructor != null) {
            $reflectionParameters = $reflectionConstructor->getParameters();

            if (count($reflectionParameters) > 0) {
                $arguments = array();
                foreach ($reflectionParameters as $oReflectionParameter) {
                    $name = $oReflectionParameter->getClass()->getName();
                    if (strpos($name, '\\') !== false) {
                        $name = '\\'.$name;
                    }
                    $dependency = $this->container->resolve($name);
                    $arguments[] = $dependency;
                }
                return $reflectionClass->newInstanceArgs($arguments);
            }
        }
        return new $className();
    }
}