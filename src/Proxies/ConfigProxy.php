<?php

namespace Orkestra\Proxies;

use Orkestra\Interfaces\ConfigInterface;
use Orkestra\Services\ConfigService;

class ConfigProxy implements ConfigInterface
{
	protected ConfigInterface $service;

	public function __construct()
	{
		$this->service = new ConfigService();
	}

	public function set(string $key, mixed $value): ConfigInterface
	{
		return $this->service->set($key, $value);
	}

	public function get(string $key): mixed
	{
		return $this->service->get($key);
	}

	public function has(string $key): bool
	{
		return $this->service->has($key);
	}
}