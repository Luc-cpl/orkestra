<?php

namespace Orkestra\Services\Hooks\Interfaces;

interface HooksInterface
{
    /**
     * @param string $hook
     * @param mixed  ...$args
     * @return void
     */
    public function call(string $hook, mixed ...$args): void;

    /**
     * @template T
     * @param string $hook
     * @param T      $value
     * @param mixed  ...$args
     * @return T
     */
    public function query(string $hook, mixed $value, mixed ...$args): mixed;

    /**
     * @param string   $hook
     * @param callable $callback
     * @param int      $priority
     * @return bool
     */
    public function register(string $hook, callable $callback, int $priority = 10): bool;

    /**
     * @param string   $hook
     * @param callable $callback
     * @param int      $priority
     * @return bool
     */
    public function remove(string $hook, callable $callback, int $priority = 10): bool;

    /**
     * @param string $hook
     * @param int|false $priority
     * @return bool
     */
    public function removeAll(string $hook, int|false $priority = false): bool;

    /**
     * @param string $hook
     * @param callable|false $callable
     * @return bool
     */
    public function has(string $hook, callable|false $callable = false): bool;

    /**
     * @param string $hook
     * @return int
     */
    public function did(string $hook): int;

    /**
     * @param string $hook
     * @return bool
     */
    public function doing(string $hook): bool;

    /**
     * @return string
     */
    public function current(): string;
}
