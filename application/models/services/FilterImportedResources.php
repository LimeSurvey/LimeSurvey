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
        \Yii::import('application.helpers.common_helper', true);
        $relativeDest = uploadRelativePath($destdir);
        if ($relativeDest !== null && !uploadPathChangeAllowed($relativeDest, 'write')) {
            return [[], [[
                'filename' => $destdir,
                'status' => gT('Could not create directory'),
            ]]];
        }

        $directory = opendir($extractdir);
        if (!$directory) {
            $aErrorFilesInfo[] = [
                "filename" => '',
                "status"   => gT("Extracted files not found - maybe a permission problem?")
            ];
            return [$aImportedFilesInfo, $aErrorFilesInfo];
        }

        [$importedFiles, $errorFiles, $createdDirectory] = $this->copyDirectory($directory, $extractdir, $destdir);
        if ($relativeDest !== null && ($importedFiles !== [] || $createdDirectory)) {
            dispatchUploadPathEvent('afterUploadPathChange', $relativeDest, ['action' => 'write']);
        }
        return [$importedFiles, $errorFiles];
    }

    /**
     * Copy $directory to $destdir.
     * @param resource $directory
     * @param string $extractdir
     * @param string $destdir
     *
     * @return array{0: array, 1: array, 2: bool} Imported files, errors, and whether a destination directory was created
     */
    private function copyDirectory($directory, $extractdir, $destdir): array
    {
        $aImportedFilesInfo = [];
        $aErrorFilesInfo = [];
        $createdDirectory = false;

        if (!is_dir($destdir)) {
            if (!mkdir($destdir) && !is_dir($destdir)) {
                $aErrorFilesInfo[] = [
                    "filename" => $destdir,
                    "status"   => gT("Could not create directory")
                ];
            } else {
                $createdDirectory = true;
            }
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
                    [$aSubImportedFilesInfo, $aSubErrorFilesInfo, $subCreated] = $this->copyDirectory($subDirectory, $subExtractdir, $subDestdir);
                    $aImportedFilesInfo = array_merge($aImportedFilesInfo, $aSubImportedFilesInfo);
                    $aErrorFilesInfo = array_merge($aErrorFilesInfo, $aSubErrorFilesInfo);
                    $createdDirectory = $createdDirectory || $subCreated;
                }
            }
        }
        return [$aImportedFilesInfo, $aErrorFilesInfo, $createdDirectory];
    }
}
