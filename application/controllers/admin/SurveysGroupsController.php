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

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}


class SurveysGroupsController extends Survey_Common_Action
{

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function view($id)
    {
        $this->render('view', array(
            'model'=>$this->loadModel($id),
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function create()
    {
        $model = new SurveysGroups;

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (Yii::app()->getRequest()->getPost('SurveysGroups')) {
            $model->attributes = Yii::app()->getRequest()->getPost('SurveysGroups');
            $model->created_by = $model->owner_id = Yii::app()->user->id;
            if ($model->save()) {
                // save new SurveysGroupsettings record
                $modelSettings = new SurveysGroupsettings;
                $modelSettings->gsid = $model->gsid;
                $modelSettings->setToInherit();
                $modelSettings->owner_id = $model->owner_id;

                if ($modelSettings->save()) {
                    $this->getController()->redirect($this->getController()->createUrl('admin/survey/sa/listsurveys').'#surveygroups');
                }
                // What happen iof SurveysGroups saved but no SurveysGroupsettings ?
            }
        }

        $aData['model'] = $model;
        $aData['fullpagebar']['savebutton']['form'] = 'surveys-groups-form';
        $aData['fullpagebar']['returnbutton'] = array(
            'url'=>'admin/survey/sa/listsurveys#surveygroups',
            'text'=>gT('Close'),
        );

        $this->_renderWrappedTemplate('surveysgroups', 'create', $aData);
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function update($id)
    {
        $bRedirect = 0;
        $model = $this->loadModel($id);

        if (isset($_POST['SurveysGroups'])) {
            $model->attributes = $_POST['SurveysGroups'];


                // prevent loop
                if (!empty($_POST['SurveysGroups']['parent_id'])){
                    $sgid = $_POST['SurveysGroups']['parent_id'] ;
                    $ParentSurveyGroup = $this->loadModel($sgid);
                    $aParentsGsid = $ParentSurveyGroup->getAllParents(true);

                    if ( in_array( $model->gsid, $aParentsGsid  ) ) {
                        Yii::app()->setFlashMessage(gT("A child group can't be set as parent group"), 'error');
                        $this->getController()->redirect($this->getController()->createUrl('admin/survey/sa/listsurveys').'#surveygroups');
                    }
                }

            if ($model->save()) {
                $bRedirect = 1;
            }
        }

        $aData['model'] = $model;
        $oSurveySearch = new Survey('search');
        $oSurveySearch->gsid = $model->gsid;
        $aData['oSurveySearch'] = $oSurveySearch;

        $oTemplateOptions           = new TemplateConfiguration();
        $oTemplateOptions->scenario = 'surveygroup';
        $aData['templateOptionsModel'] = $oTemplateOptions;

        if ($bRedirect && App()->request->getPost('saveandclose') !== null){
            $this->getController()->redirect($this->getController()->createUrl('admin/survey/sa/listsurveys').'#surveygroups');
        }

        // Page size
        if (Yii::app()->request->getParam('pageSize')) {
            Yii::app()->user->setState('pageSizeTemplateView', (int) Yii::app()->request->getParam('pageSize'));
        }
        $aData['pageSize'] = Yii::app()->user->getState('pageSizeTemplateView', Yii::app()->params['defaultPageSize']); // Page size

        $this->_renderWrappedTemplate('surveysgroups', 'update', $aData);
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function surveySettings($id)
    {
        $bRedirect = 0;
        /** @var SurveysGroups $model */
        $model = $this->loadModel($id);

        $aData['model'] = $model;

        $sPartial = Yii::app()->request->getParam('partial', '_generaloptions_panel');
        /** @var SurveysGroupsettings $oSurvey */
        $oSurvey = SurveysGroupsettings::model()->findByPk($model->gsid);
        $oSurvey->setOptions(); //this gets the "values" from the group that inherits to this group ...
        $oSurvey->owner_id = $model->owner_id;

        //every $_POST checked here is one of the switchers(On|Off|Inherit) names
        // Name of sidemenulink   => name of input field
        // "General settings"     => 'template'
        // "Presentation"         => 'showxquestions'
        // "Pariticipant setting" => 'anonymized'
        // "Notification & data"  => 'datestamp'
        // "Publication & access" => 'listpublic'
        if(isset($_POST['template']) || isset($_POST['showxquestions']) || isset($_POST['anonymized'])
            || isset($_POST['datestamp']) || isset($_POST['listpublic'])){
            $oSurvey->attributes = $_POST;

            if(isset($_POST['listpublic'])){
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
        $aData['users']['-1'] = gT('Inherit').' ['. $inheritOwner . ']';
        foreach ($users as $user) {
            $aData['users'][$user['uid']] = $user['user'].($user['full_name'] ? ' - '.$user['full_name'] : '');
        }
        // Sort users by name
        asort($aData['users']);

        $aData['oSurvey'] = $oSurvey;

        if ($bRedirect && App()->request->getPost('saveandclose') !== null){
            $this->getController()->redirect($this->getController()->createUrl('admin/survey/sa/listsurveys').'#surveygroups');
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
            'baseLinkUrl' => 'admin/surveysgroups/sa/surveysettings/id/'.$id,
            'getUrl' => Yii::app()->createUrl('admin/globalsettings/sa/surveysettingmenues'),
            'i10n' => [
                'Survey settings' => gT('Survey settings')
            ]
        ];
        $aData['partial'] = $sPartial;

        $this->_renderWrappedTemplate('surveysgroups', 'surveySettings', $aData);
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function delete($id)
    {
        $oGroupToDelete = $this->loadModel($id);
        $sGroupTitle    = $oGroupToDelete->title;

        if ($oGroupToDelete->hasSurveys) {
            Yii::app()->setFlashMessage(gT("You can't delete a group if it's not empty!"), 'error');
            $this->getController()->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin/survey/sa/listsurveys '));
        } elseif ($oGroupToDelete->hasChildGroups) {
            Yii::app()->setFlashMessage(gT("You can't delete a group because one or more groups depend on it as parent!"), 'error');
            $this->getController()->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin/survey/sa/listsurveys '));
        } else {
            $oGroupToDelete->delete();

            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if (!isset($_GET['ajax'])) {
                Yii::app()->setFlashMessage(sprintf(gT("The survey group '%s' was deleted."), CHtml::encode($sGroupTitle)), 'success');
                $this->getController()->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin/survey/sa/listsurveys '));
            }
        }
    }

    /**
     * Lists all models.
     */
    public function index()
    {
        $model = new SurveysGroups('search');
        $aData['model'] = $model;
        $this->_renderWrappedTemplate('surveysgroups', 'index', $aData);
    }

    /**
     * Manages all models.
     */
    public function admin()
    {
        $model = new SurveysGroups('search');
        $model->unsetAttributes(); // clear any default values
        if (isset($_GET['SurveysGroups'])) {
                    $model->attributes = $_GET['SurveysGroups'];
        }

        $this->render('admin', array(
            'model'=>$model,
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
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param SurveysGroups $model the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'surveys-groups-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
