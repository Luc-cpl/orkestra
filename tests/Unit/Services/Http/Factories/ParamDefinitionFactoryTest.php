<?php

namespace Tests\Unit\Services\Http\Factories;

use BadMethodCallException;
use ReflectionProperty;
use Orkestra\Entities\EntityFactory;
use Orkestra\Services\Http\Entities\ParamDefinition;
use Orkestra\Services\Http\Enum\ParamType;
use Orkestra\Services\Http\Factories\ParamDefinitionFactory;

test('factory throws exception for invalid method', function () {
    $entityFactory = app()->get(EntityFactory::class);
    $factory = new ParamDefinitionFactory($entityFactory);

    $factory->invalidType('Invalid', 'invalid');
})->throws(BadMethodCallException::class, 'Invalid method: invalidType');

// Test factory methods with named arguments
test('create parameter factory with dependencies', function () {
    $entityFactory = app()->get(EntityFactory::class);

    $factory = new ParamDefinitionFactory($entityFactory);
    expect($factory)->toBeInstanceOf(ParamDefinitionFactory::class);
});

test('create string parameter', function () {
    $entityFactory = app()->get(EntityFactory::class);
    $factory = new ParamDefinitionFactory($entityFactory);

    $param = $factory->string('My String', 'my_string', 'default value', 'required', 'A string parameter');

    expect($param)->toBeInstanceOf(ParamDefinition::class);
    expect($param->type)->toBe(ParamType::String);
    expect($param->title)->toBe('My String');
    expect($param->name)->toBe('my_string');
    expect($param->default)->toBe('default value');
    expect($param->description)->toBe('A string parameter');
    expect($param->getValidation())->toContain('required');
    expect($param->getRequired())->toBeTrue();
});

test('create int parameter', function () {
    $entityFactory = app()->get(EntityFactory::class);
    $factory = new ParamDefinitionFactory($entityFactory);

    $param = $factory->int('My Integer', 'my_int', 42, 'required|min:0', 'An integer parameter');

    expect($param)->toBeInstanceOf(ParamDefinition::class);
    expect($param->type)->toBe(ParamType::Int);
    expect($param->title)->toBe('My Integer');
    expect($param->name)->toBe('my_int');
    expect($param->default)->toBe(42);
    expect($param->description)->toBe('An integer parameter');
    expect($param->getValidation())->toContain('required');
    expect($param->getValidation())->toContain('min:0');
});

test('create number parameter', function () {
    $entityFactory = app()->get(EntityFactory::class);
    $factory = new ParamDefinitionFactory($entityFactory);

    $param = $factory->number('My Number', 'my_number', 3.14, 'required', 'A number parameter');

    expect($param)->toBeInstanceOf(ParamDefinition::class);
    expect($param->type)->toBe(ParamType::Number);
    expect($param->title)->toBe('My Number');
    expect($param->name)->toBe('my_number');
    expect($param->default)->toBe(3.14);
    expect($param->description)->toBe('A number parameter');
});

test('create boolean parameter', function () {
    $entityFactory = app()->get(EntityFactory::class);
    $factory = new ParamDefinitionFactory($entityFactory);

    $param = $factory->boolean('My Boolean', 'my_boolean', true, '', 'A boolean parameter');

    expect($param)->toBeInstanceOf(ParamDefinition::class);
    expect($param->type)->toBe(ParamType::Boolean);
    expect($param->title)->toBe('My Boolean');
    expect($param->name)->toBe('my_boolean');
    expect($param->default)->toBe(true);
    expect($param->description)->toBe('A boolean parameter');
    expect($param->getRequired())->toBeFalse();
});

test('create array parameter', function () {
    $entityFactory = app()->get(EntityFactory::class);
    $factory = new ParamDefinitionFactory($entityFactory);

    $param = $factory->array('My Array', 'my_array', [], 'required', 'An array parameter');

    expect($param)->toBeInstanceOf(ParamDefinition::class);
    expect($param->type)->toBe(ParamType::Array);
    expect($param->title)->toBe('My Array');
    expect($param->name)->toBe('my_array');
    expect($param->default)->toBe([]);
    expect($param->description)->toBe('An array parameter');
});

test('create object parameter', function () {
    $entityFactory = app()->get(EntityFactory::class);
    $factory = new ParamDefinitionFactory($entityFactory);

    $param = $factory->object('My Object', 'my_object', null, 'required', 'An object parameter');

    expect($param)->toBeInstanceOf(ParamDefinition::class);
    expect($param->type)->toBe(ParamType::Object);
    expect($param->title)->toBe('My Object');
    expect($param->name)->toBe('my_object');
    expect($param->default)->toBeNull();
    expect($param->description)->toBe('An object parameter');
});

test('create parameter with array validation', function () {
    $entityFactory = app()->get(EntityFactory::class);
    $factory = new ParamDefinitionFactory($entityFactory);

    $param = $factory->string('My String', 'my_string', 'default', ['required', 'min:3'], 'String with array validation');

    expect($param)->toBeInstanceOf(ParamDefinition::class);
    expect($param->getValidation())->toContain('required');
    expect($param->getValidation())->toContain('min:3');
});

test('create parameter with enum values', function () {
    $entityFactory = app()->get(EntityFactory::class);
    $factory = new ParamDefinitionFactory($entityFactory);

    // Create a parameter with enum values
    $param = $factory->string('Status', 'status', 'active', 'required', 'Status field', ['active', 'inactive', 'pending']);

    expect($param)->toBeInstanceOf(ParamDefinition::class);

    // Need to access protected property
    $reflectionProperty = new ReflectionProperty(ParamDefinition::class, 'enum');
    $reflectionProperty->setAccessible(true);
    $enum = $reflectionProperty->getValue($param);

    expect($enum)->toBe(['active', 'inactive', 'pending']);

    // The validation rules should include the enum values
    expect($param->getValidation())->toContain('required');
    expect($param->getValidation())->toContain('in:active,inactive,pending');
});

test('create parameter with inner parameters', function () {
    $entityFactory = app()->get(EntityFactory::class);
    $factory = new ParamDefinitionFactory($entityFactory);

    // Create inner parameter
    $nameParam = $factory->string('Name', 'name', '', 'required', 'User name');
    $ageParam = $factory->int('Age', 'age', null, 'required|min:18', 'User age');

    // Create object parameter with inner parameters
    $param = $factory->object('User', 'user', null, 'required', 'User object', [], [$nameParam, $ageParam]);

    expect($param)->toBeInstanceOf(ParamDefinition::class);
    expect($param->type)->toBe(ParamType::Object);

    // Need to access protected property
    $reflectionProperty = new ReflectionProperty(ParamDefinition::class, 'inner');
    $reflectionProperty->setAccessible(true);
    $innerParams = $reflectionProperty->getValue($param);

    expect($innerParams)->toBeArray();
    expect($innerParams)->toHaveCount(2);
    expect($innerParams[0])->toBe($nameParam);
    expect($innerParams[1])->toBe($ageParam);
});

test('factory can convert method names to proper types', function () {
    $entityFactory = app()->get(EntityFactory::class);
    $factory = new ParamDefinitionFactory($entityFactory);

    $stringParam = $factory->string('String', 'string', 'value', 'required', 'String description');
    $intParam = $factory->int('Int', 'int', 42, 'required', 'Int description');
    $numberParam = $factory->number('Number', 'number', 3.14, 'required', 'Number description');
    $booleanParam = $factory->boolean('Boolean', 'boolean', true, 'required', 'Boolean description');
    $arrayParam = $factory->array('Array', 'array', [], 'required', 'Array description');
    $objectParam = $factory->object('Object', 'object', null, 'required', 'Object description');

    expect($stringParam->type)->toBe(ParamType::String);
    expect($intParam->type)->toBe(ParamType::Int);
    expect($numberParam->type)->toBe(ParamType::Number);
    expect($booleanParam->type)->toBe(ParamType::Boolean);
    expect($arrayParam->type)->toBe(ParamType::Array);
    expect($objectParam->type)->toBe(ParamType::Object);
});
