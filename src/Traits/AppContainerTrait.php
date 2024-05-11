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
    private bool $booted = false;

    private Container $container;

    /**
     * @var ContainerBuilder<Container>
     */
    private ContainerBuilder $builder;

    /**
     * @var class-string[]
     */
    private array $providers = [];

    /**
     * @var array<string, callable[]>
     */
    private array $decorators = [];

    /**
     * @var array<string, DefinitionHelper>
     */
    private array $rootDecorators = [];

    /**
     * @var array<string, AppBind>
     */
    private array $binds = [];

    protected function bootGate(bool $booted = false): void
    {
        if ($booted && !$this->booted) {
            throw new BadMethodCallException('Application has not been booted');
        }
        if (!$booted && $this->booted) {
            throw new BadMethodCallException('Application has already been booted');
        }
    }

    protected function bootContainer(): void
    {
        $this->bootGate();
        $bindDefinitions = array_map(fn (AppBind $bind) => $bind->service, $this->binds);
        $this->builder->addDefinitions($bindDefinitions);
        $this->builder->addDefinitions($this->rootDecorators);

        $this->container = $this->builder->build();
        $this->booted = true;
    }

    protected function initContainer(): void
    {
        $this->bootGate();
        $this->builder = new ContainerBuilder();
        $this->builder->useAutowiring(true);
        $this->builder->useAttributes(true);
    }

    public function provider(string $class): void
    {
        $this->bootGate();
        $interface = ProviderInterface::class;
        if (!class_exists($class)) {
            throw new InvalidArgumentException("Provider \"$class\" does not exist");
        }
        if (!in_array($interface, class_implements($class), true)) {
            throw new InvalidArgumentException("Provider \"$class\" must implement \"$interface\"");
        }
        /** @var ProviderInterface */
        $instance = new $class();
        $instance->register($this);

        $this->providers[] = $class;
        $this->bind($class, $instance);
        return;
    }

    public function getProviders(): array
    {
        return $this->providers;
    }

    public function bind(string $name, mixed $service, bool $useAutowire = true): AppBind
    {
        $bind = new AppBind(
            container: $this->container ?? null,
            name: $name,
            service: $service,
            autowire: $useAutowire
        );

        if (!$this->booted) {
            $this->binds[$name] = $bind;
        }

        return $bind;
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
        $this->bootGate(true);
        /** @var T */
        return $this->container->get($name);
    }

    /**
     * @template T of object
     * @return T
     */
    public function make(string $name, array $params = []): mixed
    {
        $this->bootGate(true);
        /** @var T */
        return $this->container->make($name, $params);
    }

    public function call($callable, array $params = []): mixed
    {
        $this->bootGate(true);
        return $this->container->call($callable, $params);
    }

    public function has(string $name): bool
    {
        $this->bootGate(true);
        return $this->container->has($name);
    }

    public function decorate(string $name, callable $decorator): void
    {
        $this->bootGate();

        $this->decorators[$name] ??= [];
        $this->decorators[$name][] = $decorator;
        if (count($this->decorators[$name]) === 1) {
            $this->rootDecorators[$name] = $this->addContainerDecorator($name);
        }
    }

    public function runIfAvailable(string $name, callable $callback): mixed
    {
        return $this->has($name) ? $callback($this->get($name)) : null;
    }

    private function addContainerDecorator(string $name): DefinitionHelper
    {
        return decorate(function ($instance) use ($name) {
            $decorators = $this->decorators[$name] ?? [];
            foreach ($decorators as $decorator) {
                $instance = $this->call($decorator, [$instance]);
            }
            return $instance;
        });
    }
}
