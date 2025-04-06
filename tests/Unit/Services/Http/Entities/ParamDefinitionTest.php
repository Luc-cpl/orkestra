<?php

namespace Tests\Unit\Services\Http\Entities;

use BadMethodCallException;
use InvalidArgumentException;
use Orkestra\Services\Http\Entities\ParamDefinition;
use Orkestra\Services\Http\Enum\ParamType;

test('can create a parameter definition with basic properties', function () {
    $param = new ParamDefinition(
        type: ParamType::String,
        title: 'Test Parameter',
        name: 'test_param',
        default: 'default value',
        description: 'A test parameter'
    );

    expect($param->type)->toBe(ParamType::String);
    expect($param->title)->toBe('Test Parameter');
    expect($param->name)->toBe('test_param');
    expect($param->default)->toBe('default value');
    expect($param->description)->toBe('A test parameter');
});

test('can set and get validation rules as string', function () {
    $param = new ParamDefinition(
        type: ParamType::String,
        title: 'Test Parameter',
        name: 'test_param'
    );

    $param->setValidation('required|min:3|max:100');

    expect($param->getValidation())->toBe(['required', 'min:3', 'max:100']);
    expect($param->getRequired())->toBeTrue();
});

test('can set and get validation rules as array', function () {
    $param = new ParamDefinition(
        type: ParamType::String,
        title: 'Test Parameter',
        name: 'test_param'
    );

    $param->setValidation(['required', 'min:3', 'max:100']);

    expect($param->getValidation())->toBe(['required', 'min:3', 'max:100']);
    expect($param->getRequired())->toBeTrue();
});

test('can add validation rules individually', function () {
    $param = new ParamDefinition(
        type: ParamType::String,
        title: 'Test Parameter',
        name: 'test_param'
    );

    // Test lines 64-65 specifically
    $result = $param->addValidation('required');

    // Test fluent interface returns the object itself
    expect($result)->toBe($param);

    // Add additional validation rules
    $param->addValidation('min:3')
         ->addValidation('max:100');

    expect($param->getValidation())->toBe(['required', 'min:3', 'max:100']);
    expect($param->getRequired())->toBeTrue();
});

test('non-required parameters are detected correctly', function () {
    $param = new ParamDefinition(
        type: ParamType::String,
        title: 'Test Parameter',
        name: 'test_param'
    );

    $param->setValidation(['min:3', 'max:100']);

    expect($param->getRequired())->toBeFalse();
});

test('can set and get enum values from array', function () {
    $param = new ParamDefinition(
        type: ParamType::String,
        title: 'Status',
        name: 'status'
    );

    $enum = ['active', 'inactive', 'pending'];
    $param->setEnum($enum);

    // Check validation includes enum
    expect($param->getValidation())->toContain('in:active,inactive,pending');
});

test('enum values are included in validation rules', function () {
    $param = new ParamDefinition(
        type: ParamType::String,
        title: 'Status',
        name: 'status'
    );

    $param->setValidation('required');
    $param->setEnum(['active', 'inactive', 'pending']);

    // Make sure both the required validation and enum are included
    $validation = $param->getValidation();
    expect($validation)->toContain('required');
    expect($validation)->toContain('in:active,inactive,pending');
});

test('setting null enum values returns self without changes', function () {
    $param = new ParamDefinition(
        type: ParamType::String,
        title: 'Test Parameter',
        name: 'test_param'
    );

    $result = $param->setEnum(null);

    expect($result)->toBe($param);
    expect($param->getValidation())->toBe([]);
});

test('throws exception for invalid enum class', function () {
    $param = new ParamDefinition(
        type: ParamType::String,
        title: 'Test Parameter',
        name: 'test_param'
    );

    // Try to set a non-existent enum class
    $param->setEnum('NonExistentEnum');
})->throws(InvalidArgumentException::class, 'Invalid enum class: NonExistentEnum');

test('can set inner parameters for array type', function () {
    $param = new ParamDefinition(
        type: ParamType::Array,
        title: 'Items',
        name: 'items'
    );

    $innerParam = new ParamDefinition(
        type: ParamType::String,
        title: 'Item',
        name: 'item'
    );

    $result = $param->setInner([$innerParam]);

    expect($result)->toBe($param);

    // Use reflection to check inner parameters
    $reflectionProperty = new \ReflectionProperty(ParamDefinition::class, 'inner');
    $reflectionProperty->setAccessible(true);
    $innerParams = $reflectionProperty->getValue($param);

    expect($innerParams)->toHaveCount(1);
    expect($innerParams[0])->toBe($innerParam);
});

test('can set inner parameters for object type', function () {
    $param = new ParamDefinition(
        type: ParamType::Object,
        title: 'User',
        name: 'user'
    );

    $nameParam = new ParamDefinition(
        type: ParamType::String,
        title: 'Name',
        name: 'name'
    );

    $emailParam = new ParamDefinition(
        type: ParamType::String,
        title: 'Email',
        name: 'email'
    );

    $param->setInner([$nameParam, $emailParam]);

    // Use reflection to check inner parameters
    $reflectionProperty = new \ReflectionProperty(ParamDefinition::class, 'inner');
    $reflectionProperty->setAccessible(true);
    $innerParams = $reflectionProperty->getValue($param);

    expect($innerParams)->toHaveCount(2);
    expect($innerParams[0])->toBe($nameParam);
    expect($innerParams[1])->toBe($emailParam);
});

test('setting null inner parameters returns self without changes', function () {
    $param = new ParamDefinition(
        type: ParamType::Array,
        title: 'Items',
        name: 'items'
    );

    $result = $param->setInner(null);

    expect($result)->toBe($param);

    // Use reflection to check inner parameters are still empty
    $reflectionProperty = new \ReflectionProperty(ParamDefinition::class, 'inner');
    $reflectionProperty->setAccessible(true);
    $innerParams = $reflectionProperty->getValue($param);

    expect($innerParams)->toBeArray();
    expect($innerParams)->toBeEmpty();
});

test('throws exception when setting inner parameters on non-array and non-object types', function () {
    $param = new ParamDefinition(
        type: ParamType::String,
        title: 'Test Parameter',
        name: 'test_param'
    );

    $innerParam = new ParamDefinition(
        type: ParamType::String,
        title: 'Inner',
        name: 'inner'
    );

    // Try to set inner parameters on a string type
    $param->setInner([$innerParam]);
})->throws(BadMethodCallException::class, 'Cannot set inner on non-array or non-object param');

test('can set enum values from PHP enum class', function () {
    $param = new ParamDefinition(
        type: ParamType::String,
        title: 'Status',
        name: 'status'
    );

    // Set enum using the TestStatus enum class (this should test line 94)
    $param->setEnum(TestStatus::class);

    // Check validation includes enum values from the enum class
    $validation = $param->getValidation();
    expect($validation)->toContain('in:active,inactive,pending');

    // Use reflection to check that the enum values were extracted
    $reflectionProperty = new \ReflectionProperty(ParamDefinition::class, 'enum');
    $reflectionProperty->setAccessible(true);
    $enumValues = $reflectionProperty->getValue($param);

    expect($enumValues)->toBe(['active', 'inactive', 'pending']);
});
