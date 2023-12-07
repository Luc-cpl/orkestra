<?php

namespace Orkestra\Interfaces;

interface HandlerInterface
{
	/**
	 * Handle the current request.
	 * This should be called to handle the current request from the provider.
	 */
	public function handle(): void;
}
