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


include_once("login_check.php");
//Standard Template Files
//Only these files may be edited or saved
$files[]=array("name"=>"assessment.pstpl");
$files[]=array("name"=>"clearall.pstpl");
$files[]=array("name"=>"completed.pstpl");
$files[]=array("name"=>"endgroup.pstpl");
$files[]=array("name"=>"endpage.pstpl");
$files[]=array("name"=>"groupdescription.pstpl");
$files[]=array("name"=>"load.pstpl");
$files[]=array("name"=>"navigator.pstpl");
$files[]=array("name"=>"printanswers.pstpl");
$files[]=array("name"=>"privacy.pstpl");
$files[]=array("name"=>"question.pstpl");
$files[]=array("name"=>"register.pstpl");
$files[]=array("name"=>"save.pstpl");
$files[]=array("name"=>"surveylist.pstpl");
$files[]=array("name"=>"startgroup.pstpl");
$files[]=array("name"=>"startpage.pstpl");
$files[]=array("name"=>"survey.pstpl");
$files[]=array("name"=>"welcome.pstpl");

//Standard Screens
//Only these may be viewed

$screens[]=array("name"=>$clang->gT("Survey List Page", "unescaped"));
$screens[]=array("name"=>$clang->gT("Welcome Page", "unescaped"));
$screens[]=array("name"=>$clang->gT("Question Page", "unescaped"));
$screens[]=array("name"=>$clang->gT("Completed Page", "unescaped"));
$screens[]=array("name"=>$clang->gT("Clear All Page", "unescaped"));
$screens[]=array("name"=>$clang->gT("Register Page", "unescaped"));
$screens[]=array("name"=>$clang->gT("Load Page", "unescaped"));
$screens[]=array("name"=>$clang->gT("Save Page", "unescaped"));
$screens[]=array("name"=>$clang->gT("Print Answers Page", "unescaped"));


// Set this so common.php doesn't throw notices about undefined variables
$thissurvey['active']='N';


$file_version="LimeSurvey Template Editor ".$versionnumber;
$_SESSION['s_lang']=$_SESSION['adminlang'];

if (!isset($templatename)) {$templatename = sanitize_paranoid_string(returnglobal('templatename'));}
if (!isset($templatedir)) {$templatedir = sanitize_paranoid_string(returnglobal('templatedir'));}
if (!isset($editfile)) {$editfile = sanitize_paranoid_string(returnglobal('editfile'));}
if (!isset($screenname)) {$screenname=auto_unescape(returnglobal('screenname'));}

// Checks if screen name is in the list of allowed screen names  
if ( isset($screenname) && (multiarray_search($screens,'name',$screenname)===false)) {die('Invalid screen name');}  // Die you sneaky bastard!

if (!isset($action)) {$action=sanitize_paranoid_string(returnglobal('action'));}
if (!isset($otherfile)) {$otherfile = sanitize_paranoid_string(returnglobal('otherfile'));}
if (!isset($newname)) {$newname = sanitize_paranoid_string(returnglobal('newname'));}
if (!isset($copydir)) {$copydir = sanitize_paranoid_string(returnglobal('copydir'));}


if (file_exists($tpldir."/".$templatename."/template.css")) { $files[]=array("name"=>"template.css"); }


if (isset ($_POST['changes'])) {
	    $changedtext=$_POST['changes'];
	    if(get_magic_quotes_gpc())
	    {
	       $changedtext = str_replace("\'", stripslashes("'"), $changedtext);
	       $changedtext = str_replace('\"', stripslashes('"'), $changedtext);
	    }
	}


if ($action != "newtemplate" && !$templatename) {$templatename = "default";}
$template_a=gettemplatelist();
foreach ($template_a as $tp) {
	$templates[]=array("name"=>$tp, "dir"=>$tpldir."/".$tp);
}
unset($template_a);


//Save Changes if necessary
if ($action=="templatesavechanges" && $changedtext) {
	$changedtext=str_replace("\r\n", "\n", $changedtext);
	if ($editfile) {
        // Check if someone tries to submit a file other than one of the allowed filenames
        if (multiarray_search($files,'name',$editfile)===false) {die('Invalid template filename');}  // Die you sneaky bastard!
		$savefilename=$tpldir."/".$templatename."/".$editfile;
		if (is_writable($savefilename)) {
			if (!$handle = fopen($savefilename, 'w')) {
				echo "Could not open file ($savefilename)";
				exit;
			}
			if (!fwrite($handle, $changedtext)) {
				echo "Cannot write to file ($savefilename)";
				exit;
			}
			fclose($handle);
		} else {
			echo "The file $savefilename is not writable";
		}
	}
}

if ($action == "templatecopy" && isset($newname) && isset($copydir)) {
	//Copies all the files from one template directory to a new one
	//This is a security issue because it is allowing copying from get variables...
	$newdirname=$tpldir."/".$newname;
	$copydirname=$tpldir."/".$copydir;
	$mkdirresult=mkdir_p($newdirname);
	if ($mkdirresult == 1) {
		$copyfiles=getListOfFiles($copydirname);
		foreach ($copyfiles as $file) {
			$copyfile=$copydirname."/".$file;
			$newfile=$newdirname."/".$file;
			if (!copy($copyfile, $newfile)) {
                echo "<script type=\"text/javascript\">\n<!--\nalert(\"".sprintf($clang->gT("Failed to copy %s to new template directory.","js"), $file)."\");\n//-->\n</script>";
			}
		}
		$templates[]=array("name"=>$newname, "dir"=>$newdirname);
		$templatename=$newname;
	} elseif($mkdirresult == 2) {
        echo "<script type=\"text/javascript\">\n<!--\nalert(\"".sprintf($clang->gT("Directory with the name `%s` already exists - choose another name","js"), $newname)."\");\n//-->\n</script>";
	} else {
            echo "<script type=\"text/javascript\">\n<!--\nalert(\"".sprintf($clang->gT("Unable to create directory `%s`.","js"), $newname)." ".$clang->gT("Please check the directory permissions.","js")."\");\n//-->\n</script>";	
    }
}

if ($action == "templaterename" && isset($newname) && isset($copydir)) {
	$newdirname=$tpldir."/".$newname;
	$olddirname=$tpldir."/".$copydir;
	if (rename($olddirname, $newdirname)==false) {
        echo "<script type=\"text/javascript\">\n<!--\nalert(\"".sprintf($clang->gT("Directory could not be renamed to `%s`.","js"), $newname)." ".$clang->gT("Maybe you don't have permission.","js")."\");\n//-->\n</script>";
	} else {
		$templates[]=array("name"=>$newname, "dir"=>$newdirname);
		$templatename=$newname;
	}
}

if ($action == "templateupload") 
  {

      if ($demoModeOnly == true)
      {
			$action = '';
			
      } else
      {
	  $the_full_file_path = $tpldir."/".$templatename . "/" . $_FILES['the_file']['name']; //This is where the temp file is
      if ($extfile = strrchr($_FILES['the_file']['name'], '.'))
      {
         if  (!(stripos(','.$allowedtemplateuploads.',',','. substr($extfile,1).',') === false))
         {
              //Uploads the file into the appropriate directory
              if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $the_full_file_path)) {
                  echo "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
                  echo sprintf ($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),$tempdir)."<br /><br />\n";
                  echo "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" />\n";
                  echo "</td></tr></table>\n";
                  echo "</body>\n</html>\n";
                  exit;
              }
         }
          else
          {
              // if we came here is because the file extention is not allowed
              @unlink($_FILES['the_file']['tmp_name']);
              echo "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
              echo $clang->gT("This file type is not allowed to be uploaded.")."<br /><br />\n";
              echo "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" />\n";
              echo "</td></tr></table>\n";
              echo "</body>\n</html>\n";
              exit;
          }
      }
      else
      {
          // if we came here is because the file extention is not allowed
          @unlink($_FILES['the_file']['tmp_name']);
          echo "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
          echo $clang->gT("This file type is not allowed to be uploaded.")."<br /><br />\n";
          echo "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" />\n";
          echo "</td></tr></table>\n";
          echo "</body>\n</html>\n";
          exit;
      }
      }
}

if ($action == "templatefiledelete") {
	$the_full_file_path = $tpldir."/".$templatename."/".$otherfile; //This is where the temp file is
	unlink($the_full_file_path);
}

if ($action == "templatezip") {
	require("classes/phpzip/phpzip.inc.php");
	$z = new PHPZip();
	$templatedir="$tpldir/$templatename/";
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


$normalfiles=array("DUMMYENTRY", ".", "..", "preview.png");
foreach ($files as $fl) {
	$normalfiles[]=$fl["name"];
}
if (file_exists($tpldir."/".$templatename."/template.css")) { $normalfiles[]="template.css"; }



//Page Display Instructions
$SurveyList=array("startpage.pstpl", "surveylist.pstpl", "endpage.pstpl");
$Welcome=array("startpage.pstpl", "welcome.pstpl", "privacy.pstpl", "navigator.pstpl", "endpage.pstpl");
$Question=array("startpage.pstpl", "survey.pstpl", "startgroup.pstpl", "groupdescription.pstpl", "question.pstpl", "endgroup.pstpl", "navigator.pstpl", "endpage.pstpl");
$CompletedTemplate=array("startpage.pstpl", "assessment.pstpl", "completed.pstpl", "endpage.pstpl");
$Clearall=array("startpage.pstpl", "clearall.pstpl", "endpage.pstpl");
$Register=array("startpage.pstpl", "survey.pstpl", "register.pstpl", "endpage.pstpl");
$Save=array("startpage.pstpl", "save.pstpl", "endpage.pstpl");
$Load=array("startpage.pstpl", "load.pstpl", "endpage.pstpl");
$printtemplate=array("startpage.pstpl", "printanswers.pstpl", "endpage.pstpl");

//CHECK ALL FILES EXIST, AND IF NOT - COPY IT FROM DEFAULT DIRECTORY
foreach ($files as $file) {
	$thisfile="$tpldir/$templatename/".$file['name'];
	if (!is_file($thisfile)) {
		$copyfile="$tpldir/default/".$file['name'];
		$newfile=$thisfile;
		if (!@copy($copyfile, $newfile)) {
            echo "<script type=\"text/javascript\">\n<!--\nalert(\"".sprintf($clang->gT("Failed to copy %s to new template directory.","js"), $file['name'])."\");\n//-->\n</script>";
		}
	}
}


if (!$screenname) {$screenname=$clang->gT("Welcome Page", "unescaped");}
if ($screenname != $clang->gT("Welcome Page")) {$_SESSION['step']=1;} else {unset($_SESSION['step']);} //This helps handle the load/save buttons
//FAKE DATA FOR TEMPLATES
$thissurvey['name']=$clang->gT("Template Sample");
$thissurvey['description']="This is a sample survey description. It could be quite long.<br /><br />But this one isn't.";
$thissurvey['welcome']="Welcome to this sample survey.<br />\n You should have a great time doing this<br />";
$thissurvey['allowsave']="Y";
$thissurvey['templatedir']=$templatename;
$thissurvey['format']="G";
$thissurvey['url']="http://www.limesurvey.org/";
$thissurvey['urldescrip']=$clang->gT("Some URL description");
$thissurvey['usecaptcha']="A";
$percentcomplete=makegraph(6, 10);
$groupname=$clang->gT("Group 1: The first lot of questions");
$groupdescription="This group description is fairly vacuous, but quite important.";
$navigator="\t\t<input class=\"submit\" type=\"submit\" value=\" next &gt;&gt; \" name=\"move\" />\n";
if ($screenname != $clang->gT("Welcome Page")) {$navigator = "\t\t<input class=\"submit\" type=\"submit\" value=\" &lt;&lt; prev\" name=\"move\" />\n".$navigator;}
$help="This is some help text";
$totalquestions="10";
$surveyformat="Format";
$completed = "<br /><span class='success'>".$clang->gT("Thank you!")."</span><br /><br />".$clang->gT("Your survey responses have been recorded.")."<br /><br />\n";  
$notanswered="5";
$privacy="";
$surveyid="1295";
$token=1234567;
$assessments="<table align='center'><tr><th>".$clang->gT("Assessment heading")."</th></tr><tr><td align='center'>".$clang->gT("Assessment details")."<br />".$clang->gT("Note that this assessment section will only show if assessment rules have been set and assessment mode is activated.")."</td></tr></table>";
$printoutput="<span class='printouttitle'><strong>".$clang->gT("Survey name (ID)")."</strong> Test survey (46962)</span><br />
<table class='printouttable' >
<tr><th>".$clang->gT("Question")."</th><th>".$clang->gT("Your answer")."</th></tr>
    <tr>
        <td>id</td>
        <td>12</td>
    </tr>
    <tr>
        <td>Date Submitted</td>

        <td>1980-01-01 00:00:00</td>
    </tr>
    <tr>
        <td>This is a sample question text. The user was asked to enter a date.</td>
        <td>2007-11-06</td>
    </tr>
    <tr>
        <td>This is another sample question text - asking for number. </td>
        <td>666</td>
    </tr>
    <tr>
        <td>This is one last sample question text - asking for some free text. </td>
        <td>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum</td>
    </tr>
</table>";
$addbr=false;
switch($screenname) {
    case $clang->gT("Survey List Page", "unescaped"):
	unset($files);

	$list[]="<li class='surveytitle'><a href='#'>Survey Number 1</a></li>\n";
	$list[]="<li class='surveytitle'><a href='#'>Survey Number 2</a></li>\n";

	$surveylist=array(
	                  "nosid"=>$clang->gT("You have not provided a survey identification number"),
	                  "contact"=>sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$siteadminname,$siteadminemail),
                      "listheading"=>$clang->gT("The following surveys are available:"),
					  "list"=>implode("\n",$list),
					  );

	$myoutput[]="";
	foreach ($SurveyList as $qs) {
		$files[]=array("name"=>$qs);
		$myoutput = array_merge($myoutput, doreplacement("$tpldir/$templatename/$qs"));
	}
    if (file_exists($publicdir."/templates/".$templatename."/template.css")) { $files[]=array("name"=>"template.css"); }

    break;

	case $clang->gT("Question Page", "unescaped"):
	unset($files);
	foreach ($Question as $qs) {
		$files[]=array("name"=>$qs);
	}
	$myoutput[]="<meta http-equiv=\"expires\" content=\"Wed, 26 Feb 1997 08:21:57 GMT\" />\n";
	$myoutput[]="<meta http-equiv=\"Last-Modified\" content=\"".gmdate('D, d M Y H:i:s'). " GMT\" />\n";
	$myoutput[]="<meta http-equiv=\"Cache-Control\" content=\"no-store, no-cache, must-revalidate\" />\n";
	$myoutput[]="<meta http-equiv=\"Cache-Control\" content=\"post-check=0, pre-check=0, false\" />\n";
	$myoutput[]="<meta http-equiv=\"Pragma\" content=\"no-cache\" />\n";
	$myoutput = array_merge($myoutput, doreplacement("$tpldir/$templatename/startpage.pstpl"));
	$myoutput = array_merge($myoutput, doreplacement("$tpldir/$templatename/survey.pstpl"));
	$myoutput = array_merge($myoutput, doreplacement("$tpldir/$templatename/startgroup.pstpl"));
	$myoutput = array_merge($myoutput, doreplacement("$tpldir/$templatename/groupdescription.pstpl"));

	$question="How many roads must a man walk down?";
	$questioncode="1a";
	$answer="<input type='radio' class='radiobtn' name='1' value='1' id='radio1' /><label class='answertext' for='radio1'>One</label><br /><input type='radio' class='radiobtn' name='1' value='2' id='radio2' /><label class='answertext' for='radio2'>Two</label><br /><input type='radio' class='radiobtn' name='1' value='3' id='radio3' /><label class='answertext' for='radio3'>Three</label><br />\n";
	$myoutput = array_merge($myoutput, doreplacement("$tpldir/$templatename/question.pstpl"));

	$question="Please explain your details:";
	$questioncode="2";
	$answer="<textarea class='textarea'>Some text in this answer</textarea>";
	$myoutput = array_merge($myoutput, doreplacement("$tpldir/$templatename/question.pstpl"));

	$myoutput = array_merge($myoutput, doreplacement("$tpldir/$templatename/endgroup.pstpl"));
	$myoutput = array_merge($myoutput, doreplacement("$tpldir/$templatename/navigator.pstpl"));
	$myoutput = array_merge($myoutput, doreplacement("$tpldir/$templatename/endpage.pstpl"));
    if (file_exists($publicdir."/templates/".$templatename."/template.css")) { $files[]=array("name"=>"template.css"); }

	break;
	case $clang->gT("Welcome Page", "unescaped"):
	unset($files);
	$myoutput[]="";
	foreach ($Welcome as $qs) {
		$files[]=array("name"=>$qs);
		$myoutput = array_merge($myoutput, doreplacement("$tpldir/$templatename/$qs"));
	}
    if (file_exists($publicdir."/templates/".$templatename."/template.css")) { $files[]=array("name"=>"template.css"); }
	break;

	case $clang->gT("Register Page", "unescaped"):
	unset($files);
	foreach($Register as $qs) {
		$files[]=array("name"=>$qs);
	}
	foreach(file("$tpldir/$templatename/startpage.pstpl") as $op)
	{
		$myoutput[]=templatereplace($op);
	}
	foreach(file("$tpldir/$templatename/survey.pstpl") as $op)
	{
		$myoutput[]=templatereplace($op);
	}
	foreach(file("$tpldir/$templatename/register.pstpl") as $op)
	{
		$myoutput[]=templatereplace($op);
	}
	foreach(file("$tpldir/$templatename/endpage.pstpl") as $op)
	{
		$myoutput[]=templatereplace($op);
	}
	$myoutput[]= "\n";
    if (file_exists($publicdir."/templates/".$templatename."/template.css")) { $files[]=array("name"=>"template.css"); }
	break;

	case $clang->gT("Save Page", "unescaped"):
	unset($files);
	foreach($Save as $qs) {
		$files[]=array("name"=>$qs);
	}
	foreach(file("$tpldir/$templatename/startpage.pstpl") as $op)
	{
		$myoutput[]=templatereplace($op);
	}
	foreach(file("$tpldir/$templatename/save.pstpl") as $op)
	{
		$myoutput[]=templatereplace($op);
	}
	foreach(file("$tpldir/$templatename/endpage.pstpl") as $op)
	{
		$myoutput[]=templatereplace($op);
	}
	$myoutput[]= "\n";
    if (file_exists($publicdir."/templates/".$templatename."/template.css")) { $files[]=array("name"=>"template.css"); }
	break;

	case $clang->gT("Load Page", "unescaped"):
	unset($files);
	foreach($Load as $qs) {
		$files[]=array("name"=>$qs);
	}
	foreach(file("$tpldir/$templatename/startpage.pstpl") as $op)
	{
		$myoutput[]=templatereplace($op);
	}
	foreach(file("$tpldir/$templatename/load.pstpl") as $op)
	{
		$myoutput[]=templatereplace($op);
	}
	foreach(file("$tpldir/$templatename/endpage.pstpl") as $op)
	{
		$myoutput[]=templatereplace($op);
	}
	$myoutput[]= "\n";
    if (file_exists($publicdir."/templates/".$templatename."/template.css")) { $files[]=array("name"=>"template.css"); }
	break;

	case $clang->gT("Clear All Page", "unescaped"):
	unset($files);
	foreach ($Clearall as $qs) {
		$files[]=array("name"=>$qs);
	}
	foreach(file("$tpldir/$templatename/startpage.pstpl") as $op)
	{
		$myoutput[]=templatereplace($op);
	}
	foreach(file("$tpldir/$templatename/clearall.pstpl") as $op)
	{
		$myoutput[]=templatereplace($op);
	}
	foreach(file("$tpldir/$templatename/endpage.pstpl") as $op)
	{
		$myoutput[]=templatereplace($op);
	}
	$myoutput[]= "\n";
    if (file_exists($publicdir."/templates/".$templatename."/template.css")) { $files[]=array("name"=>"template.css"); }
	break;

	case $clang->gT("Completed Page", "unescaped"):
	unset($files);
	$myoutput[]="";
	foreach ($CompletedTemplate as $qs) {
		$files[]=array("name"=>$qs);
		$myoutput = array_merge($myoutput, doreplacement("$tpldir/$templatename/$qs"));
	}
    if (file_exists($publicdir."/templates/".$templatename."/template.css")) { $files[]=array("name"=>"template.css"); }
	break;


    case $clang->gT("Print Answers Page", "unescaped"):
    unset($files);
    foreach ($printtemplate as $qs) {
        $files[]=array("name"=>$qs);
    }
    foreach(file("$tpldir/$templatename/startpage.pstpl") as $op)
    {
        $myoutput[]=templatereplace($op);
    }
    foreach(file("$tpldir/$templatename/printanswers.pstpl") as $op)
    {
        $myoutput[]=templatereplace($op);
    }
    foreach(file("$tpldir/$templatename/endpage.pstpl") as $op)
    {
        $myoutput[]=templatereplace($op);
    }
    $myoutput[]= "\n";
    if (file_exists($publicdir."/templates/".$templatename."/template.css")) { $files[]=array("name"=>"template.css"); }
    break;
}
$myoutput[]="</html>";

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
$dirloc=$tpldir."/".$templatename;
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
$templatesoutput= "<script type='text/javascript'>\n"
."<!--\n"
."function copyprompt(text, defvalue, copydirectory, action)\n"
."\t{\n"
."\tif (newtemplatename=window.prompt(text, defvalue))\n"
."\t\t{\n"
//."\t\tvar url='admin.php?action=template'+action+'&newname='+newtemplatename+'&copydir='+copydirectory;\n"
//."\t\twindow.open(url, '_top');\n"
. "\t\t\tsendPost('admin.php','".$_SESSION['checksessionpost']."',new Array('action','newname','copydir'),new Array('template'+action,newtemplatename,copydirectory));\n"
."\t\t}\n"
."\t}\n"
."function checkuploadfiletype(filename)\n"
."\t{\n"
."\tvar allowedtypes=',$allowedtemplateuploads,';\n"
."\tvar lastdotpos=-1;\n"
."\tvar ext='';\n"
."\tif ((lastdotpos=filename.lastIndexOf('.')) < 0)\n"
."\t\t{\n"
."\t\talert('".$clang->gT('This file type is not allowed to be uploaded.','js')."');\n"
."\t\treturn false;\n"
."\t\t}\n"
."\telse\n"
."\t\t{\n"
."\t\text = ',' + filename.substr(lastdotpos+1) + ',';\n"
."\t\text = ext.toLowerCase();\n"
."\t\tif (allowedtypes.indexOf(ext) < 0)\n"
."\t\t\t{\n"
."\t\t\talert('".$clang->gT('This file type is not allowed to be uploaded.','js')."');\n"
."\t\t\treturn false;\n"
."\t\t\t}\n"
."\t\telse\n"
."\t\t\t{\n"
."\t\t\treturn true;\n"
."\t\t\t}\n"
."\t\t}\n"
."\t}\n"
."//-->\n</script>\n";
$templatesoutput.= "<div class='menubar'>\n"
. "\t\t\t<div class='menubar-title'>\n"
. "\t\t\t\t\t<strong>".$clang->gT('Template Editor')."</strong>\n"
. "\t\t\t\t</div>\n"
. "\t\t\t<div class='menubar-main'>\n"
. "\t\t\t<div class='menubar-left'>\n"
. "\t\t\t\t\t<a href='$scriptname'" 
. "onmouseout=\"hideTooltip()\" title=\"".$clang->gTview("Default Administration Page")."\" onmouseover=\"showTooltip(event,'".$clang->gT("Default Administration Page", "js")."')\">" 
. "<img src='$imagefiles/home.png' name='HomeButton' alt='' title='' /></a>\n"
. "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='60' height='10'  />\n"
. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt=''  />"
. "</div>\n"
. "\t\t\t<div class='menubar-right'>\n"

//Logout Button

."<font style='boxcaption'><strong>".$clang->gT("Template:")."</strong> </font>"
."<select class=\"listboxtemplates\" name='templatedir' onchange='javascript: window.open(\"admin.php?action=templates&amp;editfile=$editfile&amp;screenname=".html_escape($screenname)."&amp;templatename=\"+this.value, \"_top\")'>\n"
.makeoptions($templates, "name", "name", $templatename)
."</select>\n"
. "<a href='#' onclick=\"javascript: copyprompt('".$clang->gT("Create new template called:")."', '".$clang->gT("NewTemplate")."', 'default', 'copy')\"" 
. " onmouseout=\"hideTooltip()\" title=\"".$clang->gTview("Create new template")."\" onmouseover=\"showTooltip(event,'".$clang->gT("Create new template", "js")."')\">" 
. "<img src='$imagefiles/add.png' alt=''  title='' /></a>\n"
. "<img src='$imagefiles/seperator.gif' alt='' />"
. "\t\t<a href=\"#\" onclick=\"window.open('$scriptname?action=logout', '_top')\""
. "title=\"".$clang->gTview("Logout")."\" "
. "onmouseout=\"hideTooltip()\""
. "onmouseover=\"showTooltip(event,'".$clang->gT("Logout", "js")."');return false\">"
. "<img src='$imagefiles/logout.png' name='Logout'"
. "title='' alt='".$clang->gT("Logout")."' /></a>"
. "<img src='$imagefiles/blank.gif' alt='' width='20'  />"
."</div></div></div>\n"
."<font style='size:12px;line-height:2px;'>&nbsp;&nbsp;</font>"; //CSS Firefox 2 transition fix


//TEMPLATE DETAILS
$templatesoutput.= "\t\t\t<div class='menubar'>\n"
. "\t\t\t<div class='menubar-title'>\n"
. "\t\t\t\t\t<strong>".$clang->gT("Template:")." <i>$templatename</i></strong>\n"
. "\t\t\t\t</div>\n"
. "\t\t\t<div class='menubar-main'>\n"
. "\t\t\t<div class='menubar-left'>\n";
if (is_writable("$publicdir/templates/$templatename") && ($templatename != "default") ) {
	$templatesoutput.= "\t\t\t\t\t<img src='$imagefiles/trafficgreen.png' alt='' " 
            		  ." onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'".$clang->gT("This template can be modified", "js")."')\" />\n";
} else {
	$templatesoutput.= "\t\t\t\t\t<img src='$imagefiles/trafficred.png' alt='' '" 
            		  ." onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'".$clang->gT("This template cannot be modified", "js")."')\" />\n";
}
$templatesoutput.= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='60' height='40'/>\n"
."\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt=''  />\n";

if ($templatename == "default" && $debug<2) 
{
        $templatesoutput.= "\t\t\t\t\t" .
    		 "<img name='EditName' src='$imagefiles/noedit.png' alt='' title=''" .
    		 " onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'".$clang->gT("You can't edit the default template.", "js")."')\" ".
             " />";
}
else 
    {	
        $templatesoutput.= "\t\t\t\t\t<a href='#' onclick=\"javascript: copyprompt('".$clang->gT("Rename this template to:")."', '$templatename', '$templatename', 'rename')\">" .
    		 "<img name='EditName' src='$imagefiles/edit.png' alt='' title=''" .
    		 " onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'".$clang->gT("Rename this template", "js")."')\" ".
             " /></a>";
    }
$templatesoutput.= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='20' height='10' />\n"
."\t\t\t\t\t<a href='#' onclick='javascript:window.open(\"admin.php?action=templatezip&amp;editfile=$editfile&amp;screenname=".html_escape($screenname)."&amp;templatename=$templatename\", \"_top\")'".
		"onmouseout=\"hideTooltip()\" title=\"".$clang->gTview("Export Template")."\" onmouseover=\"showTooltip(event,'".$clang->gT("Export Template", "js")."')\">" .
				"<img name='Export' src='$imagefiles/export.png' alt='' title='' /></a>\n"
."\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' />\n"
."\t\t\t\t\t" .
		"<a href='#' onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'".$clang->gT("Copy Template", "js")."')\" title=\"".$clang->gTview("Copy Template")."\" " .
		"onclick=\"javascript: copyprompt('".$clang->gT("Make a copy of this template")."', '".$clang->gT("copy_of_")."$templatename', '$templatename', 'copy')\">" .
		"<img name='MakeCopy' src='$imagefiles/copy.png' alt='' title='' /></a>"
."</div>\n"
."<div class='menubar-right'>\n"
."<font style='boxcaption'><strong>".$clang->gT("Screen:")."</strong> </font>"
. "<select class=\"listboxtemplates\" name='screenname' onchange='javascript: window.open(\"admin.php?action=templates&amp;templatename=$templatename&amp;editfile=$editfile&amp;screenname=\"+this.value, \"_top\")'>\n"
. makeoptions($screens, "name", "name", html_escape($screenname) )
. "</select>\n"
."<img src='$imagefiles/blank.gif' width='45' height='10' alt='' />"
."<img src='$imagefiles/seperator.gif' alt='' />"
."<img src='$imagefiles/blank.gif' width='62' height='10' alt=''/>"
."</div></div></div>\n"
."<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>" //CSS Firefox 2 transition fix
."<table width='100%' border='0' bgcolor='#FFFFFF'>\n"
. "\t<tr>\n"
. "\t\t<td>\n";


//FILE CONTROL DETAILS
$templatesoutput.= "\t\t\t<table class='menubar'>\n"
. "\t\t\t<tr>\n"
. "\t\t\t\t<td colspan='2' height='8'>\n"
. "\t\t\t\t\t<strong>".$clang->gT("File Control:")."</strong>\n"
. "\t\t\t\t</td>\n"
. "\t\t\t</tr>\n"
. "\t\t\t<tr>"
. "\t\t\t\t<td align='center' >\n";

$templatesoutput.= "\t\t\t\t<table width='100%' border='0'>\n"
."\t\t\t\t\t<tr>\n"
."\t\t\t\t\t\t<td align='center' valign='top' width='80%'>"
. "\t\t\t\t<table width='100%' align='center' class='menubar'><tr><td>"
."<strong>".$clang->gT("Standard Files:")."</strong></td>"
."<td align='center'><strong>".$clang->gT("Now editing:");
if (trim($editfile)!='') {$templatesoutput.= " <i>$editfile</i>";}
$templatesoutput.= "</strong></td>"
."<td align='right' ><strong>".$clang->gT("Other Files:")."</strong></td></tr>\n"
."<tr><td valign='top'><select size='12' name='editfile' onchange='javascript: window.open(\"admin.php?action=templates&amp;templatename=$templatename&amp;screenname=".html_escape($screenname)."&amp;editfile=\"+this.value, \"_top\")'>\n"
.makeoptions($files, "name", "name", $editfile)
."</select>\n"
."\t\t\t\t\t\t</td>\n"
."\t\t\t\t\t\t<td align='center' valign='top'>\n"
. "<form name='editTemplate' method='post' action='admin.php'>\n"
. "\t\t\t<input type='hidden' name='templatename' value='$templatename' />\n"
. "\t\t\t<input type='hidden' name='screenname' value='".html_escape($screenname)."' />\n"
. "\t\t\t<input type='hidden' name='editfile' value='$editfile' />\n"
. "\t\t\t<input type='hidden' name='action' value='templatesavechanges' />\n"
."<textarea name='changes' id='changes' cols='110' rows='12'>";
if ($editfile) {
	$templatesoutput.= textarea_encode(filetext($editfile));
}
$templatesoutput.= "</textarea><br />\n";
if (is_writable("$tpldir/$templatename")) {
	$templatesoutput.= "<input align='right' type='submit' value='".$clang->gT("Save Changes")."'";
	if ($templatename == "default" && $debug<2) {
		$templatesoutput.= " disabled='disabled' alt='".$clang->gT("Changes cannot be saved to the default template.")."'";
	}
	$templatesoutput.= " />";
}
$templatesoutput.= "<br />\n"
."\t\t\t\t\t\t</form></td><td valign='top' align='right' width='20%'><form action='admin.php' method='post'>"
."<table width='90' align='right' border='0' cellpadding='0' cellspacing='0'>\n<tr><td></td></tr><tr><td align='right'>"
. "<select size='12' style='min-width:130px;'name='otherfile' id='otherfile'>\n"
.makeoptions($otherfiles, "name", "name", "")
."</select>"
."</td></tr><tr><td align='right'>"
."<input type='submit' value='".$clang->gT("Delete")."' onclick=\"javascript:return confirm('".$clang->gT("Are you sure you want to delete this file?","js")."')\"";
if ($templatename == "default") {
		$templatesoutput.= " style='color: #BBBBBB;' disabled='disabled' alt='".$clang->gT("Files in the default template cannot be deleted.")."'";
}
$templatesoutput.= " />\n"
."<input type='hidden' name='screenname' value='".html_escape($screenname)."' />\n"
."<input type='hidden' name='templatename' value='$templatename' />\n"
."<input type='hidden' name='action' value='templatefiledelete' />\n"
. "</td></tr></table></form></td>\n"
."</tr>\n"
."</table></td></tr><tr><td align='right' valign='top'>"
."<form enctype='multipart/form-data' name='importsurvey' action='admin.php' method='post' onsubmit='return checkuploadfiletype(this.the_file.value);'>\n"
."<table><tr> <td align='right' valign='top' style='border: solid 1 #000080'>\n"
."<strong>".$clang->gT("Upload a File").":</strong></td></tr><tr><td><input name=\"the_file\" type=\"file\" size=\"30\" /><br />"
."<input type='submit' value='".$clang->gT("Upload")."'";
if ($templatename == "default") {
	$templatesoutput.= " disabled='disabled'";
}
$templatesoutput.= " />\n"
."<input type='hidden' name='editfile' value='$editfile' />\n"
."<input type='hidden' name='screenname' value='".html_escape($screenname)."' />\n"
."<input type='hidden' name='templatename' value='$templatename' />\n"
."<input type='hidden' name='action' value='templateupload' />\n"
."</td></tr></table></form>\n"
."\t\t\t\t\t\t</td>\n"
."\t\t\t\t\t</tr>\n"
."\t\t\t\t</table>\n"
."\t\t\t</td>\n"
."\t</tr>"
."</table>"
."</td></tr></table>";

//SAMPLE ROW
$templatesoutput.= "\t\t\t<table class='menubar'>\n"
. "\t\t\t<tr>\n"
. "\t\t\t\t<td colspan='2' height='8'>\n"
. "\t\t\t\t\t<strong>".$clang->gT("Preview:")."</strong>\n"
. "\t\t\t\t</td>\n"
. "\t\t\t</tr>\n"
."\t<tr>\n"
."\t\t<td width='90%' align='center' >\n";


unlink_wc($tempdir, "template_temp_*.html"); //Delete any older template files
$time=date("ymdHis");
$fnew=fopen("$tempdir/template_temp_$time.html", "w+");
fwrite ($fnew, getHeader());
$myoutput=str_replace('template.css',"template.css?t=$time",$myoutput);
foreach($myoutput as $line) {
	fwrite($fnew, $line);
}
fclose($fnew);
$langdir_template="$publicurl/locale/".$_SESSION['adminlang']."/help";
$templatesoutput.= "<br />\n"
."<iframe src='$tempurl/template_temp_$time.html' width='95%' height='400' name='sample' style='background-color: white'>Embedded Frame</iframe>\n"
."<br />&nbsp;<br />"
."</td></tr></table>\n";


function doreplacement($file) { //Produce sample page from template file
	$output=array();
	foreach(file($file) as $op) {
		$output[]=templatereplace($op);
	}
	return $output;
}

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
	sort($arr);
	return $arr;
}

function textarea_encode($html_code)
	{
	$from = array('<', '>');
	$to = array('&lt;', '&gt;');
	$html_code = str_replace($from, $to, $html_code);
	return $html_code;
	}

//Load this editfile
function filetext($templatefile) {
	global $tpldir, $templatename;
	$output="";
	foreach(file("$tpldir/$templatename/$templatefile") as $line) {
		$output .= $line;
	}
	return $output;
}

function makegraph($currentstep, $total)
{
	global $thissurvey, $publicurl, $templatename, $clang;
	$shchart="$publicurl/templates/{$thissurvey['templatedir']}/chart.jpg";
	$graph = "<table class='graph' width='100' align='center' cellpadding='2'><tr><td>\n"
	. "<table width='180' align='center' cellpadding='0' cellspacing='0' border='0' class='innergraph'>\n"
	. "<tr><td align='right' width='40'>0%&nbsp;</td>\n";
	$size=intval(($currentstep-1)/$total*100);
	$graph .= "<td width='100' align='left'>\n"
	. "<table cellspacing='0' cellpadding='0' border='0' width='100%'>\n"
	. "<tr><td>\n"
    . "<img src='$shchart' width='$size' align='left' alt='".sprintf($clang->gT("%s %% complete"), $size)."' />\n"
	. "</td></tr>\n"
	. "</table>\n"
	. "</td>\n"
	. "<td align='left' width='40'>&nbsp;100%</td></tr>\n"
	. "</table>\n"
	. "</td></tr>\n</table>\n";
	return $graph;
}

function mkdir_p($target){
	//creates a new directory
	//Returns 1 for success
	//        2 for "directory/file by that name exists
	//        0 for other errors
	if(file_exists($target) || is_dir($target))
	return 2;
	if(mkdir($target,0777)){
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
		$return .= "<option value='".html_escape($ar[$value])."'";
		if (html_escape($ar[$value]) == $selectedvalue) {
			$return .= " selected='selected'";
		}
		$return .= ">".$ar[$text]."</option>\n";
	}
	return $return;
}

function multiarray_search($arrayVet, $campo, $valor){
    while(isset($arrayVet[key($arrayVet)])){
        if($arrayVet[key($arrayVet)][$campo] == $valor){
            return key($arrayVet);
        }
        next($arrayVet);
    }
    return false;
}


?>
