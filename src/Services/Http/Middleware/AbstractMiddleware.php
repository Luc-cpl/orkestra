<?php

namespace Orkestra\Services\Http\Middleware;

use Orkestra\App;
use Orkestra\Services\Http\Interfaces\RouteAwareInterface;
use Orkestra\Services\Http\Interfaces\RouteInterface;
use Orkestra\Services\Http\Traits\ErrorResponseTrait;

use Psr\Http\Server\MiddlewareInterface;
use DI\Attribute\Inject;

abstract class AbstractMiddleware implements
    MiddlewareInterface,
    RouteAwareInterface
{
    use ErrorResponseTrait;

    #[Inject]
    protected App $app;

    protected ?RouteInterface $route = null;

    /**
     * @return $this
     */
    public function setRoute(RouteInterface $route): self
    {
        $this->route = $route;
        return $this;
    }
}
