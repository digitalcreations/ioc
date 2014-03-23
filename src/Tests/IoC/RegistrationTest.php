<?php

namespace DC\Tests\IoC;

class RegistrationTest extends \PHPUnit_Framework_TestCase {
    /**
     * @expectedException \DC\IoC\Exceptions\InvalidClassOrInterfaceNameException
     */
    public function testRegistrationThrowsOnNonExistentClass() {
        $mockLifetimeManager = $this->getMock('\DC\IoC\Lifetime\IExtendedLifetimeManagerFactory');
        $mockConstructorInjector = $this->getMock('\DC\IoC\Injection\IConstructorInjector');
        new \DC\IoC\Registrations\ClassNameRegistration("Foo!", $mockLifetimeManager, $mockLifetimeManager, $mockConstructorInjector);
    }

    /**
     * @expectedException \DC\IoC\Exceptions\InvalidClassOrInterfaceNameException
     */
    public function testRegistrationThrowsOnNonIncompleteClassName() {
        $mockLifetimeManager = $this->getMock('\DC\IoC\Lifetime\IExtendedLifetimeManagerFactory');
        $mockConstructorInjector = $this->getMock('\DC\IoC\Injection\IConstructorInjector');
        new \DC\IoC\Registrations\ClassNameRegistration("RegistrationTest", $mockLifetimeManager, $mockLifetimeManager, $mockConstructorInjector);
    }

    public function testRegistrationSucceeds() {
        $mockLifetimeManager = $this->getMock('\DC\IoC\Lifetime\IExtendedLifetimeManagerFactory');
        $mockConstructorInjector = $this->getMock('\DC\IoC\Injection\IConstructorInjector');
        $registration = new \DC\IoC\Registrations\ClassNameRegistration('\DC\Tests\IoC\Foo', $mockLifetimeManager, $mockLifetimeManager, $mockConstructorInjector);
        $this->assertTrue($registration->canResolve('\DC\Tests\IoC\Foo'));
    }
}