<?php

namespace Orkestra\Services\Router;

use Orkestra\App;
use Orkestra\Services\Router\Interfaces\RouterInterface;
use Orkestra\Services\Router\Route;
use Orkestra\Services\Router\RouteGroup;
use Orkestra\Services\Router\Traits\RouteCollectionTrait;

use League\Route\Router as LeagueRouter;
use FastRoute\RouteCollector;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Extends the League Router class.
 * This way we can add custom functionality as custom validation and meta data.
 */
class Router extends LeagueRouter implements RouterInterface
{
    use RouteCollectionTrait;

    /**
     * @var RouteGroup[]
     */
    protected $groups = [];

    /**
     * @var Route[]
     */
    protected $namedRoutes = [];

    /**
     * @var Route[]
     */
    protected $routes = [];

    public function __construct(
        protected App $app,
        ?RouteCollector $routeCollector = null
    ) {
        parent::__construct($routeCollector);
    }

    /**
     * {@inheritdoc}
     * @param callable $handler
     */
    public function map(string $method, string $path, $handler): Route
    {
        $path  = sprintf('/%s', ltrim($path, '/'));
        $route = $this->app->get(Route::class, [
            'method'  => $method,
            'path'    => $path,
            'handler' => $handler
        ]);

        $this->routes[] = $route;

        return $route;
    }

    public function group(string $prefix, callable $group): RouteGroup
    {
        $group = $this->app->get(RouteGroup::class, [
            'prefix'     => $prefix,
            'callback'   => $group,
            'collection' => $this
        ]);
        $this->groups[] = $group;
        return $group;
    }

    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $request = $this->app->hookQuery('router.dispatch', $request, $this);
        return parent::dispatch($request);
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}
