<?php

use Orkestra\Services\Http\interfaces\RouterInterface;

return function (RouterInterface $router) {
	$router->get('/', function () {
		return 'Hello World';
	});
};