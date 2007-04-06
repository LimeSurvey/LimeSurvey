<?php
/*
#############################################################
# >>> PHPSurveyor  										    #
#############################################################
#															#
# This set of scripts allows you to develop, publish and	#
# perform data-entry on surveys.							#
#############################################################
#															#
#	Copyright (C) 2007  PHPSurveyor community   			#
#															#
# This program is free software; you can redistribute 		#
# it and/or modify it under the terms of the GNU General 	#
# Public License Version 2 as published by the Free         #
# Software Foundation.										#
#															#
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

//Ensure script is not run directly, avoid path disclosure
if (!isset($dbprefix)) {die ("Cannot run this script directly");}

if (($ugid && !$surveyid) || $action == "editusergroups" || $action == "addusergroup" || $action=="usergroupindb" || $action == "editusergroup" || $action == "mailusergroup")
{
	if($ugid)
	{
		$grpquery = "SELECT * FROM ".db_table_name('user_groups')." WHERE ugid = $ugid";
		$grpresult = db_execute_assoc($grpquery);
		$grow = array_map('htmlspecialchars', $grpresult->FetchRow());
	}
	$usergroupsummary = "<table width='100%' align='center' bgcolor='#DDDDDD' border='0'>\n";
	$usergroupsummary .= "\t<tr>\n"
	. "\t\t<td colspan='2'>\n"
	. "\t\t\t<table class='menubar'>\n"
	. "\t\t\t\t<tr><td colspan='2' height='4' align='left'>"
	. "<strong>".$clang->gT("User Groups")."</strong> ";
	if($ugid)
	{
		$usergroupsummary .= "<font color='silver'>{$grow['name']}</font></td></tr>\n";
	}
	else
	{
		$usergroupsummary .= "</font></td></tr>\n";
	}


	$usergroupsummary .= "\t\t\t\t<tr>\n"
	. "\t\t\t\t\t<td>\n";

	$usergroupsummary .=  "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='55' height='20' border='0' hspace='0' align='left' />\n"
	. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left' />\n";

	if($ugid)
	{
		$usergroupsummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=mailusergroup&amp;ugid=$ugid', '_top')\""
		. "onmouseout=\"hideTooltip()\""
		. "onmouseover=\"showTooltip(event,'".$clang->gT("Mail to all Members", "js")."');return false\"> " .
		"<img src='$imagefiles/invite.png' title='' align='left' alt='' name='MailUserGroup' /></a>\n" ;
	}
	$usergroupsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='135' height='20' border='0' hspace='0' align='left' />\n"
	. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left' />\n";

	if($ugid && $_SESSION['loginID'] == $grow['owner_id'])
	{
		$usergroupsummary .=  "<a href=\"#\" onclick=\"window.open('$scriptname?action=editusergroup&amp;ugid=$ugid','_top')\""
		. "onmouseout=\"hideTooltip()\""
		. "onmouseover=\"showTooltip(event,'".$clang->gT("Edit Current User Group", "js")."');return false\">" .
		"<img src='$imagefiles/edit.png' title='' alt='' name='EditUserGroup' align='left' /></a>\n" ;
	}
	else
	{
		$usergroupsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='45' height='20' border='0' hspace='0' align='left' />\n";
	}

	if($ugid && $_SESSION['loginID'] == $grow['owner_id'])
	{
		$usergroupsummary .= "\t\t\t\t\t<a href='$scriptname?action=delusergroup&amp;ugid=$ugid' onclick=\"return confirm('".$clang->gT("Are you sure you want to delete this entry.")."')\""
		. "onmouseout=\"hideTooltip()\""
		. "onmouseover=\"showTooltip(event,'".$clang->gT("Delete Current User Group", "js")."');return false\">"
		. "<img src='$imagefiles/delete.png' alt='' name='DeleteUserGroup' title='' align='left' border='0' hspace='0' /></a>";
	}
	else
	{
		$usergroupsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='43' height='20' border='0' hspace='0' align='left' />\n";
	}
	$usergroupsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='86' height='20' align='left' border='0' hspace='0' />\n"
	. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left' />\n"
	. "\t\t\t\t\t</td>\n"
	. "\t\t\t\t\t<td align='right' width='480'>\n"
	. "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' align='right' border='0' width='82' height='20' />\n"
	. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='right' border='0' hspace='0' />\n";
	
	if ($_SESSION['loginID'] == 1)
	{
		$usergroupsummary .= "<a href='$scriptname?action=addusergroup'"
		."onmouseout=\"hideTooltip()\""
		."onmouseover=\"showTooltip(event,'".$clang->gT("Add New User Group", "js")."');return false\">" .
		"<img src='$imagefiles/add.png' title='' alt='' " .
		"align='right' name='AddNewUserGroup' onclick=\"window.open('', '_top')\" /></a>\n";
	}
	$usergroupsummary .= "\t\t\t\t\t<font class=\"boxcaption\">".$clang->gT("User Groups").":</font>&nbsp;<select class=\"listboxgroups\" name='ugid' "
	. "onchange=\"window.open(this.options[this.selectedIndex].value, '_top')\">\n"
	. getusergrouplist()
	. "\t\t\t\t\t</select>\n"
	. "\t\t\t\t</td></tr>\n"
	. "\t\t\t</table>\n"
	. "\t\t</td>\n"
	. "\t</tr>\n"
	. "\n</table>\n";
}


if ($action == "adduser" || $action=="deluser" || $action == "moduser" || $action == "userrights")
{
	include("usercontrol.php");
}

if ($action == "modifyuser")
{
	$userlist = getuserlist();
	foreach ($userlist as $usr)
	{
		if ($usr['uid'] == $_POST['uid'])
		{
				$squery = "SELECT create_survey, configurator, create_user, delete_user, move_user, manage_template, manage_label FROM {$dbprefix}users WHERE uid={$usr['parent_id']}";	//		added by Dennis
				$sresult = $connect->Execute($squery);
				$parent = $sresult->FetchRow();
				break;
		}
	}
	
	if($_SESSION['loginID'] == 1 || $_SESSION['loginID'] == $_POST['uid'] || $parent['create_user'] == 1)
	{
		$usersummary = "<table width='100%' border='0'>\n\t<tr><td colspan='4' bgcolor='black' align='center'>\n"
		. "\t\t<strong><font color='white'>".$clang->gT("Modifying User")."</td></tr>\n"
		. "\t<tr>\n"
		. "\t\t<th>".$clang->gT("Username")."</th>\n"
		. "\t\t<th>".$clang->gT("Email")."</th>\n"
		. "\t\t<th>".$clang->gT("Full name")."</th>\n"
		. "\t\t<th>".$clang->gT("Password")."</th>\n"
		. "\t</tr>\n";
		$muq = "SELECT a.users_name, a.full_name, a.email, a.uid, b.users_name AS parent FROM ".db_table_name('users')." AS a LEFT JOIN ".db_table_name('users')." AS b ON a.parent_id = b.uid WHERE a.uid='{$_POST['uid']}'";	//	added by Dennis
		//echo($muq);

		$mur = db_select_limit_assoc($muq, 1);
		$usersummary .= "\t<tr><form action='$scriptname' method='post'>";
		while ($mrw = $mur->FetchRow())
		{
			$mrw = array_map('htmlspecialchars', $mrw);
			$usersummary .= "\t<td align='center'><strong>{$mrw['users_name']}</strong>\n"
			. "\t<td align='center'>\n\t\t<input type='text' name='email' value=\"{$mrw['email']}\" /></td>\n"
			. "\t<td align='center'>\n\t\t<input type='text' name='full_name' value=\"{$mrw['full_name']}\" /></td>\n"
			. "\t\t<input type='hidden' name='user' value=\"{$mrw['users_name']}\" /></td>\n"
			. "\t\t<input type='hidden' name='uid' value=\"{$mrw['uid']}\" /></td>\n";	// added by Dennis
			$usersummary .= "\t<td align='center'>\n\t\t<input type='text' name='pass' value=\"\" /></td>\n";
		}
		$usersummary .= "\t</tr>\n\t<tr><td colspan='4' align='center'>\n"
		. "\t\t<input type='submit' value='".$clang->gT("Update")."' />\n"
		. "<input type='hidden' name='action' value='moduser' /></td></tr>\n"
		. "</form></table>\n";
	}
	else
	{
		include("access_denied.php");
	}
}

if ($action == "setuserrights")
{
	if($_SESSION['loginID'] != $_POST['uid'])
	{
		$usersummary = "<table width='100%' border='0'>\n\t<tr><td colspan='8' bgcolor='black' align='center'>\n"
		. "\t\t<strong><font color='white'>".$clang->gT("Set User Rights").": ".$_POST['user']."</td></tr>\n";

		$userlist = getuserlist();
		foreach ($userlist as $usr)
		{
			if ($usr['uid'] == $_POST['uid'])
			{
				$squery = "SELECT create_survey, configurator, create_user, delete_user, move_user, manage_template, manage_label FROM {$dbprefix}users WHERE uid={$usr['parent_id']}";	//		added by Dennis
				$sresult = $connect->Execute($squery);
				$parent = $sresult->FetchRow();

				if($parent['create_survey']) {
					$usersummary .= "\t\t<th align='center'>".$clang->gT("Create Survey")."</th>\n";
				}
				if($parent['configurator']) {
					$usersummary .= "\t\t<th align='center'>".$clang->gT("Configurator")."</th>\n";
				}
				if($parent['create_user']) {
					$usersummary .= "\t\t<th align='center'>".$clang->gT("Create User")."</th>\n";
				}
				if($parent['delete_user']) {
					$usersummary .= "\t\t<th align='center'>".$clang->gT("Delete User")."</th>\n";
				}
				if($parent['move_user']) {
					$usersummary .= "\t\t<th align='center'>".$clang->gT("Move User")."</th>\n";
				}
				if($parent['manage_template']) {
					$usersummary .= "\t\t<th align='center'>".$clang->gT("Manage Template")."</th>\n";
				}
				if($parent['manage_label']) {
					$usersummary .= "\t\t<th align='center'>".$clang->gT("Manage Labels")."</th>\n";
				}

				$usersummary .="\t\t<th></th>\n\t</tr>\n"
				."\t<tr><form method='post' action='$scriptname'></tr>"
				."<form action='$scriptname' method='post'>\n";
				//content
				if($parent['create_survey']) {
					$usersummary .= "\t\t<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"create_survey\" value=\"create_survey\"";
					if($usr['create_survey']) {
						$usersummary .= " checked ";
					}
					$usersummary .=" /></td>\n";
				}
				if($parent['configurator']) {
					$usersummary .= "\t\t<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"configurator\" value=\"configurator\"";
					if($usr['configurator']) {
						$usersummary .= " checked ";
					}
					$usersummary .=" /></td>\n";
				}
				if($parent['create_user']) {
					$usersummary .= "\t\t<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"create_user\" value=\"create_user\"";
					if($usr['create_user']) {
						$usersummary .= " checked ";
					}
					$usersummary .=" /></td>\n";
				}
				if($parent['delete_user']) {
					$usersummary .= "\t\t<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"delete_user\" value=\"delete_user\"";
					if($usr['delete_user']) {
						$usersummary .= " checked ";
					}
					$usersummary .=" /></td>\n";
				}
				if($parent['move_user']) {
					$usersummary .= "\t\t<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"move_user\" value=\"move_user\"";
					if($usr['move_user']) {
						$usersummary .= " checked ";
					}
					$usersummary .=" /></td>\n";
				}
				if($parent['manage_template']) {
					$usersummary .= "\t\t<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"manage_template\" value=\"manage_template\"";
					if($usr['manage_template']) {
						$usersummary .= " checked ";
					}
					$usersummary .=" /></td>\n";
				}
				if($parent['manage_label']) {
					$usersummary .= "\t\t<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"manage_label\" value=\"manage_label\"";
					if($usr['manage_label']) {
						$usersummary .= " checked ";
					}
					$usersummary .=" /></td>\n";
				}

				$usersummary .= "\t\t\t<tr><form method='post' action='$scriptname'></tr>"	// added by Dennis
				."\t\n\t<tr><td colspan='8' align='center'>"
				."<input type='submit' value='".$clang->gT("Save Now")."' />"
				."<input type='hidden' name='action' value='userrights' />"
				."<input type='hidden' name='uid' value='{$_POST['uid']}' /></td></tr>"
				."</form>"
				. "</table>\n";
				continue;
			}	// if
		}	// foreach
	}	// if
	else
	{
		include("access_denied.php");
	}
}	// if

if($action == "setnewparents")
{
	// muss noch eingeschraenkt werden ...
	if($_SESSION['USER_RIGHT_MOVE_USER'])
	{
		$uid = $_POST['uid'];
		$newparentid = $_POST['parent'];
		$oldparent = -1;
		$query = "SELECT parent_id FROM ".db_table_name('users')." WHERE uid = ".$uid;
		$result = $connect->Execute($query) or die($connect->ErrorMsg());
		if($srow = $result->FetchRow()) {
			$oldparent = $srow['parent_id'];
		}
		$query = "SELECT create_survey, configurator, create_user, delete_user, move_user, manage_template, manage_label FROM ".db_table_name('users')." WHERE uid = ".$newparentid;
		$result = $connect->Execute($query) or die($connect->ErrorMsg());
		$srow = $result->FetchRow();
		$query = "UPDATE ".db_table_name('users')." SET parent_id = ".$newparentid.", create_survey = IF({$srow['create_survey']} = 1, create_survey, {$srow['create_survey']}), configurator = IF({$srow['configurator']} = 1, configurator, {$srow['configurator']}), create_user = IF({$srow['create_user']} = 1, create_user, {$srow['create_user']}), delete_user = IF({$srow['delete_user']} = 1, delete_user, {$srow['delete_user']}), move_user = IF({$srow['move_user']} = 1, move_user, {$srow['move_user']}), manage_template = IF({$srow['manage_template']} = 1, manage_template, {$srow['manage_template']}), manage_label = IF({$srow['manage_label']} = 1, manage_label, {$srow['manage_label']}) WHERE uid = ".$uid;
		$connect->Execute($query) or die($connect->ErrorMsg()." ".$query);
		$query = "UPDATE ".db_table_name('users')." SET parent_id = ".$oldparent." WHERE parent_id = ".$uid;
		$connect->Execute($query) or die($connect->ErrorMsg()." ".$query);
		$usersummary = "<br /><strong>".$clang->gT("Setting new Parent")."</strong><br />"
		. "<br />".$clang->gT("Set Parent successful.")."<br />"
		. "<br /><a href='$scriptname?action=editusers'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
	}
	else
	{
		include("access_denied.php");
	}
}

if ($action == "editusers")
{
	$usersummary = "<table rules='rows' width='100%'>\n"
	. "\t\t\t\t<tr bgcolor='#555555'><td colspan='6' height='4'>"
	. "<font size='1' face='verdana' color='white'><strong>".$clang->gT("User Control")."</strong></font></td></tr>\n"
	. "\t<tr>\n"
	. "\t\t<th>".$clang->gT("Username")."</th>\n"
	. "\t\t<th>".$clang->gT("Email")."</th>\n"
	. "\t\t<th>".$clang->gT("Full name")."</th>\n"
	. "\t\t<th>".$clang->gT("Password")."</th>\n"
	. "\t\t<th>".$clang->gT("Created by")."</th>\n"
	. "\t\t<th></th>\n"
	. "\t</tr>\n";

	$userlist = getuserlist();
	$ui = count($userlist);
	$usrhimself = $userlist[0];
	unset($userlist[0]);

	//	output users
	$usersummary .= "\t<tr bgcolor='#999999'>\n"
	. "\t<td align='center'><strong>{$usrhimself['user']}</strong></td>\n"
	. "\t<td align='center'><strong>{$usrhimself['email']}</strong></td>\n"
	. "\t\t<td align='center'><strong>{$usrhimself['full_name']}</strong></td>\n"
	. "\t\t<td align='center'><strong>********</strong></td>\n";
	if(isset($usrhimself['parent_id']) && $usrhimself['parent_id']!=0) {
		$usersummary .= "\t\t<td align='center'>{$userlist[$usrhimself['parent_id']]['user']}</td>\n";
	}
	else
	{
		$usersummary .= "\t\t<td align='center'><strong>---</strong></td>\n";
	}
	$usersummary .= "\t\t<td align='center' style='padding-top:10px;'>\n";
	
	if ($_SESSION['loginID'] == "1")
	{
		$usersummary .= "\t\t\t<form method='post' action='$scriptname'>"
		."<input type='submit' value='".$clang->gT("Edit User")."' />"
		."<input type='hidden' name='action' value='modifyuser' />"
		."<input type='hidden' name='uid' value='{$usrhimself['uid']}' />"
		."</form>";
	}
	// users are allowed to delete all successor users (but the admin not himself)
	if ($usrhimself['parent_id'] != 0 && ($_SESSION['USER_RIGHT_DELETE_USER'] == 1 || ($usrhimself['uid'] == $_SESSION['loginID'])))
	{
		$usersummary .= "\t\t\t<form method='post' action='$scriptname?action=deluser'>"
		."<input type='submit' value='".$clang->gT("Delete")."' onclick='return confirm(\"".$clang->gT("Are you sure you want to delete this entry.")."\")' />"
		."<input type='hidden' name='action' value='deluser' />"
		."<input type='hidden' name='user' value='{$usrhimself['user']}' />"
		."<input type='hidden' name='uid' value='{$usrhimself['uid']}' />"
		."</form>";
	}

	$usersummary .= "\t\t</td>\n"
	. "\t</tr>\n";

	// empty row
	if(count($userlist) > 0) $usersummary .= "\t<tr>\n\t<td height=\"20\" colspan=\"6\"></td>\n\t</tr>";
		
	// other users
	$row = 0;
	$usr_arr = $userlist;
	for($i=1; $i<=count($usr_arr); $i++)
	{
		$usr = $usr_arr[$i];
		if(($row % 2) == 0) $usersummary .= "\t<tr  bgcolor='#999999'>\n";
		else $usersummary .= "\t<tr>\n";

		$usersummary .= "\t<td align='center'>{$usr['user']}</td>\n"
		. "\t<td align='center'><a href='mailto:{$usr['email']}'>{$usr['email']}</a></td>\n"
		. "\t<td align='center'>{$usr['full_name']}</td>\n";

		// passwords of other users will not be displayed
		$usersummary .=  "\t\t<td align='center'>******</td>\n";

		// Get Parent's User Name
		$uquery = "SELECT users_name FROM ".db_table_name('users')." WHERE uid=".$usr['parent_id'];
		$uresult = db_execute_assoc($uquery);
		$userlist = array();
		$srow = $uresult->FetchRow();
		$usr['parent'] = $srow['users_name'];
		/*
		if($_SESSION['USER_RIGHT_MOVE_USER'])
		{
			$usersummary .= "\t\t<td align='center'>"
			."<form name='parentsform{$usr['uid']}'action='$scriptname?action=setnewparents' method='post'>"
			."<input type='hidden' name='uid' value='{$usr['uid']}' />";
			//."<select name='parent' size='1' onchange='document.getElementById(\"button{$usr['uid']}\").innerHTML = \"<input type=\\\"submit\\\" value=\\\"".$clang->gT("Change")."\\\">\"'>"
			//."<select name='parent' size='1' onchange='document.getElementById(\"button{$usr['uid']}\").createElement(\"input\")'>";
			if($usr['uid'] != $usrhimself['uid'])
			{
				//$usersummary .= "<option value='{$usrhimself['uid']}'";
				if($usr['parent_id'] == $usrhimself['uid']) {
					$usersummary .= $usrhimself['user'];
				}
			}
			$usersummary .= "<div id='button{$usr['uid']}'></div>\n";
			$usersummary .= "</form></td>\n";
		}
		else
		{*/
			
			
			//TODO: Find out why parent isn't set
			if (isset($usr['parent']))
			{
				$usersummary .= "\t\t<td align='center'>{$usr['parent']}</td>\n";
			} else 
			{
				$usersummary .= "\t\t<td align='center'>-----</td>\n";
			}
		//}
		
		$usersummary .= "\t\t<td align='center' style='padding-top:10px;'>\n";
		// users are allowed to delete all successor users (but the admin not himself)
		//  || ($usr['uid'] == $_SESSION['loginID']))
		if ($_SESSION['loginID'] == "1" || ($_SESSION['USER_RIGHT_DELETE_USER'] == 1  && $usr['parent_id'] == $_SESSION['loginID']))
		{
			$usersummary .= "\t\t\t<form method='post' action='$scriptname?action=deluser'>"
			."<input type='submit' value='".$clang->gT("Delete")."' onclick='return confirm(\"".$clang->gT("Are you sure you want to delete this entry.")."\")' />"
			."<input type='hidden' name='action' value='deluser' />"
			."<input type='hidden' name='user' value='{$usr['user']}' />"
			."<input type='hidden' name='uid' value='{$usr['uid']}' />"
			."</form>";
		}
		if ($_SESSION['loginID'] == "1" || ($_SESSION['USER_RIGHT_CREATE_USER'] == 1 && ($usr['parent_id'] == $_SESSION['loginID'])))
		{
			$usersummary .= "\t\t\t<form method='post' action='$scriptname'>"
			."<input type='submit' value='".$clang->gT("Set User Rights")."' />"
			."<input type='hidden' name='action' value='setuserrights' />"
			."<input type='hidden' name='user' value='{$usr['user']}' />"
			."<input type='hidden' name='uid' value='{$usr['uid']}' />"
			."</form>";
		}
		if ($_SESSION['loginID'] == "1" || $usr['uid'] == $_SESSION['loginID'] || ($_SESSION['USER_RIGHT_CREATE_USER'] == 1 && $usr['parent_id'] == $_SESSION['loginID']))
		{
			$usersummary .= "\t\t\t<form method='post' action='$scriptname'>"
			."<input type='submit' value='".$clang->gT("Edit User")."' />"
			."<input type='hidden' name='action' value='modifyuser' />"
			."<input type='hidden' name='uid' value='{$usr['uid']}' />"
			."</form>";
		}
		$usersummary .= "\t\t</td>\n"
		. "\t</tr>\n";
		$row++;
	}

	if($_SESSION['USER_RIGHT_CREATE_USER'])
	{
		$usersummary .= "\t\t<form action='$scriptname' method='post'>\n"
		. "\t\t<tr>\n"
		. "\t\t<td align='center'><input type='text' name='new_user' /></td>\n"
		. "\t\t<td align='center'><input type='text' name='new_email' /></td>\n"
		. "\t\t<td align='center'><input type='text' name='new_full_name' /></td>\n"
		. "\t\t<td align='center'><input type='submit' value='".$clang->gT("Add User")."' />"
		. "<input type='hidden' name='action' value='adduser' /></td>\n"
		. "\t</tr>\n";
	}
	
}

if ($action == "addusergroup")
{
	if ($_SESSION['loginID'] == 1)
	{
		$usersummary = "<form action='$scriptname'  method='post'><table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n"
		. "\t\t<strong><font color='white'>".$clang->gT("Add User Group")."</font></strong></td></tr>\n"
		. "\t<tr>\n"
		. "\t\t<td align='right'><strong>".$clang->gT("Name:")."</strong></td>\n"
		. "\t\t<td><input type='text' size='50' name='group_name' /><font color='red' face='verdana' size='1'> ".$clang->gT("Required")."</font></td></tr>\n"
		. "\t<tr><td align='right'><strong>".$clang->gT("Description:")."</strong>(".$clang->gT("Optional").")</td>\n"
		. "\t\t<td><textarea cols='50' rows='4' name='group_description'></textarea></td></tr>\n"
		. "\t<tr><td colspan='2' align='center'><input type='submit' value='".$clang->gT("Add Group")."' />\n"
		. "\t<input type='hidden' name='action' value='usergroupindb' />\n"
		. "\t</td></table>\n"
		. "</form>\n";
	}
}

if ($action == "editusergroup")
{
	if ($_SESSION['loginID'] == 1)
	{
		$query = "SELECT * FROM ".db_table_name('user_groups')." WHERE ugid = ".$_GET['ugid']." AND owner_id = ".$_SESSION['loginID'];
		$result = db_select_limit_assoc($query, 1);
		$esrow = $result->FetchRow();
		$usersummary = "<form action='$scriptname' name='editusergroup' method='post'>"
		. "<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n"
		. "\t\t<strong><font color='white'>".$clang->gT("Edit User Group (Owner: ").$_SESSION['user'].")</font></strong></td></tr>\n"
		. "\t<tr>\n"
		. "\t\t<td align='right' width='20%'><strong>".$clang->gT("Name:")."</strong></td>\n"
		. "\t\t<td><input type='text' size='50' name='name' value=\"{$esrow['name']}\" /></td></tr>\n"
		. "\t<tr><td align='right'><strong>".$clang->gT("Description:")."</strong>(optional)</td>\n"
		. "\t\t<td><textarea cols='50' rows='4' name='description'>{$esrow['description']}</textarea></td></tr>\n"
		. "\t<tr><td colspan='2' align='center'><input type='submit' value='".$clang->gT("Update User Group")."' />\n"
		. "\t<input type='hidden' name='action' value='editusergroupindb' />\n"
		. "\t<input type='hidden' name='owner_id' value='".$_SESSION['loginID']."' />\n"
		. "\t<input type='hidden' name='ugid' value='$ugid' />\n"
		. "\t</td></tr>\n"
		. "</table>\n"
		. "\t</form>\n";
	}
}

if ($action == "mailusergroup")
{
	$query = "SELECT a.ugid, a.name, a.owner_id, b.uid FROM ".db_table_name('user_groups') ." AS a LEFT JOIN ".db_table_name('user_in_groups') ." AS b ON a.ugid = b.ugid WHERE a.ugid = {$ugid} AND uid = {$_SESSION['loginID']} ORDER BY name";
	$result = db_execute_assoc($query);
	$crow = $result->FetchRow();
	$eguquery = "SELECT * FROM ".db_table_name("user_in_groups")." AS a INNER JOIN ".db_table_name("users")." AS b ON a.uid = b.uid WHERE ugid = " . $ugid . " AND b.uid != {$_SESSION['loginID']} ORDER BY b.users_name";
	$eguresult = db_execute_assoc($eguquery);
	$addressee = '';
	$to = '';
	while ($egurow = $eguresult->FetchRow())
	{
		$to .= $egurow['users_name']. ' <'.$egurow['email'].'>'. ', ' ;
		$addressee .= $egurow['users_name'].', ';
	}

	$to = substr("$to", 0, -2);
	$addressee = substr("$addressee", 0, -2);

	$usersummary = "<form action='$scriptname' name='mailusergroup' method='post'>"
	. "<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n"
	. "\t\t<strong><font color='white'>".$clang->gT("Mail to all Members")."</font></strong></td></tr>\n"
	. "\t<tr>\n"
	. "\t\t<td align='right' width='20%'><strong>".$clang->gT("To:")."</strong></td>\n"
	. "\t\t<td><input type='text' size='50' name='to' value=\"{$to}\" /></td></tr>\n"
	. "\t\t<td align='right' width='20%'><strong>".$clang->gT("Send me a copy:")."</strong></td>\n"
	. "\t\t<td><input name='copymail' type='checkbox' class='checkboxbtn' value='1' /></td></tr>\n"
	. "\t\t<td align='right' width='20%'><strong>".$clang->gT("Subject:")."</strong></td>\n"
	. "\t\t<td><input type='text' size='50' name='subject' value='' /></td></tr>\n"
	. "\t<tr><td align='right'><strong>".$clang->gT("Message:")."</strong></td>\n"
	. "\t\t<td><textarea cols='50' rows='4' name='body'></textarea></td></tr>\n"
	. "\t<tr><td colspan='2' align='center'><input type='submit' value='".$clang->gT("Send")."'>\n"
	. "<input type='reset' value='Reset'><br />"
	. "\t<input type='hidden' name='action' value='mailsendusergroup' />\n"
	. "\t<input type='hidden' name='addressee' value='$addressee' />\n"
	. "\t<input type='hidden' name='ugid' value='$ugid' />\n"
	. "\t</td></tr>\n"
	. "</table>\n"
	. "\t</form>\n";
}

if ($action == "delusergroup")
{
		if ($_SESSION['loginID'] == 1)
	{
	$usersummary = "<br /><strong>".$clang->gT("Deleting User Group")."</strong><br />\n";

	if(!empty($_GET['ugid']) && $_GET['ugid'] > -1)
	{
		$query = "SELECT ugid, name, owner_id FROM ".db_table_name('user_groups')." WHERE ugid = ".$_GET['ugid']." AND owner_id = ".$_SESSION['loginID'];
		$result = db_select_limit_assoc($query, 1);
		if($result->RecordCount() > 0)
		{
			$row = $result->FetchRow();

			$remquery = "DELETE FROM ".db_table_name('user_groups')." WHERE ugid = {$_GET['ugid']} AND owner_id = {$_SESSION['loginID']}";
			if($connect->Execute($remquery))
			{
				$usersummary .= "<br />".$clang->gT("Group Name").": {$row['name']}<br />\n";
			}
			else
			{
				$usersummary .= "<br />".$clang->gT("Could not delete user group.")."<br />\n";
			}
		}
		else
		{
			include("access_denied.php");
		}
	}
	else
	{
		$usersummary .= "<br />".$clang->gT("Could not delete user group. No group selected.")."<br />\n";
	}
	$usersummary .= "<br /><a href='$scriptname?action=editusergroups'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
	}
}

if ($action == "usergroupindb") {
	$usersummary = "<br /><strong>".$clang->gT("Adding User Group")."...</strong><br />\n";

	$group_name = $_POST['group_name'];
	$group_description = $_POST['group_description'];
	if(isset($group_name) && strlen($group_name) > 0)
	{
		$ugid = addUserGroupInDB($group_name, $group_description);
		if($ugid > 0)
		{
			$usersummary .= "<br />".$clang->gT("Group Name").": {$group_name}<br />\n";

			if(isset($group_description) && strlen($group_description) > 0)
			{
				$usersummary .= $clang->gT("Description: ").$group_description."<br />\n";
			}

         	$usersummary .= "<br /><strong>".$clang->gT("User group successfully added!")."</strong><br />\n";
			$usersummary .= "<br /><a href='$scriptname?action=editusergroups&amp;ugid={$ugid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
		else
		{
			$usersummary .= "<br /><strong>".$clang->gT("Failed to add Group!")."</strong><br />\n"
			. $clang->gT("Group already exists!")."<br />\n"
			. "<br /><a href='$scriptname?action=editusergroups'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
	}
	else
	{
		$usersummary .= "<br /><strong>".$clang->gT("Failed to add Group!")."</strong><br />\n"
		. $clang->gT("Group name was not supplied!")."<br />\n"
		. "<br /><a href='$scriptname?action=addusergroup'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
	}
}

if ($action == "mailsendusergroup")
{
	$usersummary = "<br /><strong>".$clang->gT("Mail to all Members")."</strong><br />\n";

	// user musst be in user group
	$query = "SELECT uid FROM ".db_table_name('user_in_groups') ." WHERE ugid = {$ugid} AND uid = {$_SESSION['loginID']}";
	$result = db_execute_assoc($query);

	if($result->RecordCount() > 0)
	{
		$from_user = "SELECT email, user FROM ".db_table_name("users")." WHERE uid = " .$_SESSION['loginID'];
		$from_user_result = mysql_query($from_user);
		$from_user_row = mysql_fetch_array($from_user_result, MYSQL_BOTH);
		$from = $from_user_row['user'].' <'.$from_user_row['email'].'> ';

		$ugid = $_POST['ugid'];
		$to	= $_POST['to'];
		$body = $_POST['body'];
		$subject = $_POST['subject'];
		$addressee = $_POST['addressee'];

		if(isset($_POST['copymail']) && $_POST['copymail'] == 1)
		{
			$to .= ", " . $from;
		}

		$body = str_replace("\n.", "\n..", $body);
		$body = wordwrap($body, 70);

		if (mail($to, $subject, $body, "From: $from"))
		{
			$usersummary = "<br /><strong>".("Message sent successfully!")."</strong><br />\n"
			. "<br />To: $addressee<br />\n"
			. "<br /><a href='$scriptname?action=editusergroups&amp;ugid={$ugid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
		else
		{
			$usersummary .= "<br /><strong>".$clang->gT("Mail not sent!")."</strong><br />\n";
			$usersummary .= "<br /><a href='$scriptname?action=mailusergroup&amp;ugid={$ugid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
	}
	else
	{
		include("access_denied.php");
	}
}

if ($action == "editusergroupindb"){

	$ugid = $_POST['ugid'];
	$name = $_POST['name'];
	$description = $_POST['description'];

	if(updateusergroup($name, $description, $ugid))
	{
		$usersummary = "<br /><strong>".$clang->gT("Edit User Group Successfully!")."</strong><br />\n";
		$usersummary .= "<br />".$clang->gT("Name").": {$name}<br />\n";
		$usersummary .= $clang->gT("Description: ").$description."<br />\n";
		$usersummary .= "<br /><a href='$scriptname?action=editusergroups&amp;ugid={$ugid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
	}
	else $usersummary .= "<br /><strong>".$clang->gT("Failed to update!")."</strong><br />\n"
	. "<br /><a href='$scriptname?action=editusergroups'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
}

if ($action == "editusergroups"  )
{
	if(isset($_GET['ugid']))
	{
		$ugid = $_GET['ugid'];

		$query = "SELECT a.ugid, a.name, a.owner_id, a.description, b.uid FROM ".db_table_name('user_groups') ." AS a LEFT JOIN ".db_table_name('user_in_groups') ." AS b ON a.ugid = b.ugid WHERE a.ugid = {$ugid} AND uid = {$_SESSION['loginID']} ORDER BY name";
		$result = db_execute_assoc($query);
		$crow = $result->FetchRow();

		if($result->RecordCount() > 0)
		{

			if(!empty($crow['description']))
			{
				$usergroupsummary .= "<table rules='rows' width='100%' border='1' cellpadding='10'>\n"
				. "\t\t\t\t<tr id='surveydetails20'><td align='justify' colspan='2' height='4'>"
				. "<font size='2' face='verdana' color='black'><strong>".$clang->gT("Description: ")."</strong>"
				. "<font color='black'>{$crow['description']}</font></td></tr>\n"
				. "</table>";
			}


			$eguquery = "SELECT * FROM ".db_table_name("user_in_groups")." AS a INNER JOIN ".db_table_name("users")." AS b ON a.uid = b.uid WHERE ugid = " . $ugid . " ORDER BY b.users_name";
			$eguresult = db_execute_assoc($eguquery);
			$usergroupsummary .= "<table rules='rows' width='100%' border='1'>\n"
			. "\t<tr>\n"
			. "\t\t<th>".$clang->gT("Username")."</th>\n"
			. "\t\t<th>".$clang->gT("Email")."</th>\n"
			. "\t\t<th>".$clang->gT("Action")."</th>\n"
			. "\t</tr>\n";

			$query2 = "SELECT ugid FROM ".db_table_name('user_groups')." WHERE ugid = ".$ugid." AND owner_id = ".$_SESSION['loginID'];
			$result2 = db_select_limit_assoc($query2, 1);
			$row2 = $result2->FetchRow();

			$row = 1;
			$usergroupentries='';
			while ($egurow = $eguresult->FetchRow())
			{
				if($egurow['uid'] == $crow['owner_id'])
				{
					$usergroupowner = "\t<tr bgcolor='#999999'>\n"
					. "\t<td align='center'><strong>{$egurow['users_name']}</strong></td>\n"
					. "\t<td align='center'><strong>{$egurow['email']}</strong></td>\n"
					. "\t\t<td align='center'>\n";
					continue;
				}
				//	output users
				
				if($row == 1){ $usergroupentries .= "\t<tr>\n\t<td height=\"20\" colspan=\"6\"></td>\n\t</tr>"; $row++;}
				if(($row % 2) == 0) $usergroupentries .= "\t<tr  bgcolor='#999999'>\n";
				else $usergroupentries .= "\t<tr>\n";
				$usergroupentries .= "\t<td align='center'>{$egurow['users_name']}</td>\n"
				. "\t<td align='center'>{$egurow['email']}</td>\n"
				. "\t\t<td align='center' style='padding-top:10px;'>\n";

				// owner and not himself    or    not owner and himself
				if((isset($row2['ugid']) && $_SESSION['loginID'] != $egurow['uid']) || (!isset($row2['ugid']) && $_SESSION['loginID'] == $egurow['uid']))
				{
					$usergroupentries .= "\t\t\t<form method='post' action='$scriptname?action=deleteuserfromgroup&ugid=$ugid'>"
					." <input type='submit' value='".$clang->gT("Delete")."' onclick='return confirm(\"".$clang->gT("Are you sure you want to delete this entry.")."\")' />"
					." <input type='hidden' name='user' value='{$egurow['users_name']}' />"
					." <input name='uid' type='hidden' value='{$egurow['uid']}' />"
					." <input name='ugid' type='hidden' value='{$ugid}' />";
				}
				$usergroupentries .= "</form>"
				. "\t\t</td>\n"
				. "\t</tr>\n";
				$row++;
			}
			$usergroupsummary .= $usergroupowner;
            if (isset($usergroupentries)) {$usergroupsummary .= $usergroupentries;};

			if(isset($row2['ugid']))
			{
				$usergroupsummary .= "\t\t<form action='$scriptname?ugid={$ugid}' method='post'>\n"
				. "\t\t<tr><td></td>\n"
				. "\t\t\t<td align='right'>"
				. "\t\t\t\t<select name='uid' class=\"listboxgroups\">\n"
				. getgroupuserlist()
				. "\t\t\t\t</select></td>\n"
				. "\t\t\t\t<td align='center'><input type='submit' value='".$clang->gT("Add User")."' />\n"
				. "\t\t\t\t<input type='hidden' name='action' value='addusertogroup' /></td></form>\n"
				. "\t\t\t</td>\n"
				. "\t\t</tr>\n"
				. "\t</form>\n";
			}
		}
		else
		{
			include("access_denied.php");
		}
	}
}

if($action == "deleteuserfromgroup") {
	$ugid = $_POST['ugid'];
	$uid = $_POST['uid'];
	$usersummary = "<br /><strong>".$clang->gT("Delete User")."</strong><br />\n";

	$query = "SELECT ugid, owner_id FROM ".db_table_name('user_groups')." WHERE ugid = ".$ugid." AND ((owner_id = ".$_SESSION['loginID']." AND owner_id != ".$uid.") OR (owner_id != ".$_SESSION['loginID']." AND $uid = ".$_SESSION['loginID']."))";
	$result = db_execute_assoc($query);
	if($result->RecordCount() > 0)
	{
		$remquery = "DELETE FROM ".db_table_name('user_in_groups')." WHERE ugid = {$ugid} AND uid = {$uid}";
		if($connect->Execute($remquery))
		{
			$usersummary .= "<br />".$clang->gT("Username").": {$_POST['user']}<br />\n";
		}
		else
		{
			$usersummary .= "<br />".$clang->gT("Could not delete user. User was not supplied.")."<br />\n";
		}
	}
	else
	{
		include("access_denied.php");
	}
	if($_SESSION['loginID'] != $_POST['uid'])
	{
		$usersummary .= "<br /><a href='$scriptname?action=editusergroups&amp;ugid=$ugid'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
	}
	else
	{
		$usersummary .= "<br /><a href='$scriptname?action=editusergroups'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
	}
}

if ($action == "addgroup")
{
	if($sumrows5['define_questions'])
	{
		$grplangs = GetAdditionalLanguagesFromSurveyID($surveyid);
		$baselang = GetBaseLanguageFromSurveyID($surveyid);
		$grplangs[] = $baselang;
		$grplangs = array_reverse($grplangs);

		$newgroupoutput = "<tr><td><form action='$scriptname' name='addnewgroupfrom' method='post'>"
                   ."<table width='100%' border='0'><tr>\n"
                   ."\t<td colspan='2' bgcolor='black' align='center'>\n\t\t<strong><font color='white'>".$clang->gT("Add Group")."</font></strong></td>"
                   ."</tr></table>\n";


		$newgroupoutput .="<table width='100%' border='0'>\n\t<tr><td>\n"
		. '<div class="tab-pane" id="tab-pane-1">';
		foreach ($grplangs as $grouplang)
		{
			$newgroupoutput .= '<div class="tab-page"> <h2 class="tab">'.GetLanguageNameFromCode($grouplang);
			if ($grouplang==$baselang) {$newgroupoutput .= '('.$clang->gT("Base Language").')';}
			$newgroupoutput .= "</h2>"
            . "<table width='100%' border='0'>"
    		. "\t\t<tr><td align='right'><strong>".$clang->gT("Title").":</strong></td>\n"
    		. "\t\t<td><input type='text' size='50' name='group_name_$grouplang' /><font color='red' face='verdana' size='1'> ".$clang->gT("Required")."</font></td></tr>\n"
    		. "\t<tr><td align='right'><strong>".$clang->gT("Description:")."</strong>(".$clang->gT("Optional").")</td>\n"
    		. "\t\t<td><textarea cols='50' rows='4' name='description_$grouplang'></textarea></td></tr>\n"
    		. "</table></div>";
        }

		$newgroupoutput.= "</div>" 
        . "\t<input type='hidden' name='action' value='insertnewgroup' />\n"
		. "\t<input type='hidden' name='sid' value='$surveyid' /></td></tr>"
		. "\t<tr><td colspan='2' align='center'><input type='submit' value='".$clang->gT("Add Group")."' />\n"
		. "\t</td></table>\n"
		. "</form></td></tr>\n"
		. "<tr><td align='center'><strong>".$clang->gT("OR")."</strong></td></tr>\n"
		. "<tr><td><form enctype='multipart/form-data' name='importgroup' action='$scriptname' method='post' onsubmit='return validatefilename(this,\"".$clang->gT('Please select a file to import!','js')."\");'>\n"
		. "<table width='100%' border='0'>\n\t<tr><td colspan='3' bgcolor='black' align='center'>\n"
		. "\t\t<strong><font color='white'>".$clang->gT("Import Group")."</font></strong></td></tr>\n\t<tr>"
		. "\t\n"
		. "\t\t<td align='right'><strong>".$clang->gT("Select CSV File:")."</strong></td>\n"
		. "\t\t<td><input name=\"the_file\" type=\"file\" size=\"35\" /></td></tr>\n"
		. "\t<tr><td colspan='2' align='center'><input type='submit' value='".$clang->gT("Import Group")."' />\n"
		. "\t<input type='hidden' name='action' value='importgroup' />\n"
		. "\t<input type='hidden' name='sid' value='$surveyid' />\n"
		. "\t</td></tr>\n</table></form>\n";

	}
	else
	{
		include("access_denied.php");
	}
}

if ($action == "editgroup")
{
	if ($sumrows5['edit_survey_property'])
	{
		$grplangs = GetAdditionalLanguagesFromSurveyID($surveyid);
		$baselang = GetBaseLanguageFromSurveyID($surveyid);

		if (isset($grplangs)) {array_unshift($grplangs, $baselang);}
		else {$grplangs[] = $baselang;}
		$grplangs = array_flip($grplangs);
		

		$egquery = "SELECT * FROM ".db_table_name('groups')." WHERE sid=$surveyid AND gid=$gid";
		$egresult = db_execute_assoc($egquery);
		while ($esrow = $egresult->FetchRow())
		{
			if(!array_key_exists($esrow['language'], $grplangs)) // Language Exists, BUT ITS NOT ON THE SURVEY ANYMORE.
			{
				$egquery = "DELETE FROM ".db_table_name('groups')." WHERE sid='{$surveyid}' AND gid='{$gid}' AND language='".$esrow['language']."'";
				$egresultD = $connect->Execute($egquery);
			} else {
				$grplangs[$esrow['language']] = 99;
			}
			if ($esrow['language'] == $baselang) $basesettings = array('group_name' => $esrow['group_name'],'description' => $esrow['description'],'group_order' => $esrow['group_order']);

		}
	
		while (list($key,$value) = each($grplangs))
		{
			if ($value != 99)
			{
				//die("INSERT:".$key);
				$egquery = "INSERT INTO ".db_table_name('groups')." (gid, sid, group_name, description,group_order,language) VALUES ('{$gid}', '{$surveyid}', '{$basesettings['group_name']}', '{$basesettings['description']}','{$basesettings['group_order']}', '{$key}')";
				$egresult = $connect->Execute($egquery);
			}
		}
		
		$egquery = "SELECT * FROM ".db_table_name('groups')." WHERE sid=$surveyid AND gid=$gid";
		$egresult = db_execute_assoc($egquery);
		$editgroup ="<table width='100%' border='0'>\n\t<tr><td bgcolor='black' align='center'>"
		. "\t\t<font class='settingcaption'><font color='white'>".$clang->gT("Edit Group")."</font></font></td></tr></table>\n"
		. "<form name='editgroup' action='$scriptname' method='post'>\n"
		. '<div class="tab-pane" id="tab-pane-1">';
		while ($esrow = $egresult->FetchRow())
		{
			$editgroup .= '<div class="tab-page"> <h2 class="tab">'.getLanguageNameFromCode($esrow['language'],false);
			if ($esrow['language']==GetBaseLanguageFromSurveyID($surveyid)) {$editgroup .= '('.$clang->gT("Base Language").')';}
			$esrow = array_map('htmlspecialchars', $esrow);
			$editgroup .= '</h2>';
			$editgroup .= "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Title").":</span>\n"
			. "\t\t<span class='settingentry'><input type='text' maxlength='100' size='80' name='group_name_{$esrow['language']}' value=\"{$esrow['group_name']}\" />\n"
			. "\t</span></div>\n"
			. "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Description:")."</span>\n"
			. "\t\t<span class='settingentry'><textarea cols='70' rows='8' name='description_{$esrow['language']}'>{$esrow['description']}</textarea>\n"
			. "\t</span></div><div class='settingrow'></div></div>"; // THis empty div class is needed for forcing the tabpage border under the button
		}
		$editgroup .= '</div>';
		$editgroup .= "\t<p><input type='submit' class='standardbtn' value='".$clang->gT("Update Group")."' />\n"
		. "\t<input type='hidden' name='action' value='updategroup' />\n"
		. "\t<input type='hidden' name='sid' value=\"{$surveyid}\" />\n"
		. "\t<input type='hidden' name='gid' value='{$gid}' />\n"
		. "\t<input type='hidden' name='language' value=\"{$esrow['language']}\" />\n"
		. "\t</p>\n"
		. "</form>\n";
	}
	else
	{
		include("access_denied.php");
	}
}

if($action == "addusertogroup")
{
	$addsummary = "<br /><strong>".$clang->gT("Adding User to group")."...</strong><br />\n";

	$query = "SELECT ugid, owner_id FROM ".db_table_name('user_groups')." WHERE ugid = ".$_GET['ugid']." AND owner_id = ".$_SESSION['loginID']." AND owner_id != ".$_POST['uid'];
	$result = db_execute_assoc($query);
	if($result->RecordCount() > 0)
	{
		if($_POST['uid'] > 0)
		{
			$isrquery = "INSERT INTO {$dbprefix}user_in_groups VALUES(".$_GET['ugid'].",". $_POST['uid'].")";
			$isrresult = $connect->Execute($isrquery);

			if($isrresult)
			{
				$addsummary .= "<br />".$clang->gT("User added.")."<br />\n";
			}
			else  // ToDo: for this to happen the keys on the table must still be set accordingly
			{
				// Username already exists.
				$addsummary .= "<br /><strong>".$clang->gT("Failed to add User.")."</strong><br />\n" . " " . $clang->gT("Username already exists.")."<br />\n";
			}
			$addsummary .= "<br /><a href='$scriptname?action=editusergroups&amp;ugid=".$_GET['ugid']."'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
		else
		{
			$addsummary .= "<br /><strong>".$clang->gT("Failed to add User.")."</strong><br />\n" . " " . $clang->gT("No Username selected.")."<br />\n";
			$addsummary .= "<br /><a href='$scriptname?action=editusergroups&amp;ugid=".$_GET['ugid']."'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
	}
	else
	{
		include("access_denied.php");
	}
}
