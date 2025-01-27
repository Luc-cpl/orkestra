<?php

namespace Orkestra\Services\Http\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Entity
{
	/**
	 * @param class-string $class
	 */
	public function __construct(
        public readonly string $class
	) {
		//
	}
}