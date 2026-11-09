<?php

declare(strict_types=1);

namespace PhpOffice\WMF\Renderer;

use Exception;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Finds the TrueType/OpenType font file matching a logical font
 */
class FontResolver
{
    /**
     * Font families used when a font is not available
     */
    protected const FONT_FAMILIES = [
        'sans' => ['arial', 'liberationsans', 'arimo', 'helvetica', 'dejavusans', 'freesans', 'notosans', 'verdana'],
        'serif' => ['timesnewroman', 'liberationserif', 'tinos', 'times', 'dejavuserif', 'freeserif', 'notoserif'],
        'mono' => ['couriernew', 'liberationmono', 'cousine', 'courier', 'dejavusansmono', 'freemono', 'notosansmono'],
    ];

    /**
     * Families of common fonts
     */
    protected const FONT_ALIASES = [
        'arial' => 'sans',
        'arialnarrow' => 'sans',
        'calibri' => 'sans',
        'helvetica' => 'sans',
        'microsoftsansserif' => 'sans',
        'mssansserif' => 'sans',
        'segoeui' => 'sans',
        'tahoma' => 'sans',
        'verdana' => 'sans',
        'bookantiqua' => 'serif',
        'cambria' => 'serif',
        'garamond' => 'serif',
        'georgia' => 'serif',
        'msserif' => 'serif',
        // The texts of the Symbol font are converted to Unicode (see Encoding::decodeSymbol())
        'symbol' => 'serif',
        'times' => 'serif',
        'timesnewroman' => 'serif',
        'consolas' => 'mono',
        'courier' => 'mono',
        'couriernew' => 'mono',
        'lucidaconsole' => 'mono',
    ];

    /**
     * Suffixes of font files for each style
     */
    protected const FONT_STYLES = [
        'regular' => ['', 'regular', 'r', 'book', 'roman', 'normal', 'medium'],
        'bold' => ['bold', 'bd', 'b'],
        'italic' => ['italic', 'i', 'oblique', 'it'],
        'bolditalic' => ['bolditalic', 'bi', 'z', 'boldoblique'],
    ];

    /**
     * Directories where fonts are searched (null for the system directories)
     *
     * @var array<string>|null
     */
    protected $directories;

    /**
     * Font files indexed by normalized name, for each set of directories
     *
     * @var array<string, array<string, string>>
     */
    protected static $indexes = [];

    /**
     * Defines the directories where TrueType/OpenType fonts are searched (recursively)
     *
     * By default, the system directories are used
     *
     * @param array<string> $directories
     */
    public function setDirectories(array $directories): self
    {
        $this->directories = array_values($directories);

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getDirectories(): array
    {
        if ($this->directories !== null) {
            return $this->directories;
        }

        $home = getenv('HOME') ?: getenv('USERPROFILE');
        $directories = [
            // Windows
            (getenv('WINDIR') ?: 'C:\\Windows') . '\\Fonts',
            getenv('LOCALAPPDATA') ? getenv('LOCALAPPDATA') . '\\Microsoft\\Windows\\Fonts' : '',
            // MacOS
            '/Library/Fonts',
            '/System/Library/Fonts',
            $home ? $home . '/Library/Fonts' : '',
            // Linux
            '/usr/share/fonts',
            '/usr/local/share/fonts',
            $home ? $home . '/.fonts' : '',
            $home ? $home . '/.local/share/fonts' : '',
        ];

        return array_values(array_filter($directories, function (string $directory): bool {
            return $directory !== '' && @is_dir($directory);
        }));
    }

    /**
     * Returns the font file matching a logical font
     *
     * @param array<string, mixed> $font Logical font (keys `face`, `weight`, `italic` & `pitchAndFamily`)
     */
    public function resolve(array $font): ?string
    {
        $index = $this->getIndex();
        if (empty($index)) {
            return null;
        }

        $face = $this->normalize($font['face']);
        $family = self::FONT_ALIASES[$face] ?? null;
        if (!$family) {
            // FF_ROMAN, FF_MODERN or FIXED_PITCH
            $pitchAndFamily = $font['pitchAndFamily'];
            $family = 'sans';
            if (($pitchAndFamily & 0xF0) == 0x10) {
                $family = 'serif';
            } elseif (($pitchAndFamily & 0xF0) == 0x30 || ($pitchAndFamily & 0x03) == 1) {
                $family = 'mono';
            }
        }
        // The Symbol font itself is not used : its characters are not Unicode characters
        $names = array_unique(array_merge($face == 'symbol' ? [] : [$face], self::FONT_FAMILIES[$family]));

        $isBold = $font['weight'] >= 600;
        $styles = ['regular'];
        if ($isBold && $font['italic']) {
            $styles = ['bolditalic', 'bold', 'italic', 'regular'];
        } elseif ($isBold) {
            $styles = ['bold', 'regular'];
        } elseif ($font['italic']) {
            $styles = ['italic', 'regular'];
        }

        foreach ($styles as $style) {
            foreach ($names as $name) {
                foreach (self::FONT_STYLES[$style] as $suffix) {
                    if ($name !== '' && isset($index[$name . $suffix])) {
                        return $index[$name . $suffix];
                    }
                }
            }
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    protected function getIndex(): array
    {
        $directories = $this->getDirectories();
        $key = implode('|', $directories);
        if (isset(self::$indexes[$key])) {
            return self::$indexes[$key];
        }

        $index = [];
        foreach ($directories as $directory) {
            try {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::LEAVES_ONLY,
                    RecursiveIteratorIterator::CATCH_GET_CHILD
                );
                foreach ($iterator as $file) {
                    $extension = strtolower($file->getExtension());
                    if ($extension != 'ttf' && $extension != 'otf') {
                        continue;
                    }
                    $name = $this->normalize($file->getBasename('.' . $file->getExtension()));
                    if (!isset($index[$name])) {
                        $index[$name] = $file->getPathname();
                    }
                }
            } catch (Exception $e) {
                // Unreadable directory
            }
        }
        self::$indexes[$key] = $index;

        return $index;
    }

    protected function normalize(string $name): string
    {
        return (string) preg_replace('/[^a-z0-9]/', '', strtolower($name));
    }
}
