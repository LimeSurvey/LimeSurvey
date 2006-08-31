<?php
/*
#############################################################
# >>> PHPSurveyor  										    #
#############################################################
# > Author:  Jason Cleeland									#
# > E-mail:  jason@cleeland.org								#
# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
# >          CARLTON SOUTH 3053, AUSTRALIA
# > Date: 	 20 February 2003								#
#															#
# This set of scripts allows you to develop, publish and	#
# perform data-entry on surveys.							#
#############################################################
#															#
#	Copyright (C) 2003  Jason Cleeland						#
#															#
# This program is free software; you can redistribute 		#
# it and/or modify it under the terms of the GNU General 	#
# Public License as published by the Free Software 			#
# Foundation; either version 2 of the License, or (at your 	#
# option) any later version.								#
#															#
# This program is distributed in the hope that it will be 	#
# useful, but WITHOUT ANY WARRANTY; without even the 		#
# implied warranty of MERCHANTABILITY or FITNESS FOR A 		#
# PARTICULAR PURPOSE.  See the GNU General Public License 	#
# for more details.											#
#															#
# You should have received a copy of the GNU General 		#
# Public License along with this program; if not, write to 	#
# the Free Software Foundation, Inc., 59 Temple Place - 	#
# Suite 330, Boston, MA  02111-1307, USA.					#
#############################################################
*/

if (empty($homedir)) {die("Cannot run this script directly (usercontrol)");}
if ($accesscontrol <> 1) {exit;}

if (!isset($_SESSION['loginID']))
	{
	if($action == "forgotpass")
		{
		$loginsummary = "<br /><strong>"._("Forgot Password")."</strong><br />\n";
		
		if (isset($_POST['user']) && isset($_POST['email']))
			{
			$query = "SELECT user, DECODE(password, '{$codeString}') AS password FROM {$dbprefix}users WHERE user='{$_POST['user']}' AND email='{$_POST['email']}' LIMIT 1";
			$result = $connect->Execute($query) or die ($query."<br />".$connect->ErrorMsg());
			//echo $query;
			if ($result->RecordCount() < 1) 
				{
				// wrong or unknown username and/or email
				$loginsummary .= "<br />"._("User name and/or email not found!")."<br />";
				$loginsummary .= "<br /><br /><a href='$scriptname?action=forgotpassword'>"._("Continue")."</a><br />&nbsp;\n";
				}
			else 
				{
				$fields = $result->FetchRow();
				 
				// send Mail
			
				$body = _("Your data:");
				$body .= _("Username") . ": " . $fields['user'] . "<br>\n";
				$body .= _("Password") . ": " . $fields['password'] . "<br>\n";
				
				$subject = 'User Data';
				$to = $_POST['email'];
				$from = $siteadminemail;
				$sitename = $siteadminname;
				
				if(MailTextMessage($body, $subject, $to, $from, $sitename))
					{			
					$loginsummary .= "<br />"._("Username").": {$fields['user']}<br />"._("Email").": {$_POST['email']}<br />";
					$loginsummary .= "<br />"._("An email with your login data was sent to you.");
					$loginsummary .= "<br /><br /><a href='$scriptname'>"._("Continue")."</a><br />&nbsp;\n";
					}
				else
					{
					$tmp = str_replace("{NAME}", "<strong>".$fields['user']."</strong>", _("Email to {NAME} ({EMAIL}) failed."));
					$loginsummary .= "<br />".str_replace("{EMAIL}", $_POST['email'], $tmp) . "<br />";
					$loginsummary .= "<br /><br /><a href='$scriptname?action=forgotpassword'>"._("Continue")."</a><br />&nbsp;\n";
					}
				}									
			}	
		}	
	else	// normal login
		{
		$loginsummary = "<br /><strong>"._("Login")."</strong><br />\n";
		
		if (isset($_POST['user']) && isset($_POST['password']))
			{
			$query = "SELECT uid, user, DECODE(password, '{$codeString}') AS password, parent_id, email, lang FROM {$dbprefix}users WHERE user='{$_POST['user']}' LIMIT 1";
			$result = $connect->Execute($query) or die ($query."<br />".$connect->ErrorMsg());
			//echo $query;
			if ($result->RecordCount() < 1) 
				{
				// falsche bzw. unbekannte Email-Adresse
				$loginsummary .= _("Login failed!");
				}
			else 
				{
				$fields = $result->FetchRow();
				//echo("Passwort aus der DB: " . $fields[2] . "<br>");
				//echo("eingegebenes Passwort " . $_POST['password'] . "<br>");
				if ($_POST['password'] == $fields['password']) 
					{
					// Anmeldung ERFOLGREICH
					killSession();	// clear $_SESSION
					
					$_SESSION['loginID'] = intval($fields['uid']);
					$_SESSION['user'] = $fields['user'];
					$_SESSION['adminlang'] = $fields['lang'];
	
					SetInterfaceLanguage($_SESSION['adminlang']);
					
					$loginsummary .= "<br />" .str_replace("{NAME}", $_SESSION['user'], _("Welcome {NAME}")) . "<br />";				
					$loginsummary .= _("Login successful.");
					$loginsummary .= "<br /><br /><a href='$scriptname?action=editusers'>"._("Continue")."</a><br />&nbsp;\n";
					
					}
				else 
					{
					$loginsummary .= _("Login failed!");
					}
				}	
			}
		}
	}
elseif ($action == "logout")
	{
	$logoutsummary = "<br /><strong>"._("Logout")."</strong><br />\n";
	
	killSession();
					
	$logoutsummary .= _("Logout successful.");
	$logoutsummary .= "<br /><br /><a href='$scriptname'>"._("Main Admin Screen")."</a><br />&nbsp;\n";
	}

elseif ($action == "adduser" && $_SESSION['USER_RIGHT_CREATE_USER'])
	{
	$addsummary = "<br /><strong>"._("Add User")."</strong><br />\n";
	
	$new_user = html_entity_decode($_POST['new_user']);
	$new_email = html_entity_decode($_POST['new_email']);
	$valid_email = true;
	
	if(!validate_email($new_email))	
		{
        $valid_email = false;		
		$addsummary .= "<br /><strong>"._("Failed to add User.")."</strong><br />\n" . " " . _("Email address ist not valid.")."<br />\n";     
      	}
	if(empty($new_user))
		{
		if($valid_email) $addsummary .= "<br /><strong>"._("Failed to add User.")."</strong><br />\n" . " "; 
		$addsummary .= _("Username was not supplied.")."<br />\n";
		}		
	elseif($valid_email)
		{
		echo ($new_pass = createPassword());
		$uquery = "INSERT INTO {$dbprefix}users VALUES (NULL, '$new_user', ENCODE('{$new_pass}', '{$codeString}'), {$_SESSION['loginID']}, '{$defaultlang}', '{$new_email}',0,0,0,0,0,0,0)";
		//echo($uquery);
		$uresult = $connect->Execute($uquery);
		//echo($uresult); //TODO Is this working?I don't know if you so get the affacted rows 
		
		if(mysql_affected_rows() < 0)
		//if(modify_database($uquery.";") < 0)//Has to be terminated by a semi-colon
			{
			$addsummary .= "<br /><strong>"._("Failed to add User.")."</strong><br />\n" . " " . _("Username and/or email address already exists.")."<br />\n";		
			}
		else{
			// send Mail
			
			$body = _("You were signed in. Your data:");
			$body .= _("Username") . ": " . $new_user . "<br>\n";
			$body .= _("Password") . ": " . $new_pass . "<br>\n";
			
			$subject = 'Anmeldung';
			$to = $new_email;
			$from = $siteadminemail;
			$sitename = $siteadminname;
			
			if(MailTextMessage($body, $subject, $to, $from, $sitename))
				{			
				$addsummary .= "<br />"._("Username").": $new_user<br />"._("Email").": $new_email<br />";
				$addsummary .= "<br />"._("An email with a generated password was sent to the user.");
				}
			else
				{
				// Muss noch mal gesendet werden oder andere Möglichkeit
				$tmp = str_replace("{NAME}", "<strong>".$new_user."</strong>", _("Email to {NAME} ({EMAIL}) failed."));
				$addsummary .= "<br />".str_replace("{EMAIL}", $new_email, $tmp) . "<br />";
				}
			}
		}
	$addsummary .= "<br /><br /><a href='$scriptname?action=editusers'>"._("Continue")."</a><br />&nbsp;\n";
	}

elseif ($action == "deluser" && ($_SESSION['USER_RIGHT_DELETE_USER'] || ($_POST['uid'] == $_SESSION['loginID'])))
	{	
	$addsummary = "<br /><strong>"._("Deleting User")."</strong><br />\n";
		
	$adminquery = "SELECT uid FROM {$dbprefix}users WHERE parent_id=0 LIMIT 1";
	$adminresult = $connect->Execute($adminquery);
	$row=$adminresult->FetchRow();
		
	if($row['uid'] == $_POST['uid'])	// it's the superadmin !!!
		{		
		$addsummary .= "<br />"._("Admin cannot be deleted!")."<br />\n";	
		}
	else
		{		
		if (isset($_POST['uid']))
			{		
			// is the user allowed to delete?
			foreach ($_SESSION['userlist'] as $usr)
				{
				if ($usr['uid'] == $_POST['uid'])
					{
					$isallowed = true;
					continue;
					}
				}
			
			if($isallowed)
				{
				// Wenn ein Benutzer gelöscht wird, werden die von ihm erstellten Benutzer dem Benutzer
				// zugeordnet, von dem er selbst erstellt wurde
				$squery = "SELECT parent_id FROM {$dbprefix}users WHERE uid={$_POST['uid']}";
				$sresult = $connect->Execute($squery);
				$fields = $sresult->FetchRow($sresult);
				
				$uquery = "UPDATE {$dbprefix}users SET parent_id={$fields[0]} WHERE parent_id={$_POST['uid']}";	//		added by Dennis
				$uresult = $connect->Execute($uquery);	
				
				//DELETE USER FROM TABLE
				$dquery="DELETE FROM {$dbprefix}users WHERE uid={$_POST['uid']}";	//	added by Dennis
				$dresult=$connect->Execute($dquery);
				
				if($_POST['uid'] == $_SESSION['loginID']) killSession();	// user deleted himself
				
				$addsummary .= "<br />"._("Username").": {$_POST['user']}<br />\n";								
				}
			else
				{			
				include("access_denied.php");
				//$addsummary .= "<br />"._("You are not allowed to perform this operation!")."<br />\n";			
				}
			}
		else
			{
			$addsummary .= "<br />"._("Could not delete user. User was not supplied.")."<br />\n";
			}				
		}
	$addsummary .= "<br /><br /><a href='$scriptname?action=editusers'>"._("Continue")."</a><br />&nbsp;\n";
	}

elseif ($action == "moduser")// && $_POST['uid'] == $_SESSION['loginID'])
	{
	$addsummary = "<br /><strong>"._("Modifying User")."</strong><br />\n";
		
	if($_POST['uid'] == $_SESSION['loginID'])
		{		
		$user = html_entity_decode($_POST['user']);
		$email = html_entity_decode($_POST['email']);	
		$pass = html_entity_decode($_POST['pass']);
		$valid_email = true;
		
		if(!validate_email($email))
			{
			$valid_email = false;		
			$failed = true;
			$addsummary .= "<br /><strong>"._("Could not modify User Data.")."</strong><br />\n" . " "._("Email address ist not valid.")."<br />\n";     
			}
		if(empty($pass))
			{
			$failed = true;
			if($valid_email) $addsummary .= "<br /><strong>"._("Could not modify User Data.")."</strong><br />\n";
			$addsummary .= _("Password was not supplied.");		
			}
		elseif($valid_email)
			{
			$uquery = "UPDATE {$dbprefix}users SET email='{$email}', password=ENCODE('{$pass}', '{$codeString}') WHERE uid={$_POST['uid']}";	//		added by Dennis
			//echo($uquery);
			$uresult = $connect->Execute($uquery);
			if(mysql_affected_rows() < 0)
				{
				// Username and/or email adress already exists.
				$addsummary .= "<br /><strong>"._("Could not modify User Data.")."</strong><br />\n" . " "._("Email address already exists.")."<br />\n";     
				}
			else
				{
				$addsummary .= "<br />"._("Username").": $user<br />"._("Password").": $pass<br />\n";
				}
			}		
		if($failed)
			{
			//$addsummary .= "<br /><br /><a href='$scriptname?action=modifyuser&user=$user&uid={$_POST['uid']}'>"._("Continue")."</a><br />&nbsp;\n";
			$addsummary .= "<br /><br /><form method='post' action='$scriptname'>"	// added by Dennis
						 ."<input type='submit' value='"._("Back")."'>"
						 ."<input type='hidden' name='action' value='modifyuser'>"
						 ."<input type='hidden' name='uid' value='{$_POST['uid']}'>"
						 ."</form>";
			}
		else
			{
			$addsummary .= "<br /><br /><a href='$scriptname?action=editusers'>"._("Continue")."</a><br />&nbsp;\n";
			}
		}
	else
		{			
		include("access_denied.php");
		//$addsummary .= "<br />"._("You are not allowed to perform this operation!")."<br />\n";		
		//$addsummary .= "<br /><br /><a href='$scriptname?action=editusers'>"._("Continue")."</a><br />&nbsp;\n";
		}
	}

elseif ($action == "userrights")
	{	
	$addsummary = "<br /><strong>"._("Set User Rights")."</strong><br />\n";
	
	if($_POST['uid'] != $_SESSION['loginID'])
		{
		foreach ($_SESSION['userlist'] as $usr)
			{
			if ($usr['uid'] == $_POST['uid'])
				{
				$isallowed = true;
				continue;
				}
			}
				
		if($isallowed)
			{			
			$rights = array();
			
			if(isset($_POST['create_survey']))$rights['create_survey']=1;		else $rights['create_survey']=0;
			if(isset($_POST['configurator']))$rights['configurator']=1;			else $rights['configurator']=0;
			if(isset($_POST['create_user']))$rights['create_user']=1;			else $rights['create_user']=0;
			if(isset($_POST['delete_user']))$rights['delete_user']=1;			else $rights['delete_user']=0;
			if(isset($_POST['pull_up_user']))$rights['pull_up_user']=1;			else $rights['pull_up_user']=0;
			if(isset($_POST['push_down_user']))$rights['push_down_user']=1;		else $rights['push_down_user']=0;
			if(isset($_POST['create_template']))$rights['create_template']=1;	else $rights['create_template']=0;
		
			setrights($_POST['uid'], $rights);
			$addsummary .= "<br />"._("Update user rights successful.")."<br />\n"; 						
			$addsummary .= "<br /><br /><a href='$scriptname?action=editusers'>"._("Continue")."</a><br />&nbsp;\n";
			}
		else
			{
			include("access_denied.php");
			//$addsummary .= "<br />"._("You are not allowed to perform this operation!")."<br />\n";		
			}
		}
	else
		{			
		$addsummary .= "<br />"._("You are not allowed to change your own rights!")."<br />\n";		
		$addsummary .= "<br /><br /><a href='$scriptname?action=editusers'>"._("Continue")."</a><br />&nbsp;\n";
		}
	}

?>