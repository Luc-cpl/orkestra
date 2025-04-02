---
sidebar_position: 3
---

# Configuration Management

Orkestra provides a robust configuration system that allows you to define, validate, and manage application settings. This guide covers all aspects of the configuration system.

## Configuration Basics

The `Configuration` class is the central component of Orkestra's configuration system:

```php
use Orkestra\Configuration;

// Create a new configuration instance
$config = new Configuration([
    'key' => 'value',
    'anotherKey' => 'anotherValue'
]);
```

### Setting and Getting Values

```php
// Set a value
$config->set('key', 'value');

// Get a value
$value = $config->get('key'); // Returns 'value'

// Check if a key exists
if ($config->has('key')) {
    // Key exists
}
```

## Configuration Definitions

Definitions provide structure and documentation for your configuration values:

```php
$config = new Configuration([
    'definition' => [
        'db_host' => ['Database hostname', 'localhost'],
        'db_port' => ['Database port', 3306],
        'api_key' => ['API key for external service', null], // Required value
    ]
]);
```

Each definition consists of:

- A descriptive label explaining the purpose of the configuration
- A default value (or `null` for required values)

> **Important**: Orkestra's current implementation only supports flat configuration definitions. Nested definitions (definitions for nested arrays or objects) are not directly supported.

### Accessing Default Values

When retrieving a value, if the key doesn't exist but has a definition with a default, the default is returned:

```php
// Even if 'db_host' isn't explicitly set, it will return 'localhost'
$dbHost = $config->get('db_host');
```

### Required Values

If a configuration key is defined with a `null` default, it's considered required. Attempting to retrieve a required value without setting it first will throw an exception:

```php
// This will throw an InvalidArgumentException if 'api_key' isn't set
$apiKey = $config->get('api_key');
```

## Working with Complex Values

While definitions are flat, the configuration values themselves can be complex objects or arrays:

```php
// Set a complex value
$config->set('database', [
    'host' => 'localhost',
    'port' => 3306,
    'credentials' => [
        'username' => 'user',
        'password' => 'password'
    ]
]);

// Get the complex value
$database = $config->get('database');
$host = $database['host'];
$username = $database['credentials']['username'];
```

To define and validate complex values, use a flat definition with a validation function:

```php
$config = new Configuration([
    'definition' => [
        'database' => ['Database configuration', []], // Default is empty array
    ],
    'validation' => [
        'database' => function ($value) {
            // Validate database configuration
            if (!is_array($value)) return false;
            
            // Check required fields
            if (!isset($value['host']) || !isset($value['credentials'])) {
                return false;
            }
            
            // Check credentials
            if (!isset($value['credentials']['username']) || 
                !isset($value['credentials']['password'])) {
                return false;
            }
            
            return true;
        }
    ]
]);
```

## Configuration Validation

Orkestra's configuration system includes powerful validation capabilities:

```php
$config = new Configuration([
    'db_port' => 3306,
    'definition' => [
        'db_port' => ['Database port', 3306],
    ],
    'validation' => [
        'db_port' => fn ($value) => is_int($value) && $value > 0,
    ]
]);

// Validates all configuration values
$config->validate(); // Returns true if all validations pass
```

### Custom Validators

You can define custom validation functions for your configuration values:

```php
$config = new Configuration([]);

$config->set('validation', [
    'api_url' => function ($value) {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    },
    'email' => function ($value) {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
]);

$config->set('api_url', 'https://api.example.com');
$config->set('email', 'user@example.com');
$config->validate(); // Returns true
```

### Validation on Boot

Configuration validation typically happens during the application boot process:

```php
// In a service provider
public function register(App $app): void
{
    $app->config()->set('api_key', '1234567890');
}

// In App::boot()
$app->config()->validate(); // Will throw if any validations fail
```

## Advanced Configuration Patterns

### Environment-Specific Configuration

You can load different configuration values based on the environment:

```php
$env = $_ENV['APP_ENV'] ?? 'development';

// Load base config
$config = new Configuration([
    // Common configuration
]);

// Load environment-specific config
$envConfig = require "config/{$env}.php";
foreach ($envConfig as $key => $value) {
    $config->set($key, $value);
}
```

### Organization of Complex Configuration

Since nested definitions aren't supported, it's recommended to organize complex configurations using distinct top-level keys:

```php
// Instead of nested definitions for database configuration
// Use separate keys for different components

// config/database.php
return [
    'db_driver' => $_ENV['DB_DRIVER'] ?? 'mysql',
    'db_host' => $_ENV['DB_HOST'] ?? 'localhost',
    'db_port' => (int)($_ENV['DB_PORT'] ?? 3306),
    'db_name' => $_ENV['DB_NAME'],
    'db_user' => $_ENV['DB_USER'],
    'db_password' => $_ENV['DB_PASSWORD'],
];

// In bootstrap file
$app->config()->set('database', require __DIR__ . '/config/database.php');

// Define validation for the entire database config
$app->config()->set('validation', [
    'database' => function ($value) {
        return isset($value['db_host']) && isset($value['db_name']);
    }
]);

// Or use an array of values for more complex structures
$app->config()->set('services', [
    'email' => [
        'driver' => 'smtp',
        'host' => 'smtp.example.com'
    ],
    'payment' => [
        'provider' => 'stripe',
        'key' => 'sk_test_123'
    ]
]);
```

## Common Error Cases and Troubleshooting

### Undefined Configuration Keys

Attempting to get an undefined configuration key will throw an exception:

```php
$config = new Configuration([]);
$config->get('undefinedKey'); // Throws InvalidArgumentException
```

Solution: Always check if a key exists before attempting to get its value:

```php
if ($config->has('key')) {
    $value = $config->get('key');
}
```

### Invalid Validation Handlers

Setting invalid validation handlers will throw an exception:

```php
$config = new Configuration([]);

// These will all throw InvalidArgumentException
$config->set('validation', 'invalidValidator');
$config->set('validation', ['key' => 'invalidValidator']);
$config->set('validation', [fn () => true]); // Missing key
```

Solution: Ensure validation handlers are arrays of key-function pairs:

```php
$config->set('validation', [
    'key' => fn ($value) => true // Valid validator
]);
```

### Invalid Definitions

Setting invalid definitions will throw an exception:

```php
$config = new Configuration([]);

// These will all throw InvalidArgumentException
$config->set('definition', 'invalidDefinition');
$config->set('definition', ['key' => []]); // Empty definition
$config->set('definition', ['key' => ['description', 'default', 'extraValue']]); // Too many elements
```

Solution: Ensure definitions follow the correct format:

```php
$config->set('definition', [
    'key' => ['Description', 'default'] // Valid definition
]);
```

## Best Practices

1. **Use Descriptive Keys**: Use descriptive keys that indicate both the category and the specific setting (e.g., `db_host` instead of just `host`).

2. **Validate Critical Values**: Use validation for all critical configuration values to catch issues early.

3. **Group Related Settings**: Use arrays for complex values but keep the definitions at the top level.

4. **Use Environment Variables**: Load sensitive values from environment variables rather than hardcoding them.

5. **Document Validation Rules**: Include clear documentation about validation rules in your definitions.

## Related Topics

- [Application Lifecycle](/docs/core-concepts/app-lifecycle) - How configuration integrates into the application lifecycle
- [Environment Setup](/docs/getting-started/environment) - Setting up environment-specific configuration 