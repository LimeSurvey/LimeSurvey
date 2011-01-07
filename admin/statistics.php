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
 * We need this later:
 *  1 - Array Dual Scale
 *  5 - 5 Point Choice
 *  A - Array (5 Point Choice)
 *  B - Array (10 Point Choice)
 *  C - Array (Yes/No/Uncertain)
 *  D - Date
 *  E - Array (Increase, Same, Decrease)
 *  F - Array (Flexible Labels)
 *  G - Gender
 *  H - Array (Flexible Labels) by Column
 *  I - Language Switch
 *  K - Multiple Numerical Input
 *  L - List (Radio)
 *  M - Multiple choice
 *  N - Numerical Input
 *  O - List With Comment
 *  P - Multiple choice with comments
 *  Q - Multiple Short Text
 *  R - Ranking
 *  S - Short Free Text
 *  T - Long Free Text
 *  U - Huge Free Text
 *  X - Boilerplate Question
 *  Y - Yes/No
 *  ! - List (Dropdown)
 *  : - Array (Flexible Labels) multiple drop down
 *  ; - Array (Flexible Labels) multiple texts
 *  | - File Upload


 Debugging help:
 echo '<script language="javascript" type="text/javascript">alert("HI");</script>';
 */

//split up results to extend statistics -> NOT WORKING YET! DO NOT ENABLE THIS!
$showcombinedresults = 0;

/*
 * this variable is used in the function shortencode() which cuts off a question/answer title
 * after $maxchars and shows the rest as tooltip
 */
$maxchars = 50;



//don't call this script directly!
if (isset($_REQUEST['homedir'])) {die('You cannot start this script directly');}

//some includes, the progressbar is used to show a progressbar while generating the graphs
//include_once("login_check.php");
require_once('classes/core/class.progressbar.php');

//we collect all the output within this variable
$statisticsoutput ='';

//output for chosing questions to cross query
$cr_statisticsoutput = '';

// This gets all the 'to be shown questions' from the POST and puts these into an array
$summary=returnglobal('summary');
$statlang=returnglobal('statlang');

//if $summary isn't an array we create one
if (isset($summary) && !is_array($summary)) {
    $summary = explode("+", $summary);
}

//no survey ID? -> come and get one
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}

//still no survey ID -> error
if (!$surveyid)
{
    //need to have a survey id
    $statisticsoutput .= "<center>You have not selected a survey!</center>";
    exit;
}

// Set language for questions and answers to base language of this survey
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
    elseif  ($language=='el' )
    {
        $chartfontfile='DejaVuLGCSans.ttf';
    }
    elseif  ($language=='zh-Hant-HK' || $language=='zh-Hant-TW' || $language=='zh-Hans')
    {
        $chartfontfile='fireflysung.ttf';
    }

}
//$statisticsoutput .= "
//<script type='text/javascript'' >
//<!--
//function selectAll(name){
//	//var name=name;
//
//	alert(name);
//
//	temp = document.+name+.elements.length;
//
//    for (i=0; i < temp; i++) {
//    if(document.+name+.elements[i].checked == 1)
//    	{document.+name+.elements[i].checked = 0;
//    	 document.+name+.+name+_btn.value = 'Select All'; }
//    else {document.+name.elements[i].checked = 1;
//   	 document.+name+.+name+_btn.value = 'Deselect All'; }
//	}
//}
////-->
//</script>";

//hide/show the filter
//filtersettings by default aren't shown when showing the results
$statisticsoutput .= '<script type="text/javascript" src="scripts/statistics.js"></script>';

//headline with all icons for available statistic options
//Get the menubar
$statisticsoutput .= browsemenubar($clang->gT("Quick statistics"))


//we need a form which can pass the selected data later
."<form method='post' name='formbuilder' action='$scriptname?action=statistics#start'>\n";

//Select public language file
$query = "SELECT datestamp FROM {$dbprefix}surveys WHERE sid=$surveyid";
$result = db_execute_assoc($query) or safe_die("Error selecting language: <br />".$query."<br />".$connect->ErrorMsg());

/*
 * check if there is a datestamp available for this survey
 * yes -> $datestamp="Y"
 * no -> $datestamp="N"
 */
while ($row=$result->FetchRow()) {$datestamp=$row['datestamp'];}



// 1: Get list of questions from survey

/*
 * We want to have the following data
 * a) "questions" -> all table namens, e.g.
 * qid
 * sid
 * gid
 * type
 * title
 * question
 * preg
 * help
 * other
 * mandatory
 * lid
 * lid1
 * question_order
 * language
 *
 * b) "groups" -> group_name + group_order *
 */
$query = "SELECT questions.*, groups.group_name, groups.group_order\n"
." FROM ".db_table_name("questions") ." as questions, ".db_table_name("groups")." as groups\n"
." WHERE groups.gid=questions.gid\n"
." AND groups.language='".$language."'\n"
." AND questions.language='".$language."'\n"
." AND questions.parent_qid=0\n"
." AND questions.sid=$surveyid";
$result = db_execute_assoc($query) or safe_die("Couldn't do it!<br />$query<br />".$connect->ErrorMsg());

//store all the data in $rows
$rows = $result->GetRows();

//SORT IN NATURAL ORDER!
usort($rows, 'GroupOrderThenQuestionOrder');

//put the question information into the filter array
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

//var_dump($filters);
// SHOW ID FIELD

$statisticsoutput .= "<div class='header ui-widget-header'>".$clang->gT("General filters")."</div><div id='statistics_general_filter'>";


$grapherror='';
if (!function_exists("gd_info")) {
    $grapherror.='<br />'.$clang->gT('You do not have the GD Library installed. Showing charts requires the GD library to function properly.');
    $grapherror.='<br />'.$clang->gT('visit http://us2.php.net/manual/en/ref.image.php for more information').'<br />';
}
elseif (!function_exists("imageftbbox")) {
    $grapherror.='<br />'.$clang->gT('You do not have the Freetype Library installed. Showing charts requires the Freetype library to function properly.');
    $grapherror.='<br />'.$clang->gT('visit http://us2.php.net/manual/en/ref.image.php for more information').'<br />';
}
if ($grapherror!='')
{
    unset($_POST['usegraph']);
}


//pre-selection of filter forms
if (incompleteAnsFilterstate() == "filter")
{
    $selecthide="selected='selected'";
    $selectshow="";
    $selectinc="";
}
elseif (incompleteAnsFilterstate() == "inc")
{
    $selecthide="";
    $selectshow="";
    $selectinc="selected='selected'";
}
else
{
    $selecthide="";
    $selectshow="selected='selected'";
    $selectinc="";
}
$statisticsoutput .="<fieldset style='clear:both;'><legend>".$clang->gT("Data selection")."</legend><ul>";
$statisticsoutput .="<li><label for='filterinc'>".$clang->gT("Include:")."</label><select name='filterinc' id='filterinc'>\n"
."<option value='show' $selectshow>".$clang->gT("All responses")."</option>\n"
."<option value='filter' $selecthide>".$clang->gT("Completed responses only")."</option>\n"
."<option value='incomplete' $selectinc>".$clang->gT("Incomplete responses only")."</option>\n"
."</select></li>\n"

."<li><label for='viewsummaryall'>".$clang->gT("View summary of all available fields")."</label>
                <input type='checkbox' id='viewsummaryall' name='viewsummaryall' ";
if (isset($_POST['viewsummaryall'])) {$statisticsoutput .= "checked='checked'";}
$statisticsoutput.="/></li>";

$statisticsoutput .="<li id='vertical_slide'";
//if ($selecthide!='')
//{
//    $statisticsoutput .= " style='display:none' ";
//}
$statisticsoutput.=" ><label id='noncompletedlbl' for='noncompleted' title='".$clang->gT("Count stats for each question based only on the total number of responses for which the question was displayed")."'>".$clang->gT("Subtotals based on displayed questions")."</label>
                <input type='checkbox' id='noncompleted' name='noncompleted' ";
if (isset($_POST['noncompleted'])) {$statisticsoutput .= "checked='checked'";}
$statisticsoutput.=" />\n</li>\n";

$survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
$survlangs [] = GetBaseLanguageFromSurveyID($surveyid);
$language_options="";
foreach ($survlangs as $survlang)
{
    $language_options .= "\t<option value=\"{$survlang}\"";
    if ($_SESSION['adminlang'] == $survlang)
    {
        $language_options .= "selected=\"selected\" " ;
    }
    $language_options .= ">".getLanguageNameFromCode($survlang,true)."</option>\n";
}

$statisticsoutput .="<li><label for='statlang'>".$clang->gT("Statistics report language")."</label>"
. " <select name=\"statlang\" id=\"statlang\">".$language_options."</select>\n";

$statisticsoutput.="\n</ul></fieldset>\n";

$statisticsoutput .= "<fieldset id='left'><legend>".$clang->gT("Response ID")."</legend><ul><li>"
."<label for='idG'>".$clang->gT("Greater than:")."</label>\n"
."<input type='text' id='idG' name='idG' size='10' value='";
if (isset($_POST['idG'])){$statisticsoutput .= sanitize_int($_POST['idG']);}
$statisticsoutput .= "' onkeypress=\"return goodchars(event,'0123456789')\" /></li><li><label for='idL'>\n"
.$clang->gT("Less than:")."</label>\n"
."<input type='text' id='idL' name='idL' size='10' value='";
if (isset($_POST['idL'])) {$statisticsoutput .= sanitize_int($_POST['idL']);}
$statisticsoutput .= "' onkeypress=\"return goodchars(event,'0123456789')\" /></li></ul></fieldset>\n";
$statisticsoutput .= "<input type='hidden' name='summary[]' value='idG' />";
$statisticsoutput .= "<input type='hidden' name='summary[]' value='idL' />";


//if the survey contains timestamps you can filter by timestamp, too
if (isset($datestamp) && $datestamp == "Y") {


    $statisticsoutput .= "<fieldset id='right'><legend>".$clang->gT("Submission date")."</legend><ul><li>"
    ."<label for='datestampE'>".$clang->gT("Equals:")."</label>\n"
    ."<input class='popupdate' id='datestampE' name='datestampE' type='text' value='";
    if (isset($_POST['datestampE'])) {$statisticsoutput .= $_POST['datestampE'];}
    $statisticsoutput .= "' /></li><li><label for='datestampG'>\n"
    ."&nbsp;&nbsp;".$clang->gT("Later than:")."</label>\n"
    ."<input class='popupdatetime' id='datestampG' name='datestampG' value='";
    if (isset($_POST['datestampG'])) {$statisticsoutput .= $_POST['datestampG'];}
    $statisticsoutput .= "' type='text' /></li><li><label for='datestampL'> ".$clang->gT("Earlier than:")."</label><input  class='popupdatetime' id='datestampL' name='datestampL' value='";
    if (isset($_POST['datestampL'])) {$statisticsoutput .= $_POST['datestampL'];}
    $statisticsoutput .= "' type='text' /></li></ul></fieldset>\n";
    $statisticsoutput .= "<input type='hidden' name='summary[]' value='datestampE' />";
    $statisticsoutput .= "<input type='hidden' name='summary[]' value='datestampG' />";
    $statisticsoutput .= "<input type='hidden' name='summary[]' value='datestampL' />";

}


$statisticsoutput .="<fieldset><legend>".$clang->gT("Output options")."</legend><ul>"

."<li><label for='usegraph'>".$clang->gT("Show graphs")."</label><input type='checkbox' id='usegraph' name='usegraph' ";
if (isset($_POST['usegraph'])) {$statisticsoutput .= "checked='checked'";}
$statisticsoutput .= "/><br />";
if ($grapherror!='')
{
    $statisticsoutput.="<span id='grapherror' style='display:none'>$grapherror<hr /></span>";
}
$statisticsoutput.="</li>\n";

//Output selector
$statisticsoutput .= "<li>"
."<label>"
.$clang->gT("Select output format").":</label>"
."<input type='radio' name='outputtype' value='html' checked='checked' /><label for='outputtype'>HTML</label> <input type='radio' name='outputtype' value='pdf' /><label for='outputtype'>PDF</label> <input type='radio' onclick='nographs();' name='outputtype' value='xls' /><label for='outputtype'>Excel</label>";


$statisticsoutput .= "</ul></fieldset></div><p>"
."<input type='submit' value='".$clang->gT("View stats")."' />\n"
."<input type='button' value='".$clang->gT("Clear")."' onclick=\"window.open('$scriptname?action=statistics&amp;sid=$surveyid', '_top')\" />\n";

//second row below options -> filter settings headline
$statisticsoutput.="<div class='header header_statistics'>"
."<img src='$imageurl/plus.gif' align='right' id='showfilter' /><img src='$imageurl/minus.gif' align='right' id='hidefilter' />"
.$clang->gT("Response filters")
."</div>\n";

$filterchoice_state=returnglobal('filterchoice_state');
$statisticsoutput.="<input type='hidden' id='filterchoice_state' name='filterchoice_state' value='{$filterchoice_state}' />\n";

$statisticsoutput .="<table cellspacing='0' cellpadding='0' width='100%' id='filterchoices' ";
if ($filterchoice_state!='')
{
    $statisticsoutput .= " style='display:none' ";
}
$statisticsoutput .=">";


/*
 * let's go through the filter array which contains
 * 	['qid'],
 ['gid'],
 ['type'],
 ['title'],
 ['group_name'],
 ['question'],
 ['lid'],
 ['lid1']);
 */

$currentgroup='';
foreach ($filters as $flt)
{
    //is there a previous question type set?
    if (!isset($previousquestiontype)) {$previousquestiontype="";}


    //does gid equal $currentgroup?
    if ($flt[1] != $currentgroup)
    {
        //If the groupname has changed, start a new row
        if ($currentgroup!='')
        {
            //if we've already drawn a table for a group, and we're changing - close off table
            $statisticsoutput .= "<!-- Close filter group --></tr>\n</table></div></td></tr>\n";
        }

        $statisticsoutput .= "\t\t<tr><td><div class='header ui-widget-header'>\n"
		
        ."<input type=\"checkbox\" id='btn_$flt[1]' onclick=\"selectCheckboxes('grp_$flt[1]', 'summary[]', 'btn_$flt[1]');\" />"

        //use current groupname and groupid as heading
        ."<font size='1'><strong>$flt[4]</strong> (".$clang->gT("Question group")." $flt[1])</font></div></td></tr>\n\t\t"
        ."<tr><td align='center'>\n"
        ."<div id='grp_$flt[1]'><table class='filtertable'><tr>\n";

        //counter which is used to adapt layout depending on counter #
        $counter=0;
    }

    //we don't want more than 4 questions in a row
    //and we need a new row after each multiple/array question
    if (isset($counter) && $counter == 4 ||
    ($previousquestiontype == "1" ||
    $previousquestiontype == "A" ||
    $previousquestiontype == "B" ||
    $previousquestiontype == "C" ||
    $previousquestiontype == "E" ||
    $previousquestiontype == "F" ||
    $previousquestiontype == "H" ||
    $previousquestiontype == "K" ||
    $previousquestiontype == "Q" ||
    $previousquestiontype == "R" ||
    $previousquestiontype == ":" ||
    $previousquestiontype == ";"))
    {
        $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>"; $counter=0;
    }

    /*
     * remember: $flt is structured like this
     *  ['qid'],
     ['gid'],
     ['type'],
     ['title'],
     ['group_name'],
     ['question'],
     ['lid'],
     ['lid1']);
     */

    //SGQ identifier
    $myfield = "{$surveyid}X{$flt[1]}X{$flt[0]}";

    //full question title
    $niceqtext = FlattenText($flt[5]);

    /*
     * Check question type: This question types will be used (all others are separated in the if clause)
     *  5 - 5 Point Choice
     G - Gender
     I - Language Switch
     L - List (Radio)
     M - Multiple choice
     N - Numerical Input
     | - File Upload
     O - List With Comment
     P - Multiple choice with comments
     Y - Yes/No
     ! - List (Dropdown) )
     */
    if ($flt[2]=='M' || $flt[2]=='P' || $flt[2]=='N' || $flt[2]=='L' || $flt[2]=='5'
     || $flt[2]=='G' || $flt[2]=='I' || $flt[2]=='O' || $flt[2]=='Y' || $flt[2]=='!') //Have to make an exception for these types!
    {

        $statisticsoutput .= "\t\t\t\t<td align='center'>";

        //Multiple choice:
        if ($flt[2] == "M") {$myfield = "M$myfield";}
        if ($flt[2] == "P") {$myfield = "P$myfield";}

        // File Upload will need special filters in future, hence the special treatment
        if ($flt[2] == "|") {$myfield = "|$myfield";}
        
        //numerical input will get special treatment (arihtmetic mean, standard derivation, ...)
        if ($flt[2] == "N") {$myfield = "N$myfield";}
        $statisticsoutput .= "<input type='checkbox'  id='filter$myfield' name='summary[]' value='$myfield'";

        /*
         * one of these conditions has to be true
         * 1. SGQ can be found within the summary array
         * 2. M-SGQ can be found within the summary array (M = Multiple choice)
         * 3. N-SGQ can be found within the summary array (N = numerical input)
         *
         * Always remember that we just have very few question types that are checked here
         * due to the if ouside this section!
         *
         * Auto-check the question types mentioned above
         */
        if (isset($summary) && (array_search("{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE  
            || array_search("M{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE 
            || array_search("P{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE 
            || array_search("N{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
        {$statisticsoutput .= " checked='checked'";}

        //show speaker symbol which contains full question text
        $statisticsoutput .= " /><label for='filter$myfield'>".showspeaker(FlattenText($flt[5]))
        ."</label><br />\n";

        //numerical question type -> add some HTML to the output
        //if ($flt[2] == "N") {$statisticsoutput .= "</font>";}		//removed to correct font error
        if ($flt[2] != "N" && $flt[2] != "|") {$statisticsoutput .= "\t\t\t\t<select name='";}

        //Multiple choice ("M"/"P") -> add "M" to output
        if ($flt[2] == "M" ) {$statisticsoutput .= "M";}
        if ($flt[2] == "P" ) {$statisticsoutput .= "P";}

        //numerical -> add SGQ to output
        if ($flt[2] != "N" && $flt[2] != "|") {$statisticsoutput .= "{$surveyid}X{$flt[1]}X{$flt[0]}[]' multiple='multiple'>\n";}

    }	//end if -> filter certain question types

    $statisticsoutput .= "\t\t\t\t\t<!-- QUESTION TYPE = $flt[2] -->\n";
    /////////////////////////////////////////////////////////////////////////////////////////////////
    //This section presents the filter list, in various different ways depending on the question type
    /////////////////////////////////////////////////////////////////////////////////////////////////

    //let's switch through the question type for each question
    switch ($flt[2])
    {
        case "K": // Multiple Numerical
            $statisticsoutput .= "\t\t\t\t\t</tr>\n\t\t\t\t\t<tr>\n";

            //get answers
            $query = "SELECT title as code, question as answer FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language = '{$language}' ORDER BY question_order";
            $result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());

            //counter is used for layout
            $counter2=0;

            //go through all the (multiple) answers
            while ($row=$result->FetchRow())
            {
                /*
                 * filter form for numerical input
                 * - checkbox
                 * - greater than
                 * - less than
                 */
                $myfield1="K".$myfield.$row[0];
                $myfield2="K{$myfield}".$row[0]."G";
                $myfield3="K{$myfield}".$row[0]."L";
                if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
                 
                //start new TD
                $statisticsoutput .= "\t\t\t\t<td align='center' valign='top'>";
                 
                //checkbox
                $statisticsoutput .= "<input type='checkbox'  name='summary[]' value='$myfield1'";
                 
                //check SGQA -> do we want to pre-check the checkbox?
                if (isset($summary) && (array_search("K{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}", $summary) !== FALSE))
                {$statisticsoutput .= " checked='checked'";}
                $statisticsoutput .= " />&nbsp;";
                 
                //show speaker
                $statisticsoutput .= showSpeaker($flt[3]." - ".FlattenText($row[1]))."<br />\n";

                //input fields
                $statisticsoutput .= "\t\t\t\t\t<font size='1'>".$clang->gT("Number greater than").":</font><br />\n"
                ."\t\t\t\t\t<input type='text' name='$myfield2' value='";
                if (isset($_POST[$myfield2])){$statisticsoutput .= $_POST[$myfield2];}

                //check number input using JS
                $statisticsoutput .= "' onkeypress=\"return goodchars(event,'0123456789.,')\" /><br />\n"
                ."\t\t\t\t\t<font size='1'>".$clang->gT("Number less than").":</font><br />\n"
                ."\t\t\t\t\t<input type='text' name='$myfield3' value='";
                if (isset($_POST[$myfield3])) {$statisticsoutput .= $_POST[$myfield3];}
                $statisticsoutput .= "' onkeypress=\"return goodchars(event,'0123456789.,')\" /><br />\n";
                 
                //we added 1 form -> increase counter
                $counter2++;
                 
            }
            break;



        case "Q": // Multiple Short Text

            //new section
            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";

            //get subqestions
            $query = "SELECT title as code, question as answer FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language='{$language}' ORDER BY question_order";
            $result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
            $counter2=0;

            //loop through all answers
            while ($row = $result->FetchRow())
            {
                //collecting data for output, for details see above (question type "N")
                 
                //we have one input field for each answer
                $myfield2 = "Q".$myfield."$row[0]";
                if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
                 
                $statisticsoutput .= "\t\t\t\t<td align='center' valign='top'>";
                $statisticsoutput .= "<input type='checkbox'  name='summary[]' value='$myfield2'";
                if (isset($summary) && (array_search("Q{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}", $summary) !== FALSE))
                {$statisticsoutput .= " checked='checked'";}
                 
                $statisticsoutput .= " />&nbsp;";
                $statisticsoutput .= showSpeaker($flt[3]." - ".FlattenText($row[1]))
                ."<br />\n"
                ."\t\t\t\t\t<font size='1'>".$clang->gT("Responses containing").":</font><br />\n"
                ."\t\t\t\t\t<input type='text' name='$myfield2' value='";
                if (isset($_POST[$myfield2]))
                {$statisticsoutput .= $_POST[$myfield2];}
                 
                $statisticsoutput .= "' />";
                $counter2++;
            }
            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $counter=0;
            break;



            /*
             * all "free text" types (T, U, S)  get the same prefix ("T")
             */
        case "T": // Long free text
        case "U": // Huge free text

            $myfield2="T$myfield";
            $statisticsoutput .= "\t\t\t\t<td align='center' valign='top'>";
            $statisticsoutput .= "<input type='checkbox'  name='summary[]' value='$myfield2'";
            if (isset($summary) && (array_search("T{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
            {$statisticsoutput .= " checked='checked'";}

            $statisticsoutput .= " />&nbsp;"
            ."&nbsp;".showSpeaker($niceqtext)
            ."<br />\n"
            ."\t\t\t\t\t<font size='1'>".$clang->gT("Responses containing").":</font><br />\n"
            ."\t\t\t\t\t<textarea name='$myfield2' rows='3' cols='80'>";

            if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}

            $statisticsoutput .= "</textarea>";
            break;



        case "S": // Short free text

            $myfield2="T$myfield";
            $statisticsoutput .= "\t\t\t\t<td align='center' valign='top'>";
            $statisticsoutput .= "<input type='checkbox'  name='summary[]' value='$myfield2'";

            if (isset($summary) && (array_search("T{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
            {$statisticsoutput .= " checked='checked'";}

            $statisticsoutput .= " />&nbsp;"
            ."&nbsp;".showSpeaker($niceqtext)
            ."<br />\n"
            ."\t\t\t\t\t<font size='1'>".$clang->gT("Responses containing").":</font><br />\n"
            ."\t\t\t\t\t<input type='text' name='$myfield2' value='";

            if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}

            $statisticsoutput .= "' />";
            $statisticsoutput .= "\t\t\t\t</td>\n";
            break;



        case "N": // Numerical

            //textfields for greater and less than X
            $myfield2="{$myfield}G";
            $myfield3="{$myfield}L";
            $statisticsoutput .= "\t\t\t\t\t<font size='1'>".$clang->gT("Number greater than").":</font><br />\n"
            ."\t\t\t\t\t<input type='text' name='$myfield2' value='";

            if (isset($_POST[$myfield2])){$statisticsoutput .= $_POST[$myfield2];}

            $statisticsoutput .= "' onkeypress=\"return goodchars(event,'0123456789.,')\" /><br />\n"
            ."\t\t\t\t\t<font size='1'>".$clang->gT("Number less than").":</font><br />\n"
            ."\t\t\t\t\t<input type='text' name='$myfield3' value='";

            if (isset($_POST[$myfield3])) {$statisticsoutput .= $_POST[$myfield3];}

            //only numeriacl input allowed -> check using JS
            $statisticsoutput .= "' onkeypress=\"return goodchars(event,'0123456789.,')\" /><br />\n";

            //put field names into array

            break;


        case "|": // File Upload

            // Number of files uploaded for greater and less than X
            $myfield2 = "{$myfield}G";
            $myfield3 = "{$myfield}L";
            $statisticsoutput .= "\t\t\t\t\t<font size='1'>".$clang->gT("Number of files greater than").":</font><br />\n"
            ."\t\t\t\t\t<input type='text' name='$myfield2' value='";

            if (isset($_POST[$myfield2])){$statisticsoutput .= $_POST[$myfield2];}

            $statisticsoutput .= "' onkeypress=\"return goodchars(event,'0123456789.,')\" /><br />\n"
            ."\t\t\t\t\t<font size='1'>".$clang->gT("Number of files less than").":</font><br />\n"
            ."\t\t\t\t\t<input type='text' name='$myfield3' value='";

            if (isset($_POST[$myfield3])) {$statisticsoutput .= $_POST[$myfield3];}

            //only numeriacl input allowed -> check using JS
            $statisticsoutput .= "' onkeypress=\"return goodchars(event,'0123456789.,')\" /><br />\n";

            //put field names into array

            break;


            /*
             * DON'T show any statistics for date questions
             * because there aren't any statistics implemented yet!
             *
             * Only filtering by date is possible.
             *
             * See bug report #2539 and
             * feature request #2620
             */
        case "D": // Date

            /*
             * - input name
             * - date equals
             * - date less than
             * - date greater than
             */
            $myfield2="D$myfield";
            $myfield3="$myfield2=";
            $myfield4="$myfield2<";
            $myfield5="$myfield2>";
            $statisticsoutput .= "\t\t\t\t<td align='center' valign='top'>";

            $statisticsoutput .= "<input type='checkbox'  name='summary[]' value='$myfield2'";

            if (isset($summary) && (array_search("D{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
            {$statisticsoutput .= " checked='checked'";}

            $statisticsoutput .= " /><strong>";
            $statisticsoutput .= showSpeaker($niceqtext)
            ."<br />\n"
		
            ."\t\t\t\t\t<font size='1'>".$clang->gT("Date (YYYY-MM-DD) equals").":<br />\n"
            ."\t\t\t\t\t<input name='$myfield3' type='text' value='";

            if (isset($_POST[$myfield3])) {$statisticsoutput .= $_POST[$myfield3];}

            $statisticsoutput .= "' /><br />\n"
            ."\t\t\t\t\t&nbsp;&nbsp;".$clang->gT("Date is")." >=<br />\n"
            ."\t\t\t\t\t<input name='$myfield4' value='";

            if (isset($_POST[$myfield4])) {$statisticsoutput .= $_POST[$myfield4];}

            $statisticsoutput .= "' type='text' /> <br />"
            .$clang->gT("AND/OR Date is")." <= <br /> <input  name='$myfield5' value='";

            if (isset($_POST[$myfield5])) {$statisticsoutput .= $_POST[$myfield5];}

            $statisticsoutput .= "' type='text' /></font>\n";
            break;



        case "5": // 5 point choice

            //we need a list of 5 entries
            for ($i=1; $i<=5; $i++)
            {
                $statisticsoutput .= "\t\t\t\t\t<option value='$i'";
                 
                //pre-select values which were marked before
                if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($i, $_POST[$myfield]))
                {$statisticsoutput .= " selected";}
                 
                $statisticsoutput .= ">$i</option>\n";
            }

            //End the select which starts before the CASE statement (around line 411)
            $statisticsoutput .="\t\t\t\t</select>\n";
            break;



        case "G": // Gender
            $statisticsoutput .= "\t\t\t\t\t<option value='F'";

            //pre-select values which were marked before
            if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("F", $_POST[$myfield])) {$statisticsoutput .= " selected";}

            $statisticsoutput .= ">".$clang->gT("Female")."</option>\n";
            $statisticsoutput .= "\t\t\t\t\t<option value='M'";

            //pre-select values which were marked before
            if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("M", $_POST[$myfield])) {$statisticsoutput .= " selected";}

            $statisticsoutput .= ">".$clang->gT("Male")."</option>\n\t\t\t\t</select>\n";
            $statisticsoutput .= "\t\t\t\t</td>\n";
            break;



        case "Y": // Yes\No
            $statisticsoutput .= "\t\t\t\t\t<option value='Y'";

            //pre-select values which were marked before
            if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("Y", $_POST[$myfield])) {$statisticsoutput .= " selected";}

            $statisticsoutput .= ">".$clang->gT("Yes")."</option>\n"
            ."\t\t\t\t\t<option value='N'";

            //pre-select values which were marked before
            if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("N", $_POST[$myfield])) {$statisticsoutput .= " selected";}

            $statisticsoutput .= ">".$clang->gT("No")."</option></select>\n";
            break;



        case "I": // Language
            $survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $survlangs[] = GetBaseLanguageFromSurveyID($surveyid);
            foreach ($survlangs  as $availlang)
            {
                $statisticsoutput .= "\t\t\t\t\t<option value='".$availlang."'";
                 
                //pre-select values which were marked before
                if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($availlang, $_POST[$myfield]))
                {$statisticsoutput .= " selected";}

                $statisticsoutput .= ">".getLanguageNameFromCode($availlang,false)."</option>\n";
            }
            break;



            //----------------------- ARRAYS --------------------------

        case "A": // ARRAY OF 5 POINT CHOICE QUESTIONS

            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";

            //get answers
            $query = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language='{$language}' ORDER BY question_order";
            $result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
            $counter2=0;

            //check all the results
            while ($row=$result->FetchRow())
            {
                $myfield2 = $myfield.$row[0];
                $statisticsoutput .= "<!-- $myfield2 - ";
                 
                if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
                 
                $statisticsoutput .= " -->\n";
                 
                if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}

                $statisticsoutput .= "\t\t\t\t<td align='center'>"
                ."<input type='checkbox'  name='summary[]' value='$myfield2'";
                 
                //pre-check
                if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {$statisticsoutput .= " checked='checked'";}
                 
                $statisticsoutput .= " />&nbsp;"
                .showSpeaker($niceqtext." ".str_replace("'", "`", $row[1])." - # ".$flt[3])
                ."<br />\n"
                ."\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple='multiple'>\n";
                 
                //there are always exactly 5 values which have to be listed
                for ($i=1; $i<=5; $i++)
                {
                    $statisticsoutput .= "\t\t\t\t\t<option value='$i'";

                    //pre-select
                    if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($i, $_POST[$myfield2])) {$statisticsoutput .= " selected";}
                    if (isset($_POST[$myfield2]) && $_POST[$myfield2] == $i) {$statisticsoutput .= " selected";}

                    $statisticsoutput .= ">$i</option>\n";
                }
                 
                $statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
                $counter2++;
                 
                //add this to all the other fields
            }

            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $counter=0;
            break;



            //just like above only a different loop
        case "B": // ARRAY OF 10 POINT CHOICE QUESTIONS
            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $query = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language='{$language}' ORDER BY question_order";
            $result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
            $counter2=0;
            while ($row=$result->FetchRow())
            {
                $myfield2 = $myfield . "$row[0]";
                $statisticsoutput .= "<!-- $myfield2 - ";
                 
                if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
                 
                $statisticsoutput .= " -->\n";
                 
                if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}

                $statisticsoutput .= "\t\t\t\t<td align='center'>"; //heading
                $statisticsoutput .= "<input type='checkbox'  name='summary[]' value='$myfield2'";
                 
                if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {$statisticsoutput .= " checked='checked'";}
                 
                $statisticsoutput .= " />&nbsp;"
                .showSpeaker($niceqtext." ".str_replace("'", "`", $row[1])." - # ".$flt[3])
                ."<br />\n"
                ."\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple='multiple'>\n";
                 
                //here wo loop through 10 entries to create a larger output form
                for ($i=1; $i<=10; $i++)
                {
                    $statisticsoutput .= "\t\t\t\t\t<option value='$i'";
                    if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($i, $_POST[$myfield2])) {$statisticsoutput .= " selected";}
                    if (isset($_POST[$myfield2]) && $_POST[$myfield2] == $i) {$statisticsoutput .= " selected";}
                    $statisticsoutput .= ">$i</option>\n";
                }
                 
                $statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
                $counter2++;
            }

            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $counter=0;
            break;



        case "C": // ARRAY OF YES\No\$clang->gT("Uncertain") QUESTIONS
            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";

            //get answers
            $query = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language='{$language}' ORDER BY question_order";
            $result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
            $counter2=0;

            //loop answers
            while ($row=$result->FetchRow())
            {
                $myfield2 = $myfield . "$row[0]";
                $statisticsoutput .= "<!-- $myfield2 - ";
                 
                if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
                 
                $statisticsoutput .= " -->\n";
                 
                if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
                 
                $statisticsoutput .= "\t\t\t\t<td align='center'>"
                ."<input type='checkbox'  name='summary[]' value='$myfield2'";
                 
                if (isset($summary) && array_search($myfield2, $summary)!== FALSE)
                {$statisticsoutput .= " checked='checked'";}
                 
                $statisticsoutput .= " />&nbsp;<strong>"
                .showSpeaker($niceqtext." ".str_replace("'", "`", $row[1])." - # ".$flt[3])
                ."</strong><br />\n"
                ."\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple='multiple'>\n"
                ."\t\t\t\t\t<option value='Y'";
                 
                //pre-select "yes"
                if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("Y", $_POST[$myfield2])) {$statisticsoutput .= " selected";}
                 
                $statisticsoutput .= ">".$clang->gT("Yes")."</option>\n"
                ."\t\t\t\t\t<option value='U'";
                 
                //pre-select "uncertain"
                if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("U", $_POST[$myfield2])) {$statisticsoutput .= " selected";}
                 
                $statisticsoutput .= ">".$clang->gT("Uncertain")."</option>\n"
                ."\t\t\t\t\t<option value='N'";
                 
                //pre-select "no"
                if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("N", $_POST[$myfield2])) {$statisticsoutput .= " selected";}
                 
                $statisticsoutput .= ">".$clang->gT("No")."</option>\n"
                ."\t\t\t\t</select>\n\t\t\t\t</td>\n";
                $counter2++;
                 
                //add to array
            }

            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $counter=0;
            break;



            //similiar to the above one
        case "E": // ARRAY OF Increase/Same/Decrease QUESTIONS
            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $query = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language='{$language}' ORDER BY question_order";
            $result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
            $counter2=0;

            while ($row=$result->FetchRow())
            {
                $myfield2 = $myfield . "$row[0]";
                $statisticsoutput .= "<!-- $myfield2 - ";
                 
                if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
                 
                $statisticsoutput .= " -->\n";
                 
                if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
                 
                $statisticsoutput .= "\t\t\t\t<td align='center'>"
                ."<input type='checkbox'  name='summary[]' value='$myfield2'";
                 
                if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {$statisticsoutput .= " checked='checked'";}
                 
                $statisticsoutput .= " />&nbsp;<strong>"
                .showSpeaker($niceqtext." ".str_replace("'", "`", $row[1])." - # ".$flt[3])
                ."</strong><br />\n"
                ."\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple='multiple'>\n"
                ."\t\t\t\t\t<option value='I'";
                 
                if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("I", $_POST[$myfield2])) {$statisticsoutput .= " selected";}
                 
                $statisticsoutput .= ">".$clang->gT("Increase")."</option>\n"
                ."\t\t\t\t\t<option value='S'";
                 
                if (isset($_POST[$myfield]) && is_array($_POST[$myfield2]) && in_array("S", $_POST[$myfield2])) {$statisticsoutput .= " selected";}
                 
                $statisticsoutput .= ">".$clang->gT("Same")."</option>\n"
                ."\t\t\t\t\t<option value='D'";
                 
                if (isset($_POST[$myfield]) && is_array($_POST[$myfield2]) && in_array("D", $_POST[$myfield2])) {$statisticsoutput .= " selected";}
                 
                $statisticsoutput .= ">".$clang->gT("Decrease")."</option>\n"
                ."\t\t\t\t</select>\n\t\t\t\t</td>\n";
                $counter2++;
            }

            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $counter=0;
            break;

        case ";":  //ARRAY (Multi Flex) (Text)
            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $query = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language='{$language}' AND scale_id=0 ORDER BY question_order";
            $result = db_execute_num($query) or die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
            $counter2=0;
            while ($row=$result->FetchRow())
            {
                $fquery = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language='{$language}' AND scale_id=1 ORDER BY question_order";
                $fresult = db_execute_assoc($fquery);
                while ($frow = $fresult->FetchRow())
                {
                    $myfield2 = "T".$myfield . $row[0] . "_" . $frow['title'];
                    $statisticsoutput .= "<!-- $myfield2 - ";
                    if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
                    $statisticsoutput .= " -->\n";
                    if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
                    $statisticsoutput .= "\t\t\t\t<td align='center'>"
                    ."<input type='checkbox'  name='summary[]' value='$myfield2'";
                    if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {$statisticsoutput .= " checked='checked'";}
                    $statisticsoutput .= " />&nbsp;<strong>"
                    .showSpeaker($niceqtext." ".str_replace("'", "`", $row[1]." [".$frow['question']."]")." - ".$row[0]."/".$frow['title'])
                    ."</strong><br />\n";
                    //$statisticsoutput .= $fquery;
                    $statisticsoutput .= "\t\t\t\t\t<font size='1'>".$clang->gT("Responses containing").":</font><br />\n";
                    $statisticsoutput .= "\t\t\t\t<input type='text' name='{$myfield2}' value='";
                    if(isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
                    $statisticsoutput .= "' />\n\t\t\t\t</td>\n";
                    $counter2++;
                }
            }
            $statisticsoutput .= "\t\t\t\t<td>\n";
            $counter=0;
            break;

        case ":":  //ARRAY (Multi Flex) (Numbers)
            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $query = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language = '{$language}'  AND scale_id=0 ORDER BY question_order";
            $result = db_execute_num($query) or die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
            $counter2=0;
            //Get qidattributes for this question
            $qidattributes=getQuestionAttributes($flt[0]);
            if (trim($qidattributes['multiflexible_max'])!='' && trim($qidattributes['multiflexible_min']) ==''){
                $maxvalue=$qidattributes['multiflexible_max'];
                $minvalue=1;
            }
            if (trim($qidattributes['multiflexible_min'])!='' && trim($qidattributes['multiflexible_max']) ==''){
                $minvalue=$qidattributes['multiflexible_min'];
                $maxvalue=$qidattributes['multiflexible_min'] + 10;
            }
            if (trim($qidattributes['multiflexible_min'])=='' && trim($qidattributes['multiflexible_max']) ==''){
                $minvalue=1;
                $maxvalue=10;
            }
            if (trim($qidattributes['multiflexible_min']) !='' && trim($qidattributes['multiflexible_max']) !=''){
                if($qidattributes['multiflexible_min'] < $qidattributes['multiflexible_max']){
                    $minvalue=$qidattributes['multiflexible_min'];
                    $maxvalue=$qidattributes['multiflexible_max'];
                }
            }

            if (trim($qidattributes['multiflexible_step'])!='') {
                $stepvalue=$qidattributes['multiflexible_step'];
            } else {
                $stepvalue=1;
            }
            if ($qidattributes['multiflexible_checkbox']!=0)
            {
                $minvalue=0;
                $maxvalue=1;
                $stepvalue=1;
            }
            while ($row=$result->FetchRow())
            {
                $fquery = "SELECT * FROM ".db_table_name("questions")." WHERE parent_qid={$flt[0]} AND language='{$language}' AND scale_id=1 ORDER BY question_order, title";
                $fresult = db_execute_assoc($fquery);
                while ($frow = $fresult->FetchRow())
                {
                    $myfield2 = $myfield . $row[0] . "_" . $frow['title'];
                    $statisticsoutput .= "<!-- $myfield2 - ";
                    if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
                    $statisticsoutput .= " -->\n";
                    if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
                    $statisticsoutput .= "\t\t\t\t<td align='center'>"
                    ."<input type='checkbox'  name='summary[]' value='$myfield2'";
                    if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {$statisticsoutput .= " checked='checked'";}
                    $statisticsoutput .= " />&nbsp;<strong>"
                    .showSpeaker($niceqtext." ".str_replace("'", "`", $row[1]." [".$frow['question']."]")." - ".$row[0]."/".$frow['title'])
                    ."</strong><br />\n";
                    //$statisticsoutput .= $fquery;
                    $statisticsoutput .= "\t\t\t\t<select name='{$myfield2}[]' multiple='multiple' rows='5' cols='5'>\n";
                    for($ii=$minvalue; $ii<=$maxvalue; $ii+=$stepvalue)
                    {
                        $statisticsoutput .= "\t\t\t\t\t<option value='$ii'";
                        if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($frow['code'], $_POST[$myfield2])) {$statisticsoutput .= " selected";}
                        $statisticsoutput .= ">$ii</option>\n";
                    }
                    $statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
                    $counter2++;
                }
            }
            $statisticsoutput .= "\t\t\t\t<td>\n";
            $counter=0;
            break;
            /*
             * For question type "F" and "H" you can use labels.
             * The only difference is that the labels are applied to column heading
             * or rows respectively
             */
        case "F": // FlEXIBLE ARRAY
        case "H": // ARRAY (By Column)
            //$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";

            //Get answers. We always use the answer code because the label might be too long elsewise
            $query = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language='{$language}' ORDER BY question_order";
            $result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
            $counter2=0;

            //check all the answers
            while ($row=$result->FetchRow())
            {
                $myfield2 = $myfield . "$row[0]";
                $statisticsoutput .= "<!-- $myfield2 - ";
                 
                if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
                 
                $statisticsoutput .= " -->\n";
                 
                if ($counter2 == 4)
                {
                    $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
                    $counter2=0;
                }
                 
                $statisticsoutput .= "\t\t\t\t<td align='center'>"
                ."<input type='checkbox'  name='summary[]' value='$myfield2'";
                 
                if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {$statisticsoutput .= " checked='checked'";}
                 
                $statisticsoutput .= " />&nbsp;<strong>"
                .showSpeaker($niceqtext." ".str_replace("'", "`", $row[1])." - # ".$flt[3])
                ."</strong><br />\n";
                 
                /*
                 * when hoovering the speaker symbol we show the whole question
                 *
                 * flt[6] is the label ID
                 *
                 * table "labels" contains
                 * - lid
                 * - code
                 * - title
                 * - sortorder
                 * - language
                 */
                $fquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$flt[0]} AND language='{$language}' ORDER BY sortorder, code";
                $fresult = db_execute_assoc($fquery);
                 
                //for debugging only:
                //$statisticsoutput .= $fquery;
                 
                //creating form
                $statisticsoutput .= "\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple='multiple'>\n";
                 
                //loop through all possible answers
                while ($frow = $fresult->FetchRow())
                {
                    $statisticsoutput .= "\t\t\t\t\t<option value='{$frow['code']}'";

                    //pre-select
                    if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($frow['code'], $_POST[$myfield2])) {$statisticsoutput .= " selected";}

                    $statisticsoutput .= ">({$frow['code']}) ".FlattenText($frow['answer'])."</option>\n";
                }
                 
                $statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
                $counter2++;
                 
                //add fields to main array
            }

            //$statisticsoutput .= "\t\t\t\t<td>\n";
            $counter=0;
            break;



        case "R": //RANKING

            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";

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

            $counter2=0;

            //loop through all answers. if there are 3 items to rate there will be 3 statistics
            for ($i=1; $i<=$count; $i++)
            {
                //adjust layout depending on counter
                if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter=0;}
                 
                //myfield is the SGQ identifier
                //myfield2 is just used as comment in HTML like "R40X34X1721-1"
                $myfield2 = "R" . $myfield . $i . "-" . strlen($i);
                $myfield3 = $myfield . $i;
                $statisticsoutput .= "<!-- $myfield2 - ";

                if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
                 
                $statisticsoutput .= " -->\n"
                ."\t\t\t\t<td align='center'>"
                ."<input type='checkbox'  name='summary[]' value='$myfield2'";
                 
                //pre-check
                if (isset($summary) && array_search($myfield2, $summary) !== FALSE) {$statisticsoutput .= " checked='checked'";}
                 
                $statisticsoutput .= " />&nbsp;<strong>"
                .showSpeaker($niceqtext." ".str_replace("'", "`", $row[1])." - # ".$flt[3])
                ."</strong><br />\n"
                ."\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$i}[]' multiple='multiple'>\n";
                 
                //output lists of ranking items
                foreach ($answers as $ans)
                {
                    $statisticsoutput .= "\t\t\t\t\t<option value='$ans[0]'";

                    //pre-select
                    if (isset($_POST[$myfield3]) && is_array($_POST[$myfield3]) && in_array("$ans[0]", $_POST[$myfield3])) {$statisticsoutput .= " selected";}

                    $statisticsoutput .= ">$ans[1]</option>\n";
                }
                 
                $statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
                $counter2++;
                 
                //add averything to main array
            }

            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";

            //Link to rankwinner script - awaiting completion - probably never gonna happen. Mystery creator.
            //          $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr bgcolor='#DDDDDD'>\n"
            //              ."<td colspan=$count align=center>"
            //              ."<input type='button' value='Show Rank Summary' onclick=\"window.open('rankwinner.php?sid=$surveyid&amp;qid=$flt[0]', '_blank', 'toolbar=no, directories=no, location=no, status=yes, menubar=no, resizable=no, scrollbars=no, width=400, height=300, left=100, top=100')\">"
            //              ."</td></tr>\n\t\t\t\t<tr>\n";
            $counter=0;
            unset($answers);
            break;

            //Boilerplate questions are only used to put some text between other questions -> no analysis needed
        case "X": //This is a boilerplate question and it has no business in this script
            $statisticsoutput .= "\t\t\t\t<td></td>";
            break;

        case "1": // MULTI SCALE
            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";

            //special dual scale counter
            $counter2=0;

            //get answers
            $query = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language='{$language}' ORDER BY question_order";
            $result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());

            //loop through answers
            while ($row=$result->FetchRow())
            {

                //----------------- LABEL 1 ---------------------
                //myfield2 = answer code.
                $myfield2 = $myfield . "$row[0]#0";

                //3 lines of debugging output
                $statisticsoutput .= "<!-- $myfield2 - ";
                if (isset($_POST[$myfield2]))
                {
                    $statisticsoutput .= $_POST[$myfield2];
                }
                $statisticsoutput .= " -->\n";

                //some layout adaptions -> new line after 4 entries
                if ($counter2 == 4)
                {
                    $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
                    $counter2=0;
                }

                //output checkbox and question/label text
                $statisticsoutput .= "\t\t\t\t<td align='center'>";
                $statisticsoutput .= "<input type='checkbox' name='summary[]' value='$myfield2'";

                //pre-check
                if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {$statisticsoutput .= " checked='checked'";}

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
                    $labeltitle='';
                }

                $statisticsoutput .= " />&nbsp;<strong>"
                .showSpeaker($niceqtext." [".str_replace("'", "`", $row[1])."] - ".$clang->gT("Label").": ".$labeltitle)
                ."</strong><br />\n";
                 
                /* get labels
                 * table "labels" contains
                 * - lid
                 * - code
                 * - title
                 * - sortorder
                 * - language
                 */
                 
                $fquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$flt[0]} AND language='{$language}' and scale_id=0 ORDER BY sortorder, code";
                $fresult = db_execute_assoc($fquery);

                //this is for debugging only
                //$statisticsoutput .= $fquery;

                $statisticsoutput .= "\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}#{0}[]' multiple='multiple'>\n";

                //list answers
                while ($frow = $fresult->FetchRow())
                {
                    $statisticsoutput .= "\t\t\t\t\t<option value='{$frow['code']}'";

                    //pre-check
                    if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($frow['code'], $_POST[$myfield2])) {$statisticsoutput .= " selected";}

                    $statisticsoutput .= ">({$frow['code']}) ".FlattenText($frow['answer'])."</option>\n";

                }

                $statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
                $counter2++;




                //----------------- LABEL 2 ---------------------
                 
                //myfield2 = answer code
                $myfield2 = $myfield . "$row[0]#1";

                //3 lines of debugging output
                $statisticsoutput .= "<!-- $myfield2 - ";
                if (isset($_POST[$myfield2]))
                {
                    $statisticsoutput .= $_POST[$myfield2];
                }

                $statisticsoutput .= " -->\n";

                //some layout adaptions -> new line after 4 entries
                if ($counter2 == 4)
                {
                    $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
                    $counter2=0;
                }

                //output checkbox and question/label text
                $statisticsoutput .= "\t\t\t\t<td align='center'>";
                $statisticsoutput .= "<input type='checkbox' name='summary[]' value='$myfield2'";

                //pre-check
                if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {$statisticsoutput .= " checked='checked'";}

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

                    $labeltitle2 = '';
                }

                $statisticsoutput .= " />&nbsp;<strong>"
                .showSpeaker($niceqtext." [".str_replace("'", "`", $row[1])."] - ".$clang->gT("Label").": ".$labeltitle2)
                ."</strong><br />\n";
                 
                $fquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$flt[0]} AND language='{$language}' and scale_id=1 ORDER BY sortorder, code";
                $fresult = db_execute_assoc($fquery);

                //this is for debugging only
                //$statisticsoutput .= $fquery;

                $statisticsoutput .= "\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}#{1}[]' multiple='multiple'>\n";

                //list answers
                while ($frow = $fresult->FetchRow())
                {
                    $statisticsoutput .= "\t\t\t\t\t<option value='{$frow['code']}'";

                    //pre-check
                    if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($frow['code'], $_POST[$myfield2])) {$statisticsoutput .= " selected";}

                    $statisticsoutput .= ">({$frow['code']}) ".FlattenText($frow['answer'])."</option>\n";

                }

                $statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
                $counter2++;

            }	//end WHILE -> loop through all answers

            $statisticsoutput .= "\t\t\t\t<td>\n";


            $counter=0;
            break;

        case "P":  //P - Multiple choice with comments
        case "M":  //M - Multiple choice

            //get answers
            $query = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language='{$language}' ORDER BY question_order";
            $result = db_execute_num($query) or safe_die("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());

            //loop through answers
            while ($row=$result->FetchRow())
            {
                $statisticsoutput .= "\t\t\t\t\t\t<option value='{$row[0]}'";

                //pre-check
                if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($row[0], $_POST[$myfield])) {$statisticsoutput .= " selected";}

                $statisticsoutput .= '>'.FlattenText($row[1])."</option>\n";
            }

            $statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
            break;


            /*
             * This question types use the default settings:
             * 	L - List (Radio)
             O - List With Comment
             P - Multiple choice with comments
             ! - List (Dropdown)
             */
        default:

            //get answers
            $query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
            $result = db_execute_num($query) or safe_die("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());

            //loop through answers
            while ($row=$result->FetchRow())
            {
                $statisticsoutput .= "\t\t\t\t\t\t<option value='{$row[0]}'";
                 
                //pre-check
                if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($row[0], $_POST[$myfield])) {$statisticsoutput .= " selected";}
                 
                $statisticsoutput .= '>'.FlattenText($row[1])."</option>\n";
            }

            $statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
            break;

    }	//end switch -> check question types and create filter forms

    $currentgroup=$flt[1];

    if (!isset($counter)) {$counter=0;}
    $counter++;

    //temporary save the type of the previous question
    //used to adjust linebreaks
    $previousquestiontype = $flt[2];

    //Group close
    //$statisticsoutput .= "\n\t\t\t\t<!-- --></tr>\n\t\t\t</table></div></td></tr>\n";
}

//complete output
$statisticsoutput .= "\n\t\t\t\t</tr>\n";






//add last lines to filter forms
$statisticsoutput .= "\t\t\t</table></div>\n"
."\t\t</td></tr>\n";


//add line to separate the the filters from the other options
$statisticsoutput .= "<tr class='statistics-tbl-separator'><td></td></tr>";

$statisticsoutput .= "</table>";




//very last lines of output
$statisticsoutput .= "\t\t<p id='vertical_slide2'>\n"
."\t\t\t<input type='submit' value='".$clang->gT("View stats")."' />\n"
."\t\t\t<input type='button' value='".$clang->gT("Clear")."' onclick=\"window.open('$scriptname?action=statistics&amp;sid=$surveyid', '_top')\" />\n"
."\t\t<input type='hidden' name='sid' value='$surveyid' />\n"
."\t\t<input type='hidden' name='display' value='stats' />\n"
."\t</form><br /><a name='start'></a>\n";

// ----------------------------------- END FILTER FORM ---------------------------------------


//Show Summary results
if (isset($summary) && $summary)
{
    if(isset($_POST['usegraph']))
    {
        $usegraph = 1;
    }
    else
    {
        $usegraph = 0;
    }
    include_once("statistics_function.php");
    $outputType = $_POST['outputtype'];
    switch($outputType){

        case 'html':
            $statisticsoutput .= generate_statistics($surveyid,$summary,$summary,$usegraph,$outputType,'DD',$statlang);
            break;
        case 'pdf':
            generate_statistics($surveyid,$summary,$summary,$usegraph,$outputType,'I',$statlang);
            break;
        case 'xls':
            generate_statistics($surveyid,$summary,$summary,$usegraph,$outputType,'DD',$statlang);
            break;
        default:

            break;

    }

    //print_r($summary); exit;

}	//end if -> show summary results

function showSpeaker($hinttext)
{
    global $clang, $imageurl, $maxchars;

    if(!isset($maxchars))
    {
        $maxchars = 100;
    }
    $htmlhinttext=str_replace("'",'&#039;',$hinttext);  //the string is already HTML except for single quotes so we just replace these only
    $jshinttext=javascript_escape($hinttext,true,true);

    if(strlen($hinttext) > ($maxchars))
    {
        $shortstring = FlattenText($hinttext);

        $shortstring = htmlspecialchars(mb_strcut(html_entity_decode($shortstring,ENT_QUOTES,'UTF-8'), 0, $maxchars, 'UTF-8'));

        //output with hoover effect
        $reshtml= "<span style='cursor: hand' title='".$htmlhinttext."' "
        ." onclick=\"alert('".$clang->gT("Question","js").": $jshinttext')\">"
        ." \"$shortstring...\" </span>"
        ."<img style='cursor: hand' src='$imageurl/speaker.png' align='bottom' alt='$htmlhinttext' title='$htmlhinttext' "
        ." onclick=\"alert('".$clang->gT("Question","js").": $jshinttext')\" />";
    }
    else
    {
        $reshtml= "<span title='".$htmlhinttext."'> \"$htmlhinttext\"</span>";
    }
    return $reshtml;
}

////simple function to square a value
//function square($number)
//{
//	if($number == 0)
//	{
//		$squarenumber = 0;
//	}
//	else
//	{
//		$squarenumber = $number * $number;
//	}
//
//	return $squarenumber;
//}

?>
