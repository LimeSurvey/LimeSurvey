<?php

namespace AltchaOrg\Altcha;

use AltchaOrg\Altcha\Algorithm\DeriveKeyInterface;

class Altcha
{
    public function __construct(
        private readonly ?string $hmacSignatureSecret = null,
        private readonly ?string $hmacKeySignatureSecret = null,
        private readonly HmacAlgorithm $hmacAlgorithm = HmacAlgorithm::SHA256,
    ) {
    }

    public function createChallenge(CreateChallengeOptions $options): Challenge
    {
        $nonce = $options->nonce ?? bin2hex(random_bytes(16));
        $salt = $options->salt ?? bin2hex(random_bytes(16));

        $params = new ChallengeParameters(
            algorithm: $options->algorithm->getAlgorithmName(),
            nonce: $nonce,
            salt: $salt,
            cost: $options->cost,
            keyLength: $options->keyLength,
            keyPrefix: $options->keyPrefix,
            memoryCost: $options->memoryCost,
            parallelism: $options->parallelism,
            expiresAt: $options->expiresAt,
            data: $options->data,
        );

        $keyPrefix = $params->keyPrefix;
        $keySignature = null;
        $keyPrefixLength = $options->keyPrefixLength ?? $params->keyLength / 2;

        if (null !== $options->counter) {
            $result = $this->deriveKeyForCounter($options->algorithm, $params, $options->counter);
            $keyPrefix = bin2hex(substr($result, 0, $keyPrefixLength));
            if (null !== $this->hmacKeySignatureSecret) {
                $keySignature = $this->hmacHex($result, $this->hmacKeySignatureSecret);
            }
        } elseif (empty($keyPrefix)) {
            // Generate a random prefix of the desired hex length
            $keyPrefix = bin2hex(random_bytes(max(1, (int) $keyPrefixLength)));
        }

        $params = new ChallengeParameters(
            algorithm: $params->algorithm,
            nonce: $params->nonce,
            salt: $params->salt,
            cost: $params->cost,
            keyLength: $params->keyLength,
            keyPrefix: $keyPrefix,
            keySignature: $keySignature,
            memoryCost: $params->memoryCost,
            parallelism: $params->parallelism,
            expiresAt: $params->expiresAt,
            data: $params->data,
        );

        $secret = $this->signatureSecret();
        $signature = null !== $secret ? $this->hmacHex($params->toCanonicalJson(), $secret) : null;

        return new Challenge($params, $signature);
    }

    public function solveChallenge(SolveChallengeOptions $options): ?Solution
    {
        $params = $options->challenge->parameters;
        $nonceBytes = hex2bin($params->nonce) ?: '';
        $saltBytes = hex2bin($params->salt) ?: '';
        $keyPrefix = $params->keyPrefix;
        $keyPrefixBytes = self::hexToBytes($keyPrefix);

        $startTime = microtime(true);
        $deadline = $startTime + $options->timeout;
        $i = 0;

        while (microtime(true) < $deadline) {
            $counter = $options->start + ($i * $options->step);
            $password = $nonceBytes . pack('N', $counter);

            $result = $options->algorithm->deriveKey($params, $saltBytes, $password);
            $derivedKey = $result->derivedKey;

            if (self::hasKeyPrefix($derivedKey, $keyPrefix, $keyPrefixBytes)) {
                $time = microtime(true) - $startTime;

                return new Solution($counter, bin2hex($derivedKey), $time);
            }

            $i++;
        }

        return null;
    }

    public function verifySolution(VerifySolutionOptions $options): VerifySolutionResult
    {
        $startTime = microtime(true);
        $payload = $options->payload;
        $params = $payload->challenge->parameters;

        // Check expiration: 0 means no expiry; fractional-second comparison, matching altcha-lib (JS)
        if ($params->expiresAt) {
            if ($params->expiresAt < microtime(true)) {
                return new VerifySolutionResult(
                    verified: false,
                    expired: true,
                    time: microtime(true) - $startTime,
                );
            }
        }

        // Verify challenge signature. Like altcha-lib (JS) there is no unsigned mode: a missing
        // signature, or a verifier without a signature secret, can never verify.
        $secret = $this->signatureSecret();
        $signature = $payload->challenge->signature;
        try {
            $canonicalJson = $params->toCanonicalJson();
        } catch (\JsonException) {
            $canonicalJson = null; // e.g. invalid UTF-8 in parameters passed as a PHP array
        }
        if (
            null === $secret
            || null === $signature
            || null === $canonicalJson
            || !hash_equals($this->hmacHex($canonicalJson, $secret), $signature)
        ) {
            return new VerifySolutionResult(
                verified: false,
                invalidSignature: true,
                time: microtime(true) - $startTime,
            );
        }

        // Fast path: the challenge carries an HMAC of the raw derived key bytes. A mismatch is final
        // (no fallback to re-derivation), matching altcha-lib (JS).
        if (null !== $params->keySignature && null !== $this->hmacKeySignatureSecret) {
            $derivedKeyBytes = self::hexToBytes($payload->solution->derivedKey);
            $verified = null !== $derivedKeyBytes
                && hash_equals($params->keySignature, $this->hmacHex($derivedKeyBytes, $this->hmacKeySignatureSecret));

            return new VerifySolutionResult(
                verified: $verified,
                invalidSolution: $verified ? null : true,
                time: microtime(true) - $startTime,
            );
        }

        // Full re-derivation path
        $nonceBytes = hex2bin($params->nonce) ?: '';
        $saltBytes = hex2bin($params->salt) ?: '';
        $password = $nonceBytes . pack('N', $payload->solution->counter);

        $result = $options->algorithm->deriveKey($params, $saltBytes, $password);
        $derivedKeyHex = bin2hex($result->derivedKey);

        if (!hash_equals($derivedKeyHex, $payload->solution->derivedKey)) {
            return new VerifySolutionResult(
                verified: false,
                invalidSolution: true,
                time: microtime(true) - $startTime,
            );
        }

        // Verify the derived key starts with the required prefix
        $keyPrefix = $params->keyPrefix;
        if (!self::hasKeyPrefix($result->derivedKey, $keyPrefix, self::hexToBytes($keyPrefix))) {
            return new VerifySolutionResult(
                verified: false,
                invalidSolution: true,
                time: microtime(true) - $startTime,
            );
        }

        return new VerifySolutionResult(
            verified: true,
            time: microtime(true) - $startTime,
        );
    }

    private function deriveKeyForCounter(DeriveKeyInterface $algorithm, ChallengeParameters $params, int $counter): string
    {
        $nonceBytes = hex2bin($params->nonce) ?: '';
        $saltBytes = hex2bin($params->salt) ?: '';
        $password = $nonceBytes . pack('N', $counter);

        $result = $algorithm->deriveKey($params, $saltBytes, $password);

        return $result->derivedKey;
    }

    /**
     * An empty secret counts as unset, matching altcha-lib (JS).
     */
    private function signatureSecret(): ?string
    {
        return '' === $this->hmacSignatureSecret ? null : $this->hmacSignatureSecret;
    }

    private function hmacHex(string $data, string $key): string
    {
        return bin2hex(hash_hmac($this->hmacAlgorithm->hashAlgo(), $data, $key, true));
    }

    /**
     * Decodes an even-length hex string; returns null for anything else (no hex2bin warning).
     */
    private static function hexToBytes(string $hex): ?string
    {
        return 1 === preg_match('/\A(?:[0-9a-fA-F]{2})*\z/', $hex) ? (string) hex2bin($hex) : null;
    }

    /**
     * Even-length prefixes compare bytes; odd-length (half-byte) prefixes compare the lowercase
     * hex string, matching altcha-lib (JS).
     */
    private static function hasKeyPrefix(string $derivedKey, string $keyPrefix, ?string $keyPrefixBytes): bool
    {
        return null !== $keyPrefixBytes
            ? str_starts_with($derivedKey, $keyPrefixBytes)
            : str_starts_with(bin2hex($derivedKey), $keyPrefix);
    }
}
