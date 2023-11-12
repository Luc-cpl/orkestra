<?php

namespace Orkestra\Services\Router\Middlewares;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

use Rakit\Validation\Validator;

class ValidationMiddleware extends BaseMiddleware
{
	/**
	 * @param Validator             $validator
	 * @param array<string, string> $rules
	 */
	public function __construct(
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
		$this->app->hookCall('middleware.validation.rules', $validator);

		$validation = $validator->make($data, $rules);

		$this->app->hookCall('middleware.validation.before', $validation);

		$validation->validate();

		$this->app->hookCall('middleware.validation.after', $validation);

		if ($validation->fails()) {
			$this->app->hookCall('middleware.validation.fail', $validation);

			return $this->errorResponse(
				$request,
				'validation_failed',
				'Validation failed',
				'Theres one or more errors in your request data',
				$validation->errors()->toArray(),
			);
		}

		$this->app->hookCall('middleware.validation.success', $validation);

		return $handler->handle($request);
	}
}
