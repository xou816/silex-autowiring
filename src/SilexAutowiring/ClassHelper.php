<?php

namespace SilexAutowiring;

class ClassHelper {

	private $auto;
	private $classname;

	public function __construct(AutowiringService $auto, $classname) {
		$this->auto = $auto;
		$this->classname = $classname;
	}

    /**
     * @see AutowiringService::wire
     * @param array $args
     * @return $this
     */
    public function wire(array $args = []) {
		$this->auto->wire($this->classname, $args);
		return $this;
	}

    /**
     * @see AutowiringService::extend
     * @param callable $closure
     */
    public function extend(callable $closure) {
		$this->auto->extend($this->classname, $closure);
	}

    /**
     * @see AutowiringService::factory
     * @param callable $closure
     */
    public function factory(callable $closure) {
		$this->auto->factory($this->classname, $closure);
	}

    /**
     * @see AutowiringService::configure
     * @param null $root
     * @return $this
     */
    public function configure($root = null) {
		$this->auto->configure($this->classname, $root);
		return $this;
	}

    /**
     * @see AutowiringService::name
     * @return string
     */
    public function name() {
		return $this->auto->name($this->classname);
	}

    /**
     * @see AutowiringService::provides
     * @param bool $strict
     * @return bool
     */
    public function provides($strict = false) {
		return $this->auto->provides($this->classname, null, $strict);
	}

    /**
     * @see AutowiringService::provider
     * @return mixed
     */
    public function provider() {
		return $this->auto->provider($this->classname);
	}

    /**
     * @see AutowiringService::provide
     * @param callable $closure
     * @return string
     */
    public function provide(callable $closure) {
		return $this->auto->provide($this->classname, $closure);
	}

    /**
     * @see AutowiringService::alias
     * @param $alias
     */
    public function alias($alias) {
		$this->auto->alias($alias, $this->classname);
	}

}