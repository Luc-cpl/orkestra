<?php

namespace Orkestra\Services\Router\Traits;

trait RouteConfigTrait
{
	/**
	 * @var array<string, mixed>
	 */
	protected array $config = [];

	/**
	 * @param array<string, mixed> $config
	 * @return self
	 */
	public function setConfig(array $config): self
	{
		$this->config = $config;
		return $this;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getAllConfig(): array
	{
		return $this->config;
	}

	/**
	 * @param string $key
	 * @param mixed  $default
	 * @return mixed
	 */
	public function getConfig(string $key, mixed $default = false): mixed
	{
		return $this->config[$key] ?? $default;
	}
}
