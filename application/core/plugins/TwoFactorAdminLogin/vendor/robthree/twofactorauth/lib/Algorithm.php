<?php

declare(strict_types=1);

namespace RobThree\Auth;

/**
 * List of supported cryptographic algorithms
 */
enum Algorithm: string
{
    case Md5 = 'md5';
    case Sha1 = 'sha1';
    case Sha256 = 'sha256';
    case Sha512 = 'sha512';
}
