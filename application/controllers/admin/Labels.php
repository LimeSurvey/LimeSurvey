<?php

/*
* LimeSurvey
* Copyright (C) 2007-2026 The LimeSurvey Project Team
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

use LimeSurvey\DI;
use LimeSurvey\Models\Services\LabelSetService;
use LimeSurvey\Models\Services\Exception\{
    BadRequestException,
    ConflictException,
    NotFoundException,
    PermissionDeniedException,
    PersistErrorException
};

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
            $folders = array('files', 'images');
            $hasResourceFolders = false;
            foreach ($folders as $folder) {
                if (is_dir($extractdir . "/" . $folder)) {
                    $hasResourceFolders = true;
                    break;
                }
            }
            foreach ($folders as $folder) {
                $filterImportedService = new \LimeSurvey\Models\Services\FilterImportedResources();
                list($_aImportedFilesInfo, $_aErrorFilesInfo) = $filterImportedService->filterImportedResources($extractdir . "/" . $folder, $destdir . $folder);
                $aImportedFilesInfo = array_merge($aImportedFilesInfo, $_aImportedFilesInfo);
                $aErrorFilesInfo = array_merge($aErrorFilesInfo, $_aErrorFilesInfo);
            }

            // Deletes the temp directory
            rmdirr($extractdir);

            // Delete the temporary file
            unlink($zipfilename);

            if (empty($aErrorFilesInfo) && empty($aImportedFilesInfo)) {
                $sError = $hasResourceFolders
                    ? gT("This ZIP archive contains no valid Resources files. Import failed.")
                    : gT("The ZIP archive must contain a 'files' and/or an 'images' folder at its top level, with the resource files placed inside. Import failed.");
                $this->getController()->error($sError, $this->getController()->createUrl("admin/labels/sa/view/lid/{$lid}"));
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
        /* unknown usage */
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
                'hasPermissionExport' => $lid && LabelSet::model()->findByPk($lid)->hasPermission('labelset', 'export')
            ],
            true
        );
        $aData['topbar']['title'] = $pageTitle;
        $aData['topbar']['backLink'] = App()->createUrl('admin/labels/sa/view');

        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/admin/labels/partials/topbarBtns_newimport/rightSideButtons',
            [
                'hasPermissionExport' => $lid && LabelSet::model()->findByPk($lid)->hasPermission('labelset', 'export')
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

        $model = LabelSet::model()->findByPk($lid);
        if ($lid > 0) {
            $lid = $this->validateLabelSetId($lid, 'read');
            // Now receive all labelset information and display it
            $aData['lid'] = $lid;
            $aData['row'] = $model->attributes;

            // Make languages array from the current row
            $lslanguages = explode(" ", trim((string) $model->languages));

            Yii::app()->loadHelper("admin.htmleditor");

            initKcfinder();

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
            $aData['topbar']['title'] = gT('Label set list');
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
            $aData['topbar']['title'] = gT('Label set list');
            $aData['topbar']['middleButtons'] = Yii::app()->getController()->renderPartial(
                '/admin/labels/partials/topbarBtns_singlelabelset/leftSideButtons',
                [
                    'hasUpdatePermission' => $model->hasPermission('labelset', 'update'),
                    'hasDeletePermission' => $model->hasPermission('labelset', 'delete'),
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
     * Note: Requires the global labelsets export permission, matching admin/export/sa/dumplabel.
     *
     * @return void
     * @throws CHttpException
     */
    public function exportmulti()
    {
        if (!Permission::model()->hasGlobalPermission('labelsets', 'export')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
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
        $results = LabelSet::model()->findAll(array('order' => 'label_name'));
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
        // Same list as the overwrite list of the new editor
        $output = array();
        foreach ($this->getLabelSetService()->getLabelSets([], true) as $labelSet) {
            $output[$labelSet['lid']] = $labelSet['name'];
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
     */
    public function ajaxSave()
    {
        if (!Permission::model()->hasGlobalPermission('labelsets', 'create')) {
            throw new CHttpException(403);
        }
        $labelName = Yii::app()->getRequest()->getPost('laname');
        if (empty($labelName) || !is_string($labelName)) {
            throw new CHttpException(400, gT('Could not save label set: Label set name is empty.'));
        }
        if (empty(Yii::app()->getRequest()->getPost('answers'))) {
            throw new CHttpException(400, gT('Could not save label set: Found no answers.'));
        }

        $this->saveLabelSetFromRequest(null, $labelName);

        eT('Label set successfully saved.');
    }

    /**
     * Ajax Update
     * @return void
     */
    public function ajaxUpdate()
    {
        $labelSetId = $this->validateLabelSetId(App()->getRequest()->getParam('labelSetId'), 'update');

        $this->saveLabelSetFromRequest((int)$labelSetId);

        eT('Label set successfully updated');
    }

    /**
     * Get the label set service, which both question editors use to read and write label sets.
     *
     * @return LabelSetService
     */
    private function getLabelSetService(): LabelSetService
    {
        return DI::getContainer()->get(LabelSetService::class);
    }

    /**
     * Create a label set, or overwrite one, from the answers posted by the question editor.
     *
     * @param int|null $labelSetId Label set to overwrite, null to create a new one
     * @param string $labelName Name of the new label set
     * @return void
     * @throws CHttpException When the label set can't be saved
     */
    private function saveLabelSetFromRequest(?int $labelSetId, string $labelName = '')
    {
        $request = Yii::app()->getRequest();
        $languages = (array)$request->getPost('languages', []);
        $labels = $this->getLabelsFromRequest($request);
        try {
            if ($labelSetId === null) {
                $this->getLabelSetService()->create($labelName, $languages, $labels);
            } else {
                $this->getLabelSetService()->overwrite($labelSetId, $languages, $labels);
            }
        } catch (PermissionDeniedException $exception) {
            throw new CHttpException(403);
        } catch (NotFoundException $exception) {
            throw new CHttpException(404, gT("Label set not found"));
        } catch (BadRequestException $exception) {
            throw new CHttpException(400, $exception->getMessage());
        } catch (ConflictException $exception) {
            // Another overwrite of the same set is still running
            throw new CHttpException(409, $exception->getMessage());
        } catch (PersistErrorException $exception) {
            throw new CHttpException(500, $exception->getMessage());
        }
    }

    /**
     * Turn the answer options or subquestions posted by the classic question editor
     * into labels for LabelSetService.
     *
     * The editor posts the text inputs keyed by their names, which PHP turns into
     * answers[<row>][<scale id>][answeroptionl10n|subquestionl10n][<language>] = text,
     * plus the codes (and assessment values) of the rows in the same order.
     *
     * @param CHttpRequest $request
     * @return array Labels in the shape LabelSetService expects
     */
    private function getLabelsFromRequest(CHttpRequest $request): array
    {
        $codes = (array)$request->getPost('codes', []);
        $assessmentValues = (array)$request->getPost('assessmentvalues', []);
        $labels = [];
        $rowIndex = 0;
        foreach ((array)$request->getPost('answers', []) as $row) {
            foreach ((array)$row as $l10nGroups) {
                $l10ns = [];
                foreach ((array)$l10nGroups as $texts) {
                    foreach ((array)$texts as $language => $text) {
                        $l10ns[$language] = $text;
                    }
                }
                $labels[] = [
                    'code' => $codes[$rowIndex] ?? null,
                    'assessmentValue' => $assessmentValues[$rowIndex] ?? null,
                    'l10ns' => $l10ns,
                ];
            }
            $rowIndex++;
        }
        return $labels;
    }

    /**
     * Get Label Sets for Question
     * @return void
     */
    public function getLabelSetsForQuestion()
    {
        $languages = Yii::app()->request->getParam('languages', null);
        // Same filter as the new editor: only sets that contain all of the languages
        $lids = array_column(
            $this->getLabelSetService()->getLabelSets(is_array($languages) ? $languages : []),
            'lid'
        );
        $aLabelSets = empty($lids) ? [] : LabelSet::model()->findAllByPk($lids, ['order' => 'label_name']);

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
     * @param bool         $sRenderFile
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
     *
     * Note: The permission is checked as the CRUD part of the 'labelset' entity permission,
     * global labelsets permission or ownership grant it (see LabelSet::hasPermission()).
     *
     * @param mixed $lid The label set id, sanitized to integer
     * @param string $permission The CRUD permission to check (read, update, delete, export)
     * @return integer The label set id
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
        if (!LabelSet::model()->findByPk($lid)->hasPermission('labelset', $permission)) {
            throw new CHttpException(403);
        }
        return $lid;
    }
}
