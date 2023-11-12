<?php

namespace Orkestra\Traits;

use Orkestra\AppBind;

use Orkestra\Interfaces\ProviderInterface;
use Orkestra\Interfaces\ConfigurationInterface;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use DI\ContainerBuilder;
use Exception;

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
        if (!class_exists($class)) {
            throw new Exception("Provider \"$class\" does not exist");
        }
        if (!in_array($interface, class_implements($class), true)) {
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
     * @return AppBind A bind instance that allows manage the service constructor and properties
     * @throws Exception If the class specified in $service does not exist
     */
    public function bind(string $name, mixed $service, bool $useAutowire = true): AppBind
    {
        return new AppBind($this->container, $name, $service, $useAutowire);
    }

    /**
     * Add a service to the container as a singleton
     *
     * @param string $name
     * @param mixed  $service
     * @param bool   $useAutowire
     * @return AppBind A bind instance that allows manage the service constructor and properties
     * @throws Exception If the class specified in $service does not exist
     */
    public function singleton(string $name, mixed $service, bool $useAutowire = true): ?AppBind
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
     * @param class-string<T> $name   Entry name or a class name.
     * @param array           $params Optional parameters to use to build the entry. Use this to force
     *                                specific parameters to specific values. Parameters not defined in this
     *                                array will be resolved using the container.
     * @return T
     * @throws InvalidArgumentException The name parameter must be of type string.
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
