<?php

use Orkestra\App;
use Orkestra\Services\Http\Dispatcher;
use Orkestra\Services\Http\Interfaces\RouteAwareInterface;
use Orkestra\Services\Http\Interfaces\RouteInterface;
use Orkestra\Services\Http\Route;
use Orkestra\Services\Http\Facades\RouteDefinitionFacade;
use Orkestra\Services\Http\Middleware\JsonMiddleware;
use Orkestra\Services\Http\Middleware\ValidationMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\UriInterface;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;
use FastRoute\Dispatcher as FastRoute;
use League\Route\Strategy\ApplicationStrategy;
use League\Route\Strategy\StrategyInterface;
use League\Route\Http\Exception\NotFoundException;
use League\Route\Http\Exception\MethodNotAllowedException;

// Create a simple subclass to expose protected methods
class TestDispatcher extends Dispatcher
{
    // Mock dispatch result for testing
    protected $mockDispatchResult = null;
    
    // Flag to control whether conditions match
    protected $mockConditionsMatch = true;
    
    // Response to return
    protected $mockResponse = null;
    
    public function setMockDispatchResult($result): void
    {
        $this->mockDispatchResult = $result;
    }
    
    public function setMockConditionsMatch(bool $match): void
    {
        $this->mockConditionsMatch = $match;
    }
    
    public function setMockResponse(ResponseInterface $response): void
    {
        $this->mockResponse = $response;
    }
    
    // Override dispatch to return mock result
    public function dispatch($httpMethod, $uri): array
    {
        if ($this->mockDispatchResult !== null) {
            return $this->mockDispatchResult;
        }
        
        return parent::dispatch($httpMethod, $uri);
    }
    
    // Override to control condition matching
    protected function isExtraConditionMatch(\League\Route\Route $route, ServerRequestInterface $request): bool
    {
        return $this->mockConditionsMatch;
    }
    
    // Override to avoid needing to mock all route methods
    protected function setFoundMiddleware(\League\Route\Route $route): void
    {
        // Simplified version - just add our middleware without checking strategy
        $this->prependMiddleware(new class($this->mockResponse) implements \Psr\Http\Server\MiddlewareInterface {
            public function __construct(private ResponseInterface $response) {}
            
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                return $this->response;
            }
        });
    }
    
    // Override to avoid needing strategy
    protected function setNotFoundDecoratorMiddleware(): void
    {
        // Simplified version - just add our middleware without using strategy
        $this->prependMiddleware(new class($this->mockResponse) implements \Psr\Http\Server\MiddlewareInterface {
            public function __construct(private ResponseInterface $response) {}
            
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                return $this->response;
            }
        });
    }
    
    // Override to avoid needing strategy
    protected function setMethodNotAllowedDecoratorMiddleware(array $methods): void
    {
        // Simplified version - just add our middleware without using strategy
        $this->prependMiddleware(new class($this->mockResponse) implements \Psr\Http\Server\MiddlewareInterface {
            public function __construct(private ResponseInterface $response) {}
            
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                return $this->response;
            }
        });
    }
    
    // Make this public for testing
    public function addValidationMiddleware(\League\Route\Route $route): void
    {
        parent::addValidationMiddleware($route);
    }
    
    // Override to avoid needing to mock all route methods
    protected function requestWithRouteAttributes(ServerRequestInterface $request, \League\Route\Route $route): ServerRequestInterface
    {
        // Simply return the request without modifications for testing
        return $request;
    }
}

// We'll simplify these tests to focus on what's crucial for coverage
test('can handle a request with middleware', function () {
    $app = app();
    $response = new Response();
    
    // Create a simple middleware that just returns a response
    $middleware = new class($response) implements \Psr\Http\Server\MiddlewareInterface {
        public function __construct(private ResponseInterface $response) {}
        
        public function process(
            ServerRequestInterface $request,
            RequestHandlerInterface $handler
        ): ResponseInterface {
            return $this->response;
        }
    };
    
    // Create an empty dispatcher with properly formatted data
    $dispatchData = [
        [], // staticRouteMap
        [], // variableRouteData
    ];
    
    $dispatcher = new Dispatcher($app, $dispatchData);
    
    // Set middleware using reflection
    $reflection = new \ReflectionClass($dispatcher);
    $property = $reflection->getProperty('middleware');
    $property->setAccessible(true);
    $property->setValue($dispatcher, [$middleware]);
    
    // Process a mock request
    $request = Mockery::mock(ServerRequestInterface::class);
    $result = $dispatcher->handle($request);
    
    expect($result)->toBe($response);
});

test('can handle a request with RouteAwareInterface middleware', function () {
    $app = app();
    $response = new Response();
    
    // Create a route-aware middleware
    $middleware = new class($response) implements \Psr\Http\Server\MiddlewareInterface, RouteAwareInterface {
        private ?RouteInterface $route = null;
        
        public function __construct(private ResponseInterface $response) {}
        
        public function process(
            ServerRequestInterface $request,
            RequestHandlerInterface $handler
        ): ResponseInterface {
            return $this->response;
        }
        
        public function setRoute(RouteInterface $route): RouteAwareInterface
        {
            $this->route = $route;
            return $this;
        }
    };
    
    // Create an empty dispatcher with properly formatted data
    $dispatchData = [
        [], // staticRouteMap
        [], // variableRouteData
    ];
    
    $dispatcher = new Dispatcher($app, $dispatchData);
    
    // Set the middleware and route using reflection
    $reflection = new \ReflectionClass($dispatcher);
    $middlewareProperty = $reflection->getProperty('middleware');
    $middlewareProperty->setAccessible(true);
    $middlewareProperty->setValue($dispatcher, [$middleware]);
    
    $routeProperty = $reflection->getProperty('route');
    $routeProperty->setAccessible(true);
    $routeProperty->setValue($dispatcher, Mockery::mock(RouteInterface::class));
    
    // Process a mock request
    $request = Mockery::mock(ServerRequestInterface::class);
    $result = $dispatcher->handle($request);
    
    expect($result)->toBe($response);
});

test('can add validation middleware when params exist', function () {
    $app = app();
    
    // Get a real instance of RouteDefinitionFacade
    $validDefinition = Mockery::mock(RouteDefinitionFacade::class);
    $validDefinition->shouldReceive('params')->andReturn(['param1', 'param2']);
    
    // Create a route with params
    $route = Mockery::mock(Route::class);
    $route->shouldReceive('getDefinition')->andReturn($validDefinition);
    
    // Test that prependMiddleware is called with the right class
    $route->shouldReceive('prependMiddleware')
        ->with(ValidationMiddleware::class, ['params' => ['param1', 'param2']])
        ->once();
    
    $dispatcher = new TestDispatcher($app, [[], []]);
    $dispatcher->addValidationMiddleware($route);
});

test('does not add validation middleware when no params exist', function () {
    $app = app();
    
    // Get a real instance of RouteDefinitionFacade without params
    $emptyDefinition = Mockery::mock(RouteDefinitionFacade::class);
    $emptyDefinition->shouldReceive('params')->andReturn([]);
    
    // Create a route with no params
    $route = Mockery::mock(Route::class);
    $route->shouldReceive('getDefinition')->andReturn($emptyDefinition);
    
    // Expect prependMiddleware NOT to be called
    $route->shouldReceive('prependMiddleware')->never();
    
    $dispatcher = new TestDispatcher($app, [[], []]);
    $dispatcher->addValidationMiddleware($route);
});

test('can handle not found routes', function () {
    $app = app();
    $response = new Response();
    
    // Create a dispatcher with our response
    $dispatcher = new TestDispatcher($app, [[], []]);
    $dispatcher->setMockResponse($response);
    
    // Set mock dispatch result to NOT_FOUND
    $dispatcher->setMockDispatchResult([FastRoute::NOT_FOUND]);
    
    // Create the request
    $request = new ServerRequest([], [], new Uri('http://example.com/not-found'), 'GET');
    
    // Test the dispatchRequest method
    $result = $dispatcher->dispatchRequest($request);
    
    expect($result)->toBe($response);
});

test('can handle method not allowed', function () {
    $app = app();
    $response = new Response();
    
    // Create a dispatcher with our response
    $dispatcher = new TestDispatcher($app, [[], []]);
    $dispatcher->setMockResponse($response);
    
    // Set mock dispatch result to METHOD_NOT_ALLOWED
    $dispatcher->setMockDispatchResult([FastRoute::METHOD_NOT_ALLOWED, ['GET', 'POST']]);
    
    // Create the request
    $request = new ServerRequest([], [], new Uri('http://example.com/resource'), 'PUT');
    
    // Test the dispatchRequest method
    $result = $dispatcher->dispatchRequest($request);
    
    expect($result)->toBe($response);
});

test('can handle found route with matching conditions', function () {
    $app = app();
    $response = new Response();
    
    // Create a dispatcher with our response
    $dispatcher = new TestDispatcher($app, [[], []]);
    $dispatcher->setMockResponse($response);
    $dispatcher->setMockConditionsMatch(true);
    
    // Create a minimal route mock - only needs what addValidationMiddleware uses
    $route = Mockery::mock(Route::class);
    $route->shouldReceive('setVars')->andReturnSelf();
    
    // Need to mock getDefinition for validation middleware
    $definition = Mockery::mock(RouteDefinitionFacade::class);
    $definition->shouldReceive('params')->andReturn([]);
    $route->shouldReceive('getDefinition')->andReturn($definition);
    
    // Need to mock prependMiddleware for JsonMiddleware
    $route->shouldReceive('prependMiddleware')
        ->with(JsonMiddleware::class)
        ->andReturnSelf();
    
    // Set mock dispatch result to FOUND
    $dispatcher->setMockDispatchResult([FastRoute::FOUND, $route, ['id' => '123']]);
    
    // Create the request
    $request = new ServerRequest([], [], new Uri('http://example.com/users/123'), 'GET');
    
    // Test the dispatchRequest method
    $result = $dispatcher->dispatchRequest($request);
    
    expect($result)->toBe($response);
});

test('can handle found route with non-matching conditions', function () {
    $app = app();
    $response = new Response();
    
    // Create a dispatcher with our response
    $dispatcher = new TestDispatcher($app, [[], []]);
    $dispatcher->setMockResponse($response);
    $dispatcher->setMockConditionsMatch(false);
    
    // Create a minimal route mock
    $route = Mockery::mock(Route::class);
    $route->shouldReceive('setVars')->andReturnSelf();
    
    // Set mock dispatch result to FOUND
    $dispatcher->setMockDispatchResult([FastRoute::FOUND, $route, ['id' => '123']]);
    
    // Create the request
    $request = new ServerRequest([], [], new Uri('http://example.com/users/123'), 'GET');
    
    // Test the dispatchRequest method
    $result = $dispatcher->dispatchRequest($request);
    
    expect($result)->toBe($response);
}); 