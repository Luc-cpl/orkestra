<?php

namespace Orkestra;

use Orkestra\Facades\ConfigFacade;
use Orkestra\Facades\HooksFacade;

use Orkestra\Proxies\ConfigProxy;
use Orkestra\Proxies\HooksProxy;

use Orkestra\Interfaces\ConfigInterface;
use Orkestra\Interfaces\HooksInterface;

/**
 * @property-read ConfigInterface $config
 * @property-read HooksInterface $hooks
 */
class App
{
	protected array $services = [];
	protected array $providers = [];

	public function __construct() {
		// Define default services
		$this->addService('config', fn() => new ConfigFacade(new ConfigProxy()));
		$this->addService('hooks', fn() => new HooksFacade(new HooksProxy()));
	}

	public function __get($name): object
	{
		return $this->getService($name);
	}

	/**
	 * Run the app
	 * It start the registered providers
	 *
	 * @return void
	 */
	public function run(): void
	{
		foreach ($this->providers as $provider) {
			if (is_string($provider)) {
				new $provider($this);
			} else if (is_callable($provider)) {
				$provider($this);
			}
		}
	}

	public function addProvider(string|array $provider): self
	{
		if (is_array($provider)) {
			$this->providers = array_merge($this->providers, $provider);
			return $this;
		}
		$this->providers[] = $provider;
		return $this;
	}

	public function addService(string $name, mixed $service): self
	{
		$this->services[$name] = $service;
		return $this;
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