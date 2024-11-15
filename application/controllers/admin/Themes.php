<?php

/*
* LimeSurvey
* Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

use LimeSurvey\ExtensionInstaller\FileFetcherUploadZip;
use LimeSurvey\ExtensionInstaller\QuestionThemeInstaller;

/**
* templates
*
* @package LimeSurvey
* @author
* @copyright 2011
*/
class Themes extends SurveyCommonAction
{
    public function runWithParams($params)
    {
        $sTemplateName = trim(Yii::app()->request->getPost('templatename', ''));
        if (Permission::model()->hasGlobalPermission('templates', 'read') || Permission::model()->hasTemplatePermission($sTemplateName)) {
            parent::runWithParams($params);
        } else {
            Yii::app()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
            $this->getController()->redirect(array("themeOptions/index"));
        }
    }

    /**
     * Exports a template
     *
     * @access public
     * @param string $templatename
     * @return void
     */
    public function templatezip($templatename)
    {
        $oEditedTemplate = Template::getInstance($templatename, null, null, true);

        if (Permission::model()->hasGlobalPermission('templates', 'export')) {
            $templatedir = $oEditedTemplate->path;
            $tempdir = Yii::app()->getConfig('tempdir');

            $zipfile = "$tempdir/$templatename.zip";
            $zip = new ZipArchive();
            $zip->open($zipfile, ZipArchive::CREATE);

            $zipHelper = new LimeSurvey\Helpers\ZipHelper($zip);
            $zipHelper->addFolder($templatedir);

            $zip->close();

            if (is_file($zipfile)) {
                // Send the file for download!
                header("Expires: 0");
                header("Cache-Control: must-revalidate");
                header("Content-Type: application/force-download");
                header("Content-Disposition: attachment; filename=$templatename.zip");
                header("Content-Description: File Transfer");

                @readfile($zipfile);

                // Delete the temporary file
                unlink($zipfile);
            }
        } else {
            Yii::app()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
            $this->getController()->redirect(array("themeOptions/index"));
        }
    }

    /**
    * Exports a deprecated template
    *
    * @access public
    * @param string $templatename
    * @return void
    */
    public function deprecatedtemplatezip($templatename)
    {
        //$oEditedTemplate = Template::model()->getTemplateConfiguration($templatename);
        $templatename        = sanitize_dirname($templatename);
        $usertemplaterootdir = Yii::app()->getConfig("uploaddir") . DIRECTORY_SEPARATOR . "templates";
        $templatePath        = $usertemplaterootdir . DIRECTORY_SEPARATOR . $templatename;
        $this->folderzip($templatename, $templatePath);
    }

    /**
    * Exports a broken theme
    *
    * @access public
    * @param string $templatename
    * @return void
    */
    public function brokentemplatezip($templatename)
    {
        //$oEditedTemplate = Template::model()->getTemplateConfiguration($templatename);
        $templatename        = sanitize_dirname($templatename);
        $templatePath        = Yii::app()->getConfig("userthemerootdir") . DIRECTORY_SEPARATOR . $templatename;
        $this->folderzip($templatename, $templatePath);
    }

    /**
    * Exports a theme folder
    * NOTE: This function must remain private !!! it doesn't sanitize the $templatePath
    * This should be done by the proxy function (eg: deprecatedtemplatezip(), brokentemplatezip() )
    *
    * @access public
    * @param string $templatename
    * @return void
    */
    private function folderzip($templatename, $templatePath)
    {

        if (!Permission::model()->hasGlobalPermission('templates', 'export')) {
            Yii::app()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
            $this->getController()->redirect(array("themeOptions/index"));
        }

        $tempdir = Yii::app()->getConfig('tempdir');

        $zipfile = "$tempdir/$templatename.zip";

        $zip = new ZipArchive();
        $zip->open($zipfile, ZipArchive::CREATE);

        $zipHelper = new LimeSurvey\Helpers\ZipHelper($zip);
        $zipHelper->addFolder($templatePath);

        $zip->close();

        if (is_file($zipfile)) {
            // Send the file for download!
            header("Expires: 0");
            header("Cache-Control: must-revalidate");
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=$templatename.zip");
            header("Content-Description: File Transfer");

            @readfile($zipfile);

            // Delete the temporary file
            unlink($zipfile);
        }
    }

    /**
     * Retrieves a temporary template file from disk
     *
     * @param mixed $id ID of the template file
     */
    public function tmp($id)
    {
        $iTime = $id = CHtml::encode($id);
        $sFile = Yii::app()->getConfig("tempdir") . DIRECTORY_SEPARATOR . "template_temp_{$iTime}.html";

        if (!is_file($sFile) || !file_exists($sFile)) {
            die("Found no file with id " . $id);
        }

        readfile($sFile);
    }

    /**
     * Responsible to import a template archive.
     *
     * @access public
     * @return void
     */
    public function upload()
    {
        $action = returnGlobal('action');
        if ($action == 'templateuploadimagefile' && App()->request->getPost('surveyid')) {
            App()->getController()->forward("/surveyAdministration/uploadimagefile/");
            App()->end();
        }
        $sTemplateName = trim(App()->request->getPost('templatename', ''));
        // This controller has several actions. Even actions that manage multiple subactions.
        // In case you are uploading a template, the templatename does not exist in the POST.
        // It's not going to fail, but it's checking for a permission with an empty templatename.
        // Surely it works as expected, but it would be nice if the code was clearer.
        if (Permission::model()->hasGlobalPermission('templates', 'import') || Permission::model()->hasTemplatePermission($sTemplateName)) {
            App()->loadHelper('admin/template');
            // NB: lid = label id
            $lid = returnGlobal('lid');
            if ($action == 'templateuploadimagefile') {
                return $this->uploadTemplateImageFile($sTemplateName);
            } elseif ($action == 'templateupload') {
                $aData = $this->uploadTemplate();
                $aViewUrls = 'importuploaded_view';
            } else {
                $aViewUrls = 'importform_view';
                $aData = array('lid' => $lid);
            }

            $this->renderWrappedTemplate('themes', $aViewUrls, $aData);
        } else {
            App()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
            $this->getController()->redirect(array("themeOptions/index"));
        }
    }

    /**
     * @param string $sTemplateName
     * @return boolean
     */
    protected function uploadTemplateImageFile(string $sTemplateName)
    {
        // $iTemplateConfigurationId = App()->request->getPost('templateconfig');
        // $oTemplateConfiguration = TemplateConfiguration::getInstanceFromConfigurationId($iTemplateConfigurationId);
        /** @var Template */
        $oTemplateConfiguration = Template::getInstance($sTemplateName);

        /** @var boolean */
        $success = false;
        /** @var string */
        $uploadresult = "";
        /** @var array<mixed> */
        $debug = [];

        $debug[] = $sTemplateName;
        $debug[] = $oTemplateConfiguration;

        // Redirect back if demo mode is set.
        $this->checkDemoMode();

        $debug[] = $_FILES;

        // Return json at file size error.
        $uploadValidator = new LimeSurvey\Models\Services\UploadValidator();
        $uploadValidator->renderJsonOnError('file', $debug);

        $checkImageContent = LSYii_ImageValidator::validateImage($_FILES["file"]);
        if ($checkImageContent['check'] === false) {
            $message = $checkImageContent['check'] === false ? $checkImageContent['uploadresult'] : null;
            $debug = $checkImageContent['check'] === false ? $checkImageContent['debug'] : null;
            return Yii::app()->getController()->renderPartial(
                '/admin/super/_renderJson',
                array('data' => ['success' => $success, 'message' => $message, 'debug' => $debug]),
                false,
                false
            );
        }

        $destdir = $oTemplateConfiguration->filesPath;
        Yii::import('application.helpers.SurveyThemeHelper');
        if (SurveyThemeHelper::isStandardTemplate($oTemplateConfiguration->sTemplateName)) {
            $destdir = $oTemplateConfiguration->generalFilesPath;
        }

        // Don't force lowercase or alphanumeric
        $filename = sanitize_filename($_FILES['file']['name'], false, false, false);
        $fullfilepath = $destdir . $filename;
        $debug[] = $destdir;
        $debug[] = $filename;
        $debug[] = $fullfilepath;
        if (!@move_uploaded_file($_FILES['file']['tmp_name'], $fullfilepath)) {
            $uploadresult = gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder.");
        } else {
            $uploadresult = sprintf(gT("File %s uploaded"), $filename);
            App()->user->setFlash('success', "Data saved!");
            $success = true;
        };

        return App()->getController()->renderPartial(
            '/admin/super/_renderJson',
            array('data' => ['success' => $success, 'message' => $uploadresult, 'debug' => $debug]),
            false,
            false
        );
    }

    /**
     * Upload template/theme/question theme.
     *
     * @return array $aData
     */
    protected function uploadTemplate()
    {
        App()->loadLibrary('admin.pclzip');

        // Redirect back if demo mode is set.
        $this->checkDemoMode();

        // Redirect back at file size error.
        $this->checkFileSizeError();

        // NB: lid = label id
        $lid = returnGlobal('lid');

        // TODO: Don't branch on $_POST, but on config.xml <type> tag.
        /** @var string */
        $themeType = returnGlobal('theme');
        if ($themeType === 'question') {
            // Make questiontheme upload folder if it doesn't exist
            if (!is_dir($questionthemerootdir = App()->getConfig('userquestionthemerootdir'))) {
                mkdir($questionthemerootdir, 0777, true);
            }

            try {
                $src = $_FILES['the_file']['tmp_name'];
                $extConfig = ExtensionConfig::loadFromZip($src);
                $destdir = $extConfig->getName();
                // TODO: Replace with extension installer factory.
                $installer = $this->getQuestionThemeInstaller();
                $installer->fetchFiles();
                /** @var ExtensionConfig */
                $config = $installer->getConfig();
                if (!$config->isCompatible()) {
                    $installer->abort();
                    throw new Exception(gT('The question theme is not compatible with your version of LimeSurvey.'));
                }
                $questionTheme = QuestionTheme::model()->findByAttributes(['name' => $config->getName()]);
                try {
                    if (empty($questionTheme)) {
                        $installer->install();
                    } else {
                        $installer->update();
                    }
                    // TODO: If you want to do nice file upload summary, you need to define a
                    // FileFetcherResult and return it from install().
                    return [
                        'aImportedFilesInfo' => [],
                        'aErrorFilesInfo' => [],
                        'aImportErrors' => [],
                        'lid' => null,
                        'newdir' => 'newdir',
                        'theme' => 'question',
                        'result' => 'success'
                    ];
                } catch (Throwable $ex) {
                    $installer->abort();
                    return [
                        'aImportedFilesInfo' => [],
                        'aErrorFilesInfo' => [],
                        'aImportErrors' => [],
                        'lid' => null,
                        'newdir' => 'newdir',
                        'theme' => 'question',
                        'result' => 'error'
                    ];
                }
            } catch (Throwable $t) {
                Yii::app()->setFlashMessage($t->getMessage(), 'error');
                $this->getController()->redirect(["/themeOptions#questionthemes"]);
            }
        }

        $sNewDirectoryName = $this->getNewDirectoryName($themeType, $_FILES['the_file']['tmp_name']);

        if ($themeType == 'survey') {
            $destdir = App()->getConfig('userthemerootdir') . DIRECTORY_SEPARATOR . $sNewDirectoryName;
        } else {
            App()->setFlashMessage(
                sprintf(
                    gT("This theme type (%s) is not allowed."),
                    json_encode(htmlspecialchars($themeType))
                ),
                'error'
            );
            $this->getController()->redirect(array("themeOptions/index"));
        }

        // Redirect back if $destdir is not writable OR if it already exists.
        $this->checkDestDir($destdir, $sNewDirectoryName, $themeType);

        // All OK if we're here.
        // TODO: Always check if successful.
        $extractDir = $destdir;
        mkdir($destdir);

        $aImportedFilesInfo = [];
        $aErrorFilesInfo = [];

        // TODO: Move all this to new SurveyThemeInstaller class (same as done for QuestionThemeInstaller).
        if (is_file($_FILES['the_file']['tmp_name'])) {
            $zipExtractor = new \LimeSurvey\Models\Services\ZipExtractor($_FILES['the_file']['tmp_name']);
            $zipExtractor->setFilterCallback('templateExtractFilter');

            if (!$zipExtractor->extractTo($extractDir)) {
                App()->user->setFlash('error', gT("This file is not a valid ZIP file archive. Import failed."));
                rmdirr($destdir);
                $this->getController()->redirect(array("admin/themes/sa/upload"));
            } else {
                // Successfully unpacked
                $aExtractResult = $zipExtractor->getExtractResult();
                foreach ($aExtractResult as $sFile) {
                    if ($sFile['status'] == 'skipped' && !$sFile['is_folder']) {
                        $aErrorFilesInfo[] = array(
                            "filename" => $sFile['name'],
                        );
                    } else {
                        $aImportedFilesInfo[] = [
                            "filename" => $sFile['name'],
                            "status" => gT("OK"),
                            'is_folder' => $sFile['is_folder']
                        ];
                    }
                    if ($sFile['name'] == "config.xml") {
                        SurveyThemeHelper::checkConfigFiles($sFile['target_filename']);
                    }
                }
                if (Template::checkIfTemplateExists($sNewDirectoryName)) {
                    App()->user->setFlash('error', gT("Can not import a theme that already exists!"));
                    rmdirr($destdir);
                    $this->getController()->redirect(array("admin/themes/sa/upload"));
                }
                if (count($aImportedFilesInfo) == 0) {
                    App()->user->setFlash(
                        'error',
                        gT("This ZIP archive contains no valid template files. Import failed.")
                    );
                    // TODO: Always check if successful.
                    rmdirr($destdir);
                    $this->getController()->redirect(array("admin/themes/sa/upload"));
                }
                // TODO: make proper import manifest for questiontheme in configuration class
                if ($themeType !== 'question') {
                    TemplateManifest::importManifest($sNewDirectoryName, ['extends' => $destdir]);
                }
            }
        } else {
            App()->setFlashMessage(
                gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder."),
                'error'
            );
            // TODO: Always check if successful.
            rmdirr($destdir);
            $this->getController()->redirect(array("admin/themes/sa/upload"));
        }

        $aImportErrors = [];
        if (count($aErrorFilesInfo) == 0 && empty($aImportErrors) && count($aImportedFilesInfo) > 0) {
            $result = 'success';
        } elseif ((count($aErrorFilesInfo) > 0 || !empty($aImportErrors)) && count($aImportedFilesInfo) > 0) {
            $result = 'partial';
        } else {
            $result = 'error';
        }

        return array(
            'aImportedFilesInfo' => $aImportedFilesInfo,
            'aErrorFilesInfo' => $aErrorFilesInfo,
            'aImportErrors' => $aImportErrors,
            'lid' => $lid,
            'newdir' => $sNewDirectoryName,
            'theme' => $themeType,
            'result' => $result
        );
    }

    /**
     * Responsible to import a template file.
     *
     * @access public
     * @return void
     */
    public function uploadfile()
    {
        $editfile               = App()->request->getPost('editfile');
        $templatename           = returnGlobal('templatename');
        $screenname             = returnGlobal('screenname');
        if (empty($screenname)) {
            $screenname = 'welcome';
        }

        $redirectUrl = array('admin/themes', 'sa' => 'view', 'editfile' => $editfile, 'screenname' => $screenname, 'templatename' => $templatename);

        if (Permission::model()->hasGlobalPermission('templates', 'import')) {
            // Check file size and redirect on error
            $uploadValidator = new LimeSurvey\Models\Services\UploadValidator();
            $uploadValidator->redirectOnError('upload_file', $redirectUrl);

            $action                 = returnGlobal('action');
            $oEditedTemplate        = Template::getInstance($templatename);
            $allowedthemeuploads    = Yii::app()->getConfig('allowedthemeuploads') . ',' . Yii::app()->getConfig('allowedthemeimageformats');
            $filename               = sanitize_filename($_FILES['upload_file']['name'], false, false, false); // Don't force lowercase or alphanumeric
            $dirfilepath            = $oEditedTemplate->filesPath;

            if (!file_exists($dirfilepath)) {
                if (is_writable($oEditedTemplate->path)) {
                    mkdir($dirfilepath, 0777, true);
                } else {
                    $uploadresult = sprintf(gT("The folder %s doesn't exist and can't be created."), $dirfilepath);
                    Yii::app()->setFlashMessage($uploadresult, 'error');
                    $this->getController()->redirect(array('admin/themes', 'sa' => 'view', 'editfile' => $editfile, 'screenname' => $screenname, 'templatename' => $templatename));
                }
            }

            $fullfilepath = $dirfilepath . $filename;
            $status       = 'error';

            if ($action == "templateuploadfile") {
                if (Yii::app()->getConfig('demoMode')) {
                    $uploadresult = gT("Demo mode: Uploading template files is disabled.");
                } elseif ($filename != $_FILES['upload_file']['name']) {
                    $uploadresult = gT("This filename is not allowed to be uploaded.");
                } elseif (!in_array(strtolower(substr(strrchr((string) $filename, '.'), 1)), explode(",", $allowedthemeuploads))) {
                    $uploadresult = gT("This file type is not allowed to be uploaded.");
                } else {
                    //Uploads the file into the appropriate directory
                    if (!@move_uploaded_file($_FILES['upload_file']['tmp_name'], $fullfilepath)) {
                        $uploadresult = gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder.");
                    } else {
                        $uploadresult = sprintf(gT("File %s uploaded"), $filename);
                        Template::model()->findByPk($templatename)->resetAssetVersion(); // Upload a files, asset need to be reset (maybe)
                        $status = 'success';
                    }
                }
                Yii::app()->setFlashMessage($uploadresult, $status);
            }
        } else {
            Yii::app()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
        }
        $this->getController()->redirect($redirectUrl);
    }


    /**
     * Strips file extension
     *
     * @access protected
     * @param string $name
     * @return string
     * @todo Used? Previous name: _strip_ext
     */
    protected function stripExt($name)
    {
        $ext = strrchr($name, '.');
        if ($ext !== false) {
            $name = substr($name, 0, -strlen($ext));
        }
        return $name;
    }

    /**
     * Load default view screen of template controller.
     *
     * @access public
     * @param string $editfile
     * @param string $screenname
     * @param string $templatename
     * @return void
     */
    public function index(string $editfile = '', string $screenname = 'welcome', string $templatename = '')
    {
        if ($templatename == '') {
            $templatename = App()->getConfig('defaulttheme');
        }

        // This can happen if the global default template is deleted
        // TODO: check if we can load template without needing the model, only from xml, so we can load the theme editor even when it is not installed
        if (!Template::checkIfTemplateExists($templatename)) {
            // Redirect to the default template
            Yii::app()->setFlashMessage(sprintf(gT('Theme %s does not exist.'), htmlspecialchars((string) $templatename, ENT_QUOTES)), 'error');
            $this->getController()->redirect(array('admin/themes/sa/view/', 'templatename' => getGlobalSetting('defaulttheme')));
        }

        /* Keep Bootstrap Package clean after loading template : because template can update boostrap */

        $aViewUrls = $this->initialise($templatename, $screenname, $editfile, true, true);

        App()->getClientScript()->reset();

        $undo    = gT("Undo (Ctrl-Z)", "js");
        $redo    = gT("Redo (Ctrl-Y)", "js");
        $find    = gT("Find (Ctrl-F)", "js");
        $replace = gT("Replace (Ctrl-H)", "js");
        App()->getClientScript()->registerScript(
            "SurveyThemeEditorLanguageData",
            <<<JAVASCRIPT
surveyThemeEditorLanguageData = {
    undo: "$undo",
    redo: "$redo",
    find: "$find",
    replace: "$replace"
};
JAVASCRIPT
            ,
            CClientScript::POS_BEGIN
        );
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'templates.js', CClientScript::POS_END);
        App()->getClientScript()->registerPackage('ace');
        App()->getClientScript()->registerPackage('jsuri');
        AdminTheme::getInstance()->registerStylesAndScripts();

        // page title
        $pageTitle = gT('Theme editor:') . ' ' . $templatename;

        $aData['topbar']['title'] = $pageTitle;
        $aData['topbar']['backLink'] = App()->createUrl('themeOptions/index');


        $aData['topbar']['middleButtons'] = Yii::app()->getController()->renderPartial(
            '/admin/themes/partial/topbarBtns/leftSideButtons',
            [
                'isExport' => (Permission::model()->hasGlobalPermission('templates', 'export') && class_exists('ZipArchive')),
                'templatename' => $templatename,
                'isExtend' => true,
            ],
            true
        );

        // White Bar
        $aData['templateEditorBar']['buttons']['returnbutton'] = true;

        $this->renderWrappedTemplate('themes', $aViewUrls, $aData);

        // This helps handle the load/save buttons)
        if ($screenname != 'welcome') {
            Yii::app()->session['step'] = 1;
        } else {
            unset(Yii::app()->session['step']);
        }
    }

    /**
     * Function responsible to delete a template file.
     *
     * @access public
     * @return void
     */
    public function templatefiledelete()
    {
        if (Permission::model()->hasGlobalPermission('templates', 'update')) {
            $sTemplateName   = Template::templateNameFilter(trim(App()->request->getPost('templatename', '')));
            $oEditedTemplate = Template::getInstance($sTemplateName);
            $templatedir     = $oEditedTemplate->viewPath;
            $sPostedFiletype = CHtml::decode(App()->request->getPost('filetype'));
            $sPostedFile     = CHtml::decode(App()->request->getPost('filename')); // Filename is encode, need to decode.

            if ($sPostedFiletype == 'screen') {
                $filesdir        = $oEditedTemplate->viewPath;
                $sFileToDelete   = str_replace($oEditedTemplate->filesPath, '', (string) $sPostedFile);
            } elseif ($sPostedFiletype == 'js' || $sPostedFiletype == 'css') {
                $filesdir        = $oEditedTemplate->path;
                $sFileToDelete   = str_replace($oEditedTemplate->filesPath, '', (string) $sPostedFile);
            } elseif ($sPostedFiletype == 'other') {
                $filesdir        = $oEditedTemplate->filesPath;
                $sFileToDelete   = str_replace($oEditedTemplate->filesPath, '', (string) $sPostedFile);
            } else {
                Yii::app()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
                $this->getController()->redirect(array('admin/themes', 'sa' => 'view', 'editfile' => App()->request->getPost('editfile'), 'screenname' => App()->request->getPost('screenname'), 'templatename' => $sTemplateName));
            }

            $the_full_file_path = realpath($filesdir . $sFileToDelete);
            if (substr($the_full_file_path, 0, strlen(realpath($filesdir))) != realpath($filesdir)) {
                /* User tries to delete a file outside of files dir */
                Yii::app()->user->setFlash('error', sprintf(gT("File %s cannot be deleted for security reasons."), CHtml::encode($sPostedFile)));
                $this->getController()->redirect(array('admin/themes', 'sa' => 'view', 'editfile' => App()->request->getPost('editfile'), 'screenname' => App()->request->getPost('screenname'), 'templatename' => $sTemplateName));
            }
            /* No try to hack, go to delete */
            if (@unlink($the_full_file_path)) {
                Yii::app()->user->setFlash('success', sprintf(gT("The file %s was deleted."), CHtml::encode($sPostedFile)));
                Template::model()->findByPk($sTemplateName)->resetAssetVersion(); // Delete a files, asset need to be reset (maybe)
            } else {
                Yii::app()->user->setFlash('error', sprintf(gT("File %s couldn't be deleted. Please check the permissions on the /upload/themes folder"), CHtml::encode($sPostedFile)));
            }
        } else {
            Yii::app()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
        }
        $this->getController()->redirect(array('admin/themes', 'sa' => 'view', 'editfile' => App()->request->getPost('editfile'), 'screenname' => App()->request->getPost('screenname'), 'templatename' => $sTemplateName));
    }

    /**
     * Function responsible to rename a template(folder).
     *
     * @access public
     * @return void
     */
    public function templaterename()
    {
        $sNewName = trim(sanitize_dirname(App()->getRequest()->getPost('newname')));
        $sOldName = sanitize_dirname(App()->getRequest()->getPost('copydir'));
        $sNewName = CHtml::encode($sNewName);
        Template::validateTemplateName($sNewName);
        if (Permission::model()->hasGlobalPermission('templates', 'update')) {
            if ($sNewName && $sOldName) {
                $sNewDirectoryPath = Yii::app()->getConfig('userthemerootdir') . DIRECTORY_SEPARATOR . $sNewName;
                $sOldDirectoryPath = Yii::app()->getConfig('userthemerootdir') . DIRECTORY_SEPARATOR . $sOldName;
                if (Template::isStandardTemplate($sNewName)) {
                    App()->user->setFlash('error', sprintf(gT("Template could not be renamed to '%s'."), $sNewName) . " " . gT("This name is reserved for standard template."));
                } elseif (file_exists($sNewDirectoryPath)) {
                    App()->user->setFlash('error', sprintf(gT("Template could not be renamed to '%s'."), $sNewName) . " " . gT("A template with that name already exists."));
                } elseif (rename($sOldDirectoryPath, $sNewDirectoryPath) == false) {
                    App()->user->setFlash('error', sprintf(gT("Template could not be renamed to '%s'."), $sNewName) . " " . gT("Maybe you don't have permission."));
                } else {
                    /* We renamle the directory */
                    $oTemplate = Template::model()->findByAttributes(array('name' => $sOldName));
                    if (is_a($oTemplate, 'Template')) {
                        $oTemplate->renameTo($sNewName);
                        if (App()->getConfig('defaulttheme') == $sOldName) {
                            SettingGlobal::setSetting('defaulttheme', $sNewName);
                        }
                        $this->getController()->redirect(array('admin/themes', 'sa' => 'view', 'editfile' => 'layout_global.twig', 'screenname' => 'welcome', 'templatename' => $sNewName));
                    } else {
                        App()->user->setFlash('error', sprintf(gT("Template '%s' could not be found."), $sOldName));
                    }
                    $this->getController()->redirect(array('themeOptions/index'));
                }
            }
        } else {
            App()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
        }
        $this->getController()->redirect(array('admin/themes', 'sa' => 'view', 'editfile' => 'layout_global.twig', 'screenname' => 'welcome', 'templatename' => $sOldName));
    }


    /**
     * Function responsible to copy a template.
     *
     * @access public
     * @return void
     */
    public function templatecopy()
    {
        $copydir = sanitize_dirname(Yii::app()->request->getPost("copydir"));

        if (Permission::model()->hasGlobalPermission('templates', 'create')) {
            $newname = trim(sanitize_dirname(Yii::app()->request->getPost("newname")));
            $newname = CHtml::encode($newname);
            Template::validateTemplateName($newname);

            Yii::import('application.helpers.SurveyThemeHelper');
            if (SurveyThemeHelper::isStandardTemplate($newname)) {
                Yii::app()->setFlashMessage(sprintf(gT("Directory with the name `%s` already exists - choose another name"), $newname), 'error');
                $this->getController()->redirect(array("themeOptions/index"));
            }

            if ($newname && $copydir) {
                // Copies all the files from one template directory to a new one
                Yii::app()->loadHelper('admin/template');
                $newdirname  = Yii::app()->getConfig('userthemerootdir') . "/" . $newname;
                $copydirname = getTemplatePath($copydir);
                $oFileHelper = new CFileHelper();
                $mkdirresult = mkdir_p($newdirname);

                if ($mkdirresult == 1) {
                    // We just copy the while directory structure, but only the xml file
                    $oFileHelper->copyDirectory($copydirname, $newdirname, array('fileTypes' => array('xml', 'png', 'jpg'), 'newDirMode' => 0755));
                    //TemplateConfiguration::removeAllNodes($newdirname);
                    TemplateManifest::extendsConfig($copydir, $newname);
                    TemplateManifest::importManifest($newname, ['extends' => $copydir]);
                    $this->getController()->redirect(array("admin/themes/sa/view", 'templatename' => $newname));
                } elseif ($mkdirresult == 2) {
                    Yii::app()->setFlashMessage(sprintf(gT("Directory with the name `%s` already exists - choose another name"), $newname), 'error');
                    $this->getController()->redirect(array("admin/themes/sa/view", 'templatename' => $copydir));
                } else {
                    Yii::app()->setFlashMessage(sprintf(gT("Unable to create directory `%s`."), $newname), 'error');
                    Yii::app()->setFlashMessage(gT("Please check the directory permissions."));
                    $this->getController()->redirect(array("admin/themes/sa/view"));
                }
            } else {
                $this->getController()->redirect(array("admin/themes/sa/view"));
            }
        } else {
            Yii::app()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
        }
        $this->getController()->redirect(array("admin/themes/sa/view", 'templatename' => $copydir));
    }

    /**
     * Function responsible to delete a template while inside the theme editor
     *
     * @access public
     * @return void
     * @throws CDbException
     * @throws CException
     */
    public function delete()
    {
        $templatename = trim(Yii::app()->request->getPost('templatename', ''));
        if (Permission::model()->hasGlobalPermission('templates', 'delete')) {
            Yii::app()->loadHelper("admin/template");

            Yii::import('application.helpers.SurveyThemeHelper');
            if (Template::checkIfTemplateExists($templatename) && !SurveyThemeHelper::isStandardTemplate($templatename)) {
                if (!Template::hasInheritance($templatename)) {
                    if (rmdirr(Yii::app()->getConfig('userthemerootdir') . "/" . $templatename)) {
                        Template::model()->findByPk($templatename)->deleteAssetVersion();
                        $surveys = Survey::model()->findAllByAttributes(array('template' => $templatename));

                        // The default template could be the same as the one we're trying to remove
                        $globalDefaultIsGettingDeleted = getGlobalSetting('defaulttheme') == $templatename;

                        if ($globalDefaultIsGettingDeleted) {
                            SettingGlobal::setSetting('defaulttheme', getGlobalSetting('defaulttheme'));
                        }

                        foreach ($surveys as $s) {
                            $s->template = getGlobalSetting('defaulttheme');
                            $s->save();
                        }

                        TemplateConfiguration::uninstall($templatename);
                        Permission::model()->deleteAllByAttributes(array('permission' => $templatename, 'entity' => 'template'));

                        Yii::app()->setFlashMessage(sprintf(gT("Template '%s' was successfully deleted."), $templatename));
                    } else {
                        Yii::app()->setFlashMessage(sprintf(gT("There was a problem deleting the template '%s'. Please check your directory/file permissions."), $templatename), 'error');
                    }
                } else {
                    Yii::app()->setFlashMessage(sprintf(gT("You can't delete template '%s' because one or more templates inherit from it."), $templatename), 'error');
                }
            } else {
                Yii::app()->setFlashMessage(sprintf(gT("Theme '%s' does not exist."), $templatename), 'error');
            }
        } else {
            Yii::app()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
        }


        // Redirect with default templatename, editfile and screenname
        $this->getController()->redirect(array("themeOptions/index"));
    }

    public function deleteBrokenTheme()
    {
        $templatename = trim(Yii::app()->request->getPost('templatename', ''));

        if (Permission::model()->hasGlobalPermission('templates', 'delete')) {
            // First we check that the theme is really broken
            $aBrokenThemes = [];
            $aTemplatesWithNoDB = TemplateConfig::getTemplatesWithNoDb();
            if (!empty($aTemplatesWithNoDB['invalid'])) {
                $aBrokenThemes = $aTemplatesWithNoDB['invalid'];
            }
            $templatename  = sanitize_dirname($templatename);
            if (array_key_exists($templatename, $aBrokenThemes)) {
                if (rmdirr(Yii::app()->getConfig('userthemerootdir') . "/" . $templatename)) {
                    Yii::app()->setFlashMessage(sprintf(gT("Theme '%s' was successfully deleted."), $templatename));
                }
            } else {
                Yii::app()->setFlashMessage(gT("Not a broken theme!"), 'error');
            }
        }

        $this->getController()->redirect(array("themeOptions/index"));
    }


    /**
     * Deletes a survey theme from the "Available survey themes", after it has been uninstalled TemplateConfig::uninstall.
     * This will delete all local files.
     * @return void
     * @throws CException
     * @throws CHttpException
     */
    public function deleteAvailableTheme()
    {
        $templatename = trim(App()->request->getPost('templatename', ''));
        $templatename = CHtml::decode($templatename);

        if (Permission::model()->hasGlobalPermission('templates', 'delete')) {
            $completeFileName = realpath(App()->getConfig('userthemerootdir') . "/" . $templatename);
            /* If retuirn false, not a dir or not inside userthemerootdir: try to hack : throw a 403 for security */
            if (!is_dir($completeFileName) || strpos($completeFileName, App()->getConfig('userthemerootdir')) !== 0) {
                throw new CHttpException(403, "Disable for security reasons.");
            }
            // CheckIfTemplateExists check if the template is installed....
                Yii::import('application.helpers.SurveyThemeHelper');
            if (! Template::checkIfTemplateExists($templatename) && !SurveyThemeHelper::isStandardTemplate($templatename)) {
                if (rmdirr(App()->getConfig('userthemerootdir') . "/" . $templatename)) {
                    App()->setFlashMessage(sprintf(gT("Theme '%s' was successfully deleted."), $templatename));
                } else {
                    App()->setFlashMessage(sprintf(gT("There was a problem deleting the template '%s'. Please check your directory/file permissions."), $templatename), 'error');
                }
            } else {
                // This should never happen... trying to submit the form via a script? so no translation
                App()->setFlashMessage("You're trying to delete a theme that is installed. Please, uninstall it first", 'error');
            }
        }

        $this->getController()->redirect(array("themeOptions/index"));
    }

    /**
     * Function responsible to save the changes made in CodemMirror editor.
     *
     * @access public
     * @return void
     */
    public function templatesavechanges()
    {
        if (Permission::model()->hasGlobalPermission('templates', 'update')) {
            $changedtext = null;

            if (returnGlobal('changes')) {
                $changedtext = returnGlobal('changes');
                $changedtext = str_replace('<?', '', (string) $changedtext);
            }

            if (returnGlobal('changes_cp')) {
                $changedtext = returnGlobal('changes_cp');
                $changedtext = str_replace('<?', '', (string) $changedtext);
            }

            $action               = returnGlobal('action');
            $editfile             = returnGlobal('editfile');
            $relativePathEditfile = returnGlobal('relativePathEditfile');
            $sTemplateName        = Template::templateNameFilter(trim(App()->request->getPost('templatename', '')));
            $screenname           = returnGlobal('screenname');
            $oEditedTemplate      = Template::model()->getTemplateConfiguration($sTemplateName, null, null, true)->prepareTemplateRendering($sTemplateName);

            $aScreenFiles         = $oEditedTemplate->getValidScreenFiles("view");
            $cssfiles             = $oEditedTemplate->getValidScreenFiles("css");
            $jsfiles              = $oEditedTemplate->getValidScreenFiles("js");

            if ($action == "templatesavechanges" && $changedtext) {
                Yii::app()->loadHelper('admin/template');
                $changedtext = str_replace("\r\n", "\n", $changedtext);


                if ($relativePathEditfile) {
                    // Check if someone tries to submit a file other than one of the allowed filenames
                    if (
                        in_array($relativePathEditfile, $aScreenFiles) === false &&
                        in_array($relativePathEditfile, $cssfiles) === false &&
                        in_array($relativePathEditfile, $jsfiles) === false
                    ) {
                        Yii::app()->user->setFlash('error', gT('Invalid theme name'));
                         $this->getController()->redirect(array("admin/themes/sa/upload"));
                    }

                    //$savefilename = $oEditedTemplate
                    if (!file_exists($oEditedTemplate->path . $relativePathEditfile) && !file_exists($oEditedTemplate->viewPath . $relativePathEditfile)) {
                        $oEditedTemplate->extendsFile($relativePathEditfile);
                    }

                    $savefilename = $oEditedTemplate->extendsFile($relativePathEditfile, $relativePathEditfile);

                    if (is_writable($savefilename)) {
                        if (!$handle = fopen($savefilename, 'w')) {
                            Yii::app()->user->setFlash('error', gT('Could not open file ') . $savefilename);
                            $this->getController()->redirect(array("admin/themes/sa/upload"));
                        }

                        if (!fwrite($handle, $changedtext)) {
                            Yii::app()->user->setFlash('error', gT('Could not write file ') . $savefilename);
                            $this->getController()->redirect(array("admin/themes/sa/upload"));
                        } else {
                            Yii::app()->setFlashMessage(gT("Changes saved successfully."));
                        }

                        $oEditedTemplate->actualizeLastUpdate();

                        // If the file is an asset file, we refresh asset number
                        if (in_array($relativePathEditfile, $cssfiles) || in_array($relativePathEditfile, $jsfiles)) {
                            //SettingGlobal::increaseCustomAssetsversionnumber();
                            Template::model()->findByPk($sTemplateName)->resetAssetVersion();
                        }

                        fclose($handle);
                    } else {
                        Yii::app()->user->setFlash('error', "The file $savefilename is not writable");
                        $this->getController()->redirect(array("admin/themes/sa/upload"));
                    }
                }
            }
        } else {
            Yii::app()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
        }

        $this->getController()->redirect(array('admin/themes/', 'sa' => 'view', 'editfile' => $relativePathEditfile, 'screenname' => $screenname, 'templatename' => $sTemplateName), true);
    }

    /**
     * Load menu bar related to a template.
     *
     * @access protected
     * @param string $screenname
     * @param string $editfile
     * @param string $screens
     * @param string $tempdir
     * @param string $templatename
     * @return void
     * @deprecated ? 151005
     */
    protected function templatebar($screenname, $editfile, $screens, $tempdir, $templatename)
    {
        $aData = array();
        $aData['screenname'] = $screenname;
        $aData['editfile'] = $editfile;
        $aData['screens'] = $screens;
        $aData['tempdir'] = $tempdir;
        $aData['templatename'] = $templatename;
        $aData['userthemerootdir'] = Yii::app()->getConfig('userthemerootdir');

        $this->getController()->renderPartial("/admin/themes/templatebar_view", $aData);
    }

    /**
     * Load CodeMirror editor and various files information.
     *
     * @access protected
     * @param string $templatename
     * @param string $screenname
     * @param string $editfile
     * @param string $templates
     * @param string $files
     * @param string $cssfiles
     * @param array $otherfiles
     * @param array $myoutput
     * @return array
     */
    protected function templatesummary($templatename, $screenname, $editfile, $relativePathEditfile, $templates, $files, $cssfiles, $jsfiles, $otherfiles, $myoutput)
    {
        $tempdir = Yii::app()->getConfig("tempdir");
        $tempurl = Yii::app()->getConfig("tempurl");
        Yii::app()->loadHelper("admin/template");
        $aData = array();
        $time = date("ymdHis");
        // Prepare textarea class for optional javascript
        $templateclasseditormode = getGlobalSetting('defaultthemeteeditormode'); // default
        if (Yii::app()->session['templateeditormode'] == 'none') {
            $templateclasseditormode = 'none';
        }

        $aData['templateclasseditormode'] = $templateclasseditormode;

        // The following lines are forcing the browser to refresh the templates on each save
        @$fnew = fopen("$tempdir/template_temp_$time.html", "w+");
        $aData['time'] = $time;
        /* Load this template config, else 'survey-template' package can be outdated */
        $oEditedTemplate = Template::model()->getTemplateConfiguration($templatename, null, null, true)->prepareTemplateRendering($templatename);

        if (!$fnew) {
            $aData['filenotwritten'] = true;
        } else {
            //App()->getClientScript()->reset();
            @fwrite($fnew, (string) getHeader());

            App()->getClientScript()->registerScript("activateActionLink", "activateActionLink();", LSYii_ClientScript::POS_POSTSCRIPT); /* show the button if needed */

            /* Must remove all exitsing scripts / css and js */
            App()->getClientScript()->unregisterPackage('admin-theme'); // We remove the admin package

            App()->getClientScript()->render($myoutput);

            @fwrite($fnew, $myoutput);
            @fclose($fnew);
        }
        if (Yii::app()->session['templateeditormode'] !== 'default') {
            $sTemplateEditorMode = Yii::app()->session['templateeditormode'];
        } else {
            $sTemplateEditorMode = getGlobalSetting('templateeditormode');
        }
        $sExtension = substr(strrchr($editfile, '.'), 1);

        // Select ACE editor mode
        switch ($sExtension) {
            case 'css':
                $sEditorFileType = 'css';
                break;
            case 'pstpl':
                $sEditorFileType = 'html';
                break;
            case 'js':
                $sEditorFileType = 'javascript';
                break;
            case 'twig':
                $sEditorFileType = 'twig';
                break;
            default:
                $sEditorFileType = 'html';
                break;
        }

        $sFileDisplayName = ltrim(str_replace(Yii::app()->getConfig('rootdir'), '', $editfile), DIRECTORY_SEPARATOR);

        $editableCssFiles = $oEditedTemplate->getValidScreenFiles("css");
        $filesdir = $oEditedTemplate->filesPath;
        $aData['oEditedTemplate'] = $oEditedTemplate;
        $aData['screenname'] = $screenname;
        $aData['editfile'] = $editfile;
        $aData['filedisplayname'] = $sFileDisplayName;
        $aData['relativePathEditfile'] = $relativePathEditfile;
        $aData['tempdir'] = $tempdir;
        $aData['templatename'] = $templatename;
        $aData['templates'] = $templates;
        $aData['files'] = $files;
        $aData['cssfiles'] = $editableCssFiles;
        $aData['jsfiles'] = $jsfiles;
        $aData['otherfiles'] = $otherfiles;
        $aData['filespath'] = $filesdir;
        $aData['tempurl'] = $tempurl;
        $aData['time'] = $time;
        $aData['sEditorFileType'] = $sEditorFileType;
        $aData['sTemplateEditorMode'] = $sTemplateEditorMode;

        $aViewUrls['templatesummary_view'][] = $aData;

        return $aViewUrls;
    }

    /**
     * Function that initialises all data and call other functions to load default view.
     *
     * @access protected
     * @param string $templatename
     * @param string $screenname
     * @param string $editfile
     * @param bool $showsummary
     * @return void
     */
    protected function initialise($templatename, $screenname, $editfile, $showsummary = true)
    {
        // LimeSurvey style
        $oEditedTemplate = Template::getInstance($templatename, null, null, true, true)->prepareTemplateRendering($templatename, null, true);

        //App()->getClientScript()->reset();
        Yii::app()->loadHelper('surveytranslator');
        Yii::app()->loadHelper('admin/template');

        $files        = $oEditedTemplate->getValidScreenFiles("view", $screenname);
        $sLayoutFile  = $oEditedTemplate->getLayoutForScreen($screenname);
        $sContentFile = $oEditedTemplate->getContentForScreen($screenname);
        $cssfiles     = $oEditedTemplate->getValidScreenFiles("css");
        $jsfiles      = $oEditedTemplate->getValidScreenFiles("js");
        $editfile     = (empty($editfile) || ! ( in_array($editfile, $files) || in_array($editfile, $cssfiles) || in_array($editfile, $jsfiles)  )) ? $sLayoutFile : $editfile;

        // Standard screens
        $screens = $oEditedTemplate->getScreensList();

        Yii::app()->session['s_lang'] = Yii::app()->session['adminlang'];

        $templatename = sanitize_dirname($templatename);

        // Checks if screen name is in the list of allowed screen names
        if (!isset($screens[$screenname])) {
            Yii::app()->user->setFlash('error', gT('Invalid screen name'));
            $this->getController()->redirect(array("admin/themes/sa/upload"));
        }

        /* See if we found the file to be edited inside template */
        /* @todo must control if is updatable : in updatable file OR is a view */
        /* Actually allow to update any file example css/template-core.css */
        // @TODO: Proper language code conversion
        $sLanguageCode = 'en';
        $availableeditorlanguages = array('bg', 'cs', 'de', 'dk', 'en', 'eo', 'es', 'fi', 'fr', 'hr', 'it', 'ja', 'mk', 'nl', 'pl', 'pt', 'ru', 'sk', 'zh');
        if (in_array(Yii::app()->session['adminlang'], $availableeditorlanguages)) {
            $sLanguageCode = Yii::app()->session['adminlang'];
        }
        $aAllTemplates = Template::getTemplateList();
        if (!isset($aAllTemplates[$templatename])) {
            $templatename = getGlobalSetting('defaulttheme');
        }

        $normalfiles = array("DUMMYENTRY", ".", "..", "preview.png");
        $normalfiles = $normalfiles + $files + $cssfiles;
        // Some global data
        $aData['sitename'] = Yii::app()->getConfig('sitename');
        $siteadminname  = Yii::app()->getConfig('siteadminname');
        $siteadminemail = Yii::app()->getConfig('siteadminemail');

        // NB: Used by answer print PDF layout.
        $print = [];

        $thissurvey  = $oEditedTemplate->getDefaultDataForRendering();
        $templatedir = $oEditedTemplate->viewPath;
        $templateurl = getTemplateURL($templatename);

        // Save these variables in an array
        // TODO: check if this aData is still used
        $aData['thissurvey']       = $thissurvey;
        $aGlobalReplacements       = array();
        $myoutput[]                = "";


        switch ($screenname) {
            case 'welcome':
                break;

            case 'question':
              // NOTE: this seems not to be used anymore
              // TODO: try if it can be removed
                $aReplacements = array(
                    'QUESTION_TEXT' => gT("How many roads must a man walk down?"),
                    'QUESTION_CODE' => 'Q1 ',
                    'QUESTIONHELP' => $this->getController()->renderPartial('/survey/questions/question_help/questionhelp', array('classes' => '', 'questionHelp' => gT("This is some helpful text.")), true),
                    'QUESTION_MANDATORY' => App()->twigRenderer->renderPartial('/survey/questions/question_help/asterisk.twig', array()),
                    'QUESTION_MAN_CLASS' => ' mandatory',
                    'QUESTION_ESSENTIALS' => 'id="question1"',
                    'QUESTION_CLASS' => 'list-radio',
                    'QUESTION_NUMBER' => '1',
                    'QUESTION_VALID_MESSAGE' => App()->twigRenderer->renderPartial('/survey/questions/question_help/em_tip.twig', array(
                        'coreId' => "vmsg_4496_num_answers",
                        'coreClass' => "em-tip ", // Unsure for this one
                        'vtip' => gT('Hint when response is valid')
                    ), true),
                );

                $aReplacements['ANSWER'] = $this->getController()->renderPartial('/admin/themes/templateeditor_question_answer_view', array(), true);
                $aData['aReplacements'] = array_merge($aGlobalReplacements, $aReplacements);
                break;

            case 'register':
                break;

            case 'completed':
                break;

            case 'assessments':
                break;

            case 'printablesurvey':
                $sLayoutFile = "TODO";
                $aData['aReplacements'] = $aGlobalReplacements;
                $questionoutput = array();
                foreach (file("$templatedir/print_question.pstpl") as $op) {
                    $questionoutput[] = templatereplace($op, array(
                        'QUESTION_NUMBER' => '1',
                        'QUESTION_CODE' => 'Q1',
                        'QUESTION_MANDATORY' => gT('*'),
                        // If there are conditions on a question, list the conditions.
                        'QUESTION_SCENARIO' => 'Only answer this if certain conditions are met.',
                        'QUESTION_CLASS' => ' mandatory list-radio',
                        'QUESTION_TYPE_HELP' => gT('Please choose *only one* of the following:'),
                        // (not sure if this is used) mandatory error
                        'QUESTION_MAN_MESSAGE' => '',
                        // (not sure if this is used) validation error
                        'QUESTION_VALID_MESSAGE' => '',
                        // (not sure if this is used) file validation error
                        'QUESTION_FILE_VALID_MESSAGE' => '',
                        'QUESTION_TEXT' => gT('This is a sample question text. The user was asked to pick an entry.'),
                        'QUESTIONHELP' => gT('This is some help text for this question.'),
                        'ANSWER' =>
                        $this->getController()->renderPartial('/admin/themes/templateeditor_printablesurvey_quesanswer_view', array(
                            'templateurl' => $templateurl
                            ), true),
                        ), $aData, 'Unspecified', false, null, array(), false, $oEditedTemplate);
                }
                $groupoutput = array();
                $groupoutput[] = templatereplace(file_get_contents("$templatedir/print_group.pstpl"), array('QUESTIONS' => implode(' ', $questionoutput)), $aData, 'Unspecified', false, null, array(), false, $oEditedTemplate);

                $myoutput[] = templatereplace(file_get_contents("$templatedir/print_survey.pstpl"), array('GROUPS' => implode(' ', $groupoutput),
                    'SUBMIT_TEXT' => gT("Submit your survey."),
                    'HEADELEMENTS' => getPrintableHeader(),
                    'SUBMIT_BY' => sprintf(gT("Please submit by %s"), date('d.m.y')),
                    'THANKS' => gT('Thank you for completing this survey.'),
                    'END' => gT('This is the survey end message.')
                    ), $aData, 'Unspecified', false, null, array(), false, $oEditedTemplate);
                break;

            case 'printanswers':
                // $sLayoutFile = "TODO";
                //$printoutput = $this->getController()->renderPartial('/admin/themes/templateeditor_printoutput_view', array(), true);
                // $myoutput[] = templatereplace(file_get_contents("$templatedir/startpage.pstpl"), array(), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);
                // $myoutput[] = templatereplace(file_get_contents("$templatedir/printanswers.pstpl"), array('ANSWERTABLE' => $printoutput), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);
                // $myoutput[] = templatereplace(file_get_contents("$templatedir/endpage.pstpl"), array(), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);

                // $myoutput[] = "\n";
                break;

            case 'navigation':
                // Show question index navigation.

                break;

            case 'pdf':
                $print['groups'] = [
                    [
                        'name' => gT('Question group name'),
                        'description' => gT('Question group description'),
                        'questions' => [
                        ]
                    ]
                ];
                break;

            case 'maintenance':
                break;

            case 'error':
                break;
        }


        $thissurvey['include_content'] = $sContentFile;


        // new TemplateConfiguration model created so preview can read theme options from DB
        $oTemplateForPreview =  Template::getInstance($templatename, null, null, false);

        try {
            $myoutput = Yii::app()->twigRenderer->renderTemplateForTemplateEditor(
                $sLayoutFile,
                array(
                    'aSurveyInfo' => $thissurvey,
                    'print'       => $print  // Only used for PDF print layout.
                ),
                $oTemplateForPreview
            );
        } catch (Exception $ex) {
            $myoutput = "<h3>ERROR!</h3>";
            $myoutput .= $ex->getMessage();
        }



        $jsfiles        = $oEditedTemplate->getValidScreenFiles("js");
        $aCssAndJsfiles = array_merge($cssfiles, $jsfiles);

        // XML Behaviour: if only one file, then $files is just a string
        if (!is_array($files) && is_string($files)) {
            $files = array(0 => $files);
        }

        $otherfiles = $oEditedTemplate->getOtherFiles();
        $sEditfile = $oEditedTemplate->getFilePathForEditing($editfile, array_merge($files, $aCssAndJsfiles));

        $extension = substr(strrchr((string) $sEditfile, "."), 1);
        $highlighter = 'html';
        if ($extension == 'css' || $extension == 'js') {
            $highlighter = $extension;
        }

        $aData['codelanguage'] = $sLanguageCode;
        $aData['highlighter'] = $highlighter;
        $aData['screens'] = $screens;
        $aData['templatename'] = $templatename;
        $aData['templateapiversion'] = $oEditedTemplate->getApiVersion();
        $aData['templates'] = $aAllTemplates;
        $aData['editfile'] = $sEditfile;
        $aData['screenname'] = $screenname;
        $aData['tempdir'] = Yii::app()->getConfig('tempdir');
        $aData['userthemerootdir'] = Yii::app()->getConfig('userthemerootdir');
        $aData['relativePathEditfile'] = $editfile;
        $aViewUrls['templateeditorbar_view'][] = $aData;

        $this->showIntroNotification();

        if ($showsummary) {
            Yii::app()->clientScript->registerPackage($oEditedTemplate->sPackageName);
            $aViewUrls = array_merge($aViewUrls, $this->templatesummary($templatename, $screenname, $sEditfile, $editfile, $aAllTemplates, $files, $cssfiles, $jsfiles, $otherfiles, $myoutput));
        }


        return $aViewUrls;
    }

    /**
     * First time user visits template editor, show
     * a notification about manual and forum.
     * @return void
     */
    protected function showIntroNotification()
    {
        $user = User::model()->findByPk(Yii::app()->session['loginID']);
        $not = new UniqueNotification(array(
            'user_id'    => $user->uid,
            'title'      => gT('LimeSurvey theme editor'),
            'markAsNew'  => false,
            'importance' => Notification::HIGH_IMPORTANCE,
            'message'    => sprintf(
                gT('Welcome to the theme editor of LimeSurvey. To get an overview of new functionality and possibilities, please visit the %s LimeSurvey manual %s. For further questions and information, feel free to post your questions on the %s LimeSurvey forums %s.', 'unescaped'),
                '<a target="_blank" href="https://www.limesurvey.org/manual/New_Template_System_in_LS3.x">',
                '</a>',
                '<a target="_blank" href="https://forums.limesurvey.org">',
                '</a>'
            )
        ));
        $not->save();
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function renderWrappedTemplate($sAction = 'themes', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        parent::renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }

    /**
     * Redirects if demo mode is set.
     * @return void
     */
    protected function checkDemoMode()
    {
        if (Yii::app()->getConfig('demoMode')) {
            Yii::app()->user->setFlash('error', gT("Demo mode: Uploading templates is disabled."));
            $this->getController()->redirect(array("admin/themes/sa/upload"));
        }
    }

    /**
     * Redirect if file size is too big.
     * @return void
     */
    protected function checkFileSizeError($uploadName = 'the_file')
    {
        $uploadValidator = new LimeSurvey\Models\Services\UploadValidator();
        $uploadValidator->redirectOnError($uploadName, array("admin/themes/sa/upload"));
    }

    /**
     * Redirect back if $destdir is not writable or already exists.
     *
     * @param string $destdir
     * @param string $sNewDirectoryName
     * @param string $themeType *
     * @return void
     */
    protected function checkDestDir($destdir, $sNewDirectoryName, $themeType)
    {
        if ($themeType == 'question') {
            $redirectUrl = 'themeOptions/index#questionthemes';
        } elseif ($themeType == 'survey') {
            $redirectUrl = 'admin/themes/sa/upload';
        } else {
            $redirectUrl = 'admin/themes/sa/upload';
        }
        if (!is_writeable(dirname($destdir))) {
            Yii::app()->user->setFlash('error', sprintf(gT("Incorrect permissions in your %s folder."), dirname($destdir)));
            $this->getController()->redirect(array($redirectUrl));
        }

        if (is_dir($destdir)) {
            Yii::app()->user->setFlash('error', sprintf(gT("Template '%s' does already exist."), $sNewDirectoryName));
            $this->getController()->redirect(array($redirectUrl));
        }
    }

    /**
     * Get directory name for $themeType in zip file $src based on <metadata><name> tag
     *
     * @param string $themeType 'question' or 'survey'
     * @param string $src
     * @return string
     * @throws Exception
     * @todo Move to service class
     * @todo Same logic for survey theme
     */
    protected function getNewDirectoryName($themeType, $src)
    {
        if ($themeType === 'question') {
            $extConfig = ExtensionConfig::loadFromZip($src);
            return $extConfig->getName();
        } else {
            return sanitize_dirname(pathinfo((string) $_FILES['the_file']['name'], PATHINFO_FILENAME));
        }
    }

    /**
     * @return QuestionThemeInstaller
     */
    private function getQuestionThemeInstaller()
    {
        $fileFetcher = new FileFetcherUploadZip();
        $fileFetcher->setUnzipFilter('templateExtractFilter');
        $installer = new QuestionThemeInstaller();
        $installer->setFileFetcher($fileFetcher);
        return $installer;
    }

    /**
     * @param ZipArchive $zip
     * @return string|null
     * @todo Remove this? Doesn't seem to be used anymore.
     */
    public function findConfigXml(ZipArchive $zip)
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (strpos($filename, 'config.xml') !== false) {
                return $filename;
            }
        }
        return null;
    }
}
