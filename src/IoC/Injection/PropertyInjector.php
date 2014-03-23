<?php

namespace DC\IoC\Injection;

class PropertyInjector extends InjectorBase implements IPropertyInjector {

    function inject($object)
    {
        $reflectionObject = new \ReflectionObject($object);
        $reflectionProperties = $reflectionObject->getProperties();
        foreach ($reflectionProperties as $reflectionProperty) {
            if ($reflectionProperty->isStatic()) continue;
            if (!$reflectionProperty->isDefault()) continue;

            $doc = $reflectionProperty->getDocComment();
            if (!preg_match('/^\s+\*\s+@inject\s/im', $doc)) continue;

            if (preg_match('/^\s+\*\s+@var\s+(?:array\|)?(.*)\s?.*$/im', $doc, $regs)) {
                $type = trim($regs[1]);
                $shouldResolveAll = preg_match('/\[\]$/', $type);
                if ($shouldResolveAll) {
                    $type = substr($type, 0, count($type)-3);
                }

                if (class_exists($type) || interface_exists($type)) {
                    try {
                        if ($shouldResolveAll) {
                            $resolved = $this->container->resolveAll($type);
                        } else {
                            $resolved = $this->container->resolve($type);
                        }
                        $reflectionProperty->setValue($object, $resolved);
                    } catch (\Exception $e) {
                        throw new \DC\IoC\Exceptions\InjectorException(get_class($object), $reflectionProperty->getName(), $type, $e);
                    }
                }
            } else {
                throw new \DC\IoC\Exceptions\InjectorException(get_class($object), $reflectionProperty->getName(), "unknown");
            }
        }
    }
}