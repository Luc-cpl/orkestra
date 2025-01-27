<?php

use Orkestra\App;
use Orkestra\Configuration;
use Orkestra\Interfaces\ProviderInterface;
use Orkestra\Services\Hooks\Interfaces\HooksInterface;
use Psr\Container\NotFoundExceptionInterface;
use Orkestra\Providers\CommandsProvider;
use Orkestra\Providers\HooksProvider;
use Orkestra\Providers\HttpProvider;
use Orkestra\Providers\ViewProvider;

test('can get slug', function () {
    expect(app()->slug())->toEqual('app');
    app()->config()->set('slug', 'testSlug');
    expect(app()->slug())->toEqual('testSlug');
});

test('can get from container', function () {
    app()->bind('test', fn () => 'testValue');
    expect(app()->get('test'))->toEqual('testValue');
});

test('can not get from container with non existent key', function () {
    app()->get('nonExistentKey');
})->expectException(NotFoundExceptionInterface::class);

test('can make from container with constructor parameters', function () {
    app()->bind('test', fn ($param) => $param);
    expect(app()->make('test', ['param' => 'testValue']))->toEqual('testValue');
});

test('can not use a invalid env config', function () {
    app()->config()->set('env', 'invalidEnv');
    app()->boot();
})->expectException(InvalidArgumentException::class);

test('can not use a invalid root config', function () {
    app()->config()->set('root', 'invalidRoot');
    app()->boot();
})->expectException(InvalidArgumentException::class);

test('can not use a invalid slug config', function (string $slug) {
    app()->config()->set('slug', $slug);
    app()->boot();
})->with([
    'invalid slug',
    'invalidSlug',
    'invalid slug!',
    'invalidSlug!',
    'invalid-slug!',
    'invalid_slug!',
])->expectException(InvalidArgumentException::class);

test('can register a provider', function () {
    $providerClass = new class () implements ProviderInterface {
        public string $test;
        public function register(App $app): void
        {
        }
        public function boot(App $app): void
        {
        }
    };
    app()->provider($providerClass::class);
    $provider = app()->get($providerClass::class);
    $provider->test = 'testValue';
    expect(app()->get($providerClass::class))->toEqual($provider);
});

test('can not register provider with non existent class', function () {
    app()->provider('testProvider');
})->expectException(InvalidArgumentException::class);

test('can not register a provider with non provider class', function () {
    $nonProviderClass = new class () {};
    app()->provider($nonProviderClass::class);
})->throws(InvalidArgumentException::class);

test('can not bind a value in container', function () {
    app()->bind('test', 'testValue');
})->throws(InvalidArgumentException::class);

test('can bind a closure in container', function () {
    $callback = fn () => 'testValue';
    app()->bind('test', $callback);
    expect(app()->get('test'))->toEqual('testValue');
});

test('can bind a class in container by name', function () {
    $class = new class () {
        public string $value;
    };

    app()->bind('testClassString', $class::class);
    expect(app()->get('testClassString'))->toBeInstanceOf(get_class($class));
    expect(app()->make('testClassString'))->toBeInstanceOf(get_class($class));
    $instance = app()->make('testClassString');
    $instance->value = 'testValue2';
    expect(app()->make('testClassString'))->not->toEqual($instance);
});

test('can bind a class instance in container', function () {
    $class = new class () {
    };

    app()->bind('testClass', $class);
    expect(app()->get('testClass'))->toBeInstanceOf(get_class($class));
    expect(app()->get('testClass'))->toEqual($class);
});

test('can get same instance in container', function () {
    $class = new class () {
        public string $value;
    };

    app()->bind('testClassString', $class::class);
    expect(app()->get('testClassString'))->toBeInstanceOf(get_class($class));
    $instance = app()->get('testClassString');
    $instance->value = 'testValue2';
    expect(app()->get('testClassString'))->toEqual($instance);
});

test('can run if available with existing class', function () {
    $class = new class () {
    };
    $value = app()->runIfAvailable($class::class, fn ($instance) => $instance);
    expect($value)->toBeInstanceOf(get_class($class));
});

test('can not run if available with non existent class', function () {
    $value = app()->runIfAvailable('notExistClass', fn () => 'testValue');
    expect($value)->toBeNull();
});

test('can check if container has service', function () {
    expect(app()->has('test'))->toBeFalse();
    app()->bind('test', fn () => 'testValue');
    expect(app()->has('test'))->toBeTrue();
});

test('can throw exception when booting twice', function () {
    app()->boot();
    app()->boot();
})->throws(Exception::class);

test('can boot', function () {
    $providerClass = new class () implements ProviderInterface {
        public $test = null;
        public function register(App $app): void
        {
        }
        public function boot(App $app): void
        {
            $this->test = 'testValue';
        }
    };

    app()->provider($providerClass::class);
    app()->config()->set('env', 'development');
    app()->config()->set('root', './');
    app()->boot();

    $provider = app()->get($providerClass::class);
    expect($provider->test)->toEqual('testValue');
});

test('can boot all existing providers', function () {
    app()->provider(CommandsProvider::class);
    app()->provider(HooksProvider::class);
    app()->provider(HttpProvider::class);
    app()->provider(ViewProvider::class);

    app()->boot();

    /**
     * Do not run any assertions as we are only testing if the boot method runs without errors.
     * We should add assertions to test the providers individually while testing the related services.
     */
    expect(true)->toBeTrue();
});

test('can not get a service from container before booting', function () {
    $app = new App(new Configuration());
    $app->get('test');
})->throws(BadMethodCallException::class);

test('can decorate a service', function () {
    $mock = Mockery::mock();
    $mock->shouldReceive('test')->andReturn('testValue');

    $mock2 = Mockery::mock();
    $mock2->shouldReceive('test')->andReturn('testValueDecorated');

    $mock3 = Mockery::mock();
    $mock3->shouldReceive('test')->andReturn('testValueDecoratedDecorated');

    $callbackMock = Mockery::mock();
    $callbackMock->shouldReceive('run')->once()->andReturn($mock2);

    app()->bind($mock::class, fn () => $mock);
    app()->decorate($mock::class, fn ($service) => $callbackMock->run());
    app()->decorate($mock::class, fn ($service) => $mock3);
    expect(app()->get($mock::class)->test())->toEqual('testValueDecoratedDecorated');
});

test('can decorate a service before add to container', function () {
    $class = new class () {
        public function test()
        {
            return 'testValue';
        }
    };

    $mock2 = Mockery::mock();
    $mock2->shouldReceive('test')->andReturn('testValueDecorated');

    app()->decorate($class::class, fn ($service) => $mock2);
    app()->bind($class::class, fn () => $class);
    expect(app()->get($class::class)->test())->toEqual('testValueDecorated');
});

test('can decorate a bind interface', function () {
    interface TestInterface
    {
        public function test();
    }

    $class = new class () implements TestInterface {
        public function test()
        {
            return 'testValue';
        }
    };

    $class2 = new class () implements TestInterface {
        public function test()
        {
            return 'testValueDecorated';
        }
    };

    app()->bind(TestInterface::class, $class::class);
    app()->decorate(TestInterface::class, fn ($service) => $class2);
    expect(app()->get(TestInterface::class)->test())->toEqual('testValueDecorated');
});

test('can decorate a service without bind the class', function () {
    $class = new class () {
        public function test()
        {
            return 'testValue';
        }
    };
    $mock2 = Mockery::mock();
    $mock2->shouldReceive('test')->andReturn('testValueDecorated');

    app()->decorate($class::class, fn ($service) => $mock2);
    app()->boot();
    expect(app()->get($class::class)->test())->toEqual('testValueDecorated');
});

test('can not decorate a service after booting', function () {
    app()->boot();
    app()->decorate('test', fn ($service) => $service);
})->throws(Exception::class);

test('can decorate a value in container', function () {
    app()->bind('test', fn () => 'testValue');
    app()->decorate('test', fn ($value) => $value . 'Decorated');
    expect(app()->get('test'))->toEqual('testValueDecorated');
});

test('can query a app hook', function () {
    app()->provider(HooksProvider::class);
    app()->get(HooksInterface::class)->register('app.test', fn () => 'testValue');
    expect(app()->hookQuery('test', null))->toEqual('testValue');
});

test('can call a app hook', function () {
    app()->provider(HooksProvider::class);

    $value = false;
    $callback = function () use (&$value) {
        $value = true;
    };

    app()->get(HooksInterface::class)->register('app.test', $callback);
    app()->hookCall('test');
    expect($value)->toBeTrue();
});

test('can register an app hook', function () {
    app()->provider(HooksProvider::class);

    $value = false;
    $callback = function () use (&$value) {
        $value = true;
    };

    app()->hookRegister('test', $callback);
    app()->get(HooksInterface::class)->call('app.test');
    expect($value)->toBeTrue();
});
