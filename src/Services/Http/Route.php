<?php

namespace Orkestra\Services\Http;

use Orkestra\App;

use Orkestra\Services\Http\Interfaces\RouteValidationInterface;
use Orkestra\Services\Http\Interfaces\RouteConfigInterface;
use Orkestra\Services\Http\Traits\RouteValidationTrait;
use Orkestra\Services\Http\Traits\RouteStrategyTrait;
use Orkestra\Services\Http\Traits\RouteConfigTrait;

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
		string          $method,
		string          $path,
		string|callable $handler,
	) {
		parent::__construct($method, $path, $handler);
	}

	public function getParentGroup(): ?RouteGroup
	{
		/** @var ?RouteGroup */
		return $this->group;
	}
}
