---
sidebar_position: 1
---

# Application Lifecycle

Understanding the Orkestra application lifecycle is essential for properly leveraging the framework's capabilities. This guide covers the bootstrapping process, various lifecycle stages, and how to hook into these stages in your application.

## Overview

The Orkestra application lifecycle consists of several distinct phases:

1. **Initialization**: The `App` instance is created
2. **Configuration**: Application settings are loaded and validated
3. **Provider Registration**: Service providers are registered
4. **Provider Bootstrapping**: Service providers are booted in sequence
5. **Execution**: The application handles the request and generates a response
6. **Termination**: Resources are released and the application terminates

## Initialization Phase

The application begins with the creation of an `App` instance, which serves as the central container for all services:

```php
use Orkestra\App;
use Orkestra\Configuration;

// Create a new app instance
$app = new App(new Configuration());
```

### Skeleton Repository Bootstrap

If you're using the Orkestra skeleton repository, the initialization is handled for you in the `bootstrap/app.php` file:

```php
// bootstrap/app.php
$app = new \Orkestra\App(new \Orkestra\Configuration(require __DIR__ . '/../config/app.php'));

// Register additional configurations
$app->config()->set('custom_config', require __DIR__ . '/../config/custom.php');

// This helper is only available in the skeleton repository
function app(): \Orkestra\App
{
    global $app;
    return $app;
}

return $app;
```

> **Note**: The `app()` function is only available in the skeleton repository and should not be relied upon in core Orkestra applications. Always use the `$app` instance directly.

## Hooking Into Initialization

To customize the initialization phase, you can modify the bootstrap file or create your own bootstrap process:

```php
// Create a configuration with custom values
$config = new Configuration([
    'env' => $_ENV['APP_ENV'] ?? 'development',
    'root' => __DIR__,
    'slug' => 'my-app'
]);

// Create the app with custom configuration
$app = new App($config);

// Store app instance globally if needed
$GLOBALS['app'] = $app;
```

## Configuration Phase

During this phase, the application loads and validates configuration values. In the skeleton repository, configuration is typically defined in the `config/app.php` file:

```php
// config/app.php
return [
    'env' => $_ENV['APP_ENV'] ?? 'development',
    'root' => dirname(__DIR__),
    'slug' => 'my-app',
    'providers' => [
        // List of service providers
        \Orkestra\Providers\CommandsProvider::class,
        \Orkestra\Providers\HooksProvider::class,
        \App\Providers\AppServiceProvider::class,
    ],
    // Other configuration values
];
```

You can set configuration values manually:

```php
// Set configuration values
$app->config()->set('env', 'development');
$app->config()->set('root', './');
$app->config()->set('slug', 'my-app');

// Configuration values can be validated
$app->config()->validate();
```

## Hooking Into Configuration

To add custom configuration logic:

1. Create additional configuration files:

```php
// config/database.php
return [
    'driver' => $_ENV['DB_DRIVER'] ?? 'mysql',
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'port' => (int)($_ENV['DB_PORT'] ?? 3306),
    'name' => $_ENV['DB_NAME'],
    'user' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASSWORD'],
];

// In bootstrap/app.php
$app->config()->set('database', require __DIR__ . '/../config/database.php');
```

2. Add configuration validation:

```php
// In a service provider's register method
public function register(App $app): void
{
    $app->config()->set('validation', [
        'database' => function ($value) {
            // Validate database configuration
            return isset($value['host']) && isset($value['name']);
        }
    ]);
}
```

### Configuration Validation

Orkestra validates configuration values against defined rules:

```php
// Define configuration schema
$config = new Configuration([
    'definition' => [
        'key1' => ['Description of key1', 'default1'],
        'key2' => ['Description of key2', null], // Required value (null default)
    ],
    'validation' => [
        'key1' => fn ($value) => $value === 'validValue',
    ],
]);

// Set and validate
$config->set('key1', 'validValue');
$config->set('key2', 'someValue');
$config->validate(); // Will throw exception if validation fails
```

## Provider Registration Phase

Service providers are registered with the application. In the skeleton repository, providers are typically listed in the `config/app.php` file:

```php
// config/app.php
return [
    'providers' => [
        \Orkestra\Providers\CommandsProvider::class,
        \Orkestra\Providers\HooksProvider::class,
        \Orkestra\Providers\HttpProvider::class,
        \Orkestra\Providers\ViewProvider::class,
        \App\Providers\AppServiceProvider::class,
    ],
];
```

You can also register providers manually:

```php
// Register providers
$app->provider(MyServiceProvider::class);
$app->provider(AnotherServiceProvider::class);
```

Service providers must implement the `ProviderInterface`:

```php
use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;

class MyServiceProvider implements ProviderInterface
{
    public function register(App $app): void
    {
        // Register services in container
        $app->bind(MyService::class, fn() => new MyService());
    }
    
    public function boot(App $app): void
    {
        // Initialize services, run setup tasks
        $app->get(MyService::class)->initialize();
    }
}
```

## Hooking Into Provider Registration

To customize provider registration:

1. Create custom service providers:

```php
namespace App\Providers;

use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;

class CustomProvider implements ProviderInterface
{
    public function register(App $app): void
    {
        // Registration logic
    }
    
    public function boot(App $app): void
    {
        // Boot logic
    }
}

// Add to config/app.php's providers array
// Or register manually in bootstrap/app.php
$app->provider(\App\Providers\CustomProvider::class);
```

2. Extend existing providers:

```php
namespace App\Providers;

use Orkestra\Providers\HttpProvider;

class ExtendedHttpProvider extends HttpProvider
{
    public function register(App $app): void
    {
        parent::register($app);
        // Additional registration logic
    }
    
    public function boot(App $app): void
    {
        parent::boot($app);
        // Additional boot logic
    }
}

// In config/app.php, replace HttpProvider with ExtendedHttpProvider
```

## Provider Bootstrapping Phase

After registration, providers are booted in sequence. In the skeleton repository, this is typically handled in the public entry point:

```php
// public/index.php
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Boot the application
$app->boot();

// Handle the request
$kernel = $app->get(\Orkestra\Services\Http\Kernel::class);
$response = $kernel->handle($request);
$response->send();
```

You can also boot the application manually:

```php
// Initialize all registered providers
$app->boot();
```

During this phase:

1. The application validates the environment, root path, and slug
2. Each provider's `register()` method is called on all providers first
3. Then each provider's `boot()` method is called in the order they were registered
4. Service dependencies are resolved and ready to use

## Hooking Into Provider Bootstrapping

The primary way to hook into bootstrapping is through provider `boot()` methods:

```php
public function boot(App $app): void
{
    // Access configuration
    $debug = $app->config()->get('app.debug');
    
    // Use hooks (if HooksProvider is available)
    $hooks = $app->get(\Orkestra\Services\Hooks\Interfaces\HooksInterface::class);
    $hooks->addListener('application.booted', function() {
        // Run after all providers are booted
    });
    
    // Initialize services
    $myService = $app->get(MyService::class);
    $myService->initialize();
}
```

## Execution Phase

Once booted, the application handles requests through the appropriate channels (HTTP, CLI, etc.). In the skeleton repository, this is typically handled in the entry point files:

```php
// For HTTP requests (public/index.php)
$kernel = $app->get(\Orkestra\Services\Http\Kernel::class);
$response = $kernel->handle($request);
$response->send();

// For CLI commands (maestro)
$runner = $app->get(\Orkestra\Services\Commands\Runner::class);
$runner->run();
```

## Hooking Into Execution

To customize the execution phase:

1. Create custom controllers or command handlers:

```php
namespace App\Controllers;

use Orkestra\Services\Http\Controllers\AbstractController;

class HomeController extends AbstractController
{
    public function index()
    {
        return $this->view('home', ['title' => 'Welcome']);
    }
}

// Register in routes (config/routes.php)
return [
    'GET /' => 'App\Controllers\HomeController@index'
];
```

2. Use middleware (if HttpProvider is used):

```php
namespace App\Middleware;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class AuthMiddleware
{
    public function handle(ServerRequestInterface $request, Closure $next): ResponseInterface
    {
        // Add middleware logic
        if (!$this->isAuthenticated($request)) {
            return redirect('/login');
        }
        
        return $next($request);
    }
}

// Register in routes or providers
```

## Service Container Usage During Lifecycle

The container is the backbone of the application lifecycle:

```php
// Register services during provider registration
public function register(App $app): void
{
    $app->bind(MyInterface::class, MyImplementation::class);
    
    // Sophisticated bindings
    $app->bind(ComplexService::class, function() {
        $instance = new ComplexService();
        $instance->setLogger(new Logger());
        return $instance;
    });
}

// Use services during the boot phase
public function boot(App $app): void
{
    $service = $app->get(MyInterface::class);
    $service->doSomething();
}
```

## Important Considerations

### Boot Only Once

An application can only be booted once. Attempting to boot multiple times will throw an exception:

```php
$app->boot();
$app->boot(); // Throws Exception
```

### Service Access Before Boot

You cannot access services from the container before the application is booted:

```php
$app = new App(new Configuration());
$app->get('service'); // Throws BadMethodCallException

// Correct approach
$app->boot();
$app->get('service'); // Works after booting
```

### Configuration Validation

Invalid configurations will throw exceptions during boot:

```php
// Invalid environment
$app->config()->set('env', 'invalidEnv');
$app->boot(); // Throws InvalidArgumentException

// Invalid root path
$app->config()->set('root', 'invalidRoot');
$app->boot(); // Throws InvalidArgumentException

// Invalid slug format
$app->config()->set('slug', 'invalid slug!');
$app->boot(); // Throws InvalidArgumentException
```

## Lifecycle Diagram

```
┌─────────────────┐      ┌─────────────────┐      ┌─────────────────┐
│  Initialization │ ──▶  │  Configuration  │ ──▶  │    Provider     │
│                 │      │                 │      │  Registration   │
└─────────────────┘      └─────────────────┘      └─────────────────┘
         │                                                 │
         │                                                 ▼
┌─────────────────┐      ┌─────────────────┐      ┌─────────────────┐
│   Termination   │ ◀──  │    Execution    │ ◀──  │    Provider     │
│                 │      │                 │      │  Bootstrapping  │
└─────────────────┘      └─────────────────┘      └─────────────────┘
```

## Best Practices

1. **Register in Register, Initialize in Boot**: Use the `register()` method for binding services and the `boot()` method for initializing them.

2. **Keep Providers Focused**: Each provider should have a specific responsibility.

3. **Validate Configuration Early**: Use configuration validation to catch issues before they cause runtime errors.

4. **Avoid Circular Dependencies**: Be careful not to create circular dependencies between providers.

5. **Leverage Configuration Files**: Keep configuration in dedicated files (like `config/app.php`, `config/database.php`) rather than setting values directly in code.

## Related Topics

- [Service Providers](/docs/guides/providers) - Deep dive into creating and using service providers
- [Dependency Injection](/docs/core-concepts/dependency-injection) - Understand the service container 