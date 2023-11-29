<?php

namespace Orkestra\Services\Http\Interfaces;

use Orkestra\Services\Http\Interfaces\Partials\RouteDefinitionInterface;
use Orkestra\Services\Http\Interfaces\Partials\RouteStrategyInterface;
use League\Route\Middleware\MiddlewareAwareInterface;
use League\Route\RouteConditionHandlerInterface;
use League\Route\Strategy\StrategyAwareInterface;
use Psr\Http\Server\MiddlewareInterface;

interface RouteInterface extends
	MiddlewareInterface,
	MiddlewareAwareInterface,
	RouteConditionHandlerInterface,
	StrategyAwareInterface,
	RouteDefinitionInterface,
	RouteStrategyInterface
{
	public function getParentGroup(): ?RouteGroupInterface;
	public function getPath(): string;
}
