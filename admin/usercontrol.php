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
	$loginsummary = "<br /><strong>"._("Login")."</strong><br />\n";
	
	if (isset($_POST['user']) && isset($_POST['password']))
		{
		$query = "SELECT uid, user, DECODE(password, '{$codeString}') AS password, parent_id, email, lang FROM {$dbprefix}users WHERE user = '{$_POST['user']}'";
		$result = $connect->Execute($query) or die ($query."<br />".$connect->ErrorMsg());
		//echo $query;
		if ($result->RecordCount() < 1) 
			{
			// falsche bzw. unbekannte Email-Adresse
			$loginsummary .= _("Login failed!");
			//echo("Benutzer nicht gefunden!<br>");
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
				//$_SESSION['loginIP'] = $_SERVER['REMOTE_ADDR'];
				$_SESSION['user'] = $fields['user'];
				$_SESSION['adminlang'] = $fields['lang'];

				//echo SetInterfaceLanguage($_SESSION['adminlang']);
				$loginsummary .= str_replace("{NAME}", $_SESSION['user'], _("Welcome {NAME}")) . "<br />";				
				$loginsummary .= _("Login successful!");
				$loginsummary .= "<br /><br /><a href='$scriptname?action=editusers'>"._("Continue")."</a><br />&nbsp;\n";
				
				}
			else 
				{
				$loginsummary .= _("Login failed!");
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
	
	$user = html_entity_decode($_POST['user']);
	
	if ($_POST['uid'])
		{		
		/*foreach ($_SESSION['userlist'] as $usr)
			{
			if ($usr['uid'] == $_POST['uid'])
				{
				$isallowed = frue;
				continue;
				}
			}		
		
		//if(in_array($_GET['uid'], $_SESSION['userlist']['uid']))
		if($isallowed)
			{		*/
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
			
			$addsummary .= "<br />"._("Username").": $user<br />\n";
			/*}
		else
			{			
			$addsummary .= "<br />"._("You are not allowed to perform this operation!")."<br />\n";		
			//$addsummary .= "<br />"._("Not allowed to delete this User!")."<br />\n";		
			}*/
		}
	else
		{
		$addsummary .= "<br />"._("Could not delete user. Username was not supplied.")."<br />\n";
		}		
	$addsummary .= "<br /><br /><a href='$scriptname?action=editusers'>"._("Continue")."</a><br />&nbsp;\n";
	}

elseif ($action == "moduser" && $_POST['uid'] == $_SESSION['loginID'])
	{
	$addsummary = "<br /><strong>"._("Modifying User")."</strong><br />\n";
	
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
		$addsummary .= "<br /><br /><a href='$scriptname?action=modifyuser&user=$user&uid={$_POST['uid']}'>"._("Continue")."</a><br />&nbsp;\n";
		}
	else{
		$addsummary .= "<br /><br /><a href='$scriptname?action=editusers'>"._("Continue")."</a><br />&nbsp;\n";
		}
	}

function createPassword()
	{
	$pwchars = "abcdefhjmnpqrstuvwxyz23456789";
	$password_length = 8;
	$passwd = '';
	
	for ($i=0; $i<$password_length; $i++)
		{
		$passwd .= $pwchars[floor(rand(0,strlen($pwchars)))];
		}	
	return $passwd;
	}

?>
