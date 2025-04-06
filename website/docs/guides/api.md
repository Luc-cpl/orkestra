---
sidebar_position: 1
---

# API Development

Orkestra follows PSR-15 for HTTP message handling and provides abstract controllers for building APIs.

## Abstract Controllers

Orkestra provides abstract controllers that extend PSR-15's request handler interface:

```php
use Orkestra\Services\Http\Controllers\AbstractController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ApiController extends AbstractController
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Handle the request
        return $this->response;
    }
}
```

## Request Handling

```php
class UserController extends AbstractController
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Get request data
        $data = $request->getParsedBody();
        
        // Get query parameters
        $query = $request->getQueryParams();
        
        // Get headers
        $headers = $request->getHeaders();
        
        // Get request method
        $method = $request->getMethod();
        
        return $this->response;
    }
}
```

## Response Handling

```php
class UserController extends AbstractController
{
    public function handle(ServerRequestInterface $request): ResponseInterface
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

## Route Definitions

Routes are defined in your configuration files:

```php
// config/routes.php
return [
    'GET /api/users' => [UserController::class, 'handle'],
    'POST /api/users' => [UserController::class, 'handle'],
];
```

## Entity Parameters

You can use entities as route parameters:

```php
use Orkestra\Services\Http\Attributes\Entity;

class UserController extends AbstractController
{
    #[Entity(User::class)]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute('user');
        
        return $this->response;
    }
}
```

## Best Practices

1. Follow PSR-15 standards
2. Use dependency injection
3. Keep controllers focused
4. Validate input data
5. Use proper HTTP status codes
6. Handle errors gracefully
7. Document your API endpoints
8. Use consistent response formats

## Related Topics

- [Controllers](/docs/guides/controllers) - Learn about controllers
- [Routing](/docs/guides/routing) - Define API routes