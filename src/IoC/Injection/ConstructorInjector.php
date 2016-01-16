<?php

namespace DC\IoC\Injection;

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

                        $definition[] = [
                            "name" => $parameterName,
                            "class" => $type
                        ];
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
                    $params[] = $this->container->resolve($d["class"]);
                }
            }
            else {
                $params[] = $parameters[$d["name"]];
            }
        }
        return new $class(...$params);
    }
}