<?php
require_once __DIR__.'/Timer.php';
require_once __DIR__.'/../vendor/autoload.php';

const INJECTION_COUNT = 10000;

interface IFoo {

}
class Foo implements IFoo {

}
class Bar {

}

echo "Testing ".INJECTION_COUNT." injections into a factory function...\n";

$container = new \DC\IoC\Container();
$container->register('\Foo')->to('\IFoo')->withContainerLifetime();
$container->register(function(\IFoo $foo) use (&$invokeCount) {
    return new Bar();
})->to('\Bar');

\Timer::time(function($t) use ($container) {
    for ($i = 0; $i < INJECTION_COUNT; $i++) {
        $container->resolve('\Bar');
        if ($i % 1000 == 0) {
            $t->mark($i);
        }
    }
});