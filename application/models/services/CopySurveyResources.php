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

        // Don't do anything if the source survey doesn't have a directory
        if (!is_dir($sourceDir)) {
            return [[], []];
        }

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

        if (is_dir($sourceDir)) {
            $directory = opendir($sourceDir);
            if (!$directory) {
                $errorFilesInfo[] = [
                    "filename" => $sourceDir,
                    "status"   => gT("Could not open source dir - maybe a permission problem?")
                ];
                return [$copiedFilesInfo, $errorFilesInfo];
            }

            // Create target dir if it doesn't exist
            if (!is_dir($targetDir)) {
                if (!mkdir($targetDir)) {
                    $errorFilesInfo[] = [
                        "filename" => $targetDir,
                        "status"   => gT("Could not create directory")
                    ];
                    return [$copiedFilesInfo, $errorFilesInfo];
                }
            } else {
                $errorFilesInfo[] = [
                    "filename" => $targetDir,
                    "status"   => gT("Destination dir already exists! Can contain more files than source dir.")
                ];
            }

            // Copy source dir contents
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
        } else {
            $errorFilesInfo[] = [
                "filename" => $sourceDir,
                "status"   => gT("Source dir not found.")
            ];
        }
        return [$copiedFilesInfo, $errorFilesInfo];
    }
}
