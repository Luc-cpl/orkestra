<?php

namespace Orkestra\Interfaces;

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
	 * Set a configuration value
	 * 
	 * If the key is 'validation' then the value must be an array with keys as the config key and the value as a callable
	 * and the callable must return true if the value is valid or a string with the error message if the value is invalid.
	 * This array will be merged with the existing validation array.
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