<?php

namespace Orkestra\Services\Http\Entities;

use Orkestra\Services\Http\Enum\ParamType;
use BadMethodCallException;

class ParamDefinition
{
	public readonly bool $required;

	/**
	 * @param string|string[]   $validation
	 * @param string|string[]   $sanitization
	 * @param ParamDefinition[] $inner
	 */
	public function __construct(
		public readonly ParamType    $type,
		public readonly string       $title,
		public readonly string       $name,
		public readonly string|array $validation   = '',
		public readonly string|array $sanitization = '',
		public readonly mixed        $default      = null,
		public readonly ?string      $description  = null,
		public readonly array        $inner = [],
	) {
		$validation = is_string($validation) ? explode('|', $validation) : $validation;
		$this->required = in_array('required', $validation);
	}

	/**
	 * @param ParamDefinition[] $inner
	 */
	public function setInner(array $inner): self
	{
		if ($this->type->name !== 'Array' && $this->type->name !== 'Object') {
			throw new BadMethodCallException('Cannot set inner on non-array or non-object param');
		}

		$params = (array) $this;
		$params['inner'] = $inner;
		unset($params['required']);

		return new self(...$params);
	}
}
