<?php

namespace Orkestra\Interfaces;

interface ConfigInterface
{
	public function set(string $key, mixed $value): self;
    public function get(string $key): mixed;
	public function has(string $key): bool;
}