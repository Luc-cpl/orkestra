<?php

namespace Orkestra\Services\Http\Controllers;

use Orkestra\App;
use Orkestra\Services\Http\Interfaces\RouteAwareInterface;
use Orkestra\Services\Http\Interfaces\RouteInterface;
use DI\Attribute\Inject;

/**
 * AbstractController
 */
abstract class AbstractController implements RouteAwareInterface
{
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
