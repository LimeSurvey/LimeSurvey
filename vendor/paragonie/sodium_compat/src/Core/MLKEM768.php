<?php

if (class_exists('ParagonIE_Sodium_Core_MLKEM768', false)) {
    return;
}

/**
 * ML-KEM-768, FIPS 203, with libsodium expanded secret keys.
 * @internal
 * @psalm-suppress PossiblyUnusedMethod
 */
final class ParagonIE_Sodium_Core_MLKEM768
{
    /**
     * @param string $seed
     * @return string
     */
    public static function seedKeypair(
        #[\SensitiveParameter]
        $seed
    ) {
        if (ParagonIE_Sodium_Core_Util::strlen($seed) !== 64) {
            throw new SodiumException('Expected 64 bytes');
        }
        $key = ParagonIE_Sodium_Core_MLKem_Operations::kemKeyGen(
            3,
            2,
            ParagonIE_Sodium_Core_Util::substr($seed, 0, 32),
            ParagonIE_Sodium_Core_Util::substr($seed, 32, 32)
        );
        $secret = '';
        foreach ($key['s'] as $poly) {
            $secret .= ParagonIE_Sodium_Core_MLKem_Operations::polyByteEncode($poly);
        }
        $public = $key['encapsulationKeyBytes'];
        return $secret . $public . $key['h'] . ParagonIE_Sodium_Core_Util::substr($seed, 32, 32) . $public;
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
        if (ParagonIE_Sodium_Core_Util::strlen($publicKey) !== 1184) {
            throw new SodiumException('Expected 1184 bytes');
        }
        if (ParagonIE_Sodium_Core_Util::strlen($seed) !== 32) {
            throw new SodiumException('Expected 32 bytes');
        }
        $key = ParagonIE_Sodium_Core_MLKem_Operations::parseEncapsulationKey(3, $publicKey);
        $result = ParagonIE_Sodium_Core_MLKem_Operations::kemEncaps(3, 2, 10, 4, $key, $seed);
        return array($result['ciphertext'], $result['sharedKey']);
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
        if (ParagonIE_Sodium_Core_Util::strlen($ciphertext) !== 1088) {
            throw new SodiumException('Expected 1088 bytes');
        }
        if (ParagonIE_Sodium_Core_Util::strlen($secretKey) !== 2400) {
            throw new SodiumException('Expected 2400 bytes');
        }
        $s = array();
        for ($i = 0; $i < 3; ++$i) {
            $s[$i] = ParagonIE_Sodium_Core_MLKem_Operations::polyByteDecode(ParagonIE_Sodium_Core_Util::substr($secretKey, $i * 384, 384));
        }
        $public = ParagonIE_Sodium_Core_Util::substr($secretKey, 1152, 1184);
        $h = ParagonIE_Sodium_Core_Util::substr($secretKey, 2336, 32);
        if (!ParagonIE_Sodium_Core_Util::hashEquals(ParagonIE_Sodium_Core_Keccak::sha3_256($public), $h)) {
            throw new SodiumException('Invalid ML-KEM secret key hash');
        }
        $key = ParagonIE_Sodium_Core_MLKem_Operations::parseEncapsulationKey(3, $public);
        return ParagonIE_Sodium_Core_MLKem_Operations::kemDecaps(
            3,
            2,
            10,
            4,
            ParagonIE_Sodium_Core_Util::substr($secretKey, 2368, 32),
            $h,
            $key,
            $s,
            $ciphertext
        );
    }

    /**
     * Validate the FIPS 203 expanded-key invariants before native dispatch.
     *
     * @param string $secretKey
     *
     * @return void
     */
    public static function validateSecretKey(
        #[\SensitiveParameter]
        $secretKey
    ) {
        if (ParagonIE_Sodium_Core_Util::strlen($secretKey) !== 2400) {
            throw new SodiumException('Expected 2400 bytes');
        }
        $public = ParagonIE_Sodium_Core_Util::substr($secretKey, 1152, 1184);
        if (!ParagonIE_Sodium_Core_Util::hashEquals(ParagonIE_Sodium_Core_Keccak::sha3_256($public), ParagonIE_Sodium_Core_Util::substr($secretKey, 2336, 32))) {
            throw new SodiumException('Invalid ML-KEM secret key hash');
        }
        for ($i = 0; $i < 6; ++$i) {
            ParagonIE_Sodium_Core_MLKem_Operations::polyByteDecode(ParagonIE_Sodium_Core_Util::substr($secretKey, $i * 384, 384));
        }
    }
}
