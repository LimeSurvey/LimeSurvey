<?php if (!defined('BASEPATH')) {
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
*
*/

/**
* Usergroups
*
* @package LimeSurvey
* @author
* @copyright 2011
* @access public
*/

class Usergroups extends Survey_Common_Action
{

    /**
     * Usergroups::mail()
     * Function responsible to send an e-mail to a user group.
     * @param mixed $ugid
     * @return void
     */
    public function mail($ugid)
    {

        $ugid = sanitize_int($ugid);


        $action = Yii::app()->request->getPost("action");

        if ($action == "mailsendusergroup") {

            // user must be in user group or superadmin
            $result = UserInGroup::model()->findAllByPk(array('ugid' => $ugid, 'uid' => Yii::app()->session['loginID']));
            if (count($result) > 0 || Permission::model()->hasGlobalPermission('superadmin', 'read')) {
                $criteria = new CDbCriteria;
                $criteria->compare('ugid', $ugid)->addNotInCondition('users.uid', array(Yii::app()->session['loginID']));
                $eguresult = UserInGroup::model()->with('users')->findAll($criteria);
                //die('me');
                $to = array();

                foreach ($eguresult as $egurow) {
                    $to[] = \CHtml::encode($egurow->users->users_name).' <'.$egurow->users->email.'>';
                }
                $from_user_result = User::model()->findByPk(Yii::app()->session['loginID']);
                $from_user_row = $from_user_result;

                if ($from_user_row->full_name) {
                    $from = $from_user_row->full_name;
                    $from .= ' <';
                    $from .= $from_user_row->email.'> ';
                } else {
                    $from = $from_user_row->users_name.' <'.$from_user_row->email.'> ';
                }

                $body = $_POST['body'];
                $subject = $_POST['subject'];

                if (isset($_POST['copymail']) && $_POST['copymail'] == 1) {
                    $to[] = $from;
                }
                $body = str_replace("\n.", "\n..", $body);
                $body = wordwrap($body, 70);

                if (SendEmailMessage($body, $subject, $to, $from, '')) {
                    list($aViewUrls, $aData) = $this->index($ugid, array("type" => "success", "message" => gT("Message(s) sent successfully!")));
                } else {
                    global $maildebug;
                    global $debug;
                    global $maildebugbody;
                    //$maildebug = (isset($maildebug)) ? $maildebug : "Their was a unknown error in the mailing part :)";
                    //$debug = (isset($debug)) ? $debug : 9;
                    //$maildebugbody = (isset($maildebugbody)) ? $maildebugbody : 'an unknown error accourd';
                    $headercfg["type"] = "warning";
                    $headercfg["message"] = sprintf(gT("Email to %s failed. Error Message:"), $to)." ".$maildebug;
                    list($aViewUrls, $aData) = $this->index($ugid, $headercfg);
                }
            } else {
                die();
            }

        } else {
            $where = array('and', 'a.ugid ='.$ugid, 'uid ='.Yii::app()->session['loginID']);
            $join = array('where' => "{{user_in_groups}} AS b", 'on' => 'a.ugid = b.ugid');
            $result = UserGroup::model()->join(array('a.ugid', 'a.name', 'a.owner_id', 'b.uid'), "{{user_groups}} AS a", $where, $join, 'name');

            $crow = $result;
            $aData['ugid'] = $ugid;

            $aViewUrls = 'mailUserGroup_view';
        }

        $aData['usergroupbar']['closebutton']['url'] = Yii::app()->request->getUrlReferrer(App()->createUrl('admin/usergroups/sa/index')); // Close button, UrlReferrer

        $this->_renderWrappedTemplate('usergroup', $aViewUrls, $aData);
    }

    /**
     * Usergroups::delete()
     * Function responsible to delete a user group.
     * @return void
     */
    public function delete()
    {

        $aViewUrls = array();
        $aData = array();

        if (Permission::model()->hasGlobalPermission('usergroups', 'delete')) {
            $ugid = Yii::app()->request->getPost("ugid");
            if (!empty($ugid) && ($ugid > -1)) {
                $userGroup = UserGroup::model()->requestEditGroup($ugid, Yii::app()->session["loginID"]);
                if (!empty($userGroup)) {
                    if ($userGroup->delete()) {
                        Yii::app()->user->setFlash("success", gT("Successfully deleted user group."));
                    } else {
                        Yii::app()->user->setFlash("notice", gT("Could not delete user group."));
                    }
                }
            } else {
                Yii::app()->user->setFlash("error", gT("Could not delete user group. No group selected."));
            }
        }

        $this->getController()->redirect($this->getController()->createUrl('/admin/usergroups/sa/view'));
    }


    public function add()
    {


        $action = (isset($_POST['action'])) ? $_POST['action'] : '';
        $aData = array();

        if (Permission::model()->hasGlobalPermission('usergroups', 'create')) {

            if ($action == "usergroupindb") {
                $db_group_name = flattenText($_POST['group_name'], false, true, 'UTF-8', true);
                $db_group_description = $_POST['group_description'];

                if (isset($db_group_name) && strlen($db_group_name) > 0) {
                    if (strlen($db_group_name) > 21) {
                        list($aViewUrls, $aData) = $this->index(false, array("type" => "warning", "message" => gT("Failed to add group! Group name length more than 20 characters.")));
                        Yii::app()->user->setFlash('error', gT("Failed to add group! Group name length more than 20 characters."));
                    } elseif (UserGroup::model()->find("name=:groupName", array(':groupName'=>$db_group_name))) {
                        list($aViewUrls, $aData) = $this->index(false, array("type" => "warning", "message" => gT("Failed to add group! Group already exists.")));
                        Yii::app()->user->setFlash('error', gT("Failed to add group! Group already exists."));
                    } else {
                        $ugid = UserGroup::model()->addGroup($db_group_name, $db_group_description);
                        Yii::app()->session['flashmessage'] = gT("User group successfully added!");
                        list($aViewUrls, $aData) = $this->index($ugid, true);
                        $this->getController()->redirect(array('admin/usergroups/sa/view/ugid/'.$ugid));
                    }

                    $this->getController()->redirect(array('admin/usergroups'));

                } else {
                    list($aViewUrls, $aData) = $this->index(false, array("type" => "warning", "message" => gT("Failed to add group! Group Name was not supplied.")));
                }
            } else {
                $aViewUrls = 'addUserGroup_view';
            }
        }
        $aData['usergroupbar']['savebutton']['form'] = 'usergroupform';
        $aData['usergroupbar']['savebutton']['text'] = gT('Save');
        $aData['usergroupbar']['closebutton']['url'] = Yii::app()->request->getUrlReferrer(App()->createUrl('admin/usergroups/sa/index')); // Close button, urlReferrer
        $aData['usergroupbar']['add'] = 'admin/usergroups';
        $this->_renderWrappedTemplate('usergroup', $aViewUrls, $aData);
    }

    /**
     * Usergroups::edit()
     * Load edit user group screen.
     * @param mixed $ugid
     * @return void
     */
    function edit($ugid)
    {
        $ugid = (int) $ugid;

        $action = (isset($_POST['action'])) ? $_POST['action'] : '';
        if (Permission::model()->hasGlobalPermission('usergroups', 'update')) {
            if ($action == "editusergroupindb") {

                $ugid = (int) $_POST['ugid'];

                $db_name = $_POST['name'];
                $db_description = $_POST['description'];
                if (UserGroup::model()->updateGroup($db_name, $db_description, $ugid)) {
                    Yii::app()->session['flashmessage'] = gT("User group successfully saved!");
                    $aData['ugid'] = $ugid;
                    $this->getController()->redirect(array('admin/usergroups/sa/view/ugid/'.$ugid));
                } else {
                    Yii::app()->session['flashmessage'] = gT("Failed to edit user group!");
                    $this->getController()->redirect(array('admin/usergroups/sa/edit/ugid/'.$ugid));
                }

            } else {
                $result = UserGroup::model()->requestEditGroup($ugid, Yii::app()->session['loginID']);
                $aData['esrow'] = $result;
                $aData['ugid'] = $result->ugid;
                $aViewUrls = 'editUserGroup_view';
            }
        }

        $aData['usergroupbar']['closebutton']['url'] = Yii::app()->request->getUrlReferrer(App()->createUrl('admin/usergroups/sa/index')); // Close button, urlReferrer
        $aData['usergroupbar']['savebutton']['form'] = 'usergroupform';
        $aData['usergroupbar']['savebutton']['text'] = gT("Update user group");

        $this->_renderWrappedTemplate('usergroup', 'editUserGroup_view', $aData);
    }


    /**
     * Load viewing of a user group screen.
     * @param bool $ugid
     * @param array|bool $header (type=success, warning)(message=localized message)
     * @return array
     */
    public function index($ugid = false, $header = false)
    {
        if (!Permission::model()->hasGlobalPermission('usergroups', 'read')) {
            Yii::app()->session['flashmessage'] = gT('Access denied!');
            $this->getController()->redirect(App()->createUrl("/admin"));
        }
        if ($ugid != false) {
                    $ugid = (int) $ugid;
        }

        if (!empty($header)) {
                    $aData['headercfg'] = $header;
        } else {
                    $aData = array();
        }

        $aViewUrls = array();
        $aData['ugid'] = $ugid;
        $aData['imageurl'] = Yii::app()->getConfig("adminimageurl");


        if (Yii::app()->session['loginID']) {

            if ($ugid) {
                $ugid = sanitize_int($ugid);
                $aData["usergroupid"] = $ugid;
                $result = UserGroup::model()->requestViewGroup($ugid, Yii::app()->session["loginID"]);
                $crow = $result[0];
                if ($result) {
                    $aData["groupfound"] = true;
                    $aData["groupname"] = $crow['name'];
                    if (!empty($crow['description'])) {
                                            $aData["usergroupdescription"] = $crow['description'];
                    } else {
                                            $aData["usergroupdescription"] = "";
                    }
                }
                //$this->user_in_groups_model = new User_in_groups;
                $eguquery = "SELECT * FROM {{user_in_groups}} AS a LEFT JOIN {{user_groups}} ug on a.ugid=ug.ugid INNER JOIN {{users}} AS b ON a.uid = b.uid WHERE a.ugid = ".$ugid." ORDER BY b.users_name";
                $eguresult = dbExecuteAssoc($eguquery);
                $aUserInGroupsResult = $eguresult->readAll();
                $sCondition2 = "ugid = :ugid";
                $sParams2 = [':ugid'=>$ugid];
                if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
                    $sCondition2 .= " AND owner_id = :owner_id";
                    $sParams2[':owner_id'] = Yii::app()->session['loginID'];
                }
                
                $row2 = Yii::app()->db->createCommand()
                ->select('ugid')
                ->from('{{user_groups}}')
                ->where($sCondition2, $sParams2)
                ->limit(1)
                ->queryRow();
                $row = 1;
                $userloop = array();
                $bgcc = "oddrow";
                foreach ($aUserInGroupsResult as $egurow) {
                    // @todo: Move the zebra striping to view
                    if ($bgcc == "evenrow") {
                        $bgcc = "oddrow";
                    } else {
                        $bgcc = "evenrow";
                    }
                    $userloop[$row]["userid"] = $egurow['uid'];

                    //	output users
                    $userloop[$row]["rowclass"] = $bgcc;                                                                                       
                    if (Permission::model()->hasGlobalPermission('usergroups', 'update') && $egurow['owner_id']==Yii::app()->session['loginID'])  {
                        $userloop[$row]["displayactions"] = true;
                    } else {
                        $userloop[$row]["displayactions"] = false;
                    }

                    $userloop[$row]["username"] = $egurow['users_name'];
                    $userloop[$row]["email"] = $egurow['email'];

                    $row++;
                }
                $aData["userloop"] = $userloop;
                if ($row2 !== false) {
                    $aData["useradddialog"] = true;
                    $aData["useraddusers"] = getGroupUserList($ugid, 'optionlist');
                    $aData["useraddurl"] = "";
                }
                $aViewUrls[] = 'viewUserGroup_view';
            } else {
                //show listing
                $aViewUrls['usergroups_view'][] = array();
                $aData['model'] = UserGroup::model();
            }


        }

        if ($ugid == false) {
            $aData['usergroupbar']['returnbutton']['url'] = 'admin/index';
            $aData['usergroupbar']['returnbutton']['text'] = gT('Return to admin home');
        } else {
            $aData['usergroupbar']['edit'] = true;
            $aData['usergroupbar']['closebutton']['url'] = Yii::app()->createUrl('admin/usergroups/sa/view'); // Close button
        }

        if (isset($_GET['pageSize'])) {
            Yii::app()->user->setState('pageSize', (int) $_GET['pageSize']);
        }

        if (!empty($header)) {
            return array($aViewUrls, $aData);
        } else {
            $this->_renderWrappedTemplate('usergroup', $aViewUrls, $aData);
        }
    }

    /**
     * @todo Doc
     */
    function user($ugid, $action = 'add')
    {
        if (!Permission::model()->hasGlobalPermission('usergroups', 'read') || !in_array($action, array('add', 'remove'))) {
            die('access denied');
        }
        $uid = (int) Yii::app()->request->getPost('uid');
        if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $group = UserGroup::model()->findByAttributes(array('ugid' => $ugid));
        } else {
            $group = UserGroup::model()->findByAttributes(array('ugid' => $ugid, 'owner_id' => Yii::app()->session['loginID']));
        }
        if (empty($group)) {
            list($aViewUrls, $aData) = $this->index(0, array('type' => 'warning', 'message' => gT('Failed.').'<br />'.gT('Group not found.')));
        } else {
            if ($uid > 0 && User::model()->findByPk($uid)) {
                if ($group->owner_id == $uid) {
                    list($aViewUrls, $aData) = $this->index($ugid, array('type' => 'warning', 'message' => gT('Failed.').'<br />'.gT('You can not add or remove the group owner from the group.')));
                } else {
                    $user_in_group = UserInGroup::model()->findByPk(array('ugid' => $ugid, 'uid' => $uid));
                    $sFlashType = ''; $sFlashMessage = '';
                    switch ($action) {
                        case 'add' :
                            if (empty($user_in_group) && UserInGroup::model()->insertRecords(array('ugid' => $ugid, 'uid' => $uid))) {
                                $sFlashType = 'success'; $sFlashMessage = gT('User added.');
                            } else {
                                $sFlashType = 'error'; $sFlashMessage = gT('Failed to add user.').'<br />'.gT('User already exists in the group.');
                            }
                            break;
                        case 'remove' :
                            if (!empty($user_in_group) && UserInGroup::model()->deleteByPk(array('ugid' => $ugid, 'uid' => $uid))) {
                                $sFlashType = 'success'; $sFlashMessage = gT('User removed.');
                            } else {
                                $sFlashType = 'error'; $sFlashMessage = gT('Failed to remove user.').'<br />'.gT('User does not exist in the group.');
                            }
                            break;
                    }
                    if (!empty($sFlashType) && !empty($sFlashMessage)) { 
                        Yii::app()->user->setFlash($sFlashType, $sFlashMessage);
                    }
                    $this->getController()->redirect(array('admin/usergroups/sa/view/ugid/'.$ugid));
                }
            } else {
                list($aViewUrls, $aData) = $this->index($ugid, array('type' => 'warning', 'message' => gT('Failed.').'<br />'.gT('User not found.')));
            }
        }
        $this->_renderWrappedTemplate('usergroup', $aViewUrls, $aData);
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'usergroup', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        App()->getClientScript()->registerPackage('jquery-tablesorter');
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts').'users.js');
        $aData['display']['menu_bars']['user_group'] = true;

        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }
}
