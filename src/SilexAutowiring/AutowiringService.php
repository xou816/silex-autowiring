<?php

namespace SilexAutowiring;

use Silex\Application;
use SilexAutowiring\ClassHelper;
use SilexAutowiring\Traits\Autowire;
use SilexAutowiring\Traits\Autoconfigure;
use SilexAutowiring\Injectable\Injectable;
use SilexAutowiring\Injectable\InjectableResolver;
use SilexAutowiring\Injectable\CasingShiftResolver;

class AutowiringService {

	private $app;

	public function __construct(Application $app) {
		$this->app = $app;
		$this->wire(CasingShiftResolver::class);
	}

	private function register($classname, $closure) {
		$this->app[$this->name($classname)] = $closure;
		foreach (class_implements($classname) as $interface) {
			$this->app[$this->name($interface)] = $closure;
		}
		return $this->name($classname);
	}

	private function hasTrait($classname, $trait) {
		try {
			$class = new \ReflectionClass($classname);
			return in_array($trait, $class->getTraitNames());
		} catch (\ReflectionException $e) {
			return false;
		}
	}

	private function mapParameters(Application $app, \ReflectionFunctionAbstract $fun, $args) {
		return array_map(function($param) use ($app, $args) {
			$class = $param->getClass();
			if (is_null($class)) {
				return array_shift($args);
			} else {
				return $this->provider($class->name, $param->getName());
			}
		}, $fun->getParameters());
	}

	public function wire($classname, $args = []) {
		$name = null;
		if (method_exists($classname, '__construct')) {
			$ref = new \ReflectionMethod($classname, '__construct');
			$name = $this->register($classname, function($app) use ($classname, $ref, $args) {
				$args = $this->mapParameters($app, $ref, $args);
				$class = new \ReflectionClass($classname);
				return $class->newInstanceArgs($args);
			});
		} else {
			$name = $this->register($classname, function($app) use ($classname) {
				$class = new \ReflectionClass($classname);
				return $class->newInstance();
			});
		}
		if ($this->hasTrait($classname, Autoconfigure::class)) {
			$this->configure($classname);
		}
		return $name;
	}

	public function configure($classname, $root = null) {
		$resolver = $this->provider(InjectableResolver::class);
		$service = $this->provider($classname);
		$ref = new \ReflectionClass($classname);
		try {
			$root = $ref->getProperty('autoconfigure');
			$root->setAccessible(true);
			$root = $root->getValue($service);
		} catch (\ReflectionException $e) {}
		$props = $ref->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PRIVATE);
		foreach ($props as $prop) {
			$prop->setAccessible(true);
			$key = $prop->getName();
			$base = is_null($root) ? $key : $root;
			if ($resolver->provides($this->app, $base)) {
				$value = $resolver->value($this->app, $base);
				if (!is_null($root) && isset($value[$key])) {
					$value = $value[$key];
				}
				$prop->setValue($service, $value);
			}
		}
	}

	public function expose($service_name, $classname = null) {
		$classname = is_null($classname) ? get_class($this->app[$service_name]) : $classname;
		return $this->register($classname, function($app) use ($service_name) {
			return $app[$service_name];
		});
	}

	public function name($classname, $paramname = null) {
		if ($classname == Injectable::class) {
			return $this->provider(InjectableResolver::class)->wire($this->app, $paramname);
		} else {
			return substr(sha1(str_replace('\\', '.', $classname)), 0, 10);
		}
	}

	public function provides($classname, $paramname = null, $strict = false) {
		return isset($this->app[$this->name($classname, $paramname)]) || (!$strict && $this->hasTrait($classname, Autowire::class));
	}

	public function provider($classname, $paramname = null) {
		if ($this->provides($classname, $paramname, true)) {
			return $this->app[$this->name($classname, $paramname)];
		} else if ($this->hasTrait($classname, Autowire::class)) {
			$name = $this->wire($classname);
			return $this->app[$name];
		} else {
			throw new \InvalidArgumentException('no provider for "'.$classname.'"');
		}
	}

	public function provide($classname, $closure) {
		return $this->register($classname, function($app) use ($closure) {
			return $this->invoke($closure, [$app]);
		});
	}

	public function invoke($closure, $args = []) {
		$ref = new \ReflectionFunction($closure);
		$args = $this->mapParameters($this->app, $ref, $args);
		return $ref->invokeArgs($args);
	}

	public function class($classname) {
		return new ClassHelper($this, $classname);
	}

}