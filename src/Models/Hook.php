<?php

namespace Orkestra\Models;

use Closure;

class Hook
{
	public readonly Closure $callback;

	public int $count = 0;

	public function __construct(
		public readonly string $name,
		public readonly int    $priority = 10,
		callable $callback,
	) {
		$this->count = 0;
		$this->callback = Closure::fromCallable($callback);
	}

	public function __invoke(...$args): mixed
	{
		$this->count++;
		return call_user_func_array($this->callback, $args);
	}
}