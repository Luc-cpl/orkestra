<?php

namespace Orkestra\Testing;

use Orkestra\Entities\AbstractEntity;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\ResponseFactory;

class Middleware extends AbstractEntity
{
    protected ?ServerRequestInterface $request = null;

    public function __construct(
        protected string $name,
        protected array $params = [],
        protected string $method = 'GET',
        protected array $data = [],
        protected array $headers = []
    ) {
        //
    }

    private function runController(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        return (new ResponseFactory())->createResponse();
    }

    public function process(): ResponseInterface
    {
        $request = generateRequest($this->method, '/', $this->data, $this->headers);

        /** @var MiddlewareInterface */
        $middleware  = app()->make($this->name, $this->params);

        $mockHandler = test()->createMock(RequestHandlerInterface::class);
        $mockHandler->method('handle')->willReturnCallback($this->runController(...));

        return $middleware->process($request, $mockHandler);
    }
}
