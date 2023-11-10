<?php

namespace Orkestra\Controllers;

use Orkestra\App;
use Orkestra\Interfaces\ViewInterface;
use Psr\Http\Message\ResponseInterface;
use DI\Attribute\Inject;

/**
 * BaseHtmlController
 */
abstract class BaseHtmlController
{
	#[Inject]
	protected App $app;

	#[Inject]
	protected ViewInterface $view;

	#[Inject]
	protected ResponseInterface $response;
}
