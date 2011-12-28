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
 *	$Id: Admin_Controller.php 11256 2011-10-25 13:52:18Z c_schmitz $
 */

 /**
 * labels
 *
 * @package LimeSurvey
 * @author
 * @copyright 2011
 * @version $Id: labels.php 11246 2011-10-23 20:46:05Z c_schmitz $
 * @access public
 */
class labels extends Survey_Common_Action
{
    /**
     * routes to the correct subdir
     *
     * @access public
     * @param string $sa
     * @return void
     */
    public function run($sa)
    {
        if ($sa == 'newlabelset' || $sa == 'editlabelset' || $sa == 'index')
            $this->route('index', array('sa', 'lid'));
        elseif ($sa == 'import')
            $this->route('import', array());
        elseif ($sa == 'importlabelresources')
            $this->route('importlabelresources', array());
        elseif ($sa == 'exportmulti')
            $this->route('exportmulti', array());
        elseif ($sa == 'process')
            $this->route('process', array());
        elseif ($sa == 'view')
            $this->route('view', array('lid'));
    }

    /**
     * Function responsible to import label resources from a '.zip' file.
     *
     * @access public
     * @return void
     */
    public function importlabelresources()
    {
        $clang = $this->getController()->lang;
        $action = returnglobal('action');
        $lid = returnglobal('lid');
        $this->getController()->_getAdminHeader();
        $this->_labelsetbar();

        if ($action == "importlabelresources" && $lid)
        {
            if (Yii::app()->getConfig('demoMode'))
                $this->getController()->error($clang->gT("Demo mode only: Uploading files is disabled in this system."));

            Yii::import('application.libraries.admin.Phpzip');

            $zipfile = $_FILES['the_file']['tmp_name'];
            $z = new Phpzip();

            // Create temporary directory
            // If dangerous content is unzipped
            // then no one will know the path
            $extractdir = self::_tempdir(Yii::app()->getConfig('tempdir'));
            $basedestdir = Yii::app()->getConfig('publicdir') . "/upload/labels";
            $destdir = $basedestdir . "/$lid/";

            if (!is_writeable($basedestdir))
                $this->getController()->error(sprintf($clang->gT("Incorrect permissions in your %s folder."), $basedestdir));

            if (!is_dir($destdir))
                mkdir($destdir);

            $aImportedFilesInfo = null;
            $aErrorFilesInfo = null;

            if (is_file($zipfile))
            {
                $importlabelresourcesoutput .= $clang->gT("Reading file..") . "<br /><br />\n";

                if ($z->extract($extractdir, $zipfile) != 'OK')
                    $this->getController()->error($clang->gT("This file is not a valid ZIP file archive. Import failed."));

                // now read tempdir and copy authorized files only
                $dh = opendir($extractdir);
                $aErrorFilesInfo = array();
                $aImportedFilesInfo = array();
                while ($direntry = readdir($dh))
                {
                    if (($direntry != ".") && ($direntry != ".."))
                    {
                        if (is_file($extractdir . "/" . $direntry))
                        {
                            // is  a file
                            $extfile = substr(strrchr($direntry, '.'), 1);
                            if (!(stripos(',' . Yii::app()->getConfig('allowedresourcesuploads') . ',', ',' . $extfile . ',') === false))
                            {
                                // Extension allowed
                                if (!copy($extractdir . "/" . $direntry, $destdir . $direntry))
                                {
                                    $aErrorFilesInfo[] = Array(
                                        "filename" => $direntry,
                                        "status" => $clang->gT("Copy failed")
                                    );
                                    unlink($extractdir . "/" . $direntry);
                                }
                                else
                                {
                                    $aImportedFilesInfo[] = Array(
                                        "filename" => $direntry,
                                        "status" => $clang->gT("OK")
                                    );
                                    unlink($extractdir . "/" . $direntry);
                                }
                            }
                            else
                            {
                                // Extension forbidden
                                $aErrorFilesInfo[] = Array(
                                    "filename" => $direntry,
                                    "status" => $clang->gT("Error") . " (" . $clang->gT("Forbidden Extension") . ")"
                                );
                                unlink($extractdir . "/" . $direntry);
                            }
                        }
                    }
                }

                // Delete the temporary file
                unlink($zipfile);

                // Delete temporary folder
                rmdir($extractdir);

                if (is_null($aErrorFilesInfo) && is_null($aImportedFilesInfo))
                    $this->getController()->error($clang->gT("This ZIP archive contains no valid Resources files. Import failed.") . '<br /><br />' . $clang->gT("Remember that we do not support subdirectories in ZIP archives."));
            }
            else
                $this->getController()->error(sprintf($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."), $basedestdir));

            $this->getController()->render('/admin/labels/importlabelresources_view', array(
                'clang' => $clang,
                'aErrorFilesInfo' => $aErrorFilesInfo,
                'aImportedFilesInfo' => $aImportedFilesInfo,
            ));
        }

        $this->getController()->_loadEndScripts();

        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
    }

    /**
     * Gives a temporary directory in given $dir that doesn't exist previously
     *
     * @access protected
     * @param string $dir
     * @param type $prefix
     * @param type $mode
     * @return string
     */
    protected function _tempdir($dir, $prefix='', $mode=0700)
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
     * Function to import a label set
     *
     * @access public
     * @return void
     */
    public function import()
    {
        $clang = $this->getController()->lang;
        $action = returnglobal('action');
        $this->getController()->_getAdminHeader();
        $this->_labelsetbar();

        if ($action == 'importlabels')
        {
            Yii::app()->loadHelper('admin/import');

            $sFullFilepath = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $_FILES['the_file']['name'];
            $aPathInfo = pathinfo($sFullFilepath);
            $sExtension = !empty($aPathInfo['extension']) ? $aPathInfo['extension'] : '';

            if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $sFullFilepath))
                $this->getController()->error(sprintf($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."), Yii::app()->getConfig('tempdir')));

            $options['checkforduplicates'] = 'off';
            if (isset($_POST['checkforduplicates']))
                $options['checkforduplicates'] = $_POST['checkforduplicates'];

            if (strtolower($sExtension) == 'csv')
                $aImportResults = CSVImportLabelset($sFullFilepath, $options);
            elseif (strtolower($sExtension) == 'lsl')
                $aImportResults = XMLImportLabelsets($sFullFilepath, $options);
            else
                $this->getController()->error($clang->gT("Uploaded label set file needs to have an .lsl extension."));

            unlink($sFullFilepath);

            $this->getController()->render('/admin/labels/import_view', array(
                'aImportResults' => $aImportResults,
                'clang' => $clang,
            ));
        }
        $this->getController()->_loadEndScripts();

        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
    }

    /**
     * Function to load new/edit labelset screen.
     *
     * @access public
     * @param mixed $action
     * @param integer $lid
     * @return
     */
    public function index($action, $lid=0)
    {
        $lid = sanitize_int($lid);

        Yii::app()->loadHelper('surveytranslator');
        $clang = $this->getController()->lang;

        $this->getController()->_getAdminHeader();
        $this->_labelsetbar($lid);

        if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 || Yii::app()->session['USER_RIGHT_MANAGE_LABEL'] == 1)
        {
            if ($action == "editlabelset")
            {
                $result = Labelsets::model()->findAllByAttributes(array('lid' => $lid));
                foreach ($result as $row)
                {
                    $row = $row->attributes;
                    $lbname = $row['label_name'];
                    $lblid = $row['lid'];
                    $langids = $row['languages'];
                }
                $data['lbname'] = $lbname;
                $data['lblid'] = $lblid;
            }

            $data['clang'] = $clang;
            $data['action'] = $action;
            $data['lid'] = $lid;

            if ($action == "newlabelset")
            {
                $langids = Yii::app()->session['adminlang'];
                $tabitem = $clang->gT("Create New Label Set");
            }
            else
                $tabitem = $clang->gT("Edit label set");

            $langidsarray = explode(" ", trim($langids)); // Make an array of it

            if (isset($row['lid']))
                $panecookie = $row['lid'];
            else
                $panecookie = 'new';

            $data['langids'] = $langids;
            $data['langidsarray'] = $langidsarray;
            $data['panecookie'] = $panecookie;
            $data['tabitem'] = $tabitem;

            $this->getController()->render('/admin/labels/editlabel_view', $data);
        }

        $this->getController()->_loadEndScripts();

        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));
    }

    /**
     * Function to view a labelset.
     *
     * @access public
     * @param int $lid
     * @return void
     */
    public function view($lid = 0)
    {
        // Escapes the id variable
        if ($lid != false)
            $lid = sanitize_int($lid);

        // Gets the current language
        $clang = $this->getController()->lang;
        $action = 'labels';

        // Loads admin header
        $this->getController()->_getAdminHeader();

        // Includes some javascript files
        $this->getController()->_js_admin_includes(Yii::app()->baseUrl . '/scripts/admin/labels.js');
        $this->getController()->_js_admin_includes(Yii::app()->baseUrl . '/scripts/admin/updateset.js');

        // Checks if user have the sufficient rights to manage the labels
        if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 || Yii::app()->session['USER_RIGHT_MANAGE_LABEL'] == 1)
        {
            // Sets the menubar
            $this->_labelsetbar($lid);

            // Get a result containing labelset with the specified id
            $result = Labelsets::model()->findByAttributes(array('lid' => $lid));

            // If there is label id in the variable $lid and there are labelset records in the database
            $labelset_exists = !empty($result);

            if ($lid && $labelset_exists)
            {
                // Now recieve all labelset information and display it
                $data['lid'] = $lid;
                $data['clang'] = $clang;
                $data['row'] = $result->attributes;

                // Display a specific labelbar menu
                $this->getController()->render("/admin/labels/labelbar_view", $data);

                $rwlabelset = $result;

                // Make languages array from the current row
                $lslanguages = explode(" ", trim($result['languages']));

                Yii::app()->loadHelper("admin/htmleditor");

                PrepareEditorScript(true, $this->controller);

                $criteria = new CDbCriteria;
                $criteria->select = 'max(sortorder) as maxsortorder, sortorder';
                $criteria->addCondition('lid = :lid');
                $criteria->addCondition('language = :language');
                $criteria->params = array(':lid' => $lid, ':language' => $lslanguages[0]);
                $maxresult = Label::model()->find($criteria);
                $maxsortorder = 1;
                if (!empty($maxresult))
                    $maxsortorder = $maxresult->maxsortorder + 1;

                $i = 0;
                Yii::app()->loadHelper("surveytranslator");
                $results = array();
                foreach ($lslanguages as $lslanguage)
                {
                    $result = Label::model()->findAllByAttributes(array('lid' => $lid, 'language' => $lslanguage), array('order' => 'sortorder, code'));
                    $criteria = new CDbCriteria;
                    $criteria->order = 'sortorder, code';
                    $criteria->condition = 'lid = :lid AND language = :language';
                    $criteria->params = array(':lid' => $lid, ':language' => $lslanguage);
                    $labelcount = Label::model()->count($criteria);

                    $results[$i] = array();

                    foreach ($result as $row)
                        $results[$i][] = $row->attributes;

                    $i++;
                }

                $this->getController()->render('/admin/labels/labelview_view', array(
                    'results' => $results,
                    'lslanguages' => $lslanguages,
                    'clang' => $clang,
                    'lid' => $lid,
                    'maxsortorder' => $maxsortorder,
                    'msorow' => $maxresult->attributes,
                    'action' => $action,
                ));
            }
        }

        $this->getController()->_loadEndScripts();

        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
    }

    /**
     * Process labels form data depending on $action.
     *
     * @access public
     * @return void
     */
    public function process()
    {
        if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 || Yii::app()->session['USER_RIGHT_MANAGE_LABEL'] == 1)
        {
            if (isset($_POST['sortorder']))
                $postsortorder = sanitize_int($_POST['sortorder']);

            if (isset($_POST['method']) && get_magic_quotes_gpc())
                $_POST['method'] = stripslashes($_POST['method']);

            $action = returnglobal('action');
            Yii::app()->loadHelper('admin/label');
            $lid = returnglobal('lid');

            //DO DATABASE UPDATESTUFF <- HAHAHAHAH FAIL! <Dragooon>
            if ($action == "updateset")
                updateset($lid);
            if ($action == "insertlabelset")
                $lid = insertlabelset();
            if (($action == "modlabelsetanswers") || ($action == "ajaxmodlabelsetanswers"))
                modlabelsetanswers($lid);
            if ($action == "deletelabelset")
                if (deletelabelset($lid))
                    $lid = 0;

            if ($lid)
                $this->getController()->redirect($this->getController()->createUrl("admin/labels/sa/view/lid/" . $lid));
            else
                $this->getController()->redirect($this->getController()->createUrl("admin/labels/sa/view"));
        }
    }

    /**
     * Multi label export
     *
     * @access public
     * @return void
     */
    public function exportmulti()
    {
        $this->getController()->_getAdminHeader();
        $this->_labelsetbar(0);
        $this->getController()->_js_admin_includes(Yii::app()->baseUrl . 'scripts/admin/labels.js');
        $data['clang'] = $this->getController()->lang;
        $data['labelsets'] = getlabelsets();
        $this->getController()->render('/admin/labels/exportmulti_view', $data);
        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));
    }

    /**
     * Common labelset menu
     *
     * @access protected
     * @param int $lid
     * @return void
     */
    protected function _labelsetbar($lid = 0)
    {
        $lid = (int) $lid;
        $data['clang'] = $this->getController()->lang;
        $data['lid'] = $lid;
        $data['labelsets'] = getlabelsets();
        $this->getController()->render("/admin/labels/labelsetsbar_view",$data);
    }
 }
