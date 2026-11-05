<?php

namespace AltchaOrg\Altcha;

class VerifyServerResult
{
    public function __construct(
        public readonly bool $verified,
        public readonly ?string $apiKey = null,
        public readonly ?string $reason = null,
        public readonly ?ServerSignatureVerificationData $verificationData = null,
    ) {
    }

    /**
     * @param array<string, mixed> $arr
     */
    public static function fromArray(array $arr): self
    {
        /** @var null|array<string, mixed> $verificationData */
        $verificationData = isset($arr['verificationData']) && \is_array($arr['verificationData']) ? $arr['verificationData'] : null;

        return new self(
            verified: isset($arr['verified']) && \is_bool($arr['verified']) && $arr['verified'],
            apiKey: isset($arr['apiKey']) && \is_string($arr['apiKey']) ? $arr['apiKey'] : null,
            reason: isset($arr['reason']) && \is_string($arr['reason']) ? $arr['reason'] : null,
            verificationData: null !== $verificationData ? new ServerSignatureVerificationData($verificationData) : null,
        );
    }
}
