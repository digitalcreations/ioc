<?php

namespace DC\IoC\Injection;

interface IConstructorInjector {
    function construct(array $parameters = []);
}