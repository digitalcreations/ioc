<?php

namespace DC\IoC;

class ExtendedLifetimeManager extends LifetimeManager {

    private $instance;
    function resolve()
    {
        if ($this->instance == null) {
            $this->instance = $this->registration->create();
        }
        return $this->instance;
    }
}