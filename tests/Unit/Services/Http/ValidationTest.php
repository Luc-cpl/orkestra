<?php

use Orkestra\Providers\HttpProvider;
use Orkestra\Services\Http\Factories\ParamDefinitionFactory;
use Orkestra\Services\Http\Interfaces\DefinitionInterface;
use Orkestra\Services\Http\Interfaces\RouterInterface;
use Orkestra\Services\Http\Route;

beforeEach(function () {
	app()->provider(HttpProvider::class);
});

test('can add a route definition with validation and default value', function () {
	$route = new Route(app(), 'GET', '/', fn () => 'test');
	$route->setDefinition([
		'params' => [
			'query' => [
				'default' => 'test',
				'validation' => 'required|min:3|max:255',
			],
		],
	]);

	expect($route->getDefinition()->params()[0]->validation)->toBe(['required', 'min:3', 'max:255']);
	expect($route->getDefinition()->params()[0]->default)->toBe('test');
});

test('can add a route definition class with validation', function () {
	$definition = Mockery::mock(DefinitionInterface::class);
	app()->singleton($definition::class, function () use ($definition) {
		$factory = app()->get(ParamDefinitionFactory::class);
		$definition->shouldReceive('params')->andReturn([
			$factory->String(
				title: 'Query',
				name: 'query',
				validation: 'required|min:3|max:255',
			),
		]);
		return $definition;
	});

	$route = new Route(app(), 'GET', '/', fn () => 'test');
	$route->setDefinition($definition::class);
	expect($route->getDefinition()->params()[0]->validation)->toBe(['required', 'min:3', 'max:255']);
});

test('can add inner parameters as array', function () {
	$definition = Mockery::mock(DefinitionInterface::class);
	app()->singleton($definition::class, function () use ($definition) {
		$factory = app()->get(ParamDefinitionFactory::class);
		$definition->shouldReceive('params')->andReturn([
			$factory->Array(
				title: 'Query',
				name: 'query'
			)->setInner([
				$factory->String(
					title: 'Query value value',
					name: 'value',
					validation: 'required|min:4|max:255',
				),
				$factory->String(
					title: 'Query value type',
					name: 'key',
					validation: 'required|min:5|max:255',
				),
			]),
		]);
		return $definition;
	});

	$route = new Route(app(), 'GET', '/', fn () => 'test');
	$route->setDefinition($definition::class);
	expect($route->getDefinition()->params()[0]->inner[0]->validation)->toBe(['required', 'min:4', 'max:255']);
	expect($route->getDefinition()->params()[0]->inner[1]->validation)->toBe(['required', 'min:5', 'max:255']);
});

test('can validate a request with a route definition', function () {
	$definition = Mockery::mock(DefinitionInterface::class);
	app()->singleton($definition::class, function () use ($definition) {
		$factory = app()->get(ParamDefinitionFactory::class);
		$definition->shouldReceive('params')->andReturn([
			$factory->String(
				title: 'Query',
				name: 'query',
				validation: 'required|min:3|max:255',
			),
		]);
		return $definition;
	});

	$router = app()->get(RouterInterface::class);
	$router->get('/', fn () => ['status' => 'passed'])->setDefinition($definition::class)->json();

	$response = request();
	$response = json_decode((string) $response->getBody());
	expect($response->status_code)->toBe(400);

	$response = request(data: ['query' => 'test']);
	$response = json_decode((string) $response->getBody());
	expect($response->status)->toBe('passed');
});

test('can validate a request with inner parameters', function () {
	$definition = Mockery::mock(DefinitionInterface::class);
	app()->singleton($definition::class, function () use ($definition) {
		$factory = app()->get(ParamDefinitionFactory::class);
		$definition->shouldReceive('params')->andReturn([
			$factory->Array(
				title: 'Query',
				name: 'query'
			)->setInner([
				$factory->string(
					title: 'Query value value',
					name: 'value',
					validation: 'required',
				),
				$factory->string(
					title: 'Query value type',
					name: 'key',
					validation: 'required',
				),
			]),
		]);
		return $definition;
	});

	$router = app()->get(RouterInterface::class);
	$router->get('/', fn () => ['status' => 'passed'])->setDefinition($definition::class)->json();

	// The query is not required
	$response = request();
	$response = json_decode((string) $response->getBody());
	expect($response->status)->toBe('passed');

	$response = request(data: ['query' => [['value' => 'test']]]);
	$response = json_decode((string) $response->getBody());
	expect($response->status_code)->toBe(400);

	$response = request(data: ['query' => [['value' => 'test', 'key' => 'test']]]);
	$response = json_decode((string) $response->getBody());
	expect($response->status)->toBe('passed');
});