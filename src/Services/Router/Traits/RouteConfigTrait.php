<?php

namespace Orkestra\Services\Router\Traits;

trait RouteConfigTrait
{
	protected array $config = [];

	public function setConfig(array $config): self
	{
		$this->config = $config;
		return $this;
	}

	public function getAllConfig(): array
	{
		return $this->config;
	}

	public function getConfig(string $key, mixed $default = false): mixed
	{
		return $this->config[$key] ?? $default;
	}
}
