<?php

namespace Orkestra\Services\Hooks;

use Orkestra\Services\Hooks\Interfaces\HooksInterface;
use Orkestra\Services\Hooks\Hook;

final class Hooks implements HooksInterface
{
	/**
	 * @var array<string, array<Hook>>
	 */
	protected array $hooks = [];

	protected string $current = '';

	/**
	 * @var array<string, int>
	 */
	protected array $executionCount = [];

	public function call(string $tag, mixed ...$args): void
	{
		$this->hooks[$tag] ??= [];
		$this->current = $tag;

		foreach ($this->hooks[$tag] as $hook) {
			$hook(...$args);
		}

		$this->executionCount[$tag] ??= 0;
		$this->executionCount[$tag]++;

		$this->current = '';
	}

	public function query(string $tag, mixed $value, mixed ...$args): mixed
	{
		$this->hooks[$tag] ??= [];
		$this->current = $tag;

		foreach ($this->hooks[$tag] as $hook) {
			$value = $hook($value, ...$args);
		}

		$this->executionCount[$tag] ??= 0;
		$this->executionCount[$tag]++;

		$this->current = '';

		return $value;
	}

	public function register(string $tag, callable $callback, int $priority = 10): bool
	{
		// If the hook already exists, remove it first.
		$this->remove($tag, $callback, $priority);

		$this->hooks[$tag][] = new Hook($tag, $callback, $priority);

		return usort($this->hooks[$tag], function ($a, $b) {
			return $a->priority <=> $b->priority;
		});
	}

	public function remove(string $tag, callable $callback, int $priority = 10): bool
	{
		$this->hooks[$tag] ??= [];

		foreach ($this->hooks[$tag] as $index => $hook) {
			if ($hook->priority === $priority && $hook->isSameCallback($callback)) {
				unset($this->hooks[$tag][$index]);
			}
		}

		return true;
	}

	public function removeAll(string $tag, int|false $priority = false): bool
	{
		if ($priority === false) {
			unset($this->hooks[$tag]);
			return true;
		}

		$this->hooks[$tag] ??= [];

		foreach ($this->hooks[$tag] as $index => $hook) {
			if ($hook->priority === $priority) {
				unset($this->hooks[$tag][$index]);
			}
		}

		return true;
	}

	public function has(string $tag, callable|false $callable = false): bool
	{
		if ($callable === false) {
			return isset($this->hooks[$tag]) && count($this->hooks[$tag]) > 0;
		}

		$this->hooks[$tag] ??= [];

		foreach ($this->hooks[$tag] as $hook) {
			if ($hook->isSameCallback($callable)) {
				return true;
			}
		}

		return false;
	}

	public function did(string $tag): int
	{
		if (!isset($this->executionCount[$tag])) {
			return 0;
		}

		return $this->executionCount[$tag];
	}

	public function doing(string $tag): bool
	{
		return $this->current === $tag;
	}

	public function current(): string
	{
		return $this->current;
	}
}
