<?php

use Orkestra\Services\Http\Controllers\AbstractApiController;
use Orkestra\Services\Http\Interfaces\RouteInterface;

test('can set a route in api controller', function () {
    $route = Mockery::mock(RouteInterface::class);

    $class = new class () extends AbstractApiController {
        public function getRoute(): RouteInterface
        {
            return $this->route;
        }
    };

    app()->bind(AbstractApiController::class, $class::class);
    $controller = app()->get(AbstractApiController::class);
    $controller->setRoute($route);
    expect($controller->getRoute())->toBe($route);
});
