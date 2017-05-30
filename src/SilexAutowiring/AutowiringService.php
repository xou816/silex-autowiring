<?php

namespace SilexAutowiring;

use Silex\Application;
use SilexAutowiring\Injectable;

class AutowiringService {

	private $registered = array();
	private $app;

	public function __construct(Application $app) {
		$this->app = $app;
	}

	private function register($classname, $closure) {
		$this->registered[] = $classname;
		$this->app[$this->name($classname)] = $closure;
		foreach (class_implements($classname) as $interface) {
			$this->registered[] = $interface;
			$this->app[$this->name($interface)] = $closure;
		}
		return $this->name($classname);
	}

	private function deps($classname) {
		if (!method_exists($classname, '__construct')) {
			return array();
		} else {
			$ref = new \ReflectionMethod($classname, '__construct');
			$deps = $ref->getParameters();
			$deps = array_map(function($dep) {
				return $this->name($dep->getClass()->name, $dep->getName());
			}, $deps);
			return $deps;
		}
	}

	public function wire($classname) {
		$deps = $this->deps($classname);
		return $this->register($classname, function($app) use ($classname, $deps) {
			$deps = array_map(function($dep) use ($app) {
				return $app[$dep];
			}, $deps);
			$reflect = new \ReflectionClass($classname);
			return $reflect->newInstanceArgs($deps);
		});
	}

	public function provide($service_name) {
		$classname = get_class($this->app[$service_name]);
		return $this->register($classname, function($app) use ($service_name) {
			return $app[$service_name];
		});
	}

	public function name($classname, $paramname = null) {
		if ($classname == Injectable::class) {
			$converted = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $paramname));
			$hash = sha1($converted);
			$this->app[$hash] = function($app) use ($converted) {
				return new Injectable($app[$converted]);
			};
			return $hash;
		} else {
			return sha1(str_replace('\\', '.', $classname));
		}
	}

	public function isProvided($classname) {
		return in_array($classname, $this->registered);
	}

	public function provider($classname) {
		return $this->app[$this->name($classname)];
	}

	public function invoke($anonymous, $args) {
		$ref = new \ReflectionFunction($anonymous);
		$args = array_map(function($param) use ($args) {
			$class = $param->getClass();
			if (is_null($class)) {
				return array_shift($args);
			} else {
				return $this->app[$this->name($class->name, $param->getName())];
			}
		}, $ref->getParameters());
		return $ref->invokeArgs($args);
	}

}