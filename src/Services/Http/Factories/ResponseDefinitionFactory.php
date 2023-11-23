<?php

namespace Orkestra\Services\Http\Factories;

use Orkestra\Services\Http\Entities\ParamDefinition;
use Orkestra\Services\Http\Entities\ResponseDefinition;
use Orkestra\Services\Http\Enum\ResponseStatus;
use BadMethodCallException;

/**
 * @method ResponseDefinition ok(string $description, ParamDefinition[] $schema = [])
 * @method ResponseDefinition created(string $description, ParamDefinition[] $schema = [])
 * @method ResponseDefinition accepted(string $description, ParamDefinition[] $schema = [])
 * @method ResponseDefinition noContent(string $description, ParamDefinition[] $schema = [])
 * @method ResponseDefinition badRequest(string $description, ParamDefinition[] $schema = [])
 * @method ResponseDefinition unauthorized(string $description, ParamDefinition[] $schema = [])
 * @method ResponseDefinition forbidden(string $description, ParamDefinition[] $schema = [])
 * @method ResponseDefinition notFound(string $description, ParamDefinition[] $schema = [])
 * @method ResponseDefinition methodNotAllowed(string $description, ParamDefinition[] $schema = [])
 * @method ResponseDefinition notAcceptable(string $description, ParamDefinition[] $schema = [])
 * @method ResponseDefinition conflict(string $description, ParamDefinition[] $schema = [])
 * @method ResponseDefinition gone(string $description, ParamDefinition[] $schema = [])
 * @method ResponseDefinition unprocessableEntity(string $description, ParamDefinition[] $schema = [])
 * @method ResponseDefinition tooManyRequests(string $description, ParamDefinition[] $schema = [])
 * @method ResponseDefinition internalServerError(string $description, ParamDefinition[] $schema = [])
 * @method ResponseDefinition notImplemented(string $description, ParamDefinition[] $schema = [])
 * @method ResponseDefinition badGateway(string $description, ParamDefinition[] $schema = [])
 * @method ResponseDefinition serviceUnavailable(string $description, ParamDefinition[] $schema = [])
 * @method ResponseDefinition gatewayTimeout(string $description, ParamDefinition[] $schema = [])
 */
class ResponseDefinitionFactory
{
	/**
	 * @param array{string,ParamDefinition[]} $args
	 */
	public function __call(string $method, array $args): ResponseDefinition
	{
		$options = ResponseStatus::cases();
		$name    = ucfirst($method);

		foreach ($options as $option) {
			if ($option->name === $name) {
				$type = $option;
				break;
			}
		}

		if (!isset($type)) {
			throw new BadMethodCallException("Invalid method: $method");
		}

		/** @var ResponseStatus $type */
		return new ResponseDefinition(
			$type,
			$args[0],
			$args[1]
		);
	}
}
