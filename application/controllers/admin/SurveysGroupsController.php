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
 * Surveys Groups Controller
 */

use LimeSurvey\Models\Services\SurveysGroupCreator;

/**
 * Class SurveysGroupsController
 */
class SurveysGroupsController extends SurveyCommonAction
{
    /**
     * Displays a particular model.
     *
     * @param integer $id the ID of the model to be displayed
     * @return void
     */
    public function view($id)
    {
        $this->render('view', array(
            'model' => $this->loadModel($id),
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return void
     * @throws CHttpException
     */
    public function create()
    {
        if (!Permission::model()->hasGlobalPermission('surveysgroups', 'create')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }

        $model = new SurveysGroups();
        /* Move to SurveysGroup model init ? */
        $model->owner_id = Yii::app()->user->id;
        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        $user = Yii::app()->user;
        $request = Yii::app()->request;
        if ($request->getPost('SurveysGroups')) {
            $service = new SurveysGroupCreator(
                $request,
                $user,
                $model,
                new SurveysGroupsettings()
            );
            if ($service->save()) {
                $this->getController()->redirect(
                    App()->createUrl("admin/surveysgroups/sa/update", array('id' => $model->gsid, '#' => 'settingsForThisGroup'))
                );
            } else {
                $errors = $service->getMessages('error');
                if (!empty($errors)) {
                    foreach ($errors as $error) {
                        Yii::app()->setFlashMessage($error->getMessage(), 'error');
                    }
                }
            }
        } else {
            $model->name = SurveysGroups::getNewCode();
        }

        $aData = array(
            'model' => $model,
            'action' => App()->createUrl("admin/surveysgroups/sa/create", array('#' => 'settingsForThisGroup')),
        );
        $aData['aRigths'] = array(
            'update' => true,
            'delete' => false,
            'owner_id' => true,
        );
        $aData['topbar']['title'] = gT('Create survey group');
        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/layouts/partial_topbar/right_close_saveclose_save',
            [
                'backUrl' => Yii::app()->createUrl("surveyAdministration/listsurveys#surveygroups"),
                'isCloseBtn' => true,
                'isSaveBtn' => true,
                'isSaveAndCloseBtn' => true,
                'formIdSave' => 'surveys-groups-form',
                'formIdSaveClose' => 'surveys-groups-form',
            ],
            true
        );

        /* User for dropdown */
        $aUserIds = getUserList('onlyuidarray');
        $userCriteria = new CDbCriteria();
        $userCriteria->select = array("uid", "users_name", "full_name");
        $userCriteria->order = "full_name";
        $userCriteria->addInCondition('uid', $aUserIds);
        $aData['oUsers'] = User::model()->findAll($userCriteria);
        $this->renderWrappedTemplate('surveysgroups', 'create', $aData);
    }

    /**
     * Show and updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id the ID of the model to be updated
     * @return void
     * @throws CHttpException
     */
    public function update(int $id)
    {
        $model = $this->loadModel($id);
        if (!empty(App()->getRequest()->getPost('SurveysGroups'))) {
            if (!$model->hasPermission('group', 'update')) {
                throw new CHttpException(403, gT("You do not have permission to access this page."));
            }
            $postSurveysGroups = App()->getRequest()->getPost('SurveysGroups');
            // Remove name from post data, as it shouldn't be updated
            unset($postSurveysGroups['name']);
            /* Mimic survey system : only owner and superadmin can update owner … */
            /* After update : potential loose of rights on SurveysGroups */
            if (
                $model->owner_id != Yii::app()->user->id
                && !Permission::model()->hasGlobalPermission('superadmin', 'read')
            ) {
                $postSurveysGroups['owner_id'] = $model->owner_id;
            }
            if ($model->gsid == 1) {
                /* Move this to model */
                $postSurveysGroups['alwaysavailable'] = 1;
            }
            // parent_id control
            if (!empty($postSurveysGroups['parent_id'])) {
                $parentId = $postSurveysGroups['parent_id'] ;
                /* Check permission */
                $aAvailableParents = $model->getParentGroupOptions($model->gsid);
                if (!array_key_exists($parentId, $aAvailableParents)) {
                    Yii::app()->setFlashMessage(sprintf(gT("You don't have rights on Survey group"), CHtml::encode($parentId)), 'error');
                    $postSurveysGroups['parent_id'] = $model->parent_id;
                }
                /* avoid loop */
                $ParentSurveyGroup = $this->loadModel($parentId);
                $aParentsGsid = $ParentSurveyGroup->getAllParents(true);
                if (in_array($model->gsid, $aParentsGsid)) {
                    Yii::app()->setFlashMessage(gT("A child group can't be set as parent group"), 'error');
                    $this->getController()->redirect($this->getController()->createUrl('surveyAdministration/listsurveys', array("#" => 'surveygroups')));
                }
            }
            $model->attributes = $postSurveysGroups;
            if ($model->save()) {
                if (App()->request->getPost('saveandclose') !== null) {
                    $this->getController()->redirect($this->getController()->createUrl('surveyAdministration/listsurveys', array("#" => 'surveygroups')));
                }
            }
        }

        $oSurveySearch = new Survey('search');
        $oSurveySearch->gsid = $model->gsid;

        $aData = array(
            'model' => $model,
            'action' => App()->createUrl("admin/surveysgroups/sa/update", array('id' => $model->gsid, '#' => 'settingsForThisGroup')),
            'pageTitle' => gT('Update survey group: ') . CHtml::encode($model->title),
        );

        $aData['oSurveySearch'] = $oSurveySearch;
        $aData['aRigths'] = array(
            'update' => $model->hasPermission('group', 'update'),
            'delete' => $model->hasPermission('group', 'delete'),
            'owner_id' => $model->owner_id == Yii::app()->user->id || Permission::model()->hasGlobalPermission('superadmin', 'read')
        );

        $updateRightsForm = $aData['aRigths']['update'] ? 'surveys-groups-form' : null;

        $aData['topbar']['title'] = $aData['pageTitle'];
        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/layouts/partial_topbar/right_close_saveclose_save',
            [
                'isReturnBtn' => true,
                'returnUrl' => Yii::app()->createUrl("surveyAdministration/listsurveys#surveygroups"),
                'isCloseBtn' => false,
                'isSaveBtn' => true,
                'isSaveAndCloseBtn' => false,
                'formIdSave' => $updateRightsForm,
            ],
            true
        );

        /* User for dropdown */
        $aUserIds = getUserList('onlyuidarray');
        if (!in_array($model->owner_id, $aUserIds)) {
            $aUserIds[] = $model->owner_id;
        }
        $userCriteria = new CDbCriteria();
        $userCriteria->select = array("uid", "users_name", "full_name");
        $userCriteria->order = "full_name";
        $userCriteria->addInCondition('uid', $aUserIds);
        $aData['oUsers'] = User::model()->findAll($userCriteria);

        $oTemplateOptions           = new TemplateConfiguration();
        $oTemplateOptions->scenario = 'surveygroup';
        $filterForm = Yii::app()->request->getPost('TemplateConfiguration', false);
        if ($filterForm) {
            $oTemplateOptions->setAttributes($filterForm, false);
            if (array_key_exists('template_description', $filterForm)) {
                $oTemplateOptions->template_description = $filterForm['template_description'];
            }
            if (array_key_exists('template_type', $filterForm)) {
                $oTemplateOptions->template_type = $filterForm['template_type'];
            }
            if (array_key_exists('template_extends', $filterForm)) {
                $oTemplateOptions->template_extends = $filterForm['template_extends'];
            }
        }
        $aData['templateOptionsModel'] = $oTemplateOptions;

        // Page size
        if (Yii::app()->request->getParam('pageSize')) {
            Yii::app()->user->setState('pageSizeTemplateView', (int) Yii::app()->request->getParam('pageSize'));
        }
        $aData['pageSize'] = Yii::app()->user->getState('pageSizeTemplateView', Yii::app()->params['defaultPageSize']); // Page size

        $this->renderWrappedTemplate('surveysgroups', 'update', $aData);
    }

    /**
     * Show the survey settings menue for a particular group
     * @param integer $id group id, used for permission control
     * @return void
     */
    public function surveysettingmenues($id)
    {
        if (!$this->loadModel($id)->hasPermission('surveysettings', 'read')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        /* Can not call gloalsettings contoller fuinction sice _construct check access … */
        $menues = Surveymenu::model()->getMenuesForGlobalSettings();
        Yii::app()->getController()->renderPartial('super/_renderJson', ['data' => $menues[0]]);
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     * @throws CHttpException
     * @todo : find where it shown
     * @todo : fix $_POST call
     */
    public function surveySettings(int $id)
    {
        $bRedirect = 0;
        /** @var SurveysGroups $model */
        $model = $this->loadModel($id);
        if (!$model->hasPermission('surveysettings', 'read')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        $aData = array(
            'model' => $model
        );

        $sPartial = Yii::app()->request->getParam('partial', '_generaloptions_panel');

        /** @var SurveysGroupsettings $oSurvey */
        $oSurvey = SurveysGroupsettings::model()->findByPk($model->gsid);
        $oSurvey->setOptions(); //this gets the "values" from the group that inherits to this group ...
        $oSurvey->owner_id = $model->owner_id;

        if (App()->getRequest()->isPostRequest && !$model->hasPermission('surveysettings', 'update')) {
            throw new CHttpException(403, gT("You do not have permission to update survey settings."));
        }
        //every $_POST checked here is one of the switchers(On|Off|Inherit) names
        // Name of sidemenulink   => name of input field
        // "General settings"     => 'template'
        // "Presentation"         => 'showxquestions'
        // "Pariticipant setting" => 'anonymized'
        // "Notification & data"  => 'datestamp'
        // "Publication & access" => 'listpublic'
        if (
            isset($_POST['template']) || isset($_POST['showxquestions']) || isset($_POST['anonymized'])
            || isset($_POST['datestamp']) || isset($_POST['listpublic'])
        ) {
            // Get the current othersettings
            $currentOtherSettings = $oSurvey->othersettings ? json_decode($oSurvey->othersettings, true) : [];

            // Add the new attributes to othersettings
            $newOtherSettings = [
                'question_code_prefix' => Yii::app()->request->getPost('question_code_prefix', ''),
                'subquestion_code_prefix' => Yii::app()->request->getPost('subquestion_code_prefix', ''),
                'answer_code_prefix' => Yii::app()->request->getPost('answer_code_prefix', '')
            ];

            // Merge with existing settings (preserving other values that might be there)
            $mergedOtherSettings = array_merge($currentOtherSettings, $newOtherSettings);

            // Convert back to JSON
            $_POST['othersettings'] = json_encode($mergedOtherSettings);

            $oSurvey->attributes = $_POST;

            if (isset($_POST['listpublic'])) {
                //what is usecaptcha used for? see saveTranscribeCaptchaOptions method description ...
                // in default group this is set to 'N' ... (this means 'none' no captcha for survey access, regigstration
                // and 'save&load'
                $oSurvey->usecaptcha = Survey::saveTranscribeCaptchaOptions();
            }
            if ($oSurvey->save()) {
                $bRedirect = 1;
            }
        }

        $users = getUserList();
        $aData['users'] = array();
        $inheritOwner = empty($oSurvey['ownerLabel']) ? $oSurvey['owner_id'] : $oSurvey['ownerLabel'];
        $aData['users']['-1'] = gT('Inherit') . ' [' . $inheritOwner . ']';
        foreach ($users as $user) {
            $aData['users'][$user['uid']] = $user['user'] . ($user['full_name'] ? ' - ' . $user['full_name'] : '');
        }
        // Sort users by name
        asort($aData['users']);

        $aData['oSurvey'] = $oSurvey;

        if ($bRedirect && App()->request->getPost('saveandclose') !== null) {
            $this->getController()->redirect($this->getController()->createUrl('surveyAdministration/listsurveys', array("#" => 'surveygroups')));
        }

        // Page size
        if (Yii::app()->request->getParam('pageSize')) {
            Yii::app()->user->setState('pageSizeTemplateView', (int) Yii::app()->request->getParam('pageSize'));
        }
        $aData['pageSize'] = Yii::app()->user->getState('pageSizeTemplateView', Yii::app()->params['defaultPageSize']); // Page size

        Yii::app()->clientScript->registerPackage('bootstrap-switch', LSYii_ClientScript::POS_BEGIN);
        Yii::app()->clientScript->registerPackage('globalsidepanel');

        $aData['aDateFormatDetails'] = getDateFormatData(Yii::app()->session['dateformat']);
        $aData['jsData'] = [
            'sgid' => $id,
            'baseLinkUrl' => 'admin/surveysgroups/sa/surveysettings/id/' . $id,
            'getUrl' => Yii::app()->createUrl(
                'admin/surveysgroups/sa/surveysettingmenues',
                array('id' => $id)
            ),
            'i10n' => [
                'Survey settings' => gT('Survey settings')
            ]
        ];

        $aData['partial'] = $sPartial;

        $surveySettingsPermission = $model->hasPermission('surveysettings', 'update');
        $aData['topbar']['title'] = gT('Survey settings for group: ') . CHtml::encode($model->title);
        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/layouts/partial_topbar/right_close_saveclose_save',
            [
                'isReturnBtn' => false,
                'isCloseBtn' => true,
                'backUrl' => Yii::app()->createUrl("surveyAdministration/listsurveys#surveygroups"),
                'isSaveBtn' => $surveySettingsPermission,
                'formIdSave' => 'survey-settings-options-form',
                'isSaveAndCloseBtn' => $surveySettingsPermission,
                'formIdSaveClose' => 'survey-settings-options-form',
            ],
            true
        );
        $this->renderWrappedTemplate('surveysgroups', 'surveySettings', $aData);
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function delete($id)
    {
        $this->requirePostRequest();

        $oGroupToDelete = $this->loadModel($id);
        if (!$oGroupToDelete->hasPermission('group', 'delete')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        $sGroupTitle    = $oGroupToDelete->title;
        $returnUrl = App()->getRequest()->getPost('returnUrl', array('surveyAdministration/listsurveys', '#' => 'surveygroups'));

        if ($oGroupToDelete->gsid == 1) {
            Yii::app()->setFlashMessage(gT("You can't delete the default survey group!"), 'error');
            $this->getController()->redirect($returnUrl);
        } elseif ($oGroupToDelete->hasSurveys) {
            Yii::app()->setFlashMessage(gT("You can't delete a group if it's not empty!"), 'error');
            $this->getController()->redirect($returnUrl);
        } elseif ($oGroupToDelete->hasChildGroups) {
            Yii::app()->setFlashMessage(gT("You can't delete a group because one or more groups depend on it as parent!"), 'error');
            $this->getController()->redirect($returnUrl);
        } else {
            $oGroupToDelete->delete();
            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if (!App()->getRequest()->getQuery('ajax')) {
                Yii::app()->setFlashMessage(sprintf(gT("The survey group '%s' was deleted."), CHtml::encode($sGroupTitle)), 'success');
                $this->getController()->redirect($returnUrl);
            }
        }
    }

    /**
     * Lists all models
     * Only list SurveysGroup according to Permission, user must just be loggued.
     * @return void
     */
    public function index()
    {
        $model = new SurveysGroups('search');
        $aData = array(
            'model' => $model
        );
        $this->renderWrappedTemplate('surveysgroups', 'index', $aData);
    }

    /**
     * Manages all models.
     * @TODO : Remove
     */
    public function admin()
    {
        /* @see next comment : throw 500 error */
        throw new CHttpException(400, gT("Invalid action"));

        $model = new SurveysGroups('search'); // @todo : fix this : need update permission
        $model->unsetAttributes(); // clear any default values
        if (!empty(App()->getRequest()->getParam('SurveysGroups'))) {
            $model->attributes = App()->getRequest()->getParam('SurveysGroups');
        }
        /* Throw : SurveysGroupsController and its behaviors do not have a method or closure named "render". */
        $this->render('admin', array(
            'model' => $model,
        ));
    }


    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return SurveysGroups the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model = SurveysGroups::model()->findByPk($id);
        if ($model === null) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }
        if (!$model->hasPermission(SurveysGroups::getMinimalPermissionRead())) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param SurveysGroups $model the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if (App()->getRequest()->getPost('ajax') === 'surveys-groups-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
