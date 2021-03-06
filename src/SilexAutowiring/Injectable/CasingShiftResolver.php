<?php

namespace SilexAutowiring\Injectable;

use Silex\Application;

class CasingShiftResolver extends AbstractCompositeKeyResolver {

	protected function convert($key) {
		$next = explode('.', $key);
		$raw = array_shift($next);
		$converted = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $raw));
		return implode('.', [$converted] + $next);
	}

	public function provides(Application $app, $key) {
		$key = $this->convert($key);
		return isset($app[$key]) || $this->compositeKeyExists($app, $key);
	}

	public function wire(Application $app, $classname, $key) {
		$key = $this->convert($key);
		$hash = substr(sha1($key), 0, 10);
		if (!isset($app[$hash])) {
			$app[$hash] = function($a) use ($key, $classname) {
				return new $classname($this->getCompositeKey($a, $key));
			};
		}
		return $hash;
	}

	public function value(Application $app, $key) {
		$key = $this->convert($key);
		return $this->getCompositeKey($app, $key);
	}

}