<?php

namespace LimeSurvey\Helpers;

class ZipHelper
{
    /** @var \ZipArchive */
    private $zip;

    /**
     * Creates the helper for an already opened archive.
     *
     * @param \ZipArchive|null $zip The archive that files and folders are added to
     */
    public function __construct($zip = null)
    {
        $this->zip = $zip;
    }

    /**
     * Adds a folder recursively to the archive, including its empty subfolders.
     *
     * Empty folders are kept because survey themes rely on them, e.g. an extended theme's views folder.
     *
     * @param string $folder Absolute path of the folder to add
     * @param string $pathInZip Path inside the archive to add the folder content to; Empty for the archive root
     * @return void
     * @throws \InvalidArgumentException If the folder does not exist
     */
    public function addFolder($folder, $pathInZip = '')
    {
        if (!is_dir($folder)) {
            throw new \InvalidArgumentException('The folder does not exist.');
        }

        if (!empty($pathInZip)) {
            $pathInZip = rtrim($pathInZip, '/') . '/';
        }

        $entries = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folder, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($entries as $entry) {
            $entryPath = $entry->getPathname();
            $relativePath = ltrim(substr($entryPath, strlen($folder)), DIRECTORY_SEPARATOR);
            // ZIP entries always use forward slashes as folder separator
            $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
            if ($entry->isDir()) {
                $this->zip->addEmptyDir($pathInZip . $relativePath);
            } else {
                $this->zip->addFile($entryPath, $pathInZip . $relativePath);
            }
        }
    }
}
