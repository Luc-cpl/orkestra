<?php

use Orkestra\Commands\StartServerCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Process\Process;
use \Mockery as m;

test('can start server on default port', function () {
    // Mock the Process
    $mockProcess = m::mock(Process::class);
    $mockProcess->shouldReceive('setTimeout')->once()->with(null)->andReturnSelf();
    $mockProcess->shouldReceive('run')->once()->andReturnUsing(function ($callback) {
        // Simulate some output
        $callback(Process::OUT, 'PHP test server started on localhost:3000');
        return 0;
    });
    
    // Create a new instance of the command that returns our mock process
    $command = new class($mockProcess) extends StartServerCommand {
        private $mockProcess;
        
        public function __construct($mockProcess) 
        {
            $this->mockProcess = $mockProcess;
            parent::__construct();
        }
        
        protected function createProcess(string $port): Process
        {
            // Verify we're using the default port
            expect($port)->toBe('3000');
            return $this->mockProcess;
        }
    };
    
    // Use CommandTester to test the command
    $commandTester = new CommandTester($command);
    $commandTester->execute([]);
    
    // Get the output
    $output = $commandTester->getDisplay();
    
    // Check that the command executed successfully
    expect($commandTester->getStatusCode())->toBe(0);
    
    // Check that the output contains the expected information
    expect($output)->toContain('Starting the test server on port 3000');
    expect($output)->toContain('Press Ctrl+C to stop');
    expect($output)->toContain('PHP test server started on localhost:3000');
});

test('can start server on custom port', function () {
    // Mock the Process
    $mockProcess = m::mock(Process::class);
    $mockProcess->shouldReceive('setTimeout')->once()->with(null)->andReturnSelf();
    $mockProcess->shouldReceive('run')->once()->andReturnUsing(function ($callback) {
        // Simulate some output
        $callback(Process::OUT, 'PHP test server started on localhost:8080');
        return 0;
    });
    
    // Create a new instance of the command that returns our mock process
    $command = new class($mockProcess) extends StartServerCommand {
        private $mockProcess;
        
        public function __construct($mockProcess) 
        {
            $this->mockProcess = $mockProcess;
            parent::__construct();
        }
        
        protected function createProcess(string $port): Process
        {
            // Verify we're using the custom port
            expect($port)->toBe('8080');
            return $this->mockProcess;
        }
    };
    
    // Use CommandTester to test the command with a custom port
    $commandTester = new CommandTester($command);
    $commandTester->execute([
        '--port' => 8080
    ]);
    
    // Get the output
    $output = $commandTester->getDisplay();
    
    // Check that the command executed successfully
    expect($commandTester->getStatusCode())->toBe(0);
    
    // Check that the output contains the expected information
    expect($output)->toContain('Starting the test server on port 8080');
});

test('can use port shortcut option', function () {
    // Mock the Process
    $mockProcess = m::mock(Process::class);
    $mockProcess->shouldReceive('setTimeout')->once()->with(null)->andReturnSelf();
    $mockProcess->shouldReceive('run')->once()->andReturnUsing(function ($callback) {
        // Simulate some output
        $callback(Process::OUT, 'PHP test server started on localhost:9090');
        return 0;
    });
    
    // Create a new instance of the command that returns our mock process
    $command = new class($mockProcess) extends StartServerCommand {
        private $mockProcess;
        
        public function __construct($mockProcess) 
        {
            $this->mockProcess = $mockProcess;
            parent::__construct();
        }
        
        protected function createProcess(string $port): Process
        {
            // Verify we're using the custom port with short option
            expect($port)->toBe('9090');
            return $this->mockProcess;
        }
    };
    
    // Use CommandTester to test the command with a short option
    $commandTester = new CommandTester($command);
    $commandTester->execute([
        '-p' => 9090
    ]);
    
    // Check that the command executed successfully
    expect($commandTester->getStatusCode())->toBe(0);
}); 