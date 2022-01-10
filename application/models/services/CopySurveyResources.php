<?php

namespace LimeSurvey\Models\Services;

/**
 * This class is responsible for copying a survey's resources.
 *
 * Class CopySurveyResources
 * @package LimeSurvey\Models\Services
 */
class CopySurveyResources
{
    /**
     * Copy resources from the source survey ($sourceSurveyId) to the target survey ($targetSurveyId)
     *
     * @param int $sourceSurveyId
     * @param int $targetSurveyId
     *
     * @return array An array of failed and copied files/directories
     */
    public function copyResources($sourceSurveyId, $targetSurveyId)
    {
        $sourceSurveyId = (int) $sourceSurveyId;
        $targetSurveyId = (int) $targetSurveyId;
        $sourceDir = \Yii::app()->getConfig('uploaddir') . "/surveys/{$sourceSurveyId}/";
        $targetDir = \Yii::app()->getConfig('uploaddir') . "/surveys/{$targetSurveyId}/";
        return $this->copyDirectory($sourceDir, $targetDir);
    }

    /**
     * Copy $sourceDir to $targetDir and return [$copiedFilesInfo, $errorFilesInfo]
     * @param string $sourceDir
     * @param string $targetDir
     *
     * @return array An array of failed and copied files/directories
     */
    private function copyDirectory($sourceDir, $targetDir): array
    {
        $copiedFilesInfo = [];
        $errorFilesInfo = [];

        if (file_exists($sourceDir)) {
            $directory = opendir($sourceDir);
            if (!$directory) {
                $errorFilesInfo[] = [
                    "filename" => '',
                    "status"   => gT("Source dir not found - maybe a permission problem?")
                ];
                return [$copiedFilesInfo, $errorFilesInfo];
            }

            if (!is_dir($targetDir) && !mkdir($targetDir) && !is_dir($targetDir)) {
                $errorFilesInfo[] = [
                    "filename" => $targetDir,
                    "status"   => gT("Could not create directory")
                ];
            }
            while ($direntry = readdir($directory)) {
                if ($direntry !== "." && $direntry !== "..") {
                    if (is_file($sourceDir . "/" . $direntry)) {
                        if (!copy($sourceDir . "/" . $direntry, $targetDir . "/" . $direntry)) {
                            $errorFilesInfo[] = [
                                "filename" => $direntry,
                                "status"   => gT("Copy failed")
                            ];
                        } else {
                            $copiedFilesInfo[] = [
                                "filename" => $direntry,
                                "status"   => gT("OK")
                            ];
                        }
                    } elseif (is_dir($sourceDir . "/" . $direntry)) {
                        $subExtractdir = $sourceDir . "/" . $direntry;
                        $subDestdir = $targetDir . "/" . $direntry;
                        list($subImportedFilesInfo, $subErrorFilesInfo) = $this->copyDirectory($subExtractdir, $subDestdir);
                        $copiedFilesInfo = array_merge($copiedFilesInfo, $subImportedFilesInfo);
                        $errorFilesInfo = array_merge($errorFilesInfo, $subErrorFilesInfo);
                    }
                }
            }
        }
        return [$copiedFilesInfo, $errorFilesInfo];
    }

}
