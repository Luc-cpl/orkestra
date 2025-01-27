<?php

namespace Orkestra;

use Orkestra\Interfaces\ConfigurationInterface;
use InvalidArgumentException;

class Configuration implements ConfigurationInterface
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        protected array $config = []
    ) {
        //
    }

    public function validate(): bool
    {
        /**
         * @var array<string, array{string, mixed}> $definitions
         */
        $definitions = $this->get('definition');

        /**
         * @var array<string, callable> $validation
         */
        $validation = $this->get('validation');

        foreach ($this->config as $key => $value) {
            if ($key === 'validation' || $key === 'definition') {
                continue;
            }
            if (!isset($definitions[$key])) {
                throw new InvalidArgumentException("Configuration key \"$key\" does not have a definition");
            }
            unset($definitions[$key]);
        }

        foreach ($definitions as $key => $definition) {
            if (!isset($definition[1]) && !isset($this->config[$key])) {
                throw new InvalidArgumentException("Configuration key \"$key\" is required");
            }
        }

        foreach ($validation as $key => $validator) {
            $value = $this->get($key);
            $valid = call_user_func($validator, $value);
            if (!$valid || is_string($valid)) {
                $message = "Invalid configuration for \"$key\": ";
                $message .= is_string($valid) ? $valid : "The value does not pass the validation";
                throw new InvalidArgumentException($message);
            }
        }
        return true;
    }

    public function set(string $key, mixed $value): self
    {
        if ($key === 'validation') {
            $errorMessage = 'Validation must be an array with keys as the config key and the value as a callable';
            if (!is_array($value)) {
                throw new InvalidArgumentException($errorMessage);
            }
            foreach ($value as $k => $validator) {
                if (!is_string($k) || !is_callable($validator)) {
                    throw new InvalidArgumentException($errorMessage);
                }
            }
            $current = (array) $this->get($key);
            $value   = array_filter(array_merge($current, $value));
        }

        if ($key === 'definition') {
            if (!is_array($value)) {
                throw new InvalidArgumentException('Definition must be an array');
            }
            foreach ($value as $k => $v) {
                if (!is_string($k) || !is_array($v) || count($v) < 1 || count($v) > 2) {
                    throw new InvalidArgumentException('Definition must be an array with keys as the config key and the value as an array <description, ?default>');
                }
            }
            $current = (array) $this->get($key);
            $value   = array_filter(array_merge($current, $value));
        }

        $this->config[$key] = $value;
        return $this;
    }

    public function get(string $key): mixed
    {
        if ($key === 'validation' || $key === 'definition') {
            return $this->config[$key] ?? [];
        }

        if (!isset($this->config[$key])) {
            /** @var array<string, array{string, mixed}> */
            $definitionStack = $this->get('definition');
            $definition = $definitionStack[$key] ?? false;
            if (!$definition) {
                throw new InvalidArgumentException("Configuration key \"$key\" does not exist");
            }
            if (!isset($definition[1])) {
                throw new InvalidArgumentException("Configuration key \"$key\" is required and is not set");
            }
            return !is_string($definition[1]) && is_callable($definition[1]) ? $definition[1]() : $definition[1];
        }

        return is_callable($this->config[$key]) ? $this->config[$key]() : $this->config[$key];
    }

    public function has(string $key): bool
    {
        try {
            $this->get($key);
            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }
}
