<?php

namespace Tests\Unit\Services\View\Twig;

use Mockery;
use Orkestra\Services\View\Twig\RuntimeLoader;
use Psr\Container\ContainerInterface;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

test('RuntimeLoader can be instantiated with a container', function () {
    $container = Mockery::mock(ContainerInterface::class);
    
    $loader = new RuntimeLoader($container);
    
    expect($loader)->toBeInstanceOf(RuntimeLoader::class);
});

test('RuntimeLoader returns null for class not in container', function () {
    // Create a test runtime class internal to the test
    $testRuntimeClass = 'TestRuntime';
    
    $container = Mockery::mock(ContainerInterface::class);
    
    $container->shouldReceive('has')
        ->once()
        ->with($testRuntimeClass)
        ->andReturn(false);
    
    $loader = new RuntimeLoader($container);
    $result = $loader->load($testRuntimeClass);
    
    expect($result)->toBeNull();
});

test('RuntimeLoader loads class from container', function () {
    // Create a test runtime class
    $testRuntime = new class() {
        private string $value = 'test';
        
        public function getValue(): string
        {
            return $this->value;
        }
    };
    $testRuntimeClass = get_class($testRuntime);
    
    $container = Mockery::mock(ContainerInterface::class);
    
    $container->shouldReceive('has')
        ->once()
        ->with($testRuntimeClass)
        ->andReturn(true);
    
    $container->shouldReceive('get')
        ->once()
        ->with($testRuntimeClass)
        ->andReturn($testRuntime);
    
    $loader = new RuntimeLoader($container);
    $result = $loader->load($testRuntimeClass);
    
    expect($result)->toBe($testRuntime);
    expect($result->getValue())->toBe('test');
});

test('RuntimeLoader loads class with constructor arguments', function () {
    // Create a test runtime class
    $testRuntime = new class('custom value') {
        private string $value;
        
        public function __construct(string $value)
        {
            $this->value = $value;
        }
        
        public function getValue(): string
        {
            return $this->value;
        }
    };
    $testRuntimeClass = get_class($testRuntime);
    
    $container = Mockery::mock(ContainerInterface::class);
    
    $container->shouldReceive('has')
        ->once()
        ->with($testRuntimeClass)
        ->andReturn(true);
    
    $container->shouldReceive('get')
        ->once()
        ->with($testRuntimeClass)
        ->andReturn($testRuntime);
    
    $loader = new RuntimeLoader($container);
    $result = $loader->load($testRuntimeClass);
    
    expect($result)->toBe($testRuntime);
    expect($result->getValue())->toBe('custom value');
});

test('RuntimeLoader implements RuntimeLoaderInterface', function () {
    $container = Mockery::mock(ContainerInterface::class);
    $loader = new RuntimeLoader($container);
    
    expect($loader)->toBeInstanceOf(RuntimeLoaderInterface::class);
});

test('RuntimeLoader uses container to resolve dependencies', function () {
    // Create a test runtime class
    $testRuntime = new class('initial value') {
        private string $value;
        
        public function __construct(string $value)
        {
            $this->value = $value;
        }
        
        public function getValue(): string
        {
            return $this->value;
        }
    };
    $testRuntimeClass = get_class($testRuntime);
    
    // Setup app container with an actual implementation
    $app = app();
    $app->bind($testRuntimeClass, fn() => new $testRuntimeClass('container resolved'));
    
    // Create loader with the real app container
    $loader = new RuntimeLoader($app);
    $result = $loader->load($testRuntimeClass);
    
    expect($result)->toBeInstanceOf($testRuntimeClass);
    expect($result->getValue())->toBe('container resolved');
}); 