<?php

namespace DC\IoC\Injection;

interface IConstructorInjector {
    function construct($className, array $parameters = []);
}