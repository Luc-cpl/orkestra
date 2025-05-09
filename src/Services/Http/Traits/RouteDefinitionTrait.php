<?php

namespace Orkestra\Services\Http\Traits;

use Orkestra\App;
use DI\Attribute\Inject;
use Orkestra\Services\Http\Factories\ParamDefinitionFactory;
use Orkestra\Services\Http\Facades\RouteDefinitionFacade;
use Orkestra\Services\Http\Interfaces\Partials\RouteDefinitionInterface;
use Orkestra\Services\Http\Interfaces\DefinitionInterface;
use Orkestra\Services\Http\Interfaces\RouteAwareInterface;
use Orkestra\Services\Http\Interfaces\RouteInterface;
use Orkestra\Services\Http\RouteDefinition;
use InvalidArgumentException;

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
     * 		'enum': ?mixed[],
     * 		'default': mixed,
     * 		'inner': mixed
     * 	}>,
     * } $definition
     * @param array<string, mixed> $constructorParams
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setDefinition(string|array $definition, array $constructorParams = []): RouteDefinitionInterface
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
        if ($this->definition instanceof RouteDefinitionFacade) {
            return $this->definition;
        }

        $instance = null;

        if (is_string($this->definition)) {
            $instance = $this->app->make($this->definition, $this->definitionParams);
        }

        if (is_array($this->definition)) {
            $group = method_exists($this, 'getParentGroup') ? $this->getParentGroup() : null;
            $parentDefinition = $group ? $group->getDefinition() : null;
            $definition = array_merge($this->definition, ['parentDefinition' => $parentDefinition]);
            $instance = $this->app->make(RouteDefinition::class, $definition);
        }

        if ($this instanceof RouteInterface && $instance instanceof RouteAwareInterface) {
            $instance->setRoute($this);
        }

        $this->definition = $this->app->make(RouteDefinitionFacade::class, [
            'definition' => $instance
        ]);

        return $this->definition;
    }
}
