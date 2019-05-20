<?php
use LimeSurvey\PluginManager\AuthPluginBase;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
* LimeSurvey
* Copyright (C) 2013 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
* Usermanagement Controller
*
* This controller is for the user management panel
*
* @package        LimeSurvey
* @subpackage    Backend
*/
class UserManagement extends Survey_Common_Action
{
    public function index() {
        $this->getController()->redirect(App()->createUrl('admin/usermanagement/sa/view'));
    }

    public function view() {
        
        if (isset($_GET['pageSize'])) {
            Yii::app()->user->setState('pageSize', $this->api->getRequest()->getParam('pageSize'));
        }
        App()->getClientScript()->registerPackage('usermanagement');

        $aData = [];
        $model = new User('search');
        $model->setAttributes(Yii::app()->getRequest()->getParam('User'), false);
        $aData['model'] = $model;
        

        $aData['columnDefinition'] = $model->managementColums;
        $aData['pageSize'] = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
        $aData['formUrl'] = App()->createUrl('admin/usermanager/sa/view');
        
        $aData['massiveAction'] = App()->getController()->renderPartial(
            '/admin/usermanager/massiveAction/_selector',
            array(),
            true,
            false
        );

        $this->_renderWrappedTemplate('usermanager', 'view', $aData);
        //return $this->renderPartial('view', $aData,true);
    }

    
    /**
     * Open modal to edit, or create a new user
     *
     * @param integer $userid 
     * @return string
     */
    public function editusermodal($userid = null)
    {
        $oUser = $userid === null ? new User() : User::model()->findByPk($userid);
        return Yii::app()->getController()->renderPartial('/admin/usermanager/partial/addedituser', ['oUser'=>$oUser]);
    }

    /**
     * Stores changes to user, or triggers userCreateEvent
     *
     *
     * @param integer $userid
     * @return string | JSON
     */
    public function applyedit($userid=null)
    {
        if (!Permission::model()->hasGlobalPermission('users', 'update')) {
            return Yii::app()->getController()->renderPartial('/admin/usermanager/partial/json.php', ["data"=>[
                'success' => false,
                'error' => gT("You do not have permission to access this page.")
            ]]);
        }

        $aUser = Yii::app()->request->getParam('User');
        $paswordTest = Yii::app()->request->getParam('password_repeat', false);

        if (!empty($paswordTest) && $paswordTest !== $aUser['password']) {
            return Yii::app()->getController()->renderPartial('/admin/usermanager/partial/json', ["data"=>[
                'success' => false,
                'error' => gT('Passwords do not match')
            ]]);
        }
        
        if($userid === null) {
            return $this->_createNewUser($aUser);
        }

        return $this->_editUser($aUser);
    }

    /**
     * Show some user detail and statistics
     *
     *
     * @param PluginEvent $oEvent
     * @param CHttpRequest $oRequest
     * @return string
     */
    public function viewuser($userid)
    {
        $oUser = User::model()->findByPk($userid);
        
        $usergroups = array_map(function ($oUGMap) {
            return $oUGMap->group->name;
        }, UserInGroup::model()->findAllByAttributes(['uid'=>$oUser->uid]));

        return $this->renderPartial('partial.showuser', ['usergroups' => $usergroups, 'oUser'=>$oUser]);
    }

    /**
     * Öffnet ein Bestätigungsfenster zum Löschen eines Nutzers
     *
     * Zum Aufruf mittels GET an plugins/direct Event
     * Bsp.: /plugins/direct/plugin/SMKUserManagement/function/delete
     *
     * @param PluginEvent $oEvent
     * @param CHttpRequest $oRequest
     * @return string
     */
    public function delete($oEvent, $oRequest)
    {
        $userId = $oRequest->getParam('userid');
        $oUser = User::model()->findByPk($userId);
        return $this->renderPartial('partial.delete', ['oUser'=>$oUser]);
    }

    /**
     * Löscht einen Benutzer nach vorheriger Bestätigung
     *
     * Zum Aufruf mittels POST an plugins/direct Event
     * Bsp.: /plugins/direct/plugin/SMKUserManagement/function/deleteconfirm
     *
     * @param PluginEvent $oEvent
     * @param CHttpRequest $oRequest
     * @return void
     */
    public function deleteconfirm($oEvent, $oRequest)
    {
        if (!Permission::model()->hasGlobalPermission('users', 'delete')) {
            return Yii::app()->getController()->renderPartial('/admin/usermanager/partial/json', ["data"=>[
                'success' => false,
                'error' => "Keine Berechtigung für diese Aktion"
            ]]);
        }
        $userId = $oRequest->getPost('userid');
        $oUser = User::model()->findByPk($userId);
        $oUser->delete();
        App()->getController()->redirect(App()->createUrl('/admin/pluginhelper/sa/fullpagewrapper/plugin/SMKUserManager/method/index'));
    }

    /**
     * Zeigt eine Maske an um die Berechtigung eines Benutzers zu editieren
     *
     * Zum Aufruf mittels GET an plugins/direct Event
     * Bsp.: /plugins/direct/plugin/SMKUserManagement/function/userpermissions
     *
     * @param PluginEvent $oEvent
     * @param CHttpRequest $oRequest
     * @return string
     */
    public function userpermissions()
    {
        $oRequest = App()->request;
        $userId = $oRequest->getParam('userid');
        $oUser = User::model()->findByPk($userId);
        $sCurrentState = $this->parseCurrentState($oUser);
        
        $aAllSurveys = Survey::model()->findAll();
        $aMySurveys = array_filter($aAllSurveys, function ($oSurvey) {
            if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
                return true;
            }
            if ($oSurvey->owner_id == App()->user->id) {
                return true;
            }
            return array_reduce($oSurvey->permissions, function ($coll, $oPermission) {
                if ($oPermission->permission=='surveysecurity' && $oPermission->update_p == 1 && $oPermission->uid == App()->user->id) {
                    return true;
                }
                return $coll;
            }, false);
        });
        return $this->renderPartial('partial.editpermissions', ["oUser" => $oUser, "currentState" => $sCurrentState, "aMySurveys" => $aMySurveys]);
    }

    /**
     * Speichert die geänderten Nutzerberechtigungen
     *
     * Zum Aufruf mittels POST an plugins/direct Event
     * Bsp.: /plugins/direct/plugin/SMKUserManagement/function/userpermissions
     *
     * @param PluginEvent $oEvent
     * @param CHttpRequest $oRequest
     * @return string | JSON
     */
    public function saveuserpermissions($oEvent, $oRequest)
    {
        if (!Permission::model()->hasGlobalPermission('users', 'update')) {
            return Yii::app()->getController()->renderPartial('/admin/usermanager/partial/json', ["data"=>[
                'success' => false,
                'error' => "Keine Berechtigung für diese Aktion"
            ]]);
        }
        $userId = $oRequest->getPost('userid');
        $oUser = User::model()->findByPk($userId);
        $permissionclass = $oRequest->getPost('permissionclass');
        $entity_ids = $oRequest->getPost('entity_ids', []);
        $results = $this->applyPermissionTemplate($oUser, $permissionclass, $entity_ids);
        
        $oUser->modified = date('Y-m-d H:i:s');
        $save = $oUser->save();

        return Yii::app()->getController()->renderPartial('/admin/usermanager/partial/json', ["data"=>[
            'success' => true,
            'html' => $this->renderPartial('partial.permissionsuccess', ['results' => $results], true)
        ]]);
    }

    /**
     * Speichert die geänderten Nutzerberechtigungen (MASSEDIT)
     *
     * Zum Aufruf mittels POST an plugins/direct Event
     * Bsp.: /plugins/direct/plugin/SMKUserManagement/function/batchPermissions
     *
     * @param PluginEvent $oEvent
     * @param CHttpRequest $oRequest
     * @return string
     */
    public function batchPermissions($oEvent, $oRequest)
    {
        if (!Permission::model()->hasGlobalPermission('users', 'update')) {
            return Yii::app()->getController()->renderPartial('/admin/usermanager/partial/json', ["data"=>[
                'success' => false,
                'error' => "Keine Berechtigung für diese Aktion"
            ]]);
        }

        $userIds = json_decode($oRequest->getPost('sItems', "[]"));
        $entity_ids = $oRequest->getPost('entity_ids', []);
        $permissionclass = $oRequest->getPost('permissionclass');

        $results = [];
        foreach ($userIds as $userId) {
            $oUser = User::model()->findByPk($userId);
            $results[] = $this->applyPermissionTemplate($oUser, $permissionclass, $entity_ids);
            $oUser->modified = date('Y-m-d H:i:s');
            $save = $oUser->save();
        }

        // $results = array_reduce($results, function ($coll, $arr) {
        //     return array_merge($coll, $arr);
        // }, []);
        $this->renderPartial('partial.permissionsuccess', ['results' => $results, "noButton" => true]);
    }

    public function batchSendAndResetLoginData($oEvent, $oRequest)
    {
        if (!Permission::model()->hasGlobalPermission('users', 'update')) {
            return Yii::app()->getController()->renderPartial('/admin/usermanager/partial/json', ["data"=>[
                'success' => false,
                'error' => "Keine Berechtigung für diese Aktion"
            ]]);
        }

        $userIds = json_decode($oRequest->getPost('sItems', "[]"));
        $entity_ids = $oRequest->getPost('entity_ids', []);
        $permissionclass = $oRequest->getPost('permissionclass');

        $results = [];
        foreach ($userIds as $userId) {
            $oUser = User::model()->findByPk($userId);
            $result = $this->resetLoginData($oUser, true);
            $oUser->modified = date('Y-m-d H:i:s');
            $result['saved'] = $oUser->save();
            $results[] = $result;
        }

        // $results = array_reduce($results, function ($coll, $arr) {
        //     return array_merge($coll, $arr);
        // }, []);
        $this->renderPartial('partial.success', ['sMessage' => json_encode($results, JSON_PRETTY_PRINT), 'noButton' => true]);
    }
    /**
     * Löscht Benutzer (MASSEDIT)
     *
     * Zum Aufruf mittels POST an plugins/direct Event
     * Bsp.: /plugins/direct/plugin/SMKUserManagement/function/batchPermissions
     *
     * @param PluginEvent $oEvent
     * @param CHttpRequest $oRequest
     * @return string
     */
    public function batchDelete($oEvent, $oRequest)
    {
        $aItems = json_decode($oRequest->getPost('sItems', []));
        $success = [];
        foreach ($aItems as $sItem) {
            $oUser = User::model()->findByPk($sItem);
            $success[$oUser->uid] = $oUser->delete();
        }

        $this->renderPartial('partial.success', ['noButton' => true]);
    }

    /**
     * Zeigt eine Maske an um Gruppenmanager zu erstellen
     *
     * Zum Aufruf mittels GET an plugins/direct Event
     * Bsp.: /plugins/direct/plugin/SMKUserManagement/function/adddummyuser
     *
     * @param PluginEvent $oEvent
     * @param CHttpRequest $oRequest
     * @return string
     */
    public function adddummyuser($oEvent, $oRequest)
    {
        return $this->renderPartial('partial.adddummyuser', []);
    }

    /**
     * Erstellt die neuen Gruppenmanger
     *
     * Zum Aufruf mittels POST an plugins/direct Event
     * Bsp.: /plugins/direct/plugin/SMKUserManagement/function/batchPermissions
     *
     * @param PluginEvent $oEvent
     * @param CHttpRequest $oRequest
     * @return string | JSON
     */
    public function runadddummyuser($oEvent, $oRequest)
    {
        if (!Permission::model()->hasGlobalPermission('users', 'create')) {
            return Yii::app()->getController()->renderPartial('/admin/usermanager/partial/json', ["data"=>[
                'success' => false,
                'error' => "Keine Berechtigung für diese Aktion"
            ]]);
        }
        $times = $oRequest->getParam('times', 5);
        $prefix = $oRequest->getParam('prefix', 'randuser_');
        $email = $oRequest->getParam('email', User::model()->findByPk(App()->user->id)->email);

        $randomUsers = [];

        for (;$times>0;$times--) {
            $name = 'Gruppenmanager_'.$this->getRandomUsername($prefix);
            $password = $this->getRandomPassword();
            $oUser = new User;
            $oUser->users_name = $name;
            $oUser->full_name = $name;
            $oUser->email = $email;
            $oUser->parent_id = App()->user->id;
            $oUser->created = date('Y-m-d H:i:s');
            $oUser->modified = date('Y-m-d H:i:s');
            $oUser->password = password_hash($password, PASSWORD_DEFAULT);
            $save = $oUser->save();
            $randomUsers[] = ['username' => $name, 'password' => $password, 'save' => $save];
            Permission::model()->insertSomeRecords(
                array(
                    'uid' => $oUser->uid,
                    'permission' => getGlobalSetting('defaulttheme'),
                    'entity'=>'template',
                    'read_p' => 1,
                    'entity_id'=>0
                )
            );
            $this->applyCorrectUsergroup($oUser->uid, 'Gruppenmanager');
            Permission::model()->setGlobalPermission($oUser->uid, 'auth_db');
        }
        return Yii::app()->getController()->renderPartial('/admin/usermanager/partial/json', ["data"=>[
            'success' => true,
            'html' => $this->renderPartial('partial.createdrandoms', ['randomUsers'=>$randomUsers, 'filename' => $prefix], true)
        ]]);
    }

    /**
     * Zeigt eine Maske an um Benutzer aus einer CSV zu importieren
     *
     * Zum Aufruf mittels GET an plugins/direct Event
     * Bsp.: /plugins/direct/plugin/SMKUserManagement/function/importuser
     *
     * @param PluginEvent $oEvent
     * @param CHttpRequest $oRequest
     * @return string
     */
    public function importuser($oEvent, $oRequest)
    {
        return $this->renderPartial('partial.importuser', []);
    }
    
    /**
     * Erstellt Benutzer aus einer CSV
     *
     * Zum Aufruf mittels POST an plugins/direct Event
     * Bsp.: /plugins/direct/plugin/SMKUserManagement/function/importcsv
     *
     * @param PluginEvent $oEvent
     * @param CHttpRequest $oRequest
     * @return string
     */
    public function importcsv()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'create')) {
            return Yii::app()->getController()->renderPartial('/admin/usermanager/partial/json', ["data"=>[
                'success' => false,
                'error' => "Keine Berechtigung für diese Aktion"
            ]]);
        }
        $created = [];
        $aNewUsers = SMKUserParser::getDataFromCSV($_FILES);
        foreach ($aNewUsers as $aNewUser) {
            $name = $this->filterSpecials("{$aNewUser['firstname']}.{$aNewUser['name']}");
            $fullname = "{$aNewUser['firstname']} {$aNewUser['name']}";
            $password = $this->getRandomPassword(8);
            if (User::model()->findByAttributes(['users_name' => $name]) !== null) {
                continue;
            }
            $oUser = new User;
            $oUser->users_name = $name;
            $oUser->full_name = $fullname;
            $oUser->email = $aNewUser['email'];
            $oUser->parent_id = App()->user->id;
            $oUser->created = date('Y-m-d H:i:s');
            $oUser->modified = date('Y-m-d H:i:s');
            $oUser->password = password_hash($password, PASSWORD_DEFAULT);
            $save = $oUser->save();
            if($save) {
                $created[] = [
                    'uid' => $oUser->uid,
                    'username' => $name, 
                    'full_name' => $fullname, 
                    'email'=>$aNewUser['email'], 
                    'password' => $password, 
                    'save' => $save
                ];
            }
        }
        return $this->renderPartial('userimported', ['created' => $created], true);
    }

    public function importfromjson()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'create')) {
            return Yii::app()->getController()->renderPartial('/admin/usermanager/partial/json', ["data"=>[
                'success' => false,
                'error' => "Keine Berechtigung für diese Aktion"
            ]]);
        }
        $sJsonUserString = $this->api->getRequest()->getPost('jsonstring', '{}');

        $result = [];

        if($sJsonUserString != '{}') {
            $aJsonUsers = json_decode($sJsonUserString, true);
            $result = ['created' => [], 'updated' => []];
            foreach($aJsonUsers as $aUserData) {
                $storeTo = 'updated';
                $oUser = User::model()->findByAttributes(['email' => $aUserData['email']]);
                if($oUser == null) {
                    $storeTo = 'created';
                    $oUser = new User;
                    $oUser->users_name = $aUserData['username'];
                    $oUser->full_name = $aUserData['full_name'];
                    $oUser->email = $aUserData['email'];
                    $oUser->parent_id = App()->user->id;
                    $oUser->created = date('Y-m-d H:i:s');
                    $oUser->modified = date('Y-m-d H:i:s');
                }
                $oUser->password = password_hash($aUserData['password'], PASSWORD_DEFAULT);
                $save = $oUser->save();
                
                if($save) {
                    $this->applyPermissionTemplate($oUser, 'Befragungsmanager', []);
                }

                $result[$storeTo][] = [
                    'uid' => $oUser->uid,
                    'username' => $oUser->users_name, 
                    'full_name' => $oUser->full_name, 
                    'email'=> $oUser->email, 
                    'password' => $aUserData['password'], 
                    'save' => $save
                ];
            }
        }
        
        return $this->renderPartial('importfromjson', ['result' => $result], true);
    }

    #####

    public function _createNewUser($aUser, $sendMail=true) {
        if (!Permission::model()->hasGlobalPermission('users', 'create')) {
            return Yii::app()->getController()->renderPartial('/admin/usermanager/partial/json', ["data"=>[
                'success' => false,
                'error' => gT("You do not have permissionfor this action.")
            ]]);
        }
        
        $aUser['users_name'] = flattenText($aUser['users_name']);

        if(empty($aUser['users_name'])){
            return Yii::app()->getController()->renderPartial('/admin/usermanager/partial/json', ["data"=>[
                'success' => false,
                'error' => gT("A username was not supplied or the username is invalid.")
            ]]);
        }

        if (User::model()->find("users_name=:users_name", array(':users_name'=>$aUser['users_name']))) {
            return Yii::app()->getController()->renderPartial('/admin/usermanager/partial/json', ["data"=>[
                'success' => false,
                'error' => gT("A user with this username already exists.")
            ]]);
        }

        $event = new PluginEvent('createNewUser');
        $event->set('errorCode', AuthPluginBase::ERROR_NOT_ADDED);
        $event->set('errorMessageTitle', gT("Failed to add user"));
        $event->set('errorMessageBody', gT("Plugin is not active"));
        App()->getPluginManager()->dispatchEvent($event);

        if ($event->get('errorCode') != AuthPluginBase::ERROR_NONE) {
            return Yii::app()->getController()->renderPartial('/admin/usermanager/partial/json', ["data"=>[
                'success' => false,
                'error' => $event->get('errorMessageTitle').'<br/>'.$event->get('errorMessageBody')
            ]]);
        }
        
        $new_user = $aUser['users_name'];
        $iNewUID = $event->get('newUserID');
        $new_pass = $event->get('newPassword');
        $new_email = $event->get('newEmail');
        $new_full_name = $event->get('newFullName');

        // add default template to template rights for user
        Permission::model()->insertSomeRecords(array('uid' => $iNewUID, 'permission' => getGlobalSetting('defaulttheme'), 'entity'=>'template', 'read_p' => 1, 'entity_id'=>0));
        // add default usersettings to the user
        SettingsUser::applyBaseSettings($iNewUID);

        if($sendMail && $event->get('sendMail')) {
            if ($this->_sendAdminRegistrationMail($aUser)) {
                $aReturnArray['message'] = CHtml::tag("p",array(),sprintf(gT("Username : %s - Email : %s."),$new_user,$new_email));
                $aReturnArray['message'] .= CHtml::tag("p",array(),gT("An email with a generated password was sent to the user."));
                $success = true;
                $aReturnArray['header'] = gT("Success");
            } else {
                // has to be sent again or no other way
                $aReturnArray['message'] = CHtml::tag("p",array(),sprintf(gT("Email to %s (%s) failed."),"<strong>".$new_user."</strong>",$new_email));
                $aReturnArray['message'] .= CHtml::tag("p",array('class'=>'alert alert-danger'),$mailer->getError());
                $success = false;
                $aReturnArray['header'] = gT("Warning");
            }
        }
        
        $aReturnArray['newPassword'] = $display_user_password_in_html ? $new_pass : false;

        return Yii::app()->getController()->renderPartial('/admin/usermanager/partial/json', ["data"=>[
            'success' => $success,
            'html' => Yii::app()->getController()->renderPartial('/admin/usermanager/partial/success', $aReturnArray, true)
        ]]);

    }

    /**
     * private method to store a change in a user
     * return json to return to js frontend
     *
     * @param array $aUser
     * @param integer $userid
     * @return string
     */
    public function _editUser($aUser, $userid) {

        $oUser = User::model()->findByPk($userid);
        
        $rawPassword = $aUser['password'];
        $display_user_password_in_html = Yii::app()->getConfig("display_user_password_in_html");

        $aUser['password'] = password_hash($rawPassword, PASSWORD_DEFAULT);
        $oUser->setAttributes($aUser);
        $oUser->modified = date('Y-m-d H:i:s');
        
        $save = $oUser->save();
        
        $aReturnArray = $display_user_password_in_html ? ['newPassword' => $rawPassword] : [];

        if ($save) {
            return Yii::app()->getController()->renderPartial('/admin/usermanager/partial/json', ["data"=>[
                'success' => true,
                'html' => Yii::app()->getController()->renderPartial('/admin/usermanager/partial/success', $aReturnArray, true)
            ]]);
        }

        return Yii::app()->getController()->renderPartial('/admin/usermanager/partial/json', ["data"=>[
            'success' => false,
            'error' => print_r($oUser->getErrors(), true)
        ]]);
    }

    /**
     * Send the registration email to a new survey administrator
     * @TODO: make this user configurable by TWIG, or similar
     *
     * @param array $aUser
     * @return boolean if send is successfull
     */
    public function _sendAdminRegistrationMail($aUser) {
         // send Mail
        /* @todo : must move this to Plugin (or sendMail as boolean in plugin event) */
        $body = sprintf(gT("Hello %s,"), $aUser['full_name'])."<br /><br />\n";
        $body .= sprintf(gT("this is an automated email to notify that a user has been created for you on the site '%s'."), Yii::app()->getConfig("sitename"))."<br /><br />\n";
        $body .= gT("You can use now the following credentials to log into the site:")."<br />\n";
        $body .= gT("Username").": ".htmlspecialchars($aUser['users_name'])."<br />\n";
        // authent is not delegated to web server or LDAP server
        if (Yii::app()->getConfig("auth_webserver") === false && Permission::model()->hasGlobalPermission('auth_db', 'read', $aUser['uid'])) {
            // send password (if authorized by config)
            if (Yii::app()->getConfig("display_user_password_in_email") === true) {
                $body .= gT("Password").": ".$aUser['rawPassword']."<br />\n";
            } else {
                $body .= gT("Password").": ".gT("Please contact your LimeSurvey administrator for your password.")."<br />\n";
            }
        }

        $body .= "<a href='".$this->getController()->createAbsoluteUrl("/admin")."'>".gT("Click here to log in.")."</a><br /><br />\n";
        $body .= sprintf(gT('If you have any questions regarding this mail please do not hesitate to contact the site administrator at %s. Thank you!'), Yii::app()->getConfig("siteadminemail"))."<br />\n";

        $mailer = new LimeMailer;
        $mailer->addAddress($new_email,$new_user);
        $mailer->Subject = sprintf(gT("User registration at '%s'", "unescaped"), Yii::app()->getConfig("sitename"));;
        $mailer->Body = $body;
        $mailer->isHtml(true);
        $mailer->emailType = "addadminuser";
        return $mailer->sendMessage(); 
    }

    /**
     * Setzt einen benutzer in die entsprechende Benutzergruppe
     *
     * @param integer $uid
     * @param string $mGroupName
     * @return void
     */
    private function applyCorrectUsergroup($uid, $mGroupName)
    {
        if (!is_array($mGroupName)) {
            $mGroupName = [$mGroupName];
        }
        foreach ($mGroupName as $sGroupName) {
            $oUserGroupGroupmanager = UserGroup::model()->findByAttributes(['name' => $sGroupName]);
            if ($oUserGroupGroupmanager != null) {
                if (!$oUserGroupGroupmanager->hasUser($uid)) {
                    $oUGMap = new UserInGroup();
                    $oUGMap->uid = $uid;
                    $oUGMap->ugid = $oUserGroupGroupmanager->ugid;
                    $oUGMap->save();
                }
            }
        }
    }

    /**
     * Filtert Umlaute zu Umschreibungen
     *
     * @param string $in String der Umgewandelt werden soll
     * @return string
     */
    private function filterSpecials($in)
    {
        $was = array("ä", "ö", "ü", "Ä", "Ö", "Ü", "ß","é", " ");
        $wie = array("ae", "oe", "ue", "Ae", "Oe", "Ue", "ss",'e', "");
        return str_replace($was, $wie, $in);
    }

    /**
     * Erstellt einen zufälligen String aus einem hash
     *
     * @return string
     */
    private function getRandomString()
    {
        if (is_callable('openssl_random_pseudo_bytes')) {
            $uiq = openssl_random_pseudo_bytes(128);
        } else {
            $uiq = decbin(rand(1000000, 9999999)*(rand(100, 999).rand(100, 999).rand(100, 999).rand(100, 999)));
        }
        return hash('sha256', bin2hex($uiq));
    }

    /**
     * Erstellt einen zufälligen Benutzernamen mit Präfix
     *
     * Prüft ob der Name einzigartig ist
     *
     * @param string $prefix der Präfix
     * @return string
     */
    private function getRandomUsername($prefix)
    {
        do {
            $rand = $this->getRandomString();
            $username = $prefix.'_'.substr($rand, rand(0, strlen($rand)-6), 4);
            $oUser = User::model()->findByAttributes(['users_name' => $username]);
        } while ($oUser != null);
        return $username;
    }

    /**
     * Erstellt ein zufälliges Passwort
     *
     * @param integer $length Länge des Passworts
     * @return string
     */
    private function getRandomPassword($length = 5)
    {
        $pw = $this->getRandomString();
        return substr($pw, rand(0, strlen($pw)-($length+3)), $length);
    }

    /**
     * Prüft den derzeigigen Berechtigungsstatus
     *
     * @param User $oUser
     * @return string
     */
    private function parseCurrentState($oUser)
    {
        if (Permission::model()->hasGlobalPermission('superadmin', 'read', $oUser->uid)) {
            return 'Administrator';
        }
        
        if (Permission::model()->hasGlobalPermission('users', 'create', $oUser->uid)) {
            return 'Befragungsmanager';
        }

        if (Permission::model()->hasGlobalPermission('surveys', 'export', $oUser->uid)) {
            return 'Wissenschaftler';
        }
        return 'Gruppenmanager';
    }

    /**
     * Legt eine Berechtigungsvporlage für einen Benutzer fest
     *
     * @param User $oUser
     * @param string $permissionclass
     * @param array $entity_ids
     * @return array
     */
    private function applyPermissionTemplate($oUser, $permissionclass, $entity_ids=[])
    {
        if ($permissionclass == 'Gruppenmanager' && empty($entity_ids)) {
            return [
                "success" => false,
                "error" => "Keine Umfrage für Berechtigung ausgewählt"
            ];
        }
        $oCriteria = new CDbCriteria();
        $oCriteria->compare('uid', $oUser->uid);
        //Kill all Permissions
        $aPermissionsCurrently = Permission::model()->deleteAll($oCriteria);
        
        //Allow Login again
        Permission::model()->setGlobalPermission($oUser->uid, 'auth_db');
        
        $result = false;
        if (in_array($permissionclass, ['Befragungsmanager', 'Wissenschaftler', 'combo'])) {
            $result = $this->applyGlobalPermissionTemplate($oUser, $permissionclass);
            $this->applyCorrectUsergroup($oUser->uid, ($permissionclass=='combo' ? ['Befragungsmanager', 'Wissenschaftler'] : [$permissionclass]));
        } elseif ($permissionclass == 'Gruppenmanager') {
            $result = $this->applySurveyPermissionTemplate($oUser, $permissionclass, $entity_ids);
            $this->applyCorrectUsergroup($oUser->uid, [$permissionclass]);
        }
        return $result;
    }

    /**
     * Setzt globale Berechtigungen fü einen Nutzer
     *
     * @param User $oUser
     * @param string $permissionclass
     * @return array
     */
    private function applyGlobalPermissionTemplate($oUser, $permissionclass)
    {
        $permissionTemplate = SMKPermissionTemplates::getPermissionTemplateBlock($permissionclass, $oUser->uid);
        $check = [];
        foreach ($permissionTemplate as $permission) {
            $oPermission = new Permission();
            array_walk($permission, function ($val, $key) use (&$oPermission) {
                $oPermission->$key = $val;
            });
            $check[$permission['permission']] = $oPermission->save(false);
        }
        return $check;
    }

    /**
     * Setzt Umfragespezifische Berechtigungen füe inen Nutzer
     *
     * @param User $oUser
     * @param string $permissionclass
     * @param array $entity_ids
     * @return array
     */
    private function applySurveyPermissionTemplate($oUser, $permissionclass, $entity_ids)
    {
        $permissionTemplate = SMKPermissionTemplates::getPermissionTemplateBlock($permissionclass, $oUser->uid);
        $check = [];
        foreach ($permissionTemplate as $permission) {
            array_walk($entity_ids, function ($entity_id) use ($permission, &$check) {
                $oPermission = new Permission();
                $permission['entity_id'] = $entity_id;
                array_walk($permission, function ($val, $key) use (&$oPermission) {
                    $oPermission->$key = $val;
                });
                $check[$permission['permission'].'/'.$entity_id] = $oPermission->save(false);
            });
        }
        return $check;
    }

    public function resetLoginData(&$oUser, $sendMail = false)
    {
        $newPassword = $this->getRandomPassword(8);
        $oUser->setPassword($newPassword);
        $success = true;
        if ($sendMail == true) {
            $to = $oUser->full_name.' <'.$oUser->email.'>';
            $from = Yii::app()->getConfig("siteadminname")." <".Yii::app()->getConfig("siteadminemail").">";
            $body = $this->renderPartial('partial/usernotificationemail', ['username' => $oUser->users_name, 'password' => $newPassword], true);
            $success = SendEmailMessage($body, "[Bleib dran] Ihre Zugangsdaten", $to, $from, Yii::app()->getConfig("sitename"), true, Yii::app()->getConfig("siteadminbounce"));
        }
        return [
            'success' => $success, 'uid' =>$oUser->uid, 'username' => $oUser->users_name, 'password' => $newPassword
        ];
    }
}
