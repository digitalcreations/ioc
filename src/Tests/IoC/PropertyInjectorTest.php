<?php

namespace DC\Tests\IoC;

use DC\IoC\Injection\PropertyInjector;

class PropertyInjectorModel {
    /**
     * @inject
     * @var \DC\Tests\IoC\IFoo
     */
    public $foo;

    /**
     * @inject
     * @var \DC\Tests\IoC\IFoo[]
     */
    public $foos;

    /**
     * @var \DC\Tests\IoC\IFoo
     */
    public $notInjected;
}

class InvalidPropertyInjectorModel {
    /**
     * @inject
     */
    public $invalid;
}

class PropertyInjectorTest extends \PHPUnit_Framework_TestCase {
    public function testResolveProperty() {
        $mockContainer = $this->getMock('\DC\IoC\Container');
        $mockContainer->expects($this->once())
                      ->method('resolve')
                      ->willReturn(new \stdClass());
        $mockContainer->expects($this->once())
                      ->method('resolveAll')
                      ->willReturn(array( new \stdClass() ));

        $injector = new PropertyInjector($mockContainer);
        $object = new PropertyInjectorModel();
        $injector->inject($object);
        $this->assertNotNull($object->foo);
        $this->assertInstanceOf('\stdClass', $object->foo);

        $this->assertCount(1, $object->foos);

        $this->assertNull($object->notInjected);
    }

    /**
     * @expectedException \DC\IoC\Exceptions\InjectorException
     */
    public function testInvalidInjectionMark() {
        $mockContainer = $this->getMock('\DC\IoC\Container');
        $injector = new PropertyInjector($mockContainer);
        $object = new InvalidPropertyInjectorModel();
        $injector->inject($object);
    }
} 