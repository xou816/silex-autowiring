SilexAutowiring\Injectable\Configuration
===============






* Class name: Configuration
* Namespace: SilexAutowiring\Injectable
* This class implements: [SilexAutowiring\Injectable\InjectableInterface](SilexAutowiring-Injectable-InjectableInterface.md), ArrayAccess




Properties
----------


### $arr

    private mixed $arr





* Visibility: **private**


Methods
-------


### __construct

    mixed SilexAutowiring\Injectable\Configuration::__construct(array $arr)





* Visibility: **public**


#### Arguments
* $arr **array**



### get

    mixed SilexAutowiring\Injectable\InjectableInterface::get()

Obtain the wrapped service.



* Visibility: **public**
* This method is defined by [SilexAutowiring\Injectable\InjectableInterface](SilexAutowiring-Injectable-InjectableInterface.md)




### offsetSet

    mixed SilexAutowiring\Injectable\Configuration::offsetSet($offset, $value)





* Visibility: **public**


#### Arguments
* $offset **mixed**
* $value **mixed**



### offsetExists

    mixed SilexAutowiring\Injectable\Configuration::offsetExists($offset)





* Visibility: **public**


#### Arguments
* $offset **mixed**



### offsetUnset

    mixed SilexAutowiring\Injectable\Configuration::offsetUnset($offset)





* Visibility: **public**


#### Arguments
* $offset **mixed**



### offsetGet

    mixed SilexAutowiring\Injectable\Configuration::offsetGet($offset)





* Visibility: **public**


#### Arguments
* $offset **mixed**


