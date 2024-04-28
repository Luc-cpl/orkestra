<?php

use Orkestra\App;
use Orkestra\Providers\ViewProvider;
use Orkestra\Services\View\Interfaces\ViewInterface;
use Orkestra\Services\View\View;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

beforeEach(function () {
	app()->provider(ViewProvider::class);
});

test('can render a html view', function () {
	/** @var ViewInterface */
	$view = app()->get(ViewInterface::class);

	$rendered = $view->render('index', ['name' => 'World']);
	expect($rendered)->toBe('<!DOCTYPE html><html lang="en"><head></head><body>Hello World</body></html>');
});

test('can render a html view with different language', function () {
	/** @var ViewInterface */
	$view = app()->get(ViewInterface::class);

	$rendered = $view->render('lang', ['lang' => 'pt_BR']);

	expect($rendered)->toBe('<!DOCTYPE html><html lang="pt_BR"><head></head><body></body></html>');
});

test('can render a html view with head tags', function () {
	/** @var ViewInterface */
	$view = app()->get(ViewInterface::class);

	$rendered = $view->render('head', ['name' => 'World']);

	expect($rendered)->toBe('<!DOCTYPE html><html lang="en"><head><title>Hello World</title></head><body></body></html>');
});

test('can render script tags', function () {
	/** @var ViewInterface */
	$view = app()->get(ViewInterface::class);
	app()->config()->set('public_url', 'http://localhost');
	$rendered = $view->render('script');

	// Remove line breaks added in template
	$rendered = preg_replace('/\s+/', ' ', $rendered);
	expect($rendered)->toBe('<!DOCTYPE html><html lang="en"><head><script src="http://localhost/head1.js" type=""></script><script src="http://localhost/head2.js" type=""></script><script src="http://localhost/defered.js" defer type=""></script><script src="http://localhost/async.js" async type=""></script><script src="http://localhost/module.js" type="module"></script></head><body> <script src="http://localhost/footer.js" type=""></script></body></html>');
});

test('can render js constants', function () {
	/** @var ViewInterface */
	$view = app()->get(ViewInterface::class);
	$context = [ 'data' => ['test' => 'test', 'test2' => 'test2'] ];
	$rendered = $view->render('js-constant', $context);

	// Remove line breaks added in template
	$rendered = preg_replace('/\s+/', ' ', $rendered);
	expect($rendered)->toBe('<!DOCTYPE html><html lang="en"><head><script>const myConst = {"test":"test","test2":"test2"};</script></head><body> <script>const myConst2 = {"test":"test","test2":"test2"};</script></body></html>');
});

test('can respect script and const order', function () {
	/** @var ViewInterface */
	$view = app()->get(ViewInterface::class);
	$rendered = $view->render('script-const-order');

	// Remove line breaks added in template
	$rendered = preg_replace('/\s+/', ' ', $rendered);
	expect($rendered)->toBe('<!DOCTYPE html><html lang="en"><head><script src="/head1.js" type=""></script><script>const const1 = {"test":"test1"};</script><script src="/head2.js" type=""></script><script>const const2 = {"test":"test2"};</script></head><body> <script>const constFooter1 = {"test":"test1"};</script><script src="/footer.js" type=""></script><script>const constFooter2 = {"test":"test2"};</script></body></html>');
});

test('can render with css links', function () {
	/** @var ViewInterface */
	$view = app()->get(ViewInterface::class);
	app()->config()->set('public_url', 'http://localhost');
	$rendered = $view->render('css');

	// Remove line breaks added in template
	$rendered = preg_replace('/\s+/', ' ', $rendered);
	expect($rendered)->toBe('<!DOCTYPE html><html lang="en"><head><link rel="stylesheet" href="http://localhost/style1.css" /><link rel="stylesheet" href="http://localhost/style2.css" /></head><body> </body></html>');
});

test('can autoload twig runtime extensions with app container', function () {
	class RuntimeExtension
	{
		public function __construct(protected App $app) {}
		public function test($string)
		{
			return $this->app->get('hello') . ' ' . $string;
		}
	}

	app()->bind('hello', fn () => 'Hello');
	$mock = Mockery::mock(AbstractExtension::class)->makePartial();
	$mock->shouldReceive('getFunctions')->andReturn([new TwigFunction('test', [RuntimeExtension::class, 'test'])]);
	app()->get(Environment::class)->addExtension($mock);

	/** @var View */
	$view = app()->get(ViewInterface::class);
	$rendered = $view->render('runtime-extension');
	expect($rendered)->toBe('<!DOCTYPE html><html lang="en"><head></head><body>Hello World</body></html>');
});