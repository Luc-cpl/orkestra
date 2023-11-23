<?php

namespace Orkestra\Services\Http\Entities;

use Orkestra\Services\Http\Enum\ResponseStatus;

class ResponseDefinition
{
	/**
	 * @param ParamDefinition[] $schema
	 */
	public function __construct(
		public readonly ResponseStatus $status,
		public readonly string         $description,
		public readonly array          $schema,
	) {
	}
}
