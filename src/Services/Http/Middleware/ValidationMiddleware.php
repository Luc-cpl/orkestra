<?php

namespace Orkestra\Services\Http\Middleware;

use Orkestra\Services\Http\Entities\ParamDefinition;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Rakit\Validation\Validator;

class ValidationMiddleware extends BaseMiddleware
{
	/**
	 * @param Validator $validator
	 * @param array<string, string[]|string> $rules
	 * @param ParamDefinition[] $params
	 */
	public function __construct(
		protected Validator $validator,
		protected array     $rules = [],
		array $params = []
	) {
		$params = $this->flattenParams($params);
		foreach ($params as $key => $param) {
			$this->rules[$key] = $this->setValidation($param);
		}
	}

	/**
	 * @param ParamDefinition $param
	 * @return string[]|string
	 */
	protected function setValidation(ParamDefinition $param): array|string
	{
		$type = $param->type->value;

		$typeValidation = match ($type) {
			// 'string'  => 'string', // Todo: add this type
			'int'     => 'integer',
			'number'  => 'numeric',
			'boolean' => 'boolean',
			'array'   => 'array',
			'object'  => 'array',
			default   => null,
		};

		$validation = $param->validation;

		if ($typeValidation) {
			$validation = is_array($validation) ? $validation : explode('|', $validation);
			array_unshift($validation, $typeValidation);
		}

		return $validation;
	}

	/**
	 * @param ParamDefinition[] $params
	 * @return array<string, ParamDefinition>
	 */
	protected function flattenParams(array $params, string $prefix = ''): array
	{
		$flattened = [];

		foreach ($params as $param) {
			$flattened[$prefix . $param->name] = $param;

			if ($param->inner && !empty($param->inner)) {
				$inner = $param->inner;
				$inner = $this->flattenParams($inner, $prefix . $param->name . '.');
				$flattened = array_merge($flattened, $inner);
			}
		}

		return $flattened;
	}

	/**
	 * @param array<string, mixed> $params
	 * @param array<string, string[]|string> $rules
	 * @return array<string, mixed>
	 */
	protected function removeUndefinedParams(array $params, array $rules, string $prefix = ''): array
	{
		$filtered = [];

		foreach ($params as $key => $value) {
			$param = $rules[$prefix . $key] ?? null;

			if ($param) {
				$filtered[$key] = $value;
			}

			if (is_array($value)) {
				$inner = $this->removeUndefinedParams($value, $rules, $prefix . $key . '.');
				$filtered[$key] = $inner;
			}
		}

		return $filtered;
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$validator = $this->validator;
		$rules     = $this->rules;

		// remove undefined rules params from query, taking care of nested params as value.key
		$query = (array) $request->getQueryParams();
		$body  = (array) $request->getParsedBody();
		$query = $this->removeUndefinedParams($query, $rules);
		$body  = $this->removeUndefinedParams($body, $rules);
		$query = array_diff_key($query, $body);
		$data  = $query + $body;

		$request = $request->withQueryParams($query)->withParsedBody($body);

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
