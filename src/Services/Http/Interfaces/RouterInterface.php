<?php

namespace Orkestra\Services\Http\Interfaces;

use Orkestra\Services\Http\Route;

use Orkestra\Services\Http\Interfaces\Partials\RouteCollectionInterface;
use League\Route\Middleware\MiddlewareAwareInterface;
use League\Route\RouteConditionHandlerInterface;
use League\Route\Strategy\StrategyAwareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface RouterInterface extends
	MiddlewareAwareInterface,
	RouteCollectionInterface,
	StrategyAwareInterface,
	RequestHandlerInterface,
	RouteConditionHandlerInterface
{
	/**
	 * Add a route group.
	 * 
	 * @param string $prefix
	 * @param callable $group
	 * @return RouteGroupInterface
	 */
	public function group(string $prefix, callable $group): RouteGroupInterface;

	/**
	 * Get all registered routes.
	 * 
	 * @return Route[]
	 */
	public function getRoutes(): array;

	/**
	 * Get all routes by definition type.
	 * 
	 * This method should return all routes that have a
	 * DefinitionInterface type that matches the given type.
	 * 
	 * @param string $type
	 * @return Route[]
	 */
	public function getRoutesByDefinitionType(string $type): array;

	/**
	 * Dispatch the router.
	 *
	 * This method should return a response from the router.
	 *
	 * @param ServerRequestInterface $request
	 * @return ResponseInterface
	 */
	public function dispatch(ServerRequestInterface $request): ResponseInterface;

	/**
	 * Prepare the routes.
	 * This method should be called before dispatching the router.
	 *
	 * @param ServerRequestInterface $request
	 * @return void
	 */
	public function prepareRoutes(ServerRequestInterface $request): void;
}
