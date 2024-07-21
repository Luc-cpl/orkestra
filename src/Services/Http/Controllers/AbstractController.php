<?php

namespace Orkestra\Services\Http\Controllers;

use Orkestra\App;
use Orkestra\Services\Http\Interfaces\RouteAwareInterface;
use Orkestra\Services\Http\Traits\RouteAwareTrait;
use DI\Attribute\Inject;

/**
 * AbstractController
 */
abstract class AbstractController implements RouteAwareInterface
{
    use RouteAwareTrait;

    #[Inject]
    protected App $app;
}
