<?php

if (class_exists('ParagonIE_Sodium_Core_MLKem_FieldElement', false)) {
    return;
}

/**
 * @internal
 */
abstract class ParagonIE_Sodium_Core_MLKem_FieldElement
{
    const Q = 3329;
    const BARRETT_MUL = 5039;
    // floor(2^{24} / Q)
    const BARRETT_SHIFT = 24;

    /** @psalm-suppress UnusedConstructor This class exposes static arithmetic only. */
    private function __construct()
    {
    }

    /**
     * Reduce $a from [0, 2q) to [0, q)
     *
     * @param int $a
     * @return int
     */
    public static function reduceOnce($a)
    {
        $x = $a - self::Q;
        return $x + ($x >> 31 & self::Q);
    }

    /**
     * Barrett reduction (avoid modulo operators))
     *
     * @param int $a
     * @return int
     */
    public static function reduce($a)
    {
        $quotient = (($a >> 16) * self::BARRETT_MUL + ((($a & 65535) * self::BARRETT_MUL) >> 16)) >> 8;
        return self::reduceOnce($a - $quotient * self::Q);
    }

    /**
     * @throws SodiumException
     *
     * @param int $a
     * @return int
     */
    public static function checkReduced($a)
    {
        $lt = 1 & $a >> 31;
        $gt = 1 & self::Q - $a - 1 >> 31;
        if (($lt | $gt) !== 0) {
            throw new SodiumException('Unreduced field element: ' . $a);
        }
        return $a;
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
     * @param int $b
     * @return int
     */
    public static function mul($a, $b)
    {
        return self::reduce($a * $b);
    }

    /**
     * @param int $a
     * @param int $b
     * @param int $c
     * @return int
     */
    public static function mulSub($a, $b, $c)
    {
        return self::reduce($a * ($b - $c + self::Q));
    }

    /**
     * @param int $a
     * @param int $b
     * @param int $c
     * @param int $d
     * @return int
     */
    public static function addMul($a, $b, $c, $d)
    {
        return self::reduce($a * $b + $c * $d);
    }

    /**
     * FIPS 203, Definition 4.7
     *
     * @param int $x
     * @param int $d
     * @return int
     */
    public static function compress($x, $d)
    {
        $dividend = $x << $d;
        $quotient = (($dividend >> 16) * self::BARRETT_MUL + ((($dividend & 65535) * self::BARRETT_MUL) >> 16)) >> 8;
        $remainder = $dividend - $quotient * self::Q;
        // Rounding without branches
        $quotient += 1664 - $remainder >> 31 & 1;
        $quotient += 4993 - $remainder >> 31 & 1;
        return $quotient & (1 << $d) - 1;
    }

    /**
     * FIPS 203, Definition 4.8
     *
     * @param int $y
     * @param int $d
     * @return int
     */
    public static function decompress($y, $d)
    {
        $dividend = $y * self::Q;
        $quotient = $dividend >> $d;
        $quotient += $dividend >> $d - 1 & 1;
        return $quotient;
    }
}
