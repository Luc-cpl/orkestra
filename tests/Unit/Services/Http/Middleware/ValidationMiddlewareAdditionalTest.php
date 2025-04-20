<?php

use Orkestra\App;
use Orkestra\Services\Http\Middleware\ValidationMiddleware;
use Orkestra\Services\Http\Factories\ParamDefinitionFactory;
use Orkestra\Services\Http\Enum\ParamType;
use Orkestra\Services\Http\Entities\ParamDefinition;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rakit\Validation\Validator;
use Rakit\Validation\Validation;

covers(ValidationMiddleware::class);

test('validates int parameter type correctly', function () {
    // Test specifically line 41
    $app = app();
    $factory = $app->get(ParamDefinitionFactory::class);

    // Create an int parameter
    $params = [
        $factory->Int(
            title: 'Int Param',
            name: 'int_param',
            validation: 'required'
        )
    ];

    $middleware = $app->make(ValidationMiddleware::class, ['params' => $params]);

    // Use reflection to access setValidation method
    $reflector = new ReflectionClass($middleware);
    $method = $reflector->getMethod('setValidation');
    $method->setAccessible(true);

    $intParam = $params[0];

    // Call setValidation method
    $result = $method->invoke($middleware, $intParam);

    // Validate the result - int type should prepend 'integer' validation
    expect($result)->toBe(['integer', 'required']);
});

test('validates number parameter type correctly', function () {
    // Test specifically line 42
    $app = app();
    $factory = $app->get(ParamDefinitionFactory::class);

    // Create a number parameter
    $params = [
        $factory->Number(
            title: 'Number Param',
            name: 'number_param',
            validation: 'required'
        )
    ];

    $middleware = $app->make(ValidationMiddleware::class, ['params' => $params]);

    // Use reflection to access setValidation method
    $reflector = new ReflectionClass($middleware);
    $method = $reflector->getMethod('setValidation');
    $method->setAccessible(true);

    $numberParam = $params[0];

    // Call setValidation method
    $result = $method->invoke($middleware, $numberParam);

    // Validate the result - number type should prepend 'numeric' validation
    expect($result)->toBe(['numeric', 'required']);
});

test('validates boolean parameter type correctly', function () {
    // Test specifically line 43
    $app = app();
    $factory = $app->get(ParamDefinitionFactory::class);

    // Create a boolean parameter
    $params = [
        $factory->Boolean(
            title: 'Boolean Param',
            name: 'boolean_param',
            validation: 'required'
        )
    ];

    $middleware = $app->make(ValidationMiddleware::class, ['params' => $params]);

    // Use reflection to access setValidation method
    $reflector = new ReflectionClass($middleware);
    $method = $reflector->getMethod('setValidation');
    $method->setAccessible(true);

    $booleanParam = $params[0];

    // Call setValidation method
    $result = $method->invoke($middleware, $booleanParam);

    // Validate the result - boolean type should prepend 'boolean' validation
    expect($result)->toBe(['boolean', 'required']);
});

test('handles string type validation correctly', function () {
    $app = app();
    $factory = $app->get(ParamDefinitionFactory::class);

    // Create a string parameter (should test lines 41-43)
    $params = [
        $factory->String(
            title: 'String Param',
            name: 'string_param',
            validation: 'required'
        )
    ];

    // Create middleware with parameters
    $middleware = $app->make(ValidationMiddleware::class, ['params' => $params]);

    // Use reflection to access setValidation method directly
    $reflector = new ReflectionClass($middleware);
    $method = $reflector->getMethod('setValidation');
    $method->setAccessible(true);

    // Get the string param from the array
    $stringParam = $params[0];

    // Call setValidation method
    $result = $method->invoke($middleware, $stringParam);

    // Validate the result (string type doesn't add type validation but preserves existing rules)
    expect($result)->toBe(['required']);
});

test('handles array type with multiple inner parameters correctly', function () {
    $app = app();
    $factory = $app->get(ParamDefinitionFactory::class);

    // Create multiple inner parameters
    $innerParams = [
        $factory->String(
            title: 'Inner Property 1',
            name: 'prop1',
            validation: 'required'
        ),
        $factory->String(
            title: 'Inner Property 2',
            name: 'prop2',
            validation: 'required'
        )
    ];

    // Create array parameter with multiple inner parameters (should test line 73)
    $params = [
        $factory->Array(
            title: 'Array Parameter',
            name: 'array_param',
            validation: 'required'
        )->setInner($innerParams)
    ];

    // Create middleware with parameters
    $middleware = $app->make(ValidationMiddleware::class, ['params' => $params]);

    // Access the flattened rules to verify they were processed correctly
    $reflector = new ReflectionClass($middleware);
    $property = $reflector->getProperty('rules');
    $property->setAccessible(true);
    $rules = $property->getValue($middleware);

    // Verify the rules were flattened correctly
    expect($rules)->toHaveKey('array_param');
    expect($rules)->toHaveKey('array_param.*.prop1');
    expect($rules)->toHaveKey('array_param.*.prop2');
});

test('handles array with single inner parameter correctly', function () {
    // Test specifically line 73
    $app = app();
    $factory = $app->get(ParamDefinitionFactory::class);

    // Create a single inner parameter
    $innerParams = [
        $factory->String(
            title: 'Inner Property',
            name: 'prop',
            validation: 'required'
        )
    ];

    // Create array parameter with single inner parameter
    $params = [
        $factory->Array(
            title: 'Array Parameter',
            name: 'array_param',
            validation: 'required'
        )->setInner($innerParams)
    ];

    // Create middleware by directly calling the constructor
    $validator = app()->get(Validator::class);
    $middleware = new ValidationMiddleware($validator, [], $params);

    // Access the flattened rules to verify they were processed correctly
    $reflector = new ReflectionClass($middleware);
    $property = $reflector->getProperty('rules');
    $property->setAccessible(true);
    $rules = $property->getValue($middleware);

    // Verify the rules were flattened correctly, with inner param name set to '*'
    expect($rules)->toHaveKey('array_param');
    expect($rules)->toHaveKey('array_param.*');
});

test('adjusts request parameter types correctly', function () {
    // Test specifically lines 96-98
    $app = app();

    // Create a middleware instance
    $validator = app()->get(Validator::class);
    $middleware = new ValidationMiddleware($validator);

    // Use reflection to access adjustRequestParamsTypes method
    $reflector = new ReflectionClass($middleware);
    $method = $reflector->getMethod('adjustRequestParamsTypes');
    $method->setAccessible(true);

    // Test data with string values that should be converted
    $testData = [
        'nullValue' => 'null',    // Should be converted to null
        'trueValue' => 'true',    // Should be converted to boolean true
        'falseValue' => 'false',  // Should be converted to boolean false
        'otherValue' => 'text'    // Should remain as is
    ];

    // Call the method
    $result = $method->invoke($middleware, $testData);

    // Verify conversions
    expect($result['nullValue'])->toBeNull();
    expect($result['trueValue'])->toBeTrue();
    expect($result['falseValue'])->toBeFalse();
    expect($result['otherValue'])->toBe('text');
});

test('processes array parameters with different array keys correctly', function () {
    $app = app();

    // Create a mock validation object
    $validation = Mockery::mock(Validation::class);
    $validation->shouldReceive('validate')->andReturnSelf();
    $validation->shouldReceive('fails')->andReturn(false);

    // Create a mock validator
    $validator = Mockery::mock(Validator::class);
    $validator->shouldReceive('make')->andReturn($validation);

    // Create a param definition for an array with numeric keys
    $param = new ParamDefinition(
        type: ParamType::Array,
        title: 'Array Parameter',
        name: 'array_param'
    );
    $param->setValidation(['required']);

    // Create middleware with this parameter
    $middleware = new ValidationMiddleware($validator, [], [$param]);

    // Create a request with an array parameter that has numeric keys
    $request = Mockery::mock(ServerRequestInterface::class);
    $request->shouldReceive('getParsedBody')->andReturn([
        'array_param' => [
            0 => 'value1',
            1 => 'value2'
        ]
    ]);
    $request->shouldReceive('getQueryParams')->andReturn([]);
    $request->shouldReceive('withQueryParams')->andReturnSelf();
    $request->shouldReceive('withParsedBody')->andReturnSelf();

    // Setup app for hooks
    $app = Mockery::mock(App::class);
    $app->shouldReceive('hookCall')->andReturn(null);

    // Use reflection to set the app property
    $reflector = new ReflectionClass($middleware);
    $property = $reflector->getProperty('app');
    $property->setAccessible(true);
    $property->setValue($middleware, $app);

    // Setup handler
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $handler->shouldReceive('handle')->andReturn($response);

    // Execute middleware
    $result = $middleware->process($request, $handler);
    expect($result)->toBe($response);
});

test('handles complex nested object with numeric and string keys correctly', function () {
    $app = app();

    // Create a mock validation object
    $validation = Mockery::mock(Validation::class);
    $validation->shouldReceive('validate')->andReturnSelf();
    $validation->shouldReceive('fails')->andReturn(false);

    // Create a mock validator
    $validator = Mockery::mock(Validator::class);
    $validator->shouldReceive('make')->andReturn($validation);

    // Create a complex nested parameter structure with both numeric and string keys
    $stringParam = new ParamDefinition(
        type: ParamType::String,
        title: 'String Param',
        name: 'string_param'
    );
    $stringParam->setValidation(['required']);

    // Create an object parameter with inner parameters
    $objectParam = new ParamDefinition(
        type: ParamType::Object,
        title: 'Object Param',
        name: 'object_param'
    );
    $objectParam->setValidation(['required']);
    $objectParam->setInner([$stringParam]);

    // Create middleware with these parameters
    $middleware = new ValidationMiddleware($validator, [], [$objectParam]);

    // Create a request with a complex nested object that has both numeric and string keys
    // This tests the removeUndefinedRules method with array values (lines 96-98)
    $request = Mockery::mock(ServerRequestInterface::class);
    $request->shouldReceive('getParsedBody')->andReturn([
        'object_param' => [
            'string_param' => 'value',
            'numeric_keys' => [0 => 'first', 1 => 'second'],
            'string_keys' => ['a' => 'value_a', 'b' => 'value_b']
        ]
    ]);
    $request->shouldReceive('getQueryParams')->andReturn([]);
    $request->shouldReceive('withQueryParams')->andReturnSelf();
    $request->shouldReceive('withParsedBody')->andReturnSelf();

    // Setup app for hooks
    $app = Mockery::mock(App::class);
    $app->shouldReceive('hookCall')->andReturn(null);

    // Use reflection to set the app property
    $reflector = new ReflectionClass($middleware);
    $property = $reflector->getProperty('app');
    $property->setAccessible(true);
    $property->setValue($middleware, $app);

    // Setup handler
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $handler->shouldReceive('handle')->andReturn($response);

    // Execute middleware
    $result = $middleware->process($request, $handler);
    expect($result)->toBe($response);

    // Use reflection to directly test removeUndefinedRules
    $method = $reflector->getMethod('removeUndefinedRules');
    $method->setAccessible(true);

    $testArray = [
        'defined_key' => 'value',
        'nested' => [
            'numeric' => [0 => 'first', 1 => 'second'],
            'keys' => 'value'
        ]
    ];

    // Set rules property with test keys
    $rulesProperty = $reflector->getProperty('rules');
    $rulesProperty->setAccessible(true);
    $rulesProperty->setValue($middleware, [
        'defined_key' => ['required'],
        'nested.numeric.*' => ['required'],
        'nested.keys' => ['required']
    ]);

    // Call removeUndefinedRules (this should test lines 96-98)
    $result = $method->invoke($middleware, $testArray);

    // Verify the result contains both the string key and numeric array items
    expect($result)->toHaveKey('defined_key');
    expect($result)->toHaveKey('nested');
    expect($result['nested'])->toHaveKey('numeric');
    expect($result['nested'])->toHaveKey('keys');
    expect($result['nested']['numeric'])->toBe([0 => 'first', 1 => 'second']);
});
