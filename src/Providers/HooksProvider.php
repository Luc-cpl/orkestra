<?php

namespace Orkestra\Providers;

use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;
use Orkestra\Services\Hooks\Interfaces\HooksInterface;
use Orkestra\Services\Hooks\Interfaces\ListenerInterface;

use Orkestra\Services\Hooks\Hooks;
use Exception;

class HooksProvider implements ProviderInterface
{
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
        $app->config()->set('validation', [
            'listeners' => function ($value) {
                return is_array($value) ? true : 'The listeners config must be an array';
            },
        ]);

        $app->config()->set('definition', [
            'listeners'  => ['The hook listeners to register with the app', []],
        ]);

        $app->singleton(HooksInterface::class, Hooks::class);
    }

    /**
     * Here we can use the container to resolve and start services.
     *
     * @param App $app
     * @return void
     */
    public function boot(App $app): void
    {
        /** @var class-string[] */
        $listeners = $app->config()->get('listeners');
        $hooks     = $app->get(HooksInterface::class);

        $this->registerListeners($app, $hooks, $listeners);

        foreach ($app->getProviders() as $provider) {
            $provider = $app->get($provider);
            if (!property_exists($provider, 'listeners')) {
                continue;
            }
            $this->registerListeners($app, $hooks, $provider->listeners);
        }
    }

    /**
     * @param class-string[] $listeners
     */
    protected function registerListeners(App $app, HooksInterface $hooks, array $listeners): void
    {
        foreach ($listeners as $listener) {
            // Set listeners as singletons
            $app->singleton($listener, $listener);
            /** @var ListenerInterface */
            $listener = $app->get($listener);
            $listenerHooks = $listener->hook();
            $listenerHooks = is_array($listenerHooks) ? $listenerHooks : [$listenerHooks];
            foreach ($listenerHooks as $listenerHook) {
                if (!method_exists($listener, 'handle')) {
                    throw new Exception(sprintf('Listener %s must implement handle method', $listener::class));
                }
                $listenerHook = str_replace('{app}', $app->slug(), $listenerHook);
                // @phpstan-ignore-next-line
                $hooks->register($listenerHook, $listener->handle(...));
            }
        }
    }
}
