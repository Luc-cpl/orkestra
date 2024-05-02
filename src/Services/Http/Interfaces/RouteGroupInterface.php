<?php

namespace Orkestra\Services\Http\Interfaces;

use Orkestra\Services\Http\Interfaces\Partials\RouteDefinitionInterface;
use Orkestra\Services\Http\Interfaces\Partials\RouteCollectionInterface;
use Orkestra\Services\Http\Interfaces\Partials\RouteStrategyInterface;
use Orkestra\Services\Http\Interfaces\Partials\MiddlewareAwareInterface;
use League\Route\RouteConditionHandlerInterface;
use League\Route\Strategy\StrategyAwareInterface;

interface RouteGroupInterface extends
    MiddlewareAwareInterface,
    RouteCollectionInterface,
    RouteConditionHandlerInterface,
    StrategyAwareInterface,
    RouteDefinitionInterface,
    RouteStrategyInterface
{
    public function getPrefix(): string;
}
