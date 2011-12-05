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
* $Id: user.php 11221 2011-10-19 20:47:40Z tmswhite $
*yii
*/

/**
* User Controller
*
* This controller performs user actions
*
* @package		LimeSurvey
* @subpackage	Backend
*/
class UserAction extends Survey_Common_Action {
	
	/**
	* Constructor
	*/
	public function run()
	{
		Yii::app()->loadHelper('database');
		if (isset($_GET['editusers']))
			$this->editusers();
		elseif (isset($_GET['adduser']))
			$this->adduser();
		elseif (isset($_GET['deluser']))
			$this->deluser();
		elseif (isset($_GET['modifyuser']))
			$this->modifyuser();
		elseif (isset($_GET['moduser']))
			$this->moduser();
		elseif (isset($_GET['setuserrights']))
			$this->setuserrights();
		elseif (isset($_GET['userrights']))
			$this->userrights();
		elseif (isset($_GET['setusertemplates']))
			$this->setusertemplates();
		elseif (isset($_GET['usertemplates']))
			$this->usertemplates();
		elseif (isset($_GET['personalsettings']))
			$this->personalsettings();
		else
			$this->index();
    }
	function index() {
		echo 'sfl';
	}
    /**
    * Show users table
    */
    function editusers()
    {
        $this->getController()->_js_admin_includes(Yii::app()->baseUrl.'scripts/jquery/jquery.tablesorter.min.js');
        $this->getController()->_js_admin_includes(Yii::app()->baseUrl.'scripts/admin/users.js');

        $userlist = getuserlist();
        $ui = count($userlist);
        $usrhimself = $userlist[0];
        unset($userlist[0]);

        if(Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1)
        {
		$query=Survey::model()->getSomeRecords(array("count(*)"),array("owner_id"=>$usrhimself['uid']));
		$noofsurveys=count($query);
		$noofsurveys=$noofsurveys["count(*)"];
		$data['noofsurveys'] = $noofsurveys;
        }

        if(isset($usrhimself['parent_id']) && $usrhimself['parent_id']!=0) {
		$uresult = User::model()->getSomeRecords(array("users_name"),array("uid"=>$usrhimself['parent_id']));
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
		$query=Survey::model()->getSomeRecords(array("count(*)"),array("owner_id"=>$usr_arr[$i]['uid']));		
	$noofsurveyslist[$i]=$query;
		if (isset($noofsurveyslist[$i]["count(*)"])) {
		   $r = $noofsurveyslist[$i]["count(*)"];
		   }else{
		   $r = FALSE;
		   }
		$noofsurveyslist[$i]= $r;
        }


        $data['clang']=Yii::app()->lang;
        $data['imageurl']=Yii::app()->getConfig("imageurl");
        $data['noofsurveyslist']=$noofsurveyslist;

        $clang = Yii::app()->lang;
		$this->getController()->_getAdminHeader();
		$this->getController()->_showadminmenu();
        $this->getController()->render("/admin/user/editusers",$data);
        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));

    }

    function adduser()
    {
        $clang=Yii::app()->lang;
        if (!Yii::app()->session['USER_RIGHT_CREATE_USER']) {
		show_error("No permissions");
		exit;
        }

        $new_user = FlattenText($this->_post('new_user'),false,true);
        $new_email = FlattenText($this->_post('new_email'),false,true);
        $new_full_name = FlattenText($this->_post('new_full_name'),false,true);

		$this->getController()->_getAdminHeader();
		$this->getController()->_showadminmenu();
        $valid_email = true;
        if(!validate_email($new_email))
        {
		$valid_email = false;
		$this->getController()->_showMessageBox($clang->gT("Failed to add user"),"<br />\n" . " " . $clang->gT("The email address is not valid.")."<br />\n",$class='warningheader');
        }
        if(empty($new_user))
        {
		$this->getController()->_showMessageBox($clang->gT("Failed to add user"),"<br />\n" . " " . $clang->gT("A username was not supplied or the username is invalid.")."<br />\n",$class='warningheader');
        }
        elseif($valid_email)
        {
		$new_pass = createPassword();
		$uresult = User::model()->insert($new_user, $new_pass,$new_full_name,Yii::app()->session['loginID'],$new_email);

		if($uresult)
		{
			// add default template to template rights for user
		    $newqid = Yii::app()->db->getLastInsertID();
			Templates_rights::model()->insertRecords(array('uid' => $newqid, 'folder' => 'default', 'use' => '1'));

		    // add new user to userlist
				$sresult = User::model()->getAllRecords(array('uid' => $newqid));
				$srow= count($sresult);
				
		    $userlist = getuserlist();
		    array_push($userlist, array("user"=>$srow['users_name'], "uid"=>$srow['uid'], "email"=>$srow['email'],
		    "password"=>$srow["password"], "parent_id"=>$srow['parent_id'], // "level"=>$level,
		    "create_survey"=>$srow['create_survey'],"participant_panel"=>$srow['participant_panel'], "configurator"=>$srow['configurator'], "create_user"=>$srow['create_user'],
		    "delete_user"=>$srow['delete_user'], "superadmin"=>$srow['superadmin'], "manage_template"=>$srow['manage_template'],
		    "manage_label"=>$srow['manage_label']));

		    // send Mail
		    $body = sprintf($clang->gT("Hello %s,"), $new_full_name)."<br /><br />\n";
		    $body .= sprintf($clang->gT("this is an automated email to notify that a user has been created for you on the site '%s'."), Yii::app()->getConfig("sitename"))."<br /><br />\n";
		    $body .= $clang->gT("You can use now the following credentials to log into the site:")."<br />\n";
		    $body .= $clang->gT("Username") . ": " . $new_user . "<br />\n";
		    if (Yii::app()->getConfig("useWebserverAuth") === false)
		    { // authent is not delegated to web server
		        // send password (if authorized by config)
		        if (Yii::app()->getConfig("display_user_password_in_email") === true)
		        {
				$body .= $clang->gT("Password") . ": " . $new_pass . "<br />\n";
		        }
		        else
		        {
				$body .= $clang->gT("Password") . ": " . $clang->gT("Please ask your password to your LimeSurvey administrator") . "<br />\n";
		        }
		    }

		    $body .= "<a href='".$this->getController()->createUrl("admin/")."'>".$clang->gT("Click here to log in.")."</a><br /><br />\n";
		    $body .=  sprintf($clang->gT('If you have any questions regarding this mail please do not hesitate to contact the site administrator at %s. Thank you!'),Yii::app()->getConfig("siteadminemail"))."<br />\n";

		    $subject = sprintf($clang->gT("User registration at '%s'","unescaped"),Yii::app()->getConfig("sitename"));
		    $to = $new_user." <$new_email>";
		    $from = Yii::app()->getConfig("siteadminname")." <".Yii::app()->getConfig("siteadminemail").">";
		    $addsummary = "";
		    if(SendEmailMessage($body, $subject, $to, $from, Yii::app()->getConfig("sitename"), true, Yii::app()->getConfig("siteadminbounce")))
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

		    $addsummary .= "<br />\t\t\t<form method='post' action='".$this->getController()->createUrl("admin/user/setuserrights")."'>"
		    ."<input type='submit' value='".$clang->gT("Set user permissions")."'>"
		    ."<input type='hidden' name='action' value='setuserrights'>"
		    ."<input type='hidden' name='user' value='{$new_user}'>"
		    ."<input type='hidden' name='uid' value='{$newqid}'>"
		    ."</form>";
		    $this->getController()->_showMessageBox($clang->gT("Add user"),$addsummary);

		}
		else{
		    $addsummary .= "<div class='messagebox ui-corner-all'><div class='warningheader'>".$clang->gT("Failed to add user")."</div><br />\n" . " " . $clang->gT("The user name already exists.")."<br />\n";
		}
        }
        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));
    }

    /**
    * Delete user
    */
	function deluser()
	{
		$clang=Yii::app()->lang;
		if (!(Yii::app()->session['USER_RIGHT_SUPERADMIN']==1 || Yii::app()->session['USER_RIGHT_DELETE_USER'])) {
			show_error("No permissions");
			exit;
		}
		$this->getController()->_getAdminHeader();
		$this->getController()->_showadminmenu();
        $action=$this->_post("action");
        // CAN'T DELETE ORIGINAL SUPERADMIN
        // Initial SuperAdmin has parent_id == 0
		$adminresult = User::model()->getSomeRecords(array('uid'), array('parent_id' => 0));
        $row=count($adminresult);

        $postuserid = $this->_post("uid");
        $postuser = $this->_post("user");
        if($row['uid'] == $postuserid)	// it's the original superadmin !!!
        {
			$this->getController()->_showMessageBox($clang->gT("Initial Superadmin cannot be deleted!"),"","warningheader");
        }
        else
        {
		if (isset($postuserid))
		{
			$sresultcount = 0;// 1 if I am parent of $postuserid
			if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] != 1)
			{
				$sresult = User::model()->getSomeRecords(array('uid'), array('parent_id' => $postuserid, 'parent_id' => Yii::app()->session['loginID']));
					$sresultcount = count($sresult);
		    }

		    if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 || $sresultcount > 0 || $postuserid == Yii::app()->session['loginID'])
		    {
		        $transfer_surveys_to = 0;
					$result = User::model()->getSomeRecords(array('users_name','uid'));

		        $current_user = Yii::app()->session['loginID'];
		        if(count($result) == 2) {

				$action = "finaldeluser";
				foreach($result as $rows){
				    $intUid = $rows['uid'];
				    $selected = '';
				    if ($intUid == $current_user)
				        $selected = " selected='selected'";

				    if ($postuserid != $intUid)
				        $transfer_surveys_to = $intUid;
				}
		        }

				$result = Survey::model()->getSomeRecords(array('sid'), array('owner_id' => $current_user));
		        if(count($result) == 0) {
					$action = "finaldeluser";
		        }

		        if ($action=="finaldeluser")
		        {
				if (isset($_POST['transfer_surveys_to'])) {$transfer_surveys_to=sanitize_int($_POST['transfer_surveys_to']);}
				if ($transfer_surveys_to > 0){
					$model=Survey::model()->updateByPk($postuserid, array('owner_id'=>$transfer_surveys_to));
					$result = $model;
				}
				$sresult = User::model()->getSomeRecords('parent_id', array('uid'=>$postuserid));
				$fields = $sresult;
				if (isset($fields['parent_id']))
				{
							$uresult = User::model()->updateByPk(array('parent_id='=>$postuserid), array('parent_id='=>$fields['parent_id']));
				}

				//DELETE USER FROM TABLE
						$dresult=User::model()->delete('uid=' . $postuserid);

				// Delete user rights
						$dresult=Survey_permissions::model()->deleteSomeRecords(array('uid'=>$postuserid));

				if($postuserid == Yii::app()->session['loginID']) killSession();	// user deleted himself

				$addsummary = "<br />".$clang->gT("Username").": {$postuser}<br /><br />\n";
				$addsummary .= "<div class=\"successheader\">".$clang->gT("Success!")."</div>\n";
				if ($transfer_surveys_to>0){
					$user = User::model()->findByPk($transfer_surveys_to);
					$sTransferred_to = $user->users_name;
				    //$sTransferred_to = $this->getController()->_getUserNameFromUid($transfer_surveys_to);
				    $addsummary .= sprintf($clang->gT("All of the user's surveys were transferred to %s."),$sTransferred_to);
				}
				$addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".$this->getController()->createUrl('admin/user/editusers')."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
				$this->getController()->_showMessageBox("",$addsummary);
		        }
		        else
		        {
				$current_user = Yii::app()->session['loginID'];
				$addsummary = "<br />".$clang->gT("Transfer the user's surveys to: ")."\n";
				$addsummary .= "<form method='post' name='deluserform' action='".$this->createUrl("admin/user/deluser")."'><select name='transfer_surveys_to'>\n";
						$result = User::model()->getSomeRecords(array('users_name','uid'));
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
				$this->getController()->_showMessageBox("",$addsummary);
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
		    $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".$this->createUrl('admin/user/editusers')."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
		    $this->getController()->_showMessageBox("",$addsummary);
		}
        }
        $addsummary .= "</div>\n";
        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));
    }

    /**
    * Modify User
    */
    function modifyuser()
    {
		
        $postuserid=$this->_post("uid");
        if (isset($postuserid) && $postuserid)
        {
			$sresult = User::model()->getSomeRecords(array('uid'),array('uid'=>$postuserid, 'parent_id'=>Yii::app()->session['loginID']));
		$sresultcount = count($sresult);
        }
        else
        {
           // include("access_denied.php");
           // die();
        }

        // RELIABLY CHECK MY RIGHTS
        if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 || Yii::app()->session['loginID'] == $postuserid ||
        ( Yii::app()->session['USER_RIGHT_CREATE_USER'] &&
        $sresultcount > 0
        ) )
        {
			$sresult = User::model()->parentAndUser($postuserid);
			$data['mur'] = $sresult;
			//the two lines below were commented by someone before me :) don't know why they were their :)
           // $muq = "SELECT a.users_name, a.full_name, a.email, a.uid, b.users_name AS parent FROM ".$this->db->dbprefix('users')." AS a LEFT JOIN ".$this->db->dbprefix('users')." AS b ON a.parent_id = b.uid WHERE a.uid='{$postuserid}'";	//	added by Dennis
           // $data['mur'] = db_select_limit_assoc($muq, 1);

		$data['clang']=Yii::app()->lang;
		    $this->getController()->_getAdminHeader();
		    $this->getController()->_showadminmenu();
		$this->getController()->render("/admin/user/modifyuser",$data);
		$this->getController()->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));
        }
        else
        {
		include("access_denied.php");
        }
    }

    	function escape($str)
	{
		if (is_string($str))
		{
			$str = "'".$this->escape_str($str)."'";
		}
		elseif (is_bool($str))
		{
			$str = ($str === FALSE) ? 0 : 1;
		}
		elseif (is_null($str))
		{
			$str = 'NULL';
		}

		return $str;
	}
	function escape_str($str, $like = FALSE)
	{
		if (is_array($str))
		{
			foreach ($str as $key => $val)
			{
				$str[$key] = $this->escape_str($val, $like);
			}

			return $str;
		}

		// Escape single quotes
		$str = str_replace("'", "''", $this->remove_invisible_characters($str));

		return $str;
	}
	function remove_invisible_characters($str, $url_encoded = TRUE)
	{
		$non_displayables = array();

		// every control character except newline (dec 10)
		// carriage return (dec 13), and horizontal tab (dec 09)

		if ($url_encoded)
		{
			$non_displayables[] = '/%0[0-8bcef]/';	// url encoded 00-08, 11, 12, 14, 15
			$non_displayables[] = '/%1[0-9a-f]/';	// url encoded 16-31
		}

		$non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';	// 00-08, 11, 12, 14-31, 127

		do
		{
			$str = preg_replace($non_displayables, '', $str, -1, $count);
		}
		while ($count);

		return $str;
	}
    /**
    * Modify User POST
    */
    function moduser()
    {
        $clang=Yii::app()->lang;
        $postuser = $this->_post("user");
        $postemail = $this->_post("email");
        $postuserid = $this->_post("uid");
        $postfull_name = $this->_post("full_name");
        $display_user_password_in_html=Yii::app()->getConfig("display_user_password_in_html");
        $addsummary='';
		
		$sresult = User::model()->getSomeRecords(array('uid'),array('uid'=>$postuserid, 'parent_id'=>Yii::app()->session['loginID']));
        $sresultcount = count($sresult);

        if((Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 || $postuserid == Yii::app()->session['loginID'] ||
        ($sresultcount > 0 && Yii::app()->session['USER_RIGHT_CREATE_USER'])) && !(Yii::app()->getConfig("demoMode") == true && $postuserid == 1)
        )
        {
		$users_name = html_entity_decode($postuser, ENT_QUOTES, 'UTF-8');
		$email = html_entity_decode($postemail,ENT_QUOTES, 'UTF-8');
		$sPassword = html_entity_decode($this->_post('pass'),ENT_QUOTES, 'UTF-8');
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
					$uresult = User::model()->updateByPk($postuserid, array('email'=>$this->escape($email), 'full_name'=>$this->escape($full_name)));
		    } else {
					$uresult = User::model()->updateByPk($postuserid, array('email'=>$email, 'full_name'=>$full_name, 'password' => hash('sha256',$sPassword)));
		    }

		    if($uresult && empty($sPassword))
		    {
		        $addsummary .= "<br />".$clang->gT("Username").": $users_name<br />".$clang->gT("Password").": (".$clang->gT("Unchanged").")<br /><br />\n";
		        $addsummary .= "<div class=\"successheader\">".$clang->gT("Success!")."</div>\n";
		    } elseif($uresult && !empty($sPassword))
		    {
		        if ($sPassword != 'password' ) Yii::app()->session['pw_notify'] = FALSE;
		        if ($sPassword == 'password' ) Yii::app()->session['pw_notify'] = TRUE;

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
		    $addsummary .= "<br /><form method='post' action='".$this->getController()->createUrl('admin/user/modifyuser')."'>"
		    ."<input type='submit' value='".$clang->gT("Back")."'>"
		    ."<input type='hidden' name='uid' value='{$postuserid}'>"
		    ."</form>";
		}
		else
		{
		    $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".$this->getController()->createUrl('admin/user/editusers')."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
		}
        }
        else
        {
		include("access_denied.php");
        }
		$this->getController()->_getAdminHeader();
		$this->getController()->_showadminmenu();
        $this->getController()->_showMessageBox($clang->gT("Editing user"),$addsummary);
        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));

    }

    /**
    *
    */
        function _post($d) {
    		if (isset($_POST[$d])) {
    			return $_POST[$d];
    		}else{
    			return FALSE;
    		}
    	}
    function setuserrights()
    {    
        $data['clang'] = Yii::app()->lang;
        $this->getController()->_js_admin_includes(Yii::app()->baseUrl.'scripts/admin/users.js');
        $postuser = $this->_post('user');
        $postemail = $this->_post('email');
        $postuserid = $this->_post('uid');
        $postfull_name = $this->_post('full_name');
        if (isset($postuserid) && $postuserid)
        {
			$sresult = User::model()->getSomeRecords(array('uid'),array('uid'=>$postuserid, 'parent_id'=>Yii::app()->session['loginID']));
		$sresultcount = count($sresult);
        }
        else
        {
		die('Access denied1');
        }
        // RELIABLY CHECK MY RIGHTS
        if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 || ( Yii::app()->session['USER_RIGHT_CREATE_USER'] && $sresultcount >  0 && Yii::app()->session['loginID'] != $postuserid ) )
        //	if($_SESSION['loginID'] != $postuserid)
        {
		$this->getController()->_getAdminHeader();
		$this->getController()->_showadminmenu();
		$data['postuserid']=$postuserid;
		$this->getController()->render("/admin/user/setuserrights",$data);
		$this->getController()->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));

        }	// if
        else
        {
		die('Access denied2');
        }
    }
    /**
    * User Rights POST
    */
    function userrights()
    {
        $postuserid=$this->_post("uid");
        $clang=Yii::app()->lang;
        $addsummary = "<div class='header ui-widget-header'>".$clang->gT("Set user permissions")."</div>\n";
        $addsummary .= "<div class=\"messagebox\">\n";

        

        // A user can't modify his own rights ;-)
        if($postuserid != Yii::app()->session['loginID'])
        {
			$sresult = User::model()->getSomeRecords(array('uid'),array('uid'=>$postuserid, 'parent_id'=>Yii::app()->session['loginID']));
		$sresultcount = count($sresult);

		if(Yii::app()->session['USER_RIGHT_SUPERADMIN'] != 1 && $sresultcount > 0)
		{ // Not Admin, just a user with childs
		    $rights = array();

		    // Forbids Allowing more privileges than I have
		    if(isset($_POST['create_survey']) && Yii::app()->session['USER_RIGHT_CREATE_SURVEY'])$rights['create_survey']=1;		else $rights['create_survey']=0;
		    if(isset($_POST['participant_panel']) && Yii::app()->session['USER_RIGHT_PARTICIPANT_PANEL'])$rights['participant_panel']=1;	else $rights['participant_panel']=0;
		    if(isset($_POST['configurator']) && Yii::app()->session['USER_RIGHT_CONFIGURATOR'])$rights['configurator']=1;			else $rights['configurator']=0;
		    if(isset($_POST['create_user']) && Yii::app()->session['USER_RIGHT_CREATE_USER'])$rights['create_user']=1;			else $rights['create_user']=0;
		    if(isset($_POST['delete_user']) && Yii::app()->session['USER_RIGHT_DELETE_USER'])$rights['delete_user']=1;			else $rights['delete_user']=0;

		    $rights['superadmin']=0; // ONLY Initial Superadmin can give this right
		    if(isset($_POST['manage_template']) && Yii::app()->session['USER_RIGHT_MANAGE_TEMPLATE'])$rights['manage_template']=1;	else $rights['manage_template']=0;
		    if(isset($_POST['manage_label']) && Yii::app()->session['USER_RIGHT_MANAGE_LABEL'])$rights['manage_label']=1;			else $rights['manage_label']=0;

		    if ($postuserid<>1) setuserrights($postuserid, $rights);
		    $addsummary .= "<div class=\"successheader\">".$clang->gT("User permissions were updated successfully.")."</div>\n";
		    $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".$this->createUrl('admin/user/editusers')."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
		}
		elseif (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1)
		{
		    $rights = array();

		    // Only Initial Superadmin can give this right
		    if(isset($_POST['superadmin']))
		    {
		        // Am I original Superadmin ?
		        // Initial SuperAdmin has parent_id == 0
					$adminresult = User::model()->getuidfromparentid('0');
		        $row=$adminresult;
		        if($row['uid'] == Yii::app()->session['loginID'])	// it's the original superadmin !!!
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
		    $addsummary .= "<div class=\"successheader\">".$clang->gT("User permissions were updated successfully.")."</div>\n";
		    $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".$this->getController()->createUrl('admin/user/editusers')."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
		}
		else
		{
		    include("access_denied.php");
		}
        }
        else
        {
		$addsummary .= "<div class=\"warningheader\">".$clang->gT("You are not allowed to change your own permissions!")."</div>\n";
		$addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".$this->getController()->createUrl('admin/user/editusers')."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
        }
        $addsummary .= "</div>\n";
		$this->getController()->_getAdminHeader();
		$this->getController()->_showadminmenu();
        $this->getController()->_showMessageBox("",$addsummary);
        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));

    }

	function setusertemplates()
	{
		$data['clang']=Yii::app()->lang;
		$postuser = $this->_post("user");
		$postemail = $this->_post("email");
		$postuserid = $_POST["uid"];
		$postfull_name = $this->_post("full_name");
		$this->getController()->_refreshtemplates();
		$data['userlist'] = getuserlist();
		$this->getController()->_getAdminHeader();
		$this->getController()->_showadminmenu();
		$data['postuserid']=$postuserid;
		$this->load->view("admin/user/setusertemplates",$data);
		$this->getController()->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));

    }

    function usertemplates()
    {
        $postuserid=$this->_post("uid");
        $clang=Yii::app()->lang;

        
        $addsummary = "<div class='header ui-widget-header'>".$clang->gT("Set template permissions")."</div>\n";
        $addsummary .= "<div class=\"messagebox\">\n";

        // SUPERADMINS AND MANAGE_TEMPLATE USERS CAN SET THESE RIGHTS
        if( Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 || Yii::app()->session['USER_RIGHT_MANAGE_TEMPLATE'] == 1)
        {
		$templaterights = array();
			$tresult = Templates::model()->getAllRecords();
		foreach ($tresult->read() as $trow) {
		    if (isset($_POST[$trow["folder"]."_use"]))
		        $templaterights[$trow["folder"]] = 1;
		    else
		        $templaterights[$trow["folder"]] = 0;
		}
		foreach ($templaterights as $key => $value) {
				$post=new Templates_right;
				$post->uid = $postuserid;
				$post->folder = $key;
				$post->use = $value;
				$uresult = $post->save();
		    if (!$uresult)
		    {		
		    		$model=Templates_right::model()->updateByPk(array('use'=>$value), array('folder' => $key, 'uid' => $postuserid));
					$uresult = $model->save();
		    }
		}
		if ($uresult)
		{
		    $addsummary .= "<div class=\"successheader\">".$clang->gT("Template permissions were updated successfully.")."</div>\n";
		    $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".$this->createUrl('admin/user/editusers')."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
		}
		else
		{
		    $addsummary .= "<div class=\"warningheader\">".$clang->gT("Error")."</div>\n";
		    $addsummary .= "<br />".$clang->gT("Error while updating usertemplates.")."<br />\n";
		    $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".$this->createUrl('admin/user/editusers')."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
		}
        }
        else
        {
		include("access_denied.php");
        }
        $addsummary .= "</div>\n";
		$this->getController()->_getAdminHeader();
		$this->getController()->_showadminmenu();
        $this->getController()->_showMessageBox("",$addsummary);
        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));
    }

    /**
    * Manage user personal settings
    */
    function personalsettings()
    {
        $clang = Yii::app()->lang;

        if($this->_post("action"))
        {
		$data = array(	'lang' =>$this->_post('lang'), 'dateformat'=>$this->_post('dateformat'), 'htmleditormode'=>$this->_post('htmleditormode'),
		'questionselectormode'=> $this->_post('questionselectormode'), 'templateeditormode'=> $this->_post('templateeditormode'));
		$uresult = User::model()->update(Yii::app()->session['loginID'],$data);
		Yii::app()->session['adminlang'] = $this->_post('lang');
		Yii::app()->session['htmleditormode'] = $this->_post('htmleditormode');
		Yii::app()->session['questionselectormode'] =  $this->_post('questionselectormode');
        Yii::app()->session['templateeditormode'] = $this->_post('templateeditormode');
        Yii::app()->session['dateformat'] = $this->_post('dateformat');
        Yii::app()->session['flashmessage'] = $clang->gT("Your personal settings were successfully saved.");
        }
        $query = User::model()->getSomeRecords(array("lang"),array("uid"=>$this->session->userdata("loginID")));
        $data['sSavedLanguage']=reset($query->read());

        $data['clang']=$clang;

		$this->getController()->_getAdminHeader();
		$this->getController()->_showadminmenu();
        $this->load->view("admin/user/personalsettings",$data);
        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));
    }

    function _getUserNameFromUid($uid){
        $uid = sanitize_int($uid);
		$result = User::model()->getSomeRecords(array('users_name', 'uid'), array('uid' => $uid));

        if(count($result) > 0) {
		foreach($result->read() as $rows){
		    return $rows['users_name'];
		}
        }
    }

    function _refreshtemplates() {
        $template_a = gettemplatelist();
        foreach ($template_a as $tp=>$fullpath) {
		// check for each folder if there is already an entry in the database
		// if not create it with current user as creator (user with rights "create user" can assign template rights)
			$result = Templates::model()->getSomeRecords(array('folder' => $tp));
			
		if (count($result) == 0) {
 				$post=new Templates;
				$post->folder = $tp;
				$post->creator = Yii::app()->session['loginID'];
				$post->save();
		}
        }
        return true;
    }
}
