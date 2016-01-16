<?php

namespace DC\Tests\IoC;

class RegistrationTest extends \PHPUnit_Framework_TestCase {
    /**
     * @expectedException \DC\IoC\Exceptions\InvalidClassOrInterfaceNameException
     */
    public function testRegistrationThrowsOnNonExistentClass() {
        $mockPropertyInjector = $this->getMock('\DC\IoC\Injection\IPropertyInjector');
        $mockLifetimeManager = new \DC\IoC\Lifetime\LifetimeManagerFactory($mockPropertyInjector);
        $mockConstructorInjector = $this->getMock('\DC\IoC\Injection\IConstructorInjector');
        new \DC\IoC\Registrations\ClassNameRegistration("Foo!", $mockLifetimeManager, $mockConstructorInjector);
    }

    /**
     * @expectedException \DC\IoC\Exceptions\InvalidClassOrInterfaceNameException
     */
    public function testRegistrationThrowsOnNonIncompleteClassName() {
        $mockPropertyInjector = $this->getMock('\DC\IoC\Injection\IPropertyInjector');
        $mockLifetimeManager = new \DC\IoC\Lifetime\LifetimeManagerFactory($mockPropertyInjector);
        $mockConstructorInjector = $this->getMock('\DC\IoC\Injection\IConstructorInjector');
        new \DC\IoC\Registrations\ClassNameRegistration("RegistrationTest", $mockLifetimeManager, $mockConstructorInjector);
    }

    public function testRegistrationSucceeds() {
        $mockPropertyInjector = $this->getMock('\DC\IoC\Injection\IPropertyInjector');
        $mockLifetimeManager = new \DC\IoC\Lifetime\LifetimeManagerFactory($mockPropertyInjector);
        $mockConstructorInjector = $this->getMock('\DC\IoC\Injection\IConstructorInjector');
        $registration = new \DC\IoC\Registrations\ClassNameRegistration('\DC\Tests\IoC\Foo', $mockLifetimeManager, $mockConstructorInjector);
        $this->assertEquals('\DC\Tests\IoC\Foo', $registration->getServiceType());
    }
}