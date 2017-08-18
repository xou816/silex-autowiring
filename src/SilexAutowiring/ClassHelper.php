<?php

namespace SilexAutowiring;

use SilexAutowiring\AutowiringService;

class ClassHelper {

	private $auto;
	private $classname;

	public function __construct(AutowiringService $auto, $classname) {
		$this->auto = $auto;
		$this->classname = $classname;
	}

	public function wire($args = []) {
		$this->auto->wire($this->classname, $args);
		return $this;
	}

	public function configure($root = null) {
		$this->auto->configure($this->classname, $root);
		return $this;
	}

	public function name() {
		return $this->auto->name($this->classname);
	}

	public function provides($strict = false) {
		return $this->auto->provides($this->classname, null, $strict);
	}

	public function alias($alias) {
		return $this->auto->alias($alias, $this->classname);
	}

}