<?php

namespace Orkestra\Services\View;

use Orkestra\Services\View\Interfaces\ViewInterface;
use Orkestra\Services\Http\Interfaces\RouteAwareInterface;
use Orkestra\Services\Http\Interfaces\RouteInterface;
use Orkestra\Services\View\Twig\OrkestraExtension;
use Twig\Environment;

class View implements ViewInterface, RouteAwareInterface
{
	protected ?RouteInterface $route = null;

	public function __construct(
		protected Environment $twig,
	) {
	}

	public function setRoute(RouteInterface $route): self
	{
		$this->route = $route;
		return $this;
	}

	/**
	 * Render the view
	 *
	 * @param string  $name
	 * @param mixed[] $context
	 * @return string
	 */
	public function render(string $name, array $context = []): string
	{
		$name = explode('.', $name, 1)[0] . '.twig';

		$content    = $this->twig->render($name, $context);
		$htmlBlock  = $this->twig->getExtension(OrkestraExtension::class)->getHtmlBlock();
		$headData   = $this->twig->getExtension(OrkestraExtension::class)->getHead();
		$footerData = $this->twig->getExtension(OrkestraExtension::class)->getFooter();

		$head = new HtmlTag('head', [], join('', $headData));
		$body = new HtmlTag('body', [], $content . join('', $footerData));

		return '<!DOCTYPE html>' . $htmlBlock->setContent($head . $body);
	}
}
