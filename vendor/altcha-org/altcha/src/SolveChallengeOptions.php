<?php

namespace AltchaOrg\Altcha;

use AltchaOrg\Altcha\Algorithm\DeriveKeyInterface;

class SolveChallengeOptions
{
    public function __construct(
        public readonly Challenge $challenge,
        public readonly DeriveKeyInterface $algorithm,
        public readonly int $start = 0,
        public readonly int $step = 1,
        public readonly float $timeout = 30.0,
    ) {
    }
}
