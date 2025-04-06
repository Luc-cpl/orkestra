<?php

use Orkestra\Services\Http\Interfaces\RouteInterface;
use Orkestra\Services\Http\Middleware\ValidationMiddleware;
use Orkestra\Services\Http\Factories\ParamDefinitionFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

test('can validate request with body parameters', function () {
    $app = app();
    $factory = $app->get(ParamDefinitionFactory::class);

    // Define parameters for validation
    $params = [
        $factory->String(
            title: 'Test Parameter',
            name: 'test',
            validation: 'required|min:3'
        )
    ];

    // Create middleware with parameters
    $middleware = $app->make(ValidationMiddleware::class, ['params' => $params]);

    // Setup route
    $route = Mockery::mock(RouteInterface::class);
    $middleware->setRoute($route);

    // Setup request with valid body
    $request = Mockery::mock(ServerRequestInterface::class);
    $request->shouldReceive('getParsedBody')->andReturn(['test' => 'valid value']);
    $request->shouldReceive('getQueryParams')->andReturn([]);

    // Setup withQueryParams and withParsedBody properly chained
    $request->shouldReceive('withQueryParams')->andReturnSelf();
    $request->shouldReceive('withParsedBody')->andReturnSelf();

    // Setup handler to return a response
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $handler->shouldReceive('handle')->with($request)->andReturn($response);

    // Execute middleware
    $result = $middleware->process($request, $handler);
    expect($result)->toBe($response);
});

test('validation fails with invalid body parameters', function () {
    $app = app();
    $factory = $app->get(ParamDefinitionFactory::class);

    // Define parameters for validation
    $params = [
        $factory->String(
            title: 'Test Parameter',
            name: 'test',
            validation: 'required|min:3'
        )
    ];

    // Create middleware with parameters
    $middleware = $app->make(ValidationMiddleware::class, ['params' => $params]);

    // Setup route
    $route = Mockery::mock(RouteInterface::class);
    $middleware->setRoute($route);

    // Setup request with invalid body (too short)
    $request = Mockery::mock(ServerRequestInterface::class);
    $request->shouldReceive('getParsedBody')->andReturn(['test' => 'ab']);
    $request->shouldReceive('getQueryParams')->andReturn([]);

    // Setup withQueryParams and withParsedBody properly chained
    $request->shouldReceive('withQueryParams')->andReturnSelf();
    $request->shouldReceive('withParsedBody')->andReturnSelf();

    // Setup handler (should not be called)
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $handler->shouldNotReceive('handle');

    // Test expected exception
    expect(function () use ($middleware, $request, $handler) {
        $middleware->process($request, $handler);
    })->toThrow(\League\Route\Http\Exception\BadRequestException::class);
});

test('can validate request with query parameters', function () {
    $app = app();
    $factory = $app->get(ParamDefinitionFactory::class);

    // Define parameters for validation with query parameter
    $params = [
        $factory->String(
            title: 'Query Parameter',
            name: 'query_param',
            validation: 'required|numeric'
        )
    ];

    // Create middleware with parameters
    $middleware = $app->make(ValidationMiddleware::class, ['params' => $params]);

    // Setup route
    $route = Mockery::mock(RouteInterface::class);
    $middleware->setRoute($route);

    // Setup request with valid query
    $request = Mockery::mock(ServerRequestInterface::class);
    $request->shouldReceive('getQueryParams')->andReturn(['query_param' => '123']);
    $request->shouldReceive('getParsedBody')->andReturn([]);

    // Setup withQueryParams and withParsedBody properly chained
    $request->shouldReceive('withQueryParams')->andReturnSelf();
    $request->shouldReceive('withParsedBody')->andReturnSelf();

    // Setup handler to return a response
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $handler->shouldReceive('handle')->with($request)->andReturn($response);

    // Execute middleware
    $result = $middleware->process($request, $handler);
    expect($result)->toBe($response);
});

test('can validate request with route parameters', function () {
    $app = app();
    $factory = $app->get(ParamDefinitionFactory::class);

    // Define parameters for validation with route parameter
    $params = [
        $factory->String(
            title: 'Route Parameter',
            name: 'route_param',
            validation: 'required|alpha_num'
        )
    ];

    // Create middleware with parameters
    $middleware = $app->make(ValidationMiddleware::class, ['params' => $params]);

    // Setup route with route variables
    $route = Mockery::mock(RouteInterface::class);
    $route->shouldReceive('getVars')->andReturn(['route_param' => 'abc123']);
    $middleware->setRoute($route);

    // Setup request with route params (as if they were already extracted)
    $request = Mockery::mock(ServerRequestInterface::class);
    $request->shouldReceive('getParsedBody')->andReturn(['route_param' => 'abc123']);
    $request->shouldReceive('getQueryParams')->andReturn([]);

    // Setup withQueryParams and withParsedBody properly chained
    $request->shouldReceive('withQueryParams')->andReturnSelf();
    $request->shouldReceive('withParsedBody')->andReturnSelf();

    // Setup handler to return a response
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $handler->shouldReceive('handle')->with($request)->andReturn($response);

    // Execute middleware
    $result = $middleware->process($request, $handler);
    expect($result)->toBe($response);
});

test('can validate request with inner object validation', function () {
    $app = app();
    $factory = $app->get(ParamDefinitionFactory::class);

    // Define inner object parameters
    $innerParams = [
        $factory->String(
            title: 'Inner Property',
            name: 'inner_prop',
            validation: 'required|min:3'
        )
    ];

    // Define object parameter with inner parameters
    $params = [
        $factory->Object(
            title: 'Object Parameter',
            name: 'obj',
            validation: 'required'
        )->setInner($innerParams)
    ];

    // Create middleware with parameters
    $middleware = $app->make(ValidationMiddleware::class, ['params' => $params]);

    // Setup route
    $route = Mockery::mock(RouteInterface::class);
    $middleware->setRoute($route);

    // Setup request with valid nested object
    $request = Mockery::mock(ServerRequestInterface::class);
    $request->shouldReceive('getParsedBody')->andReturn([
        'obj' => [
            'inner_prop' => 'valid value'
        ]
    ]);
    $request->shouldReceive('getQueryParams')->andReturn([]);

    // Setup withQueryParams and withParsedBody properly chained
    $request->shouldReceive('withQueryParams')->andReturnSelf();
    $request->shouldReceive('withParsedBody')->andReturnSelf();

    // Setup handler to return a response
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $handler->shouldReceive('handle')->with($request)->andReturn($response);

    // Execute middleware
    $result = $middleware->process($request, $handler);
    expect($result)->toBe($response);
});
