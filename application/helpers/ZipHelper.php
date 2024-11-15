<?php

namespace LimeSurvey\Helpers;

class ZipHelper
{
    /** @var ZipArchive */
    private $zip;

    public function __construct($zip = null)
    {
        $this->zip = $zip;
    }

    public function addFolder($folder, $pathInZip = '')
    {
        if (!is_dir($folder)) {
            throw new \InvalidArgumentException('The folder does not exist.');
        }

        if (!empty($pathInZip)) {
            $pathInZip = rtrim($pathInZip, '/') . '/';
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folder),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = ltrim(substr($filePath, strlen($folder)), DIRECTORY_SEPARATOR);
                $this->zip->addFile($filePath, $pathInZip . $relativePath);
            }
        }
    }
}