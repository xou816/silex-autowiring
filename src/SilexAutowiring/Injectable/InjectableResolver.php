<?php

namespace SilexAutowiring\Injectable;

use Silex\Application;

interface InjectableResolver {

	public function provides(Application $app, $key);
	public function wire(Application $app, $key);
	public function value(Application $app, $key);

}