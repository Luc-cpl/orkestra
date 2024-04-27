<?php

namespace Orkestra\Services\Http;

use Orkestra\Services\Http\Factories\ParamDefinitionFactory;
use Orkestra\Services\Http\Interfaces\DefinitionInterface;
use Orkestra\Services\Http\Entities\ParamDefinition;

class RouteDefinition implements DefinitionInterface
{
	/**
	 * @param array<string, mixed> $meta
	 * @param array<string, array{
	 * 	'type': string,
	 * 	'title': ?string,
	 * 	'description': ?string,
	 * 	'validation': ?string,
	 * 	'default': mixed,
	 *	'inner': mixed,
	 *  'enum': ?mixed[],
	 * }> $params
	 */
	public function __construct(
		protected ?string $title       = null,
		protected ?string $description = null,
		protected ?string $type        = null,
		protected ?array  $meta        = [],
		protected array   $params      = [],
	) {
	}

	public function title(): string
	{
		return $this->title ?? '';
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
	 * @param array<string, array{
	 * 	'type': string,
	 * 	'title': ?string,
	 * 	'description': ?string,
	 * 	'validation': ?string,
	 * 	'default': mixed,
	 *	'inner': mixed,
	 *  'enum': ?mixed[],
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
				'title'       => $value['title'] ?? $key,
				'name'        => $key,
				'default'     => $value['default'] ?? null,
				'validation'  => $value['validation'] ?? '',
				'description' => $value['description'] ?? null,
				'enum'        => $value['enum'] ?? [],
			]);

			if (isset($value['inner'])) {
				/**
				 * @var array<string, array{
				 * 	'type': string,
				 * 	'title': ?string,
				 * 	'description': ?string,
				 * 	'validation': ?string,
				 * 	'default': mixed,
				 *	'inner': mixed,
				 *  'enum': ?mixed[],
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
