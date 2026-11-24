<?php

if (class_exists('ParagonIE_Sodium_Core_XWing', false)) {
    return;
}

/** @internal XWing hybrid KEM, following libsodium's construction. */
final class ParagonIE_Sodium_Core_XWing
{
    /**
     * @param string $seed
     * @return string
     */
    public static function seedKeypair(
        #[\SensitiveParameter]
        $seed
    ) {
        if (ParagonIE_Sodium_Core_Util::strlen($seed) !== 32) {
            throw new SodiumException('Expected 32 bytes');
        }
        $expanded = ParagonIE_Sodium_Core_Keccak::shake256()->absorb($seed)->squeeze(96);
        $mlkem = ParagonIE_Sodium_Core_MLKEM768::seedKeypair(ParagonIE_Sodium_Core_Util::substr($expanded, 0, 64));
        $publicX = ParagonIE_Sodium_Compat::crypto_scalarmult_base(ParagonIE_Sodium_Core_Util::substr($expanded, 64, 32));
        return $seed . ParagonIE_Sodium_Core_Util::substr($mlkem, 2400) . $publicX;
    }

    /**
     * @param string $publicKey
     * @param string $seed
     * @return array
     */
    public static function enc(
        $publicKey,
        #[\SensitiveParameter]
        $seed
    ) {
        if (ParagonIE_Sodium_Core_Util::strlen($publicKey) !== 1216) {
            throw new SodiumException('Expected 1216 bytes');
        }
        if (ParagonIE_Sodium_Core_Util::strlen($seed) !== 64) {
            throw new SodiumException('Expected 64 bytes');
        }
        $mlkem = ParagonIE_Sodium_Core_MLKEM768::enc(
            ParagonIE_Sodium_Core_Util::substr($publicKey, 0, 1184),
            ParagonIE_Sodium_Core_Util::substr($seed, 0, 32)
        );
        $publicX = ParagonIE_Sodium_Core_Util::substr($publicKey, 1184, 32);
        $secretX = ParagonIE_Sodium_Core_Util::substr($seed, 32, 32);
        $cipherX = ParagonIE_Sodium_Compat::crypto_scalarmult_base($secretX);
        $sharedX = ParagonIE_Sodium_Compat::crypto_scalarmult($secretX, $publicX);
        return array($mlkem[0] . $cipherX, self::combine($mlkem[1], $sharedX, $cipherX, $publicX));
    }

    /**
     * @param string $ciphertext
     * @param string $secretKey
     * @return string
     */
    public static function dec(
        $ciphertext,
        #[\SensitiveParameter]
        $secretKey
    ) {
        if (ParagonIE_Sodium_Core_Util::strlen($ciphertext) !== 1120) {
            throw new SodiumException('Expected 1120 bytes');
        }
        if (ParagonIE_Sodium_Core_Util::strlen($secretKey) !== 32) {
            throw new SodiumException('Expected 32 bytes');
        }
        $expanded = ParagonIE_Sodium_Core_Keccak::shake256()->absorb($secretKey)->squeeze(96);
        $mlkem = ParagonIE_Sodium_Core_MLKEM768::seedKeypair(ParagonIE_Sodium_Core_Util::substr($expanded, 0, 64));
        $secretX = ParagonIE_Sodium_Core_Util::substr($expanded, 64, 32);
        $publicX = ParagonIE_Sodium_Compat::crypto_scalarmult_base($secretX);
        $cipherX = ParagonIE_Sodium_Core_Util::substr($ciphertext, 1088, 32);
        $shared = ParagonIE_Sodium_Core_MLKEM768::dec(
            ParagonIE_Sodium_Core_Util::substr($ciphertext, 0, 1088),
            ParagonIE_Sodium_Core_Util::substr($mlkem, 0, 2400)
        );
        $sharedX = ParagonIE_Sodium_Compat::crypto_scalarmult($secretX, $cipherX);
        return self::combine($shared, $sharedX, $cipherX, $publicX);
    }

    /**
     * @param string $mlkem
     * @param string $x25519
     * @param string $cipherX
     * @param string $publicX
     * @return string
     */
    private static function combine($mlkem, $x25519, $cipherX, $publicX)
    {
        return ParagonIE_Sodium_Core_Keccak::sha3_256($mlkem . $x25519 . $cipherX . $publicX . "\\.//^\\");
    }
}
