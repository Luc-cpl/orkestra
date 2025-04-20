<?php

use Orkestra\Entities\AbstractEntity;
use Orkestra\Entities\EntityFactory;

// Remove Bencher import if not needed elsewhere, or keep if other tests might use it
// use Pest\Bench\Bencher;

// Define a simple entity for benchmarking
class BenchEntity extends AbstractEntity
{
    public string $publicProp;
    protected string $name;
    protected int $age;
    private string $internalValue;

    public function __construct(
        string $name = '',
        int $age = 0
    ) {
        $this->name = $name;
        $this->age = $age;
    }

    public function set_internal_value(string $value): void
    {
        $this->internalValue = $value;
    }

    protected function getAge(): int
    {
        return $this->age * 2; // Some simple logic
    }
}

// Benchmark creating multiple instances using the set method (manual timing)
test('Entity Creation Performance - Manual Timing', function () {
    $factory = app()->get(EntityFactory::class);
    $count = 10000; // Number of instances to create

    $startTime = microtime(true);

    for ($i = 0; $i < $count; $i++) {
        // Using set() with a mix of constructor, public, and set_ method properties
        $entity = $factory->make(BenchEntity::class, [
            'name' => 'Bench Name ' . $i,
            'publicProp' => 'Public Value ' . $i,
            'internal_value' => 'Set Internal ' . $i,
            // 'age' is handled by constructor
        ]);
    }

    $endTime = microtime(true);
    $duration = $endTime - $startTime;

    dump(sprintf("Manual Benchmark: Creating %d entities took %.4f seconds.", $count, $duration));

    // You can add assertions here if needed, e.g., checking the last entity
    expect($entity)->toBeInstanceOf(BenchEntity::class);
    expect($entity->name)->toBe('Bench Name 9999');

}); // Removed ->repeat(5)

// Benchmark creating multiple instances using direct assignment
test('Entity Creation Performance - Direct Assignment', function () {
    $count = 10000; // Number of instances to create

    $startTime = microtime(true);

    for ($i = 0; $i < $count; $i++) {
        // Using constructor and direct public property assignment
        $entity = new BenchEntity(
            name: 'Bench Name ' . $i
            // age is handled by constructor default
        );
        $entity->publicProp = 'Public Value ' . $i;
        // Explicitly call the setter method to match the behavior invoked by set()
        $entity->set_internal_value('Set Internal ' . $i);
    }

    $endTime = microtime(true);
    $duration = $endTime - $startTime;

    dump(sprintf("Manual Benchmark (Direct): Creating %d entities took %.4f seconds.", $count, $duration));

    // Basic assertion for the last entity
    expect($entity)->toBeInstanceOf(BenchEntity::class);
    expect($entity->name)->toBe('Bench Name 9999');
});

// --- Placeholder for Alternative Implementations ---
// You would need to adapt any alternative benchmarks similarly
// if not using the benchmarking plugin.
