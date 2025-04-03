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

test('can return null if property is not set', function () {
    $entity = new EntityTest('John Doe', 20);
    expect($entity->nonConstructProperty)->toBeNull();
});

test('can convert entity to array', function () {
    $entity = new EntityTest('John Doe', 20);
    $entity->set(nonConstructProperty: 'test');
    expect($entity->toArray())->toBe([
        'nonConstructProperty' => 'test',
        'publicValue' => null,
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

// Class without extending AbstractEntity for testing
class SimpleClass
{
    public string $name;
    protected string $age;

    public function __construct(
        string $name = '',
        string $age = ''
    ) {
        $this->name = $name;
        $this->age = $age;
    }

    public function getAge(): string
    {
        return $this->age;
    }
}

// Class with repository attribute but not extending AbstractEntity
#[Repository('testRepository')]
class SimpleClassWithRepository
{
    public string $name;

    public function __construct(string $name = '')
    {
        $this->name = $name;
    }
}

// Add this at the end
test('can make a non-AbstractEntity class', function () {
    $app = app();
    $factory = $app->get(EntityFactory::class);

    $simpleObj = $factory->make(SimpleClass::class, name: 'John Doe', age: '30');

    expect($simpleObj)->toBeInstanceOf(SimpleClass::class);
    expect($simpleObj->name)->toBe('John Doe');
    expect($simpleObj->getAge())->toBe('30');
});

test('can set public properties for non-AbstractEntity classes', function () {
    $app = app();
    $factory = $app->get(EntityFactory::class);

    $simpleObj = $factory->make(
        SimpleClass::class,
        name: 'John Doe',
        age: '25',
    );

    expect($simpleObj->name)->toBe('John Doe');
    expect($simpleObj->getAge())->toBe('25');
});

test('cannot set protected properties directly for non-AbstractEntity classes', function () {
    $app = app();
    $factory = $app->get(EntityFactory::class);

    // This should still work because setAge method exists
    $simpleObj = $factory->make(SimpleClass::class, age: '30');
    expect($simpleObj->getAge())->toBe('30');

    // This would throw an exception if we tried to set a non-existent property
    $factory->make(SimpleClass::class, nonExistent: 'value');
})->throws(InvalidArgumentException::class);

test('can create a non-AbstractEntity class with repository', function () {
    $repository = Mockery::mock(EntitiesTestRepository::class);
    $repository->shouldReceive('persist')->once();
    app()->bind('testRepository', fn () => $repository);

    $factory = app()->get(EntityFactory::class);
    $simpleObj = $factory->create(SimpleClassWithRepository::class, name: 'John Doe');

    expect($simpleObj)->toBeInstanceOf(SimpleClassWithRepository::class);
    expect($simpleObj->name)->toBe('John Doe');
});

test('can use faker with non-AbstractEntity classes', function () {
    $app = app();
    $factory = $app->make(EntityFactory::class, ['useFaker' => true]);

    // Create a class with Faker attribute on the fly
    $className = 'FakerTestClass_' . uniqid();
    eval('
        #[\\Orkestra\\Entities\\Attributes\\Faker("name", method: "name")]
        class ' . $className . ' {
            public string $name;
            
            public function __construct(string $name = "") {
                $this->name = $name;
            }
        }
    ');

    $obj = $factory->make($className);
    expect($obj->name)->toBeString()->not->toBeEmpty();
});

// Entity with Traversable property for testing
class EntityWithTraversable extends AbstractEntity
{
    protected \ArrayIterator $traversableWithNumericKeys;
    protected \ArrayIterator $traversableWithStringKeys;

    public function __construct()
    {
        $this->traversableWithNumericKeys = new \ArrayIterator([1, 2, 3]);
        $this->traversableWithStringKeys = new \ArrayIterator(['key1' => 'value1', 'key2' => 'value2']);
    }
}

// Entity with nested objects for testing
class NestedEntity extends AbstractEntity
{
    public function __construct(
        protected string $name
    ) {
    }

    public function toArray(): array
    {
        return ['custom' => 'array'];
    }
}

class EntityWithNestedObjects extends AbstractEntity
{
    protected NestedEntity $nestedEntity;
    protected array $arrayOfObjects;
    protected \DateTime $dateTime;

    public function __construct()
    {
        $this->nestedEntity = new NestedEntity('test');
        $this->arrayOfObjects = [new NestedEntity('test1'), new NestedEntity('test2')];
        $this->dateTime = new \DateTime('2021-01-01');
    }
}

// Tests for AbstractEntity toArray
test('can convert entity with Traversable properties to array', function () {
    $entity = new EntityWithTraversable();
    $array = $entity->toArray();
    
    expect($array)->toHaveKey('traversableWithNumericKeys');
    expect($array['traversableWithNumericKeys'])->toBe([1, 2, 3]);
    
    expect($array)->toHaveKey('traversableWithStringKeys');
    expect($array['traversableWithStringKeys'])->toBe(['key1' => 'value1', 'key2' => 'value2']);
});

test('can convert entity with nested objects to array', function () {
    $entity = new EntityWithNestedObjects();
    $array = $entity->toArray();
    
    expect($array)->toHaveKey('nestedEntity');
    expect($array['nestedEntity'])->toBe(['custom' => 'array']);
    
    expect($array)->toHaveKey('arrayOfObjects');
    expect($array['arrayOfObjects'])->toBe([['custom' => 'array'], ['custom' => 'array']]);
    
    expect($array)->toHaveKey('dateTime');
    expect($array['dateTime'])->toBeString();
});

test('can serialize entity to JSON', function () {
    $entity = new EntityTest('John Doe', 20);
    $entity->set(nonConstructProperty: 'test');
    
    // Test JsonSerializable interface
    $json = json_encode($entity);
    $decoded = json_decode($json, true);
    
    expect($decoded)->toBe([
        'nonConstructProperty' => 'test',
        'publicValue' => null,
        'name' => 'John Doe',
        'age' => 10,
    ]);
});

// Test for EntityFactory with different locale and repository handling
test('can create entity factory with custom locale', function () {
    $app = app();
    $factory = $app->make(EntityFactory::class, [
        'useFaker' => true,
        'locale' => 'es_ES'
    ]);
    
    $entity = $factory->make(EntityTest::class);
    expect($entity)->toBeInstanceOf(EntityTest::class);
});

// Test for no repository case
class EntityWithNoRepository extends AbstractEntity
{
    public function __construct(
        protected string $name = 'test'
    ) {
    }
}

test('repository is null when class has no repository attribute', function () {
    $app = app();
    $factory = $app->get(EntityFactory::class);
    
    // Using reflection to test private method
    $reflection = new \ReflectionClass($factory);
    $method = $reflection->getMethod('getRepository');
    $method->setAccessible(true);
    
    $entityReflection = new \ReflectionClass(EntityWithNoRepository::class);
    $result = $method->invoke($factory, $entityReflection);
    
    expect($result)->toBeNull();
});

test('repository is null when repository class is not in container', function () {
    #[Repository('nonExistentRepository')]
    class EntityWithNonExistentRepository extends AbstractEntity
    {
        public function __construct(
            protected string $name = 'test'
        ) {
        }
    }
    
    $app = app();
    $factory = $app->get(EntityFactory::class);
    
    // Using reflection to test private method
    $reflection = new \ReflectionClass($factory);
    $method = $reflection->getMethod('getRepository');
    $method->setAccessible(true);
    
    $entityReflection = new \ReflectionClass(EntityWithNonExistentRepository::class);
    $result = $method->invoke($factory, $entityReflection);
    
    expect($result)->toBeNull();
});

// Testing passing an array as first arg to set()
test('can set entity values with array argument', function () {
    $entity = new EntityTest('John Doe', 20);
    $entity->set(['name' => 'Jane Doe', 'nonConstructProperty' => 'test']);
    expect($entity->name)->toBe('Jane Doe');
    expect($entity->nonConstructProperty)->toBe('test');
});
