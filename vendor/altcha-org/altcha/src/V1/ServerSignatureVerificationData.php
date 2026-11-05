<?php

namespace AltchaOrg\Altcha\V1;

class ServerSignatureVerificationData
{
    /**
     * @param array<array-key, mixed> $fields
     * @param array<array-key, mixed> $reasons
     */
    public function __construct(
        public readonly string $classification,
        public readonly string $country,
        public readonly string $detectedLanguage,
        public readonly string $email,
        public readonly int $expire,
        public readonly array $fields,
        public readonly string $fieldsHash,
        public readonly array $reasons,
        public readonly float $score,
        public readonly int $time,
        public readonly bool $verified,
    ) {
    }
}
