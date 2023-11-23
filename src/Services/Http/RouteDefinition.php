<?php

namespace Orkestra\Services\Http;

use Orkestra\Services\Http\Factories\ParamDefinitionFactory;
use Orkestra\Services\Http\Factories\ResponseDefinitionFactory;
use Orkestra\Services\Http\Interfaces\DefinitionInterface;
use Orkestra\Services\Http\Entities\ParamDefinition;
use Orkestra\Services\Http\Entities\ResponseDefinition;
use Orkestra\Services\Http\Enum\ResponseStatus;

class RouteDefinition implements DefinitionInterface
{
	/**
	 * @param array<string, mixed> $meta
	 * @param array<string, array{
	 * 	'type': string,
	 * 	'title': ?string,
	 * 	'description': ?string,
	 * 	'validation': ?string,
	 * 	'sanitization': ?string,
	 * 	'default': mixed,
	 *	'inner': mixed
	 * }> $params
	 * @param array<int, array{
	 * 	'description': ?string,
	 * 	'schema': ?array<string, array{
	 * 		'type': string,
	 * 		'title': ?string,
	 * 		'description': ?string,
	 * 		'validation': ?string,
	 * 		'sanitization': ?string,
	 * 		'default': mixed,
	 *		'inner': mixed
	 * 	}>
	 * }> $responses
	 */
	public function __construct(
		protected ?string $name        = null,
		protected ?string $description = null,
		protected ?string $type        = null,
		protected ?array  $meta        = [],
		protected array   $params      = [],
		protected array   $responses   = [],
	) {
	}

	public function name(): string
	{
		return $this->name ?? '';
	}

	public function description(): string
	{
		return $this->description ?? '';
	}

	public function type(): string
	{
		return $this->type ?? '';
	}

	public function meta(string $key, mixed $default = null): mixed
	{
		$meta = $this->meta ?? [];
		return $meta[$key] ?? $default;
	}

	/**
	 * @return ParamDefinition[]
	 */
	public function params(ParamDefinitionFactory $factory): array
	{
		return $this->generateParams($this->params ?? [], $factory);
	}

	/**
	 * @return ResponseDefinition[]
	 */
	public function responses(ResponseDefinitionFactory $response, ParamDefinitionFactory $param): array
	{
		$responses = [];

		foreach ($this->responses ?? [] as $key => $value) {
			$type = ResponseStatus::from($key)->value;

			/** @var ResponseDefinition $definition */
			$definition = $response->$type(
				$value['description'] ?? null,
				isset($value['schema']) ? $this->generateParams($value['schema'], $param) : []
			);

			$responses[] = $definition;
		}

		return $responses;
	}

	/**
	 * @param array<string, array{
	 * 	'type': string,
	 * 	'title': ?string,
	 * 	'description': ?string,
	 * 	'validation': ?string,
	 * 	'sanitization': ?string,
	 * 	'default': mixed,
	 *	'inner': mixed
	 * }> $params
	 * @return ParamDefinition[]
	 */
	protected function generateParams(array $params, ParamDefinitionFactory $factory): array
	{
		$definitions = [];

		foreach ($params as $key => $value) {
			// Set a default type to string
			$value = ['type' => 'string'] + $value;

			/** @var callable $callable */
			$callable = [$factory, $value['type']];

			/** @var ParamDefinition $definition */
			$definition = call_user_func_array($callable, [
				$value['title'] ?? $key,
				$key,
				$value['validation'] ?? '',
				$value['sanitization'] ?? '',
				$value['default'] ?? null,
				$value['description'] ?? null
			]);

			if (isset($value['inner'])) {
				/**
				 * @var array<string, array{
				 * 	'type': string,
				 * 	'title': ?string,
				 * 	'description': ?string,
				 * 	'validation': ?string,
				 * 	'sanitization': ?string,
				 * 	'default': mixed,
				 *	'inner': mixed
				 * }> $inner
				 */
				$inner = $value['inner'];
				$definition->setInner($this->generateParams($inner, $factory));
			}

			$definitions[] = $definition;
		}

		return $definitions;
	}
}
