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
	 * It will start all services not started yet
	 *
	 * @return void
	 */
	public function run(): void
	{
		foreach ($this->services as $service) {
			if (!$service[1]) {
				continue;
			}
			$this->getService($service);
		}
	}

	public function addService(string $name, mixed $service, bool $startOnRun = false): self
	{
		$this->services[$name] = [$service, $startOnRun];
		return $this;
	}

	public function getService(string $name): object
	{
		$service = $this->services[$name][0];

		/**
		 * Allow services lazy loading
		 */
		if (is_string($service)) {
			$this->services[$name][0] = new $service($this);
		} else if (is_callable($service)) {
			$this->services[$name][0] = $service($this);
		}

		return $this->services[$name][0];
	}

	public function hasService(string $name): bool
	{
		return isset($this->services[$name]);
	}
}