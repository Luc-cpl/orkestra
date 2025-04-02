<?php

namespace Orkestra\Services\Http\Traits;

use Orkestra\Services\Http\Interfaces\RouteInterface;
use Orkestra\Services\Http\Interfaces\RouteAwareInterface;

trait RouteAwareTrait
{
    protected ?RouteInterface $route = null;

    public function setRoute(RouteInterface $route): RouteAwareInterface
    {
        $this->route = $route;
        return $this;
    }
}
