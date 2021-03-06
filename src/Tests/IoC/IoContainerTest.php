<?php

namespace DC\Tests\IoC;

interface IFoo {

}
class Foo implements IFoo {
    public static $constructed = false;
    public function __construct() {
        Foo::$constructed = true;
    }
}
class Foo2 implements IFoo {

}

class Bar {

}

class ConstructorDependency {
    public $foo;

    public function __construct(IFoo $foo) {
        $this->foo = $foo;
    }
}

class UnresolvableConstructorDependency {
    public $foo;
    public $bar;
    public function __construct(IFoo $foo, $bar) {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}

class UnresolvableConstructorDependencyWithDefaultValue {
    public $foo;
    public $bar;
    public function __construct(IFoo $foo, $bar = 42) {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}

class UnresolvableConstructorDependencyWithDefaultNull {
    public $foo;
    public $bar;
    public function __construct(IFoo $foo, $bar = null) {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}

class ArrayConstructorDependency {
    /**
     * @var array|IFoo[]
     */
    public $foos;

    /**
     * @param \DC\Tests\IoC\IFoo[] $foos
     */
    public function __construct(array $foos = []) {

        $this->foos = $foos;
    }
}

class ArrayConstructorDependencyWithArgumentNameAndTypeSwapped {
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
        $container = new \DC\IoC\Container();
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
        $container = new \DC\IoC\Container();
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
        $container = new \DC\IoC\Container();
        $instance = new Bar();
        $container->register($instance);
        $this->assertInstanceOf('\DC\Tests\IoC\Bar', $container->resolve('\DC\Tests\IoC\Bar'));
        $this->assertTrue($instance === $container->resolve('\DC\Tests\IoC\Bar'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRegisterInstanceFailsForNonInterface() {
        $container = new \DC\IoC\Container();
        $container->register(new Bar())->to('\DC\Tests\IoC\IFoo');
    }

    public function testRegisterInstance() {
        $container = new \DC\IoC\Container();
        $instance = new Foo();
        $container->register($instance)->to('\DC\Tests\IoC\IFoo');
        $this->assertTrue($instance === $container->resolve('\DC\Tests\IoC\IFoo'));
    }

    public function testResolveAll() {
        $container = new \DC\IoC\Container();
        $container->register(new Foo())->to('\DC\Tests\IoC\IFoo');
        $container->register(new Foo())->to('\DC\Tests\IoC\IFoo');

        $instances = $container->resolveAll('\DC\Tests\IoC\IFoo');
        $this->assertEquals(2, count($instances));
        $this->assertFalse($instances[0] === $instances[1]);
    }

    public function testResolveAllWithMultipleClassRegistrationsWithContainerLifetime() {
        $container = new \DC\IoC\Container();
        $container->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo')->withContainerLifetime();
        $container->register('\DC\Tests\IoC\Foo2')->to('\DC\Tests\IoC\IFoo')->withContainerLifetime();

        $instances = $container->resolveAll('\DC\Tests\IoC\IFoo');
        $this->assertEquals(2, count($instances));
        $this->assertInstanceOf('\DC\Tests\IoC\Foo', $instances[0]);
        $this->assertInstanceOf('\DC\Tests\IoC\Foo2', $instances[1]);
    }

    public function testResolveAllWithMultipleFactoryRegistrationsWithSingletonLifetime() {
        $container = new \DC\IoC\Container();
        $container->register(function() { return new Foo(); })->to('\DC\Tests\IoC\IFoo')->withSingletonLifetime();
        $container->register(function() { return new Foo2(); })->to('\DC\Tests\IoC\IFoo')->withSingletonLifetime();

        $instances = $container->resolveAll('\DC\Tests\IoC\IFoo');
        $this->assertEquals(2, count($instances));
        $this->assertInstanceOf('\DC\Tests\IoC\Foo', $instances[0]);
        $this->assertInstanceOf('\DC\Tests\IoC\Foo2', $instances[1]);
    }

    /**
     * @expectedException \DC\IoC\Exceptions\MultipleRegistrationsFoundException
     */
    public function testResolveThrowsOnMultipleRegistrations() {
        $container = new \DC\IoC\Container();
        $container->register(new Foo())->to('\DC\Tests\IoC\IFoo');
        $container->register(new Foo())->to('\DC\Tests\IoC\IFoo');
        $container->resolve('\DC\Tests\IoC\IFoo');
    }

    /**
     * @expectedException \DC\IoC\Exceptions\CannotResolveException
     */
    public function testResolveNonRegisteredConstructorLess() {
        $container = new \DC\IoC\Container();
        $container->resolve('\DC\Tests\IoC\IFoo');
    }

    public function testPerResolveLifetime() {
        $container = new \DC\IoC\Container();
        $container->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo')->withPerResolveLifetime();

        $this->assertFalse($container->resolve('\DC\Tests\IoC\IFoo') === $container->resolve('\DC\Tests\IoC\IFoo'));
    }

    public function testSingletonLifetime() {
        $container = new \DC\IoC\Container();
        $container->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo')->withSingletonLifetime();

        $this->assertTrue($container->resolve('\DC\Tests\IoC\IFoo') === $container->resolve('\DC\Tests\IoC\IFoo'));
    }

    public function testContainerLifetimeResolvesToSame() {
        $container = new \DC\IoC\Container();
        $container->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo')->withContainerLifetime();

        $this->assertTrue($container->resolve('\DC\Tests\IoC\IFoo') === $container->resolve('\DC\Tests\IoC\IFoo'));
    }

    public function testContainerLifetimeDoesNotSurviveDifferentContainers() {
        $firstContainer = new \DC\IoC\Container();
        $firstContainer->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo')->withContainerLifetime();
        $firstInstance = $firstContainer->resolve('\DC\Tests\IoC\IFoo');

        $secondContainer = new \DC\IoC\Container();
        $secondContainer->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo')->withContainerLifetime();
        $secondInstance = $secondContainer->resolve('\DC\Tests\IoC\IFoo');
        $this->assertFalse($secondInstance === $firstInstance);
    }

    public function testSingletonLifetimeSurvivesDifferentContainers() {
        $firstContainer = new \DC\IoC\Container();
        $firstContainer->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo')->withSingletonLifetime();
        $firstInstance = $firstContainer->resolve('\DC\Tests\IoC\IFoo');

        $secondContainer = new \DC\IoC\Container();
        $secondContainer->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo')->withSingletonLifetime();
        $secondInstance = $secondContainer->resolve('\DC\Tests\IoC\IFoo');
        $this->assertTrue($secondInstance === $firstInstance);
    }

    public function testConstructorInjection() {
        $container = new \DC\IoC\Container();
        $container->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo');

        $instance = $container->resolve('\DC\Tests\IoC\ConstructorDependency');
        $this->assertNotNull($instance);
        $this->assertNotNull($instance->foo);
        $this->assertInstanceOf('\DC\Tests\IoC\IFoo', $instance->foo);
    }

    public function testConstructorInjectionWithParameters() {
        $container = new \DC\IoC\Container();
        $container->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo');
        $container->register('\DC\Tests\IoC\UnresolvableConstructorDependency')
            ->withParameters(["bar" => "bar"]);

        $instance = $container->resolve('\DC\Tests\IoC\UnresolvableConstructorDependency');
        $this->assertNotNull($instance);
        $this->assertNotNull($instance->foo);
        $this->assertEquals("bar", $instance->bar);
        $this->assertInstanceOf('\DC\Tests\IoC\IFoo', $instance->foo);
    }

    public function testConstructorInjectionWithDefaultValue() {
        $container = new \DC\IoC\Container();
        $container->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo');

        $instance = $container->resolve('\DC\Tests\IoC\UnresolvableConstructorDependencyWithDefaultValue');
        $this->assertNotNull($instance);
        $this->assertNotNull($instance->foo);
        $this->assertEquals(42, $instance->bar); // default value
        $this->assertInstanceOf('\DC\Tests\IoC\IFoo', $instance->foo);
    }

    public function testConstructorInjectionWithDefaultNull() {
        $container = new \DC\IoC\Container();
        $container->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo');

        $instance = $container->resolve('\DC\Tests\IoC\UnresolvableConstructorDependencyWithDefaultNull');
        $this->assertNotNull($instance);
        $this->assertNotNull($instance->foo);
        $this->assertNull($instance->bar);
        $this->assertInstanceOf('\DC\Tests\IoC\IFoo', $instance->foo);
    }

    /**
     * @expectedException \DC\IoC\Exceptions\CannotResolveException
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

    public function testConstructorInjectionForArrayWithArgumentNameAndTypeSwapped() {
        $container = new \DC\IoC\Container();
        $container->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo');

        $instance = $container->resolve('\DC\Tests\IoC\ArrayConstructorDependencyWithArgumentNameAndTypeSwapped');

        $this->assertEquals(1, count($instance->foos));
    }

    public function testConstructorInjectionWithUnfullfilledArray() {
        $container = new \DC\IoC\Container();
        // nothing registered for IFoo

        $instance = $container->resolve('\DC\Tests\IoC\ArrayConstructorDependency');
        $this->assertEquals(0, count($instance->foos));
    }

    public function testFunctionInjectionIncludesDefaultValue() {
        $container = new \DC\IoC\Container();
        $container->register(function($bar = 42) {
            return new \DC\Tests\IoC\Foo();
        })->to('\DC\Tests\IoC\IFoo');

        $foo = $container->resolve('\DC\Tests\IoC\IFoo');
        $this->assertInstanceOf('\DC\Tests\IoC\Foo', $foo);
    }

    public function testFunctionInjectionIncludesDefaultValueWhenNull() {
        $container = new \DC\IoC\Container();
        $container->register(function($bar = null) {
            return new \DC\Tests\IoC\Foo();
        })->to('\DC\Tests\IoC\IFoo');

        $foo = $container->resolve('\DC\Tests\IoC\IFoo');
        $this->assertInstanceOf('\DC\Tests\IoC\Foo', $foo);
    }

    public function testFactoryRegistrationServiceInjection() {
        $container = new \DC\IoC\Container();
        $container->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo');

        $container->register(function(IFoo $foo) {
            $this->assertInstanceOf('\DC\Tests\IoC\Foo', $foo);
            return new Bar();
        })->to('\DC\Tests\IoC\Bar');

        $container->resolve('\DC\Tests\IoC\Bar');
    }

    public function testFactoryRegistrationServiceArrayInjection() {
        $container = new \DC\IoC\Container();
        $container->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo');

        $container->register(
            /**
             * @param \DC\Tests\IoC\IFoo[] $foo
             */
            function(array $foo) {
                $this->assertInstanceOf('\DC\Tests\IoC\Foo', $foo[0]);
                return new Bar();
            })->to('\DC\Tests\IoC\Bar');

        $container->resolve('\DC\Tests\IoC\Bar');
    }

    public function testFactoryRegistrationParameterInjection() {
        $container = new \DC\IoC\Container();
        $container->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo');

        $container
            ->register(function(IFoo $foo, $bar) {
                $this->assertInstanceOf('\DC\Tests\IoC\Foo', $foo);
                $this->assertEquals("bar", $bar);
                return new Bar();
            })->withParameters(["bar" => "bar"])
            ->to('\DC\Tests\IoC\Bar');

        $container->resolve('\DC\Tests\IoC\Bar');
    }

    public function testInjectForMethod() {
        $container = new \DC\IoC\Container();
        $container->register('\DC\Tests\IoC\Foo')->to('\DC\Tests\IoC\IFoo');

        $ran = false;
        $result = $container->inject(function(IFoo $foo) use (&$ran) {
            $this->assertInstanceOf('\DC\Tests\IoC\Foo', $foo);
            $ran = true;
            return true;
        });
        $this->assertTrue($ran);
        $this->assertTrue($result);
    }

    /**
     * @covers \DC\IoC\Container
     */
    public function testModuleRegistration() {
        $nameList = [];
        $callback = function($name) use (&$nameList) {
            $nameList[] = $name;
        };

        $modules = [
            new \DC\Tests\IoC\Modules\TestModule("dc/cache", [], $callback),
            new \DC\Tests\IoC\Modules\TestModule("dc/router", ["dc/cache"], $callback),
            new \DC\Tests\IoC\Modules\TestModule("dc/router-outputcache", ["dc/router", "dc/cache"], $callback),
            new \DC\Tests\IoC\Modules\TestModule("dc/router-authorize", ["dc/router"], $callback)
        ];

        $resolver = new \DC\IoC\Container();
        $resolver->registerModules($modules);

        $this->assertEquals("dc/cache", $nameList[0]);
        $this->assertEquals("dc/router", $nameList[1]);

        $this->assertEquals("dc/router-outputcache", $nameList[2]);
        $this->assertEquals("dc/router-authorize", $nameList[3]);
    }
}