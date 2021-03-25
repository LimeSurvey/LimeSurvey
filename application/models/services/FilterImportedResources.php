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
        $aErrorFilesInfo = array();
        $aImportedFilesInfo = array();

        if (!is_dir($extractdir)) {
            return array(array(), array());
        }

        if (!is_dir($destdir)) {
            mkdir($destdir);
        }

        $dh = opendir($extractdir);
        if (!$dh) {
            $aErrorFilesInfo[] = array(
                "filename" => '',
                "status" => gT("Extracted files not found - maybe a permission problem?")
            );
            return array($aImportedFilesInfo, $aErrorFilesInfo);
        }
        while ($direntry = readdir($dh)) {
            if ($direntry != "." && $direntry != "..") {
                if (is_file($extractdir . "/" . $direntry)) {
                    // is  a file
                    $extfile = (string) substr(strrchr($direntry, '.'), 1);
                    if (!(stripos(',' . \Yii::app()->getConfig('allowedresourcesuploads') . ',', ',' . $extfile . ',') === false)) {
                        // Extension allowed
                        if (!copy($extractdir . "/" . $direntry, $destdir . "/" . $direntry)) {
                            $aErrorFilesInfo[] = array(
                                "filename" => $direntry,
                                "status" => gT("Copy failed")
                            );
                        } else {
                            $aImportedFilesInfo[] = array(
                                "filename" => $direntry,
                                "status" => gT("OK")
                            );
                        }
                    } else {
                        // Extension forbidden
                        $aErrorFilesInfo[] = array(
                            "filename" => $direntry,
                            "status" => gT("Forbidden Extension")
                        );
                    }
                    unlink($extractdir . "/" . $direntry);
                }
            }
        }

        return array($aImportedFilesInfo, $aErrorFilesInfo);
    }
}
