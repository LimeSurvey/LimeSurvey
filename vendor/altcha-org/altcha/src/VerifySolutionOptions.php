<?php

namespace AltchaOrg\Altcha;

use AltchaOrg\Altcha\Algorithm\DeriveKeyInterface;

class VerifySolutionOptions
{
    public readonly Payload $payload;

    /**
     * @param array<string, mixed>|Payload|string $payload A `Payload` object, a raw base64-encoded
     *                                                     payload string (as posted by the widget),
     *                                                     or an already-decoded associative array.
     */
    public function __construct(
        Payload|string|array $payload,
        public readonly DeriveKeyInterface $algorithm,
    ) {
        $this->payload = match (true) {
            $payload instanceof Payload => $payload,
            \is_string($payload) => Payload::fromBase64($payload),
            default => Payload::fromArray($payload),
        };
    }
}
