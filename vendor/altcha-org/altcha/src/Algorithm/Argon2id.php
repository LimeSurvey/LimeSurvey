<?php

namespace AltchaOrg\Altcha\Algorithm;

use AltchaOrg\Altcha\ChallengeParameters;

class Argon2id implements DeriveKeyInterface
{
    public function getAlgorithmName(): string
    {
        return 'ARGON2ID';
    }

    public function deriveKey(
        ChallengeParameters $parameters,
        string $salt,
        string $password,
    ): DeriveKeyResult {
        if (!\function_exists('sodium_crypto_pwhash')) {
            throw new \RuntimeException('ext-sodium is required for Argon2id.');
        }

        $cost = max(1, $parameters->cost);
        $memoryCost = max(1, $parameters->memoryCost ?? 32768) * 1024;
        $keyLength = max(0, $parameters->keyLength);

        $derivedKey = sodium_crypto_pwhash(
            $keyLength,
            $password,
            $salt,
            $cost,
            $memoryCost,
            \SODIUM_CRYPTO_PWHASH_ALG_ARGON2ID13,
        );

        return new DeriveKeyResult($derivedKey);
    }
}
