<?php

use Orkestra\Handlers\CommandsHandler;
use Symfony\Component\Console\Application;
use Mockery as m;

covers(CommandsHandler::class);

test('can handle command execution', function () {
    // Create a mock console application
    $console = m::mock(Application::class);

    // Set up expectations on the mock
    $console->shouldReceive('run')
        ->once()
        ->andReturn(0);

    // Create the handler with our mock console
    $handler = new CommandsHandler($console);
    $handler->handle();
});

test('command handler implements HandlerInterface', function () {
    // Check that the handler implements the correct interface
    $interfaces = class_implements(CommandsHandler::class);
    expect($interfaces)->toHaveKey('Orkestra\Interfaces\HandlerInterface');
});
