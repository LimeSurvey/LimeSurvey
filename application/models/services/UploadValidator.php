<?php

namespace LimeSurvey\Models\Services;

class UploadValidator
{
    /**
     * Check uploaded file size
     *
     * @param string $fileName the name of the posted file
     * @param mixed $customMaxSize maximum file upload size
     *
     * @throws \Exception if the file is too large or no file is found.
     */
    public function checkUploadedFileSize($fileName, $customMaxSize = null)
    {
        if (is_null($customMaxSize)) {
            $maximumSize = getMaximumFileUploadSize();
        } else {
            $maximumSize = min((int) $customMaxSize, getMaximumFileUploadSize());
        }

        // When 'post_max_size' is exceeded $_POST and $_FILES are empty.
        // There is no way to confirm if the superglobals are empty because 'post_max_size' was
        // exceeded, or because nothing was posted.
        if (empty($_POST) && empty($_FILES)) {
            throw new \Exception(
                sprintf(
                    gT("No file was uploaded or the request exceeded %01.2f MB."),
                    convertPHPSizeToBytes(ini_get('post_max_size')) / 1024 / 1024
                )
            );
        }

        if (!isset($_FILES[$fileName])) {
            throw new \Exception(gT("File not found."));
        }

        $fileSize = $_FILES[$fileName]['size'];

        if ($fileSize > $maximumSize || $_FILES[$fileName]['error'] == 1 || $_FILES[$fileName]['error'] == 2) {
            throw new \Exception(
                sprintf(
                    gT("Sorry, this file is too large. Only files up to %01.2f MB are allowed."),
                    $maximumSize / 1024 / 1024
                )
            );
        }
    }

    /**
     * Check uploaded file size. Redirects to the specified URL on failure.
     *
     * @param string $fileName the name of the posted file
     * @param mixed $redirectUrl the URL to redirect on failure
     * @param mixed $customMaxSize maximum file upload size
     */
    public function checkUploadedFileSizeAndRedirect($fileName, $redirectUrl, $customMaxSize = null)
    {
        try {
            $this->checkUploadedFileSize($fileName, $customMaxSize);
        } catch (\Exception $ex) {
            \Yii::app()->setFlashMessage($ex->getMessage(), 'error');
            \Yii::app()->getController()->redirect($redirectUrl);
        }
    }

    /**
     * Check uploaded file size. Renders JSON on failure.
     *
     * @param string $fileName the name of the posted file
     * @param array $debugInfo the URL to redirect on failure
     * @param mixed $customMaxSize maximum file upload size
     */
    public function checkUploadedFileSizeAndRenderJson($fileName, $debugInfo = [], $customMaxSize = null)
    {
        try {
            $this->checkUploadedFileSize($fileName, $customMaxSize);
        } catch (\Exception $ex) {
            $error = $ex->getMessage();
            return \Yii::app()->getController()->renderPartial(
                '/admin/super/_renderJson',
                array('data' => ['success' => 'error', 'message' => $error, 'debug' => $debugInfo]),
                false,
                false
            );
        }
    }
}
