<?php

namespace Anper\Iuliia;

/**
 * Class Builder
 * @package Anper\Iuliia
 */
class Builder
{
    /**
     * @var string
     */
    protected $schemaDir;

    /**
     * @param string $schemaDir
     */
    public function __construct(string $schemaDir)
    {
        if (\file_exists($schemaDir) === false || \is_dir($schemaDir) === false) {
            throw new \InvalidArgumentException("Dir '$schemaDir' not found");
        }

        $this->schemaDir = \rtrim($schemaDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * Build schema from original json file.
     *
     * @param string $schema
     *
     * @return Schema
     */
    public function build(string $schema): Schema
    {
        $definition = $this->load($this->schemaDir . $schema . '.json');

        $defaultMap = $definition['mapping'] ?? [];
        $prevMap = $definition['prev_mapping'] ?? [];
        $nextMap = $definition['next_mapping'] ?? [];
        $endingMap = $definition['ending_mapping'] ?? [];

        foreach ($defaultMap as $key => $value) {
            $defaultMap[$this->ucfirst($key)] = $this->ucfirst($value);
        }

        foreach ($prevMap as $key => $value) {
            $prevMap[$this->ucfirst($key)] = $value;
            $prevMap[\mb_strtoupper($key)] = $this->ucfirst($value);
        }

        foreach ($nextMap as $key => $value) {
            $nextMap[$this->ucfirst($key)] = $this->ucfirst($value);
            $nextMap[\mb_strtoupper($key)] = $this->ucfirst($value);
        }

        foreach ($endingMap as $key => $value) {
            $endingMap[\mb_strtoupper($key)] = \mb_strtoupper($value);
        }

        return new Schema(
            new Map($defaultMap),
            new Map($prevMap),
            new Map($nextMap),
            new Map($endingMap),
            $definition['samples'] ?? []
        );
    }

    /**
     * @param string $filename
     *
     * @return array<string,mixed>
     */
    protected function load(string $filename): array
    {
        if (\file_exists($filename) === false || \is_file($filename) === false) {
            throw new \InvalidArgumentException("File '$filename' not found");
        }

        $content = \file_get_contents($filename);

        if ($content === false) {
            throw new \RuntimeException("Error read the contents of the file '$filename'");
        }

        $data = \json_decode($content, true);

        if (JSON_ERROR_NONE !== \json_last_error()) {
            throw new \RuntimeException(\json_last_error_msg(), \json_last_error());
        }

        return $data;
    }

    /**
     * @param string $str
     *
     * @return string
     */
    protected function ucfirst(string $str): string
    {
        if (\mb_strlen($str) < 2) {
            return \mb_strtoupper($str);
        }

        $first = \mb_substr($str, 0, 1);
        $last = \mb_substr($str, 1);

        return \mb_strtoupper($first) . \mb_strtolower($last);
    }
}
