<?php

declare(strict_types=1);

namespace PhpOffice\WMF\Reader\EMF;

use GdImage;
use PhpOffice\WMF\Exception\WMFException;
use PhpOffice\WMF\Reader\Detector;
use PhpOffice\WMF\Reader\EMFPlus\Player;
use PhpOffice\WMF\Reader\GDTrait;
use PhpOffice\WMF\Renderer\Bitmap;
use PhpOffice\WMF\Renderer\Encoding;
use PhpOffice\WMF\Renderer\FontResolver;
use PhpOffice\WMF\Renderer\GD as Renderer;

/**
 * Reader of EMF files based on GD
 *
 * The records are drawn by the renderer PhpOffice\WMF\Renderer\GD.
 *
 * @see https://learn.microsoft.com/en-us/openspecs/windows_protocols/ms-emf/
 */
class GD extends ReaderAbstract
{
    use GDTrait;

    public const EMR_HEADER = 0x01;
    public const EMR_POLYBEZIER = 0x02;
    public const EMR_POLYGON = 0x03;
    public const EMR_POLYLINE = 0x04;
    public const EMR_POLYBEZIERTO = 0x05;
    public const EMR_POLYLINETO = 0x06;
    public const EMR_POLYPOLYLINE = 0x07;
    public const EMR_POLYPOLYGON = 0x08;
    public const EMR_SETWINDOWEXTEX = 0x09;
    public const EMR_SETWINDOWORGEX = 0x0A;
    public const EMR_SETVIEWPORTEXTEX = 0x0B;
    public const EMR_SETVIEWPORTORGEX = 0x0C;
    public const EMR_SETBRUSHORGEX = 0x0D;
    public const EMR_EOF = 0x0E;
    public const EMR_SETPIXELV = 0x0F;
    public const EMR_SETMAPPERFLAGS = 0x10;
    public const EMR_SETMAPMODE = 0x11;
    public const EMR_SETBKMODE = 0x12;
    public const EMR_SETPOLYFILLMODE = 0x13;
    public const EMR_SETROP2 = 0x14;
    public const EMR_SETSTRETCHBLTMODE = 0x15;
    public const EMR_SETTEXTALIGN = 0x16;
    public const EMR_SETCOLORADJUSTMENT = 0x17;
    public const EMR_SETTEXTCOLOR = 0x18;
    public const EMR_SETBKCOLOR = 0x19;
    public const EMR_MOVETOEX = 0x1B;
    public const EMR_OFFSETCLIPRGN = 0x1A;
    public const EMR_SETMETARGN = 0x1C;
    public const EMR_EXCLUDECLIPRECT = 0x1D;
    public const EMR_INTERSECTCLIPRECT = 0x1E;
    public const EMR_SCALEVIEWPORTEXTEX = 0x1F;
    public const EMR_SCALEWINDOWEXTEX = 0x20;
    public const EMR_SAVEDC = 0x21;
    public const EMR_RESTOREDC = 0x22;
    public const EMR_SETWORLDTRANSFORM = 0x23;
    public const EMR_MODIFYWORLDTRANSFORM = 0x24;
    public const EMR_SELECTOBJECT = 0x25;
    public const EMR_CREATEPEN = 0x26;
    public const EMR_CREATEBRUSHINDIRECT = 0x27;
    public const EMR_DELETEOBJECT = 0x28;
    public const EMR_ANGLEARC = 0x29;
    public const EMR_ELLIPSE = 0x2A;
    public const EMR_RECTANGLE = 0x2B;
    public const EMR_ROUNDRECT = 0x2C;
    public const EMR_ARC = 0x2D;
    public const EMR_CHORD = 0x2E;
    public const EMR_PIE = 0x2F;
    public const EMR_SELECTPALETTE = 0x30;
    public const EMR_CREATEPALETTE = 0x31;
    public const EMR_REALIZEPALETTE = 0x34;
    public const EMR_LINETO = 0x36;
    public const EMR_ARCTO = 0x37;
    public const EMR_SETARCDIRECTION = 0x39;
    public const EMR_SETMITERLIMIT = 0x3A;
    public const EMR_BEGINPATH = 0x3B;
    public const EMR_ENDPATH = 0x3C;
    public const EMR_CLOSEFIGURE = 0x3D;
    public const EMR_FILLPATH = 0x3E;
    public const EMR_STROKEANDFILLPATH = 0x3F;
    public const EMR_STROKEPATH = 0x40;
    public const EMR_FLATTENPATH = 0x41;
    public const EMR_ABORTPATH = 0x44;
    public const EMR_SELECTCLIPPATH = 0x43;
    public const EMR_GDICOMMENT = 0x46;
    public const EMR_EXTSELECTCLIPRGN = 0x4B;
    public const EMR_BITBLT = 0x4C;
    public const EMR_STRETCHBLT = 0x4D;
    public const EMR_SETDIBITSTODEVICE = 0x50;
    public const EMR_STRETCHDIBITS = 0x51;
    public const EMR_EXTCREATEFONTINDIRECTW = 0x52;
    public const EMR_EXTTEXTOUTA = 0x53;
    public const EMR_EXTTEXTOUTW = 0x54;
    public const EMR_POLYBEZIER16 = 0x55;
    public const EMR_POLYGON16 = 0x56;
    public const EMR_POLYLINE16 = 0x57;
    public const EMR_POLYBEZIERTO16 = 0x58;
    public const EMR_POLYLINETO16 = 0x59;
    public const EMR_POLYPOLYLINE16 = 0x5A;
    public const EMR_POLYPOLYGON16 = 0x5B;
    public const EMR_CREATEMONOBRUSH = 0x5D;
    public const EMR_CREATEDIBPATTERNBRUSHPT = 0x5E;
    public const EMR_EXTCREATEPEN = 0x5F;
    public const EMR_SETICMMODE = 0x62;
    public const EMR_PIXELFORMAT = 0x68;
    public const EMR_SMALLTEXTOUT = 0x6C;
    public const EMR_SETLAYOUT = 0x73;
    public const EMR_GRADIENTFILL = 0x76;

    /**
     * Records which have no effect on the rendering
     */
    protected const IGNORED_RECORDS = [
        self::EMR_SETBRUSHORGEX,
        self::EMR_SETMAPPERFLAGS,
        self::EMR_SETROP2,
        self::EMR_SETSTRETCHBLTMODE,
        self::EMR_SETCOLORADJUSTMENT,
        self::EMR_SETMETARGN,
        self::EMR_SELECTPALETTE,
        self::EMR_REALIZEPALETTE,
        self::EMR_FLATTENPATH,
        self::EMR_GDICOMMENT,
        self::EMR_SETICMMODE,
        self::EMR_PIXELFORMAT,
        self::EMR_SETLAYOUT,
    ];

    /**
     * Resolution of the output image
     */
    public const DPI = 96;

    /**
     * @var Renderer
     */
    protected $renderer;
    /**
     * @var FontResolver
     */
    protected $fontResolver;
    /**
     * Objects (pens, brushes, fonts) indexed by their handle
     *
     * @var array<int, array<string, mixed>>
     */
    protected $objects = [];

    /**
     * If the EMF+ records are drawn (else, only the EMF records are drawn)
     *
     * @var bool
     */
    protected $isEMFPlusEnabled = true;
    /**
     * If the last loaded file has been drawn with its EMF+ records
     *
     * @var bool
     */
    protected $isEMFPlusRendered = false;

    public function __construct()
    {
        $this->fontResolver = new FontResolver();
    }

    /**
     * Enables/disables the drawing of EMF+ records
     *
     * When enabled (by default), EMF+ files are drawn with their EMF+ records, and only the EMF records following
     * EmfPlusGetDC records are drawn. If the EMF+ records can not be drawn, the file is drawn with its EMF records.
     */
    public function setEMFPlusEnabled(bool $isEnabled): self
    {
        $this->isEMFPlusEnabled = $isEnabled;

        return $this;
    }

    public function isEMFPlusEnabled(): bool
    {
        return $this->isEMFPlusEnabled;
    }

    /**
     * Returns if the last loaded file has been drawn with its EMF+ records
     */
    public function isEMFPlusRendered(): bool
    {
        return $this->isEMFPlusRendered;
    }

    /**
     * Returns if the file is a EMF file (EMF+ dual files are EMF files too)
     */
    public function isEMF(): bool
    {
        return Detector::isEMF((string) $this->content);
    }

    /**
     * Defines the directories where TrueType/OpenType fonts are searched (recursively)
     *
     * By default, the system directories are used
     *
     * @param array<string> $directories
     */
    public function setFontDirectories(array $directories): self
    {
        $this->fontResolver->setDirectories($directories);

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getFontDirectories(): array
    {
        return $this->fontResolver->getDirectories();
    }

    protected function loadContent(): bool
    {
        if (!$this->isEMF()) {
            return false;
        }

        // Corrupted files must not raise PHP errors
        set_error_handler(function (int $errno, string $errstr): bool {
            if (!(error_reporting() & $errno)) {
                return false;
            }
            throw new WMFException('Reader : Invalid file : ' . $errstr);
        });
        $this->isEMFPlusRendered = false;
        try {
            if ($this->isEMFPlusEnabled && Detector::isEMFPlus((string) $this->content)) {
                try {
                    $this->readRecords(true);
                    $this->isEMFPlusRendered = true;
                } catch (WMFException $e) {
                    // The EMF+ records can not be drawn : the EMF records are drawn
                    $this->readRecords(false);
                }
            } else {
                $this->readRecords(false);
            }
        } catch (WMFException $e) {
            if ($this->hasExceptionsEnabled()) {
                throw $e;
            }

            return false;
        } finally {
            restore_error_handler();
        }

        return true;
    }

    /**
     * @param bool $isEMFPlus If the EMF+ records are drawn (the EMF records are then drawn only after EmfPlusGetDC records)
     */
    protected function readRecords(bool $isEMFPlus = false): void
    {
        $this->readHeader();
        $this->objects = [];
        $player = $isEMFPlus ? new Player($this->renderer) : null;
        $isEMFPlaying = !$isEMFPlus;

        $contentLen = strlen($this->content);
        // The first record is the header
        list(, $pos) = unpack('V', (string) substr($this->content, 4, 4));
        $pos = (int) $pos;

        while ($pos + 8 <= $contentLen) {
            list(, $recordType, $size) = unpack('V2', (string) substr($this->content, $pos, 8));
            $recordType = (int) $recordType;
            $size = (int) $size;
            if ($size < 8 || $pos + $size > $contentLen) {
                break;
            }
            $record = (string) substr($this->content, $pos, $size);
            $pos += $size;

            if ($recordType == self::EMR_EOF) {
                break;
            }
            // EMF+ records are stored in comments (after the "EMF+" identifier)
            if ($player && $recordType == self::EMR_GDICOMMENT && $size >= 16 && (string) substr($record, 12, 4) === 'EMF+') {
                list(, $dataSize) = unpack('V', (string) substr($record, 8, 4));
                $isEMFPlaying = $player->play((string) substr($record, 16, max(0, min((int) $dataSize, $size - 12) - 4)));
                continue;
            }
            if (!$isEMFPlaying) {
                continue;
            }
            if (in_array($recordType, self::IGNORED_RECORDS)) {
                continue;
            }
            if (!$this->readRecord($recordType, $record)) {
                throw new WMFException('Reader : Function not implemented : 0x' . str_pad(dechex($recordType), 4, '0', STR_PAD_LEFT));
            }
        }

        $this->gd = $this->renderer->render();
    }

    /**
     * Creates the renderer from the header : the frame defines the size of the image
     */
    protected function readHeader(): void
    {
        $header = unpack(
            'Vtype/Vsize/lboundsLeft/lboundsTop/lboundsRight/lboundsBottom/lframeLeft/lframeTop/lframeRight/lframeBottom/Vsignature/Vversion/Vbytes/Vrecords/vhandles/vreserved/VnDescription/VoffDescription/VnPalEntries/ldeviceX/ldeviceY/lmillimetersX/lmillimetersY',
            (string) substr($this->content, 0, 88)
        );

        $pixelsPerMmX = $header['millimetersX'] > 0 ? $header['deviceX'] / $header['millimetersX'] : 96 / 25.4;
        $pixelsPerMmY = $header['millimetersY'] > 0 ? $header['deviceY'] / $header['millimetersY'] : 96 / 25.4;

        $frameWidth = $header['frameRight'] - $header['frameLeft'];
        $frameHeight = $header['frameBottom'] - $header['frameTop'];
        if ($frameWidth > 0 && $frameHeight > 0) {
            // The frame (in 0.01mm) defines the physical size of the image
            $originX = $header['frameLeft'] * $pixelsPerMmX / 100;
            $originY = $header['frameTop'] * $pixelsPerMmY / 100;
            $deviceWidth = $frameWidth * $pixelsPerMmX / 100;
            $deviceHeight = $frameHeight * $pixelsPerMmY / 100;
            $width = (int) max(1, round($frameWidth / 2540 * self::DPI));
            $height = (int) max(1, round($frameHeight / 2540 * self::DPI));
        } else {
            // Fallback on the bounds (in device units)
            $originX = $header['boundsLeft'];
            $originY = $header['boundsTop'];
            $deviceWidth = $width = (int) max(1, $header['boundsRight'] - $header['boundsLeft'] + 1);
            $deviceHeight = $height = (int) max(1, $header['boundsBottom'] - $header['boundsTop'] + 1);
        }

        $this->destroyImage($this->gd);
        $this->gd = false;
        $this->renderer = new Renderer($width, $height, $deviceWidth, $deviceHeight, $originX, $originY, $this->fontResolver, $this->backgroundColor);
        $this->renderer->setPixelsPerMm($pixelsPerMmX, $pixelsPerMmY);
    }

    /**
     * Returns false if the record is not supported
     */
    protected function readRecord(int $recordType, string $record): bool
    {
        $renderer = $this->renderer;
        switch ($recordType) {
            case self::EMR_SETMAPMODE:
                $renderer->setMapMode($this->readUInt($record, 8));
                break;
            case self::EMR_SETWINDOWEXTEX:
                $renderer->setWindowExt(...$this->readInts($record, 8, 2));
                break;
            case self::EMR_SETWINDOWORGEX:
                $renderer->setWindowOrg(...$this->readInts($record, 8, 2));
                break;
            case self::EMR_SETVIEWPORTEXTEX:
                $renderer->setViewportExt(...$this->readInts($record, 8, 2));
                break;
            case self::EMR_SETVIEWPORTORGEX:
                $renderer->setViewportOrg(...$this->readInts($record, 8, 2));
                break;
            case self::EMR_SCALEVIEWPORTEXTEX:
                $renderer->scaleViewportExt(...$this->readInts($record, 8, 4));
                break;
            case self::EMR_SCALEWINDOWEXTEX:
                $renderer->scaleWindowExt(...$this->readInts($record, 8, 4));
                break;
            case self::EMR_SETWORLDTRANSFORM:
                $renderer->setWorldTransform(array_values(unpack('g6', (string) substr($record, 8, 24))));
                break;
            case self::EMR_MODIFYWORLDTRANSFORM:
                $renderer->modifyWorldTransform(array_values(unpack('g6', (string) substr($record, 8, 24))), $this->readUInt($record, 32));
                break;
            case self::EMR_SETARCDIRECTION:
                $renderer->setArcDirection($this->readUInt($record, 8));
                break;
            case self::EMR_SETTEXTCOLOR:
                $renderer->setTextColor($this->readColor($record, 8));
                break;
            case self::EMR_SETBKCOLOR:
                $renderer->setBkColor($this->readColor($record, 8));
                break;
            case self::EMR_SETBKMODE:
                $renderer->setBkMode($this->readUInt($record, 8));
                break;
            case self::EMR_SETTEXTALIGN:
                $renderer->setTextAlign($this->readUInt($record, 8));
                break;
            case self::EMR_SETMITERLIMIT:
                $renderer->setMiterLimit($this->readUInt($record, 8));
                break;
            case self::EMR_SETPOLYFILLMODE:
                $renderer->setPolyFillMode($this->readUInt($record, 8));
                break;
            case self::EMR_SAVEDC:
                $renderer->saveDC();
                break;
            case self::EMR_RESTOREDC:
                $renderer->restoreDC($this->readInts($record, 8, 1)[0]);
                break;

            case self::EMR_CREATEPEN:
                $pen = unpack('Vih/Vstyle/lwidth/lunused/Cr/Cg/Cb', (string) substr($record, 8, 23));
                $this->objects[$pen['ih']] = [
                    'type' => 'pen',
                    'style' => $pen['style'],
                    'width' => $pen['width'],
                    'geometric' => true,
                    'color' => [$pen['r'], $pen['g'], $pen['b']],
                    // Dashes are drawn only for pens of one pixel
                    'dashes' => $pen['width'] <= 1 ? Renderer::getPenDashes($pen['style'], true, $pen['width']) : null,
                ];
                break;
            case self::EMR_EXTCREATEPEN:
                $pen = unpack('Vih/VoffBmi/VcbBmi/VoffBits/VcbBits/Vstyle/Vwidth/VbrushStyle/Cr/Cg/Cb', (string) substr($record, 8, 35));
                $isGeometric = ($pen['style'] & 0x00010000) > 0;
                // PS_USERSTYLE : lengths of dashes & spaces
                $userStyle = [];
                if (($pen['style'] & 0x0F) == 7 && strlen($record) >= 52) {
                    $count = min($this->readUInt($record, 48), intdiv(strlen($record) - 52, 4));
                    $userStyle = $count > 0 ? array_values(unpack('V' . $count, (string) substr($record, 52, 4 * $count))) : [];
                }
                $this->objects[$pen['ih']] = [
                    'type' => 'pen',
                    'style' => $pen['brushStyle'] == 1 ? 5 : $pen['style'],
                    'width' => $pen['width'],
                    'geometric' => $isGeometric,
                    'color' => [$pen['r'], $pen['g'], $pen['b']],
                    'dashes' => Renderer::getPenDashes($pen['style'], !$isGeometric, $pen['width'], $userStyle),
                ];
                break;
            case self::EMR_CREATEBRUSHINDIRECT:
                $brush = unpack('Vih/Vstyle/Cr/Cg/Cb/x/Vhatch', (string) substr($record, 8, 16));
                $this->objects[$brush['ih']] = [
                    'type' => 'brush',
                    'style' => $brush['style'],
                    'color' => [$brush['r'], $brush['g'], $brush['b']],
                    'hatch' => $brush['hatch'],
                ];
                break;
            case self::EMR_CREATEMONOBRUSH:
            case self::EMR_CREATEDIBPATTERNBRUSHPT:
                // The pattern is tiled (its average color is used when a color is needed)
                $brush = unpack('Vih/Vusage/VoffBmi/VcbBmi/VoffBits/VcbBits', (string) substr($record, 8, 24));
                $bitmap = $this->requireBitmap(
                    Bitmap::readDIB($record, $brush['offBmi'], $brush['offBits'], $brush['cbBmi'], $brush['cbBits'], $brush['usage'] == Bitmap::DIB_PAL_COLORS),
                    $recordType
                );
                $this->objects[$brush['ih']] = [
                    'type' => 'brush',
                    'style' => 3,
                    'color' => Bitmap::getAverageColor($bitmap),
                    'pattern' => $bitmap,
                ];
                break;
            case self::EMR_EXTCREATEFONTINDIRECTW:
                $font = unpack('Vih/lheight/lwidth/lescapement/lorientation/lweight/Citalic/Cunderline/CstrikeOut/Ccharset/CoutPrecision/CclipPrecision/Cquality/CpitchAndFamily', (string) substr($record, 8, 32));
                $this->objects[$font['ih']] = [
                    'type' => 'font',
                    'height' => $font['height'],
                    'escapement' => $font['escapement'],
                    'weight' => $font['weight'],
                    'italic' => $font['italic'] > 0,
                    'underline' => $font['underline'] > 0,
                    'strikeOut' => $font['strikeOut'] > 0,
                    'charset' => $font['charset'],
                    'pitchAndFamily' => $font['pitchAndFamily'],
                    'face' => Encoding::decodeUTF16((string) substr($record, 40, 64)),
                ];
                break;
            case self::EMR_CREATEPALETTE:
                $this->objects[$this->readUInt($record, 8)] = ['type' => 'other'];
                break;
            case self::EMR_SELECTOBJECT:
                $ih = $this->readUInt($record, 8);
                $object = ($ih & 0x80000000) ? Renderer::getStockObject($ih & 0x7FFFFFFF) : ($this->objects[$ih] ?? null);
                if ($object) {
                    $renderer->selectObject($object);
                }
                break;
            case self::EMR_DELETEOBJECT:
                unset($this->objects[$this->readUInt($record, 8)]);
                break;

            case self::EMR_BEGINPATH:
                $renderer->beginPath();
                break;
            case self::EMR_ENDPATH:
                $renderer->endPath();
                break;
            case self::EMR_ABORTPATH:
                $renderer->abortPath();
                break;
            case self::EMR_CLOSEFIGURE:
                $renderer->closeFigure();
                break;
            case self::EMR_FILLPATH:
                $renderer->fillPath();
                break;
            case self::EMR_STROKEPATH:
                $renderer->strokePath();
                break;
            case self::EMR_STROKEANDFILLPATH:
                $renderer->strokeAndFillPath();
                break;
            case self::EMR_SELECTCLIPPATH:
                $renderer->selectClipPath($this->readUInt($record, 8));
                break;

            case self::EMR_INTERSECTCLIPRECT:
                $renderer->intersectClipRect(...$this->readInts($record, 8, 4));
                break;
            case self::EMR_EXCLUDECLIPRECT:
                $renderer->excludeClipRect(...$this->readInts($record, 8, 4));
                break;
            case self::EMR_OFFSETCLIPRGN:
                $renderer->offsetClipRegion(...$this->readInts($record, 8, 2));
                break;
            case self::EMR_EXTSELECTCLIPRGN:
                list(, $cbRgnData, $mode) = unpack('V2', (string) substr($record, 8, 8));
                if ($cbRgnData == 0) {
                    // Only RGN_COPY is allowed : the clipping region is reset
                    $renderer->resetClipRegion();
                    break;
                }
                // Region rectangles are in device units
                $count = $this->readUInt($record, 16 + 8);
                $rectangles = [];
                for ($i = 0; $i < $count; ++$i) {
                    $rectangles[] = $this->readInts($record, 16 + 32 + 16 * $i, 4);
                }
                $renderer->selectClipRegion($rectangles, $mode, true);
                break;

            case self::EMR_POLYGON:
            case self::EMR_POLYGON16:
                $renderer->polygon($this->readPoints($record, 28, $this->readUInt($record, 24), $recordType == self::EMR_POLYGON16));
                break;
            case self::EMR_POLYLINE:
            case self::EMR_POLYLINE16:
                $renderer->polyline($this->readPoints($record, 28, $this->readUInt($record, 24), $recordType == self::EMR_POLYLINE16));
                break;
            case self::EMR_POLYBEZIER:
            case self::EMR_POLYBEZIER16:
                $renderer->polyBezier($this->readPoints($record, 28, $this->readUInt($record, 24), $recordType == self::EMR_POLYBEZIER16));
                break;
            case self::EMR_POLYPOLYGON:
            case self::EMR_POLYPOLYGON16:
            case self::EMR_POLYPOLYLINE:
            case self::EMR_POLYPOLYLINE16:
                $is16 = in_array($recordType, [self::EMR_POLYPOLYGON16, self::EMR_POLYPOLYLINE16]);
                $numPolys = $this->readUInt($record, 24);
                $counts = $numPolys > 0 ? array_values(unpack('V' . $numPolys, (string) substr($record, 32, 4 * $numPolys))) : [];
                $offset = 32 + 4 * $numPolys;
                $polygons = [];
                foreach ($counts as $count) {
                    $polygons[] = $this->readPoints($record, $offset, $count, $is16);
                    $offset += $count * ($is16 ? 4 : 8);
                }
                $renderer->polyPolygon($polygons, in_array($recordType, [self::EMR_POLYPOLYGON, self::EMR_POLYPOLYGON16]));
                break;
            case self::EMR_RECTANGLE:
                $renderer->rectangle(...$this->readInts($record, 8, 4));
                break;
            case self::EMR_ELLIPSE:
                $renderer->ellipse(...$this->readInts($record, 8, 4));
                break;
            case self::EMR_ROUNDRECT:
                $renderer->roundRect(...$this->readInts($record, 8, 6));
                break;
            case self::EMR_ARC:
                $renderer->arc(...$this->readInts($record, 8, 8));
                break;
            case self::EMR_ARCTO:
                $renderer->arcTo(...$this->readInts($record, 8, 8));
                break;
            case self::EMR_CHORD:
                $renderer->chord(...$this->readInts($record, 8, 8));
                break;
            case self::EMR_PIE:
                $renderer->pie(...$this->readInts($record, 8, 8));
                break;
            case self::EMR_ANGLEARC:
                $data = unpack('lx/ly/Vradius/gstart/gsweep', (string) substr($record, 8, 20));
                $renderer->angleArc($data['x'], $data['y'], $data['radius'], $data['start'], $data['sweep']);
                break;
            case self::EMR_SETPIXELV:
                list($x, $y) = $this->readInts($record, 8, 2);
                $renderer->setPixel($x, $y, $this->readColor($record, 16));
                break;
            case self::EMR_EXTTEXTOUTA:
            case self::EMR_EXTTEXTOUTW:
                $this->readText($record, $recordType == self::EMR_EXTTEXTOUTW);
                break;
            case self::EMR_SMALLTEXTOUT:
                $data = unpack('lx/ly/Vchars/Voptions', (string) substr($record, 8, 16));
                $offset = 36;
                $rectangle = [0, 0, 0, 0];
                // ETO_NO_RECT
                if (!($data['options'] & 0x0100)) {
                    $rectangle = $this->readInts($record, $offset, 4);
                    $offset += 16;
                }
                // ETO_SMALL_CHARS : 8-bit characters
                $text = ($data['options'] & 0x0200)
                    ? Encoding::decodeANSI((string) substr($record, $offset, $data['chars']))
                    : Encoding::decodeUTF16((string) substr($record, $offset, 2 * $data['chars']));
                $renderer->textOut($data['x'], $data['y'], $text, [], $data['options'], $rectangle);
                break;
            case self::EMR_MOVETOEX:
                $renderer->moveTo(...$this->readInts($record, 8, 2));
                break;
            case self::EMR_LINETO:
                $renderer->lineTo(...$this->readInts($record, 8, 2));
                break;
            case self::EMR_POLYLINETO:
            case self::EMR_POLYLINETO16:
                $renderer->polylineTo($this->readPoints($record, 28, $this->readUInt($record, 24), $recordType == self::EMR_POLYLINETO16));
                break;
            case self::EMR_POLYBEZIERTO:
            case self::EMR_POLYBEZIERTO16:
                $renderer->polylineTo($this->readPoints($record, 28, $this->readUInt($record, 24), $recordType == self::EMR_POLYBEZIERTO16), true);
                break;

            case self::EMR_STRETCHDIBITS:
                $data = unpack('l4bounds/lxDest/lyDest/lxSrc/lySrc/lcxSrc/lcySrc/VoffBmi/VcbBmi/VoffBits/VcbBits/Vusage/Vrop/lcxDest/lcyDest', (string) substr($record, 8, 72));
                if ($data['rop'] == Renderer::ROP_NOP) {
                    break;
                }
                $bitmap = $this->requireBitmap(
                    Bitmap::readDIB($record, $data['offBmi'], $data['offBits'], $data['cbBmi'], $data['cbBits'], $data['usage'] == Bitmap::DIB_PAL_COLORS),
                    $recordType
                );
                $renderer->drawBitmap($bitmap, $data['xDest'], $data['yDest'], $data['cxDest'], $data['cyDest'], $data['xSrc'], $data['ySrc'], $data['cxSrc'], $data['cySrc'], $data['rop']);
                break;
            case self::EMR_SETDIBITSTODEVICE:
                $this->readSetDIBitsToDevice($record);
                break;
            case self::EMR_GRADIENTFILL:
                $this->readGradientFill($record);
                break;
            case self::EMR_BITBLT:
            case self::EMR_STRETCHBLT:
                $data = unpack('l4bounds/lxDest/lyDest/lcxDest/lcyDest/Vrop/lxSrc/lySrc/g6xform/VbkColor/Vusage/VoffBmi/VcbBmi/VoffBits/VcbBits', (string) substr($record, 8, 92));
                if ($data['rop'] == Renderer::ROP_NOP) {
                    break;
                }
                if ($data['cbBmi'] == 0) {
                    // No source bitmap : the rectangle is filled with the brush (PATCOPY), in black or in white
                    $renderer->patBlt($data['xDest'], $data['yDest'], $data['xDest'] + $data['cxDest'], $data['yDest'] + $data['cyDest'], $data['rop']);
                    break;
                }
                $bitmap = $this->requireBitmap(
                    Bitmap::readDIB($record, $data['offBmi'], $data['offBits'], $data['cbBmi'], $data['cbBits'], $data['usage'] == Bitmap::DIB_PAL_COLORS),
                    $recordType
                );
                $cxSrc = $data['cxDest'];
                $cySrc = $data['cyDest'];
                if ($recordType == self::EMR_STRETCHBLT) {
                    list($cxSrc, $cySrc) = $this->readInts($record, 100, 2);
                }
                $renderer->drawBitmap($bitmap, $data['xDest'], $data['yDest'], $data['cxDest'], $data['cyDest'], $data['xSrc'], $data['ySrc'], $cxSrc, $cySrc, $data['rop']);
                break;
            default:
                return false;
        }

        return true;
    }

    /**
     * Draws a EMR_SETDIBITSTODEVICE record : a band of scan lines of a DIB, copied without stretching
     *
     * The bits only contain the scan lines from iStartScan to iStartScan + cScans - 1 (the scan line 0 is the bottom row
     * of a bottom-up DIB, the top row of a top-down DIB), while the header of the DIB defines the height of the whole DIB.
     * The source rectangle is expressed in the coordinates of the whole DIB, whose origin is its lower-left corner for
     * a bottom-up DIB and its upper-left corner for a top-down DIB.
     */
    protected function readSetDIBitsToDevice(string $record): void
    {
        $data = unpack('l4bounds/lxDest/lyDest/lxSrc/lySrc/lcxSrc/lcySrc/VoffBmi/VcbBmi/VoffBits/VcbBits/Vusage/ViStartScan/VcScans', (string) substr($record, 8, 68));
        if ($data['cScans'] <= 0 || $data['cxSrc'] <= 0 || $data['cySrc'] <= 0 || strlen($record) < $data['offBmi'] + 40) {
            return;
        }
        list(, $height) = unpack('l', (string) substr($record, $data['offBmi'] + 8, 4));
        $isBottomUp = $height > 0;
        // The DIB is decoded with the height of the band
        $band = substr_replace($record, pack('l', $isBottomUp ? $data['cScans'] : -$data['cScans']), $data['offBmi'] + 8, 4);
        $bitmap = $this->requireBitmap(
            Bitmap::readDIB($band, $data['offBmi'], $data['offBits'], $data['cbBmi'], $data['cbBits'], $data['usage'] == Bitmap::DIB_PAL_COLORS),
            self::EMR_SETDIBITSTODEVICE
        );
        // Top row of the source rectangle in the band (whose rows are decoded from top to bottom)
        $ySrc = $isBottomUp
            ? $data['iStartScan'] + $data['cScans'] - $data['ySrc'] - $data['cySrc']
            : $data['ySrc'] - $data['iStartScan'];
        // Only the scan lines of the band are drawn
        $top = max(0, $ySrc);
        $bottom = min($bitmap['height'], $ySrc + $data['cySrc']);
        if ($bottom <= $top) {
            return;
        }
        $this->renderer->drawBitmap(
            $bitmap,
            $data['xDest'],
            $data['yDest'] + $top - $ySrc,
            $data['cxSrc'],
            $bottom - $top,
            $data['xSrc'],
            $top,
            $data['cxSrc'],
            $bottom - $top
        );
    }

    protected function readUInt(string $record, int $offset): int
    {
        list(, $value) = unpack('V', (string) substr($record, $offset, 4));

        return (int) $value;
    }

    /**
     * @return array<int>
     */
    protected function readInts(string $record, int $offset, int $count): array
    {
        return array_values(unpack('l' . $count, (string) substr($record, $offset, 4 * $count)));
    }

    /**
     * Reads a COLORREF
     *
     * @return array<int>
     */
    protected function readColor(string $record, int $offset): array
    {
        return array_values(unpack('C3', (string) substr($record, $offset, 3)));
    }

    /**
     * @return array<array<float>> Logical coordinates
     */
    protected function readPoints(string $record, int $offset, int $count, bool $is16): array
    {
        if ($count <= 0) {
            return [];
        }
        $values = array_values(unpack(($is16 ? 's' : 'l') . ($count * 2), (string) substr($record, $offset, $count * ($is16 ? 4 : 8))));

        $points = [];
        for ($i = 0; $i < $count; ++$i) {
            $points[] = [$values[2 * $i], $values[2 * $i + 1]];
        }

        return $points;
    }

    /**
     * Reads EMR_EXTTEXTOUTA & EMR_EXTTEXTOUTW records
     */
    protected function readText(string $record, bool $isUnicode): void
    {
        $data = unpack('lx/ly/Vchars/VoffString/Voptions/lleft/ltop/lright/lbottom/VoffDx', (string) substr($record, 36, 40));
        $count = $data['chars'];

        $text = '';
        if ($count > 0) {
            $text = $isUnicode
                ? Encoding::decodeUTF16((string) substr($record, $data['offString'], 2 * $count))
                : Encoding::decodeANSI((string) substr($record, $data['offString'], $count));
        }

        // Advances of each character (ETO_PDY : pairs of horizontal & vertical advances)
        $dx = $dy = [];
        if ($count > 0 && $data['offDx'] > 0) {
            $step = ($data['options'] & 0x2000) ? 2 : 1;
            $values = array_values(unpack('l' . ($count * $step), (string) substr($record, $data['offDx'], 4 * $count * $step)));
            for ($i = 0; $i < $count; ++$i) {
                $dx[] = $values[$i * $step];
                if ($step == 2) {
                    $dy[] = $values[$i * $step + 1];
                }
            }
        }

        $this->renderer->textOut($data['x'], $data['y'], $text, $dx, $data['options'], [$data['left'], $data['top'], $data['right'], $data['bottom']], $dy);
    }

    protected function readGradientFill(string $record): void
    {
        list(, $countVertices, $countObjects, $mode) = unpack('V3', (string) substr($record, 24, 12));

        $vertices = [];
        for ($i = 0; $i < $countVertices; ++$i) {
            $vertex = unpack('lx/ly/vr/vg/vb', (string) substr($record, 36 + 16 * $i, 14));
            $vertices[] = [$vertex['x'], $vertex['y'], [$vertex['r'] >> 8, $vertex['g'] >> 8, $vertex['b'] >> 8]];
        }

        // Triangles (GRADIENT_FILL_TRIANGLE) or rectangles (GRADIENT_FILL_RECT_H & GRADIENT_FILL_RECT_V)
        $size = $mode == 2 ? 3 : 2;
        $offset = 36 + 16 * $countVertices;
        $objects = [];
        for ($i = 0; $i < $countObjects; ++$i) {
            $objects[] = array_values(unpack('V' . $size, (string) substr($record, $offset + 4 * $size * $i, 4 * $size)));
        }

        $this->renderer->gradientFill($vertices, $objects, $mode);
    }

    /**
     * @phpstan-ignore-next-line
     *
     * @return GdImage|resource
     */
    public function getResource()
    {
        return $this->gd;
    }
}
