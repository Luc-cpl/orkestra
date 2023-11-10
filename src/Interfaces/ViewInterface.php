<?php

namespace Orkestra\Interfaces;

interface ViewInterface
{
	/**
	 * Render the view
	 *
	 * @param string $name
	 * @param array $context
	 * @return string
	 */
	public function render($name, array $context = []): string;
}
