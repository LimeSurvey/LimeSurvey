<?php

namespace AltchaOrg\Altcha;

use AltchaOrg\Altcha\Algorithm\DeriveKeyInterface;

class CreateChallengeOptions
{
    public readonly int|float|null $expiresAt;

    /**
     * @param null|array<string, mixed> $data
     */
    public function __construct(
        public readonly DeriveKeyInterface $algorithm,
        public readonly int $cost,
        public readonly int $keyLength = 32,
        public readonly string $keyPrefix = '00',
        public readonly ?int $counter = null,
        public readonly ?int $memoryCost = null,
        public readonly ?int $parallelism = null,
        \DateTimeInterface|int|float|null $expiresAt = null,
        public readonly ?array $data = null,
        public readonly ?string $nonce = null,
        public readonly ?string $salt = null,
        public readonly ?int $keyPrefixLength = null,
    ) {
        $this->expiresAt = $expiresAt instanceof \DateTimeInterface
            ? $expiresAt->getTimestamp()
            : $expiresAt;
    }
}
