<?php

namespace Orkestra\Services\Router\Interfaces;

interface RouteValidationInterface
{
	public function setValidation(array $validation): self;
	public function getValidation(): array;
}
