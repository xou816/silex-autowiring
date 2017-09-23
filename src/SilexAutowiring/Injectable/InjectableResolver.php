<?php

namespace SilexAutowiring\Injectable;

use Silex\Application;

interface InjectableResolver {

    /**
     * Check if a key can be provided.
     * @param Application $app
     * @param string $key
     * @return bool
     */
    public function provides(Application $app, $key);


    /**
     * Create an application service that provides a key, wrapped in an InjectableInterface.
     * The parameter $classname should be the classname of an InjectableInterface implementation.
     *
     * @param Application $app
     * @param string $classname
     * @param string $key
     * @return string
     */
    public function wire(Application $app, $classname, $key);

    /**
     * Obtain the value of a key.
     *
     * @param Application $app
     * @param string $key
     * @return mixed
     */
    public function value(Application $app, $key);

}