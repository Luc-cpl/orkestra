<?php

namespace Orkestra;

use Orkestra\Interfaces\ConfigurationInterface;
use Orkestra\Interfaces\ProviderInterface;
use Orkestra\Traits\AppContainerTrait;
use Orkestra\Traits\AppHooksTrait;

use Psr\Container\ContainerInterface;

use Exception;

class App implements ContainerInterface
{
    use AppContainerTrait;
    use AppHooksTrait;

    public function __construct(
        ConfigurationInterface $config,
    ) {
        // Define default container
        $this->initContainer($config);
        $this->singleton(ConfigurationInterface::class, $config);
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
     * Run the app
     * It starts the registered providers
     *
     * @return void
     */
    public function run(): void
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

        $this->hookCall('boot.after', $this);
    }
}
