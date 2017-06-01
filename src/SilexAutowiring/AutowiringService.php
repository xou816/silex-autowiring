<?php

namespace SilexAutowiring;

use Silex\Application;
use SilexAutowiring\Injectable;

class AutowiringService {

	private $app;

	public function __construct(Application $app) {
		$this->app = $app;
	}

	private function register($classname, $closure) {
		$this->app[$this->name($classname)] = $closure;
		foreach (class_implements($classname) as $interface) {
			$this->app[$this->name($interface)] = $closure;
		}
		return $this->name($classname);
	}

	private function mapParameters(Application $app, \ReflectionFunctionAbstract $fun, $args) {
		return array_map(function($param) use ($app, $args) {
			$class = $param->getClass();
			if (is_null($class)) {
				return array_shift($args);
			} else {
				return $app[$this->name($class->name, $param->getName())];
			}
		}, $fun->getParameters());
	}

	public function wire($classname, $args = []) {
		if (method_exists($classname, '__construct')) {
			$ref = new \ReflectionMethod($classname, '__construct');
			return $this->register($classname, function($app) use ($classname, $ref, $args) {
				$args = $this->mapParameters($app, $ref, $args);
				$class = new \ReflectionClass($classname);
				return $class->newInstanceArgs($args);
			});
		} else {
			return $this->register($classname, function($app) use ($classname) {
				$class = new \ReflectionClass($classname);
				return $class->newInstance();
			});
		}
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
			if (!isset($this->app[$hash])) {
				$this->app[$hash] = function($app) use ($converted) {
					return new Injectable($app[$converted]);
				};
			}
			return $hash;
		} else {
			return sha1(str_replace('\\', '.', $classname));
		}
	}

	public function provides($classname) {
		return isset($this->app[$this->name($classname)]);
	}

	public function provider($classname) {
		return $this->app[$this->name($classname)];
	}

	public function invoke($anonymous, $args = []) {
		$ref = new \ReflectionFunction($anonymous);
		$args = $this->mapParameters($this->app, $ref, $args);
		return $ref->invokeArgs($args);
	}

}