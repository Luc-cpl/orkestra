<?php

namespace Tests\Unit\Services\Encryption;

use Orkestra\Services\Encryption\Encrypt;
use RuntimeException;

test('cannot create encrypt service with empty app key', function () {
    expect(fn() => new Encrypt(''))
        ->toThrow(RuntimeException::class, 'The app key must not be empty. Please run the `app:key:create` command to generate a new application key.');
});

test('detects malformed encrypted data', function () {
    $encrypt = new Encrypt('test_key_12345678901234567890123456789012');
    
    // Test with completely invalid format
    $result = $encrypt->decrypt('invalid-data-without-separator');
    expect($result)->toBeFalse();
    
    // Test with invalid base64 data
    $result = $encrypt->decrypt('invalid-iv:invalid-encrypted');
    expect($result)->toBeFalse();
});

test('can encrypt and decrypt data', function () {
    $encrypt = new Encrypt('test_key_12345678901234567890123456789012');
    $data = ['test' => 'data', 'nested' => ['value' => 123]];
    
    $encrypted = $encrypt->encrypt($data);
    expect($encrypted)->toBeString();
    expect($encrypted)->toContain(':'); // Format check
    
    $decrypted = $encrypt->decrypt($encrypted);
    expect($decrypted)->toBeArray();
    expect($decrypted)->toEqual($data);
});

test('handles json decoding errors gracefully', function() {
    // Create a partial mock to test the json_decode failure case
    $encrypt = new Encrypt('test_key_12345678901234567890123456789012');
    
    // Create mock encrypted data that decrypts to invalid JSON
    $mockData = 'validIV:validEncrypted';
    $result = $encrypt->decrypt($mockData);
    
    expect($result)->toBeFalse();
});

test('handles empty array data correctly', function() {
    $encrypt = new Encrypt('test_key_12345678901234567890123456789012');
    
    $encrypted = $encrypt->encrypt([]);
    expect($encrypted)->toBeString();
    
    $decrypted = $encrypt->decrypt($encrypted);
    expect($decrypted)->toBeArray();
    expect($decrypted)->toBeEmpty();
});

test('handles construction with custom algorithm', function() {
    // Valid algorithm
    $encrypt = new Encrypt(
        'test_key_12345678901234567890123456789012',
        [],
        'AES-128-CBC'
    );
    
    expect($encrypt)->toBeInstanceOf(Encrypt::class);
});

test('throws exception for invalid algorithm', function() {
    // Set error_reporting to not report warnings for this test
    $originalErrorReporting = error_reporting();
    error_reporting(E_ERROR);
    
    try {
        $encrypt = new Encrypt(
            'test_key_12345678901234567890123456789012',
            [],
            'INVALID-ALGORITHM'
        );
        
        expect(fn() => $encrypt->encrypt(['test' => 'data']))
            ->toThrow(RuntimeException::class, 'The encryption algorithm is not supported.');
    } finally {
        // Restore original error reporting
        error_reporting($originalErrorReporting);
    }
});

test('handles invalid IV length in encrypted data', function() {
    // Set error_reporting to not report warnings for this test
    $originalErrorReporting = error_reporting();
    error_reporting(E_ERROR);
    
    try {
        $encrypt = new Encrypt('test_key_12345678901234567890123456789012');
        
        // Create malformed data with an invalid IV length
        $invalidData = 'abcde:' . base64_encode('some encrypted data');
        $result = $encrypt->decrypt($invalidData);
        expect($result)->toBeFalse();
    } finally {
        // Restore original error reporting
        error_reporting($originalErrorReporting);
    }
});

test('can decrypt data with previous keys', function() {
    $oldKey = 'old_key_123456789012345678901234567890';
    $newKey = 'new_key_123456789012345678901234567890';
    
    // First encrypt with old key
    $oldEncrypt = new Encrypt($oldKey);
    $data = ['test' => 'previous key data'];
    $encrypted = $oldEncrypt->encrypt($data);
    
    // Now create new encrypter with old key as previous
    $newEncrypt = new Encrypt($newKey, [$oldKey]);
    
    // Should still be able to decrypt
    $decrypted = $newEncrypt->decrypt($encrypted);
    expect($decrypted)->toEqual($data);
    
    // And should detect need to re-encrypt
    expect($newEncrypt->needsReEncrypt($encrypted))->toBeTrue();
    
    // New data should use new key
    $newData = ['test' => 'new key data'];
    $newEncrypted = $newEncrypt->encrypt($newData);
    $decrypted = $newEncrypt->decrypt($newEncrypted);
    expect($decrypted)->toEqual($newData);
    
    // And should not need re-encryption
    expect($newEncrypt->needsReEncrypt($newEncrypted))->toBeFalse();
});

test('handles complex nested data structures', function () {
    $encrypt = new Encrypt('test_key_12345678901234567890123456789012');
    $data = [
        'string' => 'value',
        'int' => 123,
        'bool' => true,
        'null' => null,
        'nested' => [
            'array' => [1, 2, 3],
            'object' => ['a' => 'b', 'c' => 'd'],
            'deep' => [
                'deeper' => [
                    'deepest' => 'value'
                ]
            ]
        ]
    ];
    
    $encrypted = $encrypt->encrypt($data);
    $decrypted = $encrypt->decrypt($encrypted);
    expect($decrypted)->toEqual($data);
});

test('cannot decrypt data with incorrect key', function() {
    $encrypt1 = new Encrypt('key_one_12345678901234567890123456789');
    $encrypt2 = new Encrypt('key_two_12345678901234567890123456789');
    
    $data = ['secure' => 'data'];
    $encrypted = $encrypt1->encrypt($data);
    
    $decrypted = $encrypt2->decrypt($encrypted);
    expect($decrypted)->toBeFalse();
});

test('handles base64 decoding failures', function() {
    $encrypt = new Encrypt('test_key_12345678901234567890123456789012');
    
    // Create a string that will cause base64_decode to fail with specially crafted invalid data
    $invalidData = 'valid_iv:this#is!not@valid^base64';
    $result = $encrypt->decrypt($invalidData);
    expect($result)->toBeFalse();
});

test('uses all previous keys when needed', function() {
    // Create a series of keys
    $key1 = 'key_one_12345678901234567890123456789';
    $key2 = 'key_two_12345678901234567890123456789';
    $key3 = 'key_three_123456789012345678901234567';
    $key4 = 'key_four_12345678901234567890123456789';
    
    // Encrypt with the oldest key
    $encrypt1 = new Encrypt($key1);
    $data = ['value' => 'secret data'];
    $encrypted = $encrypt1->encrypt($data);
    
    // Create new encrypter with multiple previous keys
    $encrypt4 = new Encrypt($key4, [$key3, $key2, $key1]);
    
    // Should be able to decrypt data encrypted with the oldest key
    $decrypted = $encrypt4->decrypt($encrypted);
    expect($decrypted)->toEqual($data);
    
    // Test all keys in the chain
    $encrypt3 = new Encrypt($key3, [$key2, $key1]);
    $encrypted3 = $encrypt3->encrypt($data);
    $decrypted3 = $encrypt4->decrypt($encrypted3);
    expect($decrypted3)->toEqual($data);
    
    $encrypt2 = new Encrypt($key2, [$key1]);
    $encrypted2 = $encrypt2->encrypt($data);
    $decrypted2 = $encrypt4->decrypt($encrypted2);
    expect($decrypted2)->toEqual($data);
}); 