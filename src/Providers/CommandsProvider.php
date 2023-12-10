<?php

namespace Orkestra\Providers;

use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;
use Orkestra\Commands\StartServerCommand;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

class CommandsProvider implements ProviderInterface
{
	/**
	 * @var array<class-string<Command>>
	 */
	protected array $commands = [
		StartServerCommand::class,
	];

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
		$app->singleton(Application::class, Application::class);

		// Set the required config so we can validate it
		$app->config()->set('validation', [
			// 'app_name' => fn ($value) => is_string($value) ? true : 'App name must be a string',
			'commands' => function ($value) {
				$extendedClass = Command::class;
				if (!is_array($value)) {
					return "Commands must be an array with command classes extending \"$extendedClass\"";
				}
				foreach ($value as $command) {
					// Check if command class exists
					if (!class_exists($command)) {
						return "Command class \"$command\" does not exist";
					}
					// Check if command class extends Symfony Command
					if (!in_array(Command::class, class_parents($command), true)) {
						return "Command class \"$command\" must extends \"$extendedClass\"";
					}
				}
				return true;
			},
		]);
	}

	/**
	 * Here we can use the container to resolve and start services.
	 * 
	 * @param App $app
	 * @return void
	 */
	public function boot(App $app): void
	{
		$console  = $app->get(Application::class);
		$commands = $app->config()->get('commands');

		/**
		 * Allow other packages to add commands
		 *
		 * @var array<class-string<Command>> $commands
		 */
		$commands = $app->hookQuery('commands.register', $commands);
		$commands = array_merge($this->commands, $commands);

		foreach (array_unique($commands) as $command) {
			$console->add($app->get($command));
		}
	}
}
