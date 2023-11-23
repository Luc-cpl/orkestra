<?php

namespace Orkestra\Services\Http;

use Orkestra\App;
use Orkestra\Services\Http\Route;
use Orkestra\Services\Http\Traits\RouteCollectionTrait;
use Orkestra\Services\Http\Traits\RouteStrategyTrait;
use Orkestra\Services\Http\Interfaces\RouteDefinitionInterface;

use League\Route\RouteGroup as LeagueRouteGroup;

use League\Route\RouteCollectionInterface;
use Orkestra\Services\Http\Traits\RouteDefinitionTrait;

class RouteGroup extends LeagueRouteGroup implements
    RouteDefinitionInterface
{
    use RouteCollectionTrait;
    use RouteStrategyTrait;
    use RouteDefinitionTrait;

    public function __construct(
        protected App $app,
        string   $prefix,
        callable $callback,
        RouteCollectionInterface $collection
    ) {
        parent::__construct($prefix, $callback, $collection);
    }

    /**
     * {@inheritdoc}
     * @param callable $handler
     */
    public function map(string $method, string $path, $handler): Route
    {
        $path = ($path === '/') ? $this->prefix : $this->prefix . sprintf('/%s', ltrim($path, '/'));

        /** @var Route $route */
        $route = $this->collection->map($method, $path, $handler);

        $route->setParentGroup($this);

        if ($host = $this->getHost()) {
            $route->setHost($host);
        }

        if ($scheme = $this->getScheme()) {
            $route->setScheme($scheme);
        }

        if ($port = $this->getPort()) {
            $route->setPort($port);
        }

        if ($route->getStrategy() === null && $this->getStrategy() !== null) {
            $route->setStrategy($this->getStrategy());
        }

        return $route;
    }
}
