<?php

namespace Orkestra\Services\Http\Attributes;

use Orkestra\Services\Http\Enum\ParamType;
use Orkestra\Services\Http\Entities\ParamDefinition;
use Orkestra\Services\Http\Factories\ParamDefinitionFactory;
use InvalidArgumentException;
use Attribute;

#[Attribute(
	Attribute::TARGET_CLASS
	| Attribute::TARGET_METHOD
	| Attribute::TARGET_PROPERTY
	| Attribute::IS_REPEATABLE
)]
class Param
{
	/**
	 * @param ParamType|class-string $type
	 * @param string[]|string $validation
	 */
	public function __construct(
        private string           $name,
		private ParamType|string $type        = ParamType::String,
		private ?string          $title       = null,
        private mixed            $default     = null,
        private ?string          $description = null,
		private array|string     $validation  = [],
	) {
		//
	}

	public function getParamDefinition(ParamDefinitionFactory $factory, callable $generator): ParamDefinition
	{
		$inner = null;

		if (is_string($this->type)) {
			try {
				$type = strtolower($this->type);
				$this->type = ParamType::from($type);
			} catch (\ValueError) {
				// Not a ParamType enum value, continue with class check
			}
		}

		if (is_string($this->type)) {
			if (!class_exists($this->type)) {
				throw new InvalidArgumentException("Invalid type: {$this->type}");
			}
			$inner = $generator($factory, $this->type);
			$this->type = ParamType::Object;
		}

		$callable = [$factory, strtolower($this->type->name)];
		if (!is_callable($callable)) {
			throw new InvalidArgumentException("Invalid type: {$this->type->name}");
		}

		/** @var ParamDefinition $definition */
		$definition = call_user_func_array($callable, [
			'title'       => $this->title ?? $this->name,
			'name'        => $this->name,
			'default'     => $this->default,
			'validation'  => $this->validation,
			'description' => $this->description,
		]);

		$inner && $definition->setInner($inner);
		return $definition;
	}
}