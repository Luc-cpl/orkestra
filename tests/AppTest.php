<?php

use PHPUnit\Framework\TestCase;
use Orkestra\App;
use Orkestra\Interfaces\ConfigurationInterface;
use Orkestra\Interfaces\ProviderInterface;
use Psr\Container\NotFoundExceptionInterface;

class AppTest extends TestCase
{
    private $config;
    private App $app;

    protected function setUp(): void
    {
        $this->config = $this->createMock(ConfigurationInterface::class);
        $this->app = new App($this->config);
    }

    /**
     * @covers \Orkestra\App::__construct
     * @covers \Orkestra\Traits\AppContainerTrait::initContainer
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(App::class, $this->app);
    }

    /**
     * @covers \Orkestra\App::slug
     */
    public function testSlug(): void
    {
        $this->config->method('get')->willReturn('testSlug');
        $this->assertEquals('testSlug', $this->app->slug());
    }

    /**
     * @covers \Orkestra\App::config
     */
    public function testConfig(): void
    {
        $this->assertSame($this->config, $this->app->config());
    }

    /**
     * @covers \Orkestra\Traits\AppContainerTrait::get
     */
    public function testGetWithNonExistentKey(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->app->get('nonExistentKey');
    }

    /**
     * @covers \Orkestra\Traits\AppContainerTrait::get
     */
    public function testGetWithExistentKey(): void
    {
        $this->app->bind('test', fn() => 'testValue');
        $this->assertEquals('testValue', $this->app->get('test'));
    }

    /**
     * @covers \Orkestra\Traits\AppContainerTrait::get
     */
    public function testGetWithConstructorParams(): void
    {
        $this->app->bind('test', fn($param) => $param);
        $this->assertEquals('testValue', $this->app->get('test', ['param' => 'testValue']));
    }    

    /**
     * @covers \Orkestra\Traits\AppContainerTrait::provider
     */
    public function testProviderWithNonExistentClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->app->provider('testProvider');
    }

    /**
     * @covers \Orkestra\Traits\AppContainerTrait::provider
     */
    public function testProviderWithInvalidClass(): void
    {
        $nonProviderClass = new class {};
        $this->expectException(InvalidArgumentException::class);
        $this->app->provider($nonProviderClass::class);
    }

    /**
     * @covers \Orkestra\Traits\AppContainerTrait::provider
     */
    public function testProviderWithValidClass(): void
    {
        $providerClass = $this->createMock(ProviderInterface::class)::class;
        $this->app->provider($providerClass);
        $provider = $this->app->get($providerClass);
        $provider->expects($this->once())->method('register');
        // Set a property to ensure we get the same instance back
        $provider->test = 'testValue';
        $this->assertEquals($provider, $this->app->get($providerClass));
    }

    /**
     * @covers \Orkestra\Traits\AppContainerTrait::bind
     * @covers \Orkestra\AppBind
     */
    public function testBindWithValue(): void
    {
        $value = 'testValue';
        $this->expectException(InvalidArgumentException::class);
        $this->app->bind('test', $value);
    }

    /**
     * @covers \Orkestra\Traits\AppContainerTrait::bind
     */
    public function testBindWithCallback(): void
    {
        $callback = fn() => 'testValue';
        $this->app->bind('test', $callback);
        $this->assertEquals('testValue', $this->app->get('test'));
    }

    /**
     * @covers \Orkestra\Traits\AppContainerTrait::bind
     */
    public function testBindWithClassName(): void
    {
        $class = new class {
            public $value = 'testValue';
        };

        $this->app->bind('testClassString', $class::class);
        $this->assertInstanceOf(get_class($class), $this->app->get('testClassString'));
        $instance = $this->app->get('testClassString');
        $instance->value = 'testValue2';
        $this->assertNotEquals($instance, $this->app->get('testClassString'));
    }

    /**
     * @covers \Orkestra\Traits\AppContainerTrait::bind
     */
    public function testBindWithObjectInstance(): void
    {
        $class = new class {
            public $value = 'testValue';
        };

        $this->app->bind('testClass', $class);
        $this->assertInstanceOf(get_class($class), $this->app->get('testClass'));
        $this->assertEquals($class, $this->app->get('testClass'));
    }

    /**
     * @covers \Orkestra\Traits\AppContainerTrait::singleton
     * @covers \Orkestra\Traits\AppContainerTrait::get
     */
    public function testSingleton(): void
    {
        $class = new class {
            public $value = 'testValue';
        };

        $this->app->singleton('testClassString', $class::class);
        $this->assertInstanceOf(get_class($class), $this->app->get('testClassString'));
        $instance = $this->app->get('testClassString');
        $instance->value = 'testValue2';
        $this->assertEquals($instance, $this->app->get('testClassString'));
    }

    /**
     * @covers \Orkestra\Traits\AppContainerTrait::runIfAvailable
     */
    public function testRunIfAvailableWithExistingClass(): void
    {
        $providerClass = $this->createMock(ProviderInterface::class)::class;
        $this->app->provider($providerClass);
        $value = $this->app->runIfAvailable($providerClass, fn() => 'testValue');
        $this->assertEquals('testValue', $value);
    }

    /**
     * @covers \Orkestra\Traits\AppContainerTrait::runIfAvailable
     */
    public function testRunIfAvailableWithNonExistingClass(): void
    {
        $value = $this->app->runIfAvailable('notExistClass', fn() => 'testValue');
        $this->assertNull($value);
    }

    /**
     * @covers \Orkestra\Traits\AppContainerTrait::has
     */
    public function testHas(): void
    {
        $this->assertFalse($this->app->has('test'));
        $this->app->bind('test', fn() => 'testValue');
        $this->assertTrue($this->app->has('test'));
    }

    /**
     * @covers \Orkestra\App::boot
     * @covers \Orkestra\Traits\AppContainerTrait::getProviders
     */
    public function testBoot(): void
    {
        $this->expectNotToPerformAssertions();
        $providerClass = $this->createMock(ProviderInterface::class)::class;
        $this->app->provider($providerClass);
        $provider = $this->app->get($providerClass);
        $provider->expects($this->once())->method('boot')->with($this->app);

        $this->app->boot();
    }

    /**
     * @covers \Orkestra\App::run
     */
    public function testRunThrowsExceptionWhenCalledTwice(): void
    {
        $this->expectException(Exception::class);

        $this->app->boot();
        $this->app->boot(); // This should throw an exception
    }
}