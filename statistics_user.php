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
* 
*/

/*
 * Created 12-2008 by Maziminke (maziminke@web.de)
 * 
 * This file handles the "Show results to users" option:
 * Survey Settings -> Presentation & navigation -> "Public statistics?"
 * 
 * The admin can set a question attribute "public_statistics" for each question
 * to determine whether the results of a certain question should be shown to the user
 * after he/she has submitted the survey.
 * 
 * See http://docs.limesurvey.org/tiki-index.php?page=Question+attributes#public_statistics
 */

//don't call this script directly!
if (isset($_REQUEST['homedir'])) {die('You cannot start this script directly');}


require_once(dirname(__FILE__).'/admin/classes/core/class.progressbar.php');
require_once(dirname(__FILE__).'/classes/core/startup.php');  
require_once(dirname(__FILE__).'/config-defaults.php');
require_once(dirname(__FILE__).'/common.php');
require_once(dirname(__FILE__).'/classes/core/language.php');
require_once(dirname(__FILE__).'/classes/core/html_entity_decode_php4.php');


//XXX enable/disable this for testing
//$publicgraphs = 1;
//$showaggregateddata = 1;

/*
 * List of important settings:
 * - publicstatistics: General survey setting which determines if public statistics for this survey 
 * 	 should be shown at all.
 * 
 * - publicgraphs: General survey setting which determines if public statistics for this survey 
 * 	 should include graphs or only show a tabular overview.
 * 
 * - public_statistics: Question attribute which has to be applied to each question so that 
 * 	 its statistics will be shown to the user. If not set no statistics for this question will be shown.
 * 
 * - filterout_incomplete_answers: Setting taken from config-defaults.php which determines if 
 * 	 not completed answers will be filtered.
 */

$surveyid=returnglobal('sid');
if (!$surveyid){
        //This next line ensures that the $surveyid value is never anything but a number.
        safe_die('You have to provide a valid survey ID.');
     }

     
if ($surveyid)
{
    $actquery="SELECT * FROM ".db_table_name('surveys')." WHERE sid=$surveyid and active='Y'";
    $actresult=db_execute_assoc($actquery) or safe_die ("Couldn't access survey settings<br />$query<br />".$connect->ErrorMsg());      //Checked
    if ($actresult->RecordCount() == 0) { safe_die('You have to provide a valid survey ID.'); }
    else 
    {
        $surveyinfo=getSurveyInfo($surveyid);
       if ($surveyinfo['publicstatistics']!='Y')
       {
          safe_die('The public statistics for this survey are deactivated.'); 
       }
       
       //check if graphs should be shown for this survey
       if ($surveyinfo['publicgraphs']=='Y')
       {
          $publicgraphs = 1;
       }
    }
}


     
//DEFAULT SETTINGS FOR TEMPLATES
if (!$publicdir) {$publicdir=".";}
$tpldir="$publicdir/templates";



//we collect all the output within this variable
$statisticsoutput ='';


//for creating graphs we need some more scripts which are included here
//True -> include
//False -> forget about charts
if (isset($publicgraphs) && $publicgraphs == 1) 
{
    require_once('classes/pchart/pchart/pChart.class');
    require_once('classes/pchart/pchart/pData.class');

	//$currentuser is created as prefix for pchart files
	if (isset($_SERVER['REDIRECT_REMOTE_USER']))
	{
		$currentuser=$_SERVER['REDIRECT_REMOTE_USER'];
	}
	elseif (session_id())
	{
		$currentuser=substr(session_id(), 0, 15);
	}
	else
	{
		$currentuser="standard";
	}
}


// Set language for questions and labels to base language of this survey
$language = GetBaseLanguageFromSurveyID($surveyid);

//set survey language for translations
$clang = SetSurveyLanguage($surveyid, $language);


//Delete any stats files from the temp directory that aren't from today.
deleteNotPattern($tempdir, "STATS_*.png","STATS_".date("d")."*.png");


/*
 * only show questions where question attribute "public_statistics" is set to "1"
 */
$query = "SELECT ".db_table_name("questions").".*, group_name, group_order\n"
."FROM ".db_table_name("questions").", ".db_table_name("groups").", ".db_table_name("survey_$surveyid").", ".db_table_name("question_attributes")."\n"
."WHERE ".db_table_name("groups").".gid=".db_table_name("questions").".gid\n"
."AND ".db_table_name("groups").".language='".$language."'\n"
."AND ".db_table_name("questions").".language='".$language."'\n"
."AND ".db_table_name("questions").".sid=$surveyid\n"
."AND ".db_table_name("questions").".qid=".db_table_name("question_attributes").".qid\n"
."AND ".db_table_name("question_attributes").".attribute='public_statistics'\n"
."AND ".db_table_name("question_attributes").".value='1'\n";

//check filter setting in config file
if ($filterout_incomplete_answers == true) 
{
	$query .= " AND ".db_table_name("survey_$surveyid").".submitdate is not null";
}
	
//execute query
$result = db_execute_assoc($query) or safe_die("Couldn't do it!<br />$query<br />".$connect->ErrorMsg());

//store all the data in $rows
$rows = $result->GetRows();


//SORT IN NATURAL ORDER!
usort($rows, 'CompareGroupThenTitle');


//number of records for this survey
$totalrecords = 0;

//count number of answers
$query = "SELECT count(*) FROM ".db_table_name("survey_$surveyid");

//if incompleted answers should be filtert submitdate has to be not null
//this setting is taken from config-defaults.php
if ($filterout_incomplete_answers == true) 
{
	$query .= " WHERE ".db_table_name("survey_$surveyid").".submitdate is not null";
}
$result = db_execute_num($query) or safe_die ("Couldn't get total<br />$query<br />".$connect->ErrorMsg());

//$totalrecords = total number of answers
while ($row=$result->FetchRow()) 
{
	$totalrecords=$row[0];
}


//this is the array which we need later...
$summary = array();
//...while this is the array from copy/paste which we don't want to replace because this is a nasty source of error
$allfields = array();


		//---------- CREATE SGQA OF ALL QUESTIONS WHICH USE "PUBLIC_STATISTICS" ----------

//put the question information into the filter array
foreach ($rows as $row)
{
	//store some column names in $filters array
	$filters[]=array(
	$row['qid'],
	$row['gid'],
	$row['type'],
	$row['title'],
	$row['group_name'],
	FlattenText($row['question']),
	$row['lid'],
    $row['lid1']);
    
    
    //to use the copy/pasted code from /admin/statistics.php some files have to be renamed
    $flt[0] = $row['qid'];    
    $flt[6] = $row['lid'];
    $flt[7] = $row['lid1'];   
    
    
    //$myfield normally looks like the SGQ identifier
    $myfield = "{$surveyid}X{$row['gid']}X{$row['qid']}";
  
    
    //switch through the different question types to create a valid SGQ(A) identifier    
    switch($row['type'])
	{	
		/*
		 * keep in mind that some question types are ignored at this script:
		 * "D": // Date
		 * "Q": // Multiple Short Text
		 * "S": // Short free text
		 * "T": // Long free text
		 * "U": // Huge free text
		 * "I": // Language
		 * ";":  //ARRAY (Multi Flex) (Text)
		 * "X": //Boilerplate question
		 * ...
		 * there might be some more, check the docs for details:
		 * http://docs.limesurvey.org/tiki-index.php?page=Question+attributes#public_statistics
		 */
		
		
		
		//----------- MULTIPLE OPTIONS -------------
		
		case "K": // Multiple Numerical
			
		//get answers
		$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language = '{$language}' ORDER BY sortorder, answer";
		$result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
		
		//go through all the (multiple) answers
		while ($row=$result->FetchRow())
		{
			//mark this question type by adding a special character to the beginning
			//...and adding the answer code
		    $myfield1="K".$myfield.$row[0];

			//add fields to array which contains all fields names
			$allfields[]=$myfield1;
		}
		break;


		
		case "M": //Multiple Options
		case "P": //Multiple Options with Comments
		
		//mark this question type by adding a special character to the beginning
		$myfield = "M$myfield";
		
		//put field names into array
		$allfields[]=$myfield;
		
		break;
		
		
		
		case "N": // Numerical
		
		//mark this question type by adding a special character to the beginning
		$myfield = "N$myfield";
		
		//put field names into array
		$allfields[]=$myfield;
		
		break;
		
		
		
		//----------------------- ARRAYS --------------------------
		
		case "A": // ARRAY OF 5 POINT CHOICE QUESTIONS			
		
		//get answers
		$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
		$result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
		
		//check all the results
		while ($row=$result->FetchRow())
		{
			//array = multiple options -> add the answer code
			$myfield2 = $myfield.$row[0];
			
			//add this to all the other fields
			$allfields[]=$myfield2;
		}
		
		break;
		
		
		
		//just like above only a different loop
		case "B": // ARRAY OF 10 POINT CHOICE QUESTIONS
		$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
		$result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());

		while ($row=$result->FetchRow())
		{
			//array = multiple options -> add the answer code
			$myfield2 = $myfield . "$row[0]";			
			$allfields[]=$myfield2;
		}
		
		break;
		
		
		
		case "C": // ARRAY OF YES\No\$clang->gT("Uncertain") QUESTIONS
		$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
		
		//get answers
		$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
		$result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
		
		//loop answers
		while ($row=$result->FetchRow())
		{			
			//array = multiple options -> add the answer code
			$myfield2 = $myfield . "$row[0]";
			
			//add to array
			$allfields[]=$myfield2;
		}
		
		break;
		
		
		
		//similiar to the above one
		case "E": // ARRAY OF Increase/Same/Decrease QUESTIONS
		
		$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
		$result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
		
		while ($row=$result->FetchRow())
		{
			//array = multiple options -> add the answer code
			$myfield2 = $myfield . "$row[0]";
			
			//add to array
			$allfields[]=$myfield2;		
		}
		
		break;

		
		
		/*
		 * For question type "F" and "H" you can use labels. 
		 * The only difference is that the labels are applied to column heading 
		 * or rows respectively
		 */
		case "F": // ARRAY OF Flexible QUESTIONS
		case "H": // ARRAY OF Flexible Questions (By Column)
		
		//Get answers. We always use the answer code because the label might be too long elsewise 
		$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
		$result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
		$counter2=0;
		
		//check all the answers
		while ($row=$result->FetchRow())
		{
			//array = multiple options -> add the answer code
			$myfield2 = $myfield . "$row[0]";
			
			//add fields to main array
			$allfields[]=$myfield2;
		}
		
		break;
		
		
		
		//----------------------- SPECIAL STUFF --------------------------
		
		case "R": //RANKING
		
		//get some answers
		$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
		$result = db_execute_assoc($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
		
		//get number of answers
		$count = $result->RecordCount();
		
		//lets put the answer code and text into the answers array
		while ($row = $result->FetchRow())
		{
			$answers[]=array($row['code'], $row['answer']);
		}
		
		
		//loop through all answers. if there are 3 items to rate there will be 3 statistics
		for ($i=1; $i<=$count; $i++)
		{
			//ranking questions use a special SGQA!
			$myfield2 = "R" . $myfield . $i . "-" . strlen($i);
			
			//add averything to main array
			$allfields[]=$myfield2;
		}
				
		unset($answers);
		
		break;		
		
		
		
        case "1": // MULTI SCALE
        $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
                
        //get answers
        $query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
        $result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
                        
        //loop through answers
         while ($row=$result->FetchRow())
         {
         	
         	//----------------- LABEL 1 ---------------------
          	//dual scale questions use a special SGQA!
            $myfield2 = $myfield . "$row[0]#0";
                
             //check if there is a dualscale_headerA/B
            $dshquery = "SELECT value FROM ".db_table_name("question_attributes")." WHERE qid={$flt[0]} AND attribute='dualscale_headerA'";
            $dshresult = db_execute_num($dshquery) or safe_die ("Couldn't get dualscale header!<br />$dshquery<br />".$connect->ErrorMsg());
         
            //get header
            while($dshrow=$dshresult->FetchRow())
            {
            	$dualscaleheadera = $dshrow[0];
            }
            
            if(isset($dualscaleheadera) && $dualscaleheadera != "")
            {
            	$labeltitle = $dualscaleheadera;
            }
            else
            {
	            //get label text
	            $lquery = "SELECT label_name FROM ".db_table_name("labelsets")." WHERE lid={$flt[6]}";
	            $lresult = db_execute_num($lquery) or safe_die ("Couldn't get label title!<br />$lquery<br />".$connect->ErrorMsg());
	         
	            //get title
	            while ($lrow=$lresult->FetchRow())
	            {
	            	$labeltitle = $lrow[0];
	            }
            }
                
            //add averything to main array
            $allfields[]=$myfield2;  

                
                
                
             //----------------- LABEL 2 ---------------------           
             
            //dual scale questions use a special SGQA!
            $myfield2 = $myfield . "$row[0]#1";
              
            
            //check if there is a dualsclae_headerA/B
            $dshquery2 = "SELECT value FROM ".db_table_name("question_attributes")." WHERE qid={$flt[0]} AND attribute='dualscale_headerB'";
            $dshresult2 = db_execute_num($dshquery2) or safe_die ("Couldn't get dualscale header!<br />$dshquery2<br />".$connect->ErrorMsg());
         
            //get header
            while($dshrow2=$dshresult2->FetchRow())
            {
            	$dualscaleheaderb = $dshrow2[0];
            }
            
            if(isset($dualscaleheaderb) && $dualscaleheaderb != "")
            {
            	$labeltitle2 = $dualscaleheaderb;
            }
            else
            {
	            //get label text
	            $lquery2 = "SELECT label_name FROM ".db_table_name("labelsets")." WHERE lid={$flt[7]}";
	            $lresult2 = db_execute_num($lquery2) or safe_die ("Couldn't get label title!<br />$lquery2<br />".$connect->ErrorMsg());
	         
	            //get title
	            while($lrow2=$lresult2->FetchRow())
	            {
	            	$labeltitle2 = $lrow2[0];
	            }
            }

            //add averything to main array
            $allfields[]=$myfield2;
            
        }	//end WHILE -> loop through all answers of dual scale question
                   
        break;
        

        
        case ":":  //ARRAY (Multi Flex) (Numbers)
		$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
		$result = db_execute_num($query) or die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
		$counter2=0;
		
		//Get qidattributes for this question
    	$qidattributes=getQuestionAttributes($flt[0]);
    	
    	if ($maxvalue=arraySearchByKey("multiflexible_max", $qidattributes, "attribute", 1)) {
    		$maxvalue=$maxvalue['value'];
    	} 
    	else {
    		$maxvalue=10;
    	}
    	
    	if ($minvalue=arraySearchByKey("multiflexible_min", $qidattributes, "attribute", 1)) {
    		$minvalue=$minvalue['value'];
    	} 
    	else {
    		$minvalue=1;
    	}
    	
    	if ($stepvalue=arraySearchByKey("multiflexible_step", $qidattributes, "attribute", 1)) {
    		$stepvalue=$stepvalue['value'];
    	} 
    	else {
    		$stepvalue=1;
    	}
    	
    	if (arraySearchByKey("multiflexible_checkbox", $qidattributes, "attribute", 1)) {
    		$minvalue=0;
    		$maxvalue=1;
    		$stepvalue=1;
    	}
    	
		while ($row=$result->FetchRow())
		{
			$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid={$flt[6]} AND language='{$language}' ORDER BY sortorder, code";
			$fresult = db_execute_assoc($fquery);
			
			while ($frow = $fresult->FetchRow())
			{
			    $myfield2 = $myfield . $row[0] . "_" . $frow['code'];
			    
			    //add averything to main array
				$allfields[]=$myfield2;
			}
		}
		break;
		
		
		//--------------- DEFAULT --------------
		default:
		
		$allfields[] = $myfield;
			
		break;	
		
	}	//end switch -> check question types and create SGQA

}	//end foreach -> loop through all questions with "public_statistics" enabled


//rename the array which contains all the data we need later
$summary = $allfields;




			//---------- CREATE STATISTICS ----------


//some progress bar stuff	

// Create progress bar which is shown while creating the results
$prb = new ProgressBar();
$prb->pedding = 2;	// Bar Pedding
$prb->brd_color = "#404040 #dfdfdf #dfdfdf #404040";	// Bar Border Color

$prb->setFrame();	// set ProgressBar Frame
$prb->frame['left'] = 50;	// Frame position from left
$prb->frame['top'] = 	80;	// Frame position from top
$prb->addLabel('text','txt1',$clang->gT("Please wait ..."));	// add Text as Label 'txt1' and value 'Please wait'
$prb->addLabel('percent','pct1');	// add Percent as Label 'pct1'
$prb->addButton('btn1',$clang->gT('Go back'),'?action=statistics&amp;sid='.$surveyid);	// add Button as Label 'btn1' and action '?restart=1'

//progress bar starts with 35%
$process_status = 35;
$prb->show();	// show the ProgressBar


// 1: Get list of questions with answers chosen
//"Getting Questions and Answers ..." is shown above the bar
$prb->setLabelValue('txt1',$clang->gT('Getting questions and answers ...'));
$prb->moveStep(5);

// creates array of post variable names
for (reset($_POST); $key=key($_POST); next($_POST)) 
{ 
	$postvars[]=$key;
}

//show some main data at the beginnung
$statisticsoutput .= "<br />\n<table align='center' width='95%' border='1'  "
."cellpadding='2' cellspacing='0' >\n"
."\t<tr><td colspan='2' align='center'><strong>"
.$clang->gT("Total records in survey").": $totalrecords</strong></td></tr>\n";

//close table
$statisticsoutput .= "</table><br />\n";


//push progress bar from 35 to 40
$process_status = 40;





//Show Summary results
if (isset($summary) && $summary)
{
	//"Generating Summaries ..." is shown above the progress bar
	$prb->setLabelValue('txt1',$clang->gT('Generating summaries ...'));
	$prb->moveStep($process_status);

	//check if pchart should be used
	if (isset($publicgraphs) && $publicgraphs == 1)  
	{
		//Delete any old temp image files
		deletePattern($tempdir, "STATS_".date("d")."X".$currentuser."X".$surveyid."X"."*.png");
	}

	//let's run through the survey // Fixed bug 3053 with array_unique
	$runthrough=array_unique($summary);
	
	//GET LIST OF LEGIT QIDs FOR TESTING LATER	
	$lq = "SELECT DISTINCT qid FROM ".db_table_name("questions")." WHERE sid=$surveyid"; 
	$lr = db_execute_assoc($lq);
	
	//loop through the IDs
	while ($lw = $lr->FetchRow())
	{
		//this creates an array of question id's'
		$legitqids[] = $lw['qid']; 
	}
	
	//loop through all selected questions
	foreach ($runthrough as $rt)
	{
		
		//update progress bar
		if ($process_status < 100) $process_status++;		
		$prb->moveStep($process_status);
		
		$firstletter = substr($rt, 0, 1);##
				
		//M - Multiple Options, therefore multiple fields
		if ($firstletter == "M") 
		{
			//get SGQ data
			list($qsid, $qgid, $qqid) = explode("X", substr($rt, 1, strlen($rt)), 3);
			
			//select details for this question
			$nquery = "SELECT title, type, question, lid, other FROM ".db_table_name("questions")." WHERE language='{$language}' and qid='$qqid'";
			$nresult = db_execute_num($nquery) or safe_die ("Couldn't get question<br />$nquery<br />".$connect->ErrorMsg());
			
			//loop through question data
			while ($nrow=$nresult->FetchRow())
			{
				$qtitle=$nrow[0];
				$qtype=$nrow[1];
				$qquestion=FlattenText($nrow[2]);
				$qlid=$nrow[3];
				$qother=$nrow[4];
			}

			//1. Get list of answers
			$query="SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qqid' AND language='{$language}' ORDER BY sortorder, answer";
			$result=db_execute_num($query) or safe_die("Couldn't get list of answers for multitype<br />$query<br />".$connect->ErrorMsg());
			
			//loop through multiple answers
			while ($row=$result->FetchRow())
			{
				$mfield=substr($rt, 1, strlen($rt))."$row[0]";
				
				//create an array containing answer code, answer and fieldname(??)
				$alist[]=array("$row[0]", "$row[1]", $mfield);
			}
			
			//check "other" field. is it set?
			if ($qother == "Y")
			{
				$mfield=substr($rt, 1, strlen($rt))."other";
				
				//create an array containing answer code, answer and fieldname(??)
				$alist[]=array($clang->gT("Other"), $clang->gT("Other"), $mfield);
			}
		}

		
		
		//RANKING OPTION THEREFORE CONFUSING
		elseif ($firstletter == "R") 
		{
			//getting the needed IDs somehow
			$lengthofnumeral=substr($rt, strpos($rt, "-")+1, 1);
			list($qsid, $qgid, $qqid) = explode("X", substr($rt, 1, strpos($rt, "-")-($lengthofnumeral+1)), 3);
			
			//get question data
			$nquery = "SELECT title, type, question FROM ".db_table_name("questions")." WHERE qid='$qqid' AND language='{$language}'";
			$nresult = db_execute_num($nquery) or safe_die ("Couldn't get question<br />$nquery<br />".$connect->ErrorMsg());
			
			//loop through question data
			while ($nrow=$nresult->FetchRow())
			{
				$qtitle=FlattenText($nrow[0]). " [".substr($rt, strpos($rt, "-")-($lengthofnumeral), $lengthofnumeral)."]";
				$qtype=$nrow[1];
				$qquestion=FlattenText($nrow[2]). "[".$clang->gT("Ranking")." ".substr($rt, strpos($rt, "-")-($lengthofnumeral), $lengthofnumeral)."]";
			}
			
			//get answers
			$query="SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qqid' AND language='{$language}' ORDER BY sortorder, answer";
			$result=db_execute_num($query) or safe_die("Couldn't get list of answers for multitype<br />$query<br />".$connect->ErrorMsg());
			
			//loop through answers
			while ($row=$result->FetchRow())
			{
				//create an array containing answer code, answer and fieldname(??)
				$mfield=substr($rt, 1, strpos($rt, "-")-1);
				$alist[]=array("$row[0]", "$row[1]", $mfield);
			}
		}	
		
		//N = numerical input
		//K = multiple numerical input
		elseif ($firstletter == "N" || $firstletter == "K") //NUMERICAL TYPE
		{
		    //Zero handling
		    if (!isset($excludezeros)) //If this hasn't been set, set it to on as default:
		    {
			    $excludezeros=1;
			}
			//create SGQ identifier
	        list($qsid, $qgid, $qqid) = explode("X", $rt, 3);
			
	        //multiple numerical input
		    if($firstletter == "K")
		    { 
		    	// This is a multiple numerical question so we need to strip of the answer id to find the question title
                $tmpqid=substr($qqid, 0, strlen($qqid)-1);
                    
                //did we get a valid ID?
                while (!in_array ($tmpqid,$legitqids)) 
                $tmpqid=substr($tmpqid, 0, strlen($tmpqid)-1); 
                    
                //check lenght of ID
                $qidlength=strlen($tmpqid);
                    
                //get answer ID from qid
                $qaid=substr($qqid, $qidlength, strlen($qqid)-$qidlength);
                   
                //get question details from DB
		        $nquery = "SELECT title, type, question, qid, lid 
						   FROM ".db_table_name("questions")." 
						   WHERE qid='".substr($qqid, 0, $qidlength)."' 
						   AND language='{$language}'";
		        $nresult = db_execute_num($nquery) or safe_die("Couldn't get text question<br />$nquery<br />".$connect->ErrorMsg());
			} 
			
			//probably question type "N" = numerical input
			else 
			{
				//we can use the qqid without any editing
			    $nquery = "SELECT title, type, question, qid, lid FROM ".db_table_name("questions")." WHERE qid='$qqid' AND language='{$language}'";
			    $nresult = db_execute_num($nquery) or safe_die ("Couldn't get question<br />$nquery<br />".$connect->ErrorMsg());
			}
			
			//loop through results
			while ($nrow=$nresult->FetchRow()) 
			{				
			    $qtitle=FlattenText($nrow[0]); //clean up title
				$qtype=$nrow[1]; 
				$qquestion=FlattenText($nrow[2]); //clean up question
				$qiqid=$nrow[3]; 
				$qlid=$nrow[4];
			}
			
			//Get answer texts for multiple numerical
			if(substr($rt, 0, 1) == "K")
			{
				//get answer data
			    $qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qiqid' AND code='$qaid' AND language='{$language}' ORDER BY sortorder, answer";
			    $qresult=db_execute_num($qquery) or safe_die ("Couldn't get answer details (Array 5p Q)<br />$qquery<br />".$connect->ErrorMsg());
			    
			    //handle answer
			    while ($qrow=$qresult->FetchRow())
		    	{
			    	$atext=$qrow[1];
			    }
			    //put single items in brackets at output
			    $qtitle .= " [$atext]";
			}
			
			//outputting headline
			$statisticsoutput .= "\n<table align='center' width='95%' border='1'  cellpadding='2' cellspacing='0' >\n"
			."\t<tr><td colspan='2' align='center'><strong>".$clang->gT("Field summary for")." $qtitle:</strong>"
			."</td></tr>\n"
			."\t<tr><td colspan='2' align='center'><strong>$qquestion</strong></td></tr>\n"
			."\t<tr>\n\t\t<td width='50%' align='center' ><strong>"
			.$clang->gT("Calculation")."</strong></td>\n"
			."\t\t<td width='50%' align='center' ><strong>"
			.$clang->gT("Result")."</strong></td>\n"
			."\t</tr>\n";
			
			//this field is queried using mathematical functions
			$fieldname=substr($rt, 1, strlen($rt));
			
			//special treatment for MS SQL databases
            if ($connect->databaseType == 'odbc_mssql' || $connect->databaseType == 'odbtp' || $connect->databaseType == 'mssql_n')
            { 
                //standard deviation
                $query = "SELECT STDEVP(".db_quote_id($fieldname)."*1) as stdev"; 
            }
                    
            //other databases (MySQL, Postgres)
            else
            { 
                //standard deviation
                $query = "SELECT STDDEV(".db_quote_id($fieldname).") as stdev"; 
            }

            //sum
			$query .= ", SUM(".db_quote_id($fieldname)."*1) as sum";
			
			//average
			$query .= ", AVG(".db_quote_id($fieldname)."*1) as average";
			
			//min
			$query .= ", MIN(".db_quote_id($fieldname)."*1) as minimum";
			
			//max
			$query .= ", MAX(".db_quote_id($fieldname)."*1) as maximum";

			//Only select responses where there is an actual number response, ignore nulls and empties (if these are included, they are treated as zeroes, and distort the deviation/mean calculations)
                
			//special treatment for MS SQL databases
			if ($connect->databaseType == 'odbc_mssql' || $connect->databaseType == 'odbtp' || $connect->databaseType == 'mssql_n')
                { 
            	    //no NULL/empty values please
            	    $query .= " FROM ".db_table_name("survey_$surveyid")." WHERE ".db_quote_id($fieldname)." IS NOT NULL";
				    if(!$excludezeros)
				    {
				        //NO ZERO VALUES
				        $query .= " AND (".db_quote_id($fieldname)." <> 0)"; 
                    }
                }
                
                //other databases (MySQL, Postgres)
                else
                { 
                	//no NULL/empty values please
                	$query .= " FROM ".db_table_name("survey_$surveyid")." WHERE ".db_quote_id($fieldname)." IS NOT NULL";
					if(!$excludezeros)
					{
					    //NO ZERO VALUES
					    $query .= " AND (".db_quote_id($fieldname)." != 0)";
					}
                }

			
			//filter incomplete answers?
			if ($filterout_incomplete_answers == true) 
			{
				$query .= " AND ".db_table_name("survey_$surveyid").".submitdate is not null";
			}
	
            //execute query
            $result=db_execute_assoc($query) or safe_die("Couldn't do maths testing<br />$query<br />".$connect->ErrorMsg());
			
            //get calculated data
            while ($row=$result->FetchRow())
			{
				//put translation of mean and calculated data into $showem array
				$showem[]=array($clang->gT("Sum"), $row['sum']);
				$showem[]=array($clang->gT("Standard deviation"), round($row['stdev'],2));
				$showem[]=array($clang->gT("Average"), round($row['average'],2));
				$showem[]=array($clang->gT("Minimum"), $row['minimum']);
				
				//Display the maximum and minimum figures after the quartiles for neatness
				$maximum=$row['maximum']; 
				$minimum=$row['minimum'];
			}
			
			//CALCULATE QUARTILES			
			//get data
			$query ="SELECT ".db_quote_id($fieldname)." FROM ".db_table_name("survey_$surveyid")." WHERE ".db_quote_id($fieldname)." IS NOT null";
			//NO ZEROES
			if(!$excludezeros)
			{
			    $query .= " AND ".db_quote_id($fieldname)." != 0";
			}
			
			//filter incomplete answers?
			if ($filterout_incomplete_answers == true) 
			{
				$query .= " AND ".db_table_name("survey_$surveyid").".submitdate is not null";
			}
			
			//execute query
			$result=$connect->Execute($query) or safe_die("Disaster during median calculation<br />$query<br />".$connect->ErrorMsg());
			
			$querystarter="SELECT ".db_quote_id($fieldname)." FROM ".db_table_name("survey_$surveyid")." WHERE ".db_quote_id($fieldname)." IS NOT null";
			//No Zeroes
			if(!$excludezeros)
			{
			    $querystart .= " AND ".db_quote_id($fieldname)." != 0";
			}
			
			//filter incomplete answers?
			if ($filterout_incomplete_answers == true) 
			{
				$querystarter .= " AND ".db_table_name("survey_$surveyid").".submitdate is not null";
			}
			//we just count the number of records returned
			$medcount=$result->RecordCount();

			//put the total number of records at the beginning of this array
			array_unshift($showem, array($clang->gT("Count"), $medcount));
			
			// Calculating only makes sense with more than one result
			if ($medcount>1)   
			{
				//1ST QUARTILE (Q1)
				$q1=(1/4)*($medcount+1);
				$q1b=(int)((1/4)*($medcount+1));
				$q1c=$q1b-1;
				$q1diff=$q1-$q1b;
				$total=0;
				
				// fix if there are too few values to evaluate.
				if ($q1c<1) {$q1c=1;$lastnumber=0;}  
				
				if ($q1 != $q1b)
				{
					//ODD NUMBER
					$query = $querystarter . " ORDER BY ".db_quote_id($fieldname)."*1 ";
					$result=db_select_limit_assoc($query, $q1c, 2) or safe_die("1st Quartile query failed<br />".$connect->ErrorMsg());
					
					while ($row=$result->FetchRow())
					{
						if ($total == 0)    {$total=$total-$row[$fieldname];}
						
						else                {$total=$total+$row[$fieldname];}
						
						$lastnumber=$row[$fieldname];
					}
					
					$q1total=$lastnumber-(1-($total*$q1diff));
					
					if ($q1total < $minimum) {$q1total=$minimum;}
					
					$showem[]=array($clang->gT("1st quartile (Q1)"), $q1total);
				}
				else
				{
					//EVEN NUMBER
					$query = $querystarter . " ORDER BY ".db_quote_id($fieldname)."*1 ";
					$result=db_select_limit_assoc($query,1, $q1c) or safe_die ("1st Quartile query failed<br />".$connect->ErrorMsg());
					
					while ($row=$result->FetchRow()) 
					{
						$showem[]=array("1st Quartile (Q1)", $row[$fieldname]);
					}
				}					
				
				$total=0;
				
				
				//MEDIAN (Q2)
				$median=(1/2)*($medcount+1);
				$medianb=(int)((1/2)*($medcount+1));
				$medianc=$medianb-1;
				$mediandiff=$median-$medianb;
				
				if ($median != (int)((($medcount+1)/2)-1))
				{
					//remainder
					$query = $querystarter . " ORDER BY ".db_quote_id($fieldname)."*1 ";
					$result=db_select_limit_assoc($query,2, $medianc) or safe_die("What a complete mess with the remainder<br />$query<br />".$connect->ErrorMsg());
					
					while
					(
						$row=$result->FetchRow()) {$total=$total+$row[$fieldname];
					}
					
					$showem[]=array($clang->gT("2nd quartile (Median)"), $total/2);
				}
				
				else
				{
					//EVEN NUMBER
					$query = $querystarter . " ORDER BY ".db_quote_id($fieldname)."*1 ";
					$result=db_select_limit_assoc($query,1, $medianc) or safe_die("What a complete mess<br />$query<br />".$connect->ErrorMsg());
					
					while ($row=$result->FetchRow()) 
					{
						$showem[]=array("Median Value", $row[$fieldname]);
					}
				}
				
				$total=0;				
				
				//3RD QUARTILE (Q3)
				$q3=(3/4)*($medcount+1);
				$q3b=(int)((3/4)*($medcount+1));
				$q3c=$q3b-1;
				$q3diff=$q3-$q3b;
				
				if ($q3 != $q3b)
				{
					$query = $querystarter . " ORDER BY ".db_quote_id($fieldname)."*1 ";
					$result = db_select_limit_assoc($query,2,$q3c) or safe_die("3rd Quartile query failed<br />".$connect->ErrorMsg());
					
					$lastnumber='';
					
					while ($row=$result->FetchRow())
					{
						if ($total == 0)    {$total=$total-$row[$fieldname];}
						
						else                {$total=$total+$row[$fieldname];}
						
						if (!$lastnumber) {$lastnumber=$row[$fieldname];}
					}
					$q3total=$lastnumber+($total*$q3diff);
					
					if ($q3total < $maximum) {$q1total=$maximum;}
					
					$showem[]=array($clang->gT("3rd quartile (Q3)"), $q3total);
				}
				
				else
				{
					$query = $querystarter . " ORDER BY ".db_quote_id($fieldname)."*1";
					$result = db_select_limit_assoc($query,1, $q3c) or safe_die("3rd Quartile even query failed<br />".$connect->ErrorMsg());
					
					while ($row=$result->FetchRow()) 
					{
						$showem[]=array("3rd Quartile (Q3)", $row[$fieldname]);
					}
				}
				
				$total=0;
				
				$showem[]=array($clang->gT("Maximum"), $maximum);
				
				//output results
				foreach ($showem as $shw)
				{
					$statisticsoutput .= "\t<tr>\n"
					."\t\t<td align='center' >$shw[0]</td>\n"
					."\t\t<td align='center' >$shw[1]</td>\n"
					."\t</tr>\n";
				}
				
				//footer of question type "N"
				$statisticsoutput .= "\t<tr>\n"
				."\t\t<td colspan='4' align='center' bgcolor='#EEEEEE'>\n"
				."\t\t\t<font size='1'>".$clang->gT("Null values are ignored in calculations")."<br />\n"
				."\t\t\t".sprintf($clang->gT("Q1 and Q3 calculated using %s"), "<a href='http://mathforum.org/library/drmath/view/60969.html' target='_blank'>".$clang->gT("minitab method")."</a>")
				."</font>\n"
				."\t\t</td>\n"
				."\t</tr>\n</table>\n";
				
				//clean up
				unset($showem);
				
			}	//end if (enough results?)
			
			//not enough (<1) results for calculation
			else
			{
				//output
				$statisticsoutput .= "\t<tr>\n"
				."\t\t<td align='center'  colspan='4'>".$clang->gT("Not enough values for calculation")."</td>\n"
				."\t</tr>\n</table><br />\n";
				unset($showem);
			}
				
			
		}	//end else-if -> multiple numerical types
		
		
		// NICE SIMPLE SINGLE OPTION ANSWERS
		else 
		{
			//get database fields for this survey
			$fieldmap=createFieldMap($surveyid, "full");
			
			//search for key
			$fielddata=arraySearchByKey($rt, $fieldmap, "fieldname", 1);
						
			//get SGQA IDs
			$qsid=$fielddata['sid'];
			$qgid=$fielddata['gid'];
			$qqid=$fielddata['qid'];
			$qanswer=$fielddata['aid'];
			
			//question type
            $qtype=$fielddata['type'];
            
            //question string
            $qastring=$fielddata['question'];
            
            //question ID
			$rqid=$qqid;
			
			//get question data
			$nquery = "SELECT title, type, question, qid, lid, lid1, other FROM ".db_table_name("questions")." WHERE qid='{$rqid}' AND language='{$language}'";
			$nresult = db_execute_num($nquery) or safe_die ("Couldn't get question<br />$nquery<br />".$connect->ErrorMsg());
			
			//loop though question data
			while ($nrow=$nresult->FetchRow())
			{
				$qtitle=FlattenText($nrow[0]);
				$qtype=$nrow[1];
				$qquestion=FlattenText($nrow[2]);
				$qiqid=$nrow[3];
				$qlid=$nrow[4];
                $qlid1=$nrow[5];
				$qother=$nrow[6];
			}
			
			//check question types
			switch($qtype)
			{
				//Array of 5 point choices (several items to rank!)
				case "A": 
				
				//get data
				$qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qiqid' AND code='$qanswer' AND language='{$language}' ORDER BY sortorder, answer";
				$qresult=db_execute_num($qquery) or safe_die ("Couldn't get answer details (Array 5p Q)<br />$qquery<br />".$connect->ErrorMsg());
				
				//loop through results
				while ($qrow=$qresult->FetchRow())
				{
					//5-point array
					for ($i=1; $i<=5; $i++)
					{
						//add data
						$alist[]=array("$i", "$i");
					}
					//add counter
					$atext=$qrow[1];
				}
				
				//list IDs and answer codes in brackets
				$qquestion .= "<br />\n[".$atext."]";
				$qtitle .= "($qanswer)";
				break;
				
				
				
				//Array of 10 point choices
				//same as above just with 10 items
				case "B": 
				$qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qiqid' AND code='$qanswer' AND language='{$language}' ORDER BY sortorder, answer";
				$qresult=db_execute_num($qquery) or safe_die ("Couldn't get answer details (Array 10p Q)<br />$qquery<br />".$connect->ErrorMsg());
				while ($qrow=$qresult->FetchRow())
				{
					for ($i=1; $i<=10; $i++)
					{
						$alist[]=array("$i", "$i");
					}
					$atext=$qrow[1];
				}
				
				$qquestion .= "<br />\n[".$atext."]";
				$qtitle .= "($qanswer)";
				break;
				
				
				
				//Array of Yes/No/$clang->gT("Uncertain")
				case "C": 
				$qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qiqid' AND code='$qanswer' AND language='{$language}' ORDER BY sortorder, answer";
				$qresult=db_execute_num($qquery) or safe_die ("Couldn't get answer details<br />$qquery<br />".$connect->ErrorMsg());
				
				//loop thorugh results
				while ($qrow=$qresult->FetchRow())
				{
					//add results
					$alist[]=array("Y", $clang->gT("Yes"));
					$alist[]=array("N", $clang->gT("No"));
					$alist[]=array("U", $clang->gT("Uncertain"));
					$atext=$qrow[1];
				}
				//output
				$qquestion .= "<br />\n[".$atext."]";
				$qtitle .= "($qanswer)";
				break;
				
				
				
				//Array of Yes/No/$clang->gT("Uncertain")
				//same as above
				case "E": 
				$qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qiqid' AND code='$qanswer' AND language='{$language}' ORDER BY sortorder, answer";
				$qresult=db_execute_num($qquery) or safe_die ("Couldn't get answer details<br />$qquery<br />".$connect->ErrorMsg());
				while ($qrow=$qresult->FetchRow())
				{
					$alist[]=array("I", $clang->gT("Increase"));
					$alist[]=array("S", $clang->gT("Same"));
					$alist[]=array("D", $clang->gT("Decrease"));
					$atext=$qrow[1];
				}
				$qquestion .= "<br />\n[".$atext."]";
				$qtitle .= "($qanswer)";
				break;

				

				case ":": //Array (Multiple Flexi) (Numbers)
            	$qidattributes=getQuestionAttributes($qiqid);
            	if ($maxvalue=arraySearchByKey("multiflexible_max", $qidattributes, "attribute", 1)) {
            		$maxvalue=$maxvalue['value'];
            	} 
            	else {
            		$maxvalue=10;
            	}
				
            	if ($minvalue=arraySearchByKey("multiflexible_min", $qidattributes, "attribute", 1)) {
            		$minvalue=$minvalue['value'];
            	} 
            	else {
            		$minvalue=1;
            	}
            	
            	if ($stepvalue=arraySearchByKey("multiflexible_step", $qidattributes, "attribute", 1)) {
            		$stepvalue=$stepvalue['value'];
            	} 
            	else {
            		$stepvalue=1;
            	}
            	
				if (arraySearchByKey("multiflexible_checkbox", $qidattributes, "attribute", 1)) {
					$minvalue=0;
					$maxvalue=1;
					$stepvalue=1;
				}
				
				list($qacode, $licode)=explode("_", $qanswer);
				
				$qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qiqid' AND code='$qacode' AND language='{$language}' ORDER BY sortorder, answer";
				//echo $qquery."<br />";
				$qresult=db_execute_num($qquery) or die ("Couldn't get answer details<br />$qquery<br />".$connect->ErrorMsg());
				
				while ($qrow=$qresult->FetchRow())
				{
				    $fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$qlid}' AND code = '{$licode}' AND language='{$language}'ORDER BY sortorder, code";
					$fresult = db_execute_assoc($fquery);
					while ($frow=$fresult->FetchRow())
					{
						//$alist[]=array($frow['code'], $frow['title']);
						$ltext=$frow['title'];
					}
					$atext=$qrow[1];
				}
				
				for($i=$minvalue; $i<=$maxvalue; $i+=$stepvalue) 
				{
				    $alist[]=array($i, $i);
				}
				
				$qquestion .= "<br />\n[".$atext."] [".$ltext."]";
				$qtitle .= "($qanswer)";
				break;
				
				
				
				case "F": //Array of Flexible
				case "H": //Array of Flexible by Column
				$qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qiqid' AND code='$qanswer' AND language='{$language}' ORDER BY sortorder, answer";
				$qresult=db_execute_num($qquery) or safe_die ("Couldn't get answer details<br />$qquery<br />".$connect->ErrorMsg());
				
				//loop through answers
				while ($qrow=$qresult->FetchRow())
				{
					//this question type uses its own labels
					$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$qlid}' AND language='{$language}'ORDER BY sortorder, code";
					$fresult = db_execute_assoc($fquery);
					
					//add code and title to results for outputting them later
					while ($frow=$fresult->FetchRow())
					{
						$alist[]=array($frow['code'], FlattenText($frow['title']));
					}
					
					//counter
					$atext=$qrow[1];
				}
				
				//output
				$qquestion .= "<br />\n[".$atext."]";
				$qtitle .= "($qanswer)";
				break;
				
				
				
				case "G": //Gender
				$alist[]=array("F", $clang->gT("Female"));
				$alist[]=array("M", $clang->gT("Male"));
				break;
				
				
				
				case "Y": //Yes\No
				$alist[]=array("Y", $clang->gT("Yes"));
				$alist[]=array("N", $clang->gT("No"));
				break;
				
				
				
				case "5": //5 Point (just 1 item to rank!)
				for ($i=1; $i<=5; $i++)
				{
					$alist[]=array("$i", "$i");
				}
				break;


			
				case "W":	//List felixble labels (dropdown)					
				case "Z":	//List flexible labels (radio)
					
				//get labels
				$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$qlid}' AND language='{$language}' ORDER BY sortorder, code";
				$fresult = db_execute_assoc($fquery);
				
				//put label code and label title into array
				while ($frow=$fresult->FetchRow())
				{
					$alist[]=array($frow['code'], FlattenText($frow['title']));
				}
				
				//does "other" field exist?
				if ($qother == "Y")
				{
					$alist[]=array($clang->gT("Other"),$clang->gT("Other"),$fielddata['fieldname'].'other');
				}
				break;				
				
				
				
                case "1":	//array flexible labels (dual scale)
                	
                //get question attributes
                $qidattributes=getQuestionAttributes($qqid);
                
                //check last character -> label 1
                if (substr($rt,-1,1) == 0)
                {
                    //get label 1
                    $fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$qlid}' AND language='{$language}' ORDER BY sortorder, code";
                    
                    //header available?
                    if ($dsheaderA=arraySearchByKey("dualscale_headerA", $qidattributes, "attribute", 1))
                    {
                    	//output
                        $labelheader= "[".$dsheaderA['value']."]";
                    }
                    
                    //no header
                    else
                    {
                        $labelheader ='';
                    }
                    
                    //output
                    $labelno = "Label 1";
                }
                
                //label 2
                else 
                {
                    //get label 2
                    $fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$qlid1}' AND language='{$language}' ORDER BY sortorder, code";
                    
                    //header available?
                    if ($dsheaderB=arraySearchByKey("dualscale_headerB", $qidattributes, "attribute", 1))
                    {
                    	//output
                        $labelheader= "[" . $dsheaderB['value'] . "]";
                    }

                    //no header
                    else
                    {
                        $labelheader ='';
                    }
                    
                    //output
                    $labelno = "Label 2";
                }
                
                //get data
                $fresult = db_execute_assoc($fquery);
                
                //put label code and label title into array
                while ($frow=$fresult->FetchRow())
                {
                    $alist[]=array($frow['code'], FlattenText($frow['title']));
                }
                
                //adapt title and question
                $qtitle = $qtitle." [".$qanswer."][".$labelno."]";
                $qquestion  = $qastring .$labelheader;
                break;
                
                                
                
				default:	//default handling
				
				//get answer code and title
				$qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qqid' AND language='{$language}' ORDER BY sortorder, answer";
				$qresult = db_execute_num($qquery) or safe_die ("Couldn't get answers list<br />$qquery<br />".$connect->ErrorMsg());
				
				//put answer code and title into array
				while ($qrow=$qresult->FetchRow())
				{
					$alist[]=array("$qrow[0]", "$qrow[1]");
				}
				
				//handling for "other" field for list radio or list drowpdown
				if (($qtype == "L" || $qtype == "!") && $qother == "Y")
				{
					//add "other"
					$alist[]=array($clang->gT("Other"),$clang->gT("Other"),$fielddata['fieldname'].'other');
				}
				
			}	//end switch question type
			
			//moved because it's better to have "no answer" at the end of the list instead of the beginning
			//put data into array
			$alist[]=array("", $clang->gT("No answer"));
			
		}	//end else -> single option answers 

		//foreach ($alist as $al) {echo "<br> 0: ".$al[0]." - 1: ".$al[1]."<br>";} //debugging line
		//foreach ($fvalues as $fv) {$statisticsoutput .= "$fv | ";} //debugging line
				
		
		//2. Collect and Display results #######################################################################
		if (isset($alist) && $alist) //Make sure there really is an answerlist, and if so:
		{
			//output
			$statisticsoutput .= "<table width='95%' align='center' border='1'  cellpadding='2' cellspacing='0' class='statisticstable'>\n"
			."\t<tr><td colspan='4' align='center'><strong>"
			
			//headline
			.$clang->gT("Field summary for")." $qtitle:</strong>"
			."</td></tr>\n"
			."\t<tr><td colspan='4' align='center'><strong>"
			
			//question title
			."$qquestion</strong></td></tr>\n"
			."\t<tr>\n\t\t<td width='50%' align='center' >";
			
			// this will count the answers considered completed
			$TotalCompleted = 0;    
			
			//loop thorugh the array which contains all answer data
			foreach ($alist as $al)
			{
				//picks out alist that come from the multiple list above
				if (isset($al[2]) && $al[2]) 
				{
					//handling for "other" option
					if ($al[1] == $clang->gT("Other"))
					{
						//get data
						$query = "SELECT count(*) FROM ".db_table_name("survey_$surveyid")." WHERE ";
						$query .= ($connect->databaseType == "mysql")?  db_quote_id($al[2])." != ''" : "NOT (".db_quote_id($al[2])." LIKE '')";
					}

					// all other question types
					else
					{
						$query = "SELECT count(*) FROM ".db_table_name("survey_$surveyid")." WHERE ".db_quote_id($al[2])." =";
						
						//ranking question?
						if (substr($rt, 0, 1) == "R")
						{
							$query .= " '$al[0]'";
						}
						else
						{
							$query .= " 'Y'";
						}
					}
					
				}	//end if -> alist set
				
				else
				{
					//get more data                          
                    if ($connect->databaseType == 'odbc_mssql' || $connect->databaseType == 'odbtp' || $connect->databaseType == 'mssql_n')
                    { 
                        // mssql cannot compare text blobs so we have to cast here
                        $query = "SELECT count(*) FROM ".db_table_name("survey_$surveyid")." WHERE cast(".db_quote_id($rt)." as varchar)= '$al[0]'"; 
                    }
                    else
                    $query = "SELECT count(*) FROM ".db_table_name("survey_$surveyid")." WHERE ".db_quote_id($rt)." = '$al[0]'";
				}
				
				//check filter option
				if ($filterout_incomplete_answers == true) {$query .= " AND submitdate is not null";}                     
				
				//check for any "sql" that has been passed from another script
				//if ($sql != "NULL") {$query .= " AND $sql";}
				

				//get data
				$result=db_execute_num($query) or safe_die ("Couldn't do count of values:<br />$query<br />".$connect->ErrorMsg());
                
				// this just extracts the data, after we present
				while ($row=$result->FetchRow())                   
				{
					//increase counter
                    $TotalCompleted += $row[0];                    
                    
                    //"no answer" handling
					if ($al[0] == "")
					{$fname=$clang->gT("No answer");}
					
					//check if aggregated results should be shown
					elseif ($showaggregateddata == 1 && isset($showaggregateddata))
					{	
						if(!isset($showheadline) || $showheadline != false)
						{
							if($qtype == "5" || $qtype == "A")
							{
								//four columns
								$statisticsoutput .= "<strong>".$clang->gT("Answer")."</strong></td>\n"
								."\t\t<td width='20%' align='center' >"
								."<strong>".$clang->gT("Count")."</strong></td>\n"
								."\t\t<td width='20%' align='center' >"
								."<strong>".$clang->gT("Percentage")."</strong></td>\n"
								."\t\t<td width='10%' align='center' >"
								."<strong>".$clang->gT("Sum")."</strong></td>\n"
								."\t</tr>\n";
								
								$showheadline = false;							
							}
							else
							{
								//three columns
								$statisticsoutput .= "<strong>".$clang->gT("Answer")."</strong></td>\n"
								."\t\t<td width='25%' align='center' >"
								."<strong>".$clang->gT("Count")."</strong></td>\n"
								."\t\t<td width='25%' align='center' >"
								."<strong>".$clang->gT("Percentage")."</strong></td>\n"
								."\t</tr>\n";
								
								$showheadline = false;
							}
							
						}
						
						//text for answer column is always needed
						$fname="$al[1] ($al[0])";
						
						//these question types get special treatment by $showaggregateddata
						if($qtype == "5" || $qtype == "A")
						{					
							//put non-edited data in here because $row will be edited later
							$grawdata[]=$row[0];
							
							//keep in mind that we already added data (will be checked later)
							$justadded = true;							
							
							//we need a counter because we want to sum up certain values
							//reset counter if 5 items have passed
							if(!isset($testcounter) || $testcounter >= 4)
							{
								$testcounter = 0;
							}
							else
							{
								$testcounter++;
							}							
							
							//beside the known percentage value a new aggregated value should be shown
							//therefore this item is marked in a certain way
							
							if($testcounter == 0 )	//add 300 to original value
							{													
								//HACK: add three times the total number of results to the value
								//This way we get a 300 + X percentage which can be checked later
								
								$row[0] += (3*$totalrecords);
								
							}
							
							//the third value should be shown twice later -> mark it
							if($testcounter == 2)	//add 400 to original value
							{
								//HACK: add four times the total number of results to the value
								//This way there should be a 400 + X percentage which can be checked later
								$row[0] += (4*$totalrecords);
							}
							
							//the last value aggregates the data of item 4 + item 5 later
							if($testcounter == 4 )	//add 200 to original value
							{							
								//HACK: add two times the total number of results to the value
								//This way there should be a 200 + X percentage which can be checked later
								$row[0] += (2*$totalrecords);
							}
							
						}	//end if -> question type = "5"/"A"
											
					}	//end if -> show aggregated data
					
					//handling what's left
					else
					{
						if(!isset($showheadline) || $showheadline != false)
						{						
							//three columns
							$statisticsoutput .= "<strong>".$clang->gT("Answer")."</strong></td>\n"
							."\t\t<td width='25%' align='center' >"
							."<strong>".$clang->gT("Count")."</strong></td>\n"
							."\t\t<td width='25%' align='center' >"
							."<strong>".$clang->gT("Percentage")."</strong></td>\n"
							."\t</tr>\n";
						
							$showheadline = false;
						}
						//answer text
						$fname="$al[1] ($al[0])";
					}
					
					//are there some results to play with?					
					if ($totalrecords > 0)
					{
						//calculate percentage
						$gdata[] = ($row[0]/$totalrecords)*100;
					} 
					//no results
					else
					{
						//no data!
                        $gdata[] = "N/A";
					}
					
					//only add this if we don't handle question type "5"/"A"
					if(!isset($justadded))
					{
						//put absolute data into array
						$grawdata[]=$row[0];
					}
					else
					{
						//unset to handle "no answer" data correctly
						unset($justadded);
					}
					
					//put question title and code into array
                    $label[]=$fname;
                    
                    //put only the code into the array
					$justcode[]=$al[0];
					
					//edit labels and put them into antoher array
                    $lbl[] = wordwrap(FlattenText($fname), 25, "\n");
                    
                }	//end while -> loop through results
                
			}	//end foreach -> loop through answer data

			
			//no filtering of incomplete answers and NO multiple option questions
            if (($filterout_incomplete_answers == false) and ($qtype != "M") and ($qtype != "P"))
            {            	
            	//is the checkbox "Don't consider NON completed responses (only works when Filter incomplete answers is Disable)" checked?
                if (isset($_POST["noncompleted"]) and ($_POST["noncompleted"] == "on") && (isset($showaggregateddata) && $showaggregateddata == 0))
                {
                	//counter
                    $i=0;
                    
                    while (isset($gdata[$i]))
                    {
                    	//we want to have some "real" data here
                        if ($gdata[$i] != "N/A") 
                        {
                        	//calculate percentage
                        	$gdata[$i] = ($grawdata[$i]/$TotalCompleted)*100; 
                        }
                        
                        //increase counter
                        $i++;
                        
					}	//end while (data available)
					
				}	//end if -> noncompleted checked
			
				//noncompleted is NOT checked
	            else
	            {     
	            	//calculate total number of incompleted records
	                $TotalIncomplete = $totalrecords - $TotalCompleted;
	                
	                //output
	                $fname=$clang->gT("Non completed");
	                
	                //we need some data
	                if ($totalrecords > 0)
	                {
	                	//calculate percentage
	                    $gdata[] = ($TotalIncomplete/$totalrecords)*100;
	                } 
	                
	                //no data :(
	                else
	                {
	                    $gdata[] = "N/A";
	                }
	                
	                //put data of incompleted records into array
	                $grawdata[]=$TotalIncomplete;
	                
	                //put question title ("Not completed") into array
	                $label[]= $fname;
	                
	                //put the code ("Not completed") into the array
	                $justcode[]=$fname;
	                
	                //edit labels and put them into antoher array
	                $lbl[] = wordwrap(FlattenText($fname), 20, "\n");
	                
	            }	//end else -> noncompleted NOT checked
	            
            }	//end if -> no filtering of incomplete answers and no multiple option questions
            
            
            //counter
            $i=0;
            
            //we need to know which item we are editing
      	    $itemcounter = 1;
      	    
      	    //array to store items 1 - 5 of question types "5" and "A"
      	    $stddevarray = array();
      	      
            //loop through all available answers
            while (isset($gdata[$i]))
            {
            	//repeat header (answer, count, ...) for each new question
            	unset($showheadline);
            	
            	
            	/*
            	 * there are 3 colums:
            	 * 
            	 * 1 (50%) = answer (title and code in brackets)
            	 * 2 (25%) = count (absolute)
            	 * 3 (25%) = percentage
            	 */
                $statisticsoutput .= "\t<tr>\n\t\t<td width='50%' align='center' >" . $label[$i] ."\n"
                ."\t\t</td>\n"
                
                //output absolute number of records
                ."\t\t<td width='20%' align='center' >" . $grawdata[$i] . "\n";
                
                
                //no data
                if ($gdata[$i] == "N/A") 
                {
                	//output when having no data
                	$statisticsoutput .= "\t\t</td><td width='20%' align='center' >";
                	
                	//percentage = 0
                    $statisticsoutput .= sprintf("%01.2f", $gdata[$i]) . "%"; 
                    $gdata[$i] = 0;
                    
                    //check if we have to adjust ouput due to $showaggregateddata setting
                    if($showaggregateddata == 1 && isset($showaggregateddata) && ($qtype == "5" || $qtype == "A"))
                    {
                    	$statisticsoutput .= "\t\t</td><td>";
                	}
                }
                
                //data available
                else
                {        	
                	//check if data should be aggregated
                	if($showaggregateddata == 1 && isset($showaggregateddata) && ($qtype == "5" || $qtype == "A"))
                	{
                		//mark that we have done soemthing special here
                		$aggregated = true; 
                		
                		//just calculate everything once. the data is there in the array      
                		if($itemcounter == 1)
                		{
                			//there are always 5 answers
                			for($x = 0; $x < 5; $x++)
                			{ 
                				//put 5 items into array for further calculations
                				array_push($stddevarray, $grawdata[$x]);
                			}
                		}
                		
                		//"no answer" & items 2 / 4 - nothing special to do here, just adjust output
	                	if($gdata[$i] <= 100)
	                	{	                		
	                		if($itemcounter == 2 && $label[$i+4] == $clang->gT("No answer"))
	                		{
	                			//prevent division by zero
	                			if(($totalrecords - $grawdata[$i+4]) > 0)
	                			{
	                				//re-calculate percentage
	                				$percentage = ($grawdata[$i] / ($totalrecords - $grawdata[$i+4])) * 100;
	                			}
	                			else
	                			{
	                				$percentage = 0;
	                			}
	                			
	                		}
	                		elseif($itemcounter == 4 && $label[$i+2] == $clang->gT("No answer"))
	                		{
	                			//prevent division by zero
	                			if(($totalrecords - $grawdata[$i+2]) > 0)
	                			{
	                				//re-calculate percentage
	                				$percentage = ($grawdata[$i] / ($totalrecords - $grawdata[$i+2])) * 100;
	                			}
	                			else
	                			{
	                				$percentage = 0;
	                			}
	                		}
	                		else
	                		{
	                			$percentage = $gdata[$i];
	                		}
	                		
	                		//output
	                		$statisticsoutput .= "\t\t</td><td width='20%' align='center'>";
	                		
	                		//output percentage
	                		$statisticsoutput .= sprintf("%01.2f", $percentage) . "%"; 
	                		
	                		//adjust output
	                		$statisticsoutput .= "\t\t</td><td>";	                		
	                	}
	                	
	                	//item 3 - just show results twice
	                	if($gdata[$i] >= 400 && $i != 0)
	                	{         		
	                		//remove "400" which was added before
	                		$gdata[$i] -= 400;	                		
	                		
	                		if($itemcounter == 3 && $label[$i+3] == $clang->gT("No answer"))
	                		{
	                			//prevent division by zero
	                			if(($totalrecords - $grawdata[$i+3]) > 0)
	                			{
	                				//re-calculate percentage
	                				$percentage = ($grawdata[$i] / ($totalrecords - $grawdata[$i+3])) * 100;
	                			}
	                			else
	                			{
	                				$percentage = 0;
	                			}
	                		}
	                		else
	                		{	                			
	                			//get the original percentage
	                			$percentage = $gdata[$i];
	                		}
	                		
	                		//output percentage
	                		$statisticsoutput .= "\t\t</td><td width='20%' align='center' >";
	                		$statisticsoutput .= sprintf("%01.2f", $percentage) . "%"; 
							
							//output again (no real aggregation here)
	                		$statisticsoutput .= "\t\t</td><td width='10%' align='center' >";
	                		$statisticsoutput .= sprintf("%01.2f", $percentage)."%";
	                		$statisticsoutput .= "\t\t";
	                	}
	                	
	                	//FIRST value -> add percentage of item 1 + item 2
	                	if(($gdata[$i] >= 300 && $gdata[$i] < 400) || ($i == 0 && $gdata[$i] <= 400))
	                	{                				   
	                		//remove "300" which was added before
	                		$gdata[$i] -= 300;
	                		
	                		if($itemcounter == 1 && $label[$i+5] == $clang->gT("No answer"))
	                		{
	                			//prevent division by zero
	                			if(($totalrecords - $grawdata[$i+5]) > 0)
	                			{
	                				//re-calculate percentage
	                				$percentage = ($grawdata[$i] / ($totalrecords - $grawdata[$i+5])) * 100;
	                				$percentage2 = ($grawdata[$i + 1] / ($totalrecords - $grawdata[$i+5])) * 100;
	                			}
	                			else
	                			{
	                				$percentage = 0;
	                				$percentage2 = 0;
	                		
	                			}
	                		}
	                		else
	                		{
	                			$percentage = $gdata[$i];
	                			$percentage2 = $gdata[$i+1];
	                		}
	                		
	                		//percentage of item 1 + item 2
	                		$aggregatedgdata = $percentage + $percentage2;
	                		
	                		//output percentage
	                		$statisticsoutput .= "\t\t</td><td width='20%' align='center' >";
	                		$statisticsoutput .= sprintf("%01.2f", $percentage) . "%"; 
							
	                		//output aggregated data
	                		$statisticsoutput .= "\t\t</td><td width='10%' align='center' >";
	                		$statisticsoutput .= sprintf("%01.2f", $aggregatedgdata)."%";
	                		$statisticsoutput .= "\t\t";
	                	}
	                	
	                	//LAST value -> add item 4 + item 5
	                	if($gdata[$i] > 100 && $gdata[$i] < 300)
	                	{	                		
	                		//remove "200" which was added before
	                		$gdata[$i] -= 200;
	                		
	                		if($itemcounter == 5 && $label[$i+1] == $clang->gT("No answer"))
	                		{
	                			//prevent division by zero
	                			if(($totalrecords - $grawdata[$i+1]) > 0)
	                			{
	                				//re-calculate percentage
	                				$percentage = ($grawdata[$i] / ($totalrecords - $grawdata[$i+1])) * 100;
	                				$percentage2 = ($grawdata[$i - 1] / ($totalrecords - $grawdata[$i+1])) * 100;
	                			}
	                			else
	                			{
	                				$percentage = 0;
	                				$percentage2 = 0;	                		
	                			}	                			
	                		}
	                		else
	                		{	                			
	                			$percentage = $gdata[$i];
	                			$percentage2 = $gdata[$i-1];
	                		}
	                		
	                		//item 4 + item 5
	                		$aggregatedgdata = $percentage + $percentage2;
	                		
	                		//output percentage
	                		$statisticsoutput .= "\t\t</td><td width='20%' align='center' >";
	                		$statisticsoutput .= sprintf("%01.2f", $percentage) . "%";
							
	                		//output aggregated data
	                		$statisticsoutput .= "\t\t</td><td width='10%' align='center' >";
	                		$statisticsoutput .= sprintf("%01.2f", $aggregatedgdata)."%";
	                		$statisticsoutput .= "\t\t";
	                		
	                		//calculate sum of items 1-5
	                		$sumitems = $grawdata[$i] 
	                		+ $grawdata[$i-1] 
	                		+ $grawdata[$i-2]
	                		+ $grawdata[$i-3]
	                		+ $grawdata[$i-4];
	                		
	                		//special treatment for zero values
	                		if($sumitems > 0)
	                		{
	                			$sumpercentage = "100.00";
	                		}
	                		else
	                		{
	                			$sumpercentage = "0";
	                		}
	                		//special treatment for zero values
	                		if($TotalCompleted > 0)
	                		{
	                			$casepercentage = "100.00";
	                		}
	                		else
	                		{
	                			$casepercentage = "0";
	                		}
	                		
	                		$statisticsoutput .= "\t\t&nbsp</td>\n\t</tr>\n";
	                		$statisticsoutput .= "<tr><td width='50%' align='center'><strong>".$clang->gT("Sum")." (".$clang->gT("Answers").")</strong></td>";
	                		$statisticsoutput .= "<td width='20%' align='center' ><strong>".$sumitems."</strong></td>";
	                		$statisticsoutput .= "<td width='20%' align='center' ><strong>$sumpercentage%</strong></td>";
	                		$statisticsoutput .= "<td width='10%' align='center' ><strong>$sumpercentage%</strong></td>";
	                		
	                		$statisticsoutput .= "\t\t&nbsp</td>\n\t</tr>\n";
	                		$statisticsoutput .= "<tr><td width='50%' align='center'>".$clang->gT("Number of cases")."</td>";	//German: "Fallzahl"
	                		$statisticsoutput .= "<td width='20%' align='center' >".$TotalCompleted."</td>";
	                		$statisticsoutput .= "<td width='20%' align='center' >$casepercentage%</td>";
	                		//there has to be a whitespace within the table cell to display correctly
	                		$statisticsoutput .= "<td width='10%' align='center' >&nbsp</td></tr>";  
	                		
	                	}
	                	
                	}	//end if -> show aggregated data
                	
                	//don't show aggregated data
                	else
                	{                		
                		//output percentage 
	                	$statisticsoutput .= "\t\t</td><td width='20%' align='center' >";
                		$statisticsoutput .= sprintf("%01.2f", $gdata[$i]) . "%";
                		$statisticsoutput .= "\t\t";
                	}
                	                	                	
                }	//end else -> $gdata[$i] != "N/A"                    
                
              	//end output per line. there has to be a whitespace within the table cell to display correctly
	            $statisticsoutput .= "\t\t&nbsp</td>\n\t</tr>\n";              
                
                //increase counter
                $i++;
                
				$itemcounter++;
            
            }	//end while
            
            //only show additional values when this setting is enabled
            if($showaggregateddata == 1 && isset($showaggregateddata))
            {
            	//it's only useful to calculate standard deviation and arithmetic means for question types
            	//5 = 5 Point Scale
            	//A = Array (5 Point Choice)
            	if($qtype == "5" || $qtype == "A")
            	{
            		$stddev = 0;
            		$am = 0;
            			
            		//calculate arithmetic mean
            		if(isset($sumitems) && $sumitems > 0)
            		{
            			//calculate and round results
            			//there are always 5 items
            			for($x = 0; $x < 5; $x++)
            			{
            				//create product of item * value
            				$am += (($x+1) * $stddevarray[$x]);
            			}

            			//prevent division by zero
            			if(isset($stddevarray) && array_sum($stddevarray) > 0)
            			{
            				$am = round($am / array_sum($stddevarray),2);
            			}
            			else
            			{
            				$am = 0;
            			}
            			
            			//calculate standard deviation -> loop through all data
            			/*
            			 * four steps to calculate the standard deviation
            			 * 1 = calculate difference between item and arithmetic mean and multiply with the number of elements
            			 * 2 = create sqaure value of difference
            			 * 3 = sum up square values
            			 * 4 = multiply result with 1 / (number of items)
            			 * 5 = get root
            			 */
            			
            			
            			
            			for($j = 0; $j < 5; $j++)
            			{            				
            				//1 = calculate difference between item and arithmetic mean
            				$diff = (($j+1) - $am);
            				
            				//2 = create square value of difference
            				$squarevalue = square($diff);
            				
            				//3 = sum up square values and multiply them with the occurence
            				//prevent divison by zero
            				if($squarevalue != 0 && $stddevarray[$j] != 0)
            				{
            					$stddev += $squarevalue * $stddevarray[$j];
            				}
            				   
            			}
            			
            			//4 = multiply result with 1 / (number of items (=5))            			
            			//There are two different formulas to calculate standard derivation
            			//$stddev = $stddev / array_sum($stddevarray);		//formula source: http://de.wikipedia.org/wiki/Standardabweichung
            			
            			//prevent division by zero
            			if((array_sum($stddevarray)-1) != 0 && $stddev != 0)
            			{
            				$stddev = $stddev / (array_sum($stddevarray)-1);	//formula source: http://de.wikipedia.org/wiki/Empirische_Varianz
            			}
            			else
            			{
            				$stddev = 0;
            			}
            			
            			//5 = get root
            			$stddev = sqrt($stddev);
            			$stddev = round($stddev,2);
            		}            		
            		
            		//calculate standard deviation
			        $statisticsoutput .= "<tr><td width='50%' align='center'>".$clang->gT("Arithmetic mean")." | ".$clang->gT("Standard deviation")."</td>";	//German: "Fallzahl"
			        $statisticsoutput .= "<td width='40%' align='center' colspan = '2'> $am | $stddev</td>";
			        //there has to be a whitespace within the table cell to display correctly
			        $statisticsoutput .= "<td width='10%' align='center' >&nbsp</td></tr>";
            	}
            }
            
            
            
            
            //-------------------------- PCHART OUTPUT ----------------------------
            
            //PCHART has to be enabled and we need some data
			if (isset($publicgraphs) && $publicgraphs == 1  && array_sum($gdata)>0) 
			{
				$graph = "";
				$p1 = "";
				//                  $statisticsoutput .= "<pre>";
				//                  $statisticsoutput .= "GDATA:\n";
				//                  print_r($gdata);
				//                  $statisticsoutput .= "GRAWDATA\n";
				//                  print_r($grawdata);
				//                  $statisticsoutput .= "LABEL\n";
				//                  print_r($label);
				//                  $statisticsoutput .= "JUSTCODE\n";
				//                  print_r($justcode);
				//                  $statisticsoutput .= "LBL\n";
				//                  print_r($lbl);
				//                  $statisticsoutput .= "</pre>";
				//First, lets delete any earlier graphs from the tmp directory
				//$gdata and $lbl are arrays built at the end of the last section
				//that contain the values, and labels for the data we are about
				//to send to pchart.
				
                $i = 0;
                foreach ($gdata as $data)
                {
                    if ($data != 0){$i++;}
                }	
				$totallines=$i;
				
				if ($totallines>15) 
				{
					$gheight=320+(6.7*($totallines-15));
					$fontsize=7;
					$legendtop=0.01;
					$setcentrey=0.5/(($gheight/320));
				} 
				else 
				{
					$gheight=320;
					$fontsize=8;
					$legendtop=0.07;
					$setcentrey=0.5;
				}				
				
				// Create bar chart for multiple options
				if ($qtype == "M" || $qtype == "P") 
				{ 
					//new bar chart using data from array $grawdata which contains percentage

                    $DataSet = new pData;  
                    $counter=0;
                    $maxyvalue=0;
                    foreach ($grawdata as $datapoint)
                    {
                        $DataSet->AddPoint(array($datapoint),"Serie$counter");  
                        $DataSet->AddSerie("Serie$counter");

                        $counter++;
                        if ($datapoint>$maxyvalue) $maxyvalue=$datapoint;
                    }
//                    $DataSet->AddPoint($justcode,"LabelX");
//                    $DataSet->SetAbsciseLabelSerie("LabelX");
                    if ($maxyvalue<10) {++$maxyvalue;}
                    $counter=0;
                    foreach ($lbl as $label)
                    {
                        $DataSet->SetSerieName($label,"Serie$counter");  
                        $counter++;
                    }
                    
                    //$DataSet->SetAbsciseLabelSerie();  

                    $Test = new pChart(1,1); 
                    
                    $Test->setFontProperties("classes/pchart/fonts/tahoma.ttf",10);
                    $legendsize=$Test->getLegendBoxSize($DataSet->GetDataDescription());
                     
                    if ($legendsize[1]<320) $gheight=420; else $gheight=$legendsize[1]+100;
                    $Test = new pChart(690+$legendsize[0],$gheight); 
                    $Test->loadColorPalette($homedir.'/styles/'.$admintheme.'/limesurvey.pal');
                    $Test->setFontProperties("classes/pchart/fonts/tahoma.ttf",8);  
                    $Test->setGraphArea(50,30,500,$gheight-60);  
                    $Test->drawFilledRoundedRectangle(7,7,523+$legendsize[0],$gheight-7,5,240,240,240);  
                    $Test->drawRoundedRectangle(5,5,525+$legendsize[0],$gheight-5,5,230,230,230);  
                    $Test->drawGraphArea(255,255,255,TRUE);  
                    $Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_START0,150,150,150,TRUE,90,0,TRUE,5,false);  
                    $Test->drawGrid(4,TRUE,230,230,230,50);     
                                      // Draw the 0 line
                    $Test->setFontProperties("classes/pchart/fonts/tahoma.ttf",6);
                    $Test->drawTreshold(0,143,55,72,TRUE,TRUE);

                    // Draw the bar graph
                    $Test->drawBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),FALSE);
                    //$Test->setLabel($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie4","1","Important point!");   
                    // Finish the graph
                    $Test->setFontProperties("classes/pchart/fonts/tahoma.ttf",10);
                    $Test->drawLegend(510,30,$DataSet->GetDataDescription(),255,255,255);
                    // $Test->drawTitle(50,22,"Example ".$legendsize[0].'x'.$legendsize[1],50,50,50,585);
				}	//end if (bar chart)
				
				//Pie Chart
				else 
				{ 
                    // this block is to remove the items with value == 0
                    $i = 0;
                    while (isset ($gdata[$i]))
                    {
                        if ($gdata[$i] == 0)
                        {
                           array_splice ($gdata, $i, 1);
                           array_splice ($lbl, $i, 1);
                        }
                        else
                        {$i++;}
                    }
                
                    //create new 3D pie chart
					$DataSet = new pData; 
                    $DataSet->AddPoint($gdata,"Serie1");  
                    $DataSet->AddPoint($lbl,"Serie2");  
                    $DataSet->AddAllSeries();
                    $DataSet->SetAbsciseLabelSerie("Serie2");
					
					
                    $Test = new pChart(690,$gheight);  
					$Test->drawFilledRoundedRectangle(7,7,687,$gheight-3,5,240,240,240);  
                    $Test->drawRoundedRectangle(5,5,689,$gheight-1,5,230,230,230);  
					
                    // Draw the pie chart  
                    $Test->setFontProperties("classes/pchart/fonts/tahoma.ttf",10);  
                    $Test->drawPieGraph($DataSet->GetData(),$DataSet->GetDataDescription(),225,round($gheight/2),170,PIE_PERCENTAGE,TRUE,50,20,5);  
                    $Test->setFontProperties("classes/pchart/fonts/tahoma.ttf",9);  
                    $Test->drawPieLegend(430,15,$DataSet->GetData(),$DataSet->GetDataDescription(),250,250,250);  
					
				}	//end else -> pie charts

				//introduce new counter
				if (!isset($ci)) {$ci=0;}
				
				//increase counter, start value -> 1
				$ci++;
				
				//filename of chart image
				$gfilename="STATS_".date("d")."X".$currentuser."X".$surveyid."X".$ci.date("His").".png";
				
				//create graph
				$Test->Render($tempdir."/".$gfilename);
				
				//add graph to output
				$statisticsoutput .= "<tr><td colspan='4' style=\"text-align:center\"><img src=\"$tempurl/".$gfilename."\" border='0'></td></tr>";
			}
			
			//close table/output
			$statisticsoutput .= "</table><br /> \n";
			
		}	//end if -> collect and display results
		
		//delete data
		unset($gdata);
		unset($grawdata);
        unset($label);
		unset($lbl);
		unset($justcode);
		unset ($alist);		
		
	}	// end foreach -> loop through all questions
	
	//output
    $statisticsoutput .= "<br />&nbsp\n";
    
}	//end if -> show summary results


//done! set progress bar to 100%
if (isset($prb))
{
	$prb->setLabelValue('txt1',$clang->gT('Completed'));
	$prb->moveStep(100);
	$prb->hide();
}


//output everything:
echo $statisticsoutput;

//Delete all Session Data
$_SESSION['finished'] = true;



//delete old images
function deletePattern($dir, $pattern = "")
{
	$deleted = false;
	$pattern = str_replace(array("\*","\?"), array(".*","."), preg_quote($pattern));
	if (substr($dir,-1) != "/") $dir.= "/";
	if (is_dir($dir))
	{
		$d = opendir($dir);
		while ($file = readdir($d))
		{
			if (is_file($dir.$file) && ereg("^".$pattern."$", $file))
			{
				if (unlink($dir.$file))
				{
					$deleted[] = $file;
				}
			}
		}
		closedir($d);
		return $deleted;
	}
	else return 0;
}


//delete old images (which aren't from today?)
function deleteNotPattern($dir, $matchpattern, $pattern = "")
{
	$deleted = false;
	$pattern = str_replace(array("\*","\?"), array(".*","."), preg_quote($pattern));
	$matchpattern = str_replace(array("\*","\?"), array(".*","."), preg_quote($matchpattern));
	if (substr($dir,-1) != "/") $dir.= "/";
	if (is_dir($dir))
	{
		$d = opendir($dir);
		while ($file = readdir($d))
		{
			if (is_file($dir.$file) && ereg("^".$matchpattern."$", $file) && !ereg("^".$pattern."$", $file))
			{
				if (unlink($dir.$file))
				{
					$deleted[] = $file;
				}
			}
		}
		closedir($d);
		return $deleted;
	}
	else return 0;
}



//simple function to square a value
function square($number)
{
	if($number == 0)
	{
		$squarenumber = 0;
	}
	else
	{
		$squarenumber = $number * $number;
	}
	
	return $squarenumber;
}

?>
