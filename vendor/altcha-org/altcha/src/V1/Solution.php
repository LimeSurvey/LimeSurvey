<?php

namespace AltchaOrg\Altcha\V1;

class Solution
{
    public function __construct(
        public readonly int $number,
        public readonly float $took,
    ) {
    }
}
