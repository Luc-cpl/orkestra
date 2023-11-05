<?php

namespace Orkestra\Services;

use Orkestra\App;
use Orkestra\Router\Route;
use Orkestra\Router\RouteGroup;
use Orkestra\Router\Traits\RouteCollectionTrait;

use League\Route\Router;
use FastRoute\RouteCollector;
use League\Route\Http\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Extends the League Router class.
 * This way we can add custom functionality as custom validation and meta data.
 * 
 * @method Route map(string $method, string $path, $handler)
 * @method Route get(string $path, $handler)
 * @method Route put(string $path, $handler)
 * @method Route post(string $path, $handler)
 * @method Route patch(string $path, $handler)
 * @method Route delete(string $path, $handler)
 */
class RouterService extends Router
{
    use RouteCollectionTrait;

    protected mixed $notFoundHandler;

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

    public function notFoundHandler($handler): self
    {
        $this->notFoundHandler = $handler;
        return $this;
    }

    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        try {
            return parent::dispatch($request);
        } catch (NotFoundException $th) {
            // Send to 404 page if exists
            $notFOundRoute = $this->getNamedRoute('404');
            return $this->app->get(ResponseInterface::class, [
                'status' => 404,
                'body'   => 'Not Found'
            ]);
        }
    }
}
