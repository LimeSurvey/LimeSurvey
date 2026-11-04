<?php

namespace AltchaOrg\Altcha;

use AltchaOrg\Altcha\Algorithm\DeriveKeyInterface;
use AltchaOrg\Altcha\Algorithm\Pbkdf2;

class Obfuscator
{
    private const CIPHER = 'aes-256-gcm';
    private const IV_LENGTH = 12;
    private const TAG_LENGTH = 16;
    private const DEFAULT_COST = 5000;
    private const DEFAULT_KEY_PREFIX_LENGTH = 32;
    private const DEFAULT_COUNTER_MIN = 20;
    private const DEFAULT_COUNTER_MAX = 200;

    public function __construct(
        private readonly Altcha $altcha,
        private readonly DeriveKeyInterface $algorithm = new Pbkdf2(),
    ) {
    }

    public function obfuscate(
        string $data,
        int $cost = self::DEFAULT_COST,
        int $counterMin = self::DEFAULT_COUNTER_MIN,
        int $counterMax = self::DEFAULT_COUNTER_MAX,
        int $keyPrefixLength = self::DEFAULT_KEY_PREFIX_LENGTH,
    ): string {
        $counter = random_int($counterMin, $counterMax);

        $challenge = $this->altcha->createChallenge(new CreateChallengeOptions(
            algorithm: $this->algorithm,
            cost: $cost,
            keyLength: 32,
            keyPrefixLength: $keyPrefixLength,
            counter: $counter,
        ));

        // The full keyPrefix from challenge creation is the AES key (hex-encoded).
        // Convert to raw bytes for AES-256-GCM (requires 32 bytes = 64 hex chars).
        $fullKeyHex = $challenge->parameters->keyPrefix;
        $aesKey = hex2bin($fullKeyHex) ?: throw new \RuntimeException('Invalid key prefix.');

        // Encrypt the data with AES-256-GCM
        $iv = random_bytes(self::IV_LENGTH);
        $tag = '';
        $ciphertext = openssl_encrypt($data, self::CIPHER, $aesKey, \OPENSSL_RAW_DATA, $iv, $tag, '', self::TAG_LENGTH);
        if (false === $ciphertext) {
            throw new \RuntimeException('Encryption failed.');
        }

        // Truncate the keyPrefix to half (the solver must brute-force the rest)
        $truncatedKeyPrefix = substr($fullKeyHex, 0, $challenge->parameters->keyLength);

        $params = new ChallengeParameters(
            algorithm: $challenge->parameters->algorithm,
            nonce: $challenge->parameters->nonce,
            salt: $challenge->parameters->salt,
            cost: $challenge->parameters->cost,
            keyLength: $challenge->parameters->keyLength,
            keyPrefix: $truncatedKeyPrefix,
            memoryCost: $challenge->parameters->memoryCost,
            parallelism: $challenge->parameters->parallelism,
        );

        $output = [
            'parameters' => $params->toArray(),
            'cipher' => [
                'iv' => bin2hex($iv),
                'data' => bin2hex($ciphertext . $tag),
            ],
        ];

        return base64_encode(json_encode($output, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE) ?: '');
    }

    public function deobfuscate(
        string $obfuscatedData,
        float $timeout = 30.0,
    ): string {
        $json = base64_decode($obfuscatedData, true);
        if (false === $json) {
            throw new \InvalidArgumentException('Unable to parse obfuscated data.');
        }

        /** @var null|array{parameters: array<string, mixed>, parameters: null|array<string, mixed>, cipher: null|array{iv: string, data: string}} $parsed */
        $parsed = json_decode($json, true);
        if (!\is_array($parsed) || !isset($parsed['parameters'], $parsed['cipher'])) {
            throw new \InvalidArgumentException('Invalid obfuscated data format.');
        }

        $params = ChallengeParameters::fromArray($parsed['parameters']);
        $challenge = new Challenge($params, null);

        // Solve the challenge to recover the full derived key
        $solution = $this->altcha->solveChallenge(new SolveChallengeOptions(
            challenge: $challenge,
            algorithm: $this->algorithm,
            timeout: $timeout,
        ));

        if (null === $solution) {
            throw new \RuntimeException('Unable to find solution.');
        }

        // The full derived key (hex) is the AES key
        $aesKey = hex2bin($solution->derivedKey) ?: throw new \RuntimeException('Invalid derived key.');

        $iv = hex2bin($parsed['cipher']['iv']) ?: throw new \RuntimeException('Invalid cipher IV.');
        $ciphertextWithTag = hex2bin($parsed['cipher']['data']) ?: throw new \RuntimeException('Invalid cipher data.');

        // Separate ciphertext and GCM auth tag
        $ciphertext = substr($ciphertextWithTag, 0, -self::TAG_LENGTH);
        $tag = substr($ciphertextWithTag, -self::TAG_LENGTH);

        $decrypted = openssl_decrypt($ciphertext, self::CIPHER, $aesKey, \OPENSSL_RAW_DATA, $iv, $tag);
        if (false === $decrypted) {
            throw new \RuntimeException('Decryption failed.');
        }

        return $decrypted;
    }
}
