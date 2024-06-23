<?php

namespace Orkestra\Services\Http\Traits;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use League\Route\Http\Exception\BadRequestException;

trait ErrorResponseTrait
{
    abstract protected function makeResponse($data, int $status = 200): ResponseInterface;

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
        $contentType = $request->getHeaderLine('Content-Type');
        if (strpos($contentType, 'application/json') === 0) {
            return $this->makeResponse([
                'status'      => 'error',
                'code'        => $code,
                'error'       => $error,
                'message'     => $message,
                'description' => $description,
                'errors'      => $errors,
            ], $code);
        }

        $exceptionMessage = "$message: $description";
        return $this->makeResponse($exceptionMessage, $code);
    }
}
