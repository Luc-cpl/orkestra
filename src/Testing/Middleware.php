<?php

namespace Orkestra\Testing;

use Orkestra\Entities\AbstractEntity;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\ResponseFactory;
use PHPUnit\Framework\TestCase;

/**
 * @property-read class-string           $name
 * @property-read array<string, mixed>   $params
 * @property-read string                 $method
 * @property-read mixed[]                $data
 * @property-read array<string, string>  $headers
 * @property-read ServerRequestInterface $request
 */
class Middleware extends AbstractEntity
{
    protected ?ServerRequestInterface $request = null;

    /**
     * @param class-string          $name    Middleware class name
     * @param array<string, mixed>  $params  Constructor parameters
     * @param mixed[]               $data    Request data
     * @param array<string, string> $headers Request headers
     */
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

        /** @var TestCase */
        $test = test();
        $mockHandler = $test->getMockBuilder(RequestHandlerInterface::class)->getMock();
        $mockHandler->method('handle')->willReturnCallback($this->runController(...));

        /** @var RequestHandlerInterface $mockHandler */
        return $middleware->process($request, $mockHandler);
    }
}
