<?php

use League\Route\Http\Exception\NotFoundException;
use Orkestra\Handlers\HttpHandler;
use Orkestra\Providers\HttpProvider;
use Orkestra\Services\Http\Interfaces\RouterInterface;

beforeEach(function () {
	app()->provider(HttpProvider::class);
});

test('can handle a not found request', function () {
	app()->get(HttpHandler::class)->handle();
})->throws(NotFoundException::class);

test('can handle a found request', function () {
	$router = app()->get(RouterInterface::class);
	$router->map('GET', '/', fn () => 'test');

	ob_start();
	app()->get(HttpHandler::class)->handle();
	expect(ob_get_clean())->toBe('test');
});