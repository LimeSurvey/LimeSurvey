<?php
/*
	#############################################################
	# >>> PHP Surveyor  										#
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
$file_version="PHPSurveyor Template Editor Version 0.98rc9";
require_once("config.php");
$slstyle3=$slstyle2;
if(get_magic_quotes_gpc())
	{
	$_GET = array_map("stripslashes", $_GET);
	$_POST = array_map("stripslashes", $_POST);
	}

if (!isset($templatename)) {$templatename = returnglobal('templatename');}
if (!isset($templatedir)) {$templatedir = returnglobal('templatedir');}
if (!isset($editfile)) {$editfile = returnglobal('editfile');}
if (!isset($screenname)) {$screenname=returnglobal('screenname');}
if (!isset($action)) {$action=returnglobal('action');}

if ($action != "newtemplate" && !$templatename) {$templatename = "default";}
$template_a=gettemplatelist();
foreach ($template_a as $tp) {
	$templates[]=array("name"=>$tp, "dir"=>$publicdir."/templates/".$tp);
}
unset($template_a);

function newfolder($name) { //Creates a new template directory
	if (mkdir("$publicdir/templates/$name")) {
	    return true;
	} else {
		return false;
	}
}

function createfiles($folder) { //Places basic files into new template directory

}

//PUBLIC LANGUAGE FILE
$langdir="$publicdir/lang";
$langfilename="$langdir/$defaultlang.lang.php";
if (!is_file($langfilename)) {$langfilename="$langdir/$defaultlang.lang.php";}
require($langfilename);


//Save Changes if necessary
if ($action=="savechanges" && $_POST['changes']) {
	$_POST['changes']=str_replace("\r\n", "\n", $_POST['changes']);
	if ($_POST['editfile']) {
		$savefilename=$publicdir."/templates/".$_POST['templatename']."/".$_POST['editfile'];
		if (is_writable($savefilename)) {
		    if (!$handle = fopen($savefilename, 'w')) {
		        echo "Could not open file ($savefilename)";
				exit;
		    }
			if (!fwrite($handle, $_POST['changes'])) {
			    echo "Cannot write to file ($savefilename)";
				exit;
			}
		fclose($handle);
		} else {
			echo "The file $savefilename is not writable";
		}
	}
}


function makeoptions($array, $value, $text, $selectedvalue) {
	$return="";
	foreach ($array as $ar) {
		$return .= "<option value='".$ar[$value]."'";
		if ($ar[$value] == $selectedvalue) {
		    $return .= " selected";
		}
		$return .= ">".$ar[$text]."</option>\n";
	}
	return $return;
}
//Standard Template Files
$files[]=array("name"=>"startpage.pstpl");
$files[]=array("name"=>"survey.pstpl");
$files[]=array("name"=>"welcome.pstpl");
$files[]=array("name"=>"startgroup.pstpl");
$files[]=array("name"=>"groupdescription.pstpl");
$files[]=array("name"=>"question.pstpl");
$files[]=array("name"=>"submit.pstpl");
$files[]=array("name"=>"privacy.pstpl");
$files[]=array("name"=>"completed.pstpl");
$files[]=array("name"=>"endgroup.pstpl");
$files[]=array("name"=>"navigator.pstpl");
$files[]=array("name"=>"endpage.pstpl");

$normalfiles=array("DUMMYENTRY", ".", "..");
foreach ($files as $fl) {
	$normalfiles[]=$fl["name"];
}

$screens[]=array("name"=>"Welcome");
$screens[]=array("name"=>"Question");
$screens[]=array("name"=>"Submit");
$screens[]=array("name"=>"Completed");
$screens[]=array("name"=>"Clear All");

$Welcome=array("startpage.pstpl", "welcome.pstpl", "navigator.pstpl", "endpage.pstpl");
$Question=array("startpage.pstpl", "survey.pstpl", "startgroup.pstpl", "groupdescription.pstpl", "question.pstpl", "endgroup.pstpl", "navigator.pstpl", "endpage.pstpl");
$Submit=array("startpage.pstpl", "survey.pstpl", "submit.pstpl", "privacy.pstpl", "navigator.pstpl", "endpage.pstpl");
$Completed=array("startpage.pstpl", "completed.pstpl", "endpage.pstpl");
$Clearall=array("startpage.pstpl", "endpage.pstpl");
//Load this editfile
function filetext($templatefile) {
	global $publicdir, $templatename;
	$output="";
	foreach(file("$publicdir/templates/$templatename/$templatefile") as $line) {
		$output .= $line;
	}
	return $output;
}

function templatereplace($line)
	{
	global $publicurl, $templatedir, $templatename;
	global $question, $questioncode, $answer;
	global $screenname;
	$surveyname="Template Sample";
	$surveydescription="This is a sample survey description. It could be quite long.<br /><br />But this one isn't.";
	$surveywelcome="Welcome to this sample survey.<br />\n You should have a great time doing this<br />";
	$percentcomplete=makegraph(6, 10);
	$groupname="Group 1: The first lot of questions";
	$groupdescription="This group description is fairly vacuous, but quite important.";
	$navigator="<input class='submit' type='submit' value=' next >> ' name='move' />";
	if ($screenname != "Welcome") {$navigator = "<input class='submit' type='submit' value=' << prev ' name='move' />\n".$navigator;}
	$help="Help me";
	$totalquestions="10";
	$surveyformat="Format";
	$completed="Survey is completed and saved.";
	$surveyurl="http://phpsurveyor.sourceforge.net/";
	$surveyurldescrip="A URL Description";
	$notanswered="5";
	$privacy="";
	$sid="1295";
	$token=1234567;
	
	if ($templatename) {$templateurl="$publicurl/templates/$templatename/";}
	else {$templateurl="$publicurl/templates/default/";}
	//echo $templateurl;
	//$clearall = "\t\t\t\t\t<div class='clearall'><a href='{$_SERVER['PHP_SELF']}?sid=$sid&move=clearall' onClick='return confirm(\""._CONFIRMCLEAR."\")'>["._EXITCLEAR."]</a></div>\n";
	$clearall = "\t\t\t\t\t<div class='clearall'><a href='{$_SERVER['PHP_SELF']}?sid=$sid&move=clearall' onClick='return confirm(\"Are you sure you want to clear?\")'>[Exit and Clear Responses]</a></div>\n";
	
	$line=str_replace("{SURVEYNAME}", $surveyname, $line);
	$line=str_replace("{SURVEYDESCRIPTION}", $surveydescription, $line);
	$line=str_replace("{WELCOME}", $surveywelcome, $line);
	$line=str_replace("{PERCENTCOMPLETE}", $percentcomplete, $line);
	$line=str_replace("{GROUPNAME}", $groupname, $line);
	$line=str_replace("{GROUPDESCRIPTION}", $groupdescription, $line);
	$line=str_replace("{QUESTION}", $question, $line);
	$line=str_replace("{QUESTION_CODE}", $questioncode, $line);
	$line=str_replace("{ANSWER}", $answer, $line);
	$line=str_replace("{THEREAREXQUESTIONS}", _THEREAREXQUESTIONS, $line);
	$line=str_replace("{NUMBEROFQUESTIONS}", $totalquestions, $line);
	$line=str_replace("{TOKEN}", $token, $line);
	$line=str_replace("{SID}", $sid, $line);
	if ($help) 
		{$line=str_replace("{QUESTIONHELP}", "<img src='".$publicurl."/help.gif' align='left'>".$help, $line);}
	else
		{$line=str_replace("{QUESTIONHELP}", $help, $line);}
	$line=str_replace("{NAVIGATOR}", $navigator, $line);
	//$submitbutton="<input class='submit' type='submit' value=' "._SUBMIT." ' name='move'>";
	$submitbutton="<input class='submit' type='submit' value=' Submit ' name='move'>";
	$line=str_replace("{SUBMITBUTTON}", $submitbutton, $line);
	$line=str_replace("{COMPLETED}", $completed, $line);
	if (!$surveyurldescrip) {$linkreplace="<a href='$surveyurl'>$surveyurl</a>";}
	else {$linkreplace="<a href='$surveyurl'>$surveyurldescrip</a>";}
	$line=str_replace("{URL}", $linkreplace, $line);
	$line=str_replace("{PRIVACY}", $privacy, $line);
	$line=str_replace("{PRIVACYMESSAGE}", _PRIVACY_MESSAGE, $line);
	$line=str_replace("{CLEARALL}", $clearall, $line);
	$line=str_replace("{TEMPLATEURL}", $templateurl, $line);
	return $line;
	}
function makegraph($thisstep, $total)
	{
	global $templatedir, $publicurl, $templatename;
	$chart="$publicurl/templates/$templatedir/chart.jpg";
	if (!is_file($chart)) {$shchart="chart.jpg";}
	else {$shchart = "$publicurl/templates/$templatedir/chart.jpg";}
	$graph = "<table class='graph' width='100' align='center' cellpadding='2'><tr><td>\n";
	$graph .= "<table width='180' align='center' cellpadding='0' cellspacing='0' border='0' class='innergraph'>\n";
	$graph .= "<tr><td align='right' width='40'>0%</td>\n";
	$size=intval(($thisstep-1)/$total*100);
	//$graph .= "<td width='100' align='left'><img src='$shchart' height='12' width='$size' align='left' alt='$size% "._COMPLETE."'></td>\n";
	$graph .= "<td width='100' align='left'><img src='$publicurl/templates/$templatename/$shchart' "
	."height='12' width='$size' align='left' alt='$size% complete'></td>\n";
	$graph .= "<td align='left' width='40'>100%</td></tr>\n";
	$graph .= "</table>\n";
	$graph .= "</td></tr>\n</table>\n";
	return $graph;
	}

if (!$screenname) {$screenname="Welcome";}
$addbr=false;
switch($screenname) {
	case "Question":
		unset($files);
		foreach ($Question as $qs) {
			$files[]=array("name"=>$qs);
		}
		$myoutput[]="<html>\n";
		$myoutput[]="<meta http-equiv=\"expires\" content=\"Wed, 26 Feb 1997 08:21:57 GMT\">\n";
		$myoutput[]="<meta http-equiv=\"Last-Modified\" content=\"".gmdate('D, d M Y H:i:s'). " GMT\">\n";
		$myoutput[]="<meta http-equiv=\"Cache-Control\" content=\"no-store, no-cache, must-revalidate\">\n";
		$myoutput[]="<meta http-equiv=\"Cache-Control\" content=\"post-check=0, pre-check=0, false\">\n";
		$myoutput[]="<meta http-equiv=\"Pragma\" content=\"no-cache\">\n";
		$myoutput = array_merge($myoutput, doreplacement("$publicdir/templates/$templatename/startpage.pstpl"));
		$myoutput = array_merge($myoutput, doreplacement("$publicdir/templates/$templatename/survey.pstpl"));
		$myoutput = array_merge($myoutput, doreplacement("$publicdir/templates/$templatename/startgroup.pstpl"));
		$myoutput = array_merge($myoutput, doreplacement("$publicdir/templates/$templatename/groupdescription.pstpl"));
		
		$question="How many roads must a man walk down?";
		$questioncode="1a";
		$answer="<input type='radio' name='1' value='1' id='radio1'><label class='answertext' for='radio1'>One</label><br /><input type='radio' name='1' value='2' id='radio2'><label class='answertext' for='radio2'>Two</label><br /><input type='radio' name='1' value='3' id='radio3'><label class='answertext' for='radio3'>Three</label><br />\n";
		$myoutput = array_merge($myoutput, doreplacement("$publicdir/templates/$templatename/question.pstpl"));
		
		$question="Please explain your details:";
		$questioncode="2";
		$answer="<textarea class='textarea'>Some text in this answer</textarea>";
		$myoutput = array_merge($myoutput, doreplacement("$publicdir/templates/$templatename/question.pstpl"));
		
		$myoutput = array_merge($myoutput, doreplacement("$publicdir/templates/$templatename/endgroup.pstpl"));
		$myoutput = array_merge($myoutput, doreplacement("$publicdir/templates/$templatename/navigator.pstpl"));
		$myoutput = array_merge($myoutput, doreplacement("$publicdir/templates/$templatename/endpage.pstpl"));
		break;
	case "Welcome":
		unset($files);
		$myoutput[]="<html>";
		foreach ($Welcome as $qs) {
			$files[]=array("name"=>$qs);
			$myoutput = array_merge($myoutput, doreplacement("$publicdir/templates/$templatename/$qs"));
		}
		break;
	case "Clear All":
		unset($files);
		foreach ($Clearall as $qs) {
			$files[]=array("name"=>$qs);
		}
		$myoutput[]="<html>";
		foreach(file("$publicdir/templates/$templatename/startpage.pstpl") as $op)
			{
			$myoutput[]=templatereplace($op);
			}
		$myoutput[]= "<table align='center' cellpadding='30'><tr><td align='center' bgcolor='white'>";
		$myoutput[]= "<font face='arial' size='2'>";
		$myoutput[]= "<b><font color='red'>"._ANSCLEAR."</b></font><br /><br />";
		$myoutput[]= "<a href='{$_SERVER['PHP_SELF']}?sid=x'>"._RESTART."</a><br />";
		$myoutput[]= "<a href='javascript: window.close()'>"._CLOSEWIN."</a>";
		$myoutput[]= "</font>";
		$myoutput[]= "</td></tr>";
		$myoutput[]= "</table><br />";
		foreach(file("$publicdir/templates/$templatename/endpage.pstpl") as $op)
			{
			$myoutput[]= templatereplace($op);
			}
		$myoutput[]= "\n";
		break;
	case "Submit":
		unset($files);
		$myoutput[]="<html>";
		foreach ($Submit as $qs) {
			$files[]=array("name"=>$qs);
			$myoutput = array_merge($myoutput, doreplacement("$publicdir/templates/$templatename/$qs"));
		}
		break;
	case "Completed":
		unset($files);
		$myoutput[]="<html>";
		foreach ($Completed as $qs) {
			$files[]=array("name"=>$qs);
			$myoutput = array_merge($myoutput, doreplacement("$publicdir/templates/$templatename/$qs"));
		}
		break;	
}
$myoutput[]="</html>";
function doreplacement($file) { //Produce sample page from template file
	$output=array();
	foreach(file($file) as $op) {
		$output[]=templatereplace($op);
	}
	return $output;
}

if (is_array($files)) {
	$match=0;
	foreach ($files as $f) {
		if ($editfile == $f["name"]) {
		    $match=1;
		}
	}
	if ($match != 1) {
		if (count($files) == 1) {
		    $editfile=$files[0]["name"];
		} else {
		    $editfile="";
		}
	}
}
//Get list of 'otherfiles'
$dirloc=$publicdir."/templates/".$templatename;
if ($handle = opendir($dirloc)) {
    while(false !== ($file = readdir($handle))) {
		if (!array_search($file, $normalfiles)) {
			if (!is_dir("$dirloc/$file")) {
	    	    $otherfiles[]=array("name"=>$file);
			}
    	}
    } // while
	closedir($handle);
}

//****************************************************************
//** OUTPUT STARTS HERE
//****************************************************************
echo $htmlheader;
echo "<script type='text/javascript'>\n<!--\nfunction copyprompt(text, defvalue, copydirectory)\n"
	."\t{\n"
	."\tvar newtemplatename=window.prompt(text, defvalue);\n"
	."\tvar url='templates.php?action=copy&newname='+newtemplatename+'&copydir='+copydirectory;\n"
	."\talert(url);\n"
	."\twindow.open(url, '_top');\n"
	."\t}\n//-->\n</script>\n";
echo "<table width='100%' border='0' bgcolor='#DDDDDD'>\n"
	. "\t<tr>\n"
	. "\t\t<td>\n"
	. "\t\t\t<table width='100%' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
	. "\t\t\t<tr bgcolor='#555555'>\n"
	. "\t\t\t\t<td colspan='2' height='8'>\n"
	. "\t\t\t\t\t$setfont<font size='1' color='white'><b>".$file_version."</b>\n"
	. "\t\t\t\t</font></font></td>\n"
	. "\t\t\t</tr>\n"
	. "\t\t\t<tr bgcolor='#999999'>\n"
	. "\t\t\t\t<td>\n"
	. "\t\t\t\t\t<input type='image' src='$imagefiles/home.gif' name='HomeButton' alt='"
	. _A_HOME_BT."' title='"
	. _A_HOME_BT."' border='0' align='left' hspace='0' onClick=\"window.open('$scriptname', '_top')\">\n"
	. "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='-' width='11' border='0' hspace='0' align='left'>\n"
	. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='-' border='0' hspace='0' align='left'>"
	. "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='-' width='60' height='10' border='0' hspace='0' align='left'>\n"
	. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='-' border='0' hspace='0' align='left'>"
	."</td><td align='right'>\n"
	."<img src='$imagefiles/blank.gif' align='right' border='0' width='20' hspace='0' alt='Close Window'>"
	."<img src='$imagefiles/plus.gif' align='right' alt='maximise' border='0' hspace='0'>"
	."<img src='$imagefiles/minus.gif' align='right' alt='minimise' border='0' hspace='0'>"
	."<img src='$imagefiles/seperator.gif' alt='|' align='right' alt='minimise' border='0' hspace='0'>"
	."<input type='image' name='New Template' src='$imagefiles/add.gif' hspace='0' border='0' "
	."onClick='window.open(\"templates.php?action=newtemplate\", \"_top\")' align='right' border='0' hspace='0'>"
	."<font face='verdana' size='2' color='white'><b>Template:</b></font>"
	."<select $slstyle name='templatedir' onchange='javascript: window.open(\"templates.php?editfile=$editfile&screenname=$screenname&templatename=\"+this.value, \"_top\")'>\n"
	.makeoptions($templates, "name", "name", $templatename)
	."</select>&nbsp;\n"
	."</td></tr></table>\n"
	."<table><tr><td height='1'></td></tr><table>\n";
	
//TEMPLATE DETAILS
echo "\t\t\t<table width='100%' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
	. "\t\t\t<tr bgcolor='#555555'>\n"
	. "\t\t\t\t<td colspan='2' height='8'>\n"
	. "\t\t\t\t\t$setfont<font size='1' color='white'><b>"._SL_TEMPLATE." <i>$templatename</i></b>\n"
	. "\t\t\t\t</font></font></td>\n"
	. "\t\t\t</tr>\n"
	. "\t\t\t<tr bgcolor='#999999'>\n"
	. "\t\t\t\t<td>\n";
if (is_writable("$publicdir/templates/$templatename")) {
    echo "\t\t\t\t\t<img src='$imagefiles/trafficgreen.gif' alt='Can be modified' hspace='0' align='left'>\n";
	} else {
	echo "\t\t\t\t\t<img src='$imagefiles/trafficred.gif' alt='Cannot be modified' hspace='0' align='left'>\n";
	}
echo "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='-' width='11' border='0' hspace='0' align='left'>\n"
	."\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='-' border='0' hspace='0' align='left'>\n"
	."\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='-' width='60' height='10' border='0' hspace='0' align='left'>\n"
	."\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='-' border='0' hspace='0' align='left'>\n"
	."\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='-' width='60' height='10' border='0' hspace='0' align='left'>\n"
	."\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='-' border='0' hspace='0' align='left'>\n"
	."\t\t\t\t\t<input type='image' name='MakeCopy' src='$imagefiles/copy.gif' align='left' hspace='0' border='0' title='Copy Template'"
	." onClick=\"javascript: copyprompt('Name of copy:', 'newname', '$templatename')\">"
	."</td><td align='right'>\n"
	."<img src='./images/close.gif' align='right' border='0' hspace='0' alt='Close Window' onClick='window.close()'>"
	."<img src='./images/plus.gif' align='right' alt='maximise' border='0' hspace='0'>"
	."<img src='./images/minus.gif' align='right' alt='minimise' border='0' hspace='0'>"
	."<img src='$imagefiles/seperator.gif' alt='|' align='right' alt='minimise' border='0' hspace='0'>"
	."<img src='$imagefiles/blank.gif' alt='-' width='23' align='right' alt='minimise' border='0' hspace='0'>"
	."<font face='verdana' size='2' color='white'><b>Screen:</b></font>"
	. "<select name='screenname' $slstyle onchange='javascript: window.open(\"templates.php?templatename=$templatename&editfile=$editfile&screenname=\"+this.value, \"_top\")'>\n"
	. makeoptions($screens, "name", "name", $screenname)
	. "</select>&nbsp;\n"
	."</td></tr></table>\n"
	."<table><tr><td height='1'></td></tr><table>\n";

//FILE CONTROL DETAILS
echo "\t\t\t<table width='100%' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
	. "\t\t\t<tr bgcolor='#555555'>\n"
	. "\t\t\t\t<td colspan='2' height='8'>\n"
	. "\t\t\t\t\t$setfont<font size='1' color='white'><b>File Control</b>\n"
	. "\t\t\t\t</font></font></td>\n"
	. "\t\t\t</tr>\n"
	. "\t\t\t<tr bgcolor='#999999'><form method='post' action='templates.php'>\n"
	. "\t\t\t\t<td align='center' bgcolor='#DDDDDD'>\n";

echo "\t\t\t\t<table width='100%' border='0'>\n"
	."\t\t\t\t\t<tr>\n"
	."\t\t\t\t\t\t<td align='center' valign='top' width='150'>"
	."$setfont<b>Files</b><font size='1'><br />\n"
	."<select size='8' $slstyle2 name='editfile' onChange='javascript: window.open(\"templates.php?templatename=$templatename&screenname=$screenname&editfile=\"+this.value, \"_top\")'>\n"
	.makeoptions($files, "name", "name", $editfile)
	."</select><br /><br />\n"
	."\t\t\t\t\t\t</font></font></td>\n"
	."\t\t\t\t\t\t<td align='center' valign='top'>"
	."$setfont<i><b>Now Editing $editfile</b></i><font size='1'><br />\n"
	."<textarea $slstyle3 name='changes' cols='110' rows='8'>";
if ($editfile) {
	echo filetext($editfile);
}
echo "</textarea><br />\n";
if (is_writable("$publicdir/templates/$templatename")) {
echo "<input $btstyle align='right' type='submit' value='Save Changes'";
if ($templatename == "default") {
    echo " disabled";
}
echo ">";
	}
echo "<br />\n"
	. "</font></font></td></form>\n"
	."\t\t\t\t\t\t<td valign='top' align='right'>"
	. "$setfont<b>Other Files</b><br />\n"
	//. "<iframe width='100%' height='140' src=\"templates.html\"></iframe>"
	. "<select size='8' $slstyle2 name='otherfile' style='width: 120'>\n"
	.makeoptions($otherfiles, "name", "name", "")
	."</select>\n"
	."<input type='submit' value='Del' $btstyle";
if ($templatename == "default") {
    echo " disabled";
}
echo "><input type='submit' value='Upload' $btstyle";
if ($templatename == "default") {
    echo " disabled";
}
echo ">"
	."\t\t\t\t\t\t</font></font></td>\n"
	."\t\t\t\t\t</tr>\n"
	."\t\t\t\t</table>\n"
	."\t\t\t</td>\n"
	."\t\t<input type='hidden' name='templatename' value='$templatename' />\n"
	."\t\t<input type='hidden' name='screenname' value='$screenname' />\n"
	."\t\t<input type='hidden' name='editfile' value='$editfile' />\n"
	."\t\t<input type='hidden' name='action' value='savechanges' />\n"
	."\t</tr>"
	."</table>"
	."<table><tr><td height='1'></td></tr><table>\n";

//SAMPLE ROW
echo "\t\t\t<table width='100%' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
	. "\t\t\t<tr bgcolor='#555555'>\n"
	. "\t\t\t\t<td colspan='2' height='8'>\n"
	. "\t\t\t\t\t$setfont<font size='1' color='white'><b>Preview:</b>\n"
	. "\t\t\t\t</font></font></td>\n"
	. "\t\t\t</tr>\n"
	."\t<tr>\n"
	."\t\t<td width=90% align='center' bgcolor='#EEEEEE'>\n";


unlink_wc($tempdir, "template_temp_*.html"); //Delete any older template files
$time=date("ymdHis");
$fnew=fopen("$tempdir/template_temp_$time.html", "w+");
foreach($myoutput as $line) {
	fwrite($fnew, $line);
}
fclose($fnew);

echo "<font face='verdana' size='2'><b>Sample $screenname in $templatename template</b><br />\n"
	."<iframe src='$tempurl/template_temp_$time.html' width='95%' height='400' name='sample'></iframe>\n"
	."<br />&nbsp;<br />"
	."</td></tr></table>\n"
	.htmlfooter("", "");

function unlink_wc($dir, $pattern){
   if ($dh = opendir($dir)) { 
       
       //List and put into an array all files
       while (false !== ($file = readdir($dh))){
           if ($file != "." && $file != "..") {
               $files[] = $file;
           }
       }
       closedir($dh);
       
       
       //Split file name and extenssion
       if(strpos($pattern,".")) {
           $baseexp=substr($pattern,0,strpos($pattern,"."));
           $typeexp=substr($pattern,strpos($pattern,".")+1,strlen($pattern));
       }else{ 
           $baseexp=$pattern;
           $typeexp="";
       } 
       
       //Escape all regexp Characters 
       $baseexp=preg_quote($baseexp); 
       $typeexp=preg_quote($typeexp); 
       
       // Allow ? and *
       $baseexp=str_replace(array("\*","\?"), array(".*","."), $baseexp);
       $typeexp=str_replace(array("\*","\?"), array(".*","."), $typeexp);
       
       //Search for pattern match
       $i=0;
       foreach($files as $file) {
           $filename=basename($file);
           if(strpos($filename,".")) {
               $base=substr($filename,0,strpos($filename,"."));
               $type=substr($filename,strpos($filename,".")+1,strlen($filename));
           }else{
               $base=$filename;
               $type="";
           }
       
           if(preg_match("/^".$baseexp."$/i",$base) && preg_match("/^".$typeexp."$/i",$type))  {
               $matches[$i]=$file;
               $i++;
           }
       }
       if (isset($matches)) {
	       while(list($idx,$val) = each($matches)){
	           if (substr($dir,-1) == "/"){
	               unlink($dir.$val);
	           }else{
	               unlink($dir."/".$val);
	           }
       		}
       }
       
   }
}

?>