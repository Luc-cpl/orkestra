---
sidebar_position: 2
---

# Controllers

Orkestra controllers follow PSR-15 standards and support dependency injection for better testability and maintainability.

## Abstract Controller

All controllers extend the `AbstractController` class which implements PSR-15's `RequestHandlerInterface`:

```php
use Orkestra\Services\Http\Controllers\AbstractController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserController extends AbstractController
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Handle the request
        return $this->response;
    }
}
```

## Dependency Injection

Controllers support dependency injection through the constructor:

```php
class UserController extends AbstractController
{
    private UserService $userService;
    private LoggerInterface $logger;

    public function __construct(
        UserService $userService,
        LoggerInterface $logger
    ) {
        $this->userService = $userService;
        $this->logger = $logger;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $users = $this->userService->getAllUsers();
        
        $this->logger->info('Retrieved users', ['count' => count($users)]);
        
        return $this->response;
    }
}
```

## Request Handling

Controllers receive PSR-7 request objects:

```php
class UserController extends AbstractController
{
    public function handle(ServerRequestInterface $request, array $params): ResponseInterface
    {
        // Get request data
        $data = $request->getParsedBody();
        
        // Get query parameters
        $query = $request->getQueryParams();
        
        // Get headers
        $headers = $request->getHeaders();
        
        // Get request method
        $method = $request->getMethod();
        
        // Get request attributes (route parameters)
        $id = $params['id'];
        
        return $this->response;
    }
}
```

## Response Handling

Controllers return PSR-7 response objects, strings or json serializable objects/arrays:

```php
class UserController extends AbstractController
{
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $data = [
            'users' => [
                ['id' => 1, 'name' => 'John'],
                ['id' => 2, 'name' => 'Jane']
            ]
        ];
        
        // Set response body
        $this->response->getBody()->write(json_encode($data));
        
        // Set response headers
        $this->response = $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
            
        return $this->response;
    }
}
```

```php
class UserController extends AbstractController
{
    public function __invoke(ServerRequestInterface $request): array
    {
        return [
            'users' => [
                ['id' => 1, 'name' => 'John'],
                ['id' => 2, 'name' => 'Jane']
            ]
        ];
    }
}
```

```php
class HelloController extends AbstractController
{
    public function __invoke(ServerRequestInterface $request): string
    {
        return 'Hello Word';
    }
}
```

## Attribute Parameters

Controllers can add defined parameters with validation to be used in Route Definition and validation:

```php
use Orkestra\Services\Http\Attributes\Param;

class UserController extends AbstractController
{
    #[Param('my_value_1', type: 'string', validation: 'required|min:3|max:255')]
    #[Param('my_value_2', validation: 'required', enum: ParamType::class)]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** Your code */

        return $this->response;
    }
}
```

### Entity Parameters

You can even define entire entities as route parameters, this will autommatically add the User entity params in Route Definition and validation:

```php
use Orkestra\Services\Http\Attributes\Entity;

class UserController extends AbstractController
{
    #[Entity(User::class)]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** Your code */

        return $this->response;
    }
}
```

```php
use Orkestra\Services\Http\Attributes\Param;

#[Param('entity_value_1', type: 'string', validation: 'required|min:3|max:255')]
class User
{
    #[Param]
    public int $entity_value_2;
}
```

## Best Practices

1. Follow PSR-15 standards
2. Use dependency inversion
3. Keep controllers focused
4. Validate input data
5. Use proper HTTP status codes
6. Handle errors gracefully
7. Document your endpoints
8. Use consistent response formats

## Related Topics

- [Routing](/docs/guides/routing) - Define routes for controllers
- [Middleware](/docs/guides/middleware) - Add middleware to controllers
- [Validation](/docs/guides/validation) - Validate controller input
- [API Development](/docs/guides/api) - Build APIs with controllers
- [HTTP Service](/docs/services/http) - Learn about the HTTP service
