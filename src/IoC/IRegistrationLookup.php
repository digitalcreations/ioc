<?php

namespace DC\IoC;

interface IRegistrationLookup {
    function canResolve($className);
    function create();
    function resolve();
}