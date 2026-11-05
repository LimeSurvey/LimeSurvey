<?php

namespace AltchaOrg\Altcha\Algorithm;

enum ShaAlgorithm: string
{
    case SHA1 = 'SHA-1';
    case SHA256 = 'SHA-256';
    case SHA384 = 'SHA-384';
    case SHA512 = 'SHA-512';

    public function hashAlgo(): string
    {
        return match ($this) {
            self::SHA1 => 'sha1',
            self::SHA256 => 'sha256',
            self::SHA384 => 'sha384',
            self::SHA512 => 'sha512',
        };
    }
}
