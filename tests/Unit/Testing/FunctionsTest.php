<?php

use Orkestra\Providers\HttpProvider;
use Orkestra\Services\Http\Interfaces\RouterInterface;
use Psr\Http\Server\MiddlewareInterface;

test('can process a middleware', function () {
    app()->bind(MiddlewareInterface::class, function (string $type) {
        expect($type)->toBe('test');

        $middleware = Mockery::mock(MiddlewareInterface::class);
        $middleware->shouldReceive('process')->andReturnUsing(function ($request, $handler) {
            $request = $request->withHeader('x-test', 'test');
            $response = $handler->handle($request);
            return $response->withHeader('x-test', 'test');
        });

        return $middleware;
    });

    $middleware = middleware(MiddlewareInterface::class, ['type' => 'test']);
    $response = $middleware->process();
    expect($response->getHeaderLine('x-test'))->toBe('test');
    expect($middleware->request)->not()->toBeNull();
    expect($middleware->request->getHeaderLine('x-test'))->toBe('test');
});

test('can process a request', function () {
    app()->provider(HttpProvider::class);
    app()->get(RouterInterface::class)->get('/', fn ($request) => $request->getHeaderLine('x-test'));
    $response = request('GET', '/', [], ['x-test' => 'test']);
    expect((string) $response->getBody())->toBe('test');
});

test('can throw an exception when no router is found', function () {
    request();
})->throws(RuntimeException::class, sprintf('No router found in container! Did you forget to register the %s in your test?', HttpProvider::class));
