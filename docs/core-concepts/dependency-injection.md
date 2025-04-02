---
sidebar_position: 2
---

# Dependency Injection

Orkestra includes a powerful Dependency Injection (DI) container built on PHP-DI, providing a clean and efficient way to manage class dependencies. This guide covers the core DI patterns used in Orkestra applications.

## Basic Container Usage

The container is the central registry for all your application's services and dependencies. Here's how to use it:

### Accessing the Application Instance

In Orkestra applications, you need to work with the application instance directly:

```php
// In your application where you have access to the $app instance
$app = new Orkestra\App($config);

// If you're using the Orkestra skeleton, App\app() helper is available
$app = App\app();
```

### Accessing the Application Instance in Tests

In your tests you can extend the test UseCase class with the `Orkestra\Testing\Traits\HasApplicationTrait` trait:

```php
use Orkestra\Testing\Traits\HasApplicationTrait;

class MyTestCase extends TestCase
{
    use HasApplicationTrait;
}
```

This will add some helper functions to help you access the application instance and other services during testing (very useful for feature tests):

- `app()` - Get the application instance
- `factory()` - Get the entity factory instance
- `request()` - Simulates a API request
- `middleware()` - Create a middleware testing instance
- `generateRequest()` - Generate a request instance

### Binding Services

```php
// Bind a closure
$app->bind('service_name', fn() => 'service_value');

// Bind a class by name (with autowiring)
$app->bind('service_name', MyClass::class);

// Bind a class instance directly
$instance = new MyClass();
$app->bind('service_name', $instance);

// Bind an interface to an implementation
$app->bind(MyInterface::class, MyImplementation::class);
```

### Retrieving Services

```php
// Get a service from the container
$service = $app->get('service_name');

// Make a new instance with parameters
$service = $app->make('service_name', ['param' => 'value']);
```

### Checking Service Availability

```php
// Check if a service exists in the container
if ($app->has('service_name')) {
    // Service exists
}

// Run a callback only if a service is available
$result = $app->runIfAvailable(MyClass::class, function ($instance) {
    return $instance->doSomething();
});
```

## Advanced Container Features

### Service Decoration

Decoration allows you to modify or extend a service without changing its original implementation:

```php
// Decorate an existing service
$app->bind(MyService::class, fn() => new MyService());
$app->decorate(MyService::class, function($service) {
    return new MyServiceDecorator($service);
});

// Decorate before binding (order doesn't matter)
$app->decorate(MyInterface::class, function($service) {
    return new MyDecorator($service);
});
$app->bind(MyInterface::class, MyImplementation::class);
```

### AppBind for Complex Bindings

The `AppBind` class provides more control over service definition:

```php
// Create a bind with constructor parameters
$bind = new AppBind('service_name', MyClass::class);
$bind->constructor('param1', 'param2');

// Set properties on the service
$bind = new AppBind('service_name', MyClass::class);
$bind->property('propertyName', 'propertyValue');

// Inject method parameters
$bind = new AppBind('service_name', MyClass::class);
$bind->method('methodName', 'param1', 'param2');
```

## Best Practices

1. **Favor Interface Bindings**: Bind interfaces to implementations for better testability and loose coupling.

```php
interface UserRepositoryInterface { /* ... */ }
class UserRepository implements UserRepositoryInterface { /* ... */ }

$app->bind(UserRepositoryInterface::class, UserRepository::class);
```

2. **Use Autowiring**: Let the container resolve dependencies automatically when possible.

3. **Contextual Binding**: Configure different implementations for the same interface in different contexts.

4. **Avoid Container in Business Logic**: Keep the container usage at the composition root level.

5. **Use Decoration for Cross-Cutting Concerns**: Logging, caching, and authorization are great use cases for decoration.

## Real-World Examples

### Example 1: Repository Pattern with DI

```php
interface UserRepositoryInterface {
    public function findById(int $id): ?User;
}

class UserRepository implements UserRepositoryInterface {
    public function findById(int $id): ?User {
        // Implementation...
    }
}

class UserService {
    private UserRepositoryInterface $repository;
    
    public function __construct(UserRepositoryInterface $repository) {
        $this->repository = $repository;
    }
    
    public function getUser(int $id): ?User {
        return $this->repository->findById($id);
    }
}

// In a service provider:
public function register(App $app): void {
    $app->bind(UserRepositoryInterface::class, UserRepository::class);
    $app->bind(UserService::class, UserService::class);
}
```

### Example 2: Service Decoration for Caching

```php
$app->bind(UserRepositoryInterface::class, UserRepository::class);

$app->decorate(UserRepositoryInterface::class, function ($repository) {
    return new CachedUserRepository($repository);
});

class CachedUserRepository implements UserRepositoryInterface {
    private UserRepositoryInterface $repository;
    private CacheInterface $cache;
    
    public function __construct(UserRepositoryInterface $repository, CacheInterface $cache = null) {
        $this->repository = $repository;
        // Note: In a real application, you should inject dependencies properly
        // rather than accessing the container directly
        $this->cache = $cache ?? $this->getCache();
    }
    
    private function getCache(): CacheInterface {
        // Get from DI container. In skeleton project, you might use App\app()
        // In regular Orkestra apps, you should inject this dependency instead
        return $GLOBALS['app']->get(CacheInterface::class);
    }
    
    public function findById(int $id): ?User {
        $cacheKey = "user.{$id}";
        
        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }
        
        $user = $this->repository->findById($id);
        $this->cache->set($cacheKey, $user);
        
        return $user;
    }
}
```

## Common Pitfalls and Solutions

### Circular Dependencies

Be careful with circular dependencies. When class A depends on class B, and class B depends on class A, you'll encounter issues:

**Solution**: Refactor to break the circular dependency, or use a factory pattern.

### Container in Domain Objects

Avoid directly accessing the application container in domain objects:

```php
// BAD
class UserService {
    public function getUser(int $id): ?User {
        // Don't access container directly in your domain logic
        $repository = App\app()->get(UserRepositoryInterface::class);
        return $repository->findById($id);
    }
}

// GOOD
class UserService {
    private UserRepositoryInterface $repository;
    
    public function __construct(UserRepositoryInterface $repository) {
        $this->repository = $repository;
    }
    
    public function getUser(int $id): ?User {
        return $this->repository->findById($id);
    }
}
```

## Related Topics

- [Service Providers](/docs/guides/providers) - Register and manage services
- [Application Lifecycle](/docs/core-concepts/app-lifecycle) - Understand application bootstrapping 