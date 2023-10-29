<?php

namespace Orkestra;

use Orkestra\Providers\DefaultServicesProvider;
use Orkestra\Interfaces\ProviderInterface;
use Orkestra\Interfaces\ConfigurationInterface;

use DI\ContainerBuilder;
use DI\Container;
use DI;
use DI\Definition\Helper\AutowireDefinitionHelper;
use DI\Definition\Helper\CreateDefinitionHelper;
use Exception;

class App
{
	public readonly Container $container;

	protected array $providers = [];
	protected array $singletons = [];

	public function __construct(
		ConfigurationInterface $config,
	) {
		// Define default container
		$containerBuilder = new ContainerBuilder();

		$isProduction = $config->get('env') === 'production';
		$cacheDir     = $config->get('root');

		if ($isProduction && $cacheDir) {
			$containerBuilder->enableCompilation($cacheDir);
			$containerBuilder->enableDefinitionCache('container_' . md5($cacheDir));
		}

		$containerBuilder->useAutowiring( true );
		$this->container = $containerBuilder->build();
		$this->singleton(ConfigurationInterface::class, $config);
		$this->singleton(self::class, $this);
	}

	/**
	 * Get the configuration
	 *
	 * @return ConfigurationInterface
	 */
	public function config(): ConfigurationInterface
	{
		return $this->get(ConfigurationInterface::class);
	}

	/**
	 * Run the app
	 * It start the registered providers
	 *
	 * @return void
	 */
	public function run(): void
	{
		// Ensure we only run once
		if ($this->container->has('booted')) {
			throw new Exception('App already booted');
		}

		$this->config()->validate();

		foreach ($this->providers as $provider) {
			$this->get($provider)->boot($this);
		}

		$this->container->set('booted', true);
	}

	/**
	 * Register a provider
	 * We should register classes that implement ProviderInterface
	 * That way we can use the container to resolve and start services
	 *
	 * @param string $class
	 * @return void
	 */
	public function provider(string $class): void
	{
		$interface = ProviderInterface::class;
		if (!class_exists($class) || !in_array($interface, class_implements($class), true)) {
			throw new Exception("Provider \"$class\" must implement \"$interface\"");
		}
		$this->providers[] = $class;
		$this->singleton($class, $class, false);
		$instance = $this->get($class);
		$instance->register($this);
		return;
	}

	/**
	 * Add a service to the container
	 *
	 * @param string $name        Name of the service
	 * @param mixed  $service     Service to bind to the container
	 * @param bool   $useAutowire Use autowire or create to bind the service
	 * @return CreateDefinitionHelper|AutowireDefinitionHelper|null
	 */
	public function bind(string $name, mixed $service, bool $useAutowire = true): CreateDefinitionHelper|AutowireDefinitionHelper|null
	{
		$isClassString = is_string($service);
		if ($isClassString && !class_exists($service)) {
			throw new Exception("Class \"$service\" does not exist");
		}
		$constructor = $isClassString
			? ($useAutowire ? DI\autowire($service) : DI\create($service))
			: $service;
		$this->container->set($name, $constructor);
		return $isClassString ? $constructor : null;
	}

	/**
	 * Add a service to the container as a singleton
	 *
	 * @param string $name
	 * @param mixed  $service
	 * @param bool   $useAutowire
	 * @return CreateDefinitionHelper|AutowireDefinitionHelper|null
	 */
	public function singleton(string $name, mixed $service, bool $useAutowire = true): ?CreateDefinitionHelper
	{
		$bind = $this->bind($name, $service, $useAutowire);
		$this->singletons[$name] = true;
		return $bind;
	}

	/**
     * Returns an entry of the container by its name.
	 * If the entry is a singleton, it will return the same instance
	 * otherwise it will create a new instance.
     *
     * @template T
     * @param string|class-string<T> $id Entry name or a class name.
	 * @param array $params Parameters to pass to the constructor.
     *
     * @return mixed|T
     * @throws DependencyException Error while resolving the entry.
     * @throws NotFoundException No entry found for the given name.
     */
	public function get(string $name, array $params = []): mixed
	{
		if (isset($this->singletons[$name])) {
			return $this->container->get($name);
		}
		return $this->container->make($name, $params);
	}
}
