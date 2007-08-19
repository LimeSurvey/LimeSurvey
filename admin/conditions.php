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
*/


include_once("login_check.php");

$surveyid=returnglobal('sid');
$conditionsoutput='';

//Ensure script is not run directly, avoid path disclosure
if (empty($surveyid)) {die("No SID provided.");}

// ToDo: activate this again
//if(isset($_POST['cquestions'])) {
//	echo str_replace("<body ", "<body onload='getAnswers(\"".$_POST['cquestions']."\")'", $htmlheader);
//}; 


$conditionsoutput .= "<table width='100%' border='0' bgcolor='#555555' cellspacing='0' cellpadding='0'>\n"
."\t<tr><td align='center'><font color='white'><strong>"
.$clang->gT("Condition Designer")."</strong></font></td></tr>\n"
."</table>\n";


if (!isset($surveyid))
{
	$conditionsoutput .= "<br /><center><strong>"
	.$clang->gT("You have not selected a Survey.")." ".$clang->gT("You cannot run this script directly.")
	."</strong></center>\n"
	."</body></html>\n";
	return;
}
if (!isset($_GET['qid']) && !isset($_POST['qid']))
{
	$conditionsoutput .= "<br /><center><strong>"
	.$clang->gT("You have not selected a Question.")." ".$clang->gT("You cannot run this script directly.")
	."</strong></center>\n"
	."</body></html>\n";
	return;
}

$markcidarray=Array();
if (isset($_GET['markcid']))
{
	$markcidarray=explode("-",$_GET['markcid']);
	
}

//ADD NEW ENTRY IF THIS IS AN ADD
if (isset($_POST['subaction']) && $_POST['subaction'] == "insertcondition")
{
  if ((!isset($_POST['canswers']) &&
       !isset($_POST['ValOrRegEx'])) ||
      !isset($_POST['cquestions']))
	{
		$conditionsoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Your condition could not be added! It did not include the question and/or answer upon which the condition was based. Please ensure you have selected a question and an answer.","js")."\")\n //-->\n</script>\n";
	}
	else
	{
    if (isset($_POST['canswers']))
    {
		foreach ($_POST['canswers'] as $ca)
		{
// There I must add the indicated field for condition method
// Original
//      $query = "INSERT INTO {$dbprefix}conditions (qid, cqid, cfieldname, value) VALUES "
//      . "('{$_POST['qid']}', '{$_POST['cqid']}', '{$_POST['cquestions']}', '$ca')";
// Modified
         $query = "INSERT INTO {$dbprefix}conditions (qid, cqid, cfieldname, method, value) VALUES "
         . "('{$_POST['qid']}', '{$_POST['cqid']}', '{$_POST['cquestions']}', '{$_POST['method']}', '$ca')";
         $result = $connect->Execute($query) or die ("Couldn't insert new condition<br />$query<br />".$connect->ErrorMsg());
      }
    }
    if (isset($_POST['ValOrRegEx']) && $_POST['ValOrRegEx']) //Remmember: '', ' ', 0 are evaluated as FALSE
    { //here is saved the textarea for constants or regex
      $query = "INSERT INTO {$dbprefix}conditions (qid, cqid, cfieldname, method, value) VALUES "
      . "('{$_POST['qid']}', '{$_POST['cqid']}', '{$_POST['cquestions']}', '{$_POST['method']}', '{$_POST['ValOrRegEx']}')";
			$result = $connect->Execute($query) or die ("Couldn't insert new condition<br />$query<br />".$connect->ErrorMsg());
		}
	}
}
//DELETE ENTRY IF THIS IS DELETE
if (isset($_POST['subaction']) && $_POST['subaction'] == "delete")
{
	$query = "DELETE FROM {$dbprefix}conditions WHERE cid={$_POST['cid']}";
	$result = $connect->Execute($query) or die ("Couldn't delete condition<br />$query<br />".$connect->ErrorMsg());
}
//COPY CONDITIONS IF THIS IS COPY
if (isset($_POST['subaction']) && $_POST['subaction'] == "copyconditions")
{
	$qid=returnglobal('qid');
	$copyconditionsfrom=returnglobal('copyconditionsfrom');
	$copyconditionsto=returnglobal('copyconditionsto');
	if (isset($copyconditionsto) && is_array($copyconditionsto) && isset($copyconditionsfrom) && is_array($copyconditionsfrom))
	{
		//Get the conditions we are going to copy
		$query = "SELECT * FROM {$dbprefix}conditions\n"
		."WHERE cid in ('";
		$query .= implode("', '", $copyconditionsfrom);
		$query .= "')";
		$result = db_execute_assoc($query) or die("Couldn't get conditions for copy<br />$query<br />".$connect->ErrorMsg());
		while($row=$result->FetchRow())
		{
			$proformaconditions[]=array("cqid"=>$row['cqid'],
			"cfieldname"=>$row['cfieldname'],
			"method"=>$row['method'],
			"value"=>$row['value']);
		} // while
		foreach ($copyconditionsto as $copyc)
		{
			list($newsid, $newgid, $newqid)=explode("X", $copyc);
			foreach ($proformaconditions as $pfc)
			{
				//First lets make sure there isn't already an exact replica of this condition
				$query = "SELECT * FROM {$dbprefix}conditions\n"
				."WHERE qid='$newqid'\n"
				."AND cqid='".$pfc['cqid']."'\n"
				."AND cfieldname='".$pfc['cfieldname']."'\n"
				."AND method='".$pfc['method']."'\n"
				."AND value='".$pfc['value']."'";
				$result = $connect->Execute($query) or die("Couldn't check for existing condition<br />$query<br />".$connect->ErrorMsg());
				$count = $result->RecordCount();
				if ($count == 0) //If there is no match, add the condition.
				{
					$query = "INSERT INTO {$dbprefix}conditions ( qid,cqid,cfieldname,method,value) \n"
					."VALUES ( '$newqid', '".$pfc['cqid']."',"
					."'".$pfc['cfieldname']."', '".$pfc['method']."',"
					."'".$pfc['value']."')";
					$result=$connect->Execute($query) or die ("Couldn't insert query<br />$query<br />".$connect->ErrorMsg());
				}
			}
		}
	}
	else
	{
		$message = $clang->gT("Did not copy questions","js").": ";
		if (!isset($copyconditionsfrom))
		{
			$message .= $clang->gT("No condition selected to copy from","js").". ";
		}
		if (!isset($copyconditionsto))
		{
			$message .= $clang->gT("No question selected to copy condition to","js").".";
		}
		$conditionsoutput .= "<script type=\"text/javascript\">\n<!--\nalert('$message');\n//-->\n</script>\n";
	}
}

unset($cquestions);
unset($canswers);


// *******************************************************************
// ** ADD FORM
// *******************************************************************
//1: Get information for this question
if (!isset($qid)) {$qid=returnglobal('qid');}
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}

$query = "SELECT * "
         ."FROM {$dbprefix}questions, "
              ."{$dbprefix}groups "
        ."WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid "
          ."AND qid=$qid "
          ."AND {$dbprefix}questions.language='".GetBaseLanguageFromSurveyID($surveyid)."'" ;
$result = db_execute_assoc($query) or die ("Couldn't get information for question $qid<br />$query<br />".$connect->ErrorMsg());
while ($rows=$result->FetchRow())
{
	$questiongroupname=$rows['group_name'];
	$questiontitle=$rows['title'];
	$questiontext=$rows['question'];
	$questiontype=$rows['type'];
}

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

$qresult = db_execute_assoc($qquery) or die ("$qquery<br />".$connect->ErrorMsg());
$qrows = $qresult->GetRows();
// Perform a case insensitive natural sort on group name then question title (known as "code" in the form) of a multidimensional array
usort($qrows, 'CompareGroupThenTitle');

$position="before";
//Go through each question until we reach the current one
foreach ($qrows as $qrow)
{
	if ($qrow["qid"] != $qid && $position=="before")
	{
    if ($qrow['type'] != "T" && // $qrow['type'] != "S" &&
        $qrow['type'] != "Q")   //&& $qrow['type'] != "D"
    { //remember the questions of this type
			$questionlist[]=$qrow["qid"];
		}
	}
	elseif ($qrow["qid"] == $qid)
	{
		break;
	}
}

//Now, using the same array which is now properly sorted by group then question
//Create an array of all the questions that appear AFTER the current one
$position = "before";
foreach ($qrows as $qrow) //Go through each question until we reach the current one
{
	if ($qrow["qid"] == $qid)
	{
		$position="after";
		//break;
	}
	elseif ($qrow["qid"] != $qid && $position=="after")
	{
		$postquestionlist[]=$qrow['qid'];
	}
}

$theserows=array();
//Now, use the questions left appart in the step before, these of type S, T or Q 
//(originally was D too)
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
                   ."{$dbprefix}questions.title "
              ."FROM {$dbprefix}questions, "
                   ."{$dbprefix}groups "
             ."WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid "
               ."AND {$dbprefix}questions.qid=$ql "
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
                         "question"=>$myrows['question'],
                         "type"=>$myrows['type'],
                         "lid"=>$myrows['lid'],
                         "title"=>$myrows['title']);
		}
	}
}

if (isset($postquestionlist) && is_array($postquestionlist))
{
	foreach ($postquestionlist as $pq)
	{
    $query = "SELECT {$dbprefix}questions.qid, "
                   ."{$dbprefix}questions.sid, "
                   ."{$dbprefix}questions.gid, "
                   ."{$dbprefix}questions.question, "
                   ."{$dbprefix}questions.type, "
                   ."{$dbprefix}questions.lid, "
                   ."{$dbprefix}questions.title "
              ."FROM {$dbprefix}questions, "
                   ."{$dbprefix}groups "
             ."WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid AND "
                   ."{$dbprefix}questions.qid=$pq AND "
                   ."{$dbprefix}questions.language='".GetBaseLanguageFromSurveyID($surveyid)."'" ;

		$result = db_execute_assoc($query) or die("Couldn't get postquestions $qid<br />$query<br />".$connect->ErrorMsg());

		$postcount=$result->RecordCount();

		while($myrows=$result->FetchRow())
		{
      $postrows[]=array("qid"=>$myrows['qid'],
                        "sid"=>$myrows['sid'],
                        "gid"=>$myrows['gid'],
                        "question"=>$myrows['question'],
                        "type"=>$myrows['type'],
                        "lid"=>$myrows['lid'],
                        "title"=>$myrows['title']);
		} // while
	}
	$postquestionscount=count($postrows);
}

$questionscount=count($theserows);

if (isset($postquestionscount) && $postquestionscount > 0) //Build the select box for questions after this one
{
	foreach ($postrows as $pr)
	{
		$pquestions[]=array("text"=>$pr['title'].": ".substr($pr['question'], 0, 30),
		"fieldname"=>$pr['sid']."X".$pr['gid']."X".$pr['qid']);
	}
}

if ($questionscount > 0)
{
	$X="X";
  // Will detect if the questions are type D to use latter

  $dquestions=array();

	foreach($theserows as $rows)
	{
    if (strlen($rows['question']) > 30)
    {$shortquestion=$rows['title'].": ".substr(strip_tags($rows['question']), 0, 30).".. ";}
    else
    {$shortquestion=$rows['title'].": ".strip_tags($rows['question']);}

    if ($rows['type'] == "A" ||
        $rows['type'] == "B" ||
        $rows['type'] == "C" ||
        $rows['type'] == "E" ||
        $rows['type'] == "F" ||
        $rows['type'] == "H")
    {
      $aquery="SELECT * "
               ."FROM {$dbprefix}answers "
              ."WHERE qid={$rows['qid']} "
                ."AND {$dbprefix}answers.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
           ."ORDER BY sortorder, "
                    ."answer";

			$aresult=db_execute_assoc($aquery) or die ("Couldn't get answers to Array questions<br />$aquery<br />".$connect->ErrorMsg());

			while ($arows = $aresult->FetchRow())
			{
        if (strlen($arows['answer']) > 30)
           {$shortanswer=substr($arows['answer'], 0, 30).".. ";}
        else
           {$shortanswer = $arows['answer'];}

				$shortanswer .= " [{$arows['code']}]";
				$cquestions[]=array("$shortquestion [$shortanswer]", $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code']);

				switch ($rows['type'])
				{
          case "A": //Array 5 buttons
					for ($i=1; $i<=5; $i++)
					{
						$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], $i, $i);
					}
					break;
          case "B": //Array 10 buttons
					for ($i=1; $i<=10; $i++)
					{
						$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], $i, $i);
					}
					break;
          case "C": //Array Y/N/NA
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "Y", $clang->gT("Yes"));
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "U", $clang->gT("Uncertain"));
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "N", $clang->gT("No"));
					break;
          case "E": //Array >/=/<
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "I", $clang->gT("Increase"));
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "S", $clang->gT("Same"));
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "D", $clang->gT("Decrease"));
					break;
          case "F": //Array Flexible Row
          case "H": //Array Flexible Column
              $fquery = "SELECT * "
                       ."FROM {$dbprefix}labels "
                       ."WHERE lid={$rows['lid']} "
                         ."AND language='".GetBaseLanguageFromSurveyID($surveyid)."' "
                       ."ORDER BY sortorder, code ";
					$fresult = db_execute_assoc($fquery);
					while ($frow=$fresult->FetchRow())
					{
						$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], $frow['code'], $frow['title']);
					}
					break;
				}
        $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "", $clang->gT("No answer"));
      } //while
    } //if A,B,C,E,F,H
    elseif ($rows['type'] == "R") //Answer Ranking
		{
      $aquery="SELECT * "
             ."FROM {$dbprefix}answers "
             ."WHERE qid={$rows['qid']} "
             ."AND ".db_table_name('answers').".language='".GetBaseLanguageFromSurveyID($surveyid)."' "
			."ORDER BY sortorder, answer";
			$aresult=db_execute_assoc($aquery) or die ("Couldn't get answers to Ranking question<br />$aquery<br />".$connect->ErrorMsg());
			$acount=$aresult->RecordCount();
			while ($arow=$aresult->FetchRow())
			{
				$theanswer = addcslashes($arow['answer'], "'");
				$quicky[]=array($arow['code'], $theanswer);
			}
			for ($i=1; $i<=$acount; $i++)
			{
				$cquestions[]=array("$shortquestion [RANK $i]", $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$i);
				foreach ($quicky as $qck)
				{
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$i, $qck[0], $qck[1]);
				}
				$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$i, " ", $clang->gT("No answer"));
			}
			unset($quicky);
    } // for type R
		else
		{
			$cquestions[]=array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid']);
			switch ($rows['type'])
			{
        case "Y": // Y/N/NA
				$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "Y", $clang->gT("Yes"));
				$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "N", $clang->gT("No"));
				$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], " ", $clang->gT("No answer"));
				break;
        case "G": //Gender
				$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "F", $clang->gT("Female"));
				$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "M", $clang->gT("Male"));
				$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], " ", $clang->gT("No answer"));
				break;
        case "5": // 5 choice
				for ($i=1; $i<=5; $i++)
				{
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], $i, $i);
				}
				$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], " ", $clang->gT("No answer"));
				break;
        case "W": // List
        case "Z": // List Flexible Radio Button
				$fquery = "SELECT * FROM {$dbprefix}labels\n"
				. "WHERE lid={$rows['lid']} AND language='".GetBaseLanguageFromSurveyID($surveyid)."' "
				. "ORDER BY sortorder, code";

				$fresult = db_execute_assoc($fquery);

                if (!isset($arows['code'])) {$arows['code']='';}  // for some questions types there is no code
				while ($frow=$fresult->FetchRow())
				{
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], $frow['code'], $frow['title']);
				}
				break;
				default:
            $aquery="SELECT * "
                   ."FROM {$dbprefix}answers "
                   ."WHERE qid={$rows['qid']} "
                     ."AND language='".GetBaseLanguageFromSurveyID($surveyid)."' "
                   ."ORDER BY sortorder, "
                            ."answer";
            // Ranking question? Replacing "Ranking" by "this"
            $aresult=db_execute_assoc($aquery) or die ("Couldn't get answers to this question<br />$aquery<br />".$connect->ErrorMsg());

				while ($arows=$aresult->FetchRow())
				{
					$theanswer = addcslashes($arows['answer'], "'");
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], $arows['code'], $theanswer);
				}
            if ($rows['type'] == "D")
            {
               $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], " ", $clang->gT("No answer"));

               // Now, save the questions type D only, then
               // it don´t need pass by all the array elements...
               $dquestions[]=$rows;

               // offer previous date questions to compare
               foreach ($dquestions as $dq)
				{
                   if ($rows['qid'] != $dq['qid'] &&
                       $dq['type'] == "D")
                   {   // Can´t compare with the same question, and only if are D
                       // The question tittle is enclossed by @ to be identified latter
                       // and be processed accordingly
                       $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "@".$dq['title']."@", $dq['title'].": ".$dq['question']);
				}
			}
		}
            elseif ($rows['type'] != "M" &&
                    $rows['type'] != "P" &&
                    $rows['type'] != "J" &&
                    $rows['type'] != "I" )
            {
              $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], " ", $clang->gT("No answer"));
	}
        break;
        // types X,D,!,0,Q,N,S,T,U,^ just have an option as "No Answer" originally
        // but now if type is D could have a list of questions D after it.
      }//switch row type
    } //else
  } //foreach theserows
} //if questionscount > 0

// Now I´ll add a hack to add the questions before as option
// if they are date type

//JAVASCRIPT TO SHOW MATCHING ANSWERS TO SELECTED QUESTION
$conditionsoutput .= "<script type='text/javascript'>\n"
."<!--\n"
."\tvar Fieldnames = new Array();\n"
."\tvar Codes = new Array();\n"
."\tvar Answers = new Array();\n"
."\tvar QFieldnames = new Array();\n"
."\tvar Qcqids = new Array();\n";
$jn=0;
if (isset($canswers))
{
	foreach($canswers as $can)
	{
		$an=str_replace("'", "`", $can[2]);
		$an=str_replace("\r", " ", $an);
		$an=str_replace("\n", " ", $an);
		$conditionsoutput .= "\t\tFieldnames[$jn]='$can[0]';\n"
		."\t\tCodes[$jn]='$can[1]';\n"
		."\t\tAnswers[$jn]='$an';\n";
		$jn++;
	}
}
$jn=0;

if (isset($cquestions))
{
	foreach ($cquestions as $cqn)
	{
		$conditionsoutput .= "\t\tQFieldnames[$jn]='$cqn[3]';\n"
		."\t\tQcqids[$jn]='$cqn[1]';\n";
		$jn++;
	}
}
$conditionsoutput .= "\n"
."\tfunction clearAnswers()\n"
."\t\t{\n"
."\t\t\tfor (var i=document.getElementById('canswers').options.length-1; i>=0; i--)\n"
."\t\t\t\t{\n";
//$conditionsoutput .= "alert(i);\n";
$conditionsoutput .= "\t\t\t\t\tdocument.getElementById('canswers').options[i] = null;\n"
."\t\t\t\t}\n"
."\t\t}\n";

$conditionsoutput .= "\tfunction getAnswers(fname)\n"
."\t\t{\n";
//$conditionsoutput .= "\t\talert(getElementById('canswers').options.length)\n";
//$conditionsoutput .= "\t\t\t{\n";
$conditionsoutput .= "\t\t\tfor (var i=document.getElementById('canswers').options.length-1; i>=0; i--)\n"
."\t\t\t\t{\n";
//$conditionsoutput .= "alert(i);\n";
$conditionsoutput .= "\t\t\t\t\tdocument.getElementById('canswers').options[i] = null;\n"
."\t\t\t\t}\n";
//$conditionsoutput .= "\t\t\t}\n";
//$conditionsoutput .= "\t\t\talert(fname);\n";
$conditionsoutput .= "\t\t\tvar Keys = new Array();\n"
."\t\t\tfor (var i=0;i<Fieldnames.length;i++)\n"
."\t\t\t\t{\n"
."\t\t\t\tif (Fieldnames[i] == fname)\n"
."\t\t\t\t\t{\n"
."\t\t\t\t\tKeys[Keys.length]=i;\n"
."\t\t\t\t\t}\n"
."\t\t\t\t}\n"
."\t\t\tfor (var i=0;i<QFieldnames.length;i++)\n"
."\t\t\t\t{\n"
."\t\t\t\tif (QFieldnames[i] == fname)\n"
."\t\t\t\t\t{\n"
."\t\t\t\t\tdocument.getElementById('cqid').value=Qcqids[i];\n"
."\t\t\t\t\t}\n"
."\t\t\t\t}\n";
//$conditionsoutput .= "\t\t\talert(Keys.length);\n";
$conditionsoutput .= "\t\t\tfor (var i=0;i<Keys.length;i++)\n"
."\t\t\t\t{\n";
//$conditionsoutput .= "\t\t\t\talert(Answers[Keys[i]]);\n";
$conditionsoutput .= "\t\t\t\tdocument.getElementById('canswers').options[document.getElementById('canswers').options.length] = new Option(Answers[Keys[i]], Codes[Keys[i]]);\n"
."\t\t\t\t}\n"
."\t\t}\n"
."//-->\n"
."</script>\n";

//SHOW FORM TO CREATE IT!
$conditionsoutput .= "<table width='100%' align='center' cellspacing='0' cellpadding='0' style='border-style: solid; border-width: 1; border-color: #555555'>\n"
."\t<tr bgcolor='#E1FFE1'>\n"
."\t\t<td  align='center' >\n";
$showreplace="$questiontitle". showSpeaker($questiontext);
$onlyshow=str_replace("{QID}", $showreplace, $clang->gT("Only show question {QID} IF"));
$conditionsoutput .= "\t\t\t<strong>$onlyshow</strong>\n"
."\t\t</td>\n"
."\t</tr>\n";

//3: Get other conditions currently set for this question
$query = "SELECT {$dbprefix}conditions.cid, "
               ."{$dbprefix}conditions.cqid, "
               ."{$dbprefix}conditions.cfieldname, "
               ."{$dbprefix}conditions.method, "
               ."{$dbprefix}conditions.value, "
               ."{$dbprefix}questions.type "
          ."FROM {$dbprefix}conditions, "
               ."{$dbprefix}questions "
         ."WHERE {$dbprefix}conditions.cqid={$dbprefix}questions.qid "
           ."AND {$dbprefix}questions.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
           ."AND {$dbprefix}conditions.qid=$qid\n"
      ."ORDER BY {$dbprefix}conditions.cfieldname";
$result = db_execute_assoc($query) or die ("Couldn't get other conditions for question $qid<br />$query<br />".$connect->ErrorMsg());
$conditionscount=$result->RecordCount();

// this array will be used soon,
// to explain wich conditions is used to evaluate the question
$method = array( "<"  => $clang->gT("Less"),
                 "<=" => $clang->gT("Less or Equal"),
                 "==" => $clang->gT("Equal"),
                 "!=" => $clang->gT("Not Equal"),
                 ">=" => $clang->gT("Greater or Equal"),
                 ">"  => $clang->gT("Greater"),
                 "RX" => $clang->gT("Regular Expression")
                );

if ($conditionscount > 0)
{
	while ($rows=$result->FetchRow())
	{
		if (is_null(array_search($rows['cid'], $markcidarray)) || // PHP4
			array_search($rows['cid'], $markcidarray) === FALSE) // PHP5
			// === required cause key 0 would otherwise be interpreted as FALSE
		{
			$markcidstyle="";
		}
		else {
			$markcidstyle="background-color: #5670A1;";
		}

		if (isset($currentfield) && $currentfield != $rows['cfieldname'])
		{
			$conditionsoutput .= "\t\t\t\t<tr class='evenrow'>\n"
			."\t\t\t\t\t<td valign='middle' align='center'>\n"
			."<font size='1'><strong>"
			.$clang->gT("and")."</strong></font>";
		}
		elseif (isset($currentfield))
		{
			$conditionsoutput .= "\t\t\t\t<tr class='evenrow'>\n"
			."\t\t\t\t\t<td valign='top' align='center'>\n"
			."<font size='1'><strong>"
			.$clang->gT("OR")."</strong></font>";
		}
		$conditionsoutput .= "\t<tr class='oddrow' style='$markcidstyle'>\n"
		                  ."\t<td><form style='margin-bottom:0;' name='del{$rows['cid']}' id='del{$rows['cid']}' method='post' action='$scriptname?action=conditions'>\n"
                          ."\t\t<table width='100%' style='height: 13px;' cellspacing='0' cellpadding='0'>\n"
                          ."\t\t\t<tr>\n"
                          ."\t\t\t\t<td valign='middle' align='right' width='50%'>\n"
                          ."\t\t\t\t\t<font size='1' face='verdana'>\n";
		//BUILD FIELDNAME?
		foreach ($cquestions as $cqn)
		{
			if ($cqn[3] == $rows['cfieldname'])
			{
				$conditionsoutput .= "\t\t\t$cqn[0] (qid{$rows['cqid']})\n";
				$conditionsList[]=array("cid"=>$rows['cid'],
				"text"=>$cqn[0]." ({$rows['value']})");
			}
			else
			{
				//$conditionsoutput .= "\t\t\t<font color='red'>ERROR: Delete this condition. It is out of order.</font>\n";
			}
		}

        $conditionsoutput .= "\t\t\t\t\t</font></td>\n"
                          ."\t\t\t\t\t<td align='center' valign='middle' width='15%'>\n"
                          ."\t\t\t\t\t\t<font size='1'>\n" //    .$clang->gT("Equals")."</font></td>"
                          .$method[$rows['method']]
                          ."\t\t\t\t\t\t</font>\n"
                          ."\t\t\t\t\t</td>\n"
                          ."\n"
		                  ."\t\t\t\t\t<td align='left' valign='middle' width='30%'>\n"
		                  ."\t\t\t\t\t\t<font size='1' face='verdana'>\n";
        // Here will be searched the conditional answer for this question
        // this conditional part is the labeled one
        // But there is another kind of condition
        // the specified in ValOrRegEx and is in $rows['value']
        $bHasAnswer = false;
	    foreach ($canswers as $can)
	    {
	        //$conditionsoutput .= $rows['cfieldname'] . "- $can[0]<br />";
		    //$conditionsoutput .= $can[1];
            if ($can[0] == $rows['cfieldname'] && $can[1] == $rows['value'])
		    {
                $conditionsoutput .= "\t\t\t\t\t\t$can[2] ($can[1])\n";
                $bHasAnswer = true;
            }
	    }
        if (!$bHasAnswer)
        {
            if ($rows['value'] == ' ' ||
                $rows['value'] == '')
            {
                $conditionsoutput .= "\t\t\t\t\t\t".$clang->gT("No Answer")."\n";
            } 
            else
            {
                $conditionsoutput .= "\t\t\t\t\t\t".$rows['value']."\n";
            }
	    }
	    $conditionsoutput .= "\t\t\t\t\t</font></td>\n"
	                      ."\t\t\t\t\t<td align='right' valign='middle' >\n"
	                      ."\t\t\t\t\t\t<input type='submit' value='".$clang->gT("Delete")."' style='font-family: verdana; font-size: 8; height:15' />\n"
		                  ."\t\t\t\t\t<input type='hidden' name='subaction' value='delete' />\n"
		                  ."\t\t\t\t\t<input type='hidden' name='cid' value='{$rows['cid']}' />\n"
		                  ."\t\t\t\t\t<input type='hidden' name='sid' value='$surveyid' />\n"
		                  ."\t\t\t\t\t<input type='hidden' name='qid' value='$qid' />\n"
		                  ."\t\t\t\t\t</td>\n"
		                  ."\t</table></form>\n"
		                  ."\t</tr>\n";
		$currentfield=$rows['cfieldname'];
	}
	$conditionsoutput .= "\t<tr>\n"
	                  ."\t\t<td height='3'>\n"
	                  ."\t\t</td>\n"
	                  ."\t</tr>\n";
}
else
{
	$conditionsoutput .= "\t<tr>\n"
	."\t\t<td colspan='3' height='3'>\n"
	."\t\t</td>\n"
	."\t</tr>\n";
}

$conditionsoutput .= "\t<tr bgcolor='#555555'><td colspan='3'></td></tr>\n";

if ($conditionscount > 0 && isset($postquestionscount) && $postquestionscount > 0)
{
	$conditionsoutput .= "<tr class='table2columns''><td colspan='3'><form action='$scriptname?action=conditions' name='copyconditions' id='copyconditions' method='post'>\n";

	$conditionsoutput .= "\t<table width='100%' ><tr>\n"
	."\t\t<td colspan='3' align='center' class='settingcaption'>\n"
	."\t\t<strong>"
	.$clang->gT("Copy Conditions")."</strong>\n"
	."\t\t</td>\n"
	."\t</tr>\n";

	$conditionsoutput .= "\t<tr bgcolor='#EFEFEF'>\n"
	."\t\t<th>".$clang->gT("Condition")."</th><th></th><th>".$clang->gT("Question")."</th>\n"
	."\t</tr>\n";

	$conditionsoutput .= "\t<tr>\n"
	."\t\t<td align='center'>\n"
	."\t\t<select name='copyconditionsfrom[]' multiple style='font-family:verdana; font-size:10; width:220; background-color: #E1FFE1' size='4' >\n";
	foreach ($conditionsList as $cl)
	{
		$conditionsoutput .= "<option value='".$cl['cid']."'>".$cl['text']."</option>\n";
	}
	$conditionsoutput .= "\t\t</select>\n"
	."\t\t</td>\n"
	."\t\t<td align='center'>\n"
	."\t\t".$clang->gT("copy to")."\n"
	."\t\t</td>\n"
	."\t\t<td align='center'>\n"
	."\t\t<select name='copyconditionsto[]' multiple style='font-family:verdana; font-size:10; width:220' size='4'>\n";
	foreach ($pquestions as $pq)
	{
		$conditionsoutput .= "<option value='{$pq['fieldname']}'>".$pq['text']."</option>\n";
	}
	$conditionsoutput .= "\t\t</select>\n";
	$conditionsoutput .= "\t\t</td>\n"
	."\t</tr>\n";

	$conditionsoutput .= "\t<tr><td colspan='3' align='center'>\n"
	."<input type='submit' value='".$clang->gT("Copy Conditions")."' onclick=\"return confirm('".$clang->gT("Are you sure you want to copy these condition(s) to the questions you have selected?","js")."')\" />"
	."\t\t\n";

	$conditionsoutput .= "<input type='hidden' name='subaction' value='copyconditions' />\n"
	."<input type='hidden' name='sid' value='$surveyid' />\n"
	."<input type='hidden' name='qid' value='$qid' />\n"
	."</td></tr></table></form>\n";

	$conditionsoutput .= "\t<tr ><td colspan='3'></td></tr>\n"
	."\t<tr bgcolor='#555555'><td colspan='3'></td></tr>\n";
}

$conditionsoutput .= "</table>\n";
$conditionsoutput .= "<form action='$scriptname?action=conditions' name='addconditions' id='addconditions' method='post'>\n";
$conditionsoutput .= "<table width='100%' border='0' >";
$conditionsoutput .= "\t<tr class='settingcaption'>\n"
."\t\t<td colspan='3' align='center'>\n"
."\t\t\t<strong>".$clang->gT("Add Condition")."</strong>\n"
."\t\t</td>\n"
."\t</tr>\n"
."\t<tr bgcolor='#EFEFEF'>\n"
."\t\t<th width='40%'>\n"
."\t\t\t<strong>".$clang->gT("Question")."</strong>\n"
."\t\t</th>\n"
."\t\t<th width='20%'>\n"
."\t\t</th>\n"
."\t\t<th width='40%'>\n"
."\t\t\t<strong>".$clang->gT("Answer")."</strong>\n"
."\t\t</th>\n"
."\t</tr>\n"
."\t<tr>\n"
."\t\t<td valign='top' align='center'>\n"
."\t\t\t<select onclick=\"getAnswers(this.options[this.selectedIndex].value)\" name='cquestions' id='cquestions' style='font-family:verdana; font-size:10; width:220' size='5'>\n";
if (isset($cquestions))
{
	foreach ($cquestions as $cqn)
	{
		$conditionsoutput .= "\t\t\t\t<option value='$cqn[3]'";
		if (isset($_POST['cquestions']) && $cqn[3] == $_POST['cquestions']) {
			$conditionsoutput .= " selected";
		}
		$conditionsoutput .= ">$cqn[0]</option>\n";
	}
}
$conditionsoutput .= "\t\t\t</select>\n"
."\t\t</td>\n"
."\t\t<td align='center'>\n";
// Originally was planned to do that:
//$conditionsoutput .= "\t\t\t<select name='method' id='method' style='font-family:verdana; font-size:10'>\n";
//$conditionsoutput .= "\t\t\t\t<option value='='>Equals</option>\n";
//$conditionsoutput .= "\t\t\t\t<option value='!'>Does not equal</option>\n";
//$conditionsoutput .= "\t\t\t</select>\n";
// ----------------------------------------
// Perhaps was leaved for this time with
//$conditionsoutput .= "\t\t\t".$clang->gT("Equals")."\n"
// Here I go
$conditionsoutput .= "\t\t\t".$clang->gT("NOTE: If select a label it will be used Equal or Not Equal for any other method")."\n";
$conditionsoutput .= "\t\t\t<select name='method' id='method' style='font-family:verdana; font-size:10'>\n";
// This is not really necessary. The note beffore must be self explanatory.
//$conditionsoutput .= "<option selected='selected'>".$clang->gT("Select Condition")."</option>\n";
$conditionsoutput .= "<option value='<'>".$clang->gT("Less")."</option>\n";
$conditionsoutput .= "<option value='<='>".$clang->gT("Less or Equal")."</option>\n";
$conditionsoutput .= "<option selected='selected' value='=='>".$clang->gT("Equal")."</option>\n";
$conditionsoutput .= "<option value='!='>".$clang->gT("Not Equal")."</option>\n";
$conditionsoutput .= "<option value='>='>".$clang->gT("Greater or Equal")."</option>\n";
$conditionsoutput .= "<option value='>'>".$clang->gT("Greater")."</option>\n";
$conditionsoutput .= "<option value='RX'>".$clang->gT("Regular Expression")."</option>\n";
$conditionsoutput .= "\t\t\t</select>\n"
."\t\t</td>\n"
."\t\t<td valign='top' align='center'>\n"
."\t\t\t<select name='canswers[]' multiple id='canswers' style='font-family:verdana; font-size:10; width:220' size='5'>\n";

$conditionsoutput .= "\t\t\t</select><br />\n"
."\t\t".$clang->gT("Constant Value or Regular Expression")."<br />\n"
."\t\t<textarea name='ValOrRegEx' cols='50' rows='5'></textarea>\n"
."\t\t</td>"
."\t</tr>\n"
."\t<tr>\n"
."\t\t<td colspan='3' align='center'>\n"
."\t\t\t<input type='reset' value='".$clang->gT("Clear")."' onclick=\"clearAnswers()\" />\n"
."\t\t\t<input type='submit' value='".$clang->gT("Add Condition")."' />\n"
."<input type='hidden' name='sid' value='$surveyid' />\n"
."<input type='hidden' name='qid' value='$qid' />\n"
."<input type='hidden' name='subaction' value='insertcondition' />\n"
."<input type='hidden' name='cqid' id='cqid' value='' />\n"
."\t\t</td>\n"
."\t</tr>\n"
."</table>\n"
."</form>\n"
."<table width='100%'  border='0'>\n";
$conditionsoutput .= "\t<tr><td colspan='3'></td></tr>\n"
."\t<tr bgcolor='#555555'>\n"
."\t\t<td height='5' colspan='3'>\n"
."\t\t</td>\n";
$conditionsoutput .= "\t<tr bgcolor='#CDCDCD'><td colspan=3 height='10'></td></tr>\n"
."\t\t<tr><td colspan='3' align='center'>\n"
."\t\t\t<input type='submit' value='".$clang->gT("Close Window")."' onclick=\"window.close()\"  />\n"
."\t\t</td>\n"
."\t</tr>\n";
$conditionsoutput .= "\t<tr><td colspan='3'></td></tr>\n"
."</table><br />&nbsp;\n";

function showSpeaker($hinttext)
{
  global $imagefiles, $clang;
  $reshtml= "<img src='$imagefiles/speaker.png' align='bottom' alt='$hinttext' title='$hinttext' "
           ." onclick=\"alert('".$clang->gT("Question","js").": $hinttext')\" />";
  return $reshtml; 
}


?>
