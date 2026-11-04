<?php

namespace AltchaOrg\Altcha\V1;

use AltchaOrg\Altcha\V1\Hasher\Algorithm;
use AltchaOrg\Altcha\V1\Hasher\Hasher;
use AltchaOrg\Altcha\V1\Hasher\HasherInterface;

class Obfuscator
{
    public const DEFAULT_MAX_NUMBER = 10_000;

    /**
     * @param int $maxNumber Maximum number for the random number generator (default: 10,000)
     */
    public function __construct(
        private readonly int $maxNumber = self::DEFAULT_MAX_NUMBER,
        private readonly HasherInterface $hasher = new Hasher(),
    ) {
    }

    /**
     * Encrypts a payload for PoW-based reveal using AES-GCM.
     *
     * @param string   $raw     Plaintext payload to encrypt.
     * @param string   $key     Symmetric key used to encrypt/decrypt the payload. Defaults to empty string, ie. no key.
     * @param null|int $counter Optional fixed PoW counter used to derive the IV. If null, a random integer in [0, $this->maxNumber] inclusive is chosen.
     *
     * @return string Base64-encoded bytes of ciphertext followed by the 16‑byte GCM authentication tag ($ciphertext . $tag).
     */
    public function obfuscateData(string $raw, string $key = '', ?int $counter = null): string
    {
        $cipher = 'AES-256-GCM';
        $keyHash = $this->hasher->hash(Algorithm::SHA256, $key);

        $ivLength = openssl_cipher_iv_length($cipher);

        if (false === $ivLength) {
            throw new \RuntimeException('Getting cipher iv length failed.');
        }

        $iv = ''; // AES‑GCM initialization vector (IV), typically 12 bytes for AES-256-GCM
        $num = $counter ?? $this->randomInt();

        // Fill IV from the counter, one byte at a time (little‑endian)
        for ($i = 0; $i < $ivLength; $i++) {
            $iv .= \chr(abs($num % 256));
            $num = intdiv($num, 256);
        }

        $encryptedData = openssl_encrypt($raw, $cipher, $keyHash, \OPENSSL_RAW_DATA, $iv, $tag);

        if (!$encryptedData) {
            throw new \RuntimeException('Data encryption failed.');
        }

        return base64_encode($encryptedData . $tag);
    }

    private function randomInt(): int
    {
        return random_int(0, $this->maxNumber);
    }
}
