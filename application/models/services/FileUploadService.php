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

        if (empty($fileInfoArray)) {
            $returnedData['uploadResultMessage'] = gT('No file uploaded');
            return $returnedData;
        }

        $surveyId = $this->convertSurveyIdWhenUniqUploadDir($surveyId);
        $destinationDir = $this->getSurveyUploadDirectory($surveyId);

        $validationError = $this->validateFileUpload($surveyId, $fileInfoArray, $destinationDir);
        if ($validationError !== null) {
            $returnedData['uploadResultMessage'] = $validationError;
            return $returnedData;
        }

        $returnedData = $this->saveFileInDirectory(
            $fileInfoArray['file'],
            $destinationDir
        );

        $baseUrl = $this->rTrimPathSeparators(App()->getBaseUrl(true));
        $returnedData['uploaded']['filePath'] = $this->convertFullIntoRelativePath(
            $returnedData['debug'][2]
        );
        $returnedData['uploaded']['fileUrl'] = $baseUrl . '/'
            . $returnedData['debug'][2];
        $returnedData['uploaded']['previewPath'] = $this->getPreviewPath(
            $returnedData['uploaded']['filePath']
        );
        $returnedData['uploaded']['previewUrl'] = $baseUrl . '/'
            . $returnedData['uploaded']['filePath'];
        unset($returnedData['debug']);
        $returnedData['allFilesInDir'] = $this->getFilesPathsFromDirectory(
            $destinationDir,
            $surveyId
        );

        return $returnedData;
    }


    /**
     * @param int|string $surveyId
     * @param array $fileInfoArray
     * @param string $destinationDir
     * @return null|string
     */
    private function validateFileUpload($surveyId, array $fileInfoArray, $destinationDir)
    {
        $this->uploadValidator->setPost(['surveyId' => $surveyId]);
        $this->uploadValidator->setFiles($fileInfoArray);
        $error = $this->uploadValidator->getError('file');
        if ($error != null) {
            return $error;
        }

        $checkImage = LSYii_ImageValidator::validateImage(
            $fileInfoArray['file']
        );
        if ($checkImage['check'] === false) {
            return $checkImage['uploadresult'];
        }

        if (!is_writeable($destinationDir)) {
            return gT(
                "Could not save file"
            );
        }

        return null;
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
        $surveyDir = $this->buildUrlPath([
            App()->getConfig(
                'uploaddir'
            ),
            'surveys',
            $surveyId
        ]);
        if (!is_dir($surveyDir)) {
            @mkdir($surveyDir);
        }
        if (!is_dir($surveyDir . DIRECTORY_SEPARATOR . $directoryName)) {
            @mkdir($surveyDir . DIRECTORY_SEPARATOR . $directoryName);
        }

        return $this->rTrimPathSeparators($surveyDir . DIRECTORY_SEPARATOR . $directoryName);
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
        $fileName = $fileInfoArray['name'] = sanitize_filename(
            $fileInfoArray['name'],
            false,
            false,
            false
        ); // Don't force lowercase or alphanumeric
        $fileAlreadyExists = $this->isDuplicateFile(
            $fileInfoArray,
            $destinationDir
        );
        if (!$fileAlreadyExists) {
            $fileName = $this->handleDuplicateFileName(
                $fileName,
                $destinationDir
            );
        }
        $fullFilePath = $destinationDir . DIRECTORY_SEPARATOR . $fileName;
        $debugInfoArray[] = $destinationDir;
        $debugInfoArray[] = $fileName;
        $debugInfoArray[] = $fullFilePath;
        if (
            $fileAlreadyExists
            || @move_uploaded_file(
                $fileInfoArray['tmp_name'],
                $fullFilePath
            )
        ) {
            $uploadResult = sprintf(gT("File %s uploaded"), $fileName);
            $success = true;
        } else {
            $uploadResult = gT(
                "An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder."
            );
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
        $extension = isset($fileInfo['extension']) ? '.'
            . $fileInfo['extension'] : '';

        $newFileName = $fileName;
        $counter = 1;

        while (file_exists($path . $newFileName)) {
            $newFileName = $baseName . "($counter)" . $extension;
            $counter++;
        }

        return $newFileName;
    }

    /**
     * If a file with the same name exists in the path, this function will
     * compare the new file with the existing one.
     * If those two files are identical, it will return true.
     * @param array $fileInfoArray The array containing the file and name.
     * @param string $path The directory path where the file resides.
     * @return bool
     */
    private function isDuplicateFile(array $fileInfoArray, $path)
    {
        $newFilePath = $fileInfoArray['tmp_name'];
        $existingFilePath = $path . DIRECTORY_SEPARATOR . $fileInfoArray['name'];

        // Check if a file with the same name exists
        if (!file_exists($existingFilePath)) {
            return false;
        }

        // Compare file sizes
        if (filesize($newFilePath) !== filesize($existingFilePath)) {
            return false;
        }

        // Compare MD5 hashes
        $newFileHash = md5_file($newFilePath);
        $existingFileHash = md5_file($existingFilePath);

        return $newFileHash === $existingFileHash;
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
        $baseUrl = $this->rTrimPathSeparators(App()->getBaseUrl(true));
        $relativePath = $this->convertFullIntoRelativePath($directory);
        $files = $this->getFilesFromDirectory($directory, $surveyId);
        foreach ($files as $i => $file) {
            $filesOutput[$i]['filePath'] = $relativePath . DIRECTORY_SEPARATOR . $file;
            $filesOutput[$i]['fileUrl'] = $baseUrl . '/' .  $relativePath . '/' . $file;
            $filesOutput[$i]['previewPath'] = $this->getPreviewPath(
                $relativePath . '/' . $file
            );
            $filesOutput[$i]['previewUrl'] = $baseUrl . '/' . $relativePath . '/' . $file;
        }

        return $filesOutput;
    }

    /**
     * Removes the configured "uploaddir" part from the path which
     * results in the relative path
     * @param string $filePath
     * @return string
     */
    private function convertFullIntoRelativePath(string $filePath)
    {
        return $this->rTrimPathSeparators(
            substr($filePath, strlen($this->getUploadPath()))
        );
    }

    private function getUploadPath()
    {
        return $this->rTrimPathSeparators(
            dirname(App()->getConfig(
                'uploaddir'
            ))
        );
    }

    private function rTrimPathSeparators($path)
    {
        return rtrim($path, '/\\');
    }

    private function buildUrlPath($parts)
    {
        return implode(
            '/',
            array_map(function ($part) {
                return $this->rTrimPathSeparators($part);
            }, $parts)
        );
    }

    /**
     * Logic will be added later, for images previewPath is the same as filePath
     * @param string $filePath
     * @return string
     */
    private function getPreviewPath(string $filePath)
    {
        return $filePath;
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
