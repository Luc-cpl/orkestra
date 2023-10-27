<?php

namespace Orkestra\Services;

use Orkestra\Interfaces\ConfigInterface;

class ConfigService implements ConfigInterface
{
	protected array $config = [];

	public function set(string $key, mixed $value): self
	{
		$this->config[$key] = $value;
		return $this;
	}

	public function get(string $key): mixed
	{
		return match ($key) {
			'url'    => $this->getURL(),
			'assets' => $this->getURL() . '/assets',
			default  => isset($this->config[$key]) ? $this->config[$key] : false,
		};
	}

	public function has(string $key): bool
	{
		return (bool) $this->get($key);
	}

	protected function getURL(): string
	{
		$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
		$host = $this->get('host') ?? $_SERVER['HTTP_HOST'];
		return "$protocol://$host";
	}
}