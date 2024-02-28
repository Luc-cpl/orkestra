<?php

namespace Orkestra\Entities\Attributes;

use Faker\Factory;
use InvalidArgumentException;
use Attribute;
use Closure;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Faker
{
	private ?Closure $callback = null;

	private ?string $locale = null;

	/**
	 * @param mixed[] $args
	 */
    public function __construct(
		public string $key,
		private mixed $value = null,
		?string $method = null,
		array $args = [],
	) {
		if ($method === null && $value === null) {
			throw new InvalidArgumentException('Faker attribute must have at least one argument');
		}

		if ($value !== null) {
			return;
		}

		$this->callback = function (?string $locale = null) use ($method, $args) {
			$faker = Factory::create($locale ?? Factory::DEFAULT_LOCALE);
			return $faker->{$method}(...$args);
		};
	}

	public function setLocale(?string $locale): void
	{
		$this->locale = $locale ?? Factory::DEFAULT_LOCALE;
	}

	public function getValue(): mixed
	{
		if ($this->callback) {
			return call_user_func($this->callback, $this->locale);
		}
		return $this->value;
	}
}