<?php

namespace DC\Tests\IoC;

class RegistrationTest extends \PHPUnit_Framework_TestCase {
    /**
     * @expectedException \DC\IoC\Exceptions\InvalidClassOrInterfaceNameException
     */
    public function testRegistrationThrowsOnNonExistentClass() {
        $mockContainer = $this->getMock('\DC\IoC\Container');
        new \DC\IoC\Registrations\ClassNameRegistration("Foo!", $mockContainer);
    }

    /**
     * @expectedException \DC\IoC\Exceptions\InvalidClassOrInterfaceNameException
     */
    public function testRegistrationThrowsOnNonIncompleteClassName() {
        $mockContainer = $this->getMock('\DC\IoC\Container');
        new \DC\IoC\Registrations\ClassNameRegistration("RegistrationTest", $mockContainer);
    }

    public function testRegistrationSucceeds() {
        $mockContainer = $this->getMock('\DC\IoC\Container');
        $registration = new \DC\IoC\Registrations\ClassNameRegistration('\DC\Tests\IoC\Foo', $mockContainer);
        $this->assertTrue($registration->canResolve('\DC\Tests\IoC\Foo'));
    }
}