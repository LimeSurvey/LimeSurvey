<?php

namespace AltchaOrg\Altcha\Algorithm;

use AltchaOrg\Altcha\ChallengeParameters;

class DeriveKeyResult
{
    public function __construct(
        public readonly string $derivedKey,
        public readonly ?ChallengeParameters $parameterOverrides = null,
    ) {
    }
}
