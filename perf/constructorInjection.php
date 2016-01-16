<?php
require_once __DIR__.'/Timer.php';
require_once __DIR__.'/../vendor/autoload.php';

const INJECTION_COUNT = 100;

interface IFoo {

}
class Foo implements IFoo {

}
class Bar {
    /**
     * @var \IFoo
     */
    private $foo;

    /**
     * Bar constructor.
     * @param \IFoo $foo
     */
    public function __construct($foo) {

        $this->foo = $foo;
    }
}

echo "Testing ".INJECTION_COUNT." injections into constructor...\n";

//$cache = new \DC\Cache\Implementations\Memcached\Cache(
//    new \DC\Cache\Implementations\Memcached\MemcacheConfiguration('localhost', '11211'));
$container = new \DC\IoC\Container();
$container->register('\Foo')->to('\IFoo')->withContainerLifetime();
$container->register('\Bar');

\Timer::time(function($t) use ($container) {
    for ($i = 0; $i < INJECTION_COUNT; $i++) {
        $container->resolve('\Bar');
        if ($i > 0 && $i % 1000 == 0) {
            $t->mark($i);
        }
    }
});