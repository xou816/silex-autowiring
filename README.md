# silex-autowire

A service to autowire your... services in [Silex](https://silex.sensiolabs.org/).

Features
-------

* Automatically inject dependencies in constructors and controllers

Requirements
------------

 (to be determined)

Installation
------------

Through [Composer](http://getcomposer.org): `composer require xou816/silex-autowiring`.

```php
use SilexAutowiring\AutowiringServiceProvider;

// ...

$app->register(new AutowiringServiceProvider());
```

The service is then available as ```$app['autowiring']```.

Usage
------

### Constructor injection

Call the `wire` method to let the autowiring service create your instances.

```php
class Foo {}
class Bar {
    public function __construct(Foo $foo) {
        $this->foo = $foo;
    }
    public function greet() {
        return 'Hello!';
    }
}
$app['autowiring']->wire(Foo::class);
$app['autowiring']->wire(Bar::class);
```

The resulting instance of `Bar` can be found using `$app['autowiring']->provider(Bar::class);`. If you only need the service name, use the `name` method.

**Every dependency of `Bar` has to be wired!**

### Controller injection

You can entirely avoid ever calling `provider` or `name`, as your wired services can be directly injected in controllers, alongside the usual `Request` or `Application` objects.

```php
$app->get('/', function (Bar $bar) {
    return $bar->greet();
});
```

### Injecting built-in services

If you wish to use a service shipped with Silex or a provider, you can use the `provide` method. For instance:

```php
$app->register(new DoctrineServiceProvider()); // registers a 'db' service
$app['autowiring']->provide('db');

// ...

class DAO {
    public function __construct(\Doctrine\DBAL\Connection $db) { /**/ }
}
$app['autowiring']->wire(DAO::class); // will work just fine!
```

### Injecting any service

You may also inject any other service, even plain PHP objects (for which type hinting cannot be used) such as arrays or integers, as long as they are available in the Pimple container.

In order to do that, add a dependency to `SilexAutowiring\Injectable`:

```php
$app['foo_options'] = array('bar' => true);

class Foo {
    public __construct(Injectable $fooOptions) {
        $this->bar = $fooOptions->get()['bar']; // true
    }
}
$app['autowiring']->wire(Foo::class);
```
The autowiring service knows how to inject the correct service as it infers the service's name from the constructor argument's name (`fooOptions` being converted from camel case to snake case).
Since an instance of `Injectable` has to be passed to the constructor, you can retrieve the real service by calling `get`.

### Interfaces

The autowiring service can also inject services based on interface rather than class. If multiple services implement the same interface, the last service to be wired is used.

```php
interface GreeterInterface {
    public function greet();
}

class PoliteGreeter implements GreeterInterface {
    public function greet() {
        return 'Hello!';
    }
}
$app['autowiring']->wire(PoliteGreeter::class);

class RudeGreeter implements GreeterInterface {
    public function greet() {
        return '...';
    }
}
$app['autowiring']->wire(RudeGreeter::class);

$app->get('/', function(GreeterInterface $greeter) {
    return $greeter->greet(); // '...'
});
```