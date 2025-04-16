<?php

namespace Orkestra\Entities;

use InvalidArgumentException;
use JsonSerializable;
use ReflectionClass;
use DateTimeInterface;
use DateTime;

abstract class AbstractEntity implements JsonSerializable
{
    /**
     * Set entity properties defined in constructor or set method
     *
     * @return $this
     */
    public function set(mixed ...$args): self
    {
        if (($args[0] ?? null) && is_array($args[0]) && count($args) === 1) {
            $args = $args[0];
        }

        foreach ($args as $key => $value) {
            if (is_int($key)) {
                throw new InvalidArgumentException(sprintf(
                    'Method %s does not accept numeric args: %s',
                    __METHOD__,
                    $key
                ));
            }

            // Check for setter methods first
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            if (method_exists($this, $method)) {
                $this->{$method}($value);
                unset($args[$key]);
            }

            // Check for setter methods with underscores
            $method2 = "set_{$key}";
            if (method_exists($this, $method2)) {
                $this->{$method2}($value);
                unset($args[$key]);
            }
        }

        if (empty($args)) {
            return $this;
        }

        $reflectionClass = new ReflectionClass($this);

        // Check constructor parameters
        $properties = $reflectionClass->getConstructor()?->getParameters();
        foreach ($properties ?? [] as $property) {
            $name = $property->getName();

            if (!array_key_exists($name, $args)) {
                continue;
            }

            $this->{$name} = $args[$name];
            unset($args[$name]);
        }

        // Check public properties
        foreach ($args as $key => $value) {
            if ($reflectionClass->hasProperty($key)) {
                $property = $reflectionClass->getProperty($key);
                if ($property->isPublic()) {
                    $this->{$key} = $value;
                    unset($args[$key]);
                }
            }
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

            if ($property->isPrivate()) {
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

            if ($data[$name] instanceof DateTimeInterface) {
                $data[$name] = $data[$name]->format(DateTime::ATOM);
            }

            if (is_array($data[$name]) && !empty($data[$name])) {
                $allNumeric = true;
                foreach (array_keys($data[$name]) as $key) {
                    if (!is_int($key)) {
                        $allNumeric = false;
                        break;
                    }
                }
                if ($allNumeric) {
                    $data[$name] = array_values($data[$name]);
                }
            }

            if (is_object($data[$name]) &&
                !($data[$name] instanceof DateTimeInterface) &&
                !method_exists($data[$name], 'toArray') &&
                $data[$name] instanceof \Traversable) {
                $array = iterator_to_array($data[$name]);
                $array = array_map(function ($value) {
                    return is_object($value) && method_exists($value, 'toArray') ? $value->toArray() : $value;
                }, $array);

                $allNumeric = !empty($array);
                foreach (array_keys($array) as $key) {
                    if (!is_int($key)) {
                        $allNumeric = false;
                        break;
                    }
                }
                $data[$name] = $allNumeric ? array_values($array) : $array;
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
