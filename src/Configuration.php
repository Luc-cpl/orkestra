<?php

namespace Orkestra;

use Orkestra\Interfaces\ConfigurationInterface;

use Exception;

class Configuration implements ConfigurationInterface
{
	/**
	 * @param array<string, mixed> $config
	 */
	public function __construct(
		protected array $config = []
	) {
		// Add the default validation
		$this->set('validation', [
			'env'  => fn ($value) =>
			!in_array($value, ['development', 'production'], true)
				? 'env must be either "development" or "production"'
				: true,
			'root' => fn ($value) =>
			!is_dir($value)
				? "root \"$value\" is not a directory"
				: true,
			'slug' => fn ($value) =>
			!empty($value) && !preg_match('/^[a-z0-9-]+$/', $value)
				? "slug \"$value\" is not valid"
				: true,
		]);
	}

	/**
	 * Validate the configuration
	 *
	 * @return boolean   true if the validation passes
	 * @throws Exception if the validation fails
	 */
	public function validate(): bool
	{
		/**
		 * @var array<string, callable> $validation
		 */
		$validation = $this->get('validation');
		if (!$validation) {
			return true;
		}
		foreach ($validation as $key => $validator) {
			$value = $this->get($key);
			$valid = call_user_func($validator, $value);
			if (!$valid || is_string($valid)) {
				$message = "Invalid configuration for \"$key\": ";
				$message .= is_string($valid) ? $valid : "The value does not pass the validation";
				throw new Exception($message);
			}
		}
		return true;
	}

	public function set(string $key, mixed $value): self
	{
		if ($key === 'validation') {
			$errorMessage = 'Validation must be an array with keys as the config key and the value as a callable';
			if (!is_array($value)) {
				throw new Exception($errorMessage);
			}
			foreach ($value as $k => $validator) {
				if (!is_string($k) || !is_callable($validator)) {
					throw new Exception($errorMessage);
				}
			}
			$current = (array) $this->config[$key];
			$this->config[$key] = array_filter(array_merge($current, $value));
			return $this;
		}
		$this->config[$key] = $value;
		return $this;
	}

	public function get(string $key): mixed
	{
		return match ($key) {
			'url'    => $this->getURL(),
			'assets' => $this->getURL() . '/assets',
			default  => isset($this->config[$key]) ? $this->config[$key] : false,
		};
	}

	public function has(string $key): bool
	{
		return (bool) $this->get($key);
	}

	protected function getURL(): string
	{
		$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
		$host = $this->get('host') ?? $_SERVER['HTTP_HOST'];
		return "$protocol://$host";
	}
}
