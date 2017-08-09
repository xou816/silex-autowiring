<?php

namespace SilexAutowiring\Injectable;

use SilexAutowiring\Injectable\InjectableInterface;

class Configuration implements InjectableInterface, \ArrayAccess {

	private $arr;

	public function __construct(array $arr) {
		$this->arr = $arr;
	}

	public function get() {
		return $this->arr;
	}

	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->arr[] = $value;
		} else {
			$this->arr[$offset] = $value;
		}
	}

	public function offsetExists($offset) {
		return isset($this->arr[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->arr[$offset]);
	}

	public function offsetGet($offset) {
		return isset($this->arr[$offset]) ? $this->arr[$offset] : null;
	}

}