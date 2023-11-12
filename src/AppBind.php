<?php

namespace Orkestra;

use DI\Container;
use DI\Definition\Helper\CreateDefinitionHelper;
use DI;

use Exception;

class AppBind
{
	public function __construct(
		protected Container $container,
		protected string    $name,
		protected mixed     $service,
		protected bool      $autowire = true
	) {
		$isClassString = is_string($service);

		if ($isClassString && !class_exists($service)) {
			throw new Exception("Class \"$service\" does not exist");
		}

		$this->service = $isClassString
			? ($autowire ? DI\autowire($service) : DI\create($service))
			: $service;

		$this->set();
	}

	/**
	 * Defines the arguments to use to call the constructor.
	 *
	 * This method takes a variable number of arguments, example:
	 *     ->constructor($param1, $param2, $param3)
	 *
	 * @param mixed ...$parameters Parameters to use for calling the constructor of the class.
	 *
	 * @return $this
	 */
	public function constructor(mixed ...$parameters): self
	{
		if (!($this->service instanceof CreateDefinitionHelper)) {
			return $this;
		}
		$this->service->constructor($parameters);
		return $this->set();
	}

	/**
	 * Defines a value to inject in a property of the object.
	 *
	 * @param string $property Entry in which to inject the value.
	 * @param mixed  $value    Value to inject in the property.
	 *
	 * @return $this
	 */
	public function property(string $property, mixed $value): self
	{
		if (!($this->service instanceof CreateDefinitionHelper)) {
			return $this;
		}
		$this->service->property($property, $value);
		return $this->set();
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
	 * @return $this
	 */
	public function method(string $method, mixed ...$parameters): self
	{
		if (!($this->service instanceof CreateDefinitionHelper)) {
			return $this;
		}
		$this->service->method($method, ...$parameters);
		return $this->set();
	}

	protected function set(): self
	{
		$this->container->set($this->name, $this->service);
		return $this;
	}
}
