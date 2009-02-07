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

if(!isset($_SESSION['loginID']))
{
	die ("Unauthenticated Access Forbiden");
}

$surveyid=returnglobal('sid');
if (!isset($gid)) {$gid=returnglobal('gid');}
if (!isset($qid)) {$qid=returnglobal('qid');}
$fieldtype=preg_replace("/[^_.a-zA-Z0-9-]/", "",$_GET['fieldtype']);
$action=preg_replace("/[^_.a-zA-Z0-9-]/", "",$_GET['editedaction']);

//$InsertansUnsupportedtypes=Array('TEST-A','TEST-B','TEST-C','TEST-D');
$InsertansUnsupportedtypes=Array(); // Currently all question types are supported

$replFields=Array();
$isInstertansEnabled=false;

$limereplacementoutput="<html>\n"
	. "\t<head>\n"
	. "\t\t<title>LimeReplacementFields</title>\n"
	. "\t\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\n"
	. "\t\t<meta content=\"noindex, nofollow\" name=\"robots\">\n"
	. "\t\t<script src=\"$fckeditordir/editor/dialog/common/fck_dialog_common.js\" type=\"text/javascript\"></script>\n"
	. "\t\t<script language=\"javascript\">\n"
	. "\t\t\tvar mydialog = window.parent ;\n"
	. "\t\t\tvar oEditor = mydialog.InnerDialogLoaded() ;\n"
	. "\t\t\tvar dialog = oEditor.FCK ;\n"
	. "\t\t\tvar FCKLang = oEditor.FCKLang ;\n"
	. "\t\t\tvar FCKLimeReplacementFieldss = oEditor.FCKLimeReplacementFieldss ;\n"
	. "\t\t\twindow.onload = function ()\n"
	. "\t\t\t{\n"
	. "\t\t\t\toEditor.FCKLanguageManager.TranslatePage( document ) ;\n"
	. "\t\t\t\tLoadSelected() ;\n"
	. "\t\t\t\tmydialog.SetOkButton( true ) ;\n"
	. "\n"
	. "\t\t\t\tSelectField( 'cquestions' ) ;\n" 
	. "\t\t\t}\n"
	. "\n";

/**$limereplacementoutput="\n"
	. "if (! oEditor.FCKBrowserInfo.IsIE)\n"
	. "{\n"
	. "\tinnertext = '' + dialog.EditorWindow.getSelection() + '' ;\n"
	. "}\n"
	. "else\n"
	. "{\n"
	. "\tinnertext = '' + dialog.EditorDocument.selection.createRange().text + '' ;\n"
	. "}\n";
**/

$limereplacementoutput .= ""
	. "\t\t\tvar eSelected = dialog.Selection.GetSelectedElement() ;\n"
	. "\n";

/**
$limereplacementoutput="\n"
	. "function LoadSelected()\n"
	. "{\n"
	. "\tif ( innertext == '' )\n"
	. "\t\treturn ;\n"
	. "var replcode=innertext.substring(innertext.indexOf('{')+1,innertext.lastIndexOf('}'));\n"
	. "\t\tdocument.getElementById('cquestions').value = replcode;\n"
	. "}\n";
**/

$limereplacementoutput .= ""
	. "\t\t\tfunction LoadSelected()\n"
	. "\t\t\t{\n"
	. "\t\t\t\tif ( !eSelected )\n"
	. "\t\t\t\t\treturn ;\n"
	. "\t\t\t\tif ( eSelected.tagName == 'SPAN' && eSelected._fckLimeReplacementFields )\n"
	. "\t\t\t\t\t document.getElementById('cquestions').value = eSelected._fckLimeReplacementFields ;\n"
	. "\t\t\t\telse\n"
	. "\t\t\t\t\teSelected == null ;\n"
	. "\t\t\t}\n";
	
	
$limereplacementoutput .= ""
	. "\t\t\tfunction Ok()\n"
	. "\t\t\t{\n"
	. "\t\t\t\tvar sValue = document.getElementById('cquestions').value ;\n"

	. "\t\t\t\tFCKLimeReplacementFieldss.Add( sValue ) ;\n"
	. "\t\t\t\treturn true ;\n"
	. "\t\t\t}\n";

$limereplacementoutput .= ""
	. "\t\t\t</script>\n"
	. "\t\t</head>\n";

$limereplacementoutput .= "\t<body scroll=\"no\" style=\"OVERFLOW: hidden\">\n"
			. "\t\t<table height=\"100%\" cellSpacing=\"0\" cellPadding=\"0\" width=\"100%\" border=\"0\">\n"
			. "\t\t\t<tr>\n"
			. "\t\t\t\t<td>\n";

switch ($fieldtype)
{
	case 'survey-desc':
	case 'survey-welc':
		$replFields[]=array('TOKEN:FIRSTNAME',$clang->gT("Firstname from token"));
		$replFields[]=array('TOKEN:LASTNAME',$clang->gT("Lastname from token"));
		$replFields[]=array('TOKEN:EMAIL',$clang->gT("Email from the token"));
		$replFields[]=array('TOKEN:ATTRIBUTE_1',$clang->gT("Attribute_1 from token"));
		$replFields[]=array('TOKEN:ATTRIBUTE_2',$clang->gT("Attribute_2 from token"));
		$replFields[]=array('EXPIRY',$clang->gT("Survey expiration date (YYYY-MM-DD)"));
		$replFields[]=array('EXPIRY-DMY',$clang->gT("Survey expiration date (DD-MM-YYYY)"));
		$replFields[]=array('EXPIRY-MDY',$clang->gT("Survey expiration date (MM-DD-YYYY)"));
	break;

	case 'email-inv':
	case 'email-rem':
		// these 2 fields are supported by email-inv and email-rem
		// but not email-conf and email-reg for the moment
		$replFields[]=array('EMAIL',$clang->gT("Email from the token"));
		$replFields[]=array('TOKEN',$clang->gT("Token code for this participant"));
	case 'email-conf':
	case 'email-reg':
		$replFields[]=array('FIRSTNAME',$clang->gT("Firstname from token"));
		$replFields[]=array('LASTNAME',$clang->gT("Lastname from token"));
		$replFields[]=array('SURVEYNAME',$clang->gT("Name of the survey"));
		$replFields[]=array('SURVEYDESCRIPTION',$clang->gT("Description of the survey"));
		$replFields[]=array('ATTRIBUTE_1',$clang->gT("Attribute_1 from token"));
		$replFields[]=array('ATTRIBUTE_2',$clang->gT("Attribute_2 from token"));
		$replFields[]=array('ADMINNAME',$clang->gT("Name of the survey administrator"));
		$replFields[]=array('ADMINEMAIL',$clang->gT("Email address of the survey administrator"));
		$replFields[]=array('SURVEYURL',$clang->gT("URL of the survey"));
		$replFields[]=array('EXPIRY',$clang->gT("Survey expiration date (YYYY-MM-DD)"));
		$replFields[]=array('EXPIRY-DMY',$clang->gT("Survey expiration date (DD-MM-YYYY)"));
		$replFields[]=array('EXPIRY-MDY',$clang->gT("Survey expiration date (MM-DD-YYYY)"));
	break;

	case 'group-desc':
	case 'question-text':
	case 'question-help':
		$replFields[]=array('TOKEN:FIRSTNAME',$clang->gT("Firstname from token"));
		$replFields[]=array('TOKEN:LASTNAME',$clang->gT("Lastname from token"));
		$replFields[]=array('TOKEN:EMAIL',$clang->gT("Email from the token"));
		$replFields[]=array('TOKEN:ATTRIBUTE_1',$clang->gT("Attribute_1 from token"));
		$replFields[]=array('TOKEN:ATTRIBUTE_2',$clang->gT("Attribute_2 from token"));
		$replFields[]=array('EXPIRY',$clang->gT("Survey expiration date (YYYY-MM-DD)"));
		$replFields[]=array('EXPIRY-DMY',$clang->gT("Survey expiration date (DD-MM-YYYY)"));
		$replFields[]=array('EXPIRY-MDY',$clang->gT("Survey expiration date (MM-DD-YYYY)"));
	case 'editanswer':
		$isInstertansEnabled=true;
	break;
}


if ($isInstertansEnabled===true)
{
	if (empty($surveyid)) {die("No SID provided.");}
	
	//2: Get all other questions that occur before this question that are pre-determined answer types
	
	//TO AVOID NATURAL SORT ORDER ISSUES,
	//FIRST GET ALL QUESTIONS IN NATURAL SORT ORDER
	//, AND FIND OUT WHICH NUMBER IN THAT ORDER THIS QUESTION IS
	$qquery = "SELECT * "
	        ."FROM {$dbprefix}questions, "
	             ."{$dbprefix}groups "
	        ."WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid "
	        ."AND {$dbprefix}questions.sid=$surveyid "
	          ."AND {$dbprefix}questions.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
	          ."AND {$dbprefix}groups.language='".GetBaseLanguageFromSurveyID($surveyid)."' " ;
	
	$qresult = db_execute_assoc($qquery) or safe_die ("$qquery<br />".$connect->ErrorMsg());
	$qrows = $qresult->GetRows();
	// Perform a case insensitive natural sort on group name then question title (known as "code" in the form) of a multidimensional array
	usort($qrows, 'CompareGroupThenTitle');
	


    $surveyInfo = getSurveyInfo($surveyid);
	$surveyformat = $surveyInfo['format'];// S, G, A
	$prevquestion=null;
	$previouspagequestion = true;
	//Go through each question until we reach the current one
	//error_log(print_r($qrows,true));
	foreach ($qrows as $qrow)
	{
		$AddQuestion=True;
		switch ($action)
		{
			case 'addgroup':
				$AddQuestion=True;
			break;

			case 'editgroup':
				if (empty($gid)) {die("No GID provided.");}

				if ($qrow['gid'] == $gid)
				{
					$AddQuestion=False;
				}
			break;

			case 'addquestion':
				if (empty($gid)) {die("No GID provided.");}

				if ( !is_null($prevquestion) &&
					$prevquestion['gid'] == $gid &&
					$qrow['gid'] != $gid)
				{
					$AddQuestion=False;
				}
			break;

			case 'editanswer':
			case 'copyquestion':
			case 'editquestion':
				if (empty($gid)) {die("No GID provided.");}
				if (empty($qid)) {die("No QID provided.");}

				if ($qrow['gid'] == $gid &&
					$qrow['qid'] == $qid)
				{
					$AddQuestion=False;
				}
			break;
			default:
				die("No Action provided.");
			break;
		}

		if ( $AddQuestion===True)
		{
			if ($surveyformat == "S")
			{
				$previouspagequestion = true;
			}
			elseif ($surveyformat == "G")
			{
				if ($previouspagequestion === true)
				{ // Last question was on a previous page
					if ($qrow["gid"] == $gid)
					{ // This question is on same page
						$previouspagequestion = false;
					}
				}
			}
			elseif ($surveyformat == "A")
			{
				$previouspagequestion = false;
			}

			$questionlist[]=Array( "qid" => $qrow["qid"], "previouspage" => $previouspagequestion);
			$prevquestion=$qrow;
		}
		else
		{
			break;
		}
	}
	
//		if ($qrow["qid"] != $qid)
//		{
//			if (!in_array($qrow['type'],$InsertansUnsupportedtypes)
//			{ //remember the questions of this type
//				$questionlist[]=$qrow["qid"];
//			}
//		}

	$theserows=array();

	if (isset($questionlist) && is_array($questionlist))
	{
		foreach ($questionlist as $ql)
		{
	    $query = "SELECT {$dbprefix}questions.qid, "
	                   ."{$dbprefix}questions.sid, "
	                   ."{$dbprefix}questions.gid, "
	                   ."{$dbprefix}questions.question, "
	                   ."{$dbprefix}questions.type, "
	                   ."{$dbprefix}questions.lid, "
                       ."{$dbprefix}questions.lid1, "  
	                   ."{$dbprefix}questions.title "
	              ."FROM {$dbprefix}questions, "
	                   ."{$dbprefix}groups "
	             ."WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid "
	               ."AND {$dbprefix}questions.qid=".$ql['qid']." "
	               ."AND {$dbprefix}questions.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
	               ."AND {$dbprefix}groups.language='".GetBaseLanguageFromSurveyID($surveyid)."'" ;
	
	        $result=db_execute_assoc($query) or die("Couldn't get question $qid");
	
			$thiscount=$result->RecordCount();
	
	    // And store ¿again? these questions in this array...
			while ($myrows=$result->FetchRow())
	    {                   //key => value
	      $theserows[]=array("qid"=>$myrows['qid'],
	                         "sid"=>$myrows['sid'],
	                         "gid"=>$myrows['gid'],
	                         "previouspage"=>$ql['previouspage'],
	                         "question"=>$myrows['question'],
	                         "type"=>$myrows['type'],
	                         "lid"=>$myrows['lid'],
                             "lid1"=>$myrows['lid1'],     
	                         "title"=>$myrows['title']);
			}
		}
	}
	
	
	$questionscount=count($theserows);
	
	if ($questionscount > 0)
	{
		$X="X";
	  // Will detect if the questions are type D to use latter
	
	  $dquestions=array();
	
		foreach($theserows as $rows)
		{
		    $shortquestion=$rows['title'].": ".strip_tags($rows['question']);
	
	    if ($rows['type'] == "A" ||
	        $rows['type'] == "B" ||
	        $rows['type'] == "C" ||
	        $rows['type'] == "E" ||
	        $rows['type'] == "F" ||
	        $rows['type'] == "H" ||
	        $rows['type'] == "Q" ||
	        $rows['type'] == "K") // K added by lemeur
	    {
	      $aquery="SELECT * "
	               ."FROM {$dbprefix}answers "
	              ."WHERE qid={$rows['qid']} "
	                ."AND {$dbprefix}answers.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
	           ."ORDER BY sortorder, "
	                    ."answer";
	
				$aresult=db_execute_assoc($aquery) or safe_die ("Couldn't get answers to Array questions<br />$aquery<br />".$connect->ErrorMsg());
	
				while ($arows = $aresult->FetchRow())
				{
	                $shortanswer = strip_tags($arows['answer']);
	
					$shortanswer .= " [{$arows['code']}]";
					$cquestions[]=array("$shortquestion [$shortanswer]", $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'],$rows['previouspage']);
	
	      } //while
	    } //if A,B,C,E,F,H
	    elseif ($rows['type'] == ":" || $rows['type'] == ";") // Multiflexi
	    {
		    //Get the LIDs
		    $fquery = "SELECT * "
			    ."FROM {$dbprefix}labels "
			    ."WHERE lid={$rows['lid']} "
			    ."AND language='".GetBaseLanguageFromSurveyID($surveyid)."' "
			    ."ORDER BY sortorder, code ";
		    $fresult = db_execute_assoc($fquery);
		    while ($frow=$fresult->FetchRow())
		    {
			    $lids[$frow['code']]=$frow['title'];
		    }
		    //Now cycle through the answers
		    $aquery="SELECT * "
			    ."FROM {$dbprefix}answers "
			    ."WHERE qid={$rows['qid']} "
			    ."AND {$dbprefix}answers.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
			    ."ORDER BY sortorder, "
			    ."answer";
		    $aresult=db_execute_assoc($aquery) or safe_die ("Couldn't get answers to Array questions<br />$aquery<br />".$connect->ErrorMsg());

		    while ($arows = $aresult->FetchRow())
		    {
			    $shortanswer = strip_tags($arows['answer']);

			    $shortanswer .= " [{$arows['code']}]";
			    foreach($lids as $key=>$val) 
			    {
				    $cquestions[]=array("$shortquestion [$shortanswer [$val]] ", $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code']."_".$key,$rows['previouspage']);
			    }
		    }

	    } //TIBO
	    elseif ($rows['type'] == "R") //Answer Ranking
			{
	      $aquery="SELECT * "
	             ."FROM {$dbprefix}answers "
	             ."WHERE qid={$rows['qid']} "
	             ."AND ".db_table_name('answers').".language='".GetBaseLanguageFromSurveyID($surveyid)."' "
				."ORDER BY sortorder, answer";
				$aresult=db_execute_assoc($aquery) or safe_die ("Couldn't get answers to Ranking question<br />$aquery<br />".$connect->ErrorMsg());
				$acount=$aresult->RecordCount();
				while ($arow=$aresult->FetchRow())
				{
					$theanswer = addcslashes($arow['answer'], "'");
					$quicky[]=array($arow['code'], $theanswer);
				}
				for ($i=1; $i<=$acount; $i++)
				{
					$cquestions[]=array("$shortquestion [RANK $i]", $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$i,$rows['previouspage']);
				}
				unset($quicky);
	    } // for type R
        elseif ($rows['type'] == "1") //Answer multi scale 
        {
            $aquery="SELECT * "
                ."FROM {$dbprefix}answers "
                ."WHERE qid={$rows['qid']} "
                ."AND {$dbprefix}answers.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
                ."ORDER BY sortorder, "
                ."answer";
            $aresult=db_execute_assoc($aquery) or safe_die ("Couldn't get answers to multi scale question<br />$aquery<br />".$connect->ErrorMsg());
            $acount=$aresult->RecordCount();            
            while ($arow=$aresult->FetchRow())
            {
                $theanswer = addcslashes($arow['code'], "'");
                $quicky[]=array($arow['code'], $theanswer);

                $lquery="SELECT * "
                    ."FROM {$dbprefix}labels "
                    ."WHERE lid={$rows['lid']} "
                    ."AND {$dbprefix}labels.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
                    ."ORDER BY sortorder, "
                    ."lid";
                $lresult=db_execute_assoc($lquery) or safe_die ("Couldn't get labels to Array <br />$lquery<br />".$connect->ErrorMsg());                
                while ($lrows = $lresult->FetchRow())
                {
                    $cquestions[]=array($rows['title']." ".$arow['code']." [Label ".$lrows['code']."]", $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arow['code']."#0",$rows['previouspage']);
                }                
               $lquery="SELECT * "
                    ."FROM {$dbprefix}labels "
                    ."WHERE lid={$rows['lid1']} "
                    ."AND {$dbprefix}labels.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
                    ."ORDER BY sortorder, "
                    ."lid";
                $lresult=db_execute_assoc($lquery) or safe_die ("Couldn't get labels to Array <br />$lquery<br />".$connect->ErrorMsg());                
                while ($lrows = $lresult->FetchRow())
                {
                    $cquestions[]=array($rows['title']." ".$arow['code']." [Label ".$lrows['code']."]", $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arow['code']."#1",$rows['previouspage']);
                }                

            }        
            unset($quicky);

        
        }   //Answer multi scale
			else
			{
				$cquestions[]=array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'],$rows['previouspage']);
	    } //else
	  } //foreach theserows
	} //if questionscount > 0
	
	// Now I´ll add a hack to add the questions before as option
	// if they are date type
	
	
	//$limereplacementoutput .="\t\t\t<div style='overflow-x:scroll; width:100%; overflow: -moz-scrollbars-horizontal; overflow-y:scroll; height: 100px;'>\n"
	
	
}


if (count($replFields) > 0 || isset($cquestions) )
{
	$limereplacementoutput .= "\t\t\t\t\t<select name='cquestions' id='cquestions' style='font-family:verdana; background-color: #FFFFFF; font-size:10; border: 0px;width:99%;' size='15' ondblclick='Ok();'>\n";
	$noselection = false;
}
else
{
	$limereplacementoutput .= $clang->gT("No replacement variable available for this field");
	//echo $limereplacementoutput;
	//return;
	$noselection = true;
	
}

if (count($replFields) > 0)
{
	$limereplacementoutput .= "\t\t\t\t\t\t<optgroup label='".$clang->gT("Standard Fields")."'>\n";

	foreach ($replFields as $stdfield)
	{
		$limereplacementoutput .= "\t\t\t\t\t\t\t<option value='".$stdfield[0]."'";
		$limereplacementoutput .= ">".$stdfield[1]."</option>\n";
	}
	$limereplacementoutput .= "\t\t\t\t\t\t</optgroup>\n";
}

if (isset($cquestions))
{
	$limereplacementoutput .= "\t\t\t\t\t\t<optgroup label='".$clang->gT("Previous Answers Fields")."'>\n";
	foreach ($cquestions as $cqn)
	{
		$isDisabled="";
		if (in_array($cqn[2],$InsertansUnsupportedtypes))
		{
			 $isDisabled=" disabled='disabled'";
		}
		elseif ($cqn[4] === false)
		{
			 $isDisabled=" disabled='disabled'";
		}

		$limereplacementoutput .= "\t\t\t\t\t\t\t<option value='INSERTANS:$cqn[3]'";
		$limereplacementoutput .= " $isDisabled >$cqn[0]</option>\n";
	}
	$limereplacementoutput .= "\t\t\t\t\t\t</optgroup>\n";
}


if ($noselection === false)
{
	$limereplacementoutput .= "\t\t\t\t\t</select>\n";
}

$limereplacementoutput .= "\t\t\t\t</td>\n"
			. "\t\t\t</tr>\n";

if (isset($surveyformat))
{
    switch ($surveyformat)
    {
	    case 'A':
		    $limereplacementoutput .= "\t\t\t<tr>\n"
					. "\t\t\t\t<td>\n";
		    $limereplacementoutput .= "\t\t\t\t\t<br />\n"
					. "\t\t\t\t\t<font color='orange'>".$clang->gT("Some Question have been disabled")."</font>\n";
            $limereplacementoutput .= "\t\t\t\t\t<br />\n"
				. "\t\t\t\t\t".sprintf($clang->gT("Survey Format is %s:"), $clang->gT("All in one"))
				. "\t\t\t\t\t<br />\n"
				. "\t\t\t\t\t<i>".$clang->gT("Only Previous pages answers are available")."</i>\n"
				. "\t\t\t\t\t<br />\n";
		    $limereplacementoutput .= "\t\t\t\t</td>\n"
					. "\t\t\t</tr>\n";
	    break;
	    case 'G':
		    $limereplacementoutput .= "\t\t\t<tr>\n"
					. "\t\t\t\t<td>\n";
		    $limereplacementoutput .= "\t\t\t\t\t<br /><font color='orange'>".$clang->gT("Some Question have been disabled")."</font>";
            $limereplacementoutput .= "<br />".sprintf($clang->gT("Survey mode is set to %s:"), $clang->gT("Group by Group"))."<br/><i>".$clang->gT("Only Previous pages answers are available")."</i><br />";
			$limereplacementoutput .= "\t\t\t\t</td>\n"
						. "\t\t\t</tr>\n";
	    break;
    }
}

$limereplacementoutput .= "\t\t</table>\n"
			. "\t</body>\n"
			. "</html>";

echo $limereplacementoutput;
?>
