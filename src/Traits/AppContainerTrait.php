<?php

namespace Orkestra\Traits;

use Orkestra\AppBind;

use Orkestra\Interfaces\ProviderInterface;
use DI\Container;
use DI\ContainerBuilder;
use InvalidArgumentException;

/**
 * Implement dependency injection functionality for the application.
 * This will handle the dependency injection for the application and control the service container.
 */
trait AppContainerTrait
{
    private Container $container;

    /**
     * @var class-string[]
     */
    private array $providers = [];

    protected function initContainer(): void
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAutowiring(true);
        $containerBuilder->useAttributes(true);
        $this->container = $containerBuilder->build();
    }

    public function provider(string $class): void
    {
        $interface = ProviderInterface::class;
        if (!class_exists($class)) {
            throw new InvalidArgumentException("Provider \"$class\" does not exist");
        }
        if (!in_array($interface, class_implements($class), true)) {
            throw new InvalidArgumentException("Provider \"$class\" must implement \"$interface\"");
        }
        $this->providers[] = $class;
        $this->bind($class, $class, false);

        /** @var ProviderInterface $instance */
        $instance = $this->get($class);
        $instance->register($this);
        return;
    }

    public function getProviders(): array
    {
        return $this->providers;
    }

    public function bind(string $name, mixed $service, bool $useAutowire = true): AppBind
    {
        return new AppBind($this->container, $name, $service, $useAutowire);
    }

    /**
     * @deprecated 1.1.0 Use bind instead
     */
    public function singleton(string $name, mixed $service, bool $useAutowire = true): ?AppBind
    {
        trigger_error('Method ' . __METHOD__ . ' is deprecated', E_USER_DEPRECATED);
        return $this->bind($name, $service, $useAutowire);
    }

    /**
     * @template T of object
     * @return T
     */
    public function get(string $name): mixed
    {
        /** @var T */
        return $this->container->get($name);
    }

    /**
     * @template T of object
     * @return T
     */
    public function make(string $name, array $params = []): mixed
    {
        /** @var T */
        return $this->container->make($name, $params);
    }

    public function call($callable, array $params = []): mixed
    {
        return $this->container->call($callable, $params);
    }

    public function has(string $name): bool
    {
        return $this->container->has($name);
    }

    /**
     * Run a callback if the service is available
     *
     * @param class-string $name
     * @param callable     $callback
     * @return mixed
     */
    public function runIfAvailable(string $name, callable $callback): mixed
    {
        return $this->has($name) ? $callback($this->get($name)) : null;
    }
}
