<?php

namespace Orkestra\Services;

use Orkestra\App;
use Orkestra\Router\Route;
use Orkestra\Router\RouteGroup;
use Orkestra\Router\Traits\RouteCollectionTrait;
use Orkestra\Interfaces\RouterInterface;

use League\Route\Router;
use FastRoute\RouteCollector;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Extends the League Router class.
 * This way we can add custom functionality as custom validation and meta data.
 */
class RouterService extends Router implements RouterInterface
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
