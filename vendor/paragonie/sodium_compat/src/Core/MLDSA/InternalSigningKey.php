<?php

if (class_exists('ParagonIE_Sodium_Core_MLDSA_InternalSigningKey', false)) {
    return;
}

/**
 * @internal
 * @psalm-suppress PossiblyUnusedMethod
 */
final class ParagonIE_Sodium_Core_MLDSA_InternalSigningKey
{
    /** @var ParagonIE_Sodium_Core_MLDSA_Params */
    public $params;
    /** @var string */
    public $rho;
    /** @var string */
    public $K;
    /** @var string */
    public $tr;
    /** @var ParagonIE_Sodium_Core_MLDSA_Ntt[] */
    public $s1hat;
    /** @var ParagonIE_Sodium_Core_MLDSA_Ntt[] */
    public $s2hat;
    /** @var array */
    public $t0hat;
    /** @var ParagonIE_Sodium_Core_MLDSA_InternalVerificationKey */
    public $vk;
    /** @var ParagonIE_Sodium_Core_MLDSA_Ntt[][] */
    public $Ahat;

    /**
     * @param ParagonIE_Sodium_Core_MLDSA_Params $params
     * @param string $rho
     * @param string $K
     * @param string $tr
     * @param ParagonIE_Sodium_Core_MLDSA_Ntt[] $s1hat
     * @param ParagonIE_Sodium_Core_MLDSA_Ntt[] $s2hat
     * @param array $t0hat
     * @param ParagonIE_Sodium_Core_MLDSA_InternalVerificationKey $vk
     * @param ParagonIE_Sodium_Core_MLDSA_Ntt[][] $Ahat Cached expansion of rho
     */
    public function __construct($params, $rho, $K, $tr, $s1hat, $s2hat, $t0hat, $vk, $Ahat)
    {
        $this->params = $params;
        $this->rho = $rho;
        $this->K = $K;
        $this->tr = $tr;
        $this->s1hat = $s1hat;
        $this->s2hat = $s2hat;
        $this->t0hat = $t0hat;
        $this->vk = $vk;
        $this->Ahat = $Ahat;
    }

    /**
     * FIPS 204, Algorithm 6
     *
     * @param ParagonIE_Sodium_Core_MLDSA_Params $params
     * @param string $seed
     * @return ParagonIE_Sodium_Core_MLDSA_InternalSigningKey
     */
    public static function keyGenInternal($params, $seed)
    {
        $s = ParagonIE_Sodium_Core_MLDSA_Operations::H($seed . ParagonIE_Sodium_Core_Util::intToChr($params->k()) . ParagonIE_Sodium_Core_Util::intToChr($params->l()), 128);
        $rho = ParagonIE_Sodium_Core_Util::substr($s, 0, 32);
        $rhoPrime = ParagonIE_Sodium_Core_Util::substr($s, 32, 64);
        $K = ParagonIE_Sodium_Core_Util::substr($s, 96, 32);
        $A_hat = ParagonIE_Sodium_Core_MLDSA_Operations::expandA($params, $rho);
        /**
         * @var ParagonIE_Sodium_Core_MLDSA_Ring[] $s1
         * @var ParagonIE_Sodium_Core_MLDSA_Ring[] $s2
         */
        list($s1, $s2) = ParagonIE_Sodium_Core_MLDSA_Operations::expandS($params, $rhoPrime);
        $s1hat = ParagonIE_Sodium_Core_MLDSA_Operations::nttVec($s1);
        $s2hat = ParagonIE_Sodium_Core_MLDSA_Operations::nttVec($s2);
        $tmp = ParagonIE_Sodium_Core_MLDSA_Operations::invNttVec(ParagonIE_Sodium_Core_MLDSA_Operations::matrixVectorNtt($params, $A_hat, $s1hat));
        /**
         * @var ParagonIE_Sodium_Core_MLDSA_Ring[] $t1
         * @var ParagonIE_Sodium_Core_MLDSA_Ring[] $t0
         */
        list(
            $t1,
            $t0
        ) = ParagonIE_Sodium_Core_MLDSA_Operations::power2roundVec(ParagonIE_Sodium_Core_MLDSA_Operations::addVector($tmp, $s2));
        $pk = ParagonIE_Sodium_Core_MLDSA_Operations::pkEncode($params, $rho, $t1);
        $tr = ParagonIE_Sodium_Core_MLDSA_Operations::H($pk, 64);
        $vk = new ParagonIE_Sodium_Core_MLDSA_InternalVerificationKey($params, $rho, $t1);
        $t0hat = ParagonIE_Sodium_Core_MLDSA_Operations::nttVec(ParagonIE_Sodium_Core_MLDSA_Operations::ringVectorFromSymmetric($t0));
        return new ParagonIE_Sodium_Core_MLDSA_InternalSigningKey(
            $params,
            $rho,
            $K,
            $tr,
            $s1hat,
            $s2hat,
            $t0hat,
            $vk,
            $A_hat
        );
    }

    /**
     * FIPS 204, Algorithm 7
     *
     * @throws SodiumException
     *
     * @param string $mPrime
     * @param string $rnd
     * @param string|null $mu
     * @return ParagonIE_Sodium_Core_MLDSA_InternalSignature
     */
    public function signInternal(
        #[\SensitiveParameter]
        $mPrime,
        #[\SensitiveParameter]
        $rnd,
        #[\SensitiveParameter]
        $mu = null
    ) {
        // Ahat, s1, s2, and t0 are cached in the NTT domain
        $Ahat = $this->Ahat;
        $l = $this->params->l();
        $mu = isset($mu) ? $mu : ParagonIE_Sodium_Core_MLDSA_Operations::H($this->tr . $mPrime, 64);
        $rhoPrimePrime = ParagonIE_Sodium_Core_MLDSA_Operations::H($this->K . $rnd . $mu, 64);
        $kappa = 0;
        // Let's calculate these only once:
        $lambda = $this->params->lambda();
        $gamma1_beta = (1 << $this->params->logGamma1()) - $this->params->beta();
        $gamma2_beta = $this->params->gamma2() - $this->params->beta();
        $gamma2 = $this->params->gamma2();
        // Rejection sampling loop
        while ($kappa < 821 * $l) {
            $y = ParagonIE_Sodium_Core_MLDSA_Operations::ringVectorFromSymmetric(ParagonIE_Sodium_Core_MLDSA_Operations::expandMask($this->params, $rhoPrimePrime, $kappa));
            $kappa += $l;
            $w = ParagonIE_Sodium_Core_MLDSA_Operations::invNttVec(ParagonIE_Sodium_Core_MLDSA_Operations::matrixVectorNtt($this->params, $Ahat, ParagonIE_Sodium_Core_MLDSA_Operations::nttVec($y)));
            $w1 = ParagonIE_Sodium_Core_MLDSA_Operations::highBitsVec($this->params, $w);
            $w1encoded = ParagonIE_Sodium_Core_MLDSA_Operations::w1Encode($this->params, $w1);
            $c_tilde = ParagonIE_Sodium_Core_MLDSA_Operations::H($mu . $w1encoded, $lambda >> 2);
            $c = ParagonIE_Sodium_Core_MLDSA_Operations::sampleInBall($this->params, $c_tilde);
            $c_hat = ParagonIE_Sodium_Core_MLDSA_Operations::ntt(ParagonIE_Sodium_Core_MLDSA_Ring::fromSymmetric($c));
            $cs1 = ParagonIE_Sodium_Core_MLDSA_Operations::invNttVec(ParagonIE_Sodium_Core_MLDSA_Operations::scalarVectorNtt($c_hat, $this->s1hat));
            $cs2 = ParagonIE_Sodium_Core_MLDSA_Operations::invNttVec(ParagonIE_Sodium_Core_MLDSA_Operations::scalarVectorNtt($c_hat, $this->s2hat));
            $z = ParagonIE_Sodium_Core_MLDSA_Operations::addVector($y, $cs1);
            $r0 = ParagonIE_Sodium_Core_MLDSA_Operations::lowBitsVec(
                $this->params,
                ParagonIE_Sodium_Core_MLDSA_Operations::subVector($w, $cs2)
            );
            // Rejection sampling:
            $z_inf = ParagonIE_Sodium_Core_MLDSA_Operations::infinityNormVec($z);
            $r0_inf = ParagonIE_Sodium_Core_MLDSA_Operations::infinityNormVec(ParagonIE_Sodium_Core_MLDSA_Operations::ringVectorFromSymmetric($r0));
            if ($z_inf >= $gamma1_beta || $r0_inf >= $gamma2_beta) {
                continue;
            }
            $ct0 = ParagonIE_Sodium_Core_MLDSA_Operations::invNttVec(ParagonIE_Sodium_Core_MLDSA_Operations::scalarVectorNtt($c_hat, $this->t0hat));
            $minus_ct0 = ParagonIE_Sodium_Core_MLDSA_Operations::negateVector($ct0);
            $w_cs2_ct0 = ParagonIE_Sodium_Core_MLDSA_Operations::addVector(
                ParagonIE_Sodium_Core_MLDSA_Operations::subVector($w, $cs2),
                $ct0
            );
            $ct0_inf = ParagonIE_Sodium_Core_MLDSA_Operations::infinityNormVec($ct0);
            $h = ParagonIE_Sodium_Core_MLDSA_Operations::makeHintVec($this->params, $minus_ct0, $w_cs2_ct0);
            if ($ct0_inf >= $gamma2 || $h === null) {
                continue;
            }
            return new ParagonIE_Sodium_Core_MLDSA_InternalSignature($this->params, $c_tilde, $z, $h);
        }
        throw new SodiumException("ML-DSA rejection sampling limit exceeded");
    }
}
