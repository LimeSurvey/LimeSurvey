<?php

namespace AltchaOrg\Altcha\V1\Hasher;

enum Algorithm: string
{
    case SHA1 = 'SHA-1';
    case SHA256 = 'SHA-256';
    case SHA512 = 'SHA-512';
}
