<?php

namespace Orkestra\Services\Http\Traits;

use Orkestra\Services\Http\Facades\RouteDefinitionFacade;
use Orkestra\Services\Http\Factories\ParamDefinitionFactory;
use Orkestra\Services\Http\Factories\ResponseDefinitionFactory;
use Orkestra\Services\Http\Interfaces\DefinitionInterface;
use Orkestra\Services\Http\RouteDefinition;
use DI\Attribute\Inject;
use InvalidArgumentException;
use Orkestra\App;

trait RouteDefinitionTrait
{
	#[Inject]
	protected App $app;

	#[Inject]
	private ParamDefinitionFactory $paramDefinitionFactory;

	#[Inject]
	private ResponseDefinitionFactory $responseDefinitionFactory;

	/**
	 * @var RouteDefinitionFacade|class-string|array<array-key, mixed>
	 */
	private RouteDefinitionFacade|string|array $definition = [];

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
	public function setDefinition(string|array $definition): self
	{
		if (!is_string($definition)) {
			$this->definition = $definition;
			return $this;
		}

		if (!class_exists($definition)) {
			throw new InvalidArgumentException(
				"Route definition class '{$definition}' does not exist."
			);
		}

		if (!is_subclass_of($definition, DefinitionInterface::class)) {
			throw new InvalidArgumentException(
				"Route definition class '{$definition}' must implement " . DefinitionInterface::class
			);
		}

		$this->definition = $definition;

		return $this;
	}

	public function getDefinition(): RouteDefinitionFacade
	{
		if (is_string($this->definition)) {
			$this->definition = $this->app->get(RouteDefinitionFacade::class, [
				'definition' => $this->app->get($this->definition)
			]);
		}

		if (is_array($this->definition)) {
			$this->definition = $this->app->get(RouteDefinitionFacade::class, [
				'definition' => $this->app->get(RouteDefinition::class, $this->definition)
			]);
		}

		return $this->definition;
	}
}
