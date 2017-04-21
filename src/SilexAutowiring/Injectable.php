<?php

namespace SilexAutowiring;

class Injectable {

	private $object;

	public function __construct($object) {
		$this->object = $object;
	}

	public function get() {
		return $this->object;
	}

}