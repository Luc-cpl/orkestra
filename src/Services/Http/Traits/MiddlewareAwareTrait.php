<?php

namespace Orkestra\Services\Http\Traits;

use Orkestra\App;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use DI\Attribute\Inject;
use InvalidArgumentException;
use OutOfBoundsException;

trait MiddlewareAwareTrait
{
    #[Inject]
    protected App $app;

    public function getMiddlewareStack(): iterable
    {
        return $this->middleware;
    }

    public function middleware(MiddlewareInterface|string|array $middleware, array $constructor = []): self
    {
        if (!empty($constructor)) {
            $middleware = [$middleware, $constructor];
        }
        $this->middleware[] = $middleware;
        return $this;
    }

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

    public function prependMiddleware(MiddlewareInterface|string|array $middleware): self
    {
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
        $handler = is_array($middleware) ? $middleware[0] : $middleware;

        if (is_string($handler) && !class_exists($handler)) {
            /** @var array<string, array<string, string>> */
            $middlewareConfig = $this->app->config()->get('middleware');
            $middlewareStack = $middlewareConfig['stack'] ?? [];
            $originalHandler = $handler;
            $handler = $middlewareStack[$handler] ?? false;
            $middleware = is_array($middleware) ? [$handler, ...array_slice($middleware, 1)] : $handler;
            if ($handler === false) {
                throw new InvalidArgumentException(sprintf('Could not resolve "%s" middleware', $originalHandler));
            }
        }

        if ($container === null && is_string($middleware) && class_exists($middleware)) {
            $middleware = new $middleware();
        }

        if ($container !== null && is_string($middleware) && $container->has($middleware)) {
            $middleware = $container->get($middleware);
        }

        // If the middleware is an array we should resolve from App instance
        if (is_array($middleware) && $this->app->has($middleware[0])) {
            // @phpstan-ignore-next-line
            $middleware = $this->app->make(...$middleware);
        }

        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        }

        /** @var string $handler */
        throw new InvalidArgumentException(sprintf('Could not resolve "%s" middleware', $handler));
    }
}
