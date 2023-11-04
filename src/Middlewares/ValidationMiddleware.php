<?php

namespace Orkestra\Middlewares;

use Orkestra\App;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;

use League\Route\Http\Exception\BadRequestException;
use Rakit\Validation\Validator;

class ValidationMiddleware implements MiddlewareInterface
{
	public function __construct(
		protected App	    $app,
		protected Validator $validator,
		protected array     $rules,
	) {
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$validator = $this->validator;
		$rules     = $this->rules;
		$data      = (array) $request->getQueryParams() + (array) $request->getParsedBody();

		// Allow the addition of custom validation rules
		$this->app->hookCall('validation.rules', $validator);

		$validation = $validator->make($data, $rules);

		$this->app->hookCall('validation.before', $validation, $data, $rules);

		$validation->validate();

		if ($validation->fails()) {;
			throw new BadRequestException('Invalid JSON data in request body.');
		}

		return $handler->handle($request);
	}
}