SilexAutowiring\AutowiringService
===============






* Class name: AutowiringService
* Namespace: SilexAutowiring







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


