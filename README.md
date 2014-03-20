# Simple IoC container for PHP

This is a simple IoC container for PHP.

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