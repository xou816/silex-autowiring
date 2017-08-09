<?php

namespace SilexAutowiring\Injectable;

use SilexAutowiring\Injectable\InjectableInterface;

class Injectable implements InjectableInterface {

	private $object;

	public function __construct($object) {
		$this->object = $object;
	}

	public function get() {
		return $this->object;
	}

}