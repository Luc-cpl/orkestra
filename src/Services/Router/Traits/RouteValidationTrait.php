<?php

namespace Orkestra\Services\Router\Traits;

use Orkestra\App;
use League\Route\Middleware\MiddlewareAwareInterface;
use Psr\Http\Server\MiddlewareInterface;
use Exception;
use Orkestra\Services\Router\Middlewares\ValidationMiddleware;

trait RouteValidationTrait
{
	protected App $app;

	protected array $validation = [];

	abstract public function middleware(MiddlewareInterface $middleware): MiddlewareAwareInterface;

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
}
