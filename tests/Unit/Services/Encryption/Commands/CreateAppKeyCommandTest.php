<?php

use Orkestra\Services\Encryption\Commands\CreateAppKeyCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Mockery\MockInterface;

beforeEach(function () {
    // Create a temporary directory for testing
    $this->tempDir = sys_get_temp_dir() . '/orkestra_key_test_' . uniqid();
    mkdir($this->tempDir);
    
    // Mock the configuration interface
    $this->config = Mockery::mock(\Orkestra\Interfaces\ConfigurationInterface::class);
    $this->config->shouldReceive('get')->with('root')->andReturn($this->tempDir);
    
    // Create the command
    $this->command = new CreateAppKeyCommand($this->config);
    $this->commandTester = new CommandTester($this->command);
});

afterEach(function () {
    // Clean up the temporary directory and files
    if (file_exists($this->tempDir . '/.env')) {
        unlink($this->tempDir . '/.env');
    }
    if (is_dir($this->tempDir)) {
        rmdir($this->tempDir);
    }
});

test('can create a new application key with no existing file', function () {
    // Execute the command
    $result = $this->commandTester->execute([]);
    
    // Assert the command was successful
    expect($result)->toBe(0);
    
    // Check that the output contains success message
    $output = $this->commandTester->getDisplay();
    expect($output)->toContain('Application key created successfully');
    
    // Verify that the .env file was created with APP_KEY
    $envContent = file_get_contents($this->tempDir . '/.env');
    expect($envContent)->toMatch('/^APP_KEY=[a-f0-9]{64}$/');
});

test('can rotate an existing application key', function () {
    // Create an existing .env file with an app key
    $originalKey = 'existing_app_key_for_testing';
    file_put_contents($this->tempDir . '/.env', "APP_KEY=$originalKey");
    
    // Execute the command
    $result = $this->commandTester->execute([]);
    
    // Assert the command was successful
    expect($result)->toBe(0);
    
    // Check that the output contains rotation message
    $output = $this->commandTester->getDisplay();
    expect($output)->toContain('Application key rotated successfully');
    
    // Verify that the .env file was updated with a new APP_KEY and the old key was saved
    $envContent = file_get_contents($this->tempDir . '/.env');
    
    // Should contain APP_PREVIOUS_KEYS with the original key
    expect($envContent)->toContain("APP_PREVIOUS_KEYS=$originalKey");
    
    // Should contain a new APP_KEY
    expect($envContent)->toMatch('/APP_KEY=[a-f0-9]{64}/');
    
    // The new key should be different from the original
    preg_match('/APP_KEY=([a-f0-9]{64})/', $envContent, $matches);
    $newKey = $matches[1] ?? '';
    expect($newKey)->not->toBe($originalKey);
});

test('handles multiple application key rotations correctly', function () {
    // Create an existing .env file with an app key and previous keys
    $originalKey = 'key1';
    $previousKeys = 'key2,key3,key4';
    file_put_contents(
        $this->tempDir . '/.env',
        "APP_KEY=$originalKey\nAPP_PREVIOUS_KEYS=$previousKeys"
    );
    
    // Execute the command
    $result = $this->commandTester->execute([]);
    
    // Assert the command was successful
    expect($result)->toBe(0);
    
    // Verify the .env file was updated correctly
    $envContent = file_get_contents($this->tempDir . '/.env');
    
    // Should contain APP_PREVIOUS_KEYS with the original key at the beginning
    expect($envContent)->toMatch('/APP_PREVIOUS_KEYS=' . preg_quote($originalKey) . ',' . preg_quote($previousKeys) . '/');
    
    // Should contain a new APP_KEY
    expect($envContent)->toMatch('/APP_KEY=[a-f0-9]{64}/');
});

test('handles existing .env file with no APP_KEY entry', function () {
    // Create an existing .env file without an app key
    $envContent = "DEBUG=true\nLOG_LEVEL=debug\n";
    file_put_contents($this->tempDir . '/.env', $envContent);
    
    // Execute the command
    $result = $this->commandTester->execute([]);
    
    // Assert the command was successful
    expect($result)->toBe(0);
    
    // Check that the output contains the creation message
    $output = $this->commandTester->getDisplay();
    expect($output)->toContain('Application key created successfully');
    
    // Verify the .env file was updated correctly
    $newEnvContent = file_get_contents($this->tempDir . '/.env');
    
    // Should still contain the original content
    expect($newEnvContent)->toContain('DEBUG=true');
    expect($newEnvContent)->toContain('LOG_LEVEL=debug');
    
    // Should now have an APP_KEY entry
    expect($newEnvContent)->toMatch('/APP_KEY=[a-f0-9]{64}/');
    
    // Should not have added APP_PREVIOUS_KEYS since there was no previous key
    expect($newEnvContent)->not->toContain('APP_PREVIOUS_KEYS');
});

test('limits key history to 5 previous keys', function () {
    // Create an existing .env file with an app key and many previous keys
    $originalKey = 'current-key';
    $previousKeys = 'key1,key2,key3,key4,key5';
    file_put_contents(
        $this->tempDir . '/.env',
        "APP_KEY=$originalKey\nAPP_PREVIOUS_KEYS=$previousKeys"
    );
    
    // Execute the command
    $result = $this->commandTester->execute([]);
    
    // Assert the command was successful
    expect($result)->toBe(0);
    
    // Verify the .env file was updated correctly
    $envContent = file_get_contents($this->tempDir . '/.env');
    
    // Previous keys should only have 5 entries total
    $matches = [];
    preg_match('/APP_PREVIOUS_KEYS=([^\\n]+)/', $envContent, $matches);
    $newPreviousKeys = $matches[1] ?? '';
    $keyCount = count(explode(',', $newPreviousKeys));
    expect($keyCount)->toBe(5);
    
    // Should contain the original key as the first previous key
    expect($envContent)->toMatch('/APP_PREVIOUS_KEYS=' . preg_quote($originalKey) . '/');
    
    // Should not contain the last key in the original previous keys list
    expect($envContent)->not->toContain('key5');
});

test('can handle empty .env file', function () {
    // Create an empty .env file
    file_put_contents($this->tempDir . '/.env', '');
    
    // Execute the command
    $result = $this->commandTester->execute([]);
    
    // Assert the command was successful
    expect($result)->toBe(0);
    
    // Check that the output contains creation message
    $output = $this->commandTester->getDisplay();
    expect($output)->toContain('Application key created successfully');
    
    // Verify the .env file was updated correctly
    $envContent = file_get_contents($this->tempDir . '/.env');
    expect($envContent)->toMatch('/APP_KEY=[a-f0-9]{64}/');
}); 