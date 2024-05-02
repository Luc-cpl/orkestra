<?php

namespace Orkestra\Services\Http\Interfaces;

interface RouteAwareInterface
{
    /**
     * @return $this
     */
    public function setRoute(RouteInterface $route): self;
}
