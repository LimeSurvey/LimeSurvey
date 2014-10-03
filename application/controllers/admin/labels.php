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
 * labels
 *
 * @package LimeSurvey
 * @author
 * @copyright 2011
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
    public function run($sa=null)
    {
        if ($sa == 'newlabelset' || $sa == 'editlabelset')
            $this->route('index', array('sa', 'lid'));
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
        $lid = returnGlobal('lid');

        if (!empty($lid))
        {
            if (Yii::app()->getConfig('demoMode'))
                $this->getController()->error($clang->gT("Demo mode only: Uploading files is disabled in this system."), $this->getController()->createUrl("admin/labels/sa/view/lid/{$lid}"));

            // Create temporary directory
            // If dangerous content is unzipped
            // then no one will know the path
            $extractdir = $this->_tempdir(Yii::app()->getConfig('tempdir'));
            $zipfilename = $_FILES['the_file']['tmp_name'];
            $basedestdir = Yii::app()->getConfig('uploaddir') . "/labels";
            $destdir = $basedestdir . "/$lid/";

            Yii::app()->loadLibrary('admin.pclzip');
            $zip = new PclZip($zipfilename);

            if (!is_writeable($basedestdir))
                $this->getController()->error(sprintf($clang->gT("Incorrect permissions in your %s folder."), $basedestdir), $this->getController()->createUrl("admin/labels/sa/view/lid/{$lid}"));

            if (!is_dir($destdir))
                mkdir($destdir);

            $aImportedFilesInfo = array();
            $aErrorFilesInfo = array();

            if (is_file($zipfilename))
            {
                if ($zip->extract($extractdir) <= 0)
                    $this->getController()->error($clang->gT("This file is not a valid ZIP file archive. Import failed. " . $zip->errorInfo(true)), $this->getController()->createUrl("admin/labels/sa/view/lid/{$lid}"));

                // now read tempdir and copy authorized files only
                $folders = array('flash', 'files', 'images');
                foreach ($folders as $folder)
                {
                    list($_aImportedFilesInfo, $_aErrorFilesInfo) = $this->_filterImportedResources($extractdir . "/" . $folder, $destdir . $folder);
                    $aImportedFilesInfo = array_merge($aImportedFilesInfo, $_aImportedFilesInfo);
                    $aErrorFilesInfo = array_merge($aErrorFilesInfo, $_aErrorFilesInfo);
                }

                // Deletes the temp directory
                rmdirr($extractdir);

                // Delete the temporary file
                unlink($zipfilename);

                if (is_null($aErrorFilesInfo) && is_null($aImportedFilesInfo))
                    $this->getController()->error($clang->gT("This ZIP archive contains no valid Resources files. Import failed."), $this->getController()->createUrl("admin/labels/sa/view/lid/{$lid}"));
            }
            else
                $this->getController()->error(sprintf($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."), $basedestdir), $this->getController()->createUrl("admin/labels/sa/view/lid/{$lid}"));

            $aData = array(
                'aErrorFilesInfo' => $aErrorFilesInfo,
                'aImportedFilesInfo' => $aImportedFilesInfo,
                'lid' => $lid
            );

            $this->_renderWrappedTemplate('labels', 'importlabelresources_view', $aData);
        }
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
        $action = returnGlobal('action');
        $aViewUrls = array();

        if ($action == 'importlabels')
        {
            Yii::app()->loadHelper('admin/import');

            $sFullFilepath = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . randomChars(20);
            $aPathInfo = pathinfo($_FILES['the_file']['name']);
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

            $aViewUrls['import_view'][] = array('aImportResults' => $aImportResults);
        }

        $this->_renderWrappedTemplate('labels', $aViewUrls);
    }

    /**
     * Function to load new/edit labelset screen.
     *
     * @access public
     * @param mixed $action
     * @param integer $lid
     * @return
     */
    public function index($sa, $lid=0)
    {
        Yii::app()->loadHelper('surveytranslator');

        $clang = $this->getController()->lang;
        $lid = sanitize_int($lid);
        $aViewUrls = array();

        if (Permission::model()->hasGlobalPermission('labelsets','read'))
        {
            if ($sa == "editlabelset" && Permission::model()->hasGlobalPermission('labelsets','update'))
            {
                $result = LabelSet::model()->findAllByAttributes(array('lid' => $lid));
                foreach ($result as $row)
                {
                    $row = $row->attributes;
                    $lbname = $row['label_name'];
                    $lblid = $row['lid'];
                    $langids = $row['languages'];
                }
                $aData['lbname'] = $lbname;
                $aData['lblid'] = $lblid;
            }

            $aData['action'] = $sa;
            $aData['lid'] = $lid;

            if ($sa == "newlabelset" && Permission::model()->hasGlobalPermission('labelsets','create'))
            {
                $langids = Yii::app()->session['adminlang'];
                $tabitem = $clang->gT("Create new label set");
            }
            else
                $tabitem = $clang->gT("Edit label set");

            $langidsarray = explode(" ", trim($langids)); // Make an array of it

            if (isset($row['lid']))
                $panecookie = $row['lid'];
            else
                $panecookie = 'new';

            $aData['langids'] = $langids;
            $aData['langidsarray'] = $langidsarray;
            $aData['panecookie'] = $panecookie;
            $aData['tabitem'] = $tabitem;

            $aViewUrls['editlabel_view'][] = $aData;
        }

        $this->_renderWrappedTemplate('labels', $aViewUrls, $aData);

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

        Yii::app()->session['FileManagerContext'] = "edit:label:{$lid}";

        // Gets the current language
        $clang = $this->getController()->lang;
        $action = 'labels';
        $aViewUrls = array();
        $aData = array();

        // Includes some javascript files
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . 'labels.js');
        App()->getClientScript()->registerPackage('jquery-json');
        // Checks if user have the sufficient rights to manage the labels
        if (Permission::model()->hasGlobalPermission('labelsets','read'))
        {
            // Get a result containing labelset with the specified id
            $result = LabelSet::model()->findByAttributes(array('lid' => $lid));

            // If there is label id in the variable $lid and there are labelset records in the database
            $labelset_exists = !empty($result);

            if ($lid && $labelset_exists)
            {
                // Now recieve all labelset information and display it
                $aData['lid'] = $lid;
                $aData['clang'] = $clang;
                $aData['row'] = $result->attributes;

                // Display a specific labelbar menu
                $aViewUrls['labelbar_view'][] = $aData;

                $rwlabelset = $result;

                // Make languages array from the current row
                $lslanguages = explode(" ", trim($result['languages']));

                Yii::app()->loadHelper("admin/htmleditor");

                $aViewUrls['output'] = PrepareEditorScript(false, $this->getController());

                $criteria = new CDbCriteria;
                $criteria->select = 'max(sortorder) as maxsortorder, sortorder';
                $criteria->addCondition('lid = :lid');
                $criteria->addCondition('language = :language');
                $criteria->params = array(':lid' => $lid, ':language' => $lslanguages[0]);
                $criteria->group = 'sortorder';
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

                $aViewUrls['labelview_view'][] = array(
                    'results' => $results,
                    'lslanguages' => $lslanguages,
                    'clang' => $clang,
                    'lid' => $lid,
                    'maxsortorder' => $maxsortorder,
                //    'msorow' => $maxresult->sortorder,
                    'action' => $action,
                );
            }
        }

        $this->_renderWrappedTemplate('labels', $aViewUrls, $aData);
    }

    /**
     * Process labels form data depending on $action.
     *
     * @access public
     * @return void
     */
    public function process()
    {
        if ( Permission::model()->hasGlobalPermission('labelsets','update'))
        {
            if (isset($_POST['method']) && get_magic_quotes_gpc())
                $_POST['method'] = stripslashes($_POST['method']);

            $action = returnGlobal('action');
            Yii::app()->loadHelper('admin/label');
            $lid = returnGlobal('lid');

            if ($action == "updateset")
            {
                updateset($lid);
                Yii::app()->setFlashMessage(Yii::app()->lang->gT("Label set properties sucessfully updated."),'success');
            }
            if ($action == "insertlabelset")
                $lid = insertlabelset();
            if (($action == "modlabelsetanswers") || ($action == "ajaxmodlabelsetanswers"))
                modlabelsetanswers($lid);
            if ($action == "deletelabelset")
            {
                if (deletelabelset($lid))
                {
                    Yii::app()->setFlashMessage(Yii::app()->lang->gT("Label set sucessfully deleted."),'success');
                    $lid = 0;
                }
            }


            if ($lid)
                $this->getController()->redirect(array("admin/labels/sa/view/lid/" . $lid));
            else
                $this->getController()->redirect(array("admin/labels/sa/view"));
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
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . 'labels.js');
        $this->_renderWrappedTemplate('labels', 'exportmulti_view');
    }

    public function getAllSets()
    {
        $results = LabelSet::model()->findAll();

        $output = array();

        foreach($results as $row)
        {
            $output[$row->lid] = flattenText($row->getAttribute('label_name'));
        }
        header('Content-type: application/json');
        echo ls_json_encode($output);
    }

    public function ajaxSets()
    {
        $lid = Yii::app()->getRequest()->getPost('lid');
        $answers = Yii::app()->getRequest()->getPost('answers');
        $code = Yii::app()->getRequest()->getPost('code');
        //Create new label set
        $language = "";
        foreach ($answers as $lang => $answer) {
            $language .= $lang." ";
        }
        $language = trim($language);
        if ($lid == 0)
        {
            $lset = new LabelSet;
            $lset->label_name = Yii::app()->getRequest()->getPost('laname');
            $lset->languages = $language;
            $lset->save();

            $lid = getLastInsertID($lset->tableName());
        }
        else
        {
            Label::model()->deleteAll('lid = :lid', array(':lid' => $lid));
        }
        $res = 'ok'; //optimistic
        foreach($answers as $lang => $answer) {
            foreach ($answer as $key => $ans)
            {
                $label = new Label;
                $label->lid = $lid;
                $label->code = $code[$key];
                $label->title = $ans;
                $label->sortorder = $key;
                $label->language = $lang;
                if(!$label->save())
                    $res = 'fail';
            }
        }
        echo ls_json_encode($res);
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'labels', $aViewUrls = array(), $aData = array())
    {
        if (!isset($aData['display']['menu_bars']['labels']) || $aData['display']['menu_bars']['labels'] != false)
        {
            if (empty($aData['labelsets']))
            {
                $aData['labelsets'] = getLabelSets();
            }

            if (empty($aData['lid']))
            {
                $aData['lid'] = 0;
            }

            $aViewUrls = (array) $aViewUrls;

            array_unshift($aViewUrls, 'labelsetsbar_view');
        }

        $aData['display']['menu_bars'] = false;

        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }
 }
