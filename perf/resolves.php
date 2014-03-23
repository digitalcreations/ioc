<?php
require_once __DIR__.'/Timer.php';
require_once __DIR__.'/../vendor/autoload.php';

const REGISTRATION_COUNT = 10;
const RESOLVE_COUNT = 10000;

interface IFoo {

}
class Foo implements IFoo {

}

echo "Testing ".RESOLVE_COUNT." resolves of ".REGISTRATION_COUNT." instances each...\n";

$container = new \DC\IoC\Container();
for ($i = 0; $i < REGISTRATION_COUNT; $i++) {
    $container->register('\Foo')->to('\IFoo')->withContainerLifetime();
}

\Timer::time(function($t) use ($container) {
    for ($i = 0; $i < RESOLVE_COUNT; $i++) {
        $container->resolveAll('\IFoo');
        if ($i % 1000 == 0) {
            $t->mark($i);
        }
    }
});