<?php

namespace Orkestra\Router;

use Orkestra\App;

use Orkestra\Router\Middlewares\ValidationMiddleware;
use Orkestra\Router\Traits\RouteStrategyTrait;

use League\Route\Route as LeagueRoute;

use Exception;

class Route extends LeagueRoute
{
	use RouteStrategyTrait;

	protected array $validation = [];
	protected array $meta       = [];
	protected array $errors     = [];

	public function __construct(
		protected App $app,
		string $method,
		string $path,
		$handler,
	) {
		parent::__construct($method, $path, $handler);
	}

	public function setValidation(array $validation): self
	{
		if (!empty($this->validation)) {
			throw new Exception('Validation rules already set');
		}
		$this->validation = $validation;
		$this->middleware($this->app->get(ValidationMiddleware::class, [
			'rules' => $validation,
		]));
		return $this;
	}

	public function getValidation(): array
	{
		return $this->validation;
	}

	public function setMeta(array $meta): self
	{
		$this->meta = $meta;
		return $this;
	}

	public function getAllMeta(): array
	{
		return $this->meta;
	}

	public function getMeta(string $key, mixed $default = false): mixed
	{
		return $this->meta[$key] ?? $default;
	}
}
