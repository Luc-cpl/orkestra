<?php

namespace Orkestra\Services\Http\Traits;

use Orkestra\Services\Http\Route;
use Psr\Http\Server\RequestHandlerInterface;

trait RouteCollectionTrait
{
    abstract public function map(
        string|array $method,
        string $path,
        callable|array|string|RequestHandlerInterface $handler
    ): Route;

    /**
     * @param string   $path
     * @param callable $handler
     * @return Route
     */
    public function delete(string $path, $handler): Route
    {
        return $this->map('DELETE', $path, $handler);
    }

    /**
     * @param string   $path
     * @param callable $handler
     * @return Route
     */
    public function get(string $path, $handler): Route
    {
        return $this->map('GET', $path, $handler);
    }

    /**
     * @param string   $path
     * @param callable $handler
     * @return Route
     */
    public function head(string $path, $handler): Route
    {
        return $this->map('HEAD', $path, $handler);
    }

    /**
     * @param string   $path
     * @param callable $handler
     * @return Route
     */
    public function options(string $path, $handler): Route
    {
        return $this->map('OPTIONS', $path, $handler);
    }

    /**
     * @param string   $path
     * @param callable $handler
     * @return Route
     */
    public function patch(string $path, $handler): Route
    {
        return $this->map('PATCH', $path, $handler);
    }

    /**
     * @param string   $path
     * @param callable $handler
     * @return Route
     */
    public function post(string $path, $handler): Route
    {
        return $this->map('POST', $path, $handler);
    }

    /**
     * @param string   $path
     * @param callable $handler
     * @return Route
     */
    public function put(string $path, $handler): Route
    {
        return $this->map('PUT', $path, $handler);
    }
}
