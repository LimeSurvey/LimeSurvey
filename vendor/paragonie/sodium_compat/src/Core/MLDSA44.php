<?php

if (class_exists('ParagonIE_Sodium_Core_MLDSA44', false)) {
    return;
}

/** @internal ML-DSA-44 with FIPS 204 expanded signing keys. */
final class ParagonIE_Sodium_Core_MLDSA44
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
        $params = new ParagonIE_Sodium_Core_MLDSA_Params();
        $key = ParagonIE_Sodium_Core_MLDSA_InternalSigningKey::keyGenInternal($params, $seed);
        $secret = $key->rho . $key->K . $key->tr;
        foreach (array($key->s1hat, $key->s2hat, $key->t0hat) as $i => $vector) {
            foreach (ParagonIE_Sodium_Core_MLDSA_Operations::invNttVec($vector) as $poly) {
                if ($i === 2) {
                    $packed = ParagonIE_Sodium_Core_MLDSA_Operations::bitPack($poly->symmetric(), 12);
                } else {
                    $symmetric = $poly->symmetric();
                    $encoded = new ParagonIE_Sodium_Core_MLDSA_Ring();
                    for ($j = 0; $j < 256; ++$j) {
                        $encoded[$j] = 2 - $symmetric[$j];
                    }
                    $packed = ParagonIE_Sodium_Core_MLDSA_Operations::simpleBitPack($encoded, 3);
                }
                $secret .= ParagonIE_Sodium_Core_Util::intArrayToString($packed);
            }
        }
        return $secret . $key->vk->bytes();
    }

    /**
     * Explicit randomness is an internal vector-testing hook.
     *
     * @param string $message
     * @param string $secretKey
     * @param string $context
     * @param string|null $randomness
     * @param string|null $mu
     * @return string
     */
    public static function sign(
        $message,
        #[\SensitiveParameter]
        $secretKey,
        $context = '',
        #[\SensitiveParameter]
        $randomness = null,
        $mu = null
    ) {
        if (ParagonIE_Sodium_Core_Util::strlen($secretKey) !== 2560) {
            throw new SodiumException('Expected 2560 bytes');
        }
        self::context($context);
        if ($randomness === null) {
            $randomness = random_bytes(32);
        }
        if (ParagonIE_Sodium_Core_Util::strlen($randomness) !== 32) {
            throw new SodiumException('Expected 32 bytes');
        }
        $params = new ParagonIE_Sodium_Core_MLDSA_Params();
        $rho = ParagonIE_Sodium_Core_Util::substr($secretKey, 0, 32);
        $vectors = array();
        $offset = 128;
        for ($v = 0; $v < 3; ++$v) {
            $vector = array();
            for ($i = 0; $i < 4; ++$i) {
                $bytes = ParagonIE_Sodium_Core_Util::stringToIntArray(ParagonIE_Sodium_Core_Util::substr($secretKey, $offset, $v === 2 ? 416 : 96));
                if ($v !== 2) {
                    $encoded = ParagonIE_Sodium_Core_MLDSA_Operations::simpleBitUnpack($bytes, 3);
                    for ($j = 0; $j < 256; ++$j) {
                        if ($encoded[$j] > 4) {
                            throw new SodiumException('Invalid ML-DSA secret coefficient');
                        }
                        $encoded[$j] = 2 - $encoded[$j];
                    }
                    $poly = $encoded;
                } else {
                    $poly = ParagonIE_Sodium_Core_MLDSA_Operations::bitUnpack($bytes, 12);
                }
                $vector[] = ParagonIE_Sodium_Core_MLDSA_Ring::fromSymmetric($poly);
                $offset += $v === 2 ? 416 : 96;
            }
            $vectors[] = ParagonIE_Sodium_Core_MLDSA_Operations::nttVec($vector);
        }
        $key = new ParagonIE_Sodium_Core_MLDSA_InternalSigningKey(
            $params,
            $rho,
            ParagonIE_Sodium_Core_Util::substr($secretKey, 32, 32),
            ParagonIE_Sodium_Core_Util::substr($secretKey, 64, 64),
            $vectors[0],
            $vectors[1],
            $vectors[2],
            new ParagonIE_Sodium_Core_MLDSA_InternalVerificationKey($params, $rho, array()),
            ParagonIE_Sodium_Core_MLDSA_Operations::expandA($params, $rho)
        );
        return $key->signInternal(
            ParagonIE_Sodium_Core_MLDSA_Operations::prepareMessage($message, $context),
            $randomness,
            $mu
        )->bytes();
    }

    /**
     * @internal
 * @psalm-suppress PossiblyUnusedMethod FIPS 204 external-mu test vectors.
     *
     * @param string $mu
     * @param string $secretKey
     * @param string $randomness
     * @return string
     */
    public static function signMu(
        $mu,
        #[\SensitiveParameter]
        $secretKey,
        #[\SensitiveParameter]
        $randomness
    ) {
        if (ParagonIE_Sodium_Core_Util::strlen($mu) !== 64) {
            throw new SodiumException('Expected 64 bytes');
        }
        return self::sign('', $secretKey, '', $randomness, $mu);
    }

    /**
     * @param string $signature
     * @param string $message
     * @param string $publicKey
     * @param string $context
     * @return bool
     */
    public static function verify($signature, $message, $publicKey, $context = '')
    {
        if (ParagonIE_Sodium_Core_Util::strlen($publicKey) !== 1312) {
            throw new SodiumException('Expected 1312 bytes');
        }
        self::context($context);
        if (ParagonIE_Sodium_Core_Util::strlen($signature) !== 2420) {
            throw new SodiumException('Expected 2420 bytes');
        }
        $params = new ParagonIE_Sodium_Core_MLDSA_Params();
        try {
            $decoded = ParagonIE_Sodium_Core_MLDSA_Operations::pkDecode($params, $publicKey);
            $key = new ParagonIE_Sodium_Core_MLDSA_InternalVerificationKey($params, $decoded[0], $decoded[1]);
            $sig = ParagonIE_Sodium_Core_MLDSA_InternalSignature::fromBytes($params, $signature);
            return $key->verifyInternal(
                $sig,
                ParagonIE_Sodium_Core_MLDSA_Operations::prepareMessage($message, $context)
            );
        } catch (SodiumException $ex) {
            return false;
        }
    }

    /**
     * @param string $context
     * @return void
     */
    private static function context($context)
    {
        if (ParagonIE_Sodium_Core_Util::strlen($context) > 255) {
            throw new SodiumException('ML-DSA context must be at most 255 bytes');
        }
    }
}
