![DC\IoC - IoC container for PHP](logo.png)

## Installation

```
$ composer install dc/ioc
```

Or add it to `composer.json`:

```
"require": {
	"dc/ioc": "0.*"
}
```

```
$ composer install
```

## Registering

You can register objects, classes and factory functions with the container.

Registering a class:

```php
$container = new \DC\IoC\Container();
$container
	->register('\Car') // fully qualified namespace
    ->to('\IVehicle')
    ->withContainerLifetime(); // lives as long as the container
```

Registering an instance (instance registrations do not support lifetimes, they always live as long as the container does):

```php
$container
	->register(new \Car())
	->to('\IVehicle');
```

Registering a factory function:

```php
$container
	->register(function() {
		new \Car();
	})->to('\IVehicle');
```

## Understanding lifetimes

There are 3 possible lifetime registrations: `withPerResolveLifetime()`, `withContainerLifetime()` and `withSingletonLifetime()`. You can use these when you pass a class name or factory function to `register()`, but not if you pass an instance of an object.

- `withPerResolveLifetime()` will create a new instance every time you resolve it.
- `withContainerLifetime()` will resolve the same instance from that container.
- `withSingletonLifetime()` will resolve the same instance from all containers.

## Resolving services

You can resolve services by querying the container directly:

```php
$vehicle = $container->resolve('\IVehicle');
```

If you have multiple registrations for the same interface or class name, you can use `resolveAll()`.

```php
$vehicles = $container->resolveAll('\IVehicle');
```

## Constructor injection

Use type hints to inject a dependency into a class.

```php
class Trailer {
	public __construct(\IVehicle $vehicle) {
		// attach your Trailer to you IVehicle, or something
	}
}

$container->resolve("Trailer"); // you don't even have to register Trailer
```

If you don't want to use type hints, you can use PhpDoc comments instead. This is particularly useful when you want to get an array of something, as this is not supported by PHPs type-hinting syntax.

```php
class Trailer {
    /**
     * @param $matchesVehicles array|\IVehicle[]
     */
	public __construct(array $matchesVehicles) {

	}
}
```

Or even simpler:

```php
class Trailer {
    /**
     * @param $matchesVehicles \IVehicle[]
     */
	public __construct($matchesVehicles) {

	}
}
```

## Property injection

All objects resolved through the container will have their non-static properties scanned for possible dependencies. To mark a property as injectable, it needs both a `@var` and a `@inject` PhpDoc declaration:

```php
class Car {
   /**
    * @inject
    * @var \Trailer
    */
   private $trailer;
}
```

For objects that haven't been resolved through the container (meaning objects you have constructed yourself), you can apply property injection using the `inject()` method:

```php
$car = new Car();
$container->inject($car);
``` 

## Factory injection

When you provide a factory function to registration, you can have other services injected as in a constructor. You can as before, either rely on type hints, or on PhpDoc comments (for injecting arrays).

```php
$container->register(function(\IFoo $foo) {
    return new Bar($foo);
})->to('\Bar');
```

```php
$container->register(
	/**
     * @param $foos \IFoo[]
     */
	function(array $foos) {
		return new Bar($foos);
	})->to('\Bar');
```

## Modules

If you have a large project that needs to register a lot of services, implementing a module may be the way to go.

Extend `\DC\IoC\Modules\Module` and specify a name for your module and which modules it depends on.
 
```php
class MyModule extends \DC\IoC\Modules\Module {
    public __construct() {
        parent::__construct("package-name", ["dc/router"]);
    }
    
    public register(\DC\IoC\Container $container) {
        $container->register('\Foo')->to('\Bar');
    }
}
```

When (preferably before) registering your services, register all the modules first:

```php
$container->registerModules([new MyModule(), new \DC\Router\Module()]);
```

The container will try to register any dependencies in the correct order.

## Performance

Some crude performance tests can be found in the `perf/` folder of this repos. They show the following (numbers from a VM running on my machine):

- A single registration uses about 600 bytes of memory, and takes 0.03 **ms**. *Unless you are registering thousands of services, speed and memory should not be a limiting factor*. For a typical setup with less than a hundred registrations per page view, you should expect it to add less than 25 ms to your bottom line in most cases.
- Resolves are negligible. A `resolveAll`-call that returns 10 objects, takes on average 0.09 **ms** (to resolve 10 objects).
- Constructor and function injection is done using reflection, which is often assumed to be slow. Again, to resolve a single interface takes 0.06 **ms**.