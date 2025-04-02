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
use Orkestra\Interfaces\ProviderInterface;
use Orkestra\Interfaces\AppContainerInterface;

class MyServiceProvider implements ProviderInterface
{
    public function register(App $app): void
    {
        // Register services
        $app->bind(MyInterface::class, MyService::class);
    }

    public function boot(App $app): void
    {
        // Try to not use this method.
    }
}
```

## Registering Providers

Service providers are registered in your `config/app.php` configuration file:

```php
return [
    'providers' => [
        // Framework Service Providers
        Orkestra\Framework\Providers\AppServiceProvider::class,
        Orkestra\Framework\Providers\RouteServiceProvider::class,

        // Application Service Providers
        App\Providers\ExampleServiceProvider::class,
    ],
];
```

## Best Practices

1. Keep providers focused and single-purpose
2. Use dependency injection
3. Register services in `register()`
4. Initialize services in `boot()`
5. Use service container bindings
6. Document provider behavior
7. Test provider registration
8. Follow naming conventions

## Related Topics

- [Hooks](/docs/guides/hooks) - Learn about application hooks
- [Service Container](/docs/guides/service-container) - Understand dependency injection
- [Configuration](/docs/guides/configuration) - Manage application configuration
- [Commands](/docs/guides/commands) - Create artisan commands 