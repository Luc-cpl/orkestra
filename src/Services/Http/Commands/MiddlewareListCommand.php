<?php

namespace Orkestra\Services\Http\Commands;

use Orkestra\Interfaces\ConfigurationInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'middleware:list')]
class MiddlewareListCommand extends Command
{
    public function __construct(
        private ConfigurationInterface $config,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('List the available middleware for the application.')
            ->setHelp('This command lists the available middleware stack for the application.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Available middleware options:');
        $output->writeln('');

        /** @var array<string, array<string, string>> */
        $middleware = $this->config->get('middleware');

        $middlewareSources = $middleware['sources'] ?? [];
        $middlewareStack   = $middleware['stack'] ?? [];

        $definition = array_map(function ($middleware, $alias) use ($middlewareSources) {
            return [$alias, $middleware, $middlewareSources[$alias] ?? ''];
        }, $middlewareStack, array_keys($middlewareStack));

        $table = new Table($output);
        $table
            ->setHeaders(['Alias', 'Middleware', 'Placed By'])
            ->setRows($definition);

        $table->render();

        return Command::SUCCESS;
    }
}
