<?php

use Orkestra\Handlers\CommandsHandler;
use Symfony\Component\Console\Application;
use \Mockery as m;

test('can handle command execution', function () {
    // Create a mock console application
    $console = m::mock(Application::class);
    
    // Set up expectations on the mock
    $console->shouldReceive('run')
        ->once()
        ->andReturn(0);
    
    // Create the handler with our mock console
    $handler = new CommandsHandler($console);
    
    // Call the handle method
    $handler->handle();
    
    // Mockery will automatically verify expectations when the test ends
});

test('command handler implements HandlerInterface', function () {
    // Check that the handler implements the correct interface
    $interfaces = class_implements(CommandsHandler::class);
    expect($interfaces)->toHaveKey('Orkestra\Interfaces\HandlerInterface');
}); 