<?php

namespace Orkestra;

use Orkestra\Entities\AbstractEntity;
use DI\Definition\Helper\CreateDefinitionHelper;
use DI\Container;
use DI;
use InvalidArgumentException;

/**
 * @property-read string $name
 * @property-read mixed $service
 * @property-read bool $autowire
 */
class AppBind extends AbstractEntity
{
    public function __construct(
        protected string     $name,
        protected mixed      $service,
        protected bool       $autowire = true,
        private ?Container $container = null,
    ) {
        $isClassString = is_string($service);

        if ($isClassString && !class_exists($service)) {
            throw new InvalidArgumentException("Class \"$service\" does not exist");
        }

        $this->service = $isClassString
            ? ($autowire ? DI\autowire($service) : DI\create($service))
            : $service;

        $this->update();
    }

    /**
     * Defines the arguments to use to call the constructor.
     *
     * This method takes a variable number of arguments, example:
     *     ->constructor($param1, $param2, $param3)
     *
     * @param mixed ...$parameters Parameters to use for calling the constructor of the class.
     *
     * @return self
     */
    public function constructor(mixed ...$parameters): self
    {
        if (!($this->service instanceof CreateDefinitionHelper)) {
            throw new InvalidArgumentException('Cannot define constructor parameters for a non-class services');
        }
        $this->service->constructor(...$parameters);
        return $this->update();
    }

    /**
     * Defines a value to inject in a property of the object.
     *
     * @param string $property Entry in which to inject the value.
     * @param mixed  $value    Value to inject in the property.
     *
     * @return self
     */
    public function property(string $property, mixed $value): self
    {
        if (!($this->service instanceof CreateDefinitionHelper)) {
            throw new InvalidArgumentException('Cannot define property injection for a non-class services');
        }
        $this->service->property($property, $value);
        return $this->update();
    }

    /**
     * Defines a method to call and the arguments to use.
     *
     * This method takes a variable number of arguments after the method name, example:
     *
     *     ->method('myMethod', $param1, $param2)
     *
     * Can be used multiple times to declare multiple calls.
     *
     * @param string $method       Name of the method to call.
     * @param mixed ...$parameters Parameters to use for calling the method.
     *
     * @return self
     */
    public function method(string $method, mixed ...$parameters): self
    {
        if (!($this->service instanceof CreateDefinitionHelper)) {
            throw new InvalidArgumentException('Cannot define method calls for a non-class services');
        }
        $this->service->method($method, ...$parameters);
        return $this->update();
    }

    protected function update(): self
    {
        if ($this->container) {
            $this->container->set($this->name, $this->service);
        }
        return $this;
    }
}
