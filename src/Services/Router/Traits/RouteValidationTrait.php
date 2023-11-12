<?php

namespace Orkestra\Services\Router\Traits;

use Orkestra\App;
use Orkestra\Services\Router\Middlewares\ValidationMiddleware;
use League\Route\Middleware\MiddlewareAwareInterface;
use Psr\Http\Server\MiddlewareInterface;
use Exception;

trait RouteValidationTrait
{
	protected App $app;

	/**
	 * @var array<string, string>
	 */
	protected array $validation = [];

	abstract public function middleware(MiddlewareInterface $middleware): MiddlewareAwareInterface;

	/**
	 * @param array<string, string> $validation Validation rules
	 * @return self
	 */
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

	/**
	 * @return array<string, string>
	 */
	public function getValidation(): array
	{
		return $this->validation;
	}
}
