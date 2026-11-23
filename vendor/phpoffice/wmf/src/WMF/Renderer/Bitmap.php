<?php

declare(strict_types=1);

namespace PhpOffice\WMF\Renderer;

use PhpOffice\WMF\Exception\WMFException;

/**
 * Decoding of the bitmaps of metafiles
 *
 * A decoded bitmap is an array `['width' => int, 'height' => int, 'pixels' => array<array<int>>]`,
 * where pixels are top-down rows of GD colors (0xRRGGBB)
 *
 * Methods return null for bitmaps which are not supported, and throw an exception for truncated bitmaps
 */
class Bitmap
{
    /**
     * Usage of the color table of a bitmap : it contains indexes in the palette of the file (else, it contains colors)
     */
    public const DIB_PAL_COLORS = 1;
    /**
     * Transparent pixel (alpha channel of GD)
     */
    public const TRANSPARENT = 0x7F000000;

    /**
     * Decodes a device independent bitmap (DIB)
     *
     * @param string $data Data containing the bitmap
     * @param int $offBmi Offset of the BITMAPINFO structure
     * @param int $offBits Offset of the bits
     * @param int $cbBmi Size of the BITMAPINFO structure (0 if unknown)
     * @param int $cbBits Size of the bits (0 if unknown)
     * @param bool $hasPaletteIndexes If the color table contains indexes in the palette (DIB_PAL_COLORS) instead of colors
     *
     * @return array{width: int, height: int, pixels: array<array<int>>}|null
     *
     * @throws WMFException
     */
    public static function readDIB(string $data, int $offBmi, int $offBits, int $cbBmi = 0, int $cbBits = 0, bool $hasPaletteIndexes = false): ?array
    {
        if ($offBmi < 0 || strlen($data) < $offBmi + 40) {
            throw new WMFException('Reader : Invalid file : truncated bitmap');
        }
        $header = unpack('VheaderSize/lwidth/lheight/vplanes/vbitCount/Vcompression/VsizeImage/lxPelsPerMeter/lyPelsPerMeter/VclrUsed', (string) substr($data, $offBmi, 36));
        if (!$header || $header['width'] <= 0 || $header['height'] == 0) {
            return null;
        }
        // BI_JPEG & BI_PNG : the bits are a JPEG or PNG image
        if (in_array($header['compression'], [4, 5])) {
            // Some files have a wrong size of bits : the rest of the data is used
            // A corrupted image is not drawn
            return self::readImage((string) substr($data, $offBits, $cbBits ?: strlen($data)))
                ?? self::readImage((string) substr($data, $offBits))
                ?? ['width' => 0, 'height' => 0, 'pixels' => []];
        }

        $width = $header['width'];
        $height = abs($header['height']);
        $bitCount = $header['bitCount'];
        // BI_RLE8 & BI_RLE4 are only valid for 8 & 4 bits per pixel
        $isRLE = ($header['compression'] == 1 && $bitCount == 8) || ($header['compression'] == 2 && $bitCount == 4);
        if ((!$isRLE && !in_array($header['compression'], [0, 3])) || !in_array($bitCount, [1, 4, 8, 16, 24, 32])) {
            return null;
        }

        $palette = [];
        if ($bitCount <= 8) {
            $colors = min(1 << $bitCount, $header['clrUsed'] ?: (1 << $bitCount));
            if ($hasPaletteIndexes) {
                // The palette of the metafile is not known : a gray scale is used
                $palette = self::getGrayPalette($colors);
            } else {
                $offset = $offBmi + $header['headerSize'];
                // The palette may be truncated : missing colors are black (or white for the second color of monochrome bitmaps)
                $available = (int) floor((min(strlen($data), $offBmi + ($cbBmi ?: strlen($data))) - $offset) / 4);
                $palette = $bitCount == 1 ? [0x000000, 0xFFFFFF] : [];
                for ($i = 0; $i < min($colors, $available); ++$i) {
                    list(, $blue, $green, $red) = unpack('C3', (string) substr($data, $offset + 4 * $i, 3));
                    $palette[$i] = ($red << 16) | ($green << 8) | $blue;
                }
            }
        }

        if ($isRLE) {
            // Compressed bitmaps are always bottom-up
            $rows = self::readRLE((string) substr($data, $offBits, $cbBits ?: strlen($data)), $width, $height, $bitCount == 4);
            $pixels = [];
            for ($row = $height - 1; $row >= 0; --$row) {
                $line = [];
                foreach ($rows[$row] as $index) {
                    // Skipped pixels are transparent
                    $line[] = $index < 0 ? self::TRANSPARENT : ($palette[$index] ?? 0);
                }
                $pixels[] = $line;
            }

            return ['width' => $width, 'height' => $height, 'pixels' => $pixels];
        }

        $stride = (($width * $bitCount + 31) >> 5) << 2;
        $pixels = [];
        for ($row = 0; $row < $height; ++$row) {
            // Positive height means a bottom-up bitmap
            $sourceRow = $header['height'] > 0 ? $height - 1 - $row : $row;
            $pixels[] = self::readRow((string) substr($data, $offBits + $sourceRow * $stride, $stride), $width, $bitCount, $palette);
        }

        return ['width' => $width, 'height' => $height, 'pixels' => $pixels];
    }

    /**
     * Decodes a packed DIB : the bits follow the BITMAPINFO structure
     *
     * @return array{width: int, height: int, pixels: array<array<int>>}|null
     */
    public static function readPackedDIB(string $data, bool $hasPaletteIndexes = false): ?array
    {
        if (strlen($data) < 40) {
            throw new WMFException('Reader : Invalid file : truncated bitmap');
        }
        $header = unpack('VheaderSize/lwidth/lheight/vplanes/vbitCount/Vcompression/VsizeImage/lxPelsPerMeter/lyPelsPerMeter/VclrUsed', (string) substr($data, 0, 36));
        // Only BITMAPINFOHEADER (and its extensions) are supported
        if (!$header || $header['headerSize'] < 40) {
            return null;
        }

        $colors = $header['clrUsed'];
        if ($header['bitCount'] <= 8) {
            $colors = min(1 << $header['bitCount'], $colors ?: (1 << $header['bitCount']));
        }
        $offBits = $header['headerSize'] + $colors * ($hasPaletteIndexes ? 2 : 4);
        // BI_BITFIELDS : the masks follow a BITMAPINFOHEADER
        if ($header['compression'] == 3 && $header['headerSize'] == 40) {
            $offBits += 12;
        }

        return self::readDIB($data, 0, $offBits, $offBits, 0, $hasPaletteIndexes);
    }

    /**
     * Decodes a device dependent bitmap of a WMF file (Bitmap16 object)
     *
     * Monochrome bitmaps are black & white, and indexed bitmaps use a gray scale (their palette is not known)
     *
     * @return array{width: int, height: int, pixels: array<array<int>>}|null
     */
    public static function readBitmap16(string $data): ?array
    {
        if (strlen($data) < 10) {
            throw new WMFException('Reader : Invalid file : truncated bitmap');
        }
        $header = unpack('vtype/swidth/sheight/swidthBytes/Cplanes/CbitsPixel', (string) substr($data, 0, 10));
        if (!$header || $header['width'] <= 0 || $header['height'] <= 0 || $header['widthBytes'] <= 0) {
            return null;
        }
        $bitCount = $header['bitsPixel'] * $header['planes'];
        if (!in_array($bitCount, [1, 4, 8, 16, 24, 32])) {
            return null;
        }

        $palette = $bitCount == 1 ? [0x000000, 0xFFFFFF] : self::getGrayPalette($bitCount <= 8 ? 1 << $bitCount : 0);
        $pixels = [];
        for ($row = 0; $row < $header['height']; ++$row) {
            $pixels[] = self::readRow((string) substr($data, 10 + $row * $header['widthBytes'], $header['widthBytes']), $header['width'], $bitCount, $palette);
        }

        return ['width' => $header['width'], 'height' => $header['height'], 'pixels' => $pixels];
    }

    /**
     * Decodes an image supported by GD (JPEG, PNG, GIF, BMP...)
     *
     * @param bool $keepAlpha If the pixels keep their GD alpha channel
     *
     * @return array{width: int, height: int, pixels: array<array<int>>}|null
     */
    public static function readImage(string $data, bool $keepAlpha = false): ?array
    {
        $image = @imagecreatefromstring($data);
        if (!$image) {
            return null;
        }
        imagepalettetotruecolor($image);
        $width = imagesx($image);
        $height = imagesy($image);

        $pixels = [];
        for ($y = 0; $y < $height; ++$y) {
            $line = [];
            for ($x = 0; $x < $width; ++$x) {
                $line[] = $keepAlpha ? imagecolorat($image, $x, $y) : imagecolorat($image, $x, $y) & 0xFFFFFF;
            }
            $pixels[] = $line;
        }
        if (\PHP_VERSION_ID < 80000) {
            imagedestroy($image);
        }

        return ['width' => $width, 'height' => $height, 'pixels' => $pixels];
    }

    /**
     * @param array{width: int, height: int, pixels: array<array<int>>} $bitmap
     *
     * @return array<int>
     */
    public static function getAverageColor(array $bitmap): array
    {
        $red = $green = $blue = 0;
        foreach ($bitmap['pixels'] as $line) {
            foreach ($line as $pixel) {
                $red += ($pixel >> 16) & 0xFF;
                $green += ($pixel >> 8) & 0xFF;
                $blue += $pixel & 0xFF;
            }
        }
        $count = max(1, $bitmap['width'] * $bitmap['height']);

        return [(int) round($red / $count), (int) round($green / $count), (int) round($blue / $count)];
    }

    /**
     * Decodes the bits of a BI_RLE8 or BI_RLE4 bitmap : returns the palette indexes, from the bottom row (-1 for skipped pixels)
     *
     * @return array<array<int>>
     */
    protected static function readRLE(string $data, int $width, int $height, bool $isRLE4): array
    {
        $rows = array_fill(0, $height, array_fill(0, $width, -1));
        $bytes = array_values(unpack('C*', $data ?: "\0"));
        $length = count($bytes);
        $x = $y = 0;
        $position = 0;
        while ($position + 1 < $length && $y < $height) {
            $first = $bytes[$position];
            $second = $bytes[$position + 1];
            $position += 2;
            if ($first > 0) {
                // Encoded mode : $first pixels of the same index (or of two alternating indexes for RLE4)
                for ($i = 0; $i < $first; ++$i, ++$x) {
                    if ($x < $width) {
                        $rows[$y][$x] = $isRLE4 ? ($i & 1 ? $second & 0x0F : $second >> 4) : $second;
                    }
                }
            } elseif ($second == 0) {
                // End of line
                $x = 0;
                ++$y;
            } elseif ($second == 1) {
                // End of bitmap
                break;
            } elseif ($second == 2) {
                // Delta : moves the current position
                $x += $bytes[$position] ?? 0;
                $y += $bytes[$position + 1] ?? 0;
                $position += 2;
            } else {
                // Absolute mode : $second indexes, padded to a word
                $size = $isRLE4 ? ($second + 1) >> 1 : $second;
                for ($i = 0; $i < $second; ++$i, ++$x) {
                    $byte = $bytes[$position + ($isRLE4 ? $i >> 1 : $i)] ?? 0;
                    if ($x < $width) {
                        $rows[$y][$x] = $isRLE4 ? ($i & 1 ? $byte & 0x0F : $byte >> 4) : $byte;
                    }
                }
                $position += $size + ($size & 1);
            }
        }

        return $rows;
    }

    /**
     * Decodes a row of pixels
     *
     * @param array<int> $palette
     *
     * @return array<int>
     */
    protected static function readRow(string $data, int $width, int $bitCount, array $palette): array
    {
        $bytes = array_values(unpack('C*', $data ?: "\0"));
        $line = [];
        for ($column = 0; $column < $width; ++$column) {
            switch ($bitCount) {
                case 1:
                    $index = ($bytes[$column >> 3] ?? 0) >> (7 - ($column & 7)) & 0x01;
                    $line[] = $palette[$index] ?? 0;
                    break;
                case 4:
                    $index = ($bytes[$column >> 1] ?? 0) >> (($column & 1) ? 0 : 4) & 0x0F;
                    $line[] = $palette[$index] ?? 0;
                    break;
                case 8:
                    $line[] = $palette[$bytes[$column] ?? 0] ?? 0;
                    break;
                case 16:
                    // RGB 555
                    $value = ($bytes[2 * $column] ?? 0) | (($bytes[2 * $column + 1] ?? 0) << 8);
                    $line[] = intdiv((($value >> 10) & 0x1F) * 255, 31) << 16 | intdiv((($value >> 5) & 0x1F) * 255, 31) << 8 | intdiv(($value & 0x1F) * 255, 31);
                    break;
                default:
                    $offset = $column * ($bitCount >> 3);
                    $line[] = (($bytes[$offset + 2] ?? 0) << 16) | (($bytes[$offset + 1] ?? 0) << 8) | ($bytes[$offset] ?? 0);
                    break;
            }
        }

        return $line;
    }

    /**
     * @return array<int>
     */
    protected static function getGrayPalette(int $colors): array
    {
        $palette = [];
        for ($i = 0; $i < $colors; ++$i) {
            $gray = $colors > 1 ? (int) round($i * 255 / ($colors - 1)) : 0;
            $palette[] = ($gray << 16) | ($gray << 8) | $gray;
        }

        return $palette;
    }
}
