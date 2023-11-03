<?php

namespace Orkestra\Models;

use Orkestra\App;

use League\Route\Route as LeagueRoute;
use League\Route\Strategy\JsonStrategy;
use Rakit\Validation\Validator;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Exception;

class Route extends LeagueRoute
{
	protected array     $validation = [];
	protected array     $metaData   = [];
	protected array     $errors     = [];
	protected Validator $validator;

	public function __construct(
		protected App $app,
		string $method, string $path, $handler,
	) {
		parent::__construct($method, $path, $handler);
	}

	protected function validate(ServerRequestInterface $request): bool
	{
		$data       = (array) $request->getQueryParams() + (array) $request->getParsedBody();
		$validator  = $this->validator;
		$validation = $validator->make($data, $this->validation);
		$validation->validate();

		$this->errors = $validation->errors()->all();
		return !$validation->fails();
	}

	public function setValidation(array $validation): self
	{
		$this->validation = $validation;
		return $this;
	}

	public function setValidator(Validator $validator): self
	{
		$this->validator = $validator;
		return $this;
	}

	public function getValidation(): array
	{
		return $this->validation;
	}

	public function setMetaData(array $metaData): self
	{
		$this->metaData = $metaData;
		return $this;
	}

	public function getMetaData(): array
	{
		return $this->metaData;
	}

	public function getMeta(string $key)
	{
		return $this->metaData[$key] ?? null;
	}

	public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
		// Validate the request
		if (!$this->validate($request)) {
			throw new Exception($this->errors[0]);
		}
        return parent::process($request, $handler);
    }

	public function json(): self
	{
		$this->setStrategy($this->app->get(JsonStrategy::class));
		return $this;
	}
}