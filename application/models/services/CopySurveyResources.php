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
    /** @var array<array<string,string>> array of successfully copied files/dirs in the form ['filename' => ..., 'status' => ...] */
    private $copiedFilesInfo = [];

    /** @var array<array<string,string>> array of failed files/dirs in the form ['filename' => ..., 'status' => ...] */
    private $errorFilesInfo = [];

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

        // Only try to copy the resources if the source survey has a resources directory
        if (is_dir($sourceDir)) {
            $this->copyDirectory($sourceDir, $targetDir);
        }

        return $this->getResult();
    }

    /**
     * Copy $sourceDir to $targetDir.
     * @param string $sourceDir
     * @param string $targetDir
     */
    private function copyDirectory($sourceDir, $targetDir)
    {
        if (is_dir($sourceDir)) {
            $directory = opendir($sourceDir);
            if (!$directory) {
                $this->addError($sourceDir, gT("Could not open source directory - maybe a permission problem?"));
                return;
            }

            // Create target dir if it doesn't exist, return if doesn't exist and cannot be created.
            if (!$this->checkTargetDir($targetDir)) {
                return;
            }

            // Copy source dir contents
            while ($direntry = readdir($directory)) {
                if ($direntry !== "." && $direntry !== "..") {
                    if (is_file($sourceDir . "/" . $direntry)) {
                        if (!copy($sourceDir . "/" . $direntry, $targetDir . "/" . $direntry)) {
                            $this->addError($direntry, gT("Copy failed"));
                        } else {
                            $this->addSuccess($direntry, gT("OK"));
                        }
                    } elseif (is_dir($sourceDir . "/" . $direntry)) {
                        $subExtractdir = $sourceDir . "/" . $direntry;
                        $subDestdir = $targetDir . "/" . $direntry;
                        $this->copyDirectory($subExtractdir, $subDestdir);
                    }
                }
            }
        } else {
            $this->addError($sourceDir, gT("Source directory not found"));
        }
    }

    /**
     * Creates the target directory if it doesn't exists
     * @return boolean Returns true if the directory exists or was created.
     */
    private function checkTargetDir($targetDir)
    {
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir)) {
                $this->addError($targetDir, gT("Could not create directory"));
                return false;
            }
        } else {
            $this->addError($targetDir, gT("Destination directory already exists!"));
        }
        return true;
    }

    /**
     * Returns the array of failed and copied files/directories
     * @return array<array<array<string,string>>>
     */
    private function getResult()
    {
        return [$this->copiedFilesInfo, $this->errorFilesInfo];
    }

    /**
     * Adds the file to the copied files array
     * @param string $filename
     * @param string $status
     */
    private function addSuccess($filename, $status)
    {
        $this->copiedFilesInfo[] = ["filename" => $filename, "status" => $status];
    }

    /**
     * Adds the file to the failed files array
     * @param string $filename
     * @param string $status
     */
    private function addError($filename, $status)
    {
        $this->errorFilesInfo[] = ["filename" => $filename, "status" => $status];
    }
}
