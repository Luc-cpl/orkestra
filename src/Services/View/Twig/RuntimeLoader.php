<?php

namespace Orkestra\Services\View\Twig;

use Psr\Container\ContainerInterface;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

class RuntimeLoader implements RuntimeLoaderInterface
{
    public function __construct(
        protected ContainerInterface $container
    ) {
    }

    /**
     * Creates the runtime implementation of a Twig element (filter/function/test).
     *
     * @param string $class A runtime class
     * @return object|null The runtime instance or null if the loader does not know how to create the runtime for this class
     */
    public function load(string $class)
    {
        if ($this->container->has($class)) {
            /** @var object */
            return $this->container->get($class);
        }
        return null;
    }
}
