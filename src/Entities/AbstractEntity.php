<?php

namespace Orkestra\Entities;

use InvalidArgumentException;
use ReflectionClass;

abstract class AbstractEntity
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
            if (method_exists($this, $method = 'set' . ucfirst($key))) {
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

            if (!isset($args[$name])) {
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
        if (method_exists($this, $method = 'get' . ucfirst($name))) {
            return $this->{$method}();
        }

        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        throw new InvalidArgumentException(sprintf('Undefined property: %s::$%s', static::class, $name));
    }

    public function __isset(string $name) {
        if (method_exists($this, 'get' . ucfirst($name))) {
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
        }

        return $data;
    }
}
