<?php

use Orkestra\App;
use Orkestra\Configuration;
use Orkestra\Interfaces\ProviderInterface;
use Psr\Container\NotFoundExceptionInterface;

use Orkestra\Providers\CommandsProvider;
use Orkestra\Providers\HooksProvider;
use Orkestra\Providers\HttpProvider;
use Orkestra\Providers\ViewProvider;

beforeEach(function () {
    $this->config = new Configuration();
    $this->app = new App($this->config);
});

test('can get slug', function () {
    expect($this->app->slug())->toEqual('app');
    $this->config->set('slug', 'testSlug');
    expect($this->app->slug())->toEqual('testSlug');
});

test('can get configuration', function () {
    expect($this->app->config())->toBe($this->config);
});

test('can get from container', function () {
    app()->bind('test', fn () => 'testValue');
    expect(app()->get('test'))->toEqual('testValue');
});

test('can not get from container with non existent key', function () {
    $this->expectException(NotFoundExceptionInterface::class);
    app()->get('nonExistentKey');
});

test('can make from container with constructor parameters', function () {
    app()->bind('test', fn ($param) => $param);
    expect(app()->make('test', ['param' => 'testValue']))->toEqual('testValue');
});

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
    $this->app->provider($providerClass::class);
    $this->app->boot();
    $provider = $this->app->get($providerClass::class);
    $provider->test = 'testValue';
    expect($this->app->get($providerClass::class))->toEqual($provider);
});

test('can not register provider with non existent class', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->app->provider('testProvider');
});

test('can not register a provider with non provider class', function () {
    $nonProviderClass = new class () {};
    $this->app->provider($nonProviderClass::class);
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
    $this->assertNotEquals($instance, app()->make('testClassString'));
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
    $this->app->provider($providerClass::class);
    $this->config->set('env', 'development');
    $this->config->set('root', './');
    $this->app->boot();

    $provider = $this->app->get($providerClass::class);
    expect($provider->test)->toEqual('testValue');
});

test('can boot all existing providers', function () {
    $this->config->set('env', 'development');
    $this->config->set('root', './');

    $this->app->provider(CommandsProvider::class);
    $this->app->provider(HooksProvider::class);
    $this->app->provider(HttpProvider::class);
    $this->app->provider(ViewProvider::class);

    $this->app->boot();

    /**
     * Do not run any assertions as we are only testing if the boot method runs without errors.
     * We should add assertions to test the providers individually while testing the related services.
     */
    expect(true)->toBeTrue();
});

test('can not get a service from container before booting', function () {
    $this->app->get('test');
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

    $this->app->bind($mock::class, fn () => $mock);
    $this->app->decorate($mock::class, fn ($service) => $callbackMock->run());
    $this->app->decorate($mock::class, fn ($service) => $mock3);
    $this->app->boot();
    expect($this->app->get($mock::class)->test())->toEqual('testValueDecoratedDecorated');
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

    $this->app->decorate($class::class, fn ($service) => $mock2);
    $this->app->bind($class::class, fn () => $class);
    $this->app->boot();
    expect($this->app->get($class::class)->test())->toEqual('testValueDecorated');
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

    $this->app->bind(TestInterface::class, $class::class);
    $this->app->decorate(TestInterface::class, fn ($service) => $class2);
    $this->app->boot();
    expect($this->app->get(TestInterface::class)->test())->toEqual('testValueDecorated');
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

    $this->app->decorate($class::class, fn ($service) => $mock2);
    $this->app->boot();
    expect($this->app->get($class::class)->test())->toEqual('testValueDecorated');
});

test('can not decorate a service after booting', function () {
    $this->app->boot();
    $this->app->decorate('test', fn ($service) => $service);
})->throws(Exception::class);

test('can decorate a value in container', function () {
    $this->app->bind('test', fn () => 'testValue');
    $this->app->decorate('test', fn ($value) => $value . 'Decorated');
    $this->app->boot();
    expect($this->app->get('test'))->toEqual('testValueDecorated');
});
