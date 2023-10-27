<?php

namespace Orkestra\Proxies;

use Orkestra\Interfaces\HooksInterface;
use Orkestra\Services\HooksService;

class HooksProxy implements HooksInterface
{
	protected HooksInterface $service;

	public function __construct()
	{
		$this->service = new HooksService();
	}

	public function call(string $tag, ...$args): void
	{
		$this->service->call($tag, ...$args);
	}

	public function query(string $tag, ...$args): mixed
	{
		return $this->service->query($tag, ...$args);
	}

	public function register(string $tag, callable $callback, int $priority = 10): bool
	{
		return $this->service->register($tag, $callback, $priority);
	}

	public function remove(string $tag, callable $callback, int $priority = 10): bool
	{
		return $this->service->remove($tag, $callback, $priority);
	}

	public function removeAll(string $tag, int|bool $priority = false): bool
	{
		return $this->service->removeAll($tag, $priority);
	}

	public function has(string $tag, callable|bool $callable = false): bool
	{
		return $this->service->has($tag, $callable);
	}

	public function did(string $tag): int
	{
		return $this->service->did($tag);
	}

	public function doing(string $tag): bool
	{
		return $this->service->doing($tag);
	}

	public function current(): string
	{
		return $this->service->current();
	}
}