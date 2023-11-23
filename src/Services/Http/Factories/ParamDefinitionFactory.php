<?php

namespace Orkestra\Services\Http\Factories;

use Orkestra\Services\Http\Entities\ParamDefinition;
use Orkestra\Services\Http\Enum\ParamType;

/**
 * @method ParamDefinition string(string $title, string $name, string|array|null $validation = '', string|array|null $sanitization = '', mixed $default = null, ?string $description = null)
 * @method ParamDefinition int(string $title, string $name, string|array|null $validation = '', string|array|null $sanitization = '', mixed $default = null, ?string $description = null)
 * @method ParamDefinition number(string $title, string $name, string|array|null $validation = '', string|array|null $sanitization = '', mixed $default = null, ?string $description = null)
 * @method ParamDefinition boolean(string $title, string $name, string|array|null $validation = '', string|array|null $sanitization = '', mixed $default = null, ?string $description = null)
 * @method ParamDefinition array(string $title, string $name, string|array|null $validation = '', string|array|null $sanitization = '', mixed $default = null, ?string $description = null)
 * @method ParamDefinition object(string $title, string $name, string|array|null $validation = '', string|array|null $sanitization = '', mixed $default = null, ?string $description = null)
 */
class ParamDefinitionFactory
{
	/**
	 * @param array{string,string,string[]|string|null,string[]|string|null,mixed,?string} $args
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

		return new ParamDefinition(
			$type,
			$args[0],
			$args[1],
			$args[2] ?? '',   // validation
			$args[3] ?? '',   // sanitization
			$args[4] ?? null, // default
			$args[5] ?? null  // description
		);
	}
}
