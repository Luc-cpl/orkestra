<?php

namespace Orkestra\Entities;

use Orkestra\App;
use Orkestra\Entities\Attributes\Faker;
use Faker\Factory;
use InvalidArgumentException;
use ReflectionClass;

class EntityFactory
{
    public function __construct(
        private App $app,
        private bool $useFaker = false,
        private string $locale = Factory::DEFAULT_LOCALE,
    ) {
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @param mixed ...$args
     * @return T
     */
    public function make(
        string $class,
        ...$args
    ): object {

        $reflection = new ReflectionClass($class);

        $parsedArgs  = $this->separateConstructorFromParams($reflection, $args);
        $constructor = $parsedArgs['constructor'];
        $properties  = $parsedArgs['properties'];

        if ($this->useFaker) {
            $fakerArgs = $this->getFakerData($reflection);
            $constructor = array_merge($fakerArgs['constructor'], $constructor);
            $properties  = array_merge($fakerArgs['properties'], $properties);
        }

        $entity = $this->app->get($class, $constructor);

        foreach ($properties as $key => $value) {
            if (method_exists($entity, $method = 'set' . ucfirst($key))) {
                $entity->{$method}($value);
                continue;
            }
            if (property_exists($entity, $key)) {
                $entity->{$key} = $value;
                continue;
            }
            throw new InvalidArgumentException(sprintf('Invalid property passed to make a %s: %s', $class, $key));
        }

        return $entity;
    }

    /**
     * @param ReflectionClass<object> $reflection
     * @param mixed[] $args
     * @return array{constructor: array<string, mixed>, properties: array<string, mixed>}
     */
    private function separateConstructorFromParams(ReflectionClass $reflection, array $args): array
    {
        $attributes = [
            'constructor' => [],
            'properties'  => [],
        ];

        $constructor = array_map(function ($param) {
            return $param->getName();
        }, $reflection->getConstructor()?->getParameters() ?? []);

        foreach ($args as $key => $value) {
            if (in_array($key, $constructor, true)) {
                $attributes['constructor'][$key] = $value;
                continue;
            }

            $attributes['properties'][$key] = $value;
        }

        return $attributes;
    }

    /**
     * @param ReflectionClass<object> $reflection
     * @return array{constructor: array<string, mixed>, properties: array<string, mixed>}
     */
    private function getFakerData(ReflectionClass $reflection): array
    {
        $attributes = [
            'constructor' => [],
            'properties'  => [],
        ];

        $attrs       = $reflection->getAttributes();
        $constructor = $reflection->getConstructor()?->getParameters() ?? [];

        foreach ($attrs as $attr) {
            if ($attr->getName() !== Faker::class) {
                continue;
            }

            /** @var Faker $instance */
            $instance = $attr->newInstance();
            $instance->setLocale($this->locale);

            $key   = $instance->key;
            $value = $instance->getValue();

            foreach ($constructor as $param) {
                if ($param->getName() !== $key) {
                    continue;
                }

                $attributes['constructor'][$key] = $value;
                continue 2;
            }

            $attributes['properties'][$key] = $value;
        }

        return $attributes;
    }
}
