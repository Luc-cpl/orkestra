<?php

namespace Orkestra\Facades;

use Orkestra\Interfaces\ConfigInterface;

class ConfigFacade implements ConfigInterface
{
	public function __construct(
		protected ConfigInterface $proxy,
	) {
	}

	public function set(string $key, mixed $value): ConfigInterface
	{
		return $this->proxy->set($key, $value);
	}

	public function get(string $key): mixed
	{
		return $this->proxy->get($key);
	}

	public function has(string $key): bool
	{
		return $this->proxy->has($key);
	}
}