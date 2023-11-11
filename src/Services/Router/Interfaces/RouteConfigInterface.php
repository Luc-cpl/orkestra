<?php

namespace Orkestra\Services\Router\Interfaces;

interface RouteConfigInterface
{
	public function setConfig(array $config): self;
	public function getAllConfig(): array;
	public function getConfig(string $key, mixed $default = false): mixed;
}
