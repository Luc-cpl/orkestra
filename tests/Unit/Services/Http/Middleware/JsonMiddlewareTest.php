<?php

use Orkestra\Services\Http\Middleware\JsonMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use League\Route\Http\Exception\BadRequestException;

test('can process request with JSON content type', function () {
    $middleware = new JsonMiddleware();
    
    // Setup request with JSON content type
    $request = Mockery::mock(ServerRequestInterface::class);
    $request->shouldReceive('getHeaderLine')
        ->with('Content-Type')
        ->andReturn('application/json');
    
    $request->shouldReceive('getBody')
        ->andReturn(new class {
            public function getContents(): string
            {
                return '{"test": "value"}';
            }
        });
    
    $parsedRequest = Mockery::mock(ServerRequestInterface::class);
    $request->shouldReceive('withParsedBody')
        ->with(['test' => 'value'])
        ->andReturn($parsedRequest);
    
    // Setup handler to return a response
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $handler->shouldReceive('handle')
        ->with($parsedRequest)
        ->andReturn($response);
    
    $result = $middleware->process($request, $handler);
    expect($result)->toBe($response);
});

test('can process request with JSON content type including charset', function () {
    $middleware = new JsonMiddleware();
    
    // Setup request with JSON content type and charset
    $request = Mockery::mock(ServerRequestInterface::class);
    $request->shouldReceive('getHeaderLine')
        ->with('Content-Type')
        ->andReturn('application/json; charset=utf-8');
    
    $request->shouldReceive('getBody')
        ->andReturn(new class {
            public function getContents(): string
            {
                return '{"test": "value"}';
            }
        });
    
    $parsedRequest = Mockery::mock(ServerRequestInterface::class);
    $request->shouldReceive('withParsedBody')
        ->with(['test' => 'value'])
        ->andReturn($parsedRequest);
    
    // Setup handler to return a response
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $handler->shouldReceive('handle')
        ->with($parsedRequest)
        ->andReturn($response);
    
    $result = $middleware->process($request, $handler);
    expect($result)->toBe($response);
});

test('can process request with invalid JSON body', function () {
    $middleware = new JsonMiddleware();
    
    // Setup request with JSON content type but invalid JSON
    $request = Mockery::mock(ServerRequestInterface::class);
    $request->shouldReceive('getHeaderLine')
        ->with('Content-Type')
        ->andReturn('application/json');
    
    $request->shouldReceive('getBody')
        ->andReturn(new class {
            public function getContents(): string
            {
                return '{invalid json}';
            }
        });
    
    // Should not call withParsedBody for invalid JSON
    $request->shouldNotReceive('withParsedBody');
    
    // Setup handler (should not be called)
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $handler->shouldNotReceive('handle');
    
    // Expect a BadRequestException for invalid JSON
    expect(function () use ($middleware, $request, $handler) {
        $middleware->process($request, $handler);
    })->toThrow(BadRequestException::class, 'The JSON data in the request body is invalid.');
});

test('can process request with non-JSON content type', function () {
    $middleware = new JsonMiddleware();
    
    // Setup request with non-JSON content type
    $request = Mockery::mock(ServerRequestInterface::class);
    $request->shouldReceive('getHeaderLine')
        ->with('Content-Type')
        ->andReturn('application/x-www-form-urlencoded');
    
    // Should not read the body for non-JSON content type
    $request->shouldNotReceive('getBody');
    $request->shouldNotReceive('withParsedBody');
    
    // Setup handler to return a response
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $handler->shouldReceive('handle')
        ->with($request)
        ->andReturn($response);
    
    $result = $middleware->process($request, $handler);
    expect($result)->toBe($response);
}); 