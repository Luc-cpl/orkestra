<?php

namespace Orkestra\Services\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class JsonMiddleware extends AbstractMiddleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $contentType = $request->getHeaderLine('Content-Type');

        if (strpos($contentType, 'application/json') === 0) {
            $body = $request->getBody()->getContents();
            /** @var ?object $jsonData */
            $jsonData = json_decode($body, true);

            if ($jsonData === null) {
                return $this->errorResponse(
                    $request,
                    'invalid_json',
                    'Invalid JSON',
                    'The JSON data in the request body is invalid.',
                );
            }

            // Replace the request body with the JSON data
            $request = $request->withParsedBody($jsonData);
        }

        return $handler->handle($request);
    }
}
