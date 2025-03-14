<?php

namespace Orkestra\Entities;

use InvalidArgumentException;
use JsonSerializable;
use ReflectionClass;

abstract class AbstractEntity implements JsonSerializable
{
    /**
     * Set entity properties defined in constructor or set method
     *
     * @param array<string, mixed> $args
     * @return $this
     */
    public function set(...$args): self
    {
        foreach ($args as $key => $value) {
            if (is_int($key)) {
                throw new InvalidArgumentException(sprintf(
                    'Method %s does not accept numeric args: %s',
                    __METHOD__,
                    $key
                ));
            }

            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            if (method_exists($this, $method)) {
                $this->{$method}($value);
                unset($args[$key]);
            }
        }

        if (empty($args)) {
            return $this;
        }

        $properties = (new ReflectionClass($this))->getConstructor()?->getParameters();

        foreach ($properties ?? [] as $property) {
            $name = $property->getName();

            if (!array_key_exists($name, $args)) {
                continue;
            }

            $this->{$name} = $args[$name];
            unset($args[$name]);
        }

        if (!empty($args)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid arguments passed to %s: %s',
                __METHOD__,
                implode(', ', array_keys($args))
            ));
        }

        return $this;
    }

    public function __get(string $name): mixed
    {
        $method = 'get' . str_replace('_', '', ucwords($name, '_'));
        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        if (property_exists($this, $name)) {
            return $this->{$name} ?? null;
        }

        throw new InvalidArgumentException(sprintf('Undefined property: %s::$%s', static::class, $name));
    }

    public function __isset(string $name)
    {
        $method = 'get' . str_replace('_', '', ucwords($name, '_'));
        if (method_exists($this, $method)) {
            return true;
        }

        if (property_exists($this, $name) && isset($this->{$name})) {
            return true;
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $properties = (new ReflectionClass($this))->getProperties();

        $data = [];

        foreach ($properties as $property) {
            $name = $property->getName();

            if ($property->isPrivate() || !isset($this->{$name})) {
                continue;
            }

            $data[$name] = $this->__get($name);

            if (is_object($data[$name]) && method_exists($data[$name], 'toArray')) {
                $data[$name] = $data[$name]->toArray();
            }

            if (is_array($data[$name])) {
                $data[$name] = array_map(function ($value) {
                    return is_object($value) && method_exists($value, 'toArray') ? $value->toArray() : $value;
                }, $data[$name]);
            }
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
