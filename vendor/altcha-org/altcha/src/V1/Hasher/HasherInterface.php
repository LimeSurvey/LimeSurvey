<?php

namespace AltchaOrg\Altcha\V1\Hasher;

interface HasherInterface
{
    public function hash(Algorithm $algorithm, string $data): string;

    public function hashHex(Algorithm $algorithm, string $data): string;

    public function hashHmac(Algorithm $algorithm, string $data, string $hmacKey): string;

    public function hashHmacHex(Algorithm $algorithm, string $data, string $hmacKey): string;
}
