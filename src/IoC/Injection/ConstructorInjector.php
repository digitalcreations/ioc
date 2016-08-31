<?php

namespace DC\IoC\Injection;

use DC\IoC\Exceptions\CannotResolveException;

class ConstructorInjector extends InjectorBase implements IConstructorInjector {
    const PARAMETER = 42;
    /**
     * @var string
     */
    private $className;

    public function __construct($className, \DC\IoC\Container $container, \DC\Cache\ICache $cache = null)
    {
        parent::__construct($container, $cache);

        $this->definition = $cache->getWithFallback("ioc-ci-" . $className, function() use ($className) {
            $reflectionClass = new \ReflectionClass($className);
            $reflectionConstructor = $reflectionClass->getConstructor();

            $definition = [];

            if ($reflectionConstructor != null) {
                $reflectionParameters = $reflectionConstructor->getParameters();

                if (count($reflectionParameters) > 0) {
                    foreach ($reflectionParameters as $reflectionParameter) {
                        $parameterClass = $reflectionParameter->getClass();
                        $parameterName = $reflectionParameter->getName();

                        if (isset($parameterClass)) {
                            $type = '\\' . $parameterClass->getName();
                        } else {
                            $type = $this->getParameterClassFromPhpDoc($reflectionConstructor, $parameterName);
                        }

                        $arrayLessType = rtrim($type, '[]');
                        if (!empty($type) && !interface_exists($arrayLessType) && !class_exists($arrayLessType)) {
                            throw new \DC\IoC\Exceptions\InjectorException($className, $reflectionParameter->getName(), $type);
                        }

                        $x = [
                            "name" => $parameterName,
                            "class" => $type
                        ];

                        if ($reflectionParameter->isDefaultValueAvailable()) {
                            $x["default"] = $reflectionParameter->getDefaultValue();
                        }

                        $definition[] = $x;
                     }
                }
            }

            return $definition;
        });
        $this->className = $className;
    }

    private $definition;

    public function construct(array $parameters = []) {
        $class = $this->className;
        $params = [];
        foreach ($this->definition as $d) {
            if (!empty($d["class"])) {
                if (preg_match('/\[\]$/', $d["class"])) {
                    $params[] = $this->container->resolveAll(substr($d["class"], 0, count($d["class"])-3));
                }
                else {
                    try {
                        $params[] = $this->container->resolve($d["class"], $this->className);
                    }
                    catch (CannotResolveException $e) {
                        if (array_key_exists("default", $d)) {
                            $params[] = $d["default"];
                        } else {
                            throw $e;
                        }
                    }
                }
            }
            else {
                if (isset($parameters[$d["name"]])) {
                    $params[] = $parameters[$d["name"]];
                }
                else if (array_key_exists("default", $d)) {
                    $params[] = $d["default"];
                }
                else {
                    throw new \DC\IoC\Exceptions\CannotResolveException($d["name"], $this->className);
                }
            }
        }
        return new $class(...$params);
    }
}