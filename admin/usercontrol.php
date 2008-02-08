<?php
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
*/

if (isset($_REQUEST['homedir'])) {die('You cannot start this script directly');}
include_once("login_check.php");  //Login Check dies also if the script is started directly
require_once($homedir."/classes/core/sha256.php");

if (isset($_POST['user'])) {$postuser=sanitize_user($_POST['user']);}
if (isset($_POST['email'])) {$postemail=sanitize_email($_POST['email']);}
if (isset($_POST['loginlang'])) {$postloginlang=sanitize_languagecode($_POST['loginlang']);}
if (isset($_POST['new_user'])) {$postnew_user=sanitize_user($_POST['new_user']);}
if (isset($_POST['new_email'])) {$postnew_email=sanitize_email($_POST['new_email']);}
if (isset($_POST['new_full_name'])) {$postnew_full_name=sanitize_userfullname($_POST['new_full_name']);}
if (isset($_POST['uid'])) {$postuid=sanitize_int($_POST['uid']);}
if (isset($_POST['full_name'])) {$postfull_name=sanitize_userfullname($_POST['full_name']);}



if (!isset($_SESSION['loginID']))
{
	if($action == "forgotpass")
	{
		$loginsummary = "<br /><strong>".$clang->gT("Forgot Password")."</strong><br />\n";

		if (isset($postuser) && isset($postemail))
		{
			include("database.php");
			$emailaddr = $postemail;
			$query = "SELECT users_name, password, uid FROM ".db_table_name('users')." WHERE users_name=".$connect->qstr($postuser)." AND email=".$connect->qstr($emailaddr);
			$result = db_select_limit_assoc($query, 1) or die ($query."<br />".$connect->ErrorMsg());

			if ($result->RecordCount() < 1)
			{
				// wrong or unknown username and/or email
				$loginsummary .= "<br />".$clang->gT("User name and/or email not found!")."<br />";
				$loginsummary .= "<br /><br /><a href='$scriptname?action=forgotpassword'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
			}
			else
			{
				$fields = $result->FetchRow();

				// send Mail
				$new_pass = createPassword();
				$body = $clang->gT("Your data:") . "<br />\n";;
				$body .= $clang->gT("Username") . ": " . $fields['users_name'] . "<br />\n";
				$body .= $clang->gT("New Password") . ": " . $new_pass . "<br />\n";

				$subject = 'User Data';
				$to = $emailaddr;
				$from = $siteadminemail;
				$sitename = $siteadminname;

				if(MailTextMessage($body, $subject, $to, $from, $sitename, false,$siteadminbounce))
				{
					$query = "UPDATE ".db_table_name('users')." SET password='".SHA256::hash($new_pass)."' WHERE uid={$fields['uid']}";
					$connect->Execute($query);
					$loginsummary .= "<br />".$clang->gT("Username").": {$fields['users_name']}<br />".$clang->gT("Email").": {$emailaddr}<br />";
					$loginsummary .= "<br />".$clang->gT("An email with your login data was sent to you.");
					$loginsummary .= "<br /><br /><a href='$scriptname'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
				}
				else
				{
					$tmp = str_replace("{NAME}", "<strong>".$fields['users_name']."</strong>", $clang->gT("Email to {NAME} ({EMAIL}) failed."));
					$loginsummary .= "<br />".str_replace("{EMAIL}", $emailaddr, $tmp) . "<br />";
					$loginsummary .= "<br /><br /><a href='$scriptname?action=forgotpassword'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
				}
			}
		}
	}
	elseif($action == "login")	// normal login
	{
		$loginsummary = "<br /><strong>".$clang->gT("Logging in...")."</strong><br />\n";

		if (isset($postuser) && isset($_POST['password']))
		{
			include("database.php");
			$query = "SELECT uid, users_name, password, parent_id, email, lang, htmleditormode FROM ".db_table_name('users')." WHERE users_name=".$connect->qstr($postuser);
			$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
			$result = $connect->SelectLimit($query, 1) or die ($query."<br />".$connect->ErrorMsg());
			if ($result->RecordCount() < 1)
			{
				// wrong or unknown username 
				$loginsummary .= "<br />".$clang->gT("Incorrect User name and/or Password!")."<br />";
				$loginsummary .= "<br /><br /><a href='$scriptname'>".$clang->gT("Continue")."</a><br />&nbsp;\n";

			}
			else
			{
				$fields = $result->FetchRow();
				if (SHA256::hash($_POST['password']) == $fields['password'])
				{
					// Anmeldung ERFOLGREICH
					if (strtolower($_POST['password'])=='password')
					{
						$_SESSION['pw_notify']=true;
					}
					else
					{
						$_SESSION['pw_notify']=false;
					} // Check if the user has changed his default password

					$_SESSION['loginID'] = intval($fields['uid']);
					$_SESSION['user'] = $fields['users_name'];
					$_SESSION['htmleditormode'] = $fields['htmleditormode'];
					// Compute a checksession random number to test POSTs
					$_SESSION['checksessionpost'] = randomkey(10);
					if (isset($postloginlang) && $postloginlang)
					{
						$_SESSION['adminlang'] = $postloginlang;
						$clang = new limesurvey_lang($_SESSION['adminlang']);
						$uquery = "UPDATE {$dbprefix}users "
						. "SET lang='{$_SESSION['adminlang']}' "
						. "WHERE uid={$_SESSION['loginID']}";
						$uresult = $connect->Execute($uquery);
					}
					else
					{
						$_SESSION['adminlang'] = $fields['lang'];
						$clang = new limesurvey_lang($_SESSION['adminlang']);
					}
					$login = true;

					$loginsummary .= "<br />" .str_replace("{NAME}", $_SESSION['user'], $clang->gT("Welcome {NAME}")) . "<br />";
					$loginsummary .= $clang->gT("You logged in successfully.");

					if (isset($_POST['refererargs']) && $_POST['refererargs'] &&
						strpos($_POST['refererargs'], "action=logout") === FALSE)
					{
						$_SESSION['metaHeader']="<meta http-equiv=\"refresh\""
						. " content=\"1;URL={$scriptname}?".$_POST['refererargs']."\" />";
						$loginsummary .= "<br /><font size='1'><i>".$clang->gT("Reloading Screen. Please wait.")."</i></font>\n";
					}
					$loginsummary .= "<br /><br />\n";
					GetSessionUserRights($_SESSION['loginID']);
				}
				else
				{
					$loginsummary .= "<br />".$clang->gT("Incorrect User name and/or Password!")."<br />";
					$loginsummary .= "<br /><br /><a href='$scriptname'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
				}
			}
		}
	}
	elseif($useWebserverAuth === true && !isset($_SERVER['PHP_AUTH_USER']))	// LimeSurvey expects webserver auth  but it has not been achieved
	{
		$loginsummary .= "<br />".$clang->gT("LimeSurvey is setup to use the webserver authentication, but it seems you have not already been authenticated")."<br />";
		$loginsummary .= "<br /><br />".$clang->gT("Please contact your system administrator")."<br />&nbsp;\n";
	}
	elseif($useWebserverAuth === true && isset($_SERVER['PHP_AUTH_USER']))	// normal login through webserver authentication
	{
		$action = 'login';
		// we'll include database.php
		// we need to unset surveyid
		// that could be set if the user clicked on
		// a link with all params before first auto-login
		unset($surveyid);

		$loginsummary = "<br /><strong>".$clang->gT("Logging in...")."</strong><br />\n";
		// getting user name, optionnally mapped
		if (isset($userArrayMap) && is_array($userArrayMap) &&
			isset($userArrayMap[$_SERVER['PHP_AUTH_USER']]))
		{
			$mappeduser=$userArrayMap[$_SERVER['PHP_AUTH_USER']];
		}
		else
		{
			$mappeduser=$_SERVER['PHP_AUTH_USER'];
		}

		include("database.php");
		$query = "SELECT uid, users_name, password, parent_id, email, lang, htmleditormode FROM ".db_table_name('users')." WHERE users_name=".$connect->qstr($mappeduser);
		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$result = $connect->SelectLimit($query, 1) or die ($query."<br />".$connect->ErrorMsg());
		if ($result->RecordCount() < 1)
		{
			// wrong or unknown username 
			$loginsummary .= "<br />".$clang->gT("Incorrect User name and/or Password!")."<br />";
			$loginsummary .= "<br /><br /><a href='$scriptname'>".$clang->gT("Continue")."</a><br />&nbsp;\n";

		}
		else
		{ // user exists and was authenticated by webserver
			$fields = $result->FetchRow();

			$_SESSION['loginID'] = intval($fields['uid']);
			$_SESSION['user'] = $fields['users_name'];
			$_SESSION['adminlang'] = $fields['lang'];
			$_SESSION['htmleditormode'] = $fields['htmleditormode'];
			$_SESSION['checksessionpost'] = randomkey(10);
			$_SESSION['pw_notify']=false;
			$clang = new limesurvey_lang($_SESSION['adminlang']);
			$login = true;

			$loginsummary .= "<br />" .str_replace("{NAME}", $_SESSION['user'], $clang->gT("Welcome {NAME}")) . "<br />";
			$loginsummary .= $clang->gT("You logged in successfully.");

			if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] &&
				strpos($_SERVER['QUERY_STRING'], "action=logout") === FALSE)
			{
				$_SESSION['metaHeader']="<meta http-equiv=\"refresh\""
				. " content=\"1;URL={$scriptname}?".$_SERVER['QUERY_STRING']."\" />";
				$loginsummary .= "<br /><font size='1'><i>".$clang->gT("Reloading Screen. Please wait.")."</i></font>\n";
			}
			$loginsummary .= "<br /><br />\n";
			GetSessionUserRights($_SESSION['loginID']);
		}
	}
}
elseif ($action == "logout")
{
//	$logoutsummary = "<br /><strong>".$clang->gT("Logout")."</strong><br />\n";

	killSession();

	$logoutsummary = $clang->gT("Logout successful.");
//	$logoutsummary .= "<br /><br /><a href='$scriptname'>".$clang->gT("Main Admin Screen")."</a><br />&nbsp;\n";
}

elseif ($action == "adduser" && $_SESSION['USER_RIGHT_CREATE_USER'])
{
	$addsummary = "<br /><strong>".$clang->gT("Add User")."</strong><br />\n";

	$new_user = html_entity_decode($postnew_user);
	$new_email = html_entity_decode($postnew_email);
	$new_full_name = html_entity_decode($postnew_full_name);
	$new_user = $postnew_user; // TODO: check if html decode should be used here
	$new_email = $postnew_email; // TODO: check if html decode should be used here
	$new_full_name = html_entity_decode($postnew_full_name);
	$valid_email = true;

	if(!validate_email($new_email))
	{
		$valid_email = false;
		$addsummary .= "<br /><strong>".$clang->gT("Failed to add User.")."</strong><br />\n" . " " . $clang->gT("Email address is not valid.")."<br />\n";
	}
	if(empty($new_user))
	{
		if($valid_email) $addsummary .= "<br /><strong>".$clang->gT("Failed to add User.")."</strong><br />\n" . " ";
		$addsummary .= $clang->gT("Username was not supplied.")."<br />\n";
	}
	elseif($valid_email)
	{
		$new_pass = createPassword();
		$uquery = "INSERT INTO {$dbprefix}users (users_name, password,full_name,parent_id,lang,email,create_survey,create_user,delete_user,superadmin,configurator,manage_template,manage_label) VALUES ('".db_quote($new_user)."', '".SHA256::hash($new_pass)."', '".db_quote($new_full_name)."', {$_SESSION['loginID']}, '{$defaultlang}', '".db_quote($new_email)."',0,0,0,0,0,0,0)";
		//error_log("TIBO=$uquery");
		$uresult = $connect->Execute($uquery);

		if($uresult)
		{
			$newqid = $connect->Insert_ID("{$dbprefix}users","uid");

			// add default template to template rights for user
			$template_query = "INSERT INTO {$dbprefix}templates_rights (uid, folder, use) VALUES('$newqid','default','1')";
			$connect->Execute($template_query);
			
			// add new user to userlist
			$squery = "SELECT uid, users_name, password, parent_id, email, create_survey, configurator, create_user, delete_user, superadmin, manage_template, manage_label FROM ".db_table_name('users')." WHERE uid='{$newqid}'";			//added by Dennis
			$sresult = db_execute_assoc($squery);
			$srow = $sresult->FetchRow();
			$userlist = getuserlist();
			array_push($userlist, array("user"=>$srow['users_name'], "uid"=>$srow['uid'], "email"=>$srow['email'],
			"password"=>$srow["password"], "parent_id"=>$srow['parent_id'], // "level"=>$level,
			"create_survey"=>$srow['create_survey'], "configurator"=>$srow['configurator'], "create_user"=>$srow['create_user'],
			"delete_user"=>$srow['delete_user'], "superadmin"=>$srow['superadmin'], "manage_template"=>$srow['manage_template'],
			"manage_label"=>$srow['manage_label']));

			// send Mail
			$body = $clang->gT("You were signed in on the site")." ".$sitename."<br />\n";
			$body .= $clang->gT("Your data:")."<br />\n";
			$body .= $clang->gT("Username") . ": " . $new_user . "<br />\n";
			if ($useWebserverAuth === false)
			{ // authent is not delegated to web server
				// send password otherwise do not
				$body .= $clang->gT("Password") . ": " . $new_pass . "<br />\n";
			}

			$body .= "<a href='" . $homeurl . "/admin.php'>".$clang->gT("Login here")."</a><br />\n";

			$subject = 'Registration';
			$to = $new_email;
			$from = $siteadminemail;
			$sitename = $siteadminname;

			if(MailTextMessage($body, $subject, $to, $from, $sitename, true, $siteadminbounce))
			{
				$addsummary .= "<br />".$clang->gT("Username").": $new_user<br />".$clang->gT("Email").": $new_email<br />";
				$addsummary .= "<br />".$clang->gT("An email with a generated password was sent to the user.");
			}
			else
			{
				// Muss noch mal gesendet werden oder andere M??glichkeit
				$tmp = str_replace("{NAME}", "<strong>".$new_user."</strong>", $clang->gT("Email to {NAME} ({EMAIL}) failed."));
				$addsummary .= "<br />".str_replace("{EMAIL}", $new_email, $tmp) . "<br />";
			}

			$addsummary .= "<br />\t\t\t<form method='post' action='$scriptname'>"
			."<input type='submit' value='".$clang->gT("Set User Rights")."'>"
			."<input type='hidden' name='action' value='setuserrights'>"
			."<input type='hidden' name='user' value='{$new_user}'>"
			."<input type='hidden' name='uid' value='{$newqid}'>"
			."</form>";
		}
		else{
			$addsummary .= "<br /><strong>".$clang->gT("Failed to add User.")."</strong><br />\n" . " " . $clang->gT("Username and/or email address already exists.")."<br />\n";
		}
	}
	$addsummary .= "<br /><a href='$scriptname?action=editusers'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
}

elseif ($action == "deluser" && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $_SESSION['USER_RIGHT_DELETE_USER'] || ($postuid == $_SESSION['loginID'])))
{
	$addsummary = "<br /><strong>".$clang->gT("Deleting User")."</strong><br />\n";

	// CAN'T DELETE ORIGINAL SUPERADMIN
	// Initial SuperAdmin has parent_id == 0
	$adminquery = "SELECT uid FROM {$dbprefix}users WHERE parent_id=0";
	$adminresult = db_select_limit_assoc($adminquery, 1);
	$row=$adminresult->FetchRow();

	if($row['uid'] == $postuid)	// it's the original superadmin !!!
	{
		$addsummary .= "<br />".$clang->gT("Initial Superadmin cannot be deleted!")."<br />\n";
	}
	else
	{
		if (isset($postuid))
		{
			// is the user allowed to delete?
//			$userlist = getuserlist();
//			foreach ($userlist as $usr)
//			{
//				if ($usr['uid'] == $postuid)
//				{
//					$isallowed = true;
//					continue;
//				}
//			}

			$sresultcount = 0;// 1 if I am parent of $postuserid
			if ($_SESSION['USER_RIGHT_SUPERADMIN'] != 1)
			{
				$squery = "SELECT uid FROM {$dbprefix}users WHERE uid=$postuserid AND parent_id=".$_SESSION['loginID'];
				$sresult = $connect->Execute($squery);
				$sresultcount = $sresult->RecordCount();
			}
	
			if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sresultcount > 0)
				{
				// We are about to kill an uid with potential childs
				// Let's re-assign them their grand-father as their
				// new parentid
				$squery = "SELECT parent_id FROM {$dbprefix}users WHERE uid=".db_quote($postuid);
				$sresult = $connect->Execute($squery);
				$fields = $sresult->FetchRow($sresult);

				if (isset($fields[0]))
				{
					$uquery = "UPDATE ".db_table_name('users')." SET parent_id={$fields[0]} WHERE parent_id=".db_quote($postuid);	//		added by Dennis
					$uresult = $connect->Execute($uquery);
				}

				//DELETE USER FROM TABLE
				$dquery="DELETE FROM {$dbprefix}users WHERE uid=".db_quote($postuid);	//	added by Dennis
				$dresult=$connect->Execute($dquery);

				// Delete user rights
				$dquery="DELETE FROM {$dbprefix}surveys_rights WHERE uid=".db_quote($postuid);
				$dresult=$connect->Execute($dquery);

				if($postuid == $_SESSION['loginID']) killSession();	// user deleted himself

				$addsummary .= "<br />".$clang->gT("Username").": {$postuser}<br />\n";
			}
			else
			{
				include("access_denied.php");
			}
		}
		else
		{
			$addsummary .= "<br />".$clang->gT("Could not delete user. User was not supplied.")."<br />\n";
		}
	}
	$addsummary .= "<br /><br /><a href='$scriptname?action=editusers'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
}

elseif ($action == "moduser")
{
	
	$addsummary = "<br /><strong>".$clang->gT("Modifying User")."</strong><br />\n";

	$squery = "SELECT uid FROM {$dbprefix}users WHERE uid=$postuserid AND parent_id=".$_SESSION['loginID'];
	$sresult = $connect->Execute($squery);
	$sresultcount = $sresult->RecordCount();


	//$userlist = getuserlist();
	//foreach ($userlist as $usr)
	//{
	//	if ($usr['uid'] == $postuid)
	//	{
	//			$squery = "SELECT create_survey, configurator, create_user, delete_user, superadmin, manage_template, manage_label FROM {$dbprefix}users WHERE uid={$usr['parent_id']}";	//		added by Dennis
	//			$sresult = $connect->Execute($squery);
	//			$parent = $sresult->FetchRow();
	//			break;
	//	}
	//}
//	if($postuid == $_SESSION['loginID'] || $_SESSION['loginID'] == 1 || $parent['create_user'] == 1)

	if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $postuid == $_SESSION['loginID'] ||
		($sresultcount > 0 && $_SESSION['USER_RIGHT_CREATE_USER'])
	  )
	{
		$users_name = html_entity_decode($postuser);
		$email = html_entity_decode($postemail);
		$pass = html_entity_decode($_POST['pass']);
		$full_name = html_entity_decode($postfull_name);
		$valid_email = true;

		if(!validate_email($email))
		{
			$valid_email = false;
			$failed = true;
			$addsummary .= "<br /><strong>".$clang->gT("Could not modify User Data.")."</strong><br />\n" . " ".$clang->gT("Email address ist not valid.")."<br />\n";
		}
		elseif($valid_email)
		{
			$failed = false;
			if(empty($pass))
			{
				$uquery = "UPDATE ".db_table_name('users')." SET email='".db_quote($email)."', full_name='".db_quote($full_name)."' WHERE uid=".db_quote($postuid);
			} else {
				$uquery = "UPDATE ".db_table_name('users')." SET email='".db_quote($email)."', full_name='".db_quote($full_name)."', password='".SHA256::hash($pass)."' WHERE uid=".db_quote($postuid);
			}
			
			$uresult = $connect->Execute($uquery);

			if($uresult && empty($pass))
			{
				$addsummary .= "<br />".$clang->gT("Username").": $users_name<br />".$clang->gT("Password").": {".$clang->gT("Unchanged")."}<br />\n";
			} elseif($uresult && !empty($pass))
			{
				$addsummary .= "<br />".$clang->gT("Username").": $users_name<br />".$clang->gT("Password").": $pass<br />\n";
			}
			else
			{
				// Username and/or email adress already exists.
				$addsummary .= "<br /><strong>".$clang->gT("Could not modify User Data.")."</strong><br />\n" . " ".$clang->gT("Email address already exists.")."<br />\n";
			}
		}
		if($failed)
		{
			$addsummary .= "<br /><br /><form method='post' action='$scriptname'>"
			."<input type='submit' value='".$clang->gT("Back")."'>"
			."<input type='hidden' name='action' value='modifyuser'>"
			."<input type='hidden' name='uid' value='{$postuid}'>"
			."</form>";
		}
		else
		{
			$addsummary .= "<br /><br /><a href='$scriptname?action=editusers'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
	}
	else
	{
		include("access_denied.php");
	}
}

elseif ($action == "userrights")
{
	$addsummary = "<br /><strong>".$clang->gT("Set User Rights")."</strong><br />\n";

	// A user can't modify his own rights ;-)
	if($postuid != $_SESSION['loginID'])
	{
		// DON'T DO THAT CAUS getuserlist returns
		// ALL USERS
		// (or all users from same groups depeding on policy)
		// IT DOESN'T ONLY SHOWS CHILDS !!!
		//$userlist = getuserlist();
		//foreach ($userlist as $usr)
		//{
		//	if ($usr['uid'] == $postuid)
		//	{
		//		$isallowed = true;
		//		continue;
		//	}
		//}
		$squery = "SELECT uid FROM {$dbprefix}users WHERE uid=$postuserid AND parent_id=".$_SESSION['loginID'];
		$sresult = $connect->Execute($squery);
		$sresultcount = $sresult->RecordCount();

		if($_SESSION['USER_RIGHT_SUPERADMIN'] != 1 && $sresultcount > 0)
		{ // Not Admin, just a user with childs
			$rights = array();

			// Forbids Allowing more privileges than I have
			if(isset($_POST['create_survey']) && $_SESSION['USER_RIGHT_CREATE_SURVEY'])$rights['create_survey']=1;		else $rights['create_survey']=0;
			if(isset($_POST['configurator']) && $_SESSION['USER_RIGHT_CONFIGURATOR'])$rights['configurator']=1;			else $rights['configurator']=0;
			if(isset($_POST['create_user']) && $_SESSION['USER_RIGHT_CREATE_USER'])$rights['create_user']=1;			else $rights['create_user']=0;
			if(isset($_POST['delete_user']) && $_SESSION['USER_RIGHT_DELETE_USER'])$rights['delete_user']=1;			else $rights['delete_user']=0;

			$rights['superadmin']=0; // ONLY Initial Superadmin can give this right
			if(isset($_POST['manage_template']) && $_SESSION['USER_RIGHT_MANAGE_TEMPLATE'])$rights['manage_template']=1;	else $rights['manage_template']=0;
			if(isset($_POST['manage_label']) && $_SESSION['USER_RIGHT_MANAGE_LABEL'])$rights['manage_label']=1;			else $rights['manage_label']=0;

			setuserrights($postuid, $rights);
			$addsummary .= "<br />".$clang->gT("Update user rights successful.")."<br />\n";
			$addsummary .= "<br /><br /><a href='$scriptname?action=editusers'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
		elseif ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
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
				$adminquery = "SELECT uid FROM {$dbprefix}users WHERE parent_id=0";
				$adminresult = db_select_limit_assoc($adminquery, 1);
				$row=$adminresult->FetchRow();
			
				if($row['uid'] == $_SESSION['loginID'])	// it's the original superadmin !!!
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

			setuserrights($postuid, $rights);
			$addsummary .= "<br />".$clang->gT("Update user rights successful.")."<br />\n";
			$addsummary .= "<br /><br /><a href='$scriptname?action=editusers'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
		else
		{
			include("access_denied.php");
		}
	}
	else
	{
		$addsummary .= "<br />".$clang->gT("You are not allowed to change your own rights!")."<br />\n";
		$addsummary .= "<br /><br /><a href='$scriptname?action=editusers'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
	}
}

elseif ($action == "usertemplates")
{
      $addsummary = "<br /><strong>".$clang->gT("Set Template Rights")."</strong><br />\n";

	// SUPERADMINS AND MANAGE_TEMPLATE USERS CAN SET THESE RIGHTS
      if( $_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $_SESSION['USER_RIGHT_MANAGE_TEMPLATE'] == 1)
      {
                      $templaterights = array();
                      $tquery = "SELECT * FROM ".$dbprefix."templates";
                      $tresult = mysql_query($tquery);
                      while ($trow = mysql_fetch_assoc($tresult)) {
                              if (isset($_POST[$trow["folder"]."_use"]))
                                      $templaterights[$trow["folder"]] = 1;
                              else
                                      $templaterights[$trow["folder"]] = 0;
                      }
                      echo "<!-- \n";
                      foreach ($templaterights as $key => $value) {
                              $uquery = "INSERT INTO {$dbprefix}templates_rights SET `uid`=".$_POST['uid'].", `folder`='".$key."', `use`=".$value." ON DUPLICATE KEY UPDATE `use`=".$value;
                              echo $uquery."\n";
                              $uresult = mysql_query($uquery);
                      }
                      echo "--> \n";
                      $addsummary .= "<br />".$clang->gT("Update usertemplates successful.")."<br />\n";
                      $addsummary .= "<br /><br /><a href='$scriptname?action=editusers'>".$clang->gT("Continue")."</a><br />&nbsp;\n";

              }
              else
              {
                      include("access_denied.php");
              }
}

function randomkey($length)
{
	$pattern = "1234567890abcdefghijklmnpqrstuvwxyz";
	$patternlength = strlen($pattern)-1; 
	for($i=0;$i<$length;$i++)
	{
		if(isset($key))
		$key .= $pattern{rand(0,$patternlength)};
		else
		$key = $pattern{rand(0,$patternlength)};
	}
	return $key;
}

?>
