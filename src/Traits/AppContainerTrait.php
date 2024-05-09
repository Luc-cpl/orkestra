<?php

namespace Orkestra\Traits;

use Orkestra\AppBind;

use Orkestra\Interfaces\ProviderInterface;
use DI\Container;
use DI\ContainerBuilder;
use DI\Definition\Helper\DefinitionHelper;
use InvalidArgumentException;
use BadMethodCallException;

use function DI\decorate;

/**
 * Implement dependency injection functionality for the application.
 * This will handle the dependency injection for the application and control the service container.
 */
trait AppContainerTrait
{
    private Container $container;

    /**
     * @var array<string, bool>
     */
    private array $singletons = [];

    /**
     * @var class-string[]
     */
    private array $providers = [];

    /**
     * @var array<string, callable[]>
     */
    private array $decorators = [];

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
        $this->singleton($class, $class, false);

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
        $bind = new AppBind($this->container, $name, $service, $useAutowire);
        if ($this->decorators[$name] ?? false) {
            $this->container->set($name, $this->addContainerDecorator($name));
        }
        return $bind;
    }

    public function singleton(string $name, mixed $service, bool $useAutowire = true): ?AppBind
    {
        $bind = $this->bind($name, $service, $useAutowire);
        $this->singletons[$name] = true;
        return $bind;
    }

    /**
     * @template T of object
     * @return T
     */
    public function get(string $name, array $params = []): mixed
    {
        if (isset($this->singletons[$name])) {
            /** @var T */
            return $this->container->get($name);
        }
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

    public function decorate(string $name, callable $decorator): void
    {
        if ($this->has('booted')) {
            throw new BadMethodCallException('Cannot decorate services after the application has been booted');
        }

        if (!class_exists($name)) {
            throw new InvalidArgumentException('Cannot decorate non-existent class: ' . $name);
        }

        $this->decorators[$name] ??= [];
        $this->decorators[$name][] = $decorator;
        $this->container->set($name, $this->addContainerDecorator($name));
    }

    public function runIfAvailable(string $name, callable $callback): mixed
    {
        return $this->has($name) ? $callback($this->get($name)) : null;
    }

    private function addContainerDecorator(string $name): DefinitionHelper
    {
        $decorators = $this->decorators[$name] ?? [];
        return decorate(function ($instance) use ($decorators) {
            foreach ($decorators as $decorator) {
                $instance = $this->call($decorator, [$instance]);
            }
            return $instance;
        });
    }
}
