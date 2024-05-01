<?php

use Orkestra\Providers\HttpProvider;
use Orkestra\Services\Http\Enum\ParamType;
use Orkestra\Services\Http\Facades\RouteDefinitionFacade;
use Orkestra\Services\Http\Factories\ParamDefinitionFactory;
use Orkestra\Services\Http\RouteDefinition;

beforeEach(function () {
    app()->provider(HttpProvider::class);
});

test('can instantiate a RouteDefinition', function () {
    $routeDefinition = new RouteDefinition();
    expect($routeDefinition)->toBeInstanceOf(RouteDefinition::class);

    $parent = new RouteDefinition();
    $parentFacade = new RouteDefinitionFacade(app()->get(ParamDefinitionFactory::class), $parent);
    $routeDefinition = new RouteDefinition(parentDefinition: $parentFacade);
    expect($routeDefinition)->toBeInstanceOf(RouteDefinition::class);
});

test('can sets the route definition title correctly', function () {
    $routeDefinition = new RouteDefinition(title: 'title');
    expect($routeDefinition->title())->toBe('title');

    $parent = new RouteDefinition(title: 'parent');
    $parentFacade = new RouteDefinitionFacade(app()->get(ParamDefinitionFactory::class), $parent);
    $routeDefinition = new RouteDefinition(parentDefinition: $parentFacade);
    expect($routeDefinition->title())->toBe('parent');

    $routeDefinition = new RouteDefinition();
    expect($routeDefinition->title())->toBe('');

    $parent = new RouteDefinition();
    $parentFacade = new RouteDefinitionFacade(app()->get(ParamDefinitionFactory::class), $parent);
    $routeDefinition = new RouteDefinition(parentDefinition: $parentFacade);
    expect($routeDefinition->title())->toBe('');
});

test('can sets the route definition description correctly', function () {
    $routeDefinition = new RouteDefinition(description: 'description');
    expect($routeDefinition->description())->toBe('description');

    $parent = new RouteDefinition(description: 'parent');
    $parentFacade = new RouteDefinitionFacade(app()->get(ParamDefinitionFactory::class), $parent);
    $routeDefinition = new RouteDefinition(parentDefinition: $parentFacade);
    expect($routeDefinition->description())->toBe('parent');

    $routeDefinition = new RouteDefinition();
    expect($routeDefinition->description())->toBe('');

    $parent = new RouteDefinition();
    $parentFacade = new RouteDefinitionFacade(app()->get(ParamDefinitionFactory::class), $parent);
    $routeDefinition = new RouteDefinition(parentDefinition: $parentFacade);
    expect($routeDefinition->description())->toBe('');
});

test('can sets the route definition type correctly', function () {
    $routeDefinition = new RouteDefinition(type: 'type');
    expect($routeDefinition->type())->toBe('type');

    $parent = new RouteDefinition(type: 'parent');
    $parentFacade = new RouteDefinitionFacade(app()->get(ParamDefinitionFactory::class), $parent);
    $routeDefinition = new RouteDefinition(parentDefinition: $parentFacade);
    expect($routeDefinition->type())->toBe('parent');

    $routeDefinition = new RouteDefinition();
    expect($routeDefinition->type())->toBe('');

    $parent = new RouteDefinition();
    $parentFacade = new RouteDefinitionFacade(app()->get(ParamDefinitionFactory::class), $parent);
    $routeDefinition = new RouteDefinition(parentDefinition: $parentFacade);
    expect($routeDefinition->type())->toBe('');
});

test('can sets the route definition meta correctly', function () {
    $routeDefinition = new RouteDefinition(meta: ['key' => 'value']);
    expect($routeDefinition->meta('key'))->toBe('value');
    expect($routeDefinition->meta('key2'))->toBeNull();

    $parent = new RouteDefinition(meta: ['key' => 'value']);
    $parentFacade = new RouteDefinitionFacade(app()->get(ParamDefinitionFactory::class), $parent);
    $routeDefinition = new RouteDefinition(parentDefinition: $parentFacade);
    expect($routeDefinition->meta('key'))->toBeNull();
});

test('can sets the route definition params correctly', function () {
    $factory = app()->get(ParamDefinitionFactory::class);

    $routeDefinition = new RouteDefinition(params: [
        'query' => [
            'type' => 'string',
            'title' => 'Query',
            'description' => 'Query description',
            'validation' => 'required|min:3|max:255',
            'default' => 'test',
            'inner' => null,
            'enum' => null,
        ],
    ]);
    expect($routeDefinition->params($factory)[0]->title)->toBe('Query');
    expect($routeDefinition->params($factory)[0]->description)->toBe('Query description');
    expect($routeDefinition->params($factory)[0]->validation)->toBe(['required', 'min:3', 'max:255']);
    expect($routeDefinition->params($factory)[0]->default)->toBe('test');
    expect($routeDefinition->params($factory)[0]->inner)->toBe([]);
    expect($routeDefinition->params($factory)[0]->enum)->toBe([]);

    $parent = new RouteDefinition(params: [
        'query' => [],
    ]);
    $parentFacade = new RouteDefinitionFacade($factory, $parent);
    $routeDefinition = new RouteDefinition(parentDefinition: $parentFacade);
    expect($routeDefinition->params($factory))->toBe([]);

    $routeDefinition = new RouteDefinition(params: [
        'query' => [
            'inner' => [
                'key' => [],
            ],
        ],
    ]);
    expect(fn () => $routeDefinition->params($factory))->toThrow(BadMethodCallException::class);

    $routeDefinition = new RouteDefinition(params: [
        'query' => [
            'type' => 'array',
            'inner' => [
                'key' => [
                    'type' => 'string',
                ],
            ],
        ],
    ]);
    expect($routeDefinition->params($factory)[0]->inner[0]->type)->toBe(ParamType::String);

    $routeDefinition = new RouteDefinition(params: [
        'query' => [
            'type' => 'object',
            'inner' => [
                'key' => [
                    'type' => 'string',
                ],
            ],
        ],
    ]);
    expect($routeDefinition->params($factory)[0]->inner[0]->type)->toBe(ParamType::String);
});

test('can set required params', function () {
    $factory = app()->get(ParamDefinitionFactory::class);

    $routeDefinition = new RouteDefinition(params: [
        'query' => [
            'type' => 'string',
            'validation' => 'required|min:3|max:255',
        ],
    ]);
    expect($routeDefinition->params($factory)[0]->required)->toBeTrue();

    $routeDefinition = new RouteDefinition(params: [
        'query' => [
            'type' => 'string',
            'validation' => 'min:3|max:255',
        ],
    ]);
    expect($routeDefinition->params($factory)[0]->required)->toBeFalse();
});
