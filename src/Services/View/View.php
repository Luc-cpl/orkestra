<?php

namespace Orkestra\Services\View;

use Orkestra\Interfaces\ViewInterface;
use Orkestra\Services\View\Twig\OrkestraExtension;
use Twig\Environment;

class View implements ViewInterface
{
	public function __construct(
		protected Environment $twig,
	) {
	}

	public function render($name, array $context = []): string
	{
		$name = rtrim($name, '.twig') . '.twig';

		$content    = $this->twig->render($name, $context);
		$htmlBlock  = $this->twig->getExtension(OrkestraExtension::class)->getHtmlBlock();
		$headData   = $this->twig->getExtension(OrkestraExtension::class)->getHead();
		$footerData = $this->twig->getExtension(OrkestraExtension::class)->getFooter();

		$head = new HtmlTag('head', [], join('', $headData));
		$body = new HtmlTag('body', [], $content . join('', $footerData));

		return '<!DOCTYPE html>' . $htmlBlock->setContent($head . $body);
	}
}
