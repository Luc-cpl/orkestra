<?php

use Orkestra\Configuration;

covers(Configuration::class);

test('can instantiate the Configuration class', function () {
    $config = new Configuration([]);
    expect($config)->toBeInstanceOf(Configuration::class);
});

test('can validate configuration', function () {
    $config = new Configuration([
        'definition' => [
            'key1' => ['description1', 'default1'],
            'key2' => ['description2', 'default2'],
        ],
        'validation' => [
            'key1' => fn ($value) => $value === 'default1',
            'key2' => fn ($value) => $value === 'default2',
        ],
    ]);

    expect($config->validate())->toBeTrue();
});

test('can set and get configuration', function () {
    $config = new Configuration([]);
    $config->set('key', 'value');

    expect($config->get('key'))->toBe('value');
});

test('can check if configuration exists', function () {
    $config = new Configuration(['key' => 'value']);

    expect($config->has('key'))->toBeTrue();
    expect($config->has('nonexistent_key'))->toBeFalse();
});


test('can throw exception when validating undefined configuration key', function () {
    $config = new Configuration([
        'undefinedKey' => 'value',
        'definition' => [],
        'validation' => [],
    ]);
    $config->validate();
})->throws(InvalidArgumentException::class);

test('can throw exception when required configuration key is missing', function () {
    $config = new Configuration([
        'definition' => [
            'requiredKey' => ['description', null],
        ],
        'validation' => [],
    ]);
    $config->validate();
})->throws(InvalidArgumentException::class);

test('can throw exception when configuration does not pass validation', function () {
    $config = new Configuration([
        'key' => 'invalidValue',
        'definition' => [
            'key' => ['description', 'validValue'],
        ],
        'validation' => [
            'key' => fn ($value) => $value === 'validValue',
        ],
    ]);
    $config->validate();
})->throws(InvalidArgumentException::class);

test('can throw exception with custom message when validation returns a string', function () {
    $config = new Configuration([
        'key' => 'invalidValue',
        'definition' => [
            'key' => ['description', 'validValue'],
        ],
        'validation' => [
            'key' => fn ($value) => 'Custom error message for invalid value',
        ],
    ]);

    expect(fn () => $config->validate())->toThrow(
        InvalidArgumentException::class,
        'Invalid configuration for "key": Custom error message for invalid value'
    );
});

test('can throw exception when setting invalid validation', function ($validator) {
    $config = new Configuration([]);
    $config->set('validation', $validator);
})->with([
    ['invalidValidator'],
    [['key' => 'invalidValidator']],
    [[fn () => true]],
])->throws(InvalidArgumentException::class);

test('can throw exception when setting invalid definition', function ($definition) {
    $config = new Configuration([]);
    $config->set('definition', $definition);
})->with([
    ['invalidDefinition'],
    [['key' => []]],
    [['key' => ['description', 'default', 'invalid value']]],
    [[['description', 'default']]],
])->throws(InvalidArgumentException::class);

test('can throw exception when getting undefined configuration key', function () {
    $config = new Configuration([]);
    $config->get('undefinedKey');
})->throws(InvalidArgumentException::class);

test('can throw exception when getting required configuration key that is not set', function () {
    $config = new Configuration([
        'definition' => [
            'requiredKey' => ['description', null],
        ],
    ]);
    $config->get('requiredKey');
})->throws(InvalidArgumentException::class);

test('can use callable as default value in definition', function () {
    $config = new Configuration([
        'definition' => [
            'dynamicKey' => ['A key with a dynamic default value', fn () => 'dynamic-value'],
        ],
    ]);

    expect($config->get('dynamicKey'))->toBe('dynamic-value');
});

test('can get callable configuration values', function () {
    $config = new Configuration([
        'callableKey' => fn () => 'result-from-callable',
    ]);

    expect($config->get('callableKey'))->toBe('result-from-callable');
});

test('validation can merge with existing validations', function () {
    $config = new Configuration([
        'validation' => [
            'existingKey' => fn ($value) => $value === 'valid',
        ],
        'definition' => [
            'existingKey' => ['Existing key description', 'default'],
            'newKey' => ['New key description', 'default'],
        ],
    ]);

    $config->set('validation', [
        'newKey' => fn ($value) => $value === 'valid',
    ]);

    $config->set('existingKey', 'valid');
    $config->set('newKey', 'valid');

    expect($config->validate())->toBeTrue();
});

test('definition can merge with existing definitions', function () {
    $config = new Configuration([
        'definition' => [
            'existingKey' => ['Existing description', 'default'],
        ],
    ]);

    $config->set('definition', [
        'newKey' => ['New description', 'default'],
    ]);

    expect($config->get('existingKey'))->toBe('default');
    expect($config->get('newKey'))->toBe('default');
});
