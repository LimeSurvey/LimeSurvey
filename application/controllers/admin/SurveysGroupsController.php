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

class SurveysGroupsController extends Survey_Common_Action
{

    /**
     * Displays a particular model.
     *
     * @param integer $id the ID of the model to be displayed
     * @return void
     */
    public function view($id)
    {
        if (!Permission::model()->hasSurveyGroupPermission($id, 'surveysettings', 'read')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }

        $this->render('view', array(
            'model'=>$this->loadModel($id),
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return void
     */
    public function create()
    {
        $model = new SurveysGroups;
        if (!Permission::model()->hasGlobalPermission('surveysgroups','create')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        /* Move to SurveysGroup model init ? */
        $model->owner_id = Yii::app()->user->id;
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

                if ($modelSettings->save()) {
                    $this->getController()->redirect(
                        App()->createUrl("admin/surveysgroups/sa/update", array('id' => $model->gsid, '#' => 'settingsForThisGroup'))
                    );
                }
                // What happen if SurveysGroups saved but no SurveysGroupsettings ?
            }
        }
        $aData = array(
            'model' => $model,
            'action' => App()->createUrl("admin/surveysgroups/sa/create", array('#'=>'settingsForThisGroup')),
        );
        $aData['aRigths'] = array(
            'update' => true,
            'delete' => false,
            'owner_id' => true,
        );
        $aData['fullpagebar'] = array(
            'savebutton' => array(
                'form' => 'surveys-groups-form'
            ),
            'returnbutton' => array(
                'url' => $this->getController()->createUrl('surveyAdministration/listsurveys', array("#"=>'surveygroups')),
                'text' => gT('Close'),
            )
        );
        /* User for dropdown */
        $aUserIds = getUserList('onlyuidarray');
        $userCriteria = new CDbCriteria;
        $userCriteria->select = array("uid", "users_name", "full_name");
        $userCriteria->order = "full_name";
        $userCriteria->addInCondition('uid',$aUserIds);
        $aData['oUsers'] = User::model()->findAll($userCriteria);
        $this->_renderWrappedTemplate('surveysgroups', 'create', $aData);
    }

    /**
     * Show and updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @todo : check if this function can be called with >hasSurveyGroupPermission($id, 'group', 'read')
     * @param integer $id the ID of the model to be updated
     * @return void
     */
    public function update($id)
    {
        $model = $this->loadModel($id);
        if (!empty(App()->getRequest()->getPost('SurveysGroups'))) {
            if (!Permission::model()->hasSurveyGroupPermission($id, 'group', 'update')) {
                throw new CHttpException(403, gT("You do not have permission to access this page."));
            }
            $postSurveysGroups = App()->getRequest()->getPost('SurveysGroups');
            /* Mimic survey system : only owner and superadmin can update owner … */
            /* After update : potential loose of rights on SurveysGroups */
            if($model->owner_id != Yii::app()->user->id
                && !Permission::model()->hasGlobalPermission('superadmin', 'read')
            ) {
                $postSurveysGroups['owner_id'] = $model->owner_id;
            }
            if($model->gsid == 1) {
                /* Move this to model */
                $postSurveysGroups['alwaysavailable'] = 1;
            }
            // parent_id control
            if (!empty($postSurveysGroups['parent_id'])) {
                $parentId = $postSurveysGroups['parent_id'] ;
                /* Check permission */
                $aAvailableParents = $model->getParentGroupOptions($model->gsid);
                if (!array_key_exists($parentId, $aAvailableParents)) {
                    Yii::app()->setFlashMessage(sprintf(gT("You don't have rights on Survey group"),CHtml::encode($parentId)), 'error');
                    $postSurveysGroups['parent_id'] = $model->parent_id;
                }
                /* avoid loop */
                $ParentSurveyGroup = $this->loadModel($parentId);
                $aParentsGsid = $ParentSurveyGroup->getAllParents(true);
                if ( in_array( $model->gsid, $aParentsGsid  ) ) {
                    Yii::app()->setFlashMessage(gT("A child group can't be set as parent group"), 'error');
                    $this->getController()->redirect($this->getController()->createUrl('surveyAdministration/listsurveys', array("#"=>'surveygroups')));
                }
            }
            $model->attributes = $postSurveysGroups;
            if ($model->save()) {
                if (App()->request->getPost('saveandclose') !== null){
                    $this->getController()->redirect($this->getController()->createUrl('surveyAdministration/listsurveys', array("#"=>'surveygroups')));
                }
            }
        }

        $aData = array(
            'model' => $model,
            'action' => App()->createUrl("admin/surveysgroups/sa/update", array('id' => $model->gsid, '#' => 'settingsForThisGroup')),
        );
        $oSurveySearch = new Survey('search');
        $oSurveySearch->gsid = $model->gsid;
        $aData['oSurveySearch'] = $oSurveySearch;
        $aData['aRigths'] = array(
            'update' => Permission::model()->hasSurveyGroupPermission($id, 'group', 'update'),
            'delete' => Permission::model()->hasSurveyGroupPermission($id, 'group', 'delete'),
            'owner_id' => $model->owner_id == Yii::app()->user->id || Permission::model()->hasGlobalPermission('superadmin', 'read')
        );

        /* User for dropdown */
        $aUserIds = getUserList('onlyuidarray');
        if(!in_array($model->owner_id,$aUserIds)) {
            $aUserIds[] =$model->owner_id;
        }
        $userCriteria = new CDbCriteria;
        $userCriteria->select = array("uid", "users_name", "full_name");
        $userCriteria->order = "full_name";
        $userCriteria->addInCondition('uid',$aUserIds);
        $aData['oUsers'] = User::model()->findAll($userCriteria);

        $oTemplateOptions           = new TemplateConfiguration();
        $oTemplateOptions->scenario = 'surveygroup';
        $aData['templateOptionsModel'] = $oTemplateOptions;
        // Page size
        if (Yii::app()->request->getParam('pageSize')) {
            Yii::app()->user->setState('pageSizeTemplateView', (int) Yii::app()->request->getParam('pageSize'));
        }
        $aData['pageSize'] = Yii::app()->user->getState('pageSizeTemplateView', Yii::app()->params['defaultPageSize']); // Page size

        $this->_renderWrappedTemplate('surveysgroups', 'update', $aData);
    }

    /**
     * Show the survey settings menue for a particular group
     * @param integer $id group id, used for permission control
     * @todo camelCase here and globalsettings->surveysettingmenues
     * @return void
     */
    public function surveysettingmenues($id) {
        if (!Permission::model()->hasSurveyGroupPermission($id, 'surveysettings', 'read')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        /* Can not call gloalsettings contoller fuinction sice _construct check access … */
        $menues = Surveymenu::model()->getMenuesForGlobalSettings();
        Yii::app()->getController()->renderPartial('super/_renderJson', ['data' => $menues[0]]);
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @todo : find where it shown
     * @todo : fix $_POST call
     * @param integer $id the ID of the model to be updated
     */
    public function surveySettings($id)
    {
        $bRedirect = 0;
        /** @var SurveysGroups $model */
        $model = $this->loadModel($id);
        if (!Permission::model()->hasSurveyGroupPermission($id, 'surveysettings', 'read')) {
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

        if (App()->getRequest()->isPostRequest && !Permission::model()->hasSurveyGroupPermission($id, 'surveysettings', 'update')) {
            throw new CHttpException(403, gT("You do not have permission to update survey settings."));
        }
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
            $this->getController()->redirect($this->getController()->createUrl('surveyAdministration/listsurveys', array("#"=>'surveygroups')));
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
            'getUrl' => Yii::app()->createUrl(
                'admin/surveysgroups/sa/surveysettingmenues',
                array('id' => $id)
            ),
            'i10n' => [
                'Survey settings' => gT('Survey settings')
            ]
        ];
        $aData['buttons'] = array(
            'closebutton'=>array(
                'url' => App()->createUrl('surveyAdministration/listsurveys', array('#' => 'surveygroups')),
            ),
        );
        if (Permission::model()->hasSurveyGroupPermission($id, 'surveysettings', 'update')) {
            $aData['buttons']['savebutton'] = array(
                'form' => 'survey-settings-options-form'
            );
            $aData['buttons']['saveandclosebutton'] = array(
                'form' => 'survey-settings-options-form'
            );
        }
        $aData['partial'] = $sPartial;

        $this->_renderWrappedTemplate('surveysgroups', 'surveySettings', $aData);
    }

    /**
     * Shown permissions list, allow to add user and group,
     * No action done
     * @param integer$id SurveysGroups id
     */
    public function permissions($id)
    {
        /** @var SurveysGroups $model */
        $model = $this->loadModel($id);
        if (!Permission::model()->hasSurveyGroupPermission($id, 'permission', 'read')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        $aData = array(
            'model' => $model
        );
        $aCruds = array('create', 'read', 'update', 'delete', 'import', 'export');
        $aSurveysGroupsPermissions = Permission::model()->getEntityBasePermissions('SurveysGroups');
        $aSurveysInGroupPermissions = Permission::model()->getEntityBasePermissions('SurveysInGroup');
        $aDefinitionPermissions = array_merge(
            $aSurveysGroupsPermissions,
            $aSurveysInGroupPermissions
        );
        foreach($aDefinitionPermissions as $permissionKey => $aPermission) {
            $aDefinitionPermissions[$permissionKey]['maxCrud'] = count(array_filter(array_intersect_key($aPermission,array_flip($aCruds)))); // Used for mixed class
        }
        /* Find all current user list with rights */
        /* @todo : move this to : SurveysGroups ? Permission ? User ?*/
        $oCriteria = new CDbCriteria;
        $oCriteria->select = ['uid','entity','entity_id'];
        $oCriteria->group = 'uid';
        $oCriteria->compare("entity", "SurveysGroups"); // on SurveyGroup
        $oCriteria->compare("entity_id", $model->primaryKey); // on this SurveyGroup
        $oCriteria->compare("uid", "<>".App()->user->getId()); // not self user
        $aExistingUsers = CHtml::listData(Permission::model()->findAll($oCriteria),'uid','uid');
        $oExistingUsers = array();
        $aCurrentsUserRights = array();
        if(!empty($aExistingUsers)) {
            $oCriteria = new CDbCriteria;
            $oCriteria->order = "users_name";
            $oCriteria->addInCondition("uid",$aExistingUsers);
            if (Yii::app()->getConfig('usercontrolSameGroupPolicy') == true && !Permission::model()->hasGlobalPermission('superadmin')) {
                $authorizedUsersList = getUserList('onlyuidarray'); // Limit the user list for the samegrouppolicy
                $oCriteria->addInCondition("uid",$authorizedUsersList);
            }
            $oExistingUsers = User::model()->findAll($oCriteria);
            $aCurrentsUserRights = array();
            foreach($oExistingUsers as $oUser) {
                foreach($aSurveysGroupsPermissions as $sPermission => $aPermissions) {
                    $aCurrentsUserRights[$oUser->uid][$sPermission] = array();
                    foreach(array_intersect_key($aPermissions,array_flip($aCruds)) as $sCrud => $available) {
                        if($available && Permission::model()->hasSurveyGroupPermission($id, $sPermission, $sCrud , $oUser->uid)) {
                            $aCurrentsUserRights[$oUser->uid][$sPermission][] = $sCrud;
                        }
                    }
                }
                foreach($aSurveysInGroupPermissions as $sPermission => $aPermissions) {
                    $aCurrentsUserRights[$oUser->uid][$sPermission] = array();
                    foreach(array_intersect_key($aPermissions,array_flip($aCruds)) as $sCrud => $available) {
                        if($available && Permission::model()->hasSurveyGroupPermission($id, $sPermission, $sCrud , $oUser->uid)) {
                            $aCurrentsUserRights[$oUser->uid][$sPermission][] = $sCrud;
                        }
                    }
                }
            }
        }

        $oAddUserList  = array();
        $oAddGroupList  = array();
        if (Permission::model()->hasSurveyGroupPermission($id, 'permission', 'create')) {
            /* Search user withouth rights on SurveyGroup */
            /* @todo : move this to : SurveysGroups ? Permission ? User ?*/
            $oCriteria = new CDbCriteria;
            if (Yii::app()->getConfig('usercontrolSameGroupPolicy') == true && !Permission::model()->hasGlobalPermission('superadmin')) {
                $authorizedUsersList = getUserList('onlyuidarray'); // Limit the user list for the samegrouppolicy
                $oCriteria->addInCondition("uid",$authorizedUsersList);
            }
            $oCriteria->addNotInCondition("uid",$aExistingUsers);
            $oCriteria->compare("uid","<>".Permission::getUserId());
            $oCriteria->order = "users_name";
            $oAddUserList = User::model()->findAll($oCriteria);
            /* User group according to rights */
            $oCriteria = new CDbCriteria;
            if (Yii::app()->getConfig('usercontrolSameGroupPolicy') == true && !Permission::model()->hasGlobalPermission('superadmin')) {
                $authorizedGroupsList = getUserGroupList();
                $oCriteria->addInCondition("ugid",$authorizedGroupsList);
            }
            $oCriteria->order = "name";
            $oAddGroupList = UserGroup::model()->findAll($oCriteria);
        }
        $aData['subview'] = 'viewCurrents';
        $aData['buttons'] = array(
            'closebutton'=>array(
                'url' => App()->createUrl('surveyAdministration/listsurveys', array('#' => 'surveygroups')),
            ),
        );
        $aData['aPermissionData'] = array(
            'aDefinitionPermissions' => $aDefinitionPermissions,
            'oExistingUsers' => $oExistingUsers,
            'aCurrentsUserRights' => $aCurrentsUserRights,
            'oAddUserList' => $oAddUserList,
            'oAddGroupList' => $oAddGroupList,
            'model' => $model,
        );
        $this->_renderWrappedTemplate('surveysgroups', 'permissions', $aData);
    }

    /**
     * Add minimal permission (read) to user
     * Show result and allow to set other permissions
     * @param integer $id SurveysGroups id
     * @return void
     */
    public function permissionsAddUser($id)
    {
        $model = $this->loadModel($id);
        if (!Permission::model()->hasSurveyGroupPermission($id, 'permission', 'create')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        $uid = App()->getRequest()->getPost('uid');
        if (!$uid) {
            throw new CHttpException(400, gT("Invalid action"));
        }
        /* Check if logged user can see user */
        if(!in_array($uid, getUserList('onlyuidarray'))) {
            throw new CHttpException(403, gT("You do not have permission to this user."));
        }
        $aData = array(
            'model' => $model
        );
        $aData['subview'] = 'addUserResult';
        $aData['buttons'] = array(
            'closebutton' =>array(
                'url'=> App()->createUrl("admin/surveysgroups/sa/permission",array("id" => $id)),
            ),
        );
        $aData['aPermissionData'] = array(
            'result' => array(),
            'uid' => $uid,
            'model' => $model,
        );
        $result = array(
            'success' => false,
            'warning' => false,
            'error' => false,
        );
  
        $oPermission = Permission::setMinimalEntityPermission($uid, $id, 'SurveysGroups');
        if(!$oPermission->hasErrors()) {
            $result['success'] = gT("User added.");
        } else {
            $result['error'] = CHtml::errorSummary($oPermission);
        }

        $aData['aPermissionData']['result'] = $result;
        $this->_renderWrappedTemplate('surveysgroups', 'permissions', $aData);
    }
    /**
     * Add minimal permission (read) to all users in a group of user
     * Show result and allow to set other permissions
     * @param integer $id SurveysGroups id
     * @return void
     */
    public function permissionsAddUserGroup($id)
    {
        $model = $this->loadModel($id);
        if (!Permission::model()->hasSurveyGroupPermission($id, 'permission', 'create')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        $ugid = App()->getRequest()->getPost('ugid');
        if (!$ugid) {
            throw new CHttpException(400, gT("Invalid action"));
        }
        /* Check if logged user can see user group */
        if(!in_array($ugid, getUserGroupList())) {
            throw new CHttpException(403, gT("You do not have permission to this user group."));
        }
        $aData = array(
            'model' => $model
        );
        $aData['subview'] = 'addUserGroupResult';
        $aData['buttons'] = array(
            'closebutton' =>array(
                'url'=> App()->createUrl("admin/surveysgroups/sa/permission",array("id" => $id)),
            ),
        );
        $aData['aPermissionData'] = array(
            'result' => array(),
            'ugid' => $ugid,
            'model' => $model,
        );
        $result = array(
            'success' => false,
            'warning' => false,
            'error' => false,
        );
        /* All seems OK */
        $oUserInGroups = UserInGroup::model()->findAll(
            'ugid = :ugid AND uid <> :currentUserId AND uid <> :surveysgroupOwnerId',
            array(
                ':ugid' => $ugid,
                ':currentUserId' => Permission::model()->getUserId(), // Don't need to set to current user
                ':surveysgroupOwnerId' => $model->getOwnerId(), // Don't need to set to owner (?) , get from surveyspermission
            )
        );
        foreach($oUserInGroups as $oUserInGroup) {
            Permission::setMinimalEntityPermission($oUserInGroup->uid, $id, 'SurveysGroups');
        }
        // Did we check something ? Some warning if group is empty for example ?
        $result['success'] = gT("User group added.");
        $aData['aPermissionData']['result'] = $result;
        $this->_renderWrappedTemplate('surveysgroups', 'permissions', $aData);
    }

    /**
     * Shown permissions list for user (or group)
     * @param integer $id SurveysGroups id
     * @param integer $to user id
     */
    public function permissionsUserSet($id, $to)
    {
        $oUser = User::model()->findByPk($to);
        if(empty($oUser)) {
            throw new CHttpException(401, gT("User not found"));
        }
        if(!in_array($to, getUserList('onlyuidarray'))) {
            throw new CHttpException(403, gT("You do not have permission to this user."));
        }
        if ($to == App()->user->getId()) {
            throw new CHttpException(403, gT("You do not set your own permission."));
        }
        $this->permissionsSet($id, $to, 'user');
    }

    /**
     * Shown permissions list for user (or group)
     * @param integer $id SurveysGroups id
     * @param integer $uid user id
     */
    public function permissionsUserDelete($id, $uid)
    {
        $model = $this->loadModel($id);
        if (!Permission::model()->hasSurveyGroupPermission($id, 'permission', 'delete')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        $oUser = User::model()->findByPk($uid);
        if(empty($oUser)) {
            throw new CHttpException(401, gT("User not found"));
        }
        if(!in_array($uid, getUserList('onlyuidarray'))) {
            throw new CHttpException(403, gT("You do not have permission to this user."));
        }
        if ($uid == App()->user->getId()) {
            throw new CHttpException(403, gT("You do not delete your own permission."));
        }
        Permission::model()->deleteAll("uid = :uid AND entity_id = :sid AND entity = :entity", array(
            ':uid' => $uid,
            ':sid' => $id,
            ':entity' => 'SurveysGroups'
        ));
        Permission::model()->deleteAll("uid = :uid AND entity_id = :sid AND entity = :entity", array(
            ':uid' => $uid,
            ':sid' => $id,
            ':entity' => 'SurveysInGroup'
        ));
        $aData = array(
            'model' => $model
        );
        $aData['subview'] = 'deleteUserResult';
        $aData['buttons'] = array(
            'closebutton' =>array(
                'url'=> App()->createUrl("admin/surveysgroups/sa/permission",array("id" => $id)),
            ),
        );
        $aData['aPermissionData']=array(
            'model' => $model,
            'oUser' => $oUser,
        );
        $this->_renderWrappedTemplate('surveysgroups', 'permissions', $aData);
    }

    /**
     * Shown permissions list for user (or group)
     * @param integer $id SurveysGroups id
     * @param integer $id group id
     */
    public function permissionsUserGroupSet($id, $to)
    {
        $oUserGroup = UserGroup::model()->findByPk($to);
        if(empty($oUserGroup)) {
            throw new CHttpException(401, gT("User group not found"));
        }
        /* Check if logged user can see user group */
        if(!in_array($to, getUserGroupList())) {
            throw new CHttpException(403, gT("You do not have permission to this user group."));
        }
        $this->permissionsSet($id, $to, 'group');
    }
    /**
     * Shown permissions list for user (or group)
     * @param integer $id SurveysGroups id
     * @param integer $id user or group id
     * @param string $type user or group
     */
    private function permissionsSet($id, $to, $type = 'user')
    {
        $model = $this->loadModel($id);
        if (!Permission::model()->hasSurveyGroupPermission($id, 'permission', 'read')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        if($type == 'user') {
            $oUserGroup = null;
            $oUser = User::model()->findByPk($to);
            $userId = $to; // More clear after
        } else {
            $oUserGroup = UserGroup::model()->findByPk($to);
            $oUser = null;
        }
        $aSurveysGroupsPermissions = Permission::model()->getEntityBasePermissions('SurveysGroups');
        /* Set the current : @todo move to Permission::model ? Or an helper ?*/
        foreach(array_keys($aSurveysGroupsPermissions) as $sPermission) {
            $aSurveysGroupsPermissions[$sPermission]['current'] = array(
                'create' => array(
                    'checked'=> false,
                    'disabled' => !Permission::model()->hasSurveyGroupPermission($id,$sPermission,'create'),
                    'indeterminate'=> false
                ),
                'read' => array(
                    'checked'=> false,
                    'disabled' => !Permission::model()->hasSurveyGroupPermission($id,$sPermission,'read'),
                    'indeterminate'=> false
                ),
                'update' => array(
                    'checked'=> false,
                    'disabled' => !Permission::model()->hasSurveyGroupPermission($id,$sPermission,'update'),
                    'indeterminate'=> false
                ),
                'delete' => array(
                    'checked'=> false,
                    'disabled' => !Permission::model()->hasSurveyGroupPermission($id,$sPermission,'delete'),
                    'indeterminate'=> false
                ),
                'import' => array(
                    'checked'=> false,
                    'disabled' => !Permission::model()->hasSurveyGroupPermission($id,$sPermission,'import'),
                    'indeterminate'=> false
                ),
                'export' => array(
                    'checked'=> false,
                    'disabled' => !Permission::model()->hasSurveyGroupPermission($id,$sPermission,'export'),
                    'indeterminate'=> false
                ),
            );
            $aSurveysGroupsPermissions[$sPermission]['entity'] = 'SurveysGroups';
            if ($type == 'user') {
                $oCurrentPermissions = Permission::model()->find(
                    "entity = :entity AND entity_id = :entity_id AND uid = :uid AND permission = :permission",
                    array(
                        ":entity" => 'SurveysGroups',
                        ":entity_id" => $id,
                        ":uid" => $userId,
                        ":permission" => $sPermission
                    )
                );
                foreach(array_keys($aSurveysGroupsPermissions[$sPermission]['current']) as $sCrud) {
                    if($aSurveysGroupsPermissions[$sPermission][$sCrud]) {
                        $havePermissionSet = !empty($oCurrentPermissions) && $oCurrentPermissions->getAttribute("{$sCrud}_p");
                        $aSurveysGroupsPermissions[$sPermission]['current'][$sCrud]['checked'] = $havePermissionSet;
                        $aSurveysGroupsPermissions[$sPermission]['current'][$sCrud]['indeterminate'] = !$havePermissionSet && Permission::model()->hasSurveyGroupPermission($id, $sPermission, $sCrud, $userId); // Set by global or owner
                    }
                }
            }
        }
        $aSurveysInGroupPermissions = Permission::model()->getEntityBasePermissions('SurveysInGroup');
        /* Set the current : @todo move to Permission::model ? Or an helper ?*/
        foreach(array_keys($aSurveysInGroupPermissions) as $sPermission) {
            $aSurveysInGroupPermissions[$sPermission]['current'] = array(
                'create' => array(
                    'checked'=> false,
                    'disabled' => !Permission::model()->hasSurveyGroupPermission($id,$sPermission,'create'),
                    'indeterminate'=> false
                ),
                'read' => array(
                    'checked'=> false,
                    'disabled' => !Permission::model()->hasSurveyGroupPermission($id,$sPermission,'read'),
                    'indeterminate'=> false
                ),
                'update' => array(
                    'checked'=> false,
                    'disabled' => !Permission::model()->hasSurveyGroupPermission($id,$sPermission,'update'),
                    'indeterminate'=> false
                ),
                'delete' => array(
                    'checked'=> false,
                    'disabled' => !Permission::model()->hasSurveyGroupPermission($id,$sPermission,'delete'),
                    'indeterminate'=> false
                ),
                'import' => array(
                    'checked'=> false,
                    'disabled' => !Permission::model()->hasSurveyGroupPermission($id,$sPermission,'import'),
                    'indeterminate'=> false
                ),
                'export' => array(
                    'checked'=> false,
                    'disabled' => !Permission::model()->hasSurveyGroupPermission($id,$sPermission,'export'),
                    'indeterminate'=> false
                )
            );
            $aSurveysInGroupPermissions[$sPermission]['entity'] = 'SurveysInGroup';
            if ($type == 'user') {
                $oCurrentPermissions = Permission::model()->find(
                    "entity = :entity AND entity_id = :entity_id AND uid = :uid AND permission = :permission",
                    array(
                        ":entity" => 'SurveysInGroup',
                        ":entity_id" => $id,
                        ":uid" => $userId,
                        ":permission" => $sPermission
                    )
                );
                foreach(array_keys($aSurveysInGroupPermissions[$sPermission]['current']) as $sCrud) {
                    if($aSurveysInGroupPermissions[$sPermission][$sCrud]) {
                        $havePermissionSet = !empty($oCurrentPermissions) && $oCurrentPermissions->getAttribute("{$sCrud}_p");
                        $aSurveysInGroupPermissions[$sPermission]['current'][$sCrud]['checked'] = $havePermissionSet;
                        $aSurveysInGroupPermissions[$sPermission]['current'][$sCrud]['indeterminate'] = !$havePermissionSet && Permission::model()->hasSurveyGroupPermission($id, $sPermission, $sCrud, $userId); // Set by global or owner
                    }
                }
            }
        }
        $aPermissions = array_merge(
            $aSurveysGroupsPermissions,
            $aSurveysInGroupPermissions
        );
        $aData = array(
            'model' => $model,
            'subview' => 'setPermissionForm',
            'buttons' => array(
                'savebutton' => array(
                    'form' => 'permissionsSave'
                ),
                'saveandclosebutton' => array(
                    'form' => 'permissionsSave'
                ),
                'closebutton' => array(
                    'url'=> App()->createUrl('surveyAdministration/listsurveys', array('#' => 'surveygroups')),
                ),
            ),
        );
        $aData['aPermissionData']=array(
            'aPermissions' => $aPermissions,
            'model' => $model,
            'uid' => ($type == 'user') ? $to : null,
            'to' => $to,
            'type' => $type,
            'oUser' => $oUser,
            'oUserGroup' => $oUserGroup,
        );
        $this->_renderWrappedTemplate('surveysgroups', 'permissions', $aData);
        Yii::app()->end();
    }

    /**
     * Save current permissions for user (or group)
     * use POST value for usezr id or group id
     * redirect (in all cas) to surveysgroups permission or to user set permission (permissionsSet function)
     * @param integer $id SurveysGroups id
     * @return void
     */
    public function permissionsSave($id)
    {
        $model = $this->loadModel($id);
        if (!Permission::model()->hasSurveyGroupPermission($id, 'permission', 'update')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        $type = App()->getRequest()->getPost('type','user');
        if($type == 'group') {
            $ugid = App()->getRequest()->getPost('ugid');
            if(empty($ugid)) {
                throw new CHttpException(400, gT("Invalid action"));
            }
            $oUserInGroups = UserInGroup::model()->findAll(
                'ugid = :ugid AND uid <> :currentUserId AND uid <> :surveygroupsOwnerId',
                array(
                    ':ugid' => $ugid,
                    ':currentUserId' => Permission::model()->getUserId(), // Don't need to set to current user
                    ':surveygroupsOwnerId' => $model->getOwnerId(), // Don't need to set to owner (?) , get from surveyspermission
                )
            );
            $uids = CHtml::listData($oUserInGroups,'uid','uid');
        } else {
            $uid = App()->getRequest()->getPost('uid');
            if(empty($uid)) {
                throw new CHttpException(400, gT("Invalid action"));
            }
            if(!in_array($uid, getUserList('onlyuidarray'))) {
                throw new CHttpException(403, gT("You do not have permission to this user."));
            }
            $uids = array($uid);
        }
        $set = App()->getRequest()->getPost('set');
        foreach($set as $entity => $aPermissionSet) {
            foreach($uids as $uid) {
                /* Permission::model()->setPermissions return true or break */
                Permission::model()->setPermissions(
                    $uid,
                    $id,
                    $entity,
                    $aPermissionSet
                );
            }
        }
        App()->setFlashMessage("Surveys groups permissions were successfully updated");
        if($type == 'group') {
            App()->request->redirect(App()->getController()->createUrl('admin/surveysgroups/sa/permissions', array('id'=>$id)));
        }
        if (App()->getRequest()->getParam('saveandclose')) {
            App()->request->redirect(App()->getController()->createUrl('admin/surveysgroups/sa/permissions', array('id'=>$id)));
        }
        App()->request->redirect(App()->getController()->createUrl('admin/surveysgroups/sa/permissionsUserSet', array('id'=>$id, 'to' => $uid)));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function delete($id)
    {
        $oGroupToDelete = $this->loadModel($id);
        if (!Permission::model()->hasSurveyGroupPermission($id, 'group', 'delete')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        $sGroupTitle = $oGroupToDelete->title;
        $returnUrl = App()->getRequest()->getPost('returnUrl', array('surveyAdministration/listsurveys', '#' => 'surveygroups'));
        if ($oGroupToDelete->hasSurveys) {
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
     * Lists all models.
     *
     * @todo Why we don't need permission check here?
     * @return void
     */
    public function index()
    {
        $model = new SurveysGroups('search');
        $aData = array(
            'model' => $model
        );
        $this->_renderWrappedTemplate('surveysgroups', 'index', $aData);
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
        if (App()->getRequest()->getPost('ajax') === 'surveys-groups-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
