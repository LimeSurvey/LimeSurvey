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


if (!isset($dbprefix) || isset($_REQUEST['dbprefix'])) {die("Cannot run this script directly");}
if (!isset($action)) {$action=returnglobal('action');}

// check data for login
if( isset($_POST['user']) && isset($_POST['password']) || 
	($action == "forgotpass") || ($action == "login") || 
	($action == "logout") || 
	($useWebserverAuth === true && !isset($_SESSION['loginID'])) )	// added by Dennis
{
	include("usercontrol.php");
}


// login form
if(!isset($_SESSION['loginID']) && $action != "forgotpass" && ($action != "logout" || ($action == "logout" && !isset($_SESSION['loginID'])))) // && $action != "login")	// added by Dennis
{
	if($action == "forgotpassword")
	{
		$loginsummary = '
			<form name="forgot" id="forgot" method="post" action="'.$rooturl.'/admin/admin.php" >
				<p><strong>'.$clang->gT('You have to enter user name and email.').'</strong></p>

				<table>
					<tbody>
					<tr>
						<td><label for="user">'.$clang->gT('Username').'</label></td>
						<td><input name="user" id="user" type="text" size="40" maxlength="40" value="" /></td>
					</tr>
					<tr>
						<td><label for="email">'.$clang->gT('Email').'</label></td>
						<td><input name="email" id="email" type="text" size="40" maxlength="40" value="" /></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><input type="hidden" name="action" value="forgotpass" />
						<input class="action" type="submit" value="'.$clang->gT('Check Data').'" /></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><a href="'.$scriptname.'">'.$clang->gT('Main Admin Screen').'</a></td>
					</tr>
				</table>
			</form>
';
	}
	elseif (!isset($loginsummary))
	{ // could be at login or after logout 
		$refererargs=''; // If this is a direct access to admin.php, no args are given
		// If we are called from a link with action and other args set, get them
		if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'])
		{
			$refererargs = html_escape($_SERVER['QUERY_STRING']);
		}

		// Language selection
		$loginsummary = '
			<form name="language" id="language" method="post" action="'.$rooturl.'/admin/admin.php" >
				<table style="margin-bottom: 2em;">
					<tbody>
						<tr>
							<td style="padding-top:2em; text-align: center;">
								<label for="lang">'.$clang->gT('Current Language').'</label>:
								<select name="lang" id="lang" onchange="form.submit()">';
		foreach (getlanguagedata() as $langkey=>$languagekind)
		{
			$loginsummary .= "\n\t\t\t\t\t\t\t\t\t<option value=\"$langkey\"";
			if (isset($_SESSION['adminlang']) && $langkey == $_SESSION['adminlang']) {$loginsummary .= ' selected="selected"';}
			// in case it is a logout, session has already been killed
			if (!isset($_SESSION['adminlang']) && $langkey == $clang->getlangcode() ){$loginsummary .= ' selected="selected"';}
			$loginsummary .= '>'.$languagekind['description'].' - '.$languagekind['nativedescription']."</option>\n";
		}
		$loginsummary .= '
								</select>
								<input type="hidden" name="action" value="changelang" />
							</td>
						</tr>
					</tbody>
				</table>
			</form>
';

		$hidden_loginlang = '';
		if (isset($_POST['lang']) && $_POST['lang'])
		{
			$hidden_loginlang = '<input type="hidden" name="loginlang" value="'.sanitize_languagecode($_POST['lang']).'" />';
		}

		if (!isset($logoutsummary))
		{
			$loginsummary .= '
			<form name="login" id="login" method="post" action="'.$rooturl.'/admin/admin.php" >
				<p><strong>'.$clang->gT('You have to login first.').'</strong></p>
';
		}
		else
		{
			$loginsummary .= '
			<form name="login" id="login" method="post" action="'.$rooturl.'/admin/admin.php" >
				<p><strong>'.$logoutsummary.'</strong></p>
';
		}

		$loginsummary .= '
				<table>
					<tbody>
						<tr>
							<td><label for="user">'.$clang->gT('Username').'</label></td>
							<td><input name="user" id="user" type="text" size="40" maxlength="40" value="" /></td>
						</tr>
						<tr>
							<td><label for="password">'.$clang->gT('Password').'</label></td>
							<td><input name="password" id="password" type="password" size="40" maxlength="40" /></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td align="center">
								<input type="hidden" name="action" value="login" />
								<input type="hidden" name="refererargs" value="'.$refererargs.'" />
								'.$hidden_loginlang.'
								<input class="action" type="submit" value="'.$clang->gT('Login').'" />
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><a href="'.$scriptname.'?action=forgotpassword">'.$clang->gT('Forgot Your Password?').'</a></td>
						</tr>
					</tbody>
				</table>
			</form>
';

	}
}

if (isset($loginsummary)) {

	$adminoutput.= "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n"
	."\t<tr>\n"
    ."\t\t<td valign='top' align='center' bgcolor='#F8F8FF'>\n";
	
	if(isset($_SESSION['loginID']))
	{
		$adminoutput.= showadminmenu();
	}
	$adminoutput.= $loginsummary;
	
	$adminoutput.= "\t\t</td>\n";
	$adminoutput.= "\t</tr>\n";
	$adminoutput.= "</table>\n";
}

?>
