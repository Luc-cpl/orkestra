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

test('displays empty configuration definitions correctly', function () {
    // Create a mock configuration with empty definitions
    $config = Mockery::mock(ConfigurationInterface::class);

    // Set up the mock to return an empty definition
    $config->shouldReceive('get')
        ->with('definition')
        ->andReturn([]);

    // Create the command with our mock config
    $command = new ConfigOptionsCommand($config);

    // Use CommandTester to test the command
    $commandTester = new CommandTester($command);
    $commandTester->execute([]);

    // Get the output
    $output = $commandTester->getDisplay();

    // Check that the command executed successfully
    expect($commandTester->getStatusCode())->toBe(0);

    // Check that the output shows the header but no options
    expect($output)->toContain('Available configuration options');
    expect($output)->toContain('Key');
    expect($output)->toContain('Required');
    expect($output)->toContain('Description');
});

test('handles complex default values correctly', function () {
    // Create a mock configuration
    $config = Mockery::mock(ConfigurationInterface::class);

    // Function to be used as a default value
    $callableDefault = fn () => 'calculated value';

    // Complex array as default value
    $complexArray = ['key1' => 'value1', 'key2' => ['nested' => 'value']];

    // Define a sample definition with complex default values
    $sampleDefinition = [
        'callable_default' => ['Option with callable default', $callableDefault],
        'complex_array' => ['Option with complex array', $complexArray],
        'object_default' => ['Option with object', new stdClass()],
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

    // Check the command executed successfully
    expect($commandTester->getStatusCode())->toBe(0);

    // Check that each option is displayed correctly
    expect($output)->toContain('callable_default');
    expect($output)->toContain('complex_array');
    expect($output)->toContain('object_default');

    // All these have default values, so they should be marked as not required
    expect(substr_count($output, 'No'))->toBe(3);
});

test('handles special characters in option names and descriptions', function () {
    // Create a mock configuration
    $config = Mockery::mock(ConfigurationInterface::class);

    // Define a sample definition with special characters
    $sampleDefinition = [
        'option-with-dash' => ['Description with special chars: @#$%^&*()'],
        'option_with_underscore' => ['Another description with <html> tags'],
        'option.with.dots' => ['Description with line\nbreak'],
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

    // Check the command executed successfully
    expect($commandTester->getStatusCode())->toBe(0);

    // Check that special characters are handled correctly
    expect($output)->toContain('option-with-dash');
    expect($output)->toContain('option_with_underscore');
    expect($output)->toContain('option.with.dots');
    expect($output)->toContain('Description with special chars: @#$%^&*()');
    expect($output)->toContain('Another description with <html> tags');
    expect($output)->toContain('Description with line\nbreak');
});
