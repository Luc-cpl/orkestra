<?php

namespace Orkestra\Services\Http\Middleware;

use League\Route\Http\Exception\BadRequestException;
use Orkestra\Services\Http\Entities\ParamDefinition;
use Orkestra\Services\Http\Enum\ParamType;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Rakit\Validation\Validator;

class ValidationMiddleware extends AbstractMiddleware
{
    /**
     * @param Validator $validator
     * @param array<string, string[]|string> $rules
     * @param ParamDefinition[] $params
     */
    public function __construct(
        protected Validator $validator,
        protected array     $rules = [],
        array $params = []
    ) {
        $params = $this->flattenParams($params);
        foreach ($params as $key => $param) {
            $this->rules[$key] = $this->setValidation($param);
        }
    }

    /**
     * @param ParamDefinition $param
     * @return string[]|string
     */
    protected function setValidation(ParamDefinition $param): array|string
    {
        $type = $param->type->value;

        $typeValidation = match ($type) {
            // 'string'  => 'string', // Todo: add this type
            'int'     => 'integer',
            'number'  => 'numeric',
            'boolean' => 'boolean',
            'array'   => 'array',
            'object'  => 'array',
            default   => null,
        };

        $validation = $param->validation;

        if ($typeValidation) {
            array_unshift($validation, $typeValidation);
        }

        return $validation;
    }

    /**
     * @param ParamDefinition[] $params
     * @return array<string, ParamDefinition>
     */
    protected function flattenParams(array $params, string $prefix = ''): array
    {
        $flattened = [];

        foreach ($params as $param) {
            $flattened[$prefix . $param->name] = $param;

            if ($param->inner && !empty($param->inner)) {
                $inner = $param->inner;
                $postFix = $param->type === ParamType::Array && count($inner) > 1 ? '.*.' : '.';
                if ($param->type === ParamType::Array && count($inner) === 1) {
                    $inner[0]->set(name: '*');
                }
                $inner = $this->flattenParams($inner, $prefix . $param->name . $postFix);
                $flattened = array_merge($flattened, $inner);
            }
        }

        return $flattened;
    }

    /**
     * @param mixed[] $params
     * @return mixed[]
     */
    protected function adjustRequestParamsTypes(array $params): array
    {
        foreach ($params as $key => $value) {
            if (is_numeric($value) && is_string($value)) {
                $params[$key] = strpos($value, '.') !== false ? (float) $value : (int) $value;
            } elseif (is_array($value)) {
                $params[$key] = $this->adjustRequestParamsTypes($value);
            } else {
                $params[$key] = match ($value) {
                    'null'   => null,
                    'true'   => true,
                    'false'  => false,
                    default  => $value,
                };
            }
        }

        return $params;
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    protected function removeUndefinedRules(array $params, string $prefix = ''): array
    {
        $filtered = [];

        foreach ($params as $key => $value) {
            $keyPrefix = is_numeric($key) ? $prefix . '*' : $prefix . $key;
            $param     = $this->rules[$keyPrefix] ?? null;

            if ($param !== null) {
                $filtered[$key] = $value;
            }

            if (is_array($value)) {
                $inner = $this->removeUndefinedRules($value, $keyPrefix . '.');
                $filtered[$key] = $inner;
            }
        }

        return $filtered;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $validator = $this->validator;
        $rules     = $this->rules;

        // remove undefined rules params from query, taking care of nested params as value.key
        $query = (array) $request->getQueryParams();
        $body  = (array) $request->getParsedBody();
        $query = $this->removeUndefinedRules($query);
        $query = $this->adjustRequestParamsTypes($query);
        $body  = $this->removeUndefinedRules($body);
        $body  = $this->adjustRequestParamsTypes($body);
        $query = array_diff_key($query, $body);
        $data  = $query + $body;

        $request = $request->withQueryParams($query)->withParsedBody($body);

        // Allow the addition of custom validation rules
        $this->app->hookCall('middleware.validation.rules', $validator);

        $validation = $validator->make($data, $rules);

        $this->app->hookCall('middleware.validation.before', $validation);

        $validation->validate();

        $this->app->hookCall('middleware.validation.after', $validation);

        if ($validation->fails()) {
            $this->app->hookCall('middleware.validation.fail', $validation);
            throw new BadRequestException('Theres one or more errors in your request data');
        }

        $this->app->hookCall('middleware.validation.success', $validation);

        return $handler->handle($request);
    }
}
