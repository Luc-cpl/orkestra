<?php

namespace Orkestra\Providers;

use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;

use FastRoute;

class RoutingProvider implements ProviderInterface
{
	/**
	 * Register services with the container.
	 * We can use the container to bind services to the app.
	 * 
	 * Do not use the container to resolve services at this point.
	 *
	 * @param App $app
	 * @return void
	 */
	public function register(App $app): void
	{
		$app->singleton('router', function() {
			return FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $r) {
				$r->addRoute('GET', '/', 'Webei\Plugins\Admin\Controllers\IndexController');
			});
		});
	}
	

	/**
	 * Here we can use the container to resolve and start services.
	 * 
	 * @param App $app
	 * @return void
	 */
	public function boot(App $app): void
	{
		$httpMethod = $_SERVER['REQUEST_METHOD'];
		$uri = $_SERVER['REQUEST_URI'];

		// Strip query string (?foo=bar) and decode URI
		if (false !== $pos = strpos($uri, '?')) {
			$uri = substr($uri, 0, $pos);
		}
		$uri = rawurldecode($uri);

		$routeInfo = $app->get('router')->dispatch($httpMethod, $uri);
	}
}