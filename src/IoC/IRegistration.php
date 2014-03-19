<?php

namespace DC\IoC;

interface IRegistration {
    function to($className);
    function toSelf();

    function withSingletonLifetime();
    function withPerResolveLifetime();
    function withContainerLifetime();
}