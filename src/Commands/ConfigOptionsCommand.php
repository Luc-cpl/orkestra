<?php

namespace Orkestra\Commands;

use Orkestra\Interfaces\ConfigurationInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:config:list')]
class ConfigOptionsCommand extends Command
{
    public function __construct(
        private ConfigurationInterface $config
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('List the available configuration options for the application.')
            ->setHelp('This command lists the available configuration options for the application.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Available configuration options:');
        $output->writeln('');

        /** @var array<string, array{bool, string}> */
        $definition = $this->config->get('definition');

        $definition = array_map(function ($value, $key) {
            return [$key, isset($value[1]) ? 'Yes' : 'No', $value[0]];
        }, $definition, array_keys($definition));

        // Create a table to display the configuration options, with key, required, and description columns
        $table = new Table($output);
        $table
        ->setHeaders(['Key', 'Required', 'Description'])
        ->setRows($definition);

        $table->render();

        return Command::SUCCESS;
    }
}
