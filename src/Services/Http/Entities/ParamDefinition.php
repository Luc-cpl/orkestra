<?php

namespace Orkestra\Services\Http\Entities;

use Orkestra\Services\Http\Enum\ParamType;
use Orkestra\Entities\AbstractEntity;
use BadMethodCallException;

/**
 * @property-read ParamType         $type
 * @property-read string            $title
 * @property-read string            $name
 * @property-read mixed             $default
 * @property-read string            $description
 * @property-read string[]          $enum
 * @property-read bool              $required
 * @property-read ParamDefinition[] $inner
 * @property-read string[]          $validation
 */
class ParamDefinition extends AbstractEntity
{
	/**
	 * @var ParamDefinition[]
	 */
	protected array $inner = [];

	/**
	 * @var string[] $validation
	 */
	protected array $validation = [];

	/**
	 * @param mixed[] $enum
	 */
	public function __construct(
		protected ParamType $type,
		protected string    $title,
		protected string    $name,
		protected mixed     $default     = null,
		protected ?string   $description = null,
		protected array     $enum        = [],
	) {
		//
	}

	public function getRequired(): bool
	{
		return in_array('required', $this->validation);
	}

	/**
	 * @param string[]|string $validation
	 */
	public function setValidation(string|array $validation): self
	{
		$this->validation = is_string($validation) ? explode('|', $validation) : $validation;
		return $this;
	}

	/**
	 * @param ParamDefinition[] $inner
	 */
	public function setInner(array $inner): self
	{
		if ($this->type->name !== 'Array' && $this->type->name !== 'Object') {
			throw new BadMethodCallException('Cannot set inner on non-array or non-object param');
		}

		$this->inner = $inner;
		return $this;
	}
}
