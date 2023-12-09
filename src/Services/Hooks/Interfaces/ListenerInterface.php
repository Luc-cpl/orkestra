<?php

namespace Orkestra\Services\Hooks\Interfaces;

interface ListenerInterface
{
	/**
	 * @return string|array
	 */
	public function hook(): string|array;

	/**
	 * @param mixed ...$args
	 * @return mixed
	 */
	public function __invoke(mixed ...$args): mixed;
}
