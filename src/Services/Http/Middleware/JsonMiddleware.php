<?php

namespace Orkestra\Services\Http\Middleware;

use League\Route\Http\Exception\BadRequestException;
use League\Route\Strategy\JsonStrategy;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class JsonMiddleware extends AbstractMiddleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $contentType = $request->getHeaderLine('Content-Type');
        $strategy = $this->route?->getStrategy();

        if ($strategy instanceof JsonStrategy || strpos($contentType, 'application/json') === 0) {
            $body = $request->getBody()->getContents();

            if (empty($body)) {
                return $handler->handle($request);
            }

            /** @var ?mixed[] $jsonData */
            $jsonData = json_decode($body, true);

            if ($jsonData === null) {
                throw new BadRequestException('The JSON data in the request body is invalid.');
            }

            // Replace the request body with the JSON data
            $request = $request->withParsedBody($jsonData);
        }

        return $handler->handle($request);
    }
}
