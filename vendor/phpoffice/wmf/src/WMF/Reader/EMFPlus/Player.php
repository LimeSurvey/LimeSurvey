<?php

declare(strict_types=1);

namespace PhpOffice\WMF\Reader\EMFPlus;

use PhpOffice\WMF\Exception\WMFException;
use PhpOffice\WMF\Renderer\Encoding;
use PhpOffice\WMF\Renderer\GD as Renderer;
use PhpOffice\WMF\Renderer\Rasterizer;

/**
 * Plays the EMF+ records of an EMF file with the renderer
 *
 * EMF+ records are stored in EMR_COMMENT records of the EMF file.
 *
 * @see https://learn.microsoft.com/en-us/openspecs/windows_protocols/ms-emfplus/
 */
class Player
{
    public const EMFPLUS_HEADER = 0x4001;
    public const EMFPLUS_END_OF_FILE = 0x4002;
    public const EMFPLUS_COMMENT = 0x4003;
    public const EMFPLUS_GET_DC = 0x4004;
    public const EMFPLUS_MULTI_FORMAT_START = 0x4005;
    public const EMFPLUS_MULTI_FORMAT_SECTION = 0x4006;
    public const EMFPLUS_MULTI_FORMAT_END = 0x4007;
    public const EMFPLUS_OBJECT = 0x4008;
    public const EMFPLUS_CLEAR = 0x4009;
    public const EMFPLUS_FILL_RECTS = 0x400A;
    public const EMFPLUS_DRAW_RECTS = 0x400B;
    public const EMFPLUS_FILL_POLYGON = 0x400C;
    public const EMFPLUS_DRAW_LINES = 0x400D;
    public const EMFPLUS_FILL_ELLIPSE = 0x400E;
    public const EMFPLUS_DRAW_ELLIPSE = 0x400F;
    public const EMFPLUS_FILL_PIE = 0x4010;
    public const EMFPLUS_DRAW_PIE = 0x4011;
    public const EMFPLUS_DRAW_ARC = 0x4012;
    public const EMFPLUS_FILL_REGION = 0x4013;
    public const EMFPLUS_FILL_PATH = 0x4014;
    public const EMFPLUS_DRAW_PATH = 0x4015;
    public const EMFPLUS_FILL_CLOSED_CURVE = 0x4016;
    public const EMFPLUS_DRAW_CLOSED_CURVE = 0x4017;
    public const EMFPLUS_DRAW_CURVE = 0x4018;
    public const EMFPLUS_DRAW_BEZIERS = 0x4019;
    public const EMFPLUS_DRAW_IMAGE = 0x401A;
    public const EMFPLUS_DRAW_IMAGE_POINTS = 0x401B;
    public const EMFPLUS_DRAW_STRING = 0x401C;
    public const EMFPLUS_SET_RENDERING_ORIGIN = 0x401D;
    public const EMFPLUS_SET_ANTI_ALIAS_MODE = 0x401E;
    public const EMFPLUS_SET_TEXT_RENDERING_HINT = 0x401F;
    public const EMFPLUS_SET_TEXT_CONTRAST = 0x4020;
    public const EMFPLUS_SET_INTERPOLATION_MODE = 0x4021;
    public const EMFPLUS_SET_PIXEL_OFFSET_MODE = 0x4022;
    public const EMFPLUS_SET_COMPOSITING_MODE = 0x4023;
    public const EMFPLUS_SET_COMPOSITING_QUALITY = 0x4024;
    public const EMFPLUS_SAVE = 0x4025;
    public const EMFPLUS_RESTORE = 0x4026;
    public const EMFPLUS_BEGIN_CONTAINER = 0x4027;
    public const EMFPLUS_BEGIN_CONTAINER_NO_PARAMS = 0x4028;
    public const EMFPLUS_END_CONTAINER = 0x4029;
    public const EMFPLUS_SET_WORLD_TRANSFORM = 0x402A;
    public const EMFPLUS_RESET_WORLD_TRANSFORM = 0x402B;
    public const EMFPLUS_MULTIPLY_WORLD_TRANSFORM = 0x402C;
    public const EMFPLUS_TRANSLATE_WORLD_TRANSFORM = 0x402D;
    public const EMFPLUS_SCALE_WORLD_TRANSFORM = 0x402E;
    public const EMFPLUS_ROTATE_WORLD_TRANSFORM = 0x402F;
    public const EMFPLUS_SET_PAGE_TRANSFORM = 0x4030;
    public const EMFPLUS_RESET_CLIP = 0x4031;
    public const EMFPLUS_SET_CLIP_RECT = 0x4032;
    public const EMFPLUS_SET_CLIP_PATH = 0x4033;
    public const EMFPLUS_SET_CLIP_REGION = 0x4034;
    public const EMFPLUS_OFFSET_CLIP = 0x4035;
    public const EMFPLUS_DRAW_DRIVER_STRING = 0x4036;
    public const EMFPLUS_STROKE_FILL_PATH = 0x4037;
    public const EMFPLUS_SERIALIZABLE_OBJECT = 0x4038;
    public const EMFPLUS_SET_TS_GRAPHICS = 0x4039;
    public const EMFPLUS_SET_TS_CLIP = 0x403A;

    /**
     * Records which have no effect on the rendering (rendering hints, comments...)
     */
    protected const IGNORED_RECORDS = [
        self::EMFPLUS_END_OF_FILE,
        self::EMFPLUS_COMMENT,
        self::EMFPLUS_MULTI_FORMAT_START,
        self::EMFPLUS_MULTI_FORMAT_SECTION,
        self::EMFPLUS_MULTI_FORMAT_END,
        self::EMFPLUS_SET_RENDERING_ORIGIN,
        self::EMFPLUS_SET_ANTI_ALIAS_MODE,
        self::EMFPLUS_SET_TEXT_RENDERING_HINT,
        self::EMFPLUS_SET_TEXT_CONTRAST,
        self::EMFPLUS_SET_INTERPOLATION_MODE,
        self::EMFPLUS_SET_PIXEL_OFFSET_MODE,
        self::EMFPLUS_SET_COMPOSITING_MODE,
        self::EMFPLUS_SET_COMPOSITING_QUALITY,
        self::EMFPLUS_STROKE_FILL_PATH,
        self::EMFPLUS_SERIALIZABLE_OBJECT,
        self::EMFPLUS_SET_TS_GRAPHICS,
        self::EMFPLUS_SET_TS_CLIP,
    ];

    /**
     * Flags of records
     */
    protected const FLAG_SOLID_COLOR = 0x8000;
    protected const FLAG_COMPRESSED = 0x4000;
    protected const FLAG_CLOSED = 0x2000;
    protected const FLAG_RELATIVE = 0x0800;

    protected const IDENTITY = [1.0, 0.0, 0.0, 1.0, 0.0, 0.0];

    /**
     * @var Renderer
     */
    protected $renderer;
    /**
     * @var ObjectReader
     */
    protected $objectReader;
    /**
     * Resolution of the EMF+ records (header), used to convert units (points, inches, millimeters...) to pixels
     *
     * The pixels of EMF+ records are the device units of the EMF file
     *
     * @var float
     */
    protected $dpiX = 96.0;
    /**
     * @var float
     */
    protected $dpiY = 96.0;
    /**
     * Objects indexed by their id
     *
     * @var array<int, array<string, mixed>>
     */
    protected $objects = [];
    /**
     * Data of objects split in several records, indexed by their id
     *
     * @var array<int, array{type: int, size: int, data: string}>
     */
    protected $continuedObjects = [];
    /**
     * Graphics state : world transform, page unit & scale, base transform (containers), clipping region (spans)
     *
     * @var array<string, mixed>
     */
    protected $state;
    /**
     * Saved states, indexed by their stack index
     *
     * @var array<int, array<string, mixed>>
     */
    protected $stack = [];

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->objectReader = new ObjectReader();
        $this->state = $this->getDefaultState();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getDefaultState(): array
    {
        return [
            'world' => self::IDENTITY,
            // UnitDisplay
            'pageUnit' => 1,
            'pageScale' => 1.0,
            // Transform of the containers (EMF+ pixels are the device units of the EMF file)
            'base' => self::IDENTITY,
            'clip' => null,
        ];
    }

    /**
     * Plays the EMF+ records of an EMR_COMMENT record (without the "EMF+" identifier)
     *
     * @return bool If the next EMF records must be played (the last EMF+ record is EmfPlusGetDC)
     */
    public function play(string $data): bool
    {
        $buffer = new Buffer($data);
        $lastType = 0;
        while ($buffer->getRemaining() >= 12) {
            $type = $buffer->readUInt16();
            $flags = $buffer->readUInt16();
            $size = $buffer->readUInt32();
            $dataSize = $buffer->readUInt32();
            if ($size < 12 || $dataSize > $size - 12) {
                throw new WMFException('Reader : Invalid file : EMF+ record size');
            }
            $recordData = $buffer->read($dataSize);
            $buffer->skip(min($size - 12 - $dataSize, $buffer->getRemaining()));

            if (!in_array($type, self::IGNORED_RECORDS)) {
                $this->playRecord($type, $flags, new Buffer($recordData));
            }
            $lastType = $type;
        }

        return $lastType == self::EMFPLUS_GET_DC;
    }

    protected function playRecord(int $type, int $flags, Buffer $data): void
    {
        $isCompressed = ($flags & self::FLAG_COMPRESSED) > 0;
        $isRelative = ($flags & self::FLAG_RELATIVE) > 0;
        $id = $flags & 0xFF;

        switch ($type) {
            case self::EMFPLUS_HEADER:
                $data->skip(8);
                $this->dpiX = (float) ($data->readUInt32() ?: 96);
                $this->dpiY = (float) ($data->readUInt32() ?: 96);
                $this->state = $this->getDefaultState();
                break;
            case self::EMFPLUS_GET_DC:
                break;
            case self::EMFPLUS_OBJECT:
                $this->readObject($flags, $data);
                break;

                // State
            case self::EMFPLUS_SAVE:
                $this->stack[$data->readUInt32()] = $this->state;
                break;
            case self::EMFPLUS_RESTORE:
            case self::EMFPLUS_END_CONTAINER:
                $index = $data->readUInt32();
                if (isset($this->stack[$index])) {
                    $this->state = $this->stack[$index];
                    unset($this->stack[$index]);
                }
                break;
            case self::EMFPLUS_BEGIN_CONTAINER:
                // The source rectangle (in the unit of the flags) is mapped to the destination rectangle (world coordinates)
                $destination = $data->readRect();
                $source = $data->readRect();
                $this->stack[$data->readUInt32()] = $this->state;
                $unitX = $this->getUnitPixels(($flags >> 8) & 0xFF, $this->dpiX);
                $unitY = $this->getUnitPixels(($flags >> 8) & 0xFF, $this->dpiY);
                $scaleX = $source[2] != 0 ? $destination[2] / ($source[2] * $unitX) : 1;
                $scaleY = $source[3] != 0 ? $destination[3] / ($source[3] * $unitY) : 1;
                $mapping = [$scaleX, 0.0, 0.0, $scaleY, $destination[0] - $source[0] * $unitX * $scaleX, $destination[1] - $source[1] * $unitY * $scaleY];
                $this->beginContainer($mapping);
                break;
            case self::EMFPLUS_BEGIN_CONTAINER_NO_PARAMS:
                $this->stack[$data->readUInt32()] = $this->state;
                $this->beginContainer(self::IDENTITY);
                break;

                // Transforms
            case self::EMFPLUS_SET_WORLD_TRANSFORM:
                $this->state['world'] = $data->readMatrix();
                break;
            case self::EMFPLUS_RESET_WORLD_TRANSFORM:
                $this->state['world'] = self::IDENTITY;
                break;
            case self::EMFPLUS_MULTIPLY_WORLD_TRANSFORM:
                $this->multiplyWorld($data->readMatrix(), $flags);
                break;
            case self::EMFPLUS_TRANSLATE_WORLD_TRANSFORM:
                $this->multiplyWorld([1.0, 0.0, 0.0, 1.0, $data->readFloat(), $data->readFloat()], $flags);
                break;
            case self::EMFPLUS_SCALE_WORLD_TRANSFORM:
                $this->multiplyWorld([$data->readFloat(), 0.0, 0.0, $data->readFloat(), 0.0, 0.0], $flags);
                break;
            case self::EMFPLUS_ROTATE_WORLD_TRANSFORM:
                $angle = deg2rad($data->readFloat());
                $this->multiplyWorld([cos($angle), sin($angle), -sin($angle), cos($angle), 0.0, 0.0], $flags);
                break;
            case self::EMFPLUS_SET_PAGE_TRANSFORM:
                $this->state['pageUnit'] = $flags & 0xFF;
                $this->state['pageScale'] = $data->readFloat();
                break;

                // Clipping
            case self::EMFPLUS_RESET_CLIP:
                $this->state['clip'] = null;
                break;
            case self::EMFPLUS_SET_CLIP_RECT:
                $rect = $data->readRect();
                $this->combineClip($this->getSpans(function () use ($rect): array {
                    return $this->renderer->getFiguresSpans([$this->getRectFigure($rect)], false);
                }), ($flags >> 8) & 0x0F);
                break;
            case self::EMFPLUS_SET_CLIP_PATH:
                $path = $this->getObject($id, 'path');
                $this->combineClip($this->getSpans(function () use ($path): array {
                    return $this->renderer->getFiguresSpans($path['figures'], false);
                }), ($flags >> 8) & 0x0F);
                break;
            case self::EMFPLUS_SET_CLIP_REGION:
                $region = $this->getObject($id, 'region');
                $this->combineClip($this->getSpans(function () use ($region): array {
                    return $this->getRegionSpans($region['node']);
                }), ($flags >> 8) & 0x0F);
                break;
            case self::EMFPLUS_OFFSET_CLIP:
                $offsetX = $data->readFloat();
                $offsetY = $data->readFloat();
                if ($this->state['clip'] !== null) {
                    $this->state['clip'] = $this->getSpans(function () use ($offsetX, $offsetY): array {
                        return (array) $this->renderer->offsetClipRegion($offsetX, $offsetY)->getClipSpans();
                    });
                }
                break;

                // Drawing
            case self::EMFPLUS_CLEAR:
                $color = $data->readColor();
                $this->draw(function () use ($color): void {
                    $this->renderer->clear($color);
                });
                break;
            case self::EMFPLUS_FILL_RECTS:
            case self::EMFPLUS_DRAW_RECTS:
                $paint = $type == self::EMFPLUS_FILL_RECTS ? $this->getBrush($flags, $data->readUInt32()) : $this->getPen($id);
                $figures = [];
                $count = $data->readUInt32();
                for ($i = 0; $i < $count; ++$i) {
                    $figures[] = $this->getRectFigure($data->readRect($isCompressed));
                }
                $this->drawFigures($figures, $paint);
                break;
            case self::EMFPLUS_FILL_POLYGON:
                $brush = $this->getBrush($flags, $data->readUInt32());
                $points = $data->readPoints($data->readUInt32(), $isCompressed, $isRelative);
                $this->drawFigures([$this->getPolylineFigure($points, true)], $brush);
                break;
            case self::EMFPLUS_DRAW_LINES:
                $points = $data->readPoints($data->readUInt32(), $isCompressed, $isRelative);
                $this->drawFigures([$this->getPolylineFigure($points, ($flags & self::FLAG_CLOSED) > 0)], $this->getPen($id));
                break;
            case self::EMFPLUS_FILL_ELLIPSE:
            case self::EMFPLUS_DRAW_ELLIPSE:
                $paint = $type == self::EMFPLUS_FILL_ELLIPSE ? $this->getBrush($flags, $data->readUInt32()) : $this->getPen($id);
                $this->drawFigures([$this->getEllipseFigure($data->readRect($isCompressed))], $paint);
                break;
            case self::EMFPLUS_FILL_PIE:
            case self::EMFPLUS_DRAW_PIE:
            case self::EMFPLUS_DRAW_ARC:
                $paint = $type == self::EMFPLUS_FILL_PIE ? $this->getBrush($flags, $data->readUInt32()) : $this->getPen($id);
                $startAngle = $data->readFloat();
                $sweepAngle = $data->readFloat();
                $rect = $data->readRect($isCompressed);
                $points = $this->getArcPoints($rect, $startAngle, $sweepAngle);
                if ($type != self::EMFPLUS_DRAW_ARC) {
                    $points[] = [$rect[0] + $rect[2] / 2, $rect[1] + $rect[3] / 2];
                }
                $this->drawFigures([$this->getPolylineFigure($points, $type != self::EMFPLUS_DRAW_ARC)], $paint);
                break;
            case self::EMFPLUS_FILL_REGION:
                $brush = $this->getBrush($flags, $data->readUInt32());
                $region = $this->getObject($id, 'region');
                $this->draw(function () use ($region, $brush): void {
                    $spans = $this->getRegionSpans($region['node']);
                    $this->renderer->setClipSpans($this->state['clip'] === null ? $spans : Rasterizer::combineSpans($this->state['clip'], $spans, Rasterizer::RGN_AND));
                    if (isset($brush['shader'])) {
                        $this->renderer->selectObject($brush)->drawPathFigures([$this->getRectFigure([-1e6, -1e6, 2e6, 2e6])], true, false);
                    } else {
                        $this->renderer->clear($brush['color']);
                    }
                });
                break;
            case self::EMFPLUS_FILL_PATH:
                $brush = $this->getBrush($flags, $data->readUInt32());
                $this->drawFigures($this->getObject($id, 'path')['figures'], $brush);
                break;
            case self::EMFPLUS_DRAW_PATH:
                $pen = $this->getPen($data->readUInt32());
                $this->drawFigures($this->getObject($id, 'path')['figures'], $pen);
                break;
            case self::EMFPLUS_FILL_CLOSED_CURVE:
                $brush = $this->getBrush($flags, $data->readUInt32());
                $tension = $data->readFloat();
                $points = $data->readPoints($data->readUInt32(), $isCompressed, $isRelative);
                $this->drawFigures([$this->getCurveFigure($points, $tension, true)], $brush, ($flags & self::FLAG_CLOSED) > 0);
                break;
            case self::EMFPLUS_DRAW_CLOSED_CURVE:
                $tension = $data->readFloat();
                $points = $data->readPoints($data->readUInt32(), $isCompressed, $isRelative);
                $this->drawFigures([$this->getCurveFigure($points, $tension, true)], $this->getPen($id));
                break;
            case self::EMFPLUS_DRAW_CURVE:
                $tension = $data->readFloat();
                $offset = $data->readUInt32();
                $segments = $data->readUInt32();
                $points = $data->readPoints($data->readUInt32(), $isCompressed);
                $this->drawFigures([$this->getCurveFigure($points, $tension, false, $offset, $segments)], $this->getPen($id));
                break;
            case self::EMFPLUS_DRAW_BEZIERS:
                $points = $data->readPoints($data->readUInt32(), $isCompressed, $isRelative);
                $types = [];
                foreach ($points as $i => $point) {
                    $types[] = $i == 0 ? 0 : 3;
                }
                $this->drawFigures([['points' => $points, 'types' => $types, 'closed' => false]], $this->getPen($id));
                break;
            case self::EMFPLUS_DRAW_IMAGE:
            case self::EMFPLUS_DRAW_IMAGE_POINTS:
                $this->drawImage($type, $id, $isCompressed, $isRelative, $data);
                break;
            case self::EMFPLUS_DRAW_STRING:
                $this->drawString($flags, $id, $data);
                break;
            case self::EMFPLUS_DRAW_DRIVER_STRING:
                $this->drawDriverString($flags, $id, $data);
                break;
            default:
                throw new WMFException(sprintf('Reader : EMF+ record not implemented : 0x%04x', $type));
        }
    }

    // ---------------------------------------------------------------------
    // Objects
    // ---------------------------------------------------------------------

    /**
     * Reads an object, which may be split in several records
     */
    protected function readObject(int $flags, Buffer $data): void
    {
        $id = $flags & 0xFF;
        $type = ($flags >> 8) & 0x7F;
        if ($flags & 0x8000) {
            // Continued object : the total size precedes the data
            $size = $data->readUInt32();
            if (!isset($this->continuedObjects[$id])) {
                $this->continuedObjects[$id] = ['type' => $type, 'size' => $size, 'data' => ''];
            }
            $this->continuedObjects[$id]['data'] .= $data->read($data->getRemaining());
            if (strlen($this->continuedObjects[$id]['data']) < $size) {
                return;
            }
        } elseif (isset($this->continuedObjects[$id])) {
            // Last part of a continued object
            $this->continuedObjects[$id]['data'] .= $data->read($data->getRemaining());
        }

        if (isset($this->continuedObjects[$id])) {
            $object = $this->continuedObjects[$id];
            unset($this->continuedObjects[$id]);
            $this->objects[$id] = $this->objectReader->read($object['type'], $object['data']);

            return;
        }
        $this->objects[$id] = $this->objectReader->read($type, $data->read($data->getRemaining()));
    }

    /**
     * @return array<string, mixed>
     */
    protected function getObject(int $id, string $type): array
    {
        if (!isset($this->objects[$id]) || $this->objects[$id]['type'] != $type) {
            throw new WMFException(sprintf('Reader : Invalid file : EMF+ object %d is not a %s', $id, $type));
        }

        return $this->objects[$id];
    }

    /**
     * Returns the brush of a record : a solid color (flag S) or a brush object
     *
     * @return array<string, mixed>
     */
    protected function getBrush(int $flags, int $brushId): array
    {
        if ($flags & self::FLAG_SOLID_COLOR) {
            return ObjectReader::getSolidBrush(Buffer::toColor($brushId));
        }

        return $this->getObject($brushId & 0xFF, 'brush');
    }

    /**
     * @return array<string, mixed>
     */
    protected function getPen(int $penId): array
    {
        return $this->getObject($penId & 0xFF, 'pen');
    }

    // ---------------------------------------------------------------------
    // Transforms
    // ---------------------------------------------------------------------

    /**
     * Number of EMF+ pixels per unit
     */
    protected function getUnitPixels(int $unit, float $dpi): float
    {
        switch ($unit) {
            case 3: // UnitPoint
                return $dpi / 72;
            case 4: // UnitInch
                return $dpi;
            case 5: // UnitDocument
                return $dpi / 300;
            case 6: // UnitMillimeter
                return $dpi / 25.4;
            default: // UnitWorld, UnitDisplay, UnitPixel
                return 1.0;
        }
    }

    /**
     * Multiplies the world transform : the matrix is applied after the world transform (flag A) or before
     *
     * @param array<float> $matrix
     */
    protected function multiplyWorld(array $matrix, int $flags): void
    {
        $this->state['world'] = ($flags & 0x2000)
            ? self::multiply($this->state['world'], $matrix)
            : self::multiply($matrix, $this->state['world']);
    }

    /**
     * Starts a container : its coordinates are mapped (by the matrix) to the world coordinates of the current state
     *
     * @param array<float> $mapping
     */
    protected function beginContainer(array $mapping): void
    {
        $this->state['base'] = self::multiply($mapping, $this->getMatrix());
        $this->state['world'] = self::IDENTITY;
        $this->state['pageUnit'] = 1;
        $this->state['pageScale'] = 1.0;
    }

    /**
     * Returns the matrix converting world coordinates to device units of the EMF file
     *
     * @return array<float>
     */
    protected function getMatrix(): array
    {
        $pageX = $this->state['pageScale'] * $this->getUnitPixels($this->state['pageUnit'], $this->dpiX);
        $pageY = $this->state['pageScale'] * $this->getUnitPixels($this->state['pageUnit'], $this->dpiY);

        return self::multiply(self::multiply($this->state['world'], [$pageX, 0.0, 0.0, $pageY, 0.0, 0.0]), $this->state['base']);
    }

    /**
     * Number of EMF+ pixels per world unit (to convert sizes in other units)
     */
    protected function getWorldPixels(): float
    {
        $pageX = $this->state['pageScale'] * $this->getUnitPixels($this->state['pageUnit'], $this->dpiX);
        list($m11, $m12, $m21, $m22) = $this->state['world'];

        return sqrt(abs($m11 * $m22 - $m12 * $m21)) * abs($pageX) ?: 1.0;
    }

    /**
     * Product of matrices : the first one is applied first
     *
     * @param array<float> $first
     * @param array<float> $second
     *
     * @return array<float>
     */
    public static function multiply(array $first, array $second): array
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

    // ---------------------------------------------------------------------
    // Drawing
    // ---------------------------------------------------------------------

    /**
     * Calls the renderer with the state of EMF+ (transform & clipping), then restores the state of EMF
     *
     * @param callable(): void $callback
     * @param array<float>|null $matrix Matrix applied before the world transform
     */
    protected function draw(callable $callback, ?array $matrix = null): void
    {
        $this->renderer
            ->saveDC()
            ->setMapMode(Renderer::MM_TEXT)
            ->setWindowOrg(0, 0)
            ->setViewportOrg(0, 0)
            ->setWorldTransform($matrix ? self::multiply($matrix, $this->getMatrix()) : $this->getMatrix())
            ->setClipSpans($this->state['clip'])
            ->setBkMode(1)
            ->setTextAlign(0)
            ->setPolyFillMode(Renderer::ALTERNATE);
        try {
            $callback();
        } finally {
            $this->renderer->restoreDC(-1);
        }
    }

    /**
     * Returns spans computed with the state of EMF+
     *
     * @param callable(): array<int, array<array<int>>> $callback
     *
     * @return array<int, array<array<int>>>
     */
    protected function getSpans(callable $callback): array
    {
        $spans = [];
        $this->draw(function () use ($callback, &$spans): void {
            $spans = $callback();
        });

        return $spans;
    }

    /**
     * Fills (with a brush) or strokes (with a pen) figures
     *
     * @param array<array{points: array<array<float>>, types: array<int>, closed: bool}> $figures
     * @param array<string, mixed> $paint Brush or pen
     */
    protected function drawFigures(array $figures, array $paint, bool $isWinding = false): void
    {
        $this->draw(function () use ($figures, $paint, $isWinding): void {
            if ($paint['type'] == 'brush') {
                $this->renderer
                    ->setPolyFillMode($isWinding ? Renderer::WINDING : Renderer::ALTERNATE)
                    ->selectObject($paint)
                    ->drawPathFigures($figures, true, false);

                return;
            }
            $this->renderer
                ->selectObject($this->getRendererPen($paint))
                ->setMiterLimit($paint['miterLimit'] ?? 10)
                ->drawPathFigures($figures, false, true);
        });
    }

    /**
     * Converts the width of a pen to world units
     *
     * @param array<string, mixed> $pen
     *
     * @return array<string, mixed>
     */
    protected function getRendererPen(array $pen): array
    {
        if ($pen['unit'] != 0 && $pen['width'] > 0) {
            $pen['width'] = $pen['width'] * $this->getUnitPixels($pen['unit'], $this->dpiX) / $this->getWorldPixels();
        }
        // A pen with a width of 0 is one pixel wide
        $pen['geometric'] = $pen['width'] > 0;

        return $pen;
    }

    /**
     * Returns the spans of a region node (see ObjectReader::readRegionNode())
     *
     * @param array<string, mixed> $node
     *
     * @return array<int, array<array<int>>>
     */
    protected function getRegionSpans(array $node): array
    {
        switch ($node['type']) {
            case ObjectReader::REGION_RECT:
                return $this->renderer->getFiguresSpans([$this->getRectFigure($node['rect'])], false);
            case ObjectReader::REGION_PATH:
                return $this->renderer->getFiguresSpans($node['path']['figures'], false);
            case ObjectReader::REGION_EMPTY:
                return [];
            case ObjectReader::REGION_INFINITE:
                return $this->renderer->getFullSpans();
            default:
                $left = $this->getRegionSpans($node['left']);
                $right = $this->getRegionSpans($node['right']);
                switch ($node['type']) {
                    case ObjectReader::REGION_AND:
                        return Rasterizer::combineSpans($left, $right, Rasterizer::RGN_AND);
                    case ObjectReader::REGION_UNION:
                        return Rasterizer::combineSpans($left, $right, Rasterizer::RGN_OR);
                    case ObjectReader::REGION_XOR:
                        return Rasterizer::combineSpans($left, $right, Rasterizer::RGN_XOR);
                    case ObjectReader::REGION_EXCLUDE:
                        return Rasterizer::combineSpans($left, $right, Rasterizer::RGN_DIFF);
                    default: // REGION_COMPLEMENT
                        return Rasterizer::combineSpans($right, $left, Rasterizer::RGN_DIFF);
                }
        }
    }

    /**
     * Combines spans with the clipping region
     *
     * @param array<int, array<array<int>>> $spans
     * @param int $mode Replace (0), Intersect (1), Union (2), XOR (3), Exclude (4) or Complement (5)
     */
    protected function combineClip(array $spans, int $mode): void
    {
        if ($mode == 0) {
            $this->state['clip'] = $spans;

            return;
        }
        $current = $this->state['clip'] ?? $this->getSpans(function (): array {
            return $this->renderer->getFullSpans();
        });
        $modes = [1 => Rasterizer::RGN_AND, 2 => Rasterizer::RGN_OR, 3 => Rasterizer::RGN_XOR, 4 => Rasterizer::RGN_DIFF];
        $this->state['clip'] = $mode == 5
            ? Rasterizer::combineSpans($spans, $current, Rasterizer::RGN_DIFF)
            : Rasterizer::combineSpans($current, $spans, $modes[$mode] ?? Rasterizer::RGN_COPY);
    }

    /**
     * @param array<float> $rect [x, y, width, height]
     *
     * @return array{points: array<array<float>>, types: array<int>, closed: bool}
     */
    protected function getRectFigure(array $rect): array
    {
        list($x, $y, $width, $height) = $rect;

        return $this->getPolylineFigure([[$x, $y], [$x + $width, $y], [$x + $width, $y + $height], [$x, $y + $height]], true);
    }

    /**
     * @param array<array<float>> $points
     *
     * @return array{points: array<array<float>>, types: array<int>, closed: bool}
     */
    protected function getPolylineFigure(array $points, bool $closed): array
    {
        $types = [];
        foreach ($points as $i => $point) {
            $types[] = $i == 0 ? 0 : 1;
        }

        return ['points' => $points, 'types' => $types, 'closed' => $closed];
    }

    /**
     * An ellipse made of 4 Bézier curves
     *
     * @param array<float> $rect [x, y, width, height]
     *
     * @return array{points: array<array<float>>, types: array<int>, closed: bool}
     */
    protected function getEllipseFigure(array $rect): array
    {
        $radiusX = $rect[2] / 2;
        $radiusY = $rect[3] / 2;
        $centerX = $rect[0] + $radiusX;
        $centerY = $rect[1] + $radiusY;
        // Distance of the control points (4 / 3 * tan(PI / 8))
        $kappaX = 0.5522847498 * $radiusX;
        $kappaY = 0.5522847498 * $radiusY;

        return [
            'points' => [
                [$centerX + $radiusX, $centerY],
                [$centerX + $radiusX, $centerY + $kappaY], [$centerX + $kappaX, $centerY + $radiusY], [$centerX, $centerY + $radiusY],
                [$centerX - $kappaX, $centerY + $radiusY], [$centerX - $radiusX, $centerY + $kappaY], [$centerX - $radiusX, $centerY],
                [$centerX - $radiusX, $centerY - $kappaY], [$centerX - $kappaX, $centerY - $radiusY], [$centerX, $centerY - $radiusY],
                [$centerX + $kappaX, $centerY - $radiusY], [$centerX + $radiusX, $centerY - $kappaY], [$centerX + $radiusX, $centerY],
            ],
            'types' => [0, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3],
            'closed' => true,
        ];
    }

    /**
     * Returns the points of an elliptic arc (angles in degrees, clockwise)
     *
     * @param array<float> $rect [x, y, width, height]
     *
     * @return array<array<float>>
     */
    protected function getArcPoints(array $rect, float $startAngle, float $sweepAngle): array
    {
        $radiusX = $rect[2] / 2;
        $radiusY = $rect[3] / 2;
        $centerX = $rect[0] + $radiusX;
        $centerY = $rect[1] + $radiusY;
        $steps = (int) max(4, min(360, ceil(abs($sweepAngle) / 3)));

        $points = [];
        for ($i = 0; $i <= $steps; ++$i) {
            $angle = deg2rad($startAngle + $sweepAngle * $i / $steps);
            // The angles are measured on the ellipse (not on the circle)
            $parameter = atan2($radiusX * sin($angle), $radiusY * cos($angle));
            $points[] = [$centerX + $radiusX * cos($parameter), $centerY + $radiusY * sin($parameter)];
        }

        return $points;
    }

    /**
     * Converts a cardinal spline to Bézier curves
     *
     * @param array<array<float>> $points
     *
     * @return array{points: array<array<float>>, types: array<int>, closed: bool}
     */
    protected function getCurveFigure(array $points, float $tension, bool $closed, int $offset = 0, ?int $segments = null): array
    {
        $count = count($points);
        if ($count < 2) {
            return $this->getPolylineFigure($points, $closed);
        }
        $getPoint = function (int $index) use ($points, $count, $closed): array {
            if ($closed) {
                return $points[($index % $count + $count) % $count];
            }

            return $points[max(0, min($count - 1, $index))];
        };

        $segments = $segments ?? ($closed ? $count : $count - 1);
        $factor = $tension / 3;
        $result = [$getPoint($offset)];
        $types = [0];
        for ($i = $offset; $i < $offset + $segments; ++$i) {
            list($previousX, $previousY) = $getPoint($i - 1);
            list($startX, $startY) = $getPoint($i);
            list($endX, $endY) = $getPoint($i + 1);
            list($nextX, $nextY) = $getPoint($i + 2);
            $result[] = [$startX + $factor * ($endX - $previousX), $startY + $factor * ($endY - $previousY)];
            $result[] = [$endX - $factor * ($nextX - $startX), $endY - $factor * ($nextY - $startY)];
            $result[] = [$endX, $endY];
            array_push($types, 3, 3, 3);
        }

        return ['points' => $result, 'types' => $types, 'closed' => $closed];
    }

    protected function drawImage(int $type, int $id, bool $isCompressed, bool $isRelative, Buffer $data): void
    {
        $image = $this->getObject($id, 'image');
        $data->skip(8);
        $source = $data->readRect();
        if ($type == self::EMFPLUS_DRAW_IMAGE) {
            list($x, $y, $width, $height) = $data->readRect($isCompressed);
            $points = [[$x, $y], [$x + $width, $y], [$x, $y + $height]];
        } else {
            $points = $data->readPoints($data->readUInt32(), $isCompressed, $isRelative);
            if (count($points) < 3) {
                throw new WMFException('Reader : Invalid file : EMF+ image points');
            }
        }

        $bitmap = $image['bitmap'];
        // Metafiles are rendered as a whole
        if ($image['isMetafile']) {
            $source = [0, 0, $bitmap['width'], $bitmap['height']];
        }
        $this->draw(function () use ($bitmap, $points, $source): void {
            $this->renderer->drawImage($bitmap, $points, $source);
        });
    }

    /**
     * Converts a font to a font of the renderer (its height is in world units)
     *
     * @param array<string, mixed> $font
     *
     * @return array<string, mixed>
     */
    protected function getRendererFont(array $font): array
    {
        $height = $font['emSize'];
        if ($font['unit'] != 0) {
            $height = $height * $this->getUnitPixels($font['unit'], $this->dpiY) / $this->getWorldPixels();
        }

        return [
            'type' => 'font',
            'height' => -$height,
            'escapement' => 0,
            'weight' => $font['weight'],
            'italic' => $font['italic'],
            'underline' => $font['underline'],
            'strikeOut' => $font['strikeOut'],
            'charset' => 0,
            'pitchAndFamily' => 0,
            'face' => $font['face'],
        ];
    }

    protected function drawString(int $flags, int $id, Buffer $data): void
    {
        $font = $this->getRendererFont($this->getObject($id, 'font'));
        $brush = $this->getBrush($flags, $data->readUInt32());
        $formatId = $data->readUInt32();
        $length = $data->readUInt32();
        list($x, $y, $width, $height) = $data->readRect();
        $text = Encoding::decodeUTF16($data->readString($length));
        $format = $this->objects[$formatId] ?? null;
        $isFormat = $format && $format['type'] == 'stringFormat';
        $alignment = $isFormat ? $format['alignment'] : 0;
        $lineAlignment = $isFormat ? $format['lineAlignment'] : 0;
        $formatFlags = $isFormat ? $format['flags'] : 0;

        $this->draw(function () use ($font, $brush, $text, $x, $y, $width, $height, $alignment, $lineAlignment, $formatFlags): void {
            $this->renderer->selectObject($font)->setTextColor($brush['color']);
            $layout = [$x, $y, $width, $height];

            // Lines are wrapped in the layout rectangle (except with StringFormatFlagsNoWrap)
            $lines = [];
            foreach (explode("\n", str_replace("\r\n", "\n", $text)) as $paragraph) {
                $lines = array_merge($lines, $width > 0 && !($formatFlags & 0x1000) ? $this->wrapText($paragraph, $width) : [$paragraph]);
            }
            $lineHeight = -$font['height'] * 1.15;
            $totalHeight = count($lines) * $lineHeight;
            if ($lineAlignment == 1 && $height > 0) {
                $y += ($height - $totalHeight) / 2;
            } elseif ($lineAlignment == 2 && $height > 0) {
                $y += $height - $totalHeight;
            }
            // StringAlignmentNear (left), StringAlignmentCenter, StringAlignmentFar (right)
            $textAlign = [0 => 0, 1 => 6, 2 => 2][$alignment] ?? 0;
            if ($width > 0) {
                $x += [0 => 0, 1 => $width / 2, 2 => $width][$alignment] ?? 0;
            }

            // The text is clipped by the layout rectangle (except with StringFormatFlagsNoClip)
            if ($width > 0 && $height > 0 && !($formatFlags & 0x4000)) {
                $spans = $this->renderer->getFiguresSpans([$this->getRectFigure($layout)], false);
                $clip = $this->renderer->getClipSpans();
                $this->renderer->setClipSpans($clip === null ? $spans : Rasterizer::combineSpans($clip, $spans, Rasterizer::RGN_AND));
            }

            $this->renderer->setTextAlign($textAlign);
            foreach ($lines as $index => $line) {
                $this->renderer->textOut($x, $y + $index * $lineHeight, $line);
            }
        });
    }

    /**
     * Splits a paragraph into lines (at spaces) which fit in a width (world units)
     *
     * @return array<string>
     */
    protected function wrapText(string $paragraph, float $width): array
    {
        $words = preg_split('/(?<= )/u', $paragraph, -1, PREG_SPLIT_NO_EMPTY) ?: [''];
        $lines = [];
        $line = '';
        foreach ($words as $word) {
            if ($line !== '' && $this->renderer->getTextWidth(rtrim($line . $word)) > $width) {
                $lines[] = rtrim($line);
                $line = '';
            }
            $line .= $word;
        }
        $lines[] = rtrim($line);

        return $lines;
    }

    protected function drawDriverString(int $flags, int $id, Buffer $data): void
    {
        $font = $this->getRendererFont($this->getObject($id, 'font'));
        $brush = $this->getBrush($flags, $data->readUInt32());
        $options = $data->readUInt32();
        $hasMatrix = $data->readUInt32() != 0;
        $count = $data->readUInt32();
        // DriverStringOptionsCmapLookup : the glyphs are characters, else they are indexes of glyphs in the font
        if (!($options & 0x01)) {
            throw new WMFException('Reader : EMF+ glyph indexes not implemented');
        }
        $characters = [];
        for ($i = 0; $i < $count; ++$i) {
            $characters[] = Encoding::encodeUTF8($data->readUInt16());
        }
        $positions = $data->readPoints($count);
        $matrix = $hasMatrix ? $data->readMatrix() : null;

        $this->draw(function () use ($font, $brush, $options, $characters, $positions): void {
            $this->renderer->selectObject($font)->setTextColor($brush['color']);
            // DriverStringOptionsRealizedAdvance : only the first position is used
            if ($options & 0x04) {
                $this->renderer->setTextAlign(0x18)->textOut($positions[0][0] ?? 0, $positions[0][1] ?? 0, implode('', $characters));

                return;
            }
            $glyphs = [];
            foreach ($characters as $i => $character) {
                $glyphs[] = [$character, $positions[$i][0], $positions[$i][1]];
            }
            $this->renderer->glyphsOut($glyphs);
        }, $matrix);
    }
}
