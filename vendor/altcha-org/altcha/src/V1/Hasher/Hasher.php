<?php

namespace AltchaOrg\Altcha\V1\Hasher;

class Hasher implements HasherInterface
{
    public function hash(Algorithm $algorithm, string $data): string
    {
        return match ($algorithm) {
            Algorithm::SHA1 => sha1($data, true),
            Algorithm::SHA256 => hash('sha256', $data, true),
            Algorithm::SHA512 => hash('sha512', $data, true),
        };
    }

    public function hashHex(Algorithm $algorithm, string $data): string
    {
        return bin2hex($this->hash($algorithm, $data));
    }

    public function hashHmac(Algorithm $algorithm, string $data, string $hmacKey): string
    {
        return match ($algorithm) {
            Algorithm::SHA1 => hash_hmac('sha1', $data, $hmacKey, true),
            Algorithm::SHA256 => hash_hmac('sha256', $data, $hmacKey, true),
            Algorithm::SHA512 => hash_hmac('sha512', $data, $hmacKey, true),
        };
    }

    public function hashHmacHex(Algorithm $algorithm, string $data, string $hmacKey): string
    {
        return bin2hex($this->hashHmac($algorithm, $data, $hmacKey));
    }
}
