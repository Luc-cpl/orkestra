<?php

namespace Orkestra\Services\Http;

use Orkestra\App;
use Orkestra\Services\Http\Interfaces\RouterInterface;
use Orkestra\Services\Http\Route;
use Orkestra\Services\Http\RouteGroup;
use Orkestra\Services\Http\Traits\MiddlewareAwareTrait;
use Orkestra\Services\Http\Traits\RouteCollectionTrait;

use League\Route\Router as LeagueRouter;
use FastRoute\RouteCollector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Extends the League Router class.
 * This way we can add custom functionality as custom validation and meta data.
 */
class Router extends LeagueRouter implements RouterInterface
{
    use MiddlewareAwareTrait;
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

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getRoutesByDefinitionType(string $type): array
    {
        if ($this->routesPrepared === false) {
            $this->prepareRoutes($this->app->get(ServerRequestInterface::class));
        }

        return array_values(array_filter($this->routes, function (Route $route) use ($type) {
            $definition = $route->getDefinition();

            if ($definition->type() === $type) {
                return true;
            }

            $group = $route->getParentGroup();

            if ($group === null) {
                return false;
            }

            $definition = $group->getDefinition();

            return $definition->type() === $type;
        }));
    }

    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        if (false === $this->routesPrepared) {
            $this->prepareRoutes($request);
        }

        $strategy = $this->getStrategy();

        $dispatcher = new Dispatcher($this->app, $this->routesData);

        if ($strategy) {
            $dispatcher->setStrategy($strategy);
        }

        foreach ($this->getMiddlewareStack() as $middleware) {
            if (is_string($middleware)) {
                $dispatcher->lazyMiddleware($middleware);
                continue;
            }

            $dispatcher->middleware($middleware);
        }

        return $dispatcher->dispatchRequest($request);
    }
}
