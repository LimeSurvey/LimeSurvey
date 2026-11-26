<?php

if (class_exists('ParagonIE_Sodium_Core_MLDSA_InternalSignature', false)) {
    return;
}

/**
 * @internal
 * @psalm-suppress PossiblyUnusedMethod
 */
final class ParagonIE_Sodium_Core_MLDSA_InternalSignature
{
    /** @var ParagonIE_Sodium_Core_MLDSA_Params */
    public $params;
    /** @var string */
    public $c_tilde;
    /** @var ParagonIE_Sodium_Core_MLDSA_Ring[] */
    public $z;
    /** @var ParagonIE_Sodium_Core_MLDSA_Ring[] */
    public $h;

    /**
     * @param ParagonIE_Sodium_Core_MLDSA_Params $params
     * @param string $c_tilde
     * @param ParagonIE_Sodium_Core_MLDSA_Ring[] $z
     * @param ParagonIE_Sodium_Core_MLDSA_Ring[] $h
     */
    public function __construct($params, $c_tilde, $z, $h)
    {
        $this->params = $params;
        $this->c_tilde = $c_tilde;
        $this->z = $z;
        $this->h = $h;
    }

    /**
     * FIPS 204, Algorithm 26
     *
     * @return string
     */
    public function bytes()
    {
        $sig = $this->c_tilde;
        $logGamma1 = $this->params->logGamma1();
        $l = $this->params->l();
        for ($i = 0; $i < $l; ++$i) {
            $packed = ParagonIE_Sodium_Core_MLDSA_Operations::bitPack($this->z[$i]->symmetric(), $logGamma1);
            $sig .= ParagonIE_Sodium_Core_Util::intArrayToString($packed);
        }
        $packed = ParagonIE_Sodium_Core_MLDSA_Operations::hintBitPack($this->params, $this->h);
        $sig .= ParagonIE_Sodium_Core_Util::intArrayToString($packed);
        return $sig;
    }

    /**
     * FIPS 204, Algorithm 27
     *
     * @param ParagonIE_Sodium_Core_MLDSA_Params $params
     * @param string $encoded
     * @return self
     */
    public static function fromBytes($params, $encoded)
    {
        if (ParagonIE_Sodium_Core_Util::strlen($encoded) !== $params->signatureSize()) {
            throw new SodiumException('Invalid signature length');
        }
        $sig = $encoded;
        $logGamma1 = $params->logGamma1();
        $l = $params->l();
        $lambda_4 = $params->lambda() >> 2;
        $c_tilde = ParagonIE_Sodium_Core_Util::substr($sig, 0, $lambda_4);
        $sig = ParagonIE_Sodium_Core_Util::substr($sig, $lambda_4);
        $len = $logGamma1 + 1 << 5;
        $z = array();
        for ($i = 0; $i < $l; ++$i) {
            $x_i = ParagonIE_Sodium_Core_Util::stringToIntArray(ParagonIE_Sodium_Core_Util::substr($sig, 0, $len));
            $sig = ParagonIE_Sodium_Core_Util::substr($sig, $len);
            $z[$i] = ParagonIE_Sodium_Core_MLDSA_Operations::bitUnpack($x_i, $logGamma1);
        }
        $h = ParagonIE_Sodium_Core_MLDSA_Operations::hintBitUnpack(
            $params,
            ParagonIE_Sodium_Core_Util::stringToIntArray($sig)
        );
        return new self($params, $c_tilde, $z, $h);
    }
}
