<?php

namespace Anper\Iuliia;

/**
 * Class Schema
 * @package Anper\Iuliia
 */
class Schema
{
    /**
     * @var Map
     */
    protected $defaultMap;

    /**
     * @var Map
     */
    protected $prevMap;

    /**
     * @var Map
     */
    protected $nextMap;

    /**
     * @var Map
     */
    protected $endingMap;

    /**
     * @var array|string[][]
     */
    protected $samples;

    /**
     * @param string $filename
     *
     * @return self
     */
    public static function createFromFile(string $filename): self
    {
        if (\file_exists($filename) === false || \is_file($filename) === false) {
            throw new \InvalidArgumentException("File '$filename' not found");
        }

        $data = @include $filename;

        if (empty($data) || \is_array($data) === false) {
            throw new \RuntimeException("Error read the contents of the file '$filename'");
        }

        $data = \array_values($data);

        return new self(
            new Map($data[0] ?? []),
            new Map($data[1] ?? []),
            new Map($data[2] ?? []),
            new Map($data[3] ?? []),
            $data[4] ?? []
        );
    }

    /**
     * @param Map $defaultMap
     * @param Map $prevMap
     * @param Map $nextMap
     * @param Map $endingMap
     * @param array|string[][] $samples
     */
    public function __construct(
        Map $defaultMap,
        Map $prevMap,
        Map $nextMap,
        Map $endingMap,
        array $samples = []
    ) {
        $this->defaultMap = $defaultMap;
        $this->prevMap = $prevMap;
        $this->nextMap = $nextMap;
        $this->endingMap = $endingMap;
        $this->samples = $samples;
    }

    /**
     * @return Map
     */
    public function getDefaultMap(): Map
    {
        return $this->defaultMap;
    }

    /**
     * @return Map
     */
    public function getPrevMap(): Map
    {
        return $this->prevMap;
    }

    /**
     * @return Map
     */
    public function getNextMap(): Map
    {
        return $this->nextMap;
    }

    /**
     * @return Map
     */
    public function getEndingMap(): Map
    {
        return $this->endingMap;
    }

    /**
     * @return array|string[][]
     */
    public function getSamples(): array
    {
        return $this->samples;
    }
}
