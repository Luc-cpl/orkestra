<?php

namespace Orkestra\Providers;

use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;
use Orkestra\Services\View\Interfaces\ViewInterface;
use Orkestra\Services\View\Twig\OrkestraExtension;
use Orkestra\Services\View\Twig\RuntimeLoader;
use Orkestra\Services\View\View;

use Twig\Environment;
use Twig\Extra\Markdown\DefaultMarkdown;
use Twig\Extra\Markdown\MarkdownExtension;
use Twig\Extra\Markdown\MarkdownInterface;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;
use Twig\RuntimeLoader\RuntimeLoaderInterface;
use Twig\Extension\AbstractExtension;

class ViewProvider implements ProviderInterface
{
	/**
	 * @var array<class-string<AbstractExtension>>
	 */
	protected array $extensions = [
		MarkdownExtension::class,
		OrkestraExtension::class,
	];

	/**
	 * @var array<class-string, class-string>
	 */
	protected array $runtimeInterfaces = [
		MarkdownInterface::class => DefaultMarkdown::class,
	];

	/**
	 * Register services with the container.
	 *
	 * @param App $app
	 * @return void
	 */
	public function register(App $app): void
	{
		/**
		 * Register runtime interfaces
		 */
		foreach ($this->runtimeInterfaces as $interface => $class) {
			$app->bind($interface, $class);
		}

		$app->bind(ViewInterface::class, View::class);
		$app->bind(RuntimeLoaderInterface::class, RuntimeLoader::class);
		$app->bind(LoaderInterface::class, FilesystemLoader::class)->constructor(
			$app->config()->get('root') . '/views',
		);

		$app->singleton(Environment::class, function (
			RuntimeLoaderInterface $runtimeLoader,
			LoaderInterface        $loader,
		) use ($app) {
			/** @var string $root */
			$root         = $app->config()->get('root');
			$isProduction = $app->config()->get('env') === 'production';

			$twig = new Environment($loader, [
				'cache'       => "$root/cache/views",
				'auto_reload' => !$isProduction,
			]);

			$mappedExtensions = array_map(
				fn (string $extension) => $app->get($extension),
				$this->extensions
			);

			$twig->addRuntimeLoader($runtimeLoader);
			$twig->setExtensions($mappedExtensions);

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
