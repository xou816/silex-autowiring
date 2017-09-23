<?php

use Silex\Application;
use Silex\WebTestCase;
use SilexAutowiring\AutowiringService;
use SilexAutowiring\AutowiringServiceProvider;
use SilexAutowiring\Traits\Autowire;
use SilexAutowiring\Traits\Autoconfigure;
use SilexAutowiring\Injectable\Injectable;
use SilexAutowiring\Injectable\Configuration;
use SilexAutowiring\Injectable\IdentityResolver;
use Symfony\Component\HttpFoundation\Request;

class TestService {
	public function isAvailable() {
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

class Foo {}

class ServiceWithManuallyInjectedArgument extends TestService {
    private $target;
    public function __construct($target, SimpleService $dep, Foo $foo) {
		$this->target = $target;
	}
	public function sayHello() {
		return 'Hello '.$this->target.'!';
	}
}

class ServiceWithInjectableDependency extends TestService {
    public $injected;
    public function __construct(SimpleService $dep, Injectable $injectableService) {
		$this->injected = $injectableService->get();
	}
}

class AutowiredService extends TestService {
	use Autowire;
	public function __construct(SimpleService $dep) {}
}

class AutoconfiguredService extends TestService {
	use Autoconfigure;
	private static $autoconfigure = 'myservice';
	public $config;
}

class ServiceWithConfig extends TestService {
	public $host;
	public $port = '80';
	public $root;
	public $urls;
	public $unset;
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

    /**
     * @return AutowiringService
     */
    private function auto() {
		return $this->app['autowiring'];
	}

	public function testWiringPicksUpDependencies() {
		$name = $this->auto()->wire(ServiceWithSingleDependency::class);
		$this->auto()->wire(SimpleService::class);
		$this->assertTrue($this->app[$name]->isAvailable());
	}

	public function testServiceCanBeFoundByClass() {
		$this->auto()->wire(SimpleService::class);
		$service = $this->auto()->provider(SimpleService::class);
		$this->assertTrue($service->isAvailable());
	}

	public function testAlreadyRegisteredServiceCanBeInjectedIfExposed() {
		$this->app['simple_service'] = function($app) {
			return new SimpleService();
		};
		$this->auto()->expose('simple_service');
		$service = $this->auto()->provider(SimpleService::class);
		$this->assertTrue($service->isAvailable());
	}

	public function testCustomProvidersCanBeWritten() {
		$this->auto()->wire(SimpleService::class);
		$name = $this->auto()->provide(ServiceWithSingleDependency::class, function($app, SimpleService $s) {
			return new ServiceWithSingleDependency($s);
		});
		$this->assertTrue($this->app[$name]->isAvailable());
	}

	public function testServicesCanBeInjectedInControllers() {
		$this->app['injectable_service'] = ['available' => true];
		$this->auto()->wire(SimpleService::class);
		$this->app->get('/{arg}', function(Request $req, SimpleService $service, Injectable $injectableService, $arg) {
			$this->assertTrue($injectableService->get()['available']);
			return $service->sayHello();
		});
		$client = $this->createClient();
		$client->request('GET', '/foo');
		$this->assertTrue($client->getResponse()->isOk());
		$this->assertEquals($client->getResponse()->getContent(), 'Hello world!');
	}

	public function testServicesCanBeInjectedByInterface() {
		$this->auto()->wire(ServiceWithInterface::class);
		$service = $this->auto()->provider(ServiceInterface::class);
		$this->assertTrue($service->isAvailable());
	}

	public function testPlainObjectsCanBeInjectedByTheirContainerName() {
		$this->app['injectable_service'] = ['available' => true];
		$this->auto()->wire(ServiceWithInjectableDependency::class);
		$this->auto()->wire(SimpleService::class);
		$service = $this->auto()->provider(ServiceWithInjectableDependency::class);
		$this->assertTrue($service->isAvailable());
		$this->assertTrue($service->injected['available']);
	}

	public function testAdditionalArgumentsMayBeSuppliedToWire() {
		$this->auto()->wire(SimpleService::class);
		$this->auto()->wire(ServiceWithManuallyInjectedArgument::class, ['foo', new Foo()]);
		$service = $this->auto()->provider(ServiceWithManuallyInjectedArgument::class);
		$this->assertTrue($service->isAvailable());
		$this->assertEquals($service->sayHello(), 'Hello foo!');
	}

	public function testServicesCanBeInjectedInClosures() {
		$this->app['injectable_service'] = ['available' => true];
		$this->auto()->wire(SimpleService::class);
		$fun = function(SimpleService $service, Injectable $injectableService, $arg) {
			return $service->sayHello().' '.$arg;
		};
		$res = $this->auto()->invoke($fun, ['Hello to you too!']);
		$partial = $this->auto()->partial($fun);
		$this->assertEquals($res, 'Hello world! Hello to you too!');
		$this->assertEquals($partial('Hello to you too!'), 'Hello world! Hello to you too!');
	}

	public function testInjectableBehaviourCanBeTweaked() {
		$this->auto()->wire(IdentityResolver::class);
		$this->app['injectable_service'] = ['available' => true];
		$fun = function(Injectable $injectable_service) {
			return $injectable_service->get()['available'];
		};
		$res = $this->auto()->invoke($fun);
		$this->assertEquals($res,  true);
	}

	public function testServicesCanBeConfigured() {
		$app = $this->app;
		$app['host'] = 'localhost';
		$app['port'] = '443';
		$app['root'] = '/';
		$app['urls.home'] = '/home';
		$app['url.account'] = ['details' => 'ignore this!'];
		$app['urls.account.details'] = '/account';
		$app['urls.account.logout'] = '/logout';
		$name = $this->auto()
			->withClass(ServiceWithConfig::class)
			->wire()
			->configure()
			->name();
		$service = $app[$name];
		$this->assertEquals($service->host, 'localhost');
		$this->assertEquals($service->port, '443');
		$this->assertEquals($service->root, '/');
		$this->assertEquals($service->urls['home'], '/home');
		$this->assertEquals($service->urls['account']['details'], '/account');
		$this->assertEquals($service->urls['account']['logout'], '/logout');
		$this->assertEquals($service->unset, null);
	}

	public function testServicesCanBeConfiguredWithRootName() {
		$app = $this->app;
		$app['myservice.host'] = 'localhost';
		$app['myservice.port'] = '443';
		$app['myservice.root'] = '/';
		$app['myservice.urls.home'] = '/home';
		$app['myservice.url.account'] = ['details' => 'ignore this!'];
		$app['myservice.urls.account.details'] = '/account';
		$app['myservice.urls.account.logout'] = '/logout';
		$service = $this->auto()
			->withClass(ServiceWithConfig::class)
			->wire()
			->configure('myservice')
			->provider();
		$this->assertEquals($service->host, 'localhost');
		$this->assertEquals($service->port, '443');
		$this->assertEquals($service->root, '/');
		$this->assertEquals($service->urls['home'], '/home');
		$this->assertEquals($service->urls['account']['details'], '/account');
		$this->assertEquals($service->urls['account']['logout'], '/logout');
		$this->assertEquals($service->unset, null);
	}

	public function testServicesCanBeWiredWithATrait() {
		$this->auto()->wire(SimpleService::class);
		$this->assertTrue($this->auto()->provider(AutowiredService::class)->isAvailable());
	}

	public function testServicesCanBeConfiguredWithATrait() {
		$this->app['myservice.config'] = ['key' => 'value'];
		$this->auto()->wire(AutoconfiguredService::class);
		$this->assertEquals('value', $this->auto()->provider(AutoconfiguredService::class)->config['key']);
	}

	public function testInjectableConfigurationIsUsableAsArray() {
		$app = $this->app;
		$app['myservice.host'] = 'localhost';
		$app['myservice.port'] = '443';
		$app['myservice.root'] = '/';
		$app['myservice.urls.home'] = '/home';
		$app['myservice.url.account'] = ['details' => 'ignore this!'];
		$app['myservice.urls.account.details'] = '/account';
		$app['myservice.urls.account.logout'] = '/logout';
		$myservice = $this->auto()->provider(Configuration::class, 'myservice');
		$this->assertEquals($myservice['host'], 'localhost');
		$this->assertEquals($myservice['port'], '443');
		$this->assertEquals($myservice['root'], '/');
		$this->assertEquals($myservice['urls']['home'], '/home');
		$this->assertEquals($myservice['urls']['account']['details'], '/account');
		$this->assertEquals($myservice['urls']['account']['logout'], '/logout');
		$this->assertFalse(isset($myservice['unset']));
	}

	public function testAutowiringServiceIsAvailable() {
		$this->assertTrue($this->auto()->provides(AutowiringService::class));
	}

	public function testDebugInformsAboutUsedServices() {
		$this->auto()->debug();
		$this->auto()->wire(ServiceWithConfig::class);
		$this->auto()->wire(SimpleService::class);
		$this->auto()->wire(ServiceWithSingleDependency::class);
		$this->app->get('/', function(AutowiringService $auto, ServiceWithSingleDependency $s) {
			return json_encode($auto->getDebugInfo());
		});
		$client = $this->createClient();
		$client->request('GET', '/');
		$payload = json_decode($client->getResponse()->getContent(), true);
		$this->assertFalse($payload[ServiceWithConfig::class]);
		$this->assertTrue($payload[SimpleService::class]);
		$this->assertTrue($payload[ServiceWithSingleDependency::class]);
	}

	public function testServicesCanBeAliased() {
		$this->auto()->withClass(SimpleService::class)
			->wire()
			->alias('myalias');
		$this->assertEquals($this->app['myalias']->sayHello(), 'Hello world!');
	}

}