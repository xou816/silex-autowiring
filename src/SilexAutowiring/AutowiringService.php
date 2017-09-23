<?php

namespace SilexAutowiring;

use Silex\Application;
use SilexAutowiring\Traits\Autowire;
use SilexAutowiring\Traits\Autoconfigure;
use SilexAutowiring\Injectable\InjectableInterface;
use SilexAutowiring\Injectable\InjectableResolver;
use SilexAutowiring\Injectable\CasingShiftResolver;

class AutowiringService {

	private $app;
	private $status = [];
	private $debug = false;

	public function __construct(Application $app) {
		$this->app = $app;
		$this->wire(CasingShiftResolver::class);
	}

    /**
     * Turn on debug mode
     */
    public function debug() {
		$this->debug = true;
	}

    /**
     * Get debug info as an array mapping classnames to booleans (true means class service is up)
     * @return array
     */
    public function getDebugInfo() {
		return $this->status;
	}

    /**
     * @param string $classname
     */
    private function debugRegistration($classname) {
		$this->status[$classname] = false;
		$this->extend($classname, function($service, Application $app) use ($classname) {
			$this->status[$classname] = true;
			return $service;
		});
	}

    /**
     * @param string $classname
     * @param callable $closure
     * @return string
     */
    private function register($classname, callable $closure) {
		$this->app[$this->name($classname)] = $closure;
		foreach (class_implements($classname) as $interface) {
			$this->alias($this->name($interface), $classname);
		}
		if ($this->debug) $this->debugRegistration($classname);
		return $this->name($classname);
	}

    /**
     * @param string $classname
     * @param string $trait
     * @return bool
     */
    private function hasTrait($classname, $trait) {
		try {
			$class = new \ReflectionClass($classname);
			return in_array($trait, $class->getTraitNames());
		} catch (\ReflectionException $e) {
			return false;
		}
	}

    /**
     * @param Application $app
     * @param \ReflectionFunctionAbstract $fun
     * @param array $args
     * @return array
     */
    private function mapParameters(Application $app, \ReflectionFunctionAbstract $fun, array $args) {
		return array_reduce($fun->getParameters(), function($args, \ReflectionParameter $param) use ($app) {
			$class = $param->getClass();
			if (is_null($class)) {
				$args[] = array_shift($args);
			} else {
				try {
					$args[] = $this->provider($class->name, $param->getName());
				} catch (\InvalidArgumentException $e) {
					$args[] = array_shift($args);
				}
			}
			return $args;
		}, $args);
	}

    /**
     * @param string $classname
     * @return bool
     */
    private function isInjectable($classname) {
		try {
			$ref = new \ReflectionClass($classname);
			return $ref->implementsInterface(InjectableInterface::class);
		} catch (\ReflectionException $e) {
			return false;
		}
	}

    /**
     * @param string $root
     * @param \ReflectionProperty[] $props
     * @return \Closure
     */
    private function configureWithRoot($root, $props) {
		return function($service, Application $app) use ($root, $props) {
			$resolver = $this->provider(InjectableResolver::class);
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

    /**
     * @param \ReflectionProperty[] $props
     * @return \Closure
     */
    private function configureNoRoot($props) {
		return function($service, Application $app) use ($props) {
			$resolver = $this->provider(InjectableResolver::class);
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

    /**
     * Creates a new service, which returns an instance of the given class.
     * Dependencies of the service are resolved automatically, as long as they have been exposed in one way or another to the AutowiringService
     * Extra arguments may be passed. They will be used whenever an argument could not be resolved by the AutowiringService.
     *
     * @param string $classname
     * @param array $args
     * @return string the name of the service
     */
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
				return new $classname();
			});
		}
		if ($this->hasTrait($classname, Autoconfigure::class)) {
			$this->configure($classname);
		}
		return $name;
	}

    /**
     * Extend a class provider using a callable.
     * The callable should return an instance of the class.
     * @see Application::extend
     *
     * @param $classname
     * @param callable $closure
     * @return callable
     */
    public function extend($classname, callable $closure) {
		return $this->app->extend($this->name($classname), $closure);
	}

    /**
     * Defines a factory service for a class.
     * The callable should return an instance of the class.
     * @see Application::factory
     *
     * @param $classname
     * @param callable $closure
     * @return callable
     */
    public function factory($classname, callable $closure) {
        return $this->register($this->name($classname), $this->app->factory($this->partial($closure)));
	}

    /**
     * Configure a class available to the AutowiringService.
     * Possible values for class properties are searched in the Silex container.
     * If a $root is specified, then search for values will start with this root name.
     * If a static `autoconfigure` property exists, it will be used as the root search name.
     *
     * @param string $classname
     * @param string|null $root
     */
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

    /**
     * Expose a Silex service to the AutowiringService. A new service is created.
     * An exposed service can then be used as a dependency, and injected by class or interface.
     *
     * @param string $service_name
     * @param string|null $classname
     * @return string the name of the new service
     */
    public function expose($service_name, $classname = null) {
		$classname = is_null($classname) ? get_class($this->app[$service_name]) : $classname;
		return $this->register($classname, function($app) use ($service_name) {
			return $app[$service_name];
		});
	}

    /**
     * Obtain the service name (container offset) of a class service.
     * The service might not exist yet!
     *
     * @param string $classname
     * @param string|null $paramname
     * @return string the name of the service
     */
    public function name($classname, $paramname = null) {
		if ($this->isInjectable($classname)) {
			return $this->provider(InjectableResolver::class)->wire($this->app, $classname, $paramname);
		} else {
			return substr(sha1(str_replace('\\', '.', $classname)), 0, 10);
		}
	}

    /**
     * Check if a service provides a class.
     * If $strict is true, do not attempt to find classes with Autowire trait.
     *
     * @param $classname
     * @param string|null $paramname
     * @param bool $strict
     * @return bool
     */
    public function provides($classname, $paramname = null, $strict = false) {
		return isset($this->app[$this->name($classname, $paramname)]) || (!$strict && $this->hasTrait($classname, Autowire::class));
	}

    /**
     * Obtain an instance for a given class.
     *
     * @param string $classname
     * @param string|null $paramname
     * @return mixed a service
     */
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

    /**
     * Register a provider for a class using a callable.
     * The callable receives the Silex application as a parameter.
     * Services can be injected in the given callable.
     * @see AutowiringService::partial
     *
     * @param string $classname
     * @param callable $closure
     * @return string the name of the created service
     */
    public function provide($classname, callable $closure) {
		return $this->register($classname, $this->partial($closure));
	}

    /**
     * Execute a given callable. Services can be injected as part of the parameters.
     * Extra $args can be supplied.
     * @see AutowiringService::wire
     *
     * @param callable $closure
     * @param array $args
     * @return mixed
     */
    public function invoke(callable $closure, $args = []) {
		$ref = new \ReflectionFunction($closure);
		$args = $this->mapParameters($this->app, $ref, $args);
		return $ref->invokeArgs($args);
	}

    /**
     * Create a new Closure from a callable.
     * Services can be injected as part of the parameters.
     * The resulting closure is a function of the remaining, unresolved arguments.
     *
     * @param callable $closure
     * @return \Closure
     */
    public function partial(callable $closure) {
		return function() use ($closure) {
			$args = func_get_args();
			return $this->invoke($closure, $args);
		};
	}

    /**
     * Give a class service an alias.
     *
     * @param string $alias
     * @param string $classname
     */
    public function alias($alias, $classname) {
		$this->app[$alias] = function($app) use ($classname) {
			return $app[$this->name($classname)];
		};
	}

    /**
     * Chain operations for a given class.
     *
     * @param string $classname
     * @return ClassHelper
     */
    public function withClass($classname) {
		return new ClassHelper($this, $classname);
	}

}