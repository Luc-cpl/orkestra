<?php

use Orkestra\Services\Http\Strategy\ApplicationStrategy;
use League\Route\Http\Exception\{MethodNotAllowedException, NotFoundException};
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

covers(ApplicationStrategy::class);

test('can get method not allowed decorator', function () {
    $app = app();
    $strategy = new ApplicationStrategy($app);

    $exception = new MethodNotAllowedException(['GET'], 'Method not allowed');
    $middleware = $strategy->getMethodNotAllowedDecorator($exception);

    expect($middleware)->toBeInstanceOf(MiddlewareInterface::class);

    // Test middleware throws the exception
    $request = Mockery::mock(ServerRequestInterface::class);
    $handler = Mockery::mock(RequestHandlerInterface::class);

    try {
        $middleware->process($request, $handler);
        expect(false)->toBeTrue(); // Should not reach here
    } catch (MethodNotAllowedException $e) {
        expect($e)->toBe($exception);
    }
});

test('can get not found decorator', function () {
    $app = app();
    $strategy = new ApplicationStrategy($app);

    $exception = new NotFoundException('Not found');
    $middleware = $strategy->getNotFoundDecorator($exception);

    expect($middleware)->toBeInstanceOf(MiddlewareInterface::class);

    // Test middleware throws the exception
    $request = Mockery::mock(ServerRequestInterface::class);
    $handler = Mockery::mock(RequestHandlerInterface::class);

    try {
        $middleware->process($request, $handler);
        expect(false)->toBeTrue(); // Should not reach here
    } catch (NotFoundException $e) {
        expect($e)->toBe($exception);
    }
});

test('can get throwable handler', function () {
    $app = app();
    $strategy = new ApplicationStrategy($app);

    $middleware = $strategy->getThrowableHandler();
    expect($middleware)->toBeInstanceOf(MiddlewareInterface::class);

    // Test middleware passes through to handler if no exception
    $request = Mockery::mock(ServerRequestInterface::class);
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $response = Mockery::mock(ResponseInterface::class);

    $handler->shouldReceive('handle')
        ->with($request)
        ->andReturn($response);

    $result = $middleware->process($request, $handler);
    expect($result)->toBe($response);

    // Test middleware throws exception if handler throws
    $exception = new \Exception('Test exception');
    $handler2 = Mockery::mock(RequestHandlerInterface::class);
    $handler2->shouldReceive('handle')
        ->with($request)
        ->andThrow($exception);

    try {
        $middleware->process($request, $handler2);
        expect(false)->toBeTrue(); // Should not reach here
    } catch (\Exception $e) {
        expect($e)->toBe($exception);
    }
});
