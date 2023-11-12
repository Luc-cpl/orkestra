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

	protected int $status = 200;

	protected function setStatus(int $status): self
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * Render a view
	 *
	 * @param string $name
	 * @param mixed[] $context
	 * @return ResponseInterface
	 */
	protected function render(string $name, array $context = []): ResponseInterface
	{
		$content = $this->view->render($name, $context);
		$this->response->getBody()->write($content);
		return $this->response->withStatus($this->status);
	}
}
