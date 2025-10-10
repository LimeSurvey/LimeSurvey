<?php

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
*/

use ls\ajax\AjaxHelper;
use LimeSurvey\Exceptions\CPDBException;

/**
 * @param array $a
 * @param string $subkey
 * @param string $order
 * @return array
 */
function subval_sort($a, $subkey, $order)
{
    $b = array();
    $c = array();
    foreach ($a as $k => $v) {
        $b[$k] = strtolower((string) $v[$subkey]);
    }
    if ($order == "asc") {
        asort($b, SORT_REGULAR);
    } else {
        arsort($b, SORT_REGULAR);
    }
    foreach ($b as $key => $val) {
        $c[] = $a[$key];
    }
    return $c;
}


/**
 * This is the main controller for Participants Panel
 */
class ParticipantsAction extends SurveyCommonAction
{
    /** @var AjaxHelper $ajaxHelper */
    protected $ajaxHelper;

    /**********************************************BASIC SETTINGS AND METHODS***********************************************/

    public function runWithParams($params)
    {
        if (
            !(Permission::model()->hasGlobalPermission('participantpanel', 'read')
            || Permission::model()->hasGlobalPermission('participantpanel', 'create')
            || Permission::model()->hasGlobalPermission('participantpanel', 'update')
            || Permission::model()->hasGlobalPermission('participantpanel', 'delete')
            || ParticipantShare::model()->exists('share_uid = :userid', [':userid' => App()->user->id]))
        ) {
            App()->setFlashMessage(gT('No permission'), 'error');
            App()->getController()->redirect(App()->request->urlReferrer);
        }

        Yii::import('application.helpers.admin.ajax_helper', true);
        Yii::import('application.helpers.admin.permission_helper', true);

        // Default AjaxHelper (overridden in tests).
        // NB: The reason we "inject" this here is because the
        // tests need a mock AjaxHelper instead of the "real thing"
        // that dies.
        $this->setAjaxHelper(new \ls\ajax\AjaxHelper());

        parent::runWithParams($params);
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function renderWrappedTemplate($sAction = 'participants', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        App()->getClientScript()->registerPackage('bootstrap-multiselect');
        $aData['display']['menu_bars'] = false;

        // Add "_view" to urls
        if (is_array($aViewUrls)) {
            array_walk($aViewUrls, function (&$url) {
                $url .= "_view";
            });
        } elseif (is_string($aViewUrls)) {
            $aViewUrls .= "_view";
        } else {
            // Complete madness
            throw new \InvalidArgumentException("aViewUrls must be either string or array");
        }

        parent::renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }

    /**
     * AJAX switcher for modal calling
     * @return void
     */
    public function openModalParticipantPanel()
    {
        $target = Yii::app()->request->getPost('modalTarget');
        switch ($target) {
            case "editparticipant":
                $this->openEditParticipant();
                break;
            case "shareparticipant":
                $this->openParticipantShare();
                break;
            case "showparticipantsurveys":
                $this->openParticipantSurveys();
                break;
            case "showdeleteparticipant":
                $this->openDeleteParticipant();
                break;
            case "editattribute":
                $this->openEditAttributeNames();
                break;
            case "addToSurvey":
                $this->openAddToSurvey();
                break;
            default:
                // Unknown modal target
                safeDie('Unknown method');
                break;
        }
    }

    /**
     * AJAX switcher for action calling
     * @return void
     */
    public function editValueParticipantPanel()
    {
        $target = Yii::app()->request->getPost('actionTarget');
        switch ($target) {
            case "changeBlacklistStatus":
                $this->changeblackliststatus();
                break;
            case "changeAttributeVisibility":
                $this->changeAttributeVisibility();
                break;
            case "changeAttributeEncrypted":
                $this->changeAttributeEncrypted();
                break;
            case "deleteLanguageFromAttribute":
                $this->deleteLanguageFromAttribute();
                break;
            case "deleteAttribute":
                $this->deleteSingleAttribute();
                break;
            case "deleteParticipant":
                $this->deleteParticipant();
                break;
            case "changeSharedEditableStatus":
                $this->changeSharedEditableStatus();
                break;
            case "rejectShareParticipant":
                $this->rejectShareParticipant();
                break;
            case "deleteSingleParticipantShare":
                //Todo - function parameters are missed
                $this->deleteSingleParticipantShare();
                break;
            case "deleteMultipleParticipantShare":
                $this->deleteMultipleParticipantShare();
                break;
            default:
                echo "";
                break;
        }
    }

    /**
     * Export to csv using optional search/filter
     *
     * @param CDbCriteria $search
     * @paran mixed $mAttributeIDs Empty array for no attributes, or array of attribute IDs or null for all attributes
     * @return false|null
     */
    private function csvExport($search = null, $aAttributeIDs = null)
    {
        $this-> checkPermission('export');

        Yii::app()->loadHelper('export');
        //If super admin all the participants will be visible
        if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $iUserID = null;
        } else {
            $iUserID = Yii::app()->session['loginID'];
        }
        $aAttributeIDs = array_combine($aAttributeIDs, $aAttributeIDs);
        $query = Participant::model()->getParticipants(0, 0, $aAttributeIDs, null, $search, $iUserID);
        if (!$query) {
            return false;
        }

        // Field names in the first row
        $fields = array('participant_id', 'firstname', 'lastname', 'email', 'language', 'blacklisted', 'owner_uid');
        $outputarray = array(); // The array to be passed to the export helper to be written to a csv file

        $outputarray[0] = $fields; //fields written to output array


        foreach ($aAttributeIDs as $value) {
            $oAttributeName = ParticipantAttributeName::model()->findByPk($value);

            if (!$oAttributeName) {
                continue;
            }

            $fields[] = 'a' . $value;
            $attributeNames = $oAttributeName->participant_attribute_names_lang;
            $outputarray[0][] = (sizeof($attributeNames) > 0 && !empty($attributeNames[0]['attribute_name'])) ? $attributeNames[0]['attribute_name'] : $oAttributeName->defaultname;
        }

        $fieldNeededKeys = array_fill_keys($fields, '');
        $fieldKeys = array_flip($fields);
        foreach ($query as $field => $aData) {
            $outputarray[] = array_merge($fieldNeededKeys, array_intersect_key($aData, $fieldKeys));
        }
        CPDBExport($outputarray, "central_" . time());
    }

    /**
     * Returns a string with the number of participants available for export or 0
     *
     * @param CDbCriteria $search
     * @return string|int
     */
    protected function csvExportCount($search = null)
    {
        if (!Permission::model()->hasGlobalPermission('participantpanel', 'export')) {
            return 0;
        }

        $attid = ParticipantAttributeName::model()->getVisibleAttributes();
        //If super admin all the participants will be visible
        if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $iUserID = null;
        } else {
            $iUserID = Yii::app()->session['loginID'];
        }

        $count = (int) Participant::model()->getParticipantsCount($attid, $search, $iUserID);
        if ($count > 1) {
            return sprintf(gT("Export %s participants to CSV"), $count);
        } elseif ($count == 1) {
            return gT("Export participants to CSV");
        } else {
            return $count;
        }
    }

    /**********************************************PARTICIPANT PANEL INFORMATION***********************************************/

    /**
     * Loads the view 'participantsPanel'
     * Central Participants database summary action
     */
    public function index()
    {
        $title = gT("Central participants database summary");
        $iUserID = Yii::app()->session['loginID'];

        // if superadmin all the records in the cpdb will be displayed
        if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $iTotalRecords = Participant::model()->count();
        } else {
            // if not only the participants on which he has right on (shared and owned)
            $iTotalRecords = Participant::model()->getParticipantsOwnerCount($iUserID);
        }
        $ownsAddParticipantsButton = Permission::model()->hasGlobalPermission('superadmin', 'read')
            || Permission::model()->hasGlobalPermission('participantpanel', 'create');
        // gets the count of participants, their attributes and other such details
        $aData = array(
            'totalrecords' => $iTotalRecords,
            'owned' => Participant::model()->count('owner_uid = ' . $iUserID),
            'shared' => Participant::model()->getParticipantsSharedCount($iUserID),
            'aAttributes' => ParticipantAttributeName::model()->getAllAttributes(),
            'attributecount' => ParticipantAttributeName::model()->count(),
            'blacklisted' => Participant::model()->count('owner_uid = ' . $iUserID . ' AND blacklisted = \'Y\''),
        );

        $searchstring = Yii::app()->request->getPost('searchstring');
        $aData['searchstring'] = $searchstring;
        $aData['topbar'] = $this->getTopBarComponents($title, $ownsAddParticipantsButton, false);

        // loads the participant panel and summary view
        $this->renderWrappedTemplate('participants', array('participantsPanel', 'summary'), $aData);
    }

    /**********************************************LIST PARTICIPANTS***********************************************/

    /**
     * Loads the view 'displayParticipants' which contains the main grid
     */
    public function displayParticipants()
    {
        $title = gT('Central participant management');
        //Get list of surveys.
        //Should be all surveys owned by user (or all surveys for super admin)
        $surveys = Survey::model();
        //!!! Is this even possible to execute?
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $surveys->permission(Yii::app()->user->getId());
        }

        /** @var Survey[] $aSurveyNames */
        $aSurveyNames = $surveys->model()->with(array('languagesettings' => array('condition' => 'surveyls_language=language'), 'owner'))->findAll();

        /* Build a list of surveys that have tokens tables */
        $tSurveyNames = array();
        foreach ($aSurveyNames as $row) {
            $trow = array_merge($row->attributes, $row->defaultlanguage->attributes);
            if ($row->hasTokensTable) {
                $tSurveyNames[] = $trow;
            }
        }

        // if superadmin all the records in the cpdb will be displayed
        $iUserId = App()->user->getId();
        if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $iTotalRecords = Participant::model()->count();
        } else { // if not only the participants on which he has right on (shared and owned)
            $iTotalRecords = Participant::model()->getParticipantsOwnerCount($iUserId);
        }
        $model = new Participant();
        if (Yii::app()->getConfig('hideblacklisted') == "Y") {
            $model->blacklisted = "Y";
        }
        $request = Yii::app()->request;
        $participantParam = $request->getParam('Participant');
        if ($participantParam) {
            $model->setAttributes($participantParam, false);
        }
        /* @todo : See when/where it's used */
        $searchcondition = $request->getParam('searchcondition');
        $searchparams = array();
        if ($searchcondition) {
            $searchparams = explode('||', (string) $searchcondition);
            $model->addSurveyFilter($searchparams);
        }

        $model->bEncryption = true;

        // data to be passed to view
        $aData = array(
            'names' => User::model()->findAll(),
            'attributes' => ParticipantAttributeName::model()->getVisibleAttributes(),
            'allattributes' => ParticipantAttributeName::model()->getAllAttributes(),
            'attributeValues' => ParticipantAttributeName::model()->getAllAttributesValues(),
            'surveynames' => $aSurveyNames,
            'tokensurveynames' => $tSurveyNames,
            'searchcondition' => $searchparams,
            'aAttributes' => ParticipantAttributeName::model()->getAllAttributes(),
            'totalrecords' => $iTotalRecords,
            'model' => $model,
            'debug' => $request->getParam('Participant')
        );

        $aData['pageSizeParticipantView'] = Yii::app()->user->getState('pageSizeParticipantView');
        $searchstring = $request->getPost('searchstring');
        $aData['searchstring'] = $searchstring;
        Yii::app()->clientScript->registerPackage('bootstrap-datetimepicker');

        // check global and custom permissions and pass them to $aData
        $aData['permissions'] = permissionsAsArray(
            [
                'superadmin' => ['read'],
                'templates' => ['read'],
                'labelsets' => ['read'],
                'users' => ['read'],
                'usergroups' => ['read'],
                'participantpanel' => ['read', 'create', 'update', 'delete', 'export', 'import'],
                'settings' => ['read']
            ],
            [
                'participantpanel' => [
                    'editSharedParticipants' => empty(ParticipantShare::model()->findAllByAttributes(
                        ['share_uid' =>  $iUserId],
                        ['condition' => 'can_edit = \'0\' OR can_edit = \'\'',]
                    )),
                    'sharedParticipantExists' => ParticipantShare::model()->exists('share_uid = :userid', [':userid' => $iUserId]),
                    'isOwner' => isset($participantParam['owner_uid']) && ($participantParam['owner_uid'] === $iUserId) ? true : false
                ],

            ]
        );
        $aData['massiveAction'] = App()->getController()->renderPartial('/admin/participants/massive_actions/_selector', array('permissions' => $aData['permissions']), true, false);

        // Set page size
        if ($request->getPost('pageSizeParticipantView')) {
            Yii::app()->user->setState('pageSizeParticipantView', $request->getPost('pageSizeParticipantView'));
        }

        $aData['topbar'] = $this->getTopBarComponents($title, true, false);

        // Loads the participant panel view and display participant view
        $this->renderWrappedTemplate('participants', array('participantsPanel', 'displayParticipants'), $aData);
    }


    /**
     * Takes the delete call from the display participants and take appropriate action depending on the condition
     * @return void
     */
    public function deleteParticipant()
    {
        // Abort if no permission
        $deletePermission = Permission::model()->hasGlobalPermission('participantpanel', 'delete');
        if (!$deletePermission) {
            $this->ajaxHelper::outputNoPermission();
        }

        $selectoption = Yii::app()->request->getPost('selectedoption');

        // First for delete one, second for massive action
        $participantId = Yii::app()->request->getPost('participant_id');
        $participantIds = json_decode(Yii::app()->request->getPost('sItems', ''), true);

        if (empty($participantIds)) {
            $participantIds = $participantId;
        }

        if (is_array($participantIds)) {
            $participantIds = implode(',', $participantIds);
        }

        // Deletes from participants only
        $deletedParticipants = null;
        if ($selectoption == 'po') {
            $deletedParticipants = Participant::model()->deleteParticipants($participantIds, !$deletePermission);
        } elseif ($selectoption == 'ptt') {
            // Deletes from central and survey participant list
            $deletedParticipants = Participant::model()->deleteParticipantToken($participantIds);
        } elseif ($selectoption == 'ptta') {
            // Deletes from central , token and assosiated responses as well
            $deletedParticipants = Participant::model()->deleteParticipantTokenAnswer($participantIds);
        } else {
            // Internal error
            throw new InvalidArgumentException('Unknown select option: ' . $selectoption);
        }

        if ($deletedParticipants === 0) {
            $this->ajaxHelper::outputError(gT('No participants deleted'));
        } else {
            $this->ajaxHelper::outputSuccess(gT('Participant deleted'));
        }
    }

    /**
     * Method to open the participant edit/ new participant modal
     * Requires 'participant_id' (int|null)
     * @return void
     */
    public function openEditParticipant()
    {
        $participant_id = Yii::app()->request->getParam('participant_id');
        if ($participant_id) {
            $model = Participant::model()->findByPk($participant_id)->decrypt();
            $operationType = "edit";
        } else {
            $model = new Participant();
            $operationType = "add";
        }

        //Generate HTML for extra Attributes
        $extraAttributes = array();
        foreach ($model->allExtraAttributes as $name => $extraAttribute) {
            $value = $model->getParticipantAttribute("", $extraAttribute['attribute_id']);
            $extraAttribute['value'] = $value;
            $extraAttribute['name'] = $name;

            if ($extraAttribute['attribute_type'] == 'DD') {
                $extraAttribute['options'] = $model->getOptionsForAttribute($extraAttribute['attribute_id']);
            }

            $extraAttributes[$name] = $extraAttribute;
        }

        $aData = array(
            'model' => $model,
            'editType' => $operationType,
            'extraAttributes' => $extraAttributes,
            'users' => User::model()->findAll()
        );

        $html = $this->getController()->renderPartial(
            '/admin/participants/modal_subviews/_editParticipant',
            $aData,
            true
        );
        $this->ajaxHelper::output($html);
    }

    /**
     * ?
     */
    public function openParticipantSurveys()
    {
        $participant_id = Yii::app()->request->getPost('participant_id');
        $model = Participant::model()->findByPk($participant_id);
        $surveyModel = SurveyLink::model();
        $surveyModel->participant_id = $participant_id;
        $aData = array(
            'model' => $model,
            'surveymodel' => $surveyModel
        );
        $html = $this->getController()->renderPartial(
            '/admin/participants/modal_subviews/_showParticipantSurveys',
            $aData,
            true
        );
        $this->ajaxHelper::output($html);
    }

    /**
     * Called by Ajax to open the share participant modal
     * Used by both single share and massive action share
     * @return void
     */
    public function openParticipantShare()
    {
        $participant_id = Yii::app()->request->getPost('participant_id');
        $participant_ids = null;

        if (empty($participant_id)) {
            $participant_ids = Yii::app()->request->getPost('participantIds');
            $participant_id = $participant_ids[0];
        }

        $model = Participant::model()->findByPk($participant_id);

        if (empty($model)) {
            throw new \CException('Found no participant with id \'' . $participant_id . '\'.');
        }

        $surveyModel = SurveyLink::model();
        $surveyModel->participant_id = $participant_id;

        // Get all users except myself
        $users = User::model()->findAll('uid != ' . Yii::app()->user->id);

        $aData = array(
            'model' => $model,
            'surveymodel' => $surveyModel,
            'users' => $users,
            'participantIds' => $participant_ids
        );

        $html = $this->getController()->renderPartial(
            '/admin/participants/modal_subviews/_shareParticipant',
            $aData,
            true
        );
        $this->ajaxHelper::output($html);
    }

    /**
     * Method to open the participant delete modal
     * Requires 'participant_id' (int)
     * @return void
     */
    public function openDeleteParticipant()
    {

        $participant_id = Yii::app()->request->getPost('participant_id');
        $model = Participant::model()->findByPk($participant_id);

        $html = $this->getController()->renderPartial(
            '/admin/participants/modal_subviews/_deleteParticipant',
            array('model' => $model),
            true
        );
        $this->ajaxHelper::output($html);
    }

    /**
     * Either update or create new participant
     */
    public function editParticipant()
    {
        $operation = Yii::app()->request->getPost('oper');
        $aData = Yii::app()->request->getPost('Participant');

        if (isset($aData['blacklisted']) && ($aData['blacklisted'] == 'on' || $aData['blacklisted'] == '1' || $aData['blacklisted'] == 'Y')) {
            $aData['blacklisted'] = 'Y';
        } else {
            $aData['blacklisted'] = 'N';
        }

        $extraAttributes = Yii::app()->request->getPost('Attributes', array());

        switch ($operation) {
            case 'edit':
                $this->updateParticipant($aData, $extraAttributes);
                break;
            case 'add':
                $this->addParticipant($aData, $extraAttributes);
                break;
            default:
                // Internal error
                assert(false, 'Unknown operation: ' . $operation);
                break;
        }
    }

    public function batchEdit()
    {
        $hasUpdatePermission = Permission::model()->hasGlobalPermission('participantpanel', 'update');
        if (
            !$hasUpdatePermission
            && empty(ParticipantShare::model()->findAllByAttributes(
                ['share_uid' => (int) App()->user->id],
                'can_edit = :can_edit',
                [':can_edit' => '1']
            ))
        ) {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(Yii::app()->createUrl('/admin'));
            return;
        }

        $aParticipantIds = json_decode(Yii::app()->request->getPost('sItems', '')) ?? [];
        $aResults = array();
        $oBaseModel = Surveymenu::model();
        // First we create the array of fields to update
        $aData = array();
        $aResults['global']['result'] = true;

        // Core Fields
        $aCoreTokenFields = array('language', 'owner_uid', 'blacklisted');
        foreach ($aCoreTokenFields as $sCoreTokenField) {
            if (trim(Yii::app()->request->getPost($sCoreTokenField, 'lskeep')) != 'lskeep') {
                $aData[$sCoreTokenField] = flattenText(Yii::app()->request->getPost($sCoreTokenField));
            }
        }


        if (count($aData) > 0) {
            foreach ($aParticipantIds as $sParticipantId) {
                $oParticipant = Participant::model()->findByPk($sParticipantId);



                foreach ($aData as $key => $value) {
                    // Make sure no-one hacks owner_uid into form
                    if (!$oParticipant->isOwnerOrSuperAdmin() && $key == 'owner_uid') {
                        continue;
                    }
                    $oParticipant->$key = $value;
                }

                // Check if the User is allowed to edit the participant
                if (
                    ParticipantShare::model()->canEditSharedParticipant($sParticipantId)
                    || $oParticipant->isOwnerOrSuperAdmin()
                    || $hasUpdatePermission
                ) {
                    $bUpdateSuccess = $oParticipant->save();
                } else {
                    $bUpdateSuccess = '';
                };

                if ($bUpdateSuccess) {
                    $aResults[$sParticipantId]['status']    = true;
                    $aResults[$sParticipantId]['message']   = gT('Updated');
                } else {
                    $aResults[$sParticipantId]['status']    = false;
                    $aResults[$sParticipantId]['message']   = $oParticipant->getError('participant_id');
                }
            }
        } else {
            $aResults['global']['result']  = false;
            $aResults['global']['message'] = gT('Nothing to update');
        }

        Yii::app()->getController()->renderPartial('/admin/participants/massive_actions/_update_results', array('aResults' => $aResults));
    }

    /**
     * Update participant
     * Outputs Ajax result
     * @param array $aData
     * @param array $extraAttributes
     * @return void
     */
    public function updateParticipant($aData, array $extraAttributes = array())
    {
        $participant = Participant::model()->findByPk($aData['participant_id']);

        // Abort if not found (internal error)
        if (empty($participant)) {
            $this->ajaxHelper::outputError(sprintf('Found no participant with id %s', $aData['participant_id']));
        }

        if (!$participant->userHasPermissionToEdit()) {
            $this->ajaxHelper::outputNoPermission();
        }

        // Make sure no-one hacks owner_uid into form
        if (!$participant->isOwnerOrSuperAdmin()) {
            unset($aData['owner_uid']);
        }

        $participant->attributes = $aData;
        $participant->encryptSave(true);

        foreach ($extraAttributes as $htmlName => $attributeValue) {
            list(, $attribute_id) = explode('_', $htmlName);
            $attribute = ParticipantAttribute::model();
            $attribute->attribute_id = $attribute_id;
            $attribute->participant_id = $aData['participant_id'];
            $attribute->value = $attributeValue;
            $attribute->encrypt();
            $attribute->updateParticipantAttributeValue($attribute->attributes);
        }

        $this->ajaxHelper::outputSuccess(gT("Participant successfully updated"));
    }

    /**
     * Add new participant to database
     *
     * @param array $aData
     * @param array $extraAttributes
     * @return string json
     */
    public function addParticipant($aData, array $extraAttributes = array())
    {
        if (Permission::model()->hasGlobalPermission('participantpanel', 'create')) {
            $uuid = Participant::genUuid();
            $aData['participant_id'] = $uuid;
            $aData['owner_uid'] = Yii::app()->user->id;
            $aData['created_by'] = Yii::app()->user->id;

            // String = error message, object = success
            $result = Participant::model()->insertParticipant($aData);

            if (is_object($result)) {
                foreach ($extraAttributes as $htmlName => $attributeValue) {
                    list(, $attribute_id) = explode('_', $htmlName);
                    $attribute = ParticipantAttribute::model();
                    $attribute->attribute_id = $attribute_id;
                    $attribute->participant_id = $uuid;
                    $attribute->value = $attributeValue;
                    $attribute->encrypt();
                    $attribute->updateParticipantAttributeValue($attribute->attributes);
                }

                $this->ajaxHelper::outputSuccess(gT("Participant successfully added"));
            } elseif (is_string($result)) {
                $this->ajaxHelper::outputError('Could not add new participant: ' . $result);
            } else {
                // "Impossible"
                safeDie('Could not add participant.');
            }
        } else {
            $this->ajaxHelper::outputNoPermission();
        }
    }

    /**********************************************IMPORT PARTICIPANTS***********************************************/
    /**
     * Loads the view 'importCSV'
     */
    public function importCSV()
    {
        $this->checkPermission('import');
        $title = gT("Import CSV");
        $aData = array(
            'aAttributes' => ParticipantAttributeName::model()->getAllAttributes(),
        );
        $aData['topbar'] = $this->getTopBarComponents($title, false, false);
        $this->renderWrappedTemplate('participants', array('participantsPanel', 'importCSV'), $aData);
    }

    /**
     * Show the drag-n-drop form for CSV attributes
     */
    public function attributeMapCSV()
    {
        $this->checkPermission('import');

        // Check file size and redirect on error
        $uploadValidator = new LimeSurvey\Models\Services\UploadValidator();
        $uploadValidator->redirectOnError('the_file', array('admin/participants/sa/importCSV'));

        if ($_FILES['the_file']['name'] == '') {
            Yii::app()->setFlashMessage(gT('Please select a file to import!'), 'error');
            Yii::app()->getController()->redirect(array('admin/participants/sa/importCSV'));
        }
        $sRandomFileName = randomChars(20);
        $sFilePath = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $sRandomFileName;
        $aPathinfo = pathinfo((string) $_FILES['the_file']['name']);
        $sExtension = $aPathinfo['extension'];
        $bMoveFileResult = false;
        if (strtolower($sExtension) == 'csv') {
            $bMoveFileResult = @move_uploaded_file($_FILES['the_file']['tmp_name'], $sFilePath);
            $filterblankemails = Yii::app()->request->getPost('filterbea');
        } else {
            Yii::app()->setFlashMessage(gT("This is not a .csv file."), 'error');
            Yii::app()->getController()->redirect(array('admin/participants/sa/importCSV'));
            Yii::app()->end();
        }

        if ($bMoveFileResult === false) {
            Yii::app()->setFlashMessage(gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder."), 'error');
            Yii::app()->getController()->redirect(array('admin/participants/sa/importCSV'));
            Yii::app()->end();
        } else {
            $regularfields = array('firstname', 'participant_id', 'lastname', 'email', 'language', 'blacklisted', 'owner_uid');
            $oCSVFile = fopen($sFilePath, 'r');
            if ($oCSVFile === false) {
                safeDie('File not found.');
            }
            $aFirstLine = fgets($oCSVFile);
            rewind($oCSVFile);

            $sSeparator = Yii::app()->request->getPost('separatorused');
            if ($sSeparator == 'auto') {
                $aCount = array();
                $aCount[','] = substr_count($aFirstLine, ',');
                $aCount[';'] = substr_count($aFirstLine, ';');
                $aCount['|'] = substr_count($aFirstLine, '|');
                $aResult = array_keys($aCount, max($aCount));
                $sSeparator = $aResult[0];
            }
            $firstline = fgetcsv($oCSVFile, 1000, $sSeparator[0]);

            $selectedcsvfields = array();
            $fieldlist = array();
            foreach ($firstline as $key => $value) {
                $testvalue = preg_replace('/[^(\x20-\x7F)]*/', '', (string) $value); //Remove invalid characters from string
                if ($value != strip_tags((string) $value)) { /* see ParticipantAttributeName->rules for defaultname */
                    continue;
                }
                if (!in_array(strtolower($testvalue), $regularfields)) {
                    array_push($selectedcsvfields, $value);
                }
                $fieldlist[] = $value;
            }
            $iLineCount = count(array_filter(array_filter((array) file($sFilePath), 'trim')));

            $attributes = ParticipantAttributeName::model()->model()->getCPDBAttributes();
            $aData = array(
                'attributes' => $attributes,
                'firstline' => $selectedcsvfields,
                'fullfilepath' => $sRandomFileName,
                'linecount' => $iLineCount - 1,
                'filterbea' => $filterblankemails,
                'participant_id_exists' => in_array('participant_id', $fieldlist)
            );
            App()->getClientScript()->registerPackage('jquery-nestedSortable');
            App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'attributeMapCSV.js');

            $sAttributeMapJS = "var copyUrl = '" . App()->createUrl("admin/participants/sa/uploadCSV") . "';\n"
                . "var displayParticipants = '" . App()->createUrl("admin/participants/sa/displayParticipants") . "';\n"
                . "var mapCSVcancelled = '" . App()->createUrl("admin/participants/sa/mapCSVcancelled") . "';\n"
                . "var characterset = '" . sanitize_paranoid_string($_POST['characterset']) . "';\n"
                . "var okBtn = '" . gT("OK") . "';\n"
                . "var processed = '" . gT("Summary") . "';\n"
                . "var summary = '" . gT("Upload summary") . "';\n"
                . "var notPairedErrorTxt = '" . gT("You have to pair this field with an existing attribute.") . "';\n"
                . "var onlyOnePairedErrorTxt = '" . gT("Only one CSV attribute is mapped with central attribute.") . "';\n"
                . "var cannotAcceptErrorTxt='" . gT("This list cannot accept survey participant attributes.") . "';\n"
                . "var separator = '" . sanitize_paranoid_string($_POST['separatorused']) . "';\n"
                . "var thefilepath = '" . $sRandomFileName . "';\n"
                . "var filterblankemails = '" . sanitize_paranoid_string($filterblankemails) . "';\n";
            App()->getClientScript()->registerScript("sAttributeMapJS", $sAttributeMapJS, CClientScript::POS_BEGIN);
            $this->renderWrappedTemplate('participants', 'attributeMapCSV', $aData);
        }
    }

    /**
     * Uploads the file to the server and process it for valid enteries and import them into database
     * Also creates attributes from the mapping drag-n-drop form.
     */
    public function uploadCSV()
    {
        $this->checkPermission('import');

        unset(Yii::app()->session['summary']);
        $mappedarray = Yii::app()->request->getPost('mappedarray', false);
        $filterblankemails = Yii::app()->request->getPost('filterbea');
        $overwrite = Yii::app()->request->getPost('overwrite');
        $sFilePath = Yii::app()->getConfig('tempdir') . '/' . basename(Yii::app()->request->getPost('fullfilepath', ''));
        $errorinupload = "";
        $recordcount = 0;
        $mandatory = 0;
        $mincriteria = 0;
        $imported = 0;
        $dupcount = 0;
        $overwritten = 0;
        $dupreason = "nameemail"; //Default duplicate comparison method
        $duplicatelist = array();
        $invalidemaillist = array();
        $invalidformatlist = array();
        $invalidattribute = array();
        $invalidparticipantid = array();
        /* If no mapped array */
        if (!$mappedarray) {
            $mappedarray = array();
        }
        /* Adjust system settings to read file with MAC line endings */
        @ini_set('auto_detect_line_endings', '1');
        /* Open the uploaded file into an array */
        $tokenlistarray = file($sFilePath);

        // open it and trim the endings
        $separator = Yii::app()->request->getPost('separatorused');
        $uploadcharset = Yii::app()->request->getPost('characterset');
        /* The $newarray contains a list of fields that will be used
        to create attributes */
        $newarray = Yii::app()->request->getPost('newarray');
        if (!empty($newarray)) {
            /* Create a new entry in the lime_participant_attribute_names table,
            and it's associated lime_participant_attribute_names_lang table
            for each NEW attribute being created in this import process */
            foreach ($newarray as $key => $value) {
                $aData = array('attribute_type' => 'TB', 'defaultname' => $value, 'visible' => 'FALSE');
                $insertid = ParticipantAttributeName::model()->storeAttributeCSV($aData);
                /* Keep a record of the attribute_id for this new attribute
                in the $mappedarray string. For example, if the new attribute
                has attribute_id of 35 and is called "gender",
                $mappedarray['35']='gender' */
                $mappedarray[$insertid] = $value;
            }
        }
        if (!isset($uploadcharset)) {
            $uploadcharset = 'auto';
        }
        $allowedfieldnames = array('participant_id', 'firstname', 'lastname', 'email', 'language', 'blacklisted');
        $aFilterDuplicateFields = array('firstname', 'lastname', 'email');
        if (!empty($mappedarray)) {
            foreach ($mappedarray as $key => $value) {
                array_push($allowedfieldnames, strtolower((string) $value));
            }
        }
        foreach ($tokenlistarray as $buffer) {
            //Iterate through the CSV file line by line
            $buffer = @mb_convert_encoding((string) $buffer, "UTF-8", $uploadcharset);
            if ($recordcount == 0) {
                //The first time we iterate through the file we look at the very
                //first line, which contains field names, not values to import
                // Pick apart the first line
                $buffer = removeBOM($buffer);

                //For Attributes
                switch ($separator) {
                    case 'comma':
                        $separator = ',';
                        break;
                    case 'semicolon':
                        $separator = ';';
                        break;
                    default:
                        $comma = substr_count((string) $buffer, ',');
                        $semicolon = substr_count((string) $buffer, ';');
                        if ($semicolon > $comma) {
                            $separator = ';';
                        } else {
                            $separator = ',';
                        }
                }
                $firstline = str_getcsv((string) $buffer, $separator, '"');
                $firstline = array_map('trim', $firstline);
                $ignoredcolumns = array();
                //now check the first line for invalid fields
                foreach ($firstline as $index => $fieldname) {
                    $firstline[$index] = preg_replace("/(.*) <[^,]*>$/", "$1", $fieldname);
                    $fieldname = $firstline[$index];
                    if (!in_array(strtolower($fieldname), $allowedfieldnames) && !in_array($fieldname, $mappedarray)) {
                        $ignoredcolumns[] = $fieldname;
                    } else {
                        $firstline[$index] = strtolower($fieldname);
                    }
                }
                if ((!in_array('firstname', $firstline) && !in_array('lastname', $firstline) && !in_array('email', $firstline)) && !in_array('participant_id', $firstline)) {
                    $recordcount = count($tokenlistarray);
                    break;
                }
            } else {
                // After looking at the first line, we now import the actual values
                $line = str_getcsv($buffer, $separator, '"');
                // Discard lines where the number of fields do not match
                if (count($firstline) != count($line)) {
                    $invalidformatlist[] = $recordcount . ',' . count($line) . ',' . count($firstline);
                    $recordcount++;
                    continue;
                }
                $writearray = array_combine($firstline, $line);
                //kick out ignored columns
                foreach ($ignoredcolumns as $column) {
                    unset($writearray[$column]);
                }
                // Add aFilterDuplicateFields not in CSV to writearray : quick fix
                foreach ($aFilterDuplicateFields as $sFilterDuplicateField) {
                    if (!in_array($sFilterDuplicateField, $firstline)) {
                        $writearray[$sFilterDuplicateField] = "";
                    }
                }
                $dupfound = false;
                $thisduplicate = 0;

                //Check for duplicate participants
                //HACK - converting into SQL instead of doing an array search
                if (in_array('participant_id', $firstline)) {
                    $dupreason = "participant_id";
                    $aData = "participant_id = " . Yii::app()->db->quoteValue($writearray['participant_id']);
                } else {
                    $dupreason = "nameemail";
                    $aData = "firstname = " . Yii::app()->db->quoteValue($writearray['firstname']) . " AND lastname = " . Yii::app()->db->quoteValue($writearray['lastname']) . " AND email = " . Yii::app()->db->quoteValue($writearray['email']) . " AND owner_uid = '" . Yii::app()->session['loginID'] . "'";
                }
                //End of HACK
                $aData = Participant::model()->checkforDuplicate($aData, "participant_id");
                if ($aData !== false) {
                    $thisduplicate = 1;
                    $dupcount++;
                    if ($overwrite == "true") {
                        // We want all the non filtering internal attributes to be updated,too
                        $oParticipant = Participant::model()->findByPk($aData);
                        foreach ($writearray as $attribute => $value) {
                            if (in_array($attribute, ['firstname', 'lastname', 'email'])) {
                                continue;
                            }
                            $oParticipant->$attribute = $value;
                        }
                        $oParticipant->save();
                        //Although this person already exists, we want to update the mapped attribute values
                        if (!empty($mappedarray)) {
                            //The mapped array contains the attributes we are
                            //saving in this import
                            foreach ($mappedarray as $attid => $attname) {
                                if (!empty($attname)) {
                                    $bData = array(
                                        'participant_id' => $aData,
                                        'attribute_id' => $attid,
                                        'value' => $writearray[strtolower((string) $attname)]
                                    );
                                    ParticipantAttribute::model()->updateParticipantAttributeValue($bData);
                                } else {
                                    //If the value is empty, don't write the value
                                }
                            }
                            $overwritten++;
                        }
                    }
                }
                if ($thisduplicate == 1) {
                    $dupfound = true;
                    $duplicatelist[] = CHtml::encode($writearray['firstname'] . " " . $writearray['lastname'] . " (" . $writearray['email'] . ")");
                }

                //Checking the email address is in a valid format
                $invalidemail = false;
                $writearray['email'] = trim($writearray['email']);
                if ($writearray['email'] != '') {
                    $aEmailAddresses = explode(';', $writearray['email']);
                    // Ignore additional email addresses
                    $sEmailaddress = $aEmailAddresses[0];
                    if (!validateEmailAddress($sEmailaddress)) {
                        $invalidemail = true;
                        $invalidemaillist[] = CHtml::encode($line[0] . " " . $line[1] . " (" . $line[2] . ")");
                    }
                }
                if (!$dupfound && !$invalidemail) {
                    //If it isn't a duplicate value or an invalid email, process the entry as a new participant

                    //First, process the known fields
                    if (!isset($writearray['participant_id']) || $writearray['participant_id'] == "") {
                        $uuid = Participant::genUuid(); //Generate a UUID for the new participant
                        $writearray['participant_id'] = $uuid;
                    }
                    if (isset($writearray['emailstatus']) && trim($writearray['emailstatus'] == '')) {
                        unset($writearray['emailstatus']);
                    }
                    if (!isset($writearray['language']) || $writearray['language'] == "") {
                        $writearray['language'] = "en";
                    }
                    if (!isset($writearray['blacklisted']) || $writearray['blacklisted'] == "") {
                        $writearray['blacklisted'] = "N";
                    }
                    $writearray['owner_uid'] = Yii::app()->session['loginID'];
                    if (isset($writearray['validfrom']) && trim($writearray['validfrom'] == '')) {
                        unset($writearray['validfrom']);
                    }
                    if (isset($writearray['validuntil']) && trim($writearray['validuntil'] == '')) {
                        unset($writearray['validuntil']);
                    }
                    $dontimport = false;
                    if (($filterblankemails == "accept" && $writearray['email'] == "")) {
                        //The mandatory fields of email, firstname and lastname
                        //must be filled, but one or more are empty
                        $mandatory++;
                        $dontimport = true;
                    } else {
                        foreach ($writearray as $key => $value) {
                            if (!empty($mappedarray)) {
                                //The mapped array contains the attributes we are
                                //saving in this import
                                if (in_array($key, $allowedfieldnames)) {
                                    foreach ($mappedarray as $attid => $attname) {
                                        if (strtolower((string) $attname) == $key) {
                                            if (!empty($value)) {
                                                $attributes = ParticipantAttribute::model();
                                                $attributes->participant_id = $writearray['participant_id'];
                                                $attributes->attribute_id = $attid;
                                                $attributes->value = $value;
                                                $attributes->encrypt();
                                                ParticipantAttributeName::model()->saveParticipantAttributeValue($attributes);
                                            } else {
                                                //If the value is empty, don't write the value
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    //If any of the mandatory fields are blank, then don't import this user
                    if (!$dontimport) {
                        $participant = Participant::model();
                        foreach ($writearray as $key => $value) {
                            if ($participant->hasAttribute($key)) {
                                $participant->$key = $value;
                            }
                        }
                        $participant->encrypt()->insertParticipantCSV($participant->attributes);
                        $imported++;
                    }
                }
                $mincriteria++;
            }
            $recordcount++;
        }

        unlink($sFilePath);
        $aData = array();
        $aData['recordcount'] = $recordcount - 1;
        $aData['duplicatelist'] = $duplicatelist;
        $aData['mincriteria'] = $mincriteria;
        $aData['imported'] = $imported;
        $aData['errorinupload'] = $errorinupload;
        $aData['invalidemaillist'] = $invalidemaillist;
        $aData['aInvalidFormatlist'] = $invalidformatlist;
        $aData['mandatory'] = $mandatory;
        $aData['invalidattribute'] = $invalidattribute;
        $aData['invalidparticipantid'] = $invalidparticipantid;
        $aData['overwritten'] = $overwritten;
        $aData['dupreason'] = $dupreason;
        $this->getController()->renderPartial('/admin/participants/uploadSummary_view', $aData);
    }

    /**
     * This function deletes the uploaded csv file if the import is cancelled
     *
     */
    public function mapCSVcancelled()
    {
        $this->checkPermission('import');

        unlink(Yii::app()->getConfig('tempdir') . '/' . basename(Yii::app()->request->getPost('fullfilepath', '')));
    }

    /**********************************************EXPORT PARTICIPANTS***********************************************/

    /**
     * Exports participants as CSV - receiver function for the GUI
     * @return void
     */
    public function exporttocsv()
    {
        $this->checkPermission('export');

        if (Yii::app()->request->getPost('searchcondition', '') !== '') {
            // if there is a search condition then only the participants that match the search criteria are counted
            $condition = explode("%7C%7C", Yii::app()->request->getPost('searchcondition', ''));
            $search = Participant::model()->getParticipantsSearchMultipleCondition($condition);
        } else {
            $search = null;
        }

        $chosenParticipants = Yii::app()->request->getPost('selectedParticipant');
        $chosenParticipantsArray = explode(',', (string) $chosenParticipants);
        $searchSelected = new CDbCriteria();
        if (!empty($chosenParticipants)) {
            $searchSelected->addInCondition("p.participant_id", $chosenParticipantsArray);
        } else {
            $searchSelected = null;
        }

        if ($search) {
            $search->mergeWith($searchSelected);
        } else {
            $search = $searchSelected;
        }

        $aAttributes = explode('+', Yii::app()->request->getPost('attributes', ''));
        $this->csvExport($search, array_filter($aAttributes)); // Array filter gets rid of empty entries
    }

    /**
     * Returns the count of the participants in the CSV and show it in the title of the modal box
     * This is to give the user the hint to see the number of participants he is exporting
     */
    public function exporttocsvcount()
    {
        $this->checkPermission('export');

        $searchconditionurl = Yii::app()->request->getPost('searchURL');
        $searchcondition = Yii::app()->request->getPost('searchcondition');
        $searchconditionurl = basename((string) $searchconditionurl);

        $search = new CDbCriteria();
        if ($searchconditionurl != 'getParticipantsJson') {
            // if there is a search condition then only the participants that match the search criteria are counted
            $condition = explode("||", (string) $searchcondition);
            $search = Participant::model()->getParticipantsSearchMultipleCondition($condition);
        } else {
            $search->addCondition("1=1");
        }

        $chosenParticipants = Yii::app()->request->getPost('selectedParticipant');
        $chosenParticipantsArray = explode(',', (string) $chosenParticipants);

        $searchSelected = new CDbCriteria();
        if (!empty($chosenParticipants)) {
            $searchSelected->addInCondition("{{participant_id}}", $chosenParticipantsArray);
        } else {
            $searchSelected = null;
        }

        if ($search) {
            $search->mergeWith($searchSelected);
        } else {
            $search = $searchSelected;
        }


        echo $this->csvExportCount($search);
    }

    /**
     * Outputs the count of participants when using the export all button on the top
     */
    public function exporttocsvcountAll()
    {
        $chosenParticipants = Yii::app()->request->getPost('selectedParticipant');
        if (!empty($chosenParticipants)) {
            $search = new CDbCriteria();
            $search->addInCondition("p.participant_id", $chosenParticipants);
        } else {
            $search = null;
        }
        echo $this->csvExportCount($search);
    }

    /**
     * Responsible to export all the participants in the central table
     */
    public function exporttocsvAll()
    {
        $chosenParticipants = Yii::app()->request->getPost('selectedParticipant');
        if (!empty($chosenParticipants)) {
            $search = new CDbCriteria();
            $search->addInCondition("p.participant_id", $chosenParticipants);
        } else {
            $search = null;
        }
        $this->csvExport($search);
    }

    //Display BlacklistSetting
    /**********************************************UN-/BLACKLIST PARTICIPANTS***********************************************/
    /**
     * Loads the view 'blacklistControl'
     * @return void
     */
    public function blacklistControl()
    {
        $title = gT("Blocklist settings");
        $aData = array(
            'blacklistallsurveys' => Yii::app()->getConfig('blacklistallsurveys'),
            'blacklistnewsurveys' => Yii::app()->getConfig('blacklistnewsurveys'),
            'blockaddingtosurveys' => Yii::app()->getConfig('blockaddingtosurveys'),
            'hideblacklisted' => Yii::app()->getConfig('hideblacklisted'),
            'deleteblacklisted' => Yii::app()->getConfig('deleteblacklisted'),
            'allowunblacklist' => Yii::app()->getConfig('allowunblacklist'),
            'aAttributes' => ParticipantAttributeName::model()->getAllAttributes(),
        );
        $aData['topbar'] = $this->getTopBarComponents($title, false, false);

        $this->renderWrappedTemplate('participants', array('participantsPanel', 'blacklist'), $aData);
    }

    /**
     * Stores the blocklist setting to the database
     * @return void
     */
    public function storeBlacklistValues()
    {
        $this->requirePostRequest();

        if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
            Yii::app()->setFlashMessage(gT('Access denied!'), 'error');
            Yii::app()->getController()->redirect(array('admin/participants/sa/blacklistControl'));
        }

        $values = array('blacklistallsurveys', 'blacklistnewsurveys', 'blockaddingtosurveys', 'hideblacklisted', 'deleteblacklisted', 'allowunblacklist');
        foreach ($values as $value) {
            if (SettingGlobal::model()->findByPk($value)) {
                SettingGlobal::model()->updateByPk(
                    $value,
                    array(
                        'stg_value' => Yii::app()->request->getPost($value) ? 'Y' : 'N'
                    )
                );
            } else {
                $stg = new SettingGlobal();
                $stg->stg_name = $value;
                $stg->stg_value = Yii::app()->request->getPost($value) ? 'Y' : 'N';
                $stg->save();
            }
        }
        Yii::app()->setFlashMessage(gT('Blocklist settings were saved.'), 'success');
        Yii::app()->getController()->redirect(array('admin/participants/sa/blacklistControl'));
    }

    /**
     * AJAX Method to change the blocklist status of a participant
     * Requires POST with 'participant_id' (varchar) and 'blacklist' (boolean)
     * Echos JSON-encoded array with 'success' (boolean) and 'newValue' ('Y' || 'N')
     * @return void
     */
    public function changeblackliststatus()
    {
        $participantId = Yii::app()->request->getPost('participant_id');
        $blacklistStatus = Yii::app()->request->getPost('blacklist');
        $blacklistValue = $blacklistStatus == true ? "Y" : "N";
        $participant = Participant::model()->findByPk($participantId);
        if ($participant) {
            $participant->blacklisted = $blacklistValue;
            $participant->update(['blacklisted']);
        }
        echo json_encode(array(
            "success" => true,
            "newValue" => $blacklistValue
        ));
    }

    //Display Attributes
    /**********************************************PARTICIPANT ATTRIBUTES***********************************************/
    /**
     * Loads the view 'attributeControl'
     * @return void
     */
    public function attributeControl()
    {
        $this->checkPermission('read');

        $title = gT("Attribute management");
        $model = new ParticipantAttributeName();
        if (Yii::app()->request->getParam('ParticipantAttributeName')) {
            $model->attributes = Yii::app()->request->getParam('ParticipantAttributeName');
        }
        // data to be passed to view
        $aData = array(
            'names' => User::model()->findAll(),
            'attributes' => ParticipantAttributeName::model()->getVisibleAttributes(),
            'allattributes' => ParticipantAttributeName::model()->getAllAttributes(),
            'attributeValues' => ParticipantAttributeName::model()->getAllAttributesValues(),
            'aAttributes' => ParticipantAttributeName::model()->getAllAttributes(),
            'model' => $model,
            'debug' => Yii::app()->request->getParam('Attribute'),
        );
        // Page size
        if (Yii::app()->request->getParam('pageSizeAttributes')) {
            Yii::app()->user->setState('pageSizeAttributes', (int) Yii::app()->request->getParam('pageSizeAttributes'));
        } else {
            Yii::app()->user->setState('pageSizeAttributes', (int) Yii::app()->params['defaultPageSize']);
        }
        $searchstring = Yii::app()->request->getPost('searchstring');
        $aData['searchstring'] = $searchstring;
        // loads the participant panel view and display participant view

        $aData['massiveAction'] = App()->getController()->renderPartial(
            '/admin/participants/massive_actions/_selector_attribute',
            array(),
            true,
            false
        );
        $aData['topbar'] = $this->getTopBarComponents($title, false, true);

        $this->renderWrappedTemplate('participants', array('participantsPanel', 'attributeControl'), $aData);
    }

    /**
     * Echoes json
     * @return void
     */
    public function changeAttributeVisibility()
    {
        $attributeId = Yii::app()->request->getPost('attribute_id');
        $visible = Yii::app()->request->getPost('visible');
        $visible_value = ($visible ? "TRUE" : "FALSE");
        $attributeName = ParticipantAttributeName::model()->findByPk($attributeId);
        if (isset($attributeName)) {
            $attributeName->visible = $visible_value;
            $attributeName->update(['visible']);
        }
        echo json_encode(array(
            "debug" => Yii::app()->request,
            "debug_p1" => Yii::app()->request->getPost('attribute_id'),
            "debug_p2" => Yii::app()->request->getPost('visible'),
            "success" => true,
            "newValue" => $visible_value
        ));
    }

    /**
     * Echoes json
     * @return void
     */
    public function changeAttributeEncrypted()
    {
        $attributeId = Yii::app()->request->getPost('attribute_id');
        $encrypted = Yii::app()->request->getPost('encrypted');
        $encrypted_value = $encrypted ? 'Y' : 'N';
        $attributeName = ParticipantAttributeName::model()->findByPk($attributeId);
        $encryptedBeforeChange = $attributeName->isEncrypted();
        $attributeName->encrypted = $encrypted_value;
        $encryptedAfterChange = $attributeName->isEncrypted();
        $sDefaultname = $attributeName->defaultname;

        // encryption/decryption MUST be done in a one synchronous step, either all succeeded or none
        $oDB = Yii::app()->db;
        $oTransaction = $oDB->beginTransaction();
        try {
            if ($attributeName->isCoreAttribute()) {
                // core participant attributes
                $oParticipants = Participant::model()->findAll();
                foreach ($oParticipants as $participant) {
                    $aUpdateData = array();
                    if ($encryptedBeforeChange && !$encryptedAfterChange) {
                        $aUpdateData[$sDefaultname] = LSActiveRecord::decryptSingle($participant->$sDefaultname);
                    } elseif (!$encryptedBeforeChange && $encryptedAfterChange) {
                        $aUpdateData[$sDefaultname] = LSActiveRecord::encryptSingle($participant->$sDefaultname);
                    }
                    if (!empty($aUpdateData)) {
                        $oDB->createCommand()->update('{{participants}}', $aUpdateData, "participant_id='" . $participant->participant_id . "'");
                    }
                }
            } else {
                // custom participant attributes
                $oAttributes = ParticipantAttribute::model()->findAll(
                    'attribute_id = :attribute_id',
                    array(':attribute_id' => $attributeId)
                );
                foreach ($oAttributes as $attribute) {
                    $aUpdateData = array();
                    if ($encryptedBeforeChange && !$encryptedAfterChange) {
                        $aUpdateData['value'] = LSActiveRecord::decryptSingle($attribute->value);
                    } elseif (!$encryptedBeforeChange && $encryptedAfterChange) {
                        $aUpdateData['value'] = LSActiveRecord::encryptSingle($attribute->value);
                    }
                    if (!empty($aUpdateData) && $aUpdateData['value'] !== null) {
                        $oDB->createCommand()->update(
                            '{{participant_attribute}}',
                            $aUpdateData,
                            'attribute_id = :attribute_id AND participant_id = :participant_id',
                            array(
                                ':attribute_id' => $attributeId,
                                ':participant_id' => $attribute->participant_id
                            )
                        );
                    }
                }
            }

            // save token encryption options if everything was ok
            $attributeName->update();
            $oTransaction->commit();
        } catch (\Exception $e) {
            $oTransaction->rollback();
            return false;
        }

        echo json_encode(array(
            "debug" => Yii::app()->request,
            "debug_p1" => Yii::app()->request->getPost('attribute_id'),
            "debug_p2" => Yii::app()->request->getPost('encrypted'),
            "success" => true,
            "newValue" => $encrypted_value
        ));
    }

    /**
     * Method to open the editAttributeName Modal
     */
    public function openEditAttributeNames()
    {
        $attribute_id = Yii::app()->request->getPost('attribute_id');
        if ($attribute_id) {
            $model = ParticipantAttributeName::model()->findByPk($attribute_id);
            $editType = "edit";
        } else {
            $model = new ParticipantAttributeName();
            $model->attribute_type = 'TB';
            $editType = "new";
        }

        // Generate HTML for alternative languages
        $languagesOfAttribute = array();
        foreach ($model->participant_attribute_names_lang as $single_language) {
            $languagesOfAttribute[$single_language['lang']] = $single_language['attribute_name'];
        }

        $aData = array(
            'model' => $model,
            'editType' => $editType,
            'languagesOfAttribute' => $languagesOfAttribute
        );

        $allLangDetailArray = getLanguageData(false, Yii::app()->language);
        $aData['languagesForDropdown'][''] = gT("Select language to add");
        foreach ($allLangDetailArray as $key => $languageDetail) {
            $aData['languagesForDropdown'][$key] = $languageDetail['description'] . " (" . ($languageDetail['nativedescription']) . ")";
        }

        // Default visibility to false
        $model->visible = $model->visible ?: 'FALSE';

        // load sodium library
        $sodium = Yii::app()->sodium;
        $aData['bEncrypted'] = $sodium->bLibraryExists;

        $html = $this->getController()->renderPartial(
            '/admin/participants/modal_subviews/_editAttribute',
            $aData,
            true
        );
        $this->ajaxHelper::output($html);
    }

    /**
     * Open modal to add participant(s) to survey
     * @return void
     */
    public function openAddToSurvey()
    {
        // This is in fact a comma-separated list
        $participant_id = Yii::app()->request->getPost('participant_id');

        $data = array();
        $data['participant_id'] = $participant_id;
        $data['count'] = substr_count((string) $participant_id, ',') + 1;

        $surveys = Survey::getSurveysWithTokenTable();
        $data['surveys'] = $surveys;
        $data['hasGlobalPermission'] = Permission::model()->hasGlobalPermission('surveys', 'update');

        $html = $this->getController()->renderPartial(
            '/admin/participants/modal_subviews/_addToSurvey',
            $data,
            true
        );
        $this->ajaxHelper::output($html);
    }

    /**
     * Method to edit a global Attribute
     * Requires POST
     *   'ParticipantAttributeName' (array),
     *   'ParticipantAttributeNameLanguages' (array),
     *   'ParticipantAttributeNamesDropdown' (array|null),
     *   'oper' (string) ['edit'|'new']
     * Echoes json-encoded array 'success' (array), 'successMessage' (string)
     * @return void
     */
    public function editAttributeName()
    {
        $AttributeNameAttributes = Yii::app()->request->getPost('ParticipantAttributeName');
        $AttributeNameAttributes['encrypted'] = $AttributeNameAttributes['encrypted'] == '1' ? 'Y' : 'N';
        $AttributeNameAttributes['visible'] = $AttributeNameAttributes['visible'] == '1' ? 'TRUE' : 'FALSE';
        $AttributeNameAttributes['core_attribute'] = 'N';
        $AttributeNameLanguages = Yii::app()->request->getPost('ParticipantAttributeNameLanguages');
        $ParticipantAttributeNamesDropdown = Yii::app()->request->getPost('ParticipantAttributeNamesDropdown');
        $sEncryptedAfterChange = $AttributeNameAttributes['encrypted'];
        $operation = Yii::app()->request->getPost('oper');

        // encryption/decryption MUST be done in a one synchronous step, either all succeed or none
        $oDB = Yii::app()->db;
        $oTransaction = $oDB->beginTransaction();
        try {
            // save attribute
            if ($operation === 'edit') {
                $iAttributeId = $AttributeNameAttributes['attribute_id'];
                $ParticipantAttributeNames = ParticipantAttributeName::model()->findByPk($iAttributeId);
                $sEncryptedBeforeChange = $ParticipantAttributeNames->encrypted;
                $ParticipantAttributeNames->saveAttribute($AttributeNameAttributes);
            } else {
                $ParticipantAttributeNames = new ParticipantAttributeName();
                $sEncryptedBeforeChange = 'N';
                $ParticipantAttributeNames->setAttributes($AttributeNameAttributes);
                $ParticipantAttributeNames->save();
                $iAttributeId = $ParticipantAttributeNames->attribute_id;
            }

            // encrypt/decrypt participant data on attribute setting change
            $oAttributes = ParticipantAttribute::model()->findAll("attribute_id=:attribute_id", array("attribute_id" => $iAttributeId));
            foreach ($oAttributes as $attribute) {
                $aUpdateData = array();
                if ($sEncryptedBeforeChange == 'Y' && $sEncryptedAfterChange == 'N') {
                    $aUpdateData['value'] = LSActiveRecord::decryptSingle($attribute->value);
                } elseif ($sEncryptedBeforeChange == 'N' && $sEncryptedAfterChange == 'Y') {
                    $aUpdateData['value'] = LSActiveRecord::encryptSingle($attribute->value);
                }
                if (!empty($aUpdateData)) {
                    $oDB->createCommand()->update(
                        '{{participant_attribute}}',
                        $aUpdateData,
                        "attribute_id = :attribute_id AND participant_id = :participant_id",
                        array(
                            ':attribute_id' => $iAttributeId,
                            ':participant_id' => $attribute->participant_id
                        )
                    );
                }
            }

            // save attribute values
            if (is_array($ParticipantAttributeNamesDropdown)) {
                $ParticipantAttributeNames->clearAttributeValues();
                foreach ($ParticipantAttributeNamesDropdown as $i => $dropDownValue) {
                    if ($dropDownValue !== "") {
                        $storeArray = array(
                            "attribute_id" => $ParticipantAttributeNames->attribute_id,
                            "value" => $dropDownValue
                        );
                        $ParticipantAttributeNames->storeAttributeValue($storeArray);
                    }
                }
            }

            // save attribute translations
            if (is_array($AttributeNameLanguages)) {
                foreach ($AttributeNameLanguages as $lnKey => $lnValue) {
                    $saveLanguageArray = array(
                        'attribute_id' => $ParticipantAttributeNames->attribute_id,
                        'attribute_name' => $lnValue,
                        'lang' => $lnKey
                    );
                    $ParticipantAttributeNames->saveAttributeLanguages($saveLanguageArray);
                }
            }
            $oTransaction->commit();
            $this->ajaxHelper::outputSuccess(gT("Attribute successfully updated"));
        } catch (\Exception $e) {
            $oTransaction->rollback();
            return false;
        }
    }

    /**
     * Deletes a translation from an Attribute, if it has at least one translation
     * Requires POST 'attribute_id' (int), 'lang' (string) [language-code]
     * Echoes 'success' (boolean), 'successMessage' (string|null), 'errorMessage' (string|null)
     * @return void
     */
    public function deleteLanguageFromAttribute()
    {
        $attribute_id = Yii::app()->request->getPost('attribute_id');
        $lang = Yii::app()->request->getPost('lang');
        $AttributePackage = ParticipantAttributeName::model()->findByPk($attribute_id);
        if (count($AttributePackage->participant_attribute_names_lang) > 1) {
            ParticipantAttributeNameLang::model()->deleteByPk(array("attribute_id" => $attribute_id, "lang" => $lang));
            $this->ajaxHelper::outputSuccess(gT("Language successfully deleted"));
        } else {
            $this->ajaxHelper::outputError(gT("There has to be at least one language."));
        }
    }
    /**
     * Deletes a single Attribute via AJAX-call
     * Requires POST 'attribute_id' (int)
     * Echoes json-encoded array 'success' (boolean), successMessage (string)
     * @return void
     */
    public function deleteSingleAttribute()
    {
        $attribute_id = (int) Yii::app()->request->getPost('attribute_id');
        ParticipantAttributeName::model()->delAttribute($attribute_id);
        $this->ajaxHelper::outputSuccess(gT("Attribute successfully deleted"));
    }

    /**
     * Delete several attributes.
     * Massive action, called by Ajax.
     * @return void
     */
    public function deleteAttributes()
    {
        if (!Permission::model()->hasGlobalPermission('participantpanel', 'delete')) {
            $this->ajaxHelper::outputNoPermission();
            return;
        }

        $request = Yii::app()->request;
        $attributeIds = json_decode($request->getPost('sItems', '')) ?? [];
        $attributeIds = array_map('sanitize_int', $attributeIds);

        $deletedAttributes = 0;

        try {
            foreach ($attributeIds as $attributeId) {
                ParticipantAttributeName::model()->delAttribute($attributeId);
                $deletedAttributes++;
            }

            $this->ajaxHelper::outputSuccess(
                sprintf(
                    ngT('%s attribute deleted|%s attributes deleted', $deletedAttributes),
                    $deletedAttributes
                )
            );
        } catch (Exception $e) {
            $this->ajaxHelper::outputError(sprintf(
                gT('Error. Deleted %s attribute(s). Error message: %s'),
                $deletedAttributes,
                $e->getMessage()
            ));
        }
    }

    /**
     * Takes the edit call from the share panel, which either edits or deletes the share information
     * Basically takes the call on can_edit
     * @return void
     */
    public function editAttributeInfo()
    {
        $operation = Yii::app()->request->getPost('oper');

        if ($operation == 'del' && Yii::app()->request->getPost('id')) {
            $aAttributeIds = (array) explode(',', Yii::app()->request->getPost('id', ''));
            $aAttributeIds = array_map('trim', $aAttributeIds);
            $aAttributeIds = array_map('intval', $aAttributeIds);

            foreach ($aAttributeIds as $iAttributeId) {
                ParticipantAttributeName::model()->delAttribute($iAttributeId);
            }
        } elseif ($operation == 'add' && Yii::app()->request->getPost('attribute_name')) {
            $aData = array(
                'defaultname' => Yii::app()->request->getPost('attribute_name'),
                'attribute_name' => Yii::app()->request->getPost('attribute_name'),
                'attribute_type' => Yii::app()->request->getPost('attribute_type'),
                'visible' => Yii::app()->request->getPost('visible') ? 'TRUE' : 'FALSE'
            );
            echo ParticipantAttributeName::model()->storeAttribute($aData);
        } elseif ($operation == 'edit' && Yii::app()->request->getPost('id')) {
            $aData = array(
                'attribute_id' => Yii::app()->request->getPost('id'),
                'attribute_name' => Yii::app()->request->getPost('attribute_name'),
                'attribute_type' => Yii::app()->request->getPost('attribute_type'),
                'visible' => Yii::app()->request->getPost('visible', 'FALSE') != 'FALSE' ? 'TRUE' : 'FALSE'
            );
            ParticipantAttributeName::model()->saveAttribute($aData);
            eT("Attribute display setting updated");
        }
    }

    /**
     * Fetches the attributes of a participant to be displayed in the attribute subgrid
     * @todo Where is this called from?
     */
    public function getAttributeJson()
    {
        $iParticipantId = strip_tags(Yii::app()->request->getQuery('pid', ''));
        $records = ParticipantAttributeName::model()->getParticipantVisibleAttribute($iParticipantId);
        $records = subval_sort($records, "attribute_name", "asc");

        $i = 0;

        $doneattributes = array(); //If the user has any actual attribute values, they'll be stored here

        /* Iterate through each attribute owned by this user */
        foreach ($records as $row) {
            $outputs[$i] = array("", $row['participant_id'] . "_" . $row['attribute_id'], $row['attribute_type'], $row['attribute_id'], $row['attribute_name'], $row['value']);
            /* Collect allowed values for a DropDown attribute */
            if ($row['attribute_type'] == "DD") {
                $attvalues = ParticipantAttributeName::model()->getAttributesValues($row['attribute_id']);
                if (!empty($attvalues)) {
                    $attval = "";
                    foreach ($attvalues as $val) {
                        $attval .= $val['value'] . ":" . $val['value'];
                        $attval .= ";";
                    }
                    $attval = substr($attval, 0, -1);
                    array_push($outputs[$i], $attval);
                } else {
                    array_push($outputs[$i], "");
                }
            } else {
                array_push($outputs[$i], "");
            }
            array_push($doneattributes, $row['attribute_id']);
            $i++;
        }

        /* Build a list of attribute names for which this user has NO values stored, keep it in $attributenotdone */
        $attributenotdone = array();
        /* The user has NO values stored against any attribute */
        if (count($doneattributes) == 0) {
            $attributenotdone = ParticipantAttributeName::model()->getCPDBAttributes();
        } else {
            /* The user has SOME values stored against attributes */
            $attributenotdone = ParticipantAttributeName::model()->getNotAddedAttributes($doneattributes);
        }

        /* Go through the empty attributes and build an entry in the output for them */
        $outputs = [];
        foreach ($attributenotdone as $row) {
            $outputs[$i] = array("", $iParticipantId . "_" . $row['attribute_id'], $row['attribute_type'], $row['attribute_id'], $row['attribute_name'], "");
            if ($row['attribute_type'] == "DD") {
                $attvalues = ParticipantAttributeName::model()->getAttributesValues($row['attribute_id']);
                if (!empty($attvalues)) {
                    $attval = "";
                    foreach ($attvalues as $val) {
                        $attval .= $val['value'] . ":" . $val['value'];
                        $attval .= ";";
                    }
                    $attval = substr($attval, 0, -1);
                    array_push($outputs[$i], $attval);
                } else {
                    array_push($outputs[$i], "");
                }
            } else {
                array_push($outputs[$i], "");
            }
            $i++;
        }
        $outputs = subval_sort($outputs, 3, "asc");

        $aData = new stdClass();
        $aData->page = 1;
        $aData->rows[0]['id'] = $iParticipantId;
        $aData->rows[0]['cell'] = array();
        $aData->records = count($outputs);
        $aData->total = ceil($aData->records / 10);
        foreach ($outputs as $key => $output) {
            $aData->rows[$key]['id'] = $output[1];
            $aData->rows[$key]['cell'] = $output;
        }
        /* TODO: It'd be nice to do a natural sort on the attribute list at some point.
        Currently they're returned in order of attributes WITH values, then WITHOUT values
        */

        echo ls_json_encode($aData);
    }

    /**
     * Responsible for saving the additional attribute. It iterates through all the new attributes added dynamically
     * and iterates through them
     *
     * @return void
     */
    public function saveAttribute()
    {
        $iAttributeId = Yii::app()->request->getQuery('aid');
        $aData = array(
            'attribute_id' => $iAttributeId,
            'attribute_type' => Yii::app()->request->getPost('attribute_type'),
            'defaultname' => Yii::app()->request->getPost('defaultname'),
            'visible' => Yii::app()->request->getPost('visible')
        );
        ParticipantAttributeName::model()->saveAttribute($aData);
        Yii::app()->setFlashMessage(gT('Attribute was saved.'), 'info');

        // Save translations
        if (isset($_POST['lang'])) {
            foreach ($_POST['lang'] as $lang => $translation) {
                $langdata = array(
                    'attribute_id' => $iAttributeId,
                    'attribute_name' => $translation,
                    'lang' => $lang
                );

                ParticipantAttributeName::model()->saveAttributeLanguages($langdata);
            }
        }

        // TODO: What's the Difference between lang and langdata?
        if (Yii::app()->request->getPost('langdata')) {
            $langdata = array(
                'attribute_id' => $iAttributeId,
                'attribute_name' => Yii::app()->request->getPost('attname'),
                'lang' => Yii::app()->request->getPost('langdata')
            );

            ParticipantAttributeName::model()->saveAttributeLanguages($langdata);
        }

        /* New attribute value */
        if (Yii::app()->request->getPost('attribute_value_name_1') || Yii::app()->request->getPost('attribute_value_name_1') == "0") {
            $aDatavalues = [];
            $i = 1;
            $attvaluename = 'attribute_value_name_' . $i;
            while (array_key_exists($attvaluename, $_POST) && $_POST[$attvaluename] != "") {
                if ($_POST[$attvaluename] != "") {
                    $aDatavalues[$i] = array(
                        'attribute_id' => $iAttributeId,
                        'value' => Yii::app()->request->getPost($attvaluename)
                    );
                }
                $attvaluename = 'attribute_value_name_' . ++$i;
            };
            ParticipantAttributeName::model()->storeAttributeValues($aDatavalues);
        }
        /* Save updated attribute values */
        if (Yii::app()->request->getPost('editbox') || Yii::app()->request->getPost('editbox') == "0") {
            $editattvalue = array(
                'attribute_id' => $iAttributeId,
                'value_id' => Yii::app()->request->getPost('value_id'),
                'value' => Yii::app()->request->getPost('editbox')
            );
            ParticipantAttributeName::model()->saveAttributeValue($editattvalue);
        }
        Yii::app()->getController()->redirect(array('admin/participants/sa/attributeControl'));
    }

    /**
     * Responsible for deleting the additional attribute values in case of drop down.
     */
    public function delAttributeValues()
    {
        $iAttributeId = (int) Yii::app()->request->getQuery('aid');
        $iValueId = (int) Yii::app()->request->getQuery('vid');
        ParticipantAttributeName::model()->delAttributeValues($iAttributeId, $iValueId);
        Yii::app()->getController()->redirect(array('/admin/participants/sa/viewAttribute/aid/' . $iAttributeId));
    }

    /**
     * Responsible for editing the additional attributes values
     */
    public function editAttributevalue()
    {
        if (Yii::app()->request->getPost('oper') == "edit" && isset($_POST['attvalue'])) {
            $pid = explode('_', Yii::app()->request->getPost('participant_id', ''));
            $iAttributeId = Yii::app()->request->getPost('attid');
            if (Permission::model()->hasGlobalPermission('participantpanel', 'update') && Participant::model()->isOwner($pid[0])) {
                $aData = array('participant_id' => $pid[0], 'attribute_id' => $iAttributeId, 'value' => Yii::app()->request->getPost('attvalue'));
                ParticipantAttributeName::model()->editParticipantAttributeValue($aData);
            }
        }
    }

    /**********************************************PARTICIPANT SHARE PANEL***********************************************/
    /**
     * Loads the view 'sharePanel'
     * @return void
     * @throws CException
     */
    public function sharePanel()
    {
        $title = gT("Share panel");
        $model = new ParticipantShare();
        if (Yii::app()->request->getParam('ParticipantShare')) {
            $model->setAttributes(Yii::app()->request->getParam('ParticipantShare'), false);
        }
        $model->bEncryption = true;

        // data to be passed to view
        $aData = array(
            'names' => User::model()->findAll(),
            'attributes' => ParticipantAttributeName::model()->getVisibleAttributes(),
            'allattributes' => ParticipantAttributeName::model()->getAllAttributes(),
            'attributeValues' => ParticipantAttributeName::model()->getAllAttributesValues(),
            'aAttributes' => ParticipantAttributeName::model()->getAllAttributes(),
            'model' => $model,
            'debug' => Yii::app()->request->getParam('Participant'),
            'pageTitle' => $title,
        );
        // Page size
        if (Yii::app()->request->getParam('pageSizeShareParticipantView')) {
            Yii::app()->user->setState('pageSizeShareParticipantView', (int) Yii::app()->request->getParam('pageSizeShareParticipantView'));
        } else {
            Yii::app()->user->setState('pageSizeShareParticipantView', (int) Yii::app()->params['defaultPageSize']);
        }
        $aData['pageSizeShareParticipantView'] = Yii::app()->user->getState('pageSizeShareParticipantView');
        $searchstring = Yii::app()->request->getPost('searchstring');
        $aData['searchstring'] = $searchstring;

        $aData['massiveAction'] = App()->getController()->renderPartial('/admin/participants/massive_actions/_selector_share', array(), true, false);
        $aData['topbar'] = $this->getTopBarComponents($title, false, false);
        // Loads the participant panel view and display participant view
        $this->renderWrappedTemplate('participants', array('participantsPanel', 'sharePanel'), $aData);
    }

    /**
     * Sends the shared participant info to the share panel using JSON encoding
     * Called after the share panel grid is loaded
     * Returns the json depending on the user logged in by checking it from the session
     * @return void
     * @todo Where is this called from?
     */
    public function getShareInfoJson()
    {
        $aData = new stdClass();
        $aData->page = 1;

        // If super administrator all the share info in the links table will be shown
        if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $records = Participant::model()->getParticipantSharedAll();
            $aData->records = count($records);
            $aData->total = ceil($aData->records / 10);
            $i = 0;

            foreach ($records as $row) {
                //for conversion of uid to human readable names
                $iShareUserId = $row['share_uid'];
                if ($iShareUserId != 0) {
                    /** @var User $oUser */
                    $oUser = User::model()->findByPk($iShareUserId);
                    $sSharename = $oUser->full_name;
                } else {
                    $sSharename = 'All users';
                }
                /** @var User $owner */
                $owner = User::model()->findByPk($row['owner_uid']);
                $aData->rows[$i]['id'] = $row['participant_id'] . "--" . $row['share_uid']; //This is the unique combination per record
                $aData->rows[$i]['cell'] = array($row['firstname'], $row['lastname'], $row['email'], $sSharename, $row['share_uid'], $owner->full_name, $row['date_added'], $row['can_edit']);
                $i++;
            }

            echo ls_json_encode($aData);
        } else {
            // otherwise only the shared participants by that user
            $records = Participant::model()->getParticipantShared(Yii::app()->session['loginID']);
            $aData->records = count($records);
            $aData->total = ceil($aData->records / 10);
            $i = 0;

            foreach ($records as $row) {
                $iShareUserId = $row['share_uid']; //for conversion of uid to human readable names
                if ($iShareUserId != 0) {
                    /** @var User $oUser */
                    $oUser = User::model()->findByPk($iShareUserId);
                    $sSharename = $oUser->full_name;
                } else {
                    $sSharename = 'All users';
                }
                $aData->rows[$i]['id'] = $row['participant_id'];
                $aData['rows'][$i]['cell'] = array($row['firstname'], $row['lastname'], $row['email'], $sSharename, $row['share_uid'], $row['date_added'], $row['can_edit']);
                $i++;
            }

            echo ls_json_encode($aData);
        }
    }

    /**
     * Takes the edit call from the share panel, which either edits or deletes the share information
     * Basically takes the call on can_edit
     */
    public function editShareInfo()
    {
        $operation = Yii::app()->request->getPost('oper');
        // NB: Comma-separated list.
        $shareIds = Yii::app()->request->getPost('id');
        if ($operation == 'del') {
            // If operation is delete , it will delete, otherwise edit it
            ParticipantShare::model()->deleteRow($shareIds);
        } else {
            $aData = array(
                'participant_id' => Yii::app()->request->getPost('participant_id'),
                'can_edit' => Yii::app()->request->getPost('can_edit'),
                'share_uid' => Yii::app()->request->getPost('shared_uid')
            );
            ParticipantShare::model()->updateShare($aData);
        }
    }

    /**
     * Receives an ajax call containing the participant id in the fourth segment of the url
     * Supplies list of survey links - surveys of which this participant is on the tokens table
     * URL: [localurl]/limesurvey/admin/participants/getSurveyInfoJson/pid/[participant_id]
     * Echoes json data containing linked survey information (Survey name, survey ID, token_id and date_added)
     * @return void
     * @todo Where is this called from?
     */
    public function getSurveyInfoJson()
    {
        $participantid = Yii::app()->request->getQuery('pid');
        $records = SurveyLink::model()->findAllByAttributes((array('participant_id' => $participantid)));
        $aData = new stdClass();
        $aData->page = 1;
        $aData->records = count($records);
        $aData->total = ceil($aData->records / 10);
        $i = 0;
        foreach ($records as $row) {
            $oSurvey = Survey::model()->with(array('languagesettings' => array('condition' => 'surveyls_language=language')))->findByAttributes(array('sid' => $row['survey_id']));
            $surveyname = $oSurvey->languagesettings[0]->surveyls_title;
            $surveylink = "";
            /* Check permissions of each survey before creating a link*/
            if (!Permission::model()->hasSurveyPermission($row['survey_id'], 'tokens', 'read')) {
                $surveylink = $row['survey_id'];
            } else {
                $surveylink = '<a href=' . Yii::app()->getController()->createUrl("/admin/tokens/sa/browse/surveyid/{$row['survey_id']}") . '>' . $row['survey_id'] . '</a>';
            }
            $aData->rows[$i]['cell'] = array($surveyname, $surveylink, $row['token_id'], $row['date_created'], $row['date_invited'], $row['date_completed']);
            $i++;
        }

        echo ls_json_encode($aData);
    }

    /***********************************METHODS USED FROM OUTSIDE OF THE CPDB PANEL OR IN DEEPER VIEWS********************************/
    /**
     * Gets the ids of participants to be copied to the individual survey
     * Needed in the Participant views of the individual surveys
     */
    public function getSearchIDs()
    {
        $searchcondition = Yii::app()->request->getPost('searchcondition'); // get the search condition from the URL
        $sSearchURL = basename(Yii::app()->request->getPost('searchURL', '')); // get the search condition from the URL
        /* a search contains posted data inside $_POST['searchcondition'].
         * Each separate query is made up of 3 fields, separated by double-pipes ("|")
         * EG: fname||eq||jason||lname||ct||c
         *
         */
        if ($sSearchURL != 'getParticipantsJson') {
            // if there is a search condition present
            $participantid = "";
            $condition = explode("||", (string) $searchcondition); // explode the condition to the array
            $query = Participant::model()->getParticipantsSearchMultiple($condition, 0, 0);

            foreach ($query as $key => $value) {
                if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
                    $participantid .= "," . $value['participant_id']; // combine the participant id's in an string
                } else {
                    if (Participant::model()->isOwner($value['participant_id'])) {
                        $participantid .= "," . $value['participant_id']; // combine the participant id's in an string
                    }
                }
            }
            echo $participantid; //echo the participant id's
        } else {
            // if no search condition
            $participantid = ""; // initiallise the participant id to blank
            if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
                //If super admin all the participants will be visible
                $query = Participant::model()->getParticipantsWithoutLimit(); // get all the participant id if it is a super admin
            } else {
                // get participants on which the user has right on
                $query = Participant::model()->getParticipantsOwner(Yii::app()->session['loginID']);
            }

            foreach ($query as $key => $value) {
                $participantid = $participantid . "," . $value['participant_id']; // combine the participant id's in an string
            }
            echo $participantid; //echo the participant id's
        }
    }


    /**
     * Equal to getParticipantsJson() but now with a search
     * @return void
     * @todo Where is this called from?
     */
    public function getParticipantsResultsJson()
    {
        $searchcondition = Yii::app()->request->getpost('searchcondition');
        $condition = explode("||", (string) $searchcondition);
        $search = Participant::model()->getParticipantsSearchMultipleCondition($condition);
        $this->getParticipantsJson($search);
    }

    /*
     * Sends the data in JSON format extracted from the database to be displayed using the datatable
     * Echoes json
     * @return void
     */

    /**
     * @param CDbCriteria $search
     */
    public function getParticipantsJson($search = null)
    {
        $page = (int) Yii::app()->request->getPost('page');
        $limit = (int) Yii::app()->request->getPost('rows');
        $limit = empty($limit) ? 50 : $limit; //Stop division by zero errors

        $attid = ParticipantAttributeName::model()->getVisibleAttributes();
        $participantfields = array('participant_id', 'can_edit', 'firstname', 'lastname', 'email', 'blacklisted', 'survey', 'language', 'owner_uid');
        foreach ($attid as $key => $value) {
            array_push($participantfields, 'a' . $value['attribute_id']);
        }
        $sidx = Yii::app()->request->getPost('sidx');
        $sidx = in_array($sidx, $participantfields) ? $sidx : "lastname";
        $sord = Yii::app()->request->getPost('sord');
        $sord = ($sord == 'desc') ? 'desc' : 'asc';
        $order = $sidx . " " . $sord;


        $aData = new stdClass();

        //If super admin all the participants will be visible
        if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $iUserID = null;
        } else {
            $iUserID = Yii::app()->session['loginID'];
        }
        $aData->records = Participant::model()->getParticipantsCount($attid, $search, $iUserID);
        $aData->total = (int) ceil($aData->records / $limit);
        if ($page > $aData->total) {
            $page = $aData->total;
        }
        $aData->page = $page;
        $records = Participant::model()->getParticipants($page, $limit, $attid, $order, $search, $iUserID);


        $aRowToAdd = array();
        foreach ($records as $row) {
            if (array_key_exists('can_edit', $row)) {
                $sCanEdit = $row['can_edit'];
                if (is_null($sCanEdit)) {
                    $sCanEdit = 'true';
                }
            } else {
                // Super admin
                $sCanEdit = "true";
            }
            if (trim((string) $row['ownername']) == '') {
                $row['ownername'] = $row['username'];
            }
            $aRowToAdd['cell'] = array($row['participant_id'], $sCanEdit, htmlspecialchars((string) $row['firstname']), htmlspecialchars((string) $row['lastname']), htmlspecialchars((string) $row['email']), $row['blacklisted'], $row['survey'], $row['language'], $row['ownername']);
            $aRowToAdd['id'] = $row['participant_id'];
            // add attribute values
            foreach ($row as $key => $attvalue) {
                if (preg_match('/^a\d+$/', (string) $key)) {
                    $aRowToAdd['cell'][] = $attvalue;
                }
            }

            $aData->rows[] = $aRowToAdd;
        }

        echo ls_json_encode($aData);
    }

    /**
     * Seems to be a method to show the uploadsummary
     * @TODO investigate this more
     */
    public function summaryview()
    {
        $this->renderWrappedTemplate('participants', array('participantsPanel', 'uploadSummary'), array('aAttributes' => ParticipantAttributeName::model()->getAllAttributes()));
    }

    /**
     * Responsible for setting the session variables for attribute map page redirect
     * @todo Use user session?
     * @todo Used?
     */
    public function setSession()
    {
        unset(Yii::app()->session['participantid']);
        Yii::app()->session['participantid'] = Yii::app()->request->getPost('itemsid');
    }

    /**
     * Stores the shared participant information in participant_shares
     *
     * @return void
     * @throws CException
     */
    public function shareParticipants()
    {
        $hasUpdatePermission = Permission::model()->hasGlobalPermission('update');
        $isSuperAdmin = Permission::model()->hasGlobalPermission('superadmin', 'read');
        $permissions = [
            'hasUpdatePermission' => $hasUpdatePermission,
            'isSuperAdmin' => $isSuperAdmin
        ];
        $participantIds = Yii::app()->request->getPost('participant_id');
        $iShareUserId = (int) Yii::app()->request->getPost('shareuser');
        $bCanEdit = Yii::app()->request->getPost('can_edit') == true;

        if (!is_array($participantIds)) {
            $participantIds = array($participantIds);
        }

        // Some input validation needed
        if (empty($iShareUserId)) {
            $iShareUserId = -1; // -1 = shared with all users
        }

        $i = 0;
        foreach ($participantIds as $id) {
            $time = time();
            $aData = array(
                'participant_id' => $id, //id is a UUID, not an integer
                'share_uid' => $iShareUserId, // $iShareUserId == 0 means any user
                'date_added' => date('Y-m-d H:i:s', $time),
                'can_edit' => ($bCanEdit === false ? 0 : 1)
            );
            ParticipantShare::model()->storeParticipantShare($aData, $permissions);
            $i++;
        }

        $this->ajaxHelper::outputSuccess(sprintf(gT("%s participants have been shared"), $i));
    }

    /**
     * Stores the shared participant information in participant_shares for ONE participant     *
     *
     * @return void
     * @throws CException
     * TODO: Is this function even used anymore? Seems all logic goes through shareParticipants()
     */
    public function shareParticipant()
    {
        $hasUpdatePermission = Permission::model()->hasGlobalPermission('update');
        $isSuperAdmin = Permission::model()->hasGlobalPermission('superadmin', 'read');
        $permissions = [
            'hasUpdatePermission' => $hasUpdatePermission,
            'isSuperAdmin' => $isSuperAdmin
        ];

        $iParticipantId = Yii::app()->request->getPost('participant_id');
        $bCanEdit = Yii::app()->request->getPost('can_edit');

        if (
            ParticipantShare::model()->canEditSharedParticipant($iParticipantId)
            || $hasUpdatePermission
            || $isSuperAdmin
        ) {
            $time = time();
            $aData = array(
                'participant_id' => $iParticipantId,
                'share_uid' => yii::app()->user->getId(),
                'date_added' => date('Y-m-d H:i:s', $time),
                'can_edit' => $bCanEdit
            );
            ParticipantShare::model()->storeParticipantShare($aData, $permissions);

            $this->ajaxHelper::outputSuccess(gT("Participant shared."));
        } else {
            $this->ajaxHelper::outputNoPermission();
        }
    }

    /**
     * Deletes *all* shares for this participant
     * @return void
     */
    public function rejectShareParticipant()
    {
        $participant_id = yii::app()->request->getPost('participant_id');
        ParticipantShare::model()->deleteAllByAttributes(array('participant_id' => $participant_id));
        $this->ajaxHelper::outputSuccess(gT("Participant removed from sharing"));
    }

    /**
     * Deletes a single participant share
     * Called by Ajax; echoes success/error
     * @param string $participantId
     * @param int $shareUid
     * @return void
     */
    public function deleteSingleParticipantShare($participantId = null, $shareUid = null)
    {
        $this->requirePostRequest();

        $participantShare = ParticipantShare::model()->findByPk(array(
            'participant_id' => $participantId,
            'share_uid' => $shareUid
        ));

        if (empty($participantShare)) {
            $this->ajaxHelper::outputError(gT('Found no participant share'));
        } else {
            $userId = Yii::app()->user->id;
            $isOwner = $participantShare->participant->owner_uid == $userId;
            $isSuperAdmin = Permission::model()->hasGlobalPermission('superadmin', 'read');

            if ($isOwner || $isSuperAdmin) {
                $participantShare->delete();
                $this->ajaxHelper::outputSuccess(gT('Participant share deleted'));
            } else {
                $this->ajaxHelper::outputNoPermission();
            }
        }
    }

    /**
     * Deletes several ParticipantShare
     * NOT the same as rejectShareParticipant
     * @return void
     */
    public function deleteMultipleParticipantShare()
    {
        $request = Yii::app()->request;
        $userId = Yii::app()->user->id;
        $isSuperAdmin = Permission::model()->hasGlobalPermission('superadmin');

        // Array of strings with both participant id and share uid separated by comma
        $participantIdAndShareUids = json_decode($request->getPost('sItems', ''), true) ?? [];

        $sharesDeleted = 0;
        foreach ($participantIdAndShareUids as $participantIdAndShareUid) {
            list($participantId, $shareUid) = explode(',', (string) $participantIdAndShareUid);

            $participantShare = ParticipantShare::model()->findByPk(array(
                'participant_id' => $participantId,
                'share_uid' => $shareUid
            ));

            $isOwner = $participantShare->participant->owner_uid == $userId;
            $hasPermissionToDelete = $isOwner || $isSuperAdmin;

            if ($hasPermissionToDelete && !empty($participantShare)) {
                $participantShare->delete();
                $sharesDeleted++;
            }
        }

        if ($sharesDeleted == 0) {
            $this->ajaxHelper::outputError(gT('No participant shares were deleted'));
        } else {
            $this->ajaxHelper::outputSuccess(
                sprintf(
                    ngT('%s participant share was deleted|%s participant shares were deleted', $sharesDeleted),
                    $sharesDeleted
                )
            );
        }
    }

    /**
     * @return void
     */
    public function changeSharedEditableStatus()
    {
        $participant_id = Yii::app()->request->getPost('participant_id');
        $can_edit = Yii::app()->request->getPost('can_edit');
        $share_uid = Yii::app()->request->getPost('share_uid');
        $shareModel = ParticipantShare::model()->findByAttributes(array('participant_id' => $participant_id, 'share_uid' => $share_uid));

        if ($shareModel) {
            $shareModel->can_edit = $can_edit ? 1 : 0;
            $success = $shareModel->save();
        } else {
            $success = false;
        }
        echo json_encode(array("newValue" => $can_edit, "success" => $success));
    }

    /**
     * Responsible for copying the participant from tokens to the central Database
     *
     * TODO: Most of the work for this function is in the participants model file
     *       but it doesn't belong there.
     */
    public function addToCentral()
    {
        $newarr = Yii::app()->request->getPost('newarr');
        $mapped = Yii::app()->request->getPost('mapped');
        $overwriteauto = Yii::app()->request->getPost('overwriteauto', false);
        $overwriteman = Yii::app()->request->getPost('overwriteman', false);
        $createautomap = Yii::app()->request->getPost('createautomap');

        $response = Participant::model()->copyToCentral((int) Yii::app()->request->getPost('surveyid'), $newarr, $mapped, $overwriteauto, $overwriteman, $createautomap);

        echo "<p>";
        printf(gT("%s participants have been copied to the central participant list"), "<span class='badge rounded-pill bg-success'>" . $response['success'] . "</span>&nbsp;");
        echo "</p>";
        if ($response['duplicate'] > 0) {
            echo "<p>";
            printf(gT("%s entries were not copied because they already existed"), "<span class='badge rounded-pill bg-warning'>" . $response['duplicate'] . "</span>&nbsp;");
            echo "</p>";
        }
        if ($response['overwriteman'] == "true" || $response['overwriteauto']) {
            echo "<p>";
            eT("Attribute values for central participants have been updated from the survey participants");
            echo "</p>";
        }
    }

    /**
     * Responsible for adding the participant to the specified survey with attribute mapping
     * Used when mapping CPDB participants to survey tokens with attributes.
     * Called when user clicks "Continue" in that form.
     *
     * Echoes a result message witch will be displayed as a bootstrap modal
     *
     * @return void
     */
    public function addToTokenattmap()
    {
        $participantIdsString = Yii::app()->request->getPost('participant_id'); // TODO: This is a comma separated string of ids
        $participantIds = explode(",", (string) $participantIdsString);

        $surveyId = (int)Yii::app()->request->getPost('surveyid');

        /**
         * mapped can take values like
         *   mapped[attribute_38] = 39
         * meaning that an attribute is mapped onto another.
         */
        $mappedAttributes = Yii::app()->request->getPost('mapped', []);

        /**
         * newarr takes values like
         *   newarr[] = 39
         */
        $newAttributes = Yii::app()->request->getPost('newarr', []);

        $options = array();
        $options['overwriteauto'] = Yii::app()->request->getPost('overwrite') === 'true';
        $options['overwriteman'] = Yii::app()->request->getPost('overwriteman') === 'true';
        $options['overwritest'] = Yii::app()->request->getPost('overwritest') === 'true';
        $options['createautomap'] = Yii::app()->request->getPost('createautomap') === 'true';

        try {
            $response = Participant::model()->copyCPDBAttributesToTokens($surveyId, $participantIds, $mappedAttributes, $newAttributes, $options);
        } catch (CPDBException $e) {
            // This exception carries error messages
            echo $e->getMessage();
            return;
        } catch (Exception $e) {
            printf("Error: Could not copy attributes to participants: file %s, line %s; %s", $e->getFile(), $e->getLine(), $e->getMessage());
            return;
        }

        // TODO: This code can't be reached
        echo "<p>";
        printf(gT("%s participants have been copied to the survey participant list"), "<span class='badge rounded-pill bg-success'>" . $response['success'] . "</span>");
        echo "</p>";
        if ($response['duplicate'] > 0) {
            echo "<p>";
            printf(gT("%s entries were not copied because they already existed"), "<span class='badge rounded-pill bg-warning'>" . $response['duplicate'] . "</span>");
            echo "</p>";
        }
        if ($response['blacklistskipped'] > 0) {
            echo "<p>";
            printf(gT("%s entries were skipped because they are blocklisted"), "<span class='badge rounded-pill bg-danger'>" . $response['blacklistskipped'] . "</span>");
            echo "</p>";
        }
        if ($response['overwriteauto'] == "true" || $response['overwriteman'] == "true") {
            echo "<p>";
            eT("Attribute values for existing participants have been updated from the participants records");
            echo "</p>";
        }
    }

    /**
     * Show form for attribute mapping while copying participants from CPDB to token's table
     */
    public function attributeMap()
    {
        $iSurveyId = Yii::app()->request->getPost('survey_id');
        if (
            !Permission::model()->hasGlobalPermission('surveys', 'update')
            && !Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update')
        ) {
            Yii::app()->setFlashMessage(gT('No permission'), 'error');
            Yii::app()->getController()->redirect(['admin/participants/sa/displayParticipants']);
        }

        Yii::app()->loadHelper('common');
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'attributeMap.js');
        App()->getClientScript()->registerPackage('jqueryui');

        $redirect = Yii::app()->request->getPost('redirect');
        $count = Yii::app()->request->getPost('count');
        $iParticipantId = Yii::app()->request->getPost('participant_id');
        $CPDBAttributes = ParticipantAttributeName::model()->getCPDBAttributes();
        $tokenAttributes = getTokenFieldsAndNames($iSurveyId, true);

        $selectedattribute = array(); //List of existing attribute fields that are not mapped
        $selectedcentralattribute = array(); //List of attributes that haven't already been mapped
        $alreadymappedattid = array(); //List of fields already mapped to this tokens table
        $alreadymappedattname = array();

        foreach ($tokenAttributes as $attributeId => $attribute) {
            // attributeId like 'attribute_1'
            if (is_numeric($attributeId[10])) {
                //Assumes that if the 11th character is a number, it must be a token-table created attribute
                $selectedattribute[$attributeId] = $attribute['description'];
            } else {
                array_push($alreadymappedattid, substr((string) $attributeId, 15));
            }
        }

        foreach ($CPDBAttributes as $row) {
            if (!in_array($row['attribute_id'], $alreadymappedattid)) {
                $selectedcentralattribute[$row['attribute_id']] = $row['attribute_name'];
            } else {
                array_push($alreadymappedattname, $row['attribute_name']);
            }
        }

        // Check for automatic mappings
        // TODO: Maybe do this with SQL instead?
        $automaticallyMappedAttributes = $this->getAutomaticallyMappedAttributes($tokenAttributes, $CPDBAttributes);

        // Remove automatic mappings from CPDB list (they should only be in right-most list)
        foreach ($automaticallyMappedAttributes as $autoAttr) {
            unset($selectedcentralattribute[$autoAttr['cpdbAttribute']['attribute_id']]);
        }

        $aData = array(
            'selectedcentralattribute' => $selectedcentralattribute,
            'selectedtokenattribute' => $selectedattribute,
            'alreadymappedattributename' => $alreadymappedattname,
            'automaticallyMappedAttributes' => $automaticallyMappedAttributes,
            'survey_id' => $iSurveyId,
            'redirect' => $redirect,
            'participant_id' => $iParticipantId,
            'count' => $count
        );

        if (count($selectedcentralattribute) === 0) {
            Yii::app()->setFlashMessage(gT("There are no unmapped attributes"), 'info');
        }

        $this->renderWrappedTemplate('participants', 'attributeMap', $aData);
    }

    /**
     * This function is responsible for attribute mapping while copying participants from tokens to CPDB
     */
    public function attributeMapToken()
    {
        Yii::app()->loadHelper('common');
        $oAdminTheme = AdminTheme::getInstance();
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'attributeMapToken.js');
        App()->getClientScript()->registerCssFile($oAdminTheme->sTemplateUrl . '/css/attributeMapToken.css');
        App()->getClientScript()->registerPackage('jqueryui'); // jqueryui
        $iSurveyID = (int) Yii::app()->request->getQuery('sid');
        $aCPDBAttributes = ParticipantAttributeName::model()->getCPDBAttributes();
        $aTokenAttributes = getTokenFieldsAndNames($iSurveyID, true);

        //string of participant IDs which should be added to CPDB, if not set to sessionvar those will not be added!!
        $participants = Yii::app()->request->getPost('itemsid');
        if (isset($participants) && $participants !== null && $participants !== '') {
            unset(Yii::app()->session['participantid']);
            Yii::app()->session['participantid'] = $participants;
        }

        $selectedattribute = array();
        $selectedcentralattribute = array();
        $alreadymappedattid = array();
        $alreadymappedattdisplay = array();
        $alreadymappedattnames = array();

        foreach ($aTokenAttributes as $key => $value) {
            if ($value['cpdbmap'] == '') {
                $selectedattribute[$value['description']] = $key;
            } else {
                $attributeid = $value['cpdbmap'];
                $continue = false;
                foreach ($aCPDBAttributes as $attribute) {
                    if ($attribute['attribute_id'] == $attributeid) {
                        $continue = true;
                    }
                }
                if ($continue) {
                    $alreadymappedattid[] = $attributeid;
                    $alreadymappedattdisplay[] = $key;
                    $alreadymappedattnames[$key] = $value['description'];
                } else {
                    $selectedattribute[$value['description']] = $key;
                }
            }
        }
        foreach ($aCPDBAttributes as $row) {
            if (!in_array($row['attribute_id'], $alreadymappedattid)) {
                $selectedcentralattribute[$row['attribute_id']] = $row['attribute_name'];
            }
        }

        if (count($selectedattribute) === 0) {
            Yii::app()->setFlashMessage(gT("There are no unmapped attributes"), 'warning');
        }

        $aData = array(
            'attribute' => $selectedcentralattribute,
            'tokenattribute' => $selectedattribute,
            'alreadymappedattributename' => $alreadymappedattdisplay,
            'alreadymappedattdescription' => $alreadymappedattnames
        );

        $oSurvey = Survey::model()->findByPk($iSurveyID);
        $aData['subaction'] = gT('Add participants to central database');
        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyID . ")";
        $topbarData = TopbarConfiguration::getSurveyTopbarData($oSurvey->sid);
        $aData['topbar']['middleButtons'] = Yii::app()->getController()->renderPartial(
            '/surveyAdministration/partial/topbar/surveyTopbarLeft_view',
            $topbarData,
            true
        );

        $this->renderWrappedTemplate('participants', 'attributeMapToken', $aData);
    }

    /**
     * @param AjaxHelper $ajaxHelper
     * @return void
     */
    public function setAjaxHelper(AjaxHelper $ajaxHelper)
    {
        $this->ajaxHelper = $ajaxHelper;
    }

    /**
     * Return array of automatic mappings, pairing token attributes with CPDB attributes
     *
     * @param array $tokenAttributes
     * @param array $CPDBAttributes
     * @return array
     */
    private function getAutomaticallyMappedAttributes(array $tokenAttributes, array $CPDBAttributes)
    {
        $result = array();
        foreach ($tokenAttributes as $attributeId => $tokenAttribute) {
            // attributeId like 'attribute_1'
            if ($tokenAttribute['cpdbmap'] !== '') {
                foreach ($CPDBAttributes as $CPDBAttribute) {
                    if ($CPDBAttribute['attribute_id'] == intval($tokenAttribute['cpdbmap'])) {
                        $result[$attributeId] = array(
                            'tokenAttributeId' => $attributeId,
                            'tokenAttribute' => $tokenAttribute,
                            'cpdbAttribute' => $CPDBAttribute
                        );
                    }
                }
            }
        }
        return $result;
    }

    /**
     * If user has no permission, redirect and show error message.
     * @param string $permission Like 'import' or 'export, etc
     * @return void
     */
    private function checkPermission($permission)
    {
        if (!Permission::model()->hasGlobalPermission('participantpanel', $permission)) {
            Yii::app()->setFlashMessage(gT('No permission'), 'error');
            Yii::app()->getController()->redirect(Yii::app()->request->urlReferrer);
        }
    }

    /**
     * Returns the topbar config which then needs to be added into $aData['topbar'] in action functions
     *
     * @param $title
     * @param $ownsAddParticipantsButton
     * @param $ownsAddAttributeButton
     * @return array
     */
    private function getTopBarComponents($title, $ownsAddParticipantsButton, $ownsAddAttributeButton)
    {
        $topBarConf['title'] = $title;
        $topBarConf['backLink'] = App()->createUrl('dashboard/view');

        $topBarConf['middleButtons'] = Yii::app()->getController()->renderPartial(
            '/admin/participants/partial/topbarBtns/leftSideButtons',
            [
                'ownsAddParticipantsButton' => $ownsAddParticipantsButton
            ],
            true
        );
        $topBarConf['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/admin/participants/partial/topbarBtns/rightSideButtons',
            ['ownsAddAttributeButton' => $ownsAddAttributeButton],
            true
        );

        return $topBarConf;
    }
}
