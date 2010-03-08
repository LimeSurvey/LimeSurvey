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

//  Standard templates
$standardtemplates=array('basic',
                         'bluengrey',
                         'business_grey',
                         'clear_logo',
                         'default',
                         'eirenicon',
                         'limespired',
                         'mint_idea',
                         'sherpa',
                         'vallendar');

//Standard Template Subfiles
//Only these files may be edited or saved
$files[]=array('name'=>'assessment.pstpl');
$files[]=array('name'=>'clearall.pstpl');
$files[]=array('name'=>'completed.pstpl');
$files[]=array('name'=>'endgroup.pstpl');
$files[]=array('name'=>'endpage.pstpl');
$files[]=array('name'=>'groupdescription.pstpl');
$files[]=array('name'=>'load.pstpl');
$files[]=array('name'=>'navigator.pstpl');
$files[]=array('name'=>'printanswers.pstpl');
$files[]=array('name'=>'privacy.pstpl');
$files[]=array('name'=>'question.pstpl');
$files[]=array('name'=>'register.pstpl');
$files[]=array('name'=>'save.pstpl');
$files[]=array('name'=>'surveylist.pstpl');
$files[]=array('name'=>'startgroup.pstpl');
$files[]=array('name'=>'startpage.pstpl');
$files[]=array('name'=>'survey.pstpl');
$files[]=array('name'=>'welcome.pstpl');
$files[]=array('name'=>'print_survey.pstpl');
$files[]=array('name'=>'print_group.pstpl');
$files[]=array('name'=>'print_question.pstpl');

//Standard CSS Files
//These files may be edited or saved
$cssfiles[]=array('name'=>'template.css');
$cssfiles[]=array('name'=>'template-rtl.css');
$cssfiles[]=array('name'=>'ie_fix_6.css');
$cssfiles[]=array('name'=>'ie_fix_7.css');
$cssfiles[]=array('name'=>'ie_fix_8.css');
$cssfiles[]=array('name'=>'print_template.css');
$cssfiles[]=array('name'=>'template.js');

//Standard Support Files
//These files may be edited or saved
$supportfiles[]=array('name'=>'print_img_radio.png');
$supportfiles[]=array('name'=>'print_img_checkbox.png');

//Standard screens
//Only these may be viewed

$screens[]=array('name'=>$clang->gT('Survey List Page'),'id'=>'surveylist');
$screens[]=array('name'=>$clang->gT('Welcome Page'),'id'=>'welcome');    
$screens[]=array('name'=>$clang->gT('Question Page'),'id'=>'question');    
$screens[]=array('name'=>$clang->gT('Completed Page'),'id'=>'completed');    
$screens[]=array('name'=>$clang->gT('Clear All Page'),'id'=>'clearall');    
$screens[]=array('name'=>$clang->gT('Register Page'),'id'=>'register');    
$screens[]=array('name'=>$clang->gT('Load Page'),'id'=>'load');    
$screens[]=array('name'=>$clang->gT('Save Page'),'id'=>'save');    
$screens[]=array('name'=>$clang->gT('Print answers page'),'id'=>'printanswers');    
$screens[]=array('name'=>$clang->gT('Printable survey page'),'id'=>'printablesurvey');    

//Page display blocks
$SurveyList=array('startpage.pstpl', 
                  'surveylist.pstpl', 
                  'endpage.pstpl'
                  );
$Welcome=array('startpage.pstpl', 
               'welcome.pstpl', 
               'privacy.pstpl', 
               'navigator.pstpl', 
               'endpage.pstpl'
               );
$Question=array('startpage.pstpl', 
                'survey.pstpl', 
                'startgroup.pstpl', 
                'groupdescription.pstpl',  
                'question.pstpl', 
                'endgroup.pstpl', 
                'navigator.pstpl', 
                'endpage.pstpl'
                );
$CompletedTemplate=array(
                'startpage.pstpl', 
                'assessment.pstpl', 
                'completed.pstpl', 
                'endpage.pstpl'
                );
$Clearall=array('startpage.pstpl', 
                'clearall.pstpl', 
                'endpage.pstpl'
                );
$Register=array('startpage.pstpl', 
                'survey.pstpl', 
                'register.pstpl', 
                'endpage.pstpl'
                );
$Save=array('startpage.pstpl', 
            'save.pstpl', 
            'endpage.pstpl'
            );
$Load=array('startpage.pstpl', 
            'load.pstpl', 
            'endpage.pstpl'
            );
$printtemplate=array('startpage.pstpl', 
                     'printanswers.pstpl', 
                     'endpage.pstpl'
                     );
$printablesurveytemplate=array('print_survey.pstpl', 
                               'print_group.pstpl', 
                               'print_question.pstpl'
                               );


// Set this so common.php doesn't throw notices about undefined variables
$thissurvey['active']='N';


$file_version="LimeSurvey template editor ".$versionnumber;
$_SESSION['s_lang']=$_SESSION['adminlang'];

if (!isset($templatename)) {$templatename = sanitize_paranoid_string(returnglobal('templatename'));}
if (!isset($templatedir)) {$templatedir = sanitize_paranoid_string(returnglobal('templatedir'));}
if (!isset($editfile)) {$editfile = sanitize_paranoid_string(returnglobal('editfile'));}
if (!isset($screenname)) {$screenname=auto_unescape(returnglobal('screenname'));}
 
// Checks if screen name is in the list of allowed screen names  
if ( isset($screenname) && (multiarray_search($screens,'id',$screenname)===false)) {die('Invalid screen name');}  // Die you sneaky bastard!


if (!isset($action)) {$action=sanitize_paranoid_string(returnglobal('action'));}
if (!isset($subaction)) {$subaction=sanitize_paranoid_string(returnglobal('subaction'));}
if (!isset($otherfile)) {$otherfile = sanitize_filename(returnglobal('otherfile'));}
if (!isset($newname)) {$newname = sanitize_paranoid_string(returnglobal('newname'));}
if (!isset($copydir)) {$copydir = sanitize_paranoid_string(returnglobal('copydir'));}

if(is_file($templaterootdir.'/'.$templatename.'/question_start.pstpl')) 
{
  $files[]=array('name'=>'question_start.pstpl');
  $Question[]='question_start.pstpl';
}  


$js_adminheader_includes[]= $homeurl."/scripts/edit_area/edit_area_loader.js";
$js_adminheader_includes[]= $homeurl."/scripts/templates.js";

// find out language for code editor 
$availableeditorlanguages=array('bg','cs','de','dk','en','eo','es','fi','fr','hr','it','ja','mk','nl','pl','pt','ru','sk','zh');
$extension = substr(strrchr($editfile, "."), 1);        
if ($extension=='css' || $extension=='js') {$highlighter=$extension;} else {$highlighter='html';};
if(in_array($_SESSION['adminlang'],$availableeditorlanguages)) {$codelanguage=$_SESSION['adminlang'];}
    else  {$codelanguage='en';}     

if (isset ($_POST['changes'])) {
	$changedtext=$_POST['changes'];
    $changedtext=str_replace ('<?','',$changedtext);
	if(get_magic_quotes_gpc())
	{
	   $changedtext = stripslashes($changedtext);
	}
}

if (isset ($_POST['changes_cp'])) {
    $changedtext=$_POST['changes_cp'];
    $changedtext=str_replace ('<?','',$changedtext);
    if(get_magic_quotes_gpc())
    {
       $changedtext = stripslashes($changedtext);
    }
}



$template_a=gettemplatelist();
foreach ($template_a as $tp) {
	$templates[]=array("name"=>$tp, "dir"=>$templaterootdir."/".$tp);
}
unset($template_a);

// check if a template like this exists
if (recursive_in_array($templatename,$templates)===false)
{
   $templatename = $defaulttemplate;
}

if ($subaction == "delete" && is_template_editable($templatename)==true) 
{
   if (rmdirr($templaterootdir."/".$templatename)==true)
   {
       $templatequery = "UPDATE {$dbprefix}surveys set template='$defaulttemplate' where template='$templatename'\n";    
       $connect->Execute($templatequery) or safe_die ("Couldn't update surveys with default template!<br />\n$utquery<br />\n".$connect->ErrorMsg());     //Checked 

       $templatequery = "UPDATE {$dbprefix}surveys set template='$defaulttemplate' where template='$templatename'\n";    
       $connect->Execute($templatequery) or safe_die ("Couldn't update surveys with default template!<br />\n$utquery<br />\n".$connect->ErrorMsg());     //Checked 

       $templatequery = "delete from {$dbprefix}templates_rights where folder='$templatename'\n";    
       $connect->Execute($templatequery) or safe_die ("Couldn't update template_rights<br />\n$utquery<br />\n".$connect->ErrorMsg());     //Checked 

       $templatequery = "delete from {$dbprefix}templates where folder='$templatename'\n";    
       $connect->Execute($templatequery) or safe_die ("Couldn't update templates<br />\n$utquery<br />\n".$connect->ErrorMsg());     //Checked 
       
       $flashmessage=sprintf($clang->gT("Template '%s' was successfully deleted."),$templatename);
       $templatename = $defaulttemplate;
   }
   else
   {
       $flashmessage=sprintf($clang->gT("There was a problem deleting the template '%s'. Please check your directory/file permissions."),$templatename);
   }
}

if ($action == "templateupload")
{
    include("import_resources_zip.php");
}


//Save Changes if necessary
if ($action=="templatesavechanges" && $changedtext) {
	$changedtext=str_replace("\r\n", "\n", $changedtext);
	if ($editfile) {
        // Check if someone tries to submit a file other than one of the allowed filenames
        if (multiarray_search($files,'name',$editfile)===false && multiarray_search($cssfiles,'name',$editfile)===false) {die('Invalid template filename');}  // Die you sneaky bastard!
		$savefilename=$templaterootdir."/".$templatename."/".$editfile;
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
	$newdirname=$templaterootdir."/".$newname;
	$copydirname=$templaterootdir."/".$copydir;
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
	$newdirname=$templaterootdir."/".$newname;
	$olddirname=$templaterootdir."/".$copydir;
	if (rename($olddirname, $newdirname)==false) {
        echo "<script type=\"text/javascript\">\n<!--\nalert(\"".sprintf($clang->gT("Directory could not be renamed to `%s`.","js"), $newname)." ".$clang->gT("Maybe you don't have permission.","js")."\");\n//-->\n</script>";
	} else {
		$templates[]=array("name"=>$newname, "dir"=>$newdirname);
		$templatename=$newname;
	}
}

if ($action == "templateuploadfile") 
  {

      if ($demoModeOnly == true)
      {
			$action = '';
			
      } else
      {
	  $the_full_file_path = $templaterootdir."/".$templatename . "/" . sanitize_filename($_FILES['the_file']['name']); 
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
	$the_full_file_path = $templaterootdir."/".$templatename."/".$otherfile; //This is where the temp file is
	unlink($the_full_file_path);
}

if ($action == "templatezip") {
	require("classes/phpzip/phpzip.inc.php");
	$z = new PHPZip();
	$templatedir="$templaterootdir/$templatename/";
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
foreach ($cssfiles as $fl) {
    $normalfiles[]=$fl["name"];
}

//CHECK ALL FILES EXIST, AND IF NOT - COPY IT FROM DEFAULT DIRECTORY
foreach ($files as $file) {
	$thisfile="$templaterootdir/$templatename/".$file['name'];
	if (!is_file($thisfile)) {
		$copyfile="$templaterootdir/default/".$file['name'];
		$newfile=$thisfile;
		if (!@copy($copyfile, $newfile)) {
            echo "<script type=\"text/javascript\">\n<!--\nalert(\"".sprintf($clang->gT("Failed to copy %s to new template directory.","js"), $file['name'])."\");\n//-->\n</script>";
		}
	}
}
//CHECK if ALL CSS & JS FILES EXIST, AND IF NOT - COPY IT FROM DEFAULT DIRECTORY
foreach ($cssfiles as $file) {
	$thisfile="$templaterootdir/$templatename/".$file['name'];
	if (!is_file($thisfile)) {
		$copyfile="$templaterootdir/default/".$file['name'];
		$newfile=$thisfile;
		if (!@copy($copyfile, $newfile)) {
            echo "<script type=\"text/javascript\">\n<!--\nalert(\"".sprintf($clang->gT("Failed to copy %s to new template directory.","js"), $file['name'])."\");\n//-->\n</script>";
		}
	}
}
//CHECK IF REQUIRED SUPPORT FILE EXIST, AND IF NOT - COPY IT FROM DEFAULT DIRECTORY
foreach($supportfiles as $file) {
	$thisfile="$templaterootdir/$templatename/".$file['name'];
	if (!is_file($thisfile)) {
		$copyfile="$templaterootdir/default/".$file['name'];
		$newfile=$thisfile;
		if (!@copy($copyfile, $newfile)) {
            echo "<script type=\"text/javascript\">\n<!--\nalert(\"".sprintf($clang->gT("Failed to copy %s to new template directory.","js"), $file['name'])."\");\n//-->\n</script>";
		}
	}
}

if (!$screenname) {$screenname='welcome';}
if ($screenname != 'welcome') {$_SESSION['step']=1;} else {unset($_SESSION['step']);} //This helps handle the load/save buttons


// ===========================   FAKE DATA FOR TEMPLATES
$thissurvey['name']=$clang->gT("Template Sample");
$thissurvey['description']=$clang->gT('This is a sample survey description. It could be quite long.').'<br /><br />'.$clang->gT("But this one isn't.");
$thissurvey['welcome']=$clang->gT('Welcome to this sample survey').'<br />'.$clang->gT('You should have a great time doing this').'<br />';
$thissurvey['allowsave']="Y";
$thissurvey['active']="Y";
$thissurvey['templatedir']=$templatename;
$thissurvey['format']="G";
$thissurvey['surveyls_url']="http://www.limesurvey.org/";
$thissurvey['surveyls_urldescription']=$clang->gT("Some URL description");
$thissurvey['usecaptcha']="A";
$percentcomplete=makegraph(6, 10);
$groupname=$clang->gT("Group 1: The first lot of questions");
$groupdescription=$clang->gT("This group description is fairly vacuous, but quite important.");
$navigator="<input class=\"submit\" type=\"submit\" value=\"".$clang->gT('Next')."&gt;&gt;\" name=\"move\" />\n";
if ($screenname != 'welcome') {$navigator = "<input class=\"submit\" type=\"submit\" value=\"&lt;&lt;".$clang->gT('Previous')."\" name=\"move\" />\n".$navigator;}
$help=$clang->gT("This is some help text.");
$totalquestions="10";
$surveyformat="Format";
$completed = "<br /><span class='success'>".$clang->gT("Thank you!")."</span><br /><br />"
            .$clang->gT("Your survey responses have been recorded.")."<br /><br />\n";  
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
    case 'surveylist':
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
		    $myoutput = array_merge($myoutput, doreplacement("$templaterootdir/$templatename/$qs"));
	    }
        break;

	case 'question':
	    unset($files);
	    foreach ($Question as $qs) {
		    $files[]=array("name"=>$qs);
	    }
	    $myoutput[]="<meta http-equiv=\"expires\" content=\"Wed, 26 Feb 1997 08:21:57 GMT\" />\n";
	    $myoutput[]="<meta http-equiv=\"Last-Modified\" content=\"".gmdate('D, d M Y H:i:s'). " GMT\" />\n";
	    $myoutput[]="<meta http-equiv=\"Cache-Control\" content=\"no-store, no-cache, must-revalidate\" />\n";
	    $myoutput[]="<meta http-equiv=\"Cache-Control\" content=\"post-check=0, pre-check=0, false\" />\n";
	    $myoutput[]="<meta http-equiv=\"Pragma\" content=\"no-cache\" />\n";
	    $myoutput = array_merge($myoutput, doreplacement("$templaterootdir/$templatename/startpage.pstpl"));
	    $myoutput = array_merge($myoutput, doreplacement("$templaterootdir/$templatename/survey.pstpl"));
	    $myoutput = array_merge($myoutput, doreplacement("$templaterootdir/$templatename/startgroup.pstpl"));
	    $myoutput = array_merge($myoutput, doreplacement("$templaterootdir/$templatename/groupdescription.pstpl"));

	    $question = array(
		     'all' => 'How many roads must a man walk down?'
		    ,'text' => 'How many roads must a man walk down?'
		    ,'code' => '1a'
		    ,'help' => 'helpful text'
		    ,'mandatory' => ''
		    ,'man_message' => ''
		    ,'valid_message' => ''
		    ,'essentials' => 'id="question1"'
		    ,'class' => 'list-radio'
		    ,'man_class' => ''
		    ,'input_error_class' => ''
	    );

    //	$questioncode="1a";
	    $answer="<ul><li><input type='radio' class='radiobtn' name='1' value='1' id='radio1' /><label class='answertext' for='radio1'>One</label></li><li><input type='radio' class='radiobtn' name='1' value='2' id='radio2' /><label class='answertext' for='radio2'>Two</label></li><li><input type='radio' class='radiobtn' name='1' value='3' id='radio3' /><label class='answertext' for='radio3'>Three</label></li></ul>\n";
        $myoutput = array_merge($myoutput, doreplacement("$templaterootdir/$templatename/question.pstpl"));

    //	$question='<span class="asterisk">*</span>'.$clang->gT("Please explain something in detail:");
    //	$questioncode="2";
	    $answer="<textarea class='textarea' rows='5' cols='40'>Some text in this answer</textarea>";
	    $question = array(
		     'all' => '<span class="asterisk">*</span>'.$clang->gT("Please explain something in detail:")
		    ,'text' => $clang->gT('Please explain something in detail:')
		    ,'code' => '2a'
		    ,'help' => ''
		    ,'mandatory' => $clang->gT('*')
		    ,'man_message' => ''
		    ,'valid_message' => ''
		    ,'essentials' => 'id="question2"'
		    ,'class' => 'text-long'
		    ,'man_class' => 'mandatory'
		    ,'input_error_class' => ''
	    );

	    $myoutput = array_merge($myoutput, doreplacement("$templaterootdir/$templatename/question.pstpl"));

	    $myoutput = array_merge($myoutput, doreplacement("$templaterootdir/$templatename/endgroup.pstpl"));
	    $myoutput = array_merge($myoutput, doreplacement("$templaterootdir/$templatename/navigator.pstpl"));
	    $myoutput = array_merge($myoutput, doreplacement("$templaterootdir/$templatename/endpage.pstpl"));

	    break;

	case 'welcome':
	    unset($files);
	    $myoutput[]="";
	    foreach ($Welcome as $qs) {
		    $files[]=array("name"=>$qs);
		    $myoutput = array_merge($myoutput, doreplacement("$templaterootdir/$templatename/$qs"));
	    }
	break;

	case 'register':
	    unset($files);
	    foreach($Register as $qs) {
		    $files[]=array("name"=>$qs);
	    }
	    foreach(file("$templaterootdir/$templatename/startpage.pstpl") as $op)
	    {
		    $myoutput[]=templatereplace($op);
	    }
	    foreach(file("$templaterootdir/$templatename/survey.pstpl") as $op)
	    {
		    $myoutput[]=templatereplace($op);
	    }
	    foreach(file("$templaterootdir/$templatename/register.pstpl") as $op)
	    {
		    $myoutput[]=templatereplace($op);
	    }
	    foreach(file("$templaterootdir/$templatename/endpage.pstpl") as $op)
	    {
		    $myoutput[]=templatereplace($op);
	    }
	    $myoutput[]= "\n";
	    break; 

	case 'save':
	    unset($files);
	    foreach($Save as $qs) {
		    $files[]=array("name"=>$qs);
	    }
	    foreach(file("$templaterootdir/$templatename/startpage.pstpl") as $op)
	    {
		    $myoutput[]=templatereplace($op);
	    }
	    foreach(file("$templaterootdir/$templatename/save.pstpl") as $op)
	    {
		    $myoutput[]=templatereplace($op);
	    }
	    foreach(file("$templaterootdir/$templatename/endpage.pstpl") as $op)
	    {
		    $myoutput[]=templatereplace($op);
	    }
	    $myoutput[]= "\n";
	    break;

	case 'load':
	    unset($files);
	    foreach($Load as $qs) {
		    $files[]=array("name"=>$qs);
	    }
	    foreach(file("$templaterootdir/$templatename/startpage.pstpl") as $op)
	    {
		    $myoutput[]=templatereplace($op);
	    }
	    foreach(file("$templaterootdir/$templatename/load.pstpl") as $op)
	    {
		    $myoutput[]=templatereplace($op);
	    }
	    foreach(file("$templaterootdir/$templatename/endpage.pstpl") as $op)
	    {
		    $myoutput[]=templatereplace($op);
	    }
	    $myoutput[]= "\n";
	break;

	case 'clearall':
	    unset($files);
	    foreach ($Clearall as $qs) {
		    $files[]=array("name"=>$qs);
	    }
	    foreach(file("$templaterootdir/$templatename/startpage.pstpl") as $op)
	    {
		    $myoutput[]=templatereplace($op);
	    }
	    foreach(file("$templaterootdir/$templatename/clearall.pstpl") as $op)
	    {
		    $myoutput[]=templatereplace($op);
	    }
	    foreach(file("$templaterootdir/$templatename/endpage.pstpl") as $op)
	    {
		    $myoutput[]=templatereplace($op);
	    }
	    $myoutput[]= "\n";
	    break;

	case 'completed':
	    unset($files);
	    $myoutput[]="";
	    foreach ($CompletedTemplate as $qs) {
		    $files[]=array("name"=>$qs);
		    $myoutput = array_merge($myoutput, doreplacement("$templaterootdir/$templatename/$qs"));
	    }
	    break;

    case 'printablesurvey':
        unset($files);
        foreach ($printablesurveytemplate as $qs) {
            $files[]=array("name"=>$qs);
        }
        $questionoutput=array();
        foreach(file("$templaterootdir/$templatename/print_question.pstpl") as $op)
        { // echo '<pre>line '.__LINE__.'$op = '.htmlspecialchars(print_r($op)).'</pre>';
            $questionoutput[]=templatereplace($op, array(
                                                         'QUESTION_NUMBER'=>'1',
                                                         'QUESTION_CODE'=>'Q1',
                                                         'QUESTION_MANDATORY' => $clang->gT('*'),
                                                         'QUESTION_SCENARIO' => 'Only answer this if certain conditions are met.',    // if there are conditions on a question, list the conditions.
                                                         'QUESTION_CLASS' => ' mandatory list-radio',
                                                         'QUESTION_TYPE_HELP' => $clang->gT('Please choose *only one* of the following:'),
                                                         'QUESTION_MAN_MESSAGE' => '',        // (not sure if this is used) mandatory error
                                                         'QUESTION_VALID_MESSAGE' => '',        // (not sure if this is used) validation error
                                                         'QUESTION_TEXT'=>'This is a sample question text. The user was asked to pick an entry.',
                                                         'QUESTIONHELP'=>'This is some help text for this question.',
                                                         'ANSWER'=>'<ul>
                                                                                <li>
                                                                                <img src="'.$templaterooturl.'/'.$templatename.'/print_img_radio.png" alt="First choice" class="input-radio" height="14" width="14">
                                                                                    First choice
                                                                            </li>

                                                                                <li>
                                                                                <img src="'.$templaterooturl.'/'.$templatename.'/print_img_radio.png" alt="Second choice" class="input-radio" height="14" width="14">
                                                                                    Second choice
                                                                            </li>
                                                                                <li>
                                                                                <img src="'.$templaterooturl.'/'.$templatename.'/print_img_radio.png" alt="Third choice" class="input-radio" height="14" width="14">
                                                                                    Third choice
                                                                            </li>
                                                                        </ul>'
            ));
        }    
        $groupoutput=array();
        foreach(file("$templaterootdir/$templatename/print_group.pstpl") as $op)
        {
            $groupoutput[]=templatereplace($op, array('QUESTIONS'=>implode(' ',$questionoutput)));
        }    
        foreach(file("$templaterootdir/$templatename/print_survey.pstpl") as $op)
        {
            $myoutput[]=templatereplace($op, array('GROUPS'=>implode(' ',$groupoutput),
                                                   'FAX_TO' => $clang->gT("Please fax your completed survey to:")." 000-000-000",
                                                   'SUBMIT_TEXT'=> $clang->gT("Submit your survey."),
                                                   'HEADELEMENTS'=>getPrintableHeader(),
                                                   'SUBMIT_BY' => sprintf($clang->gT("Please submit by %s"), date('d.m.y')),
                                                   'THANKS'=>$clang->gT('Thank you for completing this survey.'),
                                                   'END'=>$clang->gT('This is the survey end message.')
            ));
        }    
        break;   
    
    case 'printanswers':
        unset($files);
        foreach ($printtemplate as $qs) {
            $files[]=array("name"=>$qs);
        }
        foreach(file("$templaterootdir/$templatename/startpage.pstpl") as $op)
        {
            $myoutput[]=templatereplace($op);
        }
        foreach(file("$templaterootdir/$templatename/printanswers.pstpl") as $op)
        {
            $myoutput[]=templatereplace($op);
        }
        foreach(file("$templaterootdir/$templatename/endpage.pstpl") as $op)
        {
            $myoutput[]=templatereplace($op);
        }
        $myoutput[]= "\n";
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
    foreach ($cssfiles as $f) {
        if ($editfile == $f["name"]) {
            $match=1;
        }
    }	
    if ($match == 0) {
		if (count($files) > 0) {
			$editfile=$files[0]["name"];
		} else {
			$editfile="";
		}
	}
}
//Get list of 'otherfiles'
$otherfiles=array();
$dirloc=$templaterootdir."/".$templatename;
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
$templatesoutput = "<script type=\"text/javascript\"> var adminlanguage='$codelanguage'; var highlighter='$highlighter'; </script>\n";
$templatesoutput.= "<script type='text/javascript'>\n"
."<!--\n"
."function copyprompt(text, defvalue, copydirectory, action)\n"
."\t{\n"
."\tif (newtemplatename=window.prompt(text, defvalue))\n"
."{\n"
//."var url='admin.php?action=template'+action+'&newname='+newtemplatename+'&copydir='+copydirectory;\n"
//."window.open(url, '_top');\n"
. "\tsendPost('admin.php','".$_SESSION['checksessionpost']."',new Array('action','newname','copydir'),new Array('template'+action,newtemplatename,copydirectory));\n"
."}\n"
."\t}\n"
."function checkuploadfiletype(filename)\n"
."\t{\n"
."\tvar allowedtypes=',$allowedtemplateuploads,';\n"
."\tvar lastdotpos=-1;\n"
."\tvar ext='';\n"
."\tif ((lastdotpos=filename.lastIndexOf('.')) < 0)\n"
."{\n"
."alert('".$clang->gT('This file type is not allowed to be uploaded.','js')."');\n"
."return false;\n"
."}\n"
."\telse\n"
."{\n"
."ext = ',' + filename.substr(lastdotpos+1) + ',';\n"
."ext = ext.toLowerCase();\n"
."if (allowedtypes.indexOf(ext) < 0)\n"
."\t{\n"
."\talert('".$clang->gT('This file type is not allowed to be uploaded.','js')."');\n"
."\treturn false;\n"
."\t}\n"
."else\n"
."\t{\n"
."\treturn true;\n"
."\t}\n"
."}\n"
."\t}\n"
."//-->\n</script>\n";
$templatesoutput.= "<div class='menubar'>\n"
. "\t<div class='menubar-title'>\n"
. "\t<strong>".$clang->gT('Template Editor')."</strong>\n"
. "</div>\n"
. "\t<div class='menubar-main'>\n"
. "\t<div class='menubar-left'>\n"
. "\t<a href='$scriptname'" 
. " title=\"".$clang->gTview("Return to survey administration")."\">" 
. "<img src='$imagefiles/home.png' name='HomeButton' alt='".$clang->gT("Return to survey administration")."' /></a>\n"
. "\t<img src='$imagefiles/blank.gif' alt='' width='60' height='10'  />\n"
. "\t<img src='$imagefiles/seperator.gif' alt=''  />";

if (isset($flashmessage))
{
  $templatesoutput.='<span class="flashmessage">'.$flashmessage.'</span>'; 
}
elseif (is_template_editable($templatename)==false)
{
  $templatesoutput.='<span class="flashmessage">'.sprintf($clang->gT('Note: This is a standard template. If you want to edit it %s please copy it first%s.'),"<a href='#' title=\"".$clang->gT("Copy Template")."\" " 
    ."onclick=\"javascript: copyprompt('".$clang->gT("Please enter the name for the copied template:")."', '".$clang->gT("copy_of_")."$templatename', '$templatename', 'copy')\">",'</a>').'</span>'; 
}

$templatesoutput.= "</div>\n"
. "\t<div class='menubar-right'>\n"

//Logout Button

."<font style='boxcaption'><strong>".$clang->gT("Template:")."</strong> </font>"
."<select class=\"listboxtemplates\" name='templatedir' onchange='javascript: window.open(\"admin.php?action=templates&amp;editfile=$editfile&amp;screenname=".urlencode($screenname)."&amp;templatename=\"+escape(this.value), \"_top\")'>\n"
.makeoptions($templates, "name", "name", $templatename)
."</select>\n"
. "<a href='#' onclick=\"javascript: copyprompt('".$clang->gT("Create new template called:")."', '".$clang->gT("NewTemplate")."', 'default', 'copy')\"" 
. " title=\"".$clang->gTview("Create new template")."\" >" 
. "<img src='$imagefiles/add.png' alt='".$clang->gT("Create new template")."' /></a>\n"
. "<img src='$imagefiles/seperator.gif' alt='' />"
. "<a href=\"#\" onclick=\"window.open('$scriptname?action=logout', '_top')\""
. " title=\"".$clang->gTview("Logout")."\" >"
. "<img src='$imagefiles/logout.png' name='Logout'"
. " alt='".$clang->gT("Logout")."' /></a>"
. "<img src='$imagefiles/blank.gif' alt='' width='20'  />"
."</div></div></div>\n"
."<font style='size:12px;line-height:2px;'>&nbsp;&nbsp;</font>"; //CSS Firefox 2 transition fix


//TEMPLATE DETAILS
$templatesoutput.= "\t<div class='menubar'>\n"
. "<div class='menubar-title'>\n"
. "<strong>".$clang->gT("Template:")." <i>$templatename</i></strong>\n"
. "</div>\n"
. "<div class='menubar-main'>\n"
. "<div class='menubar-left'>\n";
$templatesoutput.= "<img src='$imagefiles/blank.gif' alt='' width='104' height='40'/>\n"
."\t<img src='$imagefiles/seperator.gif' alt=''  />\n";

if (!is_template_editable($templatename)) 
{
    $templatesoutput.="<img name='RenameTemplate' src='$imagefiles/edit_disabled.png' alt='".$clang->gT("You can't rename a standard template.")."' title='".$clang->gTview("You can't rename a standard template.")."'" 
         ." />"
         ."<img name='EditName' src='$imagefiles/delete_disabled.png' alt='".$clang->gT("You can't delete a standard template.")."' title='".$clang->gTview("You can't delete a standard template.")."'" 
         ." />";
}
else 
    {	
        $templatesoutput.= "<a href='#' title='".$clang->gTview("Rename this template")."' onclick=\"javascript: copyprompt('".$clang->gT("Rename this template to:")."', '$templatename', '$templatename', 'rename')\">" .
    		 "<img name='RenameTemplate' src='$imagefiles/edit.png' alt='".$clang->gT("Rename this template")."'" .
             " /></a>";
        $templatesoutput.= "<a href='#' title='".$clang->gTview("Delete this template")."'"
             ." onclick='if (confirm(\"".$clang->gT("Are you sure you want to delete this template?", "js")."\")) window.open(\"admin.php?action=templates&amp;subaction=delete&amp;templatename=$templatename\", \"_top\")' >" .
             "<img name='DeleteTemplate' src='$imagefiles/delete.png' alt='".$clang->gT("Delete this template")."' " .
             " /></a>";
    }
$templatesoutput.= "\t<img src='$imagefiles/blank.gif' alt='' width='20' height='10' />\n"
    ."\t<a href='#' onclick='javascript:window.open(\"admin.php?action=templatezip&amp;editfile=$editfile&amp;screenname=".urlencode($screenname)."&amp;templatename=$templatename\", \"_top\")'"
    ." title=\"".$clang->gTview("Export Template")."\" >" 
    ."<img name='Export' src='$imagefiles/export.png' alt='".$clang->gT("Export Template")."' /></a>\n"
    ."<a href='#' onclick='javascript:window.open(\"admin.php?action=templates&amp;subaction=templateupload\", \"_top\")'"
    ." title=\"".$clang->gTview("Import template")."\" >" 
    ."<img name='ImportTemplate' src='$imagefiles/import.png' alt='".$clang->gT("Import template")."' title='' /></a>\n"
."\t<img src='$imagefiles/seperator.gif' alt='' border='0' />\n"
    ."<a href='#' title=\"".$clang->gTview("Copy Template")."\" " 
    ."onclick=\"javascript: copyprompt('".$clang->gT("Please enter the name for the copied template:")."', '".$clang->gT("copy_of_")."$templatename', '$templatename', 'copy')\">" 
    ."<img name='MakeCopy' src='$imagefiles/copy.png' alt='".$clang->gT("Copy Template")."' /></a>"
."</div>\n"
."<div class='menubar-right'>\n"
."<font style='boxcaption'><strong>".$clang->gT("Screen:")."</strong> </font>"
. "<select class=\"listboxtemplates\" name='screenname' onchange='javascript: window.open(\"admin.php?action=templates&amp;templatename=$templatename&amp;editfile=$editfile&amp;screenname=\"+escape(this.value), \"_top\")'>\n"
. makeoptions($screens, "id", "name", html_escape($screenname) )
. "</select>\n"
."<img src='$imagefiles/blank.gif' width='45' height='10' alt='' />"
."<img src='$imagefiles/seperator.gif' alt='' />"
."<img src='$imagefiles/blank.gif' width='62' height='10' alt=''/>"
."</div></div></div>\n"
."<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>"; //CSS Firefox 2 transition fix



if ($subaction=='templateupload')
{    
    $ZIPimportAction = " onclick='if (validatefilename(this.form,\"".$clang->gT('Please select a file to import!','js')."\")) {this.form.submit();}'";
    if (!function_exists("zip_open"))
    {
        $ZIPimportAction = " onclick='alert(\"".$clang->gT("zip library not supported by PHP, Import ZIP Disabled","js")."\");'";
    }    
    $templatesoutput.= "<div class='header'>".$clang->gT("Uploaded template file") ."</div>\n";

    $templatesoutput.= "\t<form enctype='multipart/form-data' id='importtemplate' name='importtemplate' action='$scriptname' method='post' onsubmit='return validatefilename(this,\"".$clang->gT('Please select a file to import!','js')."\");'>\n"
        . "\t<input type='hidden' name='lid' value='$lid' />\n"
        . "\t<input type='hidden' name='action' value='templateupload' />\n"
        . "\t<ul>\n"
        . "<li><label for='the_file'>".$clang->gT("Select template ZIP file:")."</label>\n"
        . "<input id='the_file' name='the_file' type=\"file\" size=\"50\" /></li>\n"
        . "<li><label>&nbsp;</label><input type='button' value='".$clang->gT("Import template ZIP archive")."' $ZIPimportAction /></li>\n"
        . "\t</ul></form>\n"; 
}
elseif (isset($importtemplateoutput))
{
    $templatesoutput.=$importtemplateoutput;
}
else
{


//FILE CONTROL DETAILS
if (is_template_editable($templatename)==true)
{
    $templatesoutput.= "\t<table class='templatecontrol'>\n"
    ."\t<tr>\n"
    ."<th colspan='3'>\n"
    ."\t<strong>".sprintf($clang->gT("Editing template '%s' - File '%s'"),$templatename,$editfile)."</strong>\n"
    ."</th>\n"
    ."\t</tr>\n"
    ."\t<tr><th class='subheader' width='150'>"
    .$clang->gT("Standard Files:")."</th>"
    ."<td align='center' valign='top' rowspan='3'>\n"
    ."<form name='editTemplate' method='post' action='admin.php'>\n"
    ."\t<input type='hidden' name='templatename' value='$templatename' />\n"
    ."\t<input type='hidden' name='screenname' value='".html_escape($screenname)."' />\n"
    ."\t<input type='hidden' name='editfile' value='$editfile' />\n"
    ."\t<input type='hidden' name='action' value='templatesavechanges' />\n"
    ."<textarea name='changes' id='changes' rows='15' cols='40' class='codepress html'>";
    if ($editfile) {
        $templatesoutput.= textarea_encode(filetext($editfile));
    }
    $templatesoutput.= "</textarea><br />\n";
    if (is_writable("$templaterootdir/$templatename")) {
        $templatesoutput.= "<input align='right' type='submit' value='".$clang->gT("Save Changes")."'";
        if (!is_template_editable($templatename)) {
            $templatesoutput.= " disabled='disabled' alt='".$clang->gT("Changes cannot be saved to a standard template.")."'";
        }
        $templatesoutput.= " />";
    }
    else
    {
        $templatesoutput.='<span class="flashmessage">'.$clang->gT("You can't save changes because the template directory is not writable.").'</span>';
    }
    $templatesoutput.= "<br />\n"
    ."</form></td>";
    $templatesoutput.= "<th class='subheader' colspan='2' align='right' width='200'>".$clang->gT("Other Files:")."</th></tr>\n";
        
    $templatesoutput.="<tr><td valign='top' rowspan='2' class='subheader'><select size='6' name='editfile' onchange='javascript: window.open(\"admin.php?action=templates&amp;templatename=$templatename&amp;screenname=".urlencode($screenname)."&amp;editfile=\"+escape(this.value), \"_top\")'>\n"
        .makeoptions($files, "name", "name", $editfile)
        ."</select><br /><br/>\n"
        .$clang->gT("CSS & Javascript files:")
        ."<br/><select size='8' name='cssfiles' onchange='javascript: window.open(\"admin.php?action=templates&amp;templatename=$templatename&amp;screenname=".urlencode($screenname)."&amp;editfile=\"+escape(this.value), \"_top\")'>\n"
        .makeoptions($cssfiles, "name", "name", $editfile)
        . "</select>\n"
        
        ."</td>\n"
        ."<td valign='top' align='right' width='20%'><form action='admin.php' method='post'>"
    ."<table width='90' align='left' border='0' cellpadding='0' cellspacing='0'>\n<tr><td></td></tr>"
    . "<tr><td><select size='11' style='min-width:130px;' name='otherfile' id='otherfile'>\n"
    .makeoptions($otherfiles, "name", "name", "")
    ."</select>"
    ."</td></tr>"
    ."<tr><td>"
    ."<input type='submit' value='".$clang->gT("Delete")."' onclick=\"javascript:return confirm('".$clang->gT("Are you sure you want to delete this file?","js")."')\"";
    if (!is_template_editable($templatename))  {
		    $templatesoutput.= " style='color: #BBBBBB;' disabled='disabled' alt='".$clang->gT("Files in a standard template cannot be deleted.")."'";
    }
    $templatesoutput.= " />\n"
    ."<input type='hidden' name='screenname' value='".html_escape($screenname)."' />\n"
    ."<input type='hidden' name='templatename' value='$templatename' />\n"
    ."<input type='hidden' name='action' value='templatefiledelete' />\n"
    . "</td></tr></table></form></td>\n"
    ."</tr>\n"
    ."<tr>"
    ."<td valign='top'>"
    ."<form enctype='multipart/form-data' name='importtemplatefile' action='admin.php' method='post' onsubmit='return checkuploadfiletype(this.the_file.value);'>\n"
    ."<table><tr> <th class='subheader' valign='top' style='border: solid 1 #000080'>\n"
    .$clang->gT("Upload a file:")."</th></tr><tr><td><input name=\"the_file\" type=\"file\" size=\"30\" /><br />"
    ."<input type='submit' value='".$clang->gT("Upload")."'";
    if (!is_template_editable($templatename))  {
	    $templatesoutput.= " disabled='disabled'";
    }
    
    $templatesoutput.= " />\n"
    ."<input type='hidden' name='editfile' value='$editfile' />\n"
    ."<input type='hidden' name='screenname' value='".html_escape($screenname)."' />\n"
    ."<input type='hidden' name='templatename' value='$templatename' />\n"
        ."<input type='hidden' name='action' value='templateuploadfile' />\n"
    ."</td></tr></table></form>\n"
    ."</td>\n"
    ."\t</tr>\n"
    //."</table>\n"
    //."\t</td>\n"
    //."\t</tr>"
    ."</table>";
}

//SAMPLE ROW
$templatesoutput.= "\t<div class='header'>\n"
. "\t<strong>".$clang->gT("Preview:")."</strong>\n"
. "\t<input type='button' value='iPhone' id='iphone' />\n"
. "\t<input type='button' value='640x480' id='x640' />\n"
. "\t<input type='button' value='800x600' id='x800' />\n"
. "\t<input type='button' value='1024x768' id='x1024' />\n"
. "\t<input type='button' value='".$clang->gt("Full")."' id='full' />\n"
. "</div>\n"
."<div style='width:90%; margin:0 auto;'>\n";


// The following lines are forcing the browser to refresh the templates on each save
$time=date("ymdHis");
$fnew=fopen("$tempdir/template_temp_$time.html", "w+");
fwrite ($fnew, getHeader());
foreach ($cssfiles as $cssfile)
{
    $myoutput=str_replace($cssfile['name'],$cssfile['name']."?t=$time",$myoutput);
}

foreach($myoutput as $line) {
	fwrite($fnew, $line);
}
fclose($fnew);
$langdir_template="$publicurl/locale/".$_SESSION['adminlang']."/help";
$templatesoutput.= "<p>\n"
."<iframe id='previewiframe' src='$tempurl/template_temp_$time.html' width='95%' height='768' name='previewiframe' style='background-color: white;'>Embedded Frame</iframe>\n"
."</p></div>\n";
}

function doreplacement($file) { //Produce sample page from template file
	$output=array();
	foreach(file($file) as $op) {
		$output[]=templatereplace($op);
	}
	return $output;
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
	global $templaterootdir, $templatename;
	$output="";
	foreach(file("$templaterootdir/$templatename/$templatefile") as $line) {
		$output .= $line;
	}
	return $output;
}

function makegraph($currentstep, $total)
{
    global $thissurvey;
	global $publicurl, $clang;

    $size = intval(($currentstep-1)/$total*100);
	
	$graph = '<script type="text/javascript">
	$(function() {
		$("#progressbar").progressbar({
			value: '.$size.'
		});
	});';
	if (getLanguageRTL($clang->langcode))
    {
		$graph.='
		$(document).ready(function() {
			$("div.ui-progressbar-value").removeClass("ui-corner-left");
			$("div.ui-progressbar-value").addClass("ui-corner-right");
		});';  
    }
	$graph.='
	</script>
	
	<div id="progress-wrapper">
	<span class="hide">'.sprintf($clang->gT('You have completed %s%% of this survey'),$size).'</span>
		<div id="progress-pre">';
    if (getLanguageRTL($clang->langcode))
    {
      $graph.='100%';  
    }
    else
    {
      $graph.='0%';  
    }   
    
    $graph.='</div>
		<div id="progressbar"></div>
		<div id="progress-post">';
    if (getLanguageRTL($clang->langcode))
    {
      $graph.='0%';  
    }
    else
    {
      $graph.='100%';  
    }           
    $graph.='</div>
	</div>';
	
	if ($size == 0) // Progress bar looks dumb if 0 
	{
		$graph.='
		<script type="text/javascript">
			$(document).ready(function() {
				$("div.ui-progressbar-value").hide();
			}); 
		</script>';
	}
		
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


function recursive_in_array($needle, $haystack) {
    foreach ($haystack as $stalk) {
        if ($needle == $stalk || (is_array($stalk) && recursive_in_array($needle, $stalk))) {
            return true;
        }
    }
    return false;
}

/**
* This function checks if a certain template may be by modified, copied, deleted according to the settings in config.php
* @param mixed $templatename
*/
function is_template_editable($templatename)   
{
    global $standardtemplates, $standard_templates_readonly, $debug, $defaulttemplate;
    if($debug>1) // Debug mode set to developer 
    {
        return true;
    }
    elseif ((in_array($templatename,$standardtemplates) && $standard_templates_readonly==true))
    {
        return false;
    }
    else
    {
        return true;
    }
}
