<?php

namespace Orkestra\Services\View\Twig;

use Orkestra\Services\View\HtmlTag;

use InvalidArgumentException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class OrkestraExtension extends AbstractExtension
{
	/**
	 * @var HtmlTag[]
	 */
	protected array $headTags = [];

	/**
	 * @var HtmlTag[]
	 */
	protected array $footerTags = [];

	/**
	 * @var HtmlTag
	 */
	protected HtmlTag $htmlBlock;

	public function getFunctions()
	{
		return [
			new TwigFunction('header_tag', $this->enqueueHeaderTag(...)),
			new TwigFunction('script', $this->enqueueScript(...)),
			new TwigFunction('const', $this->enqueueConst(...)),
			new TwigFunction('language', $this->setLanguage(...)),
		];
	}

	/**
	 * @return HtmlTag
	 */
	public function getHtmlBlock(): HtmlTag
	{
		return $this->htmlBlock ??= new HtmlTag('html', ['lang' => 'en']);
	}

	/**
	 * @return HtmlTag[]
	 */
	public function getHead(): array
	{
		return $this->headTags;
	}

	/**
	 * @return HtmlTag[]
	 */
	public function getFooter(): array
	{
		return $this->footerTags;
	}

	protected function setLanguage(string $lang): void
	{
		$this->htmlBlock = $this->getHtmlBlock()->setAttributes(['lang' => $lang]);
	}

	protected function enqueueHeaderTag(string $tag, array $attributes, string $content = ''): void
	{
		$this->headTags[] = new HtmlTag($tag, $attributes, $content);
	}

	protected function enqueueScript(string $src, string $placement = 'head', string $strategy = ''): void
	{
		$expectedPlacements = ['head', 'footer'];
		if (!in_array($placement, $expectedPlacements, true)) {
			throw new InvalidArgumentException('Invalid script placement');
		}

		$expectedStrategies = ['defer', 'async', ''];
		if (!in_array($strategy, $expectedStrategies, true)) {
			throw new InvalidArgumentException('Invalid script strategy');
		}

		$tag = new HtmlTag('script', [
			'src'   => $src,
			'defer' => $strategy === 'defer',
			'async' => $strategy === 'async',
		]);

		if ($placement === 'head') {
			$this->headTags[] = $tag;
			return;
		}

		$this->footerTags[] = $tag;
	}

	protected function enqueueConst(string $name = '', array $value, string $placement = 'head'): void
	{
		$expectedPlacements = ['head', 'footer'];
		if (!in_array($placement, $expectedPlacements, true)) {
			throw new InvalidArgumentException('Invalid script placement');
		}

		$value   = json_encode($value);
		$content = "const {$name} = {$value};";

		$tag = new HtmlTag('script', [], $content);

		if ($placement === 'head') {
			$this->headTags[] = $tag;
			return;
		}

		$this->footerTags[] = $tag;
	}
}
