<?php

use Psr\Http\Server\MiddlewareInterface;

test('can process a middleware', function () {
    $middleware = Mockery::mock(MiddlewareInterface::class);
    $middleware->shouldReceive('process')->andReturnUsing(function ($request, $handler) {
        $request = $request->withHeader('x-test', 'test');
        $response = $handler->handle($request);
        return $response->withHeader('x-test', 'test');
    });

    app()->bind(MiddlewareInterface::class, $middleware);
    $middleware = middleware(MiddlewareInterface::class);
    $response = $middleware->process();
    expect($response->getHeaderLine('x-test'))->toBe('test');
    expect($middleware->request)->not()->toBeNull();
    expect($middleware->request->getHeaderLine('x-test'))->toBe('test');
});
