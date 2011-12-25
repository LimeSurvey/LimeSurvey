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
 *
 * 	$Id: Admin_Controller.php 11256 2011-10-25 13:52:18Z c_schmitz $
 */

/**
 * templates
 *
 * @package LimeSurvey
 * @author
 * @copyright 2011
 * @version $Id: templates.php 11189 2011-10-17 21:18:55Z tpartner $
 */
class templates extends Survey_Common_Action
{
    /**
     * Routes to correct sub-action
     *
     * @access public
     * @param string $sa
     * @return void
     */
    public function run($sa)
    {
        if ($sa == 'fileredirect')
            $this->route('fileredirect', array('templatename', 'screenname', 'editfile'));
        elseif ($sa == 'screenredirect')
            $this->route('screenredirect', array('editfile', 'templatename', 'screenname'));
        elseif ($sa == 'view')
            $this->route('view', array('editfile', 'screenname', 'templatename'));
        elseif ($sa == 'upload')
            $this->route('upload', array());
        elseif ($sa == 'delete')
            $this->route('delete', array('templatename'));
        elseif ($sa == 'templatezip')
            $this->route('templatezip', array('templatename'));
        else
            $this->route($sa, array());
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
        Yii::import('application.libraries.admin.Phpzip', true);
        $zip = new PHPZip();
        $templatedir = sGetTemplatePath($templatename) . DIRECTORY_SEPARATOR;
        $tempdir = Yii::app()->getConfig('tempdir');

        $zipfile = "$tempdir/$templatename.zip";
        $zip->Zip($templatedir, $zipfile);

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
     * Responsible to import a template archive.
     *
     * @access public
     * @return void
     */
    public function upload()
    {
        $clang = $this->controller->lang;

        $this->controller->_getAdminHeader();
        $this->controller->_js_admin_includes(Yii::app()->baseUrl . '/scripts/admin/templates.js');

        $this->_initialise('default', 'welcome', 'startpage.pstpl', FALSE);
        $lid = returnglobal('lid');
        $action = returnglobal('action');

        if ($action == 'templateupload') {
            if (Yii::app()->getConfig('demoMode'))
                $this->controller->error($clang->gT("Demo mode: Uploading templates is disabled."));

            Yii::import('application.libraries.admin.Phpzip', true);

            $zipfile = $_FILES['the_file']['tmp_name'];
            $zip = new PHPZip();

            // Create temporary directory so that if dangerous content is unzipped it would be unaccessible
            $extractdir = self::_tempdir(Yii::app()->getConfig('tempdir'));
            $basedestdir = Yii::app()->getConfig('usertemplaterootdir');
            $newdir = str_replace('.', '', self::_strip_ext(sanitize_paranoid_string($_FILES['the_file']['name'])));
            $destdir = $basedestdir . '/' . $newdir . '/';

            if (!is_writeable($basedestdir))
                $this->controller->error(sprintf($clang->gT("Incorrect permissions in your %s folder."), $basedestdir));

            if (!is_dir($destdir))
                mkdir($destdir);
            else
                $this->controller->error(sprintf($clang->gT("Template '%s' does already exist."), $newdir));

            $aImportedFilesInfo = array();
            $aErrorFilesInfo = array();

            if (is_file($zipfile)) {
                if ($zip->extract($extractdir, $zipfile) != 'OK')
                    $this->controller->error($clang->gT("This file is not a valid ZIP file archive. Import failed."));

                // Now read tempdir and copy authorized files only
                $dh = opendir($extractdir);
                while ($direntry = readdir($dh))
                    if (($direntry != ".") && ($direntry != ".."))
                        if (is_file($extractdir . "/" . $direntry)) {
                            // Is a file
                            $extfile = substr(strrchr($direntry, '.'), 1);

                            if (!(stripos(',' . Yii::app()->getConfig('allowedresourcesuploads') . ',', ',' . $extfile . ',') === false)
                            )
                                // Extension allowed
                                if (!copy($extractdir . "/" . $direntry, $destdir . $direntry))
                                    $aErrorFilesInfo[] = Array(
                                        "filename" => $direntry,
                                        "status" => $clang->gT("Copy failed")
                                    );
                                else
                                    $aImportedFilesInfo[] = Array(
                                        "filename" => $direntry,
                                        "status" => $clang->gT("OK")
                                    );
                            else
                                // Extension forbidden
                                $aErrorFilesInfo[] = Array(
                                    "filename" => $direntry,
                                    "status" => $clang->gT("Error") . " (" . $clang->gT("Forbidden Extension") . ")"
                                );
                            unlink($extractdir . "/" . $direntry);
                        }

                // Delete the temporary file
                unlink($zipfile);
                closedir($dh);

                // Delete temporary folder
                rmdir($extractdir);

                if (count($aErrorFilesInfo) == 0 && count($aImportedFilesInfo) == 0)
                    $this->controller->error($clang->gT("This ZIP archive contains no valid template files. Import failed."));
            }
            else
                $this->controller->error(sprintf($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."), $basedestdir));

            $this->controller->render('/admin/templates/importuploaded_view', array(
                                                                                   'aImportedFilesInfo' => $aImportedFilesInfo,
                                                                                   'aErrorFilesInfo' => $aErrorFilesInfo,
                                                                                   'lid' => $lid,
                                                                                   'clang' => $clang,
                                                                                   'newdir' => $newdir,
                                                                              ));
        }
        else
            $this->controller->render('/admin/templates/importform_view', array(
                                                                               'clang' => $clang,
                                                                               'lid' => $lid,
                                                                          ));

        $this->controller->_loadEndScripts();

        $this->controller->_getAdminFooter("http://docs.limesurvey.org", $this->controller->lang->gT("LimeSurvey online manual"));
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
    public function view($editfile = 'startpage.pstpl', $screenname = 'welcome', $templatename = 'default')
    {
        $this->controller->_js_admin_includes(Yii::app()->baseUrl . '/scripts/admin/templates.js');
        $this->controller->_css_admin_includes(Yii::app()->baseUrl . '/scripts/admin/codemirror_ui/lib/CodeMirror-2.0/lib/codemirror.css');
        $this->controller->_css_admin_includes(Yii::app()->baseUrl . '/scripts/admin/codemirror_ui/lib/CodeMirror-2.0/mode/javascript/javascript.css');
        $this->controller->_css_admin_includes(Yii::app()->baseUrl . '/scripts/admin/codemirror_ui/css/codemirror-ui.css');

        $this->controller->_getAdminHeader();
        $this->_initialise($templatename, $screenname, $editfile);

        $this->controller->_loadEndScripts();

        $this->controller->_getAdminFooter("http://docs.limesurvey.org", $this->controller->lang->gT("LimeSurvey online manual"));

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
    public function screenredirect($editfile = 'startpage.pstpl', $templatename = 'default', $screenname = 'welcome')
    {
        $this->controller->redirect($this->controller->createUrl("admin/templates/sa/view/editfile/" . $editfile . "/screenname/" . $screenname . "/templatename/" . $templatename));
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
    public function fileredirect($templatename = 'default', $screenname = 'welcome', $editfile = 'startpage.pstpl')
    {
        $this->controller->redirect($this->controller->createUrl("admin/templates/sa/view/editfile/" . $editfile . "/screenname/" . $screenname . "/templatename/" . $templatename));
    }

    /**
     * Function responsible to delete a template file.
     *
     * @access public
     * @return void
     */
    public function templatefiledelete()
    {
        if (returnglobal('action') == "templatefiledelete") {
            // This is where the temp file is
            $the_full_file_path = Yii::app()->getConfig('usertemplaterootdir') . "/" . $_POST['templatename'] . "/" . returnglobal('otherfile');
            unlink($the_full_file_path);
            $this->controller->redirect($this->controller->createUrl("admin/templates/sa/view/editfile/" . returnglobal('editfile') . "/screenname/" . returnglobal('screenname') . "/templatename/" . returnglobal('templatename')));
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
        if (returnglobal('action') == "templaterename" && returnglobal('newname') && returnglobal('copydir')) {
            $clang = Yii::app()->lang;
            $newdirname = Yii::app()->getConfig('usertemplaterootdir') . "/" . returnglobal('newname');
            $olddirname = Yii::app()->getConfig('usertemplaterootdir') . "/" . returnglobal('copydir');
            if (isStandardTemplate(returnglobal('newname')))
                $this->controller->error(sprintf($clang->gT("Template could not be renamed to `%s`.", "js"), returnglobal('newname')) . " " . $clang->gT("This name is reserved for standard template.", "js"));
            elseif (rename($olddirname, $newdirname) == false)
                $this->controller->error(sprintf($clang->gT("Directory could not be renamed to `%s`.", "js"), returnglobal('newname')) . " " . $clang->gT("Maybe you don't have permission.", "js"));
            else
            {
                $templatename = returnglobal('newname');
                $this->view("startpage.pstpl", "welcome", $templatename);
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
        $clang = $this->controller->lang;

        if (returnglobal('action') == "templatecopy" && returnglobal('newname') && returnglobal('copydir')) {
            // Copies all the files from one template directory to a new one
            // This is a security issue because it is allowing copying from get variables...
            Yii::app()->loadHelper('admin/template');
            $newdirname = Yii::app()->getConfig('usertemplaterootdir') . "/" . returnglobal('newname');
            $copydirname = sGetTemplatePath(returnglobal('copydir'));
            $mkdirresult = mkdir_p($newdirname);

            if ($mkdirresult == 1) {
                $copyfiles = getListOfFiles($copydirname);
                foreach ($copyfiles as $file)
                {
                    $copyfile = $copydirname . "/" . $file;
                    $newfile = $newdirname . "/" . $file;
                    if (!copy($copyfile, $newfile))
                        $this->controller->error(sprintf($clang->gT("Failed to copy %s to new template directory.", "js"), $file));
                }

                $templatename = returnglobal('newname');
                $this->view("startpage.pstpl", "welcome", $templatename);
            }
            elseif ($mkdirresult == 2)
                $this->controller->error(sprintf($clang->gT("Directory with the name `%s` already exists - choose another name", "js"), returnglobal('newname')));
            else
                $this->controller->error(sprintf($clang->gT("Unable to create directory `%s`.", "js"), returnglobal('newname')) . " " . $clang->gT("Please check the directory permissions.", "js"));
            ;
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
        Yii::app()->loadHelper("admin/template");
        if (is_template_editable($templatename) == true) {
            $clang = $this->controller->lang;

            if (rmdirr(Yii::app()->getConfig('usertemplaterootdir') . "/" . $templatename) == true) {
                $surveys = Survey::model()->findAllByAttributes(array('template' => $templatename));
                foreach ($surveys as $s)
                {
                    $s->template = Yii::app()->getConfig('defaulttemplate');
                    $s->save();
                }

                Template::model()->deleteAllByAttributes(array('folder' => $templatename));
                Templates_rights::model()->deleteAllByAttributes(array('folder' => $templatename));

                Yii::app()->session['flashmessage'] = sprintf($clang->gT("Template '%s' was successfully deleted."), $templatename);
            }
            else
                Yii::app()->session['flashmessage'] = sprintf($clang->gT("There was a problem deleting the template '%s'. Please check your directory/file permissions."), $templatename);
        }

        // Redirect with default templatename, editfile and screenname
        $this->controller->redirect($this->controller->createUrl("admin/templates/sa/view"));
    }

    /**
     * Function responsible to save the changes made in CodemMirror editor.
     *
     * @access public
     * @return void
     */
    public function templatesavechanges()
    {
        if (returnglobal('changes')) {
            $changedtext = returnglobal('changes');
            $changedtext = str_replace('<?', '', $changedtext);
            if (get_magic_quotes_gpc())
                $changedtext = stripslashes($changedtext);
        }

        if (returnglobal('changes_cp')) {
            $changedtext = returnglobal('changes_cp');
            $changedtext = str_replace('<?', '', $changedtext);
            if (get_magic_quotes_gpc())
                $changedtext = stripslashes($changedtext);
        }

        $action = returnglobal('action');
        $editfile = returnglobal('editfile');
        $templatename = returnglobal('templatename');
        $screenname = returnglobal('screenname');
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
                    $this->controller->error('Invalid template name');

                $savefilename = Yii::app()->getConfig('usertemplaterootdir') . "/" . $templatename . "/" . $editfile;
                if (is_writable($savefilename)) {
                    if (!$handle = fopen($savefilename, 'w'))
                        $this->controller->error('Could not open file ' . $savefilename);

                    if (!fwrite($handle, $changedtext))
                        $this->controller->error('Could not write file ' . $savefilename);

                    fclose($handle);
                }
                else
                    $this->controller->error("The file $savefilename is not writable");
            }
        }

        $this->controller->redirect($this->controller->createUrl("admin/templates/sa/view/editfile/" . $editfile . "/screenname/" . $screenname . "/templatename/" . $templatename));
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
        $data['clang'] = $this->controller->lang;
        $data['screenname'] = $screenname;
        $data['editfile'] = $editfile;
        $data['screens'] = $screens;
        $data['tempdir'] = $tempdir;
        $data['templatename'] = $templatename;
        $data['usertemplaterootdir'] = Yii::app()->getConfig('usertemplaterootdir');

        $this->controller->render("/admin/templates/templatebar_view", $data);
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
        $data = array();
        $time = date("ymdHis");

        // Prepare textarea class for optional javascript
        $templateclasseditormode = 'full'; // default
        if (Yii::app()->session['templateeditormode'] == 'none')
            $templateclasseditormode = 'none';

        $data['templateclasseditormode'] = $templateclasseditormode;

        // The following lines are forcing the browser to refresh the templates on each save
        @$fnew = fopen("$tempdir/template_temp_$time.html", "w+");
        $data['time'] = $time;

        if (!$fnew) {
            $data['filenotwritten'] = true;
        }
        else
        {
            @fwrite($fnew, getHeader());
            foreach ($cssfiles as $cssfile)
                $myoutput = str_replace($cssfile['name'], $cssfile['name'] . "?t=$time", $myoutput);

            foreach ($myoutput as $line)
                @fwrite($fnew, $line);

            @fclose($fnew);
        }

        $data['clang'] = $this->controller->lang;
        $data['screenname'] = $screenname;
        $data['editfile'] = $editfile;

        $data['tempdir'] = $tempdir;
        $data['templatename'] = $templatename;
        $data['templates'] = $templates;
        $data['files'] = $files;
        $data['cssfiles'] = $cssfiles;
        $data['otherfiles'] = $otherfiles;
        $data['tempurl'] = $tempurl;
        $data['time'] = $time;

        $this->controller->render("/admin/templates/templatesummary_view", $data);
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
        global $siteadminname, $siteadminemail;

        $clang = $this->controller->lang;
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

        $templatename = sanitize_paranoid_string($templatename);
        $screenname = auto_unescape($screenname);

        // Checks if screen name is in the list of allowed screen names
        if (multiarray_search($screens, 'id', $screenname) === false)
            $this->controller->error('Invalid screen name');

        if (!isset($action))
            $action = sanitize_paranoid_string(returnglobal('action'));

        if (!isset($subaction))
            $subaction = sanitize_paranoid_string(returnglobal('subaction'));

        if (!isset($newname))
            $newname = sanitize_paranoid_string(returnglobal('newname'));

        if (!isset($copydir))
            $copydir = sanitize_paranoid_string(returnglobal('copydir'));

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

        $templates = gettemplatelist();
        if (!isset($templates[$templatename]))
            $templatename = Yii::app()->getConfig('defaulttemplate');

        $normalfiles = array("DUMMYENTRY", ".", "..", "preview.png");
        foreach ($files as $fl)
            $normalfiles[] = $fl["name"];

        foreach ($cssfiles as $fl)
            $normalfiles[] = $fl["name"];

        // Set this so common.php doesn't throw notices about undefined variables
        $thissurvey['active'] = 'N';

        // FAKE DATA FOR TEMPLATES
        $thissurvey['name'] = $clang->gT("Template Sample");
        $thissurvey['description'] =
                $clang->gT('This is a sample survey description. It could be quite long.') . '<br /><br />' .
                $clang->gT("But this one isn't.");
        $thissurvey['welcome'] =
                $clang->gT('Welcome to this sample survey') . '<br />' .
                $clang->gT('You should have a great time doing this') . '<br />';
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

        $navigator = $this->controller->render('/admin/templates/templateeditor_navigator_view', array(
                                                                                                      'screenname' => $screenname,
                                                                                                      'clang' => $clang,
                                                                                                 ), true);

        $completed = $this->controller->render('/admin/templates/templateeditor_completed_view', array(
                                                                                                      'clang' => $clang,
                                                                                                 ), true);

        $assessments = $this->controller->render('/admin/templates/templateeditor_assessments_view', array(
                                                                                                          'clang' => $clang,
                                                                                                     ), true);

        $printoutput = $this->controller->render('/admin/templates/templateeditor_printoutput_view', array(
                                                                                                          'clang' => $clang
                                                                                                     ), true);

        $help = $clang->gT("This is some help text.");
        $totalquestions = '10';
        $surveyformat = 'Format';
        $notanswered = '5';
        $privacy = '';
        $surveyid = '1295';
        $token = 1234567;

        $templatedir = sGetTemplatePath($templatename);
        $templateurl = sGetTemplateURL($templatename);

        // Save these variables in an array
        $data['thissurvey'] = $thissurvey;
        $data['percentcomplete'] = $percentcomplete;
        $data['groupname'] = $groupname;
        $data['groupdescription'] = $groupdescription;
        $data['navigator'] = $navigator;
        $data['help'] = $help;
        $data['surveyformat'] = $surveyformat;
        $data['totalquestions'] = $totalquestions;
        $data['completed'] = $completed;
        $data['notanswered'] = $notanswered;
        $data['privacy'] = $privacy;
        $data['surveyid'] = $surveyid;
        $data['token'] = $token;
        $data['assessments'] = $assessments;
        $data['printoutput'] = $printoutput;
        $data['templatedir'] = $templatedir;
        $data['templateurl'] = $templateurl;
        $data['templatename'] = $templatename;
        $data['screenname'] = $screenname;
        $data['editfile'] = $editfile;

        $myoutput[] = "";
        switch ($screenname)
        {
            case 'surveylist':
                unset($files);

                $surveylist = array(
                    "nosid" => $clang->gT("You have not provided a survey identification number"),
                    "contact" => sprintf($clang->gT("Please contact %s ( %s ) for further assistance."), $siteadminname, $siteadminemail),
                    "listheading" => $clang->gT("The following surveys are available:"),
                    "list" => $this->controller->render('/admin/templates/templateeditor_surveylist_view', array(), true),
                );
                $data['surveylist'] = $surveylist;

                $myoutput[] = "";
                foreach ($SurveyList as $qs)
                {
                    $files[] = array("name" => $qs);
                    $myoutput = array_merge($myoutput, doreplacement(sGetTemplatePath($templatename) . "/$qs", $data));
                }
                break;

            case 'question':
                unset($files);
                foreach ($Question as $qs)
                    $files[] = array("name" => $qs);

                $myoutput[] = $this->controller->render('/admin/templates/templateeditor_question_meta_view', array(), true);
                $myoutput = array_merge($myoutput, doreplacement(sGetTemplatePath($templatename) . "/startpage.pstpl", $data));
                $myoutput = array_merge($myoutput, doreplacement(sGetTemplatePath($templatename) . "/survey.pstpl", $data));
                $myoutput = array_merge($myoutput, doreplacement(sGetTemplatePath($templatename) . "/startgroup.pstpl", $data));
                $myoutput = array_merge($myoutput, doreplacement(sGetTemplatePath($templatename) . "/groupdescription.pstpl", $data));

                $question = array(
                    'all' => 'How many roads must a man walk down?',
                    'text' => 'How many roads must a man walk down?',
                    'code' => '1a',
                    'help' => 'helpful text',
                    'mandatory' => '',
                    'man_message' => '',
                    'valid_message' => '',
                    'file_valid_message' => '',
                    'essentials' => 'id="question1"',
                    'class' => 'list-radio',
                    'man_class' => '',
                    'input_error_class' => '',
                    'number' => '1'
                );
                $data['question'] = $question;

                $answer = $this->controller->render('/admin/templates/templateeditor_question_answer_view', array(), true);
                $data['answer'] = $answer;
                $myoutput = array_merge($myoutput, doreplacement(sGetTemplatePath($templatename) . "/question.pstpl", $data));

                $answer = $this->controller->render('/admin/templates/templateeditor_question_answer_view', array('alt' => true), true);
                $data['answer'] = $answer;
                $question = array(
                    'all' => '<span class="asterisk">*</span>' . $clang->gT("Please explain something in detail:"),
                    'text' => $clang->gT('Please explain something in detail:'),
                    'code' => '2a',
                    'help' => '',
                    'mandatory' => $clang->gT('*'),
                    'man_message' => '',
                    'valid_message' => '',
                    'file_valid_message' => '',
                    'essentials' => 'id="question2"',
                    'class' => 'text-long',
                    'man_class' => 'mandatory',
                    'input_error_class' => '',
                    'number' => '2'
                );
                $data['question'] = $question;
                $myoutput = array_merge($myoutput, doreplacement(sGetTemplatePath($templatename) . "/question.pstpl", $data));
                $myoutput = array_merge($myoutput, doreplacement(sGetTemplatePath($templatename) . "/endgroup.pstpl", $data));
                $myoutput = array_merge($myoutput, doreplacement(sGetTemplatePath($templatename) . "/navigator.pstpl", $data));
                $myoutput = array_merge($myoutput, doreplacement(sGetTemplatePath($templatename) . "/endpage.pstpl", $data));
                break;

            case 'welcome':
                unset($files);
                $myoutput[] = "";
                foreach ($Welcome as $qs)
                {
                    $files[] = array("name" => $qs);
                    $myoutput = array_merge($myoutput, doreplacement(sGetTemplatePath($templatename) . "/$qs", $data));
                }
                break;

            case 'register':
                unset($files);
                foreach ($Register as $qs)
                    $files[] = array("name" => $qs);

                $myoutput[] = templatereplace("$templatedir/startpage.pstpl", array(), $data);
                $myoutput[] = templatereplace("$templatedir/survey.pstpl", array(), $data);
                $myoutput[] = templatereplace("$templatedir/register.pstpl", array(), $data);
                $myoutput[] = templatereplace("$templatedir/endpage.pstpl", array(), $data);
                $myoutput[] = "\n";
                break;

            case 'save':
                unset($files);
                foreach ($Save as $qs)
                    $files[] = array("name" => $qs);

                $myoutput[] = templatereplace("$templatedir/startpage.pstpl", array(), $data);
                $myoutput[] = templatereplace("$templatedir/save.pstpl", array(), $data);
                $myoutput[] = templatereplace("$templatedir/endpage.pstpl", array(), $data);
                $myoutput[] = "\n";
                break;

            case 'load':
                unset($files);
                foreach ($Load as $qs)
                    $files[] = array("name" => $qs);

                $myoutput[] = templatereplace("$templatedir/startpage.pstpl", array(), $data);
                $myoutput[] = templatereplace("$templatedir/load.pstpl", array(), $data);
                $myoutput[] = templatereplace("$templatedir/endpage.pstpl", array(), $data);
                $myoutput[] = "\n";
                break;

            case 'clearall':
                unset($files);
                foreach ($Clearall as $qs)
                    $files[] = array("name" => $qs);

                $myoutput[] = templatereplace("$templatedir/startpage.pstpl", array(), $data);
                $myoutput[] = templatereplace("$templatedir/clear.pstpl", array(), $data);
                $myoutput[] = templatereplace("$templatedir/endpage.pstpl", array(), $data);
                $myoutput[] = "\n";
                break;

            case 'completed':
                unset($files);
                $myoutput[] = "";
                foreach ($CompletedTemplate as $qs)
                {
                    $files[] = array("name" => $qs);
                    $myoutput = array_merge($myoutput, doreplacement(sGetTemplatePath($templatename) . "/$qs", $data));
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
                                                                  'QUESTION_TEXT' => 'This is a sample question text. The user was asked to pick an entry.',
                                                                  'QUESTIONHELP' => 'This is some help text for this question.',
                                                                  'ANSWER' =>
                                                                  $this->controller->render('/admin/templates/templateeditor_printablesurvey_quesanswer_view', array(
                                                                                                                                                                    'templateurl' => $templateurl,
                                                                                                                                                               ), true),
                                                             ), $data);
                }
                $groupoutput = array();
                $groupoutput[] = templatereplace("$templatedir/print_group.pstpl", array('QUESTIONS' => implode(' ', $questionoutput)), $data);

                $myoutput[] = templatereplace("$templatedir/print_survey.pstpl", array('GROUPS' => implode(' ', $groupoutput),
                                                                                      'FAX_TO' => $clang->gT("Please fax your completed survey to:") . " 000-000-000",
                                                                                      'SUBMIT_TEXT' => $clang->gT("Submit your survey."),
                                                                                      'HEADELEMENTS' => getPrintableHeader(),
                                                                                      'SUBMIT_BY' => sprintf($clang->gT("Please submit by %s"), date('d.m.y')),
                                                                                      'THANKS' => $clang->gT('Thank you for completing this survey.'),
                                                                                      'END' => $clang->gT('This is the survey end message.')
                                                                                 ), $data);
                break;

            case 'printanswers':
                unset($files);
                foreach ($printtemplate as $qs)
                {
                    $files[] = array("name" => $qs);
                }

                $myoutput[] = templatereplace("$templatedir/startpage.pstpl", array(), $data);
                $myoutput[] = templatereplace("$templatedir/printanswers.pstpl", array('ANSWERTABLE' => $printoutput), $data);
                $myoutput[] = templatereplace("$templatedir/endpage.pstpl", array(), $data);

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

        $data['clang'] = $this->controller->lang;
        $data['codelanguage'] = $codelanguage;
        $data['highlighter'] = $highlighter;
        $data['screens'] = $screens;
        $data['templatename'] = $templatename;
        $data['templates'] = $templates;
        $data['editfile'] = $editfile;
        $data['screenname'] = $screenname;
        $data['tempdir'] = Yii::app()->getConfig('tempdir');
        $data['usertemplaterootdir'] = Yii::app()->getConfig('usertemplaterootdir');

        $this->controller->render("/admin/templates/templateeditorbar_view", $data);

        if ($showsummary)
            $this->_templatesummary($templatename, $screenname, $editfile, $templates, $files, $cssfiles, $otherfiles, $myoutput);
    }
}
