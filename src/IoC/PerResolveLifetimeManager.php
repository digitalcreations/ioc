<?php

namespace DC\IoC;

class PerResolveLifetimeManager extends LifetimeManager {

    function resolve()
    {
        return $this->registration->create();
    }
}