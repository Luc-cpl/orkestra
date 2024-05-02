<?php

namespace Orkestra\Interfaces;

use Exception;

interface ConfigurationInterface
{
    /**
     * Validate the configuration
     *
     * @return boolean   true if the validation passes
     * @throws Exception if the validation fails
     */
    public function validate(): bool;

    /**
     * Set a configuration value.
     *
     * Can be used to set the following keys:
     * - validation: array<string, fn ($value): bool|string>
     * - definition: array<string, [description, ?default]>
     * - any other key: the value to set
     *
     * - The validation should return true if the value is valid,
     * or a string with the error message.
     * - If the description does not have a default value, the
     * config turns into a required value.
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function set(string $key, mixed $value): self;

    /**
     * Get a configuration value
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed;

    /**
     * Check if a configuration value exists
     *
     * @param string $key
     * @return boolean
     */
    public function has(string $key): bool;
}
