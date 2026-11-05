<?php

namespace AltchaOrg\Altcha\V1;

use AltchaOrg\Altcha\V1\Hasher\Algorithm;

class CheckChallengeOptions extends BaseChallengeOptions
{
    public function __construct(
        Algorithm $algorithm,
        string $salt,
        int $number,
    ) {
        parent::__construct($algorithm, self::DEFAULT_MAX_NUMBER, null, $salt, $number, []);
    }
}
