<?php

namespace Orkestra\Services\Http;

use Orkestra\App;
use Orkestra\Services\Http\Interfaces\RouteInterface;
use Orkestra\Services\Http\Interfaces\RouteAwareInterface;
use Orkestra\Services\Http\Traits\MiddlewareAwareTrait;
use Orkestra\Services\Http\Traits\RouteStrategyTrait;
use Orkestra\Services\Http\Traits\RouteDefinitionTrait;

use League\Route\Route as LeagueRoute;
use Psr\Container\ContainerInterface;

class Route extends LeagueRoute implements RouteInterface
{
    use MiddlewareAwareTrait;
    use RouteStrategyTrait;
    use RouteDefinitionTrait;

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

    protected function resolve(string $class, ?ContainerInterface $container = null): mixed
    {
        $instance = parent::resolve($class, $container);
        if ($instance instanceof RouteAwareInterface) {
            $instance->setRoute($this);
        }
        return $instance;
    }
}
