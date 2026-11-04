<?php

namespace AltchaOrg\Altcha\V1;

use AltchaOrg\Altcha\V1\Hasher\Algorithm;

class ServerSignaturePayload
{
    public function __construct(
        public readonly Algorithm $algorithm,
        public readonly string $verificationData,
        public readonly string $signature,
        public readonly bool $verified,
    ) {
    }
}
