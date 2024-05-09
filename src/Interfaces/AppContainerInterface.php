<?php

namespace Orkestra\Interfaces;

use Psr\Container\ContainerInterface;

use Orkestra\AppBind;
use InvalidArgumentException;
use BadMethodCallException;

interface AppContainerInterface extends ContainerInterface
{
    /**
     * Register a provider
     * We should register classes that implement ProviderInterface
     * That way we can use the container to resolve and start services
     *
     * @param string $class
     * @return void
     * @throws InvalidArgumentException If the provider class does not exist or does not implement ProviderInterface
     */
    public function provider(string $class): void;

    /**
     * Get the providers
     *
     * @return class-string[]
     */
    public function getProviders(): array;

    /**
     * Add a service to the container
     *
     * @param string $name        Name of the service
     * @param mixed  $service     Service to bind to the container
     * @param bool   $useAutowire Use autowire or create to bind the service
     * @return AppBind A bind instance that allows manage the service constructor and properties
     * @throws InvalidArgumentException If the class specified in $service does not exist
     */
    public function bind(string $name, mixed $service, bool $useAutowire = true): AppBind;

    /**
     * Add a service to the container as a singleton
     *
     * @param string $name
     * @param mixed  $service
     * @param bool   $useAutowire
     * @return AppBind A bind instance that allows manage the service constructor and properties
     * @throws InvalidArgumentException If the class specified in $service does not exist
     */
    public function singleton(string $name, mixed $service, bool $useAutowire = true): ?AppBind;

    /**
     * Decorate a service in the container
     *
     * The $decorator function should receive the original service as the
     * first argument and return the new service.
     * You can resolve other services in the container using other arguments.
     *
     * Example:
     * $container->decorate(Service::class, function (Service $service, OtherService $otherService) {
     *    return new ServiceDecorator($service, $otherService);
     * });
     *
     * @param class-string $name      Name of the service
     * @param callable     $decorator Decorator function
     * @throws BadMethodCallException   If the application is already booted this modification should not be allowed
     * @throws InvalidArgumentException If the class does not exist
     */
    public function decorate(string $name, callable $decorator): void;

    /**
     * Returns an entry of the container by its name.
     * If the entry is a singleton, it will return the same instance,
     * otherwise, it will create a new instance.
     *
     * @template T of object
     * @param class-string<T> $name   Entry name or a class name.
     * @param mixed[]         $params Optional parameters to use to build the entry.
     * @return T
     */
    public function get(string $name, array $params = []): mixed;

    /**
     * Call the given function using the given parameters.
     *
     * Missing parameters will be resolved from the container.
     *
     * @param callable $callable Function to call.
     * @param mixed[]  $params   Parameters to use. Can be indexed by the parameter names
     *                           or not indexed (same order as the parameters).
     * @return mixed Result of the function.
     */
    public function call($callable, array $params = []): mixed;

    /**
     * Check if the container has a service
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Run a callback if the service is available
     *
     * @param class-string $name
     * @param callable     $callback
     * @return mixed
     */
    public function runIfAvailable(string $name, callable $callback): mixed;
}
