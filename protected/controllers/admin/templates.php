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
        if (!App()->user->checkAccess('templates'))
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
        if (!App()->user->checkAccess('templates', ['crud' => 'export']))
        {
            die('No permission');
        }
        $templatedir = Template::getTemplatePath($templatename) . DIRECTORY_SEPARATOR;
        $tempdir = Yii::app()->getConfig('tempdir');

        $zipfile = "$tempdir/$templatename.zip";
        Yii::app()->loadLibrary('admin.pclzip');
        $zip = new PclZip($zipfile);
        $zip->create($templatedir, PCLZIP_OPT_REMOVE_PATH, Template::getTemplatePath($templatename));

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
      $iTime= preg_replace("/[^0-9]$/", '', $id);
      $sFile = App()->runtimePath . "/template_temp_{$iTime}.html";

      if(!is_file($sFile) || !file_exists($sFile)) die();
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
        if (!App()->user->checkAccess('templates', ['crud' => 'import']))
        {
            die('No permission');
        }
        $aViewUrls = $this->_initialise('default', 'welcome', 'startpage.pstpl', FALSE);
        $lid = returnGlobal('lid');
        $action = returnGlobal('action');

        if ($action == 'templateupload') {
            if (Yii::app()->getConfig('demoMode'))
                $this->getController()->error(gT("Demo mode: Uploading templates is disabled."));

            Yii::app()->loadLibrary('admin.pclzip');

            $zip = new PclZip($_FILES['the_file']['tmp_name']);

            // Create temporary directory so that if dangerous content is unzipped it would be unaccessible
            $sNewDirectoryName=\ls\helpers\Sanitize::dirname(pathinfo($_FILES['the_file']['name'], PATHINFO_FILENAME ));
            $destdir = Yii::app()->getConfig('usertemplaterootdir').DIRECTORY_SEPARATOR.$sNewDirectoryName;

            if (!is_writeable(dirname($destdir)))
                $this->getController()->error(sprintf(gT("Incorrect permissions in your %s folder."), dirname($destdir)));

            if (!is_dir($destdir))
                mkdir($destdir);
            else
                $this->getController()->error(sprintf(gT("Template '%s' does already exist."), $sNewDirectoryName));

            $aImportedFilesInfo = [];
            $aErrorFilesInfo = [];



            if (is_file($_FILES['the_file']['tmp_name'])) {
                $aExtractResult=$zip->extract(PCLZIP_OPT_PATH, $destdir, PCLZIP_CB_PRE_EXTRACT, 'templateExtractFilter');
                if ($aExtractResult==0)
                {
                    $this->getController()->error(gT("This file is not a valid ZIP file archive. Import failed."));

                }
                else
                {

                    foreach($aExtractResult as $sFile)
                    {
                        $aImportedFilesInfo[] = [
                            "filename" => $sFile['stored_filename'],
                            "status" => gT("OK"),
                            'is_folder' => $sFile['folder']
                        ];
                    }
                }

                if (count($aErrorFilesInfo) == 0 && count($aImportedFilesInfo) == 0)
                    $this->getController()->error(gT("This ZIP archive contains no valid template files. Import failed."));
            }
            else
                $this->getController()->error(sprintf(gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."), $basedestdir));


            if (count($aImportedFilesInfo) > 0)
            {
                $templateFixes= $this->_templateFixes($sNewDirectoryName);
            }
            else
            {
                $templateFixes= [];
            }
            $aViewUrls = 'importuploaded_view';
            $aData = [
            'aImportedFilesInfo' => $aImportedFilesInfo,
            'aErrorFilesInfo' => $aErrorFilesInfo,
            'lid' => $lid,
            'newdir' => $sNewDirectoryName,
            'templateFixes' => $templateFixes,
            ];
        }
        else
        {
            $aViewUrls = 'importform_view';
            $aData = ['lid' => $lid];
        }

        $this->_renderWrappedTemplate('templates', $aViewUrls, $aData);
    }
    /**
    * Try to correct a template with new funcyionnality.
    *
    * @access private
    * @param string $templatename
    * @return array $correction ($success,$number,array($information))
    */

    private function _templateFixes($templatename)
    {
        $usertemplaterootdir=Yii::app()->getConfig("usertemplaterootdir");
        $templateFixes= [];
        $templateFixes['success']=true;
        $templateFixes['details']= [];
        // TEMPLATEJS control
        $fname="$usertemplaterootdir/$templatename/startpage.pstpl";
        if(is_file($fname))
        {

            $fhandle = fopen($fname,"r");
            $content = fread($fhandle,filesize($fname));
            if(strpos($content, "{TEMPLATEJS}")===false)
            {
                $content = str_replace("<script type=\"text/javascript\" src=\"{TEMPLATEURL}template.js\"></script>", "{TEMPLATEJS}", $content);
                $fhandle = fopen($fname,"w");
                fwrite($fhandle,$content);
                fclose($fhandle);
                if(strpos($content, "{TEMPLATEJS}")===false)
                {
                    $templateFixes['success']=false;
                    $templateFixes['details']['templatejs']=gT("Unable to add {TEMPLATEJS} placeholder, please check your startpage.pstpl.");
                }
                else
                {
                    $templateFixes['details']['templatejs']=gT("Placeholder {TEMPLATEJS} added to your startpage.pstpl.");
                }
            }
        }
        else
        {
            $templateFixes['success']=false;
            $templateFixes['details']['templatejs']=gT("Unable to find startpage.pstpl to add {TEMPLATEJS} placeholder, please check your template.");
        }
        return $templateFixes;
    }

    /**
    * Responsible to import a template file.
    *
    * @access public
    * @return void
    */
    public function uploadfile()
    {
        if (!App()->user->checkAccess('templates', ['crud' => 'import']))
        {
            die('No permission');
        }
        $action = returnGlobal('action');
        $editfile = returnGlobal('editfile');
        $templatename = returnGlobal('templatename');
        $screenname = returnGlobal('screenname');
        $files = $this->_initfiles($templatename);
        $cssfiles = $this->_initcssfiles();
        $basedestdir = Yii::app()->getConfig('usertemplaterootdir');
        $tempdir = Yii::app()->getConfig('tempdir');
        $allowedtemplateuploads=Yii::app()->getConfig('allowedtemplateuploads');
        $filename=\ls\helpers\Sanitize::filename($_FILES['upload_file']['name'],false,false);// Don't force lowercase or alphanumeric
        $fullfilepath=$basedestdir."/".$templatename . "/" . $filename;

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
                        $uploadresult = sprintf(gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),$tempdir);
                   }
                   else
                   {
                        $uploadresult = sprintf(gT("File %s uploaded"),$filename);
                   }
            }
            Yii::app()->session['flashmessage'] = $uploadresult;
        }
        $this->getController()->redirect(["admin/templates/sa/view/editfile/" . $editfile . "/screenname/" . $screenname . "/templatename/" . $templatename]);
    }

    /**
    * Generates a random temp directory
    *
    * @access protected
    * @param string $dir
    * @param string $prefix
    * @param string $mode
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
        if(!$templatename)
        {
            $templatename = Yii::app()->getConfig("defaulttemplate");
        }
        $aViewUrls = $this->_initialise($templatename, $screenname, $editfile);
        App()->getClientScript()->reset();
        App()->getComponent('bootstrap')->init();

        // After reseting, we need register again the script : maybe move it to endScripts_view for allways needed scripts ?
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . "admin_core.js");
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . "admin_core.js");
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . 'templates.js');
        App()->getClientScript()->registerPackage('ace');
        
        $this->_renderWrappedTemplate('templates', $aViewUrls);

        if ($screenname != 'welcome')
            Yii::app()->session['step'] = 1;
        // This helps handle the load/save buttons)
        else
            unset(Yii::app()->session['step']);
    }

    /**
    * templates::screenredirect()
    * Function that modify order of arguments and pass to main viewing function i.e. view()
    *
    * @access public
    * @param string $editfile
    * @param string $templatename
    * @param string $screenname
    * @return void
    */
    public function screenredirect($editfile = 'startpage.pstpl', $templatename = '', $screenname = 'welcome')
    {
        if(!$templatename)
        {
            $templatename = Yii::app()->getConfig("defaulttemplate");
        }
        $this->getController()->redirect(["admin/templates/sa/view/editfile/" . $editfile . "/screenname/" . $screenname . "/templatename/" . $templatename]);
    }

    /**
    * Function that modify order of arguments and pass to main viewing function i.e. view()
    *
    * @access public
    * @param string $templatename
    * @param string $screenname
    * @param string $editfile
    * @return void
    */
    public function fileredirect($templatename = '', $screenname = 'welcome', $editfile = 'startpage.pstpl')
    {
        if(!$templatename)
        {
            $templatename = Yii::app()->getConfig("defaulttemplate");
        }
        $this->getController()->redirect(["admin/templates/sa/view/editfile/" . $editfile . "/screenname/" . $screenname . "/templatename/" . $templatename]);
    }

    /**
    * Function responsible to delete a template file.
    *
    * @access public
    * @return void
    */
    public function templatefiledelete()
    {
        if (!App()->user->checkAccess('templates', ['crud' => 'update']))
        {
            die('No permission');
        }
        if (returnGlobal('action') == "templatefiledelete") {
            // This is where the temp file is
            $sFileToDelete=preg_replace("[^\w\s\d\.\-_~,;:\[\]\(\]]", '', returnGlobal('otherfile'));

            $the_full_file_path = Yii::app()->getConfig('usertemplaterootdir') . "/" . $_POST['templatename'] . "/" . $sFileToDelete;
            if (@unlink($the_full_file_path))
            {
                Yii::app()->session['flashmessage'] = sprintf(gT("The file %s was deleted."), htmlspecialchars($sFileToDelete));
            }
            else
            {
                Yii::app()->session['flashmessage'] = sprintf(gT("File %s couldn't be deleted. Please check the permissions on the /upload/template folder"), htmlspecialchars($sFileToDelete));
            }
            $this->getController()->redirect(["admin/templates/sa/view/editfile/" . returnGlobal('editfile') . "/screenname/" . returnGlobal('screenname') . "/templatename/" . returnGlobal('templatename')]);
        }
    }

    /**
    * Function responsible to rename a template(folder).
    *
    * @access public
    * @return void
    */
    public function templaterename()
    {
        if (!App()->user->checkAccess('templates', ['crud' => 'update']))
        {
            die('No permission');
        }
        if (returnGlobal('action') == "templaterename" && returnGlobal('newname') && returnGlobal('copydir')) {

            $sOldName = \ls\helpers\Sanitize::dirname(returnGlobal('copydir'));
            $sNewName = \ls\helpers\Sanitize::dirname(returnGlobal('newname'));
            $sNewDirectoryPath = Yii::app()->getConfig('usertemplaterootdir') . "/" . $sNewName;
            $sOldDirectoryPath = Yii::app()->getConfig('usertemplaterootdir') . "/" . returnGlobal('copydir');
            if (isStandardTemplate(returnGlobal('newname')))
                $this->getController()->error(sprintf(gT("Template could not be renamed to `%s`.", "js"), $sNewName) . " " . gT("This name is reserved for standard template.", "js"));
            elseif (file_exists($sNewDirectoryPath))
                $this->getController()->error(sprintf(gT("Template could not be renamed to `%s`.", "js"), $sNewName) . " " . gT("A template with that name already exists.", "js"));
            elseif (rename($sOldDirectoryPath, $sNewDirectoryPath) == false)
                $this->getController()->error(sprintf(gT("Template could not be renamed to `%s`.", "js"), $sNewName) . " " . gT("Maybe you don't have permission.", "js"));
            else
            {
                Survey::model()->updateAll(['template' => $sNewName], "template = :oldname", [':oldname' => $sOldName]);
                if ( \SettingGlobal::get('defaulttemplate') == $sOldName)
                {
                    \SettingGlobal::set('defaulttemplate', $sNewName);
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
        if (!App()->user->checkAccess('templates', ['crud' => 'create']))
        {
            die('No permission');
        }
        $newname=\ls\helpers\Sanitize::dirname(Yii::app()->request->getPost("newname"));
        $copydir=\ls\helpers\Sanitize::dirname(Yii::app()->request->getPost("copydir"));
        $action=Yii::app()->request->getPost("action");
        if ($newname && $copydir) {
            // Copies all the files from one template directory to a new one
            Yii::app()->loadHelper('admin/template');
            $newdirname = Yii::app()->getConfig('usertemplaterootdir') . "/" . $newname;
            $copydirname = Template::getTemplatePath($copydir);
            $oFileHelper=new CFileHelper;
            $mkdirresult = mkdir_p($newdirname);
            if ($mkdirresult == 1) {
                $oFileHelper->copyDirectory($copydirname,$newdirname);
                $templatename = $newname;
                $this->getController()->redirect(["admin/templates/sa/view",'templatename'=>$newname]);
            }
            elseif ($mkdirresult == 2)
            {
                Yii::app()->setFlashMessage(sprintf(gT("Directory with the name `%s` already exists - choose another name"), $newname),'error');
                $this->getController()->redirect(["admin/templates/sa/view",'templatename'=>$copydir]);
            }
            else
            {
                Yii::app()->setFlashMessage(sprintf(gT("Unable to create directory `%s`."), $newname),'error');
                Yii::app()->setFlashMessage(gT("Please check the directory permissions."));
                $this->getController()->redirect(["admin/templates/sa/view"]);
            }
        }
        else
        {
            $this->getController()->redirect(["admin/templates/sa/view"]);
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
        if (!App()->user->checkAccess('templates', ['crud' => 'delete']))
        {
            die('No permission');
        }
        Yii::app()->loadHelper("admin/template");
        if (is_template_editable($templatename) == true) {
            if (rmdirr(Yii::app()->getConfig('usertemplaterootdir') . "/" . $templatename) == true) {
                $surveys = Survey::model()->findAllByAttributes(['template' => $templatename]);
                foreach ($surveys as $s)
                {
                    $s->template = Yii::app()->getConfig('defaulttemplate');
                    $s->save();
                }

                Template::model()->deleteAllByAttributes(['folder' => $templatename]);
                Permission::model()->deleteAllByAttributes(['permission' => $templatename,'entity' => 'template']);

                Yii::app()->setFlashMessage(sprintf(gT("Template '%s' was successfully deleted."), $templatename));
            }
            else
                Yii::app()->setFlashMessage(sprintf(gT("There was a problem deleting the template '%s'. Please check your directory/file permissions."), $templatename),'error');
        }

        // Redirect with default templatename, editfile and screenname
        $this->getController()->redirect(["admin/templates/sa/view"]);
    }

    /**
    * Function responsible to save the changes made in CodemMirror editor.
    *
    * @access public
    * @return void
    */
    public function templatesavechanges()
    {
        if (!App()->user->checkAccess('templates', ['crud' => 'update']))
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
                $changedtext = stripslashes($changedtext);
        }

        $action = returnGlobal('action');
        $editfile = returnGlobal('editfile');
        $templatename = returnGlobal('templatename');
        $screenname = returnGlobal('screenname');
        $files = $this->_initfiles($templatename);
        $cssfiles = $this->_initcssfiles();

        if ($action == "templatesavechanges" && $changedtext) {
            Yii::app()->loadHelper('admin/template');
            $changedtext = str_replace("\r\n", "\n", $changedtext);

            if ($editfile) {
                // Check if someone tries to submit a file other than one of the allowed filenames
                if (multiarray_search($files, 'name', $editfile) === false &&
                multiarray_search($cssfiles, 'name', $editfile) === false
                )
                    $this->getController()->error('Invalid template name');

                $savefilename = Yii::app()->getConfig('usertemplaterootdir') . "/" . $templatename . "/" . $editfile;
                if (is_writable($savefilename)) {
                    if (!$handle = fopen($savefilename, 'w'))
                        $this->getController()->error('Could not open file ' . $savefilename);

                    if (!fwrite($handle, $changedtext))
                        $this->getController()->error('Could not write file ' . $savefilename);

                    fclose($handle);
                }
                else
                    $this->getController()->error("The file $savefilename is not writable");
            }
        }

        $this->getController()->redirect(["admin/templates/sa/view/editfile/" . $editfile . "/screenname/" . $screenname . "/templatename/" . $templatename]);
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
    */
    protected function _templatebar($screenname, $editfile, $screens, $tempdir, $templatename)
    {
        $aData['screenname'] = $screenname;
        $aData['editfile'] = $editfile;
        $aData['screens'] = $screens;
        $aData['tempdir'] = $tempdir;
        $aData['templatename'] = $templatename;
        $aData['usertemplaterootdir'] = Yii::app()->getConfig('usertemplaterootdir');

        $this->getController()->render("/admin/templates/templatebar_view", $aData);
    }

    /**
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
    protected function _templatesummary($templatename, $screenname, $editfile, $templates, $files, $cssfiles, $otherfiles)
    {
        $tempdir = App()->runtimePath;
        Yii::app()->loadHelper("admin/template");
        $aData = [];
        $time = date("ymdHis");

        // Prepare textarea class for optional javascript
        $templateclasseditormode = \SettingGlobal::get('defaulttemplateeditormode'); // default
        if (Yii::app()->session['templateeditormode'] == 'none')
            $templateclasseditormode = 'none';

        $aData['templateclasseditormode'] = $templateclasseditormode;

        // The following lines are forcing the browser to refresh the templates on each save
        $aData['time'] = $time;


        if (Yii::app()->session['templateeditormode'] !== 'default') {
            $sTemplateEditorMode = Yii::app()->session['templateeditormode'];
        } else {
            $sTemplateEditorMode = \SettingGlobal::get('templateeditormode', 'full');
        }
        $sExtension=substr(strrchr($editfile, '.'), 1);
        switch ($sExtension)
        {
           case 'css':$sEditorFileType='css';
           break;
           case 'pstpl':$sEditorFileType='html';
           break;
           case 'js':$sEditorFileType='javascript';
           break;
           default: $sEditorFileType='html';
           break;
        }
        $aData['screenname'] = $screenname;
        $aData['editfile'] = $editfile;
        $aData['tempdir'] = $tempdir;
        $aData['templatename'] = $templatename;
        $aData['templates'] = $templates;
        $aData['files'] = $files;
        $aData['cssfiles'] = $cssfiles;
        $aData['otherfiles'] = $otherfiles;
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
    * @param mixed $templatename
    * @return void
    */
    protected function _initfiles($templatename)
    {
        $files[] = ['name' => 'assessment.pstpl'];
        $files[] = ['name' => 'clearall.pstpl'];
        $files[] = ['name' => 'completed.pstpl'];
        $files[] = ['name' => 'endgroup.pstpl'];
        $files[] = ['name' => 'endpage.pstpl'];
        $files[] = ['name' => 'groupdescription.pstpl'];
        $files[] = ['name' => 'load.pstpl'];
        $files[] = ['name' => 'navigator.pstpl'];
        $files[] = ['name' => 'printanswers.pstpl'];
        $files[] = ['name' => 'privacy.pstpl'];
        $files[] = ['name' => 'question.pstpl'];
        $files[] = ['name' => 'register.pstpl'];
        $files[] = ['name' => 'save.pstpl'];
        $files[] = ['name' => 'surveylist.pstpl'];
        $files[] = ['name' => 'startgroup.pstpl'];
        $files[] = ['name' => 'startpage.pstpl'];
        $files[] = ['name' => 'survey.pstpl'];
        $files[] = ['name' => 'welcome.pstpl'];
        $files[] = ['name' => 'print_survey.pstpl'];
        $files[] = ['name' => 'print_group.pstpl'];
        $files[] = ['name' => 'print_question.pstpl'];

        if (is_file(Yii::app()->getConfig('usertemplaterootdir') . '/' . $templatename . '/question_start.pstpl'))
            $files[] = ['name' => 'question_start.pstpl'];

        return $files;
    }

    /**
    * Function that initialises cssfile data.
    *
    * @access protected
    * @return void
    */
    protected function _initcssfiles()
    {
        $cssfiles[] = ['name' => 'template.css'];
        $cssfiles[] = ['name' => 'template-rtl.css'];
        $cssfiles[] = ['name' => 'ie_fix_6.css'];
        $cssfiles[] = ['name' => 'ie_fix_7.css'];
        $cssfiles[] = ['name' => 'ie_fix_8.css'];
        $cssfiles[] = ['name' => 'jquery-ui-custom.css'];
        $cssfiles[] = ['name' => 'print_template.css'];
        $cssfiles[] = ['name' => 'template.js'];

        return $cssfiles;
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
        App()->getClientScript()->reset();

        Yii::app()->loadHelper('admin/template');

        $files = $this->_initfiles($templatename);

        $cssfiles = $this->_initcssfiles();

        // Standard Support Files
        // These files may be edited or saved
        $supportfiles[] = ['name' => 'print_img_radio.png'];
        $supportfiles[] = ['name' => 'print_img_checkbox.png'];

        // Standard screens
        // Only these may be viewed
        $screens[] = ['name' => gT('Survey List Page'), 'id' => 'surveylist'];
        $screens[] = ['name' => gT('Welcome Page'), 'id' => 'welcome'];
        $screens[] = ['name' => gT('Question Page'), 'id' => 'question'];
        $screens[] = ['name' => gT('Completed Page'), 'id' => 'completed'];
        $screens[] = ['name' => gT('Clear All Page'), 'id' => 'clearall'];
        $screens[] = ['name' => gT('Register Page'), 'id' => 'register'];
        $screens[] = ['name' => gT('Load Page'), 'id' => 'load'];
        $screens[] = ['name' => gT('Save Page'), 'id' => 'save'];
        $screens[] = ['name' => gT('Print answers page'), 'id' => 'printanswers'];
        $screens[] = ['name' => gT('Printable survey page'), 'id' => 'printablesurvey'];

        // Page display blocks
        $SurveyList = [
            'startpage.pstpl',
        'surveylist.pstpl',
        'endpage.pstpl'
        ];
        $Welcome = [
            'startpage.pstpl',
        'welcome.pstpl',
        'privacy.pstpl',
        'navigator.pstpl',
        'endpage.pstpl'
        ];
        $Question = [
            'startpage.pstpl',
        'survey.pstpl',
        'startgroup.pstpl',
        'groupdescription.pstpl',
        'question.pstpl',
        'endgroup.pstpl',
        'navigator.pstpl',
        'endpage.pstpl'
        ];
        $CompletedTemplate = [
        'startpage.pstpl',
        'assessment.pstpl',
        'completed.pstpl',
        'endpage.pstpl'
        ];
        $Clearall = [
            'startpage.pstpl',
        'clearall.pstpl',
        'endpage.pstpl'
        ];
        $Register = [
            'startpage.pstpl',
        'survey.pstpl',
        'register.pstpl',
        'endpage.pstpl'
        ];
        $Save = [
            'startpage.pstpl',
        'save.pstpl',
        'endpage.pstpl'
        ];
        $Load = [
            'startpage.pstpl',
        'load.pstpl',
        'endpage.pstpl'
        ];
        $printtemplate = [
            'startpage.pstpl',
        'printanswers.pstpl',
        'endpage.pstpl'
        ];
        $printablesurveytemplate = [
            'print_survey.pstpl',
        'print_group.pstpl',
        'print_question.pstpl'
        ];

        $file_version = "LimeSurvey template editor " . Yii::app()->getConfig('versionnumber');
        Yii::app()->session['s_lang'] = Yii::app()->session['adminlang'];

        $templatename = \ls\helpers\Sanitize::dirname($templatename);

        // Checks if screen name is in the list of allowed screen names
        if (multiarray_search($screens, 'id', $screenname) === false)
            $this->getController()->error('Invalid screen name');

        if (!isset($action))
            $action = \ls\helpers\Sanitize::paranoid_string(returnGlobal('action'));

        if (!isset($subaction))
            $subaction = \ls\helpers\Sanitize::paranoid_string(returnGlobal('subaction'));

        if (!isset($newname))
            $newname = \ls\helpers\Sanitize::dirname(returnGlobal('newname'));

        if (!isset($copydir))
            $copydir = \ls\helpers\Sanitize::dirname(returnGlobal('copydir'));

        if (is_file(Yii::app()->getConfig('usertemplaterootdir') . '/' . $templatename . '/question_start.pstpl')) {
            $files[] = ['name' => 'question_start.pstpl'];
            $Question[] = 'question_start.pstpl';
        }

        $availableeditorlanguages = ['bg', 'cs', 'de', 'dk', 'en', 'eo', 'es', 'fi', 'fr', 'hr', 'it', 'ja', 'mk', 'nl', 'pl', 'pt', 'ru', 'sk', 'zh'];
        $extension = substr(strrchr($editfile, "."), 1);
        if ($extension == 'css' || $extension == 'js')
            $highlighter = $extension;
        else
            $highlighter = 'html';

        if (in_array(Yii::app()->session['adminlang'], $availableeditorlanguages))
            $codelanguage = Yii::app()->session['adminlang'];
        else
            $codelanguage = 'en';

        $templates = getTemplateList();
        if (!isset($templates[$templatename]))
            $templatename = Yii::app()->getConfig('defaulttemplate');

        $normalfiles = ["DUMMYENTRY", ".", "..", "preview.png"];
        foreach ($files as $fl)
            $normalfiles[] = $fl["name"];

        foreach ($cssfiles as $fl)
            $normalfiles[] = $fl["name"];

        // Some global data
        $aData['sitename'] = App()->name;
        $siteadminname = Yii::app()->getConfig('siteadminname');
        $siteadminemail = Yii::app()->getConfig('siteadminemail');

        // Set this so common.php doesn't throw notices about undefined variables
        $survey = new Survey();
        $survey->format = \Survey::FORMAT_GROUP;
        $session = new SurveySession(null, new DummyResponse($survey), 0);
        $session->setSurvey($survey);
        $survey->languagesettings = [
            App()->language => $languageSettings = new SurveyLanguageSetting()
        ];


        // FAKE DATA FOR TEMPLATES
        $survey['sid'] = 12345;
        $languageSettings->title = gT("Template Sample");
        $languageSettings->description =
            "<p>".gT('This is a sample survey description. It could be quite long.')."</p>".
            "<p>".gT("But this one isn't.")."<p>";
        $languageSettings->welcometext =
            "<p>".gT('Welcome to this sample survey')."<p>" .
            "<p>".gT('You should have a great time doing this')."<p>";
        $survey->bool_allowsave = true;
        $survey->bool_active = true;
        $survey['tokenanswerspersistence'] = "Y";


        $languageSettings->url = "http://www.limesurvey.org/";
        $languageSettings->urldescription = gT("Some URL description");
        $survey->usecaptcha = "A";
        $percentcomplete = \ls\helpers\FrontEnd::makegraph(6, 10);

        $groupname = gT("Group 1: The first lot of questions");
        $groupdescription = gT("This group description is fairly vacuous, but quite important.");

        $navigator = $this->getController()->render('/admin/templates/templateeditor_navigator_view', [
        'screenname' => $screenname
        ], true);

        $completed = $this->getController()->render('/admin/templates/templateeditor_completed_view', [], true);

        $assessments = $this->getController()->render('/admin/templates/templateeditor_assessments_view', [], true);

        $printoutput = $this->getController()->render('/admin/templates/templateeditor_printoutput_view', [], true);

        $totalquestions = '10';
        $surveyformat = 'Format';
        $notanswered = '5';
        $privacy = '';
        $surveyid = '1295';
        $token = 1234567;

        $templatedir = \Template::getTemplatePath($templatename);
        $templateurl = \Template::getTemplateURL($templatename);

        // Save these variables in an array
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


        if (is_array($files)) {
            $match = 0;
            foreach ($files as $f)
                if ($editfile == $f["name"])
                    $match = 1;

                foreach ($cssfiles as $f)
                if ($editfile == $f["name"])
                    $match = 1;

                if ($match == 0)
                if (count($files) > 0)
                    $editfile = $files[0]["name"];
                else
                    $editfile = "";
        }

        // Get list of 'otherfiles'
        $otherfiles = [];
        if ($handle = opendir($templatedir)) {
            while (false !== ($file = readdir($handle)))
            {
                if (!array_search($file, $normalfiles)) {
                    if (!is_dir($templatedir . DIRECTORY_SEPARATOR . $file)) {
                        $otherfiles[] = ["name" => $file];
                    }
                }
            }

            closedir($handle);
        }

        $aData['codelanguage'] = $codelanguage;
        $aData['highlighter'] = $highlighter;
        $aData['screens'] = $screens;
        $aData['templatename'] = $templatename;
        $aData['templates'] = $templates;
        $aData['editfile'] = $editfile;
        $aData['screenname'] = $screenname;
        $aData['tempdir'] = Yii::app()->getConfig('tempdir');
        $aData['usertemplaterootdir'] = Yii::app()->getConfig('usertemplaterootdir');

        $aViewUrls['templateeditorbar_view'][] = $aData;

        if ($showsummary)
            $aViewUrls = array_merge($aViewUrls, $this->_templatesummary($templatename, $screenname, $editfile, $templates, $files, $cssfiles, $otherfiles));

        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . 'admin_core.js');
        return $aViewUrls;
    }

    /**
    * Renders template(s) wrapped in header and footer
    *
    * @param string $sAction Current action, the folder to fetch views from
    * @param string|array $aViewUrls View url(s)
    * @param array $aData Data to be passed on. Optional.
    */
    protected function _renderWrappedTemplate($sAction = 'templates', $aViewUrls = [], $aData = [])
    {
        $aData['display']['menu_bars'] = false;
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }
}
