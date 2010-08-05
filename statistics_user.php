
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



require_once(dirname(__FILE__).'/classes/core/startup.php');
require_once(dirname(__FILE__).'/config-defaults.php');
require_once(dirname(__FILE__).'/common.php');
require_once($homedir.'/classes/core/class.progressbar.php');
require_once(dirname(__FILE__).'/classes/core/language.php');
require_once($homedir.'/statistics_function.php');


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
        // CHANGE JSW_NZ - let's get the survey title for display
        $thisSurveyTitle = $surveyinfo["name"];
        // CHANGE JSW_NZ - let's get css from individual template.css - so define path
        $thisSurveyCssPath = $surveyinfo["template"];
        if ($surveyinfo['publicstatistics']!='Y')
        {
            safe_die('The public statistics for this survey are deactivated.');
        }
         
        //check if graphs should be shown for this survey
        if ($surveyinfo['publicgraphs']=='Y')
        {
            $publicgraphs = 1;
        } else {
            $publicgraphs = 0;
        }
    }
}

//we collect all the output within this variable
$statisticsoutput ='';


//for creating graphs we need some more scripts which are included here
//True -> include
//False -> forget about charts
if (isset($publicgraphs) && $publicgraphs == 1)
{
    require_once('classes/pchart/pchart/pChart.class');
    require_once('classes/pchart/pchart/pData.class');
    require_once('classes/pchart/pchart/pCache.class');

    $MyCache = new pCache($tempdir.'/');
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



//pick the best font file if font setting is 'auto'
if ($chartfontfile=='auto')
{
    $chartfontfile='vera.ttf';
    if ( $language=='ar')
    {
        $chartfontfile='KacstOffice.ttf';
    }
    elseif  ($language=='fa' )
    {
        $chartfontfile='KacstFarsi.ttf';
    }

}



//set survey language for translations
$clang = SetSurveyLanguage($surveyid, $language);


//Create header (fixes bug #3097)
$surveylanguage= $language;
sendcacheheaders();
if ( !$embedded )
{
    $header=  "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n"
    . "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"".$surveylanguage."\" lang=\"".$surveylanguage."\"";
    if (getLanguageRTL($surveylanguage))
    {
        $header.=" dir=\"rtl\" ";
    }
    $header.= ">\n\t<head>\n"
    . "<title>$sitename</title>\n"
    . "<meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\" />\n"
    . "<link href=\"templates/".$thisSurveyCssPath."/template.css\" rel=\"stylesheet\" type=\"text/css\" />\n"
    . "</head>\n<body>\n";

    echo $header;
}

global $embedded_headerfunc;

if ( function_exists( $embedded_headerfunc ) )
echo $embedded_headerfunc();


/*
 * only show questions where question attribute "public_statistics" is set to "1"
 */
$query = "SELECT ".db_table_name("questions").".*, group_name, group_order\n"
."FROM ".db_table_name("questions").", ".db_table_name("groups").", ".db_table_name("question_attributes")."\n"
."WHERE ".db_table_name("groups").".gid=".db_table_name("questions").".gid\n"
."AND ".db_table_name("groups").".language='".$language."'\n"
."AND ".db_table_name("questions").".language='".$language."'\n"
."AND ".db_table_name("questions").".sid=$surveyid\n"
."AND ".db_table_name("questions").".qid=".db_table_name("question_attributes").".qid\n"
."AND ".db_table_name("question_attributes").".attribute='public_statistics'\n";
if ($databasetype=='mssql_n' or $databasetype=='mssql' or $databasetype=='odbc_mssql' or $databasetype=="mssqlnative")
{
    $query .="AND CAST(CAST(".db_table_name("question_attributes").".value as varchar) as int)='1'\n";
}
else
{
    $query .="AND ".db_table_name("question_attributes").".value='1'\n";
}




//execute query
$result = db_execute_assoc($query) or safe_die("Couldn't do it!<br />$query<br />".$connect->ErrorMsg());

//store all the data in $rows
$rows = $result->GetRows();


//SORT IN NATURAL ORDER!
usort($rows, 'GroupOrderThenQuestionOrder');

//put the question information into the filter array
$filters = array();
foreach ($rows as $row)
{
    //store some column names in $filters array
    $filters[]=array($row['qid'],
    $row['gid'],
    $row['type'],
    $row['title'],
    $row['group_name'],
    FlattenText($row['question']));
}

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

        /*
 * let's go through the filter array which contains
 * 	['qid'],
 ['gid'],
 ['type'],
 ['title'],
 ['group_name'],
 ['question'];
         */

$currentgroup='';
foreach ($filters as $flt)
{
    //SGQ identifier
    $myfield = "{$surveyid}X{$flt[1]}X{$flt[0]}";

    //let's switch through the question type for each question
    switch ($flt[2])
    {
        case "K": // Multiple Numerical
        case "Q": // Multiple Short Text
            //get answers
            $query = "SELECT title as code, question as answer FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language = '{$language}' ORDER BY question_order";
            $result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());

            //go through all the (multiple) answers
            while ($row=$result->FetchRow())
            {
                $myfield2=$flt[2].$myfield.$row[0];
                $allfields[] = $myfield2;
            }
            break;
        case "A": // ARRAY OF 5 POINT CHOICE QUESTIONS
        case "B": // ARRAY OF 10 POINT CHOICE QUESTIONS
        case "C": // ARRAY OF YES\No\$clang->gT("Uncertain") QUESTIONS
        case "E": // ARRAY OF Increase/Same/Decrease QUESTIONS
        case "F": // FlEXIBLE ARRAY
        case "H": // ARRAY (By Column)
            //get answers
            $query = "SELECT title as code, question as answer FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language = '{$language}' ORDER BY question_order";
            $result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());

            //go through all the (multiple) answers
            while ($row=$result->FetchRow())
            {
                $myfield2 = $myfield.$row[0];
                $allfields[]=$myfield2;
            }
            break;
        // all "free text" types (T, U, S)  get the same prefix ("T")
        case "T": // Long free text
        case "U": // Huge free text
        case "S": // Short free text
            $myfield="T$myfield";
            $allfields[] = $myfield;
            break;
        case ";":  //ARRAY (Multi Flex) (Text)
        case ":":  //ARRAY (Multi Flex) (Numbers)
            $query = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language='{$language}' ORDER BY question_order";
            $result = db_execute_num($query) or die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
            while ($row=$result->FetchRow())
            {
                $fquery = "SELECT * FROM ".db_table_name("questions")." WHERE parent_qid={$flt[0]} AND language='{$language}' AND scale_id=1 ORDER BY question_order, title";
                $fresult = db_execute_assoc($fquery);
                while ($frow = $fresult->FetchRow())
                {
                    $myfield2 = "T".$myfield . $row[0] . "_" . $frow['title'];
                $allfields[]=$myfield2;
            }
            }
            break;
        case "R": //RANKING
            //get some answers
            $query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
            $result = db_execute_assoc($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());

            //get number of answers
            $count = $result->RecordCount();

            //loop through all answers. if there are 3 items to rate there will be 3 statistics
            for ($i=1; $i<=$count; $i++)
            {
                $myfield2 = "R" . $myfield . $i . "-" . strlen($i);
                $allfields[]=$myfield2;
            }
            break;
        //Boilerplate questions are only used to put some text between other questions -> no analysis needed
        case "X":  //This is a boilerplate question and it has no business in this script
            break;
        case "1": // MULTI SCALE
            //get answers
            $query = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language='{$language}' ORDER BY question_order";
            $result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());

            //loop through answers
            while ($row=$result->FetchRow())
            {
                //----------------- LABEL 1 ---------------------
                $myfield2 = $myfield . "$row[0]#0";
                $allfields[]=$myfield2;
                //----------------- LABEL 2 ---------------------
                $myfield2 = $myfield . "$row[0]#1";
                $allfields[]=$myfield2;
            }	//end WHILE -> loop through all answers
            break;

        case "P":  //P - Multiple options with comments
        case "M":  //M - Multiple options
        case "N":  //N - Numerical input
        case "D":  //D - Date
            $myfield2 = $flt[2].$myfield;
                    $allfields[]=$myfield2;
            break;
        default:   //Default settings    
            $allfields[] = $myfield;
            break;

    }	//end switch -> check question types and create filter forms
}
//end foreach -> loop through all questions with "public_statistics" enabled

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
// CHANGE JSW_NZ - let's allow html formatted questions to show
$statisticsoutput .= "\n<div id='statsContainer'>\n"
."\t<div id='statsHeader'> \n"
."\t\t<div class='statsSurveyTitle'>"
."$thisSurveyTitle</div>\n"
."\t\t<div class='statsNumRecords'>"
.$clang->gT("Total records in survey")." : $totalrecords</div>\n";

//close statsHeader
$statisticsoutput .= "\t</div>\n";


//push progress bar from 35 to 40
$process_status = 40;

//Show Summary results
if (isset($summary) && $summary)
{
    //"Generating Summaries ..." is shown above the progress bar
    $prb->setLabelValue('txt1',$clang->gT('Generating summaries ...'));
    $prb->moveStep($process_status);

    //let's run through the survey // Fixed bug 3053 with array_unique
    $runthrough=array_unique($summary);

    //loop through all selected questions
    foreach ($runthrough as $rt)
    {

        //update progress bar
        if ($process_status < 100) $process_status++;
        $prb->moveStep($process_status);

    }	// end foreach -> loop through all questions

    $statisticsoutput .= generate_statistics($surveyid, $summary, $summary, $publicgraphs, 'html',null,$language,false);

                //output
    $statisticsoutput .= "<br />\n"
    . "</div>\n";

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


//output footer
echo getFooter();


//Delete all Session Data
$_SESSION['finished'] = true;


?>
