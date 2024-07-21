<?php

namespace Orkestra\Providers;

use Orkestra\App;
use Orkestra\Interfaces\ProviderInterface;
use Orkestra\Services\Encryption\Commands\CreateAppKeyCommand;
use Orkestra\Services\Encryption\Encrypt;

class EncryptionServiceProvider implements ProviderInterface
{
    /**
     * @var array<class-string>
     */
    public array $commands = [
        CreateAppKeyCommand::class,
    ];

    /**
     * Register services with the container.
     */
    public function register(App $app): void
    {
        $app->config()->set('validation', [
            'app_key' => fn ($value) => !is_string($value)
                ? 'app_key must be a string with the key used to encrypt and decrypt data'
                : true,
            'app_previous_keys' => fn ($value) => !is_array($value)
                ? 'app_previous_keys must be an array with the previous keys used to encrypt data'
                : true,
        ]);

        $app->config()->set('definition', [
            'app_key' => ['The key used to encrypt and decrypt data', ''],
            'app_previous_keys' => ['The previous keys used to encrypt and decrypt data', []],
        ]);

        $app->bind('encrypt', function (App $app) {
            /** @var string */
            $appKey = $app->config()->get('app_key');

            /** @var array<string> */
            $appPreviousKeys = $app->config()->get('app_previous_keys');

            return new Encrypt(
                appKey: $appKey,
                appPreviousKeys: $appPreviousKeys,
            );
        });
    }

    /**
     * Here we can use the container to resolve and start services.
     */
    public function boot(App $app): void
    {
        //
    }
}
