<?php

declare(strict_types=1);

namespace PhpOffice\WMF\Reader\EMFPlus;

use PhpOffice\WMF\Exception\WMFException;

/**
 * Sequential reader of the binary data of EMF+ records & objects (little-endian)
 */
class Buffer
{
    /**
     * @var string
     */
    protected $data;
    /**
     * @var int
     */
    protected $position = 0;

    public function __construct(string $data)
    {
        $this->data = $data;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getRemaining(): int
    {
        return strlen($this->data) - $this->position;
    }

    public function skip(int $length): self
    {
        $this->read($length);

        return $this;
    }

    /**
     * Skips the padding up to a multiple of 4 bytes (from the start of the data)
     */
    public function align(): self
    {
        $this->position = min(strlen($this->data), (int) (ceil($this->position / 4) * 4));

        return $this;
    }

    public function read(int $length): string
    {
        if ($length < 0 || $this->position + $length > strlen($this->data)) {
            throw new WMFException('Reader : Invalid file : truncated EMF+ data');
        }
        $value = (string) substr($this->data, $this->position, $length);
        $this->position += $length;

        return $value;
    }

    public function readUInt8(): int
    {
        return ord($this->read(1));
    }

    public function readUInt16(): int
    {
        return (int) unpack('v', $this->read(2))[1];
    }

    public function readInt16(): int
    {
        $value = $this->readUInt16();

        return $value >= 0x8000 ? $value - 0x10000 : $value;
    }

    public function readUInt32(): int
    {
        return (int) unpack('V', $this->read(4))[1];
    }

    public function readInt32(): int
    {
        $value = $this->readUInt32();

        return $value >= 0x80000000 ? $value - 0x100000000 : $value;
    }

    public function readFloat(): float
    {
        $value = (float) unpack('g', $this->read(4))[1];

        return is_finite($value) ? $value : 0.0;
    }

    /**
     * Reads an ARGB color : [r, g, b, a]
     *
     * @return array<int>
     */
    public function readColor(): array
    {
        return self::toColor($this->readUInt32());
    }

    /**
     * Converts an ARGB value to a color [r, g, b, a]
     *
     * @return array<int>
     */
    public static function toColor(int $argb): array
    {
        return [($argb >> 16) & 0xFF, ($argb >> 8) & 0xFF, $argb & 0xFF, ($argb >> 24) & 0xFF];
    }

    /**
     * Reads a matrix [m11, m12, m21, m22, dx, dy]
     *
     * @return array<float>
     */
    public function readMatrix(): array
    {
        $matrix = [];
        for ($i = 0; $i < 6; ++$i) {
            $matrix[] = $this->readFloat();
        }

        return $matrix;
    }

    /**
     * Reads a rectangle [x, y, width, height] : 16-bit integers if compressed, else floats
     *
     * @return array<float>
     */
    public function readRect(bool $isCompressed = false): array
    {
        if ($isCompressed) {
            return [$this->readInt16(), $this->readInt16(), $this->readInt16(), $this->readInt16()];
        }

        return [$this->readFloat(), $this->readFloat(), $this->readFloat(), $this->readFloat()];
    }

    /**
     * Reads points : floats, 16-bit integers (compressed) or relative points (EmfPlusPointR)
     *
     * @return array<array<float>>
     */
    public function readPoints(int $count, bool $isCompressed = false, bool $isRelative = false): array
    {
        if ($count < 0 || $count > $this->getRemaining()) {
            throw new WMFException('Reader : Invalid file : truncated EMF+ data');
        }
        $points = [];
        $x = $y = 0;
        for ($i = 0; $i < $count; ++$i) {
            if ($isRelative) {
                // Each coordinate is relative to the previous point
                $x += $this->readRelativeInteger();
                $y += $this->readRelativeInteger();
                $points[] = [$x, $y];
            } elseif ($isCompressed) {
                $points[] = [$this->readInt16(), $this->readInt16()];
            } else {
                $points[] = [$this->readFloat(), $this->readFloat()];
            }
        }

        return $points;
    }

    /**
     * Reads an EmfPlusInteger7 (1 byte) or an EmfPlusInteger15 (2 bytes, big-endian)
     */
    protected function readRelativeInteger(): int
    {
        $first = $this->readUInt8();
        if ($first & 0x80) {
            $value = (($first & 0x7F) << 8) | $this->readUInt8();

            return $value >= 0x4000 ? $value - 0x8000 : $value;
        }

        return $first >= 0x40 ? $first - 0x80 : $first;
    }

    /**
     * Reads a UTF-16LE string of $length characters
     */
    public function readString(int $length): string
    {
        return $this->read(2 * max(0, $length));
    }
}
