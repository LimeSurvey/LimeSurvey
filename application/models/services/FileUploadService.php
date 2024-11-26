<?php

namespace LimeSurvey\Models\Services;

use LSYii_ImageValidator;
use Yii;

class FileUploadService
{
    private UploadValidator $uploadValidator;

    public function __construct(UploadValidator $uploadValidator)
    {
        $this->uploadValidator = $uploadValidator;
    }

    public function storeSurveyImage(int $surveyId, array $fileInfoArray)
    {
        $returnedData = ['success' => false];
        $surveyId = $this->convertSurveyIdWhenUniqUploadDir($surveyId);
        $this->uploadValidator->post = $surveyId;
        $this->uploadValidator->files = $fileInfoArray;
        $validationError = $this->uploadValidator->getError('file');
        if ($validationError === null) {
            $checkImage = LSYii_ImageValidator::validateImage(
                $fileInfoArray['file']
            );
            if ($checkImage['check'] !== false) {
                $destinationDir = $this->getSurveyUploadDirectory($surveyId);
                if (!is_writeable($destinationDir)) {
                    $returnedData['uploadResultMessage'] = gT(
                        "Could not save file"
                    );
                } else {
                    $returnedData = $this->saveFileInDirectory(
                        $fileInfoArray['file'],
                        $destinationDir
                    );
                }
            } else {
                $returnedData['uploadResultMessage'] = $checkImage['uploadresult'];
            }
        } else {
            $returnedData['uploadResultMessage'] = $validationError;
        }
        return $returnedData;
    }

    public function deleteSurveyFile($surveyId, $fileName)
    {
        // todo implement file deletion logic
    }

    /**
     * If not found, it creates the necessary directories.
     * Returns the path to the created directory.
     * @param int $surveyId
     * @param string $directoryName
     * @return string
     */
    public function getSurveyUploadDirectory(
        int $surveyId,
        string $directoryName = 'images'
    ) {
        $surveyDir = Yii::app()->getConfig(
            'uploaddir'
        ) . DIRECTORY_SEPARATOR . "surveys" . DIRECTORY_SEPARATOR . $surveyId;
        if (!is_dir($surveyDir)) {
            @mkdir($surveyDir);
        }
        if (!is_dir($surveyDir . DIRECTORY_SEPARATOR . $directoryName)) {
            @mkdir($surveyDir . DIRECTORY_SEPARATOR . $directoryName);
        }
        return $surveyDir . DIRECTORY_SEPARATOR . $directoryName . DIRECTORY_SEPARATOR;
    }

    /**
     * This function will sanitize the filename to prevent potential security issues.
     * Afterwards it will store the file into the destination directory.
     * @param array $fileInfoArray
     * @param string $destinationDir
     * @return array
     */
    public function saveFileInDirectory(array $fileInfoArray, string $destinationDir)
    {
        $success = false;
        $fileName = sanitize_filename(
            $fileInfoArray['name'],
            false,
            false,
            false
        ); // Don't force lowercase or alphanumeric
        $fileName = $this->handleDuplicateFileName($fileName, $destinationDir);
        $fullFilePath = $destinationDir . $fileName;
        $debugInfoArray[] = $destinationDir;
        $debugInfoArray[] = $fileName;
        $debugInfoArray[] = $fullFilePath;
        if (!@move_uploaded_file($fileInfoArray['tmp_name'], $fullFilePath)) {
            $uploadResult = gT(
                "An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder."
            );
        } else {
            $uploadResult = sprintf(gT("File %s uploaded"), $fileName);
            $success = true;
        };

        return [
            'debug' => $debugInfoArray,
            'uploadResultMessage' => $uploadResult,
            'success' => $success,
            'allFilesInDir' => []
        ];
    }

    /**
     * Handles duplicate filenames in a directory by appending a numeric suffix (e.g., "(1)", "(2)").
     *
     * This function checks if a file with the given filename exists in the specified directory.
     * If it does, it renames the file by appending a numeric suffix, incrementing the number
     * until a unique filename is found, mimicking Windows Explorer behavior.
     *
     * @param string $fileName The name of the file to check for duplicates (e.g., "example.txt").
     * @param string $path The directory path where the file resides.
     * @return string A unique filename with no conflicts in the given directory.
     */
    private function handleDuplicateFileName($fileName, $path)
    {
        // Separate the file name into name and extension
        $fileInfo = pathinfo($fileName);
        $baseName = $fileInfo['filename']; // File name without extension
        $extension = isset($fileInfo['extension']) ? '.' . $fileInfo['extension'] : ''; // File extension

        $newFileName = $fileName; // Start with the original file name
        $counter = 1;

        // Check if the file exists in the given path
        while (file_exists($path . $newFileName)) {
            // Append the counter to the base name
            $newFileName = $baseName . "($counter)" . $extension;
            $counter++;
        }

        return $newFileName; // Return the unique file name
    }

    /**
     * If config param "uniq_upload_dir" is set to true, convert survey ID to 'uniq'
     * @param int|string $surveyId
     * @return int|string
     */
    public function convertSurveyIdWhenUniqUploadDir($surveyId)
    {
        if (App()->getConfig('uniq_upload_dir') && !empty($surveyId)) {
            $surveyId = 'uniq';
        }
        return $surveyId;
    }
}
