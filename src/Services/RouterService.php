<?php

namespace Orkestra\Services;

use FastRoute\RouteCollector;
use Orkestra\Models\Route;
use Orkestra\Models\RouteGroup;

use League\Route\Router;
use Orkestra\App;
use Rakit\Validation\Validator;

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
    public function __construct(
        protected App $app,
        protected Validator $validator,
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
        $route = $route->setValidator($this->validator);

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
}