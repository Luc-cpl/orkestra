<?php

namespace Orkestra\Services\Router\Interfaces;

use League\Route\Middleware\MiddlewareAwareInterface;
use League\Route\RouteCollectionInterface;
use League\Route\RouteConditionHandlerInterface;
use League\Route\Strategy\StrategyAwareInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface RouterInterface extends
	MiddlewareAwareInterface,
	RouteCollectionInterface,
	StrategyAwareInterface,
	RequestHandlerInterface,
	RouteConditionHandlerInterface
{
	/**
	 * Get all registered routes.
	 * 
	 * @return array
	 */
	public function getRoutes(): array;
}
