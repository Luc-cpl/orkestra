<?php

namespace Orkestra\Providers;

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
use Rakit\Validation\Validator;

class HttpProvider implements ProviderInterface
{
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
				return is_string($value ?? '') ? true : 'The routes config must be a string path to a file';
			},
			'middleware' => function ($value) {
				return is_array($value ?? []) ? true : 'The middleware config must be an array';
			},
		]);

		$app->config()->set('definition', [
			'routes'  => [false, 'The routes directory to load'],
			'middleware'  => [false, 'The middleware to load'],
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
		$middleware = $app->config()->get('middleware', []);

		/** @var mixed[] */
		$middleware = $app->hookQuery('http.middleware', $middleware);

		foreach ($middleware as $key => $middleware) {
			$app->bind("middleware.$key", $middleware);
		}

		$router = $app->get(RouterInterface::class);
		$router->setStrategy($app->get(ApplicationStrategy::class));

		/** @var string */
		$configFile = $app->config()->get('routes');

		if (!$configFile) {
			$app->hookCall('http.router.config', $router);
			return;
		}

		(require $configFile)($router);
		$app->hookCall('http.router.config', $router);
	}
}
