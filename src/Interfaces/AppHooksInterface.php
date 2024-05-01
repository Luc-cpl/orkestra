<?php

namespace Orkestra\Interfaces;

interface AppHooksInterface
{
    /**
     * Call a hook if the service is available
     *
     * @param string $tag
     * @param mixed  ...$args
     * @return void
     */
    public function hookCall(string $tag, ...$args): void;

    /**
     * Query a hook if the service is available
     * Return the first argument if the hook is not available
     *
     * @template TKey
     * @param string $tag
     * @param TKey   $value
     * @param mixed  ...$args
     * @return TKey
     */
    public function hookQuery(string $tag, mixed $value, mixed ...$args): mixed;

    /**
     * Add a hook if the service is available
     *
     * @param string   $tag
     * @param callable $callback
     * @param int      $priority
     * @return bool
     */
    public function hookRegister(string $tag, callable $callback, int $priority = 10): bool;
}
