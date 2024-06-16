<?php

namespace Orkestra\Services\Http\Interfaces\Partials;

use Psr\Http\Server\MiddlewareInterface;

interface MiddlewareAwareInterface
{
    /**
     * @param MiddlewareInterface|class-string|string|array{class-string|string,mixed[]} $middleware
     * @param mixed[] $constructor
     * @return $this
     */
    public function middleware(MiddlewareInterface|string|array $middleware, array $constructor = []): self;

    /** @return array<MiddlewareInterface|string|array{class-string|string,mixed[]}> */
    public function getMiddlewareStack(): iterable;

    /**
     * @param array<MiddlewareInterface|class-string|string|array{class-string|string,mixed[]}> $middlewareStack
     * @return $this
     */
    public function middlewareStack(array $middlewareStack): self;

    /**
     * @param MiddlewareInterface|string|array{class-string|string,mixed[]} $middleware
     * @return $this
     */
    public function prependMiddleware(MiddlewareInterface|string|array $middleware): self;

    public function shiftMiddleware(): MiddlewareInterface;
}
