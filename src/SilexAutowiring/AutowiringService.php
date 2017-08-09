<?php

namespace SilexAutowiring;

use Silex\Application;
use SilexAutowiring\ClassHelper;
use SilexAutowiring\Traits\Autowire;
use SilexAutowiring\Traits\Autoconfigure;
use SilexAutowiring\Injectable\InjectableInterface;
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

	private function isInjectable($classname) {
		try {
			$ref = new \ReflectionClass($classname);
			return $ref->implementsInterface(InjectableInterface::class);
		} catch (\ReflectionException $e) {
			return false;
		}
	}

	private function configureWithRoot($root, $props) {
		return function($service, Application $app) use ($root, $props) {
			$resolver = $app['autowiring']->provider(InjectableResolver::class);
			if ($resolver->provides($app, $root)) {
				$arr = $resolver->value($app, $root);
				foreach ($props as $prop) {
					$prop->setAccessible(true);
					$key = $prop->getName();
					if (isset($arr[$key])) {
						$prop->setValue($service, $arr[$key]);
					}
				}
			}
			return $service;
		};
	}

	private function configureNoRoot($props) {
		return function($service, Application $app) use ($props) {
			$resolver = $app['autowiring']->provider(InjectableResolver::class);
			foreach ($props as $prop) {
				$prop->setAccessible(true);
				$key = $prop->getName();
				if ($resolver->provides($app, $key)) {
					$value = $resolver->value($app, $key);
					$prop->setValue($service, $value);
				}
			}
			return $service;
		};
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

	public function extend($classname, $closure) {
		$this->app->extend($this->name($classname), $closure);
	}

	public function configure($classname, $root = null) {
		$ref = new \ReflectionClass($classname);
		foreach ($ref->getProperties(\ReflectionProperty::IS_STATIC) as $prop) {
			if ($prop->getName() === 'autoconfigure') {
				$prop->setAccessible(true);
				$root = $prop->getValue();
				break;
			}
		}
		$props = $ref->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PRIVATE & !\ReflectionProperty::IS_STATIC);
		$closure = is_null($root) ? $this->configureNoRoot($props) : $this->configureWithRoot($root, $props);
		$this->extend($classname, $closure);
	}

	public function expose($service_name, $classname = null) {
		$classname = is_null($classname) ? get_class($this->app[$service_name]) : $classname;
		return $this->register($classname, function($app) use ($service_name) {
			return $app[$service_name];
		});
	}

	public function name($classname, $paramname = null) {
		if ($this->isInjectable($classname)) {
			return $this->provider(InjectableResolver::class)->wire($this->app, $classname, $paramname);
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