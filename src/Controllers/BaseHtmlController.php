<?php

namespace Orkestra\Controllers;

use DI\Attribute\Inject;
use InvalidArgumentException;
use Orkestra\App;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * BaseHtmlController
 * 
 * @property string $lang
 * @property array  $headers
 * @property array  $scripts
 * @property array  $styles
 * @property string $contentWrapper
 */
abstract class BaseHtmlController
{
	#[Inject]
	protected App $app;

	protected string $lang = 'en';

	protected array $headers = [];
	protected array $scripts = [];
	protected array $styles  = [];

	protected string $contentWrapper = '%s';

	abstract protected function content(ServerRequestInterface $request, array $args): string;

	protected function setContentWrapper(string $wrapper): void
	{
		$this->contentWrapper = $wrapper;
	}

	protected function language(string $lang): void
	{
		$this->lang = $lang;
	}

	protected function script(
		?string $src,
		?string $content,
		string  $placement = 'header',
		string  $strategy  = '',
	): void {
		$expectedPlacements = ['header', 'footer'];
		if (!in_array($placement, $expectedPlacements, true)) {
			throw new InvalidArgumentException('Invalid script placement');
		}

		$expectedStrategies = ['defer', 'async', ''];
		if (!in_array($strategy, $expectedStrategies, true)) {
			throw new InvalidArgumentException('Invalid script strategy');
		}

		$this->scripts[] = (object) compact('placement', 'src', 'content', 'strategy');
	}

	protected function style(
		?string $src,
		?string $content,
	): void {
		$this->styles[] = (object) compact('src', 'content');
	}

	protected function meta(
		string $name,
		string $content,
	): void {
		$this->headerTag('meta', "name=\"{$name}\" content=\"{$content}\"", '');
	}

	protected function title(
		string $title,
	): void {
		$this->headerTag('title', '', $title);
	}

	protected function headerTag(
		string $tag,
		string $attributes,
		string $content,
	): void {
		$this->headers[] = (object) compact('tag', 'attributes', 'content');
	}

	protected function generateHeader(): string
	{
		$tags = [];
		foreach ($this->headers as $header) {
			$tags[] = "<{$header->tag} {$header->attributes}>{$header->content}</{$header->tag}>";
		}
		return join('', $tags);
	}

	protected function generateStyles(): string
	{
		$styles = [];
		foreach ($this->styles as $style) {
			if ($style->src) {
				$styles[] = "<link rel=\"stylesheet\" href=\"{$style->src}\">";
			} else {
				$styles[] = "<style>{$style->content}</style>";
			}
		}
		return join('', $styles);
	}

	protected function generateScripts(string $placement): string
	{
		$scripts = [];
		foreach ($this->scripts as $script) {
			if ($script->placement !== $placement) {
				continue;
			}
			if ($script->src) {
				$scripts[] = "<script src=\"{$script->src}\" {$script->strategy}></script>";
			} else {
				$scripts[] = "<script {$script->strategy}>{$script->content}</script>";
			}
		}
		return join('', $scripts);
	}

	public function __get(string $name)
	{
		return match ($name) {
			'lang'           => $this->lang,
			'headers'        => $this->headers,
			'scripts'        => $this->scripts,
			'styles'         => $this->styles,
			'contentWrapper' => $this->contentWrapper,
			default => null,
		};
	}

	public function	__invoke(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$content = $this->content($request, $args);

		$contentOnlyHeader = $request->getHeader('X-Request-Mode');

		$contentOnly = !empty($contentOnlyHeader) && $contentOnlyHeader[0] === 'content-only';

		$response = $this->app->get(ResponseInterface::class);

		$this->app->hookCall('controller.render', $this, $content, $contentOnly);

		if ($contentOnly) {
			$response->getBody()->write($content);
			return $response->withStatus(200);
		}

		$fullContent = sprintf($this->contentWrapper, $content);

		$response->getBody()->write(<<<HTML
			<!DOCTYPE html>
			<html lang="{$this->lang}">
				<head>
					{$this->generateHeader()}
					{$this->generateScripts('head')}
					{$this->generateStyles()}
				</head>
				<body>
					{$fullContent}
					{$this->generateScripts('footer')}
				</body>
			</html>
		HTML);

		return $response->withStatus(200);
	}
}
