<?php

namespace Orkestra\Router;

use Orkestra\App;
use Orkestra\Router\Route;
use Orkestra\Router\Traits\RouteCollectionTrait;
use Orkestra\Router\Traits\RouteStrategyTrait;

use League\Route\RouteGroup as LeagueRouteGroup;

use League\Route\RouteCollectionInterface;

class RouteGroup extends LeagueRouteGroup
{
    use RouteCollectionTrait;
    use RouteStrategyTrait;

    public function __construct(
        protected App $app,
        string $prefix,
        callable $callback,
        RouteCollectionInterface $collection
    ) {
        parent::__construct($prefix, $callback, $collection);
    }

    /**
     * {@inheritdoc}
     */
    public function map(string $method, string $path, $handler): Route
    {
        $path  = ($path === '/') ? $this->prefix : $this->prefix . sprintf('/%s', ltrim($path, '/'));
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
