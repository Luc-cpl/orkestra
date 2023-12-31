<?php

namespace Orkestra\Services\Http\Traits;

use Orkestra\Services\Http\Facades\RouteDefinitionFacade;
use Orkestra\Services\Http\Factories\ParamDefinitionFactory;
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

	/**
	 * @var RouteDefinitionFacade|class-string|array<array-key, mixed>
	 */
	private RouteDefinitionFacade|string|array $definition = [];

	/**
	 * @var array<string, mixed>
	 */
	private array $definitionParams = [];

	/**
	 * @param class-string|array{
	 * 	'title': ?string,
	 * 	'description': ?string,
	 * 	'type': ?string,
	 * 	'meta': ?array<string, mixed>,
	 * 	'params': array<string, array{
	 * 		'type': string,
	 * 		'title': ?string,
	 * 		'description': ?string,
	 * 		'validation': ?string,
	 * 		'sanitization': ?string,
	 * 		'enum': ?mixed[],
	 * 		'default': mixed,
	 * 		'inner': mixed
	 * 	}>,
	 * } $definition
	 * @param array<string, mixed> $constructorParams
	 */
	public function setDefinition(string|array $definition, array $constructorParams = []): self
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
		$this->definitionParams = $constructorParams;

		return $this;
	}

	public function getDefinition(): RouteDefinitionFacade
	{
		if (is_string($this->definition)) {
			$this->definition = $this->app->get(RouteDefinitionFacade::class, [
				'definition' => $this->app->get($this->definition, $this->definitionParams)
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
