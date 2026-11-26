<?php

if (class_exists('ParagonIE_Sodium_Core32_Keccak', false)) {
    return;
}

/**
 * @internal
 *
 * We need this for SHAKE128 and SHAKE256t for PQ Algorithms
 */
class ParagonIE_Sodium_Core32_Keccak extends ParagonIE_Sodium_Core_Util
{
    /** @var array<int, array<int, int>> */
    private $state;
    /** @var int */
    private $rate;
    /** @var int */
    private $suffix;
    /** @var string */
    private $buffer = '';
    /** @var bool */
    private $squeezing = false;
    /** @var string */
    private $squeezeBuffer = '';
    /** @var int[] */
    private static $piln = array(10, 7, 11, 17, 18, 3, 5, 16, 8, 21, 24, 4, 15, 23, 19, 13, 12, 2, 20, 14, 22, 9, 6, 1);
    /** @var int[] */
    private static $rotc = array(
        1,
        3,
        6,
        10,
        15,
        21,
        28,
        36,
        45,
        55,
        2,
        14,
        27,
        41,
        56,
        8,
        25,
        43,
        62,
        18,
        39,
        61,
        20,
        44
    );
    /** @var array<int, array<int, int>> */
    private static $rc = array(
        array(1, 0, 0, 0),
        array(32898, 0, 0, 0),
        array(32906, 0, 0, 32768),
        array(32768, 32768, 0, 32768),
        array(32907, 0, 0, 0),
        array(1, 32768, 0, 0),
        array(32897, 32768, 0, 32768),
        array(32777, 0, 0, 32768),
        array(138, 0, 0, 0),
        array(136, 0, 0, 0),
        array(32777, 32768, 0, 0),
        array(10, 32768, 0, 0),
        array(32907, 32768, 0, 0),
        array(139, 0, 0, 32768),
        array(32905, 0, 0, 32768),
        array(32771, 0, 0, 32768),
        array(32770, 0, 0, 32768),
        array(128, 0, 0, 32768),
        array(32778, 0, 0, 0),
        array(10, 32768, 0, 32768),
        array(32897, 32768, 0, 32768),
        array(32896, 0, 0, 32768),
        array(1, 32768, 0, 0),
        array(32776, 32768, 0, 32768)
    );

    /**
     * @param int $rate
     * @param int $suffix
     */
    private function __construct($rate, $suffix)
    {
        $this->rate = $rate;
        $this->suffix = $suffix;
        $this->state = array_fill(0, 25, array(0, 0, 0, 0));
    }

    /**
     * @return ParagonIE_Sodium_Core32_Keccak
     */
    public static function shake128()
    {
        return new self(168, 31);
    }

    /**
     * @return ParagonIE_Sodium_Core32_Keccak
     */
    public static function shake256()
    {
        return new self(136, 31);
    }

    /**
     * @param string $message
     * @return string
     */
    public static function sha3_256($message)
    {
        $state = new self(136, 6);
        $state->absorb($message);
        return $state->squeeze(32);
    }

    /**
     * @param string $message
     * @return string
     */
    public static function sha3_512($message)
    {
        $state = new self(72, 6);
        $state->absorb($message);
        return $state->squeeze(64);
    }

    /**
     * @param string $data
     * @return ParagonIE_Sodium_Core32_Keccak
     */
    public function absorb($data)
    {
        if ($this->squeezing) {
            throw new SodiumException('Cannot absorb after squeezing');
        }
        $length = self::strlen($data);
        $offset = 0;
        if ($this->buffer !== '') {
            $take = min($this->rate - self::strlen($this->buffer), $length);
            $this->buffer .= self::substr($data, 0, $take);
            if (self::strlen($this->buffer) < $this->rate) {
                return $this;
            }
            $this->absorbBlock($this->buffer);
            $this->buffer = '';
            $offset = $take;
        }
        // Copy each rate block once, rather than the entire remaining input.
        while ($length - $offset >= $this->rate) {
            $this->absorbBlock(self::substr($data, $offset, $this->rate));
            $offset += $this->rate;
        }
        $this->buffer = self::substr($data, $offset);
        return $this;
    }

    /**
     * @param int $length
     * @return string
     */
    public function squeeze($length)
    {
        if ($length < 0) {
            throw new SodiumException('Negative output length');
        }
        if (!$this->squeezing) {
            $offset = self::strlen($this->buffer);
            $block = str_pad($this->buffer, $this->rate, "\0");
            $block[$offset] = self::intToChr(self::chrToInt($block[$offset]) | $this->suffix);
            $block[$this->rate - 1] = self::intToChr(self::chrToInt($block[$this->rate - 1]) | 128);
            $this->absorbBlock($block);
            $this->buffer = '';
            $this->squeezing = true;
        }
        $output = '';
        while ($length > 0) {
            if ($this->squeezeBuffer === '') {
                $block = '';
                for ($i = 0; $i < ($this->rate >> 3); ++$i) {
                    $lane = $this->state[$i];
                    /** @var string $packed */
                    $packed = pack('v4', $lane[0], $lane[1], $lane[2], $lane[3]);
                    $block .= $packed;
                }
                $this->squeezeBuffer = $block;
                $this->permute();
            }
            $take = min($length, self::strlen($this->squeezeBuffer));
            $output .= self::substr($this->squeezeBuffer, 0, $take);
            $this->squeezeBuffer = self::substr($this->squeezeBuffer, $take);
            $length -= $take;
        }
        return $output;
    }

    /**
     * @param string $block
     * @return void
     */
    private function absorbBlock($block)
    {
        /** @var int[] $unpacked */
        $unpacked = unpack('v*', $block);
        $words = array_values($unpacked);
        $count = $this->rate >> 1;
        for ($i = 0; $i < $count; ++$i) {
            $this->state[$i >> 2][$i & 3] ^= $words[$i];
        }
        $this->permute();
    }

    /**
     * @param int[] $lane
     * @param int $bits
     * @return int[]
     */
    private static function rotate($lane, $bits)
    {
        $word = $bits >> 4;
        $shift = $bits & 15;
        $out = array();
        for ($j = 0; $j < 4; ++$j) {
            $out[$j] = (($lane[($j - $word) & 3] << $shift) & 65535)
                | ($lane[($j - $word - 1) & 3] >> (16 - $shift));
        }
        return $out;
    }

    /**
     * @return void
     */
    private function permute()
    {
        $st = $this->state;
        for ($round = 0; $round < 24; ++$round) {
            $columns = array();
            for ($x = 0; $x < 5; ++$x) {
                $column = array();
                for ($j = 0; $j < 4; ++$j) {
                    $column[$j] = $st[$x][$j] ^ $st[$x + 5][$j] ^ $st[$x + 10][$j]
                        ^ $st[$x + 15][$j] ^ $st[$x + 20][$j];
                }
                $columns[$x] = $column;
            }
            for ($x = 0; $x < 5; ++$x) {
                $rotated = self::rotate($columns[($x + 1) % 5], 1);
                for ($j = 0; $j < 4; ++$j) {
                    $d = $columns[($x + 4) % 5][$j] ^ $rotated[$j];
                    for ($y = 0; $y < 25; $y += 5) {
                        $st[$y + $x][$j] ^= $d;
                    }
                }
            }
            $current = $st[1];
            for ($i = 0; $i < 24; ++$i) {
                $index = self::$piln[$i];
                $temp = $st[$index];
                $st[$index] = self::rotate($current, self::$rotc[$i]);
                $current = $temp;
            }
            for ($y = 0; $y < 25; $y += 5) {
                $row = array($st[$y], $st[$y + 1], $st[$y + 2], $st[$y + 3], $st[$y + 4]);
                for ($x = 0; $x < 5; ++$x) {
                    for ($j = 0; $j < 4; ++$j) {
                        $st[$y + $x][$j] = $row[$x][$j] ^ ((~$row[($x + 1) % 5][$j]) & $row[($x + 2) % 5][$j]);
                    }
                }
            }
            for ($j = 0; $j < 4; ++$j) {
                $st[0][$j] ^= self::$rc[$round][$j];
            }
        }
        $this->state = $st;
    }
}
