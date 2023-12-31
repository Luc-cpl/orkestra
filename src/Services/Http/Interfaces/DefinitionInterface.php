<?php

namespace Orkestra\Services\Http\Interfaces;

use Orkestra\Services\Http\Factories\ParamDefinitionFactory;
use Orkestra\Services\Http\Entities\ParamDefinition;

interface DefinitionInterface
{
	public function title(): string;
	public function description(): string;
	public function type(): string;
	public function meta(string $key, mixed $default = null): mixed;

	/**
	 * @return ParamDefinition[]
	 */
	public function params(ParamDefinitionFactory $param): array;
}
