<?php

namespace AltchaOrg\Altcha;

class ServerSignatureVerification
{
    public function __construct(
        public readonly bool $verified,
        public readonly ?ServerSignatureVerificationData $verificationData,
        public readonly bool $expired = false,
        public readonly bool $invalidSignature = false,
        public readonly bool $invalidSolution = false,
        public readonly float $time = 0.0,
    ) {
    }
}
