<?php

namespace Orkestra\Providers;

use InvalidArgumentException;
use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;

use Orkestra\Services\Http\Router;
use Orkestra\Services\Http\Interfaces\RouterInterface;

use Laminas\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface;

use Orkestra\Services\Http\Strategy\ApplicationStrategy;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ResponseFactory;
use Psr\Http\Message\ResponseInterface;

use League\Route\Strategy\JsonStrategy;
use Orkestra\Services\Http\Commands\MiddlewareListCommand;
use Rakit\Validation\Validator;

class HttpProvider implements ProviderInterface
{
	/**
	 * @var array<class-string<Command>>
	 */
	public array $commands = [
		MiddlewareListCommand::class,
	];

	/**
	 * Register services with the container.
	 * We can use the container to bind services to the app.
	 * 
	 * Do not use the container to resolve services at this point.
	 *
	 * @param App $app
	 * @return void
	 */
	public function register(App $app): void
	{
		// Set the required config so we can validate it
		$app->config()->set('validation', [
			'routes' => function ($value) {
				return is_string($value) ? true : 'The routes config must be a string path to a file';
			},
			'middleware' => function ($value) {
				return is_array($value) ? true : 'The middleware config must be an array';
			},
		]);

		$app->config()->set('definition', [
			'routes'  => ['The routes directory to load', ''],
			'middleware'  => ['The middleware to load', []],
		]);

		$app->singleton(Router::class, Router::class);
		$app->singleton(Validator::class, Validator::class);
		$app->singleton(RouterInterface::class, Router::class);

		$app->bind(ServerRequestInterface::class, function () {
			return ServerRequestFactory::fromGlobals(
				$_SERVER,
				$_GET,
				$_POST,
				$_COOKIE,
				$_FILES
			);
		});

		$app->bind(ResponseInterface::class, Response::class);
		$app->bind(ApplicationStrategy::class, fn (App $app) => (new ApplicationStrategy($app))->setContainer($app));
		$app->bind(JsonStrategy::class, function () use ($app) {
			$isProduction = $app->config()->get('env') === 'production';
			$jsonMode     = $isProduction ? 0 : JSON_PRETTY_PRINT;
			$strategy     = new JsonStrategy($app->get(ResponseFactory::class), $jsonMode);
			return ($strategy)->setContainer($app);
		});

		$app->bind(JsonResponse::class, function ($data, int $status = 200, $headers = []) use ($app) {
			$isProduction = $app->config()->get('env') === 'production';
			$jsonMode     = $isProduction ? 0 : JSON_PRETTY_PRINT;
			$response     = new JsonResponse($data, $status, $headers, $jsonMode);
			return $response;
		});
	}

	/**
	 * Here we can use the container to resolve and start services.
	 * 
	 * @param App $app
	 * @return void
	 */
	public function boot(App $app): void
	{
		$middlewareStack = $app->config()->get('middleware');
		$middlewareSources = array_map(fn () => 'configuration', $middlewareStack);
		foreach ($app->getProviders() as $provider) {
			$provider = $app->get($provider);
			if (!property_exists($provider, 'middleware')) {
				continue;
			}
			if (!is_array($provider->middleware)) {
				throw new InvalidArgumentException(sprintf('Middleware must be an array in %s', $provider::class));
			}
			foreach ($provider->middleware as $alias => $middleware) {
				if (!is_string($alias)) {
					throw new InvalidArgumentException(sprintf('Middleware alias must be a string in %s', $provider::class));
				}
				if (!is_string($middleware)) {
					throw new InvalidArgumentException(sprintf('Middleware must be a class string in %s', $provider::class));
				}
				if (!isset($middlewareStack[$alias])) {
					$middlewareStack[$alias] = $middleware;
					$middlewareSources[$alias] = $provider::class;
				}
			}
		}

		$app->config()->set('definition', [
			'middleware_sources' => [
				'Middleware stack sources',
				$middlewareSources,
			],
		]);

		$app->config()->set('middleware', $middlewareStack);

		$router = $app->get(RouterInterface::class);
		$router->setStrategy($app->get(ApplicationStrategy::class));

		/** @var string */
		$configFile = $app->config()->get('routes');

		if (empty($configFile)) {
			$app->hookCall('http.router.config', $router);
			return;
		}

		(require $configFile)($router);
		$app->hookCall('http.router.config', $router);
	}

	/**
	 * @param class-string[] $listeners
	 */
	protected function registerListeners(App $app, HooksInterface $hooks, array $listeners): void
	{
		foreach ($listeners as $listener) {
			// Set listeners as singletons
			$app->singleton($listener, $listener);
			/** @var ListenerInterface */
			$listener = $app->get($listener);
			$listenerHooks = $listener->hook();
			$listenerHooks = is_array($listenerHooks) ? $listenerHooks : [$listenerHooks];
			foreach ($listenerHooks as $listenerHook) {
				if (!method_exists($listener, 'handle')) {
					throw new Exception(sprintf('Listener %s must implement handle method', $listener::class));
				}
				$listenerHook = str_replace('{app}', $app->slug(), $listenerHook);
				// @phpstan-ignore-next-line
				$hooks->register($listenerHook, $listener->handle(...));
			}
		}
	}
}
