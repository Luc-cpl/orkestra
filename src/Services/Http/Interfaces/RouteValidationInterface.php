<?php

namespace Orkestra\Services\Http\Interfaces;

/**
 * Route Validation Interface
 */
interface RouteValidationInterface
{
	/**
	 * @param array<string, string> $validation Validation rules
	 * @return self
	 */
	public function setValidation(array $validation): self;

	/**
	 * @return array<string, string>
	 */
	public function getValidation(): array;
}
