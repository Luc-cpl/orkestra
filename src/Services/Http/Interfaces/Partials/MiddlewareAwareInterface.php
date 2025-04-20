<?php

namespace Orkestra\Services\Http\Interfaces\Partials;

use League\Route\Middleware\MiddlewareAwareInterface as LeagueMiddlewareAwareInterface;
use Psr\Http\Server\MiddlewareInterface;

interface MiddlewareAwareInterface extends LeagueMiddlewareAwareInterface
{
    /**
     * @param MiddlewareInterface|class-string|string|array{class-string|string,mixed[]} $middleware
     * @param mixed[] $constructor
     * @return $this
     */
    public function middleware(MiddlewareInterface|string|array $middleware, array $constructor = []): MiddlewareAwareInterface;

    /** @return array<MiddlewareInterface|string|array{class-string|string,mixed[]}> */
    public function getMiddlewareStack(): iterable;

    /**
     * @param array<MiddlewareInterface|class-string|string|array{class-string|string,mixed[]}> $middlewareStack
     * @return $this
     */
    public function middlewareStack(array $middlewareStack): MiddlewareAwareInterface;

    /**
     * @param MiddlewareInterface|class-string|string|array{class-string|string,mixed[]} $middleware
     * @param mixed[] $constructor
     * @return $this
     */
    public function prependMiddleware(MiddlewareInterface|string|array $middleware, array $constructor = []): MiddlewareAwareInterface;

    public function shiftMiddleware(): MiddlewareInterface;
}
