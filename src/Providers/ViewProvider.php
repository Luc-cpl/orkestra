<?php

namespace Orkestra\Providers;

use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;
use Orkestra\Interfaces\ViewInterface;
use Orkestra\Services\View\View;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ViewProvider implements ProviderInterface
{
	/**
	 * The Twig extensions to register
	 * 
	 * This can be overridden by the app
	 * hook `twig.extensions`
	 *
	 * @var array
	 */
	protected array $extensions = [];

	/**
	 * Register services with the container.
	 *
	 * @param App $app
	 * @return void
	 */
	public function register(App $app): void
	{
		$app->bind(ViewInterface::class, View::class);
		$app->singleton(Environment::class, function () use ($app) {
			$isProduction = $app->config()->get('env') === 'production';
			$root = $app->config()->get('root');
			$loader = new FilesystemLoader("$root/views");
			$app->hookCall('twig.loader', $loader);

			$twig = new Environment($loader, $app->hookQuery('twig.environment', [
				'cache' => $isProduction ? "$root/cache/views" : false,
			]));

			/**
			 * Allow the app to register Twig extensions with hook event
			 */
			$twig->setExtensions(array_map(
				fn ($extension) => $app->get($extension),
				$app->hookQuery('twig.extensions', [])
			));

			$app->hookCall('twig.init', $twig);

			return $twig;
		});
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
