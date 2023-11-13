<?php

namespace Orkestra\Services\Router;

use Orkestra\App;
use Orkestra\Services\Router\Route;
use Orkestra\Services\Router\Traits\RouteCollectionTrait;
use Orkestra\Services\Router\Traits\RouteStrategyTrait;
use Orkestra\Services\Router\Traits\RouteConfigTrait;
use Orkestra\Services\Router\Interfaces\RouteConfigInterface;

use League\Route\RouteGroup as LeagueRouteGroup;

use League\Route\RouteCollectionInterface;

class RouteGroup extends LeagueRouteGroup implements
    RouteConfigInterface
{
    use RouteCollectionTrait;
    use RouteStrategyTrait;
    use RouteConfigTrait;

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
