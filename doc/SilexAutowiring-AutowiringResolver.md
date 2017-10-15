SilexAutowiring\AutowiringResolver
===============






* Class name: AutowiringResolver
* Namespace: SilexAutowiring
* This class implements: Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface






Methods
-------


### __construct

    mixed SilexAutowiring\AutowiringResolver::__construct(\Silex\Application $app)





* Visibility: **public**


#### Arguments
* $app **Silex\Application**



### supports

    mixed SilexAutowiring\AutowiringResolver::supports(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata $argument)





* Visibility: **public**


#### Arguments
* $request **Symfony\Component\HttpFoundation\Request**
* $argument **Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata**



### resolve

    mixed SilexAutowiring\AutowiringResolver::resolve(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata $argument)





* Visibility: **public**


#### Arguments
* $request **Symfony\Component\HttpFoundation\Request**
* $argument **Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata**


