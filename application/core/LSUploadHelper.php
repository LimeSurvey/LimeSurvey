<?php
/*
 * LimeSurvey
 * Copyright (C) 2020 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * 
 */

class LSUploadHelper
{
    /**
     * Check uploaded file size
     * 
     * @param string $sFileName the name of the posted file
     * @param mixed $customMaxSize maximum file upload size
     * 
     * @throws Exception if the file is too large or no file is found.
     */
    public static function checkUploadedFileSize($sFileName, $customMaxSize = null)
    {
        if (is_null($customMaxSize)) {
            $iMaximumSize = getMaximumFileUploadSize();
        } else {
            $iMaximumSize = min((int) $customMaxSize, getMaximumFileUploadSize());
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

        if (!isset($_FILES[$sFileName])) {
            throw new \Exception(gT("File not found."));
        }

        $iFileSize = $_FILES[$sFileName]['size'];

        if ($iFileSize > $iMaximumSize || $_FILES[$sFileName]['error'] == 1 || $_FILES[$sFileName]['error'] == 2) {
            throw new \Exception(
                sprintf(
                    gT("Sorry, this file is too large. Only files up to %01.2f MB are allowed."),
                    $iMaximumSize / 1024 / 1024
                )
            );
        }
    }

    /**
     * Check uploaded file size. Redirects to the specified URL on failure.
     * 
     * @param string $sFileName the name of the posted file
     * @param mixed $redirectUrl the URL to redirect on failure
     * @param mixed $customMaxSize maximum file upload size
     */
    public static function checkUploadedFileSizeAndRedirect($sFileName, $redirectUrl, $customMaxSize = null)
    {
        try {
            self::checkUploadedFileSize($sFileName, $customMaxSize);
        } catch (Exception $ex) {
            Yii::app()->setFlashMessage($ex->getMessage(), 'error');
            App()->getController()->redirect($redirectUrl);
        }
    }

    /**
     * Check uploaded file size. Renders JSON on failure.
     * 
     * @param string $sFileName the name of the posted file
     * @param array $debugInfo the URL to redirect on failure
     * @param mixed $customMaxSize maximum file upload size
     */
    public static function checkUploadedFileSizeAndRenderJson($sFileName, $debugInfo = [], $customMaxSize = null)
    {
        try {
            self::checkUploadedFileSize($sFileName, $customMaxSize);
        } catch (Exception $ex) {
            $error = $ex->getMessage();
            return Yii::app()->getController()->renderPartial(
                '/admin/super/_renderJson',
                array('data' => ['success' => 'error', 'message' => $error, 'debug' => $debugInfo]),
                false,
                false
            );
        }
    }
}
