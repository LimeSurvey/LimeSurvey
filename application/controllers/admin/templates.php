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
      $iTime= preg_replace("/[^0-9]$/", '', $id);
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

            $zip = new PclZip($_FILES['the_file']['tmp_name']);

            // Create temporary directory so that if dangerous content is unzipped it would be unaccessible
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
                $aExtractResult=$zip->extract(PCLZIP_OPT_PATH, $destdir);
                if ($aExtractResult===0)
                {
                    Yii::app()->user->setFlash('error',gT("This file is not a valid ZIP file archive. Import failed."));
                    $this->getController()->redirect(array("admin/templates/sa/upload"));
                }
                else
                {

                    foreach($aExtractResult as $sFile)
                    {
                        $aImportedFilesInfo[] = Array(
                            "filename" => $sFile['stored_filename'],
                            "status" => gT("OK"),
                            'is_folder' => $sFile['folder']
                        );
                    }
                }

                if (count($aErrorFilesInfo) == 0 && count($aImportedFilesInfo) == 0)
                {
                    Yii::app()->user->setFlash('error',gT("This ZIP archive contains no valid template files. Import failed."));
                    $this->getController()->redirect(array("admin/templates/sa/upload"));
                }
            }
            else
            {
                Yii::app()->user->setFlash('error',sprintf(gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."), $basedestdir));

                $this->getController()->redirect(array("admin/templates/sa/upload"));
            }



            if (count($aImportedFilesInfo) > 0)
            {
                // Not working with 2.5 templates...
                //$templateFixes= $this->_templateFixes($sNewDirectoryName);
                $templateFixes= array();
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
     * Try to correct a template with new functionality.
     *
     * @access private
     * @param string $templatename
     * @return array $correction ($success,$number,array($information))
     */
    private function _templateFixes($templatename)
    {
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
        if (!Permission::model()->hasGlobalPermission('templates','import'))
        {
            die('No permission');
        }

        $action = returnGlobal('action');
        $editfileindex = App()->request->getPost('editfileindex');
        $useindex = App()->request->getPost('useindex');
        $templatename = returnGlobal('templatename');
        $oEditedTemplate = Template::model()->getTemplateConfiguration($templatename);
        $templatedir = $oEditedTemplate->viewPath;
        $screenname = returnGlobal('screenname');
        $files = $this->_initfiles($templatename);
        $cssfiles = $this->_initcssfiles($oEditedTemplate);
        $basedestdir = Yii::app()->getConfig('usertemplaterootdir');
        $tempdir = Yii::app()->getConfig('tempdir');
        $allowedtemplateuploads=Yii::app()->getConfig('allowedtemplateuploads');
        $filename=sanitize_filename($_FILES['upload_file']['name'],false,false);// Don't force lowercase or alphanumeric

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
                $this->getController()->redirect(array('admin/templates/sa/view/editfile/'.$editfileindex.'/screenname/'.$screenname.'/templatename/'.$templatename.'/useindex/'.$useindex));
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
                        $uploadresult = sprintf(gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),$tempdir);
                   }
                   else
                   {
                        $uploadresult = sprintf(gT("File %s uploaded"),$filename);
                        $status='success';
                   }
            }
            Yii::app()->setFlashMessage($uploadresult,$status);
        }
        $this->getController()->redirect(array('admin/templates/sa/view/editfile/'.$editfileindex.'/screenname/'.$screenname.'/templatename/'.$templatename.'/useindex/'.$useindex));
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
    public function index($editfile = 'startpage.pstpl', $screenname = 'welcome', $templatename = '', $useindex=false)
    {
        if(!$templatename)
        {
            $templatename = Yii::app()->getConfig("defaulttemplate");
        }

        // This can happen if the global default template is deleted
        if (!Template::checkIfTemplateExists($templatename))
        {
            $templatename = 'default';
        }
        $aViewUrls = $this->_initialise($templatename, $screenname, $editfile, true, $useindex);
        App()->getClientScript()->reset();

        // After reseting, we need register again the script : maybe move it to endScripts_view for allways needed scripts ?
        App()->getClientScript()->registerScriptFile( App()->getAssetManager()->publish( ADMIN_SCRIPT_PATH . "admin_core.js" ));
        App()->getClientScript()->registerScriptFile( App()->getAssetManager()->publish( ADMIN_SCRIPT_PATH  . 'templates.js'));
        App()->getClientScript()->registerPackage('ace');
        $aData['fullpagebar']['returnbutton']=true;
        $this->_renderWrappedTemplate('templates', $aViewUrls, $aData);

        if ($screenname != 'welcome')
            Yii::app()->session['step'] = 1;
        // This helps handle the load/save buttons)
        else
            unset(Yii::app()->session['step']);
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
    public function fileredirect($templatename = '', $screenname = 'welcome', $editfile = 'startpage.pstpl', $useindex=false)
    {
        if(!$templatename)
        {
            $templatename = Yii::app()->getConfig("defaulttemplate");
        }
        $this->getController()->redirect(array("admin/templates/sa/view/editfile/" . $editfile . "/screenname/" . $screenname . "/templatename/" . $templatename . '/useindex/' . $useindex));
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
        if (returnGlobal('action') == "templatefiledelete")
        {
            // This is where the temp file is
            $sFileToDelete=sanitize_filename(returnGlobal('otherfile'),false,false);

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
            $editfileindex = App()->request->getPost('editfileindex');
            $useindex = App()->request->getPost('useindex');
            $this->getController()->redirect(array('admin/templates/sa/view/editfile/'.$editfileindex.'/screenname/'.returnGlobal('screenname').'/templatename/'.$sTemplateName.'/useindex/'.$useindex));
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
        $editfileindex   = App()->request->getPost('editfileindex');
        $useindex        = App()->request->getPost('useindex');
        $screenname      = returnGlobal('screenname');
        $oEditedTemplate = Template::model()->getTemplateConfiguration($sTemplateName);
        $files           = $this->_initfiles($sTemplateName);
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
                    multiarray_search($files, 'name', $editfile) === false &&
                    multiarray_search($cssfiles, 'name', $editfile) === false &&
                    multiarray_search($jsfiles, 'name', $editfile) === false
                )
                {
                    Yii::app()->user->setFlash('error',gT('Invalid template name'));
                    $this->getController()->redirect(array("admin/templates/sa/upload"));
                }

                $savefilename = gettemplatefilename(Yii::app()->getConfig('usertemplaterootdir') . "/" . $sTemplateName, $editfile);

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
        $this->getController()->redirect(array('admin/templates/sa/view/editfile/'.$editfileindex.'/screenname/'.$screenname.'/templatename/'.$sTemplateName.'/useindex/'.$useindex));
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
            App()->getClientScript()->registerScriptFile( App()->getAssetManager()->publish( SCRIPT_PATH."survey_runtime.js" ));

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
        $oEditedTemplate = Template::model()->getTemplateConfiguration($templatename);
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

        /// TODO : use config.xml
        if (is_file(Yii::app()->getConfig('usertemplaterootdir') . '/' . $templatename . '/question_start.pstpl'))
            $files[] = array('name' => 'question_start.pstpl');

        return $files;
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

        $aNamedCssFiles = array();
        foreach($aCssFiles as $file)
        {
            $aNamedCssFiles[] = array('name' => $file);
        }
        return $aNamedCssFiles;
    }

    protected function _getEditableJsFiles($oEditedTemplate)
    {

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

        $aNamedJsFiles = array();
        foreach($aJsFiles as $file)
        {
            $aNamedJsFiles[] = array('name' => $file);
        }
        return $aNamedJsFiles;
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
    protected function _initialise($templatename, $screenname, $editfile, $showsummary = true, $useindex=false)
    {
        // LimeSurvey style
        $oEditedTemplate = Template::model()->getTemplateConfiguration($templatename);

        // In survey mode, bootstrap is loaded via the app init.
        // From template editor, we just add the bootstrap files to the js/css to load for template_helper::templatereplace()
        if($oEditedTemplate->cssFramework=='bootstrap')
        {
            // Core templates (are published only if exists)
            $oEditedTemplate->config->files->css->filename[]="../../styles-public/bootstrap-for-template-editor.css";
            $oEditedTemplate->config->files->js->filename[]="../../scripts/bootstrap-for-template-editor.js";

            // User templates (are published only if exists)
            $oEditedTemplate->config->files->css->filename[]="../../../styles-public/bootstrap-for-template-editor.css";
            $oEditedTemplate->config->files->js->filename[]="../../../scripts/bootstrap-for-template-editor.js";
        }

        //App()->getClientScript()->reset();
        Yii::app()->loadHelper('surveytranslator');
        Yii::app()->loadHelper('admin/template');
        $files = $this->_initfiles($templatename);

        $cssfiles = $this->_initcssfiles($oEditedTemplate);


        // Standard Support Files
        // These files may be edited or saved
        $supportfiles[] = array('name' => 'print_img_radio.png');
        $supportfiles[] = array('name' => 'print_img_checkbox.png');

        // Standard screens
        // Only these may be viewed
        $screens[] = array('name' => gT('Survey List Page'), 'id' => 'surveylist');
        $screens[] = array('name' => gT('Welcome Page'), 'id' => 'welcome');
        $screens[] = array('name' => gT('Question Page'), 'id' => 'question');
        $screens[] = array('name' => gT('Completed Page'), 'id' => 'completed');
        $screens[] = array('name' => gT('Clear All Page'), 'id' => 'clearall');
        $screens[] = array('name' => gT('Register Page'), 'id' => 'register');
        $screens[] = array('name' => gT('Load Page'), 'id' => 'load');
        $screens[] = array('name' => gT('Save Page'), 'id' => 'save');
        $screens[] = array('name' => gT('Print answers page'), 'id' => 'printanswers');
        $screens[] = array('name' => gT('Printable survey page'), 'id' => 'printablesurvey');

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
        {
            Yii::app()->user->setFlash('error',gT('Invalid screen name'));

            $this->getController()->redirect(array("admin/templates/sa/upload"));
        }

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

        $editfile=sanitize_filename($editfile); // Fixed with editable file after, but put in aData before fix
        $availableeditorlanguages = array('bg', 'cs', 'de', 'dk', 'en', 'eo', 'es', 'fi', 'fr', 'hr', 'it', 'ja', 'mk', 'nl', 'pl', 'pt', 'ru', 'sk', 'zh');
        // 2.06 way of doing.
        if(!$useindex)
        {
            $extension = substr(strrchr($editfile, "."), 1);
        }
        // 2.5
        else
        {
            // The extension is now set as a prefix separated by a _
            $file_datas = explode("_", $editfile);
            $extension = $file_datas[0];

            // The file name is now based on the index of the oTemplate files
            $file_index = $file_datas[1];
            switch($extension)
            {
                case 'css':
                    $aTemplateFiles = (array) $oEditedTemplate->config->files_editable->css->filename;
                break;

                case 'js':
                    $aTemplateFiles = (array) $oEditedTemplate->config->files_editable->js->filename;
                break;

                default:
                    $aTemplateFiles = (array) $oEditedTemplate->config->files_editable->css->filename;
                break;
            }
            $editfile = $aTemplateFiles[$file_index];
        }

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
        $percentcomplete = makegraph(6, 10);

        $groupname = gT("Group 1: The first lot of questions");
        $groupdescription = gT("This group description is fairly vacuous, but quite important.");

        $navigator = $this->getController()->render('/admin/templates/templateeditor_navigator_view', array(
        'screenname' => $screenname
        ), true);

        $completed = $this->getController()->render('/admin/templates/templateeditor_completed_view', array(), true);

        $assessments = $this->getController()->render('/admin/templates/templateeditor_assessments_view', array(), true);

        $printoutput = $this->getController()->render('/admin/templates/templateeditor_printoutput_view', array(), true);

        $totalquestions = '10';
        $surveyformat = 'Format';
        $notanswered = '5';
        $privacy = '';
        $surveyid = '1295';
        $token = 1234567;

        $templatedir = $oEditedTemplate->viewPath;
        $templateurl = getTemplateURL($templatename);

        // Save these variables in an array
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

        $myoutput[] = "";

        switch ($screenname)
        {
            case 'surveylist':
                unset($files);
                $surveylist = array(
                "nosid" => gT("You have not provided a survey identification number"),
                "contact" => sprintf(gT("Please contact %s ( %s ) for further assistance."), Yii::app()->getConfig("siteadminname"), Yii::app()->getConfig("siteadminemail")),
                "listheading" => gT("The following surveys are available:"),
                "list" => $this->getController()->render('/admin/templates/templateeditor_surveylist_view', array(), true),
                );
                $aData['surveylist'] = $surveylist;

                $myoutput[] = "";
                //$myoutput[] = templatereplace(file_get_contents("$templatedir/startpage.pstpl"), array(), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);
                foreach ($SurveyList as $qs)
                {
                    $files[] = array("name" => $qs);
                    $myoutput = array_merge($myoutput, doreplacement($oEditedTemplate->viewPath . "/$qs", $aData, $oEditedTemplate));
                }
                break;

            case 'question':
                unset($files);
                foreach ($Question as $qs)
                    $files[] = array("name" => $qs);

                $myoutput[] = $this->getController()->render('/admin/templates/templateeditor_question_meta_view', array(), true);

                $aData['aReplacements'] = array(
                    'SAVE_LINKS' => '<li><a href="#" id="saveallbtnlink">Resume later</a></li>',
                    'CLEARALL_LINKS' => '<li><a href="#" id="clearallbtnlink">'.gT("Exit and clear survey").'</a></li>'
                );
                $myoutput = array_merge($myoutput, doreplacement($oEditedTemplate->viewPath . "/startpage.pstpl", $aData, $oEditedTemplate));
                $myoutput = array_merge($myoutput, doreplacement($oEditedTemplate->viewPath . "/survey.pstpl", $aData, $oEditedTemplate));
                $myoutput = array_merge($myoutput, doreplacement($oEditedTemplate->viewPath . "/startgroup.pstpl", $aData, $oEditedTemplate));
                $myoutput = array_merge($myoutput, doreplacement($oEditedTemplate->viewPath . "/groupdescription.pstpl", $aData, $oEditedTemplate));

                $aReplacements = array(
                'QUESTION_TEXT' => gT("How many roads must a man walk down?"),
                'QUESTION_CODE' => '1a',
                'QUESTIONHELP' => 'helpful text',
                'QUESTION_MANDATORY' => gT("*"),
                'QUESTION_MAN_CLASS' => ' mandatory',
                'QUESTION_ESSENTIALS' => 'id="question1"',
                'QUESTION_CLASS' => 'list-radio',
                'QUESTION_NUMBER' => '1',
                );
                $aReplacements['ANSWER'] = $this->getController()->render('/admin/templates/templateeditor_question_answer_view', array(), true);
                $aData['aReplacements'] = $aReplacements;
                $myoutput = array_merge($myoutput, doreplacement($oEditedTemplate->viewPath . "/question.pstpl", $aData, $oEditedTemplate));

                $aReplacements = array(
                'QUESTION_TEXT' => gT('Please explain something in detail:'),
                'QUESTION_CODE' => '2a',
                'QUESTION_ESSENTIALS' => 'id="question2"',
                'QUESTION_CLASS' => 'text-long',
                'QUESTION_NUMBER' => '2',
                );
                $aReplacements['ANSWER'] = $this->getController()->render('/admin/templates/templateeditor_question_answer_view', array('alt' => true), true);
                $aData['aReplacements'] = $aReplacements;
                $myoutput = array_merge($myoutput, doreplacement($oEditedTemplate->viewPath . "/question.pstpl", $aData, $oEditedTemplate));

                $myoutput = array_merge($myoutput, doreplacement($oEditedTemplate->viewPath . "/endgroup.pstpl", $aData, $oEditedTemplate));

                $aData['aReplacements'] = array(
                    'MOVEPREVBUTTON' => '<button type="submit" id="moveprevbtn" value="moveprev" name="moveprev" accesskey="p" class="submit button btn btn-default btn-lg ">Previous</button>',
                    'MOVENEXTBUTTON' => '<button type="submit" id="movenextbtn" value="movenext" name="movenext" accesskey="n" class="submit button btn btn-default btn-lg ">Next</button>'
                );
                $myoutput = array_merge($myoutput, doreplacement($oEditedTemplate->viewPath . "/navigator.pstpl", $aData, $oEditedTemplate));
                $myoutput = array_merge($myoutput, doreplacement($oEditedTemplate->viewPath . "/endpage.pstpl", $aData, $oEditedTemplate));
                break;

            case 'welcome':
                unset($files);


                foreach ($Welcome as $qs) {
                    $files[] = array("name" => $qs);
                    $myoutput = array_merge($myoutput, doreplacement($oEditedTemplate->viewPath . "/$qs", $aData, $oEditedTemplate));
                }
                /*
                $myoutput = array_merge($myoutput, doreplacement($oEditedTemplate->viewPath  . "/startpage.pstpl", $aData, $oEditedTemplate));
                $myoutput = array_merge($myoutput, doreplacement($oEditedTemplate->viewPath  . "/welcome.pstpl", $aData, $oEditedTemplate));
                $myoutput = array_merge($myoutput, doreplacement($oEditedTemplate->viewPath  . "/privacy.pstpl", $aData, $oEditedTemplate));
                */

                $aData['aReplacements'] = array(
                    'MOVENEXTBUTTON' => '<button type="submit" id="movenextbtn" value="movenext" name="movenext" accesskey="n" class="submit button btn btn-default btn-lg">Next</button>'
                );
                $myoutput = array_merge($myoutput, doreplacement($oEditedTemplate->viewPath  . "/navigator.pstpl", $aData, $oEditedTemplate));

                $myoutput = array_merge($myoutput, doreplacement($oEditedTemplate->viewPath  . "/endpage.pstpl", $aData, $oEditedTemplate));
                break;

            case 'register':
                unset($files);
                foreach ($Register as $qs) {
                    $files[] = array("name" => $qs);
                }

                $myoutput[] = templatereplace(file_get_contents("$templatedir/startpage.pstpl"), array(), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);

                $aData = array(
                    'aReplacements' => array(
                        'SURVEYNAME' => 'Survey name'
                    )
                );
                $myoutput = array_merge($myoutput, doreplacement($oEditedTemplate->viewPath . "/survey.pstpl", $aData, $oEditedTemplate));

                $aData['aReplacements'] = array(
                    'REGISTERERROR' => 'Example error message',
                    'REGISTERMESSAGE1' => 'Register message 1',
                    'REGISTERMESSAGE2' => 'Register message 2',
                    'REGISTERFORM' => $this->getController()->render('/admin/templates/templateeditor_register_view', array('alt' => true), true),
                );

                $myoutput = array_merge($myoutput, doreplacement($oEditedTemplate->viewPath . "/register.pstpl", $aData, $oEditedTemplate));
                $myoutput[] = templatereplace(file_get_contents("$templatedir/endpage.pstpl"), array(), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);
                $myoutput[] = "\n";
                break;

            case 'save':
                unset($files);
                foreach ($Save as $qs)
                    $files[] = array("name" => $qs);

                $myoutput[] = templatereplace(file_get_contents("$templatedir/startpage.pstpl"), array(), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);
                $myoutput[] = templatereplace(file_get_contents("$templatedir/save.pstpl"), array(), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);
                $myoutput[] = templatereplace(file_get_contents("$templatedir/endpage.pstpl"), array(), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);
                $myoutput[] = "\n";
                break;

            case 'load':
                unset($files);
                foreach ($Load as $qs)
                    $files[] = array("name" => $qs);

                $myoutput[] = templatereplace(file_get_contents("$templatedir/startpage.pstpl"), array(), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);
                $myoutput[] = templatereplace(file_get_contents("$templatedir/load.pstpl"), array(), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);
                $myoutput[] = templatereplace(file_get_contents("$templatedir/endpage.pstpl"), array(), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);
                $myoutput[] = "\n";
                break;

            case 'clearall':
                unset($files);
                foreach ($Clearall as $qs)
                    $files[] = array("name" => $qs);

                $myoutput[] = templatereplace(file_get_contents("$templatedir/startpage.pstpl"), array(), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);
                $myoutput[] = templatereplace(file_get_contents("$templatedir/clearall.pstpl"), array(), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);
                $myoutput[] = templatereplace(file_get_contents("$templatedir/endpage.pstpl"), array(), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);
                $myoutput[] = "\n";
                break;

            case 'completed':
                unset($files);
                $myoutput[] = "";
                foreach ($CompletedTemplate as $qs)
                {
                    $files[] = array("name" => $qs);
                    $myoutput = array_merge($myoutput, doreplacement($oEditedTemplate->viewPath . "/$qs", $aData, $oEditedTemplate));
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
                    $this->getController()->render('/admin/templates/templateeditor_printablesurvey_quesanswer_view', array(
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
                unset($files);
                foreach ($printtemplate as $qs)
                {
                    $files[] = array("name" => $qs);
                }

                $myoutput[] = templatereplace(file_get_contents("$templatedir/startpage.pstpl"), array(), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);
                $myoutput[] = templatereplace(file_get_contents("$templatedir/printanswers.pstpl"), array('ANSWERTABLE' => $printoutput), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);
                $myoutput[] = templatereplace(file_get_contents("$templatedir/endpage.pstpl"), array(), $aData, 'Unspecified', false, NULL, array(), false, $oEditedTemplate);

                $myoutput[] = "\n";
                break;
        }
        $myoutput[] = "</html>";

        $jsfiles =  $this->_getEditableJsFiles($oEditedTemplate);

        if (is_array($files))
        {
            $match = 0;
            foreach ($files as $f)
            {
                if ($editfile == $f["name"])
                {
                    $match = 1;
                }
            }

            $aCssAndJsfiles = array_merge($cssfiles,$jsfiles ) ;

            foreach ($aCssAndJsfiles as $f)
            {
                if ($editfile == $f["name"])
                {
                    $match = 1;
                }
            }

            if ($match == 0)
            {
                if (count($files) > 0)
                {
                    $editfile = $files[0]["name"];
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
                        $otherfiles[] = array("name" => $file);
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
        {
            //$aCssfileseditable = (array) $oEditedTemplate->config->files_editable->css->filename;
            $aViewUrls = array_merge($aViewUrls, $this->_templatesummary($templatename, $screenname, $editfile, $templates, $files, $cssfiles, $jsfiles, $otherfiles, $myoutput));
        }

        App()->getClientScript()->registerScriptFile( App()->getAssetManager()->publish( ADMIN_SCRIPT_PATH . 'admin_core.js' ));
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
        //$aData['display']['menu_bars'] = false;
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }
}
