<?php

namespace Orkestra\Services\Http\Entities;

use Orkestra\Services\Http\Enum\ParamType;
use Orkestra\Entities\AbstractEntity;
use BadMethodCallException;
use InvalidArgumentException;

/**
 * @property-read ParamType              $type
 * @property-read string                 $title
 * @property-read string                 $name
 * @property-read mixed                  $default
 * @property-read string                 $description
 * @property-read int[]|string[]|float[] $enum
 * @property-read bool                   $required
 * @property-read ParamDefinition[]      $inner
 * @property-read string[]               $validation
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
    private array $validation = [];

    /**
     * @var int[]|string[]|float[] $enum
     */
    protected array $enum = [];

    public function __construct(
        protected ParamType $type,
        protected string    $title,
        protected string    $name,
        protected mixed     $default     = null,
        protected ?string   $description = null,
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

    public function addValidation(string $validation): self
    {
        $this->validation[] = $validation;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getValidation(): array
    {
        if (empty($this->enum)) {
            return $this->validation;
        }

        $enum = ['in:' . implode(',', $this->enum)];
        return array_merge($this->validation, $enum);
    }

    /**
     * @param int[]|string[]|float[]|class-string|null $enum
     */
    public function setEnum(array|string|null $enum): self
    {
        if ($enum === null) {
            return $this;
        }
        
        if (is_string($enum)) {
            if (!enum_exists($enum)) {
                throw new InvalidArgumentException("Invalid enum class: {$enum}");
            }
            $enum = array_column($enum::cases(), 'value');
        }

        $this->enum = $enum;
        return $this;
    }

    /**
     * @param ParamDefinition[]|null $inner
     */
    public function setInner(array|null $inner): self
    {
        if ($inner === null) {
            return $this;
        }
        
        if ($this->type->name !== 'Array' && $this->type->name !== 'Object') {
            throw new BadMethodCallException('Cannot set inner on non-array or non-object param');
        }

        $this->inner = $inner;
        return $this;
    }
}
