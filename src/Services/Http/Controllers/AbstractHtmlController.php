<?php

namespace Orkestra\Services\Http\Controllers;

use Orkestra\Services\View\Interfaces\ViewInterface;
use Psr\Http\Message\ResponseInterface;
use DI\Attribute\Inject;

/**
 * AbstractHtmlController
 */
abstract class AbstractHtmlController extends AbstractController
{
    #[Inject]
    protected ViewInterface $view;

    /**
     * Render a view
     *
     * @param string $name
     * @param mixed[] $context
     * @return ResponseInterface
     */
    protected function render(string $name, array $context = [], int $status = 200): ResponseInterface
    {
        if ($this->route) {
            $context = array_merge($context, [
                'route' => $this->route->getDefinition(),
            ]);
        }
        $content = $this->view->render($name, $context);
        $this->response->getBody()->write($content);
        return $this->response->withStatus($status);
    }
}
