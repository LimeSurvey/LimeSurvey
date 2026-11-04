<?php

namespace AltchaOrg\Altcha;

enum HmacAlgorithm: string
{
    case SHA256 = 'SHA-256';
    case SHA384 = 'SHA-384';
    case SHA512 = 'SHA-512';

    /**
     * @return non-falsy-string
     */
    public function hashAlgo(): string
    {
        return match ($this) {
            self::SHA256 => 'sha256',
            self::SHA384 => 'sha384',
            self::SHA512 => 'sha512',
        };
    }
}
