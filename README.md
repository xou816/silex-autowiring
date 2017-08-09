# silex-autowire

A service to autowire your... services in [Silex](https://silex.sensiolabs.org/).

Requirements
------------

(to be determined)

Installation
------------

Through [Composer](https://packagist.org/packages/xou816/silex-autowiring): `composer require xou816/silex-autowiring`.

```php
use SilexAutowiring\AutowiringServiceProvider;

// ...

$app->register(new AutowiringServiceProvider());
```

The service is then available as ```$app['autowiring']```.

Usage
------

## Basics

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

You can pass an array of arguments to `wire` : these arguments will be passed in order wherever an argument could not be resolved to a service. For instance, the following two examples are equivalent:

```php
class Foo {}
class Bar {
    public function __construct(Foo $foo, $arg1, $arg2) {
        $this->foo = $foo;
        echo $arg1.' '.$arg2; // => 'hello world'
    }
}
$app['autowiring']->wire(Foo::class);
$app['autowiring']->wire(Bar::class, ['hello', 'world']);
```

```php
class Foo {}
class Bar {
    public function __construct($arg1, Foo $foo, $arg2) { // $args1 is now in first position
        $this->foo = $foo;
        echo $arg1.' '.$arg2; // => 'hello world'
    }
}
$app['autowiring']->wire(Foo::class);
$app['autowiring']->wire(Bar::class, ['hello', 'world']);
```

### Controller injection

You can entirely avoid ever calling `provider` or `name`, as your wired services can be directly injected in controllers, alongside the usual `Request` or `Application` objects.

```php
$app->get('/', function (Bar $bar) {
    return $bar->greet();
});
```

### Closure injection

You can also inject services in a closure, using `invoke`:

```php
$fun = function(Foo $foo, $arg) {
    $foo->bar($arg);
};
$app['autowiring']->invoke($fun, ['bar']);
```

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

It is very useful to painlessly switch implementations of an interface.

## Injecting other services

### Exposing built-in services

If you wish to use a service shipped with Silex or a provider, you can use the `expose` method. For instance:

```php
$app->register(new DoctrineServiceProvider()); // registers a 'db' service
$app['autowiring']->expose('db');

// ...

class DAO {
    public function __construct(\Doctrine\DBAL\Connection $db) { /**/ }
}
$app['autowiring']->wire(DAO::class); // will work just fine!
```

The `AutowiringService` itself is exposed, and can therefore be injected!

When you need to register a service and expose it, you should instead use `provide`.

```php
$app['autowiring']->provide(Foo::class, function($app, Bar $bar) {
    return new Foo($bar);
});
// Better than:
// $app['whatever'] = function($app) { ... };
// $app['autowiring']->expose('whatever');
```

### Injecting any service

You may also inject any other service, even plain PHP objects (for which type hinting cannot be used) such as arrays or integers, as long as they are available in the Pimple container.

In order to do that, add a dependency to a `SilexAutowiring\Injectable\Injectable`:

```php
$app['foo_options'] = array('bar' => true);
$app['foo_options.baz'] = false;

class Foo {
    public __construct(Injectable $fooOptions) {
        $this->bar = $fooOptions->get()['bar']; // true
        $this->baz = $fooOptions->get()['baz']; // false
    }
}
$app['autowiring']->wire(Foo::class);
```
The autowiring service knows how to inject the correct service as it infers the service's name from the constructor argument's name (`fooOptions` being converted from camel case to snake case).
Since an instance of `Injectable` has to be passed to the constructor, you can retrieve the real service by calling `get`.

If you intend to inject a configuration array, you can instead use `SilexAutowiring\Injectable\Configuration`, which works the exactly like `Injectable` (both implement the `SilexAutowiring\Injectable\InjectableInterface`), but has array access.

```php
$app['foo_options'] = array('bar' => true);
$app['foo_options.baz'] = false;

class Foo {
    public __construct(Configuration $fooOptions) {
        $this->bar = $fooOptions['bar']; // true
        $this->baz = $fooOptions['baz']; // false
    }
}
$app['autowiring']->wire(Foo::class);
```

### Property injection

It is not possible to rely on type hinting to inject services on properties. Therefore, this feature is intended for configuration instead.

```php
$app['fooconfig'] = array('bar' => true);
$app['fooconfig.baz'] = false;

class Foo {
    private $bar;
    private $baz;
}
$app['autowiring']->wire(Foo::class);
$app['autowiring']->configure(Foo::class, 'fooconfig');
```

It resolves names much like with `Injectable`s, but injects plain values instead.

**Warning:** injected values on properties are only available after construction.

### Injection resolver

You might dislike the way `Injectable` and `configure` handle names (from snake case to camel case). It is possible to tweak this behaviour by providing (using `wire` for instance!) a custom implementation of `SilexAutowiring\Injectable\InjectableResolver`. This task is made easier by extending the `SilexAutowiring\Injectable\AbstractCompositeKeyResolver`, which allows handling identifiers such as `foo_options.baz` above.

You may also just `wire` the `SilexAutowiring\Injectable\IdentityResolver` class into your app to use a simpler resolution mechanism (no casing style alteration).

## Experimental

You may also use the `SilexAutowiring\Traits\Autowire` and `SilexAutowiring\Traits\Autoconfigure` traits instead of calling `wire` and `configure`.

This is however not completely equivalent and likely worse in terms of performance.