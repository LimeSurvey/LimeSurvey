<?php

namespace LimeSurvey\Models\Services;

class UploadValidator
{
    /** @var array<string,mixed> HTTP POST variables*/
    private $post;

    /** @var array<string,mixed> HTTP File Upload variables*/
    private $files;

    /**
     * UploadValidator constructor.
     *
     * @param array|null $post   HTTP POST variables. If null, $_POST is used.
     * @param array|null $files  HTTP File Upload variables. If null, $_FILES is used.
     */
    public function __construct($post = null, $files = null)
    {
        $this->post = is_null($post) ? $_POST : $post;
        $this->files = is_null($files) ? $_FILES : $files;
    }

    /**
     * Check uploaded file size
     *
     * @param string $fileName the name of the posted file
     * @param mixed $customMaxSize maximum file upload size
     *
     * @return string|null the error message or null if all checks are ok
     */
    public function getError($fileName, $customMaxSize = null)
    {
        if (is_null($customMaxSize)) {
            $maximumSize = getMaximumFileUploadSize();
        } else {
            $maximumSize = min((int) $customMaxSize, getMaximumFileUploadSize());
        }

        // When 'post_max_size' is exceeded $_POST and $_FILES are empty.
        // There is no way to confirm if the superglobals are empty because 'post_max_size' was
        // exceeded, or because nothing was posted.
        if (empty($this->post) && empty($this->files)) {
            return sprintf(
                gT("No file was uploaded or the request exceeded %01.2f MB."),
                convertPHPSizeToBytes(ini_get('post_max_size')) / 1024 / 1024
            );
        }

        if (!isset($this->files[$fileName])) {
            return gT("File not found.");
        }

        $fileSize = $this->files[$fileName]['size'];

        if ($fileSize > $maximumSize || $this->files[$fileName]['error'] == 1 || $this->files[$fileName]['error'] == 2) {
            return sprintf(
                gT("Sorry, this file is too large. Only files up to %01.2f MB are allowed."),
                $maximumSize / 1024 / 1024
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
    public function redirectOnError($fileName, $redirectUrl, $customMaxSize = null)
    {
        $error = $this->getError($fileName, $customMaxSize);
        if (!is_null($error)) {
            \Yii::app()->setFlashMessage($error, 'error');
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
    public function renderJsonOnError($fileName, $debugInfo = [], $customMaxSize = null)
    {
        $error = $this->getError($fileName, $customMaxSize);
        if (!is_null($error)) {
            return \Yii::app()->getController()->renderPartial(
                '/admin/super/_renderJson',
                array('data' => ['success' => 'error', 'message' => $error, 'debug' => $debugInfo]),
                false,
                false
            );
        }
    }

    /**
     * Sets the post property of the UploadValidator instance.
     *
     * This method allows for updating the post array after the object has been instantiated.
     * It's useful for testing or when post data needs to be modified after initial creation.
     *
     * @param array $post An associative array of POST data, typically in the format of $_POST.
     *                    This array contains key-value pairs of form data submitted via HTTP POST.
     *
     * @return void This method does not return a value.
     */
    public function setPost($post)
    {
        $this->post = $post;
    }

    /**
     * Sets the files property of the UploadValidator instance.
     *
     * This method allows for updating the files array after the object has been instantiated.
     * It's useful for testing or when file data needs to be modified after initial creation.
     *
     * @param array $files An associative array of uploaded file information, typically in the format of $_FILES.
     *                     Each element should contain file details like name, type, size, tmp_name, and error.
     *
     * @return void This method does not return a value.
     */
    public function setFiles($files)
    {
        $this->files = $files;
    }
}
