<?php

if (class_exists('ParagonIE_Sodium_Core_MLDSA_Params', false)) {
    return;
}

/** @internal ML-DSA-44 parameters (FIPS 204). */
final class ParagonIE_Sodium_Core_MLDSA_Params
{
    /**
     * @return int
     */
    public function tau()
    {
        return 39;
    }

    /**
     * @return int
     */
    public function lambda()
    {
        return 128;
    }

    /**
     * @return int
     */
    public function logGamma1()
    {
        return 17;
    }

    /**
     * @return int
     */
    public function gamma2()
    {
        return 95232;
    }

    /**
     * @return int
     */
    public function k()
    {
        return 4;
    }

    /**
     * @return int
     */
    public function l()
    {
        return 4;
    }

    /**
     * @return int
     */
    public function eta()
    {
        return 2;
    }

    /**
     * @return int
     */
    public function beta()
    {
        return 78;
    }

    /**
     * @return int
     */
    public function omega()
    {
        return 80;
    }

    /**
     * @return int
     */
    public function publicKeySize()
    {
        return 1312;
    }

    /**
     * @return int
     */
    public function signatureSize()
    {
        return 2420;
    }

    /**
     * @return int
     */
    public function w1bits()
    {
        return 6;
    }
}
