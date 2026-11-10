<?php

declare(strict_types=1);

namespace PhpOffice\WMF\Reader\WMF;

use GdImage;
use PhpOffice\WMF\Exception\WMFException;
use PhpOffice\WMF\Reader\Detector;
use PhpOffice\WMF\Reader\GDTrait;
use PhpOffice\WMF\Renderer\Bitmap;
use PhpOffice\WMF\Renderer\Encoding;
use PhpOffice\WMF\Renderer\FontResolver;
use PhpOffice\WMF\Renderer\GD as Renderer;
use PhpOffice\WMF\Renderer\Rasterizer;

/**
 * Reader of WMF files (placeable or not) based on GD
 *
 * The records are drawn by the renderer PhpOffice\WMF\Renderer\GD.
 *
 * @see https://learn.microsoft.com/en-us/openspecs/windows_protocols/ms-wmf/
 */
class GD extends ReaderAbstract
{
    use GDTrait;

    public const META_EOF = 0x0000;
    public const META_SAVEDC = 0x001E;
    public const META_REALIZEPALETTE = 0x0035;
    public const META_SETPALENTRIES = 0x0037;
    public const META_CREATEPALETTE = 0x00F7;
    public const META_SETBKMODE = 0x0102;
    public const META_SETMAPMODE = 0x0103;
    public const META_SETROP2 = 0x0104;
    public const META_SETRELABS = 0x0105;
    public const META_SETPOLYFILLMODE = 0x0106;
    public const META_SETSTRETCHBLTMODE = 0x0107;
    public const META_SETTEXTCHAREXTRA = 0x0108;
    public const META_RESTOREDC = 0x0127;
    public const META_INVERTREGION = 0x012A;
    public const META_PAINTREGION = 0x012B;
    public const META_SELECTCLIPREGION = 0x012C;
    public const META_SELECTOBJECT = 0x012D;
    public const META_SETTEXTALIGN = 0x012E;
    public const META_RESIZEPALETTE = 0x0139;
    public const META_DIBCREATEPATTERNBRUSH = 0x0142;
    public const META_SETLAYOUT = 0x0149;
    public const META_DELETEOBJECT = 0x01F0;
    public const META_CREATEPATTERNBRUSH = 0x01F9;
    public const META_SETBKCOLOR = 0x0201;
    public const META_SETTEXTCOLOR = 0x0209;
    public const META_SETTEXTJUSTIFICATION = 0x020A;
    public const META_SETWINDOWORG = 0x020B;
    public const META_SETWINDOWEXT = 0x020C;
    public const META_SETVIEWPORTORG = 0x020D;
    public const META_SETVIEWPORTEXT = 0x020E;
    public const META_OFFSETWINDOWORG = 0x020F;
    public const META_OFFSETVIEWPORTORG = 0x0211;
    public const META_LINETO = 0x0213;
    public const META_MOVETO = 0x0214;
    public const META_OFFSETCLIPRGN = 0x0220;
    public const META_FILLREGION = 0x0228;
    public const META_SETMAPPERFLAGS = 0x0231;
    public const META_SELECTPALETTE = 0x0234;
    public const META_CREATEPENINDIRECT = 0x02FA;
    public const META_CREATEFONTINDIRECT = 0x02FB;
    public const META_CREATEBRUSHINDIRECT = 0x02FC;
    public const META_POLYGON = 0x0324;
    public const META_POLYLINE = 0x0325;
    public const META_SCALEWINDOWEXT = 0x0410;
    public const META_SCALEVIEWPORTEXT = 0x0412;
    public const META_EXCLUDECLIPRECT = 0x0415;
    public const META_INTERSECTCLIPRECT = 0x0416;
    public const META_ELLIPSE = 0x0418;
    public const META_FLOODFILL = 0x0419;
    public const META_RECTANGLE = 0x041B;
    public const META_SETPIXEL = 0x041F;
    public const META_FRAMEREGION = 0x0429;
    public const META_ANIMATEPALETTE = 0x0436;
    public const META_TEXTOUT = 0x0521;
    public const META_POLYPOLYGON = 0x0538;
    public const META_EXTFLOODFILL = 0x0548;
    public const META_ROUNDRECT = 0x061C;
    public const META_PATBLT = 0x061D;
    public const META_ESCAPE = 0x0626;
    public const META_CREATEREGION = 0x06FF;
    public const META_ARC = 0x0817;
    public const META_PIE = 0x081A;
    public const META_CHORD = 0x0830;
    public const META_BITBLT = 0x0922;
    public const META_DIBBITBLT = 0x0940;
    public const META_EXTTEXTOUT = 0x0A32;
    public const META_STRETCHBLT = 0x0B23;
    public const META_DIBSTRETCHBLT = 0x0B41;
    public const META_SETDIBTODEV = 0x0D33;
    public const META_STRETCHDIB = 0x0F43;

    /**
     * Records which have no effect on the rendering
     *
     * The viewport & the map mode are ignored : the window is mapped to the image
     */
    protected const IGNORED_RECORDS = [
        self::META_REALIZEPALETTE,
        self::META_SETPALENTRIES,
        self::META_SETMAPMODE,
        self::META_SETROP2,
        self::META_SETRELABS,
        self::META_SETSTRETCHBLTMODE,
        self::META_SETTEXTCHAREXTRA,
        self::META_RESIZEPALETTE,
        self::META_SETLAYOUT,
        self::META_SETTEXTJUSTIFICATION,
        self::META_SETVIEWPORTORG,
        self::META_SETVIEWPORTEXT,
        self::META_OFFSETVIEWPORTORG,
        self::META_SETMAPPERFLAGS,
        self::META_SELECTPALETTE,
        self::META_SCALEVIEWPORTEXT,
        self::META_ANIMATEPALETTE,
        self::META_ESCAPE,
    ];

    /**
     * Resolution of the output image
     */
    public const DPI = 72;

    /**
     * @var Renderer
     */
    protected $renderer;
    /**
     * @var FontResolver
     */
    protected $fontResolver;
    /**
     * Objects (pens, brushes, fonts, regions, palettes) indexed by their position in the table of objects
     *
     * @var array<int, array<string, mixed>>
     */
    protected $objects = [];

    public function __construct()
    {
        $this->fontResolver = new FontResolver();
    }

    /**
     * Placeable WMF files (the header defines the size of the image) & standard WMF files (the size is defined by the window)
     */
    public function isWMF(): bool
    {
        return Detector::isWMF((string) $this->content);
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
        if (!$this->isWMF()) {
            return false;
        }

        // Corrupted files must not raise PHP errors
        set_error_handler(function (int $errno, string $errstr): bool {
            if (!(error_reporting() & $errno)) {
                return false;
            }
            throw new WMFException('Reader : Invalid file : ' . $errstr);
        });
        try {
            $this->readRecords();
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

    protected function readRecords(): void
    {
        $pos = $this->readHeader();
        $this->objects = [];

        $contentLen = strlen($this->content);
        while ($pos + 6 <= $contentLen) {
            $header = unpack('Vsize/vfunction', (string) substr($this->content, $pos, 6));
            // Size of the record in 16-bit words
            $size = (int) $header['size'];
            $function = (int) $header['function'];
            if ($size < 3 || $pos + 2 * $size > $contentLen) {
                break;
            }
            $params = (string) substr($this->content, $pos + 6, 2 * $size - 6);
            $pos += 2 * $size;

            if ($function == self::META_EOF) {
                break;
            }
            if (in_array($function, self::IGNORED_RECORDS)) {
                continue;
            }
            if (!$this->readRecord($function, $params, $size)) {
                throw new WMFException('Reader : Function not implemented : 0x' . str_pad(dechex($function), 4, '0', STR_PAD_LEFT));
            }
        }

        $this->gd = $this->renderer->render();
    }

    /**
     * Creates the renderer from the placeable header (or from the window for standard WMF files) : its bounding box is drawn in the image
     *
     * @return int Position of the first record
     */
    protected function readHeader(): int
    {
        if (Detector::isPlaceableWMF((string) $this->content)) {
            $header = unpack('Vkey/vhandle/sleft/stop/sright/sbottom/vinch', (string) substr($this->content, 0, 16));
            $offset = 22;
        } else {
            $header = $this->getWindowBounds();
            $offset = 0;
        }
        // Number of logical units per inch (1440 : twips)
        $unitsPerInch = $header['inch'] ?: 1440;
        $boundsWidth = $header['right'] - $header['left'];
        $boundsHeight = $header['bottom'] - $header['top'];
        $width = (int) max(1, ceil(abs($boundsWidth) / $unitsPerInch * self::DPI));
        $height = (int) max(1, ceil(abs($boundsHeight) / $unitsPerInch * self::DPI));

        // The device units are the pixels of the image : the window (by default, the bounding box) is mapped to the image
        $this->destroyImage($this->gd);
        $this->gd = false;
        $this->renderer = new Renderer($width, $height, 0, 0, 0, 0, $this->fontResolver, $this->backgroundColor);
        $this->renderer
            ->setMapMode(Renderer::MM_ANISOTROPIC)
            ->setWindowOrg($header['left'], $header['top'])
            ->setWindowExt($boundsWidth ?: 1, $boundsHeight ?: 1)
            ->setViewportOrg(0, 0)
            ->setViewportExt($width, $height);

        // META_HEADER follows the placeable header : its size is in 16-bit words
        list(, $headerSize) = unpack('v', (string) substr($this->content, $offset + 2, 2));

        return $offset + 2 * $headerSize;
    }

    /**
     * Returns the bounding box of a standard WMF file : the first window defined by the records
     *
     * The number of logical units per inch depends on the map mode (1440 for MM_ISOTROPIC & MM_ANISOTROPIC)
     *
     * @return array{left: int, top: int, right: int, bottom: int, inch: int}
     */
    protected function getWindowBounds(): array
    {
        // Logical units per inch of the map modes : MM_TEXT (pixels at 96 DPI), MM_LOMETRIC, MM_HIMETRIC, MM_LOENGLISH, MM_HIENGLISH, MM_TWIPS
        $unitsPerInch = [1 => 96, 2 => 254, 3 => 2540, 4 => 100, 5 => 1000, 6 => 1440];
        $mapMode = null;
        $origin = [0, 0];
        $extent = null;
        $contentLen = strlen((string) $this->content);
        list(, $headerSize) = unpack('v', (string) substr((string) $this->content, 2, 2));
        $pos = 2 * $headerSize;
        while ($pos + 6 <= $contentLen && $extent === null) {
            $header = unpack('Vsize/vfunction', (string) substr((string) $this->content, $pos, 6));
            if ($header['size'] < 3 || $header['function'] == self::META_EOF) {
                break;
            }
            $params = (string) substr((string) $this->content, $pos + 6, 4);
            if ($header['function'] == self::META_SETMAPMODE && $mapMode === null) {
                $mapMode = $this->readUShort($params, 0);
            } elseif ($header['function'] == self::META_SETWINDOWORG && strlen($params) == 4) {
                list($y, $x) = $this->readShorts($params, 0, 2);
                $origin = [$x, $y];
            } elseif ($header['function'] == self::META_SETWINDOWEXT && strlen($params) == 4) {
                list($y, $x) = $this->readShorts($params, 0, 2);
                $extent = [$x, $y];
            }
            $pos += 2 * $header['size'];
        }
        if (!$extent || !$extent[0] || !$extent[1]) {
            throw new WMFException('Reader : Invalid file : the size of the image is not defined');
        }

        return [
            'left' => $origin[0],
            'top' => $origin[1],
            'right' => $origin[0] + $extent[0],
            'bottom' => $origin[1] + $extent[1],
            'inch' => $unitsPerInch[$mapMode] ?? 1440,
        ];
    }

    /**
     * Returns false if the record is not supported
     *
     * @param string $params Parameters of the record
     * @param int $size Size of the record in 16-bit words
     */
    protected function readRecord(int $function, string $params, int $size): bool
    {
        $renderer = $this->renderer;
        switch ($function) {
            case self::META_SAVEDC:
                $renderer->saveDC();
                break;
            case self::META_RESTOREDC:
                $renderer->restoreDC($this->readShorts($params, 0, 1)[0]);
                break;
            case self::META_SETWINDOWORG:
                list($y, $x) = $this->readShorts($params, 0, 2);
                $renderer->setWindowOrg($x, $y);
                break;
            case self::META_SETWINDOWEXT:
                list($y, $x) = $this->readShorts($params, 0, 2);
                $renderer->setWindowExt($x, $y);
                break;
            case self::META_OFFSETWINDOWORG:
                list($y, $x) = $this->readShorts($params, 0, 2);
                $renderer->offsetWindowOrg($x, $y);
                break;
            case self::META_SCALEWINDOWEXT:
                list($yDenom, $yNum, $xDenom, $xNum) = $this->readShorts($params, 0, 4);
                $renderer->scaleWindowExt($xNum, $xDenom, $yNum, $yDenom);
                break;
            case self::META_SETBKMODE:
                $renderer->setBkMode($this->readUShort($params, 0));
                break;
            case self::META_SETPOLYFILLMODE:
                $renderer->setPolyFillMode($this->readUShort($params, 0));
                break;
            case self::META_SETTEXTALIGN:
                $renderer->setTextAlign($this->readUShort($params, 0));
                break;
            case self::META_SETBKCOLOR:
                $renderer->setBkColor($this->readColor($params, 0));
                break;
            case self::META_SETTEXTCOLOR:
                $renderer->setTextColor($this->readColor($params, 0));
                break;

                // Objects
            case self::META_CREATEPENINDIRECT:
                $pen = unpack('vstyle/swidth/sunused/Cr/Cg/Cb', (string) substr($params, 0, 9));
                $this->addObject([
                    'type' => 'pen',
                    'style' => $pen['style'],
                    'width' => $pen['width'],
                    'geometric' => true,
                    'color' => [$pen['r'], $pen['g'], $pen['b']],
                    // Dashes are drawn only for pens of one pixel
                    'dashes' => $pen['width'] <= 1 ? Renderer::getPenDashes($pen['style'], true, $pen['width']) : null,
                ]);
                break;
            case self::META_CREATEBRUSHINDIRECT:
                $brush = unpack('vstyle/Cr/Cg/Cb/x/vhatch', (string) substr($params, 0, 8));
                $this->addObject([
                    'type' => 'brush',
                    'style' => $brush['style'],
                    'color' => [$brush['r'], $brush['g'], $brush['b']],
                    'hatch' => $brush['hatch'],
                ]);
                break;
            case self::META_CREATEFONTINDIRECT:
                $font = unpack('sheight/swidth/sescapement/sorientation/sweight/Citalic/Cunderline/CstrikeOut/Ccharset/CoutPrecision/CclipPrecision/Cquality/CpitchAndFamily', (string) substr($params, 0, 18));
                $this->addObject([
                    'type' => 'font',
                    'height' => $font['height'],
                    'escapement' => $font['escapement'],
                    'weight' => $font['weight'],
                    'italic' => $font['italic'] > 0,
                    'underline' => $font['underline'] > 0,
                    'strikeOut' => $font['strikeOut'] > 0,
                    'charset' => $font['charset'],
                    'pitchAndFamily' => $font['pitchAndFamily'],
                    'face' => Encoding::decodeANSI((string) substr($params, 18, 32)),
                ]);
                break;
            case self::META_DIBCREATEPATTERNBRUSH:
            case self::META_CREATEPATTERNBRUSH:
                // The pattern is tiled (its average color is used when a color is needed)
                if ($function == self::META_CREATEPATTERNBRUSH) {
                    // Bitmap16 header, 18 reserved bytes, bits
                    $bitmap = $this->requireBitmap(Bitmap::readBitmap16((string) substr($params, 0, 10) . (string) substr($params, 28)), $function);
                } else {
                    list($style, $colorUsage) = array_values(unpack('v2', (string) substr($params, 0, 4)));
                    // BS_PATTERN : Bitmap16, else DIB
                    $bitmap = $this->requireBitmap(
                        $style == 3
                            ? Bitmap::readBitmap16((string) substr($params, 4))
                            : Bitmap::readPackedDIB((string) substr($params, 4), $colorUsage == Bitmap::DIB_PAL_COLORS),
                        $function
                    );
                }
                $this->addObject([
                    'type' => 'brush',
                    'style' => 3,
                    'color' => Bitmap::getAverageColor($bitmap),
                    'pattern' => $bitmap,
                ]);
                break;
            case self::META_CREATEREGION:
                $this->addObject(['type' => 'region', 'rectangles' => $this->readRegion($params)]);
                break;
            case self::META_CREATEPALETTE:
                $this->addObject(['type' => 'other']);
                break;
            case self::META_SELECTOBJECT:
                $object = $this->objects[$this->readUShort($params, 0)] ?? null;
                if ($object && $object['type'] == 'region') {
                    $renderer->selectClipRegion($object['rectangles'], Rasterizer::RGN_COPY, false);
                } elseif ($object) {
                    $renderer->selectObject($object);
                }
                break;
            case self::META_DELETEOBJECT:
                unset($this->objects[$this->readUShort($params, 0)]);
                break;

                // Regions & clipping
            case self::META_SELECTCLIPREGION:
                $region = $this->getRegion($this->readUShort($params, 0));
                if ($region === null) {
                    $renderer->resetClipRegion();
                } else {
                    $renderer->selectClipRegion($region, Rasterizer::RGN_COPY, false);
                }
                break;
            case self::META_INTERSECTCLIPRECT:
            case self::META_EXCLUDECLIPRECT:
                list($bottom, $right, $top, $left) = $this->readShorts($params, 0, 4);
                if ($function == self::META_INTERSECTCLIPRECT) {
                    $renderer->intersectClipRect($left, $top, $right, $bottom);
                } else {
                    $renderer->excludeClipRect($left, $top, $right, $bottom);
                }
                break;
            case self::META_OFFSETCLIPRGN:
                list($y, $x) = $this->readShorts($params, 0, 2);
                $renderer->offsetClipRegion($x, $y);
                break;
            case self::META_PAINTREGION:
                $renderer->fillRectangles($this->getRegion($this->readUShort($params, 0)) ?? []);
                break;
            case self::META_INVERTREGION:
                // DSTINVERT on each rectangle of the region
                foreach ($this->getRegion($this->readUShort($params, 0)) ?? [] as $rectangle) {
                    $renderer->patBlt(...array_merge($rectangle, [Renderer::ROP_DSTINVERT]));
                }
                break;
            case self::META_FILLREGION:
            case self::META_FRAMEREGION:
                $region = $this->getRegion($this->readUShort($params, 0)) ?? [];
                $brush = $this->objects[$this->readUShort($params, 2)] ?? null;
                if (!$brush || $brush['type'] != 'brush' || $brush['style'] == 1) {
                    break;
                }
                if ($function == self::META_FRAMEREGION) {
                    // The frame of each rectangle of the region
                    list($frameHeight, $frameWidth) = $this->readShorts($params, 4, 2);
                    $frames = [];
                    foreach ($region as list($left, $top, $right, $bottom)) {
                        $frames[] = [$left, $top, $right, $top + $frameHeight];
                        $frames[] = [$left, $bottom - $frameHeight, $right, $bottom];
                        $frames[] = [$left, $top, $left + $frameWidth, $bottom];
                        $frames[] = [$right - $frameWidth, $top, $right, $bottom];
                    }
                    $region = $frames;
                }
                $renderer->fillRectangles($region, $brush);
                break;

                // Shapes
            case self::META_MOVETO:
                list($y, $x) = $this->readShorts($params, 0, 2);
                $renderer->moveTo($x, $y);
                break;
            case self::META_LINETO:
                list($y, $x) = $this->readShorts($params, 0, 2);
                $renderer->lineTo($x, $y);
                break;
            case self::META_POLYLINE:
                $renderer->polyline($this->readPoints($params, 2, $this->readShorts($params, 0, 1)[0]));
                break;
            case self::META_POLYGON:
                $renderer->polygon($this->readPoints($params, 2, $this->readShorts($params, 0, 1)[0]));
                break;
            case self::META_POLYPOLYGON:
                $numPolys = $this->readUShort($params, 0);
                $counts = $numPolys > 0 ? array_values(unpack('v' . $numPolys, (string) substr($params, 2, 2 * $numPolys))) : [];
                $offset = 2 + 2 * $numPolys;
                $polygons = [];
                foreach ($counts as $count) {
                    $polygons[] = $this->readPoints($params, $offset, $count);
                    $offset += 4 * $count;
                }
                $renderer->polyPolygon($polygons);
                break;
            case self::META_RECTANGLE:
                list($bottom, $right, $top, $left) = $this->readShorts($params, 0, 4);
                $renderer->rectangle($left, $top, $right, $bottom);
                break;
            case self::META_ELLIPSE:
                list($bottom, $right, $top, $left) = $this->readShorts($params, 0, 4);
                $renderer->ellipse($left, $top, $right, $bottom);
                break;
            case self::META_ROUNDRECT:
                list($cornerHeight, $cornerWidth, $bottom, $right, $top, $left) = $this->readShorts($params, 0, 6);
                $renderer->roundRect($left, $top, $right, $bottom, $cornerWidth, $cornerHeight);
                break;
            case self::META_ARC:
            case self::META_PIE:
            case self::META_CHORD:
                list($yEnd, $xEnd, $yStart, $xStart, $bottom, $right, $top, $left) = $this->readShorts($params, 0, 8);
                if ($function == self::META_ARC) {
                    $renderer->arc($left, $top, $right, $bottom, $xStart, $yStart, $xEnd, $yEnd);
                } elseif ($function == self::META_PIE) {
                    $renderer->pie($left, $top, $right, $bottom, $xStart, $yStart, $xEnd, $yEnd);
                } else {
                    $renderer->chord($left, $top, $right, $bottom, $xStart, $yStart, $xEnd, $yEnd);
                }
                break;
            case self::META_SETPIXEL:
                list($y, $x) = $this->readShorts($params, 4, 2);
                $renderer->setPixel($x, $y, $this->readColor($params, 0));
                break;
            case self::META_FLOODFILL:
                list($y, $x) = $this->readShorts($params, 4, 2);
                $renderer->floodFill($x, $y, $this->readColor($params, 0), false);
                break;
            case self::META_EXTFLOODFILL:
                list($y, $x) = $this->readShorts($params, 6, 2);
                // FLOODFILLSURFACE (1) or FLOODFILLBORDER (0)
                $renderer->floodFill($x, $y, $this->readColor($params, 2), $this->readUShort($params, 0) == 1);
                break;

                // Texts
            case self::META_TEXTOUT:
                $length = $this->readShorts($params, 0, 1)[0];
                $text = Encoding::decodeANSI((string) substr($params, 2, max(0, $length)));
                $offset = 2 + $length + $length % 2;
                list($y, $x) = $this->readShorts($params, $offset, 2);
                $renderer->textOut($x, $y, $text);
                break;
            case self::META_EXTTEXTOUT:
                list($y, $x, $length) = $this->readShorts($params, 0, 3);
                $options = $this->readUShort($params, 6);
                $offset = 8;
                $rectangle = [0, 0, 0, 0];
                // ETO_OPAQUE or ETO_CLIPPED : the rectangle is present
                if ($options & 0x06) {
                    $rectangle = $this->readShorts($params, $offset, 4);
                    $offset += 8;
                }
                $length = max(0, $length);
                $text = Encoding::decodeANSI((string) substr($params, $offset, $length));
                $offset += $length + $length % 2;
                $dx = strlen($params) >= $offset + 2 * $length && $length > 0 ? $this->readShorts($params, $offset, $length) : [];
                $renderer->textOut($x, $y, $text, $dx, $options, $rectangle);
                break;

                // Bitmaps
            case self::META_PATBLT:
                $rop = $this->readUInt($params, 0);
                list($height, $width, $y, $x) = $this->readShorts($params, 4, 4);
                $renderer->patBlt($x, $y, $x + $width, $y + $height, $rop);
                break;
            case self::META_BITBLT:
            case self::META_DIBBITBLT:
            case self::META_STRETCHBLT:
            case self::META_DIBSTRETCHBLT:
                $this->readBitBlt($function, $params, $size);
                break;
            case self::META_STRETCHDIB:
                $rop = $this->readUInt($params, 0);
                $colorUsage = $this->readUShort($params, 4);
                list($srcHeight, $srcWidth, $ySrc, $xSrc, $destHeight, $destWidth, $yDest, $xDest) = $this->readShorts($params, 6, 8);
                if ($rop == Renderer::ROP_NOP) {
                    break;
                }
                $bitmap = $this->requireBitmap(Bitmap::readPackedDIB((string) substr($params, 22), $colorUsage == Bitmap::DIB_PAL_COLORS), $function);
                $renderer->drawBitmap($bitmap, $xDest, $yDest, $destWidth, $destHeight, $xSrc, $ySrc, $srcWidth, $srcHeight, $rop);
                break;
            case self::META_SETDIBTODEV:
                $colorUsage = $this->readUShort($params, 0);
                list(, , $yDib, $xDib, $height, $width, $yDest, $xDest) = $this->readShorts($params, 2, 8);
                $bitmap = $this->requireBitmap(Bitmap::readPackedDIB((string) substr($params, 18), $colorUsage == Bitmap::DIB_PAL_COLORS), $function);
                $renderer->drawBitmap($bitmap, $xDest, $yDest, $width, $height, $xDib, $yDib, $width, $height);
                break;
            default:
                return false;
        }

        return true;
    }

    /**
     * Reads META_BITBLT, META_DIBBITBLT, META_STRETCHBLT & META_DIBSTRETCHBLT records
     */
    protected function readBitBlt(int $function, string $params, int $size): void
    {
        $rop = $this->readUInt($params, 0);
        if ($rop == Renderer::ROP_NOP) {
            return;
        }
        $isStretch = in_array($function, [self::META_STRETCHBLT, self::META_DIBSTRETCHBLT]);
        // Without bitmap, the size of the record is defined by the high byte of the function
        $hasBitmap = $size != ($function >> 8) + 3;

        $values = $this->readShorts($params, 4, $isStretch ? 9 : 7);
        $srcWidth = $srcHeight = 0;
        if ($isStretch) {
            list($srcHeight, $srcWidth, $ySrc, $xSrc) = $values;
            $offset = 4;
        } else {
            list($ySrc, $xSrc) = $values;
            $offset = 2;
        }
        // Without bitmap, a reserved field precedes the destination
        if (!$hasBitmap) {
            ++$offset;
        }
        list($destHeight, $destWidth, $yDest, $xDest) = array_slice($values, $offset, 4);
        if (!$isStretch) {
            $srcWidth = $destWidth;
            $srcHeight = $destHeight;
        }

        if (!$hasBitmap) {
            $this->renderer->patBlt($xDest, $yDest, $xDest + $destWidth, $yDest + $destHeight, $rop);

            return;
        }

        $data = (string) substr($params, 4 + 2 * ($offset + 4));
        $bitmap = $this->requireBitmap(
            in_array($function, [self::META_BITBLT, self::META_STRETCHBLT])
                ? Bitmap::readBitmap16($data)
                : Bitmap::readPackedDIB($data),
            $function
        );
        $this->renderer->drawBitmap($bitmap, $xDest, $yDest, $destWidth, $destHeight, $xSrc, $ySrc, $srcWidth, $srcHeight, $rop);
    }

    /**
     * Adds an object in the first free slot of the table of objects
     *
     * @param array<string, mixed> $object
     */
    protected function addObject(array $object): void
    {
        $index = 0;
        while (isset($this->objects[$index])) {
            ++$index;
        }
        $this->objects[$index] = $object;
    }

    /**
     * Returns the rectangles of a region object
     *
     * @return array<array<int>>|null
     */
    protected function getRegion(int $index): ?array
    {
        $object = $this->objects[$index] ?? null;

        return $object && $object['type'] == 'region' ? $object['rectangles'] : null;
    }

    /**
     * Reads a Region object : a list of scans, each one containing horizontal intervals
     *
     * @return array<array<int>> Rectangles [left, top, right, bottom]
     */
    protected function readRegion(string $params): array
    {
        $length = strlen($params);
        if ($length < 22) {
            return [];
        }
        // Some writers (like libUEMF) use a 16-bit ObjectCount : the RegionSize field is then at offset 6
        $isShortHeader = $this->readUShort($params, 6) == $length && $this->readUShort($params, 8) != $length;
        $scanCount = $this->readShorts($params, $isShortHeader ? 8 : 10, 1)[0];
        $offset = $isShortHeader ? 20 : 22;
        $rectangles = [];
        for ($i = 0; $i < $scanCount && $offset + 6 <= $length; ++$i) {
            list($count, $top, $bottom) = $this->readShorts($params, $offset, 3);
            $offset += 6;
            // Truncated scans are read up to the end of the record
            $count = min(max(0, $count), intdiv($length - $offset, 2));
            for ($j = 0; $j + 1 < $count; $j += 2) {
                list($left, $right) = $this->readShorts($params, $offset + 2 * $j, 2);
                $rectangles[] = [$left, $top, $right, $bottom];
            }
            // Coordinates & Count2
            $offset += 2 * $count + 2;
        }

        return $rectangles;
    }

    protected function readUShort(string $params, int $offset): int
    {
        list(, $value) = unpack('v', (string) substr($params, $offset, 2));

        return (int) $value;
    }

    protected function readUInt(string $params, int $offset): int
    {
        list(, $value) = unpack('V', (string) substr($params, $offset, 4));

        return (int) $value;
    }

    /**
     * Reads signed 16-bit integers
     *
     * @return array<int>
     */
    protected function readShorts(string $params, int $offset, int $count): array
    {
        $values = array_values(unpack('v' . $count, (string) substr($params, $offset, 2 * $count)));
        foreach ($values as $key => $value) {
            $values[$key] = $value >= 0x8000 ? $value - 0x10000 : $value;
        }

        return $values;
    }

    /**
     * Reads a COLORREF
     *
     * @return array<int>
     */
    protected function readColor(string $params, int $offset): array
    {
        return array_values(unpack('C3', (string) substr($params, $offset, 3)));
    }

    /**
     * @return array<array<float>> Logical coordinates
     */
    protected function readPoints(string $params, int $offset, int $count): array
    {
        if ($count <= 0) {
            return [];
        }
        $values = $this->readShorts($params, $offset, 2 * $count);

        $points = [];
        for ($i = 0; $i < $count; ++$i) {
            $points[] = [$values[2 * $i], $values[2 * $i + 1]];
        }

        return $points;
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
