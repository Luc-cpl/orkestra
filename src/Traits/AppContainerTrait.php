<?php

namespace Orkestra\Traits;

use Orkestra\Interfaces\ProviderInterface;

use DI\Definition\Helper\CreateDefinitionHelper;
use DI\Definition\Helper\AutowireDefinitionHelper;
use DI\Container;
use DI;
use DI\DependencyException;
use DI\NotFoundException;
use DI\ContainerBuilder;
use Exception;
use Orkestra\Interfaces\ConfigurationInterface;

/**
 * Implement dependency injection functionality for the application.
 * This will handle the dependency injection for the application and control the service container.
 */
trait AppContainerTrait
{
    private Container $container;
    private array $singletons = [];
    private array $providers = [];

    /**
     * Initialize the container
     *
     * @param ConfigurationInterface $config
     * @return void
     */
    protected function initContainer(ConfigurationInterface $config): void
    {
        $containerBuilder = new ContainerBuilder();

        $isProduction = $config->get('env') === 'production';
        $cacheDir     = $config->get('root');

        if ($isProduction && $cacheDir) {
            $containerBuilder->enableCompilation($cacheDir);
            $containerBuilder->enableDefinitionCache('container_' . md5($cacheDir));
        }

        $containerBuilder->useAutowiring(true);
        $containerBuilder->useAttributes(true);
        $this->container = $containerBuilder->build();
    }

    /**
     * Register a provider
     * We should register classes that implement ProviderInterface
     * That way we can use the container to resolve and start services
     *
     * @param string $class
     * @return void
     * @throws Exception If the provider class does not exist or does not implement ProviderInterface
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
     * Get the providers
     * 
     * @return array
     */
    protected function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * Add a service to the container
     *
     * @param string $name        Name of the service
     * @param mixed  $service     Service to bind to the container
     * @param bool   $useAutowire Use autowire or create to bind the service
     * @return CreateDefinitionHelper|AutowireDefinitionHelper|null The definition helper if the service is a class, or null if the service is not a class
     * @throws Exception If the class specified in $service does not exist
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
     * @return CreateDefinitionHelper|null The definition helper if the service is a class, or null if the service is not a class
     */
    public function singleton(string $name, mixed $service, bool $useAutowire = true): ?CreateDefinitionHelper
    {
        $bind = $this->bind($name, $service, $useAutowire);
        $this->singletons[$name] = true;
        return $bind;
    }

    /**
     * Returns an entry of the container by its name.
     * If the entry is a singleton, it will return the same instance,
     * otherwise, it will create a new instance.
     *
     * @template T
     * @param string|class-string<T> $name Entry name or a class name.
     * @param array $params Parameters to pass to the constructor.
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

    /**
     * Check if the container has a service
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return $this->container->has($name);
    }

    /**
     * Run a callback if the service is available
     *
     * @param string   $name
     * @param callable $callback
     * @return mixed
     */
    public function runIfAvailable(string $name, callable $callback): mixed
    {
        return $this->has($name) ? $callback($this->get($name)) : false;
    }
}
