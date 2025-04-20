<?php

namespace Orkestra\Services\Http\Traits;

use Orkestra\Services\Http\MiddlewareRegistry;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use OutOfBoundsException;

trait MiddlewareAwareTrait
{
    public function getMiddlewareStack(): iterable
    {
        return $this->middleware;
    }

    /**
     * @return $this
     */
    public function middleware(MiddlewareInterface|string|array $middleware, array $constructor = []): self
    {
        if (!empty($constructor)) {
            $middleware = [$middleware, $constructor];
        }
        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * @return $this
     */
    public function middlewareStack(array $middlewareStack): self
    {
        foreach ($middlewareStack as $middleware) {
            if (is_array($middleware)) {
                $this->middleware(...$middleware);
                continue;
            }
            $this->middleware($middleware);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function prependMiddleware(MiddlewareInterface|string|array $middleware, array $constructor = []): self
    {
        if (!empty($constructor)) {
            $middleware = [$middleware, $constructor];
        }
        array_unshift($this->middleware, $middleware);
        return $this;
    }

    public function shiftMiddleware(): MiddlewareInterface
    {
        $middleware =  array_shift($this->middleware);

        if ($middleware === null) {
            throw new OutOfBoundsException('Reached end of middleware stack. Does your controller return a response?');
        }

        return $middleware;
    }

    /**
     * @param MiddlewareInterface|string|array{string,array<string,mixed>} $middleware
     */
    protected function resolveMiddleware($middleware, ?ContainerInterface $container = null): MiddlewareInterface
    {
        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        }

        $alias = is_array($middleware) ? $middleware[0] : $middleware;
        $constructor = is_array($middleware) ? $middleware[1] : [];

        if ($container === null) {
            /** @var MiddlewareInterface */
            return new $alias(...$constructor);
        }

        /** @var MiddlewareRegistry */
        $registry = $container->get(MiddlewareRegistry::class);
        return $registry->make($alias, $constructor);
    }
}
