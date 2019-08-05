<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
 * LimeSurvey
 * Copyright (C) 2013 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * Filemanagement Controller
 *
 * This controller is used in the global file management as well as the survey specific
 *
 * @package        LimeSurvey
 * @subpackage    Backend
 */
class LimeSurveyFileManager extends Survey_Common_Action
{
    /**
     * In controller error storage to have a centralizes error message system
     *
     * @var Object
     */
    private $oError = null;

    /**
     * globally available directories
     * @TODO make this a configuration in global config
     *
     * @var array
     */
    private $globalDirectories = [
        'upload' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'survey' . DIRECTORY_SEPARATOR . 'generalfiles',
        'upload' . DIRECTORY_SEPARATOR . 'global',
    ];

    /**
     * The extension that are allowed to be shown and uploaded
     * @TODO make this a configuration in global config
     *
     * @var array
     */
    private $allowedFileExtensions = [
        //Documents
        'xls', 'doc', 'xlsx', 'docx', 'odt', 'ods', 'pdf',
        //Images
        'png', 'bmp', 'gif', 'jpg', 'jpeg', 'tif', 'svg',
        //soundfiles
        'wav', 'mp3', 'flac', 'aac', 'm4a', 'opus', 'ogg', 'wma', 'mka',
        //videos
        'mp4', 'avi', 'mkv', 'mpeg', 'mpg', 'wmv', 'h264', 'h265', 'mov', 'webm', 'divx', 'xvid',
    ];

    /**
     * Basic index function to call the view
     *
     * @param int|null $iSurveyId
     * @return void Renders HTML-page
     */
    public function index($surveyid = null)
    {
        $possibleFolders = $this->_collectFolderList($surveyid);

        $aTranslate = [
            'File management' => gT('File management'),
            'Upload' => gT('Upload'),
            'Cancel transit' => gT('Cancel transit'),
            'Copy/Move' => gT('Copy/Move'),
            'Upload a file' => gT('Upload a file'),
            'Drag and drop here, or click once to start uploading' => gT('Drag and drop here, or click once to start uploading'),
            'File is uploaded to currently selected folder' => gT('File is uploaded to currently selected folder'),
            'File name' => gT('File name'),
            'Type' => gT('Type'),
            'Size' => gT('Size'),
            'Mod time' => gT('Mod time'),
            'Action' => gT('Action'),
            'Delete file' => gT('Delete file'),
            'Copy file' => gT('Copy file'),
            'Move file' => gT('Move file'),
        ];

        Yii::app()->getClientScript()->registerPackage('filemanager');
        $aData['jsData'] = [
            'surveyid' => $surveyid,
            'possibleFolders' => $possibleFolders,
            'i10N' => $aTranslate,
            'baseUrl' => $this->getController()->createUrl('admin/filemanager', ['sa' => '']),
        ];
        $renderView = $surveyid == null ? 'view' : 'surveyview';

        if ($surveyid !== null) {
            $oSurvey = Survey::model()->findByPk($surveyid);
            $aData['surveyid'] = $surveyid;
            $aData['surveybar']['buttons']['view'] = true;
            $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $surveyid . ")";
            $aData['subaction'] = gT("File manager");
        }

        $this->_renderWrappedTemplate('SurveyFiles', $renderView, $aData);
    }

    public function getFilesForSurvey($surveyid = null)
    {
        $folders = $this->_collectCompleteFolderList($surveyid);
        $result = [];

        foreach ($folders as $folder) {
            $result[$folder] = $this->_collectFileList($folder);
        }

        $this->_printJsonResponse($result);
        return;
    }

    /**
     * Calls the list of files in the selected folder
     *
     * @param string $folder
     * @param int|null $iSurveyId
     * @return void Renders json document
     */
    public function getFileList($folder, $iSurveyId = null)
    {
        $directory = $this->_checkFolder($folder, $iSurveyId);

        if ($directory === false) {
            $this->_printJsonError();
            return;
        }

        $fileList = $this->_collectFileList($directory);

        $this->_printJsonResponse($fileList);
        return;
    }

    public function getFolderList($iSurveyId = null)
    {
        $aAllowedFolders = $this->_collectRecursiveFolderList($iSurveyId);

        $this->_printJsonResponse($aAllowedFolders);
        return;
    }

    public function transitFile()
    {
        $folder = Yii::app()->request->getPost('targetFolder');
        $iSurveyId = Yii::app()->request->getPost('surveyid');
        $file = Yii::app()->request->getPost('file');
        $action = Yii::app()->request->getPost('action');

        $checkDirectory = $this->_checkFolder($folder, $iSurveyId);

        if ($checkDirectory === false) {
            $this->_printJsonError();
            return;
        }

        $realTargetPath = dirname(Yii::app()->basePath) . DIRECTORY_SEPARATOR . $folder;
        $fileDestination = realpath($realTargetPath) . DIRECTORY_SEPARATOR . $file['shortName'];

        $realFilePath = dirname(Yii::app()->basePath) . DIRECTORY_SEPARATOR . $file['path'];
        $fileSource = realpath($realFilePath);

        if ($this->checkTargetExists($fileDestination) && Yii::app()->getConfig('overwritefiles') == 0) {
            $ext = pathinfo($fileDestination, PATHINFO_EXTENSION);
            $shorthash = hash('adler32', microtime());
            $fileDestination = preg_replace("/\." . $ext . "/", "-" . $shorthash . "." . $ext, $fileDestination);
        }

        if ($action == 'copy') {
            if (!copy($fileSource, $fileDestination)) {
                $this->_setError(
                    'COPY_FAILED',
                    gT("Your file could not be copied")
                );
                $this->_printJsonError();
                return;
            }

            $this->_printJsonResponse([
                'success' => true,
                'message' => sprintf(gT("File successfully copied"), $file['shortName']),
            ]);
            return;

        } else if ($action == 'move') {
            if (!@rename($fileSource, $fileDestination)) {
                $this->_setError(
                    'MOVE_FAILED',
                    gT("Your file could not be moved")
                );
                $this->_printJsonError();
                return;
            }

            $this->_printJsonResponse([
                'success' => true,
                'message' => sprintf(gT("File successfully moved"), $file['shortName']),
            ]);
            return;

        }

        $this->_setError(
            'ACTION_UNKNOWN',
            gT("The action you tried to apply is not known")
        );
        $this->_printJsonError();
        return;
    }

    /**
     * Action to upload a file returns a json document
     * @TODO Currently a naive extension filter is in place this needs to be secured against executables.
     *
     * @return void
     */
    public function uploadFile()
    {
        $folder = Yii::app()->request->getPost('folder');
        $iSurveyId = Yii::app()->request->getPost('surveyid', null);

        if (($iSurveyId == 'null' || $iSurveyId == null) && !preg_match("/generalfiles/", $folder)) {
            $iSurveyId = null;
            $folder = 'upload' . DIRECTORY_SEPARATOR . 'global';
        }

        $directory = $this->_checkFolder($folder, $iSurveyId);

        if ($directory === false) {
            $this->_printJsonError();
            return;
        }

        $debug[] = $_FILES;

        if ($_FILES['file']['error'] == 1 || $_FILES['file']['error'] == 2) {
            $this->_setError(
                'MAX_FILESIZE_REACHED',
                sprintf(gT("Sorry, this file is too large. Only files up to %01.2f MB are allowed."), getMaximumFileUploadSize() / 1024 / 1024)
            );
            $this->_printJsonError();
            return;
        }

        $path = $_FILES['file']['name'];
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        // Naive fileextension test => needs proper evaluation

        if ($this->_extensionAllowed($ext, 'upload') === false) {
            $this->_setError(
                'FILETYPE_NOT_ALLOWED',
                gT("Sorry, this file type is not allowed. Please contact your administrator for a list of allowed filetypes.")
            );
            $this->_printJsonError();
            return;
        }

        $destdir = dirname(Yii::app()->basePath) . DIRECTORY_SEPARATOR . $folder;

        $filename = sanitize_filename($_FILES['file']['name'], false, false, false); // Don't force lowercase or alphanumeric
        $fullfilepath = $destdir . DIRECTORY_SEPARATOR . $filename;
        $fullfilepath = preg_replace("%".DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR."%", DIRECTORY_SEPARATOR, $fullfilepath);

        if ($this->checkTargetExists($fullfilepath) && Yii::app()->getConfig('overwritefiles') == 0) {
            $ext = pathinfo($fullfilepath, PATHINFO_EXTENSION);
            $shorthash = hash('adler32', microtime());
            $fullfilepath = preg_replace("/\." . $ext . "/", "-" . $shorthash . ".", $fullfilepath);
        }

        //$fullfilepath = realpath($fullfilepath);

        $debug[] = $destdir;
        $debug[] = $filename;
        $debug[] = $fullfilepath;

        if (!is_writable($destdir)) {
            $this->_setError(
                'FILE_DESTINATION_UNWRITABLE',
                sprintf(gT("An error occurred uploading your file. The folder (%s) is not writable for the webserver."), $folder)
            );
            $this->_printJsonError();
            return;
        }

        if (
            !move_uploaded_file(
                $_FILES['file']['tmp_name'], 
                $fullfilepath 
            )
        ) {
            $this->_setError(
                'FILE_COULD NOT_BE_MOVED',
                sprintf(gT("An error occurred uploading your file. This may be caused by incorrect permissions for the target folder. (%s)"), $folder)
            );
            $this->_printJsonError();
            return;
        }

        $linkToImage = Yii::app()->baseUrl . '/' . $folder . '/' . $filename;

        $this->_printJsonResponse(
            [
                'success' => true,
                'message' => sprintf(gT("File %s uploaded"), $filename),
                'src' => $linkToImage,
                'debug' => $debug,
            ]
        );

    }

    ############################ PRIVATE METHODS ############################

    /**
     * Naive test for file extension
     * @TODO enhance this for file uploads
     *
     * @param string $fileExtension
     * @return boolean
     */
    private function _extensionAllowed($fileExtension, $purpose = 'show')
    {
        if ($purpose == 'show' || 1 == 1) {
            return in_array($fileExtension, $this->allowedFileExtensions);
        }
    }

    private function checkTargetExists($fileDestination)
    {
        return is_file($fileDestination);
    }

    private function _checkFolder($sFolderPath, $iSurveyId = null)
    {

        $aAllowedFolders = $this->_collectCompleteFolderList($iSurveyId);
        $inInAllowedFolders = false;

        foreach ($aAllowedFolders as $folderName => $folderPath) {
            $inInAllowedFolders = (preg_match('%/?' . $folderPath . '/?%', $sFolderPath)) || $inInAllowedFolders;
        }

        if (!$inInAllowedFolders) {
            $this->_setError('NO_PERMISSION', gT("You don't have permission to this folder"), null, [
                "sFolderPath" => $sFolderPath,
                "aAllowedFolders" => $aAllowedFolders,
            ]);
            return false;
        }

        $realPath = dirname(Yii::app()->basePath) . DIRECTORY_SEPARATOR . $sFolderPath;
        if (!is_dir($realPath)) {
            mkdir($realPath);
        }

        return $sFolderPath;
    }

    /**
     * Creates a list of files in the selected folder
     *
     * @param int|null $iSurveyId
     * @return array list of files [filename => filepath]
     */
    private function _collectFileList($folderPath)
    {
        $directoryArray = array();

        $realPath = dirname(Yii::app()->basePath) . DIRECTORY_SEPARATOR . $folderPath;
        if (empty($realPath) || !is_dir($realPath)) {
            return $directoryArray;
        }

        $files = scandir($realPath);

        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {continue;}

            $fileRelativePath = $folderPath . DIRECTORY_SEPARATOR . $file;
            $fileRealpath = dirname(Yii::app()->basePath) . DIRECTORY_SEPARATOR . $fileRelativePath;
            $fileIsDirectoy = @is_dir($fileRealpath);
            $isImage =  !!exif_imagetype($fileRealpath);
            if ($fileIsDirectoy) {
                continue;
            } else {

                $fileExt = strtolower(pathinfo($fileRealpath, PATHINFO_EXTENSION));
                if (!$this->_extensionAllowed($fileExt)) {continue;}

                $iconClassArray = LsDefaultDataSets::fileTypeIcons();
                $size = filesize($fileRealpath);
                if (isset($iconClassArray[$fileExt])) {
                    $iconClass = $iconClassArray[$fileExt];
                } else {
                    $iconClass = $iconClassArray['blank'];
                }
            }

            $sSystemDateFormat = getDateFormatData(Yii::app()->session['dateformat']);
            $iFileTimeDate = filemtime($fileRealpath);

            $linkToImage = Yii::app()->getBaseUrl(true) . '/' . $folderPath . '/' . rawurlencode($file);
            $hash = hash_file('md5', $fileRealpath);

            $directoryArray[$file] = [
                'iconClass' => $iconClass,
                'isImage' => $isImage,
                'src' => $linkToImage,
                'hash' => $hash,
                'path' => $fileRelativePath,
                'size' => $size,
                'shortName' => $file,
                'mod_time' => date($sSystemDateFormat['phpdate'] . ' H:i', $iFileTimeDate),
            ];
        }
        return $directoryArray;

    }

    /**
     * Creates an array of possible folders
     *
     * @param int|null $iSurveyId
     * @return array List of visible folders
     */
    private function _collectFolderList($iSurveyId = null)
    {
        $folders = $this->globalDirectories;

        if ($iSurveyId != null) {
            $folders[] = 'upload' . DIRECTORY_SEPARATOR . 'surveys' . DIRECTORY_SEPARATOR . $iSurveyId;
        } else {
            $aSurveyIds = Yii::app()->db->createCommand()->select('sid')->from('{{surveys}}')->queryColumn();
            foreach ($aSurveyIds as $itrtSsurveyId) {
                if (
                    Permission::model()->hasGlobalPermission('superadmin', 'read')
                    || Permission::model()->hasGlobalPermission('surveys', 'update')
                    || Permission::model()->hasSurveyPermission($itrtSsurveyId, 'surveylocale', 'update')
                ) {
                    $folders[] = 'upload' . DIRECTORY_SEPARATOR . 'surveys' . DIRECTORY_SEPARATOR . $itrtSsurveyId;
                }

            }
        }

        return $folders;
    }

    /**
     * Creates an array of all possible folders including child folders for access permission checks.
     *
     * @param int|null $iSurveyId
     * @return array List of visible folders
     */
    private function _collectCompleteFolderList($iSurveyId = null)
    {
        $folders = $this->globalDirectories;

        if ($iSurveyId != null) {
            $folders[] = 'upload' . DIRECTORY_SEPARATOR . 'surveys' . DIRECTORY_SEPARATOR . $iSurveyId;
        } else {
            $aSurveyIds = Yii::app()->db->createCommand()->select('sid')->from('{{surveys}}')->queryColumn();
            foreach ($aSurveyIds as $itrtSsurveyId) {
                if (
                    Permission::model()->hasGlobalPermission('superadmin', 'read')
                    || Permission::model()->hasGlobalPermission('surveys', 'update')
                    || Permission::model()->hasSurveyPermission($itrtSsurveyId, 'surveylocale', 'update')
                ) {
                    $folders[] = 'upload' . DIRECTORY_SEPARATOR . 'surveys' . DIRECTORY_SEPARATOR . $itrtSsurveyId;
                }

            }
        }
        $filelist = [];
        foreach ($folders as $folder) {
            $this->__recursiveScandir($folder, $folders, $filelist);
        }

        return $folders;
    }

    /**
     * Recurses down the folder provided and adds a complete list of folders and files to the parametered arrays
     * !!! Array provided are changed !!!
     *
     * @param string $folder
     * @param array !by reference! $folderlist
     * @param array !by reference! $filelist
     * @return void
     */
    private function __recursiveScandir($folder, &$folderlist, &$filelist)
    {
        $realPath = dirname(Yii::app()->basePath) . DIRECTORY_SEPARATOR . $folder;
        if (!file_exists($realPath)) {
            return $folderlist;
        }

        $scandirCurrent = scandir($realPath);
        foreach ($scandirCurrent as $fileDescriptor) {
            if ($fileDescriptor == '.' || $fileDescriptor == '..') {continue;}

            $childRelativePath = $folder . DIRECTORY_SEPARATOR . $fileDescriptor;
            $childRealPath = realpath(Yii::getPathOfAlias('basePath') . $childRelativePath);
            $childIsDirectoy = is_dir($childRealPath);

            if ($childIsDirectoy) {
                $folderlist[] = $childRelativePath;
                $this->__recursiveScandir($childRelativePath, $folderlist, $filelist);
            } else {
                $filelist[] = $childRelativePath;
            }
        }
    }

    /**
     * Creates an associative array of the possible folders for the treeview
     *
     * @param int|null $iSurveyId
     * @return array List of visible folders
     */
    private function _collectRecursiveFolderList($iSurveyId = null)
    {
        $folders = $this->_collectFolderList($iSurveyId);
        $folderList = [];
        foreach ($folders as $folder) {
            $folderList[] = $this->_composeFolderArray($folder);
        }
        return $folderList;
    }

    /**
     * Get the correct tree array representation including child folders for provided folder
     *
     * @param string $folder
     * @return array
     */
    private function _composeFolderArray($folder)
    {

        $realPath = dirname(Yii::app()->basePath) . DIRECTORY_SEPARATOR . $folder;
        if (!file_exists($realPath)) {
            @mkdir($realPath, null, true);
        }
        $allFiles = scandir($realPath);

        $childFolders = [];
        foreach ($allFiles as $childFile) {

            if ($childFile == '.' || $childFile == '..') {continue;}

            $childRelativePath = $folder . DIRECTORY_SEPARATOR . $childFile;
            $childRealPath = realpath(Yii::getPathOfAlias('basePath') . $childRelativePath);
            $childIsDirectoy = is_dir($childRealPath);

            if (!$childIsDirectoy) {continue;}

            $childFolders[] = $this->_composeFolderArray($childRelativePath);

        }

        $pathArray = explode("/", $folder);
        $shortName = end($pathArray);

        $folderArray = [
            'folder' => $folder,
            'realPath' => $realPath,
            'shortName' => $shortName,
            'children' => $childFolders,
        ];
        return $folderArray;
    }

    /**
     * Sets the internal error object
     *
     * @param string $code
     * @param string $message
     * @param string|null $title
     * @return void
     */
    private function _setError($code, $message, $title = '', $debug = null)
    {
        $this->oError = new FileManagerError();
        $this->oError->code = $code;
        $this->oError->message = $message;
        $this->oError->title = $title;
        $this->oError->debug = $debug;
    }

    /**
     * Prints a json document with the data provided as parameter
     *
     * @param array $data The data that should be transferred
     * @return void Renders JSON document
     */
    private function _printJsonResponse($data)
    {
        $this->getController()->renderPartial(
            '/admin/super/_renderJson', [
                'success' => true,
                'data' => $data,
        ]);
    }

    /**
     * Prints a json document with the intercontroller error message
     *
     * @return void Renders JSON document
     */
    private function _printJsonError()
    {
        http_response_code(500);
        $this->getController()->renderPartial(
            '/admin/super/_renderJson', [
                'success' => false,
                'data' => [
                    'success' => false,
                    'message' => $this->oError->message,
                    'debug' => $this->oError->debug,
                ],
        ]);
    }
}

class FileManagerError
{
    public $message;
    public $title;
    public $code;
}
