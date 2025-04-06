---
sidebar_position: 1
---

# Routing

Orkestra provides a flexible and powerful routing system to handle HTTP requests and direct them to the appropriate controllers.

## Basic Routing

Routes are defined in the `config/routes.php` file using the router instance:

```php
use Orkestra\Router;

return function (Router $router) {
    $router->get('/', HomeController::class);
    $router->get('/users', UserListController::class);
    $router->post('/users', CreateUserController::class);
    $router->get('/users/{id}', UserDetailsController::class);
    $router->put('/users/{id}', UpdateUserController::class);
    $router->delete('/users/{id}', DeleteUserController::class);
};
```

## HTTP Methods

Orkestra supports all common HTTP methods:

```php
$router->get('/resource', ResourceController::class);
$router->post('/resource', CreateResourceController::class);
$router->put('/resource/{id}', UpdateResourceController::class);
$router->patch('/resource/{id}', PatchResourceController::class);
$router->delete('/resource/{id}', DeleteResourceController::class);
$router->options('/resource', ResourceOptionsController::class);
```

## Route Parameters

Routes can include parameters, which are passed to the controller:

```php
$router->get('/users/{id}', function(ServerRequestInterface $request, array $params): ResponseInterface {
    $userId = $params['id'];
    // Process the request using $userId
    return new Response();
});
```

### Optional Parameters

You can define optional parameters using the `?` modifier:

```php
$router->get('/users/{id?}', UserController::class);
```

### Parameter Validation

You can validate parameters directly in route definitions:

```php
$router->get('/users/{id:[0-9]+}', UserController::class);
$router->get('/articles/{slug:[a-z0-9\-]+}', ArticleController::class);
```

## Route Groups

Group related routes together:

```php
$router->group('/admin', function (Router $router) {
    $router->get('/dashboard', AdminDashboardController::class);
    $router->get('/users', AdminUsersController::class);
    $router->get('/settings', AdminSettingsController::class);
});
```

## Middleware

Apply middleware to routes:

```php
$router->get('/protected', ProtectedController::class)
    ->middleware(AuthMiddleware::class);

$router->group('/admin', function (Router $router) {
    $router->get('/dashboard', AdminDashboardController::class);
})->middleware([AuthMiddleware::class, AdminMiddleware::class]);
```

## Best Practices

1. Keep route definitions clean and organized
2. Use route groups for related endpoints
3. Apply middleware at the group level when possible
4. Validate route parameters when appropriate
5. Follow RESTful conventions for resource endpoints 