<?php

use Orkestra\App;
use Orkestra\Providers\HooksProvider;
use Orkestra\Services\Hooks\Hooks;
use Orkestra\Services\Hooks\Interfaces\HooksInterface;
use Orkestra\Services\Hooks\Interfaces\ListenerInterface;

// Create a sample listener for testing
class TestListener implements ListenerInterface
{
    public function hook(): string|array
    {
        return 'test.hook';
    }

    public function handle(): void
    {
        // This method is called when the hook is fired
    }
}

// Create a sample listener that returns multiple hooks
class MultipleHooksListener implements ListenerInterface
{
    public function hook(): string|array
    {
        return ['test.hook1', 'test.hook2'];
    }

    public function handle(): void
    {
        // This method is called when the hook is fired
    }
}

// Create a sample listener that uses {app} placeholder
class AppSlugListener implements ListenerInterface
{
    public function hook(): string|array
    {
        return '{app}.hook';
    }

    public function handle(): void
    {
        // This method is called when the hook is fired
    }
}

// Create an invalid listener without handle method
class InvalidListener implements ListenerInterface
{
    public function hook(): string|array
    {
        return 'test.hook';
    }
    
    // Missing handle method
}

test('can register hooks provider configuration', function () {
    $app = app();
    $provider = new HooksProvider();
    
    // Register the provider
    $provider->register($app);
    
    // Verify config setup
    // 'listeners' is initialized with [] by default, so it will exist
    expect($app->config()->get('listeners'))->toBe([]);
    expect($app->config()->get('definition'))->toHaveKey('listeners');
    expect($app->config()->get('validation'))->toHaveKey('listeners');
    
    // Get the validator callback and test it
    $validator = $app->config()->get('validation')['listeners'];
    
    // Non-array should fail
    $result = $validator('not-an-array');
    expect($result)->toBeString();
    expect($result)->toContain('must be an array');
    
    // Array should pass
    $result = $validator([]);
    expect($result)->toBeTrue();
    
    // Verify HooksInterface binding
    expect($app->has(HooksInterface::class))->toBeTrue();
    expect($app->get(HooksInterface::class))->toBeInstanceOf(Hooks::class);
});

test('can boot hooks provider and register listeners', function () {
    $app = app();
    $provider = new HooksProvider();
    
    // Register the provider
    $provider->register($app);
    
    // Set up config with a listener
    $app->config()->set('listeners', [TestListener::class]);
    $app->bind(TestListener::class, TestListener::class);
    
    // Boot the provider
    $provider->boot($app);
    
    // Get the hooks service
    $hooks = $app->get(HooksInterface::class);
    
    // Verify the listener was registered
    expect($hooks->has('test.hook'))->toBeTrue();
});

test('provider checks for listeners property in providers', function () {
    // Create a mock app
    $app = Mockery::mock(App::class);
    $hooks = Mockery::mock(HooksInterface::class);
    
    // Create a provider with listeners property
    $testProvider = new class {
        /** @var array<class-string<ListenerInterface>> */
        public array $listeners = [TestListener::class];
    };
    
    // Set up expectations
    $app->shouldReceive('config->get')->with('listeners')->andReturn([]);
    $app->shouldReceive('get')->with(HooksInterface::class)->andReturn($hooks);
    $app->shouldReceive('getProviders')->andReturn(['testProvider']);
    $app->shouldReceive('get')->with('testProvider')->andReturn($testProvider);
    $app->shouldReceive('slug')->andReturn('app');
    
    // We need to make sure the provider tries to register the listeners
    $app->shouldReceive('bind')->with(TestListener::class, TestListener::class)->once();
    $app->shouldReceive('get')->with(TestListener::class)->andReturn(new TestListener());
    
    // The provider should check if the hook is already registered
    $hooks->shouldReceive('register')->with('test.hook', Mockery::any())->once();
    
    // Create and boot the provider
    $provider = new HooksProvider();
    $provider->boot($app);
    
    // Add an explicit assertion to avoid the test being marked as risky
    expect($provider)->toBeInstanceOf(HooksProvider::class);
    
    // Verify our expectations
    Mockery::close();
});

test('can register listeners with app slug placeholder', function () {
    $app = app();
    $provider = new HooksProvider();
    
    // Register the provider
    $provider->register($app);
    
    // Set app slug
    $app->config()->set('slug', 'testapp');
    
    // Set up config with a listener that uses {app} placeholder
    $app->config()->set('listeners', [AppSlugListener::class]);
    $app->bind(AppSlugListener::class, AppSlugListener::class);
    
    // Boot the provider
    $provider->boot($app);
    
    // Get the hooks service
    $hooks = $app->get(HooksInterface::class);
    
    // Verify the listener was registered with replaced app slug
    expect($hooks->has('testapp.hook'))->toBeTrue();
});

test('throws exception for listener without handle method', function () {
    $app = app();
    $provider = new HooksProvider();
    
    // Register the provider
    $provider->register($app);
    
    // Set up config with an invalid listener
    $app->config()->set('listeners', [InvalidListener::class]);
    $app->bind(InvalidListener::class, InvalidListener::class);
    
    // Boot should throw exception
    expect(fn() => $provider->boot($app))->toThrow(
        Exception::class,
        'Listener ' . InvalidListener::class . ' must implement handle method'
    );
}); 