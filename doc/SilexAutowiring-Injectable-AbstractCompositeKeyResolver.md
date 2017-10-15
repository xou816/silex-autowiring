SilexAutowiring\Injectable\AbstractCompositeKeyResolver
===============






* Class name: AbstractCompositeKeyResolver
* Namespace: SilexAutowiring\Injectable
* This is an **abstract** class
* This class implements: [SilexAutowiring\Injectable\InjectableResolver](SilexAutowiring-Injectable-InjectableResolver.md)






Methods
-------


### provides

    boolean SilexAutowiring\Injectable\InjectableResolver::provides(\Silex\Application $app, string $key)

Check if a key can be provided.



* Visibility: **public**
* This method is defined by [SilexAutowiring\Injectable\InjectableResolver](SilexAutowiring-Injectable-InjectableResolver.md)


#### Arguments
* $app **Silex\Application**
* $key **string**



### wire

    string SilexAutowiring\Injectable\InjectableResolver::wire(\Silex\Application $app, string $classname, string $key)

Create an application service that provides a key, wrapped in an InjectableInterface.

The parameter $classname should be the classname of an InjectableInterface implementation.

* Visibility: **public**
* This method is defined by [SilexAutowiring\Injectable\InjectableResolver](SilexAutowiring-Injectable-InjectableResolver.md)


#### Arguments
* $app **Silex\Application**
* $classname **string**
* $key **string**



### value

    mixed SilexAutowiring\Injectable\InjectableResolver::value(\Silex\Application $app, string $key)

Obtain the value of a key.



* Visibility: **public**
* This method is defined by [SilexAutowiring\Injectable\InjectableResolver](SilexAutowiring-Injectable-InjectableResolver.md)


#### Arguments
* $app **Silex\Application**
* $key **string**


