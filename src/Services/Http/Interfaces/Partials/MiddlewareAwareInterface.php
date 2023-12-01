<?php

namespace Orkestra\Services\Http\Interfaces\Partials;

use Psr\Http\Server\MiddlewareInterface;

interface MiddlewareAwareInterface
{
	/**
	 * @param MiddlewareInterface|string|array{string,mixed} $middleware
	 * @return $this
	 */
	public function middleware(MiddlewareInterface|string|array $middleware): self;

	/** @return array<MiddlewareInterface|string|array{string,mixed}> */
	public function getMiddlewareStack(): iterable;

	/**
	 * @param string|array{string,mixed} $middleware
	 * @return $this
	 */
	public function lazyMiddleware(string|array $middleware): self;

	/**
	 * @param array<string|array{string,mixed}> $middlewares
	 * @return $this
	 */
	public function lazyMiddlewares(array $middlewares): self;

	/** @return $this */
	public function lazyPrependMiddleware(string $middleware): self;

	/**
	 * @param MiddlewareInterface[] $middlewares
	 * @return $this
	 */
	public function middlewares(array $middlewares): self;

	/** @return $this */
	public function prependMiddleware(MiddlewareInterface $middleware): self;

	public function shiftMiddleware(): MiddlewareInterface;
}
