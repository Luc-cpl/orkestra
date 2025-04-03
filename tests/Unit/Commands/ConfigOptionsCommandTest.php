<?php

use Orkestra\Commands\ConfigOptionsCommand;
use Orkestra\Interfaces\ConfigurationInterface;
use Symfony\Component\Console\Tester\CommandTester;

test('can list configuration options', function () {
    // Create a mock configuration
    $config = Mockery::mock(ConfigurationInterface::class);
    
    // Define a sample definition
    $sampleDefinition = [
        'debug' => ['Enable debug mode', false],
        'required_option' => ['A required configuration option'],
        'another_option' => ['Another configuration option', 'default value'],
    ];
    
    // Set up the mock to return our sample definition
    $config->shouldReceive('get')
        ->with('definition')
        ->andReturn($sampleDefinition);
    
    // Create the command with our mock config
    $command = new ConfigOptionsCommand($config);
    
    // Use CommandTester to test the command
    $commandTester = new CommandTester($command);
    $commandTester->execute([]);
    
    // Get the output
    $output = $commandTester->getDisplay();
    
    // Check that the command executed successfully
    expect($commandTester->getStatusCode())->toBe(0);
    
    // Check that the output contains our configuration options
    expect($output)->toContain('Available configuration options');
    expect($output)->toContain('debug');
    expect($output)->toContain('required_option');
    expect($output)->toContain('another_option');
    
    // Check that the output correctly indicates which options are required
    expect($output)->toContain('No'); // debug is not required
    expect($output)->toContain('Yes'); // required_option is required
});

test('correctly formats the configuration table', function () {
    // Create a mock configuration
    $config = Mockery::mock(ConfigurationInterface::class);
    
    // Define a sample definition with various types
    $sampleDefinition = [
        'string_option' => ['A string option', 'default'],
        'bool_option' => ['A boolean option', false],
        'array_option' => ['An array option', []],
        'required_option' => ['A required option'],
    ];
    
    // Set up the mock
    $config->shouldReceive('get')
        ->with('definition')
        ->andReturn($sampleDefinition);
    
    // Create the command with our mock config
    $command = new ConfigOptionsCommand($config);
    
    // Use CommandTester to test the command
    $commandTester = new CommandTester($command);
    $commandTester->execute([]);
    
    // Get the output
    $output = $commandTester->getDisplay();
    
    // Check the table formatting
    expect($output)->toContain('Key');
    expect($output)->toContain('Required');
    expect($output)->toContain('Description');
    
    // Check that each option is properly shown
    expect($output)->toContain('string_option');
    expect($output)->toContain('bool_option');
    expect($output)->toContain('array_option');
    expect($output)->toContain('required_option');
    
    // Check that descriptions are shown
    expect($output)->toContain('A string option');
    expect($output)->toContain('A boolean option');
    expect($output)->toContain('An array option');
    expect($output)->toContain('A required option');
}); 