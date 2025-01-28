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
        #[Param('entity_value_3')]
        public float $entity_value_3;

        #[Param('entity_value_4')]
        public int $entity_value_4;

        #[Param('entity_value_5')]
        public bool $entity_value_5;

        // Check if the type is overridden correctly
        #[Param('entity_value_6', type: ParamType::Object)]
        public bool $entity_value_6;

        // Check if the type is overridden correctly
        #[Param('entity_value_7')]
        public AttributesTestEntity1 $entity_value_7; // This also checks for infinite loop handle

        #[Param('entity_value_8', maxLevels: 1)]
        public AttributesTestEntity1 $entity_value_8; // This also checks for infinite loop handle

        #[Param]
        public array $entity_value_9;

        #[Param]
        public $entity_value_10;

        #[Param(enum: ['value1', 'value2'])]
        public string $entity_value_11;

        #[Param(enum: [1, 2])]
        public int $entity_value_12;

        #[Param(enum: [1.1, 2.2])]
        public float $entity_value_13;

        #[Param(validation: 'required', enum: ParamType::class)]
        public string $entity_value_14;
    }

    #[Param('class_param', validation: 'required|min:3|max:255')]
    class AttributesTestController1
    {
        #[Param('title', type: ParamType::String, validation: 'required|min:3|max:255')]
        #[Param('content', validation: 'required|min:3|max:255')]
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
    expect($routeDefinition->params($factory)[0]->type)->toBe(ParamType::String);
    expect($routeDefinition->params($factory)[0]->description)->toBe('The class_param of the AttributesTest1');
    expect($routeDefinition->params($factory)[1]->name)->toBe('title');
    expect($routeDefinition->params($factory)[1]->required)->toBeTrue();
    expect($routeDefinition->params($factory)[1]->type)->toBe(ParamType::String);
    expect($routeDefinition->params($factory)[2]->name)->toBe('content');
    expect($routeDefinition->params($factory)[2]->required)->toBeTrue();
    expect($routeDefinition->params($factory)[3]->name)->toBe('entity');
    expect($routeDefinition->params($factory)[3]->required)->toBeFalse();
    expect($routeDefinition->params($factory)[3]->inner[0]->name)->toBe('entity_value_1');
    expect($routeDefinition->params($factory)[3]->inner[1]->name)->toBe('entity_value_2');
    expect($routeDefinition->params($factory)[3]->inner[2]->name)->toBe('entity_value_3');
    expect($routeDefinition->params($factory)[3]->inner[2]->type)->toBe(ParamType::Number);
    expect($routeDefinition->params($factory)[3]->inner[3]->type)->toBe(ParamType::Int);
    expect($routeDefinition->params($factory)[3]->inner[4]->type)->toBe(ParamType::Boolean);
    expect($routeDefinition->params($factory)[3]->inner[5]->type)->toBe(ParamType::Object);
    expect($routeDefinition->params($factory)[3]->inner[6]->type)->toBe(ParamType::Object);
    expect($routeDefinition->params($factory)[3]->inner[6]->inner[0]->name)->toBe('entity_value_1');
    expect($routeDefinition->params($factory)[3]->inner[7]->inner)->toBe([]);
    expect($routeDefinition->params($factory)[3]->inner[8]->name)->toBe('entity_value_9');
    expect($routeDefinition->params($factory)[3]->inner[8]->type)->toBe(ParamType::Array);
    expect($routeDefinition->params($factory)[3]->inner[9]->type)->toBe(ParamType::String);
    expect($routeDefinition->params($factory)[3]->inner[10]->validation[0])->toBe('in:value1,value2');
    expect($routeDefinition->params($factory)[3]->inner[11]->validation[0])->toBe('in:1,2');
    expect($routeDefinition->params($factory)[3]->inner[12]->validation[0])->toBe('in:1.1,2.2');
    expect($routeDefinition->params($factory)[3]->inner[13]->required)->toBeTrue();
    expect($routeDefinition->params($factory)[3]->inner[13]->validation[1])->toBe('in:string,int,number,boolean,array,object');
});

test('can set entity from PHP attribute in class', function () {
    #[Param('entity_value_1', type: 'string', validation: 'required|min:3|max:255')]
    #[Param('entity_value_2', type: 'string', validation: 'required|min:3|max:255')]
    class AttributesTestEntity2
    {
        #[Param(validation: 'required|min:3|max:255')]
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
    expect($routeDefinition->params($factory)[2]->type)->toBe(ParamType::String);
});

test('can set entity from PHP attribute in method', function () {
    #[Param('entity_value_1', type: 'string', validation: 'required|min:3|max:255')]
    #[Param('entity_value_2', type: 'string', validation: 'required|min:3|max:255')]
    class AttributesTestEntity3
    {
        #[Param]
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
    expect($routeDefinition->params($factory)[2]->required)->toBeFalse();

    $routeDefinition = $router->getRoutes()[1]->getDefinition();
    expect($routeDefinition->params($factory))->toBe([]);
});

test('can set inner object from PHP attribute', function () {
    class AttributesTestEntity4
    {
        #[Param(type: 'string', validation: 'required|min:3|max:255')]
        public string $entity_value_1;
    }
    #[Param('array_of_object', type: ParamType::Object, inner: [
        new Param('object_value_1', type: 'string', validation: 'required|min:3|max:255'),
        new Param('object_value_2', type: 'string', validation: 'required|min:3|max:255'),
    ])]
    #[Param('array_of_entity', type: ParamType::Object, inner: AttributesTestEntity4::class)]
    class AttributesTestController4
    {
        public function __invoke()
        {
            //
        }
    }

    $router = app()->get(RouterInterface::class);
    $router->map('POST', '/', AttributesTestController4::class);

    $routeDefinition = $router->getRoutes()[0]->getDefinition();
    $factory = app()->get(ParamDefinitionFactory::class);
    expect($routeDefinition->params($factory)[0]->name)->toBe('array_of_object');
    expect($routeDefinition->params($factory)[0]->required)->toBeFalse();
    expect($routeDefinition->params($factory)[0]->inner[0]->name)->toBe('object_value_1');
    expect($routeDefinition->params($factory)[0]->inner[0]->required)->toBeTrue();
    expect($routeDefinition->params($factory)[0]->inner[1]->name)->toBe('object_value_2');
    expect($routeDefinition->params($factory)[0]->inner[1]->required)->toBeTrue();

    expect($routeDefinition->params($factory)[1]->name)->toBe('array_of_entity');
    expect($routeDefinition->params($factory)[1]->required)->toBeFalse();
    expect($routeDefinition->params($factory)[1]->inner[0]->name)->toBe('entity_value_1');
    expect($routeDefinition->params($factory)[1]->inner[0]->required)->toBeTrue();
});

test('can throw exception for invalid type', function () {
    #[Param('entity_value_1', type: 'invalid')]
    class AttributesTestEntity5
    {
        public string $entity_value_1;
    }

    #[Entity(AttributesTestEntity5::class)]
    class AttributesTestController5
    {
        public function __invoke()
        {
            //
        }
    }

    $router = app()->get(RouterInterface::class);
    $router->map('POST', '/', AttributesTestController5::class);

    $routeDefinition = $router->getRoutes()[0]->getDefinition();
    $factory = app()->get(ParamDefinitionFactory::class);
    expect(fn () => $routeDefinition->params($factory))->toThrow(InvalidArgumentException::class);
});


test('can throw exception for invalid name', function () {
    #[Param]
    class AttributesTestController6
    {
        public function __invoke()
        {
            //
        }
    }

    $router = app()->get(RouterInterface::class);
    $router->map('POST', '/', AttributesTestController6::class);

    $routeDefinition = $router->getRoutes()[0]->getDefinition();
    $factory = app()->get(ParamDefinitionFactory::class);
    expect(fn () => $routeDefinition->params($factory))->toThrow(InvalidArgumentException::class);
});

test('can throw exception for invalid enum class', function () {
    #[Param('entity_value_1', enum: AttributesTestController7::class)]
    class AttributesTestController7
    {
        public function __invoke()
        {
            //
        }
    }

    $router = app()->get(RouterInterface::class);
    $router->map('POST', '/', AttributesTestController7::class);

    $routeDefinition = $router->getRoutes()[0]->getDefinition();
    $factory = app()->get(ParamDefinitionFactory::class);
    $routeDefinition->params($factory);
})->throws(InvalidArgumentException::class);