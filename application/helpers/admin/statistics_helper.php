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
 * $Id: statistics_function.php 10193 2011-06-05 12:20:37Z c_schmitz $
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
 *  F - Array
 *  G - Gender
 *  H - Array by column
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
 *  : - Array multiple drop down
 *  ; - Array multiple texts
 *  | - File Upload


 Debugging help:
 echo '<script language="javascript" type="text/javascript">alert("HI");</script>';
 */

//split up results to extend statistics -> NOT WORKING YET! DO NOT ENABLE THIS!
//$showcombinedresults = 0;





//don't call this script directly!
if (isset($_REQUEST['homedir'])) {die('You cannot start this script directly');}


/**
* Generates statistics
*
* @param int $surveyid The survey id
* @param mixed $allfields
* @param mixed $q2show
* @param mixed $usegraph
* @param string $outputType Optional - Can be xls, html or pdf - Defaults to pdf
* @param string $pdfOutput Sets the target for the PDF output: DD=File download , F=Save file to local disk
* @param string $statlangcode Lamguage for statistics
* @param mixed $browse  Show browse buttons
* @return buffer
*/
function generate_statistics($surveyid, $allfields, $q2show='all', $usegraph=0, $outputType='pdf', $pdfOutput='I',$statlangcode=null, $browse = true)
{
    //$allfields ="";
    global $rooturl, $rootdir, $homedir, $homeurl, $scriptname,
    $chartfontfile, $chartfontsize, $admintheme, $pdfdefaultfont, $pdffontsize;
	 $CI =& get_instance();
	 $imagedir = $CI->config->item("imagedir");
	 $tempdir = $CI->config->item("tempdir");
	 $tempurl = $CI->config->item("tempurl");
	 $clang = $CI->limesurvey_lang;
	 $dbprefix = $CI->db->dbprefix;
     $fieldmap=createFieldMap($surveyid, "full");
     
     // Used for getting coordinates for google maps
     $agmapdata = array();		
     $CI->load->model('Surveys_dynamic_model');
		
    if (is_null($statlangcode))
    {
        $statlang=$clang;
    }
    else
    {
        $statlang = new limesurvey_lang(array($statlangcode));
    }

    /*
     * this variable is used in the function shortencode() which cuts off a question/answer title
     * after $maxchars and shows the rest as tooltip (in html mode)
     */
    $maxchars = 13;
    //we collect all the html-output within this variable
    $statisticsoutput ='';
    /**
     * $outputType: html || pdf ||
     */
    /**
     * get/set Survey Details
     */

    //no survey ID? -> come and get one
    if (!isset($surveyid)) {$surveyid=returnglobal('sid');}

    //Get an array of codes of all available languages in this survey
    $surveylanguagecodes = GetAdditionalLanguagesFromSurveyID($surveyid);
    $surveylanguagecodes[] = GetBaseLanguageFromSurveyID($surveyid);

    // Set language for questions and answers to base language of this survey
    $language=$statlangcode;

    if ($usegraph==1)
    {
        //for creating graphs we need some more scripts which are included here
        require_once(APPPATH.'/third_party/pchart/pchart/pChart.class');
        require_once(APPPATH.'/third_party/pchart/pchart/pData.class');
        require_once(APPPATH.'/third_party/pchart/pchart/pCache.class');
        $MyCache = new pCache($tempdir.'/');

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
    }

    if($q2show=='all' )
    {
        $summarySql=" SELECT gid, parent_qid, qid, type "
        ." FROM {$dbprefix}questions WHERE parent_qid=0"
        ." AND sid=$surveyid ";

        $summaryRs = db_execute_assoc($summarySql);

        foreach($summaryRs as $field)
        {
            $myField = $surveyid."X".$field['gid']."X".$field['qid'];

            // Multiple choice get special treatment
            if ($field['type'] == "M") {$myField = "M$myField";}
            if ($field['type'] == "P") {$myField = "P$myField";}
            //numerical input will get special treatment (arihtmetic mean, standard derivation, ...)
            if ($field['type'] == "N") {$myField = "N$myField";}

            if ($field['type'] == "|") {$myField = "|$myField";}

            if ($field['type'] == "Q") {$myField = "Q$myField";}
            // textfields get special treatment
            if ($field['type'] == "S" || $field['type'] == "T" || $field['type'] == "U"){$myField = "T$myField";}
            //statistics for Date questions are not implemented yet.
            if ($field['type'] == "D") {$myField = "D$myField";}
            if ($field['type'] == "F" || $field['type'] == "H")
            {
                //Get answers. We always use the answer code because the label might be too long elsewise
                $query = "SELECT code, answer FROM ".$CI->db->dbprefix("answers")." WHERE qid='".$field['qid']."' AND scale_id=0 AND language='{$language}' ORDER BY sortorder, answer";
                $result = db_execute_assoc($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
                $counter2=0;

                //check all the answers
                foreach ($result->result_array() as $row)
                {
                	$row=array_values($row);
                    $myField = "$myField{$row[0]}";
                }
                //$myField = "{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]";


            }
            if($q2show=='all')
            $summary[]=$myField;

            //$allfields[]=$myField;
        }
    }
    else
    {
        // This gets all the 'to be shown questions' from the POST and puts these into an array
        if (!is_array($q2show))
        $summary=returnglobal('summary');
        else
            $summary = $q2show;

        //print_r($_POST);
        //if $summary isn't an array we create one
        if (isset($summary) && !is_array($summary))
        {
            $summary = explode("+", $summary);
        }
    }

    /* Some variable depend on output type, actually : only line feed */
    switch($outputType)
        {
            case 'xls':
                $linefeed = "\n";
                break;
            case 'pdf':
                $linefeed = "\n";
                break;
            case 'html':
                $linefeed = "<br />\n";
                break;
            default:

            break;
        }

    /**
     * pdf Config
     */
    if($outputType=='pdf')
    {
        //require_once('classes/tcpdf/config/lang/eng.php');
        global $l;
        $l['w_page'] = $statlang->gT("Page",'unescaped');
        //require_once('classes/tcpdf/mypdf.php');
		$CI->load->library('admin/pdf');
        // create new PDF document
        $pdf = $CI->pdf;
        $pdf->SetFont($pdfdefaultfont,'',$pdffontsize);

        $surveyInfo = getSurveyInfo($surveyid,$language);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('LimeSurvey');
        $pdf->SetTitle('Statistic survey '.$surveyid);
        $pdf->SetSubject($surveyInfo['surveyls_title']);
        $pdf->SetKeywords('LimeSurvey, Statistics, Survey '.$surveyid.'');
        $pdf->SetDisplayMode('fullpage', 'two');

        // set header and footer fonts
        $pdf->setHeaderFont(Array($pdfdefaultfont, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array($pdfdefaultfont, '', PDF_FONT_SIZE_DATA));

        // set default header data
        $pdf->SetHeaderData("statistics.png", 10, $statlang->gT("Quick statistics",'unescaped') , $statlang->gT("Survey")." ".$surveyid." '".FlattenText($surveyInfo['surveyls_title'],false,true,'UTF-8')."'");


        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        //set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        //set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        //set some language-dependent strings
        $pdf->setLanguageArray($l);
    }
    if($outputType=='xls')
    {
        /**
         * Initiate the Spreadsheet_Excel_Writer
         */
        $CI->load->library('admin/pear/Spreadsheet/Excel/Xlswriter');
        if($pdfOutput=='F')
        $workbook = new Spreadsheet_Excel_Writer($tempdir.'/statistic-survey'.$surveyid.'.xls');
        else
        $workbook = new Spreadsheet_Excel_Writer();

        $workbook->setVersion(8);
        // Inform the module that our data will arrive as UTF-8.
        // Set the temporary directory to avoid PHP error messages due to open_basedir restrictions and calls to tempnam("", ...)
        if (!empty($tempdir)) {
            $workbook->setTempDir($tempdir);
        }
        if ($pdfOutput!='F')
        $workbook->send('statistic-survey'.$surveyid.'.xls');

        // Creating the first worksheet
        $sheet =& $workbook->addWorksheet(utf8_decode('results-survey'.$surveyid));
        $sheet->setInputEncoding('utf-8');
        $sheet->setColumn(0,20,20);
        $separator="~|";
        /**XXX*/
    }
    /**
     * Start generating
     */

    // creates array of post variable names
    for (reset($_POST); $key=key($_POST); next($_POST)) { $postvars[]=$key;}

    $aQuestionMap=array();
    foreach ($fieldmap as $field)
    {
        if(isset($field['qid']) && $field['qid']!='')
        $aQuestionMap[]=$field['sid'].'X'.$field['gid'].'X'.$field['qid'];
    }

    /*
     * Iterate through postvars to create "nice" data for SQL later.
     *
     * Remember there might be some filters applied which have to be put into an SQL statement
     */
    if(isset($postvars))

    foreach ($postvars as $pv)
    {
        //Only do this if there is actually a value for the $pv
        if (in_array($pv, $allfields) || in_array(substr($pv,1),$aQuestionMap) || in_array($pv,$aQuestionMap) || (($pv[0]=='D' || $pv[0]=='N' || $pv[0]=='K') && in_array(substr($pv,1,strlen($pv)-2),$aQuestionMap)))
        {
            $firstletter=substr($pv,0,1);
            /*
             * these question types WON'T be handled here:
             * M = Multiple choice
             * T - Long Free Text
             * Q - Multiple Short Text
             * D - Date
             * N - Numerical Input
             * | - File Upload
             * K - Multiple Numerical Input
             */
            if ($pv != "sid" && $pv != "display" && $firstletter != "M" && $firstletter != "P" && $firstletter != "T" &&
            $firstletter != "Q" && $firstletter != "D" && $firstletter != "N" && $firstletter != "K" && $firstletter != "|" &&
            $pv != "summary" && substr($pv, 0, 2) != "id" && substr($pv, 0, 9) != "datestamp") //pull out just the fieldnames
            {
                //put together some SQL here
                $thisquestion = db_quote_id($pv)." IN (";

                foreach ($_POST[$pv] as $condition)
                {
                    $thisquestion .= "'$condition', ";
                }

                $thisquestion = substr($thisquestion, 0, -2)
                . ")";

                //we collect all the to be selected data in this array
                $selects[]=$thisquestion;
            }

            //M - Multiple choice
            //P - Multiple choice with comments
            elseif ($firstletter == "M"  || $firstletter == "P")
            {
                $mselects=array();
                //create a list out of the $pv array
                list($lsid, $lgid, $lqid) = explode("X", $pv);

                $aquery="SELECT title FROM ".$CI->db->dbprefix("questions")." WHERE parent_qid=$lqid AND language='{$language}' and scale_id=0 ORDER BY question_order";
                $aresult=db_execute_assoc($aquery) or safe_die ("Couldn't get subquestions<br />$aquery<br />".$connect->ErrorMsg());

                // go through every possible answer
                foreach ($aresult->result_array() as $arow)
                {
                	$arow=array_values($arow);
                    // only add condition if answer has been chosen
                    if (in_array($arow[0], $_POST[$pv]))
                    {
                        $mselects[]=db_quote_id(substr($pv, 1, strlen($pv)).$arow[0])." = 'Y'";
                    }
                }
                if ($mselects)
                {
                    $thismulti=implode(" OR ", $mselects);
                    $selects[]="($thismulti)";
                    $mselects = "";
                }
            }


            //N - Numerical Input
            //K - Multiple Numerical Input
            elseif ($firstletter == "N" || $firstletter == "K")
            {
                //value greater than
                if (substr($pv, strlen($pv)-1, 1) == "G" && $_POST[$pv] != "")
                {
                    $selects[]=db_quote_id(substr($pv, 1, -1))." > ".sanitize_int($_POST[$pv]);
                }

                //value less than
                if (substr($pv, strlen($pv)-1, 1) == "L" && $_POST[$pv] != "")
                {
                    $selects[]=db_quote_id(substr($pv, 1, -1))." < ".sanitize_int($_POST[$pv]);
                }
            }

            //| - File Upload Question Type
            else if ($firstletter == "|")
            {
                // no. of files greater than
                if (substr($pv, strlen($pv)-1, 1) == "G" && $_POST[$pv] != "")
                    $selects[]=db_quote_id(substr($pv, 1, -1)."_filecount")." > ".sanitize_int($_POST[$pv]);

                // no. of files less than
                if (substr($pv, strlen($pv)-1, 1) == "L" && $_POST[$pv] != "")
                    $selects[]=db_quote_id(substr($pv, 1, -1)."_filecount")." < ".sanitize_int($_POST[$pv]);
            }

            //"id" is a built in field, the unique database id key of each response row
            elseif (substr($pv, 0, 2) == "id")
            {
                if (substr($pv, strlen($pv)-1, 1) == "G" && $_POST[$pv] != "")
                {
                    $selects[]=db_quote_id(substr($pv, 0, -1))." > '".$_POST[$pv]."'";
                }
                if (substr($pv, strlen($pv)-1, 1) == "L" && $_POST[$pv] != "")
                {
                    $selects[]=db_quote_id(substr($pv, 0, -1))." < '".$_POST[$pv]."'";
                }
            }

            //T - Long Free Text
            //Q - Multiple Short Text
            elseif (($firstletter == "T" || $firstletter == "Q" ) && $_POST[$pv] != "")
            {
                $selectSubs = array();
                //We intepret and * and % as wildcard matches, and use ' OR ' and , as the seperators
                $pvParts = explode(",",str_replace('*','%', str_replace(' OR ',',',$_POST[$pv])));
                if(is_array($pvParts) AND count($pvParts)){
                    foreach($pvParts AS $pvPart){
                        $selectSubs[]=db_quote_id(substr($pv, 1, strlen($pv)))." LIKE '".trim($pvPart)."'";
                    }
                    if(count($selectSubs)){
                        $selects[] = ' ('.implode(' OR ',$selectSubs).') ';
                    }
                }
            }

            //D - Date
            elseif ($firstletter == "D" && $_POST[$pv] != "")
            {
                //Date equals
                if (substr($pv, -1, 1) == "eq")
                {
                    $selects[]=db_quote_id(substr($pv, 1, strlen($pv)-2))." = '".$_POST[$pv]."'";
                }
                else
                {
                    //date less than
                    if (substr($pv, -1, 1) == "less")
                    {
                        $selects[]= db_quote_id(substr($pv, 1, strlen($pv)-2)) . " >= '".$_POST[$pv]."'";
                    }

                    //date greater than
                    if (substr($pv, -1, 1) == "more")
                    {
                        $selects[]= db_quote_id(substr($pv, 1, strlen($pv)-2)) . " <= '".$_POST[$pv]."'";
                    }
                }
            }

            //check for datestamp of given answer
            elseif (substr($pv, 0, 9) == "datestamp")
            {
                //timestamp equals
                $formatdata=getDateFormatData($_SESSION['dateformat']);
                if (substr($pv, -1, 1) == "E" && !empty($_POST[$pv]))
                {
                    $datetimeobj = new Date_Time_Converter($_POST[$pv], $formatdata['phpdate'].' H:i');
                    $_POST[$pv]=$datetimeobj->convert("Y-m-d");

                    $selects[] = db_quote_id('datestamp')." >= '".$_POST[$pv]." 00:00:00' and ".db_quote_id('datestamp')." <= '".$_POST[$pv]." 23:59:59'";
                }
                else
                {
                    //timestamp less than
                    if (substr($pv, -1, 1) == "L" && !empty($_POST[$pv]))
                    {
                        $datetimeobj = new Date_Time_Converter($_POST[$pv], $formatdata['phpdate'].' H:i');
                        $_POST[$pv]=$datetimeobj->convert("Y-m-d H:i:s");
                        $selects[]= db_quote_id('datestamp')." < '".$_POST[$pv]."'";
                    }

                    //timestamp greater than
                    if (substr($pv, -1, 1) == "G" && !empty($_POST[$pv]))
                    {
                        $datetimeobj = new Date_Time_Converter($_POST[$pv], $formatdata['phpdate'].' H:i');
                        $_POST[$pv]=$datetimeobj->convert("Y-m-d H:i:s");
                        $selects[]= db_quote_id('datestamp')." > '".$_POST[$pv]."'";
                    }
                }
            }
        }
        else
        {
            $statisticsoutput .= "<!-- $pv DOES NOT EXIST IN ARRAY -->";
        }

    }	//end foreach -> loop through filter options to create SQL

    //count number of answers
    $query = "SELECT count(*) FROM ".$CI->db->dbprefix("survey_$surveyid");

    //if incompleted answers should be filtert submitdate has to be not null
    if (incompleteAnsFilterstate() == "inc") {$query .= " WHERE submitdate is null";}
    elseif (incompleteAnsFilterstate() == "filter") {$query .= " WHERE submitdate is not null";}
    $result = db_execute_assoc($query) or safe_die ("Couldn't get total<br />$query<br />".$connect->ErrorMsg());

    //$total = total number of answers
    $row=$result->row_array(); $total=reset($row);

    //are there any filters that have to be taken care of?
    if (isset($selects) && $selects)
    {
        //filter incomplete answers?
        if (incompleteAnsFilterstate() == "filter" || incompleteAnsFilterstate() == "inc") {$query .= " AND ";}

        else {$query .= " WHERE ";}

        //add filter criteria to SQL
        $query .= implode(" AND ", $selects);
    }

    //$_POST['sql'] is a post field that is sent from the statistics script to the export script in order
    // to export just those results filtered by this statistics script. It can also be passed to the statistics
    // script to filter from external scripts.
    elseif (!empty($_POST['sql']) && !isset($_POST['id=']))
    {
        $newsql=substr($_POST['sql'], strpos($_POST['sql'], "WHERE")+5, strlen($_POST['sql']));

        //for debugging only
        //$query = $_POST['sql'];

        //filter incomplete answers?
        if (incompleteAnsFilterstate() == "inc") {$query .= " AND ".$newsql;}
        elseif (incompleteAnsFilterstate() == "filter") {$query .= " AND ".$newsql;}

        else {$query .= " WHERE ".$newsql;}
    }

    //get me some data Scotty
    $result=db_execute_assoc($query) or safe_die("Couldn't get results<br />$query<br />".$connect->ErrorMsg());

    //put all results into $results
    $row=$result->row_array(); $results=reset($row);

    if ($total)
    {
        $percent=sprintf("%01.2f", ($results/$total)*100);

    }
    switch($outputType)
    {
        case "xls":
            $xlsRow = 0;
            $sheet->write($xlsRow,0,$statlang->gT("Number of records in this query:"));
            $sheet->write($xlsRow,1,$results);
            ++$xlsRow;
            $sheet->write($xlsRow,0,$statlang->gT("Total records in survey:"));
            $sheet->write($xlsRow,1,$total);

            if($total)
            {
                ++$xlsRow;
                $sheet->write($xlsRow,0,$statlang->gT("Percentage of total:"));
                $sheet->write($xlsRow,1,$percent."%");
            }

            break;
        case 'pdf':

            // add summary to pdf
            $array = array();
            //$array[] = array($statlang->gT("Results"),"");
            $array[] = array($statlang->gT("Number of records in this query:"), $results);
            $array[] = array($statlang->gT("Total records in survey:"), $total);

            if($total)
            $array[] = array($statlang->gT("Percentage of total:"), $percent."%");

            $pdf->addPage('P','A4');

            $pdf->Bookmark($pdf->delete_html($statlang->gT("Results")), 0, 0);
            $pdf->titleintopdf($statlang->gT("Results"),$statlang->gT("Survey")." ".$surveyid);
            $pdf->tableintopdf($array);

            $pdf->addPage('P','A4');

            break;
        case 'html':

            $statisticsoutput .= "<br />\n<table class='statisticssummary' >\n"
            ."\t<thead><tr><th colspan='2'>".$statlang->gT("Results")."</th></tr></thead>\n"
            ."\t<tr><th >".$statlang->gT("Number of records in this query:").'</th>'
            ."<td>$results</td></tr>\n"
            ."\t<tr><th>".$statlang->gT("Total records in survey:").'</th>'
            ."<td>$total</td></tr>\n";

            //only calculate percentage if $total is set
            if ($total)
            {
                $percent=sprintf("%01.2f", ($results/$total)*100);
                $statisticsoutput .= "\t<tr><th align='right'>".$statlang->gT("Percentage of total:").'</th>'
                ."<td>$percent%</td></tr>\n";
            }
            $statisticsoutput .="</table>\n";

            break;
        default:


            break;
    }

    //put everything from $selects array into a string connected by AND
    if (isset ($selects) && $selects) {$sql=implode(" AND ", $selects);}

    elseif (!empty($newsql)) {$sql = $newsql;}

    if (!isset($sql) || !$sql) {$sql="NULL";}

    //only continue if we have something to output
    if ($results > 0)
    {
        if($outputType=='html' && $browse === true)
        {
            //add a buttons to browse results
            $statisticsoutput .= "<form action='".site_url("admin/browse/$surveyid/all")."' method='post' target='_blank'>\n"
            ."\t\t<p>"
            ."\t\t\t<input type='submit' value='".$statlang->gT("Browse")."'  />\n"
            ."\t\t\t<input type='hidden' name='sid' value='$surveyid' />\n"
            ."\t\t\t<input type='hidden' name='sql' value=\"$sql\" />\n"
            ."\t\t\t<input type='hidden' name='subaction' value='all' />\n"
            ."\t\t</p>"
            ."\t\t</form>\n";
        }
    }	//end if (results > 0)

    //Show Summary results
    if (isset($summary) && $summary)
    {
        //let's run through the survey
        $runthrough=$summary;

        //START Chop up fieldname and find matching questions

        //GET LIST OF LEGIT QIDs FOR TESTING LATER
        $lq = "SELECT DISTINCT qid FROM ".$CI->db->dbprefix("questions")." WHERE sid=$surveyid and parent_qid=0";
        $lr = db_execute_assoc($lq);

        //loop through the IDs
        foreach ($lr->result_array() as $lw)
        {
            //this creates an array of question id's'
            $legitqids[] = $lw['qid'];
        }

        //loop through all selected questions
        foreach ($runthrough as $rt)
        {

            $firstletter = substr($rt, 0, 1);
            // 1. Get answers for question ##############################################################

            //M - Multiple choice, therefore multiple fields
            if ($firstletter == "M" || $firstletter == "P")
            {
                //get SGQ data
                list($qsid, $qgid, $qqid) = explode("X", substr($rt, 1, strlen($rt)), 3);

                //select details for this question
                $nquery = "SELECT title, type, question, parent_qid, other FROM ".$CI->db->dbprefix("questions")." WHERE language='{$language}' AND parent_qid=0 AND qid='$qqid'";
                $nresult = db_execute_assoc($nquery) or safe_die ("Couldn't get question<br />$nquery<br />".$connect->ErrorMsg());

                //loop through question data
                foreach ($nresult->result_array() as $nrow)
                {
                	$nrow=array_values($nrow);
                    $qtitle=$nrow[0];
                    $qtype=$nrow[1];
                    $qquestion=FlattenText($nrow[2]);
                    $qlid=$nrow[3];
                    $qother=$nrow[4];
                }

                //1. Get list of answers
                $query="SELECT title, question FROM ".$CI->db->dbprefix("questions")." WHERE parent_qid='$qqid' AND language='{$language}' and scale_id=0 ORDER BY question_order";
                $result=db_execute_assoc($query) or safe_die("Couldn't get list of subquestions for multitype<br />$query<br />".$connect->ErrorMsg());

                //loop through multiple answers
                foreach ($result->result_array() as $row)
                {
                	$row=array_values($row);
                    $mfield=substr($rt, 1, strlen($rt))."$row[0]";

                    //create an array containing answer code, answer and fieldname(??)
                    $alist[]=array("$row[0]", FlattenText($row[1]), $mfield);
                }

                //check "other" field. is it set?
                if ($qother == "Y")
                {
                    $mfield=substr($rt, 1, strlen($rt))."other";

                    //create an array containing answer code, answer and fieldname(??)
                    $alist[]=array($statlang->gT("Other"), $statlang->gT("Other"), $mfield);
                }
            }


            //S - Short Free Text
            //T - Long Free Text
            elseif ($firstletter == "T" || $firstletter == "S") //Short and long text
            {

                //search for key
                $fld = substr($rt, 1, strlen($rt));
                $fielddata=$fieldmap[$fld];

                //get SGQA IDs
                $qsid=$fielddata['sid'];
                $qgid=$fielddata['gid'];
                $qqid=$fielddata['qid'];


                list($qanswer, $qlid)=!empty($fielddata['aid']) ? explode("_", $fielddata['aid']) : array("", "");
                //get SGQ data
                //list($qsid, $qgid, $qqid) = explode("X", substr($rt, 1, strlen($rt)), 3);


                //get question data
                $nquery = "SELECT title, type, question, other, parent_qid FROM ".$CI->db->dbprefix("questions")." WHERE parent_qid=0 AND qid='$qqid' AND language='{$language}'";
                $nresult = db_execute_assoc($nquery) or safe_die("Couldn't get text question<br />$nquery<br />".$connect->ErrorMsg());

                //loop through question data
                foreach ($nresult->result_array() as $nrow)
                {
                	$nrow=array_values($nrow);
                    $qtitle=FlattenText($nrow[0]);
                    $qtype=$nrow[1];
                    $qquestion=FlattenText($nrow[2]);
                    $nlid=$nrow[4];
                }

                $mfield=substr($rt, 1, strlen($rt));
										
                //Text questions either have an answer, or they don't. There's no other way of quantising the results.
                // So, instead of building an array of predefined answers like we do with lists & other types,
                // we instead create two "types" of possible answer - either there is a response.. or there isn't.
                // This question type then can provide a % of the question answered in the summary.
                $alist[]=array("Answers", $statlang->gT("Answer"), $mfield);
                $alist[]=array("NoAnswer", $statlang->gT("No answer"), $mfield);
            }


            //Multiple short text
            elseif ($firstletter == "Q")
            {
                //get SGQ data
                list($qsid, $qgid, $qqid) = explode("X", substr($rt, 1, strlen($rt)), 3);

                //separating another ID
                $tmpqid=substr($qqid, 0, strlen($qqid)-1);

                //check if we have legid QIDs. if not create them by substringing
                while (!in_array ($tmpqid,$legitqids)) $tmpqid=substr($tmpqid, 0, strlen($tmpqid)-1);

                //length of QID
                $qidlength=strlen($tmpqid);

                //we somehow get the answer code (see SQL later) from the $qqid
                $qaid=substr($qqid, $qidlength, strlen($qqid)-$qidlength);

                //get some question data
                $nquery = "SELECT title, type, question, other FROM ".$CI->db->dbprefix("questions")." WHERE qid='".substr($qqid, 0, $qidlength)."' AND parent_qid=0 AND language='{$language}'";
                $nresult = db_execute_assoc($nquery) or safe_die("Couldn't get text question<br />$nquery<br />".$connect->ErrorMsg());

                //more substrings
                $count = substr($qqid, strlen($qqid)-1);

                //loop through question data
                foreach ($nresult->result_array() as $nrow)
                {
                	$nrow=array_values($nrow);
                    $qtitle=FlattenText($nrow[0]).'-'.$count;
                    $qtype=$nrow[1];
                    $qquestion=FlattenText($nrow[2]);
                }

                //get answers
                $qquery = "SELECT title as code, question as answer FROM ".$CI->db->dbprefix("questions")." WHERE parent_qid='".substr($qqid, 0, $qidlength)."' AND title='$qaid' AND language='{$language}' ORDER BY question_order";
                $qresult=db_execute_assoc($qquery) or safe_die ("Couldn't get answer details (Array 5p Q)<br />$qquery<br />".$connect->ErrorMsg());

                //loop through answer data
                foreach ($qresult->result_array() as $qrow)
                {
                	$qrow=array_values($qrow);
                    //store each answer here
                    $atext=FlattenText($qrow[1]);
                }

                //add this to the question title
                $qtitle .= " [$atext]";

                //even more substrings...
                $mfield=substr($rt, 1, strlen($rt));

                //Text questions either have an answer, or they don't. There's no other way of quantising the results.
                // So, instead of building an array of predefined answers like we do with lists & other types,
                // we instead create two "types" of possible answer - either there is a response.. or there isn't.
                // This question type then can provide a % of the question answered in the summary.
                $alist[]=array("Answers", $statlang->gT("Answer"), $mfield);
                $alist[]=array("NoAnswer", $statlang->gT("No answer"), $mfield);
            }


            //RANKING OPTION THEREFORE CONFUSING
            elseif ($firstletter == "R")
            {
                //getting the needed IDs somehow
                $lengthofnumeral=substr($rt, strpos($rt, "-")+1, 1);
                list($qsid, $qgid, $qqid) = explode("X", substr($rt, 1, strpos($rt, "-")-($lengthofnumeral+1)), 3);

                //get question data
                $nquery = "SELECT title, type, question FROM ".$CI->db->dbprefix("questions")." WHERE parent_qid=0 AND qid='$qqid' AND language='{$language}'";
                $nresult = db_execute_assoc($nquery) or safe_die ("Couldn't get question<br />$nquery<br />".$connect->ErrorMsg());

                //loop through question data
				foreach ($nresult->result_array() as $nrow)
                {
                	$nrow=array_values($nrow);
                    $qtitle=FlattenText($nrow[0]). " [".substr($rt, strpos($rt, "-")-($lengthofnumeral), $lengthofnumeral)."]";
                    $qtype=$nrow[1];
                    $qquestion=FlattenText($nrow[2]). "[".$statlang->gT("Ranking")." ".substr($rt, strpos($rt, "-")-($lengthofnumeral), $lengthofnumeral)."]";
                }

                //get answers
                $query="SELECT code, answer FROM ".$CI->db->dbprefix("answers")." WHERE qid='$qqid' AND scale_id=0 AND language='{$language}' ORDER BY sortorder, answer";
                $result=db_execute_assoc($query) or safe_die("Couldn't get list of answers for multitype<br />$query<br />".$connect->ErrorMsg());

                //loop through answers
                foreach ($result->result_array() as $row)
                {
                	$row=array_values($row);
                    //create an array containing answer code, answer and fieldname(??)
                    $mfield=substr($rt, 1, strpos($rt, "-")-1);
                    $alist[]=array("$row[0]", FlattenText($row[1]), $mfield);
                }
            }

            else if ($firstletter == "|") // File UPload
            {

                //get SGQ data
                list($qsid, $qgid, $qqid) = explode("X", substr($rt, 1, strlen($rt)), 3);

                //select details for this question
                $nquery = "SELECT title, type, question, parent_qid, other FROM ".$CI->db->dbprefix("questions")." WHERE language='{$language}' AND parent_qid=0 AND qid='$qqid'";
                $nresult = db_execute_assoc($nquery) or safe_die ("Couldn't get question<br />$nquery<br />".$connect->ErrorMsg());

                //loop through question data
                foreach ($nresult->result_array() as $nrow)
                {
                	$nrow=array_values($nrow);
                    $qtitle=$nrow[0];
                    $qtype=$nrow[1];
                    $qquestion=FlattenText($nrow[2]);
                    $qlid=$nrow[3];
                    $qother=$nrow[4];
                }

                 /*
                    4)      Average size of file per respondent
                    5)      Average no. of files
                    5)      Summary/count of file types (ie: 37 jpg, 65 gif, 12 png)
                    6)      Total size of all files (useful if you re about to download them all)
                    7)      You could also add things like  smallest file size, largest file size, median file size
                    8)      no. of files corresponding to each extension
                    9)      max file size
                    10)     min file size
                 */

                // 1) Total number of files uploaded
                // 2)      Number of respondents who uploaded at least one file (with the inverse being the number of respondents who didn t upload any)
                $fieldname=substr($rt, 1, strlen($rt));
                $query = "SELECT SUM(".db_quote_id($fieldname.'_filecount').") as sum, AVG(".db_quote_id($fieldname.'_filecount').") as avg FROM ".$CI->db->dbprefix("survey_$surveyid");
                $result=db_execute_assoc($query) or safe_die("Couldn't fetch the records<br />$query<br />".$connect->ErrorMsg());

                $showem = array();

                foreach ($result->result_array() as $row)
                {
                    $showem[]=array($statlang->gT("Total number of files"), $row['sum']);
                    $showem[]=array($statlang->gT("Average no. of files per respondent"), $row['avg']);
                }


                $query = "SELECT ". $fieldname ." as json FROM ".$CI->db->dbprefix("survey_$surveyid");
                $result=db_execute_assoc($query) or safe_die("Couldn't fetch the records<br />$query<br />".$connect->ErrorMsg());

                $responsecount = 0;
                $filecount = 0;
                $size = 0;

                foreach ($result->result_array() as $row)
                {

                    $json = $row['json'];
                    $phparray = json_decode($json);

                    foreach ($phparray as $metadata)
                    {
                        $size += (int) $metadata->size;
                        $filecount++;
                    }
                    $responsecount++;
                }
                $showem[] = array($statlang->gT("Total size of files"), $size." KB");
                $showem[] = array($statlang->gT("Average file size"), $size/$filecount . " KB");
                $showem[] = array($statlang->gT("Average size per respondent"), $size/$responsecount . " KB");

/*              $query="SELECT title, question FROM ".$CI->db->dbprefix("questions")." WHERE parent_qid='$qqid' AND language='{$language}' ORDER BY question_order";
                $result=db_execute_num($query) or safe_die("Couldn't get list of subquestions for multitype<br />$query<br />".$connect->ErrorMsg());

                //loop through multiple answers
                while ($row=$result->FetchRow())
                {
                    $mfield=substr($rt, 1, strlen($rt))."$row[0]";

                    //create an array containing answer code, answer and fieldname(??)
                    $alist[]=array("$row[0]", FlattenText($row[1]), $mfield);
                }

*/
                //outputting
                switch($outputType)
                {
                    case 'xls':

                        $headXLS = array();
                        $tableXLS = array();
                        $footXLS = array();

                        $xlsTitle = sprintf($statlang->gT("Field summary for %s"),html_entity_decode($qtitle,ENT_QUOTES,'UTF-8'));
                        $xlsDesc = html_entity_decode($qquestion,ENT_QUOTES,'UTF-8');
                        ++$xlsRow;
                        ++$xlsRow;

                        ++$xlsRow;
                        $sheet->write($xlsRow, 0,$xlsTitle);
                        ++$xlsRow;
                        $sheet->write($xlsRow, 0,$xlsDesc);

                        $headXLS[] = array($statlang->gT("Calculation"),$statlang->gT("Result"));
                        ++$xlsRow;
                        $sheet->write($xlsRow, 0,$statlang->gT("Calculation"));
                        $sheet->write($xlsRow, 1,$statlang->gT("Result"));

                        break;
                    case 'pdf':

                        $headPDF = array();
                        $tablePDF = array();
                        $footPDF = array();

                        $pdfTitle = sprintf($statlang->gT("Field summary for %s"),html_entity_decode($qtitle,ENT_QUOTES,'UTF-8'));
                        $titleDesc = html_entity_decode($qquestion,ENT_QUOTES,'UTF-8');

                        $headPDF[] = array($statlang->gT("Calculation"),$statlang->gT("Result"));

                        break;

                    case 'html':

                        $statisticsoutput .= "\n<table class='statisticstable' >\n"
                        ."\t<thead><tr><th colspan='2' align='center'><strong>".sprintf($statlang->gT("Field summary for %s"),$qtitle).":</strong>"
                        ."</th></tr>\n"
                        ."\t<tr><th colspan='2' align='center'><strong>$qquestion</strong></th></tr>\n"
                        ."\t<tr>\n\t\t<th width='50%' align='center' ><strong>"
                        .$statlang->gT("Calculation")."</strong></th>\n"
                        ."\t\t<th width='50%' align='center' ><strong>"
                        .$statlang->gT("Result")."</strong></th>\n"
                        ."\t</tr></thead>\n";

                        foreach ($showem as $res)
                            $statisticsoutput .= "<tr><td>".$res[0]."</td><td>".$res[1]."</td></tr>";
                        break;

                    default:
                        break;
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
                //check last character, greater/less/equals don't need special treatment
                if (substr($rt, -1) == "G" ||  substr($rt, -1) == "L" || substr($rt, -1) == "=")
                {
                    //DO NOTHING
                }
                else
                {
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
                        $nquery = "SELECT title, type, question, qid, parent_qid
								   FROM ".$CI->db->dbprefix("questions")."
								   WHERE parent_qid=0 AND qid='".substr($qqid, 0, $qidlength)."'
								   AND language='{$language}'";
                        $nresult = db_execute_assoc($nquery) or safe_die("Couldn't get text question<br />$nquery<br />".$connect->ErrorMsg());
                    }

                    //probably question type "N" = numerical input
                    else
                    {
                        //we can use the qqid without any editing
                        $nquery = "SELECT title, type, question, qid, parent_qid FROM ".$CI->db->dbprefix("questions")." WHERE parent_qid=0 AND qid='$qqid' AND language='{$language}'";
                        $nresult = db_execute_assoc($nquery) or safe_die ("Couldn't get question<br />$nquery<br />".$connect->ErrorMsg());
                    }

                    //loop through results
                    foreach ($nresult->result_array() as $nrow)
                    {
                    	$nrow=array_values($nrow);
                        $qtitle=FlattenText($nrow[0]); //clean up title
                        $qtype=$nrow[1];
                        $qquestion=FlattenText($nrow[2]);
                        $qiqid=$nrow[3];
                        $qlid=$nrow[4];
                    }

                    //Get answer texts for multiple numerical
                    if(substr($rt, 0, 1) == "K")
                    {
                        //get answer data
                        $atext=$connect->GetOne("SELECT question FROM ".$CI->db->dbprefix("questions")." WHERE parent_qid='{$qiqid}' AND scale_id=0 AND title='{$qaid}' AND language='{$language}'");
                        //put single items in brackets at output
                        $qtitle .= " [$atext]";
                    }

                    //outputting
                    switch($outputType)
                    {
                        case 'xls':

                            $headXLS = array();
                            $tableXLS = array();
                            $footXLS = array();

                            $xlsTitle = sprintf($statlang->gT("Field summary for %s"),html_entity_decode($qtitle,ENT_QUOTES,'UTF-8'));
                            $xlsDesc = html_entity_decode($qquestion,ENT_QUOTES,'UTF-8');
                            ++$xlsRow;
                            ++$xlsRow;

                            ++$xlsRow;
                            $sheet->setCellValueByColumnAndRow(0,$xlsRow,$xlsTitle);
                            ++$xlsRow;
                            $sheet->setCellValueByColumnAndRow(0,$xlsRow,$xlsDesc);

                            $headXLS[] = array($statlang->gT("Calculation"),$statlang->gT("Result"));
                            ++$xlsRow;
                            $sheet->setCellValueByColumnAndRow(0,$xlsRow,$statlang->gT("Calculation"));
                            $sheet->setCellValueByColumnAndRow(1,$xlsRow,$statlang->gT("Result"));

                            break;
                        case 'pdf':

                            $headPDF = array();
                            $tablePDF = array();
                            $footPDF = array();

                            $pdfTitle = sprintf($statlang->gT("Field summary for %s"),html_entity_decode($qtitle,ENT_QUOTES,'UTF-8'));
                            $titleDesc = html_entity_decode($qquestion,ENT_QUOTES,'UTF-8');

                            $headPDF[] = array($statlang->gT("Calculation"),$statlang->gT("Result"));

                            break;
                        case 'html':

                            $statisticsoutput .= "\n<table class='statisticstable' >\n"
                            ."\t<thead><tr><th colspan='2' align='center'><strong>".sprintf($statlang->gT("Field summary for %s"),$qtitle).":</strong>"
                            ."</th></tr>\n"
                            ."\t<tr><th colspan='2' align='center'><strong>$qquestion</strong></th></tr>\n"
                            ."\t<tr>\n\t\t<th width='50%' align='center' ><strong>"
                            .$statlang->gT("Calculation")."</strong></th>\n"
                            ."\t\t<th width='50%' align='center' ><strong>"
                            .$statlang->gT("Result")."</strong></th>\n"
                            ."\t</tr></thead>\n";

                            break;
                        default:


                            break;
                    }

                    //this field is queried using mathematical functions
                    $fieldname=substr($rt, 1, strlen($rt));

                    //special treatment for MS SQL databases
                    $sDatabaseType = $CI->db->platform();
					if ($sDatabaseType == 'odbc_mssql' || $sDatabaseType == 'odbtp' || $sDatabaseType == 'mssql_n' || $sDatabaseType == 'mssqlnative')
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
					if ($sDatabaseType == 'odbc_mssql' || $sDatabaseType == 'odbtp' || $sDatabaseType == 'mssql_n' || $sDatabaseType == 'mssqlnative')
                    {
                        //no NULL/empty values please
                        $query .= " FROM ".$CI->db->dbprefix("survey_$surveyid")." WHERE ".db_quote_id($fieldname)." IS NOT NULL";
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
                        $query .= " FROM ".$CI->db->dbprefix("survey_$surveyid")." WHERE ".db_quote_id($fieldname)." IS NOT NULL";
                        if(!$excludezeros)
                        {
                            //NO ZERO VALUES
                            $query .= " AND (".db_quote_id($fieldname)." != 0)";
                        }
                    }

                    //filter incomplete answers if set
                    if (incompleteAnsFilterstate() == "inc") {$query .= " AND submitdate is null";}
                    elseif (incompleteAnsFilterstate() == "filter") {$query .= " AND submitdate is not null";}

                    //$sql was set somewhere before
                    if ($sql != "NULL") {$query .= " AND $sql";}

                    //execute query
                    $result=db_execute_assoc($query) or safe_die("Couldn't do maths testing<br />$query<br />".$connect->ErrorMsg());

                    //get calculated data
                    foreach ($nresult->result_array() as $row)
                    {
                        //put translation of mean and calculated data into $showem array
                        $showem[]=array($statlang->gT("Sum"), $row['sum']);
                        $showem[]=array($statlang->gT("Standard deviation"), round($row['stdev'],2));
                        $showem[]=array($statlang->gT("Average"), round($row['average'],2));
                        $showem[]=array($statlang->gT("Minimum"), $row['minimum']);

                        //Display the maximum and minimum figures after the quartiles for neatness
                        $maximum=$row['maximum'];
                        $minimum=$row['minimum'];
                    }



                    //CALCULATE QUARTILES

                    //get data
                    $query ="SELECT ".db_quote_id($fieldname)." FROM ".$CI->db->dbprefix("survey_$surveyid")." WHERE ".db_quote_id($fieldname)." IS NOT null";
                    //NO ZEROES
                    if(!$excludezeros)
                    {
                        $query .= " AND ".db_quote_id($fieldname)." != 0";
                    }

                    //filtering enabled?
                    if (incompleteAnsFilterstate() == "inc") {$query .= " AND submitdate is null";}
                    elseif (incompleteAnsFilterstate() == "filter") {$query .= " AND submitdate is not null";}

                    //if $sql values have been passed to the statistics script from another script, incorporate them
                    if ($sql != "NULL") {$query .= " AND $sql";}

                    //execute query
                    $result=$connect->Execute($query) or safe_die("Disaster during median calculation<br />$query<br />".$connect->ErrorMsg());

                    $querystarter="SELECT ".db_quote_id($fieldname)." FROM ".$CI->db->dbprefix("survey_$surveyid")." WHERE ".db_quote_id($fieldname)." IS NOT null";
                    //No Zeroes
                    if(!$excludezeros)
                    {
                        $querystart .= " AND ".db_quote_id($fieldname)." != 0";
                    }
                    //filtering enabled?
                    if (incompleteAnsFilterstate() == "inc") {$querystarter .= " AND submitdate is null";}
                    elseif (incompleteAnsFilterstate() == "filter") {$querystarter .= " AND submitdate is not null";}

                    //if $sql values have been passed to the statistics script from another script, incorporate them
                    if ($sql != "NULL") {$querystarter .= " AND $sql";}

                    //we just count the number of records returned
                    $medcount=$result->RecordCount();

                    //put the total number of records at the beginning of this array
                    array_unshift($showem, array($statlang->gT("Count"), $medcount));


                    //no more comment from Mazi regarding the calculation

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
                        if ($q1c<0) {$q1c=0;}

                        if ($q1 != $q1b)
                        {
                            //ODD NUMBER
                            $query = $querystarter . " ORDER BY ".db_quote_id($fieldname)."*1 ";
                            $result=db_select_limit_assoc($query, 2, $q1c) or safe_die("1st Quartile query failed<br />".$connect->ErrorMsg());

                            foreach ($result->result_array() as $row)
                            {
                                if ($total == 0)    {$total=$total-$row[$fieldname];}

                                else                {$total=$total+$row[$fieldname];}

                                $lastnumber=$row[$fieldname];
                            }

                            $q1total=$lastnumber-((1-$q1diff)*$total);

                            if ($q1total < $minimum) {$q1total=$minimum;}

                            $showem[]=array($statlang->gT("1st quartile (Q1)"), $q1total);
                        }
                        else
                        {
                            //EVEN NUMBER
                            $query = $querystarter . " ORDER BY ".db_quote_id($fieldname)."*1 ";
                            $result=db_select_limit_assoc($query,1, $q1c) or safe_die ("1st Quartile query failed<br />".$connect->ErrorMsg());

                            foreach ($result->result_array() as $row)
                            {
                                $showem[]=array($statlang->gT("1st quartile (Q1)"), $row[$fieldname]);
                            }
                        }

                        $total=0;


                        //MEDIAN (Q2)
                        $median=(1/2)*($medcount+1);
                        $medianb=(int)((1/2)*($medcount+1));
                        $medianc=$medianb-1;
                        $mediandiff=$median-$medianb;

                        if ($median != $medianb)
                        {
                            //remainder
                            $query = $querystarter . " ORDER BY ".db_quote_id($fieldname)."*1 ";
                            $result=db_select_limit_assoc($query,2, $medianc) or safe_die("What a complete mess with the remainder<br />$query<br />".$connect->ErrorMsg());

                            foreach ($result->result_array() as $row) {$total=$total+$row[$fieldname];}

                            $showem[]=array($statlang->gT("2nd quartile (Median)"), $total/2);
                        }

                        else
                        {
                            //EVEN NUMBER
                            $query = $querystarter . " ORDER BY ".db_quote_id($fieldname)."*1 ";
                            $result=db_select_limit_assoc($query,1, $medianc-1) or safe_die("What a complete mess<br />$query<br />".$connect->ErrorMsg());

                            foreach ($result->result_array() as $row)
                            {
                                $showem[]=array($statlang->gT("Median value"), $row[$fieldname]);
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

                            foreach ($result->result_array() as $row)
                            {
                                if ($total == 0)    {$total=$total-$row[$fieldname];}

                                else                {$total=$total+$row[$fieldname];}

                                $lastnumber=$row[$fieldname];
                            }
                            $q3total=$lastnumber-((1-$q3diff)*$total);

                            if ($q3total < $maximum) {$q1total=$maximum;}

                            $showem[]=array($statlang->gT("3rd quartile (Q3)"), $q3total);
                        }

                        else
                        {
                            $query = $querystarter . " ORDER BY ".db_quote_id($fieldname)."*1";
                            $result = db_select_limit_assoc($query,1, $q3c) or safe_die("3rd Quartile even query failed<br />".$connect->ErrorMsg());

                            foreach ($result->result_array() as $row)
                            {
                                $showem[]=array($statlang->gT("3rd quartile (Q3)"), $row[$fieldname]);
                            }
                        }

                        $total=0;

                        $showem[]=array($statlang->gT("Maximum"), $maximum);

                        //output results
                        foreach ($showem as $shw)
                        {
                            switch($outputType)
                            {
                                case 'xls':

                                    ++$xlsRow;
                                    $sheet->write($xlsRow, 0,html_entity_decode($shw[0],ENT_QUOTES,'UTF-8'));
                                    $sheet->write($xlsRow, 1,html_entity_decode($shw[1],ENT_QUOTES,'UTF-8'));


                                    $tableXLS[] = array($shw[0],$shw[1]);

                                    break;
                                case 'pdf':

                                    $tablePDF[] = array(html_entity_decode($shw[0],ENT_QUOTES,'UTF-8'),html_entity_decode($shw[1],ENT_QUOTES,'UTF-8'));

                                    break;
                                case 'html':

                                    $statisticsoutput .= "\t<tr>\n"
                                    ."\t\t<td align='center' >$shw[0]</td>\n"
                                    ."\t\t<td align='center' >$shw[1]</td>\n"
                                    ."\t</tr>\n";

                                    break;
                                default:


                                    break;
                            }
                        }
                        switch($outputType)
                        {
                            case 'xls':

                                ++$xlsRow;
                                $sheet->write($xlsRow, 0,$statlang->gT("Null values are ignored in calculations"));
                                ++$xlsRow;
                                $sheet->write($xlsRow, 0,sprintf($statlang->gT("Q1 and Q3 calculated using %s"), $statlang->gT("minitab method")));

                                $footXLS[] = array($statlang->gT("Null values are ignored in calculations"));
                                $footXLS[] = array(sprintf($statlang->gT("Q1 and Q3 calculated using %s"), $statlang->gT("minitab method")));

                                break;
                            case 'pdf':

                                $footPDF[] = array($statlang->gT("Null values are ignored in calculations"));
                                $footPDF[] = array(sprintf($statlang->gT("Q1 and Q3 calculated using %s"), "<a href='http://mathforum.org/library/drmath/view/60969.html' target='_blank'>".$statlang->gT("minitab method")."</a>"));
                                $pdf->addPage('P','A4');
                                $pdf->Bookmark($pdf->delete_html($qquestion), 1, 0);
                                $pdf->titleintopdf($pdfTitle,$titleDesc);

                                $pdf->headTable($headPDF, $tablePDF);

                                $pdf->tablehead($footPDF);

                                break;
                            case 'html':

                                //footer of question type "N"
                                $statisticsoutput .= "\t<tr>\n"
                                ."\t\t<td colspan='4' align='center' bgcolor='#EEEEEE'>\n"
                                ."\t\t\t<font size='1'>".$statlang->gT("Null values are ignored in calculations")."<br />\n"
                                ."\t\t\t".sprintf($statlang->gT("Q1 and Q3 calculated using %s"), "<a href='http://mathforum.org/library/drmath/view/60969.html' target='_blank'>".$statlang->gT("minitab method")."</a>")
                                ."</font>\n"
                                ."\t\t</td>\n"
                                ."\t</tr>\n</table>\n";

                                break;
                            default:


                                break;
                        }

                        //clean up
                        unset($showem);

                    }	//end if (enough results?)

                    //not enough (<1) results for calculation
                    else
                    {
                        switch($outputType)
                        {
                            case 'xls':

                                $tableXLS = array();
                                $tableXLS[] = array($statlang->gT("Not enough values for calculation"));

                                ++$xlsRow;
                                $sheet->write($xlsRow, 0, $statlang->gT("Not enough values for calculation"));



                                break;
                            case 'pdf':

                                $tablePDF = array();
                                $tablePDF[] = array($statlang->gT("Not enough values for calculation"));
                                $pdf->addPage('P','A4');
                                $pdf->Bookmark($pdf->delete_html($qquestion), 1, 0);
                                $pdf->titleintopdf($pdfTitle,$titleDesc);

                                $pdf->equalTable($tablePDF);

                                break;
                            case 'html':

                                //output
                                $statisticsoutput .= "\t<tr>\n"
                                ."\t\t<td align='center'  colspan='4'>".$statlang->gT("Not enough values for calculation")."</td>\n"
                                ."\t</tr>\n</table><br />\n";

                                break;
                            default:


                                break;
                        }

                        unset($showem);

                    }

                }	//end else -> check last character, greater/less/equals don't need special treatment

            }	//end else-if -> multiple numerical types

            //is there some "id", "datestamp" or "D" within the type?
            elseif (substr($rt, 0, 2) == "id" || substr($rt, 0, 9) == "datestamp" || ($firstletter == "D"))
            {
                /*
                 * DON'T show anything for date questions
                 * because there aren't any statistics implemented yet!
                 *
                 * See bug report #2539 and
                 * feature request #2620
                 */
            }


            // NICE SIMPLE SINGLE OPTION ANSWERS
            else
            {
                //search for key
                $fielddata=$fieldmap[$rt];
                //print_r($fielddata);
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
                $nquery = "SELECT title, type, question, qid, parent_qid, other FROM ".$CI->db->dbprefix("questions")." WHERE qid='{$rqid}' AND parent_qid=0 and language='{$language}'";
                $nresult = db_execute_assoc($nquery) or safe_die ("Couldn't get question<br />$nquery<br />".$connect->ErrorMsg());

                //loop though question data
                foreach ($nresult->result_array() as $nrow)
                {
                	$nrow=array_values($nrow);
                    $qtitle=FlattenText($nrow[0]);
                    $qtype=$nrow[1];
                    $qquestion=FlattenText($nrow[2]);
                    $qiqid=$nrow[3];
                    $qparentqid=$nrow[4];
                    $qother=$nrow[5];
                }

                //check question types
                switch($qtype)
                {
                    //Array of 5 point choices (several items to rank!)
                    case "A":

                        //get data
                        $qquery = "SELECT title, question FROM ".$CI->db->dbprefix("questions")." WHERE parent_qid='$qiqid' AND title='$qanswer' AND language='{$language}' ORDER BY question_order";
                        $qresult=db_execute_assoc($qquery) or safe_die ("Couldn't get answer details (Array 5p Q)<br />$qquery<br />".$connect->ErrorMsg());

                        //loop through results
                        foreach ($qresult->result_array() as $qrow)
                        {
                        	$qrow=array_values($qrow);
                            //5-point array
                            for ($i=1; $i<=5; $i++)
                            {
                                //add data
                                $alist[]=array("$i", "$i");
                            }
                            //add counter
                            $atext=FlattenText($qrow[1]);
                        }

                        //list IDs and answer codes in brackets
                        $qquestion .=  $linefeed."[".$atext."]";
                        $qtitle .= "($qanswer)";
                        break;



                        //Array of 10 point choices
                        //same as above just with 10 items
                    case "B":
                        $qquery = "SELECT title, question FROM ".$CI->db->dbprefix("questions")." WHERE parent_qid='$qiqid' AND title='$qanswer' AND language='{$language}' ORDER BY question_order";
                        $qresult=db_execute_assoc($qquery) or safe_die ("Couldn't get answer details (Array 10p Q)<br />$qquery<br />".$connect->ErrorMsg());
                        foreach ($qresult->result_array() as $qrow)
                        {
                        	$qrow=array_values($qrow);
                            for ($i=1; $i<=10; $i++)
                            {
                                $alist[]=array("$i", "$i");
                            }
                            $atext=FlattenText($qrow[1]);
                        }

                        $qquestion .=  $linefeed."[".$atext."]";
                        $qtitle .= "($qanswer)";
                        break;



                        //Array of Yes/No/$statlang->gT("Uncertain")
                    case "C":
                        $qquery = "SELECT title, question FROM ".$CI->db->dbprefix("questions")." WHERE parent_qid='$qiqid' AND title='$qanswer' AND language='{$language}' ORDER BY question_order";
                        $qresult=db_execute_assoc($qquery) or safe_die ("Couldn't get answer details<br />$qquery<br />".$connect->ErrorMsg());

                        //loop thorugh results
                        foreach ($qresult->result_array() as $qrow)
                        {
                        	$qrow=array_values($qrow);
                            //add results
                            $alist[]=array("Y", $statlang->gT("Yes"));
                            $alist[]=array("N", $statlang->gT("No"));
                            $alist[]=array("U", $statlang->gT("Uncertain"));
                            $atext=FlattenText($qrow[1]);
                        }
                        //output
                        $qquestion .=  $linefeed."[".$atext."]";
                        $qtitle .= "($qanswer)";
                        break;



                        //Array of Yes/No/$statlang->gT("Uncertain")
                        //same as above
                    case "E":
                        $qquery = "SELECT title, question FROM ".$CI->db->dbprefix("questions")." WHERE parent_qid='$qiqid' AND title='$qanswer' AND language='{$language}' ORDER BY question_order";
                        $qresult=db_execute_assoc($qquery) or safe_die ("Couldn't get answer details<br />$qquery<br />".$connect->ErrorMsg());
                        foreach ($qresult->result_array() as $qrow)
                        {
                        	$qrow=array_values($qrow);
                            $alist[]=array("I", $statlang->gT("Increase"));
                            $alist[]=array("S", $statlang->gT("Same"));
                            $alist[]=array("D", $statlang->gT("Decrease"));
                            $atext=FlattenText($qrow[1]);
                        }
                        $qquestion .= $linefeed."[".$atext."]";
                        $qtitle .= "($qanswer)";
                        break;


                    case ";": //Array (Multi Flexi) (Text)
                        list($qacode, $licode)=explode("_", $qanswer);

                        $qquery = "SELECT title, question FROM ".$CI->db->dbprefix("questions")." WHERE parent_qid='$qiqid' AND title='$qacode' AND language='{$language}' ORDER BY question_order";
                        $qresult=db_execute_assoc($qquery) or die ("Couldn't get answer details<br />$qquery<br />".$connect->ErrorMsg());

                        foreach ($qresult->result_array() as $qrow)
                        {
                        	$qrow=array_values($qrow);
                            $fquery = "SELECT * FROM ".$CI->db->dbprefix("answers")." WHERE qid='{$qiqid}' AND scale_id=0 AND code = '{$licode}' AND language='{$language}'ORDER BY sortorder, code";
                            $fresult = db_execute_assoc($fquery);
                            foreach ($result->result_array() as $frow)
                            {
                                $alist[]=array($frow['code'], $frow['answer']);
                                $ltext=$frow['answer'];
                            }
                            $atext=FlattenText($qrow[1]);
                        }

                        $qquestion .=  $linefeed."[".$atext."] [".$ltext."]";
                        $qtitle .= "($qanswer)";
                        break;


                    case ":": //Array (Multiple Flexi) (Numbers)
                        $qidattributes=getQuestionAttributeValues($qiqid);
                        if (trim($qidattributes['multiflexible_max'])!='') {
                            $maxvalue=$qidattributes['multiflexible_max'];
                        }
                        else {
                            $maxvalue=10;
                        }

                        if (trim($qidattributes['multiflexible_min'])!='')
                        {
                            $minvalue=$qidattributes['multiflexible_min'];
                        }
                        else {
                            $minvalue=1;
                        }

                        if (trim($qidattributes['multiflexible_step'])!='')
                        {
                            $stepvalue=$qidattributes['multiflexible_step'];
                        }
                        else {
                            $stepvalue=1;
                        }

                        if ($qidattributes['multiflexible_checkbox']!=0) {
                            $minvalue=0;
                            $maxvalue=1;
                            $stepvalue=1;
                        }

                        for($i=$minvalue; $i<=$maxvalue; $i+=$stepvalue)
                        {
                            $alist[]=array($i, $i);
                        }

                        $qquestion .= $linefeed."[".$fielddata['subquestion1']."] [".$fielddata['subquestion2']."]";
                        list($myans, $mylabel)=explode("_", $qanswer);
                        $qtitle .= "[$myans][$mylabel]";
                        break;

                    case "F": //Array of Flexible
                    case "H": //Array of Flexible by Column
                        $qquery = "SELECT title, question FROM ".$CI->db->dbprefix("questions")." WHERE parent_qid='$qiqid' AND title='$qanswer' AND language='{$language}' ORDER BY question_order";
                        $qresult=db_execute_assoc($qquery) or safe_die ("Couldn't get answer details<br />$qquery<br />".$connect->ErrorMsg());

                        //loop through answers
                        foreach ($result->result_array() as $qrow)
                        {
                        	$qrow=array_values($qrow);

                            //this question type uses its own labels
                            $fquery = "SELECT * FROM ".$CI->db->dbprefix("answers")." WHERE qid='{$qiqid}' AND scale_id=0 AND language='{$language}'ORDER BY sortorder, code";
                            $fresult = db_execute_assoc($fquery);

                            //add code and title to results for outputting them later
                            foreach ($fresult->result_array() as $frow)
                            {
                                $alist[]=array($frow['code'], FlattenText($frow['answer']));
                            }

                            //counter
                            $atext=FlattenText($qrow[1]);
                        }

                        //output
                        $qquestion .= $linefeed."[".$atext."]";
                        $qtitle .= "($qanswer)";
                        break;



                    case "G": //Gender
                        $alist[]=array("F", $statlang->gT("Female"));
                        $alist[]=array("M", $statlang->gT("Male"));
                        break;



                    case "Y": //Yes\No
                        $alist[]=array("Y", $statlang->gT("Yes"));
                        $alist[]=array("N", $statlang->gT("No"));
                        break;



                    case "I": //Language
                        // Using previously defined $surveylanguagecodes array of language codes
                        foreach ($surveylanguagecodes as $availlang)
                        {
                            $alist[]=array($availlang, getLanguageNameFromCode($availlang,false));
                        }
                        break;


                    case "5": //5 Point (just 1 item to rank!)
                        for ($i=1; $i<=5; $i++)
                        {
                            $alist[]=array("$i", "$i");
                        }
                        break;


                    case "1":	//array (dual scale)

                        $sSubquestionQuery = "SELECT  question FROM ".$CI->db->dbprefix("questions")." WHERE parent_qid='$qiqid' AND title='$qanswer' AND language='{$language}' ORDER BY question_order";
                        $sSubquestion=FlattenText($connect->GetOne($sSubquestionQuery));

                        //get question attributes
                        $qidattributes=getQuestionAttributeValues($qqid);

                        //check last character -> label 1
                        if (substr($rt,-1,1) == 0)
                        {
                            //get label 1
                            $fquery = "SELECT * FROM ".$CI->db->dbprefix("answers")." WHERE qid='{$qqid}' AND scale_id=0 AND language='{$language}' ORDER BY sortorder, code";

                            //header available?
                            if (trim($qidattributes['dualscale_headerA'])!='') {
                                //output
                                $labelheader= "[".$qidattributes['dualscale_headerA']."]";
                            }

                            //no header
                            else
                            {
                                $labelheader ='';
                            }

                            //output
                            $labelno = sprintf($clang->gT('Label %s'),'1');
                        }

                        //label 2
                        else
                        {
                            //get label 2
                            $fquery = "SELECT * FROM ".$CI->db->dbprefix("answers")." WHERE qid='{$qqid}' AND scale_id=1 AND language='{$language}' ORDER BY sortorder, code";

                            //header available?
                            if (trim($qidattributes['dualscale_headerB'])!='') {
                                //output
                                $labelheader= "[".$qidattributes['dualscale_headerB']."]";
                            }

                            //no header
                            else
                            {
                                $labelheader ='';
                            }

                            //output
                            $labelno = sprintf($clang->gT('Label %s'),'2');
                        }

                        //get data
                        $fresult = db_execute_assoc($fquery);

                        //put label code and label title into array
                        foreach ($fresult->result_array() as $frow)
                        {
                            $alist[]=array($frow['code'], FlattenText($frow['answer']));
                        }

                        //adapt title and question
                        $qtitle = $qtitle." [".$sSubquestion."][".$labelno."]";
                        $qquestion  = $qastring .$labelheader;
                        break;




                    default:	//default handling

                        //get answer code and title
                        $qquery = "SELECT code, answer FROM ".$CI->db->dbprefix("answers")." WHERE qid='$qqid' AND scale_id=0 AND language='{$language}' ORDER BY sortorder, answer";
                        $qresult = db_execute_assoc($qquery) or safe_die ("Couldn't get answers list<br />$qquery<br />".$connect->ErrorMsg());

                        //put answer code and title into array
                        foreach ($qresult->result_array() as $qrow)
                        {
                        	$qrow=array_values($qrow);
                            $alist[]=array("$qrow[0]", FlattenText($qrow[1]));
                        }

                        //handling for "other" field for list radio or list drowpdown
                        if ((($qtype == "L" || $qtype == "!") && $qother == "Y"))
                        {
                            //add "other"
                            $alist[]=array($statlang->gT("Other"),$statlang->gT("Other"),$fielddata['fieldname'].'other');
                        }
                    	if ( $qtype == "O")
                     	{
                    		//add "comment"
                    		$alist[]=array($statlang->gT("Comments"),$statlang->gT("Comments"),$fielddata['fieldname'].'comment');
                    	}

                }	//end switch question type

                //moved because it's better to have "no answer" at the end of the list instead of the beginning
                //put data into array
                $alist[]=array("", $statlang->gT("No answer"));

            }	//end else -> single option answers

            //foreach ($alist as $al) {$statisticsoutput .= "$al[0] - $al[1]<br />";} //debugging line
            //foreach ($fvalues as $fv) {$statisticsoutput .= "$fv | ";} //debugging line





            //2. Collect and Display results #######################################################################
            if (isset($alist) && $alist) //Make sure there really is an answerlist, and if so:
            {


                // this will count the answers considered completed
                $TotalCompleted = 0;
                switch($outputType)
                {
                    case 'xls':

                        $xlsTitle = sprintf($statlang->gT("Field summary for %s"),html_entity_decode($qtitle,ENT_QUOTES,'UTF-8'));
                        $xlsDesc = html_entity_decode($qquestion,ENT_QUOTES,'UTF-8');

                        ++$xlsRow;
                        ++$xlsRow;

                        ++$xlsRow;
                        $sheet->write($xlsRow, 0,$xlsTitle);
                        ++$xlsRow;
                        $sheet->write($xlsRow, 0,$xlsDesc);

                        $tableXLS = array();
                        $footXLS = array();

                        break;
                    case 'pdf':

                        $sPDFQuestion=FlattenText($qquestion,false,true);
                        $pdfTitle = $pdf->delete_html(sprintf($statlang->gT("Field summary for %s"),html_entity_decode($qtitle,ENT_QUOTES,'UTF-8')));
                        $titleDesc = $sPDFQuestion;

                        $pdf->addPage('P','A4');
                        $pdf->Bookmark($sPDFQuestion, 1, 0);
                        $pdf->titleintopdf($pdfTitle,$sPDFQuestion);
                        $tablePDF = array();
                        $footPDF = array();

                        break;
                    case 'html':
                        //output
                        $statisticsoutput .= "<table class='statisticstable'>\n"
                        ."\t<thead><tr><th colspan='4' align='center'><strong>"

                        //headline
                        .sprintf($statlang->gT("Field summary for %s"),$qtitle)."</strong>"
                        ."</th></tr>\n"
                        ."\t<tr><th colspan='4' align='center'><strong>"

                        //question title
                        .$qquestion."</strong></th></tr>\n"
                        ."\t<tr>\n\t\t<th width='50%' align='center' >";
                        break;
                    default:


                        break;
                }
                echo '';
                //loop thorugh the array which contains all answer data
                
    			$sDatabaseType = $CI->db->platform();
                foreach ($alist as $al)
                {
                    //picks out alist that come from the multiple list above
                    if (isset($al[2]) && $al[2])
                    {
                        //handling for "other" option

                        if ($al[0] == $statlang->gT("Other"))
                        {
                            if($qtype=='!' || $qtype=='L')
                            {
                                // It is better for single choice question types to filter on the number of '-oth-' entries, than to
                                // just count the number of 'other' values - that way with failing Javascript the statistics don't get messed up
                                $query = "SELECT count(*) FROM ".$CI->db->dbprefix("survey_$surveyid")." WHERE ".db_quote_id(substr($al[2],0,strlen($al[2])-5))."='-oth-'";
                            }
                            else
                            {
                            //get data
                            $query = "SELECT count(*) FROM ".$CI->db->dbprefix("survey_$surveyid")." WHERE ";
                            $query .= ($sDatabaseType == "mysql")?  db_quote_id($al[2])." != ''" : "NOT (".db_quote_id($al[2])." LIKE '')";
                        }
                        }

                        /*
                         * text questions:
                         *
                         * U = huge free text
                         * T = long free text
                         * S = short free text
                         * Q = multiple short text
                         */

                        elseif ($qtype == "U" || $qtype == "T" || $qtype == "S" || $qtype == "Q" || $qtype == ";")
                        {
                    			 
                            //free text answers
                            if($al[0]=="Answers")
                            {
                                $query = "SELECT count(*) FROM ".$CI->db->dbprefix("survey_$surveyid")." WHERE ";
                                $query .= ($sDatabaseType == "mysql")?  db_quote_id($al[2])." != ''" : "NOT (".db_quote_id($al[2])." LIKE '')";
                            }
                            //"no answer" handling
                            elseif($al[0]=="NoAnswer")
                            {
                                $query = "SELECT count(*) FROM ".$CI->db->dbprefix("survey_$surveyid")." WHERE ( ";
                                $query .= ($sDatabaseType == "mysql")?  db_quote_id($al[2])." = '')" : " (".db_quote_id($al[2])." LIKE ''))";
                            }
                        }
                        elseif ($qtype == "O")
                        {
                            $query = "SELECT count(*) FROM ".$CI->db->dbprefix("survey_$surveyid")." WHERE ( ";
                            $query .= ($sDatabaseType == "mysql")?  db_quote_id($al[2])." <> '')" : " (".db_quote_id($al[2])." NOT LIKE ''))";
                        // all other question types
                        }
                        else
                        {
                            $query = "SELECT count(*) FROM ".$CI->db->dbprefix("survey_$surveyid")." WHERE ".db_quote_id($al[2])." =";

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
                        if ($al[0] != "")
                        {
                            //get more data
                            $sDatabaseType = $CI->db->platform();
							if ($sDatabaseType == 'odbc_mssql' || $sDatabaseType == 'odbtp' || $sDatabaseType == 'mssql_n' || $sDatabaseType == 'mssqlnative')
                            {
                                // mssql cannot compare text blobs so we have to cast here
                                $query = "SELECT count(*) FROM ".$CI->db->dbprefix("survey_$surveyid")." WHERE cast(".db_quote_id($rt)." as varchar)= '$al[0]'";
                            }
                            else
                            $query = "SELECT count(*) FROM ".$CI->db->dbprefix("survey_$surveyid")." WHERE ".db_quote_id($rt)." = '$al[0]'";
                        }
                        else
                        { // This is for the 'NoAnswer' case
                            // We need to take into account several possibilities
                            // * NoAnswer cause the participant clicked the NoAnswer radio
                            //  ==> in this case value is '' or ' '
                            // * NoAnswer in text field
                            //  ==> value is ''
                            // * NoAnswer due to conditions, or a page not displayed
                            //  ==> value is NULL
                            if ($sDatabaseType == 'odbc_mssql' || $sDatabaseType == 'odbtp' || $sDatabaseType == 'mssql_n' || $sDatabaseType == 'mssqlnative')
                            {
                                // mssql cannot compare text blobs so we have to cast here
                                //$query = "SELECT count(*) FROM ".$CI->db->dbprefix("survey_$surveyid")." WHERE (".db_quote_id($rt)." IS NULL "
                                $query = "SELECT count(*) FROM ".$CI->db->dbprefix("survey_$surveyid")." WHERE ( "
                                //                                    . "OR cast(".db_quote_id($rt)." as varchar) = '' "
                                . "cast(".db_quote_id($rt)." as varchar) = '' "
                                . "OR cast(".db_quote_id($rt)." as varchar) = ' ' )";
                            }
                            else
                            //			    $query = "SELECT count(*) FROM ".$CI->db->dbprefix("survey_$surveyid")." WHERE (".db_quote_id($rt)." IS NULL "
                            $query = "SELECT count(*) FROM ".$CI->db->dbprefix("survey_$surveyid")." WHERE ( "
                            //								    . "OR ".db_quote_id($rt)." = '' "
                            . " ".db_quote_id($rt)." = '' "
                            . "OR ".db_quote_id($rt)." = ' ') ";
                        }

                    }

                    //check filter option
                    if (incompleteAnsFilterstate() == "inc") {$query .= " AND submitdate is null";}
                    elseif (incompleteAnsFilterstate() == "filter") {$query .= " AND submitdate is not null";}

                    //check for any "sql" that has been passed from another script
                    if ($sql != "NULL") {$query .= " AND $sql";}

                    //get data
                    $result=db_execute_assoc($query) or safe_die ("Couldn't do count of values<br />$query<br />".$connect->ErrorMsg());

                    // $statisticsoutput .= "\n<!-- ($sql): $query -->\n\n";

                    // this just extracts the data, after we present
                    foreach ($result->result_array() as $row)
                    {
                    	$row=array_values($row);
                        //increase counter
                        $TotalCompleted += $row[0];

                        //"no answer" handling
                        if ($al[0] === "")
                        {$fname=$statlang->gT("No answer");}

                        //"other" handling
                        //"Answers" means that we show an option to list answer to "other" text field
                        elseif ($al[0] === $statlang->gT("Other") || $al[0] === "Answers" || ($qtype === "O" && $al[0] === $statlang->gT("Comments")) || $qtype === "P")
                        {
                            if ($qtype == "P") $ColumnName_RM = $al[2]."comment";
                            else  $ColumnName_RM = $al[2];
                            if ($qtype=='O') {
                                $TotalCompleted -=$row[0];
                            }
                            $fname="$al[1]";
                            if ($browse===true) $fname .= " <input type='button' value='".$statlang->gT("Browse")."' onclick=\"window.open('admin.php?action=listcolumn&amp;sid=$surveyid&amp;column=$ColumnName_RM&amp;sql=".urlencode($sql)."', 'results', 'width=460, height=500, left=50, top=50, resizable=yes, scrollbars=yes, menubar=no, status=no, location=no, toolbar=no')\" />";
                        }

                        /*
                         * text questions:
                         *
                         * U = huge free text
                         * T = long free text
                         * S = short free text
                         * Q = multiple short text
                         */
                        elseif ($qtype == "S" || $qtype == "U" || $qtype == "T" || $qtype == "Q")
                        {
                            $headPDF = array();
                            $headPDF[] = array($statlang->gT("Answer"),$statlang->gT("Count"),$statlang->gT("Percentage"));

                            //show free text answers
                            if ($al[0] == "Answers")
                            {
                                $fname= "$al[1]";
                                if ($browse===true) $fname .= " <input type='submit' value='"
                                . $statlang->gT("Browse")."' onclick=\"window.open('admin.php?action=listcolumn&sid=$surveyid&amp;column=$al[2]&amp;sql="
                                . urlencode($sql)."', 'results', 'width=460, height=500, left=50, top=50, resizable=yes, scrollbars=yes, menubar=no, status=no, location=no, toolbar=no')\" />";
                            }
                            elseif ($al[0] == "NoAnswer")
                            {
                                $fname= "$al[1]";
                            }

							$statisticsoutput .= "</th>\n"
							."\t\t<th width='25%' align='center' >"
							."<strong>".$statlang->gT("Count")."</strong></th>\n"
							."\t\t<th width='25%' align='center' >"
							."<strong>".$statlang->gT("Percentage")."</strong></th>\n"
							."\t</tr></thead>\n";
                        }


                        //check if aggregated results should be shown
                        elseif ($CI->config->item('showaggregateddata') == 1)
                        {
                            if(!isset($showheadline) || $showheadline != false)
                            {
                                if($qtype == "5" || $qtype == "A")
                                {
                                    switch($outputType)
                                    {
                                        case 'xls':

                                            $headXLS = array();
                                            $headXLS[] = array($statlang->gT("Answer"),$statlang->gT("Count"),$statlang->gT("Percentage"),$statlang->gT("Sum"));

                                            ++$xlsRow;
                                            $sheet->write($xlsRow,0,$statlang->gT("Answer"));
                                            $sheet->write($xlsRow,1,$statlang->gT("Count"));
                                            $sheet->write($xlsRow,2,$statlang->gT("Percentage"));
                                            $sheet->write($xlsRow,3,$statlang->gT("Sum"));

                                            break;
                                        case 'pdf':

                                            $headPDF = array();
                                            $headPDF[] = array($statlang->gT("Answer"),$statlang->gT("Count"),$statlang->gT("Percentage"),$statlang->gT("Sum"));

                                            break;
                                        case 'html':
                                            //four columns
                                            $statisticsoutput .= "<strong>".$statlang->gT("Answer")."</strong></th>\n"
                                            ."\t\t<th width='15%' align='center' >"
                                            ."<strong>".$statlang->gT("Count")."</strong></th>\n"
                                            ."\t\t<th width='20%' align='center' >"
                                            ."<strong>".$statlang->gT("Percentage")."</strong></th>\n"
                                            ."\t\t<th width='15%' align='center' >"
                                            ."<strong>".$statlang->gT("Sum")."</strong></th>\n"
                                            ."\t</tr></thead>\n";
                                            break;
                                        default:


                                            break;
                                    }


                                    $showheadline = false;
                                }
                                else
                                {
                                    switch($outputType)
                                    {
                                        case 'xls':

                                            $headXLS = array();
                                            $headXLS[] = array($statlang->gT("Answer"),$statlang->gT("Count"),$statlang->gT("Percentage"));

                                            ++$xlsRow;
                                            $sheet->write($xlsRow,0,$statlang->gT("Answer"));
                                            $sheet->write($xlsRow,1,$statlang->gT("Count"));
                                            $sheet->write($xlsRow,2,$statlang->gT("Percentage"));

                                            break;

                                        case 'pdf':

                                            $headPDF = array();
                                            $headPDF[] = array($statlang->gT("Answer"),$statlang->gT("Count"),$statlang->gT("Percentage"));

                                            break;
                                        case 'html':
                                            //three columns
                                            $statisticsoutput .= "<strong>".$statlang->gT("Answer")."</strong></td>\n"
                                            ."\t\t<th width='25%' align='center' >"
                                            ."<strong>".$statlang->gT("Count")."</strong></th>\n"
                                            ."\t\t<th width='25%' align='center' >"
                                            ."<strong>".$statlang->gT("Percentage")."</strong></th>\n"
                                            ."\t</tr></thead>\n";
                                            break;
                                        default:


                                            break;
                                    }

                                    $showheadline = false;
                                }

                            }

                            //text for answer column is always needed
                            $fname="$al[1] ($al[0])";

                            //these question types get special treatment by $CI->config->item('showaggregateddata')
                            if($qtype == "5" || $qtype == "A")
                            {
                                //put non-edited data in here because $row will be edited later
                                $grawdata[]=$row[0];
                                $showaggregated_indice=count($grawdata) - 1;
                                $showaggregated_indice_table[$showaggregated_indice]="aggregated";
                                $showaggregated_indice=-1;

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
                                    $row[0] += (3*$results);
                                }

                                //the third value should be shown twice later -> mark it
                                if($testcounter == 2)	//add 400 to original value
                                {
                                    //HACK: add four times the total number of results to the value
                                    //This way there should be a 400 + X percentage which can be checked later
                                    $row[0] += (4*$results);
                                }

                                //the last value aggregates the data of item 4 + item 5 later
                                if($testcounter == 4 )	//add 200 to original value
                                {
                                    //HACK: add two times the total number of results to the value
                                    //This way there should be a 200 + X percentage which can be checked later
                                    $row[0] += (2*$results);
                                }

                            }	//end if -> question type = "5"/"A"

                        }	//end if -> show aggregated data

                        //handling what's left
                        else
                        {
                            if(!isset($showheadline) || $showheadline != false)
                            {
                                switch($outputType)
                                {
                                    case 'xls':

                                        $headXLS = array();
                                        $headXLS[] = array($statlang->gT("Answer"),$statlang->gT("Count"),$statlang->gT("Percentage"));

                                        ++$xlsRow;
                                        $sheet->write($xlsRow,0,$statlang->gT("Answer"));
                                        $sheet->write($xlsRow,1,$statlang->gT("Count"));
                                        $sheet->write($xlsRow,2,$statlang->gT("Percentage"));

                                        break;
                                    case 'pdf':

                                        $headPDF = array();
                                        $headPDF[] = array($statlang->gT("Answer"),$statlang->gT("Count"),$statlang->gT("Percentage"));

                                        break;
                                    case 'html':
                                        //three columns
                                        $statisticsoutput .= "<strong>".$statlang->gT("Answer")."</strong></th>\n"
                                        ."\t\t<th width='25%' align='center' >"
                                        ."<strong>".$statlang->gT("Count")."</strong></th>\n"
                                        ."\t\t<th width='25%' align='center' >"
                                        ."<strong>".$statlang->gT("Percentage")."</strong></th>\n"
                                        ."\t</tr></thead>\n";
                                        break;
                                    default:


                                        break;
                                }

                                $showheadline = false;

                            }
                            //answer text
                            $fname="$al[1] ($al[0])";
                        }

                        //are there some results to play with?
                        if ($results > 0)
                        {
                            //calculate percentage
                            $gdata[] = ($row[0]/$results)*100;
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
                        $lbl[] = wordwrap(FlattenText("$al[1] ($row[0])"), 25, "\n"); // NMO 2009-03-24
                        $lblrtl[] = utf8_strrev(wordwrap(FlattenText("$al[1] )$row[0]("), 25, "\n")); // NMO 2009-03-24

                    }	//end while -> loop through results

                }	//end foreach -> loop through answer data

                //no filtering of incomplete answers and NO multiple option questions
                //if ((incompleteAnsFilterstate() != "filter") and ($qtype != "M") and ($qtype != "P"))
                //error_log("TIBO ".print_r($showaggregated_indice_table,true));
                if (($qtype != "M") and ($qtype != "P"))
                {
                    //is the checkbox "Don't consider NON completed responses (only works when Filter incomplete answers is Disable)" checked?
                    //if (isset($_POST["noncompleted"]) and ($_POST["noncompleted"] == "on") && (isset($CI->config->item('showaggregateddata')) && $CI->config->item('showaggregateddata') == 0))
                    // TIBO: TODO WE MUST SKIP THE FOLLOWING SECTION FOR TYPE A and 5 when
                    // showaggreagated data is set and set to 1
                    if (isset($_POST["noncompleted"]) and ($_POST["noncompleted"] == "on") )
                    {
                        //counter
                        $i=0;

                        while (isset($gdata[$i]))
                        {
                            if (isset($showaggregated_indice_table[$i]) && $showaggregated_indice_table[$i]=="aggregated")
                            { // do nothing, we don't rewrite aggregated results
                                // or at least I don't know how !!! (lemeur)
                            }
                            else
                            {
                                //we want to have some "real" data here
                                if ($gdata[$i] != "N/A")
                                {
                                    //calculate percentage
                                    $gdata[$i] = ($grawdata[$i]/$TotalCompleted)*100;
                                }
                            }

                            //increase counter
                            $i++;

                        }	//end while (data available)

                    }	//end if -> noncompleted checked

                    //noncompleted is NOT checked
                    else
                    {
                        //calculate total number of incompleted records
                        $TotalIncomplete = $results - $TotalCompleted;

                        //output
                        if ((incompleteAnsFilterstate() != "filter"))
                        {
                            $fname=$statlang->gT("Not completed or Not displayed");
                        }
                        else
                        {
                            $fname=$statlang->gT("Not displayed");
                        }

                        //we need some data
                        if ($results > 0)
                        {
                            //calculate percentage
                            $gdata[] = ($TotalIncomplete/$results)*100;
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
                        if ((incompleteAnsFilterstate() != "filter"))
                        {
                            $lbl[] = wordwrap(FlattenText($statlang->gT("Not completed or Not displayed")." ($TotalIncomplete)"), 20, "\n"); // NMO 2009-03-24
                        }
                        else
                        {
                            $lbl[] = wordwrap(FlattenText($statlang->gT("Not displayed")." ($TotalIncomplete)"), 20, "\n"); // NMO 2009-03-24
                        }
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
                    $statisticsoutput .= "\t<tr>\n\t\t<td align='center' >" . $label[$i] ."\n"
                    ."\t\t</td>\n"

                    //output absolute number of records
                    ."\t\t<td align='center' >" . $grawdata[$i] . "\n</td>";


                    //no data
                    if ($gdata[$i] == "N/A")
                    {
                        switch($outputType)
                        {
                            case 'xls':

                                $label[$i]=FlattenText($label[$i]);
                                $tableXLS[] = array($label[$i],$grawdata[$i],sprintf("%01.2f", $gdata[$i]). "%");

                                ++$xlsRow;
                                $sheet->write($xlsRow,0,$label[$i]);
                                $sheet->write($xlsRow,1,$grawdata[$i]);
                                $sheet->write($xlsRow,2,sprintf("%01.2f", $gdata[$i]). "%");

                                break;
                            case 'pdf':

                                $tablePDF[] = array(FlattenText($label[$i]),$grawdata[$i],sprintf("%01.2f", $gdata[$i]). "%", "");

                                break;
                            case 'html':
                                //output when having no data
                                $statisticsoutput .= "\t\t<td  align='center' >";

                                //percentage = 0
                                $statisticsoutput .= sprintf("%01.2f", $gdata[$i]) . "%";
                                $gdata[$i] = 0;

                                //check if we have to adjust ouput due to $CI->config->item('showaggregateddata') setting
                                if($CI->config->item('showaggregateddata') == 1 && ($qtype == "5" || $qtype == "A"))
                                {
                                    $statisticsoutput .= "\t\t</td>";
                                }
								elseif ($qtype == "S" || $qtype == "U" || $qtype == "T" || $qtype == "Q")
                        		{
                                    $statisticsoutput .= "</td>\n\t</tr>\n";
                                }
                                break;
                            default:


                                break;
                        }

                    }

                    //data available
                    else
                    {
                        //check if data should be aggregated
                        if($CI->config->item('showaggregateddata') == 1 && ($qtype == "5" || $qtype == "A"))
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
                                if($itemcounter == 2 && $label[$i+4] == $statlang->gT("No answer"))
                                {
                                    //prevent division by zero
                                    if(($results - $grawdata[$i+4]) > 0)
                                    {
                                        //re-calculate percentage
                                        $percentage = ($grawdata[$i] / ($results - $grawdata[$i+4])) * 100;
                                    }
                                    else
                                    {
                                        $percentage = 0;
                                    }

                                }
                                elseif($itemcounter == 4 && $label[$i+2] == $statlang->gT("No answer"))
                                {
                                    //prevent division by zero
                                    if(($results - $grawdata[$i+2]) > 0)
                                    {
                                        //re-calculate percentage
                                        $percentage = ($grawdata[$i] / ($results - $grawdata[$i+2])) * 100;
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
                                switch($outputType)
                                {
                                    case 'xls':

                                        $label[$i]=FlattenText($label[$i]);
                                        $tableXLS[]= array($label[$i],$grawdata[$i],sprintf("%01.2f", $percentage)."%");

                                        ++$xlsRow;
                                        $sheet->write($xlsRow,0,$label[$i]);
                                        $sheet->write($xlsRow,1,$grawdata[$i]);
                                        $sheet->write($xlsRow,2,sprintf("%01.2f", $percentage)."%");

                                        break;
                                    case 'pdf':
                                        $label[$i]=FlattenText($label[$i]);
                                        $tablePDF[] = array($label[$i],$grawdata[$i],sprintf("%01.2f", $percentage)."%", "");

                                        break;
                                    case 'html':
                                        //output
                                        $statisticsoutput .= "\t\t<td align='center'>";

                                        //output percentage
                                        $statisticsoutput .= sprintf("%01.2f", $percentage) . "%";

                                        //adjust output
                                        $statisticsoutput .= "\t\t</td>";
                                        break;
                                    default:


                                        break;
                                }

                            }

                            //item 3 - just show results twice
                            //old: if($gdata[$i] >= 400)
                            //trying to fix bug #2583:
                            if($gdata[$i] >= 400 && $i != 0)
                            {
                                //remove "400" which was added before
                                $gdata[$i] -= 400;

                                if($itemcounter == 3 && $label[$i+3] == $statlang->gT("No answer"))
                                {
                                    //prevent division by zero
                                    if(($results - $grawdata[$i+3]) > 0)
                                    {
                                        //re-calculate percentage
                                        $percentage = ($grawdata[$i] / ($results - $grawdata[$i+3])) * 100;
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
                                switch($outputType)
                                {
                                    case 'xls':

                                        $label[$i]=FlattenText($label[$i]);
                                        $tableXLS[] = array($label[$i],$grawdata[$i],sprintf("%01.2f", $percentage)."%",sprintf("%01.2f", $percentage)."%");

                                        ++$xlsRow;
                                        $sheet->write($xlsRow,0,$label[$i]);
                                        $sheet->write($xlsRow,1,$grawdata[$i]);
                                        $sheet->write($xlsRow,2,sprintf("%01.2f", $percentage)."%");
                                        $sheet->write($xlsRow,3,sprintf("%01.2f", $percentage)."%");

                                        break;
                                    case 'pdf':
                                        $label[$i]=FlattenText($label[$i]);
                                        $tablePDF[] = array($label[$i],$grawdata[$i],sprintf("%01.2f", $percentage)."%",sprintf("%01.2f", $percentage)."%");

                                        break;
                                    case 'html':
                                        //output percentage
                                        $statisticsoutput .= "\t\t<td align='center' >";
                                        $statisticsoutput .= sprintf("%01.2f", $percentage) . "%</td>";

                                        //output again (no real aggregation here)
                                        $statisticsoutput .= "\t\t<td align='center' >";
                                        $statisticsoutput .= sprintf("%01.2f", $percentage)."%";
                                        $statisticsoutput .= "</td>\t\t";
                                        break;
                                    default:


                                        break;
                                }

                            }

                            //FIRST value -> add percentage of item 1 + item 2
                            //old: if($gdata[$i] >= 300 && $gdata[$i] < 400)
                            //trying to fix bug #2583:
                            if(($gdata[$i] >= 300 && $gdata[$i] < 400) || ($i == 0 && $gdata[$i] <= 400))
                            {
                                //remove "300" which was added before
                                $gdata[$i] -= 300;

                                if($itemcounter == 1 && $label[$i+5] == $statlang->gT("No answer"))
                                {
                                    //prevent division by zero
                                    if(($results - $grawdata[$i+5]) > 0)
                                    {
                                        //re-calculate percentage
                                        $percentage = ($grawdata[$i] / ($results - $grawdata[$i+5])) * 100;
                                        $percentage2 = ($grawdata[$i + 1] / ($results - $grawdata[$i+5])) * 100;
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


                                switch($outputType)
                                {
                                    case 'xls':

                                        $label[$i]=FlattenText($label[$i]);
                                        $tableXLS[] = array($label[$i],$grawdata[$i],sprintf("%01.2f", $percentage)."%",sprintf("%01.2f", $aggregatedgdata)."%");

                                        ++$xlsRow;
                                        $sheet->write($xlsRow,0,$label[$i]);
                                        $sheet->write($xlsRow,1,$grawdata[$i]);
                                        $sheet->write($xlsRow,2,sprintf("%01.2f", $percentage)."%");
                                        $sheet->write($xlsRow,3,sprintf("%01.2f", $aggregatedgdata)."%");

                                        break;
                                    case 'pdf':
                                        $label[$i]=FlattenText($label[$i]);
                                        $tablePDF[] = array($label[$i],$grawdata[$i],sprintf("%01.2f", $percentage)."%",sprintf("%01.2f", $aggregatedgdata)."%");

                                        break;
                                    case 'html':
                                        //output percentage
                                        $statisticsoutput .= "\t\t<td align='center' >";
                                        $statisticsoutput .= sprintf("%01.2f", $percentage) . "%</td>";

                                        //output aggregated data
                                        $statisticsoutput .= "\t\t<td align='center' >";
                                        $statisticsoutput .= sprintf("%01.2f", $aggregatedgdata)."%";
                                        $statisticsoutput .= "</td>\t\t";
                                        break;
                                    default:


                                        break;
                                }
                            }

                            //LAST value -> add item 4 + item 5
                            if($gdata[$i] > 100 && $gdata[$i] < 300)
                            {
                                //remove "200" which was added before
                                $gdata[$i] -= 200;

                                if($itemcounter == 5 && $label[$i+1] == $statlang->gT("No answer"))
                                {
                                    //prevent division by zero
                                    if(($results - $grawdata[$i+1]) > 0)
                                    {
                                        //re-calculate percentage
                                        $percentage = ($grawdata[$i] / ($results - $grawdata[$i+1])) * 100;
                                        $percentage2 = ($grawdata[$i - 1] / ($results - $grawdata[$i+1])) * 100;
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
                                switch($outputType)
                                {
                                    case 'xls':

                                        $label[$i]=FlattenText($label[$i]);
                                        $tableXLS[] = array($label[$i],$grawdata[$i],sprintf("%01.2f", $percentage)."%",sprintf("%01.2f", $aggregatedgdata)."%");

                                        ++$xlsRow;
                                        $sheet->write($xlsRow,0,$label[$i]);
                                        $sheet->write($xlsRow,1,$grawdata[$i]);
                                        $sheet->write($xlsRow,2,sprintf("%01.2f", $percentage)."%");
                                        $sheet->write($xlsRow,3,sprintf("%01.2f", $aggregatedgdata)."%");

                                        break;
                                    case 'pdf':
                                        $label[$i]=FlattenText($label[$i]);
                                        $tablePDF[] = array($label[$i],$grawdata[$i],sprintf("%01.2f", $percentage)."%",sprintf("%01.2f", $aggregatedgdata)."%");

                                        break;
                                    case 'html':
                                        //output percentage
                                        $statisticsoutput .= "\t\t<td align='center' >";
                                        $statisticsoutput .= sprintf("%01.2f", $percentage) . "%</td>";

                                        //output aggregated data
                                        $statisticsoutput .= "\t\t<td align='center' >";
                                        $statisticsoutput .= sprintf("%01.2f", $aggregatedgdata)."%";
                                        $statisticsoutput .= "</td>\t\t";
                                        break;
                                    default:


                                        break;
                                }

                                // create new row "sum"
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
                                switch($outputType)
                                {
                                    case 'xls':


                                        $footXLS[] = array($statlang->gT("Sum")." (".$statlang->gT("Answers").")",$sumitems,$sumpercentage."%",$sumpercentage."%");
                                        $footXLS[] = array($statlang->gT("Number of cases"),$TotalCompleted,$casepercentage."%","");

                                        ++$xlsRow;
                                        $sheet->write($xlsRow,0,$statlang->gT("Sum")." (".$statlang->gT("Answers").")");
                                        $sheet->write($xlsRow,1,$sumitems);
                                        $sheet->write($xlsRow,2,$sumpercentage."%");
                                        $sheet->write($xlsRow,3,$sumpercentage."%");
                                        ++$xlsRow;
                                        $sheet->write($xlsRow,0,$statlang->gT("Number of cases"));
                                        $sheet->write($xlsRow,1,$TotalCompleted);
                                        $sheet->write($xlsRow,2,$casepercentage."%");

                                        break;
                                    case 'pdf':

                                        $footPDF[] = array($statlang->gT("Sum")." (".$statlang->gT("Answers").")",$sumitems,$sumpercentage."%",$sumpercentage."%");
                                        $footPDF[] = array($statlang->gT("Number of cases"),$TotalCompleted,$casepercentage."%","");

                                        break;
                                    case 'html':
                                        $statisticsoutput .= "\t\t&nbsp;\n\t</tr>\n";
                                        $statisticsoutput .= "<tr><td align='center'><strong>".$statlang->gT("Sum")." (".$statlang->gT("Answers").")</strong></td>";
                                        $statisticsoutput .= "<td align='center' ><strong>".$sumitems."</strong></td>";
                                        $statisticsoutput .= "<td align='center' ><strong>$sumpercentage%</strong></td>";
                                        $statisticsoutput .= "<td align='center' ><strong>$sumpercentage%</strong></td>";
                                        $statisticsoutput .= "\t\t&nbsp;\n\t</tr>\n";

                                        $statisticsoutput .= "<tr><td align='center'>".$statlang->gT("Number of cases")."</td>";	//German: "Fallzahl"
                                        $statisticsoutput .= "<td align='center' >".$TotalCompleted."</td>";
                                        $statisticsoutput .= "<td align='center' >$casepercentage%</td>";
                                        //there has to be a whitespace within the table cell to display correctly
                                        $statisticsoutput .= "<td align='center' >&nbsp;</td></tr>";
                                        break;
                                    default:


                                        break;
                                }

                            }

                        }	//end if -> show aggregated data

                        //don't show aggregated data
                        else
                        {
                            switch($outputType)
                            {
                                case 'xls':
                                    $label[$i]=FlattenText($label[$i]);
                                    $tableXLS[] = array($label[$i],$grawdata[$i],sprintf("%01.2f", $gdata[$i])."%", "");

                                    ++$xlsRow;
                                    $sheet->write($xlsRow,0,$label[$i]);
                                    $sheet->write($xlsRow,1,$grawdata[$i]);
                                    $sheet->write($xlsRow,2,sprintf("%01.2f", $gdata[$i])."%");

                                    break;
                                case 'pdf':
                                    $label[$i]=FlattenText($label[$i]);
                                    $tablePDF[] = array($label[$i],$grawdata[$i],sprintf("%01.2f", $gdata[$i])."%", "");

                                    break;
                                case 'html':
                                    //output percentage
                                    $statisticsoutput .= "\t\t<td align='center' >";
                                    $statisticsoutput .= sprintf("%01.2f", $gdata[$i]) . "%";
                                    $statisticsoutput .= "\t\t";
                                    //end output per line. there has to be a whitespace within the table cell to display correctly
                                    $statisticsoutput .= "\t\t&nbsp;</td>\n\t</tr>\n";
                                    break;
                                default:


                                    break;
                            }

                        }

                    }	//end else -> $gdata[$i] != "N/A"



                    //increase counter
                    $i++;

                    $itemcounter++;

                }	//end while

                //only show additional values when this setting is enabled
                if($CI->config->item('showaggregateddata') == 1 )
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
                        switch($outputType)
                        {
                            case 'xls':

                                $tableXLS[] = array($statlang->gT("Arithmetic mean"),$am,'','');
                                $tableXLS[] = array($statlang->gT("Standard deviation"),$stddev,'','');

                                ++$xlsRow;
                                $sheet->write($xlsRow,0,$statlang->gT("Arithmetic mean"));
                                $sheet->write($xlsRow,1,$am);

                                ++$xlsRow;
                                $sheet->write($xlsRow,0,$statlang->gT("Standard deviation"));
                                $sheet->write($xlsRow,1,$stddev);

                                break;
                            case 'pdf':

                                $tablePDF[] = array($statlang->gT("Arithmetic mean"),$am,'','');
                                $tablePDF[] = array($statlang->gT("Standard deviation"),$stddev,'','');

                                break;
                            case 'html':
                                //calculate standard deviation
                                $statisticsoutput .= "<tr><td align='center'>".$statlang->gT("Arithmetic mean")."</td>";	//German: "Fallzahl"
                                $statisticsoutput .= "<td>&nbsp;</td><td align='center'> $am</td><td>&nbsp;</td></tr>";
                                $statisticsoutput .= "<tr><td align='center'>".$statlang->gT("Standard deviation")."</td>";    //German: "Fallzahl"
                                $statisticsoutput .= "<td>&nbsp;</td><td align='center'>$stddev</td><td>&nbsp;</td></tr>";

                                break;
                            default:


                                break;
                        }
                    }
                }

                if($outputType=='pdf') //XXX TODO PDF
                {
                    //$tablePDF = array();
                    $tablePDF = array_merge_recursive($tablePDF, $footPDF);
                    $pdf->headTable($headPDF,$tablePDF);
                    //$pdf->tableintopdf($tablePDF);

                    //				if(isset($footPDF))
                    //				foreach($footPDF as $foot)
                    //				{
                    //					$footA = array($foot);
                    //					$pdf->tablehead($footA);
                    //				}
                }




                //-------------------------- PCHART OUTPUT ----------------------------

                //PCHART has to be enabled and we need some data
                if ($usegraph==1 && array_sum($gdata)>0)
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

                    // Create bar chart for Multiple choice
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

                        if ($maxyvalue<10) {++$maxyvalue;}
                        $counter=0;
                        foreach ($lbl as $label)
                        {
                            $DataSet->SetSerieName($label,"Serie$counter");
                            $counter++;
                        }

                        if ($MyCache->IsInCache("graph".$surveyid,$DataSet->GetData()))
                        {
                            $cachefilename=basename($MyCache->GetFileFromCache("graph".$surveyid,$DataSet->GetData()));
                        }
                        else
                        {
                            $graph = new pChart(1,1);

                            $graph->setFontProperties($rootdir."/fonts/".$chartfontfile, $chartfontsize);
                            $legendsize=$graph->getLegendBoxSize($DataSet->GetDataDescription());

                            if ($legendsize[1]<320) $gheight=420; else $gheight=$legendsize[1]+100;
                            $graph = new pChart(690+$legendsize[0],$gheight);
                            $graph->loadColorPalette($homedir.'/styles/'.$admintheme.'/limesurvey.pal');
                            $graph->setFontProperties($rootdir."/fonts/".$chartfontfile,$chartfontsize);
                            $graph->setGraphArea(50,30,500,$gheight-60);
                            $graph->drawFilledRoundedRectangle(7,7,523+$legendsize[0],$gheight-7,5,254,255,254);
                            $graph->drawRoundedRectangle(5,5,525+$legendsize[0],$gheight-5,5,230,230,230);
                            $graph->drawGraphArea(255,255,255,TRUE);
                            $graph->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_START0,150,150,150,TRUE,90,0,TRUE,5,false);
                            $graph->drawGrid(4,TRUE,230,230,230,50);
                            // Draw the 0 line
                            $graph->setFontProperties($rootdir."/fonts/".$chartfontfile,$chartfontsize);
                            $graph->drawTreshold(0,143,55,72,TRUE,TRUE);

                            // Draw the bar graph
                            $graph->drawBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),FALSE);
                            //$Test->setLabel($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie4","1","Important point!");
                            // Finish the graph
                            $graph->setFontProperties($rootdir."/fonts/".$chartfontfile, $chartfontsize);
                            $graph->drawLegend(510,30,$DataSet->GetDataDescription(),255,255,255);

                            $MyCache->WriteToCache("graph".$surveyid,$DataSet->GetData(),$graph);
                            $cachefilename=basename($MyCache->GetFileFromCache("graph".$surveyid,$DataSet->GetData()));
                            unset($graph);
                        }
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

                        $lblout=array();
                        if ($language=='ar')
                        {
                            $lblout=$lbl; //reset text order to original
                            $CI->load->library("admin/Arabic");
                            $Arabic = new Arabic('ArGlyphs');
                            foreach($lblout as $kkey => $kval){
                                if (preg_match("^[A-Za-z]^", $kval)) { //auto detect if english
                                    //eng
                                    //no reversing
                                }
                                else{
                                    $kval = $Arabic->utf8Glyphs($kval,50,false);
                                    $lblout[$kkey] = $kval;
                                }
                            }
                        }
                        elseif (getLanguageRTL($language))
                        {
                            $lblout=$lblrtl;
                        }
                        else
                        {
                            $lblout=$lbl;
                        }


                        //create new 3D pie chart
                        if ($usegraph==1)
                        {
                            $DataSet = new pData;
                            $DataSet->AddPoint($gdata,"Serie1");
                            $DataSet->AddPoint($lblout,"Serie2");
                            $DataSet->AddAllSeries();
                            $DataSet->SetAbsciseLabelSerie("Serie2");

                            if ($MyCache->IsInCache("graph".$surveyid,$DataSet->GetData()))
                            {
                                $cachefilename=basename($MyCache->GetFileFromCache("graph".$surveyid,$DataSet->GetData()));
                            }
                            else
                            {

                                $gheight=ceil($gheight);
                                $graph = new pChart(690,$gheight);
                                $graph->loadColorPalette($homedir.'/styles/'.$admintheme.'/limesurvey.pal');
                                $graph->drawFilledRoundedRectangle(7,7,687,$gheight-3,5,254,255,254);
                                $graph->drawRoundedRectangle(5,5,689,$gheight-1,5,230,230,230);

                                // Draw the pie chart
                                $graph->setFontProperties($rootdir."/fonts/".$chartfontfile, $chartfontsize);
                                $graph->drawPieGraph($DataSet->GetData(),$DataSet->GetDataDescription(),225,round($gheight/2),170,PIE_PERCENTAGE,TRUE,50,20,5);
                                $graph->setFontProperties($rootdir."/fonts/".$chartfontfile,$chartfontsize);
                                $graph->drawPieLegend(430,12,$DataSet->GetData(),$DataSet->GetDataDescription(),250,250,250);
                                $MyCache->WriteToCache("graph".$surveyid,$DataSet->GetData(),$graph);
                                $cachefilename=basename($MyCache->GetFileFromCache("graph".$surveyid,$DataSet->GetData()));
                                unset($graph);
                            }
                            //print_r($DataSet->GetData()); echo "<br/><br/>";
                        }

                    }	//end else -> pie charts

                    //introduce new counter
                    if (!isset($ci)) {$ci=0;}

                    //increase counter, start value -> 1
                    $ci++;
                    switch($outputType)
                    {
                        case 'xls':

                            /**
                             * No Image for Excel...
                             */

                            break;
                        case 'pdf':

                            $pdf->AddPage('P','A4');

                            $pdf->titleintopdf($pdfTitle,$titleDesc);
                            $pdf->Image($tempdir."/".$cachefilename, 0, 70, 180, 0, '', $homeurl."/admin.php?sid=$surveyid", 'B', true, 150,'C',false,false,0,true);

                            break;
                        case 'html':
                        
                            $statisticsoutput .= "<tr><td colspan='4' style=\"text-align:center\"><img src=\"$tempurl/".$cachefilename."\" border='1' /></td></tr>";
                            
                            $aattr = getQuestionAttributeValues($qqid, $firstletter);	
                            if ($aattr["location_mapservice"] == "1") {
                                $statisticsoutput .= "<tr><td colspan='4'><div style=\"margin:auto;width:{$aattr['location_mapwidth']}px;height:{$aattr['location_mapheight']}px;\" id=\"statisticsmap_$fld\" class=\"statisticsmap\"></div></td></tr>";
                            
                                $aresult = $CI->Surveys_dynamic_model->getSomeRecords(array($fld), $surveyid)->result();
                                
                                $agmapdata[$fld] = array (
                                    "zoom" => $aattr['location_mapzoom'],
                                    "coord" => array()
                                );

                                //loop through question data
                                foreach ($aresult as $arow)
                                {
                                    $alocation = explode(";", $arow->$fld);                        
                                    $agmapdata[$fld]["coord"][] = "{$alocation[0]} {$alocation[1]}";
                                }
                            }
                            break;
                        default:


                            break;
                    }

                }

                //close table/output
                if($outputType=='html')
                $statisticsoutput .= "</table><br /> \n";

            }	//end if -> collect and display results

            //delete data
            unset($gdata);
            unset($grawdata);
            unset($label);
            unset($lbl);
            unset($lblrtl);
            unset($lblout);
            unset($justcode);
            unset ($alist);

        }	// end foreach -> loop through all questions

        //output
        if($outputType=='html')
        $statisticsoutput .= "<br />&nbsp;\n";

    }	//end if -> show summary results

    switch($outputType)
    {
        case 'xls':

            $workbook->close();

            if($pdfOutput=='F')
            {
                return $sFileName;
            }
            else
            {
                return;
            }
            break;

        case 'pdf':
            $pdf->lastPage();
            if($pdfOutput=='F')
            { // This is only used by lsrc to send an E-Mail attachment, so it gives back the filename to send and delete afterwards
                $pdf->Output($tempdir."/".$statlang->gT('Survey').'_'.$surveyid."_".$surveyInfo['surveyls_title'].'.pdf', $pdfOutput);
                return $tempdir."/".$statlang->gT('Survey').'_'.$surveyid."_".$surveyInfo['surveyls_title'].'.pdf';
            }
            else
            return $pdf->Output($statlang->gT('Survey').'_'.$surveyid."_".$surveyInfo['surveyls_title'].'.pdf', $pdfOutput);

            break;
        case 'html':
			$statisticsoutput .= "<script type=\"text/javascript\" src=\"http://maps.googleapis.com/maps/api/js?sensor=false\"></script>"
								."<script type=\"text/javascript\">var aGMapData=".json_encode($agmapdata)	.";</script>";
            return $statisticsoutput;

            break;
        default:
            return $statisticsoutput;

            break;
    }

}

////XXXXXX***//

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
