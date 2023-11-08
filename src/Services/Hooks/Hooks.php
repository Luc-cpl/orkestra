<?php

namespace Orkestra\Services\Hooks;

use Orkestra\Interfaces\HooksInterface;
use Orkestra\Services\Hooks\Hook;

final class Hooks implements HooksInterface
{
	protected array $hooks = [];
	protected string $current = '';

	public function call(string $tag, ...$args): void
	{
		$this->hooks[$tag] ??= [];
		$this->current = $tag;

		foreach ($this->hooks[$tag] as $hook) {
			$hook(...$args);
		}

		$this->current = '';
	}

	public function query(string $tag, ...$args): mixed
	{
		$this->hooks[$tag] ??= [];
		$this->current = $tag;

		foreach ($this->hooks[$tag] as $hook) {
			$args[0] = $hook(...$args);
		}

		$this->current = '';

		return $args[0];
	}

	public function register(string $tag, callable $callback, int $priority = 10): bool
	{
		// If the hook already exists, remove it first.
		$this->remove($tag, $callback, $priority);

		$this->hooks[$tag][] = new Hook($tag, $priority, $callback);

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

	public function removeAll(string $tag, int|bool $priority = false): bool
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

	public function has(string $tag, callable|bool $callable = false): bool
	{
		if ($callable === false) {
			return isset($this->hooks[$tag]);
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
		if (!isset($this->hooks[$tag])) {
			return 0;
		}

		$count = 0;

		/**
		 * The biggest count of all hooks registered to this tag
		 * is the number of times the tag has been called.
		 */
		foreach ($this->hooks[$tag] as $hook) {
			$count = $hook->count > $hook->count ? $hook->count : $count;
		}

		return $count;
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
