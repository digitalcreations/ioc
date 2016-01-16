<?php
require_once __DIR__.'/Timer.php';
require_once __DIR__.'/../vendor/autoload.php';

const REGISTRATION_COUNT = 10000;

interface IWidget { }
class Widget implements IWidget { }

interface IFoo { }
class Foo implements IFoo {
    /**
     * Foo constructor.
     * @param $widget IWidget[]
     */
    function __construct(array $widget) {

    }
}

echo "Testing ".REGISTRATION_COUNT." registrations...\n";

$container = new \DC\IoC\Container();
\Timer::time(function($t) use ($container) {
    $container->register('\Widget')->to('\IWidget')->withContainerLifetime();
    for ($i = 0; $i < REGISTRATION_COUNT; $i++) {
        $container->register('\Foo')->to('\IFoo');
        if ($i % 1000 == 0) {
            $t->mark($i);
        }
    }
});