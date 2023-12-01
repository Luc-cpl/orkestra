<?php

namespace Orkestra\Services\Http;

use Orkestra\Services\Http\Traits\MiddlewareAwareTrait;
use Orkestra\Services\Http\Interfaces\Partials\MiddlewareAwareInterface;
use League\Route\Dispatcher as LeagueDispatcher;

class Dispatcher extends LeagueDispatcher implements MiddlewareAwareInterface
{
	use MiddlewareAwareTrait;
}
