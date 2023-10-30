<?php

namespace Orkestra\Controllers\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class StartServerCommand extends Command
{
    protected static $defaultName = 'app:serve';

    protected function configure()
    {
        $this
            ->setDescription('Starts a test server on the specified port.')
            ->setHelp('This command starts a test server for your application.')
            ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'The port to run the server on.', 3000);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $port = $input->getOption('port');

        $output->writeln("Starting the test server on port $port...");
        $output->writeln("Press Ctrl+C to stop." . PHP_EOL);

        $process = new Process(['php', '-S', 'localhost:' . $port, '-t', 'public']);
        $process->setTimeout(null);
        $process->run(function ($type, $buffer) use ($output) {
            $output->write($buffer);
        });

        return Command::SUCCESS;
    }
}