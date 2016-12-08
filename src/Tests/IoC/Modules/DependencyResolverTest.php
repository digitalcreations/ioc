<?php

namespace DC\Tests\IoC\Modules;

class TestModule extends \DC\IoC\Modules\Module {
    /**
     * @var callable
     */
    private $callback;

    public function __construct($name, array $dependencies, callable $callback = null)
    {
        $this->callback = $callback;
        parent::__construct($name, $dependencies);
    }

    function register(\DC\IoC\Container $container)
    {
        if (is_callable($this->callback)){
            $callback = $this->callback;
            $callback($this->getName());
        }
    }
}

class DependencyResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testOrder() {
        $modules = [
            new TestModule("dc/cache", []),
            new TestModule("dc/router", ["dc/cache"]),
            new TestModule("dc/router-outputcache", ["dc/router", "dc/cache"]),
            new TestModule("dc/router-authorize", ["dc/router"])
        ];

        $resolver = new \DC\IoC\Modules\DependencyResolver();
        $ordered = $resolver->resolveOrder($modules);
        $this->assertEquals("dc/cache", $ordered[0]);
        $this->assertEquals("dc/router", $ordered[1]);

        $this->assertEquals("dc/router-outputcache", $ordered[2]);
        $this->assertEquals("dc/router-authorize", $ordered[3]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUnknownDependency() {
        $modules = [
            new TestModule("foo", ["bar"]),
        ];

        $resolver = new \DC\IoC\Modules\DependencyResolver();
        $resolver->resolveOrder($modules);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCyclicDependency() {
        $modules = [
            new TestModule("a", ["b"]),
            new TestModule("b", ["c"]),
            new TestModule("c", ["d"]),
            new TestModule("d", ["a"]),
        ];

        $resolver = new \DC\IoC\Modules\DependencyResolver();
        $resolver->resolveOrder($modules);
    }
}
