<?php

namespace Orkestra\Services\Http\Interfaces;

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
	public function setDefinition(string|array $definition): self;

	public function getDefinition(): RouteDefinitionFacade;
}
