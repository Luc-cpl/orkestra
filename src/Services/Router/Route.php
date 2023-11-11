<?php

namespace Orkestra\Services\Router;

use Orkestra\App;

use Orkestra\Services\Router\Interfaces\RouteValidationInterface;
use Orkestra\Services\Router\Interfaces\RouteConfigInterface;
use Orkestra\Services\Router\Traits\RouteValidationTrait;
use Orkestra\Services\Router\Traits\RouteStrategyTrait;
use Orkestra\Services\Router\Traits\RouteConfigTrait;

use League\Route\Route as LeagueRoute;

class Route extends LeagueRoute implements
	RouteConfigInterface,
	RouteValidationInterface
{
	use RouteStrategyTrait;
	use RouteConfigTrait;
	use RouteValidationTrait;

	public function __construct(
		protected App $app,
		string $method,
		string $path,
		$handler,
	) {
		parent::__construct($method, $path, $handler);
	}
}
