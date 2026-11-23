<?php

if (class_exists('ParagonIE_Sodium_Core_MLDSA_Operations', false)) {
    return;
}

/**
 * @internal
 * @psalm-suppress PossiblyUnusedMethod
 */
final class ParagonIE_Sodium_Core_MLDSA_Operations
{
    /** @var int[] */
    public static $ZETAS = array(
        0,
        4808194,
        3765607,
        3761513,
        5178923,
        5496691,
        5234739,
        5178987,
        7778734,
        3542485,
        2682288,
        2129892,
        3764867,
        7375178,
        557458,
        7159240,
        5010068,
        4317364,
        2663378,
        6705802,
        4855975,
        7946292,
        676590,
        7044481,
        5152541,
        1714295,
        2453983,
        1460718,
        7737789,
        4795319,
        2815639,
        2283733,
        3602218,
        3182878,
        2740543,
        4793971,
        5269599,
        2101410,
        3704823,
        1159875,
        394148,
        928749,
        1095468,
        4874037,
        2071829,
        4361428,
        3241972,
        2156050,
        3415069,
        1759347,
        7562881,
        4805951,
        3756790,
        6444618,
        6663429,
        4430364,
        5483103,
        3192354,
        556856,
        3870317,
        2917338,
        1853806,
        3345963,
        1858416,
        3073009,
        1277625,
        5744944,
        3852015,
        4183372,
        5157610,
        5258977,
        8106357,
        2508980,
        2028118,
        1937570,
        4564692,
        2811291,
        5396636,
        7270901,
        4158088,
        1528066,
        482649,
        1148858,
        5418153,
        7814814,
        169688,
        2462444,
        5046034,
        4213992,
        4892034,
        1987814,
        5183169,
        1736313,
        235407,
        5130263,
        3258457,
        5801164,
        1787943,
        5989328,
        6125690,
        3482206,
        4197502,
        7080401,
        6018354,
        7062739,
        2461387,
        3035980,
        621164,
        3901472,
        7153756,
        2925816,
        3374250,
        1356448,
        5604662,
        2683270,
        5601629,
        4912752,
        2312838,
        7727142,
        7921254,
        348812,
        8052569,
        1011223,
        6026202,
        4561790,
        6458164,
        6143691,
        1744507,
        1753,
        6444997,
        5720892,
        6924527,
        2660408,
        6600190,
        8321269,
        2772600,
        1182243,
        87208,
        636927,
        4415111,
        4423672,
        6084020,
        5095502,
        4663471,
        8352605,
        822541,
        1009365,
        5926272,
        6400920,
        1596822,
        4423473,
        4620952,
        6695264,
        4969849,
        2678278,
        4611469,
        4829411,
        635956,
        8129971,
        5925040,
        4234153,
        6607829,
        2192938,
        6653329,
        2387513,
        4768667,
        8111961,
        5199961,
        3747250,
        2296099,
        1239911,
        4541938,
        3195676,
        2642980,
        1254190,
        8368000,
        2998219,
        141835,
        8291116,
        2513018,
        7025525,
        613238,
        7070156,
        6161950,
        7921677,
        6458423,
        4040196,
        4908348,
        2039144,
        6500539,
        7561656,
        6201452,
        6757063,
        2105286,
        6006015,
        6346610,
        586241,
        7200804,
        527981,
        5637006,
        6903432,
        1994046,
        2491325,
        6987258,
        507927,
        7192532,
        7655613,
        6545891,
        5346675,
        8041997,
        2647994,
        3009748,
        5767564,
        4148469,
        749577,
        4357667,
        3980599,
        2569011,
        6764887,
        1723229,
        1665318,
        2028038,
        1163598,
        5011144,
        3994671,
        8368538,
        7009900,
        3020393,
        3363542,
        214880,
        545376,
        7609976,
        3105558,
        7277073,
        508145,
        7826699,
        860144,
        3430436,
        140244,
        6866265,
        6195333,
        3123762,
        2358373,
        6187330,
        5365997,
        6663603,
        2926054,
        7987710,
        8077412,
        3531229,
        4405932,
        4606686,
        1900052,
        7598542,
        1054478,
        7648983
    );

    /**
     * @param string $str
     * @param int $bytes
     * @return string
     */
    public static function H($str, $bytes)
    {
        return ParagonIE_Sodium_Core_Keccak::shake256()->absorb($str)->squeeze($bytes);
    }

    /**
     * FIPS 204, Algorithm 14
     *
     * @param int $b0
     * @param int $b1
     * @param int $b2
     * @return int|null
     */
    public static function fromThreeBytes($b0, $b1, $b2)
    {
        $bp2 = $b2 & 0x7f;
        $z = $bp2 << 16 | $b1 << 8 | $b0;
        $invalid = ParagonIE_Sodium_Core_MLDSA_Field::Q - ($z + 1) >> 31;
        if ($invalid !== 0) {
            return null;
        }
        return ParagonIE_Sodium_Core_MLDSA_Field::reduceOnce($z);
    }

    /**
     * FIPS 204, Algorithm 15
     *
     * @param int $eta
     * @param int $b
     * @return int|null
     */
    public static function fromHalfByte($eta, $b)
    {
        if ($eta === 2 && $b < 15) {
            return ParagonIE_Sodium_Core_MLDSA_Field::newFromSymmetric(2 - ($b - ($b * 205 >> 10) * 5));
        } elseif ($eta === 4 && $b < 9) {
            return ParagonIE_Sodium_Core_MLDSA_Field::newFromSymmetric(4 - $b);
        }
        return null;
    }

    /**
     * FIPS 204, Algorithm 16
     *
     * @param ParagonIE_Sodium_Core_MLDSA_Ring $w
     * @param int $k
     * @return array
     */
    public static function simpleBitPack($w, $k)
    {
        $coeffs = $w->toArray();
        $n = 256;
        $numBytes = $n * $k + 7 >> 3;
        $z = array_fill(0, $numBytes, 0);
        $bitPos = 0;
        for ($i = 0; $i < $n; ++$i) {
            $v = $coeffs[$i];
            for ($j = 0; $j < $k; ++$j) {
                $z[$bitPos >> 3] |= ($v & 1) << ($bitPos & 7);
                $v >>= 1;
                $bitPos++;
            }
        }
        return $z;
    }

    /**
     * FIPS 204, Algorithm 17
     *
     * @param ParagonIE_Sodium_Core_MLDSA_Ring $w
     * @param int $k
     * @return array
     */
    public static function bitPack($w, $k)
    {
        $coeffs = $w->toArray();
        $n = 256;
        $bitsPerCoeff = $k + 1;
        $numBytes = $n * $bitsPerCoeff + 7 >> 3;
        $z = array_fill(0, $numBytes, 0);
        $base = 1 << $k;
        $bitPos = 0;
        for ($i = 0; $i < $n; ++$i) {
            $v = $base - $coeffs[$i];
            for ($j = 0; $j < $bitsPerCoeff; ++$j) {
                $z[$bitPos >> 3] |= ($v & 1) << ($bitPos & 7);
                $v >>= 1;
                $bitPos++;
            }
        }
        return $z;
    }

    /**
     * @param array $z
     * @param int $k
     * @return ParagonIE_Sodium_Core_MLDSA_Ring
     */
    protected static function bitUnpackInternal($z, $k)
    {
        $coeffs = array();
        $bitPos = 0;
        for ($i = 0; $i < 256; ++$i) {
            $v = 0;
            for ($j = 0; $j < $k; ++$j) {
                $v |= ($z[$bitPos >> 3] >> ($bitPos & 7) & 1) << $j;
                $bitPos++;
            }
            $coeffs[$i] = $v;
        }
        $poly = new ParagonIE_Sodium_Core_MLDSA_Ring();
        foreach ($coeffs as $index => $coefficient) {
            $poly->{'c' . $index} = $coefficient;
        }
        return $poly;
    }

    /**
     * FIPS 204, Algorithm 18
     *
     * @param array $b
     * @param int $k
     * @return ParagonIE_Sodium_Core_MLDSA_Ring
     */
    public static function simpleBitUnpack($b, $k)
    {
        return self::bitUnpackInternal($b, $k);
    }

    /**
     * FIPS 204, Algorithm 19
     *
     * @param array $v
     * @param int $k
     * @return ParagonIE_Sodium_Core_MLDSA_Ring
     */
    public static function bitUnpack($v, $k)
    {
        $wa = self::bitUnpackInternal($v, $k + 1)->toArray();
        $za = array();
        $base = 1 << $k;
        for ($i = 0; $i < 256; ++$i) {
            $za[$i] = $base - $wa[$i];
        }
        $poly = new ParagonIE_Sodium_Core_MLDSA_Ring();
        foreach ($za as $index => $coefficient) {
            $poly->{'c' . $index} = $coefficient;
        }
        return $poly;
    }

    /**
     * FIPS 20, Algorithm 20
     *
     * This does not need to be constant-time; hints are public
     *
     * @param ParagonIE_Sodium_Core_MLDSA_Params $params
     * @param ParagonIE_Sodium_Core_MLDSA_Ring[] $h
     * @return int[]
     */
    public static function hintBitPack($params, $h)
    {
        $omega = $params->omega();
        $k = count($h);
        $y = array_fill(0, $k + $omega, 0);
        $index = 0;
        for ($i = 0; $i < $k; ++$i) {
            $ha = $h[$i]->toArray();
            for ($j = 0; $j < 256; ++$j) {
                if ($ha[$j] === 1) {
                    $y[$index] = $j & 0xff;
                    ++$index;
                }
            }
            $y[$omega + $i] = $index & 0xff;
        }
        return $y;
    }

    /**
     * FIPS 204, Algorithm 21
     *
     * @param ParagonIE_Sodium_Core_MLDSA_Params $params
     * @param int[] $y
     * @return ParagonIE_Sodium_Core_MLDSA_Ring[]
     *
     * @throws SodiumException
     */
    public static function hintBitUnpack($params, $y)
    {
        $omega = $params->omega();
        $k = $params->k();
        $h = array();
        $index = 0;
        for ($i = 0; $i < $k; ++$i) {
            $h[$i] = ParagonIE_Sodium_Core_MLDSA_Ring::zero();
            if ($y[$omega + $i] < $index || $y[$omega + $i] > $omega) {
                throw new SodiumException("hintBitUnpack: malformed input");
            }
            $first = $index;
            while ($index < $y[$omega + $i]) {
                if ($index > $first) {
                    if ($y[$index - 1] >= $y[$index]) {
                        throw new SodiumException("hintBitUnpack: malformed input");
                    }
                }
                $h[$i]->{'c' . $y[$index]} = 1;
                ++$index;
            }
        }
        for ($i = $index; $i < $omega; ++$i) {
            if ($y[$i] != 0) {
                throw new SodiumException("hintBitUnpack: malformed input");
            }
        }
        return $h;
    }

    /**
     * FIPS 204, Algorithm 22
     *
     * @param ParagonIE_Sodium_Core_MLDSA_Params $params
     * @param string $rho
     * @param ParagonIE_Sodium_Core_MLDSA_Ring[] $t1
     * @return string
     */
    public static function pkEncode($params, $rho, $t1)
    {
        $k = $params->k();
        $pk = $rho;
        for ($i = 0; $i < $k; ++$i) {
            $pk .= ParagonIE_Sodium_Core_Util::intArrayToString(self::simpleBitPack($t1[$i], 10));
        }
        return $pk;
    }

    /**
     * FIPS 204, Algorithm 23
     *
     * @throws SodiumException
     *
     * @param ParagonIE_Sodium_Core_MLDSA_Params $params
     * @param string $publicKey
     * @return array
     */
    public static function pkDecode($params, $publicKey)
    {
        if (ParagonIE_Sodium_Core_Util::strlen($publicKey) !== $params->publicKeySize()) {
            throw new SodiumException("invalid public key size");
        }
        $rho = ParagonIE_Sodium_Core_Util::substr($publicKey, 0, 32);
        $z = ParagonIE_Sodium_Core_Util::substr($publicKey, 32);
        $k = $params->k();
        $t1 = array();
        $elemLen = 320;
        for ($i = 0; $i < $k; ++$i) {
            $b = ParagonIE_Sodium_Core_Util::stringToIntArray(ParagonIE_Sodium_Core_Util::substr($z, 0, $elemLen));
            $t1[$i] = ParagonIE_Sodium_Core_MLDSA_Ring::fromSymmetric(self::simpleBitUnpack($b, 10));
            $z = ParagonIE_Sodium_Core_Util::substr($z, $elemLen);
        }
        return array($rho, $t1);
    }
    // Expanded signing-key encoding lives in ParagonIE_Sodium_Core_MLDSA44.

    /**
     * FIPS 204, Algorithm 28
     *
     * @param ParagonIE_Sodium_Core_MLDSA_Params $params
     * @param array $w1
     * @return string
     */
    public static function w1Encode($params, $w1)
    {
        $k = $params->k();
        $w = '';
        $b = $params->w1bits();
        for ($i = 0; $i < $k; ++$i) {
            $packed = self::simpleBitPack($w1[$i], $b);
            $w .= ParagonIE_Sodium_Core_Util::intArrayToString($packed);
        }
        return $w;
    }

    /**
     * FIPS 204, Algorithm 29
     *
     * @param ParagonIE_Sodium_Core_MLDSA_Params $params
     * @param string $seed
     * @return ParagonIE_Sodium_Core_MLDSA_Ring
     */
    public static function sampleInBall($params, $seed)
    {
        $ctx = ParagonIE_Sodium_Core_Keccak::shake256();
        $ctx->absorb($seed);
        $buf = $ctx->squeeze(256);
        $bufLen = 256;
        $bufIdx = 0;
        $s = array();
        for ($k = 0; $k < 8; $k++) {
            $s[$k] = ParagonIE_Sodium_Core_Util::chrToInt($buf[$bufIdx++]);
        }
        $c = ParagonIE_Sodium_Core_MLDSA_Ring::zero();
        $tau = $params->tau();
        for ($i = 256 - $tau; $i < 256; ++$i) {
            do {
                if ($bufIdx >= $bufLen) {
                    $buf .= $ctx->squeeze(136);
                    $bufLen += 136;
                }
                $j0 = ParagonIE_Sodium_Core_Util::chrToInt($buf[$bufIdx++]);
            } while ($j0 > $i);
            $c[$i] = $c[$j0];
            $idx = $i + $tau - 256;
            $h = $s[$idx >> 3] >> ($idx & 7) & 1;
            $c[$j0] = 1 - ($h << 1);
        }
        return $c;
    }

    /**
     * FIPS 204, Algorithm 30
     *
     * @param string $seed
     * @return ParagonIE_Sodium_Core_MLDSA_Ntt
     */
    public static function rejNttPoly($seed)
    {
        $ctx = ParagonIE_Sodium_Core_Keccak::shake128();
        $ctx->absorb($seed);
        $buf = $ctx->squeeze(840);
        $bufLen = 840;
        $bufIdx = 0;
        $a_hat = ParagonIE_Sodium_Core_MLDSA_Ntt::zero();
        for ($j = 0; $j < 256; ++$j) {
            do {
                if ($bufIdx + 3 > $bufLen) {
                    $buf .= $ctx->squeeze(168);
                    $bufLen += 168;
                }
                $tmp = self::fromThreeBytes(
                    ParagonIE_Sodium_Core_Util::chrToInt($buf[$bufIdx]),
                    ParagonIE_Sodium_Core_Util::chrToInt($buf[$bufIdx + 1]),
                    ParagonIE_Sodium_Core_Util::chrToInt($buf[$bufIdx + 2])
                );
                $bufIdx += 3;
            } while (is_null($tmp));
            $a_hat[$j] = $tmp;
        }
        return $a_hat;
    }

    /**
     * FIPS 204, Algorithm 31
     *
     * @param ParagonIE_Sodium_Core_MLDSA_Params $params
     * @param string $seed
     * @return ParagonIE_Sodium_Core_MLDSA_Ring
     */
    public static function rejBoundedPoly($params, $seed)
    {
        $ctx = ParagonIE_Sodium_Core_Keccak::shake256();
        $ctx->absorb($seed);
        $buf = $ctx->squeeze(272);
        $bufLen = 272;
        $bufIdx = 0;
        $a = ParagonIE_Sodium_Core_MLDSA_Ring::zero();
        $j = 0;
        $eta = $params->eta();
        while ($j < 256) {
            if ($bufIdx >= $bufLen) {
                $buf .= $ctx->squeeze(136);
                $bufLen += 136;
            }
            $ord = ParagonIE_Sodium_Core_Util::chrToInt($buf[$bufIdx++]);
            $z0 = self::fromHalfByte($eta, $ord & 0xf);
            if (!is_null($z0)) {
                $a->{'c' . $j} = $z0;
                ++$j;
            }
            $z1 = self::fromHalfByte($eta, $ord >> 4 & 0xf);
            if (!is_null($z1) && $j < 256) {
                $a->{'c' . $j} = $z1;
                ++$j;
            }
        }
        return $a;
    }

    /**
     * FIPS 204, Algorithm 32
     *
     * @param ParagonIE_Sodium_Core_MLDSA_Params $params
     * @param string $rho
     * @return ParagonIE_Sodium_Core_MLDSA_Ntt[][]
     */
    public static function expandA($params, $rho)
    {
        $k = $params->k();
        $l = $params->l();
        $A_hat = array();
        for ($r = 0; $r < $k; ++$r) {
            $A_hat[$r] = array();
            for ($s = 0; $s < $l; ++$s) {
                $rho_hat = $rho . ParagonIE_Sodium_Core_Util::intToChr($s) . ParagonIE_Sodium_Core_Util::intToChr($r);
                $A_hat[$r][$s] = self::rejNttPoly($rho_hat);
            }
        }
        return $A_hat;
    }

    /**
     * FIPS 204, Algorithm 33
     *
     * @param ParagonIE_Sodium_Core_MLDSA_Params $params
     * @param string $rho
     * @return array{0: ParagonIE_Sodium_Core_MLDSA_Ring[], 1: ParagonIE_Sodium_Core_MLDSA_Ring[]}
     */
    public static function expandS($params, $rho)
    {
        $k = $params->k();
        $l = $params->l();
        $s1 = array();
        for ($r = 0; $r < $l; ++$r) {
            /** @var string $nonce */
            $nonce = pack('v', $r);
            $s1[$r] = self::rejBoundedPoly($params, $rho . $nonce);
        }
        $s2 = array();
        for ($r = 0; $r < $k; ++$r) {
            /** @var string $nonce */
            $nonce = pack('v', $r + $l);
            $s2[$r] = self::rejBoundedPoly($params, $rho . $nonce);
        }
        return array($s1, $s2);
    }

    /**
     * FIPS 204, Algorithm 34
     *
     * @param ParagonIE_Sodium_Core_MLDSA_Params $params
     * @param string $rho
     * @param int $mu
     * @return array
     */
    public static function expandMask($params, $rho, $mu)
    {
        $l = $params->l();
        $c = $params->logGamma1() + 1;
        $y = array();
        $logGamma1 = $params->logGamma1();
        for ($r = 0; $r < $l; ++$r) {
            /** @var string $nonce */
            $nonce = pack('v', $mu + $r);
            $rhoPrime = $rho . $nonce;
            $v = ParagonIE_Sodium_Core_Util::stringToIntArray(self::H($rhoPrime, $c << 5));
            $y[$r] = self::bitUnpack($v, $logGamma1);
        }
        return $y;
    }

    /**
     * FIPS 204, Algorithm 39
     *
     * @throws SodiumException
     *
     * @param int $z
     * @param int $r
     * @param int $gamma2
     * @return int
     * @psalm-suppress PossiblyUnusedMethod Covered by internal primitive tests.
     */
    public static function makeHint($z, $r, $gamma2)
    {
        return ParagonIE_Sodium_Core_MLDSA_Field::makeHint($z, $r, $gamma2);
    }

    /**
     * FIPS 204, Algorithm 40
     *
     * This does not need to be constant-time, as it's only used in verification.
     *
     * @param ParagonIE_Sodium_Core_MLDSA_Params $params
     * @param ParagonIE_Sodium_Core_MLDSA_Ring[] $h
     * @param ParagonIE_Sodium_Core_MLDSA_Ring[] $r
     * @return ParagonIE_Sodium_Core_MLDSA_Ring[]
     *
     * @throws SodiumException
     */
    public static function useHintVec($params, $h, $r)
    {
        $g2 = $params->gamma2();
        $m = 44;
        $k = $params->k();
        $v = array();
        for ($i = 0; $i < $k; ++$i) {
            $ha = $h[$i]->toArray();
            $ra = $r[$i]->toArray();
            $va = array();
            for ($j = 0; $j < 256; ++$j) {
                list($r1, $r0) = ParagonIE_Sodium_Core_MLDSA_Field::decompose($ra[$j], $g2);
                if ($ha[$j] === 1) {
                    $va[$j] = $r0 > 0 ? ($r1 + 1) % $m : ($r1 - 1 + $m) % $m;
                } else {
                    $va[$j] = $r1;
                }
            }
            $poly = new ParagonIE_Sodium_Core_MLDSA_Ring();
            foreach ($va as $index => $coefficient) {
                $poly->{'c' . $index} = $coefficient;
            }
            $v[$i] = $poly;
        }
        return $v;
    }

    /**
     * FIPS 204, Algorithm 41
     *
     * @param ParagonIE_Sodium_Core_MLDSA_Ring $w
     * @return ParagonIE_Sodium_Core_MLDSA_Ntt
     */
    public static function ntt($w)
    {
        $c = $w->toArray();
        $m = 0;
        for ($len = 128; $len >= 1; $len >>= 1) {
            for ($start = 0; $start < 256; $start += $len << 1) {
                ++$m;
                $z = self::$ZETAS[$m];
                for ($j = $start; $j < $start + $len; $j++) {
                    $jl = $j + $len;
                    $t = ParagonIE_Sodium_Core_MLDSA_Field::mul($c[$jl], $z);
                    $c[$jl] = ParagonIE_Sodium_Core_MLDSA_Field::sub($c[$j], $t);
                    $c[$j] = ParagonIE_Sodium_Core_MLDSA_Field::add($c[$j], $t);
                }
            }
        }
        $poly = new ParagonIE_Sodium_Core_MLDSA_Ntt();
        foreach ($c as $index => $coefficient) {
            $poly->{'c' . $index} = $coefficient;
        }
        return $poly;
    }

    /**
     * FIPS 204, Algorithm 42
     *
     * @param ParagonIE_Sodium_Core_MLDSA_Ntt $wh
     * @return ParagonIE_Sodium_Core_MLDSA_Ring
     */
    public static function inverseNtt($wh)
    {
        $c = $wh->toArray();
        $m = 256;
        for ($len = 1; $len < 256; $len <<= 1) {
            for ($start = 0; $start < 256; $start += $len << 1) {
                --$m;
                $z = ParagonIE_Sodium_Core_MLDSA_Field::reduceOnce(ParagonIE_Sodium_Core_MLDSA_Field::Q - self::$ZETAS[$m]);
                for ($j = $start; $j < $start + $len; $j++) {
                    $jl = $j + $len;
                    $t = $c[$j];
                    $c[$j] = ParagonIE_Sodium_Core_MLDSA_Field::add($t, $c[$jl]);
                    $c[$jl] = ParagonIE_Sodium_Core_MLDSA_Field::mul(
                        $z,
                        ParagonIE_Sodium_Core_MLDSA_Field::sub($t, $c[$jl])
                    );
                }
            }
        }
        for ($j = 0; $j < 256; ++$j) {
            $c[$j] = ParagonIE_Sodium_Core_MLDSA_Field::mul($c[$j], 8347681);
        }
        $poly = new ParagonIE_Sodium_Core_MLDSA_Ring();
        foreach ($c as $index => $coefficient) {
            $poly->{'c' . $index} = $coefficient;
        }
        return $poly;
    }

    /**
     * @param ParagonIE_Sodium_Core_MLDSA_Ring[] $rings
     * @return ParagonIE_Sodium_Core_MLDSA_Ntt[]
     */
    public static function nttVec($rings)
    {
        $v = array();
        for ($i = 0; $i < count($rings); ++$i) {
            $v[$i] = self::ntt($rings[$i]);
        }
        return $v;
    }

    /**
     * @param ParagonIE_Sodium_Core_MLDSA_Ntt[] $vectors
     * @return ParagonIE_Sodium_Core_MLDSA_Ring[]
     */
    public static function invNttVec($vectors)
    {
        $r = array();
        for ($i = 0; $i < count($vectors); ++$i) {
            $r[$i] = self::inverseNtt($vectors[$i]);
        }
        return $r;
    }

    /**
     *  FIPS 204, Algorithm 46
     *
     * @param ParagonIE_Sodium_Core_MLDSA_Ring[] $v
     * @param ParagonIE_Sodium_Core_MLDSA_Ring[] $w
     * @return ParagonIE_Sodium_Core_MLDSA_Ring[]
     */
    public static function addVector($v, $w)
    {
        $u = array();
        for ($i = 0; $i < count($v); ++$i) {
            $u[$i] = $v[$i]->add($w[$i]);
        }
        return $u;
    }

    /**
     * @param ParagonIE_Sodium_Core_MLDSA_Ring[] $v
     * @param ParagonIE_Sodium_Core_MLDSA_Ring[] $w
     * @return ParagonIE_Sodium_Core_MLDSA_Ring[]
     */
    public static function subVector($v, $w)
    {
        $u = array();
        for ($i = 0; $i < count($v); ++$i) {
            $u[$i] = $v[$i]->sub($w[$i]);
        }
        return $u;
    }

    /**
     * @param ParagonIE_Sodium_Core_MLDSA_Ntt[] $v
     * @param ParagonIE_Sodium_Core_MLDSA_Ntt[] $w
     * @return ParagonIE_Sodium_Core_MLDSA_Ntt[]
     */
    public static function subNttVector($v, $w)
    {
        $u = array();
        for ($i = 0; $i < count($v); ++$i) {
            $u[$i] = $v[$i]->sub($w[$i]);
        }
        return $u;
    }

    /**
     * @param ParagonIE_Sodium_Core_MLDSA_Ring[] $v
     * @return ParagonIE_Sodium_Core_MLDSA_Ring[]
     */
    public static function negateVector($v)
    {
        $u = array();
        for ($i = 0; $i < count($v); ++$i) {
            $u[$i] = $v[$i]->negate();
        }
        return $u;
    }

    /**
     * FIPS 204, Algorithm 47
     *
     * @param ParagonIE_Sodium_Core_MLDSA_Ntt $cHat
     * @param ParagonIE_Sodium_Core_MLDSA_Ntt[] $vHat
     * @return ParagonIE_Sodium_Core_MLDSA_Ntt[]
     */
    public static function scalarVectorNtt($cHat, $vHat)
    {
        $ca = $cHat->toArray();
        $w = array();
        $cnt = count($vHat);
        for ($i = 0; $i < $cnt; ++$i) {
            $va = $vHat[$i]->toArray();
            $r = array();
            for ($n = 0; $n < 256; ++$n) {
                $r[$n] = ParagonIE_Sodium_Core_MLDSA_Field::mul($ca[$n], $va[$n]);
            }
            $poly = new ParagonIE_Sodium_Core_MLDSA_Ntt();
            foreach ($r as $index => $coefficient) {
                $poly->{'c' . $index} = $coefficient;
            }
            $w[$i] = $poly;
        }
        return $w;
    }

    /**
     * FIPS 204, Algorithm 48
     *
     * @param ParagonIE_Sodium_Core_MLDSA_Params $params
     * @param ParagonIE_Sodium_Core_MLDSA_Ntt[][] $mHat
     * @param ParagonIE_Sodium_Core_MLDSA_Ntt[] $vHat
     * @return ParagonIE_Sodium_Core_MLDSA_Ntt[]
     */
    public static function matrixVectorNtt($params, $mHat, $vHat)
    {
        $wh = array();
        $k = $params->k();
        $l = $params->l();
        $vArrays = array();
        for ($j = 0; $j < $l; ++$j) {
            $vArrays[$j] = $vHat[$j]->toArray();
        }
        for ($i = 0; $i < $k; ++$i) {
            $acc = array_fill(0, 256, 0);
            for ($j = 0; $j < $l; ++$j) {
                $ma = $mHat[$i][$j]->toArray();
                $va = $vArrays[$j];
                for ($n = 0; $n < 256; ++$n) {
                    $acc[$n] = ParagonIE_Sodium_Core_MLDSA_Field::add(
                        $acc[$n],
                        ParagonIE_Sodium_Core_MLDSA_Field::mul($ma[$n], $va[$n])
                    );
                }
            }
            $poly = new ParagonIE_Sodium_Core_MLDSA_Ntt();
            foreach ($acc as $index => $coefficient) {
                $poly->{'c' . $index} = $coefficient;
            }
            $wh[$i] = $poly;
        }
        return $wh;
    }

    /**
     * @param ParagonIE_Sodium_Core_MLDSA_Ring[] $x
     * @return array{0: ParagonIE_Sodium_Core_MLDSA_Ring[], 1: ParagonIE_Sodium_Core_MLDSA_Ring[]}
     */
    public static function power2roundVec($x)
    {
        $k = count($x);
        $t1 = array();
        $t0 = array();
        for ($i = 0; $i < $k; ++$i) {
            list($t1[$i], $t0[$i]) = $x[$i]->power2Round();
        }
        return array($t1, $t0);
    }

    /**
     * @param array $symmetric
     * @return array
     */
    public static function ringVectorFromSymmetric($symmetric)
    {
        $v = array();
        $len = count($symmetric);
        for ($i = 0; $i < $len; ++$i) {
            $v[$i] = ParagonIE_Sodium_Core_MLDSA_Ring::fromSymmetric($symmetric[$i]);
        }
        return $v;
    }

    /**
     * @param ParagonIE_Sodium_Core_MLDSA_Params $params
     * @param ParagonIE_Sodium_Core_MLDSA_Ring[] $vector
     * @return ParagonIE_Sodium_Core_MLDSA_Ring[]
     * @throws SodiumException
     */
    public static function highBitsVec($params, $vector)
    {
        $r1 = array();
        for ($i = 0; $i < count($vector); ++$i) {
            $r1[$i] = $vector[$i]->highBits($params);
        }
        return $r1;
    }

    /**
     * @param ParagonIE_Sodium_Core_MLDSA_Params $params
     * @param ParagonIE_Sodium_Core_MLDSA_Ring[] $vector
     * @return ParagonIE_Sodium_Core_MLDSA_Ring[]
     * @throws SodiumException
     */
    public static function lowBitsVec($params, $vector)
    {
        $r1 = array();
        for ($i = 0; $i < count($vector); ++$i) {
            $r1[$i] = $vector[$i]->lowBits($params);
        }
        return $r1;
    }

    /**
     * @param array $vector
     * @return int
     */
    public static function infinityNormVec($vector)
    {
        $norm = 0;
        for ($i = 0; $i < count($vector); ++$i) {
            $next = $vector[$i]->infinityNorm();
            $norm ^= $norm - $next >> 31 & ($norm ^ $next);
        }
        return (int) $norm;
    }

    /**
     * @param ParagonIE_Sodium_Core_MLDSA_Params $params
     * @param ParagonIE_Sodium_Core_MLDSA_Ring[] $z
     * @param ParagonIE_Sodium_Core_MLDSA_Ring[] $r
     * @return ParagonIE_Sodium_Core_MLDSA_Ring[]|null
     *
     * @throws SodiumException
     */
    public static function makeHintVec($params, $z, $r)
    {
        $hints = array();
        $weight = 0;
        $k = $params->k();
        $gamma2 = $params->gamma2();
        for ($i = 0; $i < $k; ++$i) {
            $za = $z[$i]->toArray();
            $ra = $r[$i]->toArray();
            $ha = array();
            for ($j = 0; $j < 256; ++$j) {
                $ha[$j] = ParagonIE_Sodium_Core_MLDSA_Field::makeHint($za[$j], $ra[$j], $gamma2);
                $weight += $ha[$j];
            }
            $poly = new ParagonIE_Sodium_Core_MLDSA_Ring();
            foreach ($ha as $index => $coefficient) {
                $poly->{'c' . $index} = $coefficient;
            }
            $hints[$i] = $poly;
        }
        if ($weight > $params->omega()) {
            return null;
        }
        return $hints;
    }

    /**
     * @param int $c
     * @param ParagonIE_Sodium_Core_MLDSA_Ring[] $vector
     * @return ParagonIE_Sodium_Core_MLDSA_Ring[]
     */
    public static function scalarVectorMul($c, $vector)
    {
        $w = array();
        $len = count($vector);
        for ($i = 0; $i < $len; ++$i) {
            $w[$i] = $vector[$i]->scalarMul($c);
        }
        return $w;
    }

    /**
     * Get M' from M and ctx
     *
     * @param string $message
     * @param string $ctx
     * @return string
     */
    public static function prepareMessage($message, $ctx)
    {
        if (ParagonIE_Sodium_Core_Util::strlen($ctx) > 255) {
            throw new SodiumException('Context cannot be more than 255 characters');
        }
        return "\x00" . ParagonIE_Sodium_Core_Util::intToChr(ParagonIE_Sodium_Core_Util::strlen($ctx)) . $ctx . $message;
    }
}
