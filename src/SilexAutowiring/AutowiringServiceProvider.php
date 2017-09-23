<?php

namespace SilexAutowiring;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\AppArgumentValueResolver;
use Silex\Application;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\DefaultValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestAttributeValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\VariadicValueResolver;

class AutowiringServiceProvider implements ServiceProviderInterface, BootableProviderInterface {

	public function register(Container $app) {
		$app['autowiring'] = new AutowiringService($app);
		$app['autowiring']->expose('autowiring', AutowiringService::class);
	}

	public function boot(Application $app) {
		$app['argument_value_resolvers'] = function($app) {
			return array(
				new AutowiringResolver($app),
				new AppArgumentValueResolver($app),
				new RequestAttributeValueResolver(),
				new RequestValueResolver(),
				new DefaultValueResolver(),
				new VariadicValueResolver(),
			);
		};
	}

}