<?php

declare(strict_types=1);

namespace PhpOffice\WMF\Reader;

use GdImage;
use PhpOffice\WMF\Exception\WMFException;

/**
 * Common code of the readers based on GD
 */
trait GDTrait
{
    /**
     * @phpstan-ignore-next-line
     *
     * @var GdImage|resource|false
     */
    protected $gd;

    /**
     * Color of the background of the image [r, g, b] (null : transparent background)
     *
     * @var array<int>|null
     */
    protected $backgroundColor = [255, 255, 255];

    /**
     * Defines the color of the background [r, g, b] (white by default), or a transparent background (null)
     *
     * The background must be defined before loading the file.
     * Formats without alpha channel (JPEG, WBMP & GIF) are saved with a white background.
     *
     * @param array<int>|null $color
     */
    public function setBackgroundColor(?array $color): self
    {
        $this->backgroundColor = $color === null ? null : array_values($color);

        return $this;
    }

    /**
     * @return array<int>|null
     */
    public function getBackgroundColor(): ?array
    {
        return $this->backgroundColor;
    }

    /**
     * Returns the image, on a white background if the background is transparent
     *
     * @phpstan-ignore-next-line
     *
     * @return GdImage|resource
     */
    protected function getOpaqueResource()
    {
        $image = $this->getResource();
        if ($this->backgroundColor !== null) {
            return $image;
        }
        $opaque = imagecreatetruecolor(imagesx($image), imagesy($image));
        imagefilledrectangle($opaque, 0, 0, imagesx($image), imagesy($image), 0xFFFFFF);
        imagecopy($opaque, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));

        return $opaque;
    }

    /**
     * Returns if the GD extension is loaded
     */
    public function isSupported(): bool
    {
        return extension_loaded('gd');
    }

    public function __destruct()
    {
        $this->destroyImage($this->gd);
    }

    /**
     * Returns the decoded bitmap, or throws an exception if the bitmap is not supported (like compressed bitmaps)
     *
     * @param array{width: int, height: int, pixels: array<array<int>>}|null $bitmap Decoded bitmap (see PhpOffice\WMF\Renderer\Bitmap)
     * @param int $recordType Type of the record containing the bitmap
     *
     * @return array{width: int, height: int, pixels: array<array<int>>}
     */
    protected function requireBitmap(?array $bitmap, int $recordType): array
    {
        if ($bitmap === null) {
            throw new WMFException(sprintf('Reader : Bitmap not supported : 0x%04x', $recordType));
        }

        return $bitmap;
    }

    /**
     * Since PHP 8.0, images are objects freed automatically (and imagedestroy() is deprecated since PHP 8.5)
     *
     * @phpstan-ignore-next-line
     *
     * @param GdImage|resource|false|null $image
     */
    protected function destroyImage($image): void
    {
        if ($image && \PHP_VERSION_ID < 80000) {
            imagedestroy($image);
        }
    }

    /**
     * Saves the image in a format supported by GD, or in its original format (`wmf` or `emf`)
     */
    public function save(string $filename, string $format): bool
    {
        switch (strtolower($format)) {
            case 'gif':
                return imagegif($this->getOpaqueResource(), $filename);
            case 'jpg':
            case 'jpeg':
                return imagejpeg($this->getOpaqueResource(), $filename);
            case 'png':
                return imagepng($this->getResource(), $filename);
            case 'webp':
                return imagewebp($this->getResource(), $filename);
            case 'wbmp':
                return imagewbmp($this->getOpaqueResource(), $filename);
            case $this->getFormat():
                return (bool) (file_put_contents($filename, $this->content) > 0);
            default:
                if ($this->hasExceptionsEnabled()) {
                    throw new WMFException(sprintf('Format %s not supported', $format));
                }

                return false;
        }
    }
}
