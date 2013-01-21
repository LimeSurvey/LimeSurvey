<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
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
 *	$Id$
 */

/**
 * Usergroups
 *
 * @package LimeSurvey
 * @author
 * @copyright 2011
 * @version $Id$
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
        $clang = Yii::app()->lang;

        $action = Yii::app()->request->getPost("action");

        if ($action == "mailsendusergroup") {

            // user must be in user group or superadmin
            $result = User_in_groups::model()->findAllByPk(array('ugid' => $ugid, 'uid' => Yii::app()->session['loginID']));
            if (count($result) > 0 || Yii::app()->session['USER_RIGHT_SUPERADMIN'])
            {
                $criteria = new CDbCriteria;
                $criteria->compare('ugid',$ugid)->addNotInCondition('users.uid',array(Yii::app()->session['loginID']));
                $eguresult = User_in_groups::model()->with('users')->findAll($criteria);
                //die('me');
                $to = '';

                foreach ($eguresult as $egurow)
                {
                    $to .= $egurow->users->users_name . ' <' . $egurow->users->email . '>' . '; ';
                }

                $to = substr($to, 0, -2);

                $from_user_result = User::model()->findByPk(Yii::app()->session['loginID']);
                $from_user_row = $from_user_result;

                if ($from_user_row->full_name) {
                    $from = $from_user_row->full_name;
                    $from .= ' <';
                    $from .= $from_user_row->email . '> ';
                }
                else
                {
                    $from = $from_user_row->users_name . ' <' . $from_user_row->email . '> ';
                }

                $body = $_POST['body'];
                $subject = $_POST['subject'];

                if (isset($_POST['copymail']) && $_POST['copymail'] == 1) {
                    if ($to == "")
                        $to = $from;
                    else
                        $to .= ", " . $from;
                }
                $body = str_replace("\n.", "\n..", $body);
                $body = wordwrap($body, 70);


                //echo $body . '-'.$subject .'-'.'<pre>'.htmlspecialchars($to).'</pre>'.'-'.$from;
                if (SendEmailMessage($body, $subject, $to, $from, '')) {
                    list($aViewUrls, $aData) = $this->index($ugid, array("type" => "success", "message" => "Message(s) sent successfully!"));
                }
                else
                {
                    global $maildebug;
                    global $debug;
                    global $maildebugbody;
                    //$maildebug = (isset($maildebug)) ? $maildebug : "Their was a unknown error in the mailing part :)";
                    //$debug = (isset($debug)) ? $debug : 9;
                    //$maildebugbody = (isset($maildebugbody)) ? $maildebugbody : 'an unknown error accourd';
                    $headercfg["type"] = "warning";
                    $headercfg["message"] = sprintf($clang->gT("Email to %s failed. Error Message:"), $to) . " " . $maildebug;
                    list($aViewUrls, $aData) = $this->index($ugid, $headercfg);
                }
            }
            else
            {
                die();
            }

        }
        else
        {
            $where = array('and', 'a.ugid =' . $ugid, 'uid =' . Yii::app()->session['loginID']);
            $join = array('where' => "{{user_in_groups}} AS b", 'on' => 'a.ugid = b.ugid');
            $result = User_groups::model()->join(array('a.ugid', 'a.name', 'a.owner_id', 'b.uid'), "{{user_groups}} AS a", $where, $join, 'name');

            $crow = $result;
            $aData['ugid'] = $ugid;

            $aViewUrls = 'mailUserGroup_view';
        }

        $this->_renderWrappedTemplate('usergroup', $aViewUrls, $aData);
    }

    /**
     * Usergroups::delete()
     * Function responsible to delete a user group.
     * @return void
     */
    public function delete($ugid)
    {
        $clang = Yii::app()->lang;
        $aViewUrls = array();
        $aData = array();

        if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1) {

            if (!empty($ugid) && ($ugid > -1)) {
                $result = User_groups::model()->requestEditGroup($ugid, Yii::app()->session["loginID"]);
                if ($result->count() > 0) {  // OK - AR count
                    $delquery_result = User_groups::model()->deleteGroup($ugid, Yii::app()->session["loginID"]);

                    if ($delquery_result) //Checked)
                    {
                        list($aViewUrls, $aData) = $this->index(false, array("type" => "success", "message" => $clang->gT("Success!")));
                    }
                    else
                    {
                        list($aViewUrls, $aData) = $this->index(false, array("type" => "warning", "message" => $clang->gT("Could not delete user group.")));
                    }
                }
            }
            else
            {
                list($aViewUrls, $aData) = $this->index($ugid, array("type" => "warning", "message" => $clang->gT("Could not delete user group. No group selected.")));
            }
        }

        $this->_renderWrappedTemplate('usergroup', $aViewUrls, $aData);
    }


    public function add()
    {
        $clang = Yii::app()->lang;

        $action = (isset($_POST['action'])) ? $_POST['action'] : '';
        $aData = array();

        if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1) {

            if ($action == "usergroupindb") {

                if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1) {
                    $db_group_name = $_POST['group_name'];
                    $db_group_description = $_POST['group_description'];

                    if (isset($db_group_name) && strlen($db_group_name) > 0) {
                        if (strlen($db_group_name) > 21) {
                            list($aViewUrls, $aData) = $this->index(false, array("type" => "warning", "message" => $clang->gT("Failed to add group! Group name length more than 20 characters.")));
                        }
                        elseif (User_groups::model()->find("name='$db_group_name'")) {
                            list($aViewUrls, $aData) = $this->index(false, array("type" => "warning", "message" => $clang->gT("Failed to add group! Group already exists.")));
                        }
                        else
                        {
                            $ugid = User_groups::model()->addGroup($db_group_name, $db_group_description);
                            Yii::app()->session['flashmessage'] = $clang->gT("User group successfully added!");
                            list($aViewUrls, $aData) = $this->index($ugid, true);
                        }
                    }
                    else
                    {
                        list($aViewUrls, $aData) = $this->index(false, array("type" => "warning", "message" => $clang->gT("Failed to add group! Group Name was not supplied.")));
                    }
                }
            }
            else
            {
                $aViewUrls = 'addUserGroup_view';
            }
        }

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
        $ugid = (int)$ugid;
        $clang = Yii::app()->lang;
        $action = (isset($_POST['action'])) ? $_POST['action'] : '';
        if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1) {
            if ($action == "editusergroupindb") {

                $ugid = (int)$_POST['ugid'];

                $db_name = $_POST['name'];
                $db_description = $_POST['description'];
                if (User_groups::model()->updateGroup($db_name, $db_description, $ugid)) {
                    Yii::app()->session['flashmessage'] = $clang->gT("User group successfully saved!");
					$aData['ugid'] = $ugid;
                    Yii::app()->request->redirect($this->getController()->createUrl('admin/usergroups/sa/view/ugid/'.$ugid));
                }
                else
                {
                    Yii::app()->session['flashmessage'] = $clang->gT("Failed to edit user group!");
                    Yii::app()->request->redirect($this->getController()->createUrl('admin/usergroups/sa/edit/ugid/'.$ugid));
                }

            }
            else
            {
                $result = User_groups::model()->requestEditGroup($ugid, Yii::app()->session['loginID']);
                $aData['esrow'] = $result;
                $aData['ugid'] = $result->ugid;
                $aViewUrls = 'editUserGroup_view';
            }
        }

        $this->_renderWrappedTemplate('usergroup', 'editUserGroup_view', $aData);
    }


    /**
     * Load viewing of a user group screen.
     * @param bool $ugid
     * @param array|bool $header (type=success, warning)(message=localized message)
     * @return void
     */
    public function index($ugid = false, $header = false)
    {
        if ($ugid != false)
            $ugid = (int)$ugid;

        if (!empty($header))
            $aData['headercfg'] = $header;
        else
            $aData = array();

        $aViewUrls = array();
        $aData['ugid'] = $ugid;
        $aData['imageurl'] = Yii::app()->getConfig("adminimageurl");
        $clang = Yii::app()->lang;

        if (Yii::app()->session['loginID']) {

            if ($ugid) {
                $ugid = sanitize_int($ugid);
                $aData["usergroupid"] = $ugid;
                $result = User_groups::model()->requestViewGroup($ugid, Yii::app()->session["loginID"]);
                $crow = $result[0];
                if ($result) {
                    $aData["groupfound"] = true;
                    $aData["groupname"] = $crow['name'];
                    if (!empty($crow['description']))
                        $aData["usergroupdescription"] = $crow['description'];
                    else
                        $aData["usergroupdescription"] = "";
                }
                //$this->user_in_groups_model = new User_in_groups;
                $eguquery = "SELECT * FROM {{user_in_groups}} AS a INNER JOIN {{users}} AS b ON a.uid = b.uid WHERE ugid = " . $ugid . " ORDER BY b.users_name";
                $eguresult = dbExecuteAssoc($eguquery);
                $aUserInGroupsResult = $eguresult->readAll();
                $query2 = "SELECT ugid FROM {{user_groups}} WHERE ugid = " . $ugid . " AND owner_id = " . Yii::app()->session['loginID'];
                $result2 = dbSelectLimitAssoc($query2, 1);
                $row2 = $result2->readAll();
                $row = 1;
                $userloop = array();
                $bgcc = "oddrow";
                foreach ($aUserInGroupsResult as $egurow)
                {
                    if ($bgcc == "evenrow") {
                        $bgcc = "oddrow";
                    } else {
                        $bgcc = "evenrow";
                    }
                    $userloop[$row]["userid"] = $egurow['uid'];

                    //	output users
                    $userloop[$row]["rowclass"] = $bgcc;
                    if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1) {
                        $userloop[$row]["displayactions"] = true;
                    } else {
                        $userloop[$row]["displayactions"] = false;
                    }

                    $userloop[$row]["username"] = $egurow['users_name'];
                    $userloop[$row]["email"] = $egurow['email'];
                 
                    $row++;
                }
                $aData["userloop"] = $userloop;
                if (isset($row2[0]['ugid'])) {
                    $aData["useradddialog"] = true;
                    $aData["useraddusers"] = getGroupUserList($ugid, 'optionlist');
                    $aData["useraddurl"] = "";
                }
            }

            $aViewUrls[] = 'viewUserGroup_view';
        }

        if (!empty($header))
        {
            return array($aViewUrls, $aData);
        }
        else
        {
            $this->_renderWrappedTemplate('usergroup', $aViewUrls, $aData);
        }
    }

    function user($ugid, $action = 'add')
    {
        if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] != true || !in_array($action, array('add', 'remove')))
        {
            die('access denied');
        }

        $clang = Yii::app()->lang;
        $uid = (int) Yii::app()->request->getPost('uid');

        $group = User_groups::model()->findByAttributes(array('ugid' => $ugid, 'owner_id' => Yii::app()->session['loginID']));

        if (empty($group))
        {
            list($aViewUrls, $aData) = $this->index(0, array('type' => 'warning', 'message' => $clang->gT('Failed.') . '<br />' . $clang->gT('Group not found.')));
        }
        else
        {
            if ($uid > 0 && User::model()->findByPk($uid))
            {
                if ($group->owner_id == $uid)
                {
                    list($aViewUrls, $aData) = $this->index($ugid, array('type' => 'warning', 'message' => $clang->gT('Failed.') . '<br />' . $clang->gT('You can not add or remove the group owner from the group.')));
                }

                $user_in_group = User_in_groups::model()->findByPk(array('ugid' => $ugid, 'uid' => $uid));

                switch ($action)
                {
                    case 'add' :
                        if (empty($user_in_group) && User_in_groups::model()->insertRecords(array('ugid' => $ugid, 'uid' => $uid)))
                        {
                            list($aViewUrls, $aData) = $this->index($ugid, array('type' => 'success', 'message' => $clang->gT('User added.')));
                        }
                        else
                        {
                            list($aViewUrls, $aData) = $this->index($ugid, array('type' => 'warning', 'message' => $clang->gT('Failed to add user.') . '<br />' . $clang->gT('User already exists in the group.')));
                        }

                        break;
                    case 'remove' :
                        if (!empty($user_in_group) && User_in_groups::model()->deleteByPk(array('ugid' => $ugid, 'uid' => $uid)))
                        {
                            list($aViewUrls, $aData) = $this->index($ugid, array('type' => 'success', 'message' => $clang->gT('User removed.')));
                        }
                        else
                        {
                            list($aViewUrls, $aData) = $this->index($ugid, array('type' => 'warning', 'message' => $clang->gT('Failed to remove user.') . '<br />' . $clang->gT('User does not exist in the group.')));
                        }

                        break;
                }
            }
            else
            {
                list($aViewUrls, $aData) = $this->index($ugid, array('type' => 'warning', 'message' => $clang->gT('Failed.') . '<br />' . $clang->gT('User not found.')));
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
    protected function _renderWrappedTemplate($sAction = 'usergroup', $aViewUrls = array(), $aData = array())
    {
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('adminstyleurl')."superfish.css");
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts') . 'jquery/jquery.tablesorter.min.js');
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('adminscripts').'users.js');

        $aData['display']['menu_bars']['user_group'] = true;

        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }
}
