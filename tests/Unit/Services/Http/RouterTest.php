<?php

use Orkestra\Providers\HttpProvider;
use Orkestra\Services\Http\Interfaces\RouteAwareInterface;
use Orkestra\Services\Http\Interfaces\RouteGroupInterface;
use Orkestra\Services\Http\Interfaces\RouteInterface;
use Orkestra\Services\Http\Interfaces\RouterInterface;
use Orkestra\Services\Http\RouteGroup;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

beforeEach(function () {
	app()->provider(HttpProvider::class);
});

test('can map a route', function () {
	$router = app()->get(RouterInterface::class);
	$route = $router->map('GET', '/', fn () => 'test');

	expect($route->getPath())->toBe('/');

	$callable = $route->getCallable();
	expect($callable())->toBe('test');
});

test('can group routes', function () {
	$router = app()->get(RouterInterface::class);
	$group = $router->group('/api', fn () => null);

	expect($group)->toBeInstanceOf(RouteGroup::class);
	expect($group->getPrefix())->toBe('/api');
});

test('can get routes', function () {
	$router = app()->get(RouterInterface::class);
	$router->map('GET', '/', fn () => 'test');

	$routes = $router->getRoutes();

	expect($routes)->toHaveCount(1);

	$route = $routes[0];
	expect($route->getPath())->toBe('/');

	$callable = $route->getCallable();
	expect($callable())->toBe('test');
});

test('can get routes by definition type', function () {
	$router = app()->get(RouterInterface::class);

	$router->map('GET', '/', fn () => 'test web')->setDefinition(['type' => 'web']);
	$router->map('GET', '/api/login', fn () => 'test api login')->setDefinition(['type' => 'api']);
	$router->group('/api/v1', function (RouteGroupInterface $group) {
		$group->map('GET', '/test', fn () => 'test api v1 test');
	})->setDefinition(['type' => 'api']);

	$web = $router->getRoutesByDefinitionType('web');
	expect($web)->toHaveCount(1);
	expect($web[0]->getPath())->toBe('/');
	expect($web[0]->getCallable()())->toBe('test web');

	$group = $router->getRoutesByDefinitionType('api');
	expect($group)->toHaveCount(2);
	expect($group[0]->getPath())->toBe('/api/login');
	expect($group[0]->getCallable()())->toBe('test api login');
	expect($group[1]->getPath())->toBe('/api/v1/test');
	expect($group[1]->getCallable()())->toBe('test api v1 test');
});

test('can dispatch a request', function () {
	$router = app()->get(RouterInterface::class);
	$router->map('GET', '/', fn () => 'test');
	$router->map('GET', '/test', fn () => ['test' => 'test']);

	$request = app()->get(ServerRequestInterface::class);

	$request = $request->withUri($request->getUri()->withPath('/'));
	$response = $router->dispatch($request);
	expect((string) $response->getBody())->toBe('test');

	$request = $request->withUri($request->getUri()->withPath('/test'));
	$response = $router->dispatch($request);
	expect((string) $response->getBody())->toBe('{"test":"test"}');
	expect($response->getHeaderLine('content-type'))->toBe('application/json');
});

test('can dispatch a request with a router middleware', function () {
	$middleware = Mockery::mock(MiddlewareInterface::class);
	$middleware->shouldReceive('process')->andReturnUsing(function ($request, $handler) {
		$response = $handler->handle($request);
		return $response->withHeader('x-test', 'test');
	});

	$router = app()->get(RouterInterface::class);
	$router->middleware($middleware);
	$router->map('GET', '/', fn () => 'test');

	$response = request();
	expect($response->getHeaderLine('x-test'))->toBe('test');
});

test('can dispatch a request with a router lazy middleware', function () {
	app()->bind('middleware.test', function () {
		$mock = Mockery::mock(MiddlewareInterface::class);
		$mock->shouldReceive('process')->andReturnUsing(function ($request, $handler) {
			$response = $handler->handle($request);
			return $response->withHeader('x-test', 'test');
		});
		return $mock;
	});

	$router = app()->get(RouterInterface::class);
	$router->middleware('test');
	$router->map('GET', '/', fn () => 'test');

	$response = request();
	expect($response->getHeaderLine('x-test'))->toBe('test');
});

test('can use a invocable controller', function () {
	class Controller {
		public function __invoke(ServerRequestInterface $request, array $args) {
			return 'test';
		}
	}

	$router = app()->get(RouterInterface::class);
	$router->map('GET', '/test', Controller::class);
	$response = request(uri: '/test');
	expect((string) $response->getBody())->toBe('test');	
});

test('can get the route with a RouteAwareInterface controller', function () {
	class RouteAwareController implements RouteAwareInterface {
		protected RouteInterface $route;

		public function setRoute(RouteInterface $route): self {
			$this->route = $route;
			return $this;
		}

		public function __invoke(ServerRequestInterface $request, array $args) {
			return $this->route->getPath();
		}
	}

	$router = app()->get(RouterInterface::class);
	$router->map('GET', '/', RouteAwareController::class);
	$response = request();
	expect((string) $response->getBody())->toBe('/');	
});

test('can get a route parent group', function () {
	$router = app()->get(RouterInterface::class);
	$group = $router->group('/api', fn () => null);

	$route = $group->map('GET', '/test', fn () => 'test');

	expect($route->getParentGroup())->toBe($group);
});