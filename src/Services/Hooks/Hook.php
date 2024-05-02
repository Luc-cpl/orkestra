<?php

namespace Orkestra\Services\Hooks;

use Closure;
use ReflectionFunction;

class Hook
{
    private Closure $callback;

    public function __construct(
        public readonly string $name,
        callable $callback,
        public readonly int $priority = 10,
    ) {
        $this->callback = Closure::fromCallable($callback);
    }

    public function isSameCallback(callable $callback): bool
    {
        $callback = Closure::fromCallable($callback);
        $cbString = (string) new ReflectionFunction($callback);
        return $cbString === (string) new ReflectionFunction($this->callback);
    }

    public function __invoke(mixed ...$args): mixed
    {
        return call_user_func_array($this->callback, $args);
    }
}
