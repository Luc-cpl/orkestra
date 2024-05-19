<?php

use Orkestra\App;
use Orkestra\Entities\EntityFactory;
use Orkestra\Services\Http\Interfaces\RouterInterface;
use Pest\Support\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
        /** @var ServerRequestInterface */
        $request = app()->get(ServerRequestInterface::class);

        foreach ($headers as $key => $value) {
            $request = $request->withHeader($key, $value);
        }

        if ($method === 'GET' && !empty($data)) {
            $request = $request->withQueryParams($data);
            $data = [];
        }

        $request = $request
            ->withMethod($method)
            ->withUri($request->getUri()->withPath($uri))
            ->withParsedBody($data);

        $router = app()->get(RouterInterface::class);
        return $router->dispatch($request);
    }
}
