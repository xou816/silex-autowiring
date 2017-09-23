<?php

namespace SilexAutowiring\Injectable;

use Silex\Application;

abstract class AbstractCompositeKeyResolver implements InjectableResolver {

	protected function getCompositeKey(Application $app, $key) {
		$tags = $app->keys();
		$composite = [];
		foreach ($tags as $tag) {
			if (substr($tag, 0, strlen($key)) === $key && strpos($tag, '.') > 0) {
				$exploded = explode('.', $tag);
				$composite_key = $exploded[1];
				$frags = array_reverse(array_slice($exploded, 2));
				$at_key = array_reduce($frags, function($acc, $frag) {
					return [$frag => $acc];
				}, $app[$tag]);
				if (isset($composite[$composite_key])) {
					$composite[$composite_key] = array_merge_recursive($composite[$composite_key], $at_key);
				} else {
					$composite[$composite_key] = $at_key;
				}
			}
		}
		return (count($composite) === 0) ? $app[$key] : $composite;
	}

	protected function compositeKeyExists(Application $app, $key) {
		return array_filter($app->keys(), function($otherkey) use ($key) {
			return explode('.', $otherkey)[0] === $key;
		});
	}

	abstract public function provides(Application $app, $key);
	abstract public function wire(Application $app, $classname, $key);
	abstract public function value(Application $app, $key);

}