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

	private function mapParameters(\ReflectionFunctionAbstract $fun, $args) {
		return array_map(function($param) use ($args) {
			$class = $param->getClass();
			if (is_null($class)) {
				return array_shift($args);
			} else {
				return $this->app[$this->name($class->name, $param->getName())];
			}
		}, $fun->getParameters());
	}

	public function wire($classname, $args = []) {
		if (method_exists($classname, '__construct')) {
			$ref = new \ReflectionMethod($classname, '__construct');
			$args = $this->mapParameters($ref, $args);
		}
		return $this->register($classname, function($app) use ($classname, $args) {
			$class = new \ReflectionClass($classname);
			return $class->newInstanceArgs($args);
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

	public function invoke($anonymous, $args = []) {
		$ref = new \ReflectionFunction($anonymous);
		$args = $this->mapParameters($ref, $args);
		return $ref->invokeArgs($args);
	}

}