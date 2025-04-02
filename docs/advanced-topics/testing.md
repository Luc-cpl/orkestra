---
sidebar_position: 1
---

# Testing Orkestra Applications

This guide shows how to effectively test Orkestra applications using PHPUnit and Pest, with examples drawn from the framework's own test suite.

## Testing Philosophy

Orkestra's testing approach emphasizes:

1. **Unit Testing**: Testing individual components in isolation
2. **Integration Testing**: Testing how components work together
3. **Feature Testing**: Testing full application features
4. **Mocking Dependencies**: Isolating components for testing

## Testing Setup

Orkestra uses [Pest PHP](https://pestphp.com/), a testing framework built on top of PHPUnit that provides a more fluent syntax for writing tests.

### Basic Test Structure

```php
<?php

// Basic test with Pest
test('can do something', function () {
    // Arrange - Set up test prerequisites
    $service = new MyService();
    
    // Act - Perform the action being tested
    $result = $service->doSomething();
    
    // Assert - Verify the results
    expect($result)->toEqual('expected value');
});
```

### The app() Function in Tests

In Orkestra's test suite, a global `app()` function is used for convenience to access the application instance:

```php
// This app() function is ONLY available in the test environment
// Do not rely on this in your actual application code
function app() {
    global $app;
    if (!isset($app)) {
        $app = new \Orkestra\App(new \Orkestra\Configuration());
    }
    return $app;
}
```

> **Important**: The `app()` function is only intended for testing purposes. In your actual application code, you should always use the `Orkestra\App` instance directly or the helper provided by the skeleton repository.

## Testing Application Components

### Testing the Container

The container is a central part of Orkestra. Here's how to test container bindings:

```php
test('can get from container', function () {
    // In tests, we use app() helper for convenience
    $app = app();
    $app->bind('test', fn () => 'testValue');
    expect($app->get('test'))->toEqual('testValue');
});

test('can make from container with constructor parameters', function () {
    $app = app();
    $app->bind('test', fn ($param) => $param);
    expect($app->make('test', ['param' => 'testValue']))->toEqual('testValue');
});

test('can check if container has service', function () {
    $app = app();
    expect($app->has('test'))->toBeFalse();
    $app->bind('test', fn () => 'testValue');
    expect($app->has('test'))->toBeTrue();
});
```

### Testing Service Providers

Service providers can be tested by verifying their registration and boot behavior:

```php
test('can register a provider', function () {
    $app = app();
    $providerClass = new class () implements ProviderInterface {
        public string $test;
        public function register(App $app): void
        {
            // Registration logic
        }
        public function boot(App $app): void
        {
            // Boot logic
        }
    };
    
    $app->provider($providerClass::class);
    $provider = $app->get($providerClass::class);
    $provider->test = 'testValue';
    expect($app->get($providerClass::class))->toEqual($provider);
});

test('can boot', function () {
    $app = app();
    $providerClass = new class () implements ProviderInterface {
        public $test = null;
        public function register(App $app): void
        {
            // Registration logic
        }
        public function boot(App $app): void
        {
            $this->test = 'testValue';
        }
    };

    $app->provider($providerClass::class);
    $app->config()->set('env', 'development');
    $app->config()->set('root', './');
    $app->boot();

    $provider = $app->get($providerClass::class);
    expect($provider->test)->toEqual('testValue');
});
```

### Testing Configuration

Test configuration setting, validation, and retrieval:

```php
test('can validate configuration', function () {
    $config = new Configuration([
        'definition' => [
            'key1' => ['description1', 'default1'],
            'key2' => ['description2', 'default2'],
        ],
        'validation' => [
            'key1' => fn ($value) => $value === 'default1',
            'key2' => fn ($value) => $value === 'default2',
        ],
    ]);

    expect($config->validate())->toBeTrue();
});

test('can set and get configuration', function () {
    $config = new Configuration([]);
    $config->set('key', 'value');

    expect($config->get('key'))->toBe('value');
});
```

### Testing Controllers

Test controller behavior, including route handling:

```php
test('can set a route in api controller', function () {
    $app = app();
    $route = Mockery::mock(RouteInterface::class);

    $class = new class () extends AbstractApiController {
        public function getRoute(): RouteInterface
        {
            return $this->route;
        }
    };

    $app->provider(HttpProvider::class);
    $app->bind(AbstractApiController::class, $class::class);
    $controller = $app->get(AbstractApiController::class);
    $controller->setRoute($route);
    expect($controller->getRoute())->toBe($route);
});
```

## Mocking Dependencies

Orkestra tests heavily use [Mockery](https://github.com/mockery/mockery) for mocking dependencies:

```php
test('can decorate a service', function () {
    $app = app();
    $mock = Mockery::mock();
    $mock->shouldReceive('test')->andReturn('testValue');

    $mock2 = Mockery::mock();
    $mock2->shouldReceive('test')->andReturn('testValueDecorated');

    $callbackMock = Mockery::mock();
    $callbackMock->shouldReceive('run')->once()->andReturn($mock2);

    $app->bind($mock::class, fn () => $mock);
    $app->decorate($mock::class, fn ($service) => $callbackMock->run());
    expect($app->get($mock::class)->test())->toEqual('testValueDecorated');
});
```

### PHPUnit's createMock

For simpler mocking needs, PHPUnit's `createMock` can also be used:

```php
test('can set constructor params', function () {
    $mockedService = $this->createMock(CreateDefinitionHelper::class);
    $mockedService->expects($this->once())
        ->method('constructor')
        ->with(
            $this->equalTo('testValue1'),
            $this->equalTo('testValue2')
        );

    $bind = new AppBind('test', $mockedService);
    $bind->constructor('testValue1', 'testValue2');
});
```

## Testing Error Cases

Testing error cases and exceptions is an important part of a comprehensive test suite:

```php
test('can not get from container with non existent key', function () {
    $app = app();
    $app->get('nonExistentKey');
})->expectException(NotFoundExceptionInterface::class);

test('can not use a invalid env config', function () {
    $app = app();
    $app->config()->set('env', 'invalidEnv');
    $app->boot();
})->expectException(InvalidArgumentException::class);

test('can not use a invalid slug config', function (string $slug) {
    $app = app();
    $app->config()->set('slug', $slug);
    $app->boot();
})->with([
    'invalid slug',
    'invalidSlug',
    'invalid slug!',
    'invalidSlug!',
    'invalid-slug!',
    'invalid_slug!',
])->expectException(InvalidArgumentException::class);
```

## Data Providers

Pest supports data providers for parameterized tests:

```php
test('can not use a invalid slug config', function (string $slug) {
    $app = app();
    $app->config()->set('slug', $slug);
    $app->boot();
})->with([
    'invalid slug',
    'invalidSlug',
    'invalid slug!',
    'invalidSlug!',
    'invalid-slug!',
    'invalid_slug!',
])->expectException(InvalidArgumentException::class);
```

## Testing Binding Decorations

Test service decoration patterns:

```php
test('can decorate a bind interface', function () {
    $app = app();
    interface TestInterface
    {
        public function test();
    }

    $class = new class () implements TestInterface {
        public function test()
        {
            return 'testValue';
        }
    };

    $class2 = new class () implements TestInterface {
        public function test()
        {
            return 'testValueDecorated';
        }
    };

    $app->bind(TestInterface::class, $class::class);
    $app->decorate(TestInterface::class, fn ($service) => $class2);
    expect($app->get(TestInterface::class)->test())->toEqual('testValueDecorated');
});
```

## Testing Best Practices

### 1. Reset Application State Between Tests

In Pest, you can use the `beforeEach` hook to reset the application state:

```php
beforeEach(function () {
    // Create a fresh application instance for each test
    $app = new App(new Configuration());
    $app->config()->set('env', 'testing');
    $app->config()->set('root', './');
    
    // Make the app instance available to tests
    $GLOBALS['app'] = $app;
});
```

### 2. Isolate Tests

Ensure each test is isolated and doesn't depend on the state of other tests:

```php
test('can get configuration independently', function () {
    // This test doesn't rely on state from other tests
    $config = new Configuration(['key' => 'value']);
    expect($config->get('key'))->toBe('value');
});
```

### 3. Test Error States

Always test both success and error cases:

```php
// Success case
test('can validate valid configuration', function () {
    $config = new Configuration([
        'key' => 'validValue',
        'validation' => [
            'key' => fn ($value) => $value === 'validValue',
        ],
    ]);
    expect($config->validate())->toBeTrue();
});

// Error case
test('throws exception for invalid configuration', function () {
    $config = new Configuration([
        'key' => 'invalidValue',
        'validation' => [
            'key' => fn ($value) => $value === 'validValue',
        ],
    ]);
    $config->validate();
})->throws(InvalidArgumentException::class);
```

### 4. Mock External Dependencies

Use mocks for external services to ensure test isolation:

```php
test('service uses repository correctly', function () {
    // Create a mock repository
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('findById')
        ->with(1)
        ->once()
        ->andReturn(new User(['id' => 1, 'name' => 'Test User']));
    
    // Inject the mock into the service
    $service = new UserService($repository);
    
    // Test the service with the mock
    $user = $service->getUser(1);
    expect($user->name)->toBe('Test User');
});
```

### 5. Test Provider Integration

Test how multiple providers work together:

```php
test('can boot all existing providers', function () {
    $app = app();
    $app->provider(CommandsProvider::class);
    $app->provider(HooksProvider::class);
    $app->provider(HttpProvider::class);
    $app->provider(ViewProvider::class);

    $app->boot();

    // Verify that all providers were booted correctly
    expect($app->get(HooksInterface::class))->toBeInstanceOf(HooksInterface::class);
});
```

## Writing Testable Code

### Dependency Injection

Use dependency injection to make your code testable:

```php
// Hard to test
class UserService {
    public function getUser(int $id) {
        // Don't do this - direct instantiation makes testing hard
        $repository = new UserRepository();
        return $repository->findById($id);
    }
}

// Testable with dependency injection
class UserService {
    private UserRepositoryInterface $repository;
    
    public function __construct(UserRepositoryInterface $repository) {
        $this->repository = $repository;
    }
    
    public function getUser(int $id) {
        return $this->repository->findById($id);
    }
}
```

### Interface-Based Design

Use interfaces to define contracts between components:

```php
interface UserRepositoryInterface {
    public function findById(int $id): ?User;
}

class UserRepository implements UserRepositoryInterface {
    public function findById(int $id): ?User {
        // Implementation
    }
}
```

### Avoid Global State

In your application code, avoid relying on global functions or static state:

```php
// DON'T do this in your application code
class BadService {
    public function doSomething() {
        // Don't use global app() function in your actual code
        $config = app()->config()->get('some_setting');
        // ...
    }
}

// DO this instead
class GoodService {
    private $config;
    
    public function __construct(Configuration $config) {
        $this->config = $config;
    }
    
    public function doSomething() {
        $setting = $this->config->get('some_setting');
        // ...
    }
}
```

## Continuous Integration

Add tests to your CI pipeline to ensure code quality:

```yaml
# Example GitHub Actions workflow
name: Tests

on: [push, pull_request]

jobs:
  tests:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        
    - name: Install Dependencies
      run: composer install --no-interaction --prefer-dist
        
    - name: Execute Tests
      run: vendor/bin/pest
```

## Related Topics

- [Troubleshooting](/docs/advanced-topics/troubleshooting) - Debugging common issues
- [Dependency Injection](/docs/core-concepts/dependency-injection) - Understanding the service container 