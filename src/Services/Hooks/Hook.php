<?php

namespace Orkestra\Services\Hooks;

use Closure;
use ReflectionFunction;

class Hook
{
	protected Closure $callback;

	public int $count = 0;

	public function __construct(
		public readonly string $name,
		public readonly int    $priority = 10,
		callable $callback = null,
	) {
		$this->count = 0;
		$this->callback = Closure::fromCallable($callback);
	}

	public function isSameCallback(callable $callback): bool
	{

		$callback = Closure::fromCallable($callback);
		$cbString = (string) new ReflectionFunction($callback);
		return $cbString === (string) new ReflectionFunction($this->callback);
	}

	public function __invoke(...$args): mixed
	{
		$this->count++;
		return call_user_func_array($this->callback, $args);
	}
}
