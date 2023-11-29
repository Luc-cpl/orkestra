<?php

namespace Orkestra\Services\Http\Facades;

use Orkestra\Services\Http\Entities\ParamDefinition;
use Orkestra\Services\Http\Factories\ParamDefinitionFactory;
use Orkestra\Services\Http\Interfaces\DefinitionInterface;

class RouteDefinitionFacade
{
	public function __construct(
		protected ParamDefinitionFactory    $paramDefinitionFactory,
		protected DefinitionInterface       $definition
	) {
	}

	public function name(): string
	{
		return $this->definition->name();
	}

	public function description(): string
	{
		return $this->definition->description();
	}

	public function type(): string
	{
		return $this->definition->type();
	}

	public function meta(string $key, mixed $default = null): mixed
	{
		return $this->definition->meta($key, $default);
	}

	/**
	 * @return ParamDefinition[]
	 */
	public function params(): array
	{
		return $this->definition->params($this->paramDefinitionFactory);
	}
}
