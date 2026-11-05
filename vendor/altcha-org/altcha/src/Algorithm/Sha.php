<?php

namespace AltchaOrg\Altcha\Algorithm;

use AltchaOrg\Altcha\ChallengeParameters;

class Sha implements DeriveKeyInterface
{
    public function __construct(
        private readonly ShaAlgorithm $algorithm = ShaAlgorithm::SHA256,
    ) {
    }

    public function getAlgorithmName(): string
    {
        return $this->algorithm->value;
    }

    public function deriveKey(
        ChallengeParameters $parameters,
        string $salt,
        string $password,
    ): DeriveKeyResult {
        $algo = $this->algorithm->hashAlgo();
        $derivedKey = hash($algo, $salt . $password, true);

        $cost = max(1, $parameters->cost);
        for ($i = 1; $i < $cost; $i++) {
            $derivedKey = hash($algo, $derivedKey, true);
        }

        if ($parameters->keyLength > 0 && $parameters->keyLength < \strlen($derivedKey)) {
            $derivedKey = substr($derivedKey, 0, $parameters->keyLength);
        }

        return new DeriveKeyResult($derivedKey);
    }
}
