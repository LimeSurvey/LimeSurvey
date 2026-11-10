<?php

declare(strict_types=1);

namespace PhpOffice\WMF\Renderer;

use GdImage;

/**
 * Drawing engine of the readers based on GD
 *
 * It implements the drawing model of GDI (device context, objects, transforms, paths, clipping, texts & bitmaps)
 * shared by WMF & EMF files. Coordinates are logical coordinates, unless otherwise stated.
 *
 * The drawing is rasterized on a supersampled canvas, which is then downscaled in order to get antialiased output.
 */
class GD
{
    public const MM_TEXT = 1;
    public const MM_LOMETRIC = 2;
    public const MM_HIMETRIC = 3;
    public const MM_LOENGLISH = 4;
    public const MM_HIENGLISH = 5;
    public const MM_TWIPS = 6;
    public const MM_ISOTROPIC = 7;
    public const MM_ANISOTROPIC = 8;

    public const ALTERNATE = 1;
    public const WINDING = 2;

    public const ROP_BLACKNESS = 0x00000042;
    public const ROP_NOP = 0x00AA0029;
    public const ROP_PATCOPY = 0x00F00021;
    public const ROP_WHITENESS = 0x00FF0062;
    public const ROP_SRCCOPY = 0x00CC0020;
    public const ROP_DSTINVERT = 0x00550009;

    /**
     * Ordered dither matrix (8x8) of the percent hatch styles
     */
    protected const BAYER = [
        [0, 32, 8, 40, 2, 34, 10, 42],
        [48, 16, 56, 24, 50, 18, 58, 26],
        [12, 44, 4, 36, 14, 46, 6, 38],
        [60, 28, 52, 20, 62, 30, 54, 22],
        [3, 35, 11, 43, 1, 33, 9, 41],
        [51, 19, 59, 27, 49, 17, 57, 25],
        [15, 47, 7, 39, 13, 45, 5, 37],
        [63, 31, 55, 23, 61, 29, 53, 21],
    ];

    /**
     * Maximum factor used for antialiasing
     */
    protected const SUPERSAMPLING = 4;
    /**
     * Maximum size of the supersampled canvas (the supersampling is reduced for big images)
     */
    protected const MAX_CANVAS_PIXELS = 8000000;
    /**
     * Maximum size of the output image (big images are scaled down)
     */
    protected const MAX_IMAGE_PIXELS = 16000000;

    /**
     * @phpstan-ignore-next-line
     *
     * @var GdImage|resource|false
     */
    protected $canvas;
    /**
     * Size of the output image
     *
     * @var int
     */
    protected $width;
    /**
     * @var int
     */
    protected $height;
    /**
     * @var int
     */
    protected $supersampling;
    /**
     * Size of the supersampled canvas
     *
     * @var int
     */
    protected $canvasWidth;
    /**
     * @var int
     */
    protected $canvasHeight;
    /**
     * Device coordinates of the top left corner of the image
     *
     * @var float
     */
    protected $originX;
    /**
     * @var float
     */
    protected $originY;
    /**
     * Number of canvas pixels per device unit
     *
     * @var float
     */
    protected $deviceScaleX;
    /**
     * @var float
     */
    protected $deviceScaleY;
    /**
     * Device units per millimeter (used by the metric map modes)
     *
     * @var float
     */
    protected $pixelsPerMmX = 96 / 25.4;
    /**
     * @var float
     */
    protected $pixelsPerMmY = 96 / 25.4;
    /**
     * Current device context
     *
     * @var array<string, mixed>
     */
    protected $dc = [];
    /**
     * @var array<array<string, mixed>>
     */
    protected $dcStack = [];
    /**
     * Figures of the current path (in canvas coordinates)
     *
     * @var array<array{points: array<array<float>>, closed: bool}>|null
     */
    protected $path;
    /**
     * @var bool
     */
    protected $inPath = false;
    /**
     * @var FontResolver
     */
    protected $fontResolver;
    /**
     * If the background of the image is transparent
     *
     * @var bool
     */
    protected $isTransparent = false;

    /**
     * @param int $width Width of the output image (in pixels)
     * @param int $height Height of the output image (in pixels)
     * @param float $deviceWidth Width of the image in device units (0 : the width of the image)
     * @param float $deviceHeight Height of the image in device units (0 : the height of the image)
     * @param float $originX Device coordinates of the top left corner of the image
     * @param float $originY
     * @param array<int>|null $backgroundColor Color of the background [r, g, b], or null for a transparent background
     */
    public function __construct(int $width, int $height, float $deviceWidth = 0, float $deviceHeight = 0, float $originX = 0, float $originY = 0, ?FontResolver $fontResolver = null, ?array $backgroundColor = [255, 255, 255])
    {
        $this->width = max(1, $width);
        $this->height = max(1, $height);
        $deviceWidth = $deviceWidth > 0 ? $deviceWidth : $this->width;
        $deviceHeight = $deviceHeight > 0 ? $deviceHeight : $this->height;

        if ($this->width * $this->height > self::MAX_IMAGE_PIXELS) {
            // Big images are scaled down
            $ratio = sqrt(self::MAX_IMAGE_PIXELS / ($this->width * $this->height));
            $this->width = (int) max(1, floor($this->width * $ratio));
            $this->height = (int) max(1, floor($this->height * $ratio));
        }

        $this->supersampling = (int) max(1, min(self::SUPERSAMPLING, floor(sqrt(self::MAX_CANVAS_PIXELS / ($this->width * $this->height)))));
        $this->canvasWidth = $this->width * $this->supersampling;
        $this->canvasHeight = $this->height * $this->supersampling;
        $this->deviceScaleX = $this->canvasWidth / $deviceWidth;
        $this->deviceScaleY = $this->canvasHeight / $deviceHeight;
        $this->originX = $originX;
        $this->originY = $originY;
        $this->fontResolver = $fontResolver ?? new FontResolver();

        $this->canvas = imagecreatetruecolor($this->canvasWidth, $this->canvasHeight);
        $this->isTransparent = $backgroundColor === null;
        // The transparent background is drawn without blending
        imagealphablending($this->canvas, false);
        imagefilledrectangle($this->canvas, 0, 0, $this->canvasWidth, $this->canvasHeight, $backgroundColor === null ? 0x7F000000 : $this->toGDColor($backgroundColor));
        imagealphablending($this->canvas, true);

        $this->dc = $this->getDefaultDC();
    }

    public function __destruct()
    {
        if ($this->canvas && \PHP_VERSION_ID < 80000) {
            imagedestroy($this->canvas);
        }
    }

    /**
     * Defines the number of device units per millimeter (used by the metric map modes)
     */
    public function setPixelsPerMm(float $pixelsPerMmX, float $pixelsPerMmY): self
    {
        $this->pixelsPerMmX = $pixelsPerMmX;
        $this->pixelsPerMmY = $pixelsPerMmY;

        return $this;
    }

    public function getFontResolver(): FontResolver
    {
        return $this->fontResolver;
    }

    /**
     * Downscales the supersampled canvas into the output image
     *
     * The renderer can not be used after
     *
     * @phpstan-ignore-next-line
     *
     * @return GdImage|resource
     */
    public function render()
    {
        $image = imagecreatetruecolor($this->width, $this->height);
        if ($this->isTransparent) {
            // The alpha channel is copied (without blending) and saved
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }
        imagecopyresampled($image, $this->canvas, 0, 0, 0, 0, $this->width, $this->height, $this->canvasWidth, $this->canvasHeight);
        if (\PHP_VERSION_ID < 80000) {
            imagedestroy($this->canvas);
        }
        $this->canvas = false;

        return $image;
    }

    // ---------------------------------------------------------------------
    // Device context
    // ---------------------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    protected function getDefaultDC(): array
    {
        return [
            'mapMode' => self::MM_TEXT,
            'windowOrg' => [0, 0],
            'windowExt' => [1, 1],
            'viewportOrg' => [0, 0],
            'viewportExt' => [1, 1],
            'xform' => [1.0, 0.0, 0.0, 1.0, 0.0, 0.0],
            'polyFillMode' => self::ALTERNATE,
            'pen' => self::getStockObject(7),
            'brush' => self::getStockObject(0),
            'clip' => null,
            'position' => [0, 0],
            'miterLimit' => 10,
            'arcDirection' => 1,
            'textColor' => [0, 0, 0],
            'bkColor' => [255, 255, 255],
            'bkMode' => 2,
            'textAlign' => 0,
            'font' => self::getStockObject(13),
        ];
    }

    /**
     * Returns a stock object (pen, brush or font)
     *
     * Pens are arrays with keys `type` (`pen`), `style`, `width` (logical units), `geometric` (bool) & `color` ([r, g, b]).
     * Brushes are arrays with keys `type` (`brush`), `style` (1 for a null brush) & `color`.
     * Fonts are arrays with keys `type` (`font`), `height` (logical units), `escapement` (tenths of degrees), `weight`,
     * `italic`, `underline`, `strikeOut`, `charset`, `pitchAndFamily` & `face`.
     *
     * @return array<string, mixed>|null
     */
    public static function getStockObject(int $index): ?array
    {
        switch ($index) {
            case 0: // WHITE_BRUSH
                return ['type' => 'brush', 'style' => 0, 'color' => [255, 255, 255]];
            case 1: // LTGRAY_BRUSH
                return ['type' => 'brush', 'style' => 0, 'color' => [192, 192, 192]];
            case 2: // GRAY_BRUSH
                return ['type' => 'brush', 'style' => 0, 'color' => [128, 128, 128]];
            case 3: // DKGRAY_BRUSH
                return ['type' => 'brush', 'style' => 0, 'color' => [64, 64, 64]];
            case 4: // BLACK_BRUSH
                return ['type' => 'brush', 'style' => 0, 'color' => [0, 0, 0]];
            case 5: // NULL_BRUSH
                return ['type' => 'brush', 'style' => 1, 'color' => [0, 0, 0]];
            case 6: // WHITE_PEN
                return ['type' => 'pen', 'style' => 0, 'width' => 0, 'geometric' => false, 'color' => [255, 255, 255]];
            case 7: // BLACK_PEN
                return ['type' => 'pen', 'style' => 0, 'width' => 0, 'geometric' => false, 'color' => [0, 0, 0]];
            case 8: // NULL_PEN
                return ['type' => 'pen', 'style' => 5, 'width' => 0, 'geometric' => false, 'color' => [0, 0, 0]];
            case 10: // OEM_FIXED_FONT
            case 11: // ANSI_FIXED_FONT
            case 16: // SYSTEM_FIXED_FONT
            case 12: // ANSI_VAR_FONT
            case 13: // SYSTEM_FONT
            case 14: // DEVICE_DEFAULT_FONT
            case 17: // DEFAULT_GUI_FONT
                $isFixed = in_array($index, [10, 11, 16]);

                return [
                    'type' => 'font',
                    'height' => -12,
                    'escapement' => 0,
                    'weight' => $index == 13 ? 700 : 400,
                    'italic' => false,
                    'underline' => false,
                    'strikeOut' => false,
                    'charset' => 0,
                    'pitchAndFamily' => $isFixed ? 0x31 : 0x22,
                    'face' => $isFixed ? 'Courier New' : 'Arial',
                ];
            default:
                return null;
        }
    }

    /**
     * Selects a pen, a brush or a font (see getStockObject() for their structure)
     *
     * @param array<string, mixed> $object
     */
    public function selectObject(array $object): self
    {
        if (in_array($object['type'] ?? null, ['pen', 'brush', 'font'])) {
            $this->dc[$object['type']] = $object;
        }

        return $this;
    }

    public function setMapMode(int $mapMode): self
    {
        $this->dc['mapMode'] = $mapMode;

        return $this;
    }

    public function setWindowOrg(int $x, int $y): self
    {
        $this->dc['windowOrg'] = [$x, $y];

        return $this;
    }

    public function setWindowExt(int $x, int $y): self
    {
        $this->dc['windowExt'] = [$x, $y];

        return $this;
    }

    public function setViewportOrg(int $x, int $y): self
    {
        $this->dc['viewportOrg'] = [$x, $y];

        return $this;
    }

    public function setViewportExt(int $x, int $y): self
    {
        $this->dc['viewportExt'] = [$x, $y];

        return $this;
    }

    public function offsetWindowOrg(int $x, int $y): self
    {
        $this->dc['windowOrg'] = [$this->dc['windowOrg'][0] + $x, $this->dc['windowOrg'][1] + $y];

        return $this;
    }

    public function offsetViewportOrg(int $x, int $y): self
    {
        $this->dc['viewportOrg'] = [$this->dc['viewportOrg'][0] + $x, $this->dc['viewportOrg'][1] + $y];

        return $this;
    }

    public function scaleWindowExt(int $xNum, int $xDenom, int $yNum, int $yDenom): self
    {
        return $this->scaleExtent('windowExt', $xNum, $xDenom, $yNum, $yDenom);
    }

    public function scaleViewportExt(int $xNum, int $xDenom, int $yNum, int $yDenom): self
    {
        return $this->scaleExtent('viewportExt', $xNum, $xDenom, $yNum, $yDenom);
    }

    protected function scaleExtent(string $key, int $xNum, int $xDenom, int $yNum, int $yDenom): self
    {
        if ($xDenom != 0 && $yDenom != 0) {
            $this->dc[$key] = [
                intdiv($this->dc[$key][0] * $xNum, $xDenom),
                intdiv($this->dc[$key][1] * $yNum, $yDenom),
            ];
        }

        return $this;
    }

    /**
     * @param array<float> $xform [eM11, eM12, eM21, eM22, eDx, eDy]
     */
    public function setWorldTransform(array $xform): self
    {
        $this->dc['xform'] = array_values($xform);

        return $this;
    }

    /**
     * @param array<float> $xform [eM11, eM12, eM21, eM22, eDx, eDy]
     * @param int $mode MWT_IDENTITY (1), MWT_LEFTMULTIPLY (2), MWT_RIGHTMULTIPLY (3) or MWT_SET (4)
     */
    public function modifyWorldTransform(array $xform, int $mode): self
    {
        $xform = array_values($xform);
        switch ($mode) {
            case 1: // MWT_IDENTITY
                $this->dc['xform'] = [1.0, 0.0, 0.0, 1.0, 0.0, 0.0];
                break;
            case 2: // MWT_LEFTMULTIPLY
                $this->dc['xform'] = $this->multiplyXForm($xform, $this->dc['xform']);
                break;
            case 3: // MWT_RIGHTMULTIPLY
                $this->dc['xform'] = $this->multiplyXForm($this->dc['xform'], $xform);
                break;
            case 4: // MWT_SET
                $this->dc['xform'] = $xform;
                break;
        }

        return $this;
    }

    public function setPolyFillMode(int $mode): self
    {
        $this->dc['polyFillMode'] = $mode;

        return $this;
    }

    public function setArcDirection(int $direction): self
    {
        $this->dc['arcDirection'] = $direction;

        return $this;
    }

    public function setMiterLimit(float $limit): self
    {
        $this->dc['miterLimit'] = $limit;

        return $this;
    }

    /**
     * @param array<int> $color [r, g, b]
     */
    public function setTextColor(array $color): self
    {
        $this->dc['textColor'] = array_values($color);

        return $this;
    }

    /**
     * @param array<int> $color [r, g, b]
     */
    public function setBkColor(array $color): self
    {
        $this->dc['bkColor'] = array_values($color);

        return $this;
    }

    /**
     * @param int $mode TRANSPARENT (1) or OPAQUE (2)
     */
    public function setBkMode(int $mode): self
    {
        $this->dc['bkMode'] = $mode;

        return $this;
    }

    public function setTextAlign(int $align): self
    {
        $this->dc['textAlign'] = $align;

        return $this;
    }

    public function saveDC(): self
    {
        $this->dcStack[] = $this->dc;

        return $this;
    }

    /**
     * @param int $relative Negative : relative to the last saved context, positive : index of the saved context
     */
    public function restoreDC(int $relative): self
    {
        $index = $relative < 0 ? count($this->dcStack) + $relative : $relative - 1;
        if (isset($this->dcStack[$index])) {
            $this->dc = $this->dcStack[$index];
            $this->dcStack = array_slice($this->dcStack, 0, $index);
        }

        return $this;
    }

    // ---------------------------------------------------------------------
    // Coordinates
    // ---------------------------------------------------------------------

    /**
     * @param array<float> $first
     * @param array<float> $second
     *
     * @return array<float>
     */
    protected function multiplyXForm(array $first, array $second): array
    {
        return [
            $first[0] * $second[0] + $first[1] * $second[2],
            $first[0] * $second[1] + $first[1] * $second[3],
            $first[2] * $second[0] + $first[3] * $second[2],
            $first[2] * $second[1] + $first[3] * $second[3],
            $first[4] * $second[0] + $first[5] * $second[2] + $second[4],
            $first[4] * $second[1] + $first[5] * $second[3] + $second[5],
        ];
    }

    /**
     * Returns the matrix converting logical coordinates to canvas coordinates
     * (world transform, then page transform, then device to canvas)
     *
     * @return array<float>
     */
    protected function getMatrix(): array
    {
        $windowExt = $this->dc['windowExt'];
        $viewportExt = $this->dc['viewportExt'];

        switch ($this->dc['mapMode']) {
            case self::MM_LOMETRIC:
            case self::MM_HIMETRIC:
            case self::MM_LOENGLISH:
            case self::MM_HIENGLISH:
            case self::MM_TWIPS:
                $mmPerUnit = [
                    self::MM_LOMETRIC => 0.1,
                    self::MM_HIMETRIC => 0.01,
                    self::MM_LOENGLISH => 0.254,
                    self::MM_HIENGLISH => 0.0254,
                    self::MM_TWIPS => 25.4 / 1440,
                ][$this->dc['mapMode']];
                $scaleX = $mmPerUnit * $this->pixelsPerMmX;
                $scaleY = -$mmPerUnit * $this->pixelsPerMmY;
                break;
            case self::MM_ISOTROPIC:
            case self::MM_ANISOTROPIC:
                $scaleX = $windowExt[0] != 0 ? $viewportExt[0] / $windowExt[0] : 1;
                $scaleY = $windowExt[1] != 0 ? $viewportExt[1] / $windowExt[1] : 1;
                if ($this->dc['mapMode'] == self::MM_ISOTROPIC) {
                    $scale = min(abs($scaleX), abs($scaleY));
                    $scaleX = $scaleX < 0 ? -$scale : $scale;
                    $scaleY = $scaleY < 0 ? -$scale : $scale;
                }
                break;
            case self::MM_TEXT:
            default:
                $scaleX = $scaleY = 1;
                break;
        }

        $offsetX = $this->dc['viewportOrg'][0] - $this->dc['windowOrg'][0] * $scaleX - $this->originX;
        $offsetY = $this->dc['viewportOrg'][1] - $this->dc['windowOrg'][1] * $scaleY - $this->originY;

        list($m11, $m12, $m21, $m22, $dx, $dy) = $this->dc['xform'];
        $deviceScaleX = $this->deviceScaleX;
        $deviceScaleY = $this->deviceScaleY;

        return [
            $m11 * $scaleX * $deviceScaleX,
            $m12 * $scaleY * $deviceScaleY,
            $m21 * $scaleX * $deviceScaleX,
            $m22 * $scaleY * $deviceScaleY,
            ($dx * $scaleX + $offsetX) * $deviceScaleX,
            ($dy * $scaleY + $offsetY) * $deviceScaleY,
        ];
    }

    /**
     * @param array<array<float>> $points Logical coordinates
     *
     * @return array<array<float>> Canvas coordinates
     */
    protected function toCanvasPoints(array $points): array
    {
        list($m11, $m12, $m21, $m22, $dx, $dy) = $this->getMatrix();

        $result = [];
        foreach ($points as $point) {
            $result[] = [
                $point[0] * $m11 + $point[1] * $m21 + $dx,
                $point[0] * $m12 + $point[1] * $m22 + $dy,
            ];
        }

        return $result;
    }

    /**
     * @return array<float>
     */
    protected function deviceToCanvas(float $x, float $y): array
    {
        return [
            ($x - $this->originX) * $this->deviceScaleX,
            ($y - $this->originY) * $this->deviceScaleY,
        ];
    }

    /**
     * @param array<array<array<float>>> $polygons Canvas coordinates
     *
     * @return array<int, array<array<int>>>
     */
    protected function rasterize(array $polygons, bool $nonZero): array
    {
        return Rasterizer::rasterize($polygons, $nonZero, $this->canvasWidth, $this->canvasHeight);
    }

    /**
     * @return array<array<float>> Canvas coordinates
     */
    protected function getRectanglePoints(float $left, float $top, float $right, float $bottom): array
    {
        return $this->toCanvasPoints([[$left, $top], [$right, $top], [$right, $bottom], [$left, $bottom]]);
    }

    /**
     * @return array<array<float>> Canvas coordinates
     */
    protected function getEllipsePoints(float $left, float $top, float $right, float $bottom): array
    {
        $centerX = ($left + $right) / 2;
        $centerY = ($top + $bottom) / 2;
        $radiusX = ($right - $left) / 2;
        $radiusY = ($bottom - $top) / 2;

        $points = [];
        for ($i = 0; $i < 64; ++$i) {
            $angle = 2 * M_PI * $i / 64;
            $points[] = [$centerX + $radiusX * cos($angle), $centerY + $radiusY * sin($angle)];
        }

        return $this->toCanvasPoints($points);
    }

    /**
     * @return array<array<float>> Canvas coordinates
     */
    protected function getRoundRectPoints(float $left, float $top, float $right, float $bottom, float $cornerWidth, float $cornerHeight): array
    {
        $radiusX = min(abs($cornerWidth) / 2, abs($right - $left) / 2);
        $radiusY = min(abs($cornerHeight) / 2, abs($bottom - $top) / 2);
        $corners = [
            [$right - $radiusX, $top + $radiusY, -M_PI / 2],
            [$right - $radiusX, $bottom - $radiusY, 0],
            [$left + $radiusX, $bottom - $radiusY, M_PI / 2],
            [$left + $radiusX, $top + $radiusY, M_PI],
        ];

        $points = [];
        foreach ($corners as $corner) {
            for ($i = 0; $i <= 16; ++$i) {
                $angle = $corner[2] + M_PI / 2 * $i / 16;
                $points[] = [$corner[0] + $radiusX * cos($angle), $corner[1] + $radiusY * sin($angle)];
            }
        }

        return $this->toCanvasPoints($points);
    }

    /**
     * Returns the points of an elliptic arc, from the radial through the start point to the radial through the end point
     *
     * @return array<array<float>> Logical coordinates
     */
    protected function getArcPoints(float $left, float $top, float $right, float $bottom, float $startX, float $startY, float $endX, float $endY): array
    {
        $centerX = ($left + $right) / 2;
        $centerY = ($top + $bottom) / 2;
        $radiusX = ($right - $left) / 2;
        $radiusY = ($bottom - $top) / 2;
        if ($radiusX == 0 || $radiusY == 0) {
            return [[$startX, $startY], [$endX, $endY]];
        }

        $startAngle = atan2(($startY - $centerY) * $radiusX, ($startX - $centerX) * $radiusY);
        $endAngle = atan2(($endY - $centerY) * $radiusX, ($endX - $centerX) * $radiusY);

        // The direction is defined on the device : counterclockwise is a decreasing angle if the y axis goes down
        list($m11, $m12, $m21, $m22) = $this->getMatrix();
        $isCounterClockwise = $this->dc['arcDirection'] != 2;
        $sign = (($m11 * $m22 - $m12 * $m21) > 0) == $isCounterClockwise ? -1 : 1;

        $sweep = $endAngle - $startAngle;
        if ($sign < 0) {
            while ($sweep >= 0) {
                $sweep -= 2 * M_PI;
            }
        } else {
            while ($sweep <= 0) {
                $sweep += 2 * M_PI;
            }
        }

        $steps = (int) max(4, ceil(abs($sweep) / (2 * M_PI) * 64));
        $points = [];
        for ($i = 0; $i <= $steps; ++$i) {
            $angle = $startAngle + $sweep * $i / $steps;
            $points[] = [$centerX + $radiusX * cos($angle), $centerY + $radiusY * sin($angle)];
        }

        return $points;
    }

    // ---------------------------------------------------------------------
    // Shapes
    // ---------------------------------------------------------------------

    public function moveTo(float $x, float $y): self
    {
        $this->dc['position'] = [$x, $y];
        if ($this->inPath) {
            $this->path[] = ['points' => $this->toCanvasPoints([$this->dc['position']]), 'closed' => false];
        }

        return $this;
    }

    public function lineTo(float $x, float $y): self
    {
        $this->drawTo([[$x, $y]], false);

        return $this;
    }

    /**
     * Draws lines (or Bézier curves) from the current position
     *
     * @param array<array<float>> $points
     */
    public function polylineTo(array $points, bool $isBezier = false): self
    {
        $this->drawTo($points, $isBezier);

        return $this;
    }

    /**
     * @param array<array<float>> $points
     */
    public function polyline(array $points): self
    {
        $this->drawFigures([['points' => $this->toCanvasPoints($points), 'closed' => false]], false);

        return $this;
    }

    /**
     * @param array<array<float>> $points
     */
    public function polygon(array $points): self
    {
        $this->drawFigures([['points' => $this->toCanvasPoints($points), 'closed' => true]], true);

        return $this;
    }

    /**
     * @param array<array<float>> $points Start point followed by groups of 3 points (2 control points + end point)
     */
    public function polyBezier(array $points): self
    {
        $this->drawFigures([['points' => Rasterizer::flattenBeziers($this->toCanvasPoints($points)), 'closed' => false]], false);

        return $this;
    }

    /**
     * Draws polygons (filled together) or polylines
     *
     * @param array<array<array<float>>> $polygons
     */
    public function polyPolygon(array $polygons, bool $closed = true): self
    {
        $figures = [];
        foreach ($polygons as $points) {
            $figures[] = ['points' => $this->toCanvasPoints($points), 'closed' => $closed];
        }
        $this->drawFigures($figures, $closed);

        return $this;
    }

    public function rectangle(float $left, float $top, float $right, float $bottom): self
    {
        $this->drawFigures([['points' => $this->getRectanglePoints($left, $top, $right, $bottom), 'closed' => true]], true);

        return $this;
    }

    public function roundRect(float $left, float $top, float $right, float $bottom, float $cornerWidth, float $cornerHeight): self
    {
        $points = $this->getRoundRectPoints($left, $top, $right, $bottom, $cornerWidth, $cornerHeight);
        $this->drawFigures([['points' => $points, 'closed' => true]], true);

        return $this;
    }

    public function ellipse(float $left, float $top, float $right, float $bottom): self
    {
        $this->drawFigures([['points' => $this->getEllipsePoints($left, $top, $right, $bottom), 'closed' => true]], true);

        return $this;
    }

    public function arc(float $left, float $top, float $right, float $bottom, float $startX, float $startY, float $endX, float $endY): self
    {
        $points = $this->getArcPoints($left, $top, $right, $bottom, $startX, $startY, $endX, $endY);
        $this->drawFigures([['points' => $this->toCanvasPoints($points), 'closed' => false]], false);

        return $this;
    }

    /**
     * Draws a line from the current position to the start of the arc, then the arc
     */
    public function arcTo(float $left, float $top, float $right, float $bottom, float $startX, float $startY, float $endX, float $endY): self
    {
        $this->drawTo($this->getArcPoints($left, $top, $right, $bottom, $startX, $startY, $endX, $endY), false);

        return $this;
    }

    public function chord(float $left, float $top, float $right, float $bottom, float $startX, float $startY, float $endX, float $endY): self
    {
        $points = $this->getArcPoints($left, $top, $right, $bottom, $startX, $startY, $endX, $endY);
        $this->drawFigures([['points' => $this->toCanvasPoints($points), 'closed' => true]], true);

        return $this;
    }

    public function pie(float $left, float $top, float $right, float $bottom, float $startX, float $startY, float $endX, float $endY): self
    {
        $points = $this->getArcPoints($left, $top, $right, $bottom, $startX, $startY, $endX, $endY);
        $points[] = [($left + $right) / 2, ($top + $bottom) / 2];
        $this->drawFigures([['points' => $this->toCanvasPoints($points), 'closed' => true]], true);

        return $this;
    }

    /**
     * Draws a line from the current position to the start of the arc, then the arc of a circle
     *
     * @param float $startAngle Degrees, counterclockwise
     * @param float $sweepAngle Degrees, counterclockwise
     */
    public function angleArc(float $x, float $y, float $radius, float $startAngle, float $sweepAngle): self
    {
        $points = [];
        $steps = (int) max(2, min(128, ceil(abs($sweepAngle) / 3)));
        for ($i = 0; $i <= $steps; ++$i) {
            $angle = deg2rad($startAngle + $sweepAngle * $i / $steps);
            $points[] = [$x + $radius * cos($angle), $y - $radius * sin($angle)];
        }
        $this->drawTo($points, false);

        return $this;
    }

    /**
     * Draws a device pixel
     *
     * @param array<int> $color [r, g, b]
     */
    public function setPixel(float $x, float $y, array $color): self
    {
        list($point) = $this->toCanvasPoints([[$x, $y]]);
        $half = $this->supersampling / 2;
        $square = [
            [$point[0] - $half, $point[1] - $half],
            [$point[0] + $half, $point[1] - $half],
            [$point[0] + $half, $point[1] + $half],
            [$point[0] - $half, $point[1] + $half],
        ];
        $this->drawSpans($this->rasterize([$square], false), $color);

        return $this;
    }

    /**
     * Fills a rectangle with a raster operation without source (PATCOPY, BLACKNESS or WHITENESS)
     */
    public function patBlt(float $left, float $top, float $right, float $bottom, int $rop): self
    {
        if ($rop == self::ROP_NOP) {
            return $this;
        }
        $spans = $this->rasterize([$this->getRectanglePoints($left, $top, $right, $bottom)], false);
        $brush = $this->dc['brush'];
        $hasBrush = $brush && $brush['style'] != 1;
        if ($rop == self::ROP_PATCOPY) {
            if ($hasBrush) {
                $this->drawPaint($spans, $brush);
            }

            return $this;
        }

        // Other raster operations : the pattern is the color of the brush (black for the null brush)
        $pattern = $hasBrush ? self::toRGB($brush['color']) : 0x000000;
        if (!self::isRopDependent($rop, 1)) {
            $color = self::applyRop($rop, $pattern, 0, 0);
            $this->drawSpans($spans, [($color >> 16) & 0xFF, ($color >> 8) & 0xFF, $color & 0xFF]);

            return $this;
        }
        if ($this->dc['clip'] !== null) {
            $spans = Rasterizer::combineSpans($spans, $this->dc['clip'], Rasterizer::RGN_AND);
        }
        foreach ($spans as $y => $row) {
            foreach ($row as $span) {
                for ($x = $span[0]; $x <= $span[1]; ++$x) {
                    imagesetpixel($this->canvas, $x, $y, self::applyRop($rop, $pattern, 0, $this->getDestinationColor($x, $y)));
                }
            }
        }

        return $this;
    }

    /**
     * Applies a ternary raster operation to colors (0xRRGGBB) : the third byte of the operation is a truth table
     * of the pattern (P), the source (S) & the destination (D)
     */
    public static function applyRop(int $rop, int $pattern, int $source, int $destination): int
    {
        $table = ($rop >> 16) & 0xFF;
        // Common operations
        switch ($table) {
            case 0x88: // SRCAND
                return $source & $destination;
            case 0xEE: // SRCPAINT
                return $source | $destination;
            case 0x66: // SRCINVERT
                return $source ^ $destination;
            case 0x44: // SRCERASE
                return $source & ~$destination & 0xFFFFFF;
            case 0x33: // NOTSRCCOPY
                return ~$source & 0xFFFFFF;
            case 0x55: // DSTINVERT
                return ~$destination & 0xFFFFFF;
            case 0x5A: // PATINVERT
                return $pattern ^ $destination;
            case 0xCC: // SRCCOPY
                return $source;
        }
        $result = 0;
        for ($bit = 0; $bit < 8; ++$bit) {
            if ($table & (1 << $bit)) {
                $result |= (($bit & 4) ? $pattern : ~$pattern)
                    & (($bit & 2) ? $source : ~$source)
                    & (($bit & 1) ? $destination : ~$destination);
            }
        }

        return $result & 0xFFFFFF;
    }

    /**
     * Returns the color (0xRRGGBB) of a pixel of the canvas for a raster operation : a transparent background is white (like paper)
     */
    protected function getDestinationColor(int $x, int $y): int
    {
        $color = imagecolorat($this->canvas, $x, $y);
        $alpha = ($color >> 24) & 0x7F;
        if ($alpha == 0) {
            return $color & 0xFFFFFF;
        }
        // Composition over white
        $ratio = $alpha / 127;
        $red = (int) round((($color >> 16) & 0xFF) * (1 - $ratio) + 255 * $ratio);
        $green = (int) round((($color >> 8) & 0xFF) * (1 - $ratio) + 255 * $ratio);
        $blue = (int) round(($color & 0xFF) * (1 - $ratio) + 255 * $ratio);

        return ($red << 16) | ($green << 8) | $blue;
    }

    /**
     * Returns if a raster operation depends on the pattern (4), the source (2) or the destination (1)
     */
    public static function isRopDependent(int $rop, int $operand): bool
    {
        $table = ($rop >> 16) & 0xFF;
        for ($bit = 0; $bit < 8; ++$bit) {
            if ((($table >> $bit) & 1) != (($table >> ($bit ^ $operand)) & 1)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int|float> $color [r, g, b]
     */
    protected static function toRGB(array $color): int
    {
        return ((int) $color[0] << 16) | ((int) $color[1] << 8) | (int) $color[2];
    }

    /**
     * Fills rectangles (a region) with a brush, or with the current brush
     *
     * @param array<array<float>> $rectangles [[left, top, right, bottom], ...]
     * @param array<string, mixed>|null $brush See getStockObject()
     */
    public function fillRectangles(array $rectangles, ?array $brush = null): self
    {
        $brush = $brush ?? $this->dc['brush'];
        if (!$brush || $brush['style'] == 1) {
            return $this;
        }
        $polygons = [];
        foreach ($rectangles as $rectangle) {
            $polygons[] = $this->getRectanglePoints(...$rectangle);
        }
        $this->drawPaint($this->rasterize($polygons, true), $brush);

        return $this;
    }

    /**
     * Fills an area with the current brush
     *
     * @param array<int> $color [r, g, b]
     * @param bool $isSurface FLOODFILLSURFACE : the area has the color $color, else FLOODFILLBORDER : the area is bounded by the color $color
     */
    public function floodFill(float $x, float $y, array $color, bool $isSurface): self
    {
        if (!$this->dc['brush'] || $this->dc['brush']['style'] == 1) {
            return $this;
        }
        list($point) = $this->toCanvasPoints([[$x, $y]]);
        $pointX = (int) floor($point[0]);
        $pointY = (int) floor($point[1]);
        if ($pointX < 0 || $pointY < 0 || $pointX >= $this->canvasWidth || $pointY >= $this->canvasHeight) {
            return $this;
        }
        $brushColor = $this->dc['brush']['color'];
        $gdBrushColor = ($brushColor[0] << 16) | ($brushColor[1] << 8) | $brushColor[2];
        $gdColor = ($color[0] << 16) | ($color[1] << 8) | $color[2];
        if ($isSurface) {
            if (imagecolorat($this->canvas, $pointX, $pointY) == $gdColor) {
                imagefill($this->canvas, $pointX, $pointY, $gdBrushColor);
            }
        } else {
            imagefilltoborder($this->canvas, $pointX, $pointY, $gdColor, $gdBrushColor);
        }

        return $this;
    }

    /**
     * Adds the figures to the current path, or draws them
     *
     * @param array<array{points: array<array<float>>, closed: bool}> $figures
     */
    protected function drawFigures(array $figures, bool $fill): void
    {
        if ($this->inPath) {
            foreach ($figures as $figure) {
                $this->path[] = $figure;
            }

            return;
        }
        if ($fill) {
            $this->fillFigures($figures);
        }
        $this->strokeFigures($figures);
    }

    /**
     * Draws lines or bezier curves from the current position (LineTo, PolylineTo, PolyBezierTo)
     *
     * @param array<array<float>> $points Logical coordinates
     */
    protected function drawTo(array $points, bool $isBezier): void
    {
        if (empty($points)) {
            return;
        }
        $canvasPoints = $this->toCanvasPoints(array_merge([$this->dc['position']], $points));
        if ($isBezier) {
            $canvasPoints = Rasterizer::flattenBeziers($canvasPoints);
        }
        $this->dc['position'] = $points[count($points) - 1];

        if ($this->inPath) {
            $last = count($this->path) - 1;
            if ($last < 0 || $this->path[$last]['closed']) {
                $this->path[] = ['points' => $canvasPoints, 'closed' => false];
            } else {
                array_shift($canvasPoints);
                $this->path[$last]['points'] = array_merge($this->path[$last]['points'], $canvasPoints);
            }

            return;
        }
        $this->strokeFigures([['points' => $canvasPoints, 'closed' => false]]);
    }

    /**
     * @param array<array{points: array<array<float>>, closed: bool}> $figures
     */
    protected function fillFigures(array $figures): void
    {
        $brush = $this->dc['brush'];
        if (!$brush || $brush['style'] == 1) {
            return;
        }

        $polygons = [];
        foreach ($figures as $figure) {
            if (count($figure['points']) >= 3) {
                $polygons[] = $figure['points'];
            }
        }

        $this->drawPaint($this->rasterize($polygons, $this->dc['polyFillMode'] == self::WINDING), $brush);
    }

    /**
     * @param array<array{points: array<array<float>>, closed: bool}> $figures
     */
    protected function strokeFigures(array $figures): void
    {
        $pen = $this->dc['pen'];
        if (!$pen || ($pen['style'] & 0x0F) == 5) {
            return;
        }

        // Pen width (in canvas units)
        // Cosmetic pens are one pixel wide
        $width = $this->supersampling;
        if ($pen['geometric'] && $pen['width'] > 0) {
            list($m11, $m12, $m21, $m22) = $this->getMatrix();
            $width = max($width, $pen['width'] * sqrt(abs($m11 * $m22 - $m12 * $m21)));
        }
        $endCap = $pen['endCap'] ?? $pen['style'] & 0x0F00;
        $startCap = $pen['startCap'] ?? $endCap;
        $join = $pen['geometric'] ? $pen['style'] & 0xF000 : 0x0000;

        // Shapes of the caps (like arrows) at the ends of open figures
        $capPolygons = [];
        foreach ($figures as $figure) {
            if ($figure['closed'] || count($figure['points']) < 2) {
                continue;
            }
            if (!empty($pen['startCapShape'])) {
                $capPolygons[] = $this->getCapPolygon(array_reverse($figure['points']), $pen['startCapShape'], $width);
            }
            if (!empty($pen['endCapShape'])) {
                $capPolygons[] = $this->getCapPolygon($figure['points'], $pen['endCapShape'], $width);
            }
        }

        // Compound lines : parallel lines, defined by pairs of positions in the width (from 0 to 1)
        $strokes = [[$figures, $width]];
        $compound = $pen['compound'] ?? [];
        if (count($compound) >= 2) {
            $strokes = [];
            for ($i = 0; $i + 1 < count($compound); $i += 2) {
                $offset = (($compound[$i] + $compound[$i + 1]) / 2 - 0.5) * $width;
                $offsetFigures = [];
                foreach ($figures as $figure) {
                    $offsetFigures[] = ['points' => $this->getOffsetPoints($figure['points'], $figure['closed'], $offset), 'closed' => $figure['closed']];
                }
                $strokes[] = [$offsetFigures, max($this->supersampling, ($compound[$i + 1] - $compound[$i]) * $width)];
            }
        }

        $polygons = array_filter($capPolygons);
        foreach ($strokes as list($strokeFigures, $strokeWidth)) {
            // Dashes : each dash is an open figure
            if (!empty($pen['dashes'])) {
                $dashed = [];
                foreach ($strokeFigures as $figure) {
                    $dashed = array_merge($dashed, $this->getDashes($figure, $pen['dashes'], $pen['dashOffset'] ?? 0, $width));
                }
                $strokeFigures = $dashed;
                $startCap = $endCap = $pen['dashCap'] ?? 0x0200;
            }
            foreach ($strokeFigures as $figure) {
                $polygons = array_merge($polygons, Rasterizer::getStrokePolygons($figure['points'], $figure['closed'], $strokeWidth / 2, $endCap, $join, $this->dc['miterLimit'], $startCap));
            }
        }

        $this->drawPaint($this->rasterize($polygons, true), $pen);
    }

    /**
     * Returns the points of a polyline moved perpendicularly (on the left of the direction for a positive offset)
     *
     * @param array<array<float>> $points
     *
     * @return array<array<float>>
     */
    protected function getOffsetPoints(array $points, bool $closed, float $offset): array
    {
        $count = count($points);
        if ($offset == 0 || $count < 2) {
            return $points;
        }
        // Unit normals of the segments
        $normals = [];
        for ($i = 0; $i < $count; ++$i) {
            $next = $points[($i + 1) % $count];
            $length = hypot($next[0] - $points[$i][0], $next[1] - $points[$i][1]);
            $normals[$i] = $length > 0 ? [($points[$i][1] - $next[1]) / $length, ($next[0] - $points[$i][0]) / $length] : null;
        }

        $result = [];
        for ($i = 0; $i < $count; ++$i) {
            $incoming = $i > 0 || $closed ? $normals[($i - 1 + $count) % $count] : null;
            $outgoing = $i < $count - 1 || $closed ? $normals[$i] : null;
            $normal = $incoming ?? $outgoing ?? [0, 0];
            $scale = 1.0;
            if ($incoming && $outgoing) {
                // Miter of the two normals (limited for sharp angles)
                $sum = [$incoming[0] + $outgoing[0], $incoming[1] + $outgoing[1]];
                $length = hypot($sum[0], $sum[1]);
                if ($length > 0) {
                    $normal = [$sum[0] / $length, $sum[1] / $length];
                    $scale = min(4.0, 1 / max(0.25, $normal[0] * $outgoing[0] + $normal[1] * $outgoing[1]));
                }
            }
            $result[] = [$points[$i][0] + $normal[0] * $offset * $scale, $points[$i][1] + $normal[1] * $offset * $scale];
        }

        return $result;
    }

    /**
     * Splits a figure into dashes
     *
     * @param array{points: array<array<float>>, closed: bool} $figure
     * @param array<float> $dashes Lengths of dashes & spaces, in pen widths
     *
     * @return array<array{points: array<array<float>>, closed: bool}>
     */
    protected function getDashes(array $figure, array $dashes, float $offset, float $width): array
    {
        $lengths = [];
        foreach ($dashes as $dash) {
            $lengths[] = max(0.01, $dash) * $width;
        }
        $points = $figure['points'];
        if ($figure['closed'] && count($points) > 1) {
            $points[] = $points[0];
        }

        $result = [];
        $index = 0;
        $remaining = $lengths[0];
        // The offset moves the start of the pattern
        $offset *= $width;
        while ($offset > 0 && $lengths) {
            if ($offset < $remaining) {
                $remaining -= $offset;
                break;
            }
            $offset -= $remaining;
            $index = ($index + 1) % count($lengths);
            $remaining = $lengths[$index];
        }

        $current = ($index % 2 == 0) ? [$points[0] ?? [0, 0]] : null;
        for ($i = 0; $i + 1 < count($points); ++$i) {
            list($x1, $y1) = $points[$i];
            list($x2, $y2) = $points[$i + 1];
            $length = hypot($x2 - $x1, $y2 - $y1);
            $position = 0;
            while ($length - $position > $remaining) {
                $position += $remaining;
                $point = [$x1 + ($x2 - $x1) * $position / $length, $y1 + ($y2 - $y1) * $position / $length];
                if ($current !== null) {
                    $current[] = $point;
                    $result[] = ['points' => $current, 'closed' => false];
                    $current = null;
                } else {
                    $current = [$point];
                }
                $index = ($index + 1) % count($lengths);
                $remaining = $lengths[$index];
            }
            $remaining -= $length - $position;
            if ($current !== null) {
                $current[] = [$x2, $y2];
            }
        }
        if ($current !== null && count($current) > 1) {
            $result[] = ['points' => $current, 'closed' => false];
        }

        return $result;
    }

    /**
     * Returns the polygon of a cap (like an arrow) at the end of a line
     *
     * The shape is defined in pen widths : the end of the line is the origin, the y axis follows the direction of the line
     *
     * @param array<array<float>> $points Points of the line (canvas coordinates), the cap is drawn at the last one
     * @param array<array<float>> $shape
     *
     * @return array<array<float>>
     */
    protected function getCapPolygon(array $points, array $shape, float $width): array
    {
        $end = array_pop($points);
        // Direction of the last segment which is not empty
        do {
            $from = array_pop($points);
            $length = $from === null ? 0 : hypot($end[0] - $from[0], $end[1] - $from[1]);
        } while ($from !== null && $length == 0);
        if ($from === null || count($shape) < 3) {
            return [];
        }
        $ux = ($end[0] - $from[0]) / $length;
        $uy = ($end[1] - $from[1]) / $length;

        $polygon = [];
        foreach ($shape as list($x, $y)) {
            $polygon[] = [$end[0] + ($ux * $y - $uy * $x) * $width, $end[1] + ($uy * $y + $ux * $x) * $width];
        }

        return Rasterizer::getSignedArea($polygon) < 0 ? array_reverse($polygon) : $polygon;
    }

    /**
     * Draws spans with a pen or a brush : its color, or its shader
     *
     * A shader is a callable returning the color [r, g, b, a] of a point, in logical coordinates (`space` : `logical`),
     * or in pixels of the image (`space` : `device`)
     *
     * @param array<int, array<array<int>>> $spans
     * @param array<string, mixed> $paint
     */
    protected function drawPaint(array $spans, array $paint): void
    {
        // Pattern brush : the bitmap is tiled (one pixel of the bitmap is one pixel of the image)
        if (!empty($paint['pattern']['pixels'])) {
            $this->drawPattern($spans, $paint['pattern']);

            return;
        }
        // Hatched brush (BS_HATCHED) : lines of the color of the brush, on the background color (OPAQUE background mode)
        if (($paint['style'] ?? 0) == 2 && isset($paint['hatch']) && !isset($paint['shader'])) {
            $hatch = $paint['hatch'];
            $foreground = $paint['color'];
            $background = $this->dc['bkMode'] == 2 ? $this->dc['bkColor'] : [0, 0, 0, 0];
            $paint['space'] = 'device';
            $paint['shader'] = function (float $x, float $y) use ($hatch, $foreground, $background): array {
                return self::isHatchForeground($hatch, (int) floor($x), (int) floor($y)) ? $foreground : $background;
            };
        }
        if (!isset($paint['shader'])) {
            $this->drawSpans($spans, $paint['color']);

            return;
        }

        $shader = $paint['shader'];
        if (($paint['space'] ?? 'logical') == 'device') {
            $supersampling = $this->supersampling;
            $this->drawShadedSpans($spans, function (float $x, float $y) use ($shader, $supersampling): array {
                return $shader($x / $supersampling, $y / $supersampling);
            });

            return;
        }

        list($m11, $m12, $m21, $m22, $dx, $dy) = $this->getMatrix();
        $determinant = $m11 * $m22 - $m21 * $m12;
        if ($determinant == 0) {
            return;
        }
        $this->drawShadedSpans($spans, function (float $x, float $y) use ($shader, $m11, $m12, $m21, $m22, $dx, $dy, $determinant): array {
            $x -= $dx;
            $y -= $dy;

            return $shader(($x * $m22 - $y * $m21) / $determinant, ($y * $m11 - $x * $m12) / $determinant);
        });
    }

    /**
     * Returns the dashes of a GDI pen (in pen widths, see the key `dashes` of pens), or null for a solid pen
     *
     * @param int $style Style of the pen : PS_DASH (1), PS_DOT (2), PS_DASHDOT (3), PS_DASHDOTDOT (4), PS_USERSTYLE (7), PS_ALTERNATE (8)
     * @param bool $isCosmetic If the pen is cosmetic (one pixel wide) : the dashes are in pixels
     * @param array<float> $userStyle Lengths of dashes & spaces of PS_USERSTYLE, in logical units (geometric pens) or pixels (cosmetic pens)
     *
     * @return array<float>|null
     */
    public static function getPenDashes(int $style, bool $isCosmetic, float $width, array $userStyle = []): ?array
    {
        switch ($style & 0x0F) {
            case 1:
                return $isCosmetic ? [18, 6] : [3, 1];
            case 2:
                return $isCosmetic ? [3, 3] : [1, 1];
            case 3:
                return $isCosmetic ? [9, 6, 3, 6] : [3, 1, 1, 1];
            case 4:
                return $isCosmetic ? [9, 3, 3, 3, 3, 3] : [3, 1, 1, 1, 1, 1];
            case 7:
                if (count($userStyle) < 2) {
                    return null;
                }

                return array_map(function (float $length) use ($isCosmetic, $width): float {
                    return $isCosmetic || $width <= 0 ? $length : $length / $width;
                }, $userStyle);
            case 8:
                return [1, 1];
            default:
                return null;
        }
    }

    /**
     * Returns if a pixel of the image is in the lines of a hatch (8x8 pattern)
     *
     * @param int $style Horizontal (0), vertical (1), forward diagonal (2), backward diagonal (3), cross (4), diagonal cross (5),
     *                   percent of foreground (6 to 17, EMF+ only) or other EMF+ styles (pattern of 50%)
     */
    public static function isHatchForeground(int $style, int $x, int $y): bool
    {
        $x = abs($x);
        $y = abs($y);
        switch ($style) {
            case 0:
                return $y % 8 == 0;
            case 1:
                return $x % 8 == 0;
            case 2:
                return ($x - $y) % 8 == 0;
            case 3:
                return ($x + $y) % 8 == 0;
            case 4:
                return $x % 8 == 0 || $y % 8 == 0;
            case 5:
                return ($x - $y) % 8 == 0 || ($x + $y) % 8 == 0;
            default:
                // Ordered dither
                $percent = [6 => 5, 7 => 10, 8 => 20, 9 => 25, 10 => 30, 11 => 40, 12 => 50, 13 => 60, 14 => 70, 15 => 75, 16 => 80, 17 => 90][$style] ?? 50;

                return self::BAYER[$y % 8][$x % 8] < $percent * 64 / 100;
        }
    }

    /**
     * Fills spans with a tiled bitmap
     *
     * @param array<int, array<array<int>>> $spans
     * @param array{width: int, height: int, pixels: array<array<int>>} $pattern
     */
    protected function drawPattern(array $spans, array $pattern): void
    {
        if ($this->dc['clip'] !== null) {
            $spans = Rasterizer::combineSpans($spans, $this->dc['clip'], Rasterizer::RGN_AND);
        }
        $size = $this->supersampling;
        $tile = imagecreatetruecolor($pattern['width'] * $size, $pattern['height'] * $size);
        imagealphablending($tile, false);
        foreach ($pattern['pixels'] as $y => $line) {
            foreach ($line as $x => $pixel) {
                imagefilledrectangle($tile, $x * $size, $y * $size, ($x + 1) * $size - 1, ($y + 1) * $size - 1, $pixel);
            }
        }
        imagesettile($this->canvas, $tile);
        foreach ($spans as $y => $row) {
            foreach ($row as $span) {
                imagefilledrectangle($this->canvas, $span[0], $y, $span[1], $y, IMG_COLOR_TILED);
            }
        }
        if (\PHP_VERSION_ID < 80000) {
            imagedestroy($tile);
        }
    }

    /**
     * Converts a color [r, g, b] or [r, g, b, a] (alpha from 0 : transparent to 255 : opaque) to a GD color
     *
     * @param array<int|float> $color
     */
    protected function toGDColor(array $color): int
    {
        $gdColor = ((int) $color[0] << 16) | ((int) $color[1] << 8) | (int) $color[2];
        if (isset($color[3]) && $color[3] < 255) {
            $gdColor |= (127 - (int) round(max(0, $color[3]) * 127 / 255)) << 24;
        }

        return $gdColor;
    }

    /**
     * @param array<int, array<array<int>>> $spans
     * @param array<int> $color
     */
    protected function drawSpans(array $spans, array $color): void
    {
        if (isset($color[3]) && $color[3] <= 0) {
            return;
        }
        if ($this->dc['clip'] !== null) {
            $spans = Rasterizer::combineSpans($spans, $this->dc['clip'], Rasterizer::RGN_AND);
        }
        $gdColor = $this->toGDColor($color);
        foreach ($spans as $y => $row) {
            foreach ($row as $span) {
                imagefilledrectangle($this->canvas, $span[0], $y, $span[1], $y, $gdColor);
            }
        }
    }

    // ---------------------------------------------------------------------
    // Figures (paths of EMF+ files)
    // ---------------------------------------------------------------------

    /**
     * Fills and/or strokes figures with the current brush & pen
     *
     * @param array<array{points: array<array<float>>, types: array<int>, closed: bool}> $figures Logical points and their type :
     *                                                                                            0 (start), 1 (line) or 3 (Bézier control or end point)
     */
    public function drawPathFigures(array $figures, bool $fill, bool $stroke): self
    {
        $canvasFigures = $this->getCanvasFigures($figures);
        if ($fill) {
            $this->fillFigures($canvasFigures);
        }
        if ($stroke) {
            $this->strokeFigures($canvasFigures);
        }

        return $this;
    }

    /**
     * Returns the spans of figures (to define a clipping region)
     *
     * @param array<array{points: array<array<float>>, types: array<int>, closed: bool}> $figures See drawPathFigures()
     *
     * @return array<int, array<array<int>>>
     */
    public function getFiguresSpans(array $figures, bool $nonZero): array
    {
        $polygons = [];
        foreach ($this->getCanvasFigures($figures) as $figure) {
            $polygons[] = $figure['points'];
        }

        return $this->rasterize($polygons, $nonZero);
    }

    /**
     * Returns the spans covering the whole image
     *
     * @return array<int, array<array<int>>>
     */
    public function getFullSpans(): array
    {
        $spans = [];
        for ($row = 0; $row < $this->canvasHeight; ++$row) {
            $spans[$row] = [[0, $this->canvasWidth - 1]];
        }

        return $spans;
    }

    /**
     * Returns the clipping region (spans of the canvas), or null if there is no clipping region
     *
     * @return array<int, array<array<int>>>|null
     */
    public function getClipSpans(): ?array
    {
        return $this->dc['clip'];
    }

    /**
     * Defines the clipping region (spans of the canvas, see getClipSpans()), or removes it (null)
     *
     * @param array<int, array<array<int>>>|null $spans
     */
    public function setClipSpans(?array $spans): self
    {
        $this->dc['clip'] = $spans;

        return $this;
    }

    /**
     * Converts figures to canvas coordinates, flattening Bézier curves
     *
     * @param array<array{points: array<array<float>>, types: array<int>, closed: bool}> $figures
     *
     * @return array<array{points: array<array<float>>, closed: bool}>
     */
    protected function getCanvasFigures(array $figures): array
    {
        $result = [];
        foreach ($figures as $figure) {
            $points = $this->toCanvasPoints($figure['points']);
            $count = count($points);
            if ($count == 0) {
                continue;
            }
            $flattened = [$points[0]];
            for ($i = 1; $i < $count; ++$i) {
                if (($figure['types'][$i] ?? 1) == 3 && $i + 2 < $count) {
                    $flattened = array_merge($flattened, Rasterizer::flattenBezier($points[$i - 1], $points[$i], $points[$i + 1], $points[$i + 2]));
                    $i += 2;
                } else {
                    $flattened[] = $points[$i];
                }
            }
            $result[] = ['points' => $flattened, 'closed' => $figure['closed']];
        }

        return $result;
    }

    /**
     * Fills the clipping region (or the whole image) with a color
     *
     * @param array<int> $color [r, g, b] or [r, g, b, a]
     */
    public function clear(array $color): self
    {
        $this->drawSpans($this->getFullSpans(), $color);

        return $this;
    }

    // ---------------------------------------------------------------------
    // Paths
    // ---------------------------------------------------------------------

    public function beginPath(): self
    {
        $this->path = [];
        $this->inPath = true;

        return $this;
    }

    public function endPath(): self
    {
        $this->inPath = false;

        return $this;
    }

    public function abortPath(): self
    {
        $this->path = null;
        $this->inPath = false;

        return $this;
    }

    public function closeFigure(): self
    {
        if (!empty($this->path)) {
            $this->path[count($this->path) - 1]['closed'] = true;
        }

        return $this;
    }

    public function fillPath(): self
    {
        return $this->drawPath(true, false);
    }

    public function strokePath(): self
    {
        return $this->drawPath(false, true);
    }

    public function strokeAndFillPath(): self
    {
        return $this->drawPath(true, true);
    }

    protected function drawPath(bool $fill, bool $stroke): self
    {
        if ($this->path) {
            if ($fill) {
                $this->fillFigures($this->path);
            }
            if ($stroke) {
                $this->strokeFigures($this->path);
            }
        }
        $this->path = null;

        return $this;
    }

    // ---------------------------------------------------------------------
    // Clipping
    // ---------------------------------------------------------------------

    /**
     * Combines the current path with the clipping region
     *
     * @param int $mode RGN_AND, RGN_OR, RGN_XOR, RGN_DIFF or RGN_COPY
     */
    public function selectClipPath(int $mode): self
    {
        $polygons = [];
        foreach ($this->path ?? [] as $figure) {
            $polygons[] = $figure['points'];
        }
        $this->combineClip($this->rasterize($polygons, $this->dc['polyFillMode'] == self::WINDING), $mode);
        $this->path = null;

        return $this;
    }

    public function intersectClipRect(float $left, float $top, float $right, float $bottom): self
    {
        $this->combineClip($this->rasterize([$this->getRectanglePoints($left, $top, $right, $bottom)], false), Rasterizer::RGN_AND);

        return $this;
    }

    public function excludeClipRect(float $left, float $top, float $right, float $bottom): self
    {
        $this->combineClip($this->rasterize([$this->getRectanglePoints($left, $top, $right, $bottom)], false), Rasterizer::RGN_DIFF);

        return $this;
    }

    public function offsetClipRegion(float $offsetX, float $offsetY): self
    {
        if ($this->dc['clip'] !== null) {
            list($origin, $offset) = $this->toCanvasPoints([[0, 0], [$offsetX, $offsetY]]);
            $shiftX = (int) round($offset[0] - $origin[0]);
            $shiftY = (int) round($offset[1] - $origin[1]);
            $clip = [];
            foreach ($this->dc['clip'] as $row => $spans) {
                foreach ($spans as $span) {
                    $clip[$row + $shiftY][] = [$span[0] + $shiftX, $span[1] + $shiftX];
                }
            }
            $this->dc['clip'] = $clip;
        }

        return $this;
    }

    /**
     * Removes the clipping region
     */
    public function resetClipRegion(): self
    {
        $this->dc['clip'] = null;

        return $this;
    }

    /**
     * Combines a region (rectangles) with the clipping region
     *
     * @param array<array<float>> $rectangles [[left, top, right, bottom], ...]
     * @param int $mode RGN_AND, RGN_OR, RGN_XOR, RGN_DIFF or RGN_COPY
     * @param bool $isDevice If the rectangles are in device units (else in logical units)
     */
    public function selectClipRegion(array $rectangles, int $mode, bool $isDevice): self
    {
        $polygons = [];
        foreach ($rectangles as $rectangle) {
            list($left, $top, $right, $bottom) = $rectangle;
            $polygons[] = $isDevice
                ? [
                    $this->deviceToCanvas($left, $top),
                    $this->deviceToCanvas($right, $top),
                    $this->deviceToCanvas($right, $bottom),
                    $this->deviceToCanvas($left, $bottom),
                ]
                : $this->getRectanglePoints($left, $top, $right, $bottom);
        }
        $this->combineClip($this->rasterize($polygons, true), $mode);

        return $this;
    }

    /**
     * @param array<int, array<array<int>>> $spans
     */
    protected function combineClip(array $spans, int $mode): void
    {
        if ($mode == Rasterizer::RGN_COPY) {
            $this->dc['clip'] = $spans;

            return;
        }
        if ($this->dc['clip'] === null && $mode == Rasterizer::RGN_AND) {
            $this->dc['clip'] = $spans;

            return;
        }
        if ($this->dc['clip'] === null) {
            // No clipping region means the whole canvas
            $this->dc['clip'] = [];
            for ($row = 0; $row < $this->canvasHeight; ++$row) {
                $this->dc['clip'][$row] = [[0, $this->canvasWidth - 1]];
            }
        }
        $this->dc['clip'] = Rasterizer::combineSpans($this->dc['clip'], $spans, $mode);
    }

    // ---------------------------------------------------------------------
    // Text
    // ---------------------------------------------------------------------

    /**
     * Draws a text with the current font
     *
     * @param string $text UTF-8 text
     * @param array<int> $dx Advances of each character (logical units)
     * @param int $options ETO_OPAQUE (0x02), ETO_CLIPPED (0x04)
     * @param array<int> $rectangle Logical rectangle used by ETO_OPAQUE & ETO_CLIPPED [left, top, right, bottom]
     * @param array<int> $dy Vertical advances of each character (ETO_PDY : logical units, upwards in the direction of the font)
     */
    public function textOut(float $x, float $y, string $text, array $dx = [], int $options = 0, array $rectangle = [0, 0, 0, 0], array $dy = []): self
    {
        $font = $this->dc['font'];
        $text = $this->convertSymbols($text);
        $clip = $this->dc['clip'];
        $hasRectangle = $rectangle[2] > $rectangle[0] && $rectangle[3] > $rectangle[1];

        // ETO_OPAQUE : the rectangle is filled with the background color
        if (($options & 0x02) && $hasRectangle) {
            $this->drawSpans($this->rasterize([$this->getRectanglePoints(...$rectangle)], false), $this->dc['bkColor']);
        }
        // ETO_CLIPPED : the text is clipped by the rectangle
        if (($options & 0x04) && $hasRectangle) {
            $spans = $this->rasterize([$this->getRectanglePoints(...$rectangle)], false);
            $clip = $clip === null ? $spans : Rasterizer::combineSpans($clip, $spans, Rasterizer::RGN_AND);
        }
        if ($text === '') {
            return $this;
        }

        // TA_UPDATECP : the current position is used as reference point
        $updatePosition = ($this->dc['textAlign'] & 0x01) > 0;
        if ($updatePosition) {
            list($x, $y) = $this->dc['position'];
        }

        $metrics = $this->getTextMetrics();
        if ($metrics === null) {
            return $this;
        }
        list($fontFile, $pointSize, $emSize, $angle, $scaleX) = $metrics;
        $ux = cos(deg2rad($angle));
        $uy = -sin(deg2rad($angle));
        $vx = -$uy;
        $vy = $ux;

        if (!empty($dx)) {
            $width = array_sum($dx) * $scaleX;
        } else {
            $box = imagettfbbox($pointSize, 0, $fontFile, $this->escapeText($text));
            $width = $box ? $box[2] - $box[0] : 0;
        }
        $ascent = 0.905 * $emSize;
        $descent = 0.212 * $emSize;

        // Alignment
        $align = $this->dc['textAlign'];
        $shiftX = 0;
        if (($align & 0x06) == 0x06) {
            $shiftX = -$width / 2;
        } elseif ($align & 0x02) {
            $shiftX = -$width;
        }
        $shiftY = $ascent;
        if (($align & 0x18) == 0x18) {
            $shiftY = 0;
        } elseif ($align & 0x08) {
            $shiftY = -$descent;
        }
        list($origin) = $this->toCanvasPoints([[$x, $y]]);
        $baseX = $origin[0] + $ux * $shiftX + $vx * $shiftY;
        $baseY = $origin[1] + $uy * $shiftX + $vy * $shiftY;

        // Polygon covering the text from $top to $bottom (offsets from the baseline)
        $getBand = function (float $top, float $bottom) use ($baseX, $baseY, $ux, $uy, $vx, $vy, $width): array {
            return [
                [$baseX + $vx * $top, $baseY + $vy * $top],
                [$baseX + $ux * $width + $vx * $top, $baseY + $uy * $width + $vy * $top],
                [$baseX + $ux * $width + $vx * $bottom, $baseY + $uy * $width + $vy * $bottom],
                [$baseX + $vx * $bottom, $baseY + $vy * $bottom],
            ];
        };

        $savedClip = $this->dc['clip'];
        $this->dc['clip'] = $clip;

        // OPAQUE background mode
        if ($this->dc['bkMode'] == 2) {
            $this->drawSpans($this->rasterize([$getBand(-$ascent, $descent)], false), $this->dc['bkColor']);
        }

        // Position of each glyph
        $glyphs = [[$text, $baseX, $baseY]];
        if (!empty($dx)) {
            list(, , $m21, $m22) = $this->getMatrix();
            $scaleY = hypot($m21, $m22);
            $glyphs = [];
            $advance = $rise = 0;
            foreach (preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $i => $char) {
                $glyphs[] = [$char, $baseX + $ux * $advance - $vx * $rise, $baseY + $uy * $advance - $vy * $rise];
                $advance += ($dx[$i] ?? 0) * $scaleX;
                $rise += ($dy[$i] ?? 0) * $scaleY;
            }
        }
        $this->drawGlyphs($glyphs, $pointSize, $angle, $fontFile, $this->dc['textColor'], $clip);

        // Decorations
        $thickness = max($this->supersampling, 0.06 * $emSize);
        if ($font['underline']) {
            $this->drawSpans($this->rasterize([$getBand(0.1 * $emSize, 0.1 * $emSize + $thickness)], false), $this->dc['textColor']);
        }
        if ($font['strikeOut']) {
            $this->drawSpans($this->rasterize([$getBand(-0.3 * $emSize - $thickness, -0.3 * $emSize)], false), $this->dc['textColor']);
        }

        $this->dc['clip'] = $savedClip;

        if ($updatePosition) {
            $this->dc['position'] = [$x + ($scaleX > 0 ? $width / $scaleX : 0), $y];
        }

        return $this;
    }

    /**
     * Returns the metrics of the current font : [font file, point size, em size (canvas pixels), angle (degrees), horizontal scale]
     *
     * @return array{0: string, 1: float, 2: float, 3: float, 4: float}|null Null if no font is found or if the text is too small
     */
    protected function getTextMetrics(): ?array
    {
        $font = $this->dc['font'];
        list($m11, $m12, $m21, $m22) = $this->getMatrix();
        $scaleX = hypot($m11, $m12);
        $scaleY = hypot($m21, $m22);

        // Size of the em square, in canvas pixels (a positive height is the height of the cell)
        $height = $font['height'] == 0 ? -12 : $font['height'];
        $emSize = ($height < 0 ? -$height : $height * 0.89) * $scaleY;
        $fontFile = $this->fontResolver->resolve($font);
        if (!$fontFile || $emSize < 1) {
            return null;
        }
        // GD renders fonts at 96 DPI
        $pointSize = $emSize * 72 / 96;

        // Direction of the baseline (canvas coordinates)
        // The escapement is counterclockwise in logical space : it is mirrored if the transform flips the y axis
        $escapement = $font['escapement'] / 10;
        if ($m11 * $m22 - $m12 * $m21 < 0) {
            $escapement = -$escapement;
        }
        $angle = $escapement + rad2deg(atan2(-$m12, $m11));

        return [$fontFile, $pointSize, $emSize, $angle, $scaleX];
    }

    /**
     * Returns the width of a text with the current font, in logical units (0 if no font is found)
     */
    public function getTextWidth(string $text): float
    {
        $metrics = $this->getTextMetrics();
        if ($metrics === null || $text === '') {
            return 0.0;
        }
        list($fontFile, $pointSize, , , $scaleX) = $metrics;
        $box = imagettfbbox($pointSize, 0, $fontFile, $this->escapeText($this->convertSymbols($text)));

        return $box && $scaleX > 0 ? ($box[2] - $box[0]) / $scaleX : 0.0;
    }

    /**
     * Draws glyphs with the current font, each one at its position (logical coordinates of the baseline)
     *
     * @param array<array{0: string, 1: float, 2: float}> $glyphs UTF-8 character & position
     */
    public function glyphsOut(array $glyphs): self
    {
        $metrics = $this->getTextMetrics();
        if ($metrics === null || empty($glyphs)) {
            return $this;
        }
        list($fontFile, $pointSize, , $angle) = $metrics;

        $points = $this->toCanvasPoints(array_map(function (array $glyph): array {
            return [$glyph[1], $glyph[2]];
        }, $glyphs));
        $canvasGlyphs = [];
        foreach ($glyphs as $key => $glyph) {
            $canvasGlyphs[] = [$this->convertSymbols($glyph[0]), $points[$key][0], $points[$key][1]];
        }
        $this->drawGlyphs($canvasGlyphs, $pointSize, $angle, $fontFile, $this->dc['textColor'], $this->dc['clip']);

        return $this;
    }

    /**
     * @param array<array{0: string, 1: float, 2: float}> $glyphs Text & position (canvas coordinates)
     * @param array<int> $color
     * @param array<int, array<array<int>>>|null $clip
     */
    protected function drawGlyphs(array $glyphs, float $pointSize, float $angle, string $fontFile, array $color, ?array $clip): void
    {
        $gdColor = $this->toGDColor($color);
        $alpha = ($color[3] ?? 255) / 255;
        if ($clip === null) {
            foreach ($glyphs as $glyph) {
                imagettftext($this->canvas, $pointSize, $angle, (int) round($glyph[1]), (int) round($glyph[2]), $gdColor, $fontFile, $this->escapeText($glyph[0]));
            }

            return;
        }

        // The text is drawn in a mask, which is applied through the clipping region
        $minX = $minY = PHP_INT_MAX;
        $maxX = $maxY = PHP_INT_MIN;
        foreach ($glyphs as $glyph) {
            $box = imagettfbbox($pointSize, $angle, $fontFile, $this->escapeText($glyph[0]));
            if (!$box) {
                continue;
            }
            for ($i = 0; $i < 8; $i += 2) {
                $minX = min($minX, (int) floor($glyph[1] + $box[$i]) - 1);
                $maxX = max($maxX, (int) ceil($glyph[1] + $box[$i]) + 1);
                $minY = min($minY, (int) floor($glyph[2] + $box[$i + 1]) - 1);
                $maxY = max($maxY, (int) ceil($glyph[2] + $box[$i + 1]) + 1);
            }
        }
        $minX = max(0, $minX);
        $minY = max(0, $minY);
        $maxX = min($this->canvasWidth - 1, $maxX);
        $maxY = min($this->canvasHeight - 1, $maxY);
        if ($minX > $maxX || $minY > $maxY) {
            return;
        }

        $mask = imagecreatetruecolor($maxX - $minX + 1, $maxY - $minY + 1);
        imagefilledrectangle($mask, 0, 0, $maxX - $minX, $maxY - $minY, 0xFFFFFF);
        foreach ($glyphs as $glyph) {
            imagettftext($mask, $pointSize, $angle, (int) round($glyph[1]) - $minX, (int) round($glyph[2]) - $minY, 0x000000, $fontFile, $this->escapeText($glyph[0]));
        }

        for ($y = $minY; $y <= $maxY; ++$y) {
            foreach (Rasterizer::combineRow($clip[$y] ?? [], [[$minX, $maxX]], Rasterizer::RGN_AND) as $span) {
                for ($x = $span[0]; $x <= $span[1]; ++$x) {
                    $coverage = (1 - (imagecolorat($mask, $x - $minX, $y - $minY) & 0xFF) / 255) * $alpha;
                    if ($coverage <= 0) {
                        continue;
                    }
                    $background = imagecolorat($this->canvas, $x, $y);
                    // A background with an alpha channel is blended by GD
                    if ($background & 0x7F000000) {
                        imagesetpixel($this->canvas, $x, $y, $this->toGDColor([$color[0], $color[1], $color[2], $coverage * 255]));
                        continue;
                    }
                    $red = (int) round($color[0] * $coverage + (($background >> 16) & 0xFF) * (1 - $coverage));
                    $green = (int) round($color[1] * $coverage + (($background >> 8) & 0xFF) * (1 - $coverage));
                    $blue = (int) round($color[2] * $coverage + ($background & 0xFF) * (1 - $coverage));
                    imagesetpixel($this->canvas, $x, $y, ($red << 16) | ($green << 8) | $blue);
                }
            }
        }
        if (\PHP_VERSION_ID < 80000) {
            imagedestroy($mask);
        }
    }

    /**
     * Converts the characters of the Symbol font to Unicode : the text is drawn with another font
     */
    protected function convertSymbols(string $text): string
    {
        return strtolower(trim((string) ($this->dc['font']['face'] ?? ''))) == 'symbol' ? Encoding::decodeSymbol($text) : $text;
    }

    /**
     * GD interprets HTML entities in texts
     */
    protected function escapeText(string $text): string
    {
        return str_replace('&', '&#38;', $text);
    }

    // ---------------------------------------------------------------------
    // Gradients
    // ---------------------------------------------------------------------

    /**
     * @param array<array{0: float, 1: float, 2: array<int>}> $vertices Logical position & color ([x, y, [r, g, b]])
     * @param array<array<int>> $objects Indexes of the vertices of each rectangle (2) or triangle (3)
     * @param int $mode GRADIENT_FILL_RECT_H (0), GRADIENT_FILL_RECT_V (1) or GRADIENT_FILL_TRIANGLE (2)
     */
    public function gradientFill(array $vertices, array $objects, int $mode): self
    {
        foreach ($vertices as $key => $vertex) {
            list($point) = $this->toCanvasPoints([[$vertex[0], $vertex[1]]]);
            $vertices[$key] = [$point[0], $point[1], $vertex[2]];
        }

        // GRADIENT_FILL_TRIANGLE
        if ($mode == 2) {
            foreach ($objects as $object) {
                list($first, $second, $third) = $object;
                if (isset($vertices[$first], $vertices[$second], $vertices[$third])) {
                    $this->drawGradientTriangle($vertices[$first], $vertices[$second], $vertices[$third]);
                }
            }

            return $this;
        }

        // GRADIENT_FILL_RECT_H or GRADIENT_FILL_RECT_V
        foreach ($objects as $object) {
            list($upperLeft, $lowerRight) = $object;
            if (!isset($vertices[$upperLeft], $vertices[$lowerRight])) {
                continue;
            }
            list($x1, $y1, $color1) = $vertices[$upperLeft];
            list($x2, $y2, $color2) = $vertices[$lowerRight];
            $polygon = [[$x1, $y1], [$x2, $y1], [$x2, $y2], [$x1, $y2]];
            $isHorizontal = $mode == 0;
            $this->drawShadedSpans($this->rasterize([$polygon], false), function (float $x, float $y) use ($x1, $y1, $x2, $y2, $color1, $color2, $isHorizontal): array {
                $ratio = $isHorizontal
                    ? ($x2 != $x1 ? ($x - $x1) / ($x2 - $x1) : 0)
                    : ($y2 != $y1 ? ($y - $y1) / ($y2 - $y1) : 0);
                $ratio = max(0, min(1, $ratio));

                return [
                    $color1[0] + ($color2[0] - $color1[0]) * $ratio,
                    $color1[1] + ($color2[1] - $color1[1]) * $ratio,
                    $color1[2] + ($color2[2] - $color1[2]) * $ratio,
                ];
            });
        }

        return $this;
    }

    /**
     * @param array{0: float, 1: float, 2: array<int>} $first
     * @param array{0: float, 1: float, 2: array<int>} $second
     * @param array{0: float, 1: float, 2: array<int>} $third
     */
    protected function drawGradientTriangle(array $first, array $second, array $third): void
    {
        $determinant = ($second[1] - $third[1]) * ($first[0] - $third[0]) + ($third[0] - $second[0]) * ($first[1] - $third[1]);
        if ($determinant == 0) {
            return;
        }
        $polygon = [[$first[0], $first[1]], [$second[0], $second[1]], [$third[0], $third[1]]];
        $this->drawShadedSpans($this->rasterize([$polygon], false), function (float $x, float $y) use ($first, $second, $third, $determinant): array {
            // Barycentric coordinates
            $weight1 = (($second[1] - $third[1]) * ($x - $third[0]) + ($third[0] - $second[0]) * ($y - $third[1])) / $determinant;
            $weight2 = (($third[1] - $first[1]) * ($x - $third[0]) + ($first[0] - $third[0]) * ($y - $third[1])) / $determinant;
            $weight3 = 1 - $weight1 - $weight2;

            return [
                $first[2][0] * $weight1 + $second[2][0] * $weight2 + $third[2][0] * $weight3,
                $first[2][1] * $weight1 + $second[2][1] * $weight2 + $third[2][1] * $weight3,
                $first[2][2] * $weight1 + $second[2][2] * $weight2 + $third[2][2] * $weight3,
            ];
        });
    }

    /**
     * Draws spans with a color computed for each pixel
     *
     * @param array<int, array<array<int>>> $spans
     * @param callable(float, float): array<float> $getColor
     */
    protected function drawShadedSpans(array $spans, callable $getColor): void
    {
        if ($this->dc['clip'] !== null) {
            $spans = Rasterizer::combineSpans($spans, $this->dc['clip'], Rasterizer::RGN_AND);
        }
        foreach ($spans as $y => $row) {
            foreach ($row as $span) {
                for ($x = $span[0]; $x <= $span[1]; ++$x) {
                    $color = $getColor($x + 0.5, $y + 0.5);
                    $red = (int) max(0, min(255, round($color[0])));
                    $green = (int) max(0, min(255, round($color[1])));
                    $blue = (int) max(0, min(255, round($color[2])));
                    if (!isset($color[3])) {
                        imagesetpixel($this->canvas, $x, $y, ($red << 16) | ($green << 8) | $blue);
                    } elseif ($color[3] > 0) {
                        imagesetpixel($this->canvas, $x, $y, $this->toGDColor([$red, $green, $blue, $color[3]]));
                    }
                }
            }
        }
    }

    // ---------------------------------------------------------------------
    // Bitmaps
    // ---------------------------------------------------------------------

    /**
     * Draws a (part of a) bitmap in a logical parallelogram
     *
     * @param array{width: int, height: int, pixels: array<array<int>>} $bitmap Decoded bitmap (see Bitmap), pixels may have a GD alpha channel
     * @param array<array<float>> $points Logical points of the upper-left, upper-right & lower-left corners of the destination
     * @param array<float> $source Source rectangle in the bitmap [x, y, width, height]
     */
    public function drawImage(array $bitmap, array $points, array $source): self
    {
        if (empty($bitmap['pixels']) || $source[2] == 0 || $source[3] == 0) {
            return $this;
        }
        list($origin, $right, $bottom) = $this->toCanvasPoints($points);
        // Axes of the parallelogram in the canvas
        $ux = $right[0] - $origin[0];
        $uy = $right[1] - $origin[1];
        $vx = $bottom[0] - $origin[0];
        $vy = $bottom[1] - $origin[1];
        $determinant = $ux * $vy - $vx * $uy;
        if ($determinant == 0) {
            return $this;
        }

        if ($uy == 0 && $vx == 0 && $ux > 0 && $vy > 0 && $this->dc['clip'] === null && $this->copyImage($bitmap, $origin, $ux, $vy, $source)) {
            return $this;
        }

        $xs = [$origin[0], $right[0], $bottom[0], $right[0] + $vx];
        $ys = [$origin[1], $right[1], $bottom[1], $right[1] + $vy];
        $minX = (int) max(0, floor(min($xs)));
        $maxX = (int) min($this->canvasWidth - 1, ceil(max($xs)));
        $minY = (int) max(0, floor(min($ys)));
        $maxY = (int) min($this->canvasHeight - 1, ceil(max($ys)));

        $clip = $this->dc['clip'];
        for ($y = $minY; $y <= $maxY; ++$y) {
            $rowSpans = [[$minX, $maxX]];
            if ($clip !== null) {
                $rowSpans = Rasterizer::combineRow($rowSpans, $clip[$y] ?? [], Rasterizer::RGN_AND);
            }
            foreach ($rowSpans as $span) {
                for ($x = $span[0]; $x <= $span[1]; ++$x) {
                    // Coordinates of the pixel center in the parallelogram
                    $px = $x + 0.5 - $origin[0];
                    $py = $y + 0.5 - $origin[1];
                    $u = ($px * $vy - $py * $vx) / $determinant;
                    $v = ($py * $ux - $px * $uy) / $determinant;
                    if ($u < 0 || $u >= 1 || $v < 0 || $v >= 1) {
                        continue;
                    }
                    $sourceX = (int) floor($source[0] + $u * $source[2]);
                    $sourceY = (int) floor($source[1] + $v * $source[3]);
                    if (isset($bitmap['pixels'][$sourceY][$sourceX])) {
                        imagesetpixel($this->canvas, $x, $y, $bitmap['pixels'][$sourceY][$sourceX]);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Copies a bitmap in a rectangle of the canvas (fast path of drawImage()), its alpha channel is blended
     *
     * @param array{width: int, height: int, pixels: array<array<int>>} $bitmap
     * @param array<float> $origin Upper-left corner (canvas coordinates)
     * @param array<float> $source Source rectangle in the bitmap [x, y, width, height]
     *
     * @return bool False if the source rectangle is not in the bitmap
     */
    protected function copyImage(array $bitmap, array $origin, float $width, float $height, array $source): bool
    {
        list($sourceX, $sourceY, $sourceWidth, $sourceHeight) = $source;
        if ($sourceX != (int) $sourceX || $sourceY != (int) $sourceY || $sourceWidth != (int) $sourceWidth || $sourceHeight != (int) $sourceHeight
            || $sourceX < 0 || $sourceY < 0 || $sourceX + $sourceWidth > $bitmap['width'] || $sourceY + $sourceHeight > $bitmap['height']) {
            return false;
        }

        $image = imagecreatetruecolor($bitmap['width'], $bitmap['height']);
        // The alpha channel of pixels is kept
        imagealphablending($image, false);
        foreach ($bitmap['pixels'] as $y => $line) {
            foreach ($line as $x => $pixel) {
                imagesetpixel($image, $x, $y, $pixel);
            }
        }
        $left = (int) round($origin[0]);
        $top = (int) round($origin[1]);
        imagecopyresized(
            $this->canvas,
            $image,
            $left,
            $top,
            (int) $sourceX,
            (int) $sourceY,
            (int) round($origin[0] + $width) - $left,
            (int) round($origin[1] + $height) - $top,
            (int) $sourceWidth,
            (int) $sourceHeight
        );
        if (\PHP_VERSION_ID < 80000) {
            imagedestroy($image);
        }

        return true;
    }

    /**
     * Draws a (part of a) bitmap in a logical rectangle
     *
     * @param array{width: int, height: int, pixels: array<array<int>>} $bitmap Decoded bitmap (see Bitmap)
     */
    public function drawBitmap(array $bitmap, int $xDest, int $yDest, int $cxDest, int $cyDest, int $xSrc, int $ySrc, int $cxSrc, int $cySrc, int $rop = self::ROP_SRCCOPY): self
    {
        if ($cxDest == 0 || $cyDest == 0 || empty($bitmap['pixels']) || $rop == self::ROP_NOP) {
            return $this;
        }
        // Raster operation (other than SRCCOPY) : the pattern is the color of the brush (black for the null brush)
        $isCopy = $rop == self::ROP_SRCCOPY;
        $hasDestination = self::isRopDependent($rop, 1);
        $brush = $this->dc['brush'];
        $pattern = $brush && $brush['style'] != 1 ? self::toRGB($brush['color']) : 0x000000;
        list($m11, $m12, $m21, $m22, $dx, $dy) = $this->getMatrix();
        $determinant = $m11 * $m22 - $m21 * $m12;
        if ($determinant == 0) {
            return $this;
        }

        // Bounding box of the destination in the canvas
        $corners = $this->toCanvasPoints([
            [$xDest, $yDest],
            [$xDest + $cxDest, $yDest],
            [$xDest, $yDest + $cyDest],
            [$xDest + $cxDest, $yDest + $cyDest],
        ]);
        $xs = array_column($corners, 0);
        $ys = array_column($corners, 1);
        $minX = (int) max(0, floor(min($xs)));
        $maxX = (int) min($this->canvasWidth - 1, ceil(max($xs)));
        $minY = (int) max(0, floor(min($ys)));
        $maxY = (int) min($this->canvasHeight - 1, ceil(max($ys)));

        $clip = $this->dc['clip'];
        for ($y = $minY; $y <= $maxY; ++$y) {
            $rowSpans = [[$minX, $maxX]];
            if ($clip !== null) {
                $rowSpans = Rasterizer::combineSpans([$y => $rowSpans], [$y => $clip[$y] ?? []], Rasterizer::RGN_AND)[$y] ?? [];
            }
            foreach ($rowSpans as $span) {
                for ($x = $span[0]; $x <= $span[1]; ++$x) {
                    // Inverse transform of the pixel center to logical coordinates
                    $canvasX = $x + 0.5 - $dx;
                    $canvasY = $y + 0.5 - $dy;
                    $logicalX = ($canvasX * $m22 - $canvasY * $m21) / $determinant;
                    $logicalY = ($canvasY * $m11 - $canvasX * $m12) / $determinant;

                    $u = ($logicalX - $xDest) / $cxDest;
                    $v = ($logicalY - $yDest) / $cyDest;
                    if ($u < 0 || $u >= 1 || $v < 0 || $v >= 1) {
                        continue;
                    }
                    $sourceX = $xSrc + (int) floor($u * $cxSrc);
                    $sourceY = $ySrc + (int) floor($v * $cySrc);
                    if (!isset($bitmap['pixels'][$sourceY][$sourceX])) {
                        continue;
                    }
                    $pixel = $bitmap['pixels'][$sourceY][$sourceX];
                    if (!$isCopy) {
                        $pixel = self::applyRop($rop, $pattern, $pixel & 0xFFFFFF, $hasDestination ? $this->getDestinationColor($x, $y) : 0);
                    }
                    imagesetpixel($this->canvas, $x, $y, $pixel);
                }
            }
        }

        return $this;
    }
}
