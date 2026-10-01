<?php

declare(strict_types=1);

namespace RobThree\Auth\Providers\Rng;

use function in_array;

class HashRNGProvider implements IRNGProvider
{
    public function __construct(private readonly string $algorithm = 'sha256')
    {
        $algos = array_values(hash_algos());
        if (!in_array($this->algorithm, $algos, true)) {
            throw new RNGException('Unsupported algorithm specified');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRandomBytes(int $bytecount): string
    {
        $result = '';
        $hash = mt_rand();
        for ($i = 0; $i < $bytecount; $i++) {
            $hash = hash($this->algorithm, $hash . mt_rand(), true);
            $result .= $hash[mt_rand(0, strlen($hash) - 1)];
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isCryptographicallySecure(): bool
    {
        return false;
    }
}
