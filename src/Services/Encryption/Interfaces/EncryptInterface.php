<?php

namespace Orkestra\Services\Encryption\Interfaces;

use RuntimeException;

interface EncryptInterface
{
	/**
	 * Encrypts an array of data
	 *
	 * @param mixed[] $data
	 * @throws RuntimeException if the data cannot be encrypted
	 */
	public function encrypt(array $data): string;

	/**
	 * Decrypts a string of data
	 *
	 * @return mixed[]|false the decrypted data or false if the data cannot be decrypted
	 */
	public function decrypt(string $data): array|false;

	/**
	 * Checks if the data needs to be re-encrypted
	 */
	public function needsReEncrypt(string $data): bool;
}