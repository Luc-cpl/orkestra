<?php

namespace Orkestra\Providers;

use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;
use Orkestra\Interfaces\HooksInterface;

use Orkestra\Models\Hook;
use Orkestra\Services\HooksService;

class HooksProvider implements ProviderInterface
{
	/**
	 * Register services with the container.
	 * We can use the container to bind services to the app.
	 * 
	 * Do not use the container to resolve services at this point.
	 *
	 * @param App $app
	 * @return void
	 */
	public function register(App $app): void
	{
		$app->singleton(HooksInterface::class, HooksService::class);
	}

	/**
	 * Here we can use the container to resolve and start services.
	 * 
	 * @param App $app
	 * @return void
	 */
	public function boot(App $app): void
	{

	}
}