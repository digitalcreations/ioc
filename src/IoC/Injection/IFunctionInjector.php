<?php

namespace DC\IoC\Injection;


interface IFunctionInjector {
    function run(callable $function, array $parameters = []);
} 