<?php

use Orkestra\Providers\HttpProvider;
use Orkestra\Services\Http\Attributes\Entity;
use Orkestra\Services\Http\Attributes\Param;
use Orkestra\Services\Http\Enum\ParamType;
use Orkestra\Services\Http\Facades\RouteDefinitionFacade;
use Orkestra\Services\Http\Factories\ParamDefinitionFactory;
use Orkestra\Services\Http\Interfaces\RouterInterface;
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

test('can set the route definition title correctly', function () {
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

test('can set the route definition description correctly', function () {
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

test('can set the route definition type correctly', function () {
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

test('can set the route definition meta correctly', function () {
    $routeDefinition = new RouteDefinition(meta: ['key' => 'value']);
    expect($routeDefinition->meta('key'))->toBe('value');
    expect($routeDefinition->meta('key2'))->toBeNull();

    $parent = new RouteDefinition(meta: ['key' => 'value']);
    $parentFacade = new RouteDefinitionFacade(app()->get(ParamDefinitionFactory::class), $parent);
    $routeDefinition = new RouteDefinition(parentDefinition: $parentFacade);
    expect($routeDefinition->meta('key'))->toBeNull();
});

test('can set the route definition params correctly', function () {
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

test('can set params from PHP attributes', function () {
    #[Param('entity_value_1', type: 'string', validation: 'required|min:3|max:255')]
    #[Param('entity_value_2', type: 'string', validation: 'required|min:3|max:255')]
    class AttributesTestEntity1
    {
        #[Param('entity_value_3', type: 'string', validation: 'required|min:3|max:255')]
        public string $entity_value_3;
    }

    #[Param('class_param', type: 'string', validation: 'required|min:3|max:255')]
    class AttributesTestController1
    {
        #[Param('title', type: 'string', validation: 'required|min:3|max:255')]
        #[Param('content', type: 'string', validation: 'required|min:3|max:255')]
        #[Param('entity', type: AttributesTestEntity1::class)]
        public function __invoke()
        {
            //
        }
    }

    $router = app()->get(RouterInterface::class);
    $router->map('POST', '/', AttributesTestController1::class);

    $routeDefinition = $router->getRoutes()[0]->getDefinition();
    $factory = app()->get(ParamDefinitionFactory::class);
    expect($routeDefinition->params($factory)[0]->name)->toBe('class_param');
    expect($routeDefinition->params($factory)[0]->required)->toBeTrue();
    expect($routeDefinition->params($factory)[1]->name)->toBe('title');
    expect($routeDefinition->params($factory)[1]->required)->toBeTrue();
    expect($routeDefinition->params($factory)[2]->name)->toBe('content');
    expect($routeDefinition->params($factory)[2]->required)->toBeTrue();
    expect($routeDefinition->params($factory)[3]->name)->toBe('entity');
    expect($routeDefinition->params($factory)[3]->required)->toBeFalse();
    expect($routeDefinition->params($factory)[3]->inner[0]->name)->toBe('entity_value_1');
    expect($routeDefinition->params($factory)[3]->inner[1]->name)->toBe('entity_value_2');
    expect($routeDefinition->params($factory)[3]->inner[2]->name)->toBe('entity_value_3');
});

test('can set entity from PHP attribute in class', function () {
    #[Param('entity_value_1', type: 'string', validation: 'required|min:3|max:255')]
    #[Param('entity_value_2', type: 'string', validation: 'required|min:3|max:255')]
    class AttributesTestEntity2
    {
        #[Param('entity_value_3', type: 'string', validation: 'required|min:3|max:255')]
        public string $entity_value_3;
    }

    #[Entity(AttributesTestEntity2::class)]
    class AttributesTestController2
    {
        public function __invoke()
        {
            //
        }
    }

    $router = app()->get(RouterInterface::class);
    $router->map('POST', '/', AttributesTestController2::class);

    $routeDefinition = $router->getRoutes()[0]->getDefinition();
    $factory = app()->get(ParamDefinitionFactory::class);
    expect($routeDefinition->params($factory)[0]->name)->toBe('entity_value_1');
    expect($routeDefinition->params($factory)[0]->required)->toBeTrue();
    expect($routeDefinition->params($factory)[1]->name)->toBe('entity_value_2');
    expect($routeDefinition->params($factory)[1]->required)->toBeTrue();
    expect($routeDefinition->params($factory)[2]->name)->toBe('entity_value_3');
    expect($routeDefinition->params($factory)[2]->required)->toBeTrue();
});

test('can set entity from PHP attribute in method', function () {
    #[Param('entity_value_1', type: 'string', validation: 'required|min:3|max:255')]
    #[Param('entity_value_2', type: 'string', validation: 'required|min:3|max:255')]
    class AttributesTestEntity3
    {
        #[Param('entity_value_3', type: 'string', validation: 'required|min:3|max:255')]
        public string $entity_value_3;
    }

    class AttributesTestController3
    {
        #[Entity(AttributesTestEntity3::class)]
        public function __invoke()
        {
            //
        }
        
        #[Entity(AttributesTestEntity3::class, false)]
        public function show()
        {
            //
        }
    }

    $router = app()->get(RouterInterface::class);
    $router->map('POST', '/', AttributesTestController3::class);
    $router->map('GET', '/', AttributesTestController3::class . '::show');

    $routeDefinition = $router->getRoutes()[0]->getDefinition();
    $factory = app()->get(ParamDefinitionFactory::class);
    expect($routeDefinition->params($factory)[0]->name)->toBe('entity_value_1');
    expect($routeDefinition->params($factory)[0]->required)->toBeTrue();
    expect($routeDefinition->params($factory)[1]->name)->toBe('entity_value_2');
    expect($routeDefinition->params($factory)[1]->required)->toBeTrue();
    expect($routeDefinition->params($factory)[2]->name)->toBe('entity_value_3');
    expect($routeDefinition->params($factory)[2]->required)->toBeTrue();

    $routeDefinition = $router->getRoutes()[1]->getDefinition();
    expect($routeDefinition->params($factory))->toBe([]);
});
