<?php

namespace Orkestra\Providers;

use InvalidArgumentException;
use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;
use Orkestra\Services\Http\Router;
use Orkestra\Services\Http\Interfaces\RouterInterface;
use Laminas\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface;
use Orkestra\Services\Http\Strategy\ApplicationStrategy;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use League\Route\Strategy\JsonStrategy;
use Orkestra\Services\Http\Commands\MiddlewareListCommand;
use Orkestra\Services\Http\MiddlewareRegistry;
use Rakit\Validation\Validator;

class HttpProvider implements ProviderInterface
{
    /**
     * @var class-string[]
     */
    public array $commands = [
        MiddlewareListCommand::class,
    ];

    /**
     * Register services with the container.
     * We can use the container to bind services to the app.
     *
     * Do not use the container to resolve services at this point.
     *
     * @param App $app
     * @return void
     */
    public function register(App $app): void
    {
        // Set the required config so we can validate it
        $app->config()->set('validation', [
            'routes' => function ($value) {
                return is_string($value) ? true : 'The routes config must be a string path to a file';
            },
            'middleware' => function ($value) {
                return is_array($value) ? true : 'The middleware config must be an array';
            },
        ]);

        $app->config()->set('definition', [
            'routes'  => ['The routes directory to load', ''],
            'middleware'  => ['The middleware to load', []],
        ]);

        $app->bind(Router::class, Router::class);
        $app->bind(Validator::class, Validator::class);
        $app->bind(RouterInterface::class, Router::class);

        $app->bind(ServerRequestInterface::class, function () {
            return ServerRequestFactory::fromGlobals(
                $_SERVER,
                $_GET,
                $_POST,
                $_COOKIE,
                $_FILES
            );
        });

        $app->bind(ResponseInterface::class, Response::class);
        $app->bind(ApplicationStrategy::class, fn (App $app) => (new ApplicationStrategy($app))->setContainer($app));
        $app->bind(JsonStrategy::class, function () use ($app) {
            $isProduction = $app->config()->get('env') === 'production';
            $jsonMode     = $isProduction ? 0 : JSON_PRETTY_PRINT;
            $strategy     = new JsonStrategy($app->get(ResponseFactory::class), $jsonMode);
            return $strategy->setContainer($app);
        });

        $app->bind(JsonResponse::class, function ($data, int $status = 200, $headers = []) use ($app) {
            $isProduction = $app->config()->get('env') === 'production';
            $jsonMode     = $isProduction ? 0 : JSON_PRETTY_PRINT;
            $response     = new JsonResponse($data, $status, $headers, $jsonMode);
            return $response;
        });

        $app->bind(MiddlewareRegistry::class, MiddlewareRegistry::class)->constructor(
            registry: function (App $app) {
                /** @var array<string, class-string> */
                $middlewareStack = $app->config()->get('middleware');
                return array_map(fn ($middleware) => [
                    'class' => $middleware,
                    'origin' => 'configuration'
                ], $middlewareStack);
            },
        );
    }

    /**
     * Here we can use the container to resolve and start services.
     *
     * @param App $app
     * @return void
     */
    public function boot(App $app): void
    {
        $middlewareRegistry = $app->get(MiddlewareRegistry::class);

        $providers = array_reverse($app->getProviders());
        foreach ($providers as $provider) {
            $provider = $app->get($provider);
            if (!property_exists($provider, 'middleware')) {
                continue;
            }
            if (!is_array($provider->middleware)) {
                throw new InvalidArgumentException(sprintf('Middleware must be an array in %s', $provider::class));
            }
            foreach ($provider->middleware as $alias => $middleware) {
                $middlewareRegistry->registry($middleware, $alias, $provider::class);
            }
        }

        $router = $app->get(RouterInterface::class);
        $router->setStrategy($app->get(ApplicationStrategy::class));

        /** @var string */
        $configFile = $app->config()->get('routes');

        if (empty($configFile)) {
            $app->hookCall('http.router.config', $router);
            return;
        }

        (require $configFile)($router);
        $app->hookCall('http.router.config', $router);
    }
}
