<?php

namespace Orkestra\Services\Http\Controllers;

use Orkestra\Services\Http\Interfaces\RouteAwareInterface;
use Orkestra\Services\Http\Traits\ErrorResponseTrait;
use Orkestra\Services\Http\Traits\ResponseTrait;

/**
 * AbstractApiController
 */
abstract class AbstractApiController extends AbstractController implements RouteAwareInterface
{
    use ErrorResponseTrait;
    use ResponseTrait;
}
