<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
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
    public function run($sa = null)
    {
        if ($sa == 'newlabelset' || $sa == 'editlabelset') {
                    $this->route('index', array('sa', 'lid'));
        }
    }

    /**
     * Function responsible to import label resources from a '.zip' file.
     *
     * @access public
     * @return void
     */
    public function importlabelresources()
    {
        if (!Permission::model()->hasGlobalPermission('labelsets', 'edit')) {
            Yii::app()->session['flashmessage'] = gT('Access denied!');
            $this->getController()->redirect(App()->createUrl("/admin"));
        }
        $lid = returnGlobal('lid');
        if (!empty($lid)) {
            if (Yii::app()->getConfig('demoMode')) {
                            $this->getController()->error(gT("Demo mode only: Uploading files is disabled in this system."), $this->getController()->createUrl("admin/labels/sa/view/lid/{$lid}"));
            }

            // Create temporary directory
            // If dangerous content is unzipped
            // then no one will know the path
            Yii::import('application.helpers.common_helper', true);
            $extractdir = createRandomTempDir();
            $zipfilename = $_FILES['the_file']['tmp_name'];
            $basedestdir = Yii::app()->getConfig('uploaddir')."/labels";
            $destdir = $basedestdir."/$lid/";

            Yii::app()->loadLibrary('admin.pclzip');
            $zip = new PclZip($zipfilename);

            if (!is_writeable($basedestdir)) {
                            $this->getController()->error(sprintf(gT("Incorrect permissions in your %s folder."), $basedestdir), $this->getController()->createUrl("admin/labels/sa/view/lid/{$lid}"));
            }

            if (!is_dir($destdir)) {
                            mkdir($destdir);
            }

            $aImportedFilesInfo = array();
            $aErrorFilesInfo = array();

            if (is_file($zipfilename)) {
                if ($zip->extract($extractdir) <= 0) {
                                    $this->getController()->error(gT("This file is not a valid ZIP file archive. Import failed. ".$zip->errorInfo(true)), $this->getController()->createUrl("admin/labels/sa/view/lid/{$lid}"));
                }

                // now read tempdir and copy authorized files only
                $folders = array('flash', 'files', 'images');
                foreach ($folders as $folder) {
                    list($_aImportedFilesInfo, $_aErrorFilesInfo) = $this->_filterImportedResources($extractdir."/".$folder, $destdir.$folder);
                    $aImportedFilesInfo = array_merge($aImportedFilesInfo, $_aImportedFilesInfo);
                    $aErrorFilesInfo = array_merge($aErrorFilesInfo, $_aErrorFilesInfo);
                }

                // Deletes the temp directory
                rmdirr($extractdir);

                // Delete the temporary file
                unlink($zipfilename);

                if (is_null($aErrorFilesInfo) && is_null($aImportedFilesInfo)) {
                                    $this->getController()->error(gT("This ZIP archive contains no valid Resources files. Import failed."), $this->getController()->createUrl("admin/labels/sa/view/lid/{$lid}"));
                }
            } else {
                            $this->getController()->error(gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder."), $this->getController()->createUrl("admin/labels/sa/view/lid/{$lid}"));
            }

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
        if (!Permission::model()->hasGlobalPermission('labelsets', 'import')) {
            Yii::app()->session['flashmessage'] = gT('Access denied!');
            $this->getController()->redirect(App()->createUrl("/admin"));
        }
        $action = returnGlobal('action');
        $aViewUrls = array();

        if ($action == 'importlabels') {
            Yii::app()->loadHelper('admin/import');

            $sFullFilepath = Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR.randomChars(20);
            $aPathInfo = pathinfo($_FILES['the_file']['name']);
            $sExtension = !empty($aPathInfo['extension']) ? $aPathInfo['extension'] : '';

            if ($_FILES['the_file']['error'] == 1 || $_FILES['the_file']['error'] == 2) {
                Yii::app()->setFlashMessage(sprintf(gT("Sorry, this file is too large. Only files up to %01.2f MB are allowed."), getMaximumFileUploadSize() / 1024 / 1024), 'error');
                $this->getController()->redirect(App()->createUrl("/admin/labels/sa/newlabelset"));
            }

            if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $sFullFilepath)) {
                Yii::app()->setFlashMessage(gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder."), 'error');
                $this->getController()->redirect(App()->createUrl("/admin/labels/sa/newlabelset"));
            }
            $options = $aImportResults = []; 
            $options['checkforduplicates'] = 'off';
            if ($_POST['checkforduplicates'] == 1) {
                $options['checkforduplicates'] = 'on';
            }
            if (strtolower($sExtension) == 'lsl') {
                            $aImportResults = XMLImportLabelsets($sFullFilepath, $options);
            } else {
                            $this->getController()->error(gT("Uploaded label set file needs to have an .lsl extension."));
            }

            unlink($sFullFilepath);

            $aViewUrls['import_view'][] = array('aImportResults' => $aImportResults);
        }

        $this->_renderWrappedTemplate('labels', $aViewUrls);
    }

    /**
     * Function to load new/edit labelset screen.
     *
     * @access public
     * @param integer $lid
     * @return
     */
    public function index($sa, $lid = 0)
    {
        Yii::app()->loadHelper('surveytranslator');

        $lid = sanitize_int($lid);
        $aViewUrls = $aData = [];

        if (Permission::model()->hasGlobalPermission('labelsets', 'read')) {
            if ($sa == "editlabelset" && Permission::model()->hasGlobalPermission('labelsets', 'update')) {
                $arLabelSet = LabelSet::model()->findByAttributes(array('lid' => $lid));
                $lbname = $arLabelSet->label_name;
                $lblid = $arLabelSet->lid;
                $langids = $arLabelSet->languages;
                $aData['lbname'] = $lbname;
                $aData['lblid'] = $lblid;
            }

            $aData['action'] = $sa;
            $aData['lid'] = $lid;

            if ($sa == "newlabelset" && Permission::model()->hasGlobalPermission('labelsets', 'create')) {
                $langids = Yii::app()->session['adminlang'];
                $tabitem = gT("New label set");
            } else {
                            $tabitem = gT("Edit label set");
            }

            $langidsarray = explode(" ", trim($langids)); // Make an array of it

            if (isset($row['lid'])) {
                            $panecookie = $row['lid'];
            } else {
                            $panecookie = 'new';
            }

            $aData['langids'] = $langids;
            $aData['langidsarray'] = $langidsarray;
            $aData['panecookie'] = $panecookie;
            $aData['tabitem'] = $tabitem;

            $aViewUrls['editlabel_view'][] = $aData;
        }


        $aData['labelbar']['buttons']['delete'] = ($sa != "newlabelset") ?true:false;
        $aData['labelbar']['buttons']['edition'] = true;
        $aData['labelbar']['savebutton']['form'] = 'labelsetform';
        $aData['labelbar']['savebutton']['text'] = gT("Save");
        $aData['labelbar']['closebutton']['url'] = Yii::app()->request->getUrlReferrer(Yii::app()->createUrl('admin/labels/sa/view')); // Close button, UrlReferrer
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
        if (!Permission::model()->hasGlobalPermission('labelsets', 'read')) {
            Yii::app()->session['flashmessage'] = gT('Access denied!');
            $this->getController()->redirect(App()->createUrl("/admin"));
        }
        // Escapes the id variable
        $lid = (int) $lid;

        Yii::app()->session['FileManagerContext'] = "edit:label:{$lid}";

        // Gets the current language
        $action = 'labels';
        $aViewUrls = array();
        $aData = array();

        // Includes some javascript files
        App()->getClientScript()->registerPackage('jquery-json');
        // Checks if user have the sufficient rights to manage the labels
        // Get a result containing labelset with the specified id
        $model = LabelSet::model()->findByAttributes(array('lid' => $lid));
        // If there is label id in the variable $lid and there are labelset records in the database
        $labelset_exists = $model !== null;
        
        
        if ($lid > 0 && $labelset_exists) {
            // Now recieve all labelset information and display it
            $aData['lid'] = $lid;
            $aData['row'] = $model->attributes;

            // Make languages array from the current row
            $lslanguages = explode(" ", trim($model->languages));

            Yii::app()->loadHelper("admin/htmleditor");

            $aViewUrls['output'] = PrepareEditorScript(false, $this->getController());

            $maxSortOrder = array_reduce($model->labels, function($mixed, $item)
            {
                if (((int) $item->sortorder) > $mixed) {
                    $mixed = (int) $item->sortorder;
                }
                return $mixed;
            },0);


            Yii::app()->loadHelper("surveytranslator");
            $results = array();
            foreach ($lslanguages as $lslanguage) {
                if (!$lslanguage) {
                    continue;
                }

                $results[] = array_filter($model->labels, function($item) use ($lslanguage)
                {
                    return ($item->language === $lslanguage);
                });
            }

            $aViewUrls['labelview_view'][] = array(
                'results' => $results,
                'lslanguages' => $lslanguages,
                'lid' => $lid,
                'maxsortorder' => $maxSortOrder,
                //    'msorow' => $maxresult->sortorder,
                'action' => $action,
                'model' => $model
            );
        } else {
            //show listing
            $aViewUrls['labelsets_view'][] = array();
            $model = LabelSet::model();
        }

        $aData['model'] = $model;

        if ($lid == 0) {
            $aData['labelbar']['buttons']['view'] = true;
        } else {
            $aData['labelbar']['buttons']['delete'] = true;
            $aData['labelbar']['savebutton']['form'] = 'mainform';
            $aData['labelbar']['savebutton']['text'] = gT("Save changes");
            $aData['labelbar']['closebutton']['url'] = Yii::app()->request->getUrlReferrer(Yii::app()->createUrl('admin/labels/sa/view'));
            $aData['labelbar']['buttons']['edition'] = true;

            $aData['labelbar']['buttons']['edit'] = true;
            if (!Permission::model()->hasGlobalPermission('labelsets', 'update')) {
                unset($aData['labelbar']['buttons']['edition']);
            }
        }

        if (isset($_GET['pageSize'])) {
            Yii::app()->user->setState('pageSize', (int) $_GET['pageSize']);
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
        if (!Permission::model()->hasGlobalPermission('labelsets', 'read')) {
            Yii::app()->session['flashmessage'] = gT('Access denied!');
            $this->getController()->redirect(App()->createUrl("/admin"));
        }
        $action = returnGlobal('action');
        Yii::app()->loadHelper('admin/label');
        $lid = (int) returnGlobal('lid');

        if ($action == "updateset" && Permission::model()->hasGlobalPermission('labelsets', 'update')) {
            updateset($lid);
            Yii::app()->setFlashMessage(gT("Label set properties sucessfully updated."), 'success');
        }
        if ($action == "insertlabelset" && Permission::model()->hasGlobalPermission('labelsets', 'create')) {
                    $lid = insertlabelset();
        }
        if (($action == "modlabelsetanswers" || ($action == "ajaxmodlabelsetanswers")) && Permission::model()->hasGlobalPermission('labelsets', 'update')) {
                    modlabelsetanswers($lid);
        }
        if ($action == "deletelabelset" && Permission::model()->hasGlobalPermission('labelsets', 'delete')) {
            if (deletelabelset($lid)) {
                Yii::app()->setFlashMessage(gT("Label set sucessfully deleted."), 'success');
                $lid = 0;
            }
        }
        if ($lid) {
                    $this->getController()->redirect(array("admin/labels/sa/view/lid/".$lid));
        } else {
                    $this->getController()->redirect(array("admin/labels/sa/view"));
        }
    }

    /**
     * Delete a label set
     *
     * @access public
     * @return void
     */
    public function delete()
    {
        if(!Yii::app()->getRequest()->isPostRequest) {
            throw new CHttpException(405, gT("Invalid action"));
        }
        if (!Permission::model()->hasGlobalPermission('labelsets', 'delete')) {
            throw new CHttpException(403, gT("You are not authorized to delete label sets.",'unescaped'));
        }
        $lid = Yii::app()->getRequest()->getParam('lid');
        $oLabelsSet = LabelSet::model()->findByPk($lid );
        if(empty($oLabelsSet)) {
            throw new CHttpException(404, gT("Invalid label set."));
        }
        if($oLabelsSet->delete()) {
            Yii::app()->setFlashMessage(sprintf(gT("Label set “%s” was successfully deleted."),CHtml::encode($oLabelsSet->label_name)));
        } else {
            Yii::app()->setFlashMessage(sprintf(gT("Unable to delete label set %s."),$lid));
        }
        $this->getController()->redirect(array("admin/labels/sa/view"));
    }

    /**
     * Multi label export
     *
     * @access public
     * @return void
     */
    public function exportmulti()
    {
        if (Permission::model()->hasGlobalPermission('labelsets', 'export')) {
            $aData = [];
            $aData['labelbar']['savebutton']['form'] = 'exportlabelset';
            $aData['labelbar']['savebutton']['text'] = gT("Export multiple label sets");
            $aData['labelbar']['closebutton']['url'] = Yii::app()->request->getUrlReferrer(Yii::app()->createUrl('admin/labels/sa/view'));
            $aData['labelbar']['buttons']['edition'] = true;
            $this->_renderWrappedTemplate('labels', 'exportmulti_view', $aData);
        }
    }

    public function getAllSets()
    {
        $results = LabelSet::model()->findAll();

        $output = array();

        foreach ($results as $row) {
            $output[$row->lid] = flattenText($row->getAttribute('label_name'));
        }
        header('Content-type: application/json');
        echo ls_json_encode($output);
    }

    public function ajaxSets()
    {
        $lid = (int) Yii::app()->getRequest()->getPost('lid');
        $answers = Yii::app()->getRequest()->getPost('answers');
        $code = Yii::app()->getRequest()->getPost('code');
        $aAssessmentValues = Yii::app()->getRequest()->getPost('assessmentvalues', array());
        //Create label set
        $language = "";
        foreach ($answers as $lang => $answer) {
            $language .= $lang." ";
        }
        $language = trim($language);
        if ($lid == 0) {
            $lset = new LabelSet;
            $lset->label_name = Yii::app()->getRequest()->getPost('laname');
            $lset->languages = $language;
            $lset->save();

            $lid = getLastInsertID($lset->tableName());
        } else {
            Label::model()->deleteAll('lid = :lid', array(':lid' => $lid));
        }
        $res = 'ok'; //optimistic
        foreach ($answers as $lang => $answer) {
            foreach ($answer as $key => $ans) {
                $label = new Label;
                $label->lid = $lid;
                $label->code = $code[$key];
                $label->title = $ans;
                $label->sortorder = $key;
                $label->language = $lang;
                $label->assessment_value = isset($aAssessmentValues[$key]) ? $aAssessmentValues[$key] : 0;
                if (!$label->save()) {
                                    $res = 'fail';
                }
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
    protected function _renderWrappedTemplate($sAction = 'labels', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts').'labels.js');

        if (!isset($aData['display']['menu_bars']['labels']) || $aData['display']['menu_bars']['labels'] != false) {
            if (empty($aData['labelsets'])) {
                $aData['labelsets'] = getLabelSets();
            }
            if (empty($aData['lid'])) {
                $aData['lid'] = 0;
            }
            $aViewUrls = (array) $aViewUrls;

            array_unshift($aViewUrls, 'labelsetsbar_view');
        }

        $aData['display']['menu_bars'] = false;

        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }
}
