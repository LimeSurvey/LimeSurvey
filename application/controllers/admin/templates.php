<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
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

/**
* templates
*
* @package LimeSurvey
* @author
* @copyright 2011
*/
class templates extends Survey_Common_Action
{

    public function runWithParams($params)
    {
        if (!Permission::model()->hasGlobalPermission('templates','read'))
        {
            die('No permission');
        }
        parent::runWithParams($params);
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
        $oEditedTemplate = Template::model()->getTemplateConfiguration($templatename);
        if (!Permission::model()->hasGlobalPermission('templates','export'))
        {
            die('No permission');
        }

        $templatedir = $oEditedTemplate->path . DIRECTORY_SEPARATOR;
        $tempdir = Yii::app()->getConfig('tempdir');

        $zipfile = "$tempdir/$templatename.zip";
        Yii::app()->loadLibrary('admin.pclzip');
        $zip = new PclZip($zipfile);
        $zip->create($templatedir, PCLZIP_OPT_REMOVE_PATH, $oEditedTemplate->path);

        if (is_file($zipfile)) {
            // Send the file for download!
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
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
        $sFile = Yii::app()->getConfig("tempdir").DIRECTORY_SEPARATOR."template_temp_{$iTime}.html";

        if(!is_file($sFile) || !file_exists($sFile)) {
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
        if (!Permission::model()->hasGlobalPermission('templates','import'))
        {
            die('No permission');
        }

        Yii::app()->loadHelper('admin/template');
        $lid = returnGlobal('lid');
        $action = returnGlobal('action');

        if ($action == 'templateupload')
        {
            if (Yii::app()->getConfig('demoMode'))
            {
                Yii::app()->user->setFlash('error',gT("Demo mode: Uploading templates is disabled."));
                $this->getController()->redirect(array("admin/templates/sa/upload"));
            }

            Yii::app()->loadLibrary('admin.pclzip');

            if ($_FILES['the_file']['error']==1 || $_FILES['the_file']['error']==2)
            {
                Yii::app()->setFlashMessage(sprintf(gT("Sorry, this file is too large. Only files up to %01.2f MB are allowed."), getMaximumFileUploadSize()/1024/1024),'error');
                $this->getController()->redirect(array("admin/templates/sa/upload"));
            }

            $zip = new PclZip($_FILES['the_file']['tmp_name']);

            $sNewDirectoryName=sanitize_dirname(pathinfo($_FILES['the_file']['name'], PATHINFO_FILENAME ));
            $destdir = Yii::app()->getConfig('usertemplaterootdir').DIRECTORY_SEPARATOR.$sNewDirectoryName;

            if (!is_writeable(dirname($destdir)))
            {
                Yii::app()->user->setFlash('error',sprintf(gT("Incorrect permissions in your %s folder."), dirname($destdir)));
                $this->getController()->redirect(array("admin/templates/sa/upload"));
            }

            if (!is_dir($destdir))
                mkdir($destdir);
            else
            {
                Yii::app()->user->setFlash('error', sprintf(gT("Template '%s' does already exist."), $sNewDirectoryName));
                $this->getController()->redirect(array("admin/templates/sa/upload"));
            }

            $aImportedFilesInfo = array();
            $aErrorFilesInfo = array();

            if (is_file($_FILES['the_file']['tmp_name']))
            {
                $aExtractResult=$zip->extract(PCLZIP_OPT_PATH, $destdir, PCLZIP_CB_PRE_EXTRACT, 'templateExtractFilter');

                if ($aExtractResult===0)
                {
                    Yii::app()->user->setFlash('error',gT("This file is not a valid ZIP file archive. Import failed."));
                    rmdirr($destdir);
                    $this->getController()->redirect(array("admin/templates/sa/upload"));
                }
                else
                {
                    // Successfully unpacked
                    foreach ($aExtractResult as $sFile)
                    {
                        if ($sFile['status']=='skipped' && !$sFile['folder'])
                        {
                            $aErrorFilesInfo[] = array(
                                "filename" => $sFile['stored_filename'],
                            );
                        }
                        else
                        {
                            $aImportedFilesInfo[] = array(
                                "filename" => $sFile['stored_filename'],
                                "status" => gT("OK"),
                                'is_folder' => $sFile['folder']
                            );
                        }
                    }
                    if (!Template::checkIfTemplateExists($sNewDirectoryName))
                    {
                        Yii::app()->user->setFlash('error',gT("This ZIP archive did not contain a template. Import failed."));
                        rmdirr($destdir);
                        $this->getController()->redirect(array("admin/templates/sa/upload"));
                    }
                }

                if (count($aImportedFilesInfo) == 0)
                {
                    Yii::app()->user->setFlash('error',gT("This ZIP archive contains no valid template files. Import failed."));
                    $this->getController()->redirect(array("admin/templates/sa/upload"));
                }
            }
            else
            {
                Yii::app()->setFlashMessage(gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder."),'error');
                rmdirr($destdir);
                $this->getController()->redirect(array("admin/templates/sa/upload"));
            }

            $aViewUrls = 'importuploaded_view';
            $aData = array(
                'aImportedFilesInfo' => $aImportedFilesInfo,
                'aErrorFilesInfo' => $aErrorFilesInfo,
                'lid' => $lid,
                'newdir' => $sNewDirectoryName,
            );
        }
        else
        {
            $aViewUrls = 'importform_view';
            $aData = array('lid' => $lid);
        }

        $this->_renderWrappedTemplate('templates', $aViewUrls, $aData);
    }

    /**
    * Responsible to import a template file.
    *
    * @access public
    * @return void
    */
    public function uploadfile()
    {
        if (!Permission::model()->hasGlobalPermission('templates','import'))
        {
            die('No permission');
        }

        $action = returnGlobal('action');
        $editfile = App()->request->getPost('editfile');
        $templatename = returnGlobal('templatename');
        $oEditedTemplate = Template::model()->getTemplateConfiguration($templatename);
        $templatedir = $oEditedTemplate->viewPath;
        $screenname = returnGlobal('screenname');
        $cssfiles = $this->_initcssfiles($oEditedTemplate);
        $basedestdir = Yii::app()->getConfig('usertemplaterootdir');
        $tempdir = Yii::app()->getConfig('tempdir');
        $allowedtemplateuploads=Yii::app()->getConfig('allowedtemplateuploads');
        $filename=sanitize_filename($_FILES['upload_file']['name'],false,false,false);// Don't force lowercase or alphanumeric

        $dirfilepath = $oEditedTemplate->filesPath;
        if (!file_exists($dirfilepath))
        {
            if(is_writable($oEditedTemplate->path ))
            {
                mkdir($dirfilepath, 0777, true);
            }
            else
            {
                $uploadresult = sprintf(gT("The folder %s doesn't exist and can't be created."),$dirfilepath);
                Yii::app()->setFlashMessage($uploadresult,'error');
                $this->getController()->redirect(array('admin/templates','sa'=>'view','editfile'=>$editfile,'screenname'=>$screenname,'templatename'=>$templatename));
            }
        }

        $fullfilepath = $dirfilepath . $filename;
        $status='error';
        if($action=="templateuploadfile")
        {
            if(Yii::app()->getConfig('demoMode'))
            {
                $uploadresult = gT("Demo mode: Uploading template files is disabled.");
            }
            elseif($filename!=$_FILES['upload_file']['name'])
            {
                $uploadresult = gT("This filename is not allowed to be uploaded.");
            }
            elseif(!in_array(strtolower(substr(strrchr($filename, '.'),1)),explode ( "," , $allowedtemplateuploads )))
            {
                $uploadresult = gT("This file type is not allowed to be uploaded.");
            }
            else
            {
                //Uploads the file into the appropriate directory
                if (!@move_uploaded_file($_FILES['upload_file']['tmp_name'], $fullfilepath)) {
                    $uploadresult = gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder.");
                }
                else
                {
                    $uploadresult = sprintf(gT("File %s uploaded"),$filename);
                    $status='success';
                }
            }
            Yii::app()->setFlashMessage($uploadresult,$status);
        }
        $this->getController()->redirect(array('admin/templates','sa'=>'view','editfile'=>$editfile,'screenname'=>$screenname,'templatename'=>$templatename));
    }

    /**
    * Generates a random temp directory
    *
    * @access protected
    * @param string $dir
    * @param string $prefix
    * @param integer $mode
    * @return string
    */
    protected function _tempdir($dir, $prefix = '', $mode = 0700)
    {
        if (substr($dir, -1) != '/')
            $dir .= '/';

        do
        {
            $path = $dir . $prefix . mt_rand(0, 9999999);
        }
        while (!mkdir($path, $mode));

        return $path;
    }

    /**
    * Strips file extension
    *
    * @access protected
    * @param string $name
    * @return string
    */
    protected function _strip_ext($name)
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
    public function index($editfile = 'startpage.pstpl', $screenname = 'welcome', $templatename = '')
    {
        if ($templatename=='') {
            $templatename = Yii::app()->getConfig("defaulttemplate");
        }

        // This can happen if the global default template is deleted
        if (!Template::checkIfTemplateExists($templatename)){
            // Redirect to the default template
            Yii::app()->setFlashMessage(sprintf(gT('Template %s does not exist.'),htmlspecialchars($templatename,ENT_QUOTES)),'error');
            $this->getController()->redirect(array('admin/templates/sa/view/','templatename'=>'default'));
        }

        /* Keep Bootstrap Package clean after loading template : because template can update boostrap */
        $aBootstrapPackage=Yii::app()->clientScript->packages['bootstrap'];

        $aViewUrls = $this->_initialise($templatename, $screenname, $editfile, true, true);
        //var_dump($aViewUrls); die();

        App()->getClientScript()->reset();
        Yii::app()->clientScript->packages['bootstrap']=$aBootstrapPackage;
        App()->getClientScript()->registerScriptFile( App()->getConfig('adminscripts') . 'admin_core.js');
        App()->getClientScript()->registerScriptFile( App()->getConfig('adminscripts') . 'templates.js');
        App()->getClientScript()->registerScriptFile( App()->getConfig('adminscripts') . 'notifications.js');
        App()->getClientScript()->registerPackage('ace');
        App()->getClientScript()->registerPackage('jsuri');
        $aData['fullpagebar']['returnbutton']=true;
        $this->_renderWrappedTemplate('templates', $aViewUrls, $aData);

        // This helps handle the load/save buttons)
        if ($screenname != 'welcome')
        {
            Yii::app()->session['step'] = 1;
        }
        else
        {
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
        if (!Permission::model()->hasGlobalPermission('templates','update'))
        {
            die('No permission');
        }
        // This is where the temp file is
        $sFileToDelete=sanitize_filename(App()->request->getPost('otherfile'),false,false);
        $sTemplateName=Template::templateNameFilter(App()->request->getPost('templatename'));
        $oEditedTemplate = Template::model()->getTemplateConfiguration($sTemplateName);
        $templatedir = $oEditedTemplate->viewPath;
        $filesdir = $oEditedTemplate->filesPath;
        $the_full_file_path = $filesdir . $sFileToDelete;
        if (@unlink($the_full_file_path))
        {
            Yii::app()->user->setFlash('error', sprintf(gT("The file %s was deleted."), htmlspecialchars($sFileToDelete)));
        }
        else
        {
            Yii::app()->user->setFlash('error',sprintf(gT("File %s couldn't be deleted. Please check the permissions on the /upload/template folder"), htmlspecialchars($sFileToDelete)));
        }
        $this->getController()->redirect(array('admin/templates','sa'=>'view', 'editfile'=> App()->request->getPost('editfile'),'screenname'=>App()->request->getPost('screenname'),'templatename'=>$sTemplateName));
    }

    /**
    * Function responsible to rename a template(folder).
    *
    * @access public
    * @return void
    */
    public function templaterename()
    {
        if (!Permission::model()->hasGlobalPermission('templates','update'))
        {
            die('No permission');
        }
        if (returnGlobal('action') == "templaterename" && returnGlobal('newname') && returnGlobal('copydir')) {

            $sOldName = sanitize_dirname(returnGlobal('copydir'));
            $sNewName = sanitize_dirname(returnGlobal('newname'));
            $sNewDirectoryPath = Yii::app()->getConfig('usertemplaterootdir') . "/" . $sNewName;
            $sOldDirectoryPath = Yii::app()->getConfig('usertemplaterootdir') . "/" . returnGlobal('copydir');
            if (isStandardTemplate(returnGlobal('newname')))
            {
                Yii::app()->user->setFlash('error',sprintf(gT("Template could not be renamed to `%s`.", "js"), $sNewName) . " " . gT("This name is reserved for standard template.", "js"));

                $this->getController()->redirect(array("admin/templates/sa/upload"));
            }
            elseif (file_exists($sNewDirectoryPath))
            {
                Yii::app()->user->setFlash('error',sprintf(gT("Template could not be renamed to `%s`.", "js"), $sNewName) . " " . gT("A template with that name already exists.", "js"));

                $this->getController()->redirect(array("admin/templates/sa/upload"));
            }
            elseif (rename($sOldDirectoryPath, $sNewDirectoryPath) == false)
            {
                Yii::app()->user->setFlash('error',sprintf(gT("Template could not be renamed to `%s`.", "js"), $sNewName) . " " . gT("Maybe you don't have permission.", "js"));

                $this->getController()->redirect(array("admin/templates/sa/upload"));
            }
            else
            {
                Survey::model()->updateAll(array( 'template' => $sNewName ), "template = :oldname", array(':oldname'=>$sOldName));
                if ( getGlobalSetting('defaulttemplate')==$sOldName)
                {
                    setGlobalSetting('defaulttemplate',$sNewName);
                }
                $this->index("startpage.pstpl", "welcome", $sNewName);
            }
        }
    }

    /**
    * Function responsible to copy a template.
    *
    * @access public
    * @return void
    */
    public function templatecopy()
    {
        if (!Permission::model()->hasGlobalPermission('templates','create'))
        {
            die('No permission');
        }
        $newname=sanitize_dirname(Yii::app()->request->getPost("newname"));
        $copydir=sanitize_dirname(Yii::app()->request->getPost("copydir"));
        $action=Yii::app()->request->getPost("action");
        if ($newname && $copydir) {
            // Copies all the files from one template directory to a new one
            Yii::app()->loadHelper('admin/template');
            $newdirname = Yii::app()->getConfig('usertemplaterootdir') . "/" . $newname;
            $copydirname = getTemplatePath($copydir);
            $oFileHelper=new CFileHelper;
            $mkdirresult = mkdir_p($newdirname);
            if ($mkdirresult == 1) {
                $oFileHelper->copyDirectory($copydirname,$newdirname);
                $templatename = $newname;
                $this->getController()->redirect(array("admin/templates/sa/view",'templatename'=>$newname));
            }
            elseif ($mkdirresult == 2)
            {
                Yii::app()->setFlashMessage(sprintf(gT("Directory with the name `%s` already exists - choose another name"), $newname),'error');
                $this->getController()->redirect(array("admin/templates/sa/view",'templatename'=>$copydir));
            }
            else
            {
                Yii::app()->setFlashMessage(sprintf(gT("Unable to create directory `%s`."), $newname),'error');
                Yii::app()->setFlashMessage(gT("Please check the directory permissions."));
                $this->getController()->redirect(array("admin/templates/sa/view"));
            }
        }
        else
        {
            $this->getController()->redirect(array("admin/templates/sa/view"));
        }
    }

    /**
    * Function responsible to delete a template.
    *
    * @access public
    * @param string $templatename
    * @return void
    */
    public function delete($templatename)
    {
        if (!Permission::model()->hasGlobalPermission('templates','delete'))
        {
            die('No permission');
        }
        Yii::app()->loadHelper("admin/template");
        if (array_key_exists($templatename,Template::getTemplateList()) && !Template::isStandardTemplate($templatename))
        {
            if (rmdirr(Yii::app()->getConfig('usertemplaterootdir') . "/" . $templatename) == true) {
                $surveys = Survey::model()->findAllByAttributes(array('template' => $templatename));

                // The default template could be the same as the one we're trying to remove
                $globalDefaultIsGettingDeleted = Yii::app()->getConfig('defaulttemplate') == $templatename;
                if ($globalDefaultIsGettingDeleted)
                {
                    setGlobalSetting('defaulttemplate', 'default');
                }

                foreach ($surveys as $s)
                {
                    $s->template = Yii::app()->getConfig('defaulttemplate');
                    $s->save();
                }

                Template::model()->deleteAllByAttributes(array('folder' => $templatename));
                Permission::model()->deleteAllByAttributes(array('permission' => $templatename,'entity' => 'template'));

                Yii::app()->setFlashMessage(sprintf(gT("Template '%s' was successfully deleted."), $templatename));
            }
            else
                Yii::app()->setFlashMessage(sprintf(gT("There was a problem deleting the template '%s'. Please check your directory/file permissions."), $templatename),'error');
        }
        else
        {
            // Throw an error 500 ?
        }
        // Redirect with default templatename, editfile and screenname
        $this->getController()->redirect(array("admin/templates/sa/view"));
    }

    /**
    * Function responsible to save the changes made in CodemMirror editor.
    *
    * @access public
    * @return void
    */
    public function templatesavechanges()
    {

        if (!Permission::model()->hasGlobalPermission('templates','update'))
        {
            die('No permission');
        }
        if (returnGlobal('changes')) {
            $changedtext = returnGlobal('changes');
            $changedtext = str_replace('<?', '', $changedtext);
            if (get_magic_quotes_gpc())
                $changedtext = stripslashes($changedtext);
        }

        if (returnGlobal('changes_cp')) {
            $changedtext = returnGlobal('changes_cp');
            $changedtext = str_replace('<?', '', $changedtext);
            if (get_magic_quotes_gpc())
            {
                $changedtext = stripslashes($changedtext);
            }
        }

        $action          = returnGlobal('action');
        $editfile        = returnGlobal('editfile');
        $sTemplateName   = Template::templateNameFilter(App()->request->getPost('templatename'));
        $screenname      = returnGlobal('screenname');
        $oEditedTemplate = Template::model()->getTemplateConfiguration($sTemplateName);
        $aScreenFiles    = $this->getValidScreenFiles($sTemplateName);
        $cssfiles        = $this->_initcssfiles($oEditedTemplate);
        $jsfiles         = $this->_getEditableJsFiles($oEditedTemplate);

        if ($action == "templatesavechanges" && $changedtext)
        {
            Yii::app()->loadHelper('admin/template');
            $changedtext = str_replace("\r\n", "\n", $changedtext);

            if ($editfile)
            {
                // Check if someone tries to submit a file other than one of the allowed filenames
                if (
                in_array($editfile,$aScreenFiles)===false &&
                in_array($editfile,$cssfiles)===false &&
                in_array($editfile,$jsfiles)===false
                )
                {
                    Yii::app()->user->setFlash('error',gT('Invalid template name'));
                    $this->getController()->redirect(array("admin/templates/sa/upload"));
                }

                $savefilename = gettemplatefilename($sTemplateName, $editfile);

                if (is_writable($savefilename))
                {
                    if (!$handle = fopen($savefilename, 'w'))
                    {
                        Yii::app()->user->setFlash('error',gT('Could not open file '). $savefilename);
                        $this->getController()->redirect(array("admin/templates/sa/upload"));
                    }

                    if (!fwrite($handle, $changedtext))
                    {
                        Yii::app()->user->setFlash('error',gT('Could not write file '). $savefilename);
                        $this->getController()->redirect(array("admin/templates/sa/upload"));
                    }

                    $oEditedTemplate->actualizeLastUpdate();

                    fclose($handle);
                }
                else
                {
                    Yii::app()->user->setFlash('error',"The file $savefilename is not writable");
                    $this->getController()->redirect(array("admin/templates/sa/upload"));
                }

            }
        }
        $this->getController()->redirect(array('admin/templates/','sa'=>'view','editfile'=>$editfile,'screenname'=>$screenname,'templatename'=>$sTemplateName));
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
    protected function _templatebar($screenname, $editfile, $screens, $tempdir, $templatename)
    {
        $aData=array();
        $aData['screenname'] = $screenname;
        $aData['editfile'] = $editfile;
        $aData['screens'] = $screens;
        $aData['tempdir'] = $tempdir;
        $aData['templatename'] = $templatename;
        $aData['usertemplaterootdir'] = Yii::app()->getConfig('usertemplaterootdir');

        $this->getController()->renderPartial("/admin/templates/templatebar_view", $aData);
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
    * @return void
    */
    protected function _templatesummary($templatename, $screenname, $editfile, $templates, $files, $cssfiles, $jsfiles, $otherfiles, $myoutput)
    {
        $tempdir = Yii::app()->getConfig("tempdir");
        $tempurl = Yii::app()->getConfig("tempurl");
        Yii::app()->loadHelper("admin/template");
        $aData = array();
        $time = date("ymdHis");
        // Prepare textarea class for optional javascript
        $templateclasseditormode = getGlobalSetting('defaulttemplateeditormode'); // default
        if (Yii::app()->session['templateeditormode'] == 'none')
        {
            $templateclasseditormode = 'none';
        }

        $aData['templateclasseditormode'] = $templateclasseditormode;

        // The following lines are forcing the browser to refresh the templates on each save
        @$fnew = fopen("$tempdir/template_temp_$time.html", "w+");
        $aData['time'] = $time;
        /* Load this template config, else 'survey-template' package can be outdated */
        $oEditedTemplate = Template::model()->getTemplateConfiguration($templatename);
        if (!$fnew) {
            $aData['filenotwritten'] = true;
        }
        else
        {
            App()->getClientScript()->reset();
            @fwrite($fnew, getHeader());

            App()->getClientScript()->registerScriptFile( App()->getConfig('generalscripts') . 'survey_runtime.js');
            /* register template package : PS : use asset :) */
            Yii::app()->clientScript->registerPackage( 'survey-template-'.$templatename );
            /* some needed utils script from limesurvey-public package */
            App()->getClientScript()->registerScript("activateActionLink","activateActionLink();",CClientScript::POS_END);/* show the button if needed */

            /* Must remove all exitsing scripts / css and js */
            App()->getClientScript()->unregisterPackage('admin-theme');         // We remove the admin package

            App()->getClientScript()->render($myoutput);

            @fwrite($fnew, $myoutput);
            @fclose($fnew);
        }
        if (Yii::app()->session['templateeditormode'] !== 'default') {
            $sTemplateEditorMode = Yii::app()->session['templateeditormode'];
        } else {
            $sTemplateEditorMode = getGlobalSetting('templateeditormode', 'full');
        }
        $sExtension=substr(strrchr($editfile, '.'), 1);

        // Select ACE editor mode
        switch ($sExtension)
        {
            case 'css':$sEditorFileType='css';
                break;
            case 'pstpl':$sEditorFileType='html';
                break;
            case 'js':$sEditorFileType='javascript';
                break;
            case 'twig':$sEditorFileType='twig';
                break;
            default: $sEditorFileType='html';
                break;
        }

        $editableCssFiles = $this->_initcssfiles($oEditedTemplate, true);
        $filesdir = $oEditedTemplate->filesPath;
        $aData['screenname'] = $screenname;
        $aData['editfile'] = $editfile;
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
    * Function that initialises file data.
    *
    * @access protected
    * @param string $templatename
    * @return string[]
    */
    protected function getValidScreenFiles($templatename)
    {
        $oEditedTemplate = Template::model()->getTemplateConfiguration($templatename);

        $aScreenFiles = array();
        $filesFromXML = (array) $oEditedTemplate->templateEditor->screens->xpath('//file');

        foreach( $filesFromXML as $file){
                $aScreenFiles[] = (string) $file;
        }
        $aScreenFiles = array_unique($aScreenFiles);
        return $aScreenFiles;
    }

    /**
    * Function that initialises cssfile data.
    *
    * @access protected
    * @param TemplateConfiguration $oEditedTemplate
    * @param boolean $editable
    * @return array
    */
    protected function _initcssfiles(TemplateConfiguration $oEditedTemplate, $editable=false)
    {
        return array();
        // TODO : this should be part of the template editor configuration now

        // If editable CSS files are required, and if they are defined in the template config file
        if($editable && is_object($oEditedTemplate->config->files_editable->css))
        {
            $aCssFiles = (array) $oEditedTemplate->config->files_editable->css->filename;
        }
        // Else we get all the CSS files
        else
        {
            $aCssFiles = (array) $oEditedTemplate->config->files->css->filename;
        }
        return $aCssFiles;
    }

    protected function _getEditableJsFiles($oEditedTemplate)
    {

        return array();
        // TODO : this should be part of the template editor configuration now

        // If editable JS files are defined in the template config file
        if(is_object($oEditedTemplate->config->files_editable->js))
        {
            $aJsFiles = (array) $oEditedTemplate->config->files_editable->js->filename;
        }
        // Else we get all the JS files
        else
        {
            $aJsFiles = (array) $oEditedTemplate->config->files->js->filename;
        }
        return $aJsFiles;
    }

    /**
    * Function that initialises all data and call other functions to load default view.
    *
    * @access protected
    * @param string $templatename
    * @param string $screenname
    * @param string $editfile
    * @param bool $showsummary
    * @return
    */
    protected function _initialise($templatename, $screenname, $editfile, $showsummary = true)
    {
        // LimeSurvey style
        $oEditedTemplate = Template::model()->getTemplateConfiguration($templatename);

        // In survey mode, bootstrap is loaded via the app init.
        // From template editor, we just add the bootstrap files to the js/css to load for template_helper::templatereplace()
        if($oEditedTemplate->cssFramework=='bootstrap')
        {
            /* Actually broke : $oEditedTemplate->config->files->css->filename is a string */
            // Core templates (are published only if exists)
            //$oEditedTemplate->config->files->css->filename[-1]="../../styles-public/bootstrap-for-template-editor.css";
            //$oEditedTemplate->config->files->js->filename[]="../../scripts/bootstrap-for-template-editor.js";

            // User templates (are published only if exists)
            //$oEditedTemplate->config->files->css->filename[-1]="../../../styles-public/bootstrap-for-template-editor.css";
            //$oEditedTemplate->config->files->js->filename[]="../../../scripts/bootstrap-for-template-editor.js";
        }

        //App()->getClientScript()->reset();
        Yii::app()->loadHelper('surveytranslator');
        Yii::app()->loadHelper('admin/template');
        $files    = $this->getValidScreenFiles($templatename);
        $cssfiles = $this->_initcssfiles($oEditedTemplate);


        // Standard Support Files
        // These files may be edited or saved
        $supportfiles[] = array('name' => 'print_img_radio.png');
        $supportfiles[] = array('name' => 'print_img_checkbox.png');

        // Standard screens
        // Only these may be viewed
        $screens=array();



        $screens['welcome']         = gT('Welcome Page','unescaped');       // first page*
        $screens['question']        = gT('Question Page','unescaped');      // main
        $screens['completed']       = gT('Completed Page','unescaped');     // submit?
        $screens['clearall']        = gT('Clear All Page','unescaped');
        $screens['load']            = gT('Load Page','unescaped');
        $screens['save']            = gT('Save Page','unescaped');
        $screens['surveylist']      = gT('Survey List Page','unescaped');
        $screens['error']           = gT('Error','unescaped');
        $screens['assessments']     = gT('Assessments','unescaped');

        // TODO: $screens['register']        = gT('Register Page','unescaped');      // still todo?


        //TODO: $screens['printanswers']    = gT('Print answers page','unescaped');         // todo?
        //TODO: $screens['printablesurvey'] = gT('Printable survey page','unescaped');      // todo ?
        /* Twig layout */
        /* used for call AND for pstl editable files list */
        /* should contains datas for inclusion ??? */
        $SurveyList = array('layout_survey_list.twig',);

        $Welcome = array('layout_first_page.twig',);

        /* Not used : data updated during rendering */
        $Question = array('layout_main.twig',);

        $CompletedTemplate = array('layout_submit.twig',);

        /* Not used */
        $Clearall = array('layout_clearall.twig',);

        /* Not used */
        $Save = array('layout_save.twig',);

        /* Not used */
        $Load = array('layout_load.twig',);

        /** TODO: TWIG
        // Not used
        $printtemplate = array('startpage.pstpl',
            'printanswers.pstpl',
            'endpage.pstpl'
        );
        // Not used
        $printablesurveytemplate = array('print_survey.pstpl',
            'print_group.pstpl',
            'print_question.pstpl'
        );

        // Not used
        $Register = array('startpage.pstpl',
            'survey.pstpl',
            'register.pstpl',
            'endpage.pstpl'
        );
        */

        $file_version = "LimeSurvey template editor " . Yii::app()->getConfig('versionnumber');

        Yii::app()->session['s_lang'] = Yii::app()->session['adminlang'];

        $templatename = sanitize_dirname($templatename);

        // Checks if screen name is in the list of allowed screen names
        if (!isset($screens[$screenname]))
        {
            Yii::app()->user->setFlash('error',gT('Invalid screen name'));
            $this->getController()->redirect(array("admin/templates/sa/upload"));
        }

        /**  TODO: TWIG
        if (is_file(Yii::app()->getConfig('usertemplaterootdir') . DIRECTORY_SEPARATOR . $templatename . DIRECTORY_SEPARATOR.'question_start.pstpl')) {
            $files[] = 'question_start.pstpl';
            $Question[] = 'question_start.pstpl';
        }
        **/

        /* See if we found the file to be edited inside template */
        /* @todo must control if is updatable : in updatable file OR is a view */
        /* Actually allow to update any file exemple css/template-core.css */
        $oEditedTemplate = Template::model()->getTemplateConfiguration($templatename);
        if (file_exists(Yii::app()->getConfig('usertemplaterootdir') . DIRECTORY_SEPARATOR . $templatename. DIRECTORY_SEPARATOR.$editfile)){
            /* the file seems a simple file */
            $sEditFile=realpath(Yii::app()->getConfig('usertemplaterootdir') . DIRECTORY_SEPARATOR . $templatename. DIRECTORY_SEPARATOR.$editfile);
        }elseif (file_exists($oEditedTemplate->viewPath. DIRECTORY_SEPARATOR.$editfile)){
            /* the file seems a view file */
            $sEditFile=realpath($oEditedTemplate->viewPath. DIRECTORY_SEPARATOR.$editfile);
        }else{
            /* the file seems to be invalid */
            $sEditFile='';
        }
        // Make sure file is within the template path
        if (strpos($sEditFile,realpath(Yii::app()->getConfig('usertemplaterootdir') . DIRECTORY_SEPARATOR . $templatename))===false)
        {
            $editfile='layout_first_page.twig';
        }

        $extension = substr(strrchr($editfile, "."), 1);
        $highlighter = 'html';
        if ($extension == 'css' || $extension == 'js')
        {
            $highlighter = $extension;
        }
        // @TODO: Proper language code conversion
        $sLanguageCode = 'en';
        $availableeditorlanguages = array('bg', 'cs', 'de', 'dk', 'en', 'eo', 'es', 'fi', 'fr', 'hr', 'it', 'ja', 'mk', 'nl', 'pl', 'pt', 'ru', 'sk', 'zh');
        if (in_array(Yii::app()->session['adminlang'], $availableeditorlanguages))
        {
            $sLanguageCode = Yii::app()->session['adminlang'];
        }
        $aAllTemplates = getTemplateList();
        if (!isset($aAllTemplates[$templatename]))
        {
            $templatename = Yii::app()->getConfig('defaulttemplate');
        }

        $normalfiles = array("DUMMYENTRY", ".", "..", "preview.png");
        $normalfiles = $normalfiles+$files+$cssfiles;
        // Some global data
        $aData['sitename'] = Yii::app()->getConfig('sitename');
        $siteadminname = Yii::app()->getConfig('siteadminname');
        $siteadminemail = Yii::app()->getConfig('siteadminemail');

        // Set this so common.php doesn't throw notices about undefined variables
        $thissurvey['active'] = 'N';

        // FAKE DATA FOR TEMPLATES
        $thissurvey['name'] = gT("Template Sample");
        $thissurvey['description'] =
        "<p>".gT('This is a sample survey description. It could be quite long.')."</p>".
        "<p>".gT("But this one isn't.")."<p>";
        $thissurvey['welcome'] =
        "<p>".gT('Welcome to this sample survey')."<p>" .
        "<p>".gT('You should have a great time doing this')."<p>";
        $thissurvey['allowsave'] = "Y";
        $thissurvey['active'] = "Y";
        $thissurvey['tokenanswerspersistence'] = "Y";
        $thissurvey['templatedir'] = $templatename;
        $thissurvey['format'] = "G";
        $thissurvey['surveyls_url'] = "http://www.limesurvey.org/";
        $thissurvey['surveyls_urldescription'] = gT("Some URL description");
        $thissurvey['usecaptcha'] = "A";
        $thissurvey['showprogress'] = true;
        $thissurvey['aNavigator']['show'] = true;
        $thissurvey['aNavigator']['aMoveNext']['show'] = true;
        $thissurvey['aNavigator']['aMovePrev']['show'] = true;
        $percentcomplete = 0; //makegraph(6, 10);

        $groupname = gT("Group 1: The first lot of questions");
        $groupdescription = gT("This group description is fairly vacuous, but quite important.");

        $navigator = $this->getController()->renderPartial('/admin/templates/templateeditor_navigator_view', array(
            'screenname' => $screenname
            ), true);

        $completed = $this->getController()->renderPartial('/admin/templates/templateeditor_completed_view', array(), true);

        $assessments = $this->getController()->renderPartial('/admin/templates/templateeditor_assessments_view', array(), true);

        $printoutput = $this->getController()->renderPartial('/admin/templates/templateeditor_printoutput_view', array(), true);

        $totalquestions = '10';
        $surveyformat = 'group';
        $notanswered = '5';
        $privacy = '';
        $surveyid = '1295';
        $token = 1234567;

        $templatedir = $oEditedTemplate->viewPath;
        $templateurl = getTemplateURL($templatename);

        // Save these variables in an array
        // TODO: remove the useless datas for twig
        $aData['thissurvey'] = $thissurvey;
        $aData['percentcomplete'] = $percentcomplete;
        $aData['groupname'] = $groupname;
        $aData['groupdescription'] = $groupdescription;
        $aData['navigator'] = $navigator;
        $aData['help'] = gT("This is some help text.");
        $aData['surveyformat'] = $surveyformat;
        $aData['totalquestions'] = $totalquestions;
        $aData['completed'] = $completed;
        $aData['notanswered'] = $notanswered;
        $aData['privacy'] = $privacy;
        $aData['surveyid'] = $surveyid;
        $aData['sid'] = $surveyid;
        $aData['token'] = $token;
        $aData['assessments'] = $assessments;
        $aData['printoutput'] = $printoutput;
        $aData['templatedir'] = $templatedir;
        $aData['templateurl'] = $templateurl;
        $aData['templatename'] = $templatename;
        $aData['screenname'] = $screenname;
        $aData['editfile'] = $editfile;

        /* always here, even if hidden or in navigator , for button : if nopt in navigator : must be hidden by default */
        /* TODO : TWIG
        $aGlobalReplacements = array(
            'SAVE_LINKS' =>  $this->getController()->renderPartial("/survey/system/actionLink/saveSave",array(
                'submit'=>'', // Don't do the action, just for display
                'class'=>'ls-link-action ls-link-saveall'
            ),true),
            'CLEARALL_LINKS' =>   $this->getController()->renderPartial("/survey/system/actionLink/clearAll",array(
                'class'=>'ls-link-action ls-link-clearall',
                'submit'=>'',
                'confirm'=>'',
            ),true),
            'SAVE' =>  $this->getController()->renderPartial("/survey/system/actionButton/saveSave",array(
                'value'=>'saveall',
                'name'=>'saveall',
                'class'=>'ls-saveaction ls-saveall'
            ),true),
            'CLEARALL' =>   $this->getController()->renderPartial("/survey/system/actionButton/clearAll",array(
                'value'=>'clearall',
                'name'=>'clearall',
                'class'=>'ls-clearaction ls-clearall',
                'confirmedby'=>'confirm-clearall',
                'confirmvalue'=>'confirm',
            ),true),
        );
        */
        $aGlobalReplacements = array();
        $myoutput[] = "";

        // TODO: TWIG
        switch ($screenname)
        {
            case 'surveylist':

                /*
                $aSurveyList = array(
                    'publicSurveys' => Survey::model()->active()->open()->public()->with('languagesettings')->findAll(),
                    'futureSurveys' => Survey::model()->active()->registration()->public()->with('languagesettings')->findAll(),
                );

                $aReplacementSurveyList = array(
                    "SURVEYCONTACT" => sprintf(gT("Please contact %s ( %s ) for further assistance."), Yii::app()->getConfig("siteadminname"), Yii::app()->getConfig("siteadminemail")),
                    "SURVEYLISTHEADING" => gT("The following surveys are available:"),
                    "SURVEYLIST" => $this->getController()->renderPartial('/admin/templates/templateeditor_surveylist_view', $aSurveyList, true),
                );

                //$aData['surveylist'] = $aSurveyListTexts;
                $aData['aReplacements'] = array_merge($aGlobalReplacements,$aReplacementSurveyList);
                $myoutput[] = "";
                $files=$SurveyList;
                foreach ($files as $qs)
                {
                    $myoutput = array_merge($myoutput, doreplacement($oEditedTemplate->viewPath . "/$qs", $aData, $oEditedTemplate));
                }*/
//                $files = $oTemplate->;
                //$files = ;

                $aSurveyListConfig = (array) $oEditedTemplate->templateEditor->screens->surveylist;
                $files             = $aSurveyListConfig['file'];
                $myoutput          = Yii::app()->twigRenderer->renderTemplateForTemplateEditor("layout_survey_list.twig", array('aSurveyInfo'=>$thissurvey), $oEditedTemplate);
                break;

            case 'question':
                $aSurveyListConfig = (array) $oEditedTemplate->templateEditor->screens->question;
                $files             = $aSurveyListConfig['file'];

                $aReplacements = array(
                    'QUESTION_TEXT' => gT("How many roads must a man walk down?"),
                    'QUESTION_CODE' => 'Q1 ',
                    'QUESTIONHELP' => $this->getController()->renderPartial('/survey/questions/question_help/questionhelp', array('classes' => '','questionHelp'=>gT("This is some helpful text.")), true),
                    'QUESTION_MANDATORY' => $this->getController()->renderPartial('/survey/questions/question_help/asterisk', array(), true),
                    'QUESTION_MAN_CLASS' => ' mandatory',
                    'QUESTION_ESSENTIALS' => 'id="question1"',
                    'QUESTION_CLASS' => 'list-radio',
                    'QUESTION_NUMBER' => '1',
                    'QUESTION_VALID_MESSAGE'=>$this->getController()->renderPartial('/survey/questions/question_help/em-tip',array(
                        'coreId'=>"vmsg_4496_num_answers",
                        'coreClass'=>"em-tip ",// Unsure for this one
                        'vtip'=>gT('Hint when response is valid')
                    ), true),
                );

                $aReplacements['ANSWER'] = $this->getController()->renderPartial('/admin/templates/templateeditor_question_answer_view', array(), true);
                $aData['aReplacements'] = array_merge($aGlobalReplacements,$aReplacements);

                // Group Datas
                $thissurvey['aGroups'][1]["name"]            = $groupname;
                $thissurvey['aGroups'][1]["showdescription"] = true;
                $thissurvey['aGroups'][1]["description"]     = $groupdescription;

                // Question 1 Datas
                $thissurvey['aGroups'][1]["aQuestions"][1]["qid"]           = "1";
                $thissurvey['aGroups'][1]["aQuestions"][1]["code"]          = 'Q1 ';
                $thissurvey['aGroups'][1]["aQuestions"][1]["text"]          = gT("How many roads must a man walk down?");
                $thissurvey['aGroups'][1]["aQuestions"][1]["mandatory"]     = true;
                $thissurvey['aGroups'][1]["aQuestions"][1]["valid_message"] = '<div id="vmsg_1_default" class="ls-question-message ls-em-tip em_default ls-em-success"><span class="fa fa-exclamation-circle" aria-hidden="true"></span>Choose one of the following answers</div>';
                $thissurvey['aGroups'][1]["aQuestions"][1]["answer"]        = $this->getController()->renderPartial('/admin/templates/templateeditor_question_answer_view', array(), true);
                $thissurvey['aGroups'][1]["aQuestions"][1]["help"]["show"]  = true;
                $thissurvey['aGroups'][1]["aQuestions"][1]["help"]["text"]  = "This is some helpful text.";

                // Question 2 Datas
                $thissurvey['aGroups'][1]["aQuestions"][2]["qid"]           = "1";
                $thissurvey['aGroups'][1]["aQuestions"][2]["code"]          = 'Q2 ';
                $thissurvey['aGroups'][1]["aQuestions"][2]["text"]          = gT("Please explain something in detail:");
                $thissurvey['aGroups'][1]["aQuestions"][2]["mandatory"]     = false;
                $thissurvey['aGroups'][1]["aQuestions"][2]["valid_message"] = '<div id="vmsg_4496_num_answers" class="em_num_answers emtip error"><span class="fa fa-exclamation-circle" aria-hidden="true"></span>Hint when response is not valid</div>';
                $thissurvey['aGroups'][1]["aQuestions"][2]["answer"]        = $this->getController()->renderPartial('/admin/templates/templateeditor_question_answer_view', array('alt' => true), true);
                $thissurvey['aGroups'][1]["aQuestions"][2]["help"]["show"]  = true;
                $thissurvey['aGroups'][1]["aQuestions"][2]["help"]["text"]  = "This is some helpful text.";

                // This is just to prevent getAllClasses to retreive .ls-hidden CSS class
                $thissurvey['aGroups'][1]["aQuestions"][1]['templateeditor'] = true;
                $thissurvey['aGroups'][1]["aQuestions"][2]['templateeditor'] = true;

                $myoutput = Yii::app()->twigRenderer->renderTemplateForTemplateEditor("layout_main.twig",array('aSurveyInfo'=>$thissurvey), $oEditedTemplate);

                break;

            case 'welcome':
                /*
                $sMoveNext = App()->getController()->renderPartial("/survey/system/actionButton/moveNext",array('value'=>"movenext",'class'=>"ls-move-btn ls-move-next-btn"),true);
                $aData['aReplacements'] = array_merge($aGlobalReplacements,array(
                    'MOVEPREVBUTTON' => '',
                    'MOVENEXTBUTTON' => $sMoveNext,
                    'NAVIGATOR' => "$sMoveNext",
                ));
                $files=$Welcome ;
                foreach ($files as $qs) {
                    $myoutput = array_merge($myoutput, doreplacement($oEditedTemplate->viewPath . "/$qs", $aData, $oEditedTemplate));
                }
                */
                $aSurveyListConfig = (array) $oEditedTemplate->templateEditor->screens->welcome_page;
                $files             = $aSurveyListConfig['file'];
                $myoutput = Yii::app()->twigRenderer->renderTemplateForTemplateEditor("layout_first_page.twig",array('aSurveyInfo'=>$thissurvey), $oEditedTemplate);
                //var_dump($myoutput); die();
                break;

            case 'register':
                $files=$Register;
                $myoutput[] = templatereplace(file_get_contents("$templatedir/startpage.pstpl"), array(), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);

                $aData = array(
                    'aReplacements' => array_merge($aGlobalReplacements,array(
                        'SURVEYNAME' => 'Survey name'
                    ))
                );
                $myoutput = array_merge($myoutput, doreplacement($oEditedTemplate->viewPath . "/survey.pstpl", $aData, $oEditedTemplate));

                $aData['aReplacements'] = array_merge($aGlobalReplacements,array(
                    'REGISTERERROR' => 'Example error message',
                    'REGISTERMESSAGE1' => 'Register message 1',
                    'REGISTERMESSAGE2' => 'Register message 2',
                    'REGISTERFORM' => $this->getController()->renderPartial('/admin/templates/templateeditor_register_view', array('alt' => true), true),
                ));

                $myoutput = array_merge($myoutput, doreplacement($oEditedTemplate->viewPath . "/register.pstpl", $aData, $oEditedTemplate));
                $myoutput[] = templatereplace(file_get_contents("$templatedir/endpage.pstpl"), array(), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);
                $myoutput[] = "\n";
                break;

            case 'save':
                $aSurveyListConfig = (array) $oEditedTemplate->templateEditor->screens->save;
                $files             = $aSurveyListConfig['file'];
                $myoutput = Yii::app()->twigRenderer->renderTemplateForTemplateEditor("layout_save.twig",array('aSurveyInfo'=>$thissurvey), $oEditedTemplate);
                break;

            case 'load':
                /*
                $files=$Load;
                $aData['aReplacements'] = $aGlobalReplacements;
                $myoutput[] = templatereplace(file_get_contents("$templatedir/startpage.pstpl"), array(),$aData['aReplacements'], 'Unspecified', false, NULL, array(), false, $oEditedTemplate);
                $aData['aReplacements']['LOADHEADING'] = App()->getController()->renderPartial("/survey/frontpage/loadForm/heading",array(),true);
                $aData['aReplacements']['LOADMESSAGE'] = App()->getController()->renderPartial("/survey/frontpage/loadForm/message",array(),true);
                $aData['aReplacements']['LOADERROR'] = "";
                $loadForm  = CHtml::beginForm(array("/survey/index","sid"=>$surveyid), 'post',array('id'=>'form-load'));
                $loadForm .= App()->getController()->renderPartial("/survey/frontpage/loadForm/form",array('captcha'=>false),true);
                $loadForm .= CHtml::endForm();
                $aData['aReplacements']['LOADFORM'] = $loadForm;
                $myoutput = array_merge($myoutput, doreplacement($oEditedTemplate->viewPath . "/load.pstpl", $aData, $oEditedTemplate));
                $myoutput[] = templatereplace(file_get_contents("$templatedir/endpage.pstpl"), $aData['aReplacements'], $aData['aReplacements'], 'Unspecified', false, NULL, array(), false, $oEditedTemplate);

                $myoutput[] = "\n";
                */
                $aSurveyListConfig = (array) $oEditedTemplate->templateEditor->screens->load;
                $files             = $aSurveyListConfig['file'];


                $myoutput = Yii::app()->twigRenderer->renderTemplateForTemplateEditor("layout_load.twig",array('aSurveyInfo'=>$thissurvey), $oEditedTemplate);
                break;

            case 'clearall':
                /*
                $files=$Clearall;
                $aData['aReplacements'] = $aGlobalReplacements;
                $myoutput[] = templatereplace(file_get_contents("$templatedir/startpage.pstpl"), array(), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);
                $myoutput[] = templatereplace(file_get_contents("$templatedir/clearall.pstpl"), array(), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);
                $myoutput[] = templatereplace(file_get_contents("$templatedir/endpage.pstpl"), array(), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);
                $myoutput[] = "\n";
                */
                $aSurveyListConfig = (array) $oEditedTemplate->templateEditor->screens->clearall;
                $files             = $aSurveyListConfig['file'];

                $myoutput = Yii::app()->twigRenderer->renderTemplateForTemplateEditor("layout_clearall.twig",array('aSurveyInfo'=>$thissurvey), $oEditedTemplate);
                break;

            case 'completed':
                /*
                $aData['aReplacements'] = $aGlobalReplacements;
                $files=$CompletedTemplate;
                $myoutput[] = "";
                foreach ($files as $qs)
                {
                    $myoutput = array_merge($myoutput, doreplacement($oEditedTemplate->viewPath . "/$qs", $aData, $oEditedTemplate));
                }*/
                $thissurvey['aCompleted']['showDefault'] = true;
                $thissurvey['aCompleted']['aPrintAnswers']['show'] = true;
                $thissurvey['aCompleted']['aPublicStatistics']['show'] = true;

                $aSurveyListConfig = (array) $oEditedTemplate->templateEditor->screens->completed;
                $files             = $aSurveyListConfig['file'];
                $myoutput = Yii::app()->twigRenderer->renderTemplateForTemplateEditor("layout_submit.twig",array('aSurveyInfo'=>$thissurvey), $oEditedTemplate);
                break;

            case 'assessments':
                $thissurvey['aAssessments']['show'] = true;

                // Datas for assessments
                $thissurvey['aAssessments']["datas"]["total"][0]               = array("name" => gT("Welcome to the Assessment"), "min" => "0", "max" => "3", "message" => gT("You got {TOTAL} points out of 3 possible points."));
                $thissurvey['aAssessments']["datas"]["total"]["show"]          = true;
                $thissurvey['aAssessments']["datas"]["subtotal"]["show"]       = true;
                $thissurvey['aAssessments']["datas"]["subtotal"]["datas"][2]   = 3;
                $thissurvey['aAssessments']["datas"]["subtotal_score"][1]      = 3;
                $thissurvey['aAssessments']["datas"]["total_score"]            = 3;

                $aSurveyListConfig = (array) $oEditedTemplate->templateEditor->screens->assessments;
                $files             = $aSurveyListConfig['file'];

                $myoutput = Yii::app()->twigRenderer->renderTemplateForTemplateEditor("layout_submit.twig",array('aSurveyInfo'=>$thissurvey), $oEditedTemplate);
                break;

            case 'printablesurvey':
                $aData['aReplacements'] = $aGlobalReplacements;
                $files=$printablesurveytemplate;
                $questionoutput = array();
                foreach (file("$templatedir/print_question.pstpl") as $op)
                {
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
                        $this->getController()->renderPartial('/admin/templates/templateeditor_printablesurvey_quesanswer_view', array(
                            'templateurl' => $templateurl
                            ), true),
                        ), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);
                }
                $groupoutput = array();
                $groupoutput[] = templatereplace(file_get_contents("$templatedir/print_group.pstpl"), array('QUESTIONS' => implode(' ', $questionoutput)), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);

                $myoutput[] = templatereplace(file_get_contents("$templatedir/print_survey.pstpl"), array('GROUPS' => implode(' ', $groupoutput),
                    'FAX_TO' => gT("Please fax your completed survey to:") . " 000-000-000",
                    'SUBMIT_TEXT' => gT("Submit your survey."),
                    'HEADELEMENTS' => getPrintableHeader(),
                    'SUBMIT_BY' => sprintf(gT("Please submit by %s"), date('d.m.y')),
                    'THANKS' => gT('Thank you for completing this survey.'),
                    'END' => gT('This is the survey end message.')
                    ), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);
                break;

            case 'printanswers':
                $files=$printtemplate;
                $myoutput[] = templatereplace(file_get_contents("$templatedir/startpage.pstpl"), array(), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);
                $myoutput[] = templatereplace(file_get_contents("$templatedir/printanswers.pstpl"), array('ANSWERTABLE' => $printoutput), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);
                $myoutput[] = templatereplace(file_get_contents("$templatedir/endpage.pstpl"), array(), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);

                $myoutput[] = "\n";
                break;

            case 'error':
                $thissurvey['aError']['title'] = gT("Error");
                $thissurvey['aError']['message'] = gT("This is an error message example");

                $aSurveyListConfig = (array) $oEditedTemplate->templateEditor->screens->error;
                $files             = $aSurveyListConfig['file'];

                $myoutput = Yii::app()->twigRenderer->renderTemplateForTemplateEditor("layout_errors.twig",array('aSurveyInfo'=>$thissurvey), $oEditedTemplate);
                break;
        }
        //$myoutput[] = "</html>";

        $jsfiles =  $this->_getEditableJsFiles($oEditedTemplate);
        $aCssAndJsfiles = array_merge($cssfiles,$jsfiles ) ;

        // XML Behaviour: if only one file, then $files is just a string
        if (!is_array($files) && is_string($files)){
            $files   = array(0=>$files);
        }

        if (is_array($files))
        {
            $match = 0;
            if (in_array($editfile,$files) || in_array($editfile,$aCssAndJsfiles))
            {
                $match=1;
            }

            if ($match == 0)
            {
                if (count($files) > 0)
                {
                    $editfile = $files[0];
                }
                else
                {
                    $editfile = "";
                }
            }
        }

        // Get list of 'otherfiles'
        // We can't use $oTemplate->otherFiles, because of retrocompatibility with 2.06 template and the big mess of it mixing files
        $filesdir = ($oEditedTemplate->filesPath!='')?$oEditedTemplate->filesPath:$templatedir . '../files';
        $otherfiles = array();
        if ( file_exists($filesdir) && $handle = opendir($filesdir))
        {
            while (false !== ($file = readdir($handle)))
            {
                if (!array_search($file, $normalfiles)) {
                    if (!is_dir($templatedir . DIRECTORY_SEPARATOR . $file)) {
                        $otherfiles[] = $file;
                    }
                }
            }

            closedir($handle);
        }


        $aData['codelanguage'] = $sLanguageCode;
        $aData['highlighter'] = $highlighter;
        $aData['screens'] = $screens;
        $aData['templatename'] = $templatename;
        $aData['templateapiversion'] = $oEditedTemplate->getApiVersion();
        $aData['templates'] = $aAllTemplates;
        $aData['editfile'] = $editfile;
        $aData['screenname'] = $screenname;
        $aData['tempdir'] = Yii::app()->getConfig('tempdir');
        $aData['usertemplaterootdir'] = Yii::app()->getConfig('usertemplaterootdir');
        $aViewUrls['templateeditorbar_view'][] = $aData;

        if ($showsummary)
        {
            //$aCssfileseditable = (array) $oEditedTemplate->config->files_editable->css->filename;
            $aViewUrls = array_merge($aViewUrls, $this->_templatesummary($templatename, $screenname, $editfile, $aAllTemplates, $files, $cssfiles, $jsfiles, $otherfiles, $myoutput));
        }
        App()->getClientScript()->registerScriptFile( App()->getConfig('adminscripts') . 'admin_core.js');
        return $aViewUrls;
    }

    /**
    * Renders template(s) wrapped in header and footer
    *
    * @param string $sAction Current action, the folder to fetch views from
    * @param string|array $aViewUrls View url(s)
    * @param array $aData Data to be passed on. Optional.
    */
    protected function _renderWrappedTemplate($sAction = 'templates', $aViewUrls = array(), $aData = array())
    {
        //var_dump($aViewUrls);
        //$aData['display']['menu_bars'] = false;
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }
}
