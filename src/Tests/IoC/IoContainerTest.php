<?php

namespace DC\Tests\IoC;

use DC\IoC\Container;

interface IFoo {

}
class Foo implements IFoo {
    public static $constructed = false;
    public function __construct() {
        Foo::$constructed = true;
    }
}

class Bar {

}

class ConstructorDependency {
    public $foo;

    public function __construct(IFoo $foo) {
        $this->foo = $foo;
    }
}

class ArrayConstructorDependency {
    /**
     * @var array|IFoo[]
     */
    public $foos;

    /**
     * @param $foos \DC\Tests\IoC\IFoo[]
     */
    public function __construct(array $foos) {

        $this->foos = $foos;
    }
}

class IoCContainerTest extends \PHPUnit_Framework_TestCase {
    public function testBasicResolve() {
        $container = new Container();
        $container->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo');
        $this->assertInstanceOf('\DC\Tests\IoC\Foo', $container->resolve('\DC\Tests\IoC\IFoo'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRegisterFailsForNonInterface() {
        $container = new \DC\IoC\Container();
        $container->register('\DC\Tests\IoC\Bar')->to('\DC\Tests\IoC\IFoo');
    }

    public function testBasicResolveDoesNotInstantiateBeforeRequested() {
        Foo::$constructed = false;
        $container = new \DC\IoC\Container();
        $container->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo');
        $this->assertFalse(Foo::$constructed, "Foo should not yet have been constructed");
        $container->resolve('\DC\Tests\IoC\IFoo');
        $this->assertTrue(Foo::$constructed, "Foo should have been constructed now");
    }

    public function testBasicResolveToSelf() {
        $container = new Container();
        $container->register('\DC\Tests\IoC\Bar');

        $this->assertInstanceOf('\DC\Tests\IoC\Bar', $container->resolve('\DC\Tests\IoC\Bar'));
    }

    public function testRegisterFactory() {
        $container = new \DC\IoC\Container();
        $constructed = false;
        $container->register(function() use (&$constructed) {
            $constructed = true;
            return new Foo();
        })->to('\DC\Tests\IoC\IFoo');
        $this->assertFalse($constructed);
        $instance = $container->resolve('\DC\Tests\IoC\IFoo');
        $this->assertTrue($constructed);
        $this->assertInstanceOf('\DC\Tests\IoC\IFoo', $instance);
    }

    public function testRegisterInstanceResolveToSelfByDefault() {
        $container = new Container();
        $instance = new Bar();
        $container->register($instance);
        $this->assertInstanceOf('\DC\Tests\IoC\Bar', $container->resolve('\DC\Tests\IoC\Bar'));
        $this->assertTrue($instance === $container->resolve('\DC\Tests\IoC\Bar'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRegisterInstanceFailsForNonInterface() {
        $container = new Container();
        $container->register(new Bar())->to('\DC\Tests\IoC\IFoo');
    }

    public function testRegisterInstance() {
        $container = new Container();
        $instance = new Foo();
        $container->register($instance)->to('\DC\Tests\IoC\IFoo');
        $this->assertTrue($instance === $container->resolve('\DC\Tests\IoC\IFoo'));
    }

    public function testResolveAll() {
        $container = new Container();
        $container->register(new Foo())->to('\DC\Tests\IoC\IFoo');
        $container->register(new Foo())->to('\DC\Tests\IoC\IFoo');

        $instances = $container->resolveAll('\DC\Tests\IoC\IFoo');
        $this->assertEquals(2, count($instances));
        $this->assertFalse($instances[0] === $instances[1]);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testResolveThrowsOnMultipleRegistrations() {
        $container = new Container();
        $container->register(new Foo())->to('\DC\Tests\IoC\IFoo');
        $container->register(new Foo())->to('\DC\Tests\IoC\IFoo');
        $container->resolve('\DC\Tests\IoC\IFoo');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testResolveNonRegisteredConstructorLess() {
        $container = new Container();
        $container->resolve('\DC\Tests\IoC\IFoo');
    }

    public function testPerResolveLifetime() {
        $container = new Container();
        $container->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo')->withPerResolveLifetime();

        $this->assertFalse($container->resolve('\DC\Tests\IoC\IFoo') === $container->resolve('\DC\Tests\IoC\IFoo'));
    }

    public function testSingletonLifetime() {
        $container = new Container();
        $container->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo')->withSingletonLifetime();

        $this->assertTrue($container->resolve('\DC\Tests\IoC\IFoo') === $container->resolve('\DC\Tests\IoC\IFoo'));
    }

    public function testContainerLifetimeResolvesToSame() {
        $container = new Container();
        $container->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo')->withContainerLifetime();

        $this->assertTrue($container->resolve('\DC\Tests\IoC\IFoo') === $container->resolve('\DC\Tests\IoC\IFoo'));
    }

    public function testContainerLifetimeDoesNotSurviveDifferentContainers() {
        $firstContainer = new Container();
        $firstContainer->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo')->withContainerLifetime();
        $firstInstance = $firstContainer->resolve('\DC\Tests\IoC\IFoo');

        $secondContainer = new Container();
        $secondContainer->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo')->withContainerLifetime();
        $secondInstance = $secondContainer->resolve('\DC\Tests\IoC\IFoo');
        $this->assertFalse($secondInstance === $firstInstance);
    }

    public function testSingletonLifetimeSurvivesDifferentContainers() {
        $firstContainer = new Container();
        $firstContainer->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo')->withSingletonLifetime();
        $firstInstance = $firstContainer->resolve('\DC\Tests\IoC\IFoo');

        $secondContainer = new Container();
        $secondContainer->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo')->withSingletonLifetime();
        $secondInstance = $secondContainer->resolve('\DC\Tests\IoC\IFoo');
        $this->assertTrue($secondInstance === $firstInstance);
    }

    public function testConstructorInjection() {
        $container = new Container();
        $container->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo');

        $instance = $container->resolve('\DC\Tests\IOC\ConstructorDependency');
        $this->assertNotNull($instance);
        $this->assertNotNull($instance->foo);
        $this->assertInstanceOf('\DC\Tests\IoC\IFoo', $instance->foo);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorThrowsForNonRegistration() {
        $container = new \DC\IoC\Container();

        $container->resolve('\DC\Tests\IOC\ConstructorDependency');
    }

    public function testConstructorInjectionForArray() {
        $container = new \DC\IoC\Container();
        $container->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo');

        $instance = $container->resolve('\DC\Tests\IoC\ArrayConstructorDependency');

        $this->assertEquals(1, count($instance->foos));
    }
} 