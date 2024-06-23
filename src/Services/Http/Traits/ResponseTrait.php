<?php

namespace Orkestra\Services\Http\Traits;

use Orkestra\App;
use DI\Attribute\Inject;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;

trait ResponseTrait
{
    #[Inject]
    protected App $app;

    protected function makeResponse($data, int $status = 200): ResponseInterface
    {
        $contentType = $this->request->getHeaderLine('Content-Type');
        if (strpos($contentType, 'application/json') === 0) {
            return $this->app->make(JsonResponse::class, [
                'data' => $data,
                'status' => $status,
            ]);
        }

        return $this->app->make(HtmlResponse::class, [
            'body' => json_encode($data),
            'status' => $status,
        ]);
    }
}
