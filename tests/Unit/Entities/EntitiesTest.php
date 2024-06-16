<?php

use Orkestra\Entities\AbstractEntity;
use Orkestra\Entities\Attributes\Faker;
use Orkestra\Entities\Attributes\Repository;
use Orkestra\Entities\EntityFactory;

class EntitiesTestRepository
{
    public function persist(): void
    {
        // Do nothing
    }
}

#[Repository('testRepository')]
#[Faker('name', method: 'name')]
#[Faker('nonConstructProperty', value:'test value')]
class EntityTest extends AbstractEntity
{
    protected string $nonConstructProperty;

    // Add this as a property attribute to test the attribute use
    #[Faker('publicValue', value:'public value')]
    public string $publicValue;

    public function __construct(
        protected string $name,
        protected int $age = 0
    ) {
    }

    protected function getNonPropertyValue(): string
    {
        return 'non property value';
    }

    // Force a different age
    protected function getAge(): int
    {
        return 10;
    }

    public function setNonConstructProperty(string $value): void
    {
        $this->nonConstructProperty = $value;
    }
}

test('can make a new entity', function () {
    $app = app();
    $factory = $app->get(EntityFactory::class);
    $entity = $factory->make(EntityTest::class, name: 'John Doe', nonConstructProperty: 'test', publicValue: 'public');
    expect($entity)->toBeInstanceOf(EntityTest::class);
    expect($entity->name)->toBe('John Doe');
    expect($entity->nonConstructProperty)->toBe('test');
    expect($entity->publicValue)->toBe('public');
});

test('can make a new entity with faker', function () {
    $app = app();
    $factory = $app->make(EntityFactory::class, ['useFaker' => true]);
    $entity = $factory->make(EntityTest::class);
    expect($entity)->toBeInstanceOf(EntityTest::class);
    expect($entity->name)->toBeString();
    expect($entity->nonConstructProperty)->toBe('test value');
    expect($entity->publicValue)->toBe('public value');
});

test('can get entity values', function () {
    $entity = new EntityTest('John Doe', 20);
    expect($entity->name)->toBe('John Doe');
    expect($entity->age)->toBe(10);
    expect($entity->nonPropertyValue)->toBe('non property value');
});

test('can set entity values', function () {
    $entity = new EntityTest('John Doe', 20);
    $entity->set(name: 'Jane Doe', nonConstructProperty: 'test');
    expect($entity->name)->toBe('Jane Doe');
    expect($entity->nonConstructProperty)->toBe('test');
});

test('can not set without named args', function () {
    $entity = new EntityTest('John Doe', 20);
    $entity->set('Jane Doe', 'test');
})->throws(InvalidArgumentException::class);

test('can not set with invalid args', function () {
    $entity = new EntityTest('John Doe', 20);
    $entity->set(nonExistent: true);
})->throws(InvalidArgumentException::class);

test('can not get non existent property', function () {
    $entity = new EntityTest('John Doe', 20);
    $entity->nonExistent;
})->throws(InvalidArgumentException::class);

test('can check if property is set', function () {
    $entity = new EntityTest('John Doe', 20);
    expect(isset($entity->name))->toBeTrue();
    expect(isset($entity->nonPropertyValue))->toBeTrue();
    expect(isset($entity->nonExistent))->toBeFalse();

    expect(isset($entity->nonConstructProperty))->toBeFalse();
    $entity->set(nonConstructProperty: 'test');
    expect(isset($entity->nonConstructProperty))->toBeTrue();
});

test('can not access a property before initialization', function () {
    $entity = new EntityTest('John Doe', 20);
    $entity->nonConstructProperty;
})->throws(Error::class);

test('can convert entity to array', function () {
    $entity = new EntityTest('John Doe', 20);
    $entity->set(nonConstructProperty: 'test');
    expect($entity->toArray())->toBe([
        'nonConstructProperty' => 'test',
        'name' => 'John Doe',
        'age' => 10,
    ]);
});

test('can create a new entity', function () {
    $repository = Mockery::mock(EntitiesTestRepository::class);
    $repository->shouldReceive('persist')->once();
    app()->bind('testRepository', fn () => $repository);

    $factory = app()->make(EntityFactory::class, ['useFaker' => true]);
    $entity = $factory->create(EntityTest::class);
    expect($entity)->toBeInstanceOf(EntityTest::class);
});

test('can create multiple entities', function () {
    $repository = Mockery::mock(EntitiesTestRepository::class);
    $repository->shouldReceive('persist')->times(2);
    app()->bind('testRepository', fn () => $repository);

    $factory = app()->make(EntityFactory::class, ['useFaker' => true]);
    $entities = $factory->times(2)->create(EntityTest::class);
    expect($entities)->toBeArray()->toHaveCount(2);
});

it('can not create an entity without a repository', function () {
    $factory = app()->make(EntityFactory::class, ['useFaker' => true]);
    $factory->create(EntityTest::class);
})->throws(RuntimeException::class);

it('can not create an entity without a persist method', function () {
    $repository = Mockery::mock(stdClass::class);
    app()->bind('testRepository', fn () => $repository);

    $factory = app()->make(EntityFactory::class, ['useFaker' => true]);
    $factory->create(EntityTest::class);
})->throws(BadMethodCallException::class);

test('can use a callable in make', function () {
    $factory = app()->make(EntityFactory::class, ['useFaker' => true]);
    $entity = $factory->make(EntityTest::class, fn ($i) => ['name' => 'John Doe ' . $i]);
    expect($entity)->toBeInstanceOf(EntityTest::class);
    expect($entity->name)->toBe('John Doe 0');
});