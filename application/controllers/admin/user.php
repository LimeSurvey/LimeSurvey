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
 * $Id: assessments.php 10433 2011-07-06 14:18:45Z dionet $
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
class user extends SurveyCommonController {

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
	    }
	
	    if(isset($usrhimself['parent_id']) && $usrhimself['parent_id']!=0) {
	        //$uquery = "SELECT users_name FROM ".db_table_name('users')." WHERE uid=".$usrhimself['parent_id'];
	        //$uresult = db_execute_assoc($uquery); //Checked
	        $uresult = $this->users_model->getSomeRecords(array("users_name"),array("uid"=>$usrhimself['parent_id']));
	        $srow = $uresult->row_array();
	        //$usersummary .= "<td align='center'><strong>{$srow['users_name']}</strong></td>\n";
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
	        //$noofsurveyslist[$i]=$connect->GetOne('Select count(*) from '.db_table_name('surveys').' where owner_id='.$usr_arr[$i]['uid']);
	        $query=$this->surveys_model->getSomeRecords(array("count(*)"),array("owner_id"=>$usr_arr[$i]['uid']));
			$noofsurveyslist[$i]=$query->row_array();
			$noofsurveyslist[$i]=$noofsurveyslist[$i]["count(*)"];
	    }

		$data['noofsurveys'] = $noofsurveys;
		$data['clang']=$this->limesurvey_lang;
		$data['imageurl']=$this->config->item("imageurl");
		$data['noofsurveyslist']=$noofsurveyslist;

        $clang = $this->limesurvey_lang;
        self::_getAdminHeader();
		self::_showadminmenu();
		$this->load->view("admin/User/editusers",$data);
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
			
	    $new_user = FlattenText($this->input->post('new_user'),true);
	    $new_email = FlattenText($this->input->post('new_email'),true);
	    $new_full_name = FlattenText($this->input->post('new_full_name'),true);

		self::_getAdminHeader();
		self::_showadminmenu();	
	    $valid_email = true;
	    if(!validate_email($new_email))
	    {
	        $valid_email = false;
	        //$addsummary .= "<div class='messagebox ui-corner-all'><div class='warningheader'>".$clang->gT("Failed to add user")."</div><br />\n" . " " . $clang->gT("The email address is not valid.")."<br />\n";
			self::_showMessageBox($clang->gT("Failed to add user"),"<br />\n" . " " . $clang->gT("The email address is not valid.")."<br />\n",$class='warningheader');
	    }
	    if(empty($new_user))
	    {
	        //if($valid_email) $addsummary .= "<br /><strong>".$clang->gT("Failed to add user")."</strong><br />\n" . " ";
	        //$addsummary .= $clang->gT("A username was not supplied or the username is invalid.")."<br />\n";
			self::_showMessageBox($clang->gT("Failed to add user"),"<br />\n" . " " . $clang->gT("A username was not supplied or the username is invalid.")."<br />\n",$class='warningheader');
	    }
	    elseif($valid_email)
	    {
	        $new_pass = createPassword();
	        //$uquery = "INSERT INTO {$dbprefix}users (users_name, password,full_name,parent_id,lang,email,create_survey,create_user,delete_user,superadmin,configurator,manage_template,manage_label) 
	        //           VALUES ('".db_quote($new_user)."', '".SHA256::hashing($new_pass)."', '".db_quote($new_full_name)."', {$_SESSION['loginID']}, 'auto', '".db_quote($new_email)."',0,0,0,0,0,0,0)";
	        $uresult = $this->users_model->insert($new_user, $new_pass,$new_full_name,$this->session->userdata('loginID'),$new_email);
	
	        if($uresult)
	        {
	            $newqid = $this->db->insert_id();
				$this->load->helper("database");
	            // add default template to template rights for user
	            $template_query = "INSERT INTO ".$this->db->dbprefix("templates_rights")." VALUES('$newqid','default','1')";
	            db_execute_assoc($template_query); //Checked
	
	            // add new user to userlist
	            $squery = "SELECT uid, users_name, password, parent_id, email, create_survey, configurator, create_user, delete_user, superadmin, manage_template, manage_label FROM ".$this->db->dbprefix('users')." WHERE uid='{$newqid}'";			//added by Dennis
	            $sresult = db_execute_assoc($squery);//Checked
	            $srow = $sresult->row_array();
	            $userlist = getuserlist();
	            array_push($userlist, array("user"=>$srow['users_name'], "uid"=>$srow['uid'], "email"=>$srow['email'],
				"password"=>$srow["password"], "parent_id"=>$srow['parent_id'], // "level"=>$level,
				"create_survey"=>$srow['create_survey'], "configurator"=>$srow['configurator'], "create_user"=>$srow['create_user'],
				"delete_user"=>$srow['delete_user'], "superadmin"=>$srow['superadmin'], "manage_template"=>$srow['manage_template'],
				"manage_label"=>$srow['manage_label']));
	
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
	
	            $addsummary .= "<br />\t\t\t<form method='post' action='".site_url("admin/user/setuserrights")."'>"
	            ."<input type='submit' value='".$clang->gT("Set user permissions")."'>"
	            ."<input type='hidden' name='action' value='setuserrights'>"
	            ."<input type='hidden' name='user' value='{$new_user}'>"
	            ."<input type='hidden' name='uid' value='{$newqid}'>"
	            ."</form>";
				self::_showMessageBox("",$addsummary);
				
	        }
	        else{
	            $addsummary .= "<div class='messagebox ui-corner-all'><div class='warningheader'>".$clang->gT("Failed to add user")."</div><br />\n" . " " . $clang->gT("The user name already exists.")."<br />\n";
	        }
	    }
	   //  $addsummary .= "<p><input type=\"submit\" onclick=\"window.open('$scriptname?action=editusers', '_top')\" value=\"".$clang->gT("Continue")."\"/></div>\n";
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
		$this->load->helper("database");
		
		//if (($action == "deluser" || $action == "finaldeluser") && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $_SESSION['USER_RIGHT_DELETE_USER'] ))
	    //$addsummary = "<div class=\"header\">".$clang->gT("Deleting user")."</div>\n";
	    //$addsummary .= "<div class=\"messagebox\">\n";
	
	    // CAN'T DELETE ORIGINAL SUPERADMIN
	    // Initial SuperAdmin has parent_id == 0
	    $adminquery = "SELECT uid FROM ".$this->db->dbprefix('users')." WHERE parent_id=0";
	    $adminresult = db_select_limit_assoc($adminquery, 1);//Checked
	    $row=$adminresult->row_array();
	
		$postuserid = $this->input->post("uid");
		$postuser = $this->input->post("user");
	    if($row['uid'] == $postuserid)	// it's the original superadmin !!!
	    {
	        //$addsummary .= "<div class=\"warningheader\">".$clang->gT("Initial Superadmin cannot be deleted!")."</div>\n";
			self::_showMessageBox($clang->gT("Initial Superadmin cannot be deleted!"),"","warningheader");
	    }
	    else
	    {
	        if (isset($postuserid))
	        {
	            $sresultcount = 0;// 1 if I am parent of $postuserid
	            if ($this->session->userdata('USER_RIGHT_SUPERADMIN') != 1)
	            {
	                $squery = "SELECT uid FROM ".$this->db->dbprefix('users')." WHERE uid=$postuserid AND parent_id=".$this->session->userdata('loginID');
	                $sresult = $connect->Execute($squery); //Checked
	                $sresultcount = $sresult->RecordCount();
	            }
	
	            if ($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1 || $sresultcount > 0 || $postuserid == $this->session->userdata('loginID'))
	            {
	                $transfer_surveys_to = 0;
	                $query = "SELECT users_name, uid FROM ".$this->db->dbprefix('users').";";
	                $result = db_execute_assoc($query);
	
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
	
	                $query = "SELECT sid FROM ".$this->db->dbprefix('surveys')." WHERE owner_id = $current_user ;";
	                $result = db_execute_assoc($query);
	                if($result->num_rows() == 0) {
	                    $action = "finaldeluser";
	                 }
	
	                if ($action=="finaldeluser")
	                {
	                    if (isset($_POST['transfer_surveys_to'])) {$transfer_surveys_to=sanitize_int($_POST['transfer_surveys_to']);}
	                    if ($transfer_surveys_to > 0){
	                        $query = "UPDATE ".$this->db->dbprefix('surveys')." SET owner_id = $transfer_surveys_to WHERE owner_id=$postuserid";
	                        $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	                    }
	                    $squery = "SELECT parent_id FROM ".$this->db->dbprefix('users')." WHERE uid=".$postuserid;
	                    $sresult = db_execute_assoc($squery); //Checked
	                    $fields = $sresult->row_array();
	
	                    if (isset($fields[0]))
	                    {
	                        $uquery = "UPDATE ".$this->db->dbprefix('users')." SET parent_id={$fields[0]} WHERE parent_id=".$postuserid;	//		added by Dennis
	                        $uresult = db_execute_assoc($uquery); //Checked
	                    }
	
	                    //DELETE USER FROM TABLE
	                    $dquery="DELETE FROM ".$this->db->dbprefix('users')." WHERE uid=".$postuserid;	//	added by Dennis
	                    $dresult=db_execute_assoc($dquery);  //Checked
	
	                    // Delete user rights
	                    $dquery="DELETE FROM ".$this->db->dbprefix('survey_permissions')." WHERE uid=".$postuserid;
	                    $dresult=db_execute_assoc($dquery); //Checked
	
	                    if($postuserid == $this->session->userdata('loginID')) killSession();	// user deleted himself
	
	                    $addsummary = "<br />".$clang->gT("Username").": {$postuser}<br /><br />\n";
	                    $addsummary .= "<div class=\"successheader\">".$clang->gT("Success!")."</div>\n";
	                    if ($transfer_surveys_to>0){
	                        $sTransferred_to = self::_getUserNameFromUid($transfer_surveys_to);
	                        $addsummary .= sprintf($clang->gT("All of the user's surveys were transferred to %s."),$sTransferred_to);
	                    }
	                    $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('$scriptname?action=editusers', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
						self::_showMessageBox("",$addsummary);
	                }
	                else
	                {
	                    $current_user = $this->session->userdata('loginID');
	                    $addsummary = "<br />".$clang->gT("Transfer the user's surveys to: ")."\n";
	                    $addsummary .= "<form method='post' name='deluserform' action='".site_url("admin/user/deluser")."'><select name='transfer_surveys_to'>\n";
	                    $query = "SELECT users_name, uid FROM ".$this->db->dbprefix('users').";";
	                    $result = db_execute_assoc($query);
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
	            $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('$scriptname?action=editusers', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
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
		$this->load->helper("database");
		$postuserid=$this->input->post("uid");
	    if (isset($postuserid) && $postuserid)
	    {
	        $squery = "SELECT uid FROM ".$this->db->dbprefix("users")." WHERE uid=$postuserid AND parent_id=".$this->session->userdata('loginID');	//		added by Dennis
	        $sresult = db_select_limit_assoc($squery);//Checked
	        $sresultcount = $sresult->num_rows();
	    }
	    else
	    {
	        include("access_denied.php");
			die();
	    }
	
	    // RELIABLY CHECK MY RIGHTS
	    if ($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1 || $this->session->userdata('loginID') == $postuserid ||
	    ( $this->session->userdata('USER_RIGHT_CREATE_USER') &&
	    $sresultcount > 0
	    ) )
	    {
			$muq = "SELECT a.users_name, a.full_name, a.email, a.uid, b.users_name AS parent FROM ".$this->db->dbprefix('users')." AS a LEFT JOIN ".$this->db->dbprefix('users')." AS b ON a.parent_id = b.uid WHERE a.uid='{$postuserid}'";	//	added by Dennis
			$data['mur'] = db_select_limit_assoc($muq, 1);

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
		$this->load->helper("database");
		$clang=$this->limesurvey_lang;
		$_POST = $this->input->post();
		$postuser = $this->input->post("user");
		$postemail = $this->input->post("email");
		$postuserid = $this->input->post("uid");
		$postfull_name = $this->input->post("full_name");
	    $addsummary = "<div class='header ui-widget-header'>".$clang->gT("Editing user")."</div>\n";
	    $addsummary .= "<div class=\"messagebox\">\n";
	
	    $squery = "SELECT uid FROM ".$this->db->dbprefix("users")." WHERE uid=$postuserid AND parent_id=".$this->session->userdata('loginID');
	    $sresult = db_select_limit_assoc($squery); //Checked
	    $sresultcount = $sresult->num_rows();
	
	    if(($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1 || $postuserid == $this->session->userdata('loginID') ||
	    ($sresultcount > 0 && $this->session->userdata('USER_RIGHT_CREATE_USER'))) && !($this->config->item("demoModeOnly") == true && $postuserid == 1)
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
	                $uquery = "UPDATE ".$this->db->dbprefix('users')." SET email=".$this->db->escape($email).", full_name=".$this->db->escape($full_name)." WHERE uid=".$postuserid;
	            } else {
	            	$this->load->library("admin/sha256");
	                $uquery = "UPDATE ".$this->db->dbprefix('users')." SET email=".$this->db->escape($email).", full_name=".$this->db->escape($full_name).", password='".$this->sha256->hashing($sPassword)."' WHERE uid=".$postuserid;
	            }
	
	            $uresult = db_select_limit_assoc($uquery);//Checked
	
	            if($uresult && empty($sPassword))
	            {
	                $addsummary .= "<br />".$clang->gT("Username").": $users_name<br />".$clang->gT("Password").": (".$clang->gT("Unchanged").")<br /><br />\n";
	                $addsummary .= "<div class=\"successheader\">".$clang->gT("Success!")."</div>\n";
	            } elseif($uresult && !empty($sPassword))
	            {
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
	            $addsummary .= "<br /><form method='post' action='$scriptname'>"
	            ."<input type='submit' value='".$clang->gT("Back")."'>"
	            ."<input type='hidden' name='action' value='modifyuser'>"
	            ."<input type='hidden' name='uid' value='{$postuserid}'>"
	            ."</form>";
	        }
	        else
	        {
	            $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('$scriptname?action=editusers', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
	        }
	    }
	    else
	    {
	        include("access_denied.php");
	    }
		self::_getAdminHeader();
		self::_showadminmenu();
	    $addsummary .= "</div>\n";
		self::_showMessageBox("",$addsummary);
		self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

	}

	/**
	 * 
	 */
	function setuserrights()
	{
		$this->load->helper("database");
		$data['clang']=$this->limesurvey_lang;
		$_POST = $this->input->post();
		$postuser = $this->input->post("user");
		$postemail = $this->input->post("email");
		$postuserid = $_POST["uid"];
		$postfull_name = $this->input->post("full_name");
		if (isset($postuserid) && $postuserid)
	    {
	        $squery = "SELECT uid FROM ".$this->db->dbprefix("users")." WHERE uid=$postuserid AND parent_id=".$this->session->userdata('loginID');	//		added by Dennis
	        $sresult = db_execute_assoc($squery);//Checked
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
	
	    }	// if
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
		$this->load->helper("database");
		$postuserid=$this->input->post("uid");
		$clang=$this->limesurvey_lang;
		$addsummary = "<div class='header ui-widget-header'>".$clang->gT("Set user permissions")."</div>\n";
	    $addsummary .= "<div class=\"messagebox\">\n";
		
		$_POST=$this->input->post();
	
	    // A user can't modify his own rights ;-)
	    if($postuserid != $this->session->userdata('loginID'))
	    {
	        $squery = "SELECT uid FROM ".$this->db->dbprefix("users")." WHERE uid=$postuserid AND parent_id=".$this->session->userdata('loginID');
	        $sresult = db_execute_assoc($squery); // Checked
	        $sresultcount = $sresult->num_rows();
	
	        if($this->session->userdata('USER_RIGHT_SUPERADMIN') != 1 && $sresultcount > 0)
	        { // Not Admin, just a user with childs
	            $rights = array();
	
	            // Forbids Allowing more privileges than I have
	            if(isset($_POST['create_survey']) && $this->session->userdata('USER_RIGHT_CREATE_SURVEY'))$rights['create_survey']=1;		else $rights['create_survey']=0;
	            if(isset($_POST['configurator']) && $this->session->userdata('USER_RIGHT_CONFIGURATOR'))$rights['configurator']=1;			else $rights['configurator']=0;
	            if(isset($_POST['create_user']) && $this->session->userdata('USER_RIGHT_CREATE_USER'))$rights['create_user']=1;			else $rights['create_user']=0;
	            if(isset($_POST['delete_user']) && $this->session->userdata('USER_RIGHT_DELETE_USER'))$rights['delete_user']=1;			else $rights['delete_user']=0;
	
	            $rights['superadmin']=0; // ONLY Initial Superadmin can give this right
	            if(isset($_POST['manage_template']) && $this->session->userdata('USER_RIGHT_MANAGE_TEMPLATE'))$rights['manage_template']=1;	else $rights['manage_template']=0;
	            if(isset($_POST['manage_label']) && $this->session->userdata('USER_RIGHT_MANAGE_LABEL'))$rights['manage_label']=1;			else $rights['manage_label']=0;
	
	            if ($postuserid<>1) setuserrights($postuserid, $rights);
	            $addsummary .= "<div class=\"successheader\">".$clang->gT("User permissions were updated successfully.")."</div>\n";
	            $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('$scriptname?action=editusers', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
	        }
	        elseif ($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1)
	        {
	            $rights = array();
	
	            if(isset($_POST['create_survey']))$rights['create_survey']=1;		else $rights['create_survey']=0;
	            if(isset($_POST['configurator']))$rights['configurator']=1;			else $rights['configurator']=0;
	            if(isset($_POST['create_user']))$rights['create_user']=1;			else $rights['create_user']=0;
	            if(isset($_POST['delete_user']))$rights['delete_user']=1;			else $rights['delete_user']=0;
	
	            // Only Initial Superadmin can give this right
	            if(isset($_POST['superadmin']))
	            {
	                // Am I original Superadmin ?
	
	                // Initial SuperAdmin has parent_id == 0
	                $adminquery = "SELECT uid FROM ".$this->db->dbprefix("users")." WHERE parent_id=0";
	                $adminresult = db_select_limit_assoc($adminquery, 1);
	                $row=$adminresult->FetchRow();
	                 
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
	
	            if(isset($_POST['manage_template']))$rights['manage_template']=1;	else $rights['manage_template']=0;
	            if(isset($_POST['manage_label']))$rights['manage_label']=1;			else $rights['manage_label']=0;
	
	            setuserrights($postuserid, $rights);
	            $addsummary .= "<div class=\"successheader\">".$clang->gT("User permissions were updated successfully.")."</div>\n";
	            $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('$scriptname?action=editusers', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
	        }
	        else
	        {
	            include("access_denied.php");
	        }
	    }
	    else
	    {
	        $addsummary .= "<div class=\"warningheader\">".$clang->gT("You are not allowed to change your own permissions!")."</div>\n";
	        $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('$scriptname?action=editusers', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
	    }
	    $addsummary .= "</div>\n";
		self::_getAdminHeader();
		self::_showadminmenu();
		self::_showMessageBox("",$addsummary);
		self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

    }

	function setusertemplates()
	{
		$this->load->helper("database");
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
		$this->load->helper("database");
		$postuserid=$this->input->post("uid");
		$clang=$this->limesurvey_lang;
		
		$_POST=$this->input->post();
		$addsummary = "<div class='header ui-widget-header'>".$clang->gT("Set template permissions")."</div>\n";
	    $addsummary .= "<div class=\"messagebox\">\n";
	
	    // SUPERADMINS AND MANAGE_TEMPLATE USERS CAN SET THESE RIGHTS
	    if( $this->session->userdata('USER_RIGHT_SUPERADMIN') == 1 || $this->session->userdata('USER_RIGHT_MANAGE_TEMPLATE') == 1)
	    {
	        $templaterights = array();
	        $tquery = "SELECT * FROM ".$this->db->dbprefix("templates");
	        $tresult = db_execute_assoc($tquery);
	        foreach ($tresult->result_array() as $trow) {
	            if (isset($_POST[$trow["folder"]."_use"]))
	            $templaterights[$trow["folder"]] = 1;
	            else
	            $templaterights[$trow["folder"]] = 0;
	        }
	        foreach ($templaterights as $key => $value) {
	            $uquery = "INSERT INTO ".$this->db->dbprefix("templates_rights")." (uid,`folder`,`use`)  VALUES ({$postuserid},'".$key."',$value)";
	            $uresult = db_execute_assoc($uquery);
	            if (!$uresult)
	            {
	                $uquery = "UPDATE ".$this->db->dbprefix("templates_rights")."  SET  ".$this->db->escape('use')."=$value where ".$this->db->escape('folder')."='$key' AND uid=".$postuserid;
	                $uresult = db_execute_assoc($uquery);
	            }
	        }
	        if ($uresult)
	        {
	            $addsummary .= "<div class=\"successheader\">".$clang->gT("Template permissions were updated successfully.")."</div>\n";
	            $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('$scriptname?action=editusers', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
	        }
	        else
	        {
	            $addsummary .= "<div class=\"warningheader\">".$clang->gT("Error")."</div>\n";
	            $addsummary .= "<br />".$clang->gT("Error while updating usertemplates.")."<br />\n";
	            $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('$scriptname?action=editusers', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
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
		    //$uquery = "UPDATE {$dbprefix}users SET lang='{$_POST['lang']}', dateformat='{$_POST['dateformat']}', htmleditormode= '{$_POST['htmleditormode']}', questionselectormode= '{$_POST['questionselectormode']}', templateeditormode= '{$_POST['templateeditormode']}'
		    //           WHERE uid={$_SESSION['loginID']}";
			$data = array(	'lang' =>$_POST['lang'], 'dateformat'=>$_POST['dateformat'], 'htmleditormode'=>$_POST['htmleditormode'],
							'questionselectormode'=> $_POST['questionselectormode'], 'templateeditormode'=> $_POST['templateeditormode']);
		    //$uresult = $connect->Execute($uquery)  or safe_die ($isrquery."<br />".$connect->ErrorMsg());  // Checked
		    $uresult = $this->users_model->update($this->session->userdata("loginID"),$data);
		    $this->session->set_userdata('adminlang', $_POST['lang']);
		    $this->session->set_userdata('htmleditormode', $_POST['htmleditormode']);
		    $this->session->set_userdata('questionselectormode', $_POST['questionselectormode']);
		    $this->session->set_userdata('templateeditormode', $_POST['templateeditormode']);
		    $this->session->set_userdata('dateformat', $_POST['dateformat']);
		    $this->session->set_userdata('flashmessage', $clang->gT("Your personal settings were successfully saved."));
		}
		//$sSavedLanguage=$connect->GetOne("select lang from ".db_table_name('users')." where uid={$_SESSION['loginID']}");
		$query = $this->users_model->getSomeRecords(array("lang"),array("uid"=>$this->session->userdata("loginID")));
		$data['sSavedLanguage']=reset($query->row_array());
		
		$data['clang']=$clang;
		
	    self::_getAdminHeader();
		self::_showadminmenu();
		$this->load->view("admin/User/personalsettings",$data);
		self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
	}

	function _getUserNameFromUid($uid){
	    $query = "SELECT users_name, uid FROM ".$this->db->dbprefix('users')." WHERE uid = $uid;";
	
	    $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	
	    
	    if($result->num_rows() > 0) {
	        foreach($result->row_array() as $rows){
	            return $rows['users_name'];
	        }
	    }
	}
	
	function _refreshtemplates() {
	    $template_a = gettemplatelist();
		foreach ($template_a as $tp=>$fullpath) {
	        // check for each folder if there is already an entry in the database
	        // if not create it with current user as creator (user with rights "create user" can assign template rights)
	        $query = "SELECT * FROM ".$this->db->dbprefix('templates')." WHERE folder LIKE '".$tp."'";
	        $result = db_execute_assoc($query); //Checked
	
	        if ($result->num_rows() == 0) {
	            $query2 = "INSERT INTO ".$this->db->dbprefix('templates')." (".$this->db->escape('folder').",".$this->db->escape('creator').") VALUES ('".$tp."', ".$this->session->userdata('loginID').')' ;
	            db_execute_assoc($query2); //Checked
	        }
	    }
	    return true;
	}
}