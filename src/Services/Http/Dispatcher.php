<?php

namespace Orkestra\Services\Http;

use Orkestra\App;
use Orkestra\Services\Http\Interfaces\Partials\MiddlewareAwareInterface;
use Orkestra\Services\Http\Interfaces\RouteAwareInterface;
use Orkestra\Services\Http\Interfaces\RouteInterface;
use Orkestra\Services\Http\Traits\MiddlewareAwareTrait;
use Orkestra\Services\Http\Middleware\ValidationMiddleware;
use League\Route\Dispatcher as LeagueDispatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use FastRoute\Dispatcher as FastRoute;

class Dispatcher extends LeagueDispatcher implements MiddlewareAwareInterface
{
	use MiddlewareAwareTrait;

	public function __construct(
		protected App $app,
		mixed $data
	) {
		parent::__construct($data);
	}

	public function dispatchRequest(ServerRequestInterface $request): ResponseInterface
	{
		$method = $request->getMethod();
		$uri    = $request->getUri()->getPath();
		$match  = $this->dispatch($method, $uri);
		$route  = null;

		switch ($match[0]) {
			case FastRoute::NOT_FOUND:
				$this->setNotFoundDecoratorMiddleware();
				break;
			case FastRoute::METHOD_NOT_ALLOWED:
				$allowed = (array) $match[1];
				$this->setMethodNotAllowedDecoratorMiddleware($allowed);
				break;
			case FastRoute::FOUND:
				/** @var Route $route */
				$route = $this->ensureHandlerIsRoute($match[1], $method, $uri)->setVars($match[2]);

				if ($this->isExtraConditionMatch($route, $request)) {
					$this->addValidationMiddleware($route);
					$this->setFoundMiddleware($route);
					$request = $this->requestWithRouteAttributes($request, $route);
					break;
				}

				$this->setNotFoundDecoratorMiddleware();
				break;
		}

		return $this->handle($request, $route);
	}

	public function handle(ServerRequestInterface $request, ?RouteInterface $route = null): ResponseInterface
	{
		$middleware = $this->shiftMiddleware();
		if ($route && $middleware instanceof RouteAwareInterface) {
			$middleware->setRoute($route);
		}
		return $middleware->process($request, $this);
	}

	protected function addValidationMiddleware(Route $route): void
	{
		$params = $route->getDefinition()->params();
		$rules = [];

		foreach ($params as $param) {
			$rules[$param->name] = $param->validation;
		}

		if (empty($rules)) {
			return;
		}

		$this->middleware([ValidationMiddleware::class, ['rules' => $rules]]);
	}
}
