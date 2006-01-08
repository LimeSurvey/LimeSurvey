<?php
/*
	#############################################################
	# >>> PHPSurveyor	  										#
	#############################################################
	# > Author:  Jason Cleeland									#
	# > E-mail:  jason@cleeland.org								#
	# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
	# >          CARLTON SOUTH 3053, AUSTRALIA					#
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

require_once(dirname(__FILE__).'/config.php');
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}

//DEFAULT SETTINGS FOR TEMPLATES
if (!$publicdir) {$publicdir=".";}
$tpldir="$publicdir/templates";

if (!isset($_SESSION))
	{
	//There is no session set, so this must have been called directly (from the presentations screen)
	//We need to start the session, and load the relevant data that otherwise is loaded in
	//index.php
	$source="posted";
	session_start();
	//GET BASIC INFORMATION ABOUT THIS SURVEY
	$thissurvey=getSurveyInfo($surveyid);
	
	//SET THE TEMPLATE DIRECTORY
	if (!$thissurvey['templatedir']) {$thistpl=$tpldir."/default";} else {$thistpl=$tpldir."/{$thissurvey['templatedir']}";}
	$_POST['savename']= html_escape(auto_unescape($_POST['savename']));
	$_POST['savepass'] = html_escape(auto_unescape($_POST['savepass']));
	$_POST['savepass2'] = html_escape(auto_unescape($_POST['savepass2']));
	$_POST['saveemail'] = html_escape(auto_unescape($_POST['saveemail']));	
	if (!is_dir($thistpl)) {$thistpl=$tpldir."/default";}
	
	//REQUIRE THE LANGUAGE FILE
	$langdir="$publicdir/lang";
	$langfilename="$langdir/{$thissurvey['language']}.lang.php";
	//Use the default language file if the $thissurvey['language'] file doesn't exist
	if (!is_file($langfilename)) {$langfilename="$langdir/$defaultlang.lang.php";}
	require_once($langfilename);
	}

if (isset($source))
	{
    //Check that the required fields have been completed.
	$errormsg="";
	if (!isset($_POST['savename']) || !$_POST['savename']) {$errormsg.=_SAVENONAME."<br />\n";}
	if (!isset($_POST['savepass']) || !$_POST['savepass']) {$errormsg.=_SAVENOPASS."<br />\n";}
	if (!isset($_POST['savepass2']) || !$_POST['savepass2']){$errormsg.=_SAVENOPASS2."<br />\n";}	
	// modified logic so that it correctly checks to see if (savepass != savepass2)
	// isset(savepass) and isset(savepass2) have already been checked and do not
	// need to be checked again
    if ($_POST['savepass'] != $_POST['savepass2'])
		{$errormsg.=_SAVENOMATCH."<br />\n";}
	if (!$errormsg && !isset($_SESSION['savename']))
		{
	    //All the fields are correct. Now make sure there's not already a matching saved item
		$query = "SELECT * FROM {$dbprefix}saved_control\n"
				."WHERE sid=$surveyid\n"
				."AND identifier='".$_POST['savename']."'\n"
				."AND access_code='".md5($_POST['savepass'])."'\n";
		$result = mysql_query($query) or die("Error checking for duplicates!<br />$query<br />".mysql_error());
		if (mysql_num_rows($result) > 0) 
			{
			$errormsg.=_SAVEDUPLICATE."<br />\n";
			}
		}
	if ($errormsg)
		{
		unset($source);
		}
	}

if (!isset($source)) 
	{
	//Prepare to save
	
	//First, save the posted data to session (as if we were moving from one
	//question to another). Doing this ensures that answers on the current
	//page are saved as well.
	//CONVERT POSTED ANSWERS TO SESSION VARIABLES
	if (isset($_SESSION['savename']))
		{
	    $_POST['savename']=$_SESSION['savename'];
		}
	if (isset($_POST['fieldnames']) && $_POST['fieldnames'])
		{
		$postedfieldnames=explode("|", $_POST['fieldnames']);
		foreach ($postedfieldnames as $pf)
			{
			if (isset($_POST[$pf])) {$_SESSION[$pf] = $_POST[$pf];}
			if (!isset($_POST[$pf])) {$_SESSION[$pf] = "";}
			}
		}
	sendcacheheaders();
	doHeader();
	foreach(file("$thistpl/startpage.pstpl") as $op)
		{
		echo templatereplace($op);
		}
	echo "\n\n<!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->\n"
		."\t<script type='text/javascript'>\n"
		."\t<!--\n"
		."\t\tfunction checkconditions(value, name, type)\n"
		."\t\t\t{\n"
		."\t\t\t}\n"
		."\t//-->\n"
		."\t</script>\n\n";

	echo "<form method='post' action='save.php'>\n";
	//PRESENT OPTIONS SCREEN
	if (isset($errormsg) && $errormsg != "")
		{
		$errormsg .= "<p>"._SAVETRYAGAIN."</p>";
		}
	foreach(file("$thistpl/save.pstpl") as $op)
		{
		echo templatereplace($op);
		}
	//END
	echo "<input type='hidden' name='sid' value='$surveyid'>\n";
	echo "<input type='hidden' name='thisstep' value='".$_POST['thisstep']."'>\n";
	echo "<input type='hidden' name='token' value='".returnglobal('token')."'>\n";
	echo "</form>";
	
	foreach(file("$thistpl/endpage.pstpl") as $op)
		{
		echo templatereplace($op);
		}
	doFooter();
	exit;
	}

//Script has been run by itself

//This data will be saved to the "saved_control" and "saved" tables, which are normalised
//tables with one row per response. 
// - a unique "saved_id" value (autoincremented)
// - the "sid" for this survey
// - "saved_thisstep" which is the step the user is up to in this survey
// - "saved_ip" which is the ip address of the submitter
// - "saved_date" which is the date ofthe saved response
// - an "identifier" which is like a username
// - a "password"
// - "fieldname" which is the fieldname of the saved response
// - "value" which is the value of the response
//We start by generating the first 5 values which are consistent for all rows.

$sdata = array("thisstep"=>$_POST['thisstep'],
			   "sid"=>$surveyid,
			   "ip"=>$_SERVER['REMOTE_ADDR'],
			   "date"=>date("Y-m-d H:i:s"),
			   "identifier"=>$_POST['savename'],
			   "code"=>md5($_POST['savepass']),
			   "email"=>$_POST['saveemail']);

if (isset($_SESSION['scid']))
	{
	//This person has loaded a previously saved session, so before we
	//save it again, we should delete the old one.
    $query = "DELETE FROM {$dbprefix}saved
			  WHERE scid=".$_SESSION['scid'];
	$result=mysql_query($query) or die("Couldn't delete existing saved survey.<br />$query<br />".mysql_error());
	$query = "DELETE FROM {$dbprefix}saved_control
			  WHERE scid=".$_SESSION['scid'];
	$result=mysql_query($query) or die("Couldn't delete existing saved survey.<br />$query<br />".mysql_error());
	}

//1: Create entry in "saved_control"
$query = "INSERT INTO `{$dbprefix}saved_control`
		  ( `sid`, `identifier`, `access_code`,
		   `email`, `ip`, `saved_thisstep`, `status`, `saved_date`)
		   VALUES (
		   '".$sdata['sid']."',
		   '".mysql_escape_string($sdata['identifier'])."',
		   '".$sdata['code']."',
		   '".$sdata['email']."',
		   '".$sdata['ip']."',
		   '".$sdata['thisstep']."',
		   'S',
		   '".$sdata['date']."')";
if ($result=mysql_query($query))
	{
	//Saved control entry worked, now lets save the data
    $sdata['scid']=mysql_insert_id();
	foreach ($_SESSION['insertarray'] as $sia)
		{
		if (isset($_SESSION[$sia]) && ($_SESSION[$sia] || $_SESSION[$sia] == "0")) 
			{
			$iquery = "INSERT INTO `{$dbprefix}saved`\n"
					. "(`scid`, `datestamp`, `fieldname`,\n"
					. "`value`)\n"
					. "VALUES (\n"
					. "'".$sdata['scid']."',\n"
					. "'".$sdata['date']."',\n"
					. "'".$sia."',\n"
					. "'".mysql_escape_string($_SESSION[$sia])."')";
			if (!$iresult=mysql_query($iquery))
				{
				$failed=1;
				echo mysql_error();
				}
			}
		}
	if (returnglobal('token'))
		{
		$iquery = "INSERT INTO `{$dbprefix}saved`\n"
				. "(`scid`, `datestamp`, `fieldname`,\n"
				. "`value`)\n"
				. "VALUES (\n"
				. "'".$sdata['scid']."',\n"
				. "'".$sdata['date']."',\n"
				. "'token',\n"
				. "'".returnglobal('token')."')";
		if (!$result=mysql_query($iquery))
			{
			$failed=1;
			echo $query;
			echo mysql_error();
			}
		}
	if (isset($failed))
		{
		//delete any entries that were saved. It's got to be all or nothing!
		$query = "DELETE FROM {$dbprefix}saved
				  WHERE scid=".$sdata['scid'];
		$result=mysql_query($query);
		$query = "DELETE FROM {$dbprefix}saved_control
				  WHERE scid=".$sdata['scid'];
		$result=mysql_query($query);
		}
	else
		{
		//Email if needed
		if (isset($_POST['saveemail']))
			{
			if (validate_email($_POST['saveemail']))
				{
				$subject=_SAVE_EMAILSUBJECT;
				$message=_SAVE_EMAILTEXT;
				$message.="\n\n".$thissurvey['name']."\n\n";
				$message.=_SAVENAME.": ".$_POST['savename']."\n";
				$message.=_SAVEPASSWORD.": ".$_POST['savepass']."\n\n";
				$message.=_SAVE_EMAILURL.":\n";
				$message.=$publicurl."/index.php?sid=$surveyid&loadall=reload&scid=".$sdata['scid']."&loadname=".urlencode($_POST['savename'])."&loadpass=".urlencode($_POST['savepass']);
				
				if (returnglobal('token')){$message.="&token=".returnglobal('token');}				
				$from=$thissurvey['adminemail'];
				
				if (MailtextMessage($message, $subject, $_POST['saveemail'], $from, $sitename))
					{
					$emailsent="Y";
					}
				else
					{
					echo "Error: Email failed, this may indicate a PHP Mail Setup problem on your server. Your survey details have still been saved, however you will not get an email with the details. You should note the \"name\" and \"password\" you just used for future reference.";
					}
				}
			}
		session_unset();
		session_destroy();
		}
	}
else
	{
	echo "Error:<br />$query<br />".mysql_error();
	}



sendcacheheaders();
doHeader();
foreach(file("$thistpl/startpage.pstpl") as $op)
	{
	echo templatereplace($op);
	}
echo "\n\n<!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->\n"
	."\t<script type='text/javascript'>\n"
	."\t<!--\n"
	."\t\tfunction checkconditions(value, name, type)\n"
	."\t\t\t{\n"
	."\t\t\t}\n"
	."\t//-->\n"
	."\t</script>\n\n";
echo "<center><p>";
if (isset($failed))
	{
	echo _SAVE_FAILED;
	}
else
	{
	echo _SAVE_SUCCEEDED;
	}
if (isset($emailsent))
	{
	echo "<p>"._SAVE_EMAILSENT."</p>";
	}
else
	{
	echo "<!-- EMAIL FAILED! -->\n";
	}
echo "</p>\n";
echo "<p>";
echo templatereplace("{URL}");
echo "</p>";

echo "<a href='index.php?sid=$surveyid";
if (returnglobal('token'))
	{
	echo "&amp;token=".returnglobal('token');
	}
echo "'>"._RETURNTOSURVEY."</a>";
echo "</center>\n";
foreach(file("$thistpl/endpage.pstpl") as $op)
	{
	echo templatereplace($op);
	}
doFooter();
exit;


?>