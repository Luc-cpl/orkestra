<?php

use Orkestra\App;
use Orkestra\Entities\EntityFactory;
use Pest\Support\Container;

if (!function_exists('app')) {
	/**
	 * Return the App instance
	 *
	 * @return App
	 */
	function app(): App
	{
		return Container::getInstance()->get(App::class);
	}
}

if (!function_exists('factory')) {
	/**
	 * Return the EntityFactory instance
	 * with faker enabled
	 *
	 * @return EntityFactory
	 */
	function factory(): EntityFactory
	{
		return Container::getInstance()->get(EntityFactory::class);
	}
}