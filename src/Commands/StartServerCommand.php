<?php

namespace Orkestra\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

#[AsCommand(name: 'app:serve')]
class StartServerCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('Starts a test server on the specified port.')
            ->setHelp('This command starts a test server for your application.')
            ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'The port to run the server on.', 3000);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /**
         * @var string $port
         */
        $port = $input->getOption('port');

        $output->writeln("Starting the test server on port $port...");
        $output->writeln("Press Ctrl+C to stop." . PHP_EOL);

        $process = $this->createProcess($port);
        $process->setTimeout(null);
        $process->run(function ($type, $buffer) use ($output) {
            $output->write($buffer);
        });

        return Command::SUCCESS;
    }

    /**
     * Create a process to run the PHP server
     * This method can be overridden in tests
     */
    protected function createProcess(string $port): Process
    {
        return new Process(['php', '-S', 'localhost:' . $port, '-t', 'public']);
    }
}
