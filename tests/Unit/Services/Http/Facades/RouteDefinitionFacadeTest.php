<?php

use Orkestra\Services\Http\Facades\RouteDefinitionFacade;
use Orkestra\Services\Http\Factories\ParamDefinitionFactory;
use Orkestra\Services\Http\Interfaces\DefinitionInterface;
use Orkestra\Services\Http\Entities\ParamDefinition;

test('RouteDefinitionFacade delegates title method to the definition', function () {
    // Create mock ParamDefinitionFactory
    $factory = Mockery::mock(ParamDefinitionFactory::class);

    // Create mock DefinitionInterface
    $definition = Mockery::mock(DefinitionInterface::class);
    $definition->shouldReceive('title')
        ->once()
        ->andReturn('Test Title');

    // Create the facade
    $facade = new RouteDefinitionFacade($factory, $definition);

    // Call the method and check that it returns the value from the definition
    expect($facade->title())->toBe('Test Title');
});

test('RouteDefinitionFacade delegates description method to the definition', function () {
    // Create mock ParamDefinitionFactory
    $factory = Mockery::mock(ParamDefinitionFactory::class);

    // Create mock DefinitionInterface
    $definition = Mockery::mock(DefinitionInterface::class);
    $definition->shouldReceive('description')
        ->once()
        ->andReturn('Test Description');

    // Create the facade
    $facade = new RouteDefinitionFacade($factory, $definition);

    // Call the method and check that it returns the value from the definition
    expect($facade->description())->toBe('Test Description');
});

test('RouteDefinitionFacade delegates type method to the definition', function () {
    // Create mock ParamDefinitionFactory
    $factory = Mockery::mock(ParamDefinitionFactory::class);

    // Create mock DefinitionInterface
    $definition = Mockery::mock(DefinitionInterface::class);
    $definition->shouldReceive('type')
        ->once()
        ->andReturn('json');

    // Create the facade
    $facade = new RouteDefinitionFacade($factory, $definition);

    // Call the method and check that it returns the value from the definition
    expect($facade->type())->toBe('json');
});

test('RouteDefinitionFacade delegates meta method to the definition', function () {
    // Create mock ParamDefinitionFactory
    $factory = Mockery::mock(ParamDefinitionFactory::class);

    // Create mock DefinitionInterface
    $definition = Mockery::mock(DefinitionInterface::class);
    $definition->shouldReceive('meta')
        ->once()
        ->with('key', 'default')
        ->andReturn('meta value');

    // Create the facade
    $facade = new RouteDefinitionFacade($factory, $definition);

    // Call the method and check that it returns the value from the definition
    expect($facade->meta('key', 'default'))->toBe('meta value');
});

test('RouteDefinitionFacade delegates meta method with default value', function () {
    // Create mock ParamDefinitionFactory
    $factory = Mockery::mock(ParamDefinitionFactory::class);

    // Create mock DefinitionInterface
    $definition = Mockery::mock(DefinitionInterface::class);
    $definition->shouldReceive('meta')
        ->once()
        ->with('nonexistent-key', null)
        ->andReturn(null);

    // Create the facade
    $facade = new RouteDefinitionFacade($factory, $definition);

    // Call the method with a key that doesn't exist
    expect($facade->meta('nonexistent-key'))->toBeNull();
});

test('RouteDefinitionFacade delegates params method to the definition', function () {
    // Create mock ParamDefinitionFactory
    $factory = Mockery::mock(ParamDefinitionFactory::class);

    // Create mock ParamDefinition
    $param1 = Mockery::mock(ParamDefinition::class);
    $param2 = Mockery::mock(ParamDefinition::class);

    // Create mock DefinitionInterface
    $definition = Mockery::mock(DefinitionInterface::class);
    $definition->shouldReceive('params')
        ->once()
        ->with($factory)
        ->andReturn([$param1, $param2]);

    // Create the facade
    $facade = new RouteDefinitionFacade($factory, $definition);

    // Call the method and check that it returns the value from the definition
    $params = $facade->params();
    expect($params)->toBeArray();
    expect($params)->toHaveCount(2);
    expect($params[0])->toBe($param1);
    expect($params[1])->toBe($param2);
});
