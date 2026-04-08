<?php

/*
* LimeSurvey
* Copyright (C) 2007-2023 The LimeSurvey Project Team / Carsten Schmitz
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
class Labels extends SurveyCommonAction
{
    /**
     * routes to the correct subdir
     *
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
     * @return void
     */
    public function importlabelresources()
    {
        $lid = $this->validateLabelSetId(App()->request->getParam('lid'), 'update');
        if (Yii::app()->getConfig('demoMode')) {
            $this->getController()->error(gT("Demo mode only: Uploading files is disabled in this system."), $this->getController()->createUrl("admin/labels/sa/view/lid/{$lid}"));
        }

        // Create temporary directory
        // If dangerous content is unzipped
        // then no one will know the path
        Yii::import('application.helpers.common_helper', true);
        $extractdir = createRandomTempDir();
        $zipfilename = $_FILES['the_file']['tmp_name'];
        $basedestdir = Yii::app()->getConfig('uploaddir') . "/labels";
        $destdir = $basedestdir . "/$lid/";

        if (!is_writeable($basedestdir)) {
            Yii::app()->setFlashMessage(sprintf(gT("Incorrect permissions in your %s folder."), $basedestdir), 'error');
            $this->getController()->redirect(App()->createUrl("admin/labels/sa/view/lid/{$lid}"));
        }

        if (!is_dir($destdir)) {
            mkdir($destdir);
        }

        $aImportedFilesInfo = array();
        $aErrorFilesInfo = array();

        if (is_file($zipfilename)) {
            $zip = new LimeSurvey\Zip();
            if ($zip->open($zipfilename) !== true || $zip->extractTo($extractdir) !== true) {
                $this->getController()->error(gT("This file is not a valid ZIP file archive. Import failed. " . $zip->errorInfo(true)), $this->getController()->createUrl("admin/labels/sa/view/lid/{$lid}"));
            }
            $zip->close();

            // now read tempdir and copy authorized files only
            $folders = array('flash', 'files', 'images');
            foreach ($folders as $folder) {
                list($_aImportedFilesInfo, $_aErrorFilesInfo) = $this->filterImportedResources($extractdir . "/" . $folder, $destdir . $folder);
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

        $this->renderWrappedTemplate('labels', 'importlabelresources_view', $aData);
    }

    /**
     * Function to import a label set
     *
     * @return void
     */
    public function import()
    {
        if (!Permission::model()->hasGlobalPermission('labelsets', 'import')) {
            Yii::app()->session['flashmessage'] = gT('Access denied!');
            $this->getController()->redirect(App()->createUrl("/admin"));
        }
        $action = App()->getRequest()->getParam('action');
        $aViewUrls = array();

        // Check file size and redirect on error
        $uploadValidator = new LimeSurvey\Models\Services\UploadValidator();
        $uploadValidator->redirectOnError('the_file', \Yii::app()->createUrl("/admin/labels/sa/newlabelset"));

        if ($action == 'importlabels') {
            Yii::app()->loadHelper('admin.import');

            $sFullFilepath = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . randomChars(20);
            $aPathInfo = pathinfo((string) $_FILES['the_file']['name']);
            $sExtension = !empty($aPathInfo['extension']) ? $aPathInfo['extension'] : ''; // TODO: $sExtension is not used. Remove it.

            if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $sFullFilepath)) {
                Yii::app()->setFlashMessage(gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder."), 'error');
                $this->getController()->redirect(App()->createUrl("/admin/labels/sa/newlabelset"));
            }
            $options = $aImportResults = [];
            $options['checkforduplicates'] = 'off';
            if (App()->getRequest()->getPost('checkforduplicates') == 1) {
                $options['checkforduplicates'] = 'on';
            }

            $aImportResults = XMLImportLabelsets($sFullFilepath, $options);

            unlink($sFullFilepath);

            $aViewUrls['import_view'][] = array('aImportResults' => $aImportResults);
        }

        $this->renderWrappedTemplate('labels', $aViewUrls);
    }

    /**
     * Function to load new/edit labelset screen.
     *
     * @param string  $sa
     * @param integer $lid
     * @return void
     */
    public function index(string $sa, $lid = 0)
    {
        Yii::app()->loadHelper('surveytranslator');

        $lid = sanitize_int($lid);
        $aViewUrls = $aData = [];
        if (!in_array($sa, ['editlabelset', 'newlabelset'])) {
            throw new CHttpException(400);
        }
        $aData['action'] = $sa;
        $aData['lid'] = $lid;
        /* update */
        if ($sa == "editlabelset") {
            $lid = $this->validateLabelSetId($lid, 'update');
            $labelSet = LabelSet::model()->findByPk($lid);
            $lbname = $labelSet->label_name;
            $lblid = $labelSet->lid;
            $langids = $labelSet->languages;
            $aData['lbname'] = $lbname;
            $aData['lblid'] = $lblid;
            $tabitem = gT("Edit label set");
            $pageTitle = gT('Edit label set');
        }
        /* Create */
        if ($sa == "newlabelset") {
            if (!Permission::model()->hasGlobalPermission('labelsets', 'create')) {
                throw new CHttpException(403);
            }
            $langids = Yii::app()->session['adminlang'];
            $tabitem = gT("New label set");
            $pageTitle = gT('Create or import new label set(s)');
            $lid = null;
        }

        /* other sa ? What is the default ? */
        $langidsarray = explode(" ", trim((string) $langids)); // Make an array of it
        /* unknow usage */
        $panecookie = 'new';
        /* render data */
        $aData['langids'] = $langids;
        $aData['langidsarray'] = $langidsarray;
        $aData['panecookie'] = $panecookie;
        $aData['tabitem'] = $tabitem;
        $aViewUrls['editlabel_view'][] = $aData;
        $aData['topbar']['title'] = $pageTitle;
        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/admin/labels/partials/topbarBtns_newimport/rightSideButtons',
            [
                'hasPermissionExport' => $lid && LabelSet::model()->findByPk($lid)->haspermission('export')
            ],
            true
        );
        $aData['topbar']['title'] = $pageTitle;
        $aData['topbar']['backLink'] = App()->createUrl('admin/labels/sa/view');

        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/admin/labels/partials/topbarBtns_newimport/rightSideButtons',
            [
                'hasPermissionExport' => $lid && LabelSet::model()->findByPk($lid)->haspermission('export')
            ],
            true
        );
        $this->renderWrappedTemplate('labels', $aViewUrls, $aData);
    }

    /**
     * Function to view a labelset.
     *
     * @param int $lid
     * @return void
     */
    public function view(int $lid = 0)
    {
        Yii::app()->session['FileManagerContext'] = "edit:label:{$lid}";

        // Gets the current language
        $action = 'labels';
        $aViewUrls = array();
        $aData = array();

        // Includes some javascript files
        App()->getClientScript()->registerPackage('jquery-json');
        $model = LabelSet::model()->findByPk($lid);
        if ($lid > 0) {
            $lid = $this->validateLabelSetId($lid, 'read');
            // Now recieve all labelset information and display it
            $aData['lid'] = $lid;
            $aData['row'] = $model->attributes;

            // Make languages array from the current row
            $lslanguages = explode(" ", trim((string) $model->languages));

            Yii::app()->loadHelper("admin.htmleditor");

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
                'action' => $action,
                'model' => $model,
                'addRowUrl' => \Yii::app()->createUrl("/admin/labels/sa/getLabelRowForAllLanguages"),
            );
        } else {
            //show listing
            $aViewUrls['labelsets_view'][] = array();
            $model = LabelSet::model();
        }

        $aData['model'] = $model;

        if (isset($_GET['pageSize'])) {
            Yii::app()->user->setState('pageSize', (int) $_GET['pageSize']);
        }

        if ($lid == 0) {
            $aData['topbar']['title'] = gT('Label sets list');
            $aData['topbar']['middleButtons'] = Yii::app()->getController()->renderPartial(
                '/admin/labels/partials/topbarBtns/leftSideButtons',
                [
                    'hasPermissionCreate' => Permission::model()->hasGlobalPermission('labelsets', 'create')
                        || Permission::model()->hasGlobalPermission('labelsets', 'import')
                ],
                true
            );
            $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
                '/admin/labels/partials/topbarBtns/rightSideButtons',
                [
                    'hasPermissionExport' => Permission::model()->hasGlobalPermission('labelsets', 'export')
                ],
                true
            );
        } else {
            $aData['topbar']['title'] = gT('Label sets list');
            $aData['topbar']['middleButtons'] = Yii::app()->getController()->renderPartial(
                '/admin/labels/partials/topbarBtns_singlelabelset/leftSideButtons',
                [
                    'hasUpdatePermission' => $model->hasPermission('update'),
                    'hasDeletePermission' => $model->hasPermission('delete'),
                    'lid' => $lid
                ],
                true
            );
            $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
                '/admin/labels/partials/topbarBtns_singlelabelset/rightSideButtons',
                [],
                true
            );
        }

        $this->renderWrappedTemplate('labels', $aViewUrls, $aData);
    }

    /**
     * Process labels form data depending on $action.
     *
     * @return void
     */
    public function process()
    {
        $action = App()->getRequest()->getParam('action');
        Yii::app()->loadHelper('admin.label');
        if ($action == "updateset") {
            $lid = $this->validateLabelSetId(App()->getRequest()->getPost('lid'), 'update');
            updateset($lid);
            Yii::app()->setFlashMessage(gT("Label set successfully saved."), 'success');
        }
        if ($action == "insertlabelset") {
            if (!Permission::model()->hasGlobalPermission('labelsets', 'create')) {
                throw new CHttpException(403);
            }
            $this->requirePostRequest();
            $oLabelSet = insertlabelset();
            $lid = $oLabelSet->lid;
        }
        if ($action == "modlabelsetanswers" || ($action == "ajaxmodlabelsetanswers")) {
            $lid = $this->validateLabelSetId(App()->getRequest()->getPost('lid'), 'update');
            modlabelsetanswers($lid);
        }
        if ($action == "deletelabelset") {
            $lid = $this->validateLabelSetId(App()->getRequest()->getPost('lid'), 'delete');
            if (LabelSet::model()->deleteLabelSet($lid)) {
                Yii::app()->setFlashMessage(gT("Label set successfully deleted."), 'success');
            }
        }

        if (Yii::app()->request->getPost("saveandclose")) {
            $this->getController()->redirect(array("admin/labels/sa/view"));
        }

        if ($lid) {
            $this->getController()->redirect(array("admin/labels/sa/view/lid/" . $lid));
        } else {
            $this->getController()->redirect(array("admin/labels/sa/view"));
        }
    }

    /**
     * Save new label set
     * @todo : check if it's currently used.
     * @return void
     */
    public function saveNewLabelSet()
    {
        if (!Permission::model()->hasGlobalPermission('labelsets', 'create')) {
            throw new CHttpException(403);
        }
        $label_name   = Yii::app()->request->getPost('label_name');
        $languageids  = Yii::app()->request->getPost('languageids');
        $oLabelSet = new LabelSet();
        $oLabelSet->label_name = $label_name;
        $oLabelSet->owner_id = App()->user->getId();
        $oLabelSet->languages = implode(' ', $languageids);
        if ($oLabelSet->save()) {
            Yii::app()->setFlashMessage(gT("Label set successfully created."), 'success');
            $this->getController()->redirect(array("admin/labels/sa/view/lid/" . $oLabelSet->lid));
        } else {
            Yii::app()->setFlashMessage(gT("Label could not be created."), 'error');
            $this->getController()->redirect(array("admin/labels/sa/view"));
        }
    }

    /**
     * Delete a label set
     *
     * @return void
     */
    public function delete()
    {
        $this->requirePostRequest();
        $lid = $this->validateLabelSetId(App()->getRequest()->getParam('lid'), 'delete');
        $labelSet = LabelSet::model()->findByPk($lid);
        if ($labelSet->deleteLabelSet($lid)) {
            Yii::app()->setFlashMessage(sprintf(gT("Label set “%s” was successfully deleted."), CHtml::encode($labelSet->label_name)));
        } else {
            Yii::app()->setFlashMessage(sprintf(gT("Unable to delete label set %s."), $lid));
        }
        $this->getController()->redirect(array("admin/labels/sa/view"));
    }

    /**
     * Multi label export
     *
     * @return void
     */
    public function exportmulti()
    {
        $aData = [];

        $aData['topbar']['title'] = gT('Export multiple label sets');
        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/admin/labels/partials/topbarBtns_export/rightSideButtons',
            [],
            true
        );

        $this->renderWrappedTemplate('labels', 'exportmulti_view', $aData);
    }

    /**
     * Get all label sets
     *
     * @return void
     */
    public function getAllSets()
    {
        /* Using of label sets are not controlled all label sets can be used by every one */
        $results = LabelSet::model()->findAll();
        $output = array();
        foreach ($results as $row) {
            $output[$row->lid] = flattenText($row->getAttribute('label_name'));
        }
        header('Content-type: application/json');
        echo ls_json_encode($output);
    }

    /**
     * Get all label sets with permission to update
     *
     * @return void
     */
    public function getRestrictedSets()
    {
        $labelSets = LabelSet::model()->permission()->findAll();
        $output = array();
        foreach ($labelSets as $labelSet) {
            $output[$labelSet->lid] = flattenText($labelSet->getAttribute('label_name'));
        }
        header('Content-type: application/json');
        echo ls_json_encode($output);
    }

    /**
     * Save label set via Ajax
     * Used in question editor
     * Echoes JSON
     *
     * @return void
     * @todo Move save logic into service class.
     */
    public function ajaxSave()
    {
        if (!Permission::model()->hasGlobalPermission('labelsets', 'create')) {
            throw new CHttpException(403);
        }
        $request   = Yii::app()->getRequest();
        $answers   = $request->getPost('answers');
        $codes     = $request->getPost('codes');
        $labelName = $request->getPost('laname');
        $languages = implode(' ', $request->getPost('languages'));
        $assessmentValues = $request->getPost('assessmentvalues', []);
        if (empty($labelName)) {
            throw new CHttpException(400, gT('Could not save label set: Label set name is empty.'));
        }
        if (empty($answers)) {
            throw new CHttpException(400, gT('Could not save label set: Found no answers.'));
        }
        /* Create new labelset */
        try {
            $transaction      = Yii::app()->db->beginTransaction();
            $lset             = new LabelSet();
            $lset->label_name = $request->getPost('laname');
            $lset->languages  = trim($languages);
            $lset->owner_id   = App()->user->getId();
            $lset->save();
            $lid = getLastInsertID($lset->tableName());
            $this->saveLabelSetAux($lid, $codes, $answers, $assessmentValues);
            $transaction->commit();
        } catch (Exception $exception) {
            $transaction->rollback();
            throw new CHttpException(500, $exception->getMessage());
        }

        eT('Label set successfully saved');
    }

    /**
     * Ajax Update
     * @return void
     */
    public function ajaxUpdate()
    {
        $request = Yii::app()->request;
        $labelSetId = $this->validateLabelSetId(App()->getRequest()->getParam('labelSetId'), 'update');
        $answers   = $request->getPost('answers');
        $codes     = $request->getPost('codes');
        $assessmentValues = $request->getPost('assessmentvalues', []);
        $languages = implode(' ', $request->getPost('languages'));
        /* Update this label set */
        $labelSet = LabelSet::model()->findByPk($labelSetId);
        try {
            $transaction = Yii::app()->db->beginTransaction();
            $labelSet->languages = $languages;
            $labelSet->update();
            $this->saveLabelSetAux($labelSetId, $codes, $answers, $assessmentValues);
            $transaction->commit();
        } catch (Exception $exception) {
            $transaction->rollback();
            throw new CHttpException(500, $exception->getMessage());
        }

        eT('Label set successfully updated');
    }

    /**
     * Helper function to save label set from question editor.
     *
     * @param int   $lid               Label set id
     * @param array $codes
     * @param array $answers
     * @param array $assessmentValues
     * @return void
     * @throws Exception
     */
    private function saveLabelSetAux(int $lid, array $codes, array $answers, array $assessmentValues)
    {
        Label::model()->deleteAll('lid = :lid', [':lid' => $lid]);
        $i = 0;
        foreach ($answers as $answer) {
            foreach ($answer as $answeroptionl10ns) {
                $label = new Label();
                $label->lid = $lid;
                $label->code = $codes[$i];
                $label->sortorder = $i;
                $label->assessment_value = $assessmentValues[$i] ?? 0;
                if (!$label->save()) {
                    throw new Exception('Could not save label: ' . json_encode($label->getErrors()));
                }

                foreach ($answeroptionl10ns as $langs) {
                    foreach ($langs as $lang => $content) {
                        $labell10n = new LabelL10n();
                        $labell10n->language = $lang;
                        $labell10n->label_id = $label->id;
                        $labell10n->title = $content;
                        if (!$labell10n->save()) {
                            throw new Exception('Could not save label l10n: ' . json_encode($label->getErrors()));
                        }
                    }
                }
            }
            $i++;
        }
    }

    /**
     * Get Label Sets for Question
     * @return void
     */
    public function getLabelSetsForQuestion()
    {
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
            '/admin/super/_renderJson',
            ['data' => $returnArray]
        );
        App()->end();
    }

    /**
     * New label set from question editor
     * @deprecated : not used in 6.0 and before
     * @return void
     */
    public function newLabelSetFromQuestionEditor()
    {
        $aLabelSet = Yii::app()->request->getPost('labelSet', []);
        $oLabelSet = new LabelSet();
        $aLabels = $aLabelSet['labels'];
        $oLabelSet->label_name = $aLabelSet['label_name'];
        $oLabelSet->languages = $aLabelSet['languages'];
        $oLabelSet->owner_id = App()->user->getId();
        $result = $oLabelSet->save();
        $aDebug['saveLabelSet'] = $result;

        foreach ($aLabelSet['labels'] as $i => $aLabel) {
            $oLabel = new Label();
            $oLabel->lid = $oLabelSet->lid;
            $oLabel->code = $aLabel['code'] ?? $aLabel['title'];
            $oLabel->sortorder = $i;
            $oLabel->assessment_value = $aLabel['assessment_value'] ?? 0;
            $partResult = $oLabel->save();
            $aDebug['saveLabel_' . $i] = $partResult;
            $result = $result && $partResult;
            foreach ($oLabelSet->languageArray as $language) {
                $oLabelL10n = new LabelL10n();
                $oLabelL10n->label_id = $oLabel->id;
                $oLabelL10n->language = $language;
                $oLabelL10n->title = $aLabel[$language]['question'] ?? $aLabel[$language]['answer'];

                $lngResult = $oLabelL10n->save();
                $aDebug['saveLabel_' . $i . '_' . $language] = $lngResult;
                $result = $result && $lngResult;
            }
        }

        Yii::app()->getController()->renderPartial(
            '/admin/super/_renderJson',
            ['data' => [
                'success' => $result,
                'message' => gT('Label set successfully saved')
            ]]
        );
        App()->end();
    }

    /**
     * Get Label I10N Object
     *
     * @param int    $labelId   Label ID
     * @param string $language  Language Code
     * @return LabelL10n
     * @todo Not used?
     */
    private function getLabelI10NObject(int $labelId, string $language)
    {
        $oLabelL10n = LabelL10n::model()->findByAttributes(['label_id' => $labelId, 'language' => $language]);
        if ($oLabelL10n == null) {
            $oLabelL10n = new LabelL10n();
            $oLabelL10n->label_id = $labelId;
            $oLabelL10n->language = $language;
        }
        return $oLabelL10n;
    }

    /**
     * Get Label Object
     *
     * @param int $labelId Label ID
     * @return Label
     * @todo Not used?
     */
    private function getLabelObject(int $labelId): Label
    {
        $oLabel = Label::model()->findByPk($labelId);
        return $oLabel == null ? (new Label()) : $oLabel;
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string       $sAction     Current action, the folder to fetch views from
     * @param string|array $aViewUrls   View url(s)
     * @param array        $aData       Data to be passed on. Optional.
     * @parm  bool         $sRenderFile
     * @return void
     */
    protected function renderWrappedTemplate($sAction = 'labels', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'labels.js');

        if (!isset($aData['display']['menu_bars']['labels']) || $aData['display']['menu_bars']['labels'] != false) {
            if (empty($aData['labelsets'])) {
                $aData['labelsets'] = getLabelSets();
            }
            if (empty($aData['lid'])) {
                $aData['lid'] = 0;
            }
            $aViewUrls = (array) $aViewUrls;
        }

        $aData['display']['menu_bars'] = false;

        parent::renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }

    /**
     * Outputs json with the html for a new label row, for each language.
     * Eg: {"en":"<tr>...</tr>", "es":"<tr>...</tr>"}
     * Called by ajax from the label set editor.
     * @param int $lid
     * @param string $newId
     * @param string $code
     * @param int $assessmentValue
     * @param string $title
     */
    public function getLabelRowForAllLanguages($lid, $newId, $code, $assessmentValue = 0, $title = '')
    {
        $lid = $this->validateLabelSetId($lid, 'read');
        $labelSet = LabelSet::model()->findByPk($lid);
        $languages = explode(" ", (string) $labelSet->languages);
        $rowsHtml = [];
        $first = true;
        foreach ($languages as $language) {
            $rowsHtml[$language] = $this->getLabelRow($language, $first, $newId, $code, $assessmentValue, $title);
            $first = false;
        }
        header('Content-Type: application/json');
        echo json_encode($rowsHtml);
    }

    /**
     * Returns the html for a label row.
     * Used when user clicks "Add" in label set editor.
     * @param string $language
     * @param bool $first   Indicates whether the row belongs to the first language or not.
     * @param string $newId
     * @param string $code
     * @param int $assessmentValue
     * @param string $title
     * @return string   The html of the row
     */
    private function getLabelRow($language, $first, $newId, $code, $assessmentValue, $title)
    {
        $view = 'labelRow.twig';
        $aData = array(
            'language' => $language,
            'first' => $first,
            'rowId' => $newId,
            'code' => $code,
            'assessmentValue' => $assessmentValue,
            'title' => $title,
            'hasLabelSetUpdatePermission' => Permission::model()->hasGlobalPermission('labelsets', 'update')
        );

        $html = '<!-- Inserted Row -->';
        $html .= App()->twigRenderer->renderPartial('/admin/labels/' . $view, $aData);
        $html .= '<!-- end of Inserted Row -->';
        return $html;
    }

    /**
     * Sanitize existence and permission of LabelSet->pk, throw exception if there are an issue.
     * @param $lid mixed, sanitized to intreger
     * @param $permisson to check
     * @return integer : the label id
     * @throws CHttpException
     */
    private function validateLabelSetId($lid, $permission = 'read')
    {
        $lid = sanitize_int($lid);
        if (empty($lid)) {
            throw new CHttpException(400);
        }
        if (empty(LabelSet::model()->findByPk($lid))) {
            throw new CHttpException(404, gT("Label set not found"));
        }
        if (!LabelSet::model()->findByPk($lid)->hasPermission($permission)) {
            throw new CHttpException(403);
        }
        return $lid;
    }
}
