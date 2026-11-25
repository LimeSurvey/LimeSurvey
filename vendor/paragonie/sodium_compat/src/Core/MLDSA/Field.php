<?php

if (class_exists('ParagonIE_Sodium_Core_MLDSA_Field', false)) {
    return;
}

/**
 * @internal
 * @psalm-suppress PossiblyUnusedMethod
 */
final class ParagonIE_Sodium_Core_MLDSA_Field
{
    const Q = 8380417;
    const D = 13;

    /**
     * @param int $x
     * @return int
     */
    public static function newFromSymmetric($x)
    {
        $mask = $x >> 31;
        // -1 if negative, 0 if non-negative
        return $x + ($mask & self::Q);
    }

    /**
     * @param int $r
     * @return int
     */
    public static function reduceOnce($r)
    {
        $r1 = $r - self::Q;
        $correction = self::Q - $r - 1 >> 31 & 1;
        $mask = -$correction;
        return $r ^ $mask & ($r ^ $r1);
    }

    /**
     * @param int $a
     * @param int $b
     * @return int
     */
    public static function add($a, $b)
    {
        return self::reduceOnce($a + $b);
    }

    /**
     * @param int $a
     * @param int $b
     * @return int
     */
    public static function sub($a, $b)
    {
        return self::reduceOnce($a - $b + self::Q);
    }

    /**
     * @param int $a
     * @return int
     */
    public static function neg($a)
    {
        return self::reduceOnce(self::Q - $a);
    }

    /**
     * Fold a nonnegative signed-32-bit value modulo 2^23 - 8191.
     *
     * @param int $x
     * @return int
     */
    public static function fold($x)
    {
        $r = ($x & 8388607) + ($x >> 23) * 8191;
        return self::reduceOnce($r);
    }

    /**
     * Multiply canonical field elements using three bounded byte products.
     * Each product is at most (Q - 1) * 255, below signed 32-bit overflow.
     * Reducing between shifts keeps every intermediate below 2^31.
     *
     * @param int $a
     * @param int $b
     * @return int
     */
    public static function mul($a, $b)
    {
        $r = self::fold($a * ($b >> 16));
        $r = self::reduceOnce(self::fold($r << 8) + self::fold($a * (($b >> 8) & 255)));
        return self::reduceOnce(self::fold($r << 8) + self::fold($a * ($b & 255)));
    }

    /**
     * Fixed-length binary division, used only by internal arithmetic tests.
     *
     * @throws SodiumException
     *
     * @param int $numerator
     * @param int $denominator
     * @return array
     * @psalm-suppress PossiblyUnusedMethod Covered by internal primitive tests.
     */
    public static function div($numerator, $denominator)
    {
        if ($denominator !== 95232 && $denominator !== 190464) {
            throw new SodiumException('Unsupported ML-DSA-44 divisor');
        }
        $quotient = 0;
        $remainder = 0;
        for ($i = 23; $i >= 0; --$i) {
            $remainder = ($remainder << 1) | (($numerator >> $i) & 1);
            $difference = $remainder - $denominator;
            $mask = $difference >> 31;
            $remainder = $difference + ($denominator & $mask);
            $quotient = ($quotient << 1) | ((~$mask) & 1);
        }
        return array($quotient, $remainder);
    }

    /**
     * FIPS 204 Algorithm 35
     *
     * @param int $a
     * @return array
     */
    public static function power2round($a)
    {
        $r0 = $a & (1 << self::D) - 1;
        $threshold = 1 << self::D - 1;
        $mask = $threshold - $r0 >> 31;
        $r0 -= $mask & 1 << self::D;
        $r1 = $a - $r0 >> self::D;
        return array($r1, $r0);
    }

    /**
     * FIPS 204, Algorithm 36
     *
     * @throws SodiumException
     *
     * @param int $a
     * @param int $gamma2
     * @psalm-suppress UnusedParam The shared FIPS interface includes gamma2; ML-DSA-44 fixes it at 95232.
     * @return array
     */
    public static function decompose($a, $gamma2)
    {
        // ML-DSA-44: gamma2 = (q - 1) / 88. All products fit signed 32 bits.
        $a1 = ($a + 127) >> 7;
        $a1 = ($a1 * 11275 + 8388608) >> 24;
        $a1 ^= ((43 - $a1) >> 31) & $a1;
        $a0 = $a - $a1 * 190464;
        $a0 -= ((4190208 - $a0) >> 31) & 8380417;
        return array($a1, $a0);
    }

    /**
     * FIPS 204, Algorithm 37
     *
     * @throws SodiumException
     *
     * @param int $a
     * @param int $gamma2
     * @return int
     */
    public static function highBits($a, $gamma2)
    {
        list($r1) = self::decompose($a, $gamma2);
        return $r1;
    }

    /**
     * FIPS 204, Algorithm 38
     *
     * @throws SodiumException
     *
     * @param int $a
     * @param int $gamma2
     * @return int
     */
    public static function lowBits($a, $gamma2)
    {
        list(, $r0) = self::decompose($a, $gamma2);
        return $r0;
    }

    /**
     * @param int $a
     * @return int
     */
    public static function infinityNorm($a)
    {
        $left = $a;
        $right = self::reduceOnce(self::Q - $a);
        return $right ^ $left - $right >> 31 & ($left ^ $right);
    }

    /**
     * Correct a bounded quotient and remainder without branches.
     *
     * @param int $quo
     * @param int $r
     * @param int $d
     * @return array{0: int, 1: int}
     * @psalm-suppress PossiblyUnusedMethod Covered by internal arithmetic tests.
     */
    public static function barrettCorrectDiv($quo, $r, $d)
    {
        $newR = $r - $d;
        // $correction = ($r >= $d) ? 1 : 0;
        $correction = $d - $r - 1 >> 31 & 1;
        $mask = -$correction;
        $quo += $mask & 1;
        $r = $r ^ $mask & ($newR ^ $r);
        return array($quo, $r);
    }

    /**
     * @param int $a
     * @return int
     */
    public static function symmetric($a)
    {
        static $q2 = 4190208;
        // if ($a > $q2) {
        //     $a -= $q;
        // }
        $mask = $q2 - $a >> 31;
        return self::reduceonce($a - (self::Q & $mask));
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
     */
    public static function makeHint($z, $r, $gamma2)
    {
        $r1 = self::highBits($r, $gamma2);
        $v1 = self::highBits(self::add($r, self::newFromSymmetric($z)), $gamma2);
        $difference = $r1 ^ $v1;
        return ($difference | -$difference) >> 31 & 1;
    }
}
