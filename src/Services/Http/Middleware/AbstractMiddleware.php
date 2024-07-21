<?php

namespace Orkestra\Services\Http\Middleware;

use Orkestra\App;
use Orkestra\Services\Http\Interfaces\RouteAwareInterface;

use Psr\Http\Server\MiddlewareInterface;
use Orkestra\Services\Http\Traits\RouteAwareTrait;
use DI\Attribute\Inject;

abstract class AbstractMiddleware implements
    MiddlewareInterface,
    RouteAwareInterface
{
    use RouteAwareTrait;

    #[Inject]
    protected App $app;
}
