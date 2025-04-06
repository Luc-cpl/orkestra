---
sidebar_position: 3
---

# Entities

Orkestra AbstractEntities are simple data objects that represent your application's data structures.
The initial idea is to cerate a behavior as Property Hooks and Asymmetric Visibility before PHP 8.4 and to have a easy way to create new objects with the EntityFactory

## Basic Entity

This entity below is a good example of a basic usage. The properties in protected visibility are public-read, allowing us to read and
```php
use Orkestra\Entities\AbstractEntity;

class User extends AbstractEntity
{
    public function __construct(
        protected string $name,
        protected string $email,
        private string $password,
    ) {}
}

$user = new User(
    name: 'Joe Doe',
    email: 'joe@email.com',
    password: '12345',
);

echo $user->name; // Joe Doe
echo $user->email; // joe@email.com
echo $user->password; // throws an exception

// Changes the user name and email values
$user->set(
    name: 'Jane Doe',
    email: 'jane@email.com',
);
```

## Entity Factory

Entities are created using a factory:

```php
use Orkestra\Services\Http\Factories\EntityFactory;

class UserController extends EntityFactory
{
    public function __construct(
        private EntityFactory $factory,
    ) {
        //
    }

    #[Entity(User::class)]
    public function __invoke(ServerRequestInterface $request): User
    {
        // Return a new user or throws BadRequestException in the middleware stage according validations
        return $this->factory->make(User::class, $request->getParsedBody());
    }
}
```

## Entity Attributes

You can define a repository and Faker values for tests:

```php
use Orkestra\Services\Http\Attributes\Entity;
use Orkestra\Services\Http\Attributes\Repository;
use Orkestra\Services\Http\Attributes\Faker;

#[Entity]
#[Repository(UserRepository::class)]
class User
{
    #[Faker('name')]
    private string $name;

    #[Faker('email')]
    private string $email;

    #[Faker('password')]
    private string $password;

    #[Faker('dateTime')]
    private DateTimeImmutable $created_at;
}
```

## Property Hooks (PHP 8.4+)

Orkestra recommends using native property hooks instead of our AbstractEntity since PHP 8.4.

```php
class User
{
    public function __construct(
        public string $name;
        public string $email;
        private(set) string $password;
    ) {}
}
```

## Best Practices

1. Keep entities focused and simple
2. Use EntityFactory for entity creation
3. Set entities Attributes to define middleware validation, repository and faker values
4. Validate data in constructors
5. Use events for side effects
6. Document entity properties
7. Use type hints
8. Follow immutability when possible
