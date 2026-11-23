<?php

declare(strict_types=1);

namespace PhpOffice\WMF\Reader;

abstract class ReaderAbstract implements ReaderInterface
{
    /**
     * @var bool
     */
    protected $hasExceptionsEnabled = true;
    /**
     * @var string
     */
    protected $content;

    /**
     * Enable/Disable throwing exceptions
     *
     * By default, it's enabled
     */
    public function enableExceptions(bool $enable): self
    {
        $this->hasExceptionsEnabled = $enable;

        return $this;
    }

    /**
     * Returns if exceptions are thrown
     */
    public function hasExceptionsEnabled(): bool
    {
        return $this->hasExceptionsEnabled;
    }

    public function load(string $filename): bool
    {
        $this->content = file_get_contents($filename);

        return $this->loadContent();
    }

    public function loadFromString(string $content): bool
    {
        $this->content = $content;

        return $this->loadContent();
    }

    /**
     * Loads the content stored in $this->content
     */
    abstract protected function loadContent(): bool;

    /**
     * Returns the format of the files read by the reader (`wmf` or `emf`)
     */
    abstract protected function getFormat(): string;

    public function getMediaType(): string
    {
        return 'image/' . $this->getFormat();
    }
}
