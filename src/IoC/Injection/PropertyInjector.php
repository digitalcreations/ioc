<?php

namespace DC\IoC\Injection;

class PropertyInjector extends InjectorBase implements IPropertyInjector {

    private $definitions = [];

    function getDefinition($class) {
        if (!isset($this->definitions[$class])) {
             $this->definitions[$class] = $this->cache->getWithFallback('ioc-pi-' . $class, function() use ($class) {
                 $reflectionClass = new \ReflectionClass($class);
                 $reflectionProperties = $reflectionClass->getProperties();

                 $definition = [];

                 foreach ($reflectionProperties as $reflectionProperty) {
                     $phpdoc = new \phpDocumentor\Reflection\DocBlock($reflectionProperty);
                     $injectTags = $phpdoc->getTagsByName(\DC\IoC\Tags\InjectTag::$name);
                     if (count($injectTags) < 1) {
                         continue;
                     }

                     $varTags = $phpdoc->getTagsByName("var");
                     if (count($varTags) < 1) {
                         throw new \DC\IoC\Exceptions\InjectorException($class, $reflectionProperty->getName(), "UNKNOWN");
                     }
                     else {
                         $definition[$reflectionProperty->getName()] = $varTags[0]->getType();
                     }
                 }
                 return $definition;
             });
        }
        return $this->definitions[$class];
    }

    function inject($object)
    {
        $definition = $this->getDefinition(get_class($object));
        foreach ($definition as $property => $type) {
            $shouldResolveAll = preg_match('/\[\]$/', $type);
            if ($shouldResolveAll) {
                $type = substr($type, 0, count($type)-3);
            }
            try {
                if ($shouldResolveAll) {
                    $resolved = $this->container->resolveAll($type);
                } else {
                    $resolved = $this->container->resolve($type);
                }
                $object->$property = $resolved;
            } catch (\Exception $e) {
                throw new \DC\IoC\Exceptions\InjectorException(get_class($object), $property, $type, $e);
            }
        }
    }
}