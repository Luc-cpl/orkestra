<?php

use Orkestra\Configuration;

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
            'key1' => fn($value) => $value === 'default1',
            'key2' => fn($value) => $value === 'default2',
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
            'key' => fn($value) => $value === 'validValue',
        ],
    ]);
	$config->validate();
})->throws(InvalidArgumentException::class);

test('can throw exception when setting invalid validation', function () {
    $config = new Configuration([]);
	$config->set('validation', 'invalidValue');
})->throws(InvalidArgumentException::class);

test('can throw exception when setting invalid definition', function () {
    $config = new Configuration([]);
	$config->set('definition', 'invalidValue');
})->throws(InvalidArgumentException::class);

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