<?php

namespace Orkestra\Services\Http\Traits;

use DI\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Laminas\Diactoros\Response\JsonResponse;
use League\Route\Http\Exception\BadRequestException;
use Orkestra\App;

trait ErrorResponseTrait
{
    #[Inject]
    protected App $app;

    /**
     * Return a JSON response or throw an exception with the given error
     *
     * @param Request                              $request
     * @param string                               $error
     * @param string                               $message
     * @param string                               $description
     * @param array<string, array<string, string>> $errors
     * @param int     $code
     *
     * @return ResponseInterface
     * @throws BadRequestException
     */
    public function errorResponse(
        Request $request,
        string  $error,
        string  $message,
        string  $description,
        array   $errors = [],
        int     $code   = 400,
    ): ResponseInterface {

        $this->app->hookCall(
            'middleware.error',
            $request,
            $error,
            $message,
            $description,
            $errors,
            $code
        );

        $contentType = $request->getHeaderLine('Content-Type');

        if (strpos($contentType, 'application/json') === 0) {
            $response = $this->app->get(JsonResponse::class, [
                'data' => [
                    'status'      => 'error',
                    'code'        => $code,
                    'error'       => $error,
                    'message'     => $message,
                    'description' => $description,
                    'errors'      => $errors,
                ],
                'status' => $code,
            ]);
            return $response;
        }

        $exceptionMessage = "$message: $description";

        throw new BadRequestException($exceptionMessage, null, $code);
    }
}
