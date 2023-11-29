<?php

namespace Orkestra\Services\Http\Interfaces\Partials;

use Orkestra\Services\Http\Facades\RouteDefinitionFacade;

interface RouteDefinitionInterface
{
	/**
	 * @param class-string|array{
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
	 * } $definition
	 * @param array<string, mixed> $constructorParams
	 * @return $this
	 */
	public function setDefinition(string|array $definition, array $constructorParams = []): self;

	public function getDefinition(): RouteDefinitionFacade;
}
