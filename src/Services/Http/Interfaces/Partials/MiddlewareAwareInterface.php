<?php

namespace Orkestra\Services\Http\Interfaces\Partials;

use Psr\Http\Server\MiddlewareInterface;

interface MiddlewareAwareInterface
{
    /**
     * @param MiddlewareInterface|class-string|string $middleware
     * @param array<string, mixed> $constructor
     * @return $this
     */
    public function middleware(MiddlewareInterface|string $middleware, array $constructor = []): self;

    /** @return array<MiddlewareInterface|string|array{string,mixed}> */
    public function getMiddlewareStack(): iterable;

    /**
     * @param array<MiddlewareInterface|string|array{string,mixed}> $middlewareStack
     * @return $this
     */
    public function middlewareStack(array $middlewareStack): self;

    /**
     * @param MiddlewareInterface|string|array{string,mixed} $middleware
     * @return $this
     */
    public function prependMiddleware(MiddlewareInterface|string|array $middleware): self;

    public function shiftMiddleware(): MiddlewareInterface;
}
