<?php

declare(strict_types=1);

namespace PhpOffice\WMF\Reader\EMFPlus;

use PhpOffice\WMF\Exception\WMFException;
use PhpOffice\WMF\Reader\Detector;
use PhpOffice\WMF\Reader\EMF\GD as EMFReader;
use PhpOffice\WMF\Reader\WMF\GD as WMFReader;
use PhpOffice\WMF\Renderer\Bitmap;
use PhpOffice\WMF\Renderer\Encoding;
use PhpOffice\WMF\Renderer\GD as Renderer;

/**
 * Decodes the objects of EMF+ files (brushes, pens, paths, regions, images, fonts & string formats)
 *
 * @see https://learn.microsoft.com/en-us/openspecs/windows_protocols/ms-emfplus/
 */
class ObjectReader
{
    public const OBJECT_BRUSH = 1;
    public const OBJECT_PEN = 2;
    public const OBJECT_PATH = 3;
    public const OBJECT_REGION = 4;
    public const OBJECT_IMAGE = 5;
    public const OBJECT_FONT = 6;
    public const OBJECT_STRING_FORMAT = 7;
    public const OBJECT_IMAGE_ATTRIBUTES = 8;
    public const OBJECT_CUSTOM_LINE_CAP = 9;

    public const BRUSH_SOLID = 0;
    public const BRUSH_HATCH = 1;
    public const BRUSH_TEXTURE = 2;
    public const BRUSH_PATH_GRADIENT = 3;
    public const BRUSH_LINEAR_GRADIENT = 4;

    public const REGION_AND = 0x00000001;
    public const REGION_UNION = 0x00000002;
    public const REGION_XOR = 0x00000003;
    public const REGION_EXCLUDE = 0x00000004;
    public const REGION_COMPLEMENT = 0x00000005;
    public const REGION_RECT = 0x10000000;
    public const REGION_PATH = 0x10000001;
    public const REGION_EMPTY = 0x10000002;
    public const REGION_INFINITE = 0x10000003;

    /**
     * Decodes an object
     *
     * @return array<string, mixed>
     */
    public function read(int $type, string $data): array
    {
        $buffer = new Buffer($data);
        switch ($type) {
            case self::OBJECT_BRUSH:
                return $this->readBrush($buffer);
            case self::OBJECT_PEN:
                return $this->readPen($buffer);
            case self::OBJECT_PATH:
                return $this->readPath($buffer);
            case self::OBJECT_REGION:
                $buffer->skip(4);
                $buffer->skip(4);

                return ['type' => 'region', 'node' => $this->readRegionNode($buffer)];
            case self::OBJECT_IMAGE:
                return $this->readImage($buffer);
            case self::OBJECT_FONT:
                return $this->readFont($buffer);
            case self::OBJECT_STRING_FORMAT:
                return $this->readStringFormat($buffer);
            case self::OBJECT_IMAGE_ATTRIBUTES:
            case self::OBJECT_CUSTOM_LINE_CAP:
                // Image attributes are ignored, custom line caps are read with pens
                return ['type' => 'other'];
            default:
                throw new WMFException(sprintf('Reader : EMF+ object not implemented : %d', $type));
        }
    }

    // ---------------------------------------------------------------------
    // Brushes
    // ---------------------------------------------------------------------

    /**
     * Returns a brush of the renderer : a color, or a shader (a callable returning the color of a point)
     *
     * @return array<string, mixed>
     */
    public function readBrush(Buffer $buffer): array
    {
        $buffer->skip(4);
        $type = $buffer->readUInt32();
        switch ($type) {
            case self::BRUSH_SOLID:
                return self::getSolidBrush($buffer->readColor());
            case self::BRUSH_HATCH:
                return $this->readHatchBrush($buffer);
            case self::BRUSH_TEXTURE:
                return $this->readTextureBrush($buffer);
            case self::BRUSH_PATH_GRADIENT:
                return $this->readPathGradientBrush($buffer);
            case self::BRUSH_LINEAR_GRADIENT:
                return $this->readLinearGradientBrush($buffer);
            default:
                throw new WMFException(sprintf('Reader : EMF+ brush not implemented : %d', $type));
        }
    }

    /**
     * @param array<int> $color [r, g, b, a]
     *
     * @return array<string, mixed>
     */
    public static function getSolidBrush(array $color): array
    {
        return ['type' => 'brush', 'style' => 0, 'color' => $color];
    }

    /**
     * @return array<string, mixed>
     */
    protected function readHatchBrush(Buffer $buffer): array
    {
        $style = $buffer->readUInt32();
        $foreColor = $buffer->readColor();
        $backColor = $buffer->readColor();

        return [
            'type' => 'brush',
            'style' => 0,
            'color' => self::mixColors($foreColor, $backColor, 0.5),
            'space' => 'device',
            'shader' => function (float $x, float $y) use ($style, $foreColor, $backColor): array {
                return Renderer::isHatchForeground($style, (int) floor($x), (int) floor($y)) ? $foreColor : $backColor;
            },
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function readTextureBrush(Buffer $buffer): array
    {
        $flags = $buffer->readUInt32();
        $wrapMode = $buffer->readInt32();
        $transform = ($flags & 0x02) ? $buffer->readMatrix() : [1.0, 0.0, 0.0, 1.0, 0.0, 0.0];
        $image = $this->readImage($buffer);
        $bitmap = $image['bitmap'];
        if (empty($bitmap['pixels'])) {
            return self::getSolidBrush([128, 128, 128, 255]);
        }
        $inverse = self::invertMatrix($transform);
        $width = $bitmap['width'];
        $height = $bitmap['height'];
        $average = Bitmap::getAverageColor($bitmap);

        return [
            'type' => 'brush',
            'style' => 0,
            'color' => [$average[0], $average[1], $average[2], 255],
            'shader' => function (float $x, float $y) use ($inverse, $bitmap, $width, $height, $wrapMode): array {
                list($u, $v) = self::transformPoint($inverse, $x, $y);
                $u = self::wrap($u / $width, $wrapMode, true);
                $v = self::wrap($v / $height, $wrapMode, false);
                if ($u === null || $v === null) {
                    return [0, 0, 0, 0];
                }
                $pixel = $bitmap['pixels'][min($height - 1, (int) floor($v * $height))][min($width - 1, (int) floor($u * $width))] ?? 0;

                return [($pixel >> 16) & 0xFF, ($pixel >> 8) & 0xFF, $pixel & 0xFF, 255 - (int) round((($pixel >> 24) & 0x7F) * 255 / 127)];
            },
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function readLinearGradientBrush(Buffer $buffer): array
    {
        $flags = $buffer->readUInt32();
        $wrapMode = $buffer->readInt32();
        $rect = $buffer->readRect();
        $startColor = $buffer->readColor();
        $endColor = $buffer->readColor();
        $buffer->skip(8);
        $transform = ($flags & 0x02) ? $buffer->readMatrix() : [1.0, 0.0, 0.0, 1.0, 0.0, 0.0];
        $blend = $this->readBlend($buffer, $flags, $startColor, $endColor);
        if ($flags & 0x10) {
            // Vertical blend factors are ignored
            $count = $buffer->readUInt32();
            $buffer->skip(8 * $count);
        }

        $inverse = self::invertMatrix($transform);
        $width = $rect[2] ?: 1;

        return [
            'type' => 'brush',
            'style' => 0,
            'color' => self::mixColors($startColor, $endColor, 0.5),
            'shader' => function (float $x, float $y) use ($inverse, $rect, $width, $wrapMode, $blend): array {
                list($u) = self::transformPoint($inverse, $x, $y);
                $position = self::wrap(($u - $rect[0]) / $width, $wrapMode, true) ?? 0.0;

                return $blend($position);
            },
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function readPathGradientBrush(Buffer $buffer): array
    {
        $flags = $buffer->readUInt32();
        $wrapMode = $buffer->readInt32();
        $centerColor = $buffer->readColor();
        list($centerX, $centerY) = [$buffer->readFloat(), $buffer->readFloat()];
        $countColors = $buffer->readUInt32();
        $surroundingColors = [];
        for ($i = 0; $i < $countColors; ++$i) {
            $surroundingColors[] = $buffer->readColor();
        }
        // Boundary : a path, or points
        if ($flags & 0x01) {
            $size = $buffer->readUInt32();
            $path = $this->readPath(new Buffer($buffer->read($size)));
            $boundary = [];
            foreach ($path['figures'] as $figure) {
                $boundary = array_merge($boundary, $figure['points']);
            }
        } else {
            $boundary = $buffer->readPoints($buffer->readUInt32());
        }
        $transform = ($flags & 0x02) ? $buffer->readMatrix() : [1.0, 0.0, 0.0, 1.0, 0.0, 0.0];
        $surroundingColor = $surroundingColors[0] ?? $centerColor;
        // From the boundary (0) to the center (1)
        $blend = $this->readBlend($buffer, $flags, $surroundingColor, $centerColor);
        // Several surrounding colors (without preset colors) : the color of the boundary is interpolated between its points
        $countColors = count($surroundingColors);
        $isMultiColor = !($flags & 0x04) && $countColors > 1;

        $inverse = self::invertMatrix($transform);
        $count = count($boundary);

        return [
            'type' => 'brush',
            'style' => 0,
            'color' => self::mixColors($centerColor, $surroundingColor, 0.5),
            'shader' => function (float $x, float $y) use ($inverse, $centerX, $centerY, $boundary, $count, $blend, $isMultiColor, $surroundingColors, $countColors): array {
                list($x, $y) = self::transformPoint($inverse, $x, $y);
                $dx = $x - $centerX;
                $dy = $y - $centerY;
                if ($dx == 0 && $dy == 0) {
                    return $blend(1.0);
                }
                // Intersection of the ray from the center through the point with the boundary
                $nearest = null;
                $nearestEdge = 0;
                $nearestRatio = 0.0;
                for ($i = 0; $i < $count; ++$i) {
                    list($ax, $ay) = $boundary[$i];
                    list($bx, $by) = $boundary[($i + 1) % $count];
                    $ex = $bx - $ax;
                    $ey = $by - $ay;
                    $denominator = $dx * $ey - $dy * $ex;
                    if ($denominator == 0) {
                        continue;
                    }
                    $s = (($ax - $centerX) * $ey - ($ay - $centerY) * $ex) / $denominator;
                    $r = (($ax - $centerX) * $dy - ($ay - $centerY) * $dx) / $denominator;
                    if ($s > 0 && $r >= 0 && $r <= 1 && ($nearest === null || $s < $nearest)) {
                        $nearest = $s;
                        $nearestEdge = $i;
                        $nearestRatio = $r;
                    }
                }
                $position = $nearest === null ? 0.0 : max(0.0, 1 - 1 / $nearest);
                if (!$isMultiColor) {
                    return $blend($position);
                }
                // Color of the boundary (the last color is used for the points without color)
                $edgeColor = self::mixColors(
                    $surroundingColors[min($nearestEdge, $countColors - 1)],
                    $surroundingColors[min(($nearestEdge + 1) % $count, $countColors - 1)],
                    $nearestRatio
                );

                return $blend($position, $edgeColor);
            },
        ];
    }

    /**
     * Reads the optional blend of a gradient (preset colors or blend factors), and returns the color for a position (0 to 1)
     *
     * @param array<int> $startColor
     * @param array<int> $endColor
     *
     * @return callable(float, array<int>|null=): array<int> Color for a position (the start color may be overridden, except for preset colors)
     */
    protected function readBlend(Buffer $buffer, int $flags, array $startColor, array $endColor): callable
    {
        if ($flags & 0x04) {
            // Preset colors
            $count = $buffer->readUInt32();
            $positions = [];
            for ($i = 0; $i < $count; ++$i) {
                $positions[] = $buffer->readFloat();
            }
            $colors = [];
            for ($i = 0; $i < $count; ++$i) {
                $colors[] = $buffer->readColor();
            }

            return function (float $position, ?array $start = null) use ($positions, $colors, $count): array {
                for ($i = 0; $i + 1 < $count; ++$i) {
                    if ($position <= $positions[$i + 1]) {
                        $range = $positions[$i + 1] - $positions[$i];

                        return self::mixColors($colors[$i], $colors[$i + 1], $range > 0 ? ($position - $positions[$i]) / $range : 0);
                    }
                }

                return $colors[$count - 1] ?? [0, 0, 0, 0];
            };
        }

        $positions = [0.0, 1.0];
        $factors = [0.0, 1.0];
        if ($flags & 0x08) {
            // Blend factors
            $count = $buffer->readUInt32();
            $positions = $factors = [];
            for ($i = 0; $i < $count; ++$i) {
                $positions[] = $buffer->readFloat();
            }
            for ($i = 0; $i < $count; ++$i) {
                $factors[] = $buffer->readFloat();
            }
        }
        $count = count($positions);

        return function (float $position, ?array $start = null) use ($positions, $factors, $count, $startColor, $endColor): array {
            $factor = $factors[$count - 1] ?? 1.0;
            for ($i = 0; $i + 1 < $count; ++$i) {
                if ($position <= $positions[$i + 1]) {
                    $range = $positions[$i + 1] - $positions[$i];
                    $factor = $factors[$i] + ($factors[$i + 1] - $factors[$i]) * ($range > 0 ? ($position - $positions[$i]) / $range : 0);
                    break;
                }
            }

            return self::mixColors($start ?? $startColor, $endColor, $factor);
        };
    }

    /**
     * Applies a wrap mode to a position (0 to 1) : null if the position is outside (WrapModeClamp)
     */
    protected static function wrap(float $position, int $wrapMode, bool $isHorizontal): ?float
    {
        switch ($wrapMode) {
            case 1: // WrapModeTileFlipX
            case 2: // WrapModeTileFlipY
            case 3: // WrapModeTileFlipXY
                $isFlipped = $wrapMode == 3 || ($wrapMode == 1) == $isHorizontal;
                $period = (int) floor($position);
                $position -= $period;

                return $isFlipped && $period % 2 != 0 ? 1 - $position : $position;
            case 4: // WrapModeClamp
                return $position < 0 || $position > 1 ? null : $position;
            default: // WrapModeTile
                return $position - floor($position);
        }
    }

    // ---------------------------------------------------------------------
    // Pens
    // ---------------------------------------------------------------------

    /**
     * Returns a pen of the renderer : its width is in the unit `unit` (converted when the pen is used)
     *
     * @return array<string, mixed>
     */
    public function readPen(Buffer $buffer): array
    {
        $buffer->skip(8);
        $flags = $buffer->readUInt32();
        $unit = $buffer->readUInt32();
        $width = $buffer->readFloat();

        $pen = [
            'type' => 'pen',
            // PS_GEOMETRIC & PS_JOIN_MITER (the default join of EMF+)
            'style' => 0x00012000,
            'width' => $width,
            'unit' => $unit,
            'geometric' => true,
            'startCap' => 0x0200,
            'endCap' => 0x0200,
        ];
        if ($flags & 0x0001) {
            // Transform
            $buffer->readMatrix();
        }
        if ($flags & 0x0002) {
            $this->setLineCap($pen, 'start', $buffer->readInt32());
        }
        if ($flags & 0x0004) {
            $this->setLineCap($pen, 'end', $buffer->readInt32());
        }
        if ($flags & 0x0008) {
            // Miter (0), Bevel (1), Round (2), MiterClipped (3)
            $joins = [0 => 0x2000, 1 => 0x1000, 2 => 0x0000, 3 => 0x2000];
            $pen['style'] = 0x00010000 | ($joins[$buffer->readInt32()] ?? 0x2000);
        }
        if ($flags & 0x0010) {
            $pen['miterLimit'] = $buffer->readFloat();
        }
        if ($flags & 0x0020) {
            // Solid (0), Dash (1), Dot (2), DashDot (3), DashDotDot (4), Custom (5)
            $styles = [1 => [3, 1], 2 => [1, 1], 3 => [3, 1, 1, 1], 4 => [3, 1, 1, 1, 1, 1]];
            $lineStyle = $buffer->readInt32();
            if (isset($styles[$lineStyle])) {
                $pen['dashes'] = $styles[$lineStyle];
            }
        }
        if ($flags & 0x0040) {
            // Flat (0), Round (2), Triangle (3)
            $pen['dashCap'] = $buffer->readInt32() == 2 ? 0x0000 : 0x0200;
        }
        if ($flags & 0x0080) {
            $pen['dashOffset'] = $buffer->readFloat();
        }
        if ($flags & 0x0100) {
            $count = $buffer->readUInt32();
            $dashes = [];
            for ($i = 0; $i < $count; ++$i) {
                $dashes[] = $buffer->readFloat();
            }
            if (count($dashes) >= 2) {
                $pen['dashes'] = $dashes;
            }
        }
        if ($flags & 0x0200) {
            // Alignment
            $buffer->skip(4);
        }
        if ($flags & 0x0400) {
            // Compound lines : pairs of positions in the width of the pen
            $count = $buffer->readUInt32();
            $compound = [];
            for ($i = 0; $i < $count; ++$i) {
                $compound[] = $buffer->readFloat();
            }
            // A single line on the whole width is a simple line
            if ($compound != [0.0, 1.0]) {
                $pen['compound'] = $compound;
            }
        }
        if ($flags & 0x0800) {
            $this->readCustomLineCap($pen, 'start', new Buffer($buffer->read($buffer->readUInt32())));
        }
        if ($flags & 0x1000) {
            $this->readCustomLineCap($pen, 'end', new Buffer($buffer->read($buffer->readUInt32())));
        }

        $brush = $this->readBrush($buffer);

        return $pen + ['color' => $brush['color']] + (isset($brush['shader']) ? ['shader' => $brush['shader'], 'space' => $brush['space'] ?? 'logical'] : []);
    }

    /**
     * @param array<string, mixed> $pen
     */
    protected function setLineCap(array &$pen, string $end, int $cap): void
    {
        switch ($cap) {
            case 1: // Square
            case 0x11: // SquareAnchor
                $pen[$end . 'Cap'] = 0x0100;
                break;
            case 2: // Round
            case 0x12: // RoundAnchor
                $pen[$end . 'Cap'] = 0x0000;
                break;
            case 0x14: // ArrowAnchor
                $pen[$end . 'CapShape'] = [[0, 0], [1, -2], [-1, -2]];
                break;
            default: // Flat, Triangle, NoAnchor, DiamondAnchor, Custom
                $pen[$end . 'Cap'] = 0x0200;
        }
    }

    /**
     * Reads a custom line cap : an adjustable arrow, or a path (in pen widths)
     *
     * @param array<string, mixed> $pen
     */
    protected function readCustomLineCap(array &$pen, string $end, Buffer $buffer): void
    {
        $buffer->skip(4);
        // CustomLineCapDataTypeAdjustableArrow
        if ($buffer->readUInt32() == 1) {
            $width = $buffer->readFloat();
            $height = $buffer->readFloat();
            $middleInset = $buffer->readFloat();
            $pen[$end . 'CapShape'] = [[0, 0], [$width / 2, -$height], [0, -$height + $middleInset], [-$width / 2, -$height]];

            return;
        }

        // CustomLineCapDataTypeDefault : fill path and/or line path
        $flags = $buffer->readUInt32();
        // BaseCap, BaseInset, StrokeStartCap, StrokeEndCap, StrokeJoin, StrokeMiterLimit
        $buffer->skip(24);
        $widthScale = $buffer->readFloat() ?: 1.0;
        // FillHotSpot, StrokeHotSpot
        $buffer->skip(16);
        if ($flags & 0x03) {
            $path = $this->readPath(new Buffer($buffer->read($buffer->readUInt32())));
            $shape = [];
            foreach ($path['figures'] as $figure) {
                foreach ($figure['points'] as list($x, $y)) {
                    $shape[] = [$x * $widthScale, $y * $widthScale];
                }
            }
            $pen[$end . 'CapShape'] = $shape;
        }
    }

    // ---------------------------------------------------------------------
    // Paths & regions
    // ---------------------------------------------------------------------

    /**
     * Returns the figures of a path (see PhpOffice\WMF\Renderer\GD::drawPathFigures())
     *
     * @return array{type: string, figures: array<array{points: array<array<float>>, types: array<int>, closed: bool}>}
     */
    public function readPath(Buffer $buffer): array
    {
        $buffer->skip(4);
        $count = $buffer->readUInt32();
        $flags = $buffer->readUInt16();
        $buffer->skip(2);
        $points = $buffer->readPoints($count, ($flags & 0x4000) > 0, ($flags & 0x0800) > 0);

        // Types of points, run-length encoded (R flag) or not
        $types = [];
        if ($flags & 0x1000) {
            while (count($types) < $count) {
                $run = $buffer->readUInt8();
                $type = $buffer->readUInt8();
                $length = $run & 0x3F;
                for ($i = 0; $i < max(1, $length) && count($types) < $count; ++$i) {
                    $types[] = $type;
                }
            }
        } else {
            for ($i = 0; $i < $count; ++$i) {
                $types[] = $buffer->readUInt8();
            }
        }

        $figures = [];
        $current = null;
        foreach ($points as $i => $point) {
            $type = $types[$i] & 0x07;
            if ($type == 0 || $current === null) {
                if ($current !== null) {
                    $figures[] = $current;
                }
                $current = ['points' => [], 'types' => [], 'closed' => false];
                $type = 0;
            }
            $current['points'][] = $point;
            $current['types'][] = $type;
            // PathPointTypeCloseSubpath
            if ($types[$i] & 0x80) {
                $current['closed'] = true;
                $figures[] = $current;
                $current = null;
            }
        }
        if ($current !== null) {
            $figures[] = $current;
        }

        return ['type' => 'path', 'figures' => $figures];
    }

    /**
     * Reads a node of a region : a rectangle, a path, an empty or infinite region, or a combination of two nodes
     *
     * @return array<string, mixed>
     */
    protected function readRegionNode(Buffer $buffer): array
    {
        $type = $buffer->readUInt32();
        switch ($type) {
            case self::REGION_AND:
            case self::REGION_UNION:
            case self::REGION_XOR:
            case self::REGION_EXCLUDE:
            case self::REGION_COMPLEMENT:
                $left = $this->readRegionNode($buffer);
                $right = $this->readRegionNode($buffer);

                return ['type' => $type, 'left' => $left, 'right' => $right];
            case self::REGION_RECT:
                return ['type' => $type, 'rect' => $buffer->readRect()];
            case self::REGION_PATH:
                $size = $buffer->readUInt32();

                return ['type' => $type, 'path' => $this->readPath(new Buffer($buffer->read($size)))];
            case self::REGION_EMPTY:
            case self::REGION_INFINITE:
                return ['type' => $type];
            default:
                throw new WMFException(sprintf('Reader : EMF+ region node not implemented : 0x%08x', $type));
        }
    }

    // ---------------------------------------------------------------------
    // Images, fonts & string formats
    // ---------------------------------------------------------------------

    /**
     * Returns an image : a decoded bitmap (pixels with a GD alpha channel), metafiles are rendered
     *
     * @return array{type: string, bitmap: array{width: int, height: int, pixels: array<array<int>>}, isMetafile: bool}
     */
    public function readImage(Buffer $buffer): array
    {
        $buffer->skip(4);
        $type = $buffer->readUInt32();
        if ($type == 1) {
            return ['type' => 'image', 'bitmap' => $this->readBitmap($buffer), 'isMetafile' => false];
        }
        if ($type == 2) {
            $metafileType = $buffer->readUInt32();
            $size = $buffer->readUInt32();
            // The size of placeable WMF files doesn't include their placeable header (24 bytes with its padding)
            if ($metafileType == 2) {
                $size += 24;
            }
            $data = $buffer->read(min($size, $buffer->getRemaining()));

            return ['type' => 'image', 'bitmap' => $this->renderMetafile($metafileType, $data), 'isMetafile' => true];
        }

        throw new WMFException(sprintf('Reader : EMF+ image not implemented : %d', $type));
    }

    /**
     * @return array{width: int, height: int, pixels: array<array<int>>}
     */
    protected function readBitmap(Buffer $buffer): array
    {
        $width = $buffer->readInt32();
        $height = $buffer->readInt32();
        $stride = $buffer->readInt32();
        $pixelFormat = $buffer->readUInt32();
        $isCompressed = $buffer->readUInt32() == 1;
        if ($isCompressed) {
            // PNG, JPEG, GIF, BMP... decoded by GD
            return Bitmap::readImage($buffer->read($buffer->getRemaining()), true)
                ?? ['width' => 0, 'height' => 0, 'pixels' => []];
        }
        if ($width <= 0 || $height <= 0 || $width * $height > 16000000) {
            throw new WMFException('Reader : Invalid file : EMF+ bitmap');
        }

        $bitCount = ($pixelFormat >> 8) & 0xFF;
        // PixelFormatIndexed : the palette precedes the pixels
        $palette = [];
        if ($pixelFormat & 0x00010000) {
            $buffer->skip(4);
            $count = $buffer->readUInt32();
            for ($i = 0; $i < $count; ++$i) {
                $palette[] = self::toGDPixel($buffer->readColor());
            }
        }

        $rowSize = abs($stride) ?: (int) (ceil($width * $bitCount / 32) * 4);
        $rows = [];
        for ($row = 0; $row < $height; ++$row) {
            $rows[] = $buffer->read(min($rowSize, $buffer->getRemaining()));
        }
        // A negative stride means a bottom-up bitmap
        if ($stride < 0) {
            $rows = array_reverse($rows);
        }

        $pixels = [];
        foreach ($rows as $data) {
            $bytes = array_values(unpack('C*', $data . str_repeat("\0", (int) ceil($width * $bitCount / 8) + 8)));
            $line = [];
            for ($x = 0; $x < $width; ++$x) {
                $line[] = $this->readPixel($bytes, $x, $pixelFormat, $bitCount, $palette);
            }
            $pixels[] = $line;
        }

        return ['width' => $width, 'height' => $height, 'pixels' => $pixels];
    }

    /**
     * @param array<int> $bytes
     * @param array<int> $palette
     */
    protected function readPixel(array $bytes, int $x, int $pixelFormat, int $bitCount, array $palette): int
    {
        switch ($pixelFormat) {
            case 0x00030101: // 1bpp indexed
                return $palette[($bytes[$x >> 3] >> (7 - ($x & 7))) & 0x01] ?? 0;
            case 0x00030402: // 4bpp indexed
                return $palette[($bytes[$x >> 1] >> (($x & 1) ? 0 : 4)) & 0x0F] ?? 0;
            case 0x00030803: // 8bpp indexed
                return $palette[$bytes[$x]] ?? 0;
            case 0x00101004: // 16bpp gray scale
                $gray = $bytes[2 * $x + 1];

                return ($gray << 16) | ($gray << 8) | $gray;
            case 0x00021005: // 16bpp RGB 555
            case 0x00061007: // 16bpp ARGB 1555
                $value = $bytes[2 * $x] | ($bytes[2 * $x + 1] << 8);
                $pixel = intdiv((($value >> 10) & 0x1F) * 255, 31) << 16 | intdiv((($value >> 5) & 0x1F) * 255, 31) << 8 | intdiv(($value & 0x1F) * 255, 31);

                return $pixelFormat == 0x00061007 && !($value & 0x8000) ? $pixel | (127 << 24) : $pixel;
            case 0x00021006: // 16bpp RGB 565
                $value = $bytes[2 * $x] | ($bytes[2 * $x + 1] << 8);

                return intdiv((($value >> 11) & 0x1F) * 255, 31) << 16 | intdiv((($value >> 5) & 0x3F) * 255, 63) << 8 | intdiv(($value & 0x1F) * 255, 31);
            case 0x00021808: // 24bpp RGB
                return ($bytes[3 * $x + 2] << 16) | ($bytes[3 * $x + 1] << 8) | $bytes[3 * $x];
            case 0x0026200A: // 32bpp ARGB
            case 0x000E200B: // 32bpp premultiplied ARGB
                list($blue, $green, $red, $alpha) = [$bytes[4 * $x], $bytes[4 * $x + 1], $bytes[4 * $x + 2], $bytes[4 * $x + 3]];
                if ($pixelFormat == 0x000E200B && $alpha > 0) {
                    $red = min(255, intdiv($red * 255, $alpha));
                    $green = min(255, intdiv($green * 255, $alpha));
                    $blue = min(255, intdiv($blue * 255, $alpha));
                }

                return self::toGDPixel([$red, $green, $blue, $alpha]);
            case 0x0010300C: // 48bpp RGB
                return ($bytes[6 * $x + 5] << 16) | ($bytes[6 * $x + 3] << 8) | $bytes[6 * $x + 1];
            case 0x0034400D: // 64bpp ARGB
            case 0x001A400E: // 64bpp premultiplied ARGB
                return self::toGDPixel([$bytes[8 * $x + 5], $bytes[8 * $x + 3], $bytes[8 * $x + 1], $bytes[8 * $x + 7]]);
            case 0x00022009: // 32bpp RGB
            default:
                if ($bitCount == 32) {
                    return ($bytes[4 * $x + 2] << 16) | ($bytes[4 * $x + 1] << 8) | $bytes[4 * $x];
                }
                throw new WMFException(sprintf('Reader : EMF+ pixel format not implemented : 0x%08x', $pixelFormat));
        }
    }

    /**
     * Renders an embedded metafile (WMF, placeable WMF, EMF, EMF+) with the readers of the library
     *
     * @return array{width: int, height: int, pixels: array<array<int>>}
     */
    protected function renderMetafile(int $type, string $data): array
    {
        // GDI+ may write the placeable header of WMF files with a padding of 2 bytes (24 bytes instead of 22)
        if ($type == 2 && !Detector::isPlaceableWMF($data) && Detector::isPlaceableWMF((string) substr($data, 0, 22) . (string) substr($data, 24))) {
            $data = (string) substr($data, 0, 22) . (string) substr($data, 24);
        }
        $type = Detector::detect($data);
        if ($type == Detector::TYPE_EMF || $type == Detector::TYPE_EMFPLUS) {
            $reader = new EMFReader();
        } elseif ($type == Detector::TYPE_WMF) {
            $reader = new WMFReader();
        } else {
            return ['width' => 0, 'height' => 0, 'pixels' => []];
        }
        // A metafile which can not be read is not drawn, its background is transparent
        $reader->enableExceptions(false);
        $reader->setBackgroundColor(null);
        if (!$reader->loadFromString($data)) {
            return ['width' => 0, 'height' => 0, 'pixels' => []];
        }
        $image = $reader->getResource();
        $width = imagesx($image);
        $height = imagesy($image);
        $pixels = [];
        for ($y = 0; $y < $height; ++$y) {
            $line = [];
            for ($x = 0; $x < $width; ++$x) {
                $line[] = imagecolorat($image, $x, $y);
            }
            $pixels[] = $line;
        }

        return ['width' => $width, 'height' => $height, 'pixels' => $pixels];
    }

    /**
     * @return array<string, mixed>
     */
    protected function readFont(Buffer $buffer): array
    {
        $buffer->skip(4);
        $emSize = $buffer->readFloat();
        $unit = $buffer->readUInt32();
        $style = $buffer->readInt32();
        $buffer->skip(4);
        $length = $buffer->readUInt32();

        return [
            'type' => 'font',
            'emSize' => $emSize,
            'unit' => $unit,
            'weight' => ($style & 0x01) ? 700 : 400,
            'italic' => ($style & 0x02) > 0,
            'underline' => ($style & 0x04) > 0,
            'strikeOut' => ($style & 0x08) > 0,
            'face' => Encoding::decodeUTF16($buffer->readString($length)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function readStringFormat(Buffer $buffer): array
    {
        $buffer->skip(4);
        $flags = $buffer->readUInt32();
        $buffer->skip(4);
        $alignment = $buffer->readUInt32();
        $lineAlignment = $buffer->readUInt32();

        return ['type' => 'stringFormat', 'flags' => $flags, 'alignment' => $alignment, 'lineAlignment' => $lineAlignment];
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    /**
     * @param array<int> $first
     * @param array<int> $second
     *
     * @return array<int>
     */
    public static function mixColors(array $first, array $second, float $ratio): array
    {
        $ratio = max(0.0, min(1.0, $ratio));
        $color = [];
        for ($i = 0; $i < 4; ++$i) {
            $color[] = (int) round(($first[$i] ?? 255) + ((($second[$i] ?? 255) - ($first[$i] ?? 255)) * $ratio));
        }

        return $color;
    }

    /**
     * Converts a color [r, g, b, a] to a GD pixel (with an alpha channel)
     *
     * @param array<int> $color
     */
    public static function toGDPixel(array $color): int
    {
        return ((127 - (int) round($color[3] * 127 / 255)) << 24) | ($color[0] << 16) | ($color[1] << 8) | $color[2];
    }

    /**
     * @param array<float> $matrix [m11, m12, m21, m22, dx, dy]
     *
     * @return array<float>
     */
    public static function invertMatrix(array $matrix): array
    {
        list($m11, $m12, $m21, $m22, $dx, $dy) = $matrix;
        $determinant = $m11 * $m22 - $m12 * $m21;
        if ($determinant == 0) {
            return [1.0, 0.0, 0.0, 1.0, 0.0, 0.0];
        }

        return [
            $m22 / $determinant,
            -$m12 / $determinant,
            -$m21 / $determinant,
            $m11 / $determinant,
            ($m21 * $dy - $m22 * $dx) / $determinant,
            ($m12 * $dx - $m11 * $dy) / $determinant,
        ];
    }

    /**
     * @param array<float> $matrix [m11, m12, m21, m22, dx, dy]
     *
     * @return array<float>
     */
    public static function transformPoint(array $matrix, float $x, float $y): array
    {
        return [$x * $matrix[0] + $y * $matrix[2] + $matrix[4], $x * $matrix[1] + $y * $matrix[3] + $matrix[5]];
    }
}
