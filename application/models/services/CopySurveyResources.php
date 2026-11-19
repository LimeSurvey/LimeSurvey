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
        $sourcePath = 'surveys/' . $sourceSurveyId;
        $targetPath = 'surveys/' . $targetSurveyId;
        \Yii::import('application.helpers.common_helper', true);
        dispatchUploadPathEvent('beforeUploadPathUse', $sourcePath);

        if (!is_dir($sourceDir)) {
            return $this->getResult();
        }
        if (!uploadPathChangeAllowed($targetPath, 'write', ['surveyId' => $targetSurveyId])) {
            $this->addError($targetDir, gT("Could not create directory"));
            return $this->getResult();
        }
        if ($this->copyDirectory($sourceDir, $targetDir)) {
            dispatchUploadPathEvent('afterUploadPathChange', $targetPath, [
                'action' => 'write',
                'surveyId' => $targetSurveyId,
            ]);
        }

        return $this->getResult();
    }

    /**
     * Copy $sourceDir to $targetDir.
     * @param string $sourceDir
     * @param string $targetDir
     * @return bool True when the destination directory or a file in it was created.
     */
    private function copyDirectory($sourceDir, $targetDir)
    {
        $mutated = false;
        if (is_dir($sourceDir)) {
            $directory = opendir($sourceDir);
            if (!$directory) {
                $this->addError($sourceDir, gT("Could not open source directory - maybe a permission problem?"));
                return false;
            }

            $targetExisted = is_dir($targetDir);
            // Create target dir if it doesn't exist, return if doesn't exist and cannot be created.
            if (!$this->checkTargetDir($targetDir)) {
                return false;
            }
            if (!$targetExisted && is_dir($targetDir)) {
                $mutated = true;
            }

            // Copy source dir contents
            while ($direntry = readdir($directory)) {
                if ($direntry !== "." && $direntry !== "..") {
                    if (is_file($sourceDir . "/" . $direntry)) {
                        if (!copy($sourceDir . "/" . $direntry, $targetDir . "/" . $direntry)) {
                            $this->addError($direntry, gT("Copy failed"));
                        } else {
                            $this->addSuccess($direntry, gT("OK"));
                            $mutated = true;
                        }
                    } elseif (is_dir($sourceDir . "/" . $direntry)) {
                        $subExtractdir = $sourceDir . "/" . $direntry;
                        $subDestdir = $targetDir . "/" . $direntry;
                        if ($this->copyDirectory($subExtractdir, $subDestdir)) {
                            $mutated = true;
                        }
                    }
                }
            }
        } else {
            $this->addError($sourceDir, gT("Source directory not found"));
        }
        return $mutated;
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
