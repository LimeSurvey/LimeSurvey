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
 *	$Id: Admin_Controller.php 11256 2011-10-25 13:52:18Z c_schmitz $
 */

/**
 * Usergroups
 *
 * @package LimeSurvey
 * @author
 * @copyright 2011
 * @version $Id: usergroups.php 11128 2011-10-08 22:23:24Z dionet $
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

        $action = CHttpRequest::getPost("action");

        if ($action == "mailsendusergroup") {

            // user must be in user group
            // or superadmin
            $result = User_in_groups::model()->findAllByPk(array('ugid' => $ugid, 'uid' => Yii::app()->session['loginID']));

            if (count($result) > 0 || Yii::app()->session['loginID'] == 1)
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

        $this->_renderWrappedTemplate($aViewUrls, $aData);
    }

    /**
     * Usergroups::delete()
     * Function responsible to delete a user group.
     * @return void
     */
    public function delete()
    {
        $clang = Yii::app()->lang;
        $action = $_POST['action'];
        $ugid = $_POST['ugid'];
        $aViewUrls = array();
        $aData = array();

        if ($action == "delusergroup") {

            if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1) {

                if (!empty($ugid) && ($ugid > -1)) {
                    $result = User_groups::model()->requestEditGroup($ugid, Yii::app()->session["loginID"]);
                    if ($result->count() > 0) {
                        $delquery_result = User_groups::model()->deleteGroup($ugid, Yii::app()->session["loginID"]);

                        // $del_user_in_groups_query = "DELETE FROM {{user_in_groups}} WHERE ugid=$ugid AND uid=".Yii::app()->session['loginID'];

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

        }

        $this->_renderWrappedTemplate($aViewUrls, $aData);
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
                            list($aViewUrls, $aData) = $this->index(false, array("type" => "warning", "message" => $clang->gT("Failed to add Group! Group name length more than 20 characters.")));
                        }
                        elseif (User_groups::model()->find("name='$db_group_name'")) {
                            list($aViewUrls, $aData) = $this->index(false, array("type" => "warning", "message" => $clang->gT("Failed to add Group! Group already exists.")));
                        }
                        else
                        {
                            $ugid = User_groups::model()->addGroup($db_group_name, $db_group_description);
                            list($aViewUrls, $aData) = $this->index($ugid, array("type" => "success", "message" => $clang->gT("User Group successfully added!")));
                        }
                    }
                    else
                    {
                        list($aViewUrls, $aData) = $this->index(false, array("type" => "warning", "message" => $clang->gT("Failed to add Group! Group Name was not supplied.")));
                    }
                }
            }
            else
            {
                $aViewUrls = 'addUserGroup_view';
            }
        }

        $this->_renderWrappedTemplate($aViewUrls, $aData);
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

                $ugid = $_POST['ugid'];

                $db_name = $_POST['name'];
                $db_description = $_POST['description'];
                if (User_groups::model()->findByAttributes(array('name'=>$db_name))) {
                    $headercfg['message'] = $clang->gT("The User Group already exists.");
                    $headercfg["type"] = "warning";
                }
                else if (User_groups::model()->updateGroup($db_name, $db_description, $ugid)) {
                    $headercfg["message"] = $clang->gT("Edit User Group Successfully!");
                    $headercfg["type"] = "success";
                }
                else
                {
                    $headercfg["message"] = $clang->gT("Failed to edit User Group!");
                    $headercfg["type"] = "warning";
                }
                list($aViewUrls, $aData) = $this->index($ugid, $headercfg);

            }
            else
            {
                $result = User_groups::model()->requestEditGroup($ugid, Yii::app()->session['loginID']);
                $esrow = $result->readAll();
                $aData['esrow'] = $esrow[0];
                $aData['ugid'] = $ugid;
                $aViewUrls = 'editUserGroup_view';
            }
        }

        $this->_renderWrappedTemplate($aViewUrls, $aData);
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
                $eguresult = db_execute_assoc($eguquery);
                $query2 = "SELECT ugid FROM {{user_groups}} WHERE ugid = " . $ugid . " AND owner_id = " . Yii::app()->session['loginID'];
                $result2 = db_select_limit_assoc($query2, 1);
                $row2 = $result2->readAll();
                $row = 1;
                $userloop = array();
                $bgcc = "oddrow";
                foreach ($eguresult->readAll() as $egurow)
                {
                    if ($bgcc == "evenrow")
                        $bgcc = "oddrow";
                    else
                        $bgcc = "evenrow";
                    $userloop[$row]["userid"] = $egurow['uid'];
                    if ($egurow['uid'] == $crow['owner_id']) {
                        $userloop[$row]["username"] = "<strong>{$egurow['users_name']}</strong>";
                        $userloop[$row]["email"] = "<strong>{$egurow['email']}</strong>";
                        $userloop[$row]["rowclass"] = $bgcc;
                        $userloop[$row]["displayactions"] = false;
                        continue;
                    }
                    //	output users
                    $userloop[$row]["rowclass"] = $bgcc;
                    if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1)
                        $userloop[$row]["displayactions"] = true;

                    $userloop[$row]["username"] = $egurow['users_name'];
                    $userloop[$row]["email"] = $egurow['email'];
                    $row++;
                }
                $aData["userloop"] = $userloop;
                if (isset($row2[0]['ugid'])) {
                    $aData["useradddialog"] = true;
                    $aData["useraddusers"] = getgroupuserlist($ugid, 'optionlist');
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
            $this->_renderWrappedTemplate($aViewUrls, $aData);
        }
    }

    function addusertogroup($ugid)
    {
        Yii::app()->loadHelper('database');
	    $clang = Yii::app()->lang;
	    $postuserid = CHttpRequest::getPost('uid');

        $addsummary = "<div class=\"header\">".$clang->gT("Adding User to group")."...</div>\n";
        $addsummary .= "<div class=\"messagebox\">\n";

        if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1)
        {
            $query = "SELECT ugid, owner_id FROM {{user_groups}} WHERE ugid = " . $ugid . " AND owner_id = ".Yii::app()->session['loginID']." AND owner_id != ".$postuserid;
            $result = db_execute_assoc($query); //Checked
            if($result->count() > 0)
            {
                if($postuserid > 0)
                {
                    $isrresult = User_in_groups::model()->insert(array('ugid' => $ugid, 'uid' => $postuserid)); //Checked

                    if($isrresult)
                    {
                        $addsummary .= "<div class=\"successheader\">".$clang->gT("User added.")."</div>\n";
                    }
                    else  // ToDo: for this to happen the keys on the table must still be set accordingly
                    {
                        // Username already exists.
                        $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add user.")."</div>\n" . "<br />" . $clang->gT("Username already exists.")."<br />\n";
                    }
                }
                else
                {
                    $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add user.")."</div>\n" . "<br />" . $clang->gT("No Username selected.")."<br />\n";
                }
                $addsummary .= "<br/><input type=\"submit\" onclick=\"window.location='" . $this->getController()->createUrl('admin/usergroups/view/ugid/') . '/' . $ugid . "'\" value=\"".$clang->gT("Continue")."\"/>\n";

            }
            else
            {
            	die('access denied');
            }
        }
        else
        {
        	die('access denied');
        }

        $addsummary .= "</div>\n";
        $aViewUrls['output'] = $addsummary;

        $this->_renderWrappedTemplate($aViewUrls);
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($aViewUrls = array(), $aData = array())
    {
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('styleurl')."admin/default/superfish.css");
        $this->getController()->_js_admin_includes(Yii::app()->baseUrl . 'scripts/admin/users.js');

        $aData['display']['menu_bars']['user_group'] = true;

        parent::_renderWrappedTemplate('usergroup', $aViewUrls, $aData);
    }
}
