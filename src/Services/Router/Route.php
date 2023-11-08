<?php

namespace Orkestra\Services\Router;

use Orkestra\App;

use Orkestra\Services\Router\Middlewares\ValidationMiddleware;
use Orkestra\Services\Router\Traits\RouteStrategyTrait;

use League\Route\Route as LeagueRoute;

use Exception;

class Route extends LeagueRoute
{
	use RouteStrategyTrait;

	protected array $validation = [];
	protected array $config     = [];
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

	public function setConfig(array $config): self
	{
		$this->config = $config;
		return $this;
	}

	public function getAllConfig(): array
	{
		return $this->config;
	}

	public function getConfig(string $key, mixed $default = false): mixed
	{
		return $this->config[$key] ?? $default;
	}
}
