<?php
/*
<<<<<<< HEAD
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
=======
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

>>>>>>> refs/heads/stable_plus
//Ensure script is not run directly, avoid path disclosure
if (!isset($dbprefix) || isset($_REQUEST['dbprefix'])) {die("Cannot run this script directly");}
if (isset($_POST['uid'])) {$postuserid=sanitize_int($_POST['uid']);}
if (isset($_POST['ugid'])) {$postusergroupid=sanitize_int($_POST['ugid']);}

<<<<<<< HEAD
if (get_magic_quotes_gpc())
{$_POST  = array_map('recursive_stripslashes', $_POST);}

$js_admin_includes[]='../scripts/jquery/jquery.tablesorter.min.js';
$js_admin_includes[]='scripts/users.js';


if (($ugid && !$surveyid) || $action == "editusergroups" || $action == "addusergroup" || $action=="usergroupindb" || $action == "editusergroup" || $action == "mailusergroup")
{

    if($ugid)
    {
        $grpquery = "SELECT gp.* FROM ".db_table_name('user_groups')." AS gp, ".db_table_name('user_in_groups')." AS gu WHERE gp.ugid=gu.ugid AND gp.ugid = $ugid AND gu.uid=".$_SESSION['loginID'];
        $grpresult = db_execute_assoc($grpquery);//Checked
        $grpresultcount = $grpresult->RecordCount();
        if ($grpresultcount>0)
        {
        $grow = array_map('htmlspecialchars', $grpresult->FetchRow());
    }
    }
    $usergroupsummary = "<div class='menubar'>\n"
    . "<div class='menubar-title ui-widget-header'>\n"
    . "<strong>".$clang->gT("User Group")."</strong>";
    if($ugid && $grpresultcount > 0)
    {
        $usergroupsummary .= " {$grow['name']}\n";
    }


    $usergroupsummary .= "</div>\n"
    . "<div class='menubar-main'>\n"
    . "<div class='menubar-left'>\n"
    . "<img src='$imageurl/blank.gif' alt='' width='55' height='20' />\n"
    . "<img src='$imageurl/seperator.gif' alt='' />\n";

    if($ugid && $grpresultcount > 0)
    {
        $usergroupsummary .= "<a href=\"#\" onclick=\"window.location='$scriptname?action=mailusergroup&amp;ugid=$ugid'\""
        ." title='".$clang->gTview("Mail to all Members")."'> "
        ."<img src='$imageurl/invite.png' alt='".$clang->gT("Mail to all Members")."' name='MailUserGroup' /></a>\n" ;
    }
    else
    {
        $usergroupsummary .= "<img src='$imageurl/blank.gif' alt='' width='40' height='20' />\n";
    }
    $usergroupsummary .= "<img src='$imageurl/blank.gif' alt='' width='78' height='20' />\n"
    . "<img src='$imageurl/seperator.gif' alt='' />\n";

    if($ugid && $grpresultcount > 0 &&
    $_SESSION['loginID'] == $grow['owner_id'])
    {
        $usergroupsummary .=  "<a href=\"#\" onclick=\"window.location='$scriptname?action=editusergroup&amp;ugid=$ugid'\""
        . " title='".$clang->gTview("Edit Current User Group")."'>"
        . "<img src='$imageurl/edit.png' alt='".$clang->gT("Edit Current User Group")."' name='EditUserGroup' /></a>\n" ;
    }
    else
    {
        $usergroupsummary .= "<img src='$imageurl/blank.gif' alt='' width='40' height='20' />\n";
    }

    if($ugid && $grpresultcount > 0 &&
    $_SESSION['loginID'] == $grow['owner_id'])
    {
        //		$usergroupsummary .= "<a href='$scriptname?action=delusergroup&amp;ugid=$ugid' onclick=\"return confirm('".$clang->gT("Are you sure you want to delete this entry?","js")."')\""
        $usergroupsummary .= "<a href='#' onclick=\"if (confirm('".$clang->gT("Are you sure you want to delete this entry?","js")."')) {".get2post("$scriptname?action=delusergroup&amp;ugid=$ugid")."}\" "
        . "title='".$clang->gTview("Delete Current User Group")."'>"
        . "<img src='$imageurl/delete.png' alt='".$clang->gT("Delete Current User Group")."' name='DeleteUserGroup'  /></a>\n";
    }
    else
    {
        $usergroupsummary .= "<img src='$imageurl/blank.gif' alt='' width='40' height='20' />\n";
    }
    $usergroupsummary .= "<img src='$imageurl/blank.gif' alt='' width='92' height='20' />\n"
    . "<img src='$imageurl/seperator.gif' alt='' />\n"
    . "</div>\n"
    . "<div class='menubar-right'>\n"
    . "<font class=\"boxcaption\">".$clang->gT("User Groups").":</font>&nbsp;<select name='ugid' "
    . "onchange=\"window.location=this.options[this.selectedIndex].value\">\n"
    . getusergrouplist()
    . "</select>\n";
    if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
    {
        $usergroupsummary .= "<a href='$scriptname?action=addusergroup'"
        ." title='".$clang->gTview("Add New User Group")."'>"
        ."<img src='$imageurl/add.png' alt='".$clang->gT("Add New User Group")."' "
        ."name='AddNewUserGroup' onclick=\"window.location=''\" /></a>\n";
    }
    $usergroupsummary .= "<img src='$imageurl/seperator.gif' alt='' />\n"
    . "<img src='$imageurl/blank.gif' alt='' width='82' height='20' />\n"
    . "</div></div>\n"
    . "</div>\n";
    $usergroupsummary .= "<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>"; //CSS Firefox 2 transition fix


}


if ($action == "adduser" || $action=="deluser" ||$action=="finaldeluser" || $action == "moduser" || $action == "userrights"  || $action == "usertemplates")
{
    include("usercontrol.php");
}

if ($action == "setusertemplates")
{
    refreshtemplates();
    $usersummary = "\n<form action='$scriptname' method='post'>\n\t
    <div class='header ui-widget-header'>".$clang->gT('Edit template permissions')."</div>\n
    <table id=\"user-template-rights\" width='50%' border='0' cellpadding='3' style='margin:5px auto 0 auto;'>\n
	<thead>\n
	\t<tr>\n
	\t<th colspan=\"2\" style=\"background-color:#000; color:#fff;\">\n"
    . $clang->gT('Set templates that this user may access').': '.$_POST['user']."</th>\n
	\t</tr>\n";

    $userlist = getuserlist();
    foreach ($userlist as $usr)
    {
        if ($usr['uid'] == $postuserid)
        {
            $templaterights = array();
            $squery = 'SELECT '.db_quote_id('folder').','.db_quote_id('use')." FROM {$dbprefix}templates_rights WHERE uid={$usr['uid']}";
            $sresult = db_execute_assoc($squery) or safe_die($connect->ErrorMsg());//Checked
            while ($srow = $sresult->FetchRow())
            {
                $templaterights[$srow["folder"]] = array("use"=>$srow["use"]);
            }

            $usersummary .= "\t<tr>\n<th>"
            .$clang->gT('Template name')
            ."<br />&nbsp;</th>\n<th>"
            .$clang->gT('Allowed')
            ."<br /><input type='checkbox' alt='".$clang->gT("Check or uncheck all items")."' class='tipme' id='checkall' />"
            ."</th>\n\t</tr>\n"
            ."\t</thead>\n";

            $usersummary .= "<tfoot>\n"
            ."<tr>\n"
            ."<td colspan=\"3\">\n"
            ."\t<input type=\"submit\" value=\"".$clang->gT('Save settings')."\" />\n"
            ."\t<input type=\"hidden\" name=\"action\" value=\"usertemplates\" />\n"
            ."\t<input type=\"hidden\" name=\"uid\" value=\"{$postuserid}\" />\n"
            ."</td>\n"
            ."</tr>\n"
            ."</tfoot>\n";

			$usersummary .= "<tbody>\n";

            $tquery = "SELECT * FROM ".$dbprefix."templates";
            $tresult = db_execute_assoc($tquery) or safe_die($connect->ErrorMsg()); //Checked

            $table_row_odd_even = 'odd';
            while ($trow = $tresult->FetchRow())
            {
                if($table_row_odd_even == 'odd' )
                {
                    $row_class = ' class="row_odd"';
                    $table_row_odd_even = 'even';
                }
                else
                {
                    $row_class = ' class="row_even"';
                    $table_row_odd_even = 'odd';
                };
                $usersummary .= "\t<tr$row_class>\n<td>{$trow["folder"]}</td>\n";
                $usersummary .= "<td><input type=\"checkbox\" class=\"checkboxbtn\" name=\"{$trow["folder"]}_use\" value=\"{$trow["folder"]}_use\"";

                if(isset($templaterights[$trow['folder']]) && $templaterights[$trow['folder']]['use'] == 1)
                {
                    $usersummary .= ' checked="checked"';
                }
                $usersummary .=" /></td>\n\t</tr>\n";
            }
            $usersummary .= "\n</tbody>\n"
            ."\t</table>\n"
            ."</form>\n";

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
        $usersummary = "<div class='header ui-widget-header'>\n".$clang->gT("Editing user")."</div><br />\n"
        ."<form action='$scriptname' method='post'>\n"
        . "<table class='edituser'><thead><tr>\n"
        . "<th>".$clang->gT("Username")."</th>\n"
        . "<th>".$clang->gT("Email")."</th>\n"
        . "<th>".$clang->gT("Full name")."</th>\n"
        . "<th>".$clang->gT("Password")."</th>\n"
        . "</tr></thead>\n";
        $muq = "SELECT a.users_name, a.full_name, a.email, a.uid, b.users_name AS parent FROM ".db_table_name('users')." AS a LEFT JOIN ".db_table_name('users')." AS b ON a.parent_id = b.uid WHERE a.uid='{$postuserid}'";	//	added by Dennis
        //echo($muq);

        $mur = db_select_limit_assoc($muq, 1);
        $usersummary .= "<tbody><tr>";
        while ($mrw = $mur->FetchRow())
        {
            $mrw = array_map('htmlspecialchars', $mrw);
            $usersummary .= "<td align='center'><strong>{$mrw['users_name']}</strong></td>\n"
            . "<td align='center'>\n<input type='text' size='30' name='email' value=\"{$mrw['email']}\" /></td>\n"
            . "<td align='center'>\n<input type='text' size='30' name='full_name' value=\"{$mrw['full_name']}\" />\n"
            . "<input type='hidden' name='user' value=\"{$mrw['users_name']}\" />\n"
            . "<input type='hidden' name='uid' value=\"{$mrw['uid']}\" /></td>\n";
            $usersummary .= "<td align='center'>\n<input type='password' name='pass' value=\"%%unchanged%%\" /></td>\n";
        }
        $usersummary .= "</tr>\n"
        . "</tbody>\n"
        . "</table>\n"
        . "<p>\n"
        . "<input type='submit' value='".$clang->gT("Save")."' />\n"
        . "<input type='hidden' name='action' value='moduser' />\n"
        . "</p>\n"
        . "</form>\n";
    }
    else
    {
        include("access_denied.php");
    }
=======
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
	. "<strong>".$clang->gT("User Group")."</strong> ";
	if($ugid)
	{
		$usergroupsummary .= "{$grow['name']}</td></tr>\n";
	}
	else
	{
		$usergroupsummary .= "</td></tr>\n";
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
		$usergroupsummary .= "\t\t\t\t\t<a href='$scriptname?action=delusergroup&amp;ugid=$ugid' onclick=\"return confirm('".$clang->gT("Are you sure you want to delete this entry.","js")."')\""
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
	$usergroupsummary .= "\t\t\t\t\t<font class=\"boxcaption\">".$clang->gT("User Groups").":</font>&nbsp;<select name='ugid' "
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
		if ($usr['uid'] == $postuserid)
		{
				$squery = "SELECT create_survey, configurator, create_user, delete_user, move_user, manage_template, manage_label FROM {$dbprefix}users WHERE uid={$usr['parent_id']}";	//		added by Dennis
				$sresult = $connect->Execute($squery);
				$parent = $sresult->FetchRow();
				break;
		}
	}
	
	if($_SESSION['loginID'] == 1 || $_SESSION['loginID'] == $postuserid || $parent['create_user'] == 1)
	{
		$usersummary = "<table width='100%' border='0'>\n\t<tr><td colspan='4' class='header'>\n"
		. "\t\t<strong>".$clang->gT("Modifying User")."</td></tr>\n"
		. "\t<tr>\n"
		. "\t\t<th>".$clang->gT("Username")."</th>\n"
		. "\t\t<th>".$clang->gT("Email")."</th>\n"
		. "\t\t<th>".$clang->gT("Full name")."</th>\n"
		. "\t\t<th>".$clang->gT("Password")."</th>\n"
		. "\t</tr>\n";
		$muq = "SELECT a.users_name, a.full_name, a.email, a.uid, b.users_name AS parent FROM ".db_table_name('users')." AS a LEFT JOIN ".db_table_name('users')." AS b ON a.parent_id = b.uid WHERE a.uid='{$postuserid}'";	//	added by Dennis
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
			. "\t\t<input type='hidden' name='uid' value=\"{$mrw['uid']}\" /></td>\n";	
			$usersummary .= "\t<td align='center'>\n\t\t<input type='password' name='pass' value=\"\" /></td>\n";
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
>>>>>>> refs/heads/stable_plus
}

if ($action == "setuserrights")
{
<<<<<<< HEAD
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

        $usersummary ="<form method='post' action='$scriptname'>";

        $usersummary .="<table width='100%' border='0'>\n"
        ."<tr>\n"
        ."<td colspan='7' class='header ui-widget-header' align='center'>"
        ."".$clang->gT("Set User Rights").": ".htmlspecialchars(sanitize_user($_POST['user'])).""
        ."</td>\n"
        ."</tr>\n";

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

                $usersummary .="<tr>\n";

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
                    $usersummary .= "<th align='center'>".$clang->gT("Use all/manage templates")."</th>\n";
                }
                if($parent['manage_label']) {
                    $usersummary .= "<th align='center'>".$clang->gT("Manage Labels")."</th>\n";
                }

                $usersummary .="</tr>\n"
                ."<tr>\n";

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

                $usersummary .="</tr>\n";

                $usersummary .= "\n<tr>\n"
                ."<td colspan='7' align='center'>"
                ."<input type='submit' value='".$clang->gT("Save Now")."' />"
                ."<input type='hidden' name='action' value='userrights' />"
                ."<input type='hidden' name='uid' value='{$postuserid}' />"
                ."</td>\n"
                ."</tr>\n"
                ."</table>\n"
                ."</form>\n";
                continue;
            }	// if
        }	// foreach
    }	// if
    else
    {
        include("access_denied.php");
    }
}	// if


if($subaction == "setasadminchild")
{
    // Set user as child of ADMIN FOR
    // MORE RIGHT MANAGEMENT POSSIBILITIES
    // DON'T TOUCH user CHILDS, they remain his childs

    if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
    {
        $query = "UPDATE ".db_table_name('users')." SET parent_id =1 WHERE uid = ".$postuserid;
        $connect->Execute($query) or safe_die($connect->ErrorMsg()." ".$query); //Checked
        $_SESSION['flashmessage']=$clang->gT("Ownership was successfully changed.");
    }
    else
    {
        include("access_denied.php");
    }
}

if ($action == "editusers")
{
    $usersummary = "<div class='header ui-widget-header'>".$clang->gT("User control")."</div><br />"
    . "<table id='users' class='users' width='100%' border='0'>\n"
    . "<thead>\n"
    . "<tr>\n"
    . "<th>".$clang->gT("Action")."</th>\n";


    $usersummary .= "<th width='20%'>".$clang->gT("Username")."</th>\n"
    . "<th width='20%'>".$clang->gT("Email")."</th>\n"
    . "<th width='20%'>".$clang->gT("Full name")."</th>\n";
    if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
    {
        $usersummary .= "<th width='5%'>".$clang->gT("No of surveys")."</th>\n";
    }
    $usersummary .= "<th width='15%'>".$clang->gT("Created by")."</th>\n"
    . "</tr></thead><tbody>\n";

    $userlist = getuserlist();
    $ui = count($userlist);
    $usrhimself = $userlist[0];
    unset($userlist[0]);

    //	output users
    // output admin user only if the user logged in has user management rights
    $usersummary .= "<tr >\n";

    // Action colum first
    $usersummary .= "<td align='center' style='padding:3px;'>\n";


    $usersummary .= "<form method='post' action='$scriptname'>"
    ."<input type='image' src='$imageurl/token_edit.png' value='".$clang->gT("Edit user")."' />"
    ."<input type='hidden' name='action' value='modifyuser' />"
    ."<input type='hidden' name='uid' value='{$usrhimself['uid']}' />"
    ."</form>";
    if ($usrhimself['parent_id'] != 0 && $_SESSION['USER_RIGHT_DELETE_USER'] == 1 )
    {
        $usersummary .= "<form method='post' action='$scriptname?action=deluser'>"
        ."<input type='submit' value='".$clang->gT("Delete")."' onclick='return confirm(\"".$clang->gT("Are you sure you want to delete this entry?","js")."\")' />"
        ."<input type='hidden' name='action' value='deluser' />"
        ."<input type='hidden' name='user' value='{$usrhimself['user']}' />"
        ."<input type='hidden' name='uid' value='{$usrhimself['uid']}' />"
        ."</form>";
    }

    $usersummary .= "</td>\n";

    $usersummary .= "<td align='center'><strong>{$usrhimself['user']}</strong></td>\n"
    . "<td align='center'><strong>{$usrhimself['email']}</strong></td>\n"
    . "<td align='center'><strong>{$usrhimself['full_name']}</strong></td>\n";
    if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
    {
        $noofsurveys=$connect->GetOne('Select count(*) from '.db_table_name('surveys').' where owner_id='.$usrhimself['uid']);
        $usersummary .= "<td align='center'><strong>{$noofsurveys}</strong></td>\n";
    }

    if(isset($usrhimself['parent_id']) && $usrhimself['parent_id']!=0) {
        $uquery = "SELECT users_name FROM ".db_table_name('users')." WHERE uid=".$usrhimself['parent_id'];
        $uresult = db_execute_assoc($uquery); //Checked
        $srow = $uresult->FetchRow();
        $usersummary .= "<td align='center'><strong>{$srow['users_name']}</strong></td>\n";
    }
    else
    {
        $usersummary .= "<td align='center'><strong>---</strong></td>\n";
    }

    $usersummary.="</tr>\n";

    // other users
    $row = 0;
    $usr_arr = $userlist;
    $noofsurveyslist = array(  );

    //This loops through for each user and checks the amount of surveys against them.
    for($i=1;$i<=count($usr_arr);$i++)
    {
        $noofsurveyslist[$i]=$connect->GetOne('Select count(*) from '.db_table_name('surveys').' where owner_id='.$usr_arr[$i]['uid']);
    }

    for($i=1; $i<=count($usr_arr); $i++)
    {

        $usr = $usr_arr[$i];
        $usersummary .= "<tr>\n";

        $usersummary .= "<td align='center' style='padding:3px;'>\n";
        if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $usr['uid'] == $_SESSION['loginID'] || ($_SESSION['USER_RIGHT_CREATE_USER'] == 1 && $usr['parent_id'] == $_SESSION['loginID']))
        {
            $usersummary .= "<form method='post' action='$scriptname'>"
            ."<input type='image' src='$imageurl/token_edit.png' alt='".$clang->gT("Edit this user")."' />"
            ."<input type='hidden' name='action' value='modifyuser' />"
            ."<input type='hidden' name='uid' value='{$usr['uid']}' />"
            ."</form>";
        }

        if ( (($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 &&
        $usr['uid'] != $_SESSION['loginID'] ) ||
        ($_SESSION['USER_RIGHT_CREATE_USER'] == 1 &&
        $usr['parent_id'] == $_SESSION['loginID'])) && $usr['uid']!=1)
        {
            $usersummary .= "<form method='post' action='$scriptname'>"
            ."<input type='image' src='$imageurl/security_16.png' alt='".$clang->gT("Set global permissions for this user")."' />"
            ."<input type='hidden' name='action' value='setuserrights' />"
            ."<input type='hidden' name='user' value='{$usr['user']}' />"
            ."<input type='hidden' name='uid' value='{$usr['uid']}' />"
            ."</form>";
        }
        if ($_SESSION['loginID'] == "1" && $usr['parent_id'] !=1 )
        {
            $usersummary .= "<form method='post' action='$scriptname'>"
            ."<input type='image' src='$imageurl/takeownership_small.png' alt='".$clang->gT("Take ownership")."' />"
            ."<input type='hidden' name='action' value='editusers' />"
            ."<input type='hidden' name='subaction' value='setasadminchild' />"
            ."<input type='hidden' name='user' value='{$usr['user']}' />"
            ."<input type='hidden' name='uid' value='{$usr['uid']}' />"
            ."</form>";
        }
        if (($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $_SESSION['USER_RIGHT_MANAGE_TEMPLATE'] == 1)  && $usr['uid']!=1)
        {
            $usersummary .= "<form method='post' action='$scriptname'>"
            ."<input type='image' src='$imageurl/templatepermissions_small.png' alt='".$clang->gT("Set template permissions for this user")."' />"
            ."<input type='hidden' name='action' value='setusertemplates' />"
            ."<input type='hidden' name='user' value='{$usr['user']}' />"
            ."<input type='hidden' name='uid' value='{$usr['uid']}' />"
            ."</form>";
        }

        // users are allowed to delete all successor users (but the admin not himself)
        if (($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || ($_SESSION['USER_RIGHT_DELETE_USER'] == 1  && $usr['parent_id'] == $_SESSION['loginID']))&& $usr['uid']!=1)
        {
            $usersummary .= "<form method='post' action='$scriptname?action=deluser'>"
            ."<input type='image' src='$imageurl/token_delete.png' alt='".$clang->gT("Delete this user")."' onclick='return confirm(\"".$clang->gT("Are you sure you want to delete this entry?","js")."\")' />"
            ."<input type='hidden' name='action' value='deluser' />"
            ."<input type='hidden' name='user' value='{$usr['user']}' />"
            ."<input type='hidden' name='uid' value='{$usr['uid']}' />"
            ."</form>";
        }


        $usersummary .= "</td>\n";
        $usersummary .= "<td align='center'>{$usr['user']}</td>\n"
        . "<td align='center'><a href='mailto:{$usr['email']}'>{$usr['email']}</a></td>\n"
        . "<td align='center'>{$usr['full_name']}</td>\n";

        $usersummary .= "<td align='center'>{$noofsurveyslist[$i]}</td>\n";

        // Get Parent's User Name
        $uquery = "SELECT users_name FROM ".db_table_name('users')." WHERE uid=".$usr['parent_id'];
        $uresult = db_execute_assoc($uquery); //Checked
        $userlist = array();
        $srow = $uresult->FetchRow();
        $usr['parent'] = $srow['users_name'];

        //TODO: Find out why parent isn't set
        // ==> because it is parent_id ;-)
        if (isset($usr['parent_id']))
        {
            $usersummary .= "<td align='center'>{$usr['parent']}</td>\n";
        } else
        {
            $usersummary .= "<td align='center'>-----</td>\n";
        }


        $usersummary .= "</tr>\n";
        $row++;
    }
    $usersummary .= "</tbody></table><br />";

    if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $_SESSION['USER_RIGHT_CREATE_USER'])
    {
        $usersummary .= "<form action='$scriptname' method='post'>\n"
        . "<table class='users'><tr class='oddrow'>\n"
        . "<th>".$clang->gT("Add user:")."</th>\n"
        . "<td align='center' width='20%'><input type='text' name='new_user' /></td>\n"
        . "<td align='center' width='20%'><input type='text' name='new_email' /></td>\n"
        . "<td align='center' width='20%' ><input type='text' name='new_full_name' /></td><td width='8%'>&nbsp;</td>\n"
        . "<td align='center' width='15%'><input type='submit' value='".$clang->gT("Add User")."' />"
        . "<input type='hidden' name='action' value='adduser' /></td>\n"
        . "</tr></table></form><br />\n";
    }

=======
	if($_SESSION['loginID'] != $postuserid)
	{
		$usersummary = "<table width='100%' border='0'>\n\t<tr><td colspan='8' class='header' align='center'>\n"
		. "\t\t".$clang->gT("Set User Rights").": ".sanitize_system_string($_POST['user'])."</td></tr>\n";

		$userlist = getuserlist();
		foreach ($userlist as $usr)
		{
			if ($usr['uid'] == $postuserid)
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
						$usersummary .= " checked='checked' ";
					}
					$usersummary .=" /></td>\n";
				}
				if($parent['configurator']) {
					$usersummary .= "\t\t<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"configurator\" value=\"configurator\"";
					if($usr['configurator']) {
						$usersummary .= " checked='checked' ";
					}
					$usersummary .=" /></td>\n";
				}
				if($parent['create_user']) {
					$usersummary .= "\t\t<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"create_user\" value=\"create_user\"";
					if($usr['create_user']) {
						$usersummary .= " checked='checked' ";
					}
					$usersummary .=" /></td>\n";
				}
				if($parent['delete_user']) {
					$usersummary .= "\t\t<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"delete_user\" value=\"delete_user\"";
					if($usr['delete_user']) {
						$usersummary .= " checked='checked' ";
					}
					$usersummary .=" /></td>\n";
				}
				if($parent['move_user']) {
					$usersummary .= "\t\t<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"move_user\" value=\"move_user\"";
					if($usr['move_user']) {
						$usersummary .= " checked='checked' ";
					}
					$usersummary .=" /></td>\n";
				}
				if($parent['manage_template']) {
					$usersummary .= "\t\t<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"manage_template\" value=\"manage_template\"";
					if($usr['manage_template']) {
						$usersummary .= " checked='checked' ";
					}
					$usersummary .=" /></td>\n";
				}
				if($parent['manage_label']) {
					$usersummary .= "\t\t<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"manage_label\" value=\"manage_label\"";
					if($usr['manage_label']) {
						$usersummary .= " checked='checked' ";
					}
					$usersummary .=" /></td>\n";
				}

				$usersummary .= "\t\t\t<tr><form method='post' action='$scriptname'></tr>"	// added by Dennis
				."\t\n\t<tr><td colspan='8' align='center'>"
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

/*  Commented since it is not used and not safe

if($action == "setnewparents")
{
	// muss noch eingeschraenkt werden ...
	if($_SESSION['USER_RIGHT_MOVE_USER'])
	{
		$uid = $postuserid;
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
}*/

if ($action == "editusers")
{
	$usersummary = "<table width='100%' border='0'>\n"
	. "\t\t\t\t<tr><td colspan='6' class='header'>"
	. $clang->gT("User Control")
    ."</td></tr>\n"
	. "\t<tr>\n"
	. "\t\t<th width='20%'>".$clang->gT("Username")."</th>\n"
	. "\t\t<th width='20%'>".$clang->gT("Email")."</th>\n"
	. "\t\t<th width='20%'>".$clang->gT("Full name")."</th>\n"
	. "\t\t<th width='15%'>".$clang->gT("Password")."</th>\n"
	. "\t\t<th width='15%'>".$clang->gT("Created by")."</th>\n"
	. "\t\t<th></th>\n"
	. "\t</tr>\n";

	$userlist = getuserlist();
	$ui = count($userlist);
	$usrhimself = $userlist[0];
	unset($userlist[0]);

	//	output users
	// output admin user only if the user logged in has user management rights
	if ($_SESSION['USER_RIGHT_DELETE_USER']||$_SESSION['USER_RIGHT_CREATE_USER']||$_SESSION['USER_RIGHT_MOVE_USER']){
		$usersummary .= "\t<tr class='oddrow'>\n"
		. "\t<td class='oddrow' align='center'><strong>{$usrhimself['user']}</strong></td>\n"
		. "\t<td class='oddrow' align='center'><strong>{$usrhimself['email']}</strong></td>\n"
		. "\t\t<td class='oddrow' align='center'><strong>{$usrhimself['full_name']}</strong></td>\n"
		. "\t\t<td class='oddrow' align='center'><strong>********</strong></td>\n";
		
		if(isset($usrhimself['parent_id']) && $usrhimself['parent_id']!=0) { 
			$usersummary .= "\t\t<td class='oddrow' align='center'>{$userlist[$usrhimself['parent_id']]['user']}</td>\n";
		}
		else
		{
			$usersummary .= "\t\t<td class='oddrow' align='center'><strong>---</strong></td>\n";
		}
		$usersummary .= "\t\t<td class='oddrow' align='center' style='padding:3px;'>\n";
		
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
			."<input type='submit' value='".$clang->gT("Delete")."' onclick='return confirm(\"".$clang->gT("Are you sure you want to delete this entry.","js")."\")' />"
			."<input type='hidden' name='action' value='deluser' />"
			."<input type='hidden' name='user' value='{$usrhimself['user']}' />"
			."<input type='hidden' name='uid' value='{$usrhimself['uid']}' />"
			."</form>";
		}
	
		$usersummary .= "\t\t</td>\n"
		. "\t</tr>\n";
	
		// empty row
		if(count($userlist) > 0) $usersummary .= "\t<tr>\n\t<td height=\"20\" colspan=\"6\"></td>\n\t</tr>";
	}

	
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
		$usersummary .= "\t<tr class='$bgcc'>\n";

		$usersummary .= "\t<td class='$bgcc' align='center'>{$usr['user']}</td>\n"
		. "\t<td class='$bgcc' align='center'><a href='mailto:{$usr['email']}'>{$usr['email']}</a></td>\n"
		. "\t<td class='$bgcc' align='center'>{$usr['full_name']}</td>\n";

		// passwords of other users will not be displayed
		$usersummary .=  "\t\t<td class='$bgcc' align='center'>******</td>\n";

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
				$usersummary .= "\t\t<td class='$bgcc' align='center'>{$usr['parent']}</td>\n";
			} else 
			{
				$usersummary .= "\t\t<td class='$bgcc' align='center'>-----</td>\n";
			}
		//}
		
		$usersummary .= "\t\t<td class='$bgcc' align='center' style='padding:3px;'>\n";
		// users are allowed to delete all successor users (but the admin not himself)
		//  || ($usr['uid'] == $_SESSION['loginID']))
		if ($_SESSION['loginID'] == "1" || ($_SESSION['USER_RIGHT_DELETE_USER'] == 1  && $usr['parent_id'] == $_SESSION['loginID']))
		{
			$usersummary .= "\t\t\t<form method='post' action='$scriptname?action=deluser'>"
			."<input type='submit' value='".$clang->gT("Delete")."' onclick='return confirm(\"".$clang->gT("Are you sure you want to delete this entry.","js")."\")' />"
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
    $usersummary .= "</table><br />";

	if($_SESSION['USER_RIGHT_CREATE_USER'])
	{
		$usersummary .= "\t\t<form action='$scriptname' method='post'>\n"
		. "\t\t<table width='100%' borders='0'><tr>\n"
        . "\t\t<th colspan='6'>".$clang->gT("Add User")."</th>\n"
        . "\t\t</tr><tr>\n"
		. "\t\t<td align='center' width='20%'><input type='text' name='new_user' /></td>\n"
		. "\t\t<td align='center' width='20%'><input type='text' name='new_email' /></td>\n"
		. "\t\t<td align='center' width='20%' ><input type='text' name='new_full_name' /></td><td width='15%'>&nbsp;</td><td width='15%'>&nbsp;</td>\n"
		. "\t\t<td align='center' width='15%'><input type='submit' value='".$clang->gT("Add User")."' />"
		. "<input type='hidden' name='action' value='adduser' /></td>\n"
		. "\t</tr></table></form>\n";
	}
	
>>>>>>> refs/heads/stable_plus
}

if ($action == "addusergroup")
{
<<<<<<< HEAD
    if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)  // only admins may do that
    {
        $usersummary ="<div class='header ui-widget-header'>".$clang->gT("Add User Group")."</div>\n"
        . "<form action='$scriptname' id='usergroupform' class='form30' method='post'>"
        . "<ul>\n"
        . "<li><label for='group_name'>".$clang->gT("Name:")."</label>\n"
        . "<input type='text' size='50' id='group_name' name='group_name' /><font color='red' face='verdana' size='1'> ".$clang->gT("Required")."</font></li>\n"
        . "<li><label for='group_description'>".$clang->gT("Description:")."</label>\n"
        . "<textarea cols='50' rows='4' id='group_description' name='group_description'></textarea></li>\n"
        . "</ul><p><input type='submit' value='".$clang->gT("Add Group")."' />\n"
        . "<input type='hidden' name='action' value='usergroupindb' />\n"
        . "</form>\n";
    }
=======
	if ($_SESSION['loginID'] == 1)  // from now only admins may do that
	{
		$usersummary = "<form action='$scriptname'  method='post'><table width='100%' border='0'>\n\t<tr><th colspan='2'>\n"
		. "\t\t<strong>".$clang->gT("Add User Group")."</strong></th></tr>\n"
		. "\t<tr>\n"
		. "\t\t<td><strong>".$clang->gT("Name:")."</strong></td>\n"
		. "\t\t<td><input type='text' size='50' name='group_name' /><font color='red' face='verdana' size='1'> ".$clang->gT("Required")."</font></td></tr>\n"
		. "\t<tr><td><strong>".$clang->gT("Description:")."</strong></td>\n"
		. "\t\t<td><textarea cols='50' rows='4' name='group_description'></textarea></td></tr>\n"
		. "\t<tr><td colspan='2' class='centered'><input type='submit' value='".$clang->gT("Add Group")."' />\n"
		. "\t<input type='hidden' name='action' value='usergroupindb' />\n"
		. "\t</td></table>\n"
		. "</form>\n";
	}
>>>>>>> refs/heads/stable_plus
}

if ($action == "editusergroup")
{
<<<<<<< HEAD
    if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
    {
        $query = "SELECT * FROM ".db_table_name('user_groups')." WHERE ugid = ".$_GET['ugid']." AND owner_id = ".$_SESSION['loginID'];
        $result = db_select_limit_assoc($query, 1);
        $esrow = $result->FetchRow();
        $usersummary = "<div class='header ui-widget-header'>".sprintf($clang->gT("Editing user group (Owner: %s)"),$_SESSION['user'])."</div>"
        ."<form action='$scriptname' id='usergroupform' class='form30' name='usergroupform' method='post'>"
        . "<ul>\n"
        . "<li><label for='name'>".$clang->gT("Name:")."</label>\n"
        . "<input type='text' size='50' maxlength='20' id='name' name='name' value=\"{$esrow['name']}\" /></li>\n"
        . "<li><label for='description'>".$clang->gT("Description:")."</label>\n"
        . "<textarea cols='50' rows='4' id='description' name='description'>{$esrow['description']}</textarea></li>\n"
        . "<ul><p><input type='submit' value='".$clang->gT("Update User Group")."' />\n"
        . "<input type='hidden' name='action' value='editusergroupindb' />\n"
        . "<input type='hidden' name='owner_id' value='".$_SESSION['loginID']."' />\n"
        . "<input type='hidden' name='ugid' value='$ugid' />\n"
        . "</form>\n";
    }
=======
	if ($_SESSION['loginID'] == 1)
	{
		$query = "SELECT * FROM ".db_table_name('user_groups')." WHERE ugid = ".$_GET['ugid']." AND owner_id = ".$_SESSION['loginID'];
		$result = db_select_limit_assoc($query, 1);
		$esrow = $result->FetchRow();
		$usersummary = "<form action='$scriptname' name='editusergroup' method='post'>"
		. "<table width='100%' border='0' class='form2columns'>\n\t<tr><th colspan='2'>\n"
		. "\t\t<strong>".$clang->gT("Edit User Group (Owner: ").$_SESSION['user'].")</strong></th></tr>\n"
		. "\t<tr>\n"
		. "\t\t<td><strong>".$clang->gT("Name:")."</strong></td>\n"
		. "\t\t<td><input type='text' size='50' name='name' value=\"{$esrow['name']}\" /></td></tr>\n"
		. "\t<tr><td><strong>".$clang->gT("Description:")."</strong></td>\n"
		. "\t\t<td><textarea cols='50' rows='4' name='description'>{$esrow['description']}</textarea></td></tr>\n"
		. "\t<tr><td colspan='2' class='centered'><input type='submit' value='".$clang->gT("Update User Group")."' />\n"
		. "\t<input type='hidden' name='action' value='editusergroupindb' />\n"
		. "\t<input type='hidden' name='owner_id' value='".$_SESSION['loginID']."' />\n"
		. "\t<input type='hidden' name='ugid' value='$ugid' />\n"
		. "\t</td></tr>\n"
		. "</table>\n"
		. "\t</form>\n";
	}
>>>>>>> refs/heads/stable_plus
}

if ($action == "mailusergroup")
{
<<<<<<< HEAD
    $query = "SELECT a.ugid, a.name, a.owner_id, b.uid FROM ".db_table_name('user_groups') ." AS a LEFT JOIN ".db_table_name('user_in_groups') ." AS b ON a.ugid = b.ugid WHERE a.ugid = {$ugid} AND uid = {$_SESSION['loginID']} ORDER BY name";
    $result = db_execute_assoc($query); //Checked
    $crow = $result->FetchRow();

    $usersummary = "<div class='header ui-widget-header'>".$clang->gT("Mail to all Members")."</div>"
    . "<form action='$scriptname' class='form30' id='usergroupform' name='usergroupform' method='post'><ul>"
    . "<li><label for='copymail'>".$clang->gT("Send me a copy:")."</label>\n"
    . "<input id='copymail' name='copymail' type='checkbox' class='checkboxbtn' value='1' /></li>\n"
    . "<li><label for='subject'>".$clang->gT("Subject:")."</label>\n"
    . "<input type='text' id='subject' size='50' name='subject' value='' /></li>\n"
    . "<li><label for='body'>".$clang->gT("Message:")."</label>\n"
    . "<textarea cols='50' rows='4' id='body' name='body'></textarea></li>\n"
    . "</ul><p><input type='submit' value='".$clang->gT("Send")."' />\n"
    . "<input type='reset' value='".$clang->gT("Reset")."' /><br />"
    . "<input type='hidden' name='action' value='mailsendusergroup' />\n"
    . "<input type='hidden' name='ugid' value='$ugid' />\n"
    . "</form>\n";
=======
	$query = "SELECT a.ugid, a.name, a.owner_id, b.uid FROM ".db_table_name('user_groups') ." AS a LEFT JOIN ".db_table_name('user_in_groups') ." AS b ON a.ugid = b.ugid WHERE a.ugid = {$ugid} AND uid = {$_SESSION['loginID']} ORDER BY name";
	$result = db_execute_assoc($query);
	$crow = $result->FetchRow();


	$usersummary = "<form action='$scriptname' name='mailusergroup' method='post'>"
	. "<table width='100%' border='0' class='form2columns'>\n\t<tr><th colspan='2'>\n"
	. "\t\t<strong>".$clang->gT("Mail to all Members")."</strong></th></tr>\n"
	. "\t<tr>\n"
	. "\t\t<td><strong>".$clang->gT("Send me a copy:")."</strong></td>\n"
	. "\t\t<td><input name='copymail' type='checkbox' class='checkboxbtn' value='1' /></td></tr>\n"
	. "\t<tr>\n"
	. "\t\t<td><strong>".$clang->gT("Subject:")."</strong></td>\n"
	. "\t\t<td><input type='text' size='50' name='subject' value='' /></td></tr>\n"
	. "\t<tr><td><strong>".$clang->gT("Message:")."</strong></td>\n"
	. "\t\t<td><textarea cols='50' rows='4' name='body'></textarea></td></tr>\n"
	. "\t<tr><td colspan='2' class='centered'><input type='submit' value='".$clang->gT("Send")."' />\n"
	. "<input type='reset' value='".$clang->gT("Reset")."' /><br />"
	. "\t<input type='hidden' name='action' value='mailsendusergroup' />\n"
	. "\t<input type='hidden' name='ugid' value='$ugid' />\n"
	. "\t</td></tr>\n"
	. "</table>\n"
	. "\t</form>\n";
>>>>>>> refs/heads/stable_plus
}

if ($action == "delusergroup")
{
<<<<<<< HEAD
    $usersummary = "<div class=\"header\">".$clang->gT("Deleting User Group")."...</div>\n";
    $usersummary .= "<div class=\"messagebox\">\n";

    if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
    {

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
                    $usersummary .= "<br />".$clang->gT("Group Name").": {$row['name']}<br /><br />\n";
                    $usersummary .= "<div class=\"successheader\">".$clang->gT("Success!")."</div>\n";
                }
                else
                {
                    $usersummary .= "<div class=\"warningheader\">".$clang->gT("Could not delete user group.")."</div>\n";
                }
                $usersummary .= "<br/><input type=\"submit\" onclick=\"window.location='$scriptname?action=editusergroups'\" value=\"".$clang->gT("Continue")."\"/>\n";
            }
            else
            {
                include("access_denied.php");
            }
        }
        else
        {
            $usersummary .= "<div class=\"warningheader\">".$clang->gT("Could not delete user group. No group selected.")."</div>\n";
            $usersummary .= "<br/><input type=\"submit\" onclick=\"window.location='$scriptname?action=editusergroups'\" value=\"".$clang->gT("Continue")."\"/>\n";
        }
    }
    $usersummary .= "</div>\n";
}

if ($action == "usergroupindb")
{
    $usersummary = "<div class=\"header\">".$clang->gT("Adding User Group")."...</div>\n";
    $usersummary .= "<div class=\"messagebox\">\n";

    if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
    {

        $db_group_name = db_quote($_POST['group_name']);
        $db_group_description = db_quote($_POST['group_description']);
        $html_group_name = htmlspecialchars($_POST['group_name']);
        $html_group_description = htmlspecialchars($_POST['group_description']);

        if(isset($db_group_name) && strlen($db_group_name) > 0)
        {
            $ugid = addUserGroupInDB($db_group_name, $db_group_description);
            if($ugid > 0)
            {
                $usersummary .= "<br />".$clang->gT("Group Name").": ".$html_group_name."<br /><br />\n";

                if(isset($db_group_description) && strlen($db_group_description) > 0)
                {
                    $usersummary .= $clang->gT("Description: ").$html_group_description."<br /><br />\n";
                }

                $usersummary .= "<div class=\"successheader\">".$clang->gT("User group successfully added!")."</div>\n";
                $usersummary .= "<br/><input type=\"submit\" onclick=\"window.location='$scriptname?action=editusergroups&amp;ugid={$ugid}'\" value=\"".$clang->gT("Continue")."\"/>\n";
            }
            else
            {
                $usersummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add Group!")."</div>\n"
                . "<br />" . $clang->gT("Group already exists!")."<br />\n";
                $usersummary .= "<br/><input type=\"submit\" onclick=\"window.location='$scriptname?action=addusergroup'\" value=\"".$clang->gT("Continue")."\"/>\n";
            }
        }
        else
        {
            $usersummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add Group!")."</div>\n"
            . "<br />" . $clang->gT("Group name was not supplied!")."<br />\n";
            $usersummary .= "<br/><input type=\"submit\" onclick=\"window.location='$scriptname?action=addusergroup'\" value=\"".$clang->gT("Continue")."\"/>\n";
        }
    }
    else
    {
        include("access_denied.php");
    }
    $usersummary .= "</div>\n";
}

if ($action == "mailsendusergroup")
{
    $usersummary = "<div class=\"header\">".$clang->gT("Mail to all Members")."</div>\n";
    $usersummary .= "<div class=\"messagebox\">\n";

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
            $to .= $egurow['users_name']. ' <'.$egurow['email'].'>'. '; ' ;
            $addressee .= $egurow['users_name'].', ';
        }
        $to = substr("$to", 0, -2);
        $addressee = substr("$addressee", 0, -2);

        $from_user = "SELECT email, users_name, full_name FROM ".db_table_name("users")." WHERE uid = " .$_SESSION['loginID'];
        $from_user_result = db_execute_assoc($from_user); //Checked
        $from_user_row = $from_user_result->FetchRow();
        if ($from_user_row['full_name'])
        {
            $from = $from_user_row['full_name'].' <'.$from_user_row['email'].'> ';
        }
        else
        {
            $from = $from_user_row['users_name'].' <'.$from_user_row['email'].'> ';
        }

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
        if (SendEmailMessage(null, $body, $subject, $to, $from,''))
        {
            $usersummary = "<div class=\"messagebox\">\n";
            $usersummary .= "<div class=\"successheader\">".$clang->gT("Message(s) sent successfully!")."</div>\n"
            . "<br />".$clang->gT("To:")."". $addressee."<br />\n"
            . "<br/><input type=\"submit\" onclick=\"window.location='$scriptname?action=editusergroups&amp;ugid={$ugid}'\" value=\"".$clang->gT("Continue")."\"/>\n";
        }
        else
        {
            $usersummary = "<div class=\"messagebox\">\n";
            $usersummary .= "<div class=\"warningheader\">".sprintf($clang->gT("Email to %s failed. Error Message:"),$to)." ".$maildebug."</div>";
            if ($debug>0)
            {
                $usersummary .= "<br /><pre>Subject : $subject<br /><br />".htmlspecialchars($maildebugbody)."<br /></pre>";
            }

            $usersummary .= "<br/><input type=\"submit\" onclick=\"window.location='$scriptname?action=mailusergroup&amp;ugid={$ugid}'\" value=\"".$clang->gT("Continue")."\"/>\n";
        }
    }
    else
    {
        include("access_denied.php");
    }
    $usersummary .= "</div>\n";
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

		$usersummary = "<div class=\"messagebox\">\n";
        if(updateusergroup($db_name, $db_description, $ugid))
        {

			$usersummary .= "<div class=\"successheader\">".$clang->gT("Edit User Group Successfully!")."</div>\n"
            . "<br />".$clang->gT("Name").": {$html_name}<br />\n"
            . $clang->gT("Description: ").$html_description."<br />\n"
            . "<br /><a href='$scriptname?action=editusergroups&amp;ugid={$ugid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
        }
        else
		{
			$usersummary .= "<div class=\"warningheader\">".$clang->gT("Failed to update!")."</div>\n"
        . "<br /><a href='$scriptname?action=editusergroups'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
    }
		$usersummary .= "</div>\n";
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
                $usergroupsummary .= "<table class='users'>\n"
                . "<thead><tr>\n"
                . "<th>".$clang->gT("Action")."</th>\n"
                . "<th>".$clang->gT("Username")."</th>\n"
                . "<th>".$clang->gT("Email")."</th>\n"
                . "</tr></thead><tbody>\n";

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
                        . "<td align='center'>&nbsp;</td>\n"
                        . "<td align='center'><strong>{$egurow['users_name']}</strong></td>\n"
                        . "<td align='center'><strong>{$egurow['email']}</strong></td>\n"
                        . "</tr>";
                        continue;
                    }

                    //	output users

                    $usergroupentries .= "<tr class='$bgcc'>\n"
                    . "<td align='center'>\n";

                    if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
                    {
                        $usergroupentries .= "<form method='post' action='$scriptname?action=deleteuserfromgroup&amp;ugid=$ugid'>"
                        ." <input type='image' src='$imageurl/token_delete.png' alt='".$clang->gT("Delete this user from group")."' onclick='return confirm(\"".$clang->gT("Are you sure you want to delete this entry?","js")."\")' />"
                        ." <input type='hidden' name='user' value='{$egurow['users_name']}' />"
                        ." <input name='uid' type='hidden' value='{$egurow['uid']}' />"
                        ." <input name='ugid' type='hidden' value='{$ugid}' />";
                    }
                    $usergroupentries .= "</form>"
                    . "</td>\n";
                    $usergroupentries .= "<td align='center'>{$egurow['users_name']}</td>\n"
                    . "<td align='center'>{$egurow['email']}</td>\n"
                    . "</tr>\n";
                    $row++;
                }
                $usergroupsummary .= $usergroupowner;
                if (isset($usergroupentries)) {$usergroupsummary .= $usergroupentries;};
                $usergroupsummary .= '</tbody></table>';

                if(isset($row2['ugid']))
                {
                    $usergroupsummary .= "<form action='$scriptname?ugid={$ugid}' method='post'>\n"
                    . "<table class='users'><tbody><tr><td>&nbsp;</td>\n"
                    . "<td>&nbsp;</td>"
                    . "<td align='center'><select name='uid'>\n"
                    . getgroupuserlist()
                    . "</select>\n"
                    . "<input type='submit' value='".$clang->gT("Add User")."' />\n"
                    . "<input type='hidden' name='action' value='addusertogroup' /></td>\n"
                    . "</tr></tbody></table>\n"
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

    $usersummary = "<div class=\"header\">".$clang->gT("Delete User")."</div>\n";
    $usersummary .= "<div class=\"messagebox\">\n";

    if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
    {
        $ugid = $postusergroupid;
        $uid = $postuserid;

        $query = "SELECT ugid, owner_id FROM ".db_table_name('user_groups')." WHERE ugid = ".$ugid." AND ((owner_id = ".$_SESSION['loginID']." AND owner_id != ".$uid.") OR (owner_id != ".$_SESSION['loginID']." AND $uid = ".$_SESSION['loginID']."))";
        $result = db_execute_assoc($query); //Checked
        if($result->RecordCount() > 0)
        {
            $remquery = "DELETE FROM ".db_table_name('user_in_groups')." WHERE ugid = {$ugid} AND uid = {$uid}";
            if($connect->Execute($remquery)) //Checked
            {
                $usersummary .= "<br />".$clang->gT("Username").": ".sanitize_xss_string(strip_tags($_POST['user']))."<br /><br />\n";
                $usersummary .= "<div class=\"successheader\">".$clang->gT("Success!")."</div>\n";
            }
            else
            {
                $usersummary .= "<div class=\"warningheader\">".$clang->gT("Could not delete user. User was not supplied.")."</div>\n";
            }
        }
        else
        {
            include("access_denied.php");
        }
        if($_SESSION['loginID'] != $postuserid)
        {
            $usersummary .= "<br/><input type=\"submit\" onclick=\"window.location='$scriptname?action=editusergroups&amp;ugid=$ugid'\" value=\"".$clang->gT("Continue")."\"/>\n";
        }
        else
        {
            $usersummary .= "<br/><input type=\"submit\" onclick=\"window.location='$scriptname?action=editusergroups'\" value=\"".$clang->gT("Continue")."\"/>\n";
        }
    }
    else
    {
        include("access_denied.php");
    }
    $usersummary .= "</div>\n";
=======
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

if ($action == "usergroupindb" && $_SESSION['loginID'] == 1) {
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

if ($action == "mailsendusergroup" && $_SESSION['loginID'] == 1)
{
	$usersummary = "<br /><strong>".$clang->gT("Mail to all Members")."</strong><br />\n";

	// user must be in user group
	$query = "SELECT uid FROM ".db_table_name('user_in_groups') ." WHERE ugid = {$ugid} AND uid = {$_SESSION['loginID']}";
	$result = db_execute_assoc($query);

	if($result->RecordCount() > 0)
	{

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

		$from_user = "SELECT email, users_name FROM ".db_table_name("users")." WHERE uid = " .$_SESSION['loginID'];
		$from_user_result = db_execute_assoc($from_user);
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
			$usersummary .= "<br /><strong>".$clang->gT("Mail not sent!")."</strong><br />\n";
			$usersummary .= "<br /><a href='$scriptname?action=mailusergroup&amp;ugid={$ugid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
	}
	else
	{
		include("access_denied.php");
	}
}

if ($action == "editusergroupindb" && $_SESSION['loginID'] == 1){

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

if ($action == "editusergroups"  && $_SESSION['loginID'] == 1)
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
				$usergroupsummary .= "<table width='100%' border='0'>\n"
				. "\t\t\t\t<tr><td align='justify' colspan='2' height='4'>"
				. "<font size='2' ><strong>".$clang->gT("Description: ")."</strong>"
				. "{$crow['description']}</font></td></tr>\n"
				. "</table>";
			}


			$eguquery = "SELECT * FROM ".db_table_name("user_in_groups")." AS a INNER JOIN ".db_table_name("users")." AS b ON a.uid = b.uid WHERE ugid = " . $ugid . " ORDER BY b.users_name";
			$eguresult = db_execute_assoc($eguquery);
			$usergroupsummary .= "<table  width='100%' border='0'>\n"
			. "\t<tr>\n"
			. "\t\t<th>".$clang->gT("Username")."</th>\n"
			. "\t\t<th>".$clang->gT("Email")."</th>\n"
			. "\t\t<th width='25%'>".$clang->gT("Action")."</th>\n"
			. "\t</tr>\n";

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
					$usergroupowner = "\t<tr class='$bgcc'>\n"
					. "\t<td align='center'><strong>{$egurow['users_name']}</strong></td>\n"
					. "\t<td align='center'><strong>{$egurow['email']}</strong></td>\n"
					. "\t\t<td align='center'>&nbsp;</td></tr>\n";
					continue;
				}
				//	output users
				
				if($row == 1){ $usergroupentries .= "\t<tr>\n\t<td height=\"20\" colspan=\"6\"></td>\n\t</tr>"; $row++;}
				//if(($row % 2) == 0) $usergroupentries .= "\t<tr  bgcolor='#999999'>\n";
				//else $usergroupentries .= "\t<tr>\n";
				$usergroupentries .= "\t<tr class='$bgcc'>\n";
				$usergroupentries .= "\t<td align='center'>{$egurow['users_name']}</td>\n"
				. "\t<td align='center'>{$egurow['email']}</td>\n"
				. "\t\t<td align='center' style='padding-top:10px;'>\n";

				// owner and not himself    or    not owner and himself
				if((isset($row2['ugid']) && $_SESSION['loginID'] != $egurow['uid']) || (!isset($row2['ugid']) && $_SESSION['loginID'] == $egurow['uid']))
				{
					$usergroupentries .= "\t\t\t<form method='post' action='$scriptname?action=deleteuserfromgroup&amp;ugid=$ugid'>"
					." <input type='submit' value='".$clang->gT("Delete")."' onclick='return confirm(\"".$clang->gT("Are you sure you want to delete this entry.","js")."\")' />"
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
				. "\t\t\t<td></td>"
				. "\t\t\t\t<td align='center'><select name='uid'>\n"
				. getgroupuserlist()
				. "\t\t\t\t</select>\n"
				. "\t\t\t\t<input type='submit' value='".$clang->gT("Add User")."' />\n"
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

if($action == "deleteuserfromgroup" && $_SESSION['loginID'] == 1) {
	$ugid = $postusergroupid;
	$uid = $postuserid;
	$usersummary = "<br /><strong>".$clang->gT("Delete User")."</strong><br />\n";

	$query = "SELECT ugid, owner_id FROM ".db_table_name('user_groups')." WHERE ugid = ".$ugid." AND ((owner_id = ".$_SESSION['loginID']." AND owner_id != ".$uid.") OR (owner_id != ".$_SESSION['loginID']." AND $uid = ".$_SESSION['loginID']."))";
	$result = db_execute_assoc($query);
	if($result->RecordCount() > 0)
	{
		$remquery = "DELETE FROM ".db_table_name('user_in_groups')." WHERE ugid = {$ugid} AND uid = {$uid}";
		if($connect->Execute($remquery))
		{
			$usersummary .= "<br />".$clang->gT("Username").": ".sanitize_system_string($_POST['user'])."<br />\n";
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
>>>>>>> refs/heads/stable_plus
}




<<<<<<< HEAD
if($action == "addusertogroup")
{
    $ugid=returnglobal('ugid');

    $addsummary = "<div class=\"header\">".$clang->gT("Adding User to group")."...</div>\n";
    $addsummary .= "<div class=\"messagebox\">\n";

    if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
    {

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
                    $addsummary .= "<div class=\"successheader\">".$clang->gT("User added.")."</div>\n";
                }
                else  // ToDo: for this to happen the keys on the table must still be set accordingly
                {
                    // Username already exists.
                    $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add user.")."</div>\n"
                    . "<br />" . $clang->gT("Username already exists.")."<br />\n";
                }
            }
            else
            {
                $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add user.")."</div>\n"
                . "<br />" . $clang->gT("No Username selected.")."<br />\n";
            }
            $addsummary .= "<br/><input type=\"submit\" onclick=\"window.location='$scriptname?action=editusergroups&amp;ugid={$ugid}'\" value=\"".$clang->gT("Continue")."\"/>\n";
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
    $addsummary .= "</div>\n";
=======
if($action == "addusertogroup" && $_SESSION['loginID'] == 1)
{
	$addsummary = "<br /><strong>".$clang->gT("Adding User to group")."...</strong><br />\n";

	$query = "SELECT ugid, owner_id FROM ".db_table_name('user_groups')." WHERE ugid = ".$_GET['ugid']." AND owner_id = ".$_SESSION['loginID']." AND owner_id != ".$postuserid;
	$result = db_execute_assoc($query);
	if($result->RecordCount() > 0)
	{
		if($postuserid > 0)
		{
			$isrquery = "INSERT INTO {$dbprefix}user_in_groups VALUES(".$_GET['ugid'].",". $postuserid.")";
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
>>>>>>> refs/heads/stable_plus
}


function updateusergroup($name, $description, $ugid)
{
    global $dbprefix, $scriptname, $connect;

    $uquery = "UPDATE ".db_table_name('user_groups')." SET name = '$name', description = '$description' WHERE ugid =$ugid";
    // TODO
<<<<<<< HEAD
    return $connect->Execute($uquery) or safe_die($connect->ErrorMsg()) ; //Checked
}

function refreshtemplates() {
    global $connect ;
    global $dbprefix ;

    $template_a = gettemplatelist();
	foreach ($template_a as $tp=>$fullpath) {
        // check for each folder if there is already an entry in the database
        // if not create it with current user as creator (user with rights "create user" can assign template rights)
        $query = "SELECT * FROM ".$dbprefix."templates WHERE folder LIKE '".$tp."'";
        $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg()); //Checked

        if ($result->RecordCount() == 0) {
            $query2 = "INSERT INTO ".$dbprefix."templates (".db_quote_id('folder').",".db_quote_id('creator').") VALUES ('".$tp."', ".$_SESSION['loginID'].')' ;
            $connect->Execute($query2) or safe_die($connect->ErrorMsg()); //Checked
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
=======
    return $connect->Execute($uquery) or die($connect->ErrorMsg()) ;
}
>>>>>>> refs/heads/stable_plus
