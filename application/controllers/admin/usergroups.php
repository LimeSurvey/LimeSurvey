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

class Usergroups extends CAction
{

    private $headersent;
    private $footersent;

    public function run()
    {
        $actions = array_keys($_GET);
        $_GET['method'] = $action = (!empty($actions[0])) ? $actions[0] : '';

        if (!empty($action)) {
            $this->$action($_GET[$action]);
        }
        else
        {
            $this->view();
        }
    }

    private function _post($d)
    {
        if (isset($_POST[$d])) {
            return $_POST[$d];
        } else {
            return FALSE;
        }
    }

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


        $this->_sendHeaders($ugid);
        $action = CHttpRequest::getPost("action");
        $this->_renderUserGroupBar($ugid);


        if ($action == "mailsendusergroup") {

            // $usersummary = "<div class=\"header\">".$clang->gT("Mail to all Members")."</div>\n";
            // $usersummary .= "<div class=\"messagebox\">\n";

            // user must be in user group
            // or superadmin
            //$this->load->model('user_in_groups');
            $result = User_in_groups::model()->getSomeRecords(array('uid'), array('ugid' => $ugid, 'uid' => Yii::app()->session['loginID']));

            if (count($result) > 0 || Yii::app()->session['loginID'] == 1) {
                $where = array('and', 'ugid =' . $ugid, 'b.uid !=' . Yii::app()->session['loginID']);
                $join = array('where' => "{{users}} b", 'on' => 'a.uid = b.uid');
                $eguresult = User_in_groups::model()->join(array('*'), "{{user_in_groups}} AS a", $where, $join, 'b.users_name');
                //die('me');
                $to = '';
                if (isset($eguresult[0])) {
                    foreach ($eguresult as $egurow)
                    {
                        $to .= $egurow['users_name'] . ' <' . $egurow['email'] . '>' . '; ';
                    }
                } else {
                    $to .= $eguresult['users_name'] . ' <' . $eguresult['email'] . '>' . '; ';
                }
                $to = substr($to, 0, -2);

                //$this->load->model('users');
                $from_user_result = User::model()->getSomeRecords(array('email', 'users_name', 'full_name'), array('uid' => Yii::app()->session['loginID']));
                $from_user_row = $from_user_result;

                if ($from_user_row[0]->full_name) {
                    $from = $from_user_row[0]->full_name;
                    $from .= ' <';
                    $from .= $from_user_row[0]->email . '> ';
                }
                else
                {
                    $from = $from_user_row[0]->users_name . ' <' . $from_user_row[0]->email . '> ';
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
                    $this->view($ugid, array("type" => "success", "message" => "Message(s) sent successfully!"));
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
                    $this->view($ugid, $headercfg);
                }
            }
            else
            {
                //include("access_denied.php");
            }

        }
        else
        {
            //$this->load->model('user_groups');
            $where = array('and', 'a.ugid =' . $ugid, 'uid =' . Yii::app()->session['loginID']);
            $join = array('where' => "{{user_in_groups}} AS b", 'on' => 'a.ugid = b.ugid');
            $result = User_groups::model()->join(array('a.ugid', 'a.name', 'a.owner_id', 'b.uid'), "{{user_groups}} AS a", $where, $join, 'name');

            $crow = $result;
            $data['ugid'] = $ugid;
            $data['clang'] = $clang;

            $this->getController()->render("/admin/usergroup/mailUserGroup_view", $data);
        }

        $this->_sendFooters();
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
        $this->_sendHeaders($ugid);

        if ($action == "delusergroup") {

            if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1) {

                if (!empty($ugid) && ($ugid > -1)) {
                    $result = User_groups::model()->requestEditGroup($ugid, Yii::app()->session["loginID"]);
                    if ($result->count() > 0) {
                        $delquery_result = User_groups::model()->deleteGroup($ugid, Yii::app()->session["loginID"]);

                        // $del_user_in_groups_query = "DELETE FROM ".Yii::app()->db->tablePrefix."user_in_groups WHERE ugid=$ugid AND uid=".Yii::app()->session['loginID'];

                        if ($delquery_result) //Checked)
                        {
                            $this->view(false, array("type" => "success", "message" => $clang->gT("Success!")));
                        }
                        else
                        {
                            $this->view(false, array("type" => "warning", "message" => $clang->gT("Could not delete user group.")));
                        }
                    }
                }
                else
                {
                    $this->view($ugid, array("type" => "warning", "message" => $clang->gT("Could not delete user group. No group selected.")));
                }
            }

        }

        $this->_sendFooters();
    }


    public function add()
    {
        $clang = Yii::app()->lang;

        $this->_sendHeaders(false);
        $action = (isset($_POST['action'])) ? $_POST['action'] : '';

        if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1) {

            $this->_sendHeaders(false);
            $data['clang'] = $clang;

            if ($action == "usergroupindb") {

                if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1) {
                    $db_group_name = $_POST['group_name'];
                    $db_group_description = $_POST['group_description'];

                    if (isset($db_group_name) && strlen($db_group_name) > 0) {
                        if (strlen($db_group_name) > 21) {
                            $this->view(false, array("type" => "warning", "message" => $clang->gT("Failed to add Group! Group name length more than 20 characters.")));

                        }
                        else
                        {
                            //$this->loadModel("User_groups_model");
                            $ugid = User_groups::model()->addGroup($db_group_name, $db_group_description);

                            if ($ugid > 0) {
                                $this->view($ugid, array("type" => "success", "message" => $clang->gT("User Group successfully added!")));
                            }
                            else
                            {
                                $this->view(false, array("type" => "warning", "message" => $clang->gT("Failed to add Group! Group already exists.")));
                            }
                        }

                    }
                    else
                    {
                        $this->view(false, array("type" => "warning", "message" => $clang->gT("Failed to add Group! Group Name was not supplied.")));
                    }
                }
            }
            else
            {
                $this->getController()->render('/admin/usergroup/addUserGroup_view', $data);
            }
        }

        $this->_sendFooters();
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
        $this->_sendHeaders($ugid);
        if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1) {
            $data['clang'] = $clang;
            if ($action == "editusergroupindb") {
                if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1) {
                    $ugid = $_POST['ugid'];

                    $db_name = $_POST['name'];
                    $db_description = $_POST['description'];

                    if (User_groups::model()->updateGroup($db_name, $db_description, $ugid)) {
                        $headercfg["message"] = $clang->gT("Edit User Group Successfully!");
                        $headercfg["type"] = "success";
                    }
                    else
                    {
                        $headercfg["message"] = $clang->gT("Failed to edit User Group!");
                        $headercfg["type"] = "warning";
                    }
                    $this->view($ugid, $headercfg);

                }
            }
            else
            {
                $result = User_groups::model()->requestEditGroup($ugid, Yii::app()->session['loginID']);
                $esrow = $result->readAll();
                $data['esrow'] = $esrow[0];
                $data['ugid'] = $ugid;
                $this->getController()->render("/admin/usergroup/editUserGroup_view", $data);
                $this->_sendFooters();
            }
        }
    }


    /**
     * Usergroups::view()
     * Load viewing of a user group screen.
     * @param bool $ugid
     * @param array|bool $header (type=success, warning)(message=localized message)
     * @return void
     */
    public function view($ugid = false, $header = false)
    {
        if ($ugid != false)
            $ugid = (int)$ugid;
        if ($header)
            $data["headercfg"] = $header;
        else
            $data = array();

        $clang = Yii::app()->lang;
        $data["clang"] = $clang;
        $this->_sendHeaders($ugid);

        if (Yii::app()->session['loginID']) {

            if ($ugid) {
                $ugid = sanitize_int($ugid);
                $data["usergroupid"] = $ugid;
                $result = User_groups::model()->requestViewGroup($ugid, Yii::app()->session["loginID"]);
                $crow = $result[0];
                if ($result) {
                    $data["groupfound"] = true;
                    $data["groupname"] = $crow['name'];
                    if (!empty($crow['description']))
                        $data["usergroupdescription"] = $crow['description'];
                    else
                        $data["usergroupdescription"] = "";
                }
                //$this->user_in_groups_model = new User_in_groups;
                $eguquery = "SELECT * FROM " . Yii::app()->db->tablePrefix . "user_in_groups AS a INNER JOIN " . Yii::app()->db->tablePrefix . "users AS b ON a.uid = b.uid WHERE ugid = " . $ugid . " ORDER BY b.users_name";
                $eguresult = db_execute_assoc($eguquery);
                $query2 = "SELECT ugid FROM " . Yii::app()->db->tablePrefix . "user_groups WHERE ugid = " . $ugid . " AND owner_id = " . Yii::app()->session['loginID'];
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
                $data["userloop"] = $userloop;
                if (isset($row2[0]['ugid'])) {
                    $data["useradddialog"] = true;
                    $data["useraddusers"] = getgroupuserlist($ugid, 'optionlist');
                    $data["useraddurl"] = "";
                }

                //$data['display'] = $editsurvey;
                // $this->getController()->render('/survey_view',$displaydata);
                $this->getController()->render('/admin/usergroup/viewUserGroup_view', $data);
            }
        }

        $this->_sendFooters();
    }

    private function _sendHeaders($ugid = false)
    {
        if (!$this->headersent) {
            if ($ugid)
                $ugid = sanitize_int($ugid);
            else
                $ugid = false;
            $css_admin_includes[] = Yii::app()->getConfig('styleurl') . "admin/default/superfish.css";
            Yii::app()->setConfig("css_admin_includes", $css_admin_includes);
            $this->getController()->_js_admin_includes(Yii::app()->baseUrl . 'scripts/admin/users.js');
            $this->getController()->_getAdminHeader();
            $this->getController()->_showadminmenu(false);
            $this->_renderUserGroupBar($ugid);
            $this->headersent = true;
        }
    }

    private function _sendFooters()
    {
        if (!$this->footersent) {
            $this->getController()->_loadEndScripts();
            $this->getController()->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));
            $this->footersent = true;
        }
    }

    /**
     * Usergroups::_usergroupbar()
     * Load menu bar of user group controller.
     * @param bool $ugid
     * @return void
     */
    private function _renderUserGroupBar($ugid = false)
    {
        $data['clang'] = Yii::app()->lang;
        Yii::app()->loadHelper('database');
        if ($ugid) {
            $grpquery = "SELECT gp.* FROM " . Yii::app()->db->tablePrefix . "user_groups AS gp, " . Yii::app()->db->tablePrefix . "user_in_groups AS gu WHERE gp.ugid=gu.ugid AND gp.ugid = $ugid AND gu.uid=" . Yii::app()->session['loginID'];
            $grpresult = db_execute_assoc($grpquery);
            $grpresultcount = db_records_count($grpquery);

            if ($grpresultcount > 0) {
                $grow = array_map('htmlspecialchars', $grpresult->read());
            }
            else
            {
                $grow = false;
            }

            $data['grow'] = $grow;
            $data['grpresultcount'] = $grpresultcount;

        }

        $data['ugid'] = $ugid;


        $this->getController()->render('/admin/usergroup/usergroupbar_view', $data);
    }

    /**
     * Usergroups::_updateusergroup()
     * Function responsible to update a user group.
     * @param mixed $name
     * @param mixed $description
     * @param mixed $ugid
     * @return
     */
    private function _updateUserGroup($name, $description, $ugid)
    {
        $query = 'UPDATE ' . Yii::app()->db->tablePrefix . 'user_groups SET name=\'' . $name . '\', description=\'' . $description . '\' WHERE ugid=\'' . $ugid . '\'';
        //$this->load->model('user_groups');
        //$uquery = $this->user_groups_model->update(array('name' => $name, 'description' => $description), array('ugid' => $ugid));
        $uquery = db_execute_assoc($query);
        return $uquery; //or safe_die($connect->ErrorMsg()) ; //Checked)
    }

    /**
     * Usergroups::_refreshtemplates()
     * Function to refresh templates.
     * @return
     */
    private function _refreshTemplates()
    {

        $template_a = gettemplatelist();
        foreach ($template_a as $tp => $fullpath) {
            // check for each folder if there is already an entry in the database
            // if not create it with current user as creator (user with rights "create user" can assign template rights)
            $this->load->model('templates');
            $result = $this->templates_model->getAllRecords_like(array('folder' => $tp));

            if ($result->num_rows() == 0) {
                //$query2 = "INSERT INTO ".$this->db->dbprefix."templates (".db_quote_id('folder').",".db_quote_id('creator').") VALUES ('".$tp."', ".$_SESSION['loginID'].')' ;
                $data = array(
                    'folder' => $tp,
                    'creator' => $this->session->userdata('loginID')


                );

                $this->load->model('templates_model');
                $this->templates_model->insertRecords($data);

                //db_execute_assoc($query2); // or safe_die($connect->ErrorMsg()); //Checked
            }
        }
        return true;
    }

    // adds Usergroups in Database by Moses
    /**
     * Usergroups::_addUserGroupInDB()
     * Function that add a user group in database.
     * @param mixed $group_name
     * @param mixed $group_description
     * @return
     */
    private function _addUserGroupInDB($group_name, $group_description)
    {
        $connect = Yii::app()->db;
        $iquery = "INSERT INTO " . Yii::app()->db->tablePrefix . "user_groups (`name`, `description`, `owner_id`) VALUES('{$group_name}', '{$group_description}', '{$_SESSION['loginID']}')";
        $command = $connect->createCommand($iquery);
        $result = $command->query();
        /*$data = array(
                'name' => $group_name,
                'description' => $group_description,
                'owner_id' => $this->session->userdata('loginID')

        );
        $this->load->model('user_groups_model');
        $this->load->model('user_in_groups_model');*/

        if ($result) { //Checked
            $id = $connect->getLastInsertID(); //$connect->Insert_Id(db_table_name_nq('user_groups'),'ugid');

            if ($id > 0) {
                $user_in_groups_query = 'INSERT INTO ' . Yii::app()->db->tablePrefix . 'user_in_groups (ugid, uid) VALUES (' . $id . ',' . Yii::app()->session['loginID'] . ')';
                db_execute_assoc($user_in_groups_query);
                /*$this->user_in_groups_model = new User_in_groups;
                    $this->user_in_groups_model->ugid = $id;
                    $this->user_in_groups_model->uid = Yii::app()->session['loginID'];
                    $this->user_in_groups_model->save();*/
                //$this->user_in_groups_model->insert(array('ugid' => $id, 'uid' => $this->session->userdata('loginID')));
            }
            return $id;
        }
        else
            return -1;

    }
}
