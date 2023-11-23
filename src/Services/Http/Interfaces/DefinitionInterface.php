<?php

namespace Orkestra\Services\Http\Interfaces;

use Orkestra\Services\Http\Factories\ParamDefinitionFactory;
use Orkestra\Services\Http\Factories\ResponseDefinitionFactory;
use Orkestra\Services\Http\Entities\ParamDefinition;
use Orkestra\Services\Http\Entities\ResponseDefinition;

interface DefinitionInterface
{
	public function name(): string;
	public function description(): string;
	public function type(): string;
	public function meta(string $key, mixed $default = null): mixed;

	/**
	 * @return ParamDefinition[]
	 */
	public function params(ParamDefinitionFactory $param): array;

	/**
	 * @return ResponseDefinition[]
	 */
	public function responses(
		ResponseDefinitionFactory $response,
		ParamDefinitionFactory $param
	): array;
}
