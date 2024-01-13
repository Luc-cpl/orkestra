<?php

use Orkestra\AppBind;

use PHPUnit\Framework\TestCase;
use DI\Container;
use DI\Definition\Helper\CreateDefinitionHelper;

class BindClass{}

class AppBindTest extends TestCase
{
	private $container;

    protected function setUp(): void
    {
        $this->container = $this->createMock(Container::class);
    }

    /**
     * @covers \Orkestra\App::__construct
     */
    public function testConstructWithClass(): void
	{
		// Check if the container set method was called with the correct arguments
		$this->container->expects($this->once())->method('set')->with(
			$this->equalTo('test'),
			$this->equalTo(DI\autowire(self::class)),
		);

		$this->assertInstanceOf(AppBind::class, new AppBind($this->container, 'test', self::class));
    }

	/**
     * @covers \Orkestra\App::__construct
     */
    public function testConstructWithClassAndNoAutowire(): void
	{
		// Check if the container set method was called with the correct arguments
		$this->container->expects($this->once())->method('set')->with(
			$this->equalTo('test'),
			$this->equalTo(DI\create(self::class)),
		);

		$this->assertInstanceOf(AppBind::class, new AppBind($this->container, 'test', self::class, false));
	}

	/**
	 * @covers \Orkestra\App::__construct
	 */
	public function testConstructWithFunction(): void
	{
		$this->container->expects($this->once())->method('set')->with(
			$this->equalTo('test'),
			$this->equalTo(fn () => true),
		);
		$this->assertInstanceOf(AppBind::class, new AppBind($this->container, 'test', fn () => true));
	}

	/**
	 * @covers \Orkestra\App::__construct
	 */
	public function testConstructWithNonClassString(): void
	{
		$this->expectException(Exception::class);
		new AppBind($this->container, 'test', 'nonExistentClass');
	}

	/**
	 * @covers \Orkestra\AppBind::constructor
	 */
	public function testConstructor(): void
	{
		// Mock the existing service so we can test the constructor method
		$mockedService = $this->createMock(CreateDefinitionHelper::class);
		$mockedService->expects($this->once())
			->method('constructor')
			->with(
				$this->equalTo('testValue1'),
				$this->equalTo('testValue2')
			);

		$bind = new AppBind($this->container, 'test', $mockedService);

		$bind->constructor('testValue1', 'testValue2');
	}

	/**
	 * @covers \Orkestra\AppBind::constructor
	 */
	public function testConstructorWithNonClassService(): void
	{
		$this->expectException(Exception::class);
		$bind = new AppBind($this->container, 'test', fn () => true);
		$bind->constructor('testValue1');
	}

	/**
	 * @covers \Orkestra\AppBind::constructor
	 */
	public function testProperty(): void
	{
		// Mock the existing service so we can test the property method
		$mockedService = $this->createMock(CreateDefinitionHelper::class);
		$mockedService->expects($this->once())
			->method('property')
			->with(
				$this->equalTo('testProperty'),
				$this->equalTo('testValue')
			);

		$bind = new AppBind($this->container, 'test', $mockedService);

		$bind->property('testProperty', 'testValue');
	}

	/**
	 * @covers \Orkestra\AppBind::property
	 */
	public function testPropertyWithNonClassService(): void
	{
		$this->expectException(Exception::class);
		$bind = new AppBind($this->container, 'test', fn () => true);
		$bind->property('testProperty', 'testValue');
	}

	/**
	 * @covers \Orkestra\AppBind::method
	 */
	public function testMethod(): void
	{
		// Mock the existing service so we can test the method method
		$mockedService = $this->createMock(CreateDefinitionHelper::class);
		$mockedService->expects($this->once())
			->method('method')
			->with(
				$this->equalTo('testMethod'),
				$this->equalTo('testValue1'),
				$this->equalTo('testValue2')
			);

		$bind = new AppBind($this->container, 'test', $mockedService);

		$bind->method('testMethod', 'testValue1', 'testValue2');
	}

	/**
	 * @covers \Orkestra\AppBind::method
	 */
	public function testMethodWithNonClassService(): void
	{
		$this->expectException(Exception::class);
		$bind = new AppBind($this->container, 'test', fn () => true);
		$bind->method('testMethod', 'testValue1', 'testValue2');
	}
}