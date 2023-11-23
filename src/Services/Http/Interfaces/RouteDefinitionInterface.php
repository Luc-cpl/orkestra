<?php

namespace Orkestra\Services\Http\Interfaces;

interface RouteDefinitionInterface
{
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
	public function setDefinition(DefinitionInterface|array $definition): self;

	public function getDefinition(): DefinitionInterface;
}
