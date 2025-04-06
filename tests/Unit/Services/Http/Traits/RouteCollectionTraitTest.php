<?php

use Orkestra\App;
use Orkestra\Services\Http\Interfaces\RouteInterface;
use Orkestra\Services\Http\Route;
use Orkestra\Services\Http\Traits\RouteCollectionTrait;

// Create a test class that uses the trait
class RouteCollectionTraitTest
{
    use RouteCollectionTrait;

    protected array $routes = [];

    public function __construct(protected App $app)
    {
    }

    // Implement the required abstract method
    public function map(string $method, string $path, $handler): Route
    {
        // Create a route
        $route = new Route($this->app, $method, $path, $handler);
        $this->routes[] = $route;
        return $route;
    }

    // Expose methods for testing with different method names
    public function exposedAddRoute(string $method, string $path, $handler): RouteInterface
    {
        return $this->map($method, $path, $handler);
    }

    public function exposedMap(string $method, string $path, $handler): RouteInterface
    {
        return $this->map($method, $path, $handler);
    }

    public function exposedGet(string $path, $handler): RouteInterface
    {
        return $this->get($path, $handler);
    }

    public function exposedPost(string $path, $handler): RouteInterface
    {
        return $this->post($path, $handler);
    }

    public function exposedPut(string $path, $handler): RouteInterface
    {
        return $this->put($path, $handler);
    }

    public function exposedPatch(string $path, $handler): RouteInterface
    {
        return $this->patch($path, $handler);
    }

    public function exposedDelete(string $path, $handler): RouteInterface
    {
        return $this->delete($path, $handler);
    }

    public function exposedHead(string $path, $handler): RouteInterface
    {
        return $this->head($path, $handler);
    }

    public function exposedOptions(string $path, $handler): RouteInterface
    {
        return $this->options($path, $handler);
    }
}

test('can add routes with different HTTP methods', function () {
    $app = app();
    $collection = new RouteCollectionTraitTest($app);

    $handler = fn () => 'test';

    $route1 = $collection->exposedGet('/test-get', $handler);
    $route2 = $collection->exposedPost('/test-post', $handler);
    $route3 = $collection->exposedPut('/test-put', $handler);
    $route4 = $collection->exposedPatch('/test-patch', $handler);
    $route5 = $collection->exposedDelete('/test-delete', $handler);
    $route6 = $collection->exposedHead('/test-head', $handler);
    $route7 = $collection->exposedOptions('/test-options', $handler);
    $route8 = $collection->exposedMap('CUSTOM', '/test-custom', $handler);

    expect($route1)->toBeInstanceOf(RouteInterface::class);
    expect($route1->getMethod())->toBe('GET');
    expect($route1->getPath())->toBe('/test-get');

    expect($route2)->toBeInstanceOf(RouteInterface::class);
    expect($route2->getMethod())->toBe('POST');

    expect($route3)->toBeInstanceOf(RouteInterface::class);
    expect($route3->getMethod())->toBe('PUT');

    expect($route4)->toBeInstanceOf(RouteInterface::class);
    expect($route4->getMethod())->toBe('PATCH');

    expect($route5)->toBeInstanceOf(RouteInterface::class);
    expect($route5->getMethod())->toBe('DELETE');

    expect($route6)->toBeInstanceOf(RouteInterface::class);
    expect($route6->getMethod())->toBe('HEAD');

    expect($route7)->toBeInstanceOf(RouteInterface::class);
    expect($route7->getMethod())->toBe('OPTIONS');

    expect($route8)->toBeInstanceOf(RouteInterface::class);
    expect($route8->getMethod())->toBe('CUSTOM');
});

test('can add route directly', function () {
    $app = app();
    $collection = new RouteCollectionTraitTest($app);

    $handler = fn () => 'test';
    $route = $collection->exposedAddRoute('GET', '/test-direct', $handler);

    expect($route)->toBeInstanceOf(RouteInterface::class);
    expect($route->getMethod())->toBe('GET');
    expect($route->getPath())->toBe('/test-direct');
});
