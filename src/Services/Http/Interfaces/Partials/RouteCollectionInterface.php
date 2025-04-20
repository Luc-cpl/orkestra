<?php

namespace Orkestra\Services\Http\Interfaces\Partials;

use Orkestra\Services\Http\Interfaces\RouteInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface RouteCollectionInterface
{
    /**
     * @param class-string|callable $handler
     */
    public function delete(string $path, $handler): RouteInterface;

    /**
     * @param class-string|callable $handler
     */
    public function get(string $path, $handler): RouteInterface;

    /**
     * @param class-string|callable $handler
     */
    public function head(string $path, $handler): RouteInterface;

    /**
     * @param string|array<string> $method
     * @param callable|array<string>|class-string|RequestHandlerInterface $handler
     */
    public function map(
        string|array $method,
        string $path,
        callable|array|string|RequestHandlerInterface $handler
    ): RouteInterface;

    /**
     * @param class-string|callable $handler
     */
    public function options(string $path, $handler): RouteInterface;

    /**
     * @param class-string|callable $handler
     */
    public function patch(string $path, $handler): RouteInterface;

    /**
     * @param class-string|callable $handler
     */
    public function post(string $path, $handler): RouteInterface;

    /**
     * @param class-string|callable $handler
     */
    public function put(string $path, $handler): RouteInterface;
}
