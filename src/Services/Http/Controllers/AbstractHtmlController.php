<?php

namespace Orkestra\Services\Http\Controllers;

use Orkestra\App;
use Orkestra\Services\Http\Interfaces\RouteAwareInterface;
use Orkestra\Services\Http\Interfaces\RouteInterface;
use Orkestra\Services\View\Interfaces\ViewInterface;
use Psr\Http\Message\ResponseInterface;
use DI\Attribute\Inject;

/**
 * AbstractHtmlController
 */
abstract class AbstractHtmlController implements RouteAwareInterface
{
	#[Inject]
	protected App $app;

	#[Inject]
	protected ViewInterface $view;

	#[Inject]
	protected ResponseInterface $response;

	protected ?RouteInterface $route = null;

	protected int $status = 200;

	/**
	 * @return $this
	 */
	public function setRoute(RouteInterface $route): self
	{
		$this->route = $route;
		return $this;
	}

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
		if ($this->route) {
			$context = array_merge($context, [
				'route' => $this->route->getDefinition(),
			]);
		}
		$content = $this->view->render($name, $context);
		$this->response->getBody()->write($content);
		return $this->response->withStatus($this->status);
	}
}
