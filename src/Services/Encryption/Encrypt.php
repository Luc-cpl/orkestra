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
		$algorithm = openssl_cipher_iv_length($this->algorithm);
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
		[$iv, $encrypted] = explode(':', base64_decode($data), 2);
		$decrypted = openssl_decrypt(
			data: $encrypted,
			cipher_algo: $this->algorithm,
			passphrase: $key,
			options: 0,
			iv: base64_decode($iv),
		);

		if ($decrypted !== false) {
			$decrypted = empty($decrypted) ? '{}' : $decrypted;
			/** @var mixed[]|null */
			$data = json_decode($decrypted, true);
			return $data ?? false;
		}
		return false;
	}
}