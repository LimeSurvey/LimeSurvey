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
        if (!Permission::model()->hasGlobalPermission('templates','export'))
        {
            die('No permission');
        }
        $templatedir = getTemplatePath($templatename) . DIRECTORY_SEPARATOR;
        $tempdir = Yii::app()->getConfig('tempdir');

        $zipfile = "$tempdir/$templatename.zip";
        Yii::app()->loadLibrary('admin.pclzip');
        $zip = new PclZip($zipfile);
        $zip->create($templatedir, PCLZIP_OPT_REMOVE_PATH, getTemplatePath($templatename));

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
      $sFile = Yii::app()->getConfig("tempdir").DIRECTORY_SEPARATOR."template_temp_{$iTime}.html";
      
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
        if (!Permission::model()->hasGlobalPermission('templates','import'))
        {
            die('No permission');
        }
        $clang = $this->getController()->lang;
        $aViewUrls = $this->_initialise('default', 'welcome', 'startpage.pstpl', FALSE);
        $lid = returnGlobal('lid');
        $action = returnGlobal('action');

        if ($action == 'templateupload') {
            if (Yii::app()->getConfig('demoMode'))
                $this->getController()->error($clang->gT("Demo mode: Uploading templates is disabled."));

            Yii::app()->loadLibrary('admin.pclzip');

            $zip = new PclZip($_FILES['the_file']['tmp_name']);

            // Create temporary directory so that if dangerous content is unzipped it would be unaccessible
            $sNewDirectoryName=sanitize_dirname(pathinfo($_FILES['the_file']['name'], PATHINFO_FILENAME ));
            $destdir = Yii::app()->getConfig('usertemplaterootdir').DIRECTORY_SEPARATOR.$sNewDirectoryName;

            if (!is_writeable(dirname($destdir)))
                $this->getController()->error(sprintf($clang->gT("Incorrect permissions in your %s folder."), dirname($destdir)));

            if (!is_dir($destdir))
                mkdir($destdir);
            else
                $this->getController()->error(sprintf($clang->gT("Template '%s' does already exist."), $sNewDirectoryName));

            $aImportedFilesInfo = array();
            $aErrorFilesInfo = array();



            if (is_file($_FILES['the_file']['tmp_name'])) {
                $aExtractResult=$zip->extract(PCLZIP_OPT_PATH, $destdir, PCLZIP_CB_PRE_EXTRACT, 'templateExtractFilter');
                if ($aExtractResult==0)
                {
                    $this->getController()->error($clang->gT("This file is not a valid ZIP file archive. Import failed."));

                }
                else
                {

                    foreach($aExtractResult as $sFile)
                    {
                        $aImportedFilesInfo[] = Array(
                            "filename" => $sFile['stored_filename'],
                            "status" => $clang->gT("OK"),
                            'is_folder' => $sFile['folder']
                        );                    
                    }
                }

                if (count($aErrorFilesInfo) == 0 && count($aImportedFilesInfo) == 0)
                    $this->getController()->error($clang->gT("This ZIP archive contains no valid template files. Import failed."));
            }
            else
                $this->getController()->error(sprintf($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."), $basedestdir));
                
                
            if (count($aImportedFilesInfo) > 0)
            {
                $templateFixes= $this->_templateFixes($sNewDirectoryName);
            }
            else
            {
                $templateFixes= array();
            }
            $aViewUrls = 'importuploaded_view';
            $aData = array(
            'aImportedFilesInfo' => $aImportedFilesInfo,
            'aErrorFilesInfo' => $aErrorFilesInfo,
            'lid' => $lid,
            'newdir' => $sNewDirectoryName,
            'templateFixes' => $templateFixes,
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
    * Try to correct a template with new funcyionnality.
    *
    * @access private
    * @param string $templatename
    * @return array $correction ($success,$number,array($information))
    */

    private function _templateFixes($templatename)
    {
        $clang = $this->getController()->lang;
        $usertemplaterootdir=Yii::app()->getConfig("usertemplaterootdir");
        $templateFixes=array();
        $templateFixes['success']=true;
        $templateFixes['details']=array();
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
                    $templateFixes['details']['templatejs']=$clang->gT("Unable to add {TEMPLATEJS} placeholder, please check your startpage.pstpl.");
                }
                else
                {
                    $templateFixes['details']['templatejs']=$clang->gT("Placeholder {TEMPLATEJS} added to your startpage.pstpl.");
                }
            }
        }
        else
        {
            $templateFixes['success']=false;
            $templateFixes['details']['templatejs']=$clang->gT("Unable to find startpage.pstpl to add {TEMPLATEJS} placeholder, please check your template.");
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
        if (!Permission::model()->hasGlobalPermission('templates','import'))
        {
            die('No permission');
        }
        
        $clang = $this->getController()->lang;
        $action = returnGlobal('action');
        $editfile = returnGlobal('editfile');
        $templatename = returnGlobal('templatename');
        $screenname = returnGlobal('screenname');
        $files = $this->_initfiles($templatename);
        $cssfiles = $this->_initcssfiles();
        $basedestdir = Yii::app()->getConfig('usertemplaterootdir');
        $tempdir = Yii::app()->getConfig('tempdir');
        $allowedtemplateuploads=Yii::app()->getConfig('allowedtemplateuploads');
        $filename=sanitize_filename($_FILES['upload_file']['name'],false,false);// Don't force lowercase or alphanumeric
        $fullfilepath=$basedestdir."/".$templatename . "/" . $filename;

        if($action=="templateuploadfile")
        {
            if(Yii::app()->getConfig('demoMode'))
            {
                $uploadresult = $clang->gT("Demo mode: Uploading template files is disabled.");
            }
            elseif($filename!=$_FILES['upload_file']['name'])
            {
                $uploadresult = $clang->gT("This filename is not allowed to be uploaded.");
            }
            elseif(!in_array(strtolower(substr(strrchr($filename, '.'),1)),explode ( "," , $allowedtemplateuploads )))
            {

                $uploadresult = $clang->gT("This file type is not allowed to be uploaded.");
            }
            else
            {
                  //Uploads the file into the appropriate directory
                   if (!@move_uploaded_file($_FILES['upload_file']['tmp_name'], $fullfilepath)) {
                        $uploadresult = sprintf($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),$tempdir);
                   }
                   else
                   {
                        $uploadresult = sprintf($clang->gT("File %s uploaded"),$filename);
                   }
            }
            Yii::app()->session['flashmessage'] = $uploadresult;
        }
        $this->getController()->redirect(array("admin/templates/sa/view/editfile/" . $editfile . "/screenname/" . $screenname . "/templatename/" . $templatename));
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
        App()->getClientScript()->registerPackage('jquery-superfish');
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
        $this->getController()->redirect(array("admin/templates/sa/view/editfile/" . $editfile . "/screenname/" . $screenname . "/templatename/" . $templatename));
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
        $this->getController()->redirect(array("admin/templates/sa/view/editfile/" . $editfile . "/screenname/" . $screenname . "/templatename/" . $templatename));
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
        $clang = $this->getController()->lang;
        if (returnGlobal('action') == "templatefiledelete") {
            // This is where the temp file is
            $sFileToDelete=preg_replace("[^\w\s\d\.\-_~,;:\[\]\(\]]", '', returnGlobal('otherfile'));

            $the_full_file_path = Yii::app()->getConfig('usertemplaterootdir') . "/" . $_POST['templatename'] . "/" . $sFileToDelete;
            if (@unlink($the_full_file_path))
            {
                Yii::app()->session['flashmessage'] = sprintf($clang->gT("The file %s was deleted."), htmlspecialchars($sFileToDelete));
            }
            else
            {
                Yii::app()->session['flashmessage'] = sprintf($clang->gT("File %s couldn't be deleted. Please check the permissions on the /upload/template folder"), htmlspecialchars($sFileToDelete));
            }
            $this->getController()->redirect(array("admin/templates/sa/view/editfile/" . returnGlobal('editfile') . "/screenname/" . returnGlobal('screenname') . "/templatename/" . returnGlobal('templatename')));
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
        if (!Permission::model()->hasGlobalPermission('templates','update'))
        {
            die('No permission');
        }
        if (returnGlobal('action') == "templaterename" && returnGlobal('newname') && returnGlobal('copydir')) {
            $clang = Yii::app()->lang;
            $sOldName = sanitize_dirname(returnGlobal('copydir'));
            $sNewName = sanitize_dirname(returnGlobal('newname'));
            $sNewDirectoryPath = Yii::app()->getConfig('usertemplaterootdir') . "/" . $sNewName;
            $sOldDirectoryPath = Yii::app()->getConfig('usertemplaterootdir') . "/" . returnGlobal('copydir');
            if (isStandardTemplate(returnGlobal('newname')))
                $this->getController()->error(sprintf($clang->gT("Template could not be renamed to `%s`.", "js"), $sNewName) . " " . $clang->gT("This name is reserved for standard template.", "js"));
            elseif (file_exists($sNewDirectoryPath))
                $this->getController()->error(sprintf($clang->gT("Template could not be renamed to `%s`.", "js"), $sNewName) . " " . $clang->gT("A template with that name already exists.", "js"));
            elseif (rename($sOldDirectoryPath, $sNewDirectoryPath) == false)
                $this->getController()->error(sprintf($clang->gT("Template could not be renamed to `%s`.", "js"), $sNewName) . " " . $clang->gT("Maybe you don't have permission.", "js"));
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
        $clang = $this->getController()->lang;
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
                Yii::app()->setFlashMessage(sprintf($clang->gT("Directory with the name `%s` already exists - choose another name"), $newname),'error');
                $this->getController()->redirect(array("admin/templates/sa/view",'templatename'=>$copydir));
            }
            else
            {
                Yii::app()->setFlashMessage(sprintf($clang->gT("Unable to create directory `%s`."), $newname),'error');
                Yii::app()->setFlashMessage($clang->gT("Please check the directory permissions."));
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
        if (is_template_editable($templatename) == true) {
            $clang = $this->getController()->lang;

            if (rmdirr(Yii::app()->getConfig('usertemplaterootdir') . "/" . $templatename) == true) {
                $surveys = Survey::model()->findAllByAttributes(array('template' => $templatename));
                foreach ($surveys as $s)
                {
                    $s->template = Yii::app()->getConfig('defaulttemplate');
                    $s->save();
                }

                Template::model()->deleteAllByAttributes(array('folder' => $templatename));
                Permission::model()->deleteAllByAttributes(array('permission' => $templatename,'entity' => 'template'));

                Yii::app()->setFlashMessage(sprintf($clang->gT("Template '%s' was successfully deleted."), $templatename));
            }
            else
                Yii::app()->setFlashMessage(sprintf($clang->gT("There was a problem deleting the template '%s'. Please check your directory/file permissions."), $templatename),'error');
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

        $this->getController()->redirect(array("admin/templates/sa/view/editfile/" . $editfile . "/screenname/" . $screenname . "/templatename/" . $templatename));
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
        $aData['clang'] = $this->getController()->lang;
        $aData['screenname'] = $screenname;
        $aData['editfile'] = $editfile;
        $aData['screens'] = $screens;
        $aData['tempdir'] = $tempdir;
        $aData['templatename'] = $templatename;
        $aData['usertemplaterootdir'] = Yii::app()->getConfig('usertemplaterootdir');

        $this->getController()->render("/admin/templates/templatebar_view", $aData);
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
    protected function _templatesummary($templatename, $screenname, $editfile, $templates, $files, $cssfiles, $otherfiles, $myoutput)
    {
        $tempdir = Yii::app()->getConfig("tempdir");
        $tempurl = Yii::app()->getConfig("tempurl");

        Yii::app()->loadHelper("admin/template");
        $aData = array();
        $time = date("ymdHis");

        // Prepare textarea class for optional javascript
        $templateclasseditormode = getGlobalSetting('defaulttemplateeditormode'); // default
        if (Yii::app()->session['templateeditormode'] == 'none')
            $templateclasseditormode = 'none';

        $aData['templateclasseditormode'] = $templateclasseditormode;

        // The following lines are forcing the browser to refresh the templates on each save
        @$fnew = fopen("$tempdir/template_temp_$time.html", "w+");
        $aData['time'] = $time;

        if (!$fnew) {
            $aData['filenotwritten'] = true;
        }
        else
        {
            @fwrite($fnew, getHeader());
            foreach ($cssfiles as $cssfile)
            {
                $myoutput = str_replace($cssfile['name'], $cssfile['name'] . "?t=$time", $myoutput);
            }
            $myoutput = implode("\n", $myoutput);

            App()->getClientScript()->registerPackage('jqueryui');
            App()->getClientScript()->registerPackage('jquery-touch-punch');
            App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."survey_runtime.js");            
            
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


        $aData['clang'] = $this->getController()->lang;
        $aData['screenname'] = $screenname;
        $aData['editfile'] = $editfile;

        $aData['tempdir'] = $tempdir;
        $aData['templatename'] = $templatename;
        $aData['templates'] = $templates;
        $aData['files'] = $files;
        $aData['cssfiles'] = $cssfiles;
        $aData['otherfiles'] = $otherfiles;
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
    * @param mixed $templatename
    * @return void
    */
    protected function _initfiles($templatename)
    {
        $files[] = array('name' => 'assessment.pstpl');
        $files[] = array('name' => 'clearall.pstpl');
        $files[] = array('name' => 'completed.pstpl');
        $files[] = array('name' => 'endgroup.pstpl');
        $files[] = array('name' => 'endpage.pstpl');
        $files[] = array('name' => 'groupdescription.pstpl');
        $files[] = array('name' => 'load.pstpl');
        $files[] = array('name' => 'navigator.pstpl');
        $files[] = array('name' => 'printanswers.pstpl');
        $files[] = array('name' => 'privacy.pstpl');
        $files[] = array('name' => 'question.pstpl');
        $files[] = array('name' => 'register.pstpl');
        $files[] = array('name' => 'save.pstpl');
        $files[] = array('name' => 'surveylist.pstpl');
        $files[] = array('name' => 'startgroup.pstpl');
        $files[] = array('name' => 'startpage.pstpl');
        $files[] = array('name' => 'survey.pstpl');
        $files[] = array('name' => 'welcome.pstpl');
        $files[] = array('name' => 'print_survey.pstpl');
        $files[] = array('name' => 'print_group.pstpl');
        $files[] = array('name' => 'print_question.pstpl');

        if (is_file(Yii::app()->getConfig('usertemplaterootdir') . '/' . $templatename . '/question_start.pstpl'))
            $files[] = array('name' => 'question_start.pstpl');

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
        $cssfiles[] = array('name' => 'template.css');
        $cssfiles[] = array('name' => 'template-rtl.css');
        $cssfiles[] = array('name' => 'ie_fix_6.css');
        $cssfiles[] = array('name' => 'ie_fix_7.css');
        $cssfiles[] = array('name' => 'ie_fix_8.css');
        $cssfiles[] = array('name' => 'jquery-ui-custom.css');
        $cssfiles[] = array('name' => 'print_template.css');
        $cssfiles[] = array('name' => 'template.js');

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
        $clang = $this->getController()->lang;
        Yii::app()->loadHelper('surveytranslator');
        Yii::app()->loadHelper('admin/template');

        $files = $this->_initfiles($templatename);

        $cssfiles = $this->_initcssfiles();

        // Standard Support Files
        // These files may be edited or saved
        $supportfiles[] = array('name' => 'print_img_radio.png');
        $supportfiles[] = array('name' => 'print_img_checkbox.png');

        // Standard screens
        // Only these may be viewed
        $screens[] = array('name' => $clang->gT('Survey List Page'), 'id' => 'surveylist');
        $screens[] = array('name' => $clang->gT('Welcome Page'), 'id' => 'welcome');
        $screens[] = array('name' => $clang->gT('Question Page'), 'id' => 'question');
        $screens[] = array('name' => $clang->gT('Completed Page'), 'id' => 'completed');
        $screens[] = array('name' => $clang->gT('Clear All Page'), 'id' => 'clearall');
        $screens[] = array('name' => $clang->gT('Register Page'), 'id' => 'register');
        $screens[] = array('name' => $clang->gT('Load Page'), 'id' => 'load');
        $screens[] = array('name' => $clang->gT('Save Page'), 'id' => 'save');
        $screens[] = array('name' => $clang->gT('Print answers page'), 'id' => 'printanswers');
        $screens[] = array('name' => $clang->gT('Printable survey page'), 'id' => 'printablesurvey');

        // Page display blocks
        $SurveyList = array('startpage.pstpl',
        'surveylist.pstpl',
        'endpage.pstpl'
        );
        $Welcome = array('startpage.pstpl',
        'welcome.pstpl',
        'privacy.pstpl',
        'navigator.pstpl',
        'endpage.pstpl'
        );
        $Question = array('startpage.pstpl',
        'survey.pstpl',
        'startgroup.pstpl',
        'groupdescription.pstpl',
        'question.pstpl',
        'endgroup.pstpl',
        'navigator.pstpl',
        'endpage.pstpl'
        );
        $CompletedTemplate = array(
        'startpage.pstpl',
        'assessment.pstpl',
        'completed.pstpl',
        'endpage.pstpl'
        );
        $Clearall = array('startpage.pstpl',
        'clearall.pstpl',
        'endpage.pstpl'
        );
        $Register = array('startpage.pstpl',
        'survey.pstpl',
        'register.pstpl',
        'endpage.pstpl'
        );
        $Save = array('startpage.pstpl',
        'save.pstpl',
        'endpage.pstpl'
        );
        $Load = array('startpage.pstpl',
        'load.pstpl',
        'endpage.pstpl'
        );
        $printtemplate = array('startpage.pstpl',
        'printanswers.pstpl',
        'endpage.pstpl'
        );
        $printablesurveytemplate = array('print_survey.pstpl',
        'print_group.pstpl',
        'print_question.pstpl'
        );

        $file_version = "LimeSurvey template editor " . Yii::app()->getConfig('versionnumber');
        Yii::app()->session['s_lang'] = Yii::app()->session['adminlang'];

        $templatename = sanitize_dirname($templatename);
        $screenname = autoUnescape($screenname);

        // Checks if screen name is in the list of allowed screen names
        if (multiarray_search($screens, 'id', $screenname) === false)
            $this->getController()->error('Invalid screen name');

        if (!isset($action))
            $action = sanitize_paranoid_string(returnGlobal('action'));

        if (!isset($subaction))
            $subaction = sanitize_paranoid_string(returnGlobal('subaction'));

        if (!isset($newname))
            $newname = sanitize_dirname(returnGlobal('newname'));

        if (!isset($copydir))
            $copydir = sanitize_dirname(returnGlobal('copydir'));

        if (is_file(Yii::app()->getConfig('usertemplaterootdir') . '/' . $templatename . '/question_start.pstpl')) {
            $files[] = array('name' => 'question_start.pstpl');
            $Question[] = 'question_start.pstpl';
        }

        $availableeditorlanguages = array('bg', 'cs', 'de', 'dk', 'en', 'eo', 'es', 'fi', 'fr', 'hr', 'it', 'ja', 'mk', 'nl', 'pl', 'pt', 'ru', 'sk', 'zh');
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

        $normalfiles = array("DUMMYENTRY", ".", "..", "preview.png");
        foreach ($files as $fl)
            $normalfiles[] = $fl["name"];

        foreach ($cssfiles as $fl)
            $normalfiles[] = $fl["name"];

        // Some global data
        $aData['sitename'] = Yii::app()->getConfig('sitename');
        $siteadminname = Yii::app()->getConfig('siteadminname');
        $siteadminemail = Yii::app()->getConfig('siteadminemail');

        // Set this so common.php doesn't throw notices about undefined variables
        $thissurvey['active'] = 'N';

        // FAKE DATA FOR TEMPLATES
        $thissurvey['name'] = $clang->gT("Template Sample");
        $thissurvey['description'] =
            "<p>".$clang->gT('This is a sample survey description. It could be quite long.')."</p>".
            "<p>".$clang->gT("But this one isn't.")."<p>";
        $thissurvey['welcome'] =
            "<p>".$clang->gT('Welcome to this sample survey')."<p>" .
            "<p>".$clang->gT('You should have a great time doing this')."<p>";
        $thissurvey['allowsave'] = "Y";
        $thissurvey['active'] = "Y";
        $thissurvey['tokenanswerspersistence'] = "Y";
        $thissurvey['templatedir'] = $templatename;
        $thissurvey['format'] = "G";
        $thissurvey['surveyls_url'] = "http://www.limesurvey.org/";
        $thissurvey['surveyls_urldescription'] = $clang->gT("Some URL description");
        $thissurvey['usecaptcha'] = "A";
        $percentcomplete = makegraph(6, 10);

        $groupname = $clang->gT("Group 1: The first lot of questions");
        $groupdescription = $clang->gT("This group description is fairly vacuous, but quite important.");

        $navigator = $this->getController()->render('/admin/templates/templateeditor_navigator_view', array(
        'screenname' => $screenname,
        'clang' => $clang,
        ), true);

        $completed = $this->getController()->render('/admin/templates/templateeditor_completed_view', array(
        'clang' => $clang,
        ), true);

        $assessments = $this->getController()->render('/admin/templates/templateeditor_assessments_view', array(
        'clang' => $clang,
        ), true);

        $printoutput = $this->getController()->render('/admin/templates/templateeditor_printoutput_view', array(
        'clang' => $clang
        ), true);

        $totalquestions = '10';
        $surveyformat = 'Format';
        $notanswered = '5';
        $privacy = '';
        $surveyid = '1295';
        $token = 1234567;

        $templatedir = getTemplatePath($templatename);
        $templateurl = getTemplateURL($templatename);

        // Save these variables in an array
        $aData['thissurvey'] = $thissurvey;
        $aData['percentcomplete'] = $percentcomplete;
        $aData['groupname'] = $groupname;
        $aData['groupdescription'] = $groupdescription;
        $aData['navigator'] = $navigator;
        $aData['help'] = $clang->gT("This is some help text.");
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

        $myoutput[] = "";
        switch ($screenname)
        {
            case 'surveylist':
                unset($files);
                $surveylist = array(
                "nosid" => $clang->gT("You have not provided a survey identification number"),
                "contact" => sprintf($clang->gT("Please contact %s ( %s ) for further assistance."), Yii::app()->getConfig("siteadminname"), Yii::app()->getConfig("siteadminemail")),
                "listheading" => $clang->gT("The following surveys are available:"),
                "list" => $this->getController()->render('/admin/templates/templateeditor_surveylist_view', array(), true),
                );
                $aData['surveylist'] = $surveylist;

                $myoutput[] = "";
                foreach ($SurveyList as $qs)
                {
                    $files[] = array("name" => $qs);
                    $myoutput = array_merge($myoutput, doreplacement(getTemplatePath($templatename) . "/$qs", $aData));
                }
                break;

            case 'question':
                unset($files);
                foreach ($Question as $qs)
                    $files[] = array("name" => $qs);

                $myoutput[] = $this->getController()->render('/admin/templates/templateeditor_question_meta_view', array('clang' => $clang), true);
                $myoutput = array_merge($myoutput, doreplacement(getTemplatePath($templatename) . "/startpage.pstpl", $aData));
                $myoutput = array_merge($myoutput, doreplacement(getTemplatePath($templatename) . "/survey.pstpl", $aData));
                $myoutput = array_merge($myoutput, doreplacement(getTemplatePath($templatename) . "/startgroup.pstpl", $aData));
                $myoutput = array_merge($myoutput, doreplacement(getTemplatePath($templatename) . "/groupdescription.pstpl", $aData));

                $question = array(
                'all' => $clang->gT("How many roads must a man walk down?"),// Still in use ?
                'text' => $clang->gT("How many roads must a man walk down?"),
                'code' => '1a',
                'help' => 'helpful text',
                'mandatory' => $clang->gT("*"),
                'man_class' => ' mandatory',
                'man_message' => '',
                'valid_message' => '',
                'file_valid_message' => '',
                'essentials' => 'id="question1"',
                'class' => 'list-radio',
                'input_error_class' => '',
                'number' => '1',
                'type' => 'L'
                );
                $aData['question'] = $question;

                $answer = $this->getController()->render('/admin/templates/templateeditor_question_answer_view', array('clang' => $clang), true);
                $aData['answer'] = $answer;
                $myoutput = array_merge($myoutput, doreplacement(getTemplatePath($templatename) . "/question.pstpl", $aData));

                $answer = $this->getController()->render('/admin/templates/templateeditor_question_answer_view', array('alt' => true,'clang' => $clang), true);
                $aData['answer'] = $answer;
                $question = array(
                'all' => $clang->gT("Please explain something in detail:"),// Still in use ?
                'text' => $clang->gT('Please explain something in detail:'),
                'code' => '2a',
                'help' => '',
                'mandatory' => '',
                'man_message' => '',
                'valid_message' => '',
                'file_valid_message' => '',
                'essentials' => 'id="question2"',
                'class' => 'text-long',
                'man_class' => 'mandatory',
                'input_error_class' => '',
                'number' => '2',
                'type' => 'T'
                );
                $aData['question'] = $question;
                $myoutput = array_merge($myoutput, doreplacement(getTemplatePath($templatename) . "/question.pstpl", $aData));
                $myoutput = array_merge($myoutput, doreplacement(getTemplatePath($templatename) . "/endgroup.pstpl", $aData));
                $myoutput = array_merge($myoutput, doreplacement(getTemplatePath($templatename) . "/navigator.pstpl", $aData));
                $myoutput = array_merge($myoutput, doreplacement(getTemplatePath($templatename) . "/endpage.pstpl", $aData));
                break;

            case 'welcome':
                unset($files);
                $myoutput[] = "";
                foreach ($Welcome as $qs)
                {
                    $files[] = array("name" => $qs);
                    $myoutput = array_merge($myoutput, doreplacement(getTemplatePath($templatename) . "/$qs", $aData));
                }
                break;

            case 'register':
                unset($files);
                foreach ($Register as $qs)
                    $files[] = array("name" => $qs);

                $myoutput[] = templatereplace(file_get_contents("$templatedir/startpage.pstpl"), array(), $aData);
                $myoutput[] = templatereplace(file_get_contents("$templatedir/survey.pstpl"), array(), $aData);
                $myoutput[] = templatereplace(file_get_contents("$templatedir/register.pstpl"), array(), $aData);
                $myoutput[] = templatereplace(file_get_contents("$templatedir/endpage.pstpl"), array(), $aData);
                $myoutput[] = "\n";
                break;

            case 'save':
                unset($files);
                foreach ($Save as $qs)
                    $files[] = array("name" => $qs);

                $myoutput[] = templatereplace(file_get_contents("$templatedir/startpage.pstpl"), array(), $aData);
                $myoutput[] = templatereplace(file_get_contents("$templatedir/save.pstpl"), array(), $aData);
                $myoutput[] = templatereplace(file_get_contents("$templatedir/endpage.pstpl"), array(), $aData);
                $myoutput[] = "\n";
                break;

            case 'load':
                unset($files);
                foreach ($Load as $qs)
                    $files[] = array("name" => $qs);

                $myoutput[] = templatereplace(file_get_contents("$templatedir/startpage.pstpl"), array(), $aData);
                $myoutput[] = templatereplace(file_get_contents("$templatedir/load.pstpl"), array(), $aData);
                $myoutput[] = templatereplace(file_get_contents("$templatedir/endpage.pstpl"), array(), $aData);
                $myoutput[] = "\n";
                break;

            case 'clearall':
                unset($files);
                foreach ($Clearall as $qs)
                    $files[] = array("name" => $qs);

                $myoutput[] = templatereplace(file_get_contents("$templatedir/startpage.pstpl"), array(), $aData);
                $myoutput[] = templatereplace(file_get_contents("$templatedir/clearall.pstpl"), array(), $aData);
                $myoutput[] = templatereplace(file_get_contents("$templatedir/endpage.pstpl"), array(), $aData);
                $myoutput[] = "\n";
                break;

            case 'completed':
                unset($files);
                $myoutput[] = "";
                foreach ($CompletedTemplate as $qs)
                {
                    $files[] = array("name" => $qs);
                    $myoutput = array_merge($myoutput, doreplacement(getTemplatePath($templatename) . "/$qs", $aData));
                }
                break;

            case 'printablesurvey':
                unset($files);
                foreach ($printablesurveytemplate as $qs)
                {
                    $files[] = array("name" => $qs);
                }

                $questionoutput = array();
                foreach (file("$templatedir/print_question.pstpl") as $op)
                {
                    $questionoutput[] = templatereplace($op, array(
                    'QUESTION_NUMBER' => '1',
                    'QUESTION_CODE' => 'Q1',
                    'QUESTION_MANDATORY' => $clang->gT('*'),
                    // If there are conditions on a question, list the conditions.
                    'QUESTION_SCENARIO' => 'Only answer this if certain conditions are met.',
                    'QUESTION_CLASS' => ' mandatory list-radio',
                    'QUESTION_TYPE_HELP' => $clang->gT('Please choose *only one* of the following:'),
                    // (not sure if this is used) mandatory error
                    'QUESTION_MAN_MESSAGE' => '',
                    // (not sure if this is used) validation error
                    'QUESTION_VALID_MESSAGE' => '',
                    // (not sure if this is used) file validation error
                    'QUESTION_FILE_VALID_MESSAGE' => '',
                    'QUESTION_TEXT' => $clang->gT('This is a sample question text. The user was asked to pick an entry.'),
                    'QUESTIONHELP' => $clang->gT('This is some help text for this question.'),
                    'ANSWER' =>
                    $this->getController()->render('/admin/templates/templateeditor_printablesurvey_quesanswer_view', array(
                    'templateurl' => $templateurl,
                    'clang' => $clang
                    ), true),
                    ), $aData);
                }
                $groupoutput = array();
                $groupoutput[] = templatereplace(file_get_contents("$templatedir/print_group.pstpl"), array('QUESTIONS' => implode(' ', $questionoutput)), $aData);

                $myoutput[] = templatereplace(file_get_contents("$templatedir/print_survey.pstpl"), array('GROUPS' => implode(' ', $groupoutput),
                'FAX_TO' => $clang->gT("Please fax your completed survey to:") . " 000-000-000",
                'SUBMIT_TEXT' => $clang->gT("Submit your survey."),
                'HEADELEMENTS' => getPrintableHeader(),
                'SUBMIT_BY' => sprintf($clang->gT("Please submit by %s"), date('d.m.y')),
                'THANKS' => $clang->gT('Thank you for completing this survey.'),
                'END' => $clang->gT('This is the survey end message.')
                ), $aData);
                break;

            case 'printanswers':
                unset($files);
                foreach ($printtemplate as $qs)
                {
                    $files[] = array("name" => $qs);
                }

                $myoutput[] = templatereplace(file_get_contents("$templatedir/startpage.pstpl"), array(), $aData);
                $myoutput[] = templatereplace(file_get_contents("$templatedir/printanswers.pstpl"), array('ANSWERTABLE' => $printoutput), $aData);
                $myoutput[] = templatereplace(file_get_contents("$templatedir/endpage.pstpl"), array(), $aData);

                $myoutput[] = "\n";
                break;
        }
        $myoutput[] = "</html>";

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
        $otherfiles = array();
        if ($handle = opendir($templatedir)) {
            while (false !== ($file = readdir($handle)))
            {
                if (!array_search($file, $normalfiles)) {
                    if (!is_dir($templatedir . DIRECTORY_SEPARATOR . $file)) {
                        $otherfiles[] = array("name" => $file);
                    }
                }
            }

            closedir($handle);
        }

        $aData['clang'] = $this->getController()->lang;
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
            $aViewUrls = array_merge($aViewUrls, $this->_templatesummary($templatename, $screenname, $editfile, $templates, $files, $cssfiles, $otherfiles, $myoutput));

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
    protected function _renderWrappedTemplate($sAction = 'templates', $aViewUrls = array(), $aData = array())
    {
        $aData['display']['menu_bars'] = false;
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }
}
