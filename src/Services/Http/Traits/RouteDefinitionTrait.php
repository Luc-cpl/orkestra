<?php

namespace Orkestra\Services\Http\Traits;

use Orkestra\Services\Http\Interfaces\DefinitionInterface;
use Orkestra\Services\Http\RouteDefinition;

trait RouteDefinitionTrait
{
	protected DefinitionInterface $definition;

	/**
	 * @param DefinitionInterface|array{
	 * 	'name': ?string,
	 * 	'description': ?string,
	 * 	'type': ?string,
	 * 	'meta': ?array<string, mixed>,
	 * 	'params': array<string, array{
	 * 		'type': string,
	 * 		'title': ?string,
	 * 		'description': ?string,
	 * 		'validation': ?string,
	 * 		'sanitization': ?string,
	 * 		'default': mixed,
	 * 		'inner': mixed
	 * 	}>,
	 * 	'responses': array<int, array{
	 * 		'description': ?string,
	 * 		'schema': ?array<string, array{
	 * 			'type': string,
	 * 			'title': ?string,
	 * 			'description': ?string,
	 * 			'validation': ?string,
	 * 			'sanitization': ?string,
	 * 			'default': mixed,
	 * 			'inner': mixed
	 * 		}>
	 * 	}>
	 * } $definition
	 */
	public function setDefinition(DefinitionInterface|array $definition): self
	{
		$this->definition = is_array($definition)
			? new RouteDefinition(...$definition)
			: $definition;
		return $this;
	}

	public function getDefinition(): DefinitionInterface
	{
		return $this->definition ?? new RouteDefinition();
	}
}
