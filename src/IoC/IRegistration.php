<?php

namespace DC\IoC;

interface IRegistration {
    function to($className);

    function withSingletonLifetime();
    function withPerResolveLifetime();
    function withContainerLifetime();
}