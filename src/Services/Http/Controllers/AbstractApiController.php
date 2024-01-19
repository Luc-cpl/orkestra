<?php

namespace Orkestra\Services\Http\Controllers;

use Orkestra\App;
use Orkestra\Services\Http\Interfaces\RouteAwareInterface;
use Orkestra\Services\Http\Interfaces\RouteInterface;
use DI\Attribute\Inject;
use Orkestra\Services\Http\Traits\ErrorResponseTrait;

/**
 * AbstractApiController
 */
abstract class AbstractApiController implements RouteAwareInterface
{
	use ErrorResponseTrait;

	#[Inject]
	protected App $app;

	protected ?RouteInterface $route = null;

	protected int $status = 200;

	/**
	 * @return $this
	 */
	public function setRoute(RouteInterface $route): self
	{
		$this->route = $route;
		return $this;
	}

	protected function setStatus(int $status): self
	{
		$this->status = $status;
		return $this;
	}
}
