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
     * Validates an uploaded image and saves it in the survey's images folder.
     * @param int|string $surveyId Survey ID
     * @param array $fileInfoArray Uploaded file, $_FILES format with key "file"
     * @return array Upload result, with the image's filePath and previewUrl under "uploaded"
     * @throws PermissionDeniedException
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

        // Includes the install subfolder, like the classic editor
        $filePath = $this->getSurveyUploadUrl($surveyId) . '/' . $returnedData['fileName'];
        $returnedData['uploaded'] = [
            'filePath' => $filePath,
            'previewUrl' => $this->toAbsoluteUrl($filePath),
        ];
        unset($returnedData['debug']);

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
        if (!is_dir($surveyDir . '/' . $directoryName)) {
            @mkdir($surveyDir . '/' . $directoryName);
        }

        return $this->rTrimPathSeparators($surveyDir . '/' . $directoryName);
    }

    /**
     * Returns the URL of a survey's upload folder.
     * @param int|string $surveyId Survey ID, or "uniq"
     * @param string $directoryName Folder name
     * @return string URL like "/limesurvey/upload/surveys/123/images"
     */
    private function getSurveyUploadUrl($surveyId, string $directoryName = 'images')
    {
        return $this->rTrimPathSeparators((string) App()->getConfig('uploadurl'))
            . '/surveys/' . $surveyId . '/' . $directoryName;
    }

    /**
     * Adds the current host to a URL without one.
     * @param string $url URL like "/upload/a.png"
     * @return string URL like "https://example.org/upload/a.png"
     */
    private function toAbsoluteUrl(string $url)
    {
        $parsedUrl = parse_url($url);
        if (isset($parsedUrl['host'])) {
            return $url;
        }
        return App()->getRequest()->getHostInfo() . $url;
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
        $sanitizedName = sanitize_filename(
            $fileInfoArray['name'],
            false,
            false,
            false
        ); // Don't force lowercase or alphanumeric
        // Defense-in-depth: discard any directory component regardless of OS separator,
        // so a crafted filename (e.g. "../../evil.png") can never escape $destinationDir.
        $fileName = $fileInfoArray['name'] = basename(str_replace('\\', '/', $sanitizedName));
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
        $fullFilePath = $destinationDir . '/' . $fileName;
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
            'fileName' => $fileName,
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
        $existingFilePath = $path . '/' . $fileInfoArray['name'];

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
