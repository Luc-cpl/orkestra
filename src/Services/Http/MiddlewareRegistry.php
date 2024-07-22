<?php

namespace Orkestra\Services\Http;

use Orkestra\Interfaces\AppContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use RuntimeException;

class MiddlewareRegistry
{
	/**
	 * @var array<string, array{
	 * 		class: class-string<MiddlewareInterface>,
	 * 		origin: string,
	 * }>
	 */
	public function __construct(
		private AppContainerInterface $app,
		private array $registry = [],
	) {
		//
	}

	/**
	 * @param string|class-string<MiddlewareInterface> $alias
	 * @param mixed[] $constructor
	 * @throws RuntimeException
	 */
	public function make(string $alias, array $constructor = []): MiddlewareInterface
	{
		if ($this->app->has($alias)) {
			return $this->app->make($alias, $constructor);
		} elseif (isset($this->registry[$alias])) {
			return $this->app->make($this->registry[$alias]['class'], $constructor);
		}
		throw new RuntimeException("Middleware '{$alias}' not found in registry.");
	}

	/**
	 * Add a middleware to the registry
	 *
	 * @param class-string<MiddlewareInterface> $class
	 */
	public function registry(string $class, string $alias, string $origin = 'undefined'): void
	{
		if (isset($this->registry[$alias])) {
			// Do not allow overwriting a middleware
			return;
		}

		$this->registry[$alias] = [
			'class' => $class,
			'origin' => $origin,
		];
	}

	/**
	 * Get the middleware registry
	 *
	 * @return array<string, array{
	 * 		class: class-string<MiddlewareInterface>,
	 * 		origin: string,
	 * }>
	 */
	public function getRegistry(): array
	{
		return $this->registry;
	}
}