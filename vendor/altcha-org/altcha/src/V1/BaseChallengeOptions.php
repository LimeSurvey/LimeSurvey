<?php

namespace AltchaOrg\Altcha\V1;

use AltchaOrg\Altcha\V1\Hasher\Algorithm;

/**
 * @phpstan-type ChallengeParams array<string, null|scalar>
 */
class BaseChallengeOptions
{
    public const DEFAULT_MAX_NUMBER = 1000000;

    public readonly string $salt;

    /**
     * Options for creation of a new challenge.
     *
     * @see ChallengeOptions for options with sane defaults.
     *
     * @param ChallengeParams $params
     */
    public function __construct(
        public readonly Algorithm $algorithm,
        public readonly int $maxNumber,
        public readonly ?\DateTimeInterface $expires,
        string $salt,
        public readonly int $number,
        public readonly array $params,
    ) {
        if ($expires) {
            $params['expires'] = $expires->getTimestamp();
        }

        if (!empty($params)) {
            $salt .= '?' . http_build_query($params);
        }

        // Add a delimiter to prevent parameter splicing
        if (!str_ends_with($salt, '&')) {
            $salt .= '&';
        }

        $this->salt = $salt;
    }
}
