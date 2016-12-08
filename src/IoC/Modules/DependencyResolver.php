<?php

namespace DC\IoC\Modules;

class DependencyResolver
{
    /**
     * All modules must be registered at once for dependency resolution to work.
     *
     * @param array|\Module[] $modules
     * @return Module[]
     */
    function resolveOrder(array $modules) {
        $ordered = [];
        $dependencies = [];

        foreach ($modules as $module) {
            $ordered[$module->getName()] = $module;
            $dependencies[$module->getName()] = $module->getDependencies();
        }

        $order = [];
        while (count($ordered) > 0) {
            $dependencyLessModules = array_filter($dependencies, function($d) { return count($d) == 0; });
            if (count($dependencyLessModules) === 0) {
                throw new \InvalidArgumentException("Could not resolve dependencies for modules: " .  implode(', ', array_keys($dependencies)));
            }

            foreach ($dependencyLessModules as $name => $_) {
                $order[] = $name;
                unset($ordered[$name]);
                unset($dependencies[$name]);

                // now remove this from other dependencies as it is no longer needed
                foreach ($dependencies as $k => $v) {
                    $index = array_search($name, $v);
                    if ($index !== false) {
                        unset($dependencies[$k][$index]);
                    }
                }
            }
        }
        return $order;
    }
}