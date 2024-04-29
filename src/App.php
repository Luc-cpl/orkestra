<?php

namespace Orkestra;

use Orkestra\Interfaces\ConfigurationInterface;
use Orkestra\Interfaces\AppHooksInterface;
use Orkestra\Interfaces\ProviderInterface;
use Orkestra\Traits\AppContainerTrait;
use Orkestra\Traits\AppHooksTrait;

use Psr\Container\ContainerInterface;

use Exception;

class App implements ContainerInterface, AppHooksInterface
{
    use AppContainerTrait;
    use AppHooksTrait;

    public function __construct(
        ConfigurationInterface $config,
    ) {
        // Define default container
        $this->initContainer();
        $this->singleton(ConfigurationInterface::class, $config);
        $this->singleton(ContainerInterface::class, $this);
        $this->singleton(AppHooksInterface::class, $this);
        $this->singleton(self::class, $this);
    }

    /**
     * Get the app slug
     *
     * @return string
     */
    public function slug(): string
    {
        /**
         * @var string
         */
        return $this->config()->get('slug') ?? 'app';
    }

    /**
     * Get the configuration
     *
     * @return ConfigurationInterface
     */
    public function config(): ConfigurationInterface
    {
        return $this->get(ConfigurationInterface::class);
    }

    /**
     * Boot the app
     * It starts the registered providers
     *
     * @return void
     */
    public function boot(): void
    {
        // Ensure we only run once
        if ($this->has('booted')) {
            throw new Exception('App already booted');
        }

        $this->hookCall('config.validate.before', $this);

        $this->config()->validate();

        $this->hookCall('config.validate.after', $this);
        $this->hookCall('boot.before', $this);

        foreach ($this->getProviders() as $provider) {
            /**
             * @var class-string<ProviderInterface> $provider
             */
            $this->get($provider)->boot($this);
        }

        $this->bind('booted', fn() => true, false);

        $this->hookCall('boot.after', $this);
    }
}
