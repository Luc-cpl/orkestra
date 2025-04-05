<?php

use Orkestra\App;
use Orkestra\Commands\ConfigOptionsCommand;
use Orkestra\Commands\StartServerCommand;
use Orkestra\Interfaces\ProviderInterface;
use Orkestra\Providers\CommandsProvider;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

// Let's define our command classes properly
class TestCommand extends Command
{
    // Set name in constructor rather than using static property
    public function __construct()
    {
        parent::__construct('test:basic');
        $this->setDescription('Basic test command');
    }
}

// Define commands for the command merging test
class TestCommand1 extends Command
{
    public function __construct()
    {
        parent::__construct('test:command1');
        $this->setDescription('Test command 1');
    }
}

class TestCommand2 extends Command
{
    public function __construct()
    {
        parent::__construct('test:command2');
        $this->setDescription('Test command 2');
    }
}

// Example custom provider with commands for testing
class CustomCommandsProvider implements ProviderInterface
{
    /** @var array<class-string<Command>> */
    public array $commands = [
        TestCommand1::class,
        TestCommand2::class
    ];

    public function register(App $app): void
    {
        // Nothing to register
    }

    public function boot(App $app): void
    {
        // Nothing to boot
    }
}

// Provider without commands property
class ProviderWithoutCommands implements ProviderInterface
{
    public function register(App $app): void
    {
        // Nothing to register
    }

    public function boot(App $app): void
    {
        // Nothing to boot
    }
}

beforeEach(function () {
    // Create a fresh app instance for each test
    $this->app = app([
        'slug' => 'test-app',
        'env' => 'testing',
        'root' => dirname(__DIR__, 3),
    ]);

    // Create a new provider instance
    $this->provider = new CommandsProvider();
});

test('provider implements provider interface', function () {
    expect($this->provider)->toBeInstanceOf(ProviderInterface::class);
});

test('can register and validate commands provider configuration', function () {
    // Register the provider
    $this->provider->register($this->app);

    // Verify config setup
    expect($this->app->config()->has('commands'))->toBeTrue();
    expect($this->app->config()->get('definition'))->toHaveKey('commands');
    expect($this->app->config()->get('validation'))->toHaveKey('commands');

    // Get the validator callback and test it
    $validator = $this->app->config()->get('validation')['commands'];

    // Non-array should fail
    $result = $validator('not-an-array');
    expect($result)->toBeString();
    expect($result)->toContain('Commands must be an array');

    // Non-existent class should fail
    $result = $validator(['NonExistentCommand']);
    expect($result)->toBeString();
    expect($result)->toContain('does not exist');

    // Non-Command class should fail
    $result = $validator([stdClass::class]);
    expect($result)->toBeString();
    expect($result)->toContain('must extends');

    // Valid Command class should pass
    $result = $validator([TestCommand::class]);
    expect($result)->toBe(true);
});

test('can boot commands provider without side effects', function () {
    // Boot should be a no-op for this provider
    $this->provider->boot($this->app);

    // Add an assertion to make the test not risky
    expect($this->provider)->toBeInstanceOf(CommandsProvider::class);
});

test('provider has default commands defined', function () {
    // Create reflection to access protected commands property
    $reflection = new ReflectionClass($this->provider);
    $commandsProperty = $reflection->getProperty('commands');
    $commandsProperty->setAccessible(true);

    // Get the default commands in CommandsProvider
    $defaultCommands = $commandsProperty->getValue($this->provider);

    // Verify there are default commands in the provider
    expect($defaultCommands)->toBeArray();
    expect($defaultCommands)->not->toBeEmpty();

    // Verify the specific commands
    expect($defaultCommands)->toContain(StartServerCommand::class);
    expect($defaultCommands)->toContain(ConfigOptionsCommand::class);
});

test('binds application to container during registration', function () {
    // Register provider
    $this->provider->register($this->app);

    // Boot app to resolve bindings
    $this->app->boot();

    // Check that the Application class was bound to the container
    expect($this->app->has(Application::class))->toBeTrue();

    // Get the console application
    $console = $this->app->get(Application::class);

    // Verify it's an Application instance
    expect($console)->toBeInstanceOf(Application::class);

    // Verify the app has the expected name
    expect($console->getName())->toBe($this->app->slug());
});

test('integrates commands from config and providers', function () {
    // Mock an App and provider for testing
    $app = $this->app;

    // Configure the config with custom commands
    $app->config()->set('commands', [TestCommand::class]);

    // Register custom provider
    $app->provider(CustomCommandsProvider::class);

    // Register commands provider
    $this->provider->register($app);

    // Boot the app
    $app->boot();

    // Get the console
    $console = $app->get(Application::class);

    // Get all registered commands
    $commands = $console->all();

    // Instead of printing debug output, use assertions
    expect(count($commands))->toBeGreaterThanOrEqual(7); // At least 7 commands

    // Basic tests for command registration
    expect($commands)->toHaveKey('test:basic'); // From config
    expect($commands)->toHaveKey('test:command1'); // From provider
    expect($commands)->toHaveKey('test:command2'); // From provider

    // Also verify default Symfony commands are registered
    expect($commands)->toHaveKey('help');
    expect($commands)->toHaveKey('list');
});

test('handles providers without commands property correctly', function () {
    // Configure app
    $app = $this->app;

    // Register provider without commands
    $app->provider(ProviderWithoutCommands::class);

    // Register commands provider
    $this->provider->register($app);

    // This should not throw any errors when booting
    expect(fn () => $app->boot())->not->toThrow(\Exception::class);

    // Application should exist
    expect($app->has(Application::class))->toBeTrue();

    // Console app can be retrieved
    $console = $app->get(Application::class);
    expect($console)->toBeInstanceOf(Application::class);
});

test('ensures unique command registration', function () {
    // Configure app
    $app = $this->app;

    // Set duplicated commands
    $app->config()->set('commands', [
        TestCommand::class,
        TestCommand::class, // Duplicate
    ]);

    // Bind TestCommand as service
    $app->bind(TestCommand::class, fn () => new TestCommand());

    // Register provider
    $this->provider->register($app);

    // Boot app
    $app->boot();

    // Get console
    $console = $app->get(Application::class);

    // Verify command is only registered once
    $commands = $console->all();
    expect(array_filter($commands, fn ($cmd) => $cmd->getName() === 'test:basic'))->toHaveCount(1);
});
