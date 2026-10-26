<?php

declare(strict_types=1);

namespace RobThree\Auth\Providers\Rng;

interface IRNGProvider
{
    public function getRandomBytes(int $bytecount): string;

    public function isCryptographicallySecure(): bool;
}
