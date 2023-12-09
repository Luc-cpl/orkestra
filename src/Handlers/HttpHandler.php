<?php

namespace Orkestra\Handlers;

use Orkestra\App;
use Orkestra\Interfaces\HandlerInterface;
use Orkestra\Services\Http\Interfaces\RouterInterface;
use Orkestra\Services\Http\Middleware\JsonMiddleware;
use Orkestra\Services\Http\Strategy\ApplicationStrategy;
use Psr\Http\Message\ServerRequestInterface;

use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;

class HttpHandler implements HandlerInterface
{

	public function __construct(
		protected App $app,
		protected RouterInterface $router,
		protected ServerRequestInterface $request,
		protected ApplicationStrategy $strategy,
		protected SapiEmitter $emitter,
	) {
	}

	/**
	 * Handle the current request.
	 * This should be called to handle the current request from the provider.
	 */
	public function handle(): void
	{
		$app      = $this->app;
		$router   = $this->router;
		$request  = $this->request;
		$strategy = $this->strategy;
		$emitter  = $this->emitter;

		$router->setStrategy($strategy);
		$router->middleware(JsonMiddleware::class);

		/** @var ServerRequestInterface */
		$request = $app->hookQuery('http.router.dispatch', $request, $router);

		$response = $router->dispatch($request);

		$app->hookCall('http.router.response.before', $response);

		// send the response to the browser
		$emitter->emit($response);

		$app->hookCall('http.router.response.after', $response);
	}
}
