<?php

namespace LimeSurvey\Models\Services;

use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LSYii_ImageValidator;
use Permission;

class FileUploadService
{
    /** @var int Largest image file for texts (question text etc.), in bytes */
    public const CONTENT_IMAGE_MAX_SIZE = 10 * 1024 * 1024;

    /** @var int Most pixels of an image for texts, protects against images that need huge memory when decoded */
    public const CONTENT_IMAGE_MAX_MEGAPIXELS = 40;

    /** @var array<string,int> Allowed extensions of images for texts, with their image type */
    private const CONTENT_IMAGE_TYPES = [
        'png' => IMAGETYPE_PNG,
        'jpg' => IMAGETYPE_JPEG,
        'jpeg' => IMAGETYPE_JPEG,
        'webp' => IMAGETYPE_WEBP,
    ];

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
     * Validates an image inserted into a text (e.g. with the HTML editor of the question text) and saves it in the
     * survey's images folder, like the images of the classic editor.
     * Only PNG, JPEG and WebP files up to CONTENT_IMAGE_MAX_SIZE and CONTENT_IMAGE_MAX_MEGAPIXELS are accepted,
     * and their metadata (e.g. the EXIF location of photos) is removed.
     *
     * @param int|string $surveyId Survey ID
     * @param array $fileInfoArray Uploaded file, $_FILES format with key "file"
     * @return array Upload result: success, uploadResultMessage, fileName, and the image's filePath and previewUrl
     *  under "uploaded"
     * @throws PermissionDeniedException
     */
    public function storeContentImage($surveyId, array $fileInfoArray)
    {
        $this->checkUpdatePermission($surveyId);
        $returnedData = ['success' => false];

        if (empty($fileInfoArray['file'])) {
            $returnedData['uploadResultMessage'] = gT('No file uploaded');
            return $returnedData;
        }

        $surveyId = $this->convertSurveyIdWhenUniqUploadDir($surveyId);
        $destinationDir = $this->getSurveyUploadDirectory($surveyId);

        $this->uploadValidator->setPost(['surveyId' => $surveyId]);
        $this->uploadValidator->setFiles($fileInfoArray);
        $validationError = $this->uploadValidator->getError('file', self::CONTENT_IMAGE_MAX_SIZE)
            ?? $this->validateAndCleanContentImage($fileInfoArray['file']);
        if ($validationError === null && !is_writeable($destinationDir)) {
            $validationError = gT("Could not save file");
        }
        if ($validationError !== null) {
            $returnedData['uploadResultMessage'] = $validationError;
            return $returnedData;
        }

        $returnedData = $this->saveFileInDirectory(
            $fileInfoArray['file'],
            $destinationDir
        );
        unset($returnedData['debug']);
        if (!$returnedData['success']) {
            return $returnedData;
        }

        $filePath = $this->getSurveyUploadUrl($surveyId) . '/' . $returnedData['fileName'];
        $returnedData['uploaded'] = [
            'filePath' => $filePath,
            'previewUrl' => $this->toAbsoluteUrl($filePath),
        ];

        return $returnedData;
    }

    /**
     * Checks that an uploaded file is a PNG, JPEG or WebP image matching its extension, not larger than
     * CONTENT_IMAGE_MAX_MEGAPIXELS, and removes its metadata.
     * The content of the file is checked, not the MIME type sent by the browser.
     *
     * @param array $file Uploaded file, one $_FILES entry with name and tmp_name
     * @return string|null Error message, null if the image is valid
     */
    private function validateAndCleanContentImage(array $file)
    {
        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!isset(self::CONTENT_IMAGE_TYPES[$extension])) {
            return gT("This file type is not allowed to be uploaded.");
        }
        $invalidImageError = gT("This file is not a valid PNG, JPEG or WebP image.");
        $path = (string) ($file['tmp_name'] ?? '');
        $imageInfo = is_file($path) ? @getimagesize($path) : false;
        if (
            $imageInfo === false
            || $imageInfo[2] !== self::CONTENT_IMAGE_TYPES[$extension]
            || $imageInfo[0] <= 0
            || $imageInfo[1] <= 0
        ) {
            return $invalidImageError;
        }
        // Don't fall back to the extension: The tmp file name doesn't have one
        $detectedMimeType = \CFileHelper::getMimeType($path, null, false);
        if (!empty($detectedMimeType) && strtolower((string) $detectedMimeType) !== $imageInfo['mime']) {
            return $invalidImageError;
        }
        if ($imageInfo[0] * $imageInfo[1] > self::CONTENT_IMAGE_MAX_MEGAPIXELS * 1000000) {
            return sprintf(
                gT("This image is too large. Images can have at most %s megapixels."),
                self::CONTENT_IMAGE_MAX_MEGAPIXELS
            );
        }
        if (!(new ImageMetadataRemover())->removeMetadata($path, $imageInfo[2])) {
            return $invalidImageError;
        }
        return null;
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
     * @param string $path The directory path where the file resides, with or without a trailing slash.
     * @return string A unique filename with no conflicts in the given directory.
     */
    private function handleDuplicateFileName($fileName, $path)
    {
        $path = $this->rTrimPathSeparators($path) . '/';
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
