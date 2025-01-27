<?php

namespace Orkestra\Services\Http;

use Orkestra\App;
use Orkestra\Services\Http\Interfaces\RouteInterface;
use Orkestra\Services\Http\Interfaces\RouteAwareInterface;
use Orkestra\Services\Http\Traits\MiddlewareAwareTrait;
use Orkestra\Services\Http\Traits\RouteStrategyTrait;
use Orkestra\Services\Http\Traits\RouteDefinitionTrait;

use League\Route\Route as LeagueRoute;
use Psr\Container\ContainerInterface;

class Route extends LeagueRoute implements RouteInterface
{
    use MiddlewareAwareTrait;
    use RouteStrategyTrait;
    use RouteDefinitionTrait;

    public function __construct(
        protected App $app,
        string          $method,
        string          $path,
        string|callable $handler,
    ) {
        parent::__construct($method, $path, $handler);
    }

    public function getParsedHandler(): array
    {
        $handler = $this->handler;
        if (is_string($handler) && strpos($handler, '::') !== false) {
            [$class, $method] = explode('::', $handler);
            /** @var class-string $class */
            return ['class' => $class, 'method' => $method];
        }

        if (is_string($handler) && class_exists($handler)) {
            return ['class' => $handler, 'method' => '__invoke'];
        }

        /** @var callable $handler */
        return ['callable' => $handler];
    }

    public function getParentGroup(): ?RouteGroup
    {
        /** @var ?RouteGroup */
        return $this->group;
    }

    protected function resolve(string $class, ?ContainerInterface $container = null): mixed
    {
        $instance = parent::resolve($class, $container);
        if ($instance instanceof RouteAwareInterface) {
            $instance->setRoute($this);
        }
        return $instance;
    }
}
