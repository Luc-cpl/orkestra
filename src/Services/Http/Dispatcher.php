<?php

namespace Orkestra\Services\Http;

use Orkestra\Services\Http\Traits\MiddlewareAwareTrait;
use Orkestra\Services\Http\Interfaces\Partials\MiddlewareAwareInterface;
use Orkestra\Services\Http\Middlewares\ValidationMiddleware;
use League\Route\Dispatcher as LeagueDispatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use FastRoute\Dispatcher as FastRoute;

class Dispatcher extends LeagueDispatcher implements MiddlewareAwareInterface
{
	use MiddlewareAwareTrait;

	public function dispatchRequest(ServerRequestInterface $request): ResponseInterface
	{
		$method = $request->getMethod();
		$uri    = $request->getUri()->getPath();
		$match  = $this->dispatch($method, $uri);

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

		return $this->handle($request);
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
