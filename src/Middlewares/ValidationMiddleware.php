<?php

namespace Orkestra\Middlewares;

use Orkestra\App;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;

use Laminas\Diactoros\Response\JsonResponse;
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

		$this->app->hookCall('validation.before', $validation);
		
		$validation->validate();

		$this->app->hookCall('validation.after', $validation);
		
		if ($validation->fails()) {
			$this->app->hookCall('validation.fail', $validation);

			$contentType = $request->getHeaderLine('Content-Type');

        	if (strpos($contentType, 'application/json') === 0) {
				$response = $this->app->get(JsonResponse::class, [
					'data' => [
						'status'  => 'error',
						'error'   => 'validation_failed',
						'message' => 'Validation failed',
						'description' => 'Theres one or more errors in your request data',
						'errors'  => $validation->errors()->toArray(),
					],
					'status' => 400,
				]);
				return $response;
			}

			throw new BadRequestException('Invalid data: ' . implode(', ', $validation->errors()->firstOfAll()));
		}

		return $handler->handle($request);
	}
}