<?php

namespace Orkestra\Services\Encryption\Commands;

use Orkestra\Interfaces\ConfigurationInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:key:create')]
class CreateAppKeyCommand extends Command
{
    public function __construct(
        private ConfigurationInterface $config
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Create a new application key.')
            ->setHelp('This command creates a new application key for the application.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $appKey = bin2hex(random_bytes(32));
        $root = $this->config->get('root');

        $file = $root . '/.env';

        if (!file_exists($file)) {
            file_put_contents($file, 'APP_KEY=' . $appKey);
            $output->writeln('Application key created successfully.');
            return Command::SUCCESS;
        }

        $env = file_get_contents($root . '/.env');
        $env = explode(PHP_EOL, $env ? $env : '');

        $currentKey = null;
        $hasPrevious = false;
        foreach ($env as $key => $line) {
            if (!str_starts_with($line, 'APP_KEY=')) {
                continue;
            }
            $currentKey = ltrim($line, 'APP_KEY=');
            $env[$key] = 'APP_KEY=' . $appKey;
            break;
        }

        foreach ($env as $key => $line) {
            if (!str_starts_with($line, 'APP_PREVIOUS_KEYS=')) {
                continue;
            }
            $hasPrevious = true;
            $env[$key] = 'APP_PREVIOUS_KEYS=' . $currentKey . ',' . ltrim($line, 'APP_PREVIOUS_KEYS=');
            $env[$key] = implode(',', array_slice(explode(',', $env[$key]), 0, 5));
            break;
        }

        if (!$currentKey) {
            array_unshift($env, 'APP_KEY=' . $appKey);
        }

        if (!$hasPrevious && $currentKey) {
            array_unshift($env, 'APP_PREVIOUS_KEYS=' . $currentKey);
        }

        file_put_contents($root . '/.env', implode(PHP_EOL, $env));

        $message = $currentKey ? 'Application key rotated successfully.' : 'Application key created successfully.';

        $output->writeln($message);
        return Command::SUCCESS;
    }
}
