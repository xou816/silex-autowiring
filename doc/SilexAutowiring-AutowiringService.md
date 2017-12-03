SilexAutowiring\AutowiringService
===============






* Class name: AutowiringService
* Namespace: SilexAutowiring





Properties
----------


### $app

    private mixed $app





* Visibility: **private**


### $status

    private mixed $status = array()





* Visibility: **private**


### $debug

    private mixed $debug = false





* Visibility: **private**


Methods
-------


### __construct

    mixed SilexAutowiring\AutowiringService::__construct(\Silex\Application $app)





* Visibility: **public**


#### Arguments
* $app **Silex\Application**



### debug

    mixed SilexAutowiring\AutowiringService::debug()

Turn on debug mode



* Visibility: **public**




### getDebugInfo

    array SilexAutowiring\AutowiringService::getDebugInfo()

Get debug info as an array mapping classnames to booleans (true means class service is up)



* Visibility: **public**




### debugRegistration

    mixed SilexAutowiring\AutowiringService::debugRegistration(string $classname)





* Visibility: **private**


#### Arguments
* $classname **string**



### register

    string SilexAutowiring\AutowiringService::register(string $classname, callable $closure, boolean $factory)





* Visibility: **private**


#### Arguments
* $classname **string**
* $closure **callable**
* $factory **boolean**



### hasTrait

    boolean SilexAutowiring\AutowiringService::hasTrait(string $classname, string $trait)





* Visibility: **private**


#### Arguments
* $classname **string**
* $trait **string**



### mapParameters

    array SilexAutowiring\AutowiringService::mapParameters(\Silex\Application $app, \ReflectionFunctionAbstract $fun, array $args)





* Visibility: **private**


#### Arguments
* $app **Silex\Application**
* $fun **ReflectionFunctionAbstract**
* $args **array**



### isInjectable

    boolean SilexAutowiring\AutowiringService::isInjectable(string $classname)





* Visibility: **private**


#### Arguments
* $classname **string**



### configureWithRoot

    \Closure SilexAutowiring\AutowiringService::configureWithRoot(string $root, array<mixed,\ReflectionProperty> $props)





* Visibility: **private**


#### Arguments
* $root **string**
* $props **array&lt;mixed,\ReflectionProperty&gt;**



### configureNoRoot

    \Closure SilexAutowiring\AutowiringService::configureNoRoot(array<mixed,\ReflectionProperty> $props)





* Visibility: **private**


#### Arguments
* $props **array&lt;mixed,\ReflectionProperty&gt;**



### construct

    object SilexAutowiring\AutowiringService::construct(string $classname, array $args)

Create an instance of a given class, passing an array of extra arguments to the constructor when those could not be injected.



* Visibility: **public**


#### Arguments
* $classname **string**
* $args **array**



### constructor

    \Closure SilexAutowiring\AutowiringService::constructor(string $classname)

Create a constructor for a given class.

Arguments that could not be injected into the constructor should be passed to the closure.

* Visibility: **public**


#### Arguments
* $classname **string**



### wire

    string SilexAutowiring\AutowiringService::wire(string $classname, array $args, boolean $factory)

Creates a new service, which returns an instance of the given class.

Dependencies of the service are resolved automatically, as long as they have been exposed in one way or another to the AutowiringService
Extra arguments may be passed. They will be used whenever an argument could not be resolved by the AutowiringService.

* Visibility: **public**


#### Arguments
* $classname **string**
* $args **array**
* $factory **boolean**



### extend

    callable SilexAutowiring\AutowiringService::extend($classname, callable $closure)

Extend a class provider using a callable.

The callable should return an instance of the class.

* Visibility: **public**


#### Arguments
* $classname **mixed**
* $closure **callable**



### configure

    mixed SilexAutowiring\AutowiringService::configure(string $classname, string|null $root)

Configure a class available to the AutowiringService.

Possible values for class properties are searched in the Silex container.
If a $root is specified, then search for values will start with this root name.
If a static `autoconfigure` property exists, it will be used as the root search name.

* Visibility: **public**


#### Arguments
* $classname **string**
* $root **string|null**



### expose

    string SilexAutowiring\AutowiringService::expose(string $service_name, string|null $classname)

Expose a Silex service to the AutowiringService. A new service is created.

An exposed service can then be used as a dependency, and injected by class or interface.

* Visibility: **public**


#### Arguments
* $service_name **string**
* $classname **string|null**



### name

    string SilexAutowiring\AutowiringService::name(string $classname, string|null $paramname)

Obtain the service name (container offset) of a class service.

The service might not exist yet!

* Visibility: **public**


#### Arguments
* $classname **string**
* $paramname **string|null**



### provides

    boolean SilexAutowiring\AutowiringService::provides($classname, string|null $paramname, boolean $strict)

Check if a service provides a class.

If $strict is true, do not attempt to find classes with Autowire trait.

* Visibility: **public**


#### Arguments
* $classname **mixed**
* $paramname **string|null**
* $strict **boolean**



### provider

    mixed SilexAutowiring\AutowiringService::provider(string $classname, string|null $paramname)

Obtain an instance for a given class.



* Visibility: **public**


#### Arguments
* $classname **string**
* $paramname **string|null**



### wake

    mixed SilexAutowiring\AutowiringService::wake(string $classname)

Trigger the instanciation of a lazy service.



* Visibility: **public**


#### Arguments
* $classname **string**



### provide

    string SilexAutowiring\AutowiringService::provide(string $classname, callable $closure, boolean $factory)

Register a provider for a class using a callable.

The callable receives the Silex application as a parameter.
Services can be injected in the given callable.

* Visibility: **public**


#### Arguments
* $classname **string**
* $closure **callable**
* $factory **boolean**



### invoke

    mixed SilexAutowiring\AutowiringService::invoke(callable $closure, array $args)

Execute a given callable. Services can be injected as part of the parameters.

Extra $args can be supplied.

* Visibility: **public**


#### Arguments
* $closure **callable**
* $args **array**



### partial

    \Closure SilexAutowiring\AutowiringService::partial(callable $closure)

Create a new Closure from a callable.

Services can be injected as part of the parameters.
The resulting closure is a function of the remaining, unresolved arguments.

* Visibility: **public**


#### Arguments
* $closure **callable**



### alias

    mixed SilexAutowiring\AutowiringService::alias(string $alias, string $classname)

Give a class service an alias.



* Visibility: **public**


#### Arguments
* $alias **string**
* $classname **string**



### withClass

    \SilexAutowiring\ClassHelper SilexAutowiring\AutowiringService::withClass(string $classname)

Chain operations for a given class.



* Visibility: **public**


#### Arguments
* $classname **string**


