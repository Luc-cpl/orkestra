<?php

namespace Orkestra;

use Orkestra\Interfaces\ConfigurationInterface;
use Orkestra\Interfaces\AppContainerInterface;
use Orkestra\Interfaces\AppHooksInterface;
use Orkestra\Interfaces\ProviderInterface;
use Orkestra\Traits\AppContainerTrait;
use Orkestra\Traits\AppHooksTrait;

use Psr\Container\ContainerInterface;

class App implements AppHooksInterface, AppContainerInterface
{
    use AppContainerTrait;
    use AppHooksTrait;

    public function __construct(
        private ConfigurationInterface $config,
    ) {
        $this->setDefaultConfig();
        $this->initContainer();
        $this->bind(ConfigurationInterface::class, $config);
        $this->bind(ContainerInterface::class, $this);
        $this->bind(AppContainerInterface::class, $this);
        $this->bind(AppHooksInterface::class, $this);
        $this->bind(self::class, $this);
    }

    private function setDefaultConfig(): void
    {
        $this->config->set('validation', [
            'env'  => fn ($value) =>
                !in_array($value, ['development', 'production', 'testing'], true)
                    ? 'env must be either "development", "production" or "testing"'
                    : true,
            'root' => fn ($value) =>
                !is_dir($value ?? '')
                    ? "root \"$value\" is not a directory"
                    : true,
            'slug' => fn ($value) =>
                !empty($value) && !preg_match('/^[a-z0-9-]+$/', $value)
                    ? "slug \"$value\" is not valid"
                    : true,
        ]);

        $this->config->set('definition', [
            'env'  => ['The environment the app is running in (development, production or testing)', 'development'],
            'root' => ['The root directory of the app', getcwd()],
            'slug' => ['The app slug', 'app'],
        ]);
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
        return $this->config;
    }

    /**
     * Boot the app
     * It starts the registered providers
     *
     * @return void
     */
    public function boot(): void
    {
        $this->bootGate();

        $this->bootContainer();

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

        $this->bind('booted', fn () => true, false);

        $this->hookCall('boot.after', $this);
    }
}
