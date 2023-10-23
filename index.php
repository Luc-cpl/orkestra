<?php


require_once __DIR__ . '/vendor/autoload.php';

use Orkestra\App;
use Orkestra\Facades\HooksFacade;
use Orkestra\Proxies\WordPress\HooksProxy as WPHooksProxy;

$app = new App();

$app->addService('hooks', fn() => new HooksFacade(new WPHooksProxy()));

$app->hooks->remove('init', function ($last) {
	return $last . ' tested 0';
}, 5);

echo $app->hooks->query('init' , 'test') . PHP_EOL;