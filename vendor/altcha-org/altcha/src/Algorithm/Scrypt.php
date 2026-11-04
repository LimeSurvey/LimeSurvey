<?php

namespace AltchaOrg\Altcha\Algorithm;

use AltchaOrg\Altcha\ChallengeParameters;

class Scrypt implements DeriveKeyInterface
{
    public function getAlgorithmName(): string
    {
        return 'SCRYPT';
    }

    public function deriveKey(
        ChallengeParameters $parameters,
        string $salt,
        string $password,
    ): DeriveKeyResult {
        if (!\function_exists('scrypt')) {
            throw new \RuntimeException('ext-scrypt is required for Scrypt (https://github.com/DomBlack/php-scrypt).');
        }

        $cost = max(1, $parameters->cost);
        $memoryCost = max(1, $parameters->memoryCost ?? 8);
        $parallelism = max(1, $parameters->parallelism ?? 1);
        $keyLength = max(0, $parameters->keyLength);

        /** @var string $derivedKey */
        $derivedKey = scrypt(
            $password,
            $salt,
            $cost,
            $memoryCost,
            $parallelism,
            $keyLength,
        );

        return new DeriveKeyResult($derivedKey);
    }
}
