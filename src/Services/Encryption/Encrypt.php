<?php

namespace Orkestra\Services\Encryption;

use Orkestra\Services\Encryption\Interfaces\EncryptInterface;
use RuntimeException;

class Encrypt implements EncryptInterface
{
    /**
     * @param string[] $appPreviousKeys
     */
    public function __construct(
        protected string $appKey,
        protected array $appPreviousKeys = [],
        protected string $algorithm = 'AES-256-CBC',
    ) {
        if (empty($this->appKey)) {
            throw new RuntimeException('The app key must not be empty. Please run the `app:key:create` command to generate a new application key.');
        }
    }

    public function encrypt(array $data): string
    {
        $algorithm = $this->silentCipherIvLength($this->algorithm);
        if ($algorithm === false) {
            throw new RuntimeException('The encryption algorithm is not supported.');
        }

        $data = json_encode($data);
        if ($data === false) {
            throw new RuntimeException('The data could not be encoded.');
        }

        $iv = openssl_random_pseudo_bytes($algorithm);
        $encrypted = openssl_encrypt(
            data: $data,
            cipher_algo: $this->algorithm,
            passphrase: $this->appKey,
            options: 0,
            iv: $iv,
        );

        if ($encrypted === false) {
            throw new RuntimeException('The data could not be encrypted.');
        }

        return base64_encode($iv) . ':' . base64_encode($encrypted);
    }

    public function decrypt(string $data): array|false
    {
        $keys = array_merge([$this->appKey], $this->appPreviousKeys);
        foreach ($keys as $key) {
            $decrypted = $this->singleDecrypt($data, $key);
            if ($decrypted !== false) {
                return $decrypted;
            }
        }
        return false;
    }

    public function needsReEncrypt(string $data): bool
    {
        return $this->singleDecrypt($data, $this->appKey) === false;
    }

    /**
     * @return mixed[]|false
     */
    private function singleDecrypt(string $data, string $key): array|false
    {
        // First check the format
        if (!str_contains($data, ':')) {
            return false;
        }

        // Split the data into IV and encrypted
        [$ivBase64, $encryptedBase64] = explode(':', $data, 2);

        // Decode the components
        $iv = base64_decode($ivBase64);
        $encrypted = base64_decode($encryptedBase64);

        if ($iv === false || $encrypted === false) {
            return false;
        }

        $decrypted = $this->silentDecrypt(
            data: $encrypted,
            cipher_algo: $this->algorithm,
            passphrase: $key,
            options: 0,
            iv: $iv,
        );

        if ($decrypted === false) {
            return false;
        }

        $decrypted = empty($decrypted) ? '{}' : $decrypted;
        /** @var mixed[]|null */
        $data = json_decode($decrypted, true);
        return $data ?? false;
    }

    /**
     * Silent wrapper for openssl_cipher_iv_length to suppress warnings
     */
    private function silentCipherIvLength(string $algorithm): int|false
    {
        set_error_handler(function () {
            return true;
        });

        try {
            return openssl_cipher_iv_length($algorithm);
        } finally {
            restore_error_handler();
        }
    }

    /**
     * Silent wrapper for openssl_decrypt to suppress warnings
     */
    private function silentDecrypt(
        string $data,
        string $cipher_algo,
        string $passphrase,
        int $options,
        string $iv
    ): string|false {
        set_error_handler(function () {
            return true;
        });

        try {
            return openssl_decrypt($data, $cipher_algo, $passphrase, $options, $iv);
        } finally {
            restore_error_handler();
        }
    }
}
