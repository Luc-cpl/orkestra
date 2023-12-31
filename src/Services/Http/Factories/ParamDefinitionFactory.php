<?php

namespace Orkestra\Services\Http\Factories;

use Orkestra\Services\Http\Entities\ParamDefinition;
use Orkestra\Services\Http\Enum\ParamType;

/**
 * @method ParamDefinition string(string $title, string $name, mixed $default = null, string|array|null $validation = '', string|array|null $sanitization = '', ?string $description = null)
 * @method ParamDefinition int(string $title, string $name, mixed $default = null, string|array|null $validation = '', string|array|null $sanitization = '', ?string $description = null)
 * @method ParamDefinition number(string $title, string $name, mixed $default = null, string|array|null $validation = '', string|array|null $sanitization = '', ?string $description = null)
 * @method ParamDefinition boolean(string $title, string $name, mixed $default = null, string|array|null $validation = '', string|array|null $sanitization = '', ?string $description = null)
 * @method ParamDefinition array(string $title, string $name, mixed $default = null, string|array|null $validation = '', string|array|null $sanitization = '', ?string $description = null)
 * @method ParamDefinition object(string $title, string $name, mixed $default = null, string|array|null $validation = '', string|array|null $sanitization = '', ?string $description = null)
 */
class ParamDefinitionFactory
{
	/**
	 * @param mixed[] $args
	 */
	public function __call(string $method, array $args): ParamDefinition
	{
		$options = ParamType::cases();
		$name    = ucfirst($method);

		foreach ($options as $option) {
			if ($option->name === $name) {
				$type = $option;
				break;
			}
		}

		if (!isset($type)) {
			throw new \BadMethodCallException("Invalid method: $method");
		}

		// If we pass named arguments, we need to re-order them to match the order of the constructor.
		if (isset($args['title'])) {
			$args = [
				$args['title'],
				$args['name'],
				$args['default'] ?? null,
				$args['validation'] ?? null,
				$args['sanitization'] ?? null,
				$args['description'] ?? null,
				$args['inner'] ?? null,
				$args['enum'] ?? null,
			];
		}

		/** @var array{string,string,mixed,string[]|string|null,string[]|string|null,?string,?ParamDefinition[],?mixed[]} $args*/
		return new ParamDefinition(
			$type,
			$args[0],         // title
			$args[1],         // name
			$args[2] ?? null, // default
			$args[3] ?? '',   // validation
			$args[4] ?? '',   // sanitization
			$args[5] ?? null, // description
			$args[6] ?? [],   // inner
			$args[7] ?? [],   // enum
		);
	}
}
