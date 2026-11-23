<?php

declare(strict_types=1);

namespace PhpOffice\WMF\Renderer;

/**
 * Geometry of the renderer : rasterization of polygons into spans, operations on spans, strokes & curves
 *
 * Spans are horizontal runs of pixels, indexed by row : `[row => [[xStart, xEnd], ...]]` (inclusive, sorted & disjoint)
 */
class Rasterizer
{
    public const RGN_AND = 1;
    public const RGN_OR = 2;
    public const RGN_XOR = 3;
    public const RGN_DIFF = 4;
    public const RGN_COPY = 5;

    /**
     * Converts polygons into spans of pixels (scanline algorithm)
     *
     * @param array<array<array<float>>> $polygons
     * @param bool $nonZero Nonzero winding rule (else even-odd rule)
     *
     * @return array<int, array<array<int>>>
     */
    public static function rasterize(array $polygons, bool $nonZero, int $width, int $height): array
    {
        // Edges bucketed by their first row
        $edges = [];
        $minRow = PHP_INT_MAX;
        $maxRow = PHP_INT_MIN;
        foreach ($polygons as $polygon) {
            $count = count($polygon);
            for ($i = 0; $i < $count; ++$i) {
                list($x1, $y1) = $polygon[$i];
                list($x2, $y2) = $polygon[($i + 1) % $count];
                if ($y1 == $y2) {
                    continue;
                }
                $direction = 1;
                if ($y1 > $y2) {
                    list($x1, $y1, $x2, $y2) = [$x2, $y2, $x1, $y1];
                    $direction = -1;
                }
                // Rows whose center is in [y1, y2)
                $firstRow = (int) max(0, ceil($y1 - 0.5));
                $lastRow = (int) min($height - 1, ceil($y2 - 0.5) - 1);
                if ($firstRow > $lastRow) {
                    continue;
                }
                $slope = ($x2 - $x1) / ($y2 - $y1);
                $edges[$firstRow][] = [$lastRow, $x1 + ($firstRow + 0.5 - $y1) * $slope, $slope, $direction];
                $minRow = min($minRow, $firstRow);
                $maxRow = max($maxRow, $lastRow);
            }
        }

        $spans = [];
        $active = [];
        for ($row = $minRow; $row <= $maxRow; ++$row) {
            if (isset($edges[$row])) {
                foreach ($edges[$row] as $edge) {
                    $active[] = $edge;
                }
            }
            if (empty($active)) {
                continue;
            }

            $crossings = [];
            foreach ($active as $key => $edge) {
                if ($edge[0] < $row) {
                    unset($active[$key]);
                    continue;
                }
                $crossings[] = [$edge[1], $edge[3]];
                $active[$key][1] += $edge[2];
            }
            usort($crossings, function (array $a, array $b): int {
                return $a[0] <=> $b[0];
            });

            $rowSpans = [];
            $winding = 0;
            $start = 0.0;
            foreach ($crossings as $crossing) {
                $wasInside = $nonZero ? $winding != 0 : ($winding % 2) != 0;
                $winding += $nonZero ? $crossing[1] : 1;
                $isInside = $nonZero ? $winding != 0 : ($winding % 2) != 0;
                if (!$wasInside && $isInside) {
                    $start = $crossing[0];
                } elseif ($wasInside && !$isInside) {
                    // Pixels whose center is in [start, end)
                    $xStart = (int) max(0, ceil($start - 0.5));
                    $xEnd = (int) min($width - 1, ceil($crossing[0] - 0.5) - 1);
                    if ($xStart <= $xEnd) {
                        $rowSpans[] = [$xStart, $xEnd];
                    }
                }
            }
            if (!empty($rowSpans)) {
                $spans[$row] = $rowSpans;
            }
        }

        return $spans;
    }

    /**
     * @param array<int, array<array<int>>> $first
     * @param array<int, array<array<int>>> $second
     * @param int $mode RGN_AND, RGN_OR, RGN_XOR, RGN_DIFF or RGN_COPY
     *
     * @return array<int, array<array<int>>>
     */
    public static function combineSpans(array $first, array $second, int $mode): array
    {
        switch ($mode) {
            case self::RGN_COPY:
                return $second;
            case self::RGN_AND:
                $rows = array_keys(array_intersect_key($first, $second));
                break;
            case self::RGN_DIFF:
                $rows = array_keys($first);
                break;
            default:
                $rows = array_keys($first + $second);
                break;
        }

        $result = [];
        foreach ($rows as $row) {
            $rowSpans = self::combineRow($first[$row] ?? [], $second[$row] ?? [], $mode);
            if (!empty($rowSpans)) {
                $result[$row] = $rowSpans;
            }
        }
        ksort($result);

        return $result;
    }

    /**
     * Combines two rows of sorted & disjoint spans
     *
     * @param array<array<int>> $first
     * @param array<array<int>> $second
     *
     * @return array<array<int>>
     */
    public static function combineRow(array $first, array $second, int $mode): array
    {
        $countFirst = count($first);
        $countSecond = count($second);
        $result = [];

        if ($mode == self::RGN_AND) {
            $i = $j = 0;
            while ($i < $countFirst && $j < $countSecond) {
                $start = max($first[$i][0], $second[$j][0]);
                $end = min($first[$i][1], $second[$j][1]);
                if ($start <= $end) {
                    $result[] = [$start, $end];
                }
                if ($first[$i][1] < $second[$j][1]) {
                    ++$i;
                } else {
                    ++$j;
                }
            }

            return $result;
        }

        // Boundaries of half-open intervals
        $boundaries = [];
        foreach ($first as $span) {
            $boundaries[] = $span[0];
            $boundaries[] = $span[1] + 1;
        }
        foreach ($second as $span) {
            $boundaries[] = $span[0];
            $boundaries[] = $span[1] + 1;
        }
        sort($boundaries);

        $i = $j = 0;
        $countBoundaries = count($boundaries);
        for ($k = 0; $k < $countBoundaries - 1; ++$k) {
            $x = $boundaries[$k];
            if ($x == $boundaries[$k + 1]) {
                continue;
            }
            while ($i < $countFirst && $first[$i][1] < $x) {
                ++$i;
            }
            while ($j < $countSecond && $second[$j][1] < $x) {
                ++$j;
            }
            $inFirst = $i < $countFirst && $first[$i][0] <= $x;
            $inSecond = $j < $countSecond && $second[$j][0] <= $x;
            switch ($mode) {
                case self::RGN_OR:
                    $inside = $inFirst || $inSecond;
                    break;
                case self::RGN_XOR:
                    $inside = ($inFirst xor $inSecond);
                    break;
                case self::RGN_DIFF:
                    $inside = $inFirst && !$inSecond;
                    break;
                default:
                    $inside = $inSecond;
            }
            if (!$inside) {
                continue;
            }
            $last = count($result) - 1;
            if ($last >= 0 && $result[$last][1] + 1 == $x) {
                $result[$last][1] = $boundaries[$k + 1] - 1;
            } else {
                $result[] = [$x, $boundaries[$k + 1] - 1];
            }
        }

        return $result;
    }

    /**
     * Converts a polyline into polygons covering its stroke
     *
     * @param array<array<float>> $points
     * @param int $endCap PS_ENDCAP_ROUND (0x0000), PS_ENDCAP_SQUARE (0x0100) or PS_ENDCAP_FLAT (0x0200)
     * @param int $join PS_JOIN_ROUND (0x0000), PS_JOIN_BEVEL (0x1000) or PS_JOIN_MITER (0x2000)
     * @param int|null $startCap Cap of the start of the line (null : $endCap)
     *
     * @return array<array<array<float>>>
     */
    public static function getStrokePolygons(array $points, bool $closed, float $halfWidth, int $endCap, int $join, float $miterLimit, ?int $startCap = null): array
    {
        // Remove duplicated points
        $cleaned = [];
        foreach ($points as $point) {
            $last = end($cleaned);
            if (!$last || abs($last[0] - $point[0]) > 0.001 || abs($last[1] - $point[1]) > 0.001) {
                $cleaned[] = $point;
            }
        }
        if ($closed && count($cleaned) > 1) {
            $last = end($cleaned);
            if (abs($last[0] - $cleaned[0][0]) <= 0.001 && abs($last[1] - $cleaned[0][1]) <= 0.001) {
                array_pop($cleaned);
            }
            $cleaned[] = $cleaned[0];
        }

        $count = count($cleaned);
        if ($count == 0) {
            return [];
        }
        if ($count == 1) {
            return [self::getDiscPolygon($cleaned[0], $halfWidth)];
        }

        $startCap = $startCap ?? $endCap;
        $polygons = [];
        // Normals of each segment
        $normals = [];
        for ($i = 0; $i < $count - 1; ++$i) {
            list($x1, $y1) = $cleaned[$i];
            list($x2, $y2) = $cleaned[$i + 1];
            $length = hypot($x2 - $x1, $y2 - $y1);
            $ux = ($x2 - $x1) / $length;
            $uy = ($y2 - $y1) / $length;
            $normals[$i] = [-$uy * $halfWidth, $ux * $halfWidth];

            // Square end caps extend the line by the half width
            if (!$closed) {
                if ($i == 0 && $startCap == 0x0100) {
                    $x1 -= $ux * $halfWidth;
                    $y1 -= $uy * $halfWidth;
                }
                if ($i == $count - 2 && $endCap == 0x0100) {
                    $x2 += $ux * $halfWidth;
                    $y2 += $uy * $halfWidth;
                }
            }

            list($nx, $ny) = $normals[$i];
            $polygons[] = [
                [$x1 + $nx, $y1 + $ny],
                [$x2 + $nx, $y2 + $ny],
                [$x2 - $nx, $y2 - $ny],
                [$x1 - $nx, $y1 - $ny],
            ];
        }

        // Round end caps
        if (!$closed && $startCap == 0x0000) {
            $polygons[] = self::getDiscPolygon($cleaned[0], $halfWidth);
        }
        if (!$closed && $endCap == 0x0000) {
            $polygons[] = self::getDiscPolygon($cleaned[$count - 1], $halfWidth);
        }

        // Joins between segments (for closed figures, the last segment is joined to the first one)
        $lastSegment = $count - 2;
        for ($i = 0; $i < ($closed ? $count - 1 : $count - 2); ++$i) {
            $next = $i == $lastSegment ? 0 : $i + 1;
            $polygons[] = self::getJoinPolygon($cleaned[$i + 1], $normals[$i], $normals[$next], $halfWidth, $join, $miterLimit);
        }

        // All polygons must have the same orientation, so their union is filled with the nonzero rule
        foreach ($polygons as $key => $polygon) {
            if (self::getSignedArea($polygon) < 0) {
                $polygons[$key] = array_reverse($polygon);
            }
        }

        return $polygons;
    }

    /**
     * Returns the polygon filling the outer side of the join between two segments
     *
     * @param array<float> $vertex
     * @param array<float> $normalIn Normal of the incoming segment
     * @param array<float> $normalOut Normal of the outgoing segment
     *
     * @return array<array<float>>
     */
    public static function getJoinPolygon(array $vertex, array $normalIn, array $normalOut, float $halfWidth, int $join, float $miterLimit): array
    {
        if ($join == 0x0000) {
            return self::getDiscPolygon($vertex, $halfWidth);
        }

        // The outer side is the opposite of the direction of the turn
        $cross = $normalIn[0] * $normalOut[1] - $normalIn[1] * $normalOut[0];
        $side = $cross > 0 ? -1 : 1;
        $outerIn = [$vertex[0] + $side * $normalIn[0], $vertex[1] + $side * $normalIn[1]];
        $outerOut = [$vertex[0] + $side * $normalOut[0], $vertex[1] + $side * $normalOut[1]];

        if ($join == 0x2000) {
            // Miter : the offset lines meet at a distance of halfWidth / cos(angle / 2)
            $sumX = $normalIn[0] + $normalOut[0];
            $sumY = $normalIn[1] + $normalOut[1];
            $sumSquared = $sumX * $sumX + $sumY * $sumY;
            if ($sumSquared > 0) {
                $ratio = 2 * $halfWidth / sqrt($sumSquared);
                if ($ratio <= $miterLimit) {
                    $factor = 2 * $halfWidth * $halfWidth / $sumSquared;

                    return [
                        $vertex,
                        $outerIn,
                        [$vertex[0] + $side * $sumX * $factor, $vertex[1] + $side * $sumY * $factor],
                        $outerOut,
                    ];
                }
            }
        }

        // Bevel
        return [$vertex, $outerIn, $outerOut];
    }

    /**
     * @param array<float> $center
     *
     * @return array<array<float>>
     */
    public static function getDiscPolygon(array $center, float $radius): array
    {
        $steps = (int) max(8, min(48, ceil(2 * M_PI * $radius / 3)));
        $points = [];
        for ($i = 0; $i < $steps; ++$i) {
            $angle = 2 * M_PI * $i / $steps;
            $points[] = [$center[0] + $radius * cos($angle), $center[1] + $radius * sin($angle)];
        }

        return $points;
    }

    /**
     * @param array<array<float>> $polygon
     */
    public static function getSignedArea(array $polygon): float
    {
        $area = 0;
        $count = count($polygon);
        for ($i = 0; $i < $count; ++$i) {
            $next = $polygon[($i + 1) % $count];
            $area += $polygon[$i][0] * $next[1] - $next[0] * $polygon[$i][1];
        }

        return $area / 2;
    }

    /**
     * @param array<array<float>> $points Start point followed by groups of 3 points (2 control points + end point)
     *
     * @return array<array<float>>
     */
    public static function flattenBeziers(array $points): array
    {
        if (empty($points)) {
            return [];
        }
        $result = [$points[0]];
        for ($i = 1; $i + 2 < count($points); $i += 3) {
            $result = array_merge($result, self::flattenBezier($points[$i - 1], $points[$i], $points[$i + 1], $points[$i + 2]));
        }

        return $result;
    }

    /**
     * @param array<float> $p0
     * @param array<float> $p1
     * @param array<float> $p2
     * @param array<float> $p3
     *
     * @return array<array<float>> Points of the curve, without the start point
     */
    public static function flattenBezier(array $p0, array $p1, array $p2, array $p3): array
    {
        $length = hypot($p1[0] - $p0[0], $p1[1] - $p0[1])
            + hypot($p2[0] - $p1[0], $p2[1] - $p1[1])
            + hypot($p3[0] - $p2[0], $p3[1] - $p2[1]);
        $steps = (int) max(2, min(128, ceil($length / 3)));

        $points = [];
        for ($i = 1; $i <= $steps; ++$i) {
            $t = $i / $steps;
            $mt = 1 - $t;
            $a = $mt * $mt * $mt;
            $b = 3 * $mt * $mt * $t;
            $c = 3 * $mt * $t * $t;
            $d = $t * $t * $t;
            $points[] = [
                $a * $p0[0] + $b * $p1[0] + $c * $p2[0] + $d * $p3[0],
                $a * $p0[1] + $b * $p1[1] + $c * $p2[1] + $d * $p3[1],
            ];
        }

        return $points;
    }
}
