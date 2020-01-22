<?php
/*
 * LimeSurvey
 * Copyright (C) 2019 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * Extend CFileHelper to allow update of
 * - magic database (can be set by MAGIC env, but unsure user can do it)
 * - magic file (return PHP array of extension by mime-type)
 * @author Denis Chenu
 * @version 1.0.0
 * 
 */

class LSFileHelper extends CFileHelper
{

    /**
     * @inheritdoc
     * Can not call parent since usage of self::getMimeType
     * Set $magicFile (php array) from config (if is null)
     * @see https://www.yiiframework.com/doc/api/1.1/CFileHelper#getExtensionByMimeType-detail
     * @return string|null extension name. Null is returned if the extension cannot be determined.
     */
    public static function getExtensionByMimeType($file,$magicFile=null)
    {
        static $mimeTypes,$customMimeTypes=array();
        if(empty($magicFile) && Yii::app()->getConfig('magic_file')) {
            $magicFile = Yii::app()->getConfig('magic_file');
        }
        if(empty($magicFile) && $mimeTypes===null) {
            $mimeTypes=require(Yii::getPathOfAlias('system.utils.fileExtensions').'.php');
        }
        elseif($magicFile!==null && !isset($customMimeTypes[$magicFile])) {
            $customMimeTypes[$magicFile]=require($magicFile);
        }
        $mime = self::getMimeType($file);
        if($mime !== null) {
            $mime=strtolower($mime);
            if($magicFile===null && isset($mimeTypes[$mime])) {
                return $mimeTypes[$mime];
            } elseif($magicFile!==null && isset($customMimeTypes[$magicFile][$mime])) {
                return $customMimeTypes[$magicFile][$mime];
            }
        }
        return null;
    }

    /**
     * @inheritdoc
     * Set $magicFile (magic dataBase) from config (if is null)
     * Use the magic file set by user only iof needed (else keep pĥp default or MAGIC env)
     * @see https://www.php.net/manual/en/function.finfo-open.php
     * @return string|null string if the MIME type. Null is returned if the MIME type cannot be determined.
     */
    public static function getMimeType($file,$magicFile=null,$checkExtension=true)
    {
        $mimeType = parent::getMimeType($file,$magicFile,$checkExtension);
        if((!empty($magicFile) && $mimeType != "application/octet-stream") || !is_null($magicFile)) {
            return $mimeType;
        }
        if(empty($magicFile) && Yii::app()->getConfig('magic_database')) {
            $magicFile = Yii::app()->getConfig('magic_database');
        }
        // Some PHP version can throw Notice with some files, disable this notice issue #15565
        $iErrorReportingState = error_reporting();
        error_reporting(0);
        $mimeType = parent::getMimeType($file,$magicFile,$checkExtension);
        error_reporting($iErrorReportingState);
        return $mimeType;
    }
}
