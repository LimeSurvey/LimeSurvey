<?php

if (class_exists('ParagonIE_Sodium_Core_MLDSA_InternalVerificationKey', false)) {
    return;
}

/**
 * @internal
 * @psalm-suppress PossiblyUnusedMethod
 */
final class ParagonIE_Sodium_Core_MLDSA_InternalVerificationKey
{
    /** @var ParagonIE_Sodium_Core_MLDSA_Params */
    public $params;
    /** @var string */
    public $rho;
    /** @var ParagonIE_Sodium_Core_MLDSA_Ring[] */
    public $t1;

    /** @var ParagonIE_Sodium_Core_MLDSA_Ntt[][]|null */
    private $cachedAhat = null;
    /** @var string|null */
    private $cachedTr = null;

    /**
     * @param ParagonIE_Sodium_Core_MLDSA_Params $params
     * @param string $rho
     * @param ParagonIE_Sodium_Core_MLDSA_Ring[] $t1
     */
    public function __construct($params, $rho, $t1)
    {
        $this->params = $params;
        $this->rho = $rho;
        $this->t1 = $t1;
    }

    /**
     * @return string
     */
    public function bytes()
    {
        $pk = $this->rho;
        $k = $this->params->k();
        for ($i = 0; $i < $k; ++$i) {
            $packed = ParagonIE_Sodium_Core_MLDSA_Operations::simpleBitPack($this->t1[$i]->symmetric(), 10);
            $pk .= ParagonIE_Sodium_Core_Util::intArrayToString($packed);
        }
        return $pk;
    }

    /**
     * @param ParagonIE_Sodium_Core_MLDSA_InternalSignature $signature
     * @param string $Mprime
     * @return bool
     */
    public function verifyInternal($signature, $Mprime)
    {
        if ($this->cachedAhat === null) {
            $this->cachedAhat = ParagonIE_Sodium_Core_MLDSA_Operations::expandA($this->params, $this->rho);
        }
        if ($this->cachedTr === null) {
            $this->cachedTr = ParagonIE_Sodium_Core_MLDSA_Operations::H($this->bytes(), 64);
        }
        $Ahat = $this->cachedAhat;
        $mu = ParagonIE_Sodium_Core_MLDSA_Operations::H($this->cachedTr . $Mprime, 64);
        $c = ParagonIE_Sodium_Core_MLDSA_Operations::sampleInBall($this->params, $signature->c_tilde);
        $z_hat = ParagonIE_Sodium_Core_MLDSA_Operations::nttVec(ParagonIE_Sodium_Core_MLDSA_Operations::ringVectorFromSymmetric($signature->z));
        $c_hat = ParagonIE_Sodium_Core_MLDSA_Operations::ntt(ParagonIE_Sodium_Core_MLDSA_Ring::fromSymmetric($c));
        $t1_2d = ParagonIE_Sodium_Core_MLDSA_Operations::scalarVectorMul(1 << 13, $this->t1);
        $t1_2d_hat = ParagonIE_Sodium_Core_MLDSA_Operations::nttVec($t1_2d);
        $ct1_2d_hat = ParagonIE_Sodium_Core_MLDSA_Operations::scalarVectorNtt($c_hat, $t1_2d_hat);
        $Az_hat = ParagonIE_Sodium_Core_MLDSA_Operations::matrixVectorNtt($this->params, $Ahat, $z_hat);
        $w_approx = ParagonIE_Sodium_Core_MLDSA_Operations::invNttVec(ParagonIE_Sodium_Core_MLDSA_Operations::subNttVector($Az_hat, $ct1_2d_hat));
        $w1 = ParagonIE_Sodium_Core_MLDSA_Operations::useHintVec($this->params, $signature->h, $w_approx);
        $w1_encoded = ParagonIE_Sodium_Core_MLDSA_Operations::w1Encode($this->params, $w1);
        $c_tilde_prime = ParagonIE_Sodium_Core_MLDSA_Operations::H($mu . $w1_encoded, $this->params->lambda() >> 2);
        $z_inf = ParagonIE_Sodium_Core_MLDSA_Operations::infinityNormVec(ParagonIE_Sodium_Core_MLDSA_Operations::ringVectorFromSymmetric($signature->z));
        $bound = (1 << $this->params->logGamma1()) - $this->params->beta();
        return $z_inf < $bound && ParagonIE_Sodium_Core_Util::hashEquals($signature->c_tilde, $c_tilde_prime);
    }
}
