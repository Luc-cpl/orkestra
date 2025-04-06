<?php

use Orkestra\Interfaces\ConfigurationInterface;
use Orkestra\Services\View\HtmlTag;
use Orkestra\Services\View\Twig\OrkestraExtension;

test('can get twig functions', function () {
    $config = Mockery::mock(ConfigurationInterface::class);
    $extension = new OrkestraExtension($config);

    $functions = $extension->getFunctions();

    expect($functions)->toBeArray();
    expect(count($functions))->toBe(5); // Check we have the expected number of functions

    // Verify each function exists and has the correct name
    $functionNames = array_map(fn ($func) => $func->getName(), $functions);
    expect($functionNames)->toContain('header_tag');
    expect($functionNames)->toContain('script');
    expect($functionNames)->toContain('style');
    expect($functionNames)->toContain('const');
    expect($functionNames)->toContain('language');
});

test('can get head tags', function () {
    $config = Mockery::mock(ConfigurationInterface::class);
    $extension = new OrkestraExtension($config);

    // Initially, headTags should be empty
    expect($extension->getHead())->toBeArray();
    expect($extension->getHead())->toBeEmpty();

    // Add a head tag
    $reflector = new ReflectionClass($extension);
    $property = $reflector->getProperty('headTags');
    $property->setAccessible(true);
    $property->setValue($extension, [new HtmlTag('title', [], 'Test Title')]);

    // Now headTags should contain one item
    expect($extension->getHead())->toHaveCount(1);
    expect($extension->getHead()[0]->tag)->toBe('title');
    expect($extension->getHead()[0]->content)->toBe('Test Title');
});

test('can get footer tags', function () {
    $config = Mockery::mock(ConfigurationInterface::class);
    $extension = new OrkestraExtension($config);

    // Initially, footerTags should be empty
    expect($extension->getFooter())->toBeArray();
    expect($extension->getFooter())->toBeEmpty();

    // Add a footer tag
    $reflector = new ReflectionClass($extension);
    $property = $reflector->getProperty('footerTags');
    $property->setAccessible(true);
    $property->setValue($extension, [new HtmlTag('script', [], 'console.log("test");')]);

    // Now footerTags should contain one item
    expect($extension->getFooter())->toHaveCount(1);
    expect($extension->getFooter()[0]->tag)->toBe('script');
    expect($extension->getFooter()[0]->content)->toBe('console.log("test");');
});

test('can get and set html block', function () {
    $config = Mockery::mock(ConfigurationInterface::class);
    $extension = new OrkestraExtension($config);

    // Get default html block
    $htmlBlock = $extension->getHtmlBlock();
    expect($htmlBlock)->toBeInstanceOf(HtmlTag::class);
    expect($htmlBlock->tag)->toBe('html');
    expect($htmlBlock->attributes)->toBe(['lang' => 'en']);

    // Set language
    $reflector = new ReflectionClass($extension);
    $method = $reflector->getMethod('setLanguage');
    $method->setAccessible(true);
    $method->invoke($extension, 'fr');

    // Check that the html block has been updated
    $htmlBlock = $extension->getHtmlBlock();
    expect($htmlBlock->attributes)->toBe(['lang' => 'fr']);
});

test('can enqueue header tag', function () {
    $config = Mockery::mock(ConfigurationInterface::class);
    $extension = new OrkestraExtension($config);

    // Use reflection to call protected method
    $reflector = new ReflectionClass($extension);
    $method = $reflector->getMethod('enqueueHeaderTag');
    $method->setAccessible(true);

    // Call the method with various parameters
    $method->invoke($extension, 'meta', ['charset' => 'utf-8']);
    $method->invoke($extension, 'title', [], 'Page Title');
    $method->invoke($extension, 'meta', ['name' => 'description', 'content' => 'Page description']);

    // Check that the tags were added to headTags
    $headTags = $extension->getHead();
    expect($headTags)->toHaveCount(3);
    expect($headTags[0]->tag)->toBe('meta');
    expect($headTags[0]->attributes)->toBe(['charset' => 'utf-8']);
    expect($headTags[1]->tag)->toBe('title');
    expect($headTags[1]->content)->toBe('Page Title');
    expect($headTags[2]->tag)->toBe('meta');
    expect($headTags[2]->attributes)->toBe(['name' => 'description', 'content' => 'Page description']);
});

test('script enqueuing with invalid placement throws exception', function () {
    $config = Mockery::mock(ConfigurationInterface::class);
    $extension = new OrkestraExtension($config);

    $reflector = new ReflectionClass($extension);
    $method = $reflector->getMethod('enqueueScript');
    $method->setAccessible(true);

    // Test invalid placement (this should cover line 97)
    $method->invoke($extension, 'test.js', ['placement' => 'invalid']);
})->throws(InvalidArgumentException::class, 'Invalid script placement');

test('script enqueuing with invalid strategy throws exception', function () {
    $config = Mockery::mock(ConfigurationInterface::class);
    $extension = new OrkestraExtension($config);

    $reflector = new ReflectionClass($extension);
    $method = $reflector->getMethod('enqueueScript');
    $method->setAccessible(true);

    // Test invalid strategy (this should cover line 102)
    $method->invoke($extension, 'test.js', ['strategy' => 'invalid']);
})->throws(InvalidArgumentException::class, 'Invalid script strategy');

test('can enqueue script with absolute URL', function () {
    $config = Mockery::mock(ConfigurationInterface::class);
    $extension = new OrkestraExtension($config);

    $reflector = new ReflectionClass($extension);
    $method = $reflector->getMethod('enqueueScript');
    $method->setAccessible(true);

    // Test with absolute URL
    $method->invoke($extension, 'https://example.com/test.js');

    $headTags = $extension->getHead();
    expect($headTags)->toHaveCount(1);
    expect($headTags[0]->tag)->toBe('script');
    expect($headTags[0]->attributes['src'])->toBe('https://example.com/test.js');
});

test('can enqueue script with relative URL', function () {
    $config = Mockery::mock(ConfigurationInterface::class);
    $config->shouldReceive('get')->with('assets')->andReturn('https://assets.example.com');
    $extension = new OrkestraExtension($config);

    $reflector = new ReflectionClass($extension);
    $method = $reflector->getMethod('enqueueScript');
    $method->setAccessible(true);

    // Test with relative URL
    $method->invoke($extension, '/js/test.js');

    $headTags = $extension->getHead();
    expect($headTags)->toHaveCount(1);
    expect($headTags[0]->tag)->toBe('script');
    expect($headTags[0]->attributes['src'])->toBe('https://assets.example.com/js/test.js');
});

test('can enqueue script in footer', function () {
    $config = Mockery::mock(ConfigurationInterface::class);
    $extension = new OrkestraExtension($config);

    $reflector = new ReflectionClass($extension);
    $method = $reflector->getMethod('enqueueScript');
    $method->setAccessible(true);

    // Test with footer placement
    $method->invoke($extension, 'https://example.com/test.js', ['placement' => 'footer']);

    $footerTags = $extension->getFooter();
    expect($footerTags)->toHaveCount(1);
    expect($footerTags[0]->tag)->toBe('script');
    expect($footerTags[0]->attributes['src'])->toBe('https://example.com/test.js');
});

test('can enqueue stylesheet with absolute URL', function () {
    $config = Mockery::mock(ConfigurationInterface::class);
    $extension = new OrkestraExtension($config);

    $reflector = new ReflectionClass($extension);
    $method = $reflector->getMethod('enqueueStyle');
    $method->setAccessible(true);

    // Test with absolute URL
    $method->invoke($extension, 'https://example.com/style.css');

    $headTags = $extension->getHead();
    expect($headTags)->toHaveCount(1);
    expect($headTags[0]->tag)->toBe('link');
    expect($headTags[0]->attributes['href'])->toBe('https://example.com/style.css');
    expect($headTags[0]->attributes['rel'])->toBe('stylesheet');
});

test('can enqueue stylesheet with relative URL', function () {
    $config = Mockery::mock(ConfigurationInterface::class);
    $config->shouldReceive('get')->with('assets')->andReturn('https://assets.example.com/');
    $extension = new OrkestraExtension($config);

    $reflector = new ReflectionClass($extension);
    $method = $reflector->getMethod('enqueueStyle');
    $method->setAccessible(true);

    // Test with relative URL
    $method->invoke($extension, 'css/style.css');

    $headTags = $extension->getHead();
    expect($headTags)->toHaveCount(1);
    expect($headTags[0]->tag)->toBe('link');
    expect($headTags[0]->attributes['href'])->toBe('https://assets.example.com/css/style.css');
    expect($headTags[0]->attributes['rel'])->toBe('stylesheet');
});

test('const enqueuing with invalid placement throws exception', function () {
    $config = Mockery::mock(ConfigurationInterface::class);
    $extension = new OrkestraExtension($config);

    $reflector = new ReflectionClass($extension);
    $method = $reflector->getMethod('enqueueConst');
    $method->setAccessible(true);

    // Test invalid placement (this should cover line 153)
    $method->invoke($extension, 'TEST', 'value', 'invalid');
})->throws(InvalidArgumentException::class, 'Invalid script placement');

test('can enqueue const in head', function () {
    $config = Mockery::mock(ConfigurationInterface::class);
    $extension = new OrkestraExtension($config);

    $reflector = new ReflectionClass($extension);
    $method = $reflector->getMethod('enqueueConst');
    $method->setAccessible(true);

    // Test with head placement (default)
    $method->invoke($extension, 'TEST', ['foo' => 'bar']);

    $headTags = $extension->getHead();
    expect($headTags)->toHaveCount(1);
    expect($headTags[0]->tag)->toBe('script');
    expect($headTags[0]->content)->toBe('const TEST = {"foo":"bar"};');
});

test('can enqueue const in footer', function () {
    $config = Mockery::mock(ConfigurationInterface::class);
    $extension = new OrkestraExtension($config);

    $reflector = new ReflectionClass($extension);
    $method = $reflector->getMethod('enqueueConst');
    $method->setAccessible(true);

    // Test with footer placement
    $method->invoke($extension, 'TEST', 'value', 'footer');

    $footerTags = $extension->getFooter();
    expect($footerTags)->toHaveCount(1);
    expect($footerTags[0]->tag)->toBe('script');
    expect($footerTags[0]->content)->toBe('const TEST = "value";');
});
