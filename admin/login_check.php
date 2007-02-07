<?php
/*
#############################################################
# >>> PHPSurveyor       									#
#############################################################
# This set of scripts allows you to develop, publish and	#
# perform data-entry on surveys.							#
#############################################################
#															#
#	Copyright (C) 2003 by the developers of PHPSurveyor     #
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

if (!isset($action)) {$action=returnglobal('action');}

// check data for login
if(isset($_POST['user']) && isset($_POST['password']) || ($action == "forgotpass") || ($action == "login") || ($action == "logout"))	// added by Dennis
{
	include("usercontrol.php");
}


// login form
if(!isset($_SESSION['loginID']) && $action != "forgotpass" && ($action != "logout" || ($action == "logout" && !isset($_SESSION['loginID'])))) // && $action != "login")	// added by Dennis
{
	if($action == "forgotpassword")
	{
		$loginsummary = "<form name='forgot' id='forgot' method='post' action='$rooturl/admin/admin.php' ><br /><strong>".$clang->gT("You have to enter user name and email.")."</strong><br />	<br />
							<table>
								<tr>
									<td><p>".$clang->gT("Username")."</p></td>
									<td><input name='user' type='text' id='user' size='40' maxlength='40' value='' /></td>
								</tr>
								<tr>
									<td><p>".$clang->gT("Email")."</p></td>
									<td><input name='email' id='email' type='text' size='40' maxlength='40' value='' /></td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><input type='hidden' name='action' value='forgotpass' />
									<input class='action' type='submit' value='Check data' /><br />&nbsp;\n</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><a href='$scriptname'>".$clang->gT("Main Admin Screen")."</a></td>
								</tr>
							</table>						
						</form>";	
	}
	else
	{
		$loginsummary = "<form name='login' id='login' method='post' action='$rooturl/admin/admin.php' ><br /><strong>".$clang->gT("You have to login first.")."</strong><br />	<br />
							<table>
								<tr>
									<td>".$clang->gT("Username")."</td>
									<td><input name='user' type='text' id='user' size='40' maxlength='40' value='' /></td>
								</tr>
								<tr>
									<td>".$clang->gT("Password")."</td>
									<td><input name='password' id='password' type='password' size='40' maxlength='40' /></td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td align='center'><input type='hidden' name='action' value='login' />
									<input class='action' type='submit' value='Login' /><br />&nbsp;\n</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><a href='$scriptname?action=forgotpassword'>".$clang->gT("Forgot Your Password?")."</a><br />&nbsp;\n</td>
								</tr>
							</table>
						</form>";

		// Language selection
		$loginsummary .=  "\t<form name='language' id='language' method='post' action='$rooturl/admin/admin.php' >"
		. "\t<table><tr>\n"
		. "\t\t<td align='center' >\n"
		. "\t\t\t".$clang->gT("Current Language").":\n"
		. "\t\t</td><td>\n"
		. "\t\t\t<select name='lang' onChange='form.submit()'>\n";
		foreach (getlanguagedata() as $langkey=>$languagekind)
		{
			$loginsummary .= "\t\t\t\t<option value='$langkey'";
			if (isset($_SESSION['adminlang']) && $langkey == $_SESSION['adminlang']) {$loginsummary .= " selected";}
			$loginsummary .= ">".$languagekind['description']." - ".$languagekind['nativedescription']."</option>\n";
		}
		$loginsummary .= "\t\t\t</select>\n"
		. "\t\t\t<input type='hidden' name='action' value='changelang' />\n"
		. "\t\t</td>\n"
		. "\t</tr>\n"
		. "</table>"
		. "</form><br />";
	}
}

if (isset($loginsummary)) {

	$adminoutput.= "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n"
	."\t<tr>\n"
	."\t\t<td valign='top' align='center' bgcolor='#BBBBBB'>\n";
	
	if(isset($_SESSION['loginID']))
	{
		$adminoutput.= showadminmenu();
	}
	$adminoutput.= $loginsummary;
	
	$adminoutput.= "\t\t</td>\n";
	$adminoutput.= "\t</tr>\n";
	$adminoutput.= "</table>\n";
	$adminoutput.= getAdminFooter("$langdir/instructions.html", "Using PHPSurveyors Admin Script");
}

// logout user
if ($action == "logout" && isset($_SESSION['loginID']))
{
	$adminoutput.= "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n"
	."\t<tr>\n"
	."\t\t<td valign='top' align='center' bgcolor='#BBBBBB'>\n";
	
	$adminoutput.= $logoutsummary;
	
	$adminoutput.= "\t\t</td>\n";
	$adminoutput.= "\t</tr>\n";
	$adminoutput.= "</table>\n";
	$adminoutput.= getAdminFooter("$langdir/instructions.html", "Using PHPSurveyors Admin Script");
	
}
?>
