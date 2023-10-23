<?php

namespace Orkestra\Interfaces;

interface HooksInterface
{
    public function call(string $hook, ...$args): void;
	public function query(string $hook, ...$args): mixed;
	public function register(string $hook, callable $callback, int $priority = 10): bool;
    public function remove(string $hook, callable $callback, int $priority = 10): bool;
    public function removeAll(string $hook, int|bool $priority = false): bool;
    public function has(string $hook, callable|bool $callable = false): bool;
    public function did(string $hook): int;
    public function doing(string $hook): bool;
    public function current(): string;
}
