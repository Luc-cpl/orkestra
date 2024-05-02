<?php

namespace Orkestra\Services\Http\Factories;

use BadMethodCallException;
use Orkestra\Services\Http\Entities\ParamDefinition;
use Orkestra\Services\Http\Enum\ParamType;
use Orkestra\Entities\EntityFactory;

/**
 * @method ParamDefinition string(string $title, string $name, mixed $default = null, string|array|null $validation = '', ?string $description = null)
 * @method ParamDefinition int(string $title, string $name, mixed $default = null, string|array|null $validation = '', ?string $description = null)
 * @method ParamDefinition number(string $title, string $name, mixed $default = null, string|array|null $validation = '', ?string $description = null)
 * @method ParamDefinition boolean(string $title, string $name, mixed $default = null, string|array|null $validation = '', ?string $description = null)
 * @method ParamDefinition array(string $title, string $name, mixed $default = null, string|array|null $validation = '', ?string $description = null)
 * @method ParamDefinition object(string $title, string $name, mixed $default = null, string|array|null $validation = '', ?string $description = null)
 */
class ParamDefinitionFactory
{
    public function __construct(
        private EntityFactory $factory,
    ) {
        //
    }

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
            throw new BadMethodCallException("Invalid method: $method");
        }

        // If we pass positional arguments, we need change them to named arguments.
        if (isset($args[0])) {
            $args = [
                'title'        => $args[0],
                'name'         => $args[1],
                'default'      => $args[2] ?? null,
                'validation'   => $args[3] ?? null,
                'description'  => $args[4] ?? null,
                'enum'         => $args[6] ?? null,
            ];
        }

        $args['type'] = $type;

        return $this->factory->make(ParamDefinition::class, ...$args);
    }
}
