---
sidebar_position: 2
---

# Configuration

Configuration in Orkestra is handled through the `ConfigurationInterface` and can be accessed through the `App` instance or directly via the interface.

## Configuration Interface

The `ConfigurationInterface` provides methods to manage configuration values:

```php
interface ConfigurationInterface
{
    public function validate(): bool;
    public function set(string $key, mixed $value): self;
    public function get(string $key): mixed;
    public function has(string $key): bool;
}
```

## Using Configuration

### Through App Instance

```php
use Orkestra\App;

class MyService
{
    public function __construct(
        protected App $app
    ) {}

    public function someMethod()
    {
        $value = $this->app->config()->get('my.config.key');
    }
}
```

### Through Configuration Interface

```php
use Orkestra\Interfaces\ConfigurationInterface;

class MyService
{
    public function __construct(
        protected ConfigurationInterface $config
    ) {}

    public function someMethod()
    {
        $value = $this->config->get('my.config.key');
    }
}
```

## Configuration Files

Configuration file are stored in the `config/app.php` by default. This file should return an array of configuration values, including the providers:

```php
// config/app.php
return [
    'name' => 'Orkestra',
    'debug' => false,
    'providers' => [
        // Service providers
    ],
];
```

For larger applications we recommend that you split your configuration in multiple files:

```php
// config/app.php
return [
    'name' => 'Orkestra',
    'debug' => false,
    'providers' => [
        // Service providers
    ],
    ...require __DIR__ . '/database.php',
    ...require __DIR__ . '/cache.php',
    ...require __DIR__ . '/mail.php',
];
```

```php
// config/database.php
return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'host' => 'localhost',
            'database' => 'orkestra',
            'username' => 'root',
            'password' => '',
        ],
    ],
];
```

```php
// config/cache.php
return [
    'default' => 'file',
    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache'),
        ],
        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
        ],
    ],
];
```

```php
// config/mail.php
return [
    'default' => 'smtp',
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => 'smtp.mailtrap.io',
            'port' => 2525,
            'username' => null,
            'password' => null,
        ],
    ],
];
```

## Setting Configuration Values

Configuration values can be set in two ways: through configuration files or programmatically using the `set` method.

### Using Service Providers

Service providers should only define validation rules and definitions for their own service-specific configurations. This keeps the configuration modular and maintainable:

```php
use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;

class DatabaseServiceProvider implements ProviderInterface
{
    public function register(App $app): void
    {
        // Define validation rules for database-specific configuration
        $app->config()->set('validation', [
            'database.host' => fn ($value) => filter_var($value, FILTER_VALIDATE_IP) ? true : 'Invalid database host',
            'database.port' => fn ($value) => is_numeric($value) && $value > 0 && $value <= 65535 
                ? true 
                : 'Port must be a number between 1 and 65535',
        ]);

        // Define configuration with descriptions and defaults for database
        $app->config()->set('definition', [
            'database.host' => ['Database server hostname', 'localhost'],
            'database.port' => ['Database server port', 3306],
            'database.name' => ['Database name', 'orkestra'],
        ]);
    }
}
```

This approach has several benefits:

1. Each provider is responsible for its own configuration definition
2. Configuration is modular and easier to maintain
3. Providers can be added or removed without affecting other configurations
4. Clear separation of concerns
5. Easier to test and debug configuration issues

### Validation Rules

Validation rules are callables that return either:

- `true` if the value is valid
- A string message explaining why the value is invalid

```php
$config->set('validation', [
    // Simple type validation
    'app_name' => fn ($value) => is_string($value) ? true : 'App name must be a string',
    
    // Complex validation with multiple conditions
    'email' => function ($value) {
        if (!is_string($value)) {
            return 'Email must be a string';
        }
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return 'Invalid email format';
        }
        return true;
    },
    
    // Validation with custom logic
    'port' => fn ($value) => is_numeric($value) && $value > 0 && $value <= 65535 
        ? true 
        : 'Port must be a number between 1 and 65535',
]);
```

### Configuration Definitions

#### **All configuration values must be defined in some provider in order to load the application**

Configuration definitions provide metadata about each configuration value:

- First element: Description of the configuration
- Second element: Default value (optional)

```php
$config->set('definition', [
    // Simple configuration with description and default
    'app_name' => ['The name of your application', 'Orkestra'],
    
    // Configuration without default value (required)
    'api_key' => ['Your API key for external service'],
]);
```

You can get all defined configurations in your app by running:

```bash
maestro app:config:list
```

## Validating Configuration

The `validate` method ensures all required configuration values are present and valid. This method runs automatically before providers boot process

### Validation Best Practices

1. Always define validation rules for critical configuration
2. Provide meaningful error messages
3. Use type hints in validation callables
4. Handle edge cases in validation rules
5. Document validation requirements

## Best Practices

1. Keep configuration organized in separate files
2. Use descriptive keys for configuration values
3. Provide default values when possible
4. Document configuration options
5. Use type hints for configuration values
6. Follow naming conventions

## Related Topics

- [Service Providers](/docs/guides/providers) - Learn how to register configuration in providers
- [Application Structure](/docs/getting-started/installation) - Understand the application structure
