<?php

namespace Orkestra\Models;

use Orkestra\App;
use Orkestra\Models\Route;

use League\Route\RouteGroup as LeagueRouteGroup;

use League\Route\RouteCollectionInterface;

/**
 * Map the methods to the Route class and bind the container to json controllers.
 * 
 * @method Route map(string $method, string $path, $handler)
 * @method Route get(string $path, $handler)
 * @method Route put(string $path, $handler)
 * @method Route post(string $path, $handler)
 * @method Route patch(string $path, $handler)
 * @method Route delete(string $path, $handler)
 */
class RouteGroup extends LeagueRouteGroup
{
	public function __construct(
		protected App $app,
		string $prefix, callable $callback, RouteCollectionInterface $collection)
	{
		parent::__construct($prefix, $callback, $collection);
	}

	public function json(): self
	{
		$this->setStrategy($this->app->get(JsonStrategy::class));
		return $this;
	}
}