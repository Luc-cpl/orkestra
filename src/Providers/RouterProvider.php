<?php

namespace Orkestra\Providers;

use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;

use Orkestra\Services\RouterService as Router;
use Orkestra\Router\Middlewares\JsonMiddleware;

use Laminas\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface;

use Orkestra\Router\Strategy\ApplicationStrategy;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ResponseFactory;
use Psr\Http\Message\ResponseInterface;

use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Route\Strategy\JsonStrategy;
use Rakit\Validation\Validator;

class RouterProvider implements ProviderInterface
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
		$app->singleton(Router::class, Router::class);
		$app->singleton(Validator::class, Validator::class);

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

		$app->bind(ApplicationStrategy::class, fn () => (new ApplicationStrategy)->setContainer($app));

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

		// Set the required config so we can validate it
		$app->config()->set('validation', [
			'routes' => function ($value) {
				return is_string($value) ?? 'The routes config must be a string path to a file';
			},
		]);
	}

	/**
	 * Here we can use the container to resolve and start services.
	 * 
	 * @param App $app
	 * @return void
	 */
	public function boot(App $app): void
	{
		$router  = $app->get(Router::class);
		$request = $app->get(ServerRequestInterface::class);

		$strategy = (new ApplicationStrategy)->setContainer($app);
		$router->setStrategy($strategy);

		$router->middleware($app->get(JsonMiddleware::class));

		$configFile = $app->config()->get('routes');

		(require $configFile)($router);

		$response = $router->dispatch($request);

		// send the response to the browser
		(new SapiEmitter)->emit($response);
	}
}
