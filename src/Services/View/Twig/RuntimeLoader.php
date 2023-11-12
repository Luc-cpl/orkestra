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

	public function load($class)
	{
		if ($this->container->has($class)) {
			return $this->container->get($class);
		}
	}
}
