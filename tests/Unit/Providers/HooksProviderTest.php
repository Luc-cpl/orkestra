<?php

use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;
use Orkestra\Providers\HooksProvider;
use Orkestra\Services\Hooks\Interfaces\ListenerInterface;

test('can throw a exception if the listener does not implement the method handle', function () {
    $provider = new class () implements ProviderInterface {
        public array $listeners = [];
        public function register(App $app): void
        {
            $listener = Mockery::mock(ListenerInterface::class)->makePartial();
            $listener->shouldReceive('hook')->once()->andReturn('hooked');
            $app->bind($listener::class, $listener);
            $this->listeners[] = $listener::class;
        }

        public function boot(App $app): void
        {
            //
        }
    };
    app()->provider($provider::class);
    app()->provider(HooksProvider::class);
    app()->boot();
})->throws(Exception::class);
