SilexAutowiring\ClassHelper
===============






* Class name: ClassHelper
* Namespace: SilexAutowiring





Properties
----------


### $auto

    private mixed $auto





* Visibility: **private**


### $classname

    private mixed $classname





* Visibility: **private**


Methods
-------


### __construct

    mixed SilexAutowiring\ClassHelper::__construct(\SilexAutowiring\AutowiringService $auto, $classname)





* Visibility: **public**


#### Arguments
* $auto **[SilexAutowiring\AutowiringService](SilexAutowiring-AutowiringService.md)**
* $classname **mixed**



### wire

    \SilexAutowiring\ClassHelper SilexAutowiring\ClassHelper::wire(array $args, boolean $factory)





* Visibility: **public**


#### Arguments
* $args **array**
* $factory **boolean**



### extend

    mixed SilexAutowiring\ClassHelper::extend(callable $closure)





* Visibility: **public**


#### Arguments
* $closure **callable**



### configure

    \SilexAutowiring\ClassHelper SilexAutowiring\ClassHelper::configure(null $root)





* Visibility: **public**


#### Arguments
* $root **null**



### name

    string SilexAutowiring\ClassHelper::name()





* Visibility: **public**




### provides

    boolean SilexAutowiring\ClassHelper::provides(boolean $strict)





* Visibility: **public**


#### Arguments
* $strict **boolean**



### provider

    mixed SilexAutowiring\ClassHelper::provider()





* Visibility: **public**




### wake

    mixed SilexAutowiring\ClassHelper::wake()





* Visibility: **public**




### provide

    string SilexAutowiring\ClassHelper::provide(callable $closure, boolean $factory)





* Visibility: **public**


#### Arguments
* $closure **callable**
* $factory **boolean**



### alias

    mixed SilexAutowiring\ClassHelper::alias($alias)





* Visibility: **public**


#### Arguments
* $alias **mixed**


