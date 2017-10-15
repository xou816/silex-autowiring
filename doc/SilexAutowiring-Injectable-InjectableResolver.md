SilexAutowiring\Injectable\InjectableResolver
===============






* Interface name: InjectableResolver
* Namespace: SilexAutowiring\Injectable
* This is an **interface**






Methods
-------


### provides

    boolean SilexAutowiring\Injectable\InjectableResolver::provides(\Silex\Application $app, string $key)

Check if a key can be provided.



* Visibility: **public**


#### Arguments
* $app **Silex\Application**
* $key **string**



### wire

    string SilexAutowiring\Injectable\InjectableResolver::wire(\Silex\Application $app, string $classname, string $key)

Create an application service that provides a key, wrapped in an InjectableInterface.

The parameter $classname should be the classname of an InjectableInterface implementation.

* Visibility: **public**


#### Arguments
* $app **Silex\Application**
* $classname **string**
* $key **string**



### value

    mixed SilexAutowiring\Injectable\InjectableResolver::value(\Silex\Application $app, string $key)

Obtain the value of a key.



* Visibility: **public**


#### Arguments
* $app **Silex\Application**
* $key **string**


