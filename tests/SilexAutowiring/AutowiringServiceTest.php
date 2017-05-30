<?php

use Silex\Application;
use Silex\WebTestCase;
use SilexAutowiring\AutowiringServiceProvider;
use SilexAutowiring\Injectable;
use Symfony\Component\HttpFoundation\Request;

class TestService {
	public function isLoaded() {
		return true;
	}
	public function sayHello() {
		return 'Hello world!';
	}
}

interface ServiceInterface {}

class SimpleService extends TestService {}

class ServiceWithInterface extends TestService implements ServiceInterface {}

class ServiceWithSingleDependency extends TestService {
	public function __construct(SimpleService $dep) {}
}

class ServiceWithManuallyInjectedArgument extends TestService {
	public function __construct($target, SimpleService $dep) {
		$this->target = $target;
	}
	public function sayHello() {
		return 'Hello '.$this->target.'!';
	}
}

class ServiceWithInjectableDependency extends TestService {
	public function __construct(SimpleService $dep, Injectable $injectableService) {
		$this->injected = $injectableService->get();
	}
}

class AutowiringServiceTest extends WebTestCase {

	public function setUp() {
		parent::setUp();
	}

	public function createApplication() {
		$app = new Application();
		$app['debug'] = true;
		unset($app['exception_handler']);
		$app->register(new AutowiringServiceProvider());
		return $app;
	}

	public function testWiringPicksUpDependencies() {
		$name = $this->app['autowiring']->wire(ServiceWithSingleDependency::class);
		$this->app['autowiring']->wire(SimpleService::class);
		$this->assertTrue($this->app[$name]->isLoaded());
	}

	public function testServiceCanBeFoundByClass() {
		$this->app['autowiring']->wire(SimpleService::class);
		$service = $this->app['autowiring']->provider(SimpleService::class);
		$this->assertTrue($service->isLoaded());
	}

	public function testAlreadyRegisteredServiceCanBeInjectedIfProvided() {
		$this->app['simple_service'] = function($app) {
			return new SimpleService();
		};
		$this->app['autowiring']->provide('simple_service');
		$service = $this->app['autowiring']->provider(SimpleService::class);
		$this->assertTrue($service->isLoaded());
	}

	public function testServicesCanBeInjectedInControllers() {
		$this->app['autowiring']->wire(SimpleService::class);
		$this->app->get('/', function(Request $req, SimpleService $service) {
			return $service->sayHello();
		});
		$client = $this->createClient();
		$client->request('GET', '/');
		$this->assertTrue($client->getResponse()->isOk());
		$this->assertEquals($client->getResponse()->getContent(), 'Hello world!');
	}

	public function testServicesCanBeInjectedByInterface() {
		$this->app['autowiring']->wire(ServiceWithInterface::class);
		$service = $this->app['autowiring']->provider(ServiceInterface::class);
		$this->assertTrue($service->isLoaded());
	}

	public function testPlainObjectsCanBeInjectedByTheirContainerName() {
		$this->app['injectable_service'] = ['loaded' => true];
		$this->app['autowiring']->wire(ServiceWithInjectableDependency::class);
		$this->app['autowiring']->wire(SimpleService::class);
		$service = $this->app['autowiring']->provider(ServiceWithInjectableDependency::class);
		$this->assertTrue($service->isLoaded());
		$this->assertTrue($service->injected['loaded']);
	}

	public function testAdditionalArgumentsMayBeSuppliedToWire() {
		$this->app['autowiring']->wire(SimpleService::class);
		$this->app['autowiring']->wire(ServiceWithManuallyInjectedArgument::class, ['foo']);
		$service = $this->app['autowiring']->provider(ServiceWithManuallyInjectedArgument::class);
		$this->assertTrue($service->isLoaded());
		$this->assertEquals($service->sayHello(), 'Hello foo!');
	}

	public function testServicesCanBeInjectedInClosures() {
		$this->app['autowiring']->wire(SimpleService::class);
		$fun = function(SimpleService $service, $arg) {
			return $service->sayHello().' '.$arg;
		};
		$res = $this->app['autowiring']->invoke($fun, ['Hello to you too!']);
		$this->assertEquals($res, 'Hello world! Hello to you too!');
	}

}