<?php

namespace SilexAutowiring;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class AutowiringResolver implements ArgumentValueResolverInterface {

	private $app;

	public function __construct(Application $app) {
		$this->app = $app;
	}

    /**
     * @inheritdoc
     */
    public function supports(Request $request, ArgumentMetadata $argument) {
		return $this->app['autowiring']->provides($argument->getType(), $argument->getName());
	}

    /**
     * @inheritdoc
     */
    public function resolve(Request $request, ArgumentMetadata $argument) {
		yield $this->app['autowiring']->provider($argument->getType(), $argument->getName());
	}

}
