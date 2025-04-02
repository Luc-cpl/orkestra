---
sidebar_position: 2
---

# Service Providers

Service providers are the central place for all application bootstrapping. They are responsible for binding things into the service container, registering services, and booting various components.

## Provider Interface

The `ProviderInterface` defines the core methods that all providers must implement:

```php
use Orkestra\App;

interface ProviderInterface
{
    public function register(App $app): void;
    public function boot(App $app): void;
}
```

## Creating a Provider

```php
namespace App\Providers;

use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;
use App\Services\PaymentService;
use App\Interfaces\PaymentServiceInterface;

class PaymentServiceProvider implements ProviderInterface
{
    public function register(App $app): void
    {
        // Register services in the container
        $app->bind(PaymentServiceInterface::class, PaymentService::class);

        // Register a service with a custom factory
        $app->bind('payment.config', function() use ($app) {
            return [
                'api_key' => $_ENV['PAYMENT_API_KEY'],
                'secret' => $_ENV['PAYMENT_SECRET'],
                'sandbox' => $app->config()->get('env') !== 'production'
            ];
        });
    }

    public function boot(App $app): void
    {
        // Initialize the payment service if needed
        $service = $app->get(PaymentServiceInterface::class);
        $service->initialize();
    }
}
```

## Provider Lifecycle

Providers follow a specific lifecycle within the application:

1. **Registration**: Providers are registered with the application using `$app->provider()` or through configuration
2. **Register Phase**: The `register()` method is called on all providers
3. **Boot Phase**: After all providers are registered, the `boot()` method is called on each provider in the order they were registered

This separation allows providers to depend on services registered by other providers during the boot phase.

## Registering Providers

Service providers are registered in your `config/app.php` configuration file:

```php
return [
    'providers' => [
        // Framework Service Providers
        Orkestra\Providers\CommandsProvider::class,
        Orkestra\Providers\HooksProvider::class,
        Orkestra\Providers\HttpProvider::class,
        Orkestra\Providers\ViewProvider::class,

        // Application Service Providers
        App\Providers\AuthServiceProvider::class,
        App\Providers\PaymentServiceProvider::class,
    ],
];
```

You can also register providers manually in your application bootstrap code:

```php
// Create the application
$app = new Orkestra\App(new Orkestra\Configuration());

// Register a provider
$app->provider(MyServiceProvider::class);

// Register multiple providers
$app->provider(CommandsProvider::class);
$app->provider(HooksProvider::class);
$app->provider(HttpProvider::class);
$app->provider(ViewProvider::class);
```

## Provider Registration Rules

When registering providers, be aware of these important rules:

1. **Provider Must Exist**: The provider class must exist, or an `InvalidArgumentException` will be thrown.

```php
// This will throw InvalidArgumentException
$app->provider('NonExistentProvider');
```

2. **Provider Must Implement Interface**: The class must implement the `ProviderInterface`, or an `InvalidArgumentException` will be thrown.

```php
// This will throw InvalidArgumentException
$nonProviderClass = new class () {};
$app->provider($nonProviderClass::class);
```

3. **Registration Order Matters**: Providers are booted in the order they were registered. If Provider B depends on services from Provider A, register A first.

## Service Container Integration

Providers are themselves registered in the container, allowing them to be retrieved later:

```php
// Register a provider
$app->provider(LogServiceProvider::class);

// Get the provider instance from the container
$provider = $app->get(LogServiceProvider::class);
```

This is particularly useful for providers that maintain state or expose configuration:

```php
class LogServiceProvider implements ProviderInterface
{
    public array $channels = [];
    
    public function register(App $app): void
    {
        // Registration logic
    }
    
    public function boot(App $app): void
    {
        $this->channels = [
            'file' => new FileLogger(),
            'database' => new DatabaseLogger(),
        ];
    }
    
    public function getChannel(string $name)
    {
        return $this->channels[$name] ?? null;
    }
}

// Later in your application
$logProvider = $app->get(LogServiceProvider::class);
$fileLogger = $logProvider->getChannel('file');
```

## Service Registration Patterns

### Basic Service Binding

```php
public function register(App $app): void
{
    // Bind a class with autowiring (automatically resolve dependencies)
    $app->bind(UserService::class, UserService::class);
    
    // Bind an interface to an implementation
    $app->bind(UserRepositoryInterface::class, UserRepository::class);
    
    // Bind a closure factory
    $app->bind('logger', function() {
        return new Logger($_ENV['LOG_LEVEL'] ?? 'info');
    });
}
```

### Complex Service Configuration

For more complex service configuration, use the `AppBind` class:

```php
public function register(App $app): void
{
    // Create a bind and configure it
    $bind = new AppBind('mailer', MailerService::class);
    
    // Set constructor parameters
    $bind->constructor('smtp', $_ENV['SMTP_HOST']);
    
    // Set public properties
    $bind->property('debug', $_ENV['APP_DEBUG'] ?? false);
    
    // Call methods during instantiation
    $bind->method('addChannel', 'email');
    $bind->method('addRecipient', $_ENV['ADMIN_EMAIL']);
}
```

### Service Decoration

Service decoration allows you to wrap or modify a service without changing its core implementation:

```php
public function register(App $app): void
{
    // Register the base service
    $app->bind(CacheInterface::class, FileCache::class);
    
    // Decorate it with a Redis cache if available
    if (extension_loaded('redis')) {
        $app->decorate(CacheInterface::class, function($fileCache) {
            return new RedisCache($fileCache); // Fallback to file cache if Redis fails
        });
    }
    
    // Add logging decoration in development
    if ($app->config()->get('env') === 'development') {
        $app->decorate(CacheInterface::class, function($cache) {
            return new LoggingCacheDecorator($cache);
        });
    }
}
```

## Common Provider Types

### Feature Providers

Encapsulate a specific feature or module:

```php
class PaymentProvider implements ProviderInterface
{
    public function register(App $app): void
    {
        $app->bind(PaymentGatewayInterface::class, StripeGateway::class);
        $app->bind(SubscriptionServiceInterface::class, SubscriptionService::class);
    }
    
    public function boot(App $app): void
    {
        // Initialize payment webhooks
    }
}
```

### Infrastructure Providers

Provide infrastructure services like logging, caching, database connections:

```php
class DatabaseProvider implements ProviderInterface
{
    public function register(App $app): void
    {
        $app->bind('db', function() use ($app) {
            $config = $app->config()->get('database');
            return new DatabaseConnection($config);
        });
        
        $app->bind(QueryBuilderInterface::class, QueryBuilder::class);
    }
    
    public function boot(App $app): void
    {
        // Set up database connection pool
    }
}
```

### Integration Providers

Integrate external services or libraries:

```php
class AwsProvider implements ProviderInterface
{
    public function register(App $app): void
    {
        $app->bind('aws.config', function() {
            return [
                'key' => $_ENV['AWS_KEY'],
                'secret' => $_ENV['AWS_SECRET'],
                'region' => $_ENV['AWS_REGION'] ?? 'us-east-1',
            ];
        });
        
        $app->bind(S3ClientInterface::class, function() use ($app) {
            $config = $app->get('aws.config');
            return new S3Client($config);
        });
    }
    
    public function boot(App $app): void
    {
        // Initialize AWS SDK
    }
}
```

## Testing Providers

When testing providers, focus on verifying that services are properly registered and initialized:

```php
test('payment provider registers services', function () {
    // Create a fresh app instance
    $app = new App(new Configuration());
    
    // Register the provider
    $app->provider(PaymentProvider::class);
    
    // Boot the app
    $app->config()->set('env', 'development');
    $app->config()->set('root', './');
    $app->boot();
    
    // Verify services were registered
    expect($app->has(PaymentGatewayInterface::class))->toBeTrue();
    expect($app->get(PaymentGatewayInterface::class))->toBeInstanceOf(PaymentGateway::class);
});
```

## Best Practices

1. **Keep Providers Focused**: Each provider should have a single responsibility. Create separate providers for distinct features.

2. **Register in register(), Initialize in boot()**: Use the `register()` method for binding services and the `boot()` method for initializing them. Avoid using the `boot()` method and **NEVER** get services or configuration from the container in the `register()` method directly (you can use from callable bindings, configuration definitions, or callable bindings constructors arguments).

3. **Avoid Complex Logic in Providers**: Providers should mainly wire up services, not contain business logic.

4. **Use Interface Bindings**: Bind interfaces to implementations for better testability and loose coupling.

5. **Document Provider Behavior**: Include clear documentation about what services a provider registers and initializes.

6. **Test Provider Registration**: Write tests to ensure providers correctly register and initialize their services.

7. **Use Configuration Definitions**: Make your services adaptable to different environments by using configuration definitions.

## Related Topics

- [Dependency Injection](/docs/core-concepts/dependency-injection) - Understand how the service container works
- [Application Lifecycle](/docs/core-concepts/app-lifecycle) - Learn about the application bootstrap process
- [Configuration](/docs/core-concepts/configuration-management) - Managing application configuration
- [Testing](/docs/advanced-topics/testing) - Testing Orkestra applications 