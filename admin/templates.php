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
require_once("config.php");
$file_version="PHPSurveyor Template Editor ".$versionnumber;
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

if ($action == "copy" && isset($_GET['newname']) && isset($_GET['copydir'])) {
    //Copies all the files from one template directory to a new one
	//This is a security issue because it is allowing copying from get variables...
	$newdirname=$publicdir."/templates/".$_GET['newname'];
	$copydirname=$publicdir."/templates/".$_GET['copydir'];
	$mkdirresult=mkdir_p($newdirname);
	if ($mkdirresult == 1) {
	    $copyfiles=getListOfFiles($copydirname);
		foreach ($copyfiles as $file) {
			$copyfile=$copydirname."/".$file;
			$newfile=$newdirname."/".$file;
			if (!copy($copyfile, $newfile)) {
			    echo "<script type=\"text/javascript\">\n<!--\nalert('Failed to copy $file to new template directory.');\n//-->\n</script>";
			}
		}
		$templates[]=array("name"=>$_GET['newname'], "dir"=>$newdirname);
		$templatename=$_GET['newname'];
	} elseif($mkdirresult == 2) {
		echo "<script type=\"text/javascript\">\n<!--\nalert('Directory with the name `".$_GET['newname']."` already exists - choose another name');\n//-->\n</script>";
	} else {
		echo "<script type=\"text/javascript\">\n<!--\nalert('Unable to create directory `".$_GET['newname']."`. Maybe you don't have permission.');\n//-->\n</script>";
	}
}

if ($action == "rename" && isset($_GET['newname']) && isset($_GET['copydir'])) {
    $newdirname=$publicdir."/templates/".$_GET['newname'];
	$olddirname=$publicdir."/templates/".$_GET['copydir'];
	if (!rename($olddirname, $newdirname)) {
		echo "<script type=\"text/javascript\">\n<!--\nalert('Directory could not be renamed to `".$_GET['newname']."`. Maybe you don't have permission.');\n//-->\n</script>";
	} else {
	$templates[]=array("name"=>$_GET['newname'], "dir"=>$newdirname);
	$templatename=$_GET['newname'];
	}
}

if ($action == "upload") {
	//Uploads the file into the appropriate directory
	$the_full_file_path = $publicdir."/templates/".$templatename . "/" . $_FILES['the_file']['name']; //This is where the temp file is
	if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $the_full_file_path)) {
		echo "<b><font color='red'>"._ERROR."</font></b><br />\n";
		echo _IS_FAILUPLOAD."<br /><br />\n";
		echo "<input $btstyle type='submit' value='"._GO_ADMIN."' onClick=\"window.open('$scriptname', '_top')\">\n";
		echo "</td></tr></table>\n";
		echo "</body>\n</html>\n";
		exit;
	}	
}

if ($action == "delete") {
	if ($_POST['otherfile'] != "chart.jpg") {
	    $the_full_file_path = $publicdir."/templates/".$templatename . "/" . $_POST['otherfile']; //This is where the temp file is
		unlink($the_full_file_path);
	}
}

if ($action == "zip") {
	require("classes/phpzip.inc.php");
	$z = new PHPZip();
	$templatedir="$publicdir/templates/$templatename/";
	$zipfile="$tempdir/$templatename.zip";
	$z -> Zip($templatedir, $zipfile);
	if (is_file($zipfile)) {
	    //Send the file for download!
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
		
		header("Content-Type: application/force-download");
		header( "Content-Disposition: attachment; filename=$templatename.zip" );
		header( "Content-Description: File Transfer");
		@readfile($zipfile);

		//Delete the temporary file
		unlink($zipfile);
		
	}
}


function mkdir_p($target){
	//creates a new directory
	//Returns 1 for success
	//        2 for "directory/file by that name exists
	//        0 for other errors
	if(file_exists($target) || is_dir($target))
		return 2; 
	if(mkdir($target)){ 
		return 1; 
  	} 
	if(mkdir_p(substr($target, 0, (strrpos($target, '/')))) == 1){ 
		if(mkdir_p($target) == 1) 
			return 1; 
		else 
			return 0; 
	} else { 
		return 0; 
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
$files[]=array("name"=>"clearall.pstpl");
$files[]=array("name"=>"register.pstpl");
$files[]=array("name"=>"load.pstpl");
$files[]=array("name"=>"save.pstpl");
$files[]=array("name"=>"assessment.pstpl");

$normalfiles=array("DUMMYENTRY", ".", "..");
foreach ($files as $fl) {
	$normalfiles[]=$fl["name"];
}

$screens[]=array("name"=>_TP_WELCOMEPAGE);
$screens[]=array("name"=>_TP_QUESTIONPAGE);
$screens[]=array("name"=>_TP_SUBMITPAGE);
$screens[]=array("name"=>_TP_COMPLETEDPAGE);
$screens[]=array("name"=>_TP_CLEARALLPAGE);
$screens[]=array("name"=>_TP_REGISTERPAGE);
$screens[]=array("name"=>_TP_LOADPAGE);
$screens[]=array("name"=>_TP_SAVEPAGE);

//Page Display Instructions
$Welcome=array("startpage.pstpl", "welcome.pstpl", "navigator.pstpl", "endpage.pstpl");
$Question=array("startpage.pstpl", "survey.pstpl", "startgroup.pstpl", "groupdescription.pstpl", "question.pstpl", "endgroup.pstpl", "navigator.pstpl", "endpage.pstpl");
$Submit=array("startpage.pstpl", "survey.pstpl", "submit.pstpl", "privacy.pstpl", "navigator.pstpl", "endpage.pstpl");
$Completed=array("startpage.pstpl", "assessment.pstpl", "completed.pstpl", "endpage.pstpl");
$Clearall=array("startpage.pstpl", "clearall.pstpl", "endpage.pstpl");
$Register=array("startpage.pstpl", "survey.pstpl", "register.pstpl", "endpage.pstpl");
$Save=array("startpage.pstpl", "save.pstpl", "endpage.pstpl");
$Load=array("startpage.pstpl", "load.pstpl", "endpage.pstpl");

//CHECK ALL FILES EXIST, AND IF NOT - COPY IT FROM DEFAULT DIRECTORY
foreach ($files as $file) {
	$thisfile="$publicdir/templates/$templatename/".$file['name'];
	if (!is_file($thisfile)) {
	    $copyfile="$publicdir/templates/default/".$file['name'];
		$newfile=$thisfile;
		if (!@copy($copyfile, $newfile)) {
		    echo "<script type=\"text/javascript\">\n<!--\nalert('Failed to copy ".$file['name']." to new template directory.');\n//-->\n</script>";
		}
	}
}
//Load this editfile
function filetext($templatefile) {
	global $publicdir, $templatename;
	$output="";
	foreach(file("$publicdir/templates/$templatename/$templatefile") as $line) {
		$output .= $line;
	}
	return $output;
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

if (!$screenname) {$screenname=_TP_WELCOMEPAGE;}
if ($screenname != _TP_WELCOMEPAGE) {$_SESSION['step']=1;} else {unset($_SESSION['step']);} //This helps handle the load/save buttons
if ($screenname == _TP_SUBMITPAGE) {$_POST['move'] = " "._LAST." ";}
//FAKE DATA FOR TEMPLATES
$thissurvey['name']="Template Sample";
$thissurvey['description']="This is a sample survey description. It could be quite long.<br /><br />But this one isn't.";
$thissurvey['welcome']="Welcome to this sample survey.<br />\n You should have a great time doing this<br />";
$thissurvey['allowsave']="Y";
$thissurvey['templatedir']=$templatename;
$thissurvey['format']="G";
$thissurvey['url']="http://phpsurveyor.sourceforge.net/";
$thissurvey['urldescrip']="A URL Description";
$percentcomplete=makegraph(6, 10);
$groupname="Group 1: The first lot of questions";
$groupdescription="This group description is fairly vacuous, but quite important.";
$navigator="<input class='submit' type='submit' value=' next >> ' name='move' />";
if ($screenname != _TP_WELCOMEPAGE) {$navigator = "<input class='submit' type='submit' value=' << prev ' name='move' />\n".$navigator;}
$help="This is some help text";
$totalquestions="10";
$surveyformat="Format";
$completed="Survey is completed and saved.";
$notanswered="5";
$privacy="";
$surveyid="1295";
$token=1234567;
$assessments="<table align='center'><tr><th>Assessment Heading</th></tr><tr><td align='center'>Assessment details<br />Note that assessments will only show if assessment rules have been set. Otherwise, this assessment table will not appear</td></tr></table>";

$addbr=false;
switch($screenname) {
	case _TP_QUESTIONPAGE:
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
	case _TP_WELCOMEPAGE:
		unset($files);
		$myoutput[]="<html>";
		foreach ($Welcome as $qs) {
			$files[]=array("name"=>$qs);
			$myoutput = array_merge($myoutput, doreplacement("$publicdir/templates/$templatename/$qs"));
		}
		break;
	case _TP_REGISTERPAGE:
		unset($files);
		foreach($Register as $qs) {
			$files[]=array("name"=>$qs);
		}
		$myoutput[]="<html>";
		foreach(file("$publicdir/templates/$templatename/startpage.pstpl") as $op)
			{
			$myoutput[]=templatereplace($op);
			}
		foreach(file("$publicdir/templates/$templatename/survey.pstpl") as $op)
			{
			$myoutput[]=templatereplace($op);
			}
		foreach(file("$publicdir/templates/$templatename/register.pstpl") as $op)
			{
			$myoutput[]=templatereplace($op);
			}
		foreach(file("$publicdir/templates/$templatename/endpage.pstpl") as $op)
			{
			$myoutput[]=templatereplace($op);
			}
		$myoutput[]= "\n";
		break;
	case _TP_SAVEPAGE:
		unset($files);
		foreach($Save as $qs) {
			$files[]=array("name"=>$qs);
		}
		$myoutput[]="<html>";
		foreach(file("$publicdir/templates/$templatename/startpage.pstpl") as $op)
			{
			$myoutput[]=templatereplace($op);
			}
		foreach(file("$publicdir/templates/$templatename/save.pstpl") as $op)
			{
			$myoutput[]=templatereplace($op);
			}
		foreach(file("$publicdir/templates/$templatename/endpage.pstpl") as $op)
			{
			$myoutput[]=templatereplace($op);
			}
		$myoutput[]= "\n";
		break;
	case _TP_LOADPAGE:
		unset($files);
		foreach($Load as $qs) {
			$files[]=array("name"=>$qs);
		}
		$myoutput[]="<html>";
		foreach(file("$publicdir/templates/$templatename/startpage.pstpl") as $op)
			{
			$myoutput[]=templatereplace($op);
			}
		foreach(file("$publicdir/templates/$templatename/load.pstpl") as $op)
			{
			$myoutput[]=templatereplace($op);
			}
		foreach(file("$publicdir/templates/$templatename/endpage.pstpl") as $op)
			{
			$myoutput[]=templatereplace($op);
			}
		$myoutput[]= "\n";
		break;
	case _TP_CLEARALLPAGE:
		unset($files);
		foreach ($Clearall as $qs) {
			$files[]=array("name"=>$qs);
		}
		$myoutput[]="<html>";
		foreach(file("$publicdir/templates/$templatename/startpage.pstpl") as $op)
			{
			$myoutput[]=templatereplace($op);
			}
		foreach(file("$publicdir/templates/$templatename/clearall.pstpl") as $op)
			{
			$myoutput[]=templatereplace($op);
			}
		foreach(file("$publicdir/templates/$templatename/endpage.pstpl") as $op)
			{
			$myoutput[]=templatereplace($op);
			}
		$myoutput[]= "\n";
		break;
	case _TP_SUBMITPAGE:
		unset($files);
		$myoutput[]="<html>";
		foreach ($Submit as $qs) {
			$files[]=array("name"=>$qs);
			$myoutput = array_merge($myoutput, doreplacement("$publicdir/templates/$templatename/$qs"));
		}
		break;
	case _TP_COMPLETEDPAGE:
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
sendcacheheaders();
echo $htmlheader;
echo "<script type='text/javascript'>\n"
	."<!--\n"
	."function copyprompt(text, defvalue, copydirectory, action)\n"
	."\t{\n"
	."\tif (newtemplatename=window.prompt(text, defvalue))\n"
	."\t\t{\n"
	."\t\tvar url='templates.php?action='+action+'&newname='+newtemplatename+'&copydir='+copydirectory;\n"
	."\t\twindow.open(url, '_top');\n"
	."\t\t}\n"
	."\t}\n"
	."//-->\n</script>\n";
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
	."<img src='$imagefiles/blank.gif' align='right' border='0' hspace='0' width='60' height='10'>"
	."<img src='$imagefiles/seperator.gif' alt='|' align='right' alt='minimise' border='0' hspace='0'>"
	."<input type='image' src='$imagefiles/add.gif' align='right' hspace='0' title='"._TP_CREATENEW."' "
	."onClick=\"javascript: copyprompt('"._TP_NEWTEMPLATECALLED."', '"._TP_DEFAULTNEWTEMPLATE."', 'default', 'copy')\">"
	."<font face='verdana' size='2' color='white'><b>"._SL_TEMPLATE."</b> </font>"
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
    echo "\t\t\t\t\t<img src='$imagefiles/trafficgreen.gif' alt='"._TP_CANMODIFY."' hspace='0' align='left'>\n";
	} else {
	echo "\t\t\t\t\t<img src='$imagefiles/trafficred.gif' alt='"._TP_CANNOTMODIFY."' hspace='0' align='left'>\n";
	}
echo "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='-' width='11' border='0' hspace='0' align='left'>\n"
	."\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='-' border='0' hspace='0' align='left'>\n"
	."\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='-' width='60' height='10' border='0' hspace='0' align='left'>\n"
	."\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='-' border='0' hspace='0' align='left'>\n"
	."\t\t\t\t\t<input type='image' name='EditName' src='$imagefiles/edit.gif' align='left' hspace='0' "
	."border='0' title='"._TP_RENAME."'"
	." onClick=\"javascript: copyprompt('"._TP_RENAMETO."', '$templatename', '$templatename', 'rename')\"";
if ($templatename == "default") {echo " disabled";}
echo ">";
echo "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='-' width='20' height='10' border='0' hspace='0' align='left'>\n"
	."\t\t\t\t\t<input type='image' name='Export' src='$imagefiles/export.gif' align='left' hspace='0' "
	."border='0' title='"._TP_EXPORT."'"
	." onClick='javascript:window.open(\"templates.php?action=zip&editfile=$editfile&screenname=$screenname&templatename=$templatename\", \"_top\")'>\n"
	."\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='-' border='0' hspace='0' align='left'>\n"
	."\t\t\t\t\t<input type='image' name='MakeCopy' src='$imagefiles/copy.gif' align='left' hspace='0' "
	."border='0' title='"._TP_COPY."'"
	." onClick=\"javascript: copyprompt('"._TP_COPYTO."', '"._TP_COPYOF."$templatename', '$templatename', 'copy')\">"
	."</td><td align='right'>\n"
	."<img src='./images/blank.gif' align='right' alt='-' border='0' hspace='0' width='60' height='10'>"
	."<img src='$imagefiles/seperator.gif' alt='|' align='right' alt='minimise' border='0' hspace='0'>"
	."<img src='$imagefiles/blank.gif' alt='-' width='23' align='right' alt='minimise' border='0' hspace='0'>"
	."<font face='verdana' size='2' color='white'><b>"._TP_SCREEN."</b> </font>"
	. "<select name='screenname' $slstyle onchange='javascript: window.open(\"templates.php?templatename=$templatename&editfile=$editfile&screenname=\"+this.value, \"_top\")'>\n"
	. makeoptions($screens, "name", "name", $screenname)
	. "</select>&nbsp;\n"
	."</td></tr></table>\n"
	."<table><tr><td height='1'></td></tr><table>\n";

//FILE CONTROL DETAILS
echo "\t\t\t<table width='100%' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
	. "\t\t\t<tr bgcolor='#555555'>\n"
	. "\t\t\t\t<td colspan='2' height='8'>\n"
	. "\t\t\t\t\t$setfont<font size='1' color='white'><b>"._TP_FILECONTROL."</b>\n"
	. "\t\t\t\t</font></font></td>\n"
	. "\t\t\t</tr>\n"
	. "\t\t\t<tr bgcolor='#999999'>"
	."<form name='editTemplate' method='post' action='templates.php'>\n"
	. "\t\t\t<input type='hidden' name='templatename' value='$templatename' />\n"
	. "\t\t\t<input type='hidden' name='screenname' value='$screenname' />\n"
	. "\t\t\t<input type='hidden' name='editfile' value='$editfile' />\n"
	. "\t\t\t<input type='hidden' name='action' value='savechanges' />\n"
	. "\t\t\t\t<td align='center' bgcolor='#DDDDDD'>\n";

echo "\t\t\t\t<table width='100%' border='0'>\n"
	."\t\t\t\t\t<tr>\n"
	."\t\t\t\t\t\t<td align='left' valign='top' width='150'>"
	."$setfont<b>"._TP_STANDARDFILES."</b><font size='1'><br />\n"
	."<select size='12' $slstyle2 name='editfile' onChange='javascript: window.open(\"templates.php?templatename=$templatename&screenname=$screenname&editfile=\"+this.value, \"_top\")'>\n"
	.makeoptions($files, "name", "name", $editfile)
	."</select><br /><br />\n"
	."\t\t\t\t\t\t</font></font></td>\n"
	."\t\t\t\t\t\t<td align='center' valign='top'>"
	."$setfont<b>"._TP_NOWEDITING." <i>$editfile</i></b><font size='1'><br />\n"
	."<textarea $slstyle3 name='changes' id='changes' cols='110' rows='12' onChange=\"setCursorPos()\" onClick=\"setCursorPos()\">";
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
	."\t\t\t\t\t\t<form action='templates.php' method='post'><td valign='top' align='right'>"
	. "$setfont<b>"._TP_OTHERFILES."</b><br />\n"
	//. "<iframe width='100%' height='140' src=\"templates.html\"></iframe>"
	. "<select size='9' $slstyle2 name='otherfile' id='otherfile' style='width: 120'>\n"
	.makeoptions($otherfiles, "name", "name", "")
	."</select>"
	."<table width='90' align='right' border='0' cellpadding='0' cellspacing='0'>\n"
	."<tr><td align='right'>$setfont"
	."<input type='submit' value='"._TP_DELETEFILE."' $btstyle onClick=\"javascript:return confirm('Are you sure you want to delete this file?')\"";
if ($templatename == "default") {
    echo " disabled";
}
echo "></font></td>\n"
	."<input type='hidden' name='editfile' value='$editfile'>\n"
	."<input type='hidden' name='screenname' value='$screenname'>\n"
	."<input type='hidden' name='templatename' value='$templatename'>\n"
	."<input type='hidden' name='action' value='delete'>\n"
	."</form></tr><tr $btstyle>"
	."<form enctype='multipart/form-data' name='importsurvey' action='templates.php' method='post'>\n"
	."<td align='right' style='border: solid 1 #000080'>$setfont"
	."<input $btstyle name=\"the_file\" type=\"file\" size=\"7\"><br />"
	."<input type='submit' value='"._TP_UPLOADFILE."' $btstyle";
if ($templatename == "default") {
    echo " disabled";
}
echo "></font></td>\n"
	."<input type='hidden' name='editfile' value='$editfile'>\n"
	."<input type='hidden' name='screenname' value='$screenname'>\n"
	."<input type='hidden' name='templatename' value='$templatename'>\n"
	."<input type='hidden' name='action' value='upload'>\n"
	."</form></tr></table>\n"
	."\t\t\t\t\t\t</font></font></td>\n"
	."\t\t\t\t\t</tr>\n"
	."\t\t\t\t</table>\n"
	."\t\t\t</td>\n"
	."\t</tr>"
	."</table>"
	."<table><tr><td height='1'></td></tr><table>\n";

//SAMPLE ROW
echo "\t\t\t<table width='100%' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
	. "\t\t\t<tr bgcolor='#555555'>\n"
	. "\t\t\t\t<td colspan='2' height='8'>\n"
	. "\t\t\t\t\t$setfont<font size='1' color='white'><b>"._TP_PREVIEW."</b>\n"
	. "\t\t\t\t</font></font></td>\n"
	. "\t\t\t</tr>\n"
	."\t<tr>\n"
	."\t\t<td width=90% align='center' bgcolor='#555555'>\n";


unlink_wc($tempdir, "template_temp_*.html"); //Delete any older template files
$time=date("ymdHis");
$fnew=fopen("$tempdir/template_temp_$time.html", "w+");
foreach($myoutput as $line) {
	fwrite($fnew, $line);
}
fclose($fnew);

echo "<font face='verdana' size='2'><br />\n"
	."<iframe src='$tempurl/template_temp_$time.html' width='95%' height='400' name='sample' style='background-color: white'></iframe>\n"
	."<br />&nbsp;<br />"
	."</td></tr></table>\n"
	.htmlfooter("instructions.html#Templates", "");

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

function getListOfFiles($wh){
	//Returns an array containing all files in a directory
	if ($handle = opendir($wh)) {
	while (false !== ($file = readdir($handle))) { 
		if ($file != "." && $file != ".." && !is_dir($file)) { 
			if(!isset($files) || !$files) $files="$file";
			else $files="$file\n$files";
			} 
		}
	closedir($handle); 
	}
$arr=explode("\n",$files);
return $arr;
}

?>