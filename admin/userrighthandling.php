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
//Security Checked: POST/GET/DB/SESSION
//Ensure script is not run directly, avoid path disclosure
if (!isset($dbprefix) || isset($_REQUEST['dbprefix'])) {die("Cannot run this script directly");}
if (isset($_POST['uid'])) {$postuserid=sanitize_int($_POST['uid']);}
if (isset($_POST['ugid'])) {$postusergroupid=sanitize_int($_POST['ugid']);}

if (($ugid && !$surveyid) || $action == "editusergroups" || $action == "addusergroup" || $action=="usergroupindb" || $action == "editusergroup" || $action == "mailusergroup")
{
	if($ugid)
	{
		$grpquery = "SELECT gp.* FROM ".db_table_name('user_groups')." AS gp, ".db_table_name('user_in_groups')." AS gu WHERE gp.ugid=gu.ugid AND gp.ugid = $ugid AND gu.uid=".$_SESSION['loginID'];
		$grpresult = db_execute_assoc($grpquery);//Checked
		$grpresultcount = $grpresult->RecordCount();
		$grow = array_map('htmlspecialchars', $grpresult->FetchRow());
	}
	$usergroupsummary = "<div class='menubar'>\n"
    . "<div class='menubar-title'>\n"
	. "<strong>".$clang->gT("User Group")."</strong>";
	if($ugid && $grpresultcount > 0)
	{
		$usergroupsummary .= " {$grow['name']}\n";
	}


	$usergroupsummary .= "</div>\n"
    . "<div class='menubar-main'>\n"
    . "<div class='menubar-left'>\n"
	. "<img src='$imagefiles/blank.gif' alt='' width='55' height='20' />\n"
	. "<img src='$imagefiles/seperator.gif' alt='' />\n";

	if($ugid && $grpresultcount > 0)
	{
		$usergroupsummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=mailusergroup&amp;ugid=$ugid', '_top')\""
		. "onmouseout=\"hideTooltip()\""
		. "onmouseover=\"showTooltip(event,'".$clang->gT("Mail to all Members", "js")."');return false\"> " .
		"<img src='$imagefiles/invite.png' title='' alt='' name='MailUserGroup' /></a>\n" ;
	}
    else
    {
        $usergroupsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' height='20' />\n";
    }    
	$usergroupsummary .= "<img src='$imagefiles/blank.gif' alt='' width='78' height='20' />\n"
	. "<img src='$imagefiles/seperator.gif' alt='' />\n";

	if($ugid && $grpresultcount > 0 &&
		$_SESSION['loginID'] == $grow['owner_id'])
	{
		$usergroupsummary .=  "<a href=\"#\" onclick=\"window.open('$scriptname?action=editusergroup&amp;ugid=$ugid','_top')\""
		. "onmouseout=\"hideTooltip()\""
		. "onmouseover=\"showTooltip(event,'".$clang->gT("Edit Current User Group", "js")."');return false\">" .
		"<img src='$imagefiles/edit.png' title='' alt='' name='EditUserGroup' /></a>\n" ;
	}
	else
	{
		$usergroupsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' height='20' />\n";
	}

	if($ugid && $grpresultcount > 0 &&
		$_SESSION['loginID'] == $grow['owner_id'])
	{
//		$usergroupsummary .= "<a href='$scriptname?action=delusergroup&amp;ugid=$ugid' onclick=\"return confirm('".$clang->gT("Are you sure you want to delete this entry?","js")."')\""
		$usergroupsummary .= "<a href='#' onclick=\"if (confirm('".$clang->gT("Are you sure you want to delete this entry?","js")."')) {".get2post("$scriptname?action=delusergroup&amp;ugid=$ugid")."}\" "
		. "onmouseout=\"hideTooltip()\" "
		. "onmouseover=\"showTooltip(event,'".$clang->gT("Delete Current User Group", "js")."');return false\">"
		. "<img src='$imagefiles/delete.png' alt='' name='DeleteUserGroup' title='' /></a>\n";
	}
	else
	{
		$usergroupsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' height='20' />\n";
	}
	$usergroupsummary .= "<img src='$imagefiles/blank.gif' alt='' width='92' height='20' />\n"
	. "<img src='$imagefiles/seperator.gif' alt='' />\n"
	. "</div>\n"
	. "<div class='menubar-right'>\n"
	. "<font class=\"boxcaption\">".$clang->gT("User Groups").":</font>&nbsp;<select name='ugid' "
	. "onchange=\"window.open(this.options[this.selectedIndex].value, '_top')\">\n"
	. getusergrouplist()
	. "</select>\n";
    if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
    {
        $usergroupsummary .= "<a href='$scriptname?action=addusergroup'"
        ."onmouseout=\"hideTooltip()\""
        ."onmouseover=\"showTooltip(event,'".$clang->gT("Add New User Group", "js")."');return false\">" 
        ."<img src='$imagefiles/add.png' title='' alt='' " 
        ."name='AddNewUserGroup' onclick=\"window.open('', '_top')\" /></a>\n";
    }
    $usergroupsummary .= "<img src='$imagefiles/seperator.gif' alt='' />\n"
    . "<img src='$imagefiles/blank.gif' alt='' width='82' height='20' />\n"
	. "</div></div>\n"
	. "</div>\n";
    $usergroupsummary .= "<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>"; //CSS Firefox 2 transition fix
    

}


if ($action == "adduser" || $action=="deluser" || $action == "moduser" || $action == "userrights"  || $action == "usertemplates")
{
	include("usercontrol.php");
}

if ($action == "setusertemplates")
{
              refreshtemplates();
              $usersummary = "<table width='50%' border='0'>\n<tr><td colspan='2' bgcolor='black' align='center'><form method='post' action='$scriptname'>\n"
              . "<strong><font color='white'>".$clang->gT("Set templates that this user may access").": ".$_POST['user']."</td></tr>\n";

              $userlist = getuserlist();
              foreach ($userlist as $usr)
              {
                      if ($usr['uid'] == $postuserid)
                      {
                              $templaterights = array();
                              $squery = 'SELECT '.db_quote_id('folder').','.db_quote_id('use')." FROM {$dbprefix}templates_rights WHERE uid={$usr['uid']}";
                              $sresult = db_execute_assoc($squery) or safe_die($connect->ErrorMsg());//Checked
                              while ($srow = $sresult->FetchRow()) {
                                      $templaterights[$srow["folder"]] = array("use"=>$srow["use"]);
                              }

                              $usersummary .= "<tr><th>".$clang->gT("Template Name")."</th><th>".$clang->gT("Allowed")."</th></tr>\n"
                                      ."<tr><form method='post' action='$scriptname'></tr>"
                                      ."<form action='$scriptname' method='post'>\n";

                              $tquery = "SELECT * FROM ".$dbprefix."templates";
                              $tresult = db_execute_assoc($tquery) or safe_die($connect->ErrorMsg()); //Checked
                              while ($trow = $tresult->FetchRow()) {
                                      $usersummary .= "<tr><td>{$trow["folder"]}</td>";

                                      $usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"{$trow["folder"]}_use\" value=\"{$trow["folder"]}_use\"";
                                      if(isset($templaterights[$trow["folder"]]) && $templaterights[$trow["folder"]]["use"] == 1) {
                                              $usersummary .= " checked='checked' ";
                                      }
                                      $usersummary .=" /></td>\n</tr>\n";
                              }

                              $usersummary .= "<tr><form method='post' action='$scriptname'></tr>"      // added by Dennis
                              ."\n<tr><td colspan='3' align='center'>"
                              ."<input type='submit' value='".$clang->gT("Save Settings")."' />"
                              ."<input type='hidden' name='action' value='usertemplates' />"
                              ."<input type='hidden' name='uid' value='{$postuserid}' /></td></tr>"
                              ."</form>"
                              . "</table>\n";
                              continue;
                     }
              }
}


if ($action == "modifyuser")
{
    if (isset($postuserid) && $postuserid)
    {
    	$squery = "SELECT uid FROM {$dbprefix}users WHERE uid=$postuserid AND parent_id=".$_SESSION['loginID'];	//		added by Dennis
    	$sresult = $connect->Execute($squery);//Checked
    	$sresultcount = $sresult->RecordCount(); 
    }
    else
    {
    		include("access_denied.php");
    }
    
    // RELIABLY CHECK MY RIGHTS
    if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $_SESSION['loginID'] == $postuserid ||
    	( $_SESSION['USER_RIGHT_CREATE_USER'] &&
    		$sresultcount > 0	
    	) )
    {	
		$usersummary = "<table width='100%' border='0'>\n<tr><td colspan='4' class='header'>\n"
		. "<strong>".$clang->gT("Modifying User")."</td></tr>\n"
		. "<tr>\n"
		. "<th>".$clang->gT("Username")."</th>\n"
		. "<th>".$clang->gT("Email")."</th>\n"
		. "<th>".$clang->gT("Full name")."</th>\n"
		. "<th>".$clang->gT("Password")."</th>\n"
		. "</tr>\n";
		$muq = "SELECT a.users_name, a.full_name, a.email, a.uid, b.users_name AS parent FROM ".db_table_name('users')." AS a LEFT JOIN ".db_table_name('users')." AS b ON a.parent_id = b.uid WHERE a.uid='{$postuserid}'";	//	added by Dennis
		//echo($muq);

		$mur = db_select_limit_assoc($muq, 1);
		$usersummary .= "<tr><form action='$scriptname' method='post'>";
		while ($mrw = $mur->FetchRow())
		{
			$mrw = array_map('htmlspecialchars', $mrw);
			$usersummary .= "<td align='center'><strong>{$mrw['users_name']}</strong>\n"
			. "<td align='center'>\n<input type='text' name='email' value=\"{$mrw['email']}\" /></td>\n"
			. "<td align='center'>\n<input type='text' name='full_name' value=\"{$mrw['full_name']}\" /></td>\n"
			. "<input type='hidden' name='user' value=\"{$mrw['users_name']}\" /></td>\n"
			. "<input type='hidden' name='uid' value=\"{$mrw['uid']}\" /></td>\n";	
			$usersummary .= "<td align='center'>\n<input type='password' name='pass' value=\"\" /></td>\n";
		}
		$usersummary .= "</tr>\n<tr><td colspan='4' align='center'>\n"
		. "<input type='submit' value='".$clang->gT("Update")."' />\n"
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
	if (isset($postuserid) && $postuserid)
	{
		$squery = "SELECT uid FROM {$dbprefix}users WHERE uid=$postuserid AND parent_id=".$_SESSION['loginID'];	//		added by Dennis
		$sresult = $connect->Execute($squery);//Checked
		$sresultcount = $sresult->RecordCount();
	}
	else
	{
			include("access_denied.php");
	}
	
	// RELIABLY CHECK MY RIGHTS
	if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 ||
		( $_SESSION['USER_RIGHT_CREATE_USER'] &&
			$sresultcount >  0 &&
			$_SESSION['loginID'] != $postuserid	
		) )
//	if($_SESSION['loginID'] != $postuserid)
	{
		$usersummary = "<table width='100%' border='0'>\n<tr><td colspan='8' class='header' align='center'>\n"
		. "".$clang->gT("Set User Rights").": ".htmlspecialchars(sanitize_user($_POST['user']))."</td></tr>\n";

		// HERE WE LIST FOR USER RIGHTS YOU CAN SET TO a USER
		// YOU CAN ONLY SET AT MOST THE RIGHTS YOU have yourself
		$userlist = getuserlist();
		foreach ($userlist as $usr)
		{
			if ($usr['uid'] == $postuserid)
			{
				$squery = "SELECT create_survey, configurator, create_user, delete_user, superadmin, manage_template, manage_label FROM {$dbprefix}users WHERE uid={$_SESSION['loginID']}";	//		added by Dennis
				$sresult = $connect->Execute($squery); //Checked
				$parent = $sresult->FetchRow();

				// Initial SuperAdmin has parent_id == 0
				$adminquery = "SELECT uid FROM {$dbprefix}users WHERE parent_id=0";
				$adminresult = db_select_limit_assoc($adminquery, 1);
				$row=$adminresult->FetchRow();
			
				// Only Initial SuperAdmin can give SuperAdmin rights
				if($row['uid'] == $_SESSION['loginID'])	
				{ // RENAMED AS SUPERADMIN
					$usersummary .= "<th align='center' class='admincell'>".$clang->gT("SuperAdministrator")."</th>\n";
				}
				if($parent['create_survey']) {
					$usersummary .= "<th align='center'>".$clang->gT("Create Survey")."</th>\n";
				}
				if($parent['configurator']) {
					$usersummary .= "<th align='center'>".$clang->gT("Configurator")."</th>\n";
				}
				if($parent['create_user']) {
					$usersummary .= "<th align='center'>".$clang->gT("Create User")."</th>\n";
				}
				if($parent['delete_user']) {
					$usersummary .= "<th align='center'>".$clang->gT("Delete User")."</th>\n";
				}
				if($parent['manage_template']) {
					$usersummary .= "<th align='center'>".$clang->gT("Manage Template")."</th>\n";
				}
				if($parent['manage_label']) {
					$usersummary .= "<th align='center'>".$clang->gT("Manage Labels")."</th>\n";
				}

				$usersummary .="<th></th>\n</tr>\n"
				."<tr><form method='post' action='$scriptname'></tr>"
				."<form action='$scriptname' method='post'>\n";
				//content

				// Only Initial SuperAdmmin can give SuperAdmin right
				if($row['uid'] == $_SESSION['loginID']) {
					$usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"superadmin\" id=\"superadmin\" value=\"superadmin\"";
					if($usr['superadmin']) {
						$usersummary .= " checked='checked' ";
					}
					$usersummary .= "onclick=\"if (this.checked == true) {document.getElementById('create_survey').checked=true;document.getElementById('configurator').checked=true;document.getElementById('create_user').checked=true;document.getElementById('delete_user').checked=true;document.getElementById('manage_template').checked=true;document.getElementById('manage_label').checked=true;}\"";
					$usersummary .=" /></td>\n";
				}
				if($parent['create_survey']) {
					$usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"create_survey\" id=\"create_survey\" value=\"create_survey\"";
					if($usr['create_survey']) {
						$usersummary .= " checked='checked' ";
					}
					$usersummary .=" /></td>\n";
				}
				if($parent['configurator']) {
					$usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"configurator\" id=\"configurator\" value=\"configurator\"";
					if($usr['configurator']) {
						$usersummary .= " checked='checked' ";
					}
					$usersummary .=" /></td>\n";
				}
				if($parent['create_user']) {
					$usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"create_user\" id=\"create_user\" value=\"create_user\"";
					if($usr['create_user']) {
						$usersummary .= " checked='checked' ";
					}
					$usersummary .=" /></td>\n";
				}
				if($parent['delete_user']) {
					$usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"delete_user\" id=\"delete_user\" value=\"delete_user\"";
					if($usr['delete_user']) {
						$usersummary .= " checked='checked' ";
					}
					$usersummary .=" /></td>\n";
				}
				if($parent['manage_template']) {
					$usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"manage_template\" id=\"manage_template\" value=\"manage_template\"";
					if($usr['manage_template']) {
						$usersummary .= " checked='checked' ";
					}
					$usersummary .=" /></td>\n";
				}
				if($parent['manage_label']) {
					$usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"manage_label\" id=\"manage_label\" value=\"manage_label\"";
					if($usr['manage_label']) {
						$usersummary .= " checked='checked' ";
					}
					$usersummary .=" /></td>\n";
				}

				$usersummary .= "<tr><form method='post' action='$scriptname'></tr>"	// added by Dennis
				."\n<tr><td colspan='8' align='center'>"
				."<input type='submit' value='".$clang->gT("Save Now")."' />"
				."<input type='hidden' name='action' value='userrights' />"
				."<input type='hidden' name='uid' value='{$postuserid}' /></td></tr>"
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


if($action == "setasadminchild")
{
	// Set user as child of ADMIN FOR 
	// MORE RIGHT MANAGEMENT POSSIBILITIES
	// DON'T TOUCH user CHILDS, they remain his childs

	if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
	{
		$query = "UPDATE ".db_table_name('users')." SET parent_id =1 WHERE uid = ".$postuserid;
		$connect->Execute($query) or safe_die($connect->ErrorMsg()." ".$query); //Checked
		$usersummary = "<br /><strong>".$clang->gT("Setting as Administrator Child")."</strong><br />"
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
	$usersummary = "<table width='100%' border='0'>\n"
	. "<tr><td colspan='6' class='header'>"
	. $clang->gT("User Control")
    ."</td></tr>\n"
	. "<tr>\n"
	. "<th width='20%'>".$clang->gT("Username")."</th>\n"
	. "<th width='20%'>".$clang->gT("Email")."</th>\n"
	. "<th width='20%'>".$clang->gT("Full name")."</th>\n"
	. "<th width='15%'>".$clang->gT("Password")."</th>\n"
	. "<th width='15%'>".$clang->gT("Created by")."</th>\n"
	. "<th></th>\n"
	. "</tr>\n";

	$userlist = getuserlist();
	$ui = count($userlist);
	$usrhimself = $userlist[0];
	unset($userlist[0]);

	//	output users
	// output admin user only if the user logged in has user management rights
//	if ($_SESSION['USER_RIGHT_DELETE_USER']||$_SESSION['USER_RIGHT_CREATE_USER']||$_SESSION['USER_RIGHT_SUPERADMIN']){


		$usersummary .= "<tr class='oddrow'>\n"
		. "<td class='oddrow' align='center'><strong>{$usrhimself['user']}</strong></td>\n"
		. "<td class='oddrow' align='center'><strong>{$usrhimself['email']}</strong></td>\n"
		. "<td class='oddrow' align='center'><strong>{$usrhimself['full_name']}</strong></td>\n"
		. "<td class='oddrow' align='center'><strong>********</strong></td>\n";
		
		if(isset($usrhimself['parent_id']) && $usrhimself['parent_id']!=0) { 
		$uquery = "SELECT users_name FROM ".db_table_name('users')." WHERE uid=".$usrhimself['parent_id'];
		$uresult = db_execute_assoc($uquery); //Checked
		$srow = $uresult->FetchRow();
			$usersummary .= "<td class='oddrow' align='center'><strong>{$srow['users_name']}</strong></td>\n";
		}
		else
		{
			$usersummary .= "<td class='oddrow' align='center'><strong>---</strong></td>\n";
		}
		$usersummary .= "<td class='oddrow' align='center' style='padding:3px;'>\n";
		
//		if ($_SESSION['USER_RIGHT_DELETE_USER']||$_SESSION['USER_RIGHT_CREATE_USER']||$_SESSION['USER_RIGHT_SUPERADMIN'] || 1 == 1)
//		{
			$usersummary .= "<form method='post' action='$scriptname'>"
			."<input type='submit' value='".$clang->gT("Edit User")."' />"
			."<input type='hidden' name='action' value='modifyuser' />"
			."<input type='hidden' name='uid' value='{$usrhimself['uid']}' />"
			."</form>";
//		}

		// Standard users and SuperAdmins are allowed to delete all successor users (but the admin not himself)
		// 
//		if ($usrhimself['parent_id'] != 0 && ($_SESSION['USER_RIGHT_DELETE_USER'] == 1 || ($usrhimself['uid'] == $_SESSION['loginID'])))
		if ($usrhimself['parent_id'] != 0 && $_SESSION['USER_RIGHT_DELETE_USER'] == 1 )
		{
			$usersummary .= "<form method='post' action='$scriptname?action=deluser'>"
			."<input type='submit' value='".$clang->gT("Delete")."' onclick='return confirm(\"".$clang->gT("Are you sure you want to delete this entry?","js")."\")' />"
			."<input type='hidden' name='action' value='deluser' />"
			."<input type='hidden' name='user' value='{$usrhimself['user']}' />"
			."<input type='hidden' name='uid' value='{$usrhimself['uid']}' />"
			."</form>";
		}
	
		$usersummary .= "</td>\n"
		. "</tr>\n";
	
		// empty row
		if(count($userlist) > 0) $usersummary .= "<tr>\n<td height=\"20\" colspan=\"6\"></td>\n</tr>";
//	}

	
	// other users
	$row = 0;
	$usr_arr = $userlist;
	for($i=1; $i<=count($usr_arr); $i++)
	{
		if (!isset($bgcc)) {$bgcc="evenrow";}
		else
		{
			if ($bgcc == "evenrow") {$bgcc = "oddrow";}
			else {$bgcc = "evenrow";}
		}
		$usr = $usr_arr[$i];
		$usersummary .= "<tr class='$bgcc'>\n";

		$usersummary .= "<td class='$bgcc' align='center'>{$usr['user']}</td>\n"
		. "<td class='$bgcc' align='center'><a href='mailto:{$usr['email']}'>{$usr['email']}</a></td>\n"
		. "<td class='$bgcc' align='center'>{$usr['full_name']}</td>\n";

		// passwords of other users will not be displayed
		$usersummary .=  "<td class='$bgcc' align='center'>******</td>\n";

		// Get Parent's User Name
		$uquery = "SELECT users_name FROM ".db_table_name('users')." WHERE uid=".$usr['parent_id'];
		$uresult = db_execute_assoc($uquery); //Checked
		$userlist = array();
		$srow = $uresult->FetchRow();
		$usr['parent'] = $srow['users_name'];
		/*
		if($_SESSION['USER_RIGHT_SUPERADMIN'])
		{
			$usersummary .= "<td align='center'>"
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
			// ==> because it is parent_id ;-)
			if (isset($usr['parent_id']))
			{
				$usersummary .= "<td class='$bgcc' align='center'>{$usr['parent']}</td>\n";
			} else 
			{
				$usersummary .= "<td class='$bgcc' align='center'>-----</td>\n";
			}
		//}
		
		$usersummary .= "<td class='$bgcc' align='center' style='padding:3px;'>\n";
		// users are allowed to delete all successor users (but the admin not himself)
		//  || ($usr['uid'] == $_SESSION['loginID']))
		if (($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || ($_SESSION['USER_RIGHT_DELETE_USER'] == 1  && $usr['parent_id'] == $_SESSION['loginID']))&& $usr['uid']!=1)
		{
			$usersummary .= "<form method='post' action='$scriptname?action=deluser'>"
			."<input type='submit' value='".$clang->gT("Delete")."' onclick='return confirm(\"".$clang->gT("Are you sure you want to delete this entry?","js")."\")' />"
			."<input type='hidden' name='action' value='deluser' />"
			."<input type='hidden' name='user' value='{$usr['user']}' />"
			."<input type='hidden' name='uid' value='{$usr['uid']}' />"
			."</form>";
		}
		if ( (($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 &&
			$usr['uid'] != $_SESSION['loginID'] ) || 
		     ($_SESSION['USER_RIGHT_CREATE_USER'] == 1 && 
			$usr['parent_id'] == $_SESSION['loginID'])) && $usr['uid']!=1)
		{
			$usersummary .= "<form method='post' action='$scriptname'>"
			."<input type='submit' value='".$clang->gT("Set User Rights")."' />"
			."<input type='hidden' name='action' value='setuserrights' />"
			."<input type='hidden' name='user' value='{$usr['user']}' />"
			."<input type='hidden' name='uid' value='{$usr['uid']}' />"
			."</form>";
		}
		if ($_SESSION['loginID'] == "1" && $usr['parent_id'] !=1 )
		{
			$usersummary .= "<form method='post' action='$scriptname'>"
			."<input type='submit' value='".$clang->gT("Take Ownership")."' />"
			."<input type='hidden' name='action' value='setasadminchild' />"
			."<input type='hidden' name='user' value='{$usr['user']}' />"
			."<input type='hidden' name='uid' value='{$usr['uid']}' />"
			."</form>";
		}
		if (($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $_SESSION['USER_RIGHT_MANAGE_TEMPLATE'] == 1)  && $usr['uid']!=1)
		{
			$usersummary .= "<form method='post' action='$scriptname'>"
			."<input type='submit' value='".$clang->gT("Set Template Rights")."' />"
			."<input type='hidden' name='action' value='setusertemplates' />"
			."<input type='hidden' name='user' value='{$usr['user']}' />"
			."<input type='hidden' name='uid' value='{$usr['uid']}' />"
			."</form>";
		}
		if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $usr['uid'] == $_SESSION['loginID'] || ($_SESSION['USER_RIGHT_CREATE_USER'] == 1 && $usr['parent_id'] == $_SESSION['loginID']))
		{
			$usersummary .= "<form method='post' action='$scriptname'>"
			."<input type='submit' value='".$clang->gT("Edit User")."' />"
			."<input type='hidden' name='action' value='modifyuser' />"
			."<input type='hidden' name='uid' value='{$usr['uid']}' />"
			."</form>";
		}
		$usersummary .= "</td>\n"
		. "</tr>\n";
		$row++;
	}
    $usersummary .= "</table><br />";

	if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $_SESSION['USER_RIGHT_CREATE_USER'])
	{
		$usersummary .= "<form action='$scriptname' method='post'>\n"
		. "<table width='100%'><tr>\n"
        . "<th colspan='6'>".$clang->gT("Add User")."</th>\n"
        . "</tr><tr>\n"
		. "<td align='center' width='20%'><input type='text' name='new_user' /></td>\n"
		. "<td align='center' width='20%'><input type='text' name='new_email' /></td>\n"
		. "<td align='center' width='20%' ><input type='text' name='new_full_name' /></td><td width='15%'>&nbsp;</td><td width='15%'>&nbsp;</td>\n"
		. "<td align='center' width='15%'><input type='submit' value='".$clang->gT("Add User")."' />"
		. "<input type='hidden' name='action' value='adduser' /></td>\n"
		. "</tr></table></form>\n";
	}
	
}

if ($action == "addusergroup")
{
	if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)  // for now only admins may do that
	{
		$usersummary = "<form action='$scriptname'  method='post'><table  width='100%' class='form2columns'>\n<tr><th colspan='2'>\n"
		. "<strong>".$clang->gT("Add User Group")."</strong></th></tr>\n"
		. "<tr>\n"
		. "<td><strong>".$clang->gT("Name:")."</strong></td>\n"
		. "<td><input type='text' size='50' name='group_name' /><font color='red' face='verdana' size='1'> ".$clang->gT("Required")."</font></td></tr>\n"
		. "<tr><td><strong>".$clang->gT("Description:")."</strong></td>\n"
		. "<td><textarea cols='50' rows='4' name='group_description'></textarea></td></tr>\n"
		. "<tr><td colspan='2' class='centered'><input type='submit' value='".$clang->gT("Add Group")."' />\n"
		. "<input type='hidden' name='action' value='usergroupindb' />\n"
		. "</td></table>\n"
		. "</form>\n";
	}
}

if ($action == "editusergroup")
{
	if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
	{
		$query = "SELECT * FROM ".db_table_name('user_groups')." WHERE ugid = ".$_GET['ugid']." AND owner_id = ".$_SESSION['loginID'];
		$result = db_select_limit_assoc($query, 1);
		$esrow = $result->FetchRow();
		$usersummary = "<form action='$scriptname' name='editusergroup' method='post'>"
		. "<table width='100%' border='0' class='form2columns'>\n<tr><th colspan='2'>\n"
		. "<strong>".$clang->gT("Edit User Group (Owner: ").$_SESSION['user'].")</strong></th></tr>\n"
		. "<tr>\n"
		. "<td><strong>".$clang->gT("Name:")."</strong></td>\n"
		. "<td><input type='text' size='50' name='name' value=\"{$esrow['name']}\" /></td></tr>\n"
		. "<tr><td><strong>".$clang->gT("Description:")."</strong></td>\n"
		. "<td><textarea cols='50' rows='4' name='description'>{$esrow['description']}</textarea></td></tr>\n"
		. "<tr><td colspan='2' class='centered'><input type='submit' value='".$clang->gT("Update User Group")."' />\n"
		. "<input type='hidden' name='action' value='editusergroupindb' />\n"
		. "<input type='hidden' name='owner_id' value='".$_SESSION['loginID']."' />\n"
		. "<input type='hidden' name='ugid' value='$ugid' />\n"
		. "</td></tr>\n"
		. "</table>\n"
		. "</form>\n";
	}
}

if ($action == "mailusergroup")
{
	$query = "SELECT a.ugid, a.name, a.owner_id, b.uid FROM ".db_table_name('user_groups') ." AS a LEFT JOIN ".db_table_name('user_in_groups') ." AS b ON a.ugid = b.ugid WHERE a.ugid = {$ugid} AND uid = {$_SESSION['loginID']} ORDER BY name";
	$result = db_execute_assoc($query); //Checked
	$crow = $result->FetchRow();


	$usersummary = "<form action='$scriptname' name='mailusergroup' method='post'>"
	. "<table width='100%' border='0' class='form2columns'>\n<tr><th colspan='2'>\n"
	. "<strong>".$clang->gT("Mail to all Members")."</strong></th></tr>\n"
	. "<tr>\n"
	. "<td><strong>".$clang->gT("Send me a copy:")."</strong></td>\n"
	. "<td><input name='copymail' type='checkbox' class='checkboxbtn' value='1' /></td></tr>\n"
	. "<tr>\n"
	. "<td><strong>".$clang->gT("Subject:")."</strong></td>\n"
	. "<td><input type='text' size='50' name='subject' value='' /></td></tr>\n"
	. "<tr><td><strong>".$clang->gT("Message:")."</strong></td>\n"
	. "<td><textarea cols='50' rows='4' name='body'></textarea></td></tr>\n"
	. "<tr><td colspan='2' class='centered'><input type='submit' value='".$clang->gT("Send")."' />\n"
	. "<input type='reset' value='".$clang->gT("Reset")."' /><br />"
	. "<input type='hidden' name='action' value='mailsendusergroup' />\n"
	. "<input type='hidden' name='ugid' value='$ugid' />\n"
	. "</td></tr>\n"
	. "</table>\n"
	. "</form>\n";
}

if ($action == "delusergroup")
{
		if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
	{
	$usersummary = "<br /><strong>".$clang->gT("Deleting User Group")."</strong><br />\n";

	if(!empty($postusergroupid) && ($postusergroupid > -1))
	{
		$query = "SELECT ugid, name, owner_id FROM ".db_table_name('user_groups')." WHERE ugid = {$postusergroupid} AND owner_id = ".$_SESSION['loginID'];
		$result = db_select_limit_assoc($query, 1);
		if($result->RecordCount() > 0)
		{
			$row = $result->FetchRow();

			$remquery = "DELETE FROM ".db_table_name('user_groups')." WHERE ugid = {$postusergroupid} AND owner_id = {$_SESSION['loginID']}";
			if($connect->Execute($remquery)) //Checked
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

if ($action == "usergroupindb")
{
	if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
	{
		$usersummary = "<br /><strong>".$clang->gT("Adding User Group")."...</strong><br />\n";
	
		$db_group_name = db_quote($_POST['group_name']);
		$db_group_description = db_quote($_POST['group_description']);
		$html_group_name = html_escape($_POST['group_name']);
		$html_group_description = html_escape($_POST['group_description']);
	
		if(isset($db_group_name) && strlen($db_group_name) > 0)
		{
			$ugid = addUserGroupInDB($db_group_name, $db_group_description);
			if($ugid > 0)
			{
				$usersummary .= "<br />".$clang->gT("Group Name").": ".$html_group_name."<br />\n";
	
				if(isset($db_group_description) && strlen($db_group_description) > 0)
				{
					$usersummary .= $clang->gT("Description: ").$html_group_description."<br />\n";
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
	else
	{
		include("access_denied.php");
	}
	
}

if ($action == "mailsendusergroup")
{
	$usersummary = "<br /><strong>".$clang->gT("Mail to all Members")."</strong><br />\n";

	// user must be in user group
	// or superadmin
	$query = "SELECT uid FROM ".db_table_name('user_in_groups') ." WHERE ugid = {$ugid} AND uid = {$_SESSION['loginID']}";
	$result = db_execute_assoc($query); //Checked

	if($result->RecordCount() > 0 ||
		$_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
	{

    	$eguquery = "SELECT * FROM ".db_table_name("user_in_groups")." AS a INNER JOIN ".db_table_name("users")." AS b ON a.uid = b.uid WHERE ugid = " . $ugid . " AND b.uid != {$_SESSION['loginID']} ORDER BY b.users_name";
    	$eguresult = db_execute_assoc($eguquery); //Checked
    	$addressee = '';
    	$to = '';
    	while ($egurow = $eguresult->FetchRow())
    	{
    		$to .= $egurow['users_name']. ' <'.$egurow['email'].'>'. ', ' ;
    		$addressee .= $egurow['users_name'].', ';
    	}
    	$to = substr("$to", 0, -2);
    	$addressee = substr("$addressee", 0, -2);

		$from_user = "SELECT email, users_name FROM ".db_table_name("users")." WHERE uid = " .$_SESSION['loginID'];
		$from_user_result = db_execute_assoc($from_user); //Checked
		$from_user_row = $from_user_result->FetchRow();

		$from = $from_user_row['users_name'].' <'.$from_user_row['email'].'> ';

		$ugid = $postusergroupid;
		$body = $_POST['body'];
		$subject = $_POST['subject'];

		if(isset($_POST['copymail']) && $_POST['copymail'] == 1)
		{
			$to .= ", " . $from;
		}

		$body = str_replace("\n.", "\n..", $body);
		$body = wordwrap($body, 70);

    
        //echo $body . '-'.$subject .'-'.'<pre>'.htmlspecialchars($to).'</pre>'.'-'.$from;
		if (MailTextMessage( $body, $subject, $to, $from,''))
		{
			$usersummary = "<br /><strong>".$clang->gT("Message(s) sent successfully!")."</strong><br />\n"
			. "<br />".$clang->gT("To:")." $addressee<br />\n"
			. "<br /><a href='$scriptname?action=editusergroups&amp;ugid={$ugid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
		else
		{
            $usersummary .= sprintf($clang->gT("Email to %s failed. Error Message:"),$to)." ".$maildebug."<br />";
            if ($debug>0) 
            {
                $usersummary .= "<br /><pre>Subject : $subject<br /><br />".htmlspecialchars($maildebugbody)."<br /></pre>";
            }

			$usersummary .= "<br /><a href='$scriptname?action=mailusergroup&amp;ugid={$ugid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
	}
	else
	{
		include("access_denied.php");
	}
}

if ($action == "editusergroupindb")
{

	if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
	{
		$ugid = $postusergroupid;
	
		$db_name = db_quote($_POST['name']);
		$db_description = db_quote($_POST['description']);
		$html_name = html_escape($_POST['name']);
		$html_description = html_escape($_POST['description']);
	
		if(updateusergroup($db_name, $db_description, $ugid))
		{
			$usersummary = "<br /><strong>".$clang->gT("Edit User Group Successfully!")."</strong><br />\n";
			$usersummary .= "<br />".$clang->gT("Name").": {$html_name}<br />\n";
			$usersummary .= $clang->gT("Description: ").$html_description."<br />\n";
			$usersummary .= "<br /><a href='$scriptname?action=editusergroups&amp;ugid={$ugid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
		else $usersummary .= "<br /><strong>".$clang->gT("Failed to update!")."</strong><br />\n"
		. "<br /><a href='$scriptname?action=editusergroups'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
	}
	else
	{
		include("access_denied.php");
	}
}

if ($action == "editusergroups" )
{
	// REMOVING CONDITION ON loginID == 1
	// editusergroups is only to display groups
	// a user is in
	//if ( $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
	if ( isset($_SESSION['loginID']))
	{
		if(isset($_GET['ugid']))
		{
			$ugid = sanitize_int($_GET['ugid']);
	
			$query = "SELECT a.ugid, a.name, a.owner_id, a.description, b.uid FROM ".db_table_name('user_groups') ." AS a LEFT JOIN ".db_table_name('user_in_groups') ." AS b ON a.ugid = b.ugid WHERE a.ugid = {$ugid} AND uid = {$_SESSION['loginID']} ORDER BY name";
			$result = db_execute_assoc($query); //Checked
			$crow = $result->FetchRow();
	
			if($result->RecordCount() > 0)
			{
	
				if(!empty($crow['description']))
				{
					$usergroupsummary .= "<table width='100%' border='0'>\n"
					. "<tr><td align='justify' colspan='2' height='4'>"
					. "<font size='2' ><strong>".$clang->gT("Description: ")."</strong>"
					. "{$crow['description']}</font></td></tr>\n"
					. "</table>";
				}
	
	
				$eguquery = "SELECT * FROM ".db_table_name("user_in_groups")." AS a INNER JOIN ".db_table_name("users")." AS b ON a.uid = b.uid WHERE ugid = " . $ugid . " ORDER BY b.users_name";
				$eguresult = db_execute_assoc($eguquery); //Checked
				$usergroupsummary .= "<table  width='100%' border='0'>\n"
				. "<tr>\n"
				. "<th>".$clang->gT("Username")."</th>\n"
				. "<th>".$clang->gT("Email")."</th>\n"
				. "<th width='25%'>".$clang->gT("Action")."</th>\n"
				. "</tr>\n";
	
				$query2 = "SELECT ugid FROM ".db_table_name('user_groups')." WHERE ugid = ".$ugid." AND owner_id = ".$_SESSION['loginID'];
				$result2 = db_select_limit_assoc($query2, 1);
				$row2 = $result2->FetchRow();
	
				$row = 1;
				$usergroupentries='';
				while ($egurow = $eguresult->FetchRow())
				{
					if (!isset($bgcc)) {$bgcc="evenrow";}
					else
					{
						if ($bgcc == "evenrow") {$bgcc = "oddrow";}
						else {$bgcc = "evenrow";}
					}
	
					if($egurow['uid'] == $crow['owner_id'])
					{
						$usergroupowner = "<tr class='$bgcc'>\n"
						. "<td align='center'><strong>{$egurow['users_name']}</strong></td>\n"
						. "<td align='center'><strong>{$egurow['email']}</strong></td>\n"
						. "<td align='center'>&nbsp;</td></tr>\n";
						continue;
					}
					//	output users
					
					if($row == 1){ $usergroupentries .= "<tr>\n<td height=\"20\" colspan=\"6\"></td>\n</tr>"; $row++;}
					//if(($row % 2) == 0) $usergroupentries .= "<tr  bgcolor='#999999'>\n";
					//else $usergroupentries .= "<tr>\n";
					$usergroupentries .= "<tr class='$bgcc'>\n";
					$usergroupentries .= "<td align='center'>{$egurow['users_name']}</td>\n"
					. "<td align='center'>{$egurow['email']}</td>\n"
					. "<td align='center' style='padding-top:10px;'>\n";
	
					// owner and not himself    or    not owner and himself
//					if((isset($row2['ugid']) && $_SESSION['loginID'] != $egurow['uid']) || (!isset($row2['ugid']) && $_SESSION['loginID'] == $egurow['uid']))
					// Currently only admin can do this
					// So hide button unless admin
					if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
					{
						$usergroupentries .= "<form method='post' action='$scriptname?action=deleteuserfromgroup&amp;ugid=$ugid'>"
						." <input type='submit' value='".$clang->gT("Delete")."' onclick='return confirm(\"".$clang->gT("Are you sure you want to delete this entry?","js")."\")' />"
						." <input type='hidden' name='user' value='{$egurow['users_name']}' />"
						." <input name='uid' type='hidden' value='{$egurow['uid']}' />"
						." <input name='ugid' type='hidden' value='{$ugid}' />";
					}
					$usergroupentries .= "</form>"
					. "</td>\n"
					. "</tr>\n";
					$row++;
				}
				$usergroupsummary .= $usergroupowner;
	            if (isset($usergroupentries)) {$usergroupsummary .= $usergroupentries;};
	
				if(isset($row2['ugid']))
				{
					$usergroupsummary .= "<form action='$scriptname?ugid={$ugid}' method='post'>\n"
					. "<tr><td></td>\n"
					. "<td></td>"
					. "<td align='center'><select name='uid'>\n"
					. getgroupuserlist()
					. "</select>\n"
					. "<input type='submit' value='".$clang->gT("Add User")."' />\n"
					. "<input type='hidden' name='action' value='addusertogroup' /></td></form>\n"
					. "</td>\n"
					. "</tr>\n"
					. "</form>\n";
				}
			}
			else
			{
				include("access_denied.php");
			}
		}
	}
	else
	{
		include("access_denied.php");
	}
}

if($action == "deleteuserfromgroup")
{
	if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
	{
		$ugid = $postusergroupid;
		$uid = $postuserid;
		$usersummary = "<br /><strong>".$clang->gT("Delete User")."</strong><br />\n";
	
		$query = "SELECT ugid, owner_id FROM ".db_table_name('user_groups')." WHERE ugid = ".$ugid." AND ((owner_id = ".$_SESSION['loginID']." AND owner_id != ".$uid.") OR (owner_id != ".$_SESSION['loginID']." AND $uid = ".$_SESSION['loginID']."))";
		$result = db_execute_assoc($query); //Checked
		if($result->RecordCount() > 0)
		{
			$remquery = "DELETE FROM ".db_table_name('user_in_groups')." WHERE ugid = {$ugid} AND uid = {$uid}";
			if($connect->Execute($remquery)) //Checked
			{
				$usersummary .= "<br />".$clang->gT("Username").": ".sanitize_xss_string(strip_tags($_POST['user']))."<br />\n";
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
		if($_SESSION['loginID'] != $postuserid)
		{
			$usersummary .= "<br /><a href='$scriptname?action=editusergroups&amp;ugid=$ugid'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
		else
		{
			$usersummary .= "<br /><a href='$scriptname?action=editusergroups'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
	}
	else
	{
		include("access_denied.php");
	}
}




if($action == "addusertogroup")
{ 
	$ugid=returnglobal('ugid');
    if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
	{
		$addsummary = "<br /><strong>".$clang->gT("Adding User to group")."...</strong><br />\n";
	
		$query = "SELECT ugid, owner_id FROM ".db_table_name('user_groups')." WHERE ugid = {$ugid} AND owner_id = ".$_SESSION['loginID']." AND owner_id != ".$postuserid;
		$result = db_execute_assoc($query); //Checked
		if($result->RecordCount() > 0)
		{
			if($postuserid > 0)
			{
				$isrquery = "INSERT INTO {$dbprefix}user_in_groups VALUES({$ugid},{$postuserid})";
				$isrresult = $connect->Execute($isrquery); //Checked
	
				if($isrresult)
				{
					$addsummary .= "<br />".$clang->gT("User added.")."<br />\n";
				}
				else  // ToDo: for this to happen the keys on the table must still be set accordingly
				{
					// Username already exists.
					$addsummary .= "<br /><strong>".$clang->gT("Failed to add User.")."</strong><br />\n" . " " . $clang->gT("Username already exists.")."<br />\n";
				}
				$addsummary .= "<br /><a href='$scriptname?action=editusergroups&amp;ugid={$ugid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
			}
			else
			{
				$addsummary .= "<br /><strong>".$clang->gT("Failed to add User.")."</strong><br />\n" . " " . $clang->gT("No Username selected.")."<br />\n";
				$addsummary .= "<br /><a href='$scriptname?action=editusergroups&amp;ugid={$ugid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
			}
		}
		else
		{
			include("access_denied.php");
		}
	}
	else
	{
		include("access_denied.php");
	}
}


function updateusergroup($name, $description, $ugid)
{
    global $dbprefix, $scriptname, $connect;

    $uquery = "UPDATE ".db_table_name('user_groups')." SET name = '$name', description = '$description' WHERE ugid =$ugid";
    // TODO
    return $connect->Execute($uquery) or safe_die($connect->ErrorMsg()) ; //Checked
}

function refreshtemplates() {
	global $connect ;
	global $dbprefix ;
	
	$template_a = gettemplatelist();
	foreach ($template_a as $tp) {
		// check for each folder if there is already an entry in the database
		// if not create it with current user as creator (user with rights "create user" can assign template rights)
		$query = "SELECT * FROM ".$dbprefix."templates WHERE folder LIKE '".$tp."'";
		$result = db_execute_assoc($query) or safe_die($connect->ErrorMsg()); //Checked 
		
		if ($result->RecordCount() == 0) {
			$query2 = "INSERT INTO ".$dbprefix."templates SET folder='".$tp."', creator=".$_SESSION['loginID'] ;
			$connect->Execute($query2);  //Checked
		}
	}
	return true;
}

// adds Usergroups in Database by Moses
function addUserGroupInDB($group_name, $group_description) {
    global $connect;
    $iquery = "INSERT INTO ".db_table_name('user_groups')." (name, description, owner_id) VALUES('{$group_name}', '{$group_description}', '{$_SESSION['loginID']}')";
    if($connect->Execute($iquery)) { //Checked
        $id = $connect->Insert_Id(db_table_name_nq('user_groups'),'ugid');
        if($id > 0) {
             $iquery = "INSERT INTO ".db_table_name('user_in_groups')." VALUES($id, '{$_SESSION['loginID']}')";
            $connect->Execute($iquery ) or safe_die($connect->ErrorMsg()); //Checked
        }
        return $id;
    } else {
        return -1;
    }
}

?>
