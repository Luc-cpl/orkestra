<?php

namespace Orkestra\Facades;

use Orkestra\Interfaces\HooksInterface;

class HooksFacade implements HooksInterface
{
	public function __construct(
		protected HooksInterface $proxy,
	) {
	}

	public function call(string $tag, ...$args): void
	{
		$this->proxy->call($tag, ...$args);
	}

	public function query(string $tag, ...$args): mixed
	{
		return $this->proxy->query($tag, ...$args);
	}

	public function register(string $tag, callable $callback, int $priority = 10): bool
	{
		return $this->proxy->register($tag, $callback, $priority);
	}

	public function remove(string $tag, callable $callback, int $priority = 10): bool
	{
		return $this->proxy->remove($tag, $callback, $priority);
	}

	public function removeAll(string $tag, int|bool $priority = false): bool
	{
		return $this->proxy->removeAll($tag, $priority);
	}

	public function has(string $tag, callable|bool $callable = false): bool
	{
		return $this->proxy->has($tag, $callable);
	}

	public function did(string $tag): int
	{
		return $this->proxy->did($tag);
	}

	public function doing(string $tag): bool
	{
		return $this->proxy->doing($tag);
	}

	public function current(): string
	{
		return $this->proxy->current();
	}
}
