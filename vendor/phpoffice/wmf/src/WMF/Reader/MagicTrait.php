<?php

declare(strict_types=1);

namespace PhpOffice\WMF\Reader;

use GDImage;
use Imagick as ImagickBase;

/**
 * Common code of the readers choosing automatically their backend
 *
 * The class using this trait defines the property `$backends` (backends sorted by priority)
 * and the method `isBackend()`
 */
trait MagicTrait
{
    /**
     * @var ?ReaderInterface
     */
    protected $reader;

    /**
     * Returns if the class is a backend for the format of the reader
     */
    abstract protected function isBackend(string $backend): bool;

    /**
     * Returns the first supported backend
     */
    protected function getBackend(): ?ReaderInterface
    {
        if ($this->reader) {
            return $this->reader;
        }

        foreach ($this->backends as $backend) {
            $reader = new $backend();
            if ($reader->isSupported()) {
                $this->reader = $reader;

                break;
            }
        }

        return $this->reader;
    }

    /**
     * Returns if a backend is supported
     */
    public function isSupported(): bool
    {
        return $this->getBackend() !== null;
    }

    protected function loadContent(): bool
    {
        return $this->getBackend()->loadFromString($this->content);
    }

    public function save(string $filename, string $format): bool
    {
        return $this->getBackend()->save($filename, $format);
    }

    public function getMediaType(): string
    {
        return $this->getBackend()->getMediaType();
    }

    /**
     * @phpstan-ignore-next-line
     *
     * @return GDImage|ImagickBase
     */
    public function getResource()
    {
        return $this->getBackend()->getResource();
    }

    /**
     * @return array<string>
     */
    public function getBackends(): array
    {
        return $this->backends;
    }

    /**
     * @param array<string> $backends
     */
    public function setBackends(array $backends): self
    {
        $this->backends = [];
        foreach ($backends as $backend) {
            if ($this->isBackend($backend)) {
                $this->backends[] = $backend;
            }
        }

        return $this;
    }
}
