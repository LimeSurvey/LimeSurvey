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
        $model = LabelSet::model()->findByPk($lid);
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

            $maxSortOrder = array_reduce(
                $model->labels, 
                function ($mixed, $item) {
                    if (((int) $item->sortorder) > $mixed) {
                        $mixed = (int) $item->sortorder;
                    }
                    return $mixed;
                },
                0
            );


            Yii::app()->loadHelper("surveytranslator");
            $results = $model->labels;
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
            Yii::app()->setFlashMessage(gT("Label set successfully saved."), 'success');
        }
        if ($action == "insertlabelset" && Permission::model()->hasGlobalPermission('labelsets', 'create')) {
                    $oLabelSet = insertlabelset();
                    $lid = $oLabelSet->lid;
        }
        if (($action == "modlabelsetanswers" || ($action == "ajaxmodlabelsetanswers")) && Permission::model()->hasGlobalPermission('labelsets', 'update')) {
                    modlabelsetanswers($lid);
        }
        if ($action == "deletelabelset" && Permission::model()->hasGlobalPermission('labelsets', 'delete')) {
            if (LabelSet::model()->deleteLabelSet($lid)) {
                Yii::app()->setFlashMessage(gT("Label set sucessfully deleted."), 'success');
                $lid = 0;
            }
        }

        if ($lid) {
                    $this->getController()->redirect(array("admin/labels/sa/multieditor/lid/".$lid));
        } else {
                    $this->getController()->redirect(array("admin/labels/sa/view"));
        }
    }

    public function saveNewLabelSet() {
        $label_name   = Yii::app()->request->getPost('label_name');
        $languageids  = Yii::app()->request->getPost('languageids');
        $oLabelSet = new LabelSet();
        $oLabelSet->label_name = $label_name;
        $oLabelSet->languages = implode(' ', $languageids);
        if ($oLabelSet->save()) {
            Yii::app()->setFlashMessage(gT("Label set sucessfully created."), 'success');
            $this->getController()->redirect(array("admin/labels/sa/multieditor/lid/".$oLabelSet->lid));
        } else { 
            Yii::app()->setFlashMessage(gT("Label could not be created."), 'error');
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
        if($oLabelsSet->deleteLabelSet($lid)) {
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
                $label->sortorder = $key;
                $label->language = $lang;
                $label->assessment_value = isset($aAssessmentValues[$key]) ? $aAssessmentValues[$key] : 0;
                if (!$label->save()) {
                    $res = 'fail';
                }
                
                $labelI10N = new LabelL10n;
                $labelI10N->language = $lang;
                $labelI10N->label_id = $label->id;
                $labelI10N->title = $ans;
                if (!$labelI10N->save()) {
                    $res = 'fail';
                }
            }
        }
        echo ls_json_encode($res);
    }

    public function ajxGetLabelSet($lid=null) {
        $oLabelSetObject = LabelSet::model()->findByPk($lid);
        if ($oLabelSetObject == null) {
            $oLabelSetObject = new LabelSet();
        }
        
        $aLanguages = explode(" ", $oLabelSetObject->languages);
        $sMainLanguage = $aLanguages[0];
        $aAllLanguages = getLanguageData(false, Yii::app()->session['adminlang']);
        $aLanguageArray = [];
        array_walk(
            $aLanguages,
            function ($sLanguage) use (&$aLanguageArray, $aAllLanguages) {
                $aLanguageArray[$sLanguage] =  $aAllLanguages[$sLanguage]['description'];
            }
        );

        $aLabels = $oLabelSetObject->labels;
        $aLabelCompleteArray = [];
        array_walk(
            $aLabels, 
            function ($oLabel) use (&$aLabelCompleteArray) {
                $aLabelCompleteArray[] = array_merge($oLabel->attributes, $oLabel->labell10ns);
            }
        );

        $aData['languages'] = $aLanguageArray;
        $aData['mainLanguage'] = $sMainLanguage;
        $aData['labels'] = $aLabelCompleteArray;

        Yii::app()->getController()->renderPartial('/admin/super/_renderJson', ['data' => $aData]);
    }

    /**
     * @param $lid
     *
     * @throws CException
     */
    public function ajxSetLabelSet($lid)
    {
        $oLabelSetObject = LabelSet::model()->findByPk($lid);
        $aLabelSetData = App()->request->getPost('labelSetData', []);
        $aLanguages = $oLabelSetObject->languageArray;
        $aLabels = $aLabelSetData['labels'];
        $result = true;
        $oDB = App()->db;
        $oTransaction = $oDB->beginTransaction();
        try {
            $oLabelSetObject->deleteLabelsForLabelSet();

            foreach ($aLabels as $aLabel) {
                $oLabel = $this->_getLabelObject($aLabel['id']);
                $oLabel->lid = $aLabel['lid'];
                $oLabel->code = $aLabel['code'];
                $oLabel->sortorder = $aLabel['sortorder'];
                $oLabel->assessment_value = $aLabel['assessment_value'];
                $result = $result && $oLabel->save();
                foreach ($aLanguages as $sLanguage) {
                    $oLabelI10N = $this->_getLabelI10NObject($oLabel->id, $sLanguage);
                    $oLabelI10N->title = $aLabel[$sLanguage]['title'] == '' ? $aLabel[$aLanguages[0]]['title'] : $aLabel[$sLanguage]['title'];
                    $result = $result && $oLabelI10N->save();
                }
            }

            $oTransaction->commit();

            App()->getController()->renderPartial(
                '/admin/super/_renderJson', [
                    'data' => [
                        'success' => $result,
                        'message' => $result ? gT('Label set successfully saved.') : gT("Label set couldn't be saved")
                    ]
                ]
            );
        } catch (Exception $e) {
            $oTransaction->rollback();
            App()->getController()->renderPartial(
                '/admin/super/_renderJson', [
                    'data' => [
                        'success' => false,
                        'message' => gT("Label set couldn't be saved")
                    ]
                ]
            );
        }
    }
    
    public function multieditor($lid = null) {

        $aData = [];

        $aData['labelbar'] = [
            'savebutton' => [
                'form' => 'mainform',
                'text' => gT("Save changes")
            ],
            'closebutton' => ['url' => Yii::app()->request->getUrlReferrer(Yii::app()->createUrl('admin/labels/sa/view'))],
            'buttons' => [
                'edition' => false,
            ],
        ];

        if (!Permission::model()->hasGlobalPermission('labelsets', 'update')) {
            unset($aData['labelbar']['buttons']['edition']);
        }

        $aData['jsVariables'] = [
            'lid' => $lid,
            'getDataUrl' => Yii::app()->createUrl('/admin/labels/sa/ajxGetLabelSet', ['lid' => $lid]),
            'setDataUrl' => Yii::app()->createUrl('/admin/labels/sa/ajxSetLabelSet', ['lid' => $lid]),
            'i10N' => [
                'Language' => gT('Language'),
                'Answer options' => gT('Answer options'),
                'Subquestions' => gT('Subquestions'),
            ]
        ];
        Yii::app()->getClientScript()->registerPackage('labelsets');
        $this->_renderWrappedTemplate('labels', 'labelSetEditor', $aData);
    }

    public function getLabelSetsForQuestion() {
        $languages = Yii::app()->request->getParam('languages', null);
        $oCriteria = new CDbCriteria();
        if ($languages != null) {
            array_walk(
                $languages,
                function ($lng) use (&$oCriteria) {
                    $oCriteria->compare('languages', $lng, true, 'OR');
                }
            );
        }
        $aLabelSets = LabelSet::model()->findAll($oCriteria);

        $returnArray = [];
        foreach ($aLabelSets as $oLabelSet) {
            $aLabelSet = $oLabelSet->attributes;
            $aLabelSet['labels'] = array_map(
                function ($oLabel) {
                    return array_merge($oLabel->attributes, $oLabel->labell10ns);
                },
                $oLabelSet->labels
            );
            $returnArray[] = $aLabelSet;
        }
        
        Yii::app()->getController()->renderPartial(
            '/admin/super/_renderJson', ['data' => $returnArray]
        );
        die();
    }

    public function newLabelSetFromQuestionEditor() {
        $aLabelSet = Yii::app()->request->getPost('labelSet', []);
        $oLabelSet = new LabelSet();
        $aLabels = $aLabelSet['labels'];
        $oLabelSet->label_name = $aLabelSet['label_name'];
        $oLabelSet->languages = $aLabelSet['languages'];
        $result = $oLabelSet->save();
        $aDebug['saveLabelSet'] = $result;

        foreach ($aLabelSet['labels'] as $i => $aLabel) {
            $oLabel = new Label();
            $oLabel->lid = $oLabelSet->lid;
            $oLabel->code = isset($aLabel['code']) 
                ? $aLabel['code'] 
                : $aLabel['title'];
            $oLabel->sortorder = $i;
            $oLabel->assessment_value = isset($aLabel['assessment_value']) ? $aLabel['assessment_value'] : 0;
            $partResult = $oLabel->save();
            $aDebug['saveLabel_'.$i] = $partResult;
            $result = $result && $partResult;
            foreach ($oLabelSet->languageArray as $language) {
                $oLabelL10n = new LabelL10n();
                $oLabelL10n->label_id = $oLabel->id;
                $oLabelL10n->language = $language;
                $oLabelL10n->title = isset($aLabel[$language]['question']) 
                    ? $aLabel[$language]['question'] 
                    : $aLabel[$language]['answer'];
                
                $lngResult = $oLabelL10n->save();
                $aDebug['saveLabel_'.$i.'_'.$language] = $lngResult;
                $result = $result && $lngResult;
            }
        }

        Yii::app()->getController()->renderPartial(
            '/admin/super/_renderJson', ['data' => [
                'success' => $result,
                'message' => gT('Label set successfully saved')
            ]]
        );
        die();

    }

    private function _getLabelI10NObject($labelId, $language) {
        $oLabelL10n = LabelL10n::model()->findByAttributes(['label_id' => $labelId, 'language' => $language]);
        if ($oLabelL10n == null) {
            $oLabelL10n = new LabelL10n();
            $oLabelL10n->label_id = $labelId;
            $oLabelL10n->language = $language;
        }
        return $oLabelL10n;
    }

    private function _getLabelObject($labelId) {
        $oLabel = Label::model()->findByPk($labelId);
        return $oLabel == null ? (new Label()) : $oLabel;
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
