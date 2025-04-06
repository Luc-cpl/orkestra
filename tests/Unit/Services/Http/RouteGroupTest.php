<?php

namespace Tests\Unit\Services\Http;

use Orkestra\App;
use Orkestra\Services\Http\Interfaces\RouteGroupInterface;
use Orkestra\Services\Http\Interfaces\RouteInterface;
use Orkestra\Services\Http\Route;
use Orkestra\Services\Http\RouteGroup;
use Orkestra\Services\Http\Facades\RouteDefinitionFacade;
use Orkestra\Services\Http\Factories\ParamDefinitionFactory;
use Orkestra\Services\Http\Interfaces\DefinitionInterface;
use Orkestra\Providers\HttpProvider;
use League\Route\RouteCollectionInterface;
use Psr\Http\Server\MiddlewareInterface;
use Mockery;

beforeEach(function () {
    // Ensure the App and HttpProvider are available for all tests
    app()->provider(HttpProvider::class);
    app()->bind(ParamDefinitionFactory::class, function () {
        return Mockery::mock(ParamDefinitionFactory::class);
    });
});

test('can create a route group with prefix', function () {
    $app = app();
    $collection = Mockery::mock(RouteCollectionInterface::class);
    $callback = function (RouteGroupInterface $group) {
        // Empty callback
    };

    $group = new RouteGroup($app, '/api', $callback, $collection);

    expect($group)->toBeInstanceOf(RouteGroupInterface::class);
    expect($group->getPrefix())->toBe('/api');
});

test('can map a route within a group', function () {
    $app = app();

    // Mock the collection to verify the map is called correctly
    $collection = Mockery::mock(RouteCollectionInterface::class);
    $route = new Route($app, 'GET', '/test', fn () => 'test');

    $collection->shouldReceive('map')
        ->once()
        ->with('GET', '/api/test', Mockery::type('callable'))
        ->andReturn($route);

    $callback = function (RouteGroupInterface $group) {
        // Empty callback
    };

    $group = new RouteGroup($app, '/api', $callback, $collection);

    $result = $group->map('GET', '/test', fn () => 'test');

    expect($result)->toBeInstanceOf(RouteInterface::class);
    expect($result->getParentGroup())->toBe($group);
});

test('handles root path correctly when mapping', function () {
    $app = app();

    // Mock the collection to verify the map is called correctly with root path
    $collection = Mockery::mock(RouteCollectionInterface::class);
    $route = new Route($app, 'GET', '/', fn () => 'test');

    $collection->shouldReceive('map')
        ->once()
        ->with('GET', '/api', Mockery::type('callable'))
        ->andReturn($route);

    $callback = function (RouteGroupInterface $group) {
        // Empty callback
    };

    $group = new RouteGroup($app, '/api', $callback, $collection);

    $result = $group->map('GET', '/', fn () => 'test');

    expect($result)->toBeInstanceOf(RouteInterface::class);
});

test('preserves route conditions from group', function () {
    $app = app();

    // Mock the collection
    $collection = Mockery::mock(RouteCollectionInterface::class);
    $route = new Route($app, 'GET', '/test', fn () => 'test');

    $collection->shouldReceive('map')
        ->once()
        ->andReturn($route);

    $callback = function (RouteGroupInterface $group) {
        // Empty callback
    };

    $group = new RouteGroup($app, '/api', $callback, $collection);

    // Set host, scheme, port on the group
    $group->setHost('api.example.com');
    $group->setScheme('https');
    $group->setPort(443);

    $result = $group->map('GET', '/test', fn () => 'test');

    expect($result->getHost())->toBe('api.example.com');
    expect($result->getScheme())->toBe('https');
    expect($result->getPort())->toBe(443);
});

test('group strategy is inherited by routes', function () {
    $app = app();

    // Create a real route for this test rather than a mock
    $route = new Route($app, 'GET', '/test', fn () => 'test');

    // Mock only the collection
    $collection = Mockery::mock(RouteCollectionInterface::class);
    $collection->shouldReceive('map')
        ->once()
        ->andReturn($route);

    // Create a simple callback that does nothing
    $callback = function () {};

    $strategy = Mockery::mock(\League\Route\Strategy\ApplicationStrategy::class);

    $group = new RouteGroup($app, '/api', $callback, $collection);
    $group->setStrategy($strategy);

    // Get the strategy from the group for comparison later
    $groupStrategy = $group->getStrategy();

    $result = $group->map('GET', '/test', fn () => 'test');

    // The route should have inherited the strategy from the group
    expect($groupStrategy)->toBe($strategy);
    expect($result->getStrategy())->toBe($strategy);
});

test('can add middleware to group', function () {
    $app = app();
    $collection = Mockery::mock(RouteCollectionInterface::class);
    $callback = function (RouteGroupInterface $group) {
        // Empty callback
    };

    $group = new RouteGroup($app, '/api', $callback, $collection);

    $middleware = Mockery::mock(MiddlewareInterface::class);

    $result = $group->middleware($middleware);

    expect($result)->toBe($group);
    expect($group->getMiddlewareStack())->toContain($middleware);
});

test('can add middleware with constructor parameters', function () {
    $app = app();
    $collection = Mockery::mock(RouteCollectionInterface::class);
    $callback = function (RouteGroupInterface $group) {
        // Empty callback
    };

    $group = new RouteGroup($app, '/api', $callback, $collection);

    // Create a middleware class name and constructor params
    $middlewareClass = 'TestMiddleware';
    $constructorParams = ['param1' => 'value1', 'param2' => 'value2'];

    $result = $group->middleware($middlewareClass, $constructorParams);

    expect($result)->toBe($group);

    $middlewareStack = iterator_to_array($group->getMiddlewareStack());
    expect($middlewareStack)->toContain([$middlewareClass, $constructorParams]);
});

test('can add middleware stack to group', function () {
    $app = app();
    $collection = Mockery::mock(RouteCollectionInterface::class);
    $callback = function (RouteGroupInterface $group) {
        // Empty callback
    };

    $group = new RouteGroup($app, '/api', $callback, $collection);

    $middleware1 = Mockery::mock(MiddlewareInterface::class);
    $middleware2 = 'MiddlewareClass';
    $middleware3 = ['AnotherMiddleware', ['option' => 'value']];

    $middlewareStack = [
        $middleware1,
        $middleware2,
        $middleware3
    ];

    $result = $group->middlewareStack($middlewareStack);

    expect($result)->toBe($group);

    $resultStack = iterator_to_array($group->getMiddlewareStack());
    expect($resultStack)->toHaveCount(3);
    expect($resultStack[0])->toBe($middleware1);
    expect($resultStack[1])->toBe($middleware2);
    expect($resultStack[2])->toBe($middleware3);
});

test('can prepend middleware to the stack', function () {
    $app = app();
    $collection = Mockery::mock(RouteCollectionInterface::class);
    $callback = function (RouteGroupInterface $group) {
        // Empty callback
    };

    $group = new RouteGroup($app, '/api', $callback, $collection);

    $middleware1 = Mockery::mock(MiddlewareInterface::class);
    $middleware2 = Mockery::mock(MiddlewareInterface::class);

    // Add middleware1 first
    $group->middleware($middleware1);

    // Prepend middleware2
    $result = $group->prependMiddleware($middleware2);

    expect($result)->toBe($group);

    $middlewareStack = iterator_to_array($group->getMiddlewareStack());
    expect($middlewareStack)->toHaveCount(2);
    expect($middlewareStack[0])->toBe($middleware2); // First item should be middleware2
    expect($middlewareStack[1])->toBe($middleware1); // Second item should be middleware1
});

test('can prepend middleware with constructor parameters', function () {
    $app = app();
    $collection = Mockery::mock(RouteCollectionInterface::class);
    $callback = function (RouteGroupInterface $group) {
        // Empty callback
    };

    $group = new RouteGroup($app, '/api', $callback, $collection);

    $middleware1 = Mockery::mock(MiddlewareInterface::class);

    // Add middleware1 first
    $group->middleware($middleware1);

    // Prepend middleware with constructor params
    $middlewareClass = 'PrependedMiddleware';
    $constructorParams = ['param1' => 'value1'];
    $result = $group->prependMiddleware($middlewareClass, $constructorParams);

    expect($result)->toBe($group);

    $middlewareStack = iterator_to_array($group->getMiddlewareStack());
    expect($middlewareStack)->toHaveCount(2);
    expect($middlewareStack[0])->toBe([$middlewareClass, $constructorParams]); // First item should be the prepended middleware
    expect($middlewareStack[1])->toBe($middleware1); // Second item should be middleware1
});

test('can shift middleware from the stack', function () {
    $app = app();
    $collection = Mockery::mock(RouteCollectionInterface::class);
    $callback = function (RouteGroupInterface $group) {
        // Empty callback
    };

    $group = new RouteGroup($app, '/api', $callback, $collection);

    $middleware = Mockery::mock(MiddlewareInterface::class);

    // Add middleware to the stack
    $group->middleware($middleware);

    // Shift the middleware
    $shiftedMiddleware = $group->shiftMiddleware();

    expect($shiftedMiddleware)->toBe($middleware);
    expect(iterator_to_array($group->getMiddlewareStack()))->toBeEmpty();
});

test('shifting from empty middleware stack throws exception', function () {
    $app = app();
    $collection = Mockery::mock(RouteCollectionInterface::class);
    $callback = function (RouteGroupInterface $group) {
        // Empty callback
    };

    $group = new RouteGroup($app, '/api', $callback, $collection);

    // Attempt to shift middleware from an empty stack
    $group->shiftMiddleware();
})->throws(\OutOfBoundsException::class, 'Reached end of middleware stack. Does your controller return a response?');

test('can set JSON strategy on group', function () {
    $app = app();
    $collection = Mockery::mock(RouteCollectionInterface::class);
    $callback = function (RouteGroupInterface $group) {
        // Empty callback
    };

    // Mock the JsonStrategy in the app container
    $jsonStrategy = Mockery::mock(\League\Route\Strategy\JsonStrategy::class);
    app()->bind(\League\Route\Strategy\JsonStrategy::class, $jsonStrategy);

    $group = new RouteGroup($app, '/api', $callback, $collection);

    $result = $group->json();

    expect($result)->toBe($group);
    expect($group->getStrategy())->toBe($jsonStrategy);
});

test('can set definition with array on group', function () {
    $app = app();
    $collection = Mockery::mock(RouteCollectionInterface::class);
    $callback = function (RouteGroupInterface $group) {
        // Empty callback
    };

    $group = new RouteGroup($app, '/api', $callback, $collection);

    $definition = ['title' => 'API Group', 'description' => 'Test API Group'];

    $result = $group->setDefinition($definition);

    expect($result)->toBe($group);

    // Mock the facade
    $facade = Mockery::mock(RouteDefinitionFacade::class);
    app()->bind(RouteDefinitionFacade::class, $facade);

    // The getDefinition() method will return a RouteDefinitionFacade
    expect($group->getDefinition())->toBeInstanceOf(RouteDefinitionFacade::class);
});

test('can set definition with class name on group', function () {
    $app = app();
    $collection = Mockery::mock(RouteCollectionInterface::class);
    $callback = function (RouteGroupInterface $group) {
        // Empty callback
    };

    $group = new RouteGroup($app, '/api', $callback, $collection);

    // Create a mock definition class
    $definition = Mockery::mock(DefinitionInterface::class);

    // Set up the app to return our mock when the class is requested
    $definitionClass = get_class($definition);
    app()->bind($definitionClass, $definition);

    // Create a mock facade to be returned
    $facade = Mockery::mock(RouteDefinitionFacade::class);
    app()->bind(RouteDefinitionFacade::class, $facade);

    $result = $group->setDefinition($definitionClass);

    expect($result)->toBe($group);
});

test('can set definition with class name and constructor params', function () {
    $app = app();
    $collection = Mockery::mock(RouteCollectionInterface::class);
    $callback = function (RouteGroupInterface $group) {
        // Empty callback
    };

    $group = new RouteGroup($app, '/api', $callback, $collection);

    // Create a mock definition class
    $definition = Mockery::mock(DefinitionInterface::class);

    // Set up the app to return our mock when the class is requested with constructor params
    $definitionClass = get_class($definition);
    $constructorParams = ['param1' => 'value1', 'param2' => 'value2'];

    // Mock the app->make method to expect the constructor params
    app()->bind($definitionClass, $definition);

    // Create a mock facade to be returned
    $facade = Mockery::mock(RouteDefinitionFacade::class);
    app()->bind(RouteDefinitionFacade::class, $facade);

    $result = $group->setDefinition($definitionClass, $constructorParams);

    expect($result)->toBe($group);

    // Force getDefinition to execute
    $group->getDefinition();
});

test('setting definition with non-existent class throws exception', function () {
    $app = app();
    $collection = Mockery::mock(RouteCollectionInterface::class);
    $callback = function (RouteGroupInterface $group) {
        // Empty callback
    };

    $group = new RouteGroup($app, '/api', $callback, $collection);

    // Try to set a non-existent class
    $group->setDefinition('NonExistentClass');
})->throws(\InvalidArgumentException::class, "Route definition class 'NonExistentClass' does not exist.");

test('setting definition with invalid class throws exception', function () {
    $app = app();
    $collection = Mockery::mock(RouteCollectionInterface::class);
    $callback = function (RouteGroupInterface $group) {
        // Empty callback
    };

    $group = new RouteGroup($app, '/api', $callback, $collection);

    // Try to set a class that doesn't implement DefinitionInterface
    $group->setDefinition(\stdClass::class);
})->throws(\InvalidArgumentException::class, "Route definition class 'stdClass' must implement " . DefinitionInterface::class);

test('callback is executed when group is created', function () {
    $app = app();

    // Create the collection mock
    $collection = Mockery::mock(RouteCollectionInterface::class);

    $callbackExecuted = false;

    // Make a callback that simply sets our flag to true
    $callback = function () use (&$callbackExecuted) {
        $callbackExecuted = true;
    };

    // Create the route group
    $group = new RouteGroup($app, '/api', $callback, $collection);

    // Manually invoke the group to execute the callback
    $group->__invoke();

    // Assert that our flag was set to true by the callback
    expect($callbackExecuted)->toBeTrue();
});

test('can use different HTTP methods in a group', function () {
    $app = app();

    // Mock the collection
    $collection = Mockery::mock(RouteCollectionInterface::class);
    $getRoute = new Route($app, 'GET', '/users', fn () => 'get users');
    $postRoute = new Route($app, 'POST', '/users', fn () => 'create user');
    $putRoute = new Route($app, 'PUT', '/users/1', fn () => 'update user');
    $deleteRoute = new Route($app, 'DELETE', '/users/1', fn () => 'delete user');

    $collection->shouldReceive('map')
        ->with('GET', '/api/users', Mockery::type('callable'))
        ->andReturn($getRoute);

    $collection->shouldReceive('map')
        ->with('POST', '/api/users', Mockery::type('callable'))
        ->andReturn($postRoute);

    $collection->shouldReceive('map')
        ->with('PUT', '/api/users/1', Mockery::type('callable'))
        ->andReturn($putRoute);

    $collection->shouldReceive('map')
        ->with('DELETE', '/api/users/1', Mockery::type('callable'))
        ->andReturn($deleteRoute);

    $callback = function (RouteGroupInterface $group) {
        // Empty callback
    };

    $group = new RouteGroup($app, '/api', $callback, $collection);

    $getResult = $group->get('/users', fn () => 'get users');
    $postResult = $group->post('/users', fn () => 'create user');
    $putResult = $group->put('/users/1', fn () => 'update user');
    $deleteResult = $group->delete('/users/1', fn () => 'delete user');

    expect($getResult)->toBeInstanceOf(RouteInterface::class);
    expect($getResult->getMethod())->toBe('GET');

    expect($postResult)->toBeInstanceOf(RouteInterface::class);
    expect($postResult->getMethod())->toBe('POST');

    expect($putResult)->toBeInstanceOf(RouteInterface::class);
    expect($putResult->getMethod())->toBe('PUT');

    expect($deleteResult)->toBeInstanceOf(RouteInterface::class);
    expect($deleteResult->getMethod())->toBe('DELETE');
});
