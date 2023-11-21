<?php

namespace Orkestra\Services\Http\Interfaces;

interface RouteConfigInterface
{
	/**
	 * @param array<string, mixed> $config
	 * @return self
	 */
	public function setConfig(array $config): self;

	/**
	 * @return array<string, mixed>
	 */
	public function getAllConfig(): array;

	/**
	 * @param string $key
	 * @param mixed  $default
	 * @return mixed
	 */
	public function getConfig(string $key, mixed $default = false): mixed;
}
