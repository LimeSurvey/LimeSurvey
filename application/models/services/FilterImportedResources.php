<?php

namespace LimeSurvey\Models\Services;

class FilterImportedResources
{
    //todo: make a good service class ... (constructor, dependency injection etc.)

    /**
     * @param string $extractdir
     * @param string $destdir
     * @return array
     */
    public function filterImportedResources($extractdir, $destdir)
    {
        $aErrorFilesInfo = [];
        $aImportedFilesInfo = [];

        if (!is_dir($extractdir)) {
            return [[], []];
        }

        $directory = opendir($extractdir);
        if (!$directory) {
            $aErrorFilesInfo[] = [
                "filename" => '',
                "status"   => gT("Extracted files not found - maybe a permission problem?")
            ];
            return [$aImportedFilesInfo, $aErrorFilesInfo];
        }

        return $this->copyDirectory($directory, $extractdir, $destdir);
    }

    /**
     * Copy $directory to $destdir and return [$aImportedFilesInfo, $aErrorFilesInfo]
     * @param resource $directory
     * @param string $extractdir
     * @param string $destdir
     *
     * @return array An array of failed and imported files/directories
     */
    private function copyDirectory($directory, $extractdir, $destdir): array
    {
        $aImportedFilesInfo = [];
        $aErrorFilesInfo = [];

        if (!is_dir($destdir) && !mkdir($destdir) && !is_dir($destdir)) {
            $aErrorFilesInfo[] = [
                "filename" => $destdir,
                "status"   => gT("Could not create directory")
            ];
        }
        while ($direntry = readdir($directory)) {
            if ($direntry !== "." && $direntry !== "..") {
                if (is_file($extractdir . "/" . $direntry)) {
                    // is  a file
                    $extfile = (string)substr(strrchr($direntry, '.'), 1);
                    if (!(stripos(',' . \Yii::app()->getConfig('allowedresourcesuploads') . ',', ',' . $extfile . ',') === false)) {
                        // Extension allowed
                        if (!copy($extractdir . "/" . $direntry, $destdir . "/" . $direntry)) {
                            $aErrorFilesInfo[] = [
                                "filename" => $direntry,
                                "status"   => gT("Copy failed")
                            ];
                        } else {
                            $aImportedFilesInfo[] = [
                                "filename" => $direntry,
                                "status"   => gT("OK")
                            ];
                        }
                    } else {
                        // Extension forbidden
                        $aErrorFilesInfo[] = [
                            "filename" => $direntry,
                            "status"   => gT("Forbidden extension")
                        ];
                    }
                    unlink($extractdir . "/" . $direntry);
                }
                if (is_dir($extractdir . "/" . $direntry)) {
                    $subDirectory = opendir($extractdir . "/" . $direntry);
                    $subExtractdir = $extractdir . "/" . $direntry;
                    $subDestdir = $destdir . "/" . $direntry;
                    list($aSubImportedFilesInfo, $aSubErrorFilesInfo) = $this->copyDirectory($subDirectory, $subExtractdir, $subDestdir);
                    $aImportedFilesInfo = array_merge($aImportedFilesInfo, $aSubImportedFilesInfo);
                    $aErrorFilesInfo = array_merge($aErrorFilesInfo, $aSubErrorFilesInfo);
                }
            }
        }
        return [$aImportedFilesInfo, $aErrorFilesInfo];
    }
}
