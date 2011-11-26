<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* LimeSurvey
* Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
* $Id$
*
*/

/**
* User Controller
*
* This controller performs user actions
*
* @package		LimeSurvey
* @subpackage	Backend
*/
class user extends Survey_Common_Controller {

    /**
    * Constructor
    */
    function __construct()
    {
        parent::__construct();
    }

    /**
    * Show users table
    */
    function editusers()
    {
        $this->load->model("users_model");
        $this->load->model("surveys_model");

        self::_js_admin_includes(base_url().'scripts/jquery/jquery.tablesorter.min.js');
        self::_js_admin_includes(base_url().'scripts/admin/users.js');

        $userlist = getuserlist();
        $ui = count($userlist);
        $usrhimself = $userlist[0];
        unset($userlist[0]);

        if($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1)
        {
            $query=$this->surveys_model->getSomeRecords(array("count(*)"),array("owner_id"=>$usrhimself['uid']));
            $noofsurveys=$query->row_array();
            $noofsurveys=$noofsurveys["count(*)"];
            $data['noofsurveys'] = $noofsurveys;
        }

        if(isset($usrhimself['parent_id']) && $usrhimself['parent_id']!=0) {
            $uresult = $this->users_model->getSomeRecords(array("users_name"),array("uid"=>$usrhimself['parent_id']));
            $srow = $uresult->row_array();
        }

        $data['usrhimself']=$usrhimself;
        // other users
        $data['row'] = 0;
        $usr_arr = $userlist;
        $data['usr_arr']=$usr_arr;
        $noofsurveyslist = array(  );

        //This loops through for each user and checks the amount of surveys against them.
        for($i=1;$i<=count($usr_arr);$i++)
        {
            $query=$this->surveys_model->getSomeRecords(array("count(*)"),array("owner_id"=>$usr_arr[$i]['uid']));
            $noofsurveyslist[$i]=$query->row_array();
            $noofsurveyslist[$i]=$noofsurveyslist[$i]["count(*)"];
        }


        $data['clang']=$this->limesurvey_lang;
        $data['imageurl']=$this->config->item("imageurl");
        $data['noofsurveyslist']=$noofsurveyslist;

        $clang = $this->limesurvey_lang;
        self::_getAdminHeader();
        self::_showadminmenu();
        $this->load->view("admin/user/editusers",$data);
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

    }

    function adduser()
    {
        $clang=$this->limesurvey_lang;
        $this->load->model("users_model");
        if (!$this->session->userdata('USER_RIGHT_CREATE_USER')) {
            show_error("No permissions");
            exit;
        }

        $new_user = FlattenText($this->input->post('new_user'),false,true);
        $new_email = FlattenText($this->input->post('new_email'),false,true);
        $new_full_name = FlattenText($this->input->post('new_full_name'),false,true);



        $valid_email = true;

        if(!validate_email($new_email))
        {
            self::_getAdminHeader();
            self::_showadminmenu();

            $valid_email = false;
            self::_showMessageBox($clang->gT("Failed to add user"),"<br />\n" . " " . $clang->gT("The email address is not valid.")."<br />\n",$class='warningheader');
        }
        if(empty($new_user))
        {
	         self::_getAdminHeader();
            self::_showadminmenu();

            self::_showMessageBox($clang->gT("Failed to add user"),"<br />\n" . " " . $clang->gT("A username was not supplied or the username is invalid.")."<br />\n",$class='warningheader');
        }
        elseif($valid_email)
        {
            $new_pass = createPassword();
            $uresult = $this->users_model->insert($new_user, $new_pass,$new_full_name,$this->session->userdata('loginID'),$new_email);

            if($uresult)
            {
                $newqid = $this->db->insert_id();
				$this->load->model("templates_model");
                // add default template to template rights for user
				$this->templates_model->insertRecords(array('uid' => $newqid, 'folder' => 'default', 'use' => '1'));

                // add new user to userlist
				$sresult = $this->users_model->getAllRecords(array('uid' => $newqid));
				$srow= $sresult->row_array();

                $userlist = getuserlist();
                array_push($userlist, array("user"=>$srow['users_name'], "uid"=>$srow['uid'], "email"=>$srow['email'],
                "password"=>$srow["password"], "parent_id"=>$srow['parent_id'], // "level"=>$level,
                "create_survey"=>$srow['create_survey'],"participant_panel"=>$srow['participant_panel'], "configurator"=>$srow['configurator'], "create_user"=>$srow['create_user'],
                "delete_user"=>$srow['delete_user'], "superadmin"=>$srow['superadmin'], "manage_template"=>$srow['manage_template'],
                "manage_label"=>$srow['manage_label']));

                $this->session->set_userdata('flashmessage', $clang->gT("User created successfully! ") . $clang->gT("Username: ") . $new_user . $clang->gT(", Email: ") . $new_email);
                self::_getAdminHeader();
                self::_showadminmenu();

                // send Mail
                $body = sprintf($clang->gT("Hello %s,"), $new_full_name)."<br /><br />\n";
                $body .= sprintf($clang->gT("this is an automated email to notify that a user has been created for you on the site '%s'."), $this->config->item("sitename"))."<br /><br />\n";
                $body .= $clang->gT("You can use now the following credentials to log into the site:")."<br />\n";
                $body .= $clang->gT("Username") . ": " . $new_user . "<br />\n";
                if ($this->config->item("useWebserverAuth") === false)
                { // authent is not delegated to web server
                    // send password (if authorized by config)
                    if ($this->config->item("display_user_password_in_email") === true)
                    {
                        $body .= $clang->gT("Password") . ": " . $new_pass . "<br />\n";
                    }
                    else
                    {
                        $body .= $clang->gT("Password") . ": " . $clang->gT("Please ask your password to your LimeSurvey administrator") . "<br />\n";
                    }
                }

                $body .= "<a href='".site_url("admin/")."'>".$clang->gT("Click here to log in.")."</a><br /><br />\n";
                $body .=  sprintf($clang->gT('If you have any questions regarding this mail please do not hesitate to contact the site administrator at %s. Thank you!'),$this->config->item("siteadminemail"))."<br />\n";

                $subject = sprintf($clang->gT("User registration at '%s'","unescaped"),$this->config->item("sitename"));
                $to = $new_user." <$new_email>";
                $from = $this->config->item("siteadminname")." <".$this->config->item("siteadminemail").">";
                $addsummary = "";
                if(SendEmailMessage($body, $subject, $to, $from, $this->config->item("sitename"), true, $this->config->item("siteadminbounce")))
                {
                    $addsummary .= "<br />".$clang->gT("Username").": $new_user<br />".$clang->gT("Email").": $new_email<br />";
                    $addsummary .= "<br />".$clang->gT("An email with a generated password was sent to the user.");
                }
                else
                {
                    // has to be sent again or no other way
                    $tmp = str_replace("{NAME}", "<strong>".$new_user."</strong>", $clang->gT("Email to {NAME} ({EMAIL}) failed."));
                    $addsummary .= "<br />".str_replace("{EMAIL}", $new_email, $tmp) . "<br />";
                }

                $data['user'] = $new_user;
                $data['postuserid']=$newqid;
                $this->load->view("admin/user/setuserrights",$data);

            }
            else{
	             self::_getAdminHeader();
                self::_showadminmenu();
                $addsummary = "<div class='messagebox ui-corner-all'><div class='warningheader'>".$clang->gT("Failed to add user")."</div><br />\n" . " " . $clang->gT("The user name already exists.")."<br />\n";
            }
        }
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
    }

    /**
    * Delete user
    */
    function deluser()
    {
        $clang=$this->limesurvey_lang;
        if (!($this->session->userdata('USER_RIGHT_SUPERADMIN')==1 || $this->session->userdata('USER_RIGHT_DELETE_USER'))) {
            show_error("No permissions");
            exit;
        }

        self::_getAdminHeader();
        self::_showadminmenu();
        $_POST = $this->input->post();
        $action=$this->input->post("action");
		$this->load->model("users_model");
        // CAN'T DELETE ORIGINAL SUPERADMIN
        // Initial SuperAdmin has parent_id == 0
		$adminresult = $this->users_model->getSomeRecords(array('uid'), array('parent_id' => 0));
        $row=$adminresult->row_array();

        $postuserid = $this->input->post("uid");
        $postuser = $this->input->post("user");
        if($row['uid'] == $postuserid)	// it's the original superadmin !!!
        {
            self::_showMessageBox($clang->gT("Initial Superadmin cannot be deleted!"),"","warningheader");
        }
        else
        {
            if (isset($postuserid))
            {
                $sresultcount = 0;// 1 if I am parent of $postuserid
                if ($this->session->userdata('USER_RIGHT_SUPERADMIN') != 1)
                {
					$sresult = $this->users_model->getSomeRecords(array('uid'), array('parent_id' => $postuserid, 'parent_id' => $this->session->userdata('loginID')));
					$sresultcount = $sresult->num_rows();
                }

                if ($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1 || $sresultcount > 0 || $postuserid == $this->session->userdata('loginID'))
                {
                    $transfer_surveys_to = 0;
					$result = $this->users_model->getSomeRecords(array('users_name','uid'));

                    $current_user = $this->session->userdata('loginID');
                    if($result->num_rows() == 2) {

                        $action = "finaldeluser";
                        foreach($result->row_array() as $rows){
                            $intUid = $rows['uid'];
                            $selected = '';
                            if ($intUid == $current_user)
                                $selected = " selected='selected'";

                            if ($postuserid != $intUid)
                                $transfer_surveys_to = $intUid;
                        }
                    }

					$this->load->model("surveys_model");
					$result = $this->surveys_model->getSomeRecords(array('sid'), array('owner_id' => $current_user));
                    if($result->num_rows() == 0) {
                        $action = "finaldeluser";
                    }

                    if ($action=="finaldeluser")
                    {
                        if (isset($_POST['transfer_surveys_to'])) {$transfer_surveys_to=sanitize_int($_POST['transfer_surveys_to']);}
                        if ($transfer_surveys_to > 0){
							$result = $this->surveys_model->updateSurvey(array('owner_id'=>$postuserid), array('owner_id'=>$transfer_surveys_to));
                        }
						$sresult = $this->users_model->getSomeRecords(array('parent_id'), array('uid'=>$postuserid));
                        $fields = $sresult->row_array();

                        if (isset($fields[0]))
                        {
							$uresult = $this->users_model->parent_update(array('parent_id='=>$postuserid), array('parent_id='=>$fields[0]));
                        }

                        //DELETE USER FROM TABLE
						$dresult=$this->users_model->delete(array('uid'=>$postuserid));

                        // Delete user rights
						$this->load->model("survey_permissions_model");
						$dresult=$this->survey_permissions_model->deleteSomeRecords(array('uid'=>$postuserid));

                        if($postuserid == $this->session->userdata('loginID')) killSession();	// user deleted himself

                        $addsummary = "<br />".$clang->gT("Username").": {$postuser}<br /><br />\n";
                        $addsummary .= "<div class=\"successheader\">".$clang->gT("Success!")."</div>\n";
                        if ($transfer_surveys_to>0){
                            $sTransferred_to = self::_getUserNameFromUid($transfer_surveys_to);
                            $addsummary .= sprintf($clang->gT("All of the user's surveys were transferred to %s."),$sTransferred_to);
                        }
                        $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".site_url('admin/user/editusers')."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
                        self::_showMessageBox("",$addsummary);
                    }
                    else
                    {
                        $current_user = $this->session->userdata('loginID');
                        $addsummary = "<br />".$clang->gT("Transfer the user's surveys to: ")."\n";
                        $addsummary .= "<form method='post' name='deluserform' action='".site_url("admin/user/deluser")."'><select name='transfer_surveys_to'>\n";
						$result = $this->users_model->getSomeRecords(array('users_name','uid'));
                        if($result->num_rows() > 0) {
                            foreach($result->result_array() as $rows){
                                $intUid = $rows['uid'];
                                $sUsersName = $rows['users_name'];
                                $selected = '';
                                if ($intUid == $current_user)
                                    $selected = " selected='selected'";

                                if ($postuserid != $intUid)
                                    $addsummary .= "<option value='$intUid'$selected>$sUsersName</option>\n";
                            }
                        }
                        $addsummary .= "</select><input type='hidden' name='uid' value='$postuserid'>";
                        $addsummary .= "<input type='hidden' name='user' value='$postuser'>";
                        $addsummary .= "<input type='hidden' name='action' value='finaldeluser'><br /><br />";
                        $addsummary .= "<input type='submit' value='".$clang->gT("Delete User")."'></form>";
                        self::_showMessageBox("",$addsummary);
                    }

                }
                else
                {
                    include("access_denied.php");
                }
            }
            else
            {
                $addsummary = "<div class=\"warningheader\">".$clang->gT("Could not delete user. User was not supplied.")."</div>\n";
                $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".site_url('admin/user/editusers')."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
                self::_showMessageBox("",$addsummary);
            }
        }
        $addsummary .= "</div>\n";
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
    }

    /**
    * Modify User
    */
    function modifyuser()
    {
		$this->load->model("users_model");

        $postuserid=$this->input->post("uid");
        if (isset($postuserid) && $postuserid)
        {
			$sresult = $this->users_model->getSomeRecords(array('uid'),array('uid'=>$postuserid, 'parent_id'=>$this->session->userdata('loginID')));
            $sresultcount = $sresult->num_rows();
        }
        else
        {
           // include("access_denied.php");
           // die();
        }

        // RELIABLY CHECK MY RIGHTS
        if ($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1 || $this->session->userdata('loginID') == $postuserid ||
        ( $this->session->userdata('USER_RIGHT_CREATE_USER') &&
        $sresultcount > 0
        ) )
        {
			$sresult = $this->users_model->parentAndUser();
			$data['mur'] = $sresult;

           // $muq = "SELECT a.users_name, a.full_name, a.email, a.uid, b.users_name AS parent FROM ".$this->db->dbprefix('users')." AS a LEFT JOIN ".$this->db->dbprefix('users')." AS b ON a.parent_id = b.uid WHERE a.uid='{$postuserid}'";	//	added by Dennis
           // $data['mur'] = db_select_limit_assoc($muq, 1);

            $data['clang']=$this->limesurvey_lang;
            self::_getAdminHeader();
            self::_showadminmenu();
            $this->load->view("admin/user/modifyuser",$data);
            self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
        }
        else
        {
            include("access_denied.php");
        }
    }

    /**
    * Modify User POST
    */
    function moduser()
    {
        $clang=$this->limesurvey_lang;
        $_POST = $this->input->post();
        $postuser = $this->input->post("user");
        $postemail = $this->input->post("email");
        $postuserid = $this->input->post("uid");
        $postfull_name = $this->input->post("full_name");
        $display_user_password_in_html=$this->config->item("display_user_password_in_html");
        $addsummary='';

		$this->load->model("users_model");
		$sresult = $this->users_model->getSomeRecords(array('uid'),array('uid'=>$postuserid, 'parent_id'=>$this->session->userdata('loginID')));
        $sresultcount = $sresult->num_rows();

        if(($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1 || $postuserid == $this->session->userdata('loginID') ||
        ($sresultcount > 0 && $this->session->userdata('USER_RIGHT_CREATE_USER'))) && !($this->config->item("demoMode") == true && $postuserid == 1)
        )
        {
            $users_name = html_entity_decode($postuser, ENT_QUOTES, 'UTF-8');
            $email = html_entity_decode($postemail,ENT_QUOTES, 'UTF-8');
            $sPassword = html_entity_decode($_POST['pass'],ENT_QUOTES, 'UTF-8');
            if ($sPassword=='%%unchanged%%') $sPassword='';
            $full_name = html_entity_decode($postfull_name,ENT_QUOTES, 'UTF-8');
            $valid_email = true;

            if(!validate_email($email))
            {
                $valid_email = false;
                $failed = true;
                $addsummary .= "<div class=\"warningheader\">".$clang->gT("Could not modify user data.")."</div><br />\n"
                . " ".$clang->gT("Email address is not valid.")."<br />\n";
            }
            elseif($valid_email)
            {
                $failed = false;
                if(empty($sPassword))
                {
					$uresult = $this->users_model->update($postuserid, array('email'=>$this->db->escape($email), 'full_name'=>$this->db->escape($full_name)));
                } else {
                    $this->load->library("admin/sha256");
					$uresult = $this->users_model->update($postuserid, array('email'=>$this->db->escape($email), 'full_name'=>$this->db->escape($full_name), 'password' => $this->sha256->hashing($sPassword)));
                }

                if($uresult && empty($sPassword))
                {
                    $addsummary .= "<br />".$clang->gT("Username").": $users_name<br />".$clang->gT("Password").": (".$clang->gT("Unchanged").")<br /><br />\n";
                    $addsummary .= "<div class=\"successheader\">".$clang->gT("Success!")."</div>\n";
                } elseif($uresult && !empty($sPassword))
                {
                    if ($sPassword != 'password' ) $this->session->set_userdata('pw_notify',false);
                    if ($sPassword == 'password' ) $this->session->set_userdata('pw_notify',true);

                    if ($display_user_password_in_html === true)
                    {
                        $displayedPwd = $sPassword;
                    }
                    else
                    {
                        $displayedPwd = preg_replace('/./','*',$sPassword);
                    }
                    $addsummary .= "<br />".$clang->gT("Username").": $users_name<br />".$clang->gT("Password").": {$displayedPwd}<br /><br />\n";
                    $addsummary .= "<div class=\"successheader\">".$clang->gT("Success!")."</div>\n";
                }
                else
                {
                    // Username and/or email adress already exists.
                    $addsummary .= "<div class=\"warningheader\">".$clang->gT("Could not modify user data.")."</div><br />\n"
                    . " ".$clang->gT("Email address already exists.")."<br />\n";
                }
            }
            if($failed)
            {
                $addsummary .= "<br /><form method='post' action='".site_url('admin/user/modifyuser')."'>"
                ."<input type='submit' value='".$clang->gT("Back")."'>"
                ."<input type='hidden' name='uid' value='{$postuserid}'>"
                ."</form>";
            }
            else
            {
                $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".site_url('admin/user/editusers')."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
            }
        }
        else
        {
            include("access_denied.php");
        }
        self::_getAdminHeader();
        self::_showadminmenu();
        self::_showMessageBox($clang->gT("Editing user"),$addsummary);
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

    }

    /**
    *
    */
    function setuserrights()
    {
		$this->load->model("users_model");
        $data['clang']=$this->limesurvey_lang;
        $_POST = $this->input->post();
        self::_js_admin_includes(base_url().'scripts/admin/users.js');
        $postuser = $this->input->post("user");
        $postemail = $this->input->post("email");
        $postuserid = $_POST["uid"];
        $postfull_name = $this->input->post("full_name");
        if (isset($postuserid) && $postuserid)
        {
			$sresult = $this->users_model->getSomeRecords(array('uid'),array('uid'=>$postuserid, 'parent_id'=>$this->session->userdata('loginID')));
            $sresultcount = $sresult->num_rows();


        }
        else
        {
            include("access_denied.php");
            die();
        }

        // RELIABLY CHECK MY RIGHTS
        if ($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1 ||
        ( $this->session->userdata('USER_RIGHT_CREATE_USER') &&
        $sresultcount >  0 &&
        $this->session->userdata("loginID") != $postuserid
        ) )
        //	if($_SESSION['loginID'] != $postuserid)
        {
            self::_getAdminHeader();
            self::_showadminmenu();
            $data['postuserid']=$postuserid;
            $this->load->view("admin/user/setuserrights",$data);
            self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

        }
        else
        {
            include("access_denied.php");
        }
    }

    /**
    * User Rights POST
    */
    function userrights()
    {
		$this->load->model("users_model");
        $postuserid=$this->input->post("uid");
        $clang=$this->limesurvey_lang;
        $addsummary = "<div class='header ui-widget-header'>".$clang->gT("Set user permissions")."</div>\n";
        $addsummary .= "<div class=\"messagebox\">\n";

        $_POST=$this->input->post();

        // A user can't modify his own rights ;-)
        if($postuserid != $this->session->userdata('loginID'))
        {
			$sresult = $this->users_model->getSomeRecords(array('uid'),array('uid'=>$postuserid, 'parent_id'=>$this->session->userdata('loginID')));
            $sresultcount = $sresult->num_rows();

            if($this->session->userdata('USER_RIGHT_SUPERADMIN') != 1 && $sresultcount > 0)
            { // Not Admin, just a user with childs
                $rights = array();

                // Forbids Allowing more privileges than I have
                if(isset($_POST['create_survey']) && $this->session->userdata('USER_RIGHT_CREATE_SURVEY'))$rights['create_survey']=1;		else $rights['create_survey']=0;
                if(isset($_POST['participant_panel']) && $this->session->userdata('USER_RIGHT_PARTICIPANT_PANEL'))$rights['participant_panel']=1;	else $rights['participant_panel']=0;
                if(isset($_POST['configurator']) && $this->session->userdata('USER_RIGHT_CONFIGURATOR'))$rights['configurator']=1;			else $rights['configurator']=0;
                if(isset($_POST['create_user']) && $this->session->userdata('USER_RIGHT_CREATE_USER'))$rights['create_user']=1;			else $rights['create_user']=0;
                if(isset($_POST['delete_user']) && $this->session->userdata('USER_RIGHT_DELETE_USER'))$rights['delete_user']=1;			else $rights['delete_user']=0;

                $rights['superadmin']=0; // ONLY Initial Superadmin can give this right
                if(isset($_POST['manage_template']) && $this->session->userdata('USER_RIGHT_MANAGE_TEMPLATE'))$rights['manage_template']=1;	else $rights['manage_template']=0;
                if(isset($_POST['manage_label']) && $this->session->userdata('USER_RIGHT_MANAGE_LABEL'))$rights['manage_label']=1;			else $rights['manage_label']=0;
}
            elseif ($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1)
            {
                $rights = array();

                // Only Initial Superadmin can give this right
                if(isset($_POST['superadmin']))
                {
                    // Am I original Superadmin ?
                    // Initial SuperAdmin has parent_id == 0
					$adminresult = $this->users_model->getSomeRecords(array('uid'),array('parent_id'=>0));
                    $row=$adminresult->row();

                    if($row['uid'] == $this->session->userdata('loginID'))	// it's the original superadmin !!!
                    {
                        $rights['superadmin']=1;
                    }
                    else
                    {
                        $rights['superadmin']=0;
                    }
                }
                else
                {
                    $rights['superadmin']=0;
                }

                if(isset($_POST['create_survey']) || $rights['superadmin'])$rights['create_survey']=1;		else $rights['create_survey']=0;
                if(isset($_POST['configurator']) || $rights['superadmin'])$rights['configurator']=1;			else $rights['configurator']=0;
                if(isset($_POST['create_user']) || $rights['superadmin'])$rights['create_user']=1;			else $rights['create_user']=0;
                if(isset($_POST['participant_panel']) || $rights['superadmin'])$rights['participant_panel']=1;	else $rights['participant_panel']=0;
                if(isset($_POST['delete_user']) || $rights['superadmin'])$rights['delete_user']=1;			else $rights['delete_user']=0;
                if(isset($_POST['manage_template']) || $rights['superadmin'])$rights['manage_template']=1;	else $rights['manage_template']=0;
                if(isset($_POST['manage_label']) || $rights['superadmin'])$rights['manage_label']=1;			else $rights['manage_label']=0;

                setuserrights($postuserid, $rights);

            }
            else
            {
                include("access_denied.php");
            }
        }
        else
        {
            $addsummary .= "<div class=\"warningheader\">".$clang->gT("You are not allowed to change your own permissions!")."</div>\n";
            $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".site_url('admin/user/editusers')."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
        }
        $addsummary .= "</div>\n";

        $drights = ' Status Updated!  User permissions:';
        if($rights['superadmin']){
	     $drights .= ' Superadmin,';
            }
        if($rights['participant_panel']){
            $drights .= ' Participant Panel,';
            }
        if($rights['create_survey']){
            $drights .= ' Create Survey,';
            }
        if($rights['configurator']){
            $drights .= ' Configurator,';
            }
        if($rights['create_user']){
            $drights .= ' Create User,';
            }
	     if($rights['delete_user']){
	         $drights .= ' Delete User,';
	         }
	     if($rights['manage_template']){
	         $drights .= ' Use all/manage templates,';
	         }
	     if($rights['manage_label']){
	         $drights .= ' Manage Labels,';
	         }
	     $drights = substr($drights,0,strlen($drights)-1);
	     $this->session->set_userdata('flashmessage', $clang->gT($drights));

	     redirect('/admin/user/editusers', 'refresh');
}

    function setusertemplates()
    {
        $data['clang']=$this->limesurvey_lang;
        $_POST = $this->input->post();
        $postuser = $this->input->post("user");
        $postemail = $this->input->post("email");
        $postuserid = $_POST["uid"];
        $postfull_name = $this->input->post("full_name");

        self::_refreshtemplates();
        $data['userlist'] = getuserlist();

        self::_getAdminHeader();
        self::_showadminmenu();
        $data['postuserid']=$postuserid;
        $this->load->view("admin/user/setusertemplates",$data);
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

    }

    function usertemplates()
    {
        $postuserid=$this->input->post("uid");
        $clang=$this->limesurvey_lang;

        $_POST=$this->input->post();
        $addsummary = "<div class='header ui-widget-header'>".$clang->gT("Set template permissions")."</div>\n";
        $addsummary .= "<div class=\"messagebox\">\n";

        // SUPERADMINS AND MANAGE_TEMPLATE USERS CAN SET THESE RIGHTS
        if( $this->session->userdata('USER_RIGHT_SUPERADMIN') == 1 || $this->session->userdata('USER_RIGHT_MANAGE_TEMPLATE') == 1)
        {
            $templaterights = array();
			$this->load->model("templates_model");
			$tresult = $this->templates_model->getAllRecords();
            foreach ($tresult->result_array() as $trow) {
                if (isset($_POST[$trow["folder"]."_use"]))
                    $templaterights[$trow["folder"]] = 1;
                else
                    $templaterights[$trow["folder"]] = 0;
            }
            foreach ($templaterights as $key => $value) {
				$uresult = $this->template_rights_model->insert(array('uid' => $postuserid, 'folder' => $key, 'use' => $value));
                if (!$uresult)
                {
					$uresult = $this->template_rights_model->update(array('use' => $value), array('folder' => $key, 'uid' => $postuserid));
                }
            }
            if ($uresult)
            {
                $addsummary .= "<div class=\"successheader\">".$clang->gT("Template permissions were updated successfully.")."</div>\n";
                $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".site_url('admin/user/editusers')."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
            }
            else
            {
                $addsummary .= "<div class=\"warningheader\">".$clang->gT("Error")."</div>\n";
                $addsummary .= "<br />".$clang->gT("Error while updating usertemplates.")."<br />\n";
                $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".site_url('admin/user/editusers')."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
            }
        }
        else
        {
            include("access_denied.php");
        }
        $addsummary .= "</div>\n";
        self::_getAdminHeader();
        self::_showadminmenu();
        self::_showMessageBox("",$addsummary);
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
    }

    /**
    * Manage user personal settings
    */
    function personalsettings()
    {
        $clang = $this->limesurvey_lang;
        $this->load->model("users_model");

        if($this->input->post("action"))
        {
            $_POST  = $this->input->post();
            $data = array(	'lang' =>$_POST['lang'], 'dateformat'=>$_POST['dateformat'], 'htmleditormode'=>$_POST['htmleditormode'],
            'questionselectormode'=> $_POST['questionselectormode'], 'templateeditormode'=> $_POST['templateeditormode']);
            $uresult = $this->users_model->update($this->session->userdata("loginID"),$data);
            $this->session->set_userdata('adminlang', $_POST['lang']);
            $this->session->set_userdata('htmleditormode', $_POST['htmleditormode']);
            $this->session->set_userdata('questionselectormode', $_POST['questionselectormode']);
            $this->session->set_userdata('templateeditormode', $_POST['templateeditormode']);
            $this->session->set_userdata('dateformat', $_POST['dateformat']);
            $this->session->set_userdata('flashmessage', $clang->gT("Your personal settings were successfully saved."));
        }
        $query = $this->users_model->getSomeRecords(array("lang"),array("uid"=>$this->session->userdata("loginID")));
        $data['sSavedLanguage']=reset($query->row_array());

        $data['clang']=$clang;

        self::_getAdminHeader();
        self::_showadminmenu();
        $this->load->view("admin/user/personalsettings",$data);
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
    }

    function _getUserNameFromUid($uid){
        $uid = sanitize_int($uid);
		$this->load->model("users_model");
		$result = $this->users_model->getSomeRecords(array('users_name', 'uid'), array('uid' => $uid));

        if($result->num_rows() > 0) {
            foreach($result->row_array() as $rows){
                return $rows['users_name'];
            }
        }
    }

    function _refreshtemplates() {
        $template_a = gettemplatelist();
		$this->load->model("templates_model");
        foreach ($template_a as $tp=>$fullpath) {
            // check for each folder if there is already an entry in the database
            // if not create it with current user as creator (user with rights "create user" can assign template rights)
			$result = $this->templates_model->getSomeRecords(array('folder' => $tp));

            if ($result->num_rows() == 0) {
				$this->templates_model->insertRecords(array('folder' => $tp, 'creator' => $this->session->userdata('loginID')));
            }
        }
        return true;
    }
}