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

	/**
	 * An array with MiddlewareInterface, class-string
	 * or an array with a string as first element and
	 * the rest as parameters.
	 *
	 * @var array<MiddlewareInterface|string|array{string,mixed}>
	 * @phpstan-ignore-next-line
	 */
	protected $middleware = [];

	public function getMiddlewareStack(): iterable
	{
		return $this->middleware;
	}

	public function middleware(MiddlewareInterface|string|array $middleware): self
	{
		$this->middleware[] = $middleware;
		return $this;
	}

	public function middlewareStack(array $middlewareStack): self
	{
		foreach ($middlewareStack as $middleware) {
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
	 * @param MiddlewareInterface|string|array{string,mixed} $middleware
	 */
	protected function resolveMiddleware($middleware, ?ContainerInterface $container = null): MiddlewareInterface
	{
		$handler = is_array($middleware) ? $middleware[0] : $middleware;

		if (is_string($handler) && !class_exists($handler)) {
			$originalHandler = $handler;
			$handler = $this->app->config()->get('middleware')[$handler] ?? false;
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
			$middleware = $this->app->get(...$middleware);
		}

		if ($middleware instanceof MiddlewareInterface) {
			return $middleware;
		}

		$middleware = is_array($middleware) ? $middleware[0] : $middleware;

		/** @var string $middleware */
		throw new InvalidArgumentException(sprintf('Could not resolve "%s" middleware', $originalHandler));
	}
}
