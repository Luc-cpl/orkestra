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
    $this->app->bind('test', fn() => 'testValue');
    expect($this->app->get('test'))->toEqual('testValue');
});

test('can not get from container with non existent key', function () {
    $this->expectException(NotFoundExceptionInterface::class);
    $this->app->get('nonExistentKey');
});

test('can get from container with constructor parameters', function () {
    $this->app->bind('test', fn($param) => $param);
    expect($this->app->get('test', ['param' => 'testValue']))->toEqual('testValue');
});

test('can register a provider', function () {
    $providerClass = new class implements ProviderInterface {
        public string $test;
        public function register(App $app): void
        {
        }
        public function boot(App $app): void
        {
        }
    };
    $this->app->provider($providerClass::class);
    $provider = $this->app->get($providerClass::class);
    $provider->test = 'testValue';
    expect($this->app->get($providerClass::class))->toEqual($provider);
});

test('can not register provider with non existent class', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->app->provider('testProvider');
});

test('can not register a provider with non provider class', function () {
    $nonProviderClass = new class {};
    $this->app->provider($nonProviderClass::class);
})->throws(InvalidArgumentException::class);

test('can not bind a value in container', function () {
    $value = 'testValue';
    $this->app->bind('test', $value);
})->throws(InvalidArgumentException::class);

test('can bind a closure in container', function () {
    $callback = fn() => 'testValue';
    $this->app->bind('test', $callback);
    expect($this->app->get('test'))->toEqual('testValue');
});

test('can bind a class in container by name', function () {
    $class = new class
    {
        public string $value;
    };

    $this->app->bind('testClassString', $class::class);
    expect($this->app->get('testClassString'))->toBeInstanceOf(get_class($class));
    $instance = $this->app->get('testClassString');
    $instance->value = 'testValue2';
    $this->assertNotEquals($instance, $this->app->get('testClassString'));
});

test('can bind a class instance in container', function () {
    $class = new class
    {
    };

    $this->app->bind('testClass', $class);
    expect($this->app->get('testClass'))->toBeInstanceOf(get_class($class));
    expect($this->app->get('testClass'))->toEqual($class);
});

test('can instantiate a singleton in the container', function () {
    $class = new class
    {
        public string $value;
    };

    $this->app->singleton('testClassString', $class::class);
    expect($this->app->get('testClassString'))->toBeInstanceOf(get_class($class));
    $instance = $this->app->get('testClassString');
    $instance->value = 'testValue2';
    expect($this->app->get('testClassString'))->toEqual($instance);
});

test('can run if available with existing class', function () {
    $class = new class
    {
    };
    $value = $this->app->runIfAvailable($class::class, fn($instance) => $instance);
    expect($value)->toBeInstanceOf(get_class($class));
});

test('can not run if available with non existent class', function () {
    $value = $this->app->runIfAvailable('notExistClass', fn() => 'testValue');
    expect($value)->toBeNull();
});

test('can check if container has service', function () {
    expect($this->app->has('test'))->toBeFalse();
    $this->app->bind('test', fn() => 'testValue');
    expect($this->app->has('test'))->toBeTrue();
});

test('can throw exception when booting without env', function () {
    $this->config->set('root', './');
    $this->app->boot();
})->throws(InvalidArgumentException::class);

test('can throw exception when booting without root', function () {
    $this->config->set('env', 'development');
    $this->app->boot();
})->throws(InvalidArgumentException::class);

test('can throw exception when booting twice', function () {
    $this->config->set('env', 'development');
    $this->config->set('root', './');
    $this->app->boot();
    $this->app->boot();
})->throws(Exception::class);

test('can boot', function () {
    $providerClass = new class implements ProviderInterface
    {
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
    $provider = $this->app->get($providerClass::class);
    expect($provider->test)->toEqual(null);
    $this->config->set('env', 'development');
    $this->config->set('root', './');
    $this->app->boot();
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