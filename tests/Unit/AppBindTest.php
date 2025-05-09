<?php

use Orkestra\AppBind;
use DI\Container;
use DI\Definition\Helper\CreateDefinitionHelper;

test('can create a bind with class name', function () {
    $container = $this->createMock(Container::class);
    $container->expects($this->once())->method('set')->with(
        $this->equalTo('test'),
        $this->equalTo(DI\autowire(self::class)),
    );

    expect(new AppBind('test', self::class, container: $container))->toBeInstanceOf(AppBind::class);
});

test('can create a bind with class name and no autowire', function () {
    $container = $this->createMock(Container::class);
    $container->expects($this->once())->method('set')->with(
        $this->equalTo('test'),
        $this->equalTo(DI\create(self::class)),
    );

    expect(new AppBind('test', self::class, false, $container))->toBeInstanceOf(AppBind::class);
});

test('can create a bind with closure', function () {
    $container = $this->createMock(Container::class);
    $container->expects($this->once())->method('set')->with(
        $this->equalTo('test'),
        $this->equalTo(fn () => true),
    );
    expect(new AppBind('test', fn () => true, container: $container))->toBeInstanceOf(AppBind::class);
});

test('can not create a bind with non existent class', function () {
    new AppBind('test', 'nonExistentClass');
})->throws(Exception::class);

test('can set constructor params', function () {
    $mockedService = $this->createMock(CreateDefinitionHelper::class);
    $mockedService->expects($this->once())
        ->method('constructor')
        ->with(
            $this->equalTo('testValue1'),
            $this->equalTo('testValue2')
        );

    $bind = new AppBind('test', $mockedService);
    $bind->constructor('testValue1', 'testValue2');
});

test('can not set constructor params with non class service', function () {
    $bind = new AppBind('test', fn () => true);
    $bind->constructor('testValue1');
})->throws(Exception::class);

test('can set service property', function () {
    $mockedService = $this->createMock(CreateDefinitionHelper::class);
    $mockedService->expects($this->once())
        ->method('property')
        ->with(
            $this->equalTo('testProperty'),
            $this->equalTo('testValue')
        );

    $bind = new AppBind('test', $mockedService);
    $bind->property('testProperty', 'testValue');
});

test('can not set service property with non class service', function () {
    $bind = new AppBind('test', fn () => true);
    $bind->property('testProperty', 'testValue');
})->throws(Exception::class);

test('can inject method parameters into service', function () {
    $mockedService = $this->createMock(CreateDefinitionHelper::class);
    $mockedService->expects($this->once())
        ->method('method')
        ->with(
            $this->equalTo('testMethod'),
            $this->equalTo('testValue1'),
            $this->equalTo('testValue2')
        );

    $bind = new AppBind('test', $mockedService);

    $bind->method('testMethod', 'testValue1', 'testValue2');
});

test('can not inject method parameters into non class service', function () {
    $this->expectException(Exception::class);
    $bind = new AppBind('test', fn () => true);
    $bind->method('testMethod', 'testValue1', 'testValue2');
});
