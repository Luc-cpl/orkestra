<?php

namespace Orkestra\Entities;

use Orkestra\App;
use Orkestra\Entities\Attributes\Faker;
use Orkestra\Entities\Attributes\Repository;
use Faker\Factory;
use BadMethodCallException;
use InvalidArgumentException;
use ReflectionAttribute;
use ReflectionClass;
use RuntimeException;

class EntityFactory
{
    private int $times = 1;

    public function __construct(
        private App $app,
        private bool $useFaker = false,
        private string $locale = Factory::DEFAULT_LOCALE,
    ) {
    }

    public function times(int $times): self
    {
        $clone = clone $this;
        $clone->times = $times;
        return $clone;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @param mixed ...$args
     * @return T|T[]
     */
    public function make(string $class, ...$args): object|array
    {
        $entities = [];
        for ($i = 0; $i < $this->times; $i++) {
            if (is_callable($args[0] ?? null)) {
                $args = call_user_func($args[0], $i);
            }

            $entities[] = $this->makeEntity($class, ...$args);
        }

        return $this->times === 1 ? $entities[0] : $entities;
    }

    /**
     * Make and persist entities
     * Very useful for testing
     *
     * @template T of object
     * @param class-string<T> $class
     * @param mixed ...$args
     * @return T|T[]
     */
    public function create(string $class, ...$args): object|array
    {
        // Get the repository from class
        $reflection = new ReflectionClass($class);
        $repository = $this->getRepository($reflection);

        if ($repository === null) {
            throw new RuntimeException(sprintf('No repository found for %s', $class));
        }

        if (!method_exists($repository, 'persist')) {
            throw new BadMethodCallException(sprintf('Method persist not found in %s', $repository::class));
        }

        $entities = $this->make($class, ...$args);
        $entities = is_array($entities) ? $entities : [$entities];

        foreach ($entities as $entity) {
            $repository->persist($entity);
        }

        return $this->times === 1 ? $entities[0] : $entities;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @param mixed ...$args
     * @return T
     */
    private function makeEntity(
        string $class,
        ...$args
    ): object {
        $reflection  = new ReflectionClass($class);
        $parsedArgs  = $this->separateConstructorFromParams($reflection, $args);
        $constructor = $parsedArgs['constructor'];
        $properties  = $parsedArgs['properties'];

        if ($this->useFaker) {
            $fakerArgs = $this->getFakerData($reflection);
            $constructor = array_merge($fakerArgs['constructor'], $constructor);
            $properties  = array_merge($fakerArgs['properties'], $properties);
        }

        $entity = $this->app->make($class, $constructor);

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

        /** @var ReflectionAttribute<Faker>[] */
        $attrs = [];

        foreach ($reflection->getProperties() as $property) {
            $attr = $property->getAttributes(Faker::class)[0] ?? null;
            if ($attr === null) {
                continue;
            }
            $attrs[$property->getName()] = $attr;
        }

        array_push($attrs, ...$reflection->getAttributes(Faker::class));
        $constructor = $reflection->getConstructor()?->getParameters() ?? [];

        foreach ($attrs as $key => $attr) {
            $instance = $attr->newInstance();
            $instance->setLocale($this->locale);

            $key   = is_string($key) ? $key : $instance->key;
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

    /**
     * @param ReflectionClass<object> $reflection
     */
    private function getRepository(ReflectionClass $reflection): ?object
    {
        $attr = $reflection->getAttributes(Repository::class)[0] ?? null;

        if ($attr === null) {
            return null;
        }

        $instance = $attr->newInstance();
        $class = $instance->class;

        if (!$this->app->has($class)) {
            return null;
        }

        return $this->app->get($instance->class);
    }
}
