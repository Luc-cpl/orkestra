<?php

namespace Orkestra\Traits;

use Orkestra\Services\Hooks\Interfaces\HooksInterface;

/**
 * Implement hooks functionality for the application.
 *
 * This allow call and query internal application hooks without
 * the need to check if the service is available and without a specific app slug.
 *
 * @see \Orkestra\Services\Hooks\Interfaces\HooksInterface
 */
trait AppHooksTrait
{
	abstract function slug(): string;
	abstract function runIfAvailable(string $interface, callable $callback): mixed;
	abstract function has(string $tag): bool;

	public function hookCall(string $tag, ...$args): void
	{
		$this->runIfAvailable(HooksInterface::class, function (HooksInterface $hooks) use ($tag, $args) {
			$hooks->call("{$this->slug()}.$tag", ...$args);
		});
	}

	public function hookQuery(string $tag, mixed $value, mixed ...$args): mixed
	{
		if (!$this->has(HooksInterface::class)) {
			return $value;
		}

		return $this->runIfAvailable(HooksInterface::class, function (HooksInterface $hooks) use ($tag, $value, $args) {
			return $hooks->query("{$this->slug()}.$tag", $value, ...$args);
		});
	}

	public function hookRegister(string $tag, callable $callback, int $priority = 10): bool
	{
		/** @var bool */
		return $this->runIfAvailable(HooksInterface::class, function (HooksInterface $hooks) use ($tag, $callback, $priority): bool {
			return $hooks->register("{$this->slug()}.$tag", $callback, $priority);
		});
	}
}
