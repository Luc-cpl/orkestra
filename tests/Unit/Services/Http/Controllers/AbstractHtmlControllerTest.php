<?php

use Orkestra\Providers\HttpProvider;
use Orkestra\Providers\ViewProvider;
use Orkestra\Services\Http\Controllers\AbstractHtmlController;
use Orkestra\Services\Http\Facades\RouteDefinitionFacade;
use Orkestra\Services\Http\Interfaces\DefinitionInterface;
use Orkestra\Services\Http\Interfaces\RouteInterface;
use Orkestra\Services\Http\Factories\ParamDefinitionFactory;
use Orkestra\Services\View\Interfaces\ViewInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

test('can set a route in html controller', function () {
    /** @var RouteInterface $route */
    $route = Mockery::mock(RouteInterface::class);

    $class = new class () extends AbstractHtmlController {
        public function getRoute(): ?RouteInterface
        {
            return $this->route;
        }
    };

    // Set up a concrete ViewInterface implementation to avoid DI errors
    $view = Mockery::mock(ViewInterface::class);
    app()->provider(HttpProvider::class);
    app()->provider(ViewProvider::class);
    app()->bind(ViewInterface::class, fn () => $view);
    app()->bind(AbstractHtmlController::class, $class::class);

    $controller = app()->get(AbstractHtmlController::class);
    $controller->setRoute($route);
    expect($controller->getRoute())->toBe($route);
});

test('can render a view without route', function () {
    // Mock the ViewInterface
    $view = Mockery::mock(ViewInterface::class);
    $view->shouldReceive('render')
        ->once()
        ->with('test-view', ['key' => 'value'])
        ->andReturn('<html>Test Content</html>');

    // Mock the response stream
    $stream = Mockery::mock(StreamInterface::class);
    $stream->shouldReceive('write')
        ->once()
        ->with('<html>Test Content</html>')
        ->andReturn(strlen('<html>Test Content</html>'));

    // Mock the response
    $response = Mockery::mock(ResponseInterface::class);
    $response->shouldReceive('getBody')
        ->once()
        ->andReturn($stream);
    $response->shouldReceive('withStatus')
        ->once()
        ->with(200)
        ->andReturn($response);

    // Create a test controller
    $class = new class () extends AbstractHtmlController {
        public function testRender(string $view, array $context = [], int $status = 200): ResponseInterface
        {
            return $this->render($view, $context, $status);
        }
    };

    // Configure the app
    app()->provider(HttpProvider::class);
    app()->provider(ViewProvider::class);
    app()->bind(ViewInterface::class, fn () => $view);
    app()->bind(ResponseInterface::class, fn () => $response);
    app()->bind(AbstractHtmlController::class, $class::class);

    // Execute the test
    $controller = app()->get(AbstractHtmlController::class);
    $result = $controller->testRender('test-view', ['key' => 'value']);

    expect($result)->toBe($response);
});

test('can render a view with route', function () {
    // Mock the DefinitionInterface
    $definition = Mockery::mock(DefinitionInterface::class);

    // Create a real RouteDefinitionFacade with a mock DefinitionInterface
    $paramFactory = Mockery::mock(ParamDefinitionFactory::class);
    $routeDefinition = new RouteDefinitionFacade($paramFactory, $definition);

    // Mock the Route
    $route = Mockery::mock(RouteInterface::class);
    $route->shouldReceive('getDefinition')
        ->once()
        ->andReturn($routeDefinition);

    // Mock the ViewInterface
    $view = Mockery::mock(ViewInterface::class);
    $view->shouldReceive('render')
        ->once()
        ->with('test-view', ['key' => 'value', 'route' => $routeDefinition])
        ->andReturn('<html>Test Content With Route</html>');

    // Mock the response stream
    $stream = Mockery::mock(StreamInterface::class);
    $stream->shouldReceive('write')
        ->once()
        ->with('<html>Test Content With Route</html>')
        ->andReturn(strlen('<html>Test Content With Route</html>'));

    // Mock the response
    $response = Mockery::mock(ResponseInterface::class);
    $response->shouldReceive('getBody')
        ->once()
        ->andReturn($stream);
    $response->shouldReceive('withStatus')
        ->once()
        ->with(201)
        ->andReturn($response);

    // Create a test controller
    $class = new class () extends AbstractHtmlController {
        public function testRender(string $view, array $context = [], int $status = 200): ResponseInterface
        {
            return $this->render($view, $context, $status);
        }
    };

    // Configure the app
    app()->provider(HttpProvider::class);
    app()->provider(ViewProvider::class);
    app()->bind(ViewInterface::class, fn () => $view);
    app()->bind(ResponseInterface::class, fn () => $response);
    app()->bind(AbstractHtmlController::class, $class::class);

    // Execute the test
    $controller = app()->get(AbstractHtmlController::class);
    $controller->setRoute($route);
    $result = $controller->testRender('test-view', ['key' => 'value'], 201);

    expect($result)->toBe($response);
});
