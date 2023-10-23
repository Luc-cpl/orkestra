<?php

namespace Orkestra;

use Orkestra\Facades\HooksFacade;
use Orkestra\Proxies\HooksProxy;

class App
{
	protected array $services = [];

	public function __construct()
	{
		// Define default services
		$this->addService('hooks', fn() => new HooksFacade(new HooksProxy()));
	}

	public function __get($name): object
	{
		return $this->getService($name);
	}

	public function addService(string $name, mixed $service): void
	{
		$this->services[$name] = $service;
	}

	public function getService(string $name): object
	{
		$service = $this->services[$name];

		/**
		 * Allow services lazy loading
		 */
		if (is_string($service)) {
			$this->services[$name] = new $service($this);
		} else if (is_callable($service)) {
			$this->services[$name] = $service($this);
		}

		return $this->services[$name];
	}

	public function hasService(string $name): bool
	{
		return isset($this->services[$name]);
	}
}