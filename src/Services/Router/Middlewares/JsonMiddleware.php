<?php

namespace Orkestra\Services\Router\Middlewares;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use League\Route\Http\Exception\BadRequestException;

class JsonMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $contentType = $request->getHeaderLine('Content-Type');

        if (strpos($contentType, 'application/json') === 0) {
            $body = $request->getBody()->getContents();
            /** @var ?object $jsonData */
            $jsonData = json_decode($body, true);

            if ($jsonData === null) {
                throw new BadRequestException('Invalid JSON data in request body.');
            }

            // Replace the request body with the JSON data
            $request = $request->withParsedBody($jsonData);
        }

        return $handler->handle($request);
    }
}
