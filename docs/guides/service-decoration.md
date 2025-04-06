---
sidebar_position: 5
---

# Service Decoration

Orkestra provides a powerful service decoration system that allows you to modify or extend the behavior of services without changing their original implementation. This guide explains how to use service decoration effectively.

## Understanding Service Decoration

Service decoration follows the [Decorator pattern](https://en.wikipedia.org/wiki/Decorator_pattern), allowing you to wrap a service with additional functionality. This is particularly useful for:

- Adding cross-cutting concerns like logging, caching, or validation
- Conditionally enhancing services based on environment or configuration
- Creating feature toggles and A/B testing
- Implementing [Chain of Responsibility](https://en.wikipedia.org/wiki/Chain-of-responsibility_pattern) patterns

## Basic Decoration

The most common way to decorate a service is using the `decorate()` method:

```php
// First, bind the original service
$app->bind(MyService::class, fn() => new MyService());

// Then, decorate it with additional functionality
$app->decorate(MyService::class, function($service) {
    return new MyServiceDecorator($service);
});

// When you retrieve the service, you get the decorated version
$service = $app->get(MyService::class); // Returns MyServiceDecorator instance
```

### Decoration Order

Multiple decorators can be applied to the same service. They are applied in the order they are registered:

```php
$app->bind(MyService::class, fn() => new MyService());

// First decorator
$app->decorate(MyService::class, function($service) {
    return new LoggingDecorator($service);
});

// Second decorator (wraps the LoggingDecorator)
$app->decorate(MyService::class, function($service) {
    return new CachingDecorator($service);
});

// The resulting chain is: CachingDecorator -> LoggingDecorator -> MyService
$service = $app->get(MyService::class);
```

## Interface-Based Decoration

A particularly powerful use of decoration is with interfaces. You can bind an interface to an implementation and then decorate that interface:

```php
interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
}

class UserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        // Implementation...
    }
}

// Bind the interface to the implementation
$app->bind(UserRepositoryInterface::class, UserRepository::class);

// Decorate the interface
$app->decorate(UserRepositoryInterface::class, function($repository) {
    return new CachedUserRepository($repository);
});

// When you retrieve the interface, you get the decorated implementation
$repository = $app->get(UserRepositoryInterface::class); // Returns CachedUserRepository
```

This allows you to keep your application code dependent on interfaces rather than concrete implementations while still using decoration.

## Decorator Implementation

A decorator typically:

1. Implements the same interface as the decorated service
2. Holds a reference to the decorated service
3. Delegates to the decorated service while adding its own behavior

```php
interface LoggerInterface
{
    public function log(string $message): void;
}

class FileLogger implements LoggerInterface
{
    public function log(string $message): void
    {
        file_put_contents('app.log', $message . PHP_EOL, FILE_APPEND);
    }
}

class FormattingLogger implements LoggerInterface
{
    private LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    public function log(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = "[{$timestamp}] {$message}";
        $this->logger->log($formattedMessage);
    }
}

// Usage in a service provider
public function register(App $app): void
{
    $app->bind(LoggerInterface::class, FileLogger::class);
    $app->decorate(LoggerInterface::class, function($logger) {
        return new FormattingLogger($logger);
    });
}
```

## Advanced Decoration Patterns

### Conditional Decoration

You can apply decorators conditionally based on configuration or environment:

```php
public function register(App $app): void
{
    // Bind the base implementation
    $app->bind(CacheInterface::class, FileCache::class);
    
    // Apply Redis cache decorator only if Redis is available
    if (extension_loaded('redis')) {
        $app->decorate(CacheInterface::class, function($cache) {
            return new RedisCache($cache); // Falls back to FileCache if Redis fails
        });
    }
    
    // Apply debugging decorator only in development
    if ($app->config()->get('env') === 'development') {
        $app->decorate(CacheInterface::class, function($cache) {
            return new DebuggingCache($cache);
        });
    }
}
```

### Decoration Before Binding

You can register decorators before the service is even bound. The decorators will be applied when the service is eventually bound:

```php
public function register(App $app): void
{
    // Register a decorator first
    $app->decorate(PaymentGatewayInterface::class, function($gateway) {
        return new LoggingPaymentGateway($gateway);
    });
    
    // Later, bind the interface
    $app->bind(PaymentGatewayInterface::class, StripeGateway::class);
    
    // The service is automatically decorated when retrieved
    // $app->get(PaymentGatewayInterface::class) will return LoggingPaymentGateway
}
```

### Multiple Interface Decoration

A service can implement multiple interfaces, and each interface can be decorated independently:

```php
interface Readable
{
    public function read(string $key): mixed;
}

interface Writable
{
    public function write(string $key, mixed $value): void;
}

class Storage implements Readable, Writable
{
    // Implementation...
}

public function register(App $app): void
{
    // Decorate the Readable interface
    $app->bind(Readable::class, Storage::class);
    $app->decorate(Readable::class, function($storage) {
        return new CachedReadable($storage);
    });
    
    // Decorate the Writable interface
    $app->bind(Writable::class, Storage::class);
    $app->decorate(Writable::class, function($storage) {
        return new ValidatedWritable($storage);
    });
}
```

## Real-World Decoration Examples

### Caching Decorator

```php
class CachedUserRepository implements UserRepositoryInterface
{
    private UserRepositoryInterface $repository;
    private CacheInterface $cache;
    
    public function __construct(
        UserRepositoryInterface $repository,
        CacheInterface $cache
    ) {
        $this->repository = $repository;
        $this->cache = $cache;
    }
    
    public function findById(int $id): ?User
    {
        $cacheKey = "user.{$id}";
        
        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }
        
        $user = $this->repository->findById($id);
        
        if ($user) {
            $this->cache->set($cacheKey, $user, 3600); // Cache for 1 hour
        }
        
        return $user;
    }
}

// In a service provider
public function register(App $app): void
{
    // Register the repository
    $app->bind(UserRepositoryInterface::class, UserRepository::class);
    
    // Decorate with caching
    $app->decorate(UserRepositoryInterface::class, function($repository) use ($app) {
        return new CachedUserRepository(
            $repository,
            $app->get(CacheInterface::class)
        );
    });
}
```

### Transaction Decorator

```php
class TransactionalUserService implements UserServiceInterface
{
    private UserServiceInterface $service;
    private DatabaseInterface $database;
    
    public function __construct(
        UserServiceInterface $service,
        DatabaseInterface $database
    ) {
        $this->service = $service;
        $this->database = $database;
    }
    
    public function updateUser(User $user): bool
    {
        $this->database->beginTransaction();
        
        try {
            $result = $this->service->updateUser($user);
            $this->database->commit();
            return $result;
        } catch (Exception $e) {
            $this->database->rollback();
            throw $e;
        }
    }
}

// In a service provider
public function register(App $app): void
{
    $app->bind(UserServiceInterface::class, UserService::class);
    
    $app->decorate(UserServiceInterface::class, function($service) use ($app) {
        return new TransactionalUserService(
            $service,
            $app->get(DatabaseInterface::class)
        );
    });
}
```

### Rate Limiting Decorator

```php
class RateLimitedApiClient implements ApiClientInterface
{
    private ApiClientInterface $client;
    private RateLimiterInterface $rateLimiter;
    
    public function __construct(
        ApiClientInterface $client,
        RateLimiterInterface $rateLimiter
    ) {
        $this->client = $client;
        $this->rateLimiter = $rateLimiter;
    }
    
    public function request(string $endpoint, array $data = []): array
    {
        if (!$this->rateLimiter->allowRequest()) {
            throw new RateLimitExceededException('API rate limit exceeded');
        }
        
        return $this->client->request($endpoint, $data);
    }
}

// In a service provider
public function register(App $app): void
{
    $app->bind(ApiClientInterface::class, HttpApiClient::class);
    
    $app->decorate(ApiClientInterface::class, function($client) use ($app) {
        return new RateLimitedApiClient(
            $client,
            $app->get(RateLimiterInterface::class)
        );
    });
}
```

## Testing Decorated Services

When testing decorated services, you can test both the individual decorators and the entire decoration chain:

### Testing Individual Decorators

```php
test('caching decorator returns cached user', function () {
    // Create a mock repository
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('findById')
        ->with(1)
        ->once() // Should be called only once
        ->andReturn(new User(['id' => 1, 'name' => 'Test User']));
    
    // Create a real cache
    $cache = new ArrayCache();
    
    // Create the decorator
    $cachedRepository = new CachedUserRepository($repository, $cache);
    
    // First call should hit the repository
    $user1 = $cachedRepository->findById(1);
    expect($user1->name)->toBe('Test User');
    
    // Second call should hit the cache, not the repository
    $user2 = $cachedRepository->findById(1);
    expect($user2->name)->toBe('Test User');
});
```

### Testing the Decoration Chain

```php
test('can retrieve fully decorated service', function () {
    // Create application
    $app = new Orkestra\App(new Orkestra\Configuration());
    
    // Bind the base service
    $app->bind(LoggerInterface::class, fn() => new FileLogger());
    
    // Add decorators
    $app->decorate(LoggerInterface::class, function($logger) {
        return new TimestampLogger($logger);
    });
    
    $app->decorate(LoggerInterface::class, function($logger) {
        return new JsonLogger($logger);
    });
    
    // Boot the application
    $app->boot();
    
    // Get the fully decorated service
    $logger = $app->get(LoggerInterface::class);
    
    // Test that it's the outermost decorator
    expect($logger)->toBeInstanceOf(JsonLogger::class);
    
    // Test that the decoration chain works
    $logFile = 'app.log';
    @unlink($logFile); // Clear log file
    
    $logger->log(['message' => 'Test message']);
    
    $logContent = file_get_contents($logFile);
    expect($logContent)->toContain('"message":"Test message"');
    expect($logContent)->toContain('"timestamp":"');
});
```

## Best Practices

1. **Decorate Interfaces, Not Implementations**: Whenever possible, apply decorators to interfaces rather than concrete classes for better flexibility and testability.

2. **Follow the Single Responsibility Principle**: Each decorator should add only one piece of functionality. For complex behavior, use multiple decorators.

3. **Keep the Same Interface**: Decorators should implement the same interface as the decorated service to ensure compatibility.

4. **Forward Unknown Methods**: If decorating a service with many methods, consider implementing `__call()` to forward any unhandled methods to the decorated service.

5. **Use Constructor Injection**: Pass the decorated service via constructor, not through the container, to make testing easier.

6. **Apply Decorators in the Right Order**: Consider the order of decorators carefully. For example, caching typically should come after validation.

7. **Make Decorators Configurable**: Allow decorators to be configured (e.g., cache duration, log level) for greater flexibility.

8. **Use the $app Instance in Closures**: When accessing the application instance in decorator closures, always use the `use ($app)` syntax to capture the variable instead of relying on global functions.

## Related Topics

- [Dependency Injection](/docs/core-concepts/dependency-injection) - Understanding the service container
- [Service Providers](/docs/guides/providers) - Managing service registration and decoration
- [Testing](/docs/advanced-topics/testing) - Testing Orkestra applications 