---
sidebar_position: 4
---

# Troubleshooting Orkestra Applications

This guide covers common issues you might encounter while developing with Orkestra and provides solutions based on real-world scenarios. The troubleshooting tips are organized by component and error type.

## Container-Related Issues

### `NotFoundExceptionInterface`

This exception is thrown when attempting to retrieve a service that doesn't exist in the container.

```php
// This will throw NotFoundExceptionInterface if 'nonExistentKey' isn't registered
$app->get('nonExistentKey');
```

> **Important**: The examples in this guide use the `$app` variable to represent an Orkestra App instance. The `app()` function is only available in the testing environment and should not be used in your actual application code.

**Solution:**
- Check that the service is properly registered in a service provider
- Ensure the provider is registered in your application configuration
- Use `$app->has('service')` to check if a service exists before attempting to retrieve it

### `InvalidArgumentException` (Binding Non-Closures)

This exception is thrown when attempting to bind a value that is not a closure:

```php
// This will throw InvalidArgumentException
$app->bind('test', 'testValue');
```

**Solution:**
- Always bind closures to the container:
```php
$app->bind('test', fn() => 'testValue');
```

### `InvalidArgumentException` (Non-Existent Provider Class)

This exception is thrown when attempting to register a provider class that doesn't exist:

```php
// This will throw InvalidArgumentException
$app->provider('NonExistentProviderClass');
```

**Solution:**
- Make sure the provider class exists and is properly imported
- Check namespace and class name for typos
- Verify autoloading configuration

### `InvalidArgumentException` (Invalid Provider Class)

This exception is thrown when attempting to register a class that doesn't implement `ProviderInterface`:

```php
// This will throw InvalidArgumentException
$nonProviderClass = new class () {};
$app->provider($nonProviderClass::class);
```

**Solution:**
- Ensure the class implements the `ProviderInterface`
- Make sure both `register()` and `boot()` methods are properly implemented

### `Exception` (Double Boot)

This exception is thrown when attempting to boot an application that has already been booted:

```php
// This will throw Exception
$app->boot();
$app->boot();
```

**Solution:**
- Only call `boot()` once in your application lifecycle
- If you need to check if the application is booted, use a condition to prevent multiple boots

### `BadMethodCallException` (Accessing Container Before Boot)

This exception is thrown when attempting to retrieve services from the container before the application is booted:

```php
// This will throw BadMethodCallException
$app = new App(new Configuration());
$app->get('test');
```

**Solution:**
- Always boot the application before accessing the container:
```php
$app = new App(new Configuration());
$app->boot();
$app->get('test');
```

## AppBind-Related Issues

### `Exception` (Non-Existent Class)

This exception is thrown when attempting to create an `AppBind` with a non-existent class:

```php
// This will throw Exception
new AppBind('test', 'NonExistentClass');
```

**Solution:**
- Check that the class exists and is properly imported
- Verify class name and namespace for typos

### `Exception` (Method and Property Calls on Non-Class Binds)

These exceptions are thrown when attempting to use methods like `constructor()`, `property()`, or `method()` on a bind that is a closure rather than a class:

```php
// This will throw Exception
$bind = new AppBind('test', fn () => true);
$bind->constructor('testValue1');

// This will throw Exception
$bind = new AppBind('test', fn () => true);
$bind->property('testProperty', 'testValue');

// This will throw Exception
$bind = new AppBind('test', fn () => true);
$bind->method('testMethod', 'testValue1', 'testValue2');
```

**Solution:**
- Only use these methods with class binds, not closures
- If you need to configure a service, use a class bind instead of a closure

## Configuration-Related Issues

### `InvalidArgumentException` (Undefined Configuration Key)

This exception is thrown when attempting to retrieve a configuration key that doesn't exist:

```php
// This will throw InvalidArgumentException
$config = new Configuration([]);
$config->get('undefinedKey');
```

**Solution:**
- Check if the key exists before getting it:
```php
if ($config->has('key')) {
    $value = $config->get('key');
}
```
- Provide default values in your configuration definitions

### `InvalidArgumentException` (Required Configuration Key)

This exception is thrown when attempting to get a required configuration key that is not set:

```php
// This will throw InvalidArgumentException
$config = new Configuration([
    'definition' => [
        'requiredKey' => ['description', null],
    ],
]);
$config->get('requiredKey');
```

**Solution:**
- Always set required configuration keys before attempting to retrieve them
- Use environment variables or configuration files to provide values for required keys

### `InvalidArgumentException` (Validation Failures)

This exception is thrown when configuration values fail validation:

```php
// This will throw InvalidArgumentException
$config = new Configuration([
    'key' => 'invalidValue',
    'definition' => [
        'key' => ['description', 'validValue'],
    ],
    'validation' => [
        'key' => fn ($value) => $value === 'validValue',
    ],
]);
$config->validate();
```

**Solution:**
- Ensure all configuration values meet their validation requirements
- Check validation rules for logical errors
- Use debugging techniques to identify which validation is failing

### `InvalidArgumentException` (Invalid Validation Configuration)

This exception is thrown when setting invalid validation handlers:

```php
// These will all throw InvalidArgumentException
$config->set('validation', 'invalidValidator');
$config->set('validation', ['key' => 'invalidValidator']);
$config->set('validation', [fn () => true]); // Missing key
```

**Solution:**
- Ensure validation handlers are arrays of key-function pairs:
```php
$config->set('validation', [
    'key' => fn ($value) => true // Valid validator
]);
```

### `InvalidArgumentException` (Invalid Definition Configuration)

This exception is thrown when setting invalid definition structures:

```php
// These will all throw InvalidArgumentException
$config->set('definition', 'invalidDefinition');
$config->set('definition', ['key' => []]); // Empty definition
$config->set('definition', ['key' => ['description', 'default', 'invalid extra']]); // Too many elements
```

**Solution:**
- Ensure definitions follow the correct format:
```php
$config->set('definition', [
    'key' => ['Description', 'default'] // Valid definition
]);
```

## Application Configuration Issues

### `InvalidArgumentException` (Invalid Environment)

This exception is thrown when attempting to boot with an invalid environment:

```php
// This will throw InvalidArgumentException
$app->config()->set('env', 'invalidEnv');
$app->boot();
```

**Solution:**
- Use a valid environment name (e.g., 'development', 'production', 'testing')

### `InvalidArgumentException` (Invalid Root Path)

This exception is thrown when attempting to boot with an invalid root path:

```php
// This will throw InvalidArgumentException
$app->config()->set('root', 'invalidRoot');
$app->boot();
```

**Solution:**
- Set a valid directory path as the root
- Ensure the path exists and is readable

### `InvalidArgumentException` (Invalid Slug)

This exception is thrown when attempting to boot with an invalid slug format:

```php
// This will throw InvalidArgumentException
$app->config()->set('slug', 'invalid slug!');
$app->boot();
```

**Solution:**
- Use a valid slug format (alphanumeric characters, hyphens, or underscores)
- Avoid spaces and special characters in your slug

## Debugging Techniques

### Using Test Cases for Debugging

The test suite provides valuable examples of expected behavior and common error cases. Use these as reference when debugging:

```php
// From AppTest.php - Valid slug test
test('can get slug', function () {
    $app = app(); // Note: app() is only used in tests
    expect($app->slug())->toEqual('app');
    $app->config()->set('slug', 'testSlug');
    expect($app->slug())->toEqual('testSlug');
});
```

### Inspecting Container State

Inspect the state of the container to debug service registration issues:

```php
// Check if a service exists
var_dump($app->has('service_name'));

// Dump all registered services (if using PHP-DI directly)
var_dump($app->getContainer()->getKnownEntryNames());
```

### Configuration Debugging

Dump the current configuration state to identify issues:

```php
// Display all configuration values
var_dump($app->config());

// Check specific configuration value
var_dump($app->config()->get('key'));
```

## Performance Issues

### Container Resolution Performance

If you encounter performance issues with container resolution:

1. Use singleton services for frequently accessed services
2. Consider eager loading critical services during boot
3. Profile the application to identify slow service resolutions

### Memory Leaks

If your application experiences memory leaks:

1. Check for circular references in your service definitions
2. Ensure proper cleanup of resources in long-running processes
3. Use weak references for event listeners and observers

## Common Error Patterns and Their Solutions

| Error Pattern | Symptom | Solution |
|---------------|---------|----------|
| Missing Service | `NotFoundExceptionInterface` when calling `$app->get()` | Register the service in a provider |
| Invalid Binding | `InvalidArgumentException` when calling `$app->bind()` | Use closures instead of values |
| Configuration Validation | `InvalidArgumentException` during boot | Ensure all required config values are set and valid |
| Container Usage Before Boot | `BadMethodCallException` | Always boot the app before accessing container |
| Circular Dependencies | Infinite loop or stack overflow | Refactor dependencies, use factories or decorators |
| Provider Registration | `InvalidArgumentException` | Ensure provider class exists and implements the interface |

## Related Topics

- [Testing Guide](/docs/advanced-topics/testing) - Learn how to test your Orkestra application
- [Dependency Injection](/docs/core-concepts/dependency-injection) - Understanding the container 