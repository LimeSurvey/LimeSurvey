<?php

declare(strict_types=1);

namespace PhpOffice\WMF\Reader;

use Imagick as ImagickBase;
use ImagickException;
use PhpOffice\WMF\Exception\WMFException;

/**
 * Common code of the readers based on Imagick
 */
trait ImagickTrait
{
    /**
     * @var ImagickBase
     */
    protected $im;

    /**
     * Returns if Imagick is available and supports the format of the reader
     */
    public function isSupported(): bool
    {
        return extension_loaded('imagick') && in_array(strtoupper($this->getFormat()), ImagickBase::queryFormats());
    }

    protected function loadContent(): bool
    {
        try {
            $this->im = new ImagickBase();

            return $this->im->readImageBlob($this->content);
        } catch (ImagickException $e) {
            $this->im->clear();

            if ($this->hasExceptionsEnabled()) {
                throw new WMFException(sprintf('Cannot load %s File from Imagick', strtoupper($this->getFormat())));
            }

            return false;
        }
    }

    /**
     * Returns if the loaded image is in the format of the reader
     */
    protected function isImagickFormat(): bool
    {
        return $this->im->getImageFormat() === strtoupper($this->getFormat());
    }

    public function getResource(): ImagickBase
    {
        return $this->im;
    }

    /**
     * Saves the image in a format supported by Imagick, or in its original format (`wmf` or `emf`)
     */
    public function save(string $filename, string $format): bool
    {
        switch (strtolower($format)) {
            case 'gif':
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'webp':
            case 'wbmp':
                $this->getResource()->setImageFormat(strtolower($format));

                return $this->getResource()->writeImage($filename);
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
