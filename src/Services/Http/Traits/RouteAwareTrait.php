<?php

namespace Orkestra\Services\Http\Traits;

use Orkestra\Services\Http\Interfaces\RouteInterface;

trait RouteAwareTrait
{
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
