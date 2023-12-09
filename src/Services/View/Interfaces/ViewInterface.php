<?php

namespace Orkestra\Services\View\Interfaces;

interface ViewInterface
{
	/**
	 * Render the view
	 *
	 * @param string  $name
	 * @param mixed[] $context
	 * @return string
	 */
	public function render(string $name, array $context = []): string;
}
