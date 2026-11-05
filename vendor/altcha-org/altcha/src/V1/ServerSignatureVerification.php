<?php

namespace AltchaOrg\Altcha\V1;

class ServerSignatureVerification
{
    public function __construct(
        public readonly bool $verified,
        public readonly ?ServerSignatureVerificationData $data,
    ) {
    }
}
