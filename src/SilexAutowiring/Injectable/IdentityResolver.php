<?php

namespace SilexAutowiring\Injectable;

use Silex\Application;
use SilexAutowiring\Injectable\Injectable;
use SilexAutowiring\Injectable\AbstractCompositeKeyResolver;

class IdentityResolver extends AbstractCompositeKeyResolver {

	public function provides(Application $app, $key) {
		return isset($app[$key]) || $this->compositeKeyExists($app, $key);
	}

	public function wire(Application $app, $classname, $key) {
		$hash = substr(sha1($key), 0, 10);
		if (!isset($app[$hash])) {
			$app[$hash] = function($a) use ($key, $classname) {
				return new $classname($this->getCompositeKey($a, $key));
			};
		}
		return $hash;
	}

	public function value(Application $app, $key) {
		return $this->getCompositeKey($key);
	}

}