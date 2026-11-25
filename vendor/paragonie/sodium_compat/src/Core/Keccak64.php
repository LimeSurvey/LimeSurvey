<?php

if (class_exists('ParagonIE_Sodium_Core_Keccak64', false)) {
    return;
}

/**
 * @internal
 * @psalm-suppress ClassMustBeFinal Extended by the platform-selected Keccak facade.
 */
class ParagonIE_Sodium_Core_Keccak64 extends ParagonIE_Sodium_Core_Util
{
    const ROUNDS = 24;
    /** @var int[] */
    public static $RC = array(
        1,
        32898,
        -9223372036854742902,
        -9223372034707259392,
        32907,
        2147483649,
        -9223372034707259263,
        -9223372036854743031,
        138,
        136,
        2147516425,
        2147483658,
        2147516555,
        -9223372036854775669,
        -9223372036854742903,
        -9223372036854743037,
        -9223372036854743038,
        -9223372036854775680,
        32778,
        -9223372034707292150,
        -9223372034707259263,
        -9223372036854742912,
        2147483649,
        -9223372034707259384
    );
    /** @var int[] */
    private $state;
    /** @var string */
    private $buffer = '';
    /** @var int */
    private $rate;
    /** @var int */
    private $suffix;
    /** @var bool */
    private $squeezing = false;
    /** @var string */
    private $squeezeBuffer = '';

    /**
     * @param int $rate
     * @param int $suffix
     */
    private function __construct($rate, $suffix = 0x1f)
    {
        $this->rate = $rate;
        $this->suffix = $suffix;
        $this->state = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
    }

    /**
     * SHAKE128: rate=168, capacity=256
     *
     * @return ParagonIE_Sodium_Core_Keccak64
     * @psalm-suppress PossiblyUnusedMethod Selected by the 64-bit Keccak facade.
     */
    public static function shake128()
    {
        return new self(168, 0x1f);
    }

    /**
     * SHAKE256: rate=136, capacity=512
     *
     * @return ParagonIE_Sodium_Core_Keccak64
     * @psalm-suppress PossiblyUnusedMethod Selected by the 64-bit Keccak facade.
     */
    public static function shake256()
    {
        return new self(136, 0x1f);
    }

    /**
     * @param string $message
     * @return string
     * @psalm-suppress PossiblyUnusedMethod Selected by the 64-bit Keccak facade.
     */
    public static function sha3_256($message)
    {
        $state = new self(136, 0x6);
        return $state->absorb($message)->squeeze(32);
    }

    /**
     * @param string $message
     * @return string
     * @psalm-suppress PossiblyUnusedMethod Selected by the 64-bit Keccak facade.
     */
    public static function sha3_512($message)
    {
        $state = new self(72, 0x6);
        return $state->absorb($message)->squeeze(64);
    }

    /**
     * @param string $data
     * @return ParagonIE_Sodium_Core_Keccak64
     */
    public function absorb($data)
    {
        if ($this->squeezing) {
            throw new SodiumException("Cannot absorb after squeezing");
        }
        $length = ParagonIE_Sodium_Core_Util::strlen($data);
        $offset = 0;
        if ($this->buffer !== '') {
            $take = min($this->rate - ParagonIE_Sodium_Core_Util::strlen($this->buffer), $length);
            $this->buffer .= ParagonIE_Sodium_Core_Util::substr($data, 0, $take);
            if (ParagonIE_Sodium_Core_Util::strlen($this->buffer) < $this->rate) {
                return $this;
            }
            $this->absorbBlock($this->buffer);
            $this->buffer = '';
            $offset = $take;
        }
        // Copy each rate block once, rather than the entire remaining input.
        while ($length - $offset >= $this->rate) {
            $this->absorbBlock(ParagonIE_Sodium_Core_Util::substr($data, $offset, $this->rate));
            $offset += $this->rate;
        }
        $this->buffer = ParagonIE_Sodium_Core_Util::substr($data, $offset);
        return $this;
    }

    /**
     * @param int $length
     * @return string
     */
    public function squeeze($length)
    {
        if ($length < 0) {
            throw new SodiumException("Negative output length");
        }
        if (!$this->squeezing) {
            $this->finalize();
        }
        $output = '';
        if ($this->squeezeBuffer !== '') {
            $take = min($length, ParagonIE_Sodium_Core_Util::strlen($this->squeezeBuffer));
            $output .= ParagonIE_Sodium_Core_Util::substr($this->squeezeBuffer, 0, $take);
            $this->squeezeBuffer = ParagonIE_Sodium_Core_Util::substr($this->squeezeBuffer, $take);
            $length -= $take;
        }
        while ($length > 0) {
            $block = $this->squeezeBlock();
            $take = min($length, $this->rate);
            $output .= ParagonIE_Sodium_Core_Util::substr($block, 0, $take);
            if ($take < $this->rate) {
                $this->squeezeBuffer = ParagonIE_Sodium_Core_Util::substr($block, $take);
            }
            $length -= $take;
        }
        return $output;
    }

    /**
     * @return void
     */
    private function finalize()
    {
        $padLen = ParagonIE_Sodium_Core_Util::strlen($this->buffer);
        $pad = str_pad($this->buffer, $this->rate, "\x00");
        $pad[$padLen] = self::intToChr(self::chrToInt($pad[$padLen]) | $this->suffix);
        $last = $this->rate - 1;
        $pad[$last] = self::intToChr(self::chrToInt($pad[$last]) | 0x80);
        $this->absorbBlock($pad);
        $this->squeezing = true;
        $this->buffer = '';
    }

    /**
     * @param string $block
     * @return void
     */
    private function absorbBlock($block)
    {
        $words = $this->rate >> 3;
        for ($i = 0; $i < $words; $i++) {
            /** @var array{1: int, 2: int} $words64 */
            $words64 = unpack('V2', ParagonIE_Sodium_Core_Util::substr($block, $i << 3, 8));
            $this->state[$i] ^= $words64[1] | ($words64[2] << 32);
        }
        $this->permute();
    }

    /**
     * @return string
     */
    private function squeezeBlock()
    {
        $block = '';
        $words = $this->rate >> 3;
        for ($i = 0; $i < $words; $i++) {
            /** @var string $packed */
            $packed = pack('V2', $this->state[$i] & 0xffffffff, ($this->state[$i] >> 32) & 0xffffffff);
            $block .= $packed;
        }
        $this->permute();
        return $block;
    }

    /**
     * @return void
     */
    private function permute()
    {
        /** @psalm-suppress UnsupportedPropertyReferenceUsage The permutation updates these lanes in place. */
        $st =& $this->state;
        for ($round = 0; $round < self::ROUNDS; $round++) {
            // θ — inline rotl64($c, 1) to avoid 5 calls/round
            $c0 = $st[0] ^ $st[5] ^ $st[10] ^ $st[15] ^ $st[20];
            $c1 = $st[1] ^ $st[6] ^ $st[11] ^ $st[16] ^ $st[21];
            $c2 = $st[2] ^ $st[7] ^ $st[12] ^ $st[17] ^ $st[22];
            $c3 = $st[3] ^ $st[8] ^ $st[13] ^ $st[18] ^ $st[23];
            $c4 = $st[4] ^ $st[9] ^ $st[14] ^ $st[19] ^ $st[24];
            $d0 = $c4 ^ ($c1 << 1 | $c1 >> 63 & 1);
            $d1 = $c0 ^ ($c2 << 1 | $c2 >> 63 & 1);
            $d2 = $c1 ^ ($c3 << 1 | $c3 >> 63 & 1);
            $d3 = $c2 ^ ($c4 << 1 | $c4 >> 63 & 1);
            $d4 = $c3 ^ ($c0 << 1 | $c0 >> 63 & 1);
            $st[0] ^= $d0;
            $st[1] ^= $d1;
            $st[2] ^= $d2;
            $st[3] ^= $d3;
            $st[4] ^= $d4;
            $st[5] ^= $d0;
            $st[6] ^= $d1;
            $st[7] ^= $d2;
            $st[8] ^= $d3;
            $st[9] ^= $d4;
            $st[10] ^= $d0;
            $st[11] ^= $d1;
            $st[12] ^= $d2;
            $st[13] ^= $d3;
            $st[14] ^= $d4;
            $st[15] ^= $d0;
            $st[16] ^= $d1;
            $st[17] ^= $d2;
            $st[18] ^= $d3;
            $st[19] ^= $d4;
            $st[20] ^= $d0;
            $st[21] ^= $d1;
            $st[22] ^= $d2;
            $st[23] ^= $d3;
            $st[24] ^= $d4;
            // ρ and π — fully unrolled, no array lookups
            $current = $st[1];
            $temp = $st[10];
            $st[10] = $current << 1 | $current >> 63 & 0x1;
            $current = $temp;
            $temp = $st[7];
            $st[7] = $current << 3 | $current >> 61 & 0x7;
            $current = $temp;
            $temp = $st[11];
            $st[11] = $current << 6 | $current >> 58 & 0x3f;
            $current = $temp;
            $temp = $st[17];
            $st[17] = $current << 10 | $current >> 54 & 0x3ff;
            $current = $temp;
            $temp = $st[18];
            $st[18] = $current << 15 | $current >> 49 & 0x7fff;
            $current = $temp;
            $temp = $st[3];
            $st[3] = $current << 21 | $current >> 43 & 0x1fffff;
            $current = $temp;
            $temp = $st[5];
            $st[5] = $current << 28 | $current >> 36 & 0xfffffff;
            $current = $temp;
            $temp = $st[16];
            $st[16] = $current << 36 | $current >> 28 & 0xfffffffff;
            $current = $temp;
            $temp = $st[8];
            $st[8] = $current << 45 | $current >> 19 & 0x1fffffffffff;
            $current = $temp;
            $temp = $st[21];
            $st[21] = $current << 55 | $current >> 9 & 0x7fffffffffffff;
            $current = $temp;
            $temp = $st[24];
            $st[24] = $current << 2 | $current >> 62 & 0x3;
            $current = $temp;
            $temp = $st[4];
            $st[4] = $current << 14 | $current >> 50 & 0x3fff;
            $current = $temp;
            $temp = $st[15];
            $st[15] = $current << 27 | $current >> 37 & 0x7ffffff;
            $current = $temp;
            $temp = $st[23];
            $st[23] = $current << 41 | $current >> 23 & 0x1ffffffffff;
            $current = $temp;
            $temp = $st[19];
            $st[19] = $current << 56 | $current >> 8 & 0xffffffffffffff;
            $current = $temp;
            $temp = $st[13];
            $st[13] = $current << 8 | $current >> 56 & 0xff;
            $current = $temp;
            $temp = $st[12];
            $st[12] = $current << 25 | $current >> 39 & 0x1ffffff;
            $current = $temp;
            $temp = $st[2];
            $st[2] = $current << 43 | $current >> 21 & 0x7ffffffffff;
            $current = $temp;
            $temp = $st[20];
            $st[20] = $current << 62 | $current >> 2 & 0x3fffffffffffffff;
            $current = $temp;
            $temp = $st[14];
            $st[14] = $current << 18 | $current >> 46 & 0x3ffff;
            $current = $temp;
            $temp = $st[22];
            $st[22] = $current << 39 | $current >> 25 & 0x7fffffffff;
            $current = $temp;
            $temp = $st[9];
            $st[9] = $current << 61 | $current >> 3 & 0x1fffffffffffffff;
            $current = $temp;
            $temp = $st[6];
            $st[6] = $current << 20 | $current >> 44 & 0xfffff;
            $current = $temp;
            $st[1] = $current << 44 | $current >> 20 & 0xfffffffffff;
            // χ
            for ($y = 0; $y < 25; $y += 5) {
                $t0 = $st[$y];
                $t1 = $st[$y + 1];
                $t2 = $st[$y + 2];
                $t3 = $st[$y + 3];
                $t4 = $st[$y + 4];
                $st[$y] = $t0 ^ ~$t1 & $t2;
                $st[$y + 1] = $t1 ^ ~$t2 & $t3;
                $st[$y + 2] = $t2 ^ ~$t3 & $t4;
                $st[$y + 3] = $t3 ^ ~$t4 & $t0;
                $st[$y + 4] = $t4 ^ ~$t0 & $t1;
            }
            // ι
            $st[0] ^= self::$RC[$round];
        }
    }
}
