<?php

namespace LimeSurvey\Models\Services;

use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LSYii_ImageValidator;
use Permission;

class FileUploadService
{
    private UploadValidator $uploadValidator;

    private Permission $modelPermission;

    public function __construct(UploadValidator $uploadValidator, Permission $modelPermission)
    {
        $this->uploadValidator = $uploadValidator;
        $this->modelPermission = $modelPermission;
    }

    /**
     * @param int|string $surveyId
     * @param array $fileInfoArray
     * @return array|false[]
     */
    public function storeSurveyImage($surveyId, array $fileInfoArray)
    {
        $this->checkUpdatePermission($surveyId);
        $returnedData = ['success' => false];
        if (!empty($fileInfoArray)) {
            $surveyId = $this->convertSurveyIdWhenUniqUploadDir($surveyId);
            $destinationDir = $this->getSurveyUploadDirectory($surveyId);
            $this->uploadValidator->post = ['surveyId' => $surveyId];
            $this->uploadValidator->files = $fileInfoArray;
            $validationError = $this->uploadValidator->getError('file');
            if ($validationError === null) {
                $checkImage = LSYii_ImageValidator::validateImage(
                    $fileInfoArray['file']
                );
                if ($checkImage['check'] !== false) {
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
            unset($returnedData['debug']);
            $returnedData['allFilesInDir'] = $this->getFilesPathsFromDirectory(
                $destinationDir,
                $surveyId
            );
        } else {
            $returnedData['uploadResultMessage'] = gT('No file uploaded');
        }

        return $returnedData;
    }

    /**
     * If not found, it creates the necessary directories.
     * Returns the path to the created directory.
     * @param int|string $surveyId
     * @param string $directoryName
     * @return string
     */
    public function getSurveyUploadDirectory(
        $surveyId,
        string $directoryName = 'images'
    ) {
        $surveyDir = App()->getConfig(
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
    public function saveFileInDirectory(
        array $fileInfoArray,
        string $destinationDir
    ) {
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
        }

        return [
            'debug' => $debugInfoArray,
            'uploadResultMessage' => $uploadResult,
            'success' => $success,
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
        $fileInfo = pathinfo($fileName);
        $baseName = $fileInfo['filename'];
        $extension = isset($fileInfo['extension']) ? '.' . $fileInfo['extension'] : '';

        $newFileName = $fileName;
        $counter = 1;

        while (file_exists($path . $newFileName)) {
            $newFileName = $baseName . "($counter)" . $extension;
            $counter++;
        }

        return $newFileName;
    }

    /**
     * If config param "uniq_upload_dir" is set to true, convert survey ID to 'uniq'
     * @param int $surveyId
     * @return int|string
     */
    public function convertSurveyIdWhenUniqUploadDir($surveyId)
    {
        if (App()->getConfig('uniq_upload_dir') && !empty($surveyId)) {
            $surveyId = 'uniq';
        }
        return $surveyId;
    }

    /**
     * Get all fileNames from a directory
     * @param string $directory
     * @param int $surveyId
     * @return array
     */
    private function getFilesFromDirectory(string $directory, int $surveyId)
    {
        $files = [];
        if (!is_dir($directory)) {
            return $files;
        }

        $items = scandir($directory);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $directory . DIRECTORY_SEPARATOR . $item;
            if (is_file($path)) {
                $files[] = $item;
            } elseif (is_dir($path)) {
                $subFiles = $this->getFilesFromDirectory(
                    $path,
                    $surveyId,
                    false
                );
                foreach ($subFiles as $subFile) {
                    $files[] = $item . DIRECTORY_SEPARATOR . $subFile;
                }
            }
        }

        return $files;
    }

    /**
     * Retrieves files from a directory and returns them in an associative array
     * with filePath and previewPath for every single file.
     * @param string $directory
     * @param int $surveyId
     * @return array
     */
    private function getFilesPathsFromDirectory(
        string $directory,
        int $surveyId
    ) {
        $filesOutput = [];
        $rootDir = App()->getConfig('rootdir');

        $relativePath = substr($directory, strlen($rootDir));
        $files = $this->getFilesFromDirectory($directory, $surveyId);
        foreach ($files as $i => $file) {
            $filesOutput[$i]['filePath'] = $relativePath . $file;
            $filesOutput[$i]['previewPath'] = $relativePath . $file; // Preview path is the same as file path for now
        }

        return $filesOutput;
    }

    /**
     * @param $surveyId
     * @return void
     * @throws PermissionDeniedException
     */
    private function checkUpdatePermission($surveyId)
    {
        if (
            !$this->modelPermission->hasSurveyPermission(
                $surveyId,
                'surveycontent',
                'update'
            )
        ) {
            throw new PermissionDeniedException(
                'Access denied'
            );
        }
    }
}
