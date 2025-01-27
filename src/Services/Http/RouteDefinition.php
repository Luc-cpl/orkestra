<?php

namespace Orkestra\Services\Http;

use Orkestra\Services\Http\Attributes\Param;
use Orkestra\Services\Http\Attributes\Entity;
use Orkestra\Services\Http\Factories\ParamDefinitionFactory;
use Orkestra\Services\Http\Interfaces\DefinitionInterface;
use Orkestra\Services\Http\Entities\ParamDefinition;
use Orkestra\Services\Http\Facades\RouteDefinitionFacade as DefinitionFacade;
use Orkestra\Services\Http\Interfaces\RouteAwareInterface;
use Orkestra\Services\Http\Traits\RouteAwareTrait;

class RouteDefinition implements DefinitionInterface, RouteAwareInterface
{
    use RouteAwareTrait;

    /**
     * @param array<string, mixed> $meta
     * @param array<string, array{
     * 	'type': string,
     * 	'title': ?string,
     * 	'description': ?string,
     * 	'validation': ?string,
     * 	'default': mixed,
     *	'inner': mixed,
     *  'enum': ?mixed[],
     * }> $params
     */
    public function __construct(
        protected ?string           $title            = null,
        protected ?string           $description      = null,
        protected ?string           $type             = null,
        protected ?array            $meta             = [],
        protected array             $params           = [],
        protected ?DefinitionFacade $parentDefinition = null,
    ) {
    }

    public function title(): string
    {
        $title = $this->title ?? null;
        return $title ?? ($this->parentDefinition ? $this->parentDefinition->title() : '');
    }

    public function description(): string
    {
        $description = $this->description ?? null;
        return $description ?? ($this->parentDefinition ? $this->parentDefinition->description() : '');
    }

    public function type(): string
    {
        $type = $this->type ?? null;
        return $type ?? ($this->parentDefinition ? $this->parentDefinition->type() : '');
    }

    public function meta(string $key, mixed $default = null): mixed
    {
        $meta = $this->meta ?? [];
        return $meta[$key] ?? $default;
    }

    /**
     * @return ParamDefinition[]
     */
    public function params(ParamDefinitionFactory $factory): array
    {
        return $this->generateParams($this->params ?? [], $factory);
    }

    /**
     * @param array<string, array{
     * 	'type': string,
     * 	'title': ?string,
     * 	'description': ?string,
     * 	'validation': ?string,
     * 	'default': mixed,
     *	'inner': mixed,
     *  'enum': ?mixed[],
     * }> $params
     * @return ParamDefinition[]
     */
    protected function generateParams(array $params, ParamDefinitionFactory $factory): array
    {
        $definitions = [];

        foreach ($params as $key => $value) {
            // Set a default type to string
            $value = array_merge(['type' => 'string'], $value);

            /** @var callable $callable */
            $callable = [$factory, $value['type']];

            /** @var ParamDefinition $definition */
            $definition = call_user_func_array($callable, [
                'title'       => $value['title'] ?? $key,
                'name'        => $key,
                'default'     => $value['default'] ?? null,
                'validation'  => $value['validation'] ?? '',
                'description' => $value['description'] ?? null,
                'enum'        => $value['enum'] ?? [],
            ]);

            if (isset($value['inner'])) {
                /**
                 * @var array<string, array{
                 * 	'type': string,
                 * 	'title': ?string,
                 * 	'description': ?string,
                 * 	'validation': ?string,
                 * 	'default': mixed,
                 *	'inner': mixed,
                 *  'enum': ?mixed[],
                 * }> $inner
                 */
                $inner = $value['inner'];
                $definition->setInner($this->generateParams($inner, $factory));
            }

            $definitions[] = $definition;
        }

        if (!$this->route) {
            return $definitions;
        }

        $handler = $this->route->getParsedHandler();
        if (!isset($handler['class'])) {
            return $definitions;
        }

        $class = $handler['class'];
        $method = $handler['method'];

        /** @var ParamDefinition[] */
        $definitions = array_merge($definitions, $this->getAttributeParams(
            $factory,
            $class,
            $method,
        ));

        // Remove duplicates by creating a map using param names as keys
        $uniqueDefinitions = [];
        foreach ($definitions as $definition) {
            $uniqueDefinitions[$definition->name] = $definition;
        }

        return array_values($uniqueDefinitions);
    }

    /**
     * @param class-string $class
     * @return ParamDefinition[]
     */
    protected function getAttributeParams(ParamDefinitionFactory $factory, string $class, string $method = ''): array
    {
        $definitions = [];
        $reflection = new \ReflectionClass($class);

        // Get params from class attributes
        $classParams = $reflection->getAttributes(Param::class);
        foreach ($classParams as $attribute) {
            $instance = $attribute->newInstance();
            $definitions[] = $instance->getParamDefinition($factory, $this->getAttributeParams(...));
        }

        // Get params from method attributes if method is specified
        if ($method) {
            $methodReflection = $reflection->getMethod($method);
            $methodParams = $methodReflection->getAttributes(Param::class);
            foreach ($methodParams as $attribute) {
                $instance = $attribute->newInstance();
                $definitions[] = $instance->getParamDefinition($factory, $this->getAttributeParams(...));
            }
        }

        // Get params from class properties
        $properties = $reflection->getProperties();
        foreach ($properties as $property) {
            $propertyParams = $property->getAttributes(Param::class);
            foreach ($propertyParams as $attribute) {
                $instance = $attribute->newInstance();
                $definitions[] = $instance->getParamDefinition($factory, $this->getAttributeParams(...));
            }
        }

        // Get params from UseEntity attributes on class
        $useEntityAttributes = $reflection->getAttributes(Entity::class);
        foreach ($useEntityAttributes as $attribute) {
            $instance = $attribute->newInstance();
            if ($instance->request) {
                $definitions = array_merge($definitions, $this->getAttributeParams($factory, $instance->class));
            }
        }

        // Get params from UseEntity attributes on method if specified
        if ($method) {
            $methodReflection = $reflection->getMethod($method);
            $useEntityAttributes = $methodReflection->getAttributes(Entity::class);
            foreach ($useEntityAttributes as $attribute) {
                $instance = $attribute->newInstance();
                if ($instance->request) {
                    $definitions = array_merge($definitions, $this->getAttributeParams($factory, $instance->class));
                }
            }
        }

        return $definitions;
    }
}
