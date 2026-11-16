<?php

declare(strict_types=1);

namespace RobThree\Auth\Providers\Rng;

class CSRNGProvider implements IRNGProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRandomBytes(int $bytecount): string
    {
        return random_bytes($bytecount);    // PHP7+
    }

    /**
     * {@inheritdoc}
     */
    public function isCryptographicallySecure(): bool
    {
        return true;
    }
}
