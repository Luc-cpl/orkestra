<?php

use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;
use Orkestra\Providers\EncryptionServiceProvider;
use Orkestra\Services\Encryption\Commands\CreateAppKeyCommand;
use Orkestra\Services\Encryption\Encrypt;
use Orkestra\Services\Encryption\Interfaces\EncryptInterface;

beforeEach(function () {
    // Create a fresh app instance for each test
    $this->app = app([
        'slug' => 'test-app',
        'env' => 'testing',
        'root' => dirname(__DIR__, 3),
    ]);

    // Create a new provider instance
    $this->provider = new EncryptionServiceProvider();
});

test('encryption provider implements provider interface', function () {
    expect($this->provider)->toBeInstanceOf(ProviderInterface::class);
});

test('can register encryption provider', function () {
    // Register the provider
    $this->provider->register($this->app);

    // Verify that validation and definition were set
    expect($this->app->config()->get('validation'))->toHaveKey('app_key');
    expect($this->app->config()->get('validation'))->toHaveKey('app_previous_keys');
    expect($this->app->config()->get('definition'))->toHaveKey('app_key');
    expect($this->app->config()->get('definition'))->toHaveKey('app_previous_keys');

    // Check the default values in definition
    expect($this->app->config()->get('definition')['app_key'][1])->toBe('');
    expect($this->app->config()->get('definition')['app_previous_keys'][1])->toBe([]);
});

test('validates app_key correctly', function () {
    // Register the provider
    $this->provider->register($this->app);

    // Get the validation callback
    $appKeyValidation = $this->app->config()->get('validation')['app_key'];

    // String validation for app_key
    expect($appKeyValidation('valid-key'))->toBe(true);
    expect($appKeyValidation(''))->toBe(true); // Empty is valid in validation, but will throw during instantiation

    // Invalid types
    expect($appKeyValidation(123))->toBeString(); // Should return error message
    expect($appKeyValidation(null))->toBeString();
    expect($appKeyValidation([]))->toBeString();
    expect($appKeyValidation(new stdClass()))->toBeString();
});

test('validates app_previous_keys correctly', function () {
    // Register the provider
    $this->provider->register($this->app);

    // Get the validation callback
    $appPreviousKeysValidation = $this->app->config()->get('validation')['app_previous_keys'];

    // Array validation for app_previous_keys
    expect($appPreviousKeysValidation([]))->toBe(true);
    expect($appPreviousKeysValidation(['old-key-1', 'old-key-2']))->toBe(true);

    // Invalid types
    expect($appPreviousKeysValidation('not-array'))->toBeString(); // Should return error message
    expect($appPreviousKeysValidation(null))->toBeString();
    expect($appPreviousKeysValidation(123))->toBeString();
    expect($appPreviousKeysValidation(new stdClass()))->toBeString();
});

test('binds encrypt service correctly', function () {
    // Register the provider
    $this->provider->register($this->app);

    // Set configuration values
    $this->app->config()->set('app_key', 'test-app-key');
    $this->app->config()->set('app_previous_keys', ['old-key-1', 'old-key-2']);

    // Boot the app to resolve bindings
    $this->app->boot();

    // Check that encrypt service is properly registered
    expect($this->app->has('encrypt'))->toBeTrue();

    // Get the encrypt service
    $encrypt = $this->app->get('encrypt');
    expect($encrypt)->toBeInstanceOf(Encrypt::class);
    expect($encrypt)->toBeInstanceOf(EncryptInterface::class);

    // Test if the encrypt service can be used
    $data = ['test' => 'data'];
    $encrypted = $encrypt->encrypt($data);
    expect($encrypted)->toBeString();
    expect($encrypted)->toContain(':'); // Encrypted data contains the IV separator

    // Test if the data can be decrypted
    $decrypted = $encrypt->decrypt($encrypted);
    expect($decrypted)->toBe($data);
});

test('throws exception with empty app_key during usage', function () {
    // Register the provider
    $this->provider->register($this->app);

    // Set empty app_key
    $this->app->config()->set('app_key', '');

    // Boot the app to resolve bindings
    $this->app->boot();

    // Attempt to get the encrypt service (should throw exception)
    expect(function () {
        $this->app->get('encrypt');
    })->toThrow(\RuntimeException::class, 'The app key must not be empty');
});

test('encrypt service handles previous keys correctly', function () {
    // Register the provider
    $this->provider->register($this->app);

    // Create an encrypt service with a different key to simulate previous encryption
    $oldKey = 'old-app-key';
    $currentKey = 'current-app-key';

    $oldEncrypt = new Encrypt($oldKey);

    // Encrypt data with the old key
    $data = ['secret' => 'value'];
    $encryptedWithOldKey = $oldEncrypt->encrypt($data);

    // Now set up the app with the current key and the old key as previous
    $this->app->config()->set('app_key', $currentKey);
    $this->app->config()->set('app_previous_keys', [$oldKey]);

    // Boot the app to resolve bindings
    $this->app->boot();

    // Get the encrypt service
    $currentEncrypt = $this->app->get('encrypt');

    // The current encrypt service should be able to decrypt data encrypted with the old key
    $decrypted = $currentEncrypt->decrypt($encryptedWithOldKey);
    expect($decrypted)->toBe($data);

    // The needsReEncrypt method should indicate that the data needs re-encryption
    expect($currentEncrypt->needsReEncrypt($encryptedWithOldKey))->toBeTrue();

    // Encrypt data with the current key
    $encryptedWithCurrentKey = $currentEncrypt->encrypt($data);

    // The data encrypted with the current key should not need re-encryption
    expect($currentEncrypt->needsReEncrypt($encryptedWithCurrentKey))->toBeFalse();
});

test('has correct commands registered', function () {
    expect($this->provider->commands)->toBeArray();
    expect($this->provider->commands)->toContain(CreateAppKeyCommand::class);

    // Additional checks to verify the command is properly defined
    $this->provider->register($this->app);
    $this->app->boot();

    // Check if the command class is registered and accessible
    $command = $this->app->get(CreateAppKeyCommand::class);
    expect($command)->toBeInstanceOf(CreateAppKeyCommand::class);
});

test('can boot encryption provider', function () {
    // Boot should be a no-op for this provider
    $this->provider->boot($this->app);

    // Just make sure it doesn't throw exceptions
    expect(true)->toBeTrue();
});

test('can create multiple instances of encrypt service with different keys', function () {
    // Register the provider
    $this->provider->register($this->app);

    // Create first instance with a specific key
    $key1 = 'first-key';
    $this->app->config()->set('app_key', $key1);
    $encrypt1 = new Encrypt($key1);

    // Create second instance with different key
    $key2 = 'second-key';
    $encrypt2 = new Encrypt($key2);

    // They should be different instances
    expect($encrypt1)->not->toBe($encrypt2);

    // Test that they use different keys
    $data = ['test' => 'value'];
    $encrypted1 = $encrypt1->encrypt($data);
    $encrypted2 = $encrypt2->encrypt($data);

    // Encrypted data should be different due to different keys and random IVs
    expect($encrypted1)->not->toBe($encrypted2);

    // Each instance should decrypt its own encrypted data
    expect($encrypt1->decrypt($encrypted1))->toBe($data);
    expect($encrypt2->decrypt($encrypted2))->toBe($data);

    // First instance should not decrypt data from second instance (different keys)
    expect($encrypt1->decrypt($encrypted2))->toBeFalse();
    expect($encrypt2->decrypt($encrypted1))->toBeFalse();
});
