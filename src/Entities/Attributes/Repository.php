<?php

namespace Orkestra\Entities\Attributes;

use Attribute;

/**
 * @template T of object
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Repository
{
    /**
     * @param class-string<T> $class
     */
    public function __construct(
        public readonly string $class
    ) {
        //
    }
}
