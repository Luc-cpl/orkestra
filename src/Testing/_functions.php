<?php

use Orkestra\App;
use Orkestra\Entities\EntityFactory;
use Orkestra\Services\Http\Interfaces\RouterInterface;
use Pest\Support\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Orkestra\Testing\Middleware;
use Laminas\Diactoros\ServerRequestFactory;

if (!function_exists('app')) {
    /**
     * Return the App instance
     *
     * @return App
     */
    function app(): App
    {
        /** @var App */
        return Container::getInstance()->get(App::class);
    }
}

if (!function_exists('factory')) {
    /**
     * Return the EntityFactory instance
     * with faker enabled
     *
     * @return EntityFactory
     */
    function factory(): EntityFactory
    {
        /** @var EntityFactory */
        return app()->make(EntityFactory::class, ['useFaker' => true]);
    }
}

if (!function_exists('request')) {
    /**
     * Return the Request instance
     *
     * @param string $method
     * @param string $uri
     * @param mixed[] $data
     * @param array<string, string> $headers
     * @return ResponseInterface
     */
    function request(string $method = 'GET', string $uri = '/', array $data = [], array $headers = []): ResponseInterface
    {
        $request = generateRequest($method, $uri, $data, $headers);
        $router = app()->get(RouterInterface::class);
        return $router->dispatch($request);
    }
}

if (!function_exists('middleware')) {
    /**
     * Create a middleware testing instance
     *
     * @param string $class
     * @param mixed[] $params
     */
    function middleware(string $class, array $params = []): Middleware
    {
        return factory()->make(Middleware::class, name: $class, params: $params);
    }
}

if (!function_exists('generateRequest')) {
    /**
     * Generate a request
     *
     * @param string $method
     * @param string $uri
     * @param mixed[] $data
     * @param array<string, string> $headers
     * @return ServerRequestInterface
     */
    function generateRequest(string $method = 'GET', string $uri = '/', array $data = [], array $headers = []): ServerRequestInterface
    {
        $request = ServerRequestFactory::fromGlobals();

        foreach ($headers as $key => $value) {
            $request = $request->withHeader($key, $value);
        }

        if ($method === 'GET' && !empty($data)) {
            $request = $request->withQueryParams($data);
            $data = [];
        }

        return $request
            ->withMethod($method)
            ->withUri($request->getUri()->withPath($uri))
            ->withParsedBody($data);
    }
}
