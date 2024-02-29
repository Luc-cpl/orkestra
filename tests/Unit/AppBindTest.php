<?php

use Orkestra\AppBind;
use \DI\create;
use \DI\autowire;
use DI\Container;
use DI\Definition\Helper\CreateDefinitionHelper;

beforeEach(function () {
    $this->container = $this->createMock(Container::class);
});

test('can create a bind with class name', function () {
    $this->container->expects($this->once())->method('set')->with(
        $this->equalTo('test'),
        $this->equalTo(DI\autowire(self::class)),
    );

    expect(new AppBind($this->container, 'test', self::class))->toBeInstanceOf(AppBind::class);
});

test('can create a bind with class name and no autowire', function () {
    // Check if the container set method was called with the correct arguments
    $this->container->expects($this->once())->method('set')->with(
        $this->equalTo('test'),
        $this->equalTo(DI\create(self::class)),
    );

    expect(new AppBind($this->container, 'test', self::class, false))->toBeInstanceOf(AppBind::class);
});

test('can create a bind with closure', function () {
    $this->container->expects($this->once())->method('set')->with(
        $this->equalTo('test'),
        $this->equalTo(fn () => true),
    );
    expect(new AppBind($this->container, 'test', fn () => true))->toBeInstanceOf(AppBind::class);
});

test('can not create a bind with non existent class', function () {
    new AppBind($this->container, 'test', 'nonExistentClass');
})->throws(Exception::class);

test('can set constructor params', function () {
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
});

test('can not set constructor params with non class service', function () {
    $bind = new AppBind($this->container, 'test', fn () => true);
    $bind->constructor('testValue1');
})->throws(Exception::class);

test('can set service property', function () {
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
});

test('can not set service property with non class service', function () {
    $bind = new AppBind($this->container, 'test', fn () => true);
    $bind->property('testProperty', 'testValue');
})->throws(Exception::class);

test('can inject method parameters into service', function () {
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
});

test('can not inject method parameters into non class service', function () {
    $this->expectException(Exception::class);
    $bind = new AppBind($this->container, 'test', fn () => true);
    $bind->method('testMethod', 'testValue1', 'testValue2');
});