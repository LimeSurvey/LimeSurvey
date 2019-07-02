<?php
/*
* LimeSurvey
* Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/


/**
*
*  Generate a chart for a question
*  @param int $iQuestionID      ID of the question
*  @param int $iSurveyID        ID of the survey
*  @param mixed $type           Type of the chart to be created - null produces bar chart, any other value produces pie chart
*  @param array $lbl            An array containing the labels for the chart items
*  @param mixed $gdata          An array containing the percentages for the chart items
*  @param mixed $grawdata       An array containing the raw count for the chart items
*  @param pCache $cache          An object containing [Hashkey] and [CacheFolder]
*  @param mixed $sLanguageCode  Language Code
*  @param string $sQuestionType The question type
*  @return                false|string
*/
function createChart($iQuestionID, $iSurveyID, $type = null, $lbl, $gdata, $grawdata, $cache, $sLanguageCode, $sQuestionType)
{
    /* This is a lazy solution to bug #6389. A better solution would be to find out how
    the "T" gets passed to this function from the statistics.js file in the first place! */
    if (substr($iSurveyID, 0, 1) == "T") {$iSurveyID = substr($iSurveyID, 1); }
    static $bErrorGenerate = false;

    if ($bErrorGenerate) {
        return false;
    }
    $rootdir = Yii::app()->getConfig("rootdir");
    $chartfontfile = Yii::app()->getConfig("chartfontfile");
    $chartfontsize = Yii::app()->getConfig("chartfontsize");
    $alternatechartfontfile = Yii::app()->getConfig("alternatechartfontfile");
    $cachefilename = "";

    $adminThemePath = AdminTheme::getInstance()->path;

    /* Set the fonts for the chart */
    if ($chartfontfile == 'auto') {
        // Tested with ar,be,el,fa,hu,he,is,lt,mt,sr, and en (english)
        // Not working for hi, si, zh, th, ko, ja : see $config['alternatechartfontfile'] to add some specific language font
        $chartfontfile = 'DejaVuSans.ttf';
        if (array_key_exists($sLanguageCode, $alternatechartfontfile)) {
            $neededfontfile = $alternatechartfontfile[$sLanguageCode];
            if (is_file($rootdir."/fonts/".$neededfontfile)) {
                $chartfontfile = $neededfontfile;
            } else {
                Yii::app()->setFlashMessage(sprintf(gT('The fonts file %s was not found in <limesurvey root folder>/fonts directory. Please, see the txt file for your language in fonts directory to generate the charts.'), $neededfontfile), 'error');
                $bErrorGenerate = true; // Don't do a graph again.
                return false;
            }
        }
    }
    if (count($lbl) > 72) {
        $DataSet = array(1=>array(1=>1));
        if ($cache->IsInCache("graph".$iSurveyID.$sLanguageCode.$iQuestionID, $DataSet) && Yii::app()->getConfig('debug') < 2) {
            $cachefilename = basename($cache->GetFileFromCache("graph".$iSurveyID.$sLanguageCode.$iQuestionID, $DataSet));
        } else {
            $graph = new pChart(690, 200);
            $graph->loadColorPalette($adminThemePath . DIRECTORY_SEPARATOR . 'images/limesurvey.pal');
            $graph->setFontProperties($rootdir.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'fonts'.DIRECTORY_SEPARATOR.$chartfontfile, $chartfontsize);
            $graph->setFontProperties($rootdir.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'fonts'.DIRECTORY_SEPARATOR.$chartfontfile, $chartfontsize);
            $graph->drawTitle(0, 0, gT('Sorry, but this question has too many answer options to be shown properly in a graph.', 'unescaped'), 30, 30, 30, 690, 200);
            $cache->WriteToCache("graph".$iSurveyID.$sLanguageCode.$iQuestionID, $DataSet, $graph);
            $cachefilename = basename($cache->GetFileFromCache("graph".$iSurveyID.$sLanguageCode.$iQuestionID, $DataSet));
            unset($graph);
        }
        return  $cachefilename;
    }
    if (array_sum($gdata) == 0) {
        $DataSet = array(1=>array(1=>1));
        if ($cache->IsInCache("graph".$iSurveyID.$sLanguageCode.$iQuestionID, $DataSet) && Yii::app()->getConfig('debug') < 2) {
            $cachefilename = basename($cache->GetFileFromCache("graph".$iSurveyID.$sLanguageCode.$iQuestionID, $DataSet));
        } else {
            $graph = new pChart(690, 200);
            $graph->loadColorPalette($adminThemePath . DIRECTORY_SEPARATOR . 'images/limesurvey.pal');
            $graph->setFontProperties($rootdir.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'fonts'.DIRECTORY_SEPARATOR.$chartfontfile, $chartfontsize);
            $graph->setFontProperties($rootdir.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'fonts'.DIRECTORY_SEPARATOR.$chartfontfile, $chartfontsize);
            $graph->drawTitle(0, 0, gT('Sorry, but this question has no responses yet so a graph cannot be shown.', 'unescaped'), 30, 30, 30, 690, 200);
            $cache->WriteToCache("graph".$iSurveyID.$sLanguageCode.$iQuestionID, $DataSet, $graph);
            $cachefilename = basename($cache->GetFileFromCache("graph".$iSurveyID.$sLanguageCode.$iQuestionID, $DataSet));
            unset($graph);
        }
        return  $cachefilename;
    }

    if (array_sum($gdata) > 0) {
        //Make sure that the percentages add up to more than 0
        $i = 0;
        foreach ($gdata as $data) {
            if ($data != 0) {
                $i++;
            }
        }

        /* Totatllines is the number of entries to show in the key and we need to reduce the font
        and increase the size of the chart if there are lots of them (ie more than 15) */
        $totallines = $i;
        if ($totallines > 15) {
            $gheight = 320 + (6.7 * ($totallines - 15));
        } else {
            $gheight = 320;
        }

        if (!$type) {
            // Bar chart
            $DataSet = new pData;
            $counter = 0;
            $maxyvalue = 0;
            foreach ($grawdata as $datapoint) {
                $DataSet->AddPoint(array($datapoint), 'Serie'.$counter);
                $DataSet->AddSerie("Serie".$counter);

                $counter++;
                if ($datapoint > $maxyvalue) {
                    $maxyvalue = $datapoint;
                }
            }

            $lblout = array();
            if ($sLanguageCode == 'ar') {
                if (!class_exists('I18N_Arabic_Glyphs', false)) {
                    $Arabic = new I18N_Arabic('Glyphs');
                } else {
                    $Arabic = new I18N_Arabic_Glyphs();
                }

                foreach ($lbl as $kkey => $kval) {
                    if (preg_match("^[A-Za-z]^", $kkey)) {
                        //auto detect if english
                        $lblout[] = $kkey.' ('.$kval.')';
                    } else {
                        $lblout[] = $Arabic->utf8Glyphs($kkey.' )'.$kval.'(');
                    }
                }
            } elseif (getLanguageRTL($sLanguageCode)) {
                foreach ($lbl as $kkey => $kval) {
                    $lblout[] = UTF8Strrev($kkey.' )'.$kval.'(');
                }
            } else {
                foreach ($lbl as $kkey => $kval) {
                    $lblout[] = $kkey.' ('.$kval.')';
                }
            }

            $counter = 0;
            foreach ($lblout as $sLabelName) {
                $DataSet->SetSerieName(html_entity_decode($sLabelName, null, 'UTF-8'), "Serie".$counter);
                $counter++;
            }

            if ($cache->IsInCache("graph".$iSurveyID.$sLanguageCode.$iQuestionID, $DataSet->GetData()) && Yii::app()->getConfig('debug') < 2) {
                $cachefilename = basename($cache->GetFileFromCache("graph".$iSurveyID.$sLanguageCode.$iQuestionID, $DataSet->GetData()));
            } else {
                $graph = new pChart(1, 1);
                $graph->setFontProperties($rootdir.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'fonts'.DIRECTORY_SEPARATOR.$chartfontfile, $chartfontsize);
                $legendsize = $graph->getLegendBoxSize($DataSet->GetDataDescription());

                if ($legendsize[1] < 320) {
                    $gheight = 420;
                } else {
                    $gheight = $legendsize[1] + 100;
                }
                $graph = new pChart(690 + $legendsize[0], $gheight);
                $graph->drawFilledRectangle(0, 0, 690 + $legendsize[0], $gheight, 254, 254, 254, false);
                $graph->loadColorPalette($adminThemePath . DIRECTORY_SEPARATOR . 'images/limesurvey.pal');
                $graph->setFontProperties($rootdir.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'fonts'.DIRECTORY_SEPARATOR.$chartfontfile, $chartfontsize);
                $graph->setGraphArea(50, 30, 500, $gheight - 60);
                $graph->drawFilledRoundedRectangle(7, 7, 523 + $legendsize[0], $gheight - 7, 5, 254, 255, 254);
                $graph->drawRoundedRectangle(5, 5, 525 + $legendsize[0], $gheight - 5, 5, 230, 230, 230);
                $graph->drawGraphArea(254, 254, 254, true);
                $graph->drawScale($DataSet->GetData(), $DataSet->GetDataDescription(), SCALE_START0, 150, 150, 150, true, 90, 0, true, 5, false);
                $graph->drawGrid(4, true, 230, 230, 230, 50);
                // Draw the 0 line
                $graph->setFontProperties($rootdir.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'fonts'.DIRECTORY_SEPARATOR.$chartfontfile, $chartfontsize);
                $graph->drawTreshold(0, 143, 55, 72, true, true);

                // Draw the bar graph
                $graph->drawBarGraph($DataSet->GetData(), $DataSet->GetDataDescription(), false);
                //$Test->setLabel($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie4","1","Important point!");
                // Finish the graph
                $graph->setFontProperties($rootdir.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'fonts'.DIRECTORY_SEPARATOR.$chartfontfile, $chartfontsize);
                $graph->drawLegend(510, 30, $DataSet->GetDataDescription(), 250, 250, 250);

                $cache->WriteToCache("graph".$iSurveyID.$sLanguageCode.$iQuestionID, $DataSet->GetData(), $graph);
                $cachefilename = basename($cache->GetFileFromCache("graph".$iSurveyID.$sLanguageCode.$iQuestionID, $DataSet->GetData()));
                unset($graph);
            }
        }    //end if (bar chart)

        //Pie Chart
        else {
            // this block is to remove the items with value == 0
            // and an inelegant way to remove comments from List with Comments questions
            $i = 0;
            $j = 0;
            $labelTmp = array();
            while (isset ($gdata[$i])) {
                $aHelperArray = array_keys($lbl);
                if ($gdata[$i] == 0 || ($sQuestionType == "O" && substr($aHelperArray[$i], 0, strlen(gT("Comments"))) == gT("Comments"))) {
                    array_splice($gdata, $i, 1);
                } else {
                    $i++;
                    $labelTmp = $labelTmp + array_slice($lbl, $j, 1, true); // Preserve numeric keys for the labels!
                }
                $j++;
            }
            $lbl = $labelTmp;

            if ($sLanguageCode == 'ar') {
                if (!class_exists('I18N_Arabic_Glyphs', false)) {
                    $Arabic = new I18N_Arabic('Glyphs');
                } else {
                    $Arabic = new I18N_Arabic_Glyphs();
                }

                foreach ($lbl as $kkey => $kval) {
                    if (preg_match("^[A-Za-z]^", $kkey)) {
                        //auto detect if english
                        $lblout[] = $kkey.' ('.$kval.')';
                    } else {
                        $lblout[] = $Arabic->utf8Glyphs($kkey.' )'.$kval.'(');
                    }
                }
            } elseif (getLanguageRTL($sLanguageCode)) {
                foreach ($lbl as $kkey => $kval) {
                    $lblout[] = UTF8Strrev(html_entity_decode($kkey, null, 'UTF-8').' )'.$kval.'(');
                }
            } else {
                foreach ($lbl as $kkey => $kval) {
                    $lblout[] = html_entity_decode($kkey, null, 'UTF-8').' ('.$kval.')';
                }
            }


            //create 3D pie chart
            $DataSet = new pData;
            $DataSet->AddPoint($gdata, "Serie1");
            $DataSet->AddPoint($lblout, "Serie2");
            $DataSet->AddAllSeries();
            $DataSet->SetAbsciseLabelSerie("Serie2");

            if ($cache->IsInCache("graph".$iSurveyID.$sLanguageCode.$iQuestionID, $DataSet->GetData()) && Yii::app()->getConfig('debug') < 2) {
                $cachefilename = basename($cache->GetFileFromCache("graph".$iSurveyID.$sLanguageCode.$iQuestionID, $DataSet->GetData()));
            } else {

                $gheight = ceil($gheight);
                $graph = new pChart(690, $gheight);
                $graph->drawFilledRectangle(0, 0, 690, $gheight, 254, 254, 254, false);
                $graph->loadColorPalette($adminThemePath . DIRECTORY_SEPARATOR . 'images/limesurvey.pal');
                $graph->drawFilledRoundedRectangle(7, 7, 687, $gheight - 3, 5, 254, 255, 254);
                $graph->drawRoundedRectangle(5, 5, 689, $gheight - 1, 5, 230, 230, 230);

                // Draw the pie chart
                $graph->setFontProperties($rootdir.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'fonts'.DIRECTORY_SEPARATOR.$chartfontfile, $chartfontsize);
                $graph->drawPieGraph($DataSet->GetData(), $DataSet->GetDataDescription(), 225, round($gheight / 2), 170, PIE_PERCENTAGE, true, 50, 20, 5);
                $graph->setFontProperties($rootdir.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'fonts'.DIRECTORY_SEPARATOR.$chartfontfile, $chartfontsize);
                $graph->drawPieLegend(430, 12, $DataSet->GetData(), $DataSet->GetDataDescription(), 250, 250, 250);
                $cache->WriteToCache("graph".$iSurveyID.$sLanguageCode.$iQuestionID, $DataSet->GetData(), $graph);
                $cachefilename = basename($cache->GetFileFromCache("graph".$iSurveyID.$sLanguageCode.$iQuestionID, $DataSet->GetData()));
                unset($graph);
            }
        }    //end else -> pie charts
    }

    return $cachefilename;
}


/**
* Return data to populate a Google Map
* @param string$sField    Field name
* @param $qsid             Survey id
* @param string $sField
* @return array
*/
function getQuestionMapData($sField, $qsid)
{
    $aresult = SurveyDynamic::model($qsid)->findAll();

    $d = array();

    //loop through question data
    foreach ($aresult as $arow) {
        $alocation = explode(";", $arow->$sField);
        if (count($alocation) >= 2) {
            $d[] = "{$alocation[0]} {$alocation[1]}";
        }
    }
    return $d;
}

/** Builds the list of addon SQL select statements
*   that builds the query result set
*
*   @param array    $allfields   An array containing the names of the fields/answers we want to display in the statistics summary
*   @param integer  $surveyid
*   @param string   $language    The language to use
*
*   @return array $selects array of individual select statements that can be added/appended to
*                          the 'where' portion of a SQL statement to restrict the result set
*                          ie: array("`FIELDNAME`='Y'", "`FIELDNAME2`='Hello'");
*
*/
function buildSelects($allfields, $surveyid, $language)
{

    //Create required variables
    $selects = array();
    $aQuestionMap = array();
    $survey = Survey::model()->findByPk($surveyid);

    $fieldmap = createFieldMap($survey, "full", false, false, $language);
    foreach ($fieldmap as $field) {
        if (isset($field['qid']) && $field['qid'] != '') {
                    $aQuestionMap[] = $field['sid'].'X'.$field['gid'].'X'.$field['qid'];
        }
    }

    $postvars = array();
    // creates array of post variable names
    for (reset($_POST); $key = key($_POST); next($_POST)) { $postvars[] = $key; }

    /*
    * Iterate through postvars to create "nice" data for SQL later.
    *
    * Remember there might be some filters applied which have to be put into an SQL statement
    *
    * This foreach iterates through the name ($key) of each post value and builds a SELECT
    * statement out of it. It returns an array called $selects[] which will have a select query
    * for each filter chosen. ie: $select[0]="`74X71X428EXP` ='Y'";
    *
    * This array is used later to build the overall query used to limit the number of responses
    *
    */
        foreach ($postvars as $pv) {
        //Only do this if there is actually a value for the $pv

        if (
            in_array($pv, $allfields) || in_array(substr($pv, 1), $aQuestionMap) || in_array($pv, $aQuestionMap)
            || (
                (
                    $pv[0] == 'D' || $pv[0] == 'N' || $pv[0] == 'K'
                )
                && (in_array(substr($pv, 1, strlen($pv) - 2), $aQuestionMap) || in_array(substr($pv, 1, strlen($pv) - 3), $aQuestionMap) || in_array(substr($pv, 1, strlen($pv) - 5), $aQuestionMap))
            )
            ) {
                $firstletter = substr($pv, 0, 1);
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
                $pv != "summary" && substr($pv, 0, 2) != "id" && substr($pv, 0, 9) != "datestamp") {
                    //pull out just the fieldnames
                    //put together some SQL here
                    $thisquestion = Yii::app()->db->quoteColumnName($pv)." IN (";

                    foreach ($_POST[$pv] as $condition) {
                        $thisquestion .= "'$condition', ";
                    }

                    $thisquestion = substr($thisquestion, 0, -2)
                    . ")";

                    //we collect all the to be selected data in this array
                    $selects[] = $thisquestion;
                }

                //M - Multiple choice
                //P - Multiple choice with comments
                elseif ($firstletter == "M" || $firstletter == "P") {
                    $mselects = array();
                    //create a list out of the $pv array
                    list($lsid, $lgid, $lqid) = explode("X", $pv);

                    $aresult = Question::model()->findAll(array('order'=>'question_order', 'condition'=>'parent_qid=:parent_qid AND scale_id=0', 'params'=>array(":parent_qid"=>$lqid)));
                    foreach ($aresult as $arow) {
                        // only add condition if answer has been chosen
                        if (in_array($arow['title'], $_POST[$pv])) {
                            $mselects[] = Yii::app()->db->quoteColumnName(substr($pv, 1, strlen($pv)).$arow['title'])." = 'Y'";
                        }
                    }
                    /* If there are mutliple conditions generated from this multiple choice question, join them using the boolean "OR" */
                    if ($mselects) {
                        $thismulti = implode(" OR ", $mselects);
                        $selects[] = "($thismulti)";
                        unset($mselects);
                    }
                }

                //N - Numerical Input
                //K - Multiple Numerical Input
                elseif ($firstletter == "N" || $firstletter == "K") {
                    //value greater than
                    if (substr($pv, strlen($pv) - 1, 1) == "G" && $_POST[$pv] != "") {
                        $selects[] = Yii::app()->db->quoteColumnName(substr($pv, 1, -1))." > ".sanitize_int($_POST[$pv]);
                    }

                    //value less than
                    if (substr($pv, strlen($pv) - 1, 1) == "L" && $_POST[$pv] != "") {
                        $selects[] = Yii::app()->db->quoteColumnName(substr($pv, 1, -1))." < ".sanitize_int($_POST[$pv]);
                    }
                }

                //| - File Upload Question Type
                else if ($firstletter == "|") {
                    // no. of files greater than
                    if (substr($pv, strlen($pv) - 1, 1) == "G" && $_POST[$pv] != "") {
                                                $selects[] = Yii::app()->db->quoteColumnName(substr($pv, 1, -1)."_filecount")." > ".sanitize_int($_POST[$pv]);
                    }

                    // no. of files less than
                    if (substr($pv, strlen($pv) - 1, 1) == "L" && $_POST[$pv] != "") {
                                                $selects[] = Yii::app()->db->quoteColumnName(substr($pv, 1, -1)."_filecount")." < ".sanitize_int($_POST[$pv]);
                    }
                }

                //"id" is a built in field, the unique database id key of each response row
                elseif (substr($pv, 0, 2) == "id") {
                    if (substr($pv, strlen($pv) - 1, 1) == "G" && $_POST[$pv] != "") {
                        $selects[] = Yii::app()->db->quoteColumnName(substr($pv, 0, -1))." > ".sanitize_int($_POST[$pv]);
                    }
                    if (substr($pv, strlen($pv) - 1, 1) == "L" && $_POST[$pv] != "") {
                        $selects[] = Yii::app()->db->quoteColumnName(substr($pv, 0, -1))." < ".sanitize_int($_POST[$pv]);
                    }
                }

                //T - Long Free Text
                //Q - Multiple Short Text
                elseif (($firstletter == "T" || $firstletter == "Q") && $_POST[$pv] != "") {
                    $selectSubs = array();
                    //We intepret and * and % as wildcard matches, and use ' OR ' and , as the separators
                    $pvParts = explode(",", str_replace('*', '%', str_replace(' OR ', ',', $_POST[$pv])));
                    if (is_array($pvParts) AND count($pvParts)) {
                        foreach ($pvParts AS $pvPart) {
                            $selectSubs[] = Yii::app()->db->quoteColumnName(substr($pv, 1, strlen($pv)))." LIKE '".trim($pvPart)."'";
                        }
                        if (count($selectSubs)) {
                            $selects[] = ' ('.implode(' OR ', $selectSubs).') ';
                        }
                    }
                }

                //D - Date
                elseif ($firstletter == "D" && $_POST[$pv] != "") {
                    //Date equals
                    if (substr($pv, -2) == "eq") {
                        $selects[] = Yii::app()->db->quoteColumnName(substr($pv, 1, strlen($pv) - 3))." = ".App()->db->quoteValue($_POST[$pv]);
                    } else {
                        //date less than
                        if (substr($pv, -4) == "less") {
                            $selects[] = Yii::app()->db->quoteColumnName(substr($pv, 1, strlen($pv) - 5))." >= ".App()->db->quoteValue($_POST[$pv]);
                        }

                        //date greater than
                        if (substr($pv, -4) == "more") {
                            $selects[] = Yii::app()->db->quoteColumnName(substr($pv, 1, strlen($pv) - 5))." <= ".App()->db->quoteValue($_POST[$pv]);
                        }
                    }
                }

                //check for datestamp of given answer
                elseif (substr($pv, 0, 9) == "datestamp") {
                    //timestamp equals
                    $formatdata = getDateFormatData(Yii::app()->session['dateformat']);
                    if (substr($pv, -1, 1) == "E" && !empty($_POST[$pv])) {
                        $datetimeobj = new Date_Time_Converter($_POST[$pv], $formatdata['phpdate'].' H:i');
                        $sDateValue = $datetimeobj->convert("Y-m-d");

                        $selects[] = Yii::app()->db->quoteColumnName('datestamp')." >= ".App()->db->quoteValue($sDateValue." 00:00:00")." and ".Yii::app()->db->quoteColumnName('datestamp')." <= ".App()->db->quoteValue($sDateValue." 23:59:59");
                    } else {
                        //timestamp less than
                        if (substr($pv, -1, 1) == "L" && !empty($_POST[$pv])) {
                            $datetimeobj = new Date_Time_Converter($_POST[$pv], $formatdata['phpdate'].' H:i');
                            $sDateValue = $datetimeobj->convert("Y-m-d H:i:s");
                            $selects[] = Yii::app()->db->quoteColumnName('datestamp')." < ".App()->db->quoteValue($sDateValue);
                        }

                        //timestamp greater than
                        if (substr($pv, -1, 1) == "G" && !empty($_POST[$pv])) {
                            $datetimeobj = new Date_Time_Converter($_POST[$pv], $formatdata['phpdate'].' H:i');
                            $sDateValue = $datetimeobj->convert("Y-m-d H:i:s");
                            $selects[] = Yii::app()->db->quoteColumnName('datestamp')." > ".App()->db->quoteValue($sDateValue);
                        }
                    }
                }
            }
        }

    return $selects;
}

/**
* Simple function to square a value
*
* @param mixed $number Value to square
*/
function square($number)
{
    if ($number == 0) {
        $squarenumber = 0;
    } else {
        $squarenumber = $number * $number;
    }
    return $squarenumber;
}

class statistics_helper
{
    /**
     * @var pdf
     */
    protected $pdf;

    /**
     * The Excel worksheet we are working on
     *
     * @var Spreadsheet_Excel_Writer_Worksheet
     */
    protected $sheet;

    protected $xlsPercents;

    protected $formatBold;
    /**
     * The current Excel workbook we are working on
     *
     * @var Writer
     */
    protected $workbook;

    /**
     * Keeps track of the current row in Excel sheet
     *
     * @var int
     */
    protected $xlsRow = 0;

    /**
     * Builds an array containing information about this particular question/answer combination
     *
     * @param string $rt The code passed from the statistics form listing the field/answer (SGQA) combination to be displayed
     * @param mixed $language The language to present output in
     * @param mixed $surveyid The survey id
     * @param string $outputType
     * @param boolean $browse
     *
     * @output array $output An array containing "alist"=>A list of answers to the question in the form of an array ($alist array
     *                       contains an array for every field to be displayed - with the Actual Question Code/Title, The text (flattened)
     *                       of the question, and the fieldname where the data is stored.
     *                       "qtitle"=>The title of the question,
     *                       "qquestion"=>The description of the question,
     *                       "qtype"=>The question type code
     * @return array
     */
    protected function buildOutputList($rt, $language, $surveyid, $outputType, $sql, $oLanguage, $browse = true)
    {
        //Set up required variables
        $survey = Survey::model()->findByPk($surveyid);
        $alist = array();
        $qtitle = "";
        $qquestion = "";
        $qtype = "";
        $firstletter = substr($rt, 0, 1);
        $fieldmap = createFieldMap($survey, "full", false, false, $language);
        $sDatabaseType = Yii::app()->db->getDriverName();
        $statisticsoutput = "";
        $qqid = "";

        /* Some variable depend on output type, actually : only line feed */
        switch ($outputType) {
            case 'xls':
            case 'pdf':
                $linefeed = "\n";
                break;
            case 'html':
                $linefeed = "<br />\n";
                break;
            default:
                break;
        }

        //M - Multiple choice, therefore multiple fields - one for each answer
        if ($firstletter == "M" || $firstletter == "P") {
            //get SGQ data
            list($qsid, $qgid, $qqid) = explode("X", substr($rt, 1, strlen($rt)), 3);

            //select details for this question
            $nresult = Question::model()->find('language=:language AND parent_qid=0 AND qid=:qid', array(':language'=>$language, ':qid'=>$qqid));
            $qtitle = $nresult->title;
            $qtype = $nresult->type;
            $qquestion = flattenText($nresult->question);
            $qother = $nresult->other;

            //1. Get list of answers
            $result = Question::model()->findAll(array('order'=>'question_order',
                'condition'=>'language=:language AND parent_qid=:qid AND scale_id=0',
                'params'=>array(':language'=>$language, ':qid'=>$qqid)
            ));
            foreach ($result as $row) {
                $mfield = substr($rt, 1, strlen($rt)).$row['title'];
                $alist[] = array($row['title'], flattenText($row['question']), $mfield);
            }

            //Add the "other" answer if it exists
            if ($qother == "Y") {
                $mfield = substr($rt, 1, strlen($rt))."other";
                $alist[] = array(gT("Other"), gT("Other"), $mfield);
            }
        }

        //S - Short Free Text and T - Long Free Text
        elseif ($firstletter == "T" || $firstletter == "S") {
            //Short and long text
            //search for key
            $fld = substr($rt, 1, strlen($rt));
            $fielddata = $fieldmap[$fld];

            //get question data
            $nresult = Question::model()->find('language=:language AND parent_qid=0 AND qid=:qid', array(':language'=>$language, ':qid'=>$fielddata['qid']));
            $qtitle = $nresult->title;
            $qtype = $nresult->type;
            $qquestion = flattenText($nresult->question);

            $mfield = substr($rt, 1, strlen($rt));

            //Text questions either have an answer, or they don't. There's no other way of quantising the results.
            // So, instead of building an array of predefined answers like we do with lists & other types,
            // we instead create two "types" of possible answer - either there is a response.. or there isn't.
            // This question type then can provide a % of the question answered in the summary.
            $alist[] = array("Answer", gT("Answer"), $mfield);
            $alist[] = array("NoAnswer", gT("No answer"), $mfield);
        }

        //Q - Multiple short text
        elseif ($firstletter == "Q") {
            //Build an array of legitimate qid's for testing later
            $aQuestionInfo = $fieldmap[substr($rt, 1)];
            $qqid = $aQuestionInfo['qid'];
            $qaid = $aQuestionInfo['aid'];

            //get question data
            $nresult = Question::model()->find('language=:language AND parent_qid=0 AND qid=:qid', array(':language'=>$language, ':qid'=>$qqid));
            $qtitle = $nresult->title;
            $qtype = $nresult->type;
            $qquestion = flattenText($nresult->question);

            //get answers / subquestion text
            $nresult = Question::model()->find(array('order'=>'question_order',
                'condition'=>'language=:language AND parent_qid=:parent_qid AND title=:title',
                'params'=>array(':language'=>$language, ':parent_qid'=>$qqid, ':title'=>$qaid)
            ));
            $atext = flattenText($nresult->question);
            //add this to the question title
            $qtitle .= " [$atext]";

            //even more substrings...
            $mfield = substr($rt, 1, strlen($rt));

            //Text questions either have an answer, or they don't. There's no other way of quantising the results.
            // So, instead of building an array of predefined answers like we do with lists & other types,
            // we instead create two "types" of possible answer - either there is a response.. or there isn't.
            // This question type then can provide a % of the question answered in the summary.
            $alist[] = array("Answer", gT("Answer"), $mfield);
            $alist[] = array("NoAnswer", gT("No answer"), $mfield);
        }

        //RANKING OPTION
        elseif ($firstletter == "R") {
            //getting the needed IDs somehow
            $lengthofnumeral = substr($rt, strpos($rt, "-") + 1, 1);
            list($qsid, $qgid, $qqid) = explode("X", substr($rt, 1, strpos($rt, "-") - ($lengthofnumeral + 1)), 3);

            //get question data
            $nquery = "SELECT title, type, question FROM {{questions}} WHERE parent_qid=0 AND qid='$qqid' AND language='{$language}'";
            $nresult = Yii::app()->db->createCommand($nquery)->query();

            //loop through question data
            foreach ($nresult->readAll() as $nrow) {
                $nrow = array_values($nrow);
                $qtitle = flattenText($nrow[0])." [".substr($rt, strpos($rt, "-") - ($lengthofnumeral), $lengthofnumeral)."]";
                $qtype = $nrow[1];
                $qquestion = flattenText($nrow[2])."[".gT("Ranking")." ".substr($rt, strpos($rt, "-") - ($lengthofnumeral), $lengthofnumeral)."]";
            }

            //get answers
            $query = "SELECT code, answer FROM {{answers}} WHERE qid='$qqid' AND scale_id=0 AND language='{$language}' ORDER BY sortorder, answer";
            $result = Yii::app()->db->createCommand($query)->query();

            //loop through answers
            foreach ($result->readAll() as $row) {
                $row = array_values($row);
                //create an array containing answer code, answer and fieldname(??)
                $mfield = substr($rt, 1, strpos($rt, "-") - 1);
                $alist[] = array("$row[0]", flattenText($row[1]), $mfield);
            }
        } else if ($firstletter == "|") {
            // File Upload

            //get SGQ data
            list($qsid, $qgid, $qqid) = explode("X", substr($rt, 1, strlen($rt)), 3);

            //select details for this question
            /**
            FIXME $iQuestionIDlength not defined!!
             */
            $nresult = Question::model()->find('language=:language AND parent_qid=0 AND qid=:qid', array(':language'=>$language, ':qid'=>substr($qqid, 0, $iQuestionIDlength)));
            $qtitle = $nresult->title;
            $qtype = $nresult->type;
            $qquestion = flattenText($nresult->question);
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
            $fieldname = substr($rt, 1, strlen($rt));
            $query = "SELECT SUM(".Yii::app()->db->quoteColumnName($fieldname.'_filecount').") as sum, AVG(".Yii::app()->db->quoteColumnName($fieldname.'_filecount').") as avg FROM {{survey_$surveyid}}";
            $result = Yii::app()->db->createCommand($query)->query();

            $showem = array();

            foreach ($result->readAll() as $row) {
                $showem[] = array(gT("Total number of files"), $row['sum']);
                $showem[] = array(gT("Average no. of files per respondent"), $row['avg']);
            }


            $query = "SELECT ".$fieldname." as json FROM {{survey_$surveyid}}";
            $result = Yii::app()->db->createCommand($query)->query();

            $responsecount = 0;
            $filecount = 0;
            $size = 0;

            foreach ($result->readAll() as $row) {
                $json = $row['json'];
                $phparray = json_decode($json);

                foreach ($phparray as $metadata) {
                    $size += (int) $metadata->size;
                    $filecount++;
                }
                $responsecount++;
            }
            $showem[] = array(gT("Total size of files"), $size." KB");
            $showem[] = array(gT("Average file size"), $size / $filecount." KB");
            $showem[] = array(gT("Average size per respondent"), $size / $responsecount." KB");

            /*              $query="SELECT title, question FROM {{questions}} WHERE parent_qid='$qqid' AND language='{$language}' ORDER BY question_order";
            $result=db_execute_num($query) or safeDie("Couldn't get list of subquestions for multitype<br />$query<br />");

            //loop through multiple answers
            while ($row=$result->FetchRow())
            {
            $mfield=substr($rt, 1, strlen($rt))."$row[0]";

            //create an array containing answer code, answer and fieldname(??)
            $alist[]=array("$row[0]", flattenText($row[1]), $mfield);
            }

            */
            //outputting
            switch ($outputType) {
                case 'xls':
                    $xlsTitle = sprintf(gT("Summary for %s"), html_entity_decode($qtitle, ENT_QUOTES, 'UTF-8'));
                    $xlsDesc = html_entity_decode($qquestion, ENT_QUOTES, 'UTF-8');
                    $this->xlsRow++;
                    $this->xlsRow++;
                    $this->xlsRow++;
                    $this->sheet->write($this->xlsRow, 0, $xlsTitle);
                    $this->xlsRow++;
                    $this->sheet->write($this->xlsRow, 0, $xlsDesc);
                    $this->xlsRow++;
                    $this->sheet->write($this->xlsRow, 0, gT("Calculation"));
                    $this->sheet->write($this->xlsRow, 1, gT("Result"));
                    break;

                case 'pdf':
                    $headPDF = array();
                    $headPDF[] = array(gT("Calculation"), gT("Result"));

                    break;

                case 'html':
                    $statisticsoutput .= "\n<table class='statisticstable table table-bordered >\n"
                    ."\t<thead><tr class='success'><th style='text-align: center; '><strong>".sprintf(gT("Summary for %s"), $qtitle).":</strong>"
                    ."</th></tr>\n"
                    ."\t<tr><th colspan='2' align='right'><strong>$qquestion</strong></th></tr>\n"
                    ."\t<tr>\n\t\t<th width='50%' align='right' ><strong>"
                    .gT("Calculation")."</strong></th>\n"
                    ."\t\t<th width='50%' align='right' ><strong>"
                    .gT("Result")."</strong></th>\n"
                    ."\t</tr></thead>\n";

                    foreach ($showem as $res) {
                        $statisticsoutput .= "<tr><td>".$res[0]."</td><td>".$res[1]."</td></tr>";
                    }
                    break;

                default:
                    break;
            }
        }

        //N = numerical input
        //K = multiple numerical input
        elseif ($firstletter == "N" || $firstletter == "K") {
            //NUMERICAL TYPE
            //Zero handling
            if (!isset($excludezeros)) {
                //If this hasn't been set, set it to on as default:
                $excludezeros = 1;
            }
            //check last character, greater/less/equals don't need special treatment
            if (substr($rt, -1) == "G" || substr($rt, -1) == "L" || substr($rt, -1) == "=") {
                //DO NOTHING
            } else {
                $showem = array();
                $fld = substr($rt, 1, strlen($rt));
                $fielddata = $fieldmap[$fld];

                $qtitle = flattenText($fielddata['title']);
                $qtype = $fielddata['type'];
                $qquestion = LimeExpressionManager::ProcessString($fielddata['question'], $qqid, NULL, 1, 1, false, true, true);

                //Get answer texts for multiple numerical
                if (substr($rt, 0, 1) == "K") {
                    //put single items in brackets at output
                    $qtitle .= " [".$fielddata['subquestion']."]";
                }

                //outputting
                switch ($outputType) {
                    case 'xls':
                        $xlsTitle = sprintf(gT("Summary for %s"), html_entity_decode($qtitle, ENT_QUOTES, 'UTF-8'));
                        $xlsDesc = html_entity_decode($qquestion, ENT_QUOTES, 'UTF-8');
                        $this->xlsRow++;
                        $this->xlsRow++;

                        $this->xlsRow++;
                        $this->sheet->write($this->xlsRow, 0, $xlsTitle);
                        $this->xlsRow++;
                        $this->sheet->write($this->xlsRow, 0, $xlsDesc);
                        $this->xlsRow++;
                        $this->sheet->write($this->xlsRow, 0, gT("Calculation"));
                        $this->sheet->write($this->xlsRow, 1, gT("Result"));
                        break;

                    case 'pdf':

                        $headPDF = array();
                        $tablePDF = array();
                        $footPDF = array();

                        $pdfTitle = sprintf(gT("Summary for %s"), html_entity_decode($qtitle, ENT_QUOTES, 'UTF-8'));
                        $titleDesc = html_entity_decode($qquestion, ENT_QUOTES, 'UTF-8');

                        $headPDF[] = array(gT("Calculation"), gT("Result"));

                        break;
                    case 'html':

                        $statisticsoutput .= "\n<table class='statisticstable table table-bordered' >\n"
                        ."\t<thead><tr  class='success'><th colspan='2' align='right'  class='success'><strong>".sprintf(gT("Summary for %s"), $qtitle).":</strong>"
                        ."</th></tr>\n"
                        ."\t<tr><th colspan='2' align='right'><strong>$qquestion</strong></th></tr>\n"
                        ."\t<tr>\n\t\t<th width='50%' align='right' ><strong>"
                        .gT("Calculation")."</strong></th>\n"
                        ."\t\t<th width='50%' align='right' ><strong>"
                        .gT("Result")."</strong></th>\n"
                        ."\t</tr></thead>\n";

                        break;
                    default:


                        break;
                }

                //this field is queried using mathematical functions
                $fieldname = substr($rt, 1, strlen($rt));

                //special treatment for MS SQL databases
                if ($sDatabaseType == 'mssql' || $sDatabaseType == 'sqlsrv' || $sDatabaseType == 'dblib') {
                    //standard deviation
                    $query = "SELECT STDEVP(".Yii::app()->db->quoteColumnName($fieldname)."*1) as stdev";
                }

                //other databases (MySQL, Postgres)
                else {
                    //standard deviation
                    $query = "SELECT STDDEV(CAST(".Yii::app()->db->quoteColumnName($fieldname)." AS DECIMAL(26,6))) as stdev";
                }

                //sum
                $query .= ", SUM(CAST(".Yii::app()->db->quoteColumnName($fieldname)." AS DECIMAL(26,6))) as sum";

                //average
                $query .= ", AVG(CAST(".Yii::app()->db->quoteColumnName($fieldname)." AS DECIMAL(26,6))) as average";

                //min
                $query .= ", MIN(CAST(".Yii::app()->db->quoteColumnName($fieldname)." AS DECIMAL(26,6))) as minimum";

                //max
                $query .= ", MAX(CAST(".Yii::app()->db->quoteColumnName($fieldname)." AS DECIMAL(26,6))) as maximum";
                //Only select responses where there is an actual number response, ignore nulls and empties (if these are included, they are treated as zeroes, and distort the deviation/mean calculations)

                //special treatment for MS SQL databases
                if ($sDatabaseType == 'mssql' || $sDatabaseType == 'sqlsrv' || $sDatabaseType == 'dblib') {
                    //no NULL/empty values please
                    $query .= " FROM {{survey_$surveyid}} WHERE ".Yii::app()->db->quoteColumnName($fieldname)." IS NOT NULL";
                    if (!$excludezeros) {
                        //NO ZERO VALUES
                        $query .= " AND (".Yii::app()->db->quoteColumnName($fieldname)." <> 0)";
                    }
                }

                //other databases (MySQL, Postgres)
                else {
                    //no NULL/empty values please
                    $query .= " FROM {{survey_$surveyid}} WHERE ".Yii::app()->db->quoteColumnName($fieldname)." IS NOT NULL";
                    if (!$excludezeros) {
                        //NO ZERO VALUES
                        $query .= " AND (".Yii::app()->db->quoteColumnName($fieldname)." != 0)";
                    }
                }

                //filter incomplete answers if set
                if (incompleteAnsFilterState() == "incomplete") {$query .= " AND submitdate is null"; } elseif (incompleteAnsFilterState() == "complete") {$query .= " AND submitdate is not null"; }

                //$sql was set somewhere before
                if (!empty($sql)) {$query .= " AND $sql"; }

                //execute query
                $result = Yii::app()->db->createCommand($query)->queryAll();

                //get calculated data
                foreach ($result as $row) {
                    //put translation of mean and calculated data into $showem array
                    $showem[] = array(gT("Sum"), $row['sum']);
                    $showem[] = array(gT("Standard deviation"), round($row['stdev'], 2));
                    $showem[] = array(gT("Average"), round($row['average'], 2));
                    $showem[] = array(gT("Minimum"), $row['minimum']);

                    //Display the maximum and minimum figures after the quartiles for neatness
                    $maximum = $row['maximum'];
                }


                //CALCULATE QUARTILES
                $medcount = $this->getQuartile(0, $fieldname, $surveyid, $sql, $excludezeros); // Get the recordcount
                $quartiles = array();
                $quartiles[1] = $this->getQuartile(1, $fieldname, $surveyid, $sql, $excludezeros);
                $quartiles[2] = $this->getQuartile(2, $fieldname, $surveyid, $sql, $excludezeros);
                $quartiles[3] = $this->getQuartile(3, $fieldname, $surveyid, $sql, $excludezeros);

                //we just put the total number of records at the beginning of this array
                array_unshift($showem, array(gT("Count"), $medcount));

                /* IMPORTANT IMPORTANT IMPORTANT IMPORTANT IMPORTANT IMPORTANT */
                /* IF YOU DON'T UNDERSTAND WHAT QUARTILES ARE DO NOT MODIFY THIS CODE */
                /* Quartiles and Median values are NOT related to average, and the sum is irrelevent */

                if (isset($quartiles[1])) {
                    $showem[] = array(gT("1st quartile (Q1)"), $quartiles[1]);
                }
                if (isset($quartiles[2])) {
                    $showem[] = array(gT("2nd quartile (Median)"), $quartiles[2]);
                }
                if (isset($quartiles[3])) {
                    $showem[] = array(gT("3rd quartile (Q3)"), $quartiles[3]);
                }
                $showem[] = array(gT("Maximum"), $maximum);

                //output results
                foreach ($showem as $shw) {
                    switch ($outputType) {
                        case 'xls':

                            $this->xlsRow++;
                            $this->sheet->write($this->xlsRow, 0, html_entity_decode($shw[0], ENT_QUOTES, 'UTF-8'));
                            $this->sheet->write($this->xlsRow, 1, html_entity_decode($shw[1], ENT_QUOTES, 'UTF-8'));

                            break;
                        case 'pdf':

                            $tablePDF[] = array(html_entity_decode($shw[0], ENT_QUOTES, 'UTF-8'), html_entity_decode($shw[1], ENT_QUOTES, 'UTF-8'));

                            break;
                        case 'html':

                            $statisticsoutput .= "\t<tr>\n"
                            ."\t\t<td align='right' >$shw[0]</td>\n"
                            ."\t\t<td align='right' >$shw[1]</td>\n"
                            ."\t</tr>\n";

                            break;
                        default:


                        break;
                    }
                }
                switch ($outputType) {
                    case 'xls':

                        $this->xlsRow++;
                        $this->sheet->write($this->xlsRow, 0, gT("Null values are ignored in calculations"));
                        $this->xlsRow++;
                        $this->sheet->write($this->xlsRow, 0, sprintf(gT("Q1 and Q3 calculated using %s"), gT("minitab method")));

                        $footXLS[] = array(gT("Null values are ignored in calculations"));
                        $footXLS[] = array(sprintf(gT("Q1 and Q3 calculated using %s"), gT("minitab method")));

                        break;
                    case 'pdf':

                        $footPDF[] = array(gT("Null values are ignored in calculations"));
                        $footPDF[] = array(sprintf(gT("Q1 and Q3 calculated using %s"), "<a href='http://mathforum.org/library/drmath/view/60969.html' target='_blank'>".gT("minitab method")."</a>"));
                        $this->pdf->AddPage('P', 'A4');
                        $this->pdf->Bookmark($this->pdf->delete_html($qquestion), 1, 0);
                        $this->pdf->titleintopdf($pdfTitle, $titleDesc);

                        $this->pdf->headTable($headPDF, $tablePDF);

                        $this->pdf->tablehead($footPDF);

                        break;
                    case 'html':

                        //footer of question type "N"
                        $statisticsoutput .= "\t<tr class='info'>\n"
                        ."\t\t<td colspan='4' align='right'>\n"
                        ."\t\t\t<font size='1'>".gT("Null values are ignored in calculations")."<br />\n"
                        ."\t\t\t".sprintf(gT("Q1 and Q3 calculated using %s"), "<a href='http://mathforum.org/library/drmath/view/60969.html' target='_blank'>".gT("minitab method")."</a>")
                        ."</font>\n"
                        ."\t\t</td>\n"
                        ."\t</tr>\n";
                        if ($browse) {
                            $statisticsoutput .= "\t<tr>\n"
                            ."\t\t<td align='right'  colspan='4'>
                            <input type='button' class='statisticsbrowsebutton numericalbrowse btn btn-default btn-large' value='"
                            .gT("Browse")."' id='$fieldname' /></td>\n</tr>";
                            $statisticsoutput .= "<tr><td class='statisticsbrowsecolumn' colspan='3' style='display: none'>
                            <div class='statisticsbrowsecolumn' id='columnlist_{$fieldname}'></div></td></tr>";
                        }
                        $statisticsoutput .= "</table>\n";

                        break;
                    default:


                        break;
                }

                //clean up
                unset($showem);


                //not enough (<1) results for calculation
                if ($medcount < 1) {
                    switch ($outputType) {
                        case 'xls':
                            $this->xlsRow++;
                            $this->sheet->write($this->xlsRow, 0, gT("Not enough values for calculation"));
                            break;

                        case 'pdf':
                            $tablePDF = array();
                            $tablePDF[] = array(gT("Not enough values for calculation"));
                            $this->pdf->AddPage('P', 'A4');
                            $this->pdf->Bookmark($this->pdf->delete_html($qquestion), 1, 0);
                            $this->pdf->titleintopdf($pdfTitle, $titleDesc);
                            $this->pdf->equalTable($tablePDF);
                            break;

                        case 'html':

                            //output
                            $statisticsoutput .= "<p class='printable'>".gT("Not enough values for calculation")."</p>\n";

                            break;
                        default:


                            break;
                    }

                    unset($showem);

                }

            }    //end else -> check last character, greater/less/equals don't need special treatment

        }    //end else-if -> multiple numerical types

        //is there some "id", "datestamp" or "D" within the type?
        elseif (substr($rt, 0, 2) == "id" || substr($rt, 0, 9) == "datestamp" || ($firstletter == "D")) {
            /*
            * DON'T show anything for date questions
            * because there aren't any statistics implemented yet!
            *
            * See bug report #2539 and
            * feature request #2620
            */
        }

        // NICE SIMPLE SINGLE OPTION ANSWERS
        /*
        TO DEBUG QUESTION TYPES FIRSTLETTER, UNCOMMENT THOSE LINES
        elseif(!isset($fieldmap[$rt]))
        {
            echo "problem, wrong question type for $rt ; $firstletter"; die();
        }*/
        else {
            //search for key
            $fielddata = $fieldmap[$rt];

            //get SGQA IDs
            $qqid = $fielddata['qid'];
            $qanswer = $fielddata['aid'];
            $qtype = $fielddata['type'];
            //question string
            $qastring = $fielddata['question'];
            //question ID
            $rqid = $qqid;

            //get question data
            $nquery = "SELECT title, type, question, qid, parent_qid, other FROM {{questions}} WHERE qid='{$rqid}' AND parent_qid=0 and language='{$language}'";
            $nresult = Yii::app()->db->createCommand($nquery)->query();

            //loop though question data
            foreach ($nresult->readAll() as $nrow) {
                $nrow = array_values($nrow);
                $qtitle = flattenText($nrow[0]);
                $qtype = $nrow[1];
                $qquestion = flattenText($nrow[2]);
                $qiqid = $nrow[3];
                $qother = $nrow[5];
            }

            //check question types
            switch ($qtype) {
                //Array of 5 point choices (several items to rank!)
                case "A":

                    //get data
                    $qquery = "SELECT title, question FROM {{questions}} WHERE parent_qid='$qiqid' AND title='$qanswer' AND language='{$language}' ORDER BY question_order";
                    $qresult = Yii::app()->db->createCommand($qquery)->query();

                    //loop through results
                    foreach ($qresult->readAll() as $qrow) {
                        $qrow = array_values($qrow);
                        //5-point array
                        for ($i = 1; $i <= 5; $i++) {
                            //add data
                            $alist[] = array("$i", "$i");
                        }
                        //add counter
                        $atext = flattenText($qrow[1]);
                    }

                    //list IDs and answer codes in brackets
                    $qquestion .= $linefeed;
                    $qtitle .= "($qanswer)"."[".$atext."]";
                    break;



                    //Array of 10 point choices
                    //same as above just with 10 items
                case "B":
                    $qquery = "SELECT title, question FROM {{questions}} WHERE parent_qid='$qiqid' AND title='$qanswer' AND language='{$language}' ORDER BY question_order";
                    $qresult = Yii::app()->db->createCommand($qquery)->query();
                    foreach ($qresult->readAll() as $qrow) {
                        $qrow = array_values($qrow);
                        for ($i = 1; $i <= 10; $i++) {
                            $alist[] = array("$i", "$i");
                        }
                        $atext = flattenText($qrow[1]);
                    }

                    $qquestion .= $linefeed;
                    $qtitle .= "($qanswer)"."[".$atext."]";
                    break;



                    //Array of Yes/No/gT("Uncertain")
                case "C":
                    $qquery = "SELECT title, question FROM {{questions}} WHERE parent_qid='$qiqid' AND title='$qanswer' AND language='{$language}' ORDER BY question_order";
                    $qresult = Yii::app()->db->createCommand($qquery)->query();

                    //loop thorugh results
                    foreach ($qresult->readAll() as $qrow) {
                        $qrow = array_values($qrow);
                        //add results
                        $alist[] = array("Y", gT("Yes"));
                        $alist[] = array("N", gT("No"));
                        $alist[] = array("U", gT("Uncertain"));
                        $atext = flattenText($qrow[1]);
                    }
                    //output
                    $qquestion .= $linefeed;
                    $qtitle .= "($qanswer)"."[".$atext."]";
                    break;



                    //Array of Yes/No/gT("Uncertain")
                    //same as above
                case "E":
                    $qquery = "SELECT title, question FROM {{questions}} WHERE parent_qid='$qiqid' AND title='$qanswer' AND language='{$language}' ORDER BY question_order";
                    $qresult = Yii::app()->db->createCommand($qquery)->query();
                    foreach ($qresult->readAll() as $qrow) {
                        $qrow = array_values($qrow);
                        $alist[] = array("I", gT("Increase"));
                        $alist[] = array("S", gT("Same"));
                        $alist[] = array("D", gT("Decrease"));
                        $atext = flattenText($qrow[1]);
                    }
                    $qquestion .= $linefeed;
                    $qtitle .= "($qanswer)"."[".$atext."]";
                    break;


                case ";": //Array (Multi Flexi) (Text)
                    list($qacode, $licode) = explode("_", $qanswer);

                    $qquery = "SELECT title, question FROM {{questions}} WHERE parent_qid='$qiqid' AND title='$qacode' AND language='{$language}' ORDER BY question_order";
                    $qresult = Yii::app()->db->createCommand($qquery)->query();

                    foreach ($qresult->readAll() as $qrow) {
                        $qrow = array_values($qrow);
                        $fquery = "SELECT * FROM {{answers}} WHERE qid='{$qiqid}' AND scale_id=0 AND code = '{$licode}' AND language='{$language}'ORDER BY sortorder, code";
                        $fresult = Yii::app()->db->createCommand($fquery)->query();
                        foreach ($fresult->readAll() as $frow) {
                            $alist[] = array($frow['code'], $frow['answer']);
                            $ltext = $frow['answer'];
                        }
                        $atext = flattenText($qrow[1]);
                    }

                    $qquestion .= $linefeed;
                    $qtitle .= "($qanswer)"."[".$atext."] [".$ltext."]";
                    break;

                case ":": //Array (Multiple Flexi) (Numbers)
                    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($qiqid);
                    if (trim($aQuestionAttributes['multiflexible_max']) != '') {
                        $maxvalue = $aQuestionAttributes['multiflexible_max'];
                    } else {
                        $maxvalue = 10;
                    }

                    if (trim($aQuestionAttributes['multiflexible_min']) != '') {
                        $minvalue = $aQuestionAttributes['multiflexible_min'];
                    } else {
                        $minvalue = 1;
                    }

                    if (trim($aQuestionAttributes['multiflexible_step']) != '') {
                        $stepvalue = $aQuestionAttributes['multiflexible_step'];
                    } else {
                        $stepvalue = 1;
                    }

                    if ($aQuestionAttributes['multiflexible_checkbox'] != 0) {
                        $minvalue = 0;
                        $maxvalue = 1;
                        $stepvalue = 1;
                    }

                    for ($i = $minvalue; $i <= $maxvalue; $i += $stepvalue) {
                        $alist[] = array($i, $i);
                    }

                    $qquestion .= $linefeed."[".$fielddata['subquestion1']."] [".$fielddata['subquestion2']."]";
                    list($myans, $mylabel) = explode("_", $qanswer);
                    $qtitle .= "[$myans][$mylabel]";
                    break;

                case "F": //Array of Flexible
                case "H": //Array of Flexible by Column
                    $qquery = "SELECT title, question FROM {{questions}} WHERE parent_qid='$qiqid' AND title='$qanswer' AND language='{$language}' ORDER BY question_order";
                    $qresult = Yii::app()->db->createCommand($qquery)->query();

                    //loop through answers
                    foreach ($qresult->readAll() as $qrow) {
                        $qrow = array_values($qrow);

                        //this question type uses its own labels
                        $fquery = "SELECT * FROM {{answers}} WHERE qid='{$qiqid}' AND scale_id=0 AND language='{$language}'ORDER BY sortorder, code";
                        $fresult = Yii::app()->db->createCommand($fquery)->query();

                        //add code and title to results for outputting them later
                        foreach ($fresult->readAll() as $frow) {
                            $alist[] = array($frow['code'], flattenText($frow['answer']));
                        }

                        //counter
                        $atext = flattenText($qrow[1]);
                    }

                    //output
                    $qquestion .= $linefeed;
                    $qtitle .= "($qanswer)"."[".$atext."]";
                    break;



                case "G": //Gender
                    $alist[] = array("F", gT("Female"));
                    $alist[] = array("M", gT("Male"));
                    break;



                case "Y": //Yes\No
                    $alist[] = array("Y", gT("Yes"));
                    $alist[] = array("N", gT("No"));
                    break;



                case "I": //Language
                    foreach (Survey::model()->findByPk($surveyid)->getAllLanguages() as $availlang) {
                        $alist[] = array($availlang, getLanguageNameFromCode($availlang, false));
                    }
                    break;


                case "5": //5 Point (just 1 item to rank!)
                    for ($i = 1; $i <= 5; $i++) {
                        $alist[] = array("$i", "$i");
                    }
                    break;


                case "1":    //array (dual scale)


                    $sSubquestionQuery = "SELECT  question FROM {{questions}} WHERE parent_qid='$qiqid' AND title='$qanswer' AND language='{$language}' ORDER BY question_order";
                    $questionDesc = Yii::app()->db->createCommand($sSubquestionQuery)->query()->read();
                    $sSubquestion = flattenText($questionDesc['question']);

                    //get question attributes
                    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($qqid);


                    //check last character -> label 1
                    if (substr($rt, -1, 1) == 0) {
                        //get label 1
                        $fquery = "SELECT * FROM {{answers}} WHERE qid='{$qqid}' AND scale_id=0 AND language='{$language}' ORDER BY sortorder, code";

                        //header available?
                        if (trim($aQuestionAttributes['dualscale_headerA'][$language]) != '') {
                            //output
                            $labelheader = "[".$aQuestionAttributes['dualscale_headerA'][$language]."]";
                        }

                        //no header
                        else {
                            $labelheader = '';
                        }

                        //output
                        $labelno = sprintf(gT('Label %s'), '1');
                    }

                    //label 2
                    else {
                        //get label 2
                        $fquery = "SELECT * FROM {{answers}} WHERE qid='{$qqid}' AND scale_id=1 AND language='{$language}' ORDER BY sortorder, code";

                        //header available?
                        if (trim($aQuestionAttributes['dualscale_headerB'][$language]) != '') {
                            //output
                            $labelheader = "[".$aQuestionAttributes['dualscale_headerB'][$language]."]";
                        }

                        //no header
                        else {
                            $labelheader = '';
                        }

                        //output
                        $labelno = sprintf(gT('Label %s'), '2');
                    }

                    //get data
                    $fresult = Yii::app()->db->createCommand($fquery)->query();

                    //put label code and label title into array
                    foreach ($fresult->readAll() as $frow) {
                        $alist[] = array($frow['code'], flattenText($frow['answer']));
                    }

                    //adapt title and question
                    $qtitle = $qtitle." [".$sSubquestion."][".$labelno."]";
                    $qquestion = $qastring.$labelheader;
                    break;




                default:    //default handling

                    //get answer code and title
                    $qquery = "SELECT code, answer FROM {{answers}} WHERE qid='$qqid' AND scale_id=0 AND language='{$language}' ORDER BY sortorder, answer";
                    $qresult = Yii::app()->db->createCommand($qquery)->query();

                    //put answer code and title into array
                    foreach ($qresult->readAll() as $qrow) {
                        $qrow = array_values($qrow);
                        $alist[] = array("$qrow[0]", flattenText($qrow[1]));
                    }

                    //handling for "other" field for list radio or list drowpdown
                    if ((($qtype == "L" || $qtype == "!") && $qother == "Y")) {
                        //add "other"
                        $alist[] = array(gT("Other"), gT("Other"), $fielddata['fieldname'].'other');
                    }
                    if ($qtype == "O") {
                        //add "comment"
                        $alist[] = array(gT("Comments"), gT("Comments"), $fielddata['fieldname'].'comment', 'is_comment');
                        //
                    }

            }    //end switch question type

            //moved because it's better to have "no answer" at the end of the list instead of the beginning
            //put data into array
            $alist[] = array("", gT("No answer"), false, 'is_no_answer');
        }

        return array("alist"=>$alist, "qtitle"=>$qtitle, "qquestion"=>$qquestion, "qtype"=>$qtype, "statisticsoutput"=>$statisticsoutput, "parentqid"=>$qqid);
    }

    /**
     * @param string $outputType
     * @param integer $usegraph
     * @param boolean $browse
     * @return array
     */
    protected function displaySimpleResults($outputs, $results, $rt, $outputType, $surveyid, $sql, $usegraph, $browse, $sLanguage)
    {
        /* Set up required variables */
        $TotalCompleted = 0; //Count of actually completed answers
        $sDatabaseType = Yii::app()->db->getDriverName();
        $astatdata = array();
        $sColumnName = null;

        //loop though the array which contains all answer data
        $ColumnName_RM = array();
        foreach ($outputs['alist'] as $al) {
            if (isset($al[2]) && $al[2]) {
                //handling for "other" option
                if ($al[0] == gT("Other")) {
                    if ($outputs['qtype'] == '!' || $outputs['qtype'] == 'L') {
                        $query = "SELECT count(*) FROM {{survey_$surveyid}} WHERE ".Yii::app()->db->quoteColumnName(substr($al[2], 0, strlen($al[2]) - 5))."='-oth-'";
                    } else {
                        $query = "SELECT count(*) FROM {{survey_$surveyid}} WHERE ";
                        $query .= ($sDatabaseType == "mysql") ?  Yii::app()->db->quoteColumnName($al[2])." != ''" : "NOT (".Yii::app()->db->quoteColumnName($al[2])." LIKE '')";
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
                elseif ($outputs['qtype'] == "U" || $outputs['qtype'] == "T" || $outputs['qtype'] == "S" || $outputs['qtype'] == "Q" || $outputs['qtype'] == ";") {
                    $sDatabaseType = Yii::app()->db->getDriverName();

                    //free text answers
                    if ($al[0] == "Answer") {
                        $query = "SELECT count(*) FROM {{survey_$surveyid}} WHERE ";
                        $query .= ($sDatabaseType == "mysql") ?  Yii::app()->db->quoteColumnName($al[2])." != ''" : "NOT (".Yii::app()->db->quoteColumnName($al[2])." LIKE '')";
                    }
                    //"no answer" handling
                    elseif ($al[0] == "NoAnswer") {
                        $query = "SELECT count(*) FROM {{survey_$surveyid}} WHERE ( ";
                        $query .= ($sDatabaseType == "mysql") ?  Yii::app()->db->quoteColumnName($al[2])." = '')" : " (".Yii::app()->db->quoteColumnName($al[2])." LIKE ''))";
                    }
                } elseif ($outputs['qtype'] == "O") {
                    $query = "SELECT count(*) FROM {{survey_$surveyid}} WHERE ( ";
                    $query .= ($sDatabaseType == "mysql") ?  Yii::app()->db->quoteColumnName($al[2])." <> '')" : " (".Yii::app()->db->quoteColumnName($al[2])." NOT LIKE ''))";
                    // all other question types
                } else {
                    $query = "SELECT count(*) FROM {{survey_$surveyid}} WHERE ".Yii::app()->db->quoteColumnName($al[2])." =";

                    //ranking question?
                    if (substr($rt, 0, 1) == "R") {
                        $query .= " '$al[0]'";
                    } else {
                        $query .= " 'Y'";
                    }
                }
            }    //end if -> alist set
            else {

                if ($al[0] != "") {

                    //get more data
                    $sDatabaseType = Yii::app()->db->getDriverName();
                    if ($sDatabaseType == 'mssql' || $sDatabaseType == 'sqlsrv' || $sDatabaseType == 'dblib') {
                        // mssql cannot compare text blobs so we have to cast here
                        $query = "SELECT count(*) FROM {{survey_$surveyid}} WHERE cast(".Yii::app()->db->quoteColumnName($rt)." as varchar)= '$al[0]'";
                    } else {
                                            $query = "SELECT count(*) FROM {{survey_$surveyid}} WHERE ".Yii::app()->db->quoteColumnName($rt)." = '$al[0]'";
                    }
                } else {
// This is for the 'NoAnswer' case
                    // We need to take into account several possibilities
                    // * NoAnswer cause the participant clicked the NoAnswer radio
                    //  ==> in this case value is '' or ' '
                    // * NoAnswer in text field
                    //  ==> value is ''
                    // * NoAnswer due to conditions, or a page not displayed
                    //  ==> value is NULL
                    if ($sDatabaseType == 'mssql' || $sDatabaseType == 'sqlsrv' || $sDatabaseType == 'dblib') {
                        // mssql cannot compare text blobs so we have to cast here
                        $query = "SELECT count(*) FROM {{survey_$surveyid}} WHERE ( "
                        . "cast(".Yii::app()->db->quoteColumnName($rt)." as varchar) = '' "
                        . "OR cast(".Yii::app()->db->quoteColumnName($rt)." as varchar) = ' ' )";
                    } else {
                        $query = "SELECT count(*) FROM {{survey_$surveyid}} WHERE ( "
                        . " ".Yii::app()->db->quoteColumnName($rt)." = '' "
                        . "OR ".Yii::app()->db->quoteColumnName($rt)." = ' ') ";
                    }
                }
            }

            //check filter option
            if (incompleteAnsFilterState() == "incomplete") {$query .= " AND submitdate is null"; } elseif (incompleteAnsFilterState() == "complete") {$query .= " AND submitdate is not null"; }

            //check for any "sql" that has been passed from another script
            if (!empty($sql)) {$query .= " AND $sql"; }

            //get data
            $row = Yii::app()->db->createCommand($query)->queryScalar();

            //store temporarily value of answer count of question type '5' and 'A'.
            $tempcount = -1; //count can't be less han zero

            //increase counter
            $TotalCompleted += $row;

            //"no answer" handling
            if ($al[0] === "")
                {$fname = gT("No answer"); }

            //"other" handling
            //"Answer" means that we show an option to list answer to "other" text field
            elseif (($al[0] === gT("Other") || $al[0] === "Answer" || ($outputs['qtype'] === "O" && $al[0] === gT("Comments")) || $outputs['qtype'] === "P") && count($al) > 2) {

                if ($outputs['qtype'] == "P") {
                    $sColumnName = $al[2]."comment";
                } else {
                    $sColumnName = $al[2];
                }
                $ColumnName_RM[] = $sColumnName;
                if ($outputs['qtype'] == 'O') {
                    $TotalCompleted -= $row;
                }
                $fname = "$al[1]";
                if ($browse === true) {
                    $fname .= " <input type='button' class='statisticsbrowsebutton btn btn-default btn-large' value='"
                    .gT("Browse")."' id='$sColumnName' />";
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
                elseif ($outputs['qtype'] == "S" || $outputs['qtype'] == "U" || $outputs['qtype'] == "T" || $outputs['qtype'] == "Q") {

                    $headPDF = array();
                    $headPDF[] = array(gT("Answer"), gT("Count"), gT("Percentage"));

                    //show free text answers
                    if ($al[0] == "Answer") {
                        $fname = "$al[1]";
                        if ($browse === true) {
                            $fname .= " <input type='button'  class='statisticsbrowsebutton btn btn-default btn-large' value='"
                            . gT("Browse")."' id='$sColumnName' />";
                        }
                    } elseif ($al[0] == "NoAnswer") {
                        $fname = "$al[1]";
                    }

                    $bAnswer = true; // For view
                    $bSum = false;
                }


                //check if aggregated results should be shown
                elseif (Yii::app()->getConfig('showaggregateddata') == 1) {
                    if (!isset($showheadline) || $showheadline != false) {
                            $showheadline = false;
                    }

                    //text for answer column is always needed
                    $fname = "$al[1] ($al[0])";

                }    //end if -> show aggregated data

                //handling what's left
                else {
                    if (!isset($showheadline) || $showheadline != false) {
                        $showheadline = false;
                    }
                    //answer text
                    $fname = "$al[1] ($al[0])";
                }

                //are there some results to play with?
                if ($results > 0) {
                    //calculate percentage
                    $gdata[] = ($row / $results) * 100;
                }
                //no results
                else {
                    //no data!
                    $gdata[] = "N/A";
                }

                //put absolute data into array
                $grawdata[] = $row;

                //put question title and code into array
                $label[] = $fname;

                //edit labels and put them into antoher array

                //first check if $tempcount is > 0. If yes, $row has been modified and $tempcount has the original count.
                if ($tempcount > -1) {
                    $flatLabel = wordwrap(FlattenText("$al[1]"), 25, "\n");
                    // If the flatten label is empty (like for picture, or HTML, etc.)
                    // We replace it by the subquestion code
                    if ($flatLabel == '') {
                        $flatLabel = $al[0];
                    }
                    $lbl[$flatLabel] = $tempcount;

                } else {
                    $flatLabel = wordwrap(FlattenText("$al[1]"), 25, "\n");
                    // If the flatten label is empty (like for picture, or HTML, etc.)
                    // We replace it by the subquestion code
                    if ($flatLabel == '') {
                        $flatLabel = $al[0];
                    }
                    // Duplicate labels can exist.
                    // TODO: Support three or more duplicates.
                    if (isset($lbl[$flatLabel])) {
                        $lbl[$flatLabel.' (2)'] = $row;
                    } else {
                        $lbl[$flatLabel] = $row;
                    }
                }


            }    //end foreach -> loop through answer data

            //no filtering of incomplete answers and NO multiple option questions
            //if ((incompleteAnsFilterState() != "complete") and ($outputs['qtype'] != "M") and ($outputs['qtype'] != "P"))
            //error_log("TIBO ".print_r($showaggregated_indice_table,true));
            if (($outputs['qtype'] != "M") and ($outputs['qtype'] != "P")) {
                //is the checkbox "Don't consider NON completed responses (only works when Filter incomplete answers is Disable)" checked?
                //if (isset($_POST[''noncompleted']) and ($_POST['noncompleted'] == 1) && (isset(Yii::app()->getConfig('showaggregateddata')) && Yii::app()->getConfig('showaggregateddata') == 0))
                // TIBO: TODO WE MUST SKIP THE FOLLOWING SECTION FOR TYPE A and 5 when
                // showaggreagated data is set and set to 1
                if (isset($_POST['noncompleted']) and ($_POST['noncompleted'] == 1)) {
                    //counter
                    $i = 0;

                    while (isset($gdata[$i])) {
                        if (isset($showaggregated_indice_table[$i]) && $showaggregated_indice_table[$i] == "aggregated") {
// do nothing, we don't rewrite aggregated results
                            // or at least I don't know how !!! (lemeur)
                        } else {
                            //we want to have some "real" data here
                            if ($gdata[$i] != "N/A") {
                                //calculate percentage
                                $gdata[$i] = ($grawdata[$i] / $TotalCompleted) * 100;
                            }
                        }

                        //increase counter
                        $i++;

                    }    //end while (data available)

                }    //end if -> noncompleted checked
            }

            // For multi question type, we have to check non completed with ALL sub question set to NULL
            if (($outputs['qtype'] == "M") or ($outputs['qtype'] == "P")) {
                $criteria = new CDbCriteria;
                foreach ($outputs['alist'] as $al) {
                    $criteria->addCondition(Yii::app()->db->quoteColumnName($al[2])." IS NULL");
                }
                if (incompleteAnsFilterState() == "incomplete") {$criteria->addCondition("submitdate IS NULL"); } elseif (incompleteAnsFilterState() == "complete") {$criteria->addCondition("submitdate IS NOT NULL"); }
                $multiNotDisplayed = SurveyDynamic::model($surveyid)->count($criteria);
                if (isset($_POST['noncompleted']) and ($_POST['noncompleted'] == 1)) {
                    //counter
                    $i = 0;
                    while (isset($gdata[$i])) {
                        //we want to have some "real" data here
                        if ($gdata[$i] != "N/A") {
                            //calculate percentage
                            if ($results > $multiNotDisplayed) {
                                $gdata[$i] = ($grawdata[$i] / ($results - $multiNotDisplayed)) * 100;
                            } else {
                                $gdata[$i] = "N/A";
                            }
                        }
                        $i++;
                    }
                } else {
// Add a line with not displayed %
                    if ($multiNotDisplayed > 0) {
                        if ((incompleteAnsFilterState() != "complete")) {
                            $fname = gT("Not completed or Not displayed");
                        } else {
                            $fname = gT("Not displayed");
                        }
                        $label[] = $fname;
                        $lbl[$fname] = $multiNotDisplayed;
                        //we need some data
                        if ($results > 0) {
                            //calculate percentage
                            $gdata[] = ($multiNotDisplayed / $results) * 100;
                        }
                        //no data :(
                        else {
                            $gdata[] = "N/A";
                        }
                        //put data of incompleted records into array
                        $grawdata[] = $multiNotDisplayed;
                    }
                }
            }


            //counter
            $i = 0;

            //we need to know which item we are editing
            $itemcounter = 1;

            $aData['outputs'] = (isset($outputs)) ? $outputs : '';
            $aData['bSum'] = (isset($bSum)) ? $bSum : '';
            $aData['bAnswer'] = (isset($bAnswer)) ? $bAnswer : '';
            $aData['usegraph'] = $usegraph;

            $statisticsoutput = Yii::app()->getController()->renderPartial('/admin/export/generatestats/simplestats/_statisticsoutput_header', $aData, true);

            //loop through all available answers
            while (isset($gdata[$i])) {
                $aData['i'] = $i;
                ///// We'll render at the end of this loop statisticsoutput_answer

                //repeat header (answer, count, ...) for each new question
                unset($showheadline);


                /*
                * there are 3 colums:
                *
                * 1 (50%) = answer (title and code in brackets)
                * 2 (25%) = count (absolute)
                * 3 (25%) = percentage
                */

                /*
                * If there is a "browse" button in this label, let's make sure there's an extra row afterwards
                * to store the columnlist
                *
                * */
                if (strpos($label[$i], "statisticsbrowsebutton")) {
                    $extraline = "<tr><td class='statisticsbrowsecolumn' colspan='3' style='display: none'>";
                    if ($outputs['qtype'] == 'P') {
                        $extraline .= "<div class='statisticsbrowsecolumn' id='columnlist_{$ColumnName_RM[$i]}'></div></td></tr>\n";
                    } else {
                        $extraline .= "<div class='statisticsbrowsecolumn' id='columnlist_{$sColumnName}'></div></td></tr>\n";
                    }
                }

                //data available
                if (($gdata[$i] !== "N/A")) {
                    //check if data should be aggregated
                    if (Yii::app()->getConfig('showaggregateddata') == 1 && ($outputs['qtype'] == "5" || $outputs['qtype'] == "A")) {
                        //mark that we have done soemthing special here
                        $aggregated = true;

                        if (($results - $grawdata[5]) > 0) {
                            $percentage = $grawdata[$i] / ($results - $grawdata[5]) * 100; // Only answered
                        } else {
                            $percentage = 0;
                        }

                        switch ($itemcounter) {
                            case 1:
                                if (($results - $grawdata[5]) > 0) {
                                    $aggregatedPercentage = ($grawdata[0] + $grawdata[1]) / ($results - $grawdata[5]) * 100;
                                } else {
                                    $aggregatedPercentage = 0;
                                }
                                break;

                            case 3:
                                $aggregatedPercentage = $percentage;
                                break;

                            case 5:
                                if (($results - $grawdata[5]) > 0) {
                                    $aggregatedPercentage = ($grawdata[3] + $grawdata[4]) / ($results - $grawdata[5]) * 100;
                                } else {
                                    $aggregatedPercentage = 0;
                                }
                                break;

                            case 6:
                            case 7:
                                if (($results - $grawdata[5]) > 0) {
                                    $aggregatedPercentage = $grawdata[$i] / $results * 100; // All results
                                } else {
                                    $aggregatedPercentage = 0;
                                }
                                break;

                            default:
                                $aggregatedPercentage = 'na';
                                break;
                        }


                        if ($itemcounter == 5) {
                            // new row "sum"
                            //calculate sum of items 1-5
                            $sumitems = $grawdata[0]
                            + $grawdata[1]
                            + $grawdata[2]
                            + $grawdata[3]
                            + $grawdata[4];

                            //special treatment for zero values
                            if ($sumitems > 0) {
                                $sumpercentage = "100.00";
                            } else {
                                $sumpercentage = "0";
                            }
                            //special treatment for zero values
                            if ($TotalCompleted > 0) {
                                $casepercentage = "100.00";
                            } else {
                                $casepercentage = "0";
                            }
                        }

                    }    //end if -> show aggregated data

                }    //end else -> $gdata[$i] != "N/A"
                //increase counter
                $i++;
                $itemcounter++;
                //Clear extraline
                unset($extraline);

                ///// HERE RENDER statisticsoutput_answer
                $aData['label'] = $label;
                $aData['grawdata'] = $grawdata;
                $aData['gdata'] = $gdata;
                $aData['extraline'] = (isset($extraline)) ? $extraline : false;
                $aData['aggregated'] = (isset($aggregated)) ? $aggregated : false;
                $aData['aggregatedPercentage'] = (isset($aggregatedPercentage)) ? $aggregatedPercentage : false;
                $aData['sumitems'] = (isset($sumitems)) ? $sumitems : false;
                $aData['sumpercentage'] = (isset($sumpercentage)) ? $sumpercentage : false;
                $aData['TotalCompleted'] = (isset($TotalCompleted)) ? $TotalCompleted : false;
                $aData['casepercentage'] = (isset($casepercentage)) ? $casepercentage : false;

                // Generate answer
                // _statisticsoutput_answer
                $statisticsoutput .= Yii::app()->getController()->renderPartial('/admin/export/generatestats/simplestats/_statisticsoutput_answer', $aData, true);

            }    //end while
            //$statisticsoutput .= '</table>';
            $aData['showaggregateddata'] = false;

            //only show additional values when this setting is enabled
            if (Yii::app()->getConfig('showaggregateddata') == 1) {
                //it's only useful to calculate standard deviation and arithmetic means for question types
                //5 = 5 Point Scale
                //A = Array (5 Point Choice)
                if ($outputs['qtype'] == "5" || $outputs['qtype'] == "A") {
                    $stddev = 0;
                    $stddevarray = array_slice($grawdata, 0, 5, true);
                    $am = 0;

                    //calculate arithmetic mean
                    if (isset($sumitems) && $sumitems > 0) {


                        //calculate and round results
                        //there are always 5 items
                        for ($x = 0; $x < 5; $x++) {
                            //create product of item * value
                            $am += (($x + 1) * $stddevarray[$x]);
                        }

                        //prevent division by zero
                        if (isset($stddevarray) && array_sum($stddevarray) > 0) {
                            $am = round($am / array_sum($stddevarray), 2);
                        } else {
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



                        for ($j = 0; $j < 5; $j++) {
                            //1 = calculate difference between item and arithmetic mean
                            $diff = (($j + 1) - $am);

                            //2 = create square value of difference
                            $squarevalue = square($diff);

                            //3 = sum up square values and multiply them with the occurence
                            //prevent divison by zero
                            if ($squarevalue != 0 && $stddevarray[$j] != 0) {
                                $stddev += $squarevalue * $stddevarray[$j];
                            }

                        }

                        //4 = multiply result with 1 / (number of items (=5))
                        //There are two different formulas to calculate standard derivation
                        //$stddev = $stddev / array_sum($stddevarray);        //formula source: http://de.wikipedia.org/wiki/Standardabweichung

                        //prevent division by zero
                        if ((array_sum($stddevarray) - 1) != 0 && $stddev != 0) {
                            $stddev = $stddev / (array_sum($stddevarray) - 1); //formula source: http://de.wikipedia.org/wiki/Empirische_Varianz
                        } else {
                            $stddev = 0;
                        }

                        //5 = get root
                        $stddev = sqrt($stddev);
                        $stddev = round($stddev, 2);
                    }
                    switch ($outputType) {
                        case 'html':
                            //calculate standard deviation
                            $aData['am'] = $am;
                            $aData['stddev'] = $stddev;
                            $statisticsoutput .= Yii::app()->getController()->renderPartial('/admin/export/generatestats/simplestats/_statisticsoutput_arithmetic', $aData, true);
                            break;
                        default:


                            break;
                    }
                }
            }


            // _statisticsoutput_graphs.php


            //-------------------------- PCHART OUTPUT ----------------------------
            list(, $qgid, $qqid) = explode("X", $rt, 3);
            $aattr = QuestionAttribute::model()->getQuestionAttributes($outputs['parentqid']);

            //PCHART has to be enabled and we need some data
            //
            if ($usegraph == 1) {
                $bShowGraph = $aattr["statistics_showgraph"] == "1";
                $bAllowPieChart = ($outputs['qtype'] != "M" && $outputs['qtype'] != "P");
                $bAllowMap = (isset($aattr["location_mapservice"]) && $aattr["location_mapservice"] == "1");
                $bShowMap = ($bAllowMap && $aattr["statistics_showmap"] == "1");
                $bShowPieChart = ($bAllowPieChart && (isset($aattr["statistics_graphtype"]) && $aattr["statistics_graphtype"] == "1"));

                $astatdata[$rt] = array(
                    'id' => $rt,
                    'sg' => $bShowGraph,
                    'ap' => $bAllowPieChart,
                    'am' => $bAllowMap,
                    'sm' => $bShowMap,
                    'sp' => $bShowPieChart
                );

                $stats = Yii::app()->session['stats'];
                $stats[$rt] = array(
                    'lbl' => $lbl,
                    'gdata' => $gdata,
                    'grawdata' => $grawdata
                );
                Yii::app()->session['stats'] = $stats;

                if ($bShowGraph == true) {

                    $cachefilename = '';
                    if ($outputType == 'xls' || $outputType == 'pdf') {
                        /**
                         *
                         //FIXME $MyCache is undefined
                        $cachefilename = createChart($qqid, $qsid, $bShowPieChart, $lbl, $gdata, $grawdata, $MyCache, $sLanguage, $outputs['qtype']);
                         *
                         */
                    }

                }
            }


            if (isset($aattr["statistics_graphtype"])) {
                $req_chart_type = $aattr["statistics_graphtype"];
            } else {
                $req_chart_type = 0;
            }

            //// If user forced the chartype from statistics_view
            if (isset($_POST['charttype']) && $_POST['charttype'] != 'default') {
                $req_chart_type = $_POST['charttype'];
            }

            //// The value of the select box in the question advanced setting is numerical. So we need to translate it.
            if (isset($req_chart_type)) {
                switch ($req_chart_type) {
                    case '1':
                            $charttype = "Pie";
                        break;

                    case '2':
                        $charttype = "Radar";
                        break;

                    case '3':
                        $charttype = "Line";
                        break;

                    case '4':
                        $charttype = "PolarArea";
                        break;

                    case '5':
                        $charttype = "Doughnut";
                        break;

                    default:
                        $charttype = "Bar";
                        break;
                }
            }

            //// Here the 72 colors of the original limesurvey palette.
            //// This could be change by some user palette coming from database.
            $COLORS_FOR_SURVEY = array('20,130,200', '232,95,51', '34,205,33', '210,211,28', '134,179,129', '201,171,131', '251,231,221', '23,169,161', '167,187,213', '211,151,213', '147,145,246', '147,39,90', '250,250,201', '201,250,250', '94,0,94', '250,125,127', '0,96,201', '201,202,250', '0,0,127', '250,0,250', '250,250,0', '0,250,250', '127,0,127', '127,0,0', '0,125,127', '0,0,250', '0,202,250', '201,250,250', '201,250,201', '250,250,151', '151,202,250', '251,149,201', '201,149,250', '250,202,151', '45,96,250', '45,202,201', '151,202,0', '250,202,0', '250,149,0', '250,96,0', '184,230,115', '102,128,64', '220,230,207', '134,191,48', '184,92,161', '128,64,112', '230,207,224', '191,48,155', '230,138,115', '128,77,64', '230,211,207', '191,77,48', '80,161,126', '64,128,100', '207,230,220', '48,191,130', '25,25,179', '18,18,125', '200,200,255', '145,145,255', '255,178,0', '179,125,0', '255,236,191', '255,217,128', '255,255,0', '179,179,0', '255,255,191', '255,255,128', '102,0,153', '71,0,107', '234,191,255', '213,128,255');

            //// $lbl is generated somewhere upthere by the original code. We translate it for chartjs.
                $labels = array();
                foreach ($lbl as $name => $lb) {
                                    $labels[] = $name;
                }

            //close table/output
            if ($outputType == 'html') {
                // show this block only when we show graphs and are not in the public statics controller
                if ($usegraph == 1 && $bShowGraph && get_class(Yii::app()->getController()) !== 'Statistics_userController') {
                    // We clean the labels
                    $iMaxLabelLength = 0;
                    foreach ($labels as $key => $label) {
                        $cleanLabel = viewHelper::flatEllipsizeText($label, true, 20);
                        $labels[$key] = $cleanLabel;
                        $iMaxLabelLength = (strlen($cleanLabel) > $iMaxLabelLength) ?strlen($cleanLabel) : $iMaxLabelLength;
                    }

                    $iCanvaHeight = $iMaxLabelLength * 3;
                    $aData['iCanvaHeight'] = ($iCanvaHeight > 150) ? $iCanvaHeight : 150;
                    $qqid = str_replace('#', '_', $qqid);
                    $aData['rt'] = $rt;
                    $aData['qqid'] = $qqid;
                    $aData['labels'] = $labels;
                    $aData['charttype'] = $charttype;
                    $aData['sChartname'] = '';
                    $aData['grawdata'] = $grawdata;
                    $aData['color'] = rand(0, 72); // random truc much
                    $aData['COLORS_FOR_SURVEY'] = $COLORS_FOR_SURVEY;
                    // Output graph
                    $statisticsoutput .= Yii::app()->getController()->renderPartial('/admin/export/generatestats/simplestats/_statisticsoutput_graphs', $aData, true);

                } else {
                    $statisticsoutput .= Yii::app()->getController()->renderPartial('/admin/export/generatestats/simplestats/_statisticsoutput_nograph', array(), true);
                }
                $statisticsoutput .= "</div>\n";
            }


            return array("statisticsoutput"=>$statisticsoutput, "pdf"=>$this->pdf, "astatdata"=>$astatdata);
    }

    /**
     * displayResults builds html output to display the actual results from a survey
     *
     * @param mixed $outputs
     * @param INT $results The number of results being displayed overall
     * @param mixed $rt
     * @param string $outputType
     * @param mixed $surveyid
     * @param mixed $sql
     * @param integer $usegraph
     *
     *
     */
    protected function displayResults($outputs, $results, $rt, $outputType, $surveyid, $sql, $usegraph, $browse, $sLanguage)
    {
        /* Set up required variables */
        $TotalCompleted     = 0; //Count of actually completed answers
        $statisticsoutput   = "";
        $sDatabaseType      = Yii::app()->db->getDriverName();
        $tempdir            = Yii::app()->getConfig("tempdir");
        $astatdata          = array();
        $TotalIncomplete    = 0;

        $sColumnName = null;

        if ($usegraph == 1 && $outputType != 'html') {
            //for creating graphs we need some more scripts which are included here
            require_once(APPPATH.'/third_party/pchart/pChart.class.php');
            require_once(APPPATH.'/third_party/pchart/pData.class.php');
            require_once(APPPATH.'/third_party/pchart/pCache.class.php');
            $MyCache = new pCache($tempdir.'/');
        }

        switch ($outputType) {
            case 'xls':

                $xlsTitle = sprintf(gT("Summary for %s"), html_entity_decode($outputs['qtitle'], ENT_QUOTES, 'UTF-8'));
                $xlsDesc = html_entity_decode($outputs['qquestion'], ENT_QUOTES, 'UTF-8');

                $this->xlsRow++;
                $this->xlsRow++;

                $this->xlsRow++;
                $this->sheet->write($this->xlsRow, 0, $xlsTitle);
                $this->xlsRow++;
                $this->sheet->write($this->xlsRow, 0, $xlsDesc);
                $footXLS = array();

                break;
            case 'pdf':

                $sPDFQuestion = flattenText($outputs['qquestion'], false, true);
                $pdfTitle = $this->pdf->delete_html(sprintf(gT("Summary for %s"), html_entity_decode($outputs['qtitle'], ENT_QUOTES, 'UTF-8')));
                $titleDesc = $sPDFQuestion;

                $this->pdf->AddPage('P', 'A4');
                $this->pdf->Bookmark($sPDFQuestion, 1, 0);
                $this->pdf->titleintopdf($pdfTitle, $sPDFQuestion);
                $tablePDF = array();
                $footPDF = array();

                break;
            case 'html':
                // output now generated in subview _statisticsoutuput_header
                break;
            default:
                break;
        }
        //loop though the array which contains all answer data
        $ColumnName_RM = array();
        $statisticsoutput_footer = "<script>";

        $lbl       = array();
        $tableXLS  = array();
        $tablePDF2 = array();
        foreach ($outputs['alist'] as $al) {
            //picks out answer list ($outputs['alist']/$al)) that come from the multiple list above
            if (isset($al[2]) && $al[2]) {

                //handling for "other" option
                if ($al[0] == gT("Other")) {
                    if ($outputs['qtype'] == '!' || $outputs['qtype'] == 'L') {
                        // It is better for single choice question types to filter on the number of '-oth-' entries, than to
                        // just count the number of 'other' values - that way with failing Javascript the statistics don't get messed up
                        /* This query selects a count of responses where "other" has been selected */
                        $query = "SELECT count(*) FROM {{survey_$surveyid}} WHERE ".Yii::app()->db->quoteColumnName(substr($al[2], 0, strlen($al[2]) - 5))."='-oth-'";
                    } else {
                        //get data - select a count of responses where no answer is provided
                        $query = "SELECT count(*) FROM {{survey_$surveyid}} WHERE ";
                        $query .= ($sDatabaseType == "mysql") ?  Yii::app()->db->quoteColumnName($al[2])." != ''" : "NOT (".Yii::app()->db->quoteColumnName($al[2])." LIKE '')";
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
                elseif ($outputs['qtype'] == "U" || $outputs['qtype'] == "T" || $outputs['qtype'] == "S" || $outputs['qtype'] == "Q" || $outputs['qtype'] == ";") {
                    $sDatabaseType = Yii::app()->db->getDriverName();

                    //free text answers
                    if ($al[0] == "Answer") {
                        $query = "SELECT count(*) FROM {{survey_$surveyid}} WHERE ";
                        $query .= ($sDatabaseType == "mysql") ?  Yii::app()->db->quoteColumnName($al[2])." != ''" : "NOT (".Yii::app()->db->quoteColumnName($al[2])." LIKE '')";
                    }
                    //"no answer" handling
                    elseif ($al[0] == "NoAnswer") {
                        $query = "SELECT count(*) FROM {{survey_$surveyid}} WHERE ( ";
                        $query .= ($sDatabaseType == "mysql") ?  Yii::app()->db->quoteColumnName($al[2])." = '')" : " (".Yii::app()->db->quoteColumnName($al[2])." LIKE ''))";
                    }
                } elseif ($outputs['qtype'] == "O") {
                    $query = "SELECT count(*) FROM {{survey_$surveyid}} WHERE ( ";
                    $query .= ($sDatabaseType == "mysql") ?  Yii::app()->db->quoteColumnName($al[2])." <> '')" : " (".Yii::app()->db->quoteColumnName($al[2])." NOT LIKE ''))";
                    // all other question types
                } else {

                    $query = "SELECT count(*) FROM {{survey_$surveyid}} WHERE ".Yii::app()->db->quoteColumnName($al[2])." =";

                    //ranking question?
                    if (substr($rt, 0, 1) == "R") {
                        $query .= " '$al[0]'";
                    } else {
                        $query .= " 'Y'";
                    }
                }
            }    //end if -> alist set

            else {
                if ($al[0] != "") {
                    //get more data
                    $sDatabaseType = Yii::app()->db->getDriverName();
                    if ($sDatabaseType == 'mssql' || $sDatabaseType == 'sqlsrv' || $sDatabaseType == 'dblib') {
                        // mssql cannot compare text blobs so we have to cast here
                        $query = "SELECT count(*) FROM {{survey_$surveyid}} WHERE cast(".Yii::app()->db->quoteColumnName($rt)." as varchar)= '$al[0]'";
                    } else {
                                            $query = "SELECT count(*) FROM {{survey_$surveyid}} WHERE ".Yii::app()->db->quoteColumnName($rt)." = '$al[0]'";
                    }
                } else {
// This is for the 'NoAnswer' case
                    // We need to take into account several possibilities
                    // * NoAnswer cause the participant clicked the NoAnswer radio
                    //  ==> in this case value is '' or ' '
                    // * NoAnswer in text field
                    //  ==> value is ''
                    // * NoAnswer due to conditions, or a page not displayed
                    //  ==> value is NULL
                    if ($sDatabaseType == 'mssql' || $sDatabaseType == 'sqlsrv' || $sDatabaseType == 'dblib') {
                        // mssql cannot compare text blobs so we have to cast here
                        //$query = "SELECT count(*) FROM {{survey_$surveyid}} WHERE (".sanitize_int($rt)." IS NULL "
                        $query = "SELECT count(*) FROM {{survey_$surveyid}} WHERE ( "
                        //                                    . "OR cast(".sanitize_int($rt)." as varchar) = '' "
                        . "cast(".Yii::app()->db->quoteColumnName($rt)." as varchar) = '' "
                        . "OR cast(".Yii::app()->db->quoteColumnName($rt)." as varchar) = ' ' )";
                    } elseif ($sDatabaseType == 'pgsql') {
                        $query = "SELECT count(*) FROM {{survey_$surveyid}} WHERE ( "
                        //                                    . "OR ".sanitize_int($rt)." = '' "
                        . " ".Yii::app()->db->quoteColumnName($rt)."::text = '' "
                        . "OR ".Yii::app()->db->quoteColumnName($rt)."::text = ' ') ";
                    } else {
                        $query = "SELECT count(*) FROM {{survey_$surveyid}} WHERE ( "
                        //                                    . "OR ".sanitize_int($rt)." = '' "
                        . " ".Yii::app()->db->quoteColumnName($rt)." = '' "
                        . "OR ".Yii::app()->db->quoteColumnName($rt)." = ' ') ";
                    }
                }

            }

            //check filter option
            if (incompleteAnsFilterState() == "incomplete") {$query .= " AND submitdate is null"; } elseif (incompleteAnsFilterState() == "complete") {$query .= " AND submitdate is not null"; }

            //check for any "sql" that has been passed from another script
            if (!empty($sql)) {$query .= " AND $sql"; }

            //get data
            try {
                $row = Yii::app()->db->createCommand($query)->queryScalar();
            } catch (Exception $ex) {
                $row = 0;
                Yii::app()->setFlashMessage('Faulty query: '.htmlspecialchars($query), 'error');
            }

            //store temporarily value of answer count of question type '5' and 'A'.
            $tempcount = -1; //count can't be less han zero

            //increase counter
            $TotalCompleted += $row;

            //"no answer" handling
            if ($al[0] === "")
                {$fname = gT("No answer"); }

            //"other" handling
            //"Answer" means that we show an option to list answer to "other" text field
            elseif (($al[0] === gT("Other") || $al[0] === "Answer" || ($outputs['qtype'] === "O" && $al[0] === gT("Comments")) || $outputs['qtype'] === "P") && count($al) > 2) {
                if ($outputs['qtype'] == "P") {
                    $sColumnName = $al[2]."comment";
                } else {
                    $sColumnName = $al[2];
                }
                $ColumnName_RM[] = $sColumnName;
                if ($outputs['qtype'] == 'O') {
                    $TotalCompleted -= $row;
                }
                $fname = "$al[1]";
                if ($browse === true) {
                    $fname .= " <input type='button' class='statisticsbrowsebutton btn btn-default btn-large' value='"
                    .gT("Browse")."' id='$sColumnName' />";
                }

                if ($browse === true && isset($_POST['showtextinline']) && $outputType == 'pdf') {
                    $headPDF2 = array();
                    $headPDF2[] = array(gT("ID"), gT("Response"));
                    $result2 = $this->_listcolumn($surveyid, $sColumnName);

                    foreach ($result2 as $row2) {
                        $tablePDF2[] = array($row2['id'], $row2['value']);
                    }
                }

                if ($browse === true && isset($_POST['showtextinline']) && $outputType == 'xls') {
                    $headXLS = array();
                    $headXLS[] = array(gT("ID"), gT("Response"));

                    $result2 = $this->_listcolumn($surveyid, $sColumnName);

                    foreach ($result2 as $row2) {
                        $tableXLS[] = array($row2['id'], $row2['value']);
                    }
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
            elseif ($outputs['qtype'] == "S" || $outputs['qtype'] == "U" || $outputs['qtype'] == "T" || $outputs['qtype'] == "Q") {
                $headPDF = array();
                $headPDF[] = array(gT("Answer"), gT("Count"), gT("Percentage"));

                //show free text answers
                if ($al[0] == "Answer") {
                    $fname = "$al[1]";
                    if ($browse === true) {
                        $fname .= " <input type='button'  class='statisticsbrowsebutton btn btn-default btn-large' value='"
                        . gT("Browse")."' id='$sColumnName' />";
                    }
                } elseif ($al[0] == "NoAnswer") {
                    $fname = "$al[1]";
                }

                $bShowCount = true;
                $bShowPercentage = true;
                $bAnswer = true; // For view
                $bSum    = false;

                if ($browse === true && isset($_POST['showtextinline']) && $outputType == 'pdf') {
                    $headPDF2 = array();
                    $headPDF2[] = array(gT("ID"), gT("Response"));
                    $tablePDF2 = array();
                    $result2 = $this->_listcolumn($surveyid, $sColumnName);

                    foreach ($result2 as $row2) {
                        $tablePDF2[] = array($row2['id'], $row2['value']);
                    }
                }
            }


            //check if aggregated results should be shown
            elseif (Yii::app()->getConfig('showaggregateddata') == 1) {
                if (!isset($showheadline) || $showheadline != false) {
                    if ($outputs['qtype'] == "5" || $outputs['qtype'] == "A") {
                        switch ($outputType) {
                            case 'xls':
                                $this->xlsRow++;
                                $this->sheet->write($this->xlsRow, 0, gT("Answer"));
                                $this->sheet->write($this->xlsRow, 1, gT("Count"));
                                $this->sheet->write($this->xlsRow, 2, gT("Percentage"));
                                $this->sheet->write($this->xlsRow, 3, gT("Sum"));
                                break;

                            case 'pdf':

                                $headPDF = array();
                                $headPDF[] = array(gT("Answer"), gT("Count"), gT("Percentage"), gT("Sum"));

                                break;
                            case 'html':
                                //four columns
                                $bShowCount = true;
                                $bShowPercentage = true;
                                $bAnswer = true;
                                $bSum = true;
                                break;
                            default:


                                break;
                        }

                        $showheadline = false;
                    } else {
                        switch ($outputType) {
                            case 'xls':
                                $this->xlsRow++;
                                $this->sheet->write($this->xlsRow, 0, gT("Answer"));
                                $this->sheet->write($this->xlsRow, 1, gT("Count"));
                                $this->sheet->write($this->xlsRow, 2, gT("Percentage"));
                                break;

                            case 'pdf':

                                $headPDF = array();
                                $headPDF[] = array(gT("Answer"), gT("Count"), gT("Percentage"));

                                break;
                            case 'html':
                                //three columns
                                $bAnswer = true; // For view
                                $bSum = false;
                                $bShowCount = true;
                                $bShowPercentage = true;
                                break;
                            default:

                                break;
                        }

                        $showheadline = false;
                    }

                }

                //text for answer column is always needed
                $fname = "$al[1] ($al[0])";

            }    //end if -> show aggregated data

            //handling what's left
            else {
                if (!isset($showheadline) || $showheadline != false) {
                    switch ($outputType) {
                        case 'xls':
                            $this->xlsRow++;
                            $this->sheet->write($this->xlsRow, 0, gT("Answer"));
                            $this->sheet->write($this->xlsRow, 1, gT("Count"));
                            $this->sheet->write($this->xlsRow, 2, gT("Percentage"));
                            break;

                        case 'pdf':

                            $headPDF = array();
                            $headPDF[] = array(gT("Answer"), gT("Count"), gT("Percentage"));

                            break;
                        case 'html':
                            //three columns
                            $bAnswer = true; // For view
                            $bSum = false;
                            $bShowCount = true;
                            $bShowPercentage = true;
                            break;
                        default:


                            break;
                    }

                    $showheadline = false;

                }
                //answer text
                $fname = "$al[1] ($al[0])";
            }

            //are there some results to play with?
            if ($results > 0) {
                //calculate percentage
                $gdata[] = ($row / $results) * 100;
            }
            //no results
            else {
                //no data!
                $gdata[] = "N/A";
            }

            //put absolute data into array
            $grawdata[] = $row;

            if (!(in_array('is_comment', $al) || in_array('is_no_answer', $al))) {
                $grawdata_percents[] = $row;
            }
            //put question title and code into array
            $label[] = $fname;

            //put only the code into the array
            $justcode[] = $al[0];

            //edit labels and put them into antoher array

            //first check if $tempcount is > 0. If yes, $row has been modified and $tempcount has the original count.
            if ($tempcount > -1) {
                $flatLabel = wordwrap(FlattenText("$al[1]"), 25, "\n");
                // If the flatten label is empty (like for picture, or HTML, etc.)
                // We replace it by the subquestion code
                if ($flatLabel == '') {
                    $flatLabel = $al[0];
                }

                // For legend
                $lbl[$flatLabel] = $tempcount;

            } else {
                $flatLabel = wordwrap(FlattenText("$al[1]"), 25, "\n");
                // If the flatten label is empty (like for picture, or HTML, etc.)
                // We replace it by the subquestion code
                if ($flatLabel == '') {
                    $flatLabel = $al[0];
                }

                // Duplicate labels can exist.
                // TODO: Support three or more duplicates.
                if (isset($lbl[$flatLabel])) {
                    $lbl[$flatLabel.' (2)'] = $row;
                } else {
                    $lbl[$flatLabel] = $row;
                }
            }



            // For Graph labels
            switch ($_POST['graph_labels']) {
                case 'qtext':
                    $aGraphLabels[] = $sFlatLabel = $flatLabel;
                break;

                case 'both':
                    $aGraphLabels[] = $sFlatLabel = empty($al[0]) ? $flatLabel : $al[0] . ': ' . $flatLabel;
                break;

                default:
                    $aGraphLabels[] = $sFlatLabel = empty($al[0]) ? $flatLabel : $al[0];
                break;
            }


            if (!(in_array('is_comment', $al) || in_array('is_no_answer', $al))) {
                $aGraphLabelsPercent[] = $sFlatLabel;
                $lblPercent[$flatLabel] = $lbl[$flatLabel];
            }

        }    //end foreach -> loop through answer data

        //no filtering of incomplete answers and NO multiple option questions
        //if ((incompleteAnsFilterState() != "complete") and ($outputs['qtype'] != "M") and ($outputs['qtype'] != "P"))
        //error_log("TIBO ".print_r($showaggregated_indice_table,true));
        if (($outputs['qtype'] != "M") and ($outputs['qtype'] != "P")) {
            //is the checkbox "Don't consider NON completed responses (only works when Filter incomplete answers is Disable)" checked?
            //if (isset($_POST[''noncompleted']) and ($_POST['noncompleted'] == 1) && (isset(Yii::app()->getConfig('showaggregateddata')) && Yii::app()->getConfig('showaggregateddata') == 0))
            // TIBO: TODO WE MUST SKIP THE FOLLOWING SECTION FOR TYPE A and 5 when
            // showaggreagated data is set and set to 1
            if (isset($_POST['noncompleted']) and ($_POST['noncompleted'] == 1)) {
                //counter
                $i = 0;

                while (isset($gdata[$i])) {
                    if (isset($showaggregated_indice_table[$i]) && $showaggregated_indice_table[$i] == "aggregated") {
// do nothing, we don't rewrite aggregated results
                        // or at least I don't know how !!! (lemeur)
                    } else {
                        //we want to have some "real" data here
                        if ($gdata[$i] != "N/A") {
                            //calculate percentage
                            $gdata[$i] = ($grawdata[$i] / $TotalCompleted) * 100;
                        }
                    }

                    //increase counter
                    $i++;

                }    //end while (data available)

            }    //end if -> noncompleted checked

            //noncompleted is NOT checked
            else {
                //calculate total number of incompleted records
                $TotalIncomplete = $results - $TotalCompleted;

                //output
                if ((incompleteAnsFilterState() != "complete")) {
                    $fname = gT("Not completed or Not displayed");
                } else {
                    $fname = gT("Not displayed");
                }

                //we need some data
                if ($results > 0) {
                    //calculate percentage
                    $gdata[] = ($TotalIncomplete / $results) * 100;
                }

                //no data :(
                else {
                    $gdata[] = "N/A";
                }

                //put data of incompleted records into array
                $grawdata[] = $TotalIncomplete;

                //put question title ("Not completed") into array
                $label[] = $fname;

                //put the code ("Not completed") into the array
                $justcode[] = $fname;

                //edit labels and put them into another array
                if ((incompleteAnsFilterState() != "complete")) {
                    $flatLabel = gT("Not completed or Not displayed");
                    // If the flatten label is empty (like for picture, or HTML, etc.)
                    // We replace it by the subquestion code
                    if ($flatLabel == '') {
                        $flatLabel = $al[0];
                    }

                    $lbl[$flatLabel] = $TotalIncomplete;
                } else {

                    $flatLabel = gT("Not displayed");
                    // If the flatten label is empty (like for picture, or HTML, etc.)
                    // We replace it by the subquestion code
                    if ($flatLabel == '') {
                        $flatLabel = $al[0];
                    }

                    $lbl[$flatLabel] = $TotalIncomplete;
                }
            }    //end else -> noncompleted NOT checked
        }

        // For multi question type, we have to check non completed with ALL sub question set to NULL
        if (($outputs['qtype'] == "M") or ($outputs['qtype'] == "P")) {
            $criteria = new CDbCriteria;
            foreach ($outputs['alist'] as $al) {
                $criteria->addCondition(Yii::app()->db->quoteColumnName($al[2])." IS NULL");
            }
            if (incompleteAnsFilterState() == "incomplete") {$criteria->addCondition("submitdate IS NULL"); } elseif (incompleteAnsFilterState() == "complete") {$criteria->addCondition("submitdate IS NOT NULL"); }
            $multiNotDisplayed = SurveyDynamic::model($surveyid)->count($criteria);
            if (isset($_POST['noncompleted']) and ($_POST['noncompleted'] == 1)) {
                //counter
                $i = 0;
                while (isset($gdata[$i])) {
                    //we want to have some "real" data here
                    if ($gdata[$i] != "N/A") {
                        //calculate percentage
                        if ($results > $multiNotDisplayed) {
                            $gdata[$i] = ($grawdata[$i] / ($results - $multiNotDisplayed)) * 100;
                        } else {
                            $gdata[$i] = "N/A";
                        }
                    }
                    $i++;
                }
            } else {
// Add a line with not displayed %
                if ($multiNotDisplayed > 0) {
                    if ((incompleteAnsFilterState() != "complete")) {
                        $fname = gT("Not completed or Not displayed");
                    } else {
                        $fname = gT("Not displayed");
                    }
                    $label[] = $fname;
                    $lbl[$fname] = $multiNotDisplayed;
                    //we need some data
                    if ($results > 0) {
                        //calculate percentage
                        $gdata[] = ($multiNotDisplayed / $results) * 100;
                    }
                    //no data :(
                    else {
                        $gdata[] = "N/A";
                    }
                    //put data of incompleted records into array
                    $grawdata[] = $multiNotDisplayed;
                }
            }
        }

        // Columns
        $statsColumns = $_POST['stats_columns'];

        switch ($statsColumns) {
            case "1":
                $nbcols      = "12";
                $canvaWidth  = "1150";
                $canvaHeight = "800";
            break;

            case "3":
                $nbcols = "4";
                $canvaWidth = "333";
                $canvaHeight = "500";
            break;

            default:
                $nbcols = "6";
                $canvaWidth = "500";
                $canvaHeight = "500";
            break;
        }

        //
        //counter
        $i = 0;
        //we need to know which item we are editing
        $itemcounter = 1;

        $aData['nbcols']          = $nbcols;
        $aData['canvaWidth']      = $canvaWidth;
        $aData['canvaHeight']     = $canvaHeight;
        $aData['outputs']         = (isset($outputs)) ? $outputs : '';
        $aData['bSum']            = (isset($bSum)) ? $bSum : false;
        $aData['bAnswer']         = (isset($bAnswer)) ? $bAnswer : false;
        $aData['bShowCount']      = (isset($bShowCount)) ? $bShowCount : false;
        $aData['bShowPercentage'] = (isset($bShowPercentage)) ? $bShowPercentage : false;
        $statisticsoutput         = Yii::app()->getController()->renderPartial('/admin/export/generatestats/_statisticsoutput_header', $aData, true);
        //loop through all available answers
        ////
        while (isset($gdata[$i])) {
            $aData['i'] = $i;
            ///// We'll render at the end of this loop statisticsoutput_answer

            //repeat header (answer, count, ...) for each new question
            unset($showheadline);


            /*
            * there are 3 colums:
            *
            * 1 (50%) = answer (title and code in brackets)
            * 2 (25%) = count (absolute)
            * 3 (25%) = percentage
            */

            /*
            * If there is a "browse" button in this label, let's make sure there's an extra row afterwards
            * to store the columnlist
            *
            * */
            if (strpos($label[$i], "statisticsbrowsebutton")) {
                $extraline = "<tr><td class='statisticsbrowsecolumn' colspan='3' style='display: none'>";
                if ($outputs['qtype'] == 'P') {
                    $extraline .= "<div class='statisticsbrowsecolumn' id='columnlist_{$ColumnName_RM[$i]}'></div></td></tr>\n";
                } else {
                    $extraline .= "<div class='statisticsbrowsecolumn' id='columnlist_{$sColumnName}'></div></td></tr>\n";
                }
            }

            //no data
            if ($gdata[$i] === "N/A") {
                switch ($outputType) {
                    case 'xls':
                        $label[$i] = flattenText($label[$i]);
                        $this->xlsRow++;
                        $this->sheet->write($this->xlsRow, 0, $label[$i]);
                        $this->sheet->writeNumber($this->xlsRow, 1, $grawdata[$i]);
                        $this->sheet->writeNumber($this->xlsRow, 2, $gdata[$i] / 100, $this->xlsPercents);
                        break;

                    case 'pdf':

                        $tablePDF[] = array(flattenText($label[$i]), $grawdata[$i], sprintf("%01.2f", $gdata[$i])."%", "");

                        break;
                    case 'html':
                        //output when having no data
                        /// _statisticsoutput_answer
                        $bNAgData = true;
                        if (isset($extraline)) {
                            $bNAgDataExtraLine = $extraline;
                        }
                        break;
                    default:

                        break;
                }

            }

            //data available
            else {
                //check if data should be aggregated
                if (Yii::app()->getConfig('showaggregateddata') == 1 && ($outputs['qtype'] == "5" || $outputs['qtype'] == "A")) {
                    //mark that we have done soemthing special here
                    $aggregated = true;

                    if (($results - $grawdata[5] - $TotalIncomplete) > 0) {
                        $percentage = $grawdata[$i] / ($results - $grawdata[5] - $TotalIncomplete) * 100; // Only answered
                    } else {
                        $percentage = 0;
                    }

                    switch ($itemcounter) {
                        case 1:
                            if (($results - $grawdata[5] - $TotalIncomplete) > 0) {
                                $aggregatedPercentage = ($grawdata[0] + $grawdata[1]) / ($results - $grawdata[5] - $TotalIncomplete) * 100;
                            } else {
                                $aggregatedPercentage = 0;
                            }
                            break;

                        case 3:
                            $aggregatedPercentage = $percentage;
                            break;

                        case 5:
                            if (($results - $grawdata[5] - $TotalIncomplete) > 0) {
                                $aggregatedPercentage = ($grawdata[3] + $grawdata[4]) / ($results - $grawdata[5] - $TotalIncomplete) * 100;
                            } else {
                                $aggregatedPercentage = 0;
                            }
                            break;

                        case 6:
                        case 7:
                            if (($results - $grawdata[5] - $TotalIncomplete) > 0) {
                                $percentage = $grawdata[$i] / $results * 100; // All results
                            } else {
                                $percentage = 0;
                            }
                            break;

                        default:
                            $aggregatedPercentage = 'na';
                            break;
                    }


                    switch ($outputType) {
                        case 'xls':
                            $label[$i] = flattenText($label[$i]);
                            $this->xlsRow++;
                            $this->sheet->write($this->xlsRow, 0, $label[$i]);
                            $this->sheet->writeNumber($this->xlsRow, 1, $grawdata[$i]);
                            $this->sheet->writeNumber($this->xlsRow, 2, $percentage / 100, $this->xlsPercents);
                            if ($aggregatedPercentage !== 'na') {
                                $this->sheet->writeNumber($this->xlsRow, 3, $aggregatedPercentage / 100, $this->xlsPercents);
                            }
                            break;

                        case 'pdf':
                            $label[$i] = flattenText($label[$i]);
                            if ($aggregatedPercentage !== 'na') {
                                $tablePDF[] = array($label[$i], $grawdata[$i], sprintf("%01.2f", $percentage)."%", sprintf("%01.2f", $aggregatedPercentage)."%");
                            } else {
                                $tablePDF[] = array($label[$i], $grawdata[$i], sprintf("%01.2f", $percentage)."%", "");
                            }
                            break;

                        case 'html':
                            //output percentage
                            $bNAgData = true;
                            if ($aggregatedPercentage !== 'na') {
                                $showAggregatedPercentage = true;
                            } else {
                                $showEmptyAggregatedPercentage = true;
                            }
                            break;

                        default:
                            break;
                    }

                    if ($itemcounter == 5) {
                        // new row "sum"
                        //calculate sum of items 1-5
                        $sumitems = $grawdata[0]
                        + $grawdata[1]
                        + $grawdata[2]
                        + $grawdata[3]
                        + $grawdata[4];

                        //special treatment for zero values
                        if ($sumitems > 0) {
                            $sumpercentage = "100.00";
                        } else {
                            $sumpercentage = "0";
                        }
                        //special treatment for zero values
                        if ($TotalCompleted > 0) {
                            $casepercentage = "100.00";
                        } else {
                            $casepercentage = "0";
                        }
                        switch ($outputType) {
                            case 'xls':


                                $footXLS[] = array(gT("Sum")." (".gT("Answers").")", $sumitems, $sumpercentage."%", $sumpercentage."%");
                                $footXLS[] = array(gT("Number of cases"), $TotalCompleted, $casepercentage."%", "");

                                $this->xlsRow++;
                                $this->sheet->write($this->xlsRow, 0, gT("Sum")." (".gT("Answers").")");
                                $this->sheet->writeNumber($this->xlsRow, 1, $sumitems);
                                $this->sheet->writeNumber($this->xlsRow, 2, $sumpercentage / 100, $this->xlsPercents);
                                $this->sheet->writeNumber($this->xlsRow, 3, $sumpercentage / 100, $this->xlsPercents);
                                $this->xlsRow++;
                                $this->sheet->write($this->xlsRow, 0, gT("Number of cases"));
                                $this->sheet->writeNumber($this->xlsRow, 1, $TotalCompleted);
                                $this->sheet->writeNumber($this->xlsRow, 2, $casepercentage / 100, $this->xlsPercents);

                                break;
                            case 'pdf':

                                $footPDF[] = array(gT("Sum")." (".gT("Answers").")", $sumitems, $sumpercentage."%", $sumpercentage."%");
                                $footPDF[] = array(gT("Number of cases"), $TotalCompleted, $casepercentage."%", "");

                                break;
                            case 'html':
                                    $bShowSumAnswer = true;
                                break;
                            default:


                                break;
                        }

                    }

                }    //end if -> show aggregated data

                //don't show aggregated data
                else {
                    switch ($outputType) {
                        case 'xls':
                            $label[$i] = flattenText($label[$i]);
                            $this->xlsRow++;
                            $this->sheet->write($this->xlsRow, 0, $label[$i]);
                            $this->sheet->writeNumber($this->xlsRow, 1, $grawdata[$i]);
                            $this->sheet->writeNumber($this->xlsRow, 2, $gdata[$i] / 100, $this->xlsPercents);
                            break;

                        case 'pdf':
                            $label[$i] = flattenText($label[$i]);
                            $tablePDF[] = array($label[$i], $grawdata[$i], sprintf("%01.2f", $gdata[$i])."%", "");

                            break;
                        case 'html':
                            //output percentage
                            $bNAgData = true;
                            if (isset($extraline)) {
                                $bNAgDataExtraLine = $extraline;
                            }

                            break;
                        default:


                            break;
                    }

                }

            }    //end else -> $gdata[$i] != "N/A"



            //increase counter
            $i++;

            $itemcounter++;

            //Clear extraline
            unset($extraline);


            // Convert grawdata_percent to percent

            if (isset($grawdata_percents)) {
                $pTotal = array_sum($grawdata_percents);
                if ($pTotal > 0) {
                    foreach ($grawdata_percents as $key => $data) {
                        $grawdata_percents[$key] = round(($data / $pTotal) * 100, 2);
                    }

                }
            } else {
                $grawdata_percents = array();
            }

            ///// HERE RENDER statisticsoutput_answer
            $aData['label']                = $label;
            $aData['grawdata']             = $grawdata;
            $aData['grawdata_percent']     = $grawdata_percents;
            $aData['gdata']                = $gdata;

            $aData['extraline']            = (isset($extraline)) ? $extraline : false;
            $aData['aggregated']           = (isset($aggregated)) ? $aggregated : false;
            $aData['aggregatedPercentage'] = (isset($aggregatedPercentage)) ? ($i < 6 ? $aggregatedPercentage : false) : false;
            $aData['sumitems']             = (isset($sumitems)) ? $sumitems : false;
            $aData['sumpercentage']        = (isset($sumpercentage)) ? $sumpercentage : false;
            $aData['TotalCompleted']       = (isset($TotalCompleted)) ? $TotalCompleted : false;
            $aData['casepercentage']       = (isset($casepercentage)) ? $casepercentage : false;

            $aData['bNAgData']                      = (isset($bNAgData)) ? $bNAgData : false;
            $aData['bNAgDataExtraLine']             = (isset($bNAgDataExtraLine)) ? $bNAgDataExtraLine : false;
            $aData['showAggregatedPercentage']      = (isset($showAggregatedPercentage)) ? $showAggregatedPercentage : false;
            $aData['showEmptyAggregatedPercentage'] = (isset($showEmptyAggregatedPercentage)) ? $showEmptyAggregatedPercentage : false;
            $aData['bShowSumAnswer']                = (isset($bShowSumAnswer)) ? $bShowSumAnswer : false;

            // Generate answer
            // _statisticsoutput_answer
            $statisticsoutput .= Yii::app()->getController()->renderPartial('/admin/export/generatestats/_statisticsoutput_answer', $aData, true);
            $extraline            = false;
            $aggregated           = false;
            $aggregatedPercentage = false;
            $sumitems             = false;
            $sumpercentage        = false;
            $TotalCompleted       = false;
            $casepercentage       = false;

            $bNAgData = false;
            $bNAgDataExtraLine = false;
            $showAggregatedPercentage = false;
            $showEmptyAggregatedPercentage = false;
            $bShowSumAnswer = false;



        }    //end while

        $aData['showaggregateddata'] = false;

        $aData['sumallitems']             = array_sum($grawdata);
        $statisticsoutput .= Yii::app()->getController()->renderPartial('/admin/export/generatestats/_statisticsoutput_gross_total', $aData, true);

        //only show additional values when this setting is enabled
        if (Yii::app()->getConfig('showaggregateddata') == 1) {
            //it's only useful to calculate standard deviation and arithmetic means for question types
            //5 = 5 Point Scale
            //A = Array (5 Point Choice)
            if ($outputs['qtype'] == "5" || $outputs['qtype'] == "A") {
                $stddev = 0;
                $stddevarray = array_slice($grawdata, 0, 5, true);
                $am = 0;

                $sumitems = $grawdata[0] + $grawdata[1] + $grawdata[2] + $grawdata[3] + $grawdata[4];

                //calculate arithmetic mean
                if (isset($sumitems) && $sumitems > 0) {


                    //calculate and round results
                    //there are always 5 items
                    for ($x = 0; $x < 5; $x++) {
                        //create product of item * value
                        $am += (($x + 1) * $stddevarray[$x]);
                    }

                    //prevent division by zero
                    if (isset($stddevarray) && array_sum($stddevarray) > 0) {
                        $am = round($am / array_sum($stddevarray), 2);
                    } else {
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



                    for ($j = 0; $j < 5; $j++) {
                        //1 = calculate difference between item and arithmetic mean
                        $diff = (($j + 1) - $am);

                        //2 = create square value of difference
                        $squarevalue = square($diff);

                        //3 = sum up square values and multiply them with the occurence
                        //prevent divison by zero
                        if ($squarevalue != 0 && $stddevarray[$j] != 0) {
                            $stddev += $squarevalue * $stddevarray[$j];
                        }

                    }

                    //4 = multiply result with 1 / (number of items (=5))
                    //There are two different formulas to calculate standard derivation
                    //$stddev = $stddev / array_sum($stddevarray);        //formula source: http://de.wikipedia.org/wiki/Standardabweichung

                    //prevent division by zero
                    if ((array_sum($stddevarray) - 1) != 0 && $stddev != 0) {
                        $stddev = $stddev / (array_sum($stddevarray) - 1); //formula source: http://de.wikipedia.org/wiki/Empirische_Varianz
                    } else {
                        $stddev = 0;
                    }

                    //5 = get root
                    $stddev = sqrt($stddev);
                    $stddev = round($stddev, 2);
                }
                switch ($outputType) {
                    case 'xls':
                        $this->xlsRow++;
                        $this->sheet->write($this->xlsRow, 0, gT("Arithmetic mean"));
                        $this->sheet->writeNumber($this->xlsRow, 1, $am);
                        $this->xlsRow++;
                        $this->sheet->write($this->xlsRow, 0, gT("Standard deviation"));
                        $this->sheet->writeNumber($this->xlsRow, 1, $stddev);
                        break;

                    case 'pdf':

                        $tablePDF[] = array(gT("Arithmetic mean"), $am, '', '');
                        $tablePDF[] = array(gT("Standard deviation"), $stddev, '', '');
                        break;

                    case 'html':
                        //calculate standard deviation
                        $aData['am'] = $am;
                        $aData['stddev'] = $stddev;
                        $aData['bShowSumAnswer'] = true;
                        $aData['sumitems'] = $results;
                        $statisticsoutput .= Yii::app()->getController()->renderPartial('/admin/export/generatestats/_statisticsoutput_arithmetic', $aData, true);
                        break;
                    default:


                        break;
                }
            }
        }

        if ($outputType == 'pdf') {
            $tablePDF = array_merge_recursive($tablePDF, $footPDF);
            if (!isset($headPDF)) {
                // @TODO: Why is $headPDF sometimes undefined here?
                $headPDF = array();
            }
            $this->pdf->headTable($headPDF, $tablePDF);
            if (isset($headPDF2)) {
                $this->pdf->headTable($headPDF2, $tablePDF2);
            }
        }

        if ($outputType == 'xls' && (isset($headXLS) || isset($tableXLS))) {
            if (isset($headXLS)) {
                $this->xlsRow++;
                $this->xlsRow++;
                foreach ($headXLS as $aRow) {
                    $this->xlsRow++;
                    $iColumn = 0;
                    foreach ($aRow as $sValue) {
                        $this->sheet->write($this->xlsRow, $iColumn, $sValue, $this->formatBold);
                        $iColumn++;
                    }
                }
            }
            if (isset($tableXLS)) {
                foreach ($tableXLS as $aRow) {
                    $this->xlsRow++;
                    $iColumn = 0;
                    foreach ($aRow as $sValue) {
                        $this->sheet->write($this->xlsRow, $iColumn, $sValue);
                        $iColumn++;
                    }
                }

            }
        }

        // _statisticsoutput_graphs.php

        //-------------------------- PCHART OUTPUT ----------------------------
        list($qsid, $qgid, $qqid) = explode("X", $rt, 3);
        $qsid = $surveyid;
        $aattr = QuestionAttribute::model()->getQuestionAttributes($outputs['parentqid']);

        //PCHART has to be enabled and we need some data
        //
        if ($usegraph == 1) {
            $bShowGraph = $aattr["statistics_showgraph"] == "1";
            $bAllowPieChart = ($outputs['qtype'] != "M" && $outputs['qtype'] != "P");
            $bAllowMap = (isset($aattr["location_mapservice"]) && $aattr["location_mapservice"] == "1");
            $bShowMap = ($bAllowMap && $aattr["statistics_showmap"] == "1");
            $bShowPieChart = ($bAllowPieChart && (isset($aattr["statistics_graphtype"]) && $aattr["statistics_graphtype"] == "1"));

            $astatdata[$rt] = array(
                'id' => $rt,
                'sg' => $bShowGraph,
                'ap' => $bAllowPieChart,
                'am' => $bAllowMap,
                'sm' => $bShowMap,
                'sp' => $bShowPieChart
            );

            $stats = Yii::app()->session['stats'];
            $stats[$rt] = array(
                'lbl' => $lbl,
                'gdata' => $gdata,
                'grawdata' => $grawdata
            );
            Yii::app()->session['stats'] = $stats;

            if ($bShowGraph == true) {

                $cachefilename = '';
                if ($outputType == 'xls' || $outputType == 'pdf') {
                    // This takes care of graph_lables for PDF output (previous fix only supported HTML).
                    $graphLbl = [];
                    foreach ($outputs['alist'] as $al) {
                        switch ($_POST['graph_labels']) {
                            case 'qtext':
                                $graphLbl[] = $al[1];
                                break;
                            case 'both':
                                if ($al[0] == "") {
                                    $graphLbl[] =  gT("No answer") . ': ' . $al[1];
                                } else {
                                    $graphLbl[] = $al[0] . ': ' . $al[1];
                                }
                                break;
                            default:
                                if ($al[0] == "") {
                                    $graphLbl[] =  gT("No answer");
                                } else {
                                    $graphLbl[] = $al[0];
                                }
                                break;
                        }
                    }
                    // One extra label for "Not completed or not displayed".
                    if (count($lbl) == count($outputs['alist']) + 1) {
                        end($lbl);
                        $graphLbl[] = key($lbl);
                        reset($lbl);
                    }
                    $cachefilename = createChart($qqid, $qsid, $bShowPieChart, $graphLbl, $gdata, $grawdata, $MyCache, $sLanguage, $outputs['qtype']);
                }

                if ($cachefilename || $outputType == 'html') {
                    // Add the image only if constructed
                    //introduce new counter
                    if (!isset($ci)) {$ci = 0; }

                    //increase counter, start value -> 1
                    $ci++;
                    switch ($outputType) {
                        case 'xls':
                            /**
                             * No Image for Excel...
                             */

                            break;
                        case 'pdf':

                            $this->pdf->AddPage('P', 'A4');

                            $this->pdf->titleintopdf($pdfTitle, $titleDesc);
                            $this->pdf->Image($tempdir."/".$cachefilename, 0, 70, 180, 0, '', Yii::app()->getController()->createUrl("admin/survey/sa/view/surveyid/".$surveyid), 'B', true, 150, 'C', false, false, 0, true);

                            break;
                        case 'html':

                            if (isset($aattr["statistics_graphtype"])) {
                                $req_chart_type = $aattr["statistics_graphtype"];
                            }

                            //// If user forced the chartype from statistics_view
                            if (isset($_POST['charttype']) && $_POST['charttype'] != 'default') {
                                $req_chart_type = $_POST['charttype'];
                            }

                            //// The value of the select box in the question advanced setting is numerical. So we need to translate it.
                            if (isset($req_chart_type)) {
                                switch ($req_chart_type) {
                                    case '1':
                                            $charttype = "Pie";
                                        break;

                                    case '2':
                                        $charttype = "Radar";
                                        break;

                                    case '3':
                                        $charttype = "Line";
                                        break;

                                    case '4':
                                        $charttype = "PolarArea";
                                        break;

                                    case '5':
                                        $charttype = "Doughnut";
                                        break;

                                    default:
                                        $charttype = "Bar";
                                        break;
                                }
                            }

                            //// Here the 72 colors of the original limesurvey palette.
                            //// This could be change by some user palette coming from database.
                            $COLORS_FOR_SURVEY = array('20,130,200', '232,95,51', '34,205,33', '210,211,28', '134,179,129', '201,171,131', '251,231,221', '23,169,161', '167,187,213', '211,151,213', '147,145,246', '147,39,90', '250,250,201', '201,250,250', '94,0,94', '250,125,127', '0,96,201', '201,202,250', '0,0,127', '250,0,250', '250,250,0', '0,250,250', '127,0,127', '127,0,0', '0,125,127', '0,0,250', '0,202,250', '201,250,250', '201,250,201', '250,250,151', '151,202,250', '251,149,201', '201,149,250', '250,202,151', '45,96,250', '45,202,201', '151,202,0', '250,202,0', '250,149,0', '250,96,0', '184,230,115', '102,128,64', '220,230,207', '134,191,48', '184,92,161', '128,64,112', '230,207,224', '191,48,155', '230,138,115', '128,77,64', '230,211,207', '191,77,48', '80,161,126', '64,128,100', '207,230,220', '48,191,130', '25,25,179', '18,18,125', '200,200,255', '145,145,255', '255,178,0', '179,125,0', '255,236,191', '255,217,128', '255,255,0', '179,179,0', '255,255,191', '255,255,128', '102,0,153', '71,0,107', '234,191,255', '213,128,255');

                            //// $lbl is generated somewhere upthere by the original code. We translate it for chartjs.
                                $labels = array();
                                foreach ($lbl as $name => $lb) {
                                                                    $labels[] = $name;
                                }

                                if (isset($lblPercent)) {
                                    foreach ($lblPercent as  $name => $lb) {
                                                                        $labels_percent[] = $name;
                                    }
                                } else {
                                    $labels_percent = array();
                                }

                            break;
                        default:
                            break;
                    }
                }
            }
        }

        //close table/output
        if ($outputType == 'html') {
            // show this block only when we show graphs and are not in the public statics controller
            if ($usegraph == 1 && $bShowGraph && get_class(Yii::app()->getController()) !== 'Statistics_userController') {
                // We clean the labels
                $iMaxLabelLength = 0;

                // We clean the labels

                // Labels for graphs
                $iMaxLabelLength = 0;

                // add "Not completed or Not displayed" label if missing
                if (isset($_POST['noncompleted']) && $_POST['noncompleted'] == 0 && count($labels) > count($aGraphLabels)){
                    $aGraphLabels[] = gT("Not completed or Not displayed");
                }

                foreach ($aGraphLabels as $key => $label) {
                    $cleanLabel = $label;
                    $cleanLabel = viewHelper::flatEllipsizeText($cleanLabel, true, 20);
                    $graph_labels[$key] = $cleanLabel;
                    $iMaxLabelLength = (strlen($cleanLabel) > $iMaxLabelLength) ?strlen($cleanLabel) : $iMaxLabelLength;
                }

                if (isset($aGraphLabelsPercent)) {
                    foreach ($aGraphLabelsPercent as $key => $label) {
                        $cleanLabel = $label;
                        $cleanLabel = viewHelper::flatEllipsizeText($cleanLabel, true, 20);
                        $graph_labels_percent[$key] = $cleanLabel;
                    }
                } else {
                    $graph_labels_percent = array();
                }

                $iCanvaHeight = $iMaxLabelLength * 3;
                $aData['iCanvaHeight'] = ($iCanvaHeight > 150) ? $iCanvaHeight : 150;

                $qqid = str_replace('#', '_', $qqid);
                $aData['rt'] = $rt;
                $aData['qqid'] = $qqid;
                $aData['graph_labels'] = $graph_labels;
                $aData['graph_labels_percent'] = $labels_percent;
                $aData['labels'] = $labels;
                //$aData['COLORS_FOR_SURVEY'] = COLORS_FOR_SURVEY;
                $aData['charttype'] = (isset($charttype)) ? $charttype : 'Bar';
                $aData['sChartname'] = '';
                $aData['grawdata'] = $grawdata;
                $aData['color'] = rand(0, 70);
                $aData['COLORS_FOR_SURVEY'] = $COLORS_FOR_SURVEY;
                $aData['lbl'] = $lbl;
                ///

                $statisticsoutput .= Yii::app()->getController()->renderPartial('/admin/export/generatestats/_statisticsoutput_graphs', $aData, true);
                $statisticsoutput_footer .= Yii::app()->getController()->renderPartial('/admin/export/generatestats/_statisticsoutput_footer', $aData, true);
            }
            $statisticsoutput .= "</table></div><!-- in statistics helper --> \n";
        }
            $statisticsoutput = $statisticsoutput.$statisticsoutput_footer."</script>";
        return array("statisticsoutput"=>$statisticsoutput, "pdf"=>$this->pdf, "astatdata"=>$astatdata);

    }

    /**
     * Generate simple statistics
     * @param string[] $allfields
     * @return string
     */
        public function generate_simple_statistics($surveyid, $allfields, $q2show = 'all', $usegraph = 0, $outputType = 'pdf', $pdfOutput = 'I', $sLanguageCode = null, $browse = true)
        {

                $aStatisticsData = array();
                $survey = Survey::model()->findByPk($surveyid);

                Yii::import('application.helpers.surveytranslator_helper', true);
                Yii::import('application.third_party.ar-php.Arabic', true);

                //pick the best font file if font setting is 'auto'
                if (is_null($sLanguageCode)) {
                    $sLanguageCode = $survey->language;
                }

                // Set language for questions and answers to base language of this survey
                $language = $survey->language;

                // This gets all the 'to be shown questions' from the POST and puts these into an array
                $summary = $q2show;

                /**
                 * Start generating
                 */

                //count number of answers
                $query = "SELECT count(*) FROM {{survey_$surveyid}}";

                //get me some data Scotty
                $results = $total = Yii::app()->db->createCommand($query)->queryScalar();
                $percent = '100';
                $sql = null;

                //only continue if we have something to output
                $bBrowse = true;

                $aData['results'] = $results;
                $aData['total'] = $total;
                $aData['percent'] = $percent;
                $aData['browse'] = $bBrowse;
                $aData['surveyid'] = $surveyid;
                $aData['sql'] = $sql;

                $sOutputHTML = '';

            //let's run through the survey
            $runthrough = $summary;

            //START Chop up fieldname and find matching questions

            //loop through all selected questions
            $count = 0;
            $rowOpened = 0;
            foreach ($runthrough as $rt) {
                ////Step 1: Get information about this response field (SGQA) for the summary
                $outputs = $this->buildOutputList($rt, $language, $surveyid, $outputType, $sql, $sLanguageCode);
                //$sOutputHTML .= $outputs['statisticsoutput']; // Nothing interesting for us in this output
                //2. Collect and Display results #######################################################################

                if (isset($outputs['alist']) && $outputs['alist']) {
//Make sure there really is an answerlist, and if so:
                    $count = $count + 1;
                    if ($count == 1) {
                        $sOutputHTML .= '<div class="row">';
                        $rowOpened = 1;
                    }

                    $display = $this->displaySimpleResults($outputs, $results, $rt, $outputType, $surveyid, $sql, $usegraph, $browse, $sLanguageCode);
                    $sOutputHTML .= $display['statisticsoutput'];
                    $aStatisticsData = array_merge($aStatisticsData, $display['astatdata']);

                    if ($count == 3) {
                        $sOutputHTML .= '</div>';
                        $rowOpened = 0;
                        $count = 0;
                    }

                }    //end if -> collect and display results


                //Delete Build Outputs data
                unset($outputs);
                unset($display);



            }

            if ($rowOpened) {
                    $sOutputHTML .= '</div>';
            }

            $sOutputHTML .= '</div>';

                $sGoogleMapsAPIKey = trim(Yii::app()->getConfig("googleMapsAPIKey"));
                if ($sGoogleMapsAPIKey != '') {
                    $sGoogleMapsAPIKey = '&key='.$sGoogleMapsAPIKey;
                }
                $sSSL = '';
                if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off") {
                    $sSSL = 's';
                }
                $sOutputHTML .= "<script type=\"text/javascript\" src=\"http{$sSSL}://maps.googleapis.com/maps/api/js?sensor=false$sGoogleMapsAPIKey\"></script>\n"
                ."<script type=\"text/javascript\">var site_url='".Yii::app()->baseUrl."';var temppath='".Yii::app()->getConfig("tempurl")."';var imgpath='".Yii::app()->getConfig('adminimageurl')."';var aStatData=".ls_json_encode($aStatisticsData)."</script>";


                return $sOutputHTML;
        }

    /**
     * Generates statistics with subviews
     */
    public function generate_html_chartjs_statistics($surveyid, $allfields, $q2show = 'all', $usegraph = 0, $outputType = 'pdf', $pdfOutput = 'I', $sLanguageCode = null, $browse = true)
    {
        $aStatisticsData = array();
        $survey = Survey::model()->findByPk($surveyid);

        //astatdata generates data for the output page's javascript so it can rebuild graphs on the fly
        //load surveytranslator helper

        Yii::import('application.helpers.surveytranslator_helper', true);
        Yii::import('application.third_party.ar-php.Arabic', true);

        //pick the best font file if font setting is 'auto'
        if (is_null($sLanguageCode)) {
            $sLanguageCode = $survey->language;
        }

        //no survey ID? -> come and get one
        if (!isset($surveyid)) {$surveyid = returnGlobal('sid'); }

        // Set language for questions and answers to base language of this survey
        $language = $sLanguageCode;

        if ($q2show == 'all') {
            $summarySql = " SELECT gid, parent_qid, qid, type "
            ." FROM {{questions}} WHERE parent_qid=0"
            ." AND sid=$surveyid ";

            $summaryRs = Yii::app()->db->createCommand($summarySql)->query()->readAll();

            foreach ($summaryRs as $field) {
                $myField = $surveyid."X".$field['gid']."X".$field['qid'];

                // Multiple choice get special treatment
                if ($field['type'] == "M") {$myField = "M".$myField; }
                if ($field['type'] == "P") {$myField = "P".$myField; }
                //numerical input will get special treatment (arihtmetic mean, standard derivation, ...)
                if ($field['type'] == "N") {$myField = "N".$myField; }
                if ($field['type'] == "|") {$myField = "|".$myField; }
                if ($field['type'] == "Q") {$myField = "Q".$myField; }
                // textfields get special treatment
                if ($field['type'] == "S" || $field['type'] == "T" || $field['type'] == "U") {$myField = "T".$myField; }
                //statistics for Date questions are not implemented yet.
                if ($field['type'] == "D") {$myField = "D".$myField; }
                if ($field['type'] == "F" || $field['type'] == "H") {
                    //Get answers. We always use the answer code because the label might be too long elsewise
                    $query = "SELECT code, answer FROM {{answers}} WHERE qid='".$field['qid']."' AND scale_id=0 AND language='{$language}' ORDER BY sortorder, answer";
                    $result = Yii::app()->db->createCommand($query)->query();

                    //check all the answers
                    foreach ($result->readAll() as $row) {
                        $row = array_values($row);
                        $myField = "$myField{$row[0]}";
                    }
                    //$myField = "{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]";

                }
                if ($q2show == 'all') {
                                    $summary[] = $myField;
                }

                //$allfields[]=$myField;
            }
        } else {
            // This gets all the 'to be shown questions' from the POST and puts these into an array
            if (!is_array($q2show)) {
                            $summary = returnGlobal('summary');
            } else {
                            $summary = $q2show;
            }

            //print_r($_POST);
            //if $summary isn't an array we create one
            if (isset($summary) && !is_array($summary)) {
                $summary = explode("+", $summary);
            }
        }

        /**
         * Start generating
         */
        $selects = buildSelects($allfields, $surveyid, $language);

        //count number of answers
        $query = "SELECT count(*) FROM {{survey_$surveyid}}";

        //if incompleted answers should be filtert submitdate has to be not null
        if (incompleteAnsFilterState() == "incomplete") {$query .= " WHERE submitdate is null"; } elseif (incompleteAnsFilterState() == "complete") {$query .= " WHERE submitdate is not null"; }
        $total = Yii::app()->db->createCommand($query)->queryScalar();

        //are there any filters that have to be taken care of?
        if (isset($selects) && $selects) {
            //Save the filters to session for use in browsing text & other features (statistics.php function listcolumn())
            Yii::app()->session['statistics_selects_'.$surveyid] = $selects;
            //filter incomplete answers?
            if (incompleteAnsFilterState() == "complete" || incompleteAnsFilterState() == "incomplete") {$query .= " AND "; } else {$query .= " WHERE "; }

            //add filter criteria to SQL
            $query .= implode(" AND ", $selects);
        }

        //get me some data Scotty
        $results = Yii::app()->db->createCommand($query)->queryScalar();

        if ($total) {
            $percent = sprintf("%01.2f", ($results / $total) * 100);
        }

        //put everything from $selects array into a string connected by AND
        //This string ($sql) can then be passed on to other functions so you can
        //browse these results
        if (isset ($selects) && $selects) {
            $sql = implode(" AND ", $selects);
        } elseif (!empty($newsql)) {
            $sql = $newsql;
        }

        if (!isset($sql) || !$sql) {
            $sql = null;
        }

        //only continue if we have something to output
        $bBrowse = false;
        if ($results > 0) {
            if ($outputType == 'html' && $browse === true && Permission::model()->hasSurveyPermission($surveyid, 'responses', 'read')) {
                //add a buttons to browse results
                $bBrowse = true;
            }
        }    //end if (results > 0)

        /**
         * Show Summary results
         * The $summary array contains each fieldname that we want to display statistics for
         */

        $aData['results'] = $results;
        $aData['total'] = $total;
        $aData['percent'] = (isset($percent)) ? $percent : ''; // If nobody passed the survey
        $aData['browse'] = $bBrowse;
        $aData['surveyid'] = $surveyid;
        $aData['sql'] = $sql;

        // application/views/admin/export/generatestats/_header.php
        $sOutputHTML = Yii::app()->getController()->renderPartial('/admin/export/generatestats/_header', $aData, true);


        if (isset($summary) && $summary) {
            //let's run through the survey
            $runthrough = $summary;

            //START Chop up fieldname and find matching questions

            //loop through all selected questions
            foreach ($runthrough as $rt) {

                //Step 1: Get information about this response field (SGQA) for the summary
                $outputs = $this->buildOutputList($rt, $language, $surveyid, $outputType, $sql, $sLanguageCode);
                $sOutputHTML .= $outputs['statisticsoutput'];
                //2. Collect and Display results #######################################################################
                if (isset($outputs['alist']) && $outputs['alist']) {
//Make sure there really is an answerlist, and if so:
                    $display = $this->displayResults($outputs, $results, $rt, $outputType, $surveyid, $sql, $usegraph, $browse, $sLanguageCode);
                    $sOutputHTML .= $display['statisticsoutput'];
                    $aStatisticsData = array_merge($aStatisticsData, $display['astatdata']);
                }    //end if -> collect and display results


                //Delete Build Outputs data
                unset($outputs);
                unset($display);
            }    // end foreach -> loop through all questions

            //output
            if ($outputType == 'html') {
                        $sOutputHTML .= "<br />&nbsp;\n";
            }

        }    //end if -> show summary results

        $sGoogleMapsAPIKey = trim(Yii::app()->getConfig("googleMapsAPIKey"));
        if ($sGoogleMapsAPIKey != '') {
            $sGoogleMapsAPIKey = '&key='.$sGoogleMapsAPIKey;
        }
        $sSSL = '';
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off") {
            $sSSL = 's';
        }
        $sOutputHTML .= "<script type=\"text/javascript\" src=\"http{$sSSL}://maps.googleapis.com/maps/api/js?sensor=false$sGoogleMapsAPIKey\"></script>\n"
        ."<script type=\"text/javascript\">var site_url='".Yii::app()->baseUrl."';var temppath='".Yii::app()->getConfig("tempurl")."';var imgpath='".Yii::app()->getConfig('adminimageurl')."';var aStatData=".ls_json_encode($aStatisticsData)."</script>";


        return $sOutputHTML;
    }

    /**
     * Generates statistics
     *
     * @param int $surveyid The survey id
     * @param mixed $allfields
     * @param mixed $q2show
     * @param integer $usegraph
     * @param string $outputType Optional - Can be xls, html or pdf - Defaults to pdf
     * @param mixed $browse  Show browse buttons
     * @return string
     */
    public function generate_statistics($surveyid, $allfields, $q2show = 'all', $usegraph = 0, $outputType = 'pdf', $outputTarget = 'I', $sLanguageCode = null, $browse = true)
    {
        $survey = Survey::model()->findByPk($surveyid);
        $aStatisticsData = array(); //astatdata generates data for the output page's javascript so it can rebuild graphs on the fly
        //load surveytranslator helper
        Yii::import('application.helpers.surveytranslator_helper', true);
        Yii::import('application.third_party.ar-php.Arabic', true);

        $sOutputHTML = ""; //This string carries all the actual HTML code to print.
        $sTempDir = Yii::app()->getConfig("tempdir");

        $this->pdf = array(); //Make sure $this->pdf exists - it will be replaced with an object if a $this->pdf is actually being created

        //pick the best font file if font setting is 'auto'
        if (is_null($sLanguageCode)) {
            $sLanguageCode = $survey->language;
        }

        //we collect all the html-output within this variable
        $sOutputHTML = '';
        /**
         * $outputType: html || pdf ||
         */
        /**
         * get/set Survey Details
         */

        //no survey ID? -> come and get one
        if (!isset($surveyid)) {$surveyid = returnGlobal('sid'); }

        // Set language for questions and answers to base language of this survey
        $language = $sLanguageCode;

        if ($q2show == 'all') {
            $summarySql = " SELECT gid, parent_qid, qid, type "
            ." FROM {{questions}} WHERE parent_qid=0"
            ." AND sid=$surveyid ";

            $summaryRs = Yii::app()->db->createCommand($summarySql)->query()->readAll();

            foreach ($summaryRs as $field) {
                $myField = $surveyid."X".$field['gid']."X".$field['qid'];

                // Multiple choice get special treatment
                //numerical input will get special treatment (arihtmetic mean, standard derivation, ...)
                // textfields get special treatment
                //statistics for Date questions are not implemented yet.
                // See buildOutputList for special treatment
                $specialQuestionTypes = array("M", "P", "T", "S", "Q", "R", "|", "", "N", "K", "D");
                if (in_array($field['type'], $specialQuestionTypes)) {
                    $myField = $field['type'].$myField;
                }

                if ($field['type'] == "F" || $field['type'] == "H") {
                    //Get answers. We always use the answer code because the label might be too long elsewise
                    $query = "SELECT code, answer FROM {{answers}} WHERE qid='".$field['qid']."' AND scale_id=0 AND language='{$language}' ORDER BY sortorder, answer";
                    $result = Yii::app()->db->createCommand($query)->query();

                    //check all the answers
                    foreach ($result->readAll() as $row) {
                        $row = array_values($row);
                        $myField = "$myField{$row[0]}";
                    }
                    //$myField = "{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]";


                }

                $summary[] = $myField;

                //$allfields[]=$myField;
            }
        } else {
            // This gets all the 'to be shown questions' from the POST and puts these into an array
            if (!is_array($q2show)) {
                            $summary = returnGlobal('summary');
            } else {
                            $summary = $q2show;
            }

            //print_r($_POST);
            //if $summary isn't an array we create one
            if (isset($summary) && !is_array($summary)) {
                $summary = explode("+", $summary);
            }
        }

        /**
         * pdf Config
         */
        if ($outputType == 'pdf') {
            //require_once('classes/tcpdf/mypdf.php');
            Yii::import('application.libraries.admin.pdf', true);
            Yii::import('application.helpers.pdfHelper');
            $aPdfLanguageSettings = pdfHelper::getPdfLanguageSettings($language);

            // create new PDF document
            $this->pdf = new pdf();

            $surveyInfo = getSurveyInfo($surveyid, $language);

            // set document information
            $this->pdf->SetCreator(PDF_CREATOR);
            $this->pdf->SetAuthor('LimeSurvey');
            $this->pdf->SetTitle(sprintf(gT("Statistics survey %s"), $surveyid));
            $this->pdf->SetSubject($surveyInfo['surveyls_title']);
            $this->pdf->SetKeywords('LimeSurvey,'.gT("Statistics").', '.sprintf(gT("Survey %s"), $surveyid));
            $this->pdf->SetDisplayMode('fullpage', 'two');
            $this->pdf->setLanguageArray($aPdfLanguageSettings['lg']);

            // set header and footer fonts
            $this->pdf->setHeaderFont(Array($aPdfLanguageSettings['pdffont'], '', PDF_FONT_SIZE_MAIN));
            $this->pdf->setFooterFont(Array($aPdfLanguageSettings['pdffont'], '', PDF_FONT_SIZE_DATA));

            // set default header data
            // Since png crashes some servers (and we can not try/catch that) we use .gif (or .jpg) instead
            //$headerlogo = '$this->pdf';

            $headerlogo = '';
            $logowidth = 10;
            $at = AdminTheme::getInstance();
            if (!defined('K_PATH_IMAGES')) {
                define($at->path.DIRECTORY_SEPARATOR.'images');
            }
            $path = array($at->path, 'images', 'logo_statistics.jpg');
            if (file_exists(implode(DIRECTORY_SEPARATOR, $path))) {
                $headerlogo = 'logo_statistics.jpg';
                $logowidth= 85;
            }

            $this->pdf->SetHeaderData($headerlogo, $logowidth, gT("Quick statistics", 'unescaped'), gT("Survey")." ".$surveyid." '".flattenText($surveyInfo['surveyls_title'], false, true, 'UTF-8')."'");
            $this->pdf->SetFont($aPdfLanguageSettings['pdffont'], '', $aPdfLanguageSettings['pdffontsize']);
            // set default monospaced font
            $this->pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        }
        if ($outputType == 'xls') {
            /**
             * Initiate the Spreadsheet_Excel_Writer
             */
            require_once(APPPATH.'/third_party/pear/Spreadsheet/Excel/Writer.php');

            if ($outputTarget == 'F') {
                $sFileName = $sTempDir.'/statistic-survey'.$surveyid.'.xls';
                $this->workbook = new Spreadsheet_Excel_Writer($sFileName);
            } else {
                $this->workbook = new Spreadsheet_Excel_Writer();
            }
            $this->workbook->setVersion(8);
            // Inform the module that our data will arrive as UTF-8.
            // Set the temporary directory to avoid PHP error messages due to open_basedir restrictions and calls to tempnam("", ...)
            $this->workbook->setTempDir($sTempDir);

            // Inform the module that our data will arrive as UTF-8.
            // Set the temporary directory to avoid PHP error messages due to open_basedir restrictions and calls to tempnam("", ...)
            if (!empty($sTempDir)) {
                $this->workbook->setTempDir($sTempDir);
            }

            if ($outputTarget != 'F') {
                $this->workbook->send('statistic-survey'.$surveyid.'.xls');
            }

            // Creating the first worksheet
            $this->sheet = $this->workbook->addWorksheet(utf8_decode('results-survey'.$surveyid));
            $this->xlsPercents = $this->workbook->addFormat();
            $this->xlsPercents->setNumFormat('0.00%');
            $this->formatBold = $this->workbook->addFormat(array('Bold'=>1));
            $this->sheet->setInputEncoding('utf-8');
            $this->sheet->setColumn(0, 20, 20);
        }
        /**
         * Start generating
         */



        $selects = buildSelects($allfields, $surveyid, $language);

        //count number of answers
        $query = "SELECT count(*) FROM {{survey_$surveyid}}";

        //if incompleted answers should be filtert submitdate has to be not null
        if (incompleteAnsFilterState() == "incomplete") {$query .= " WHERE submitdate is null"; } elseif (incompleteAnsFilterState() == "complete") {$query .= " WHERE submitdate is not null"; }
        $total = Yii::app()->db->createCommand($query)->queryScalar();

        //are there any filters that have to be taken care of?
        if (isset($selects) && $selects) {
            //Save the filters to session for use in browsing text & other features (statistics.php function listcolumn())
            Yii::app()->session['statistics_selects_'.$surveyid] = $selects;
            //filter incomplete answers?
            if (incompleteAnsFilterState() == "complete" || incompleteAnsFilterState() == "incomplete") {$query .= " AND "; } else {$query .= " WHERE "; }

            //add filter criteria to SQL
            $query .= implode(" AND ", $selects);
        }


        //get me some data Scotty
        $results = Yii::app()->db->createCommand($query)->queryScalar();

        if ($total) {
            $percent = sprintf("%01.2f", ($results / $total) * 100);

        }
        switch ($outputType) {
            case "xls":
                $this->xlsRow = 0;
                $this->sheet->write($this->xlsRow, 0, gT("Number of records in this query:", 'unescaped'));
                $this->sheet->writeNumber($this->xlsRow, 1, $results);
                $this->xlsRow++;
                $this->sheet->write($this->xlsRow, 0, gT("Total records in survey:", 'unescaped'));
                $this->sheet->writeNumber($this->xlsRow, 1, $total);

                if ($total) {
                    $this->xlsRow++;
                    $this->sheet->write($this->xlsRow, 0, gT("Percentage of total:", 'unescaped'));
                    $this->sheet->writeNumber($this->xlsRow, 1, $results / $total, $this->xlsPercents);
                }

                break;

            case 'pdf':
                // add summary to pdf
                $array = array(
                    array(gT("Number of records in this query:", 'unescaped'), $results),
                    array(gT("Total records in survey:", 'unescaped'), $total)
                );
                if ($total) {
                    $array[] = array(gT("Percentage of total:", 'unescaped'), $percent."%");
                }
                $this->pdf->AddPage('P', ' A4');
                $this->pdf->Bookmark(gT("Results", 'unescaped'), 0, 0);
                $this->pdf->titleintopdf(gT("Results", 'unescaped'), gT("Survey", 'unescaped')." ".$surveyid);
                $this->pdf->tableintopdf($array);
                break;

            case 'html':

                $sOutputHTML .= '<div style="clear: both; margin-bottom: 10px;"></div><div class="jumbotron message-box">';
                $sOutputHTML .= '<h2>'.gT("Results")."</h2>";
                $sOutputHTML .= '<p>'.gT("Number of records in this query:").''.$results.'</p>';
                $sOutputHTML .= '<p>'.gT("Total records in survey:").''.$total.'</p>';

                if ($total) {
                    $percent = sprintf("%01.2f", ($results / $total) * 100);
                    $sOutputHTML .= "<p>".gT("Percentage of total:").''.$percent.'%</p>';
                }

                break;
            default:


                break;
        }

        //put everything from $selects array into a string connected by AND
        //This string ($sql) can then be passed on to other functions so you can
        //browse these results
        if (isset ($selects) && $selects) {$sql = implode(" AND ", $selects); } elseif (!empty($newsql)) {$sql = $newsql; }

        if (!isset($sql) || !$sql) {
            $sql = null;
        }

        //only continue if we have something to output
        if ($results > 0) {
            if ($outputType == 'html' && $browse === true && Permission::model()->hasSurveyPermission($surveyid, 'responses', 'read')) {
                //add a buttons to browse results
                $sOutputHTML .= CHtml::form(array("admin/responses/sa/browse/surveyid/{$surveyid}"), 'post', array('target'=>'_blank'))."\n"
                ."\t\t<p>"
                ."\t\t\t<input type='submit' class='btn btn-default' value='".gT("Browse")."'  />\n"
                ."\t\t\t<input type='hidden' name='sid' value='$surveyid' />\n"
                ."\t\t\t<input type='hidden' name='sql' value=\"$sql\" />\n"
                ."\t\t\t<input type='hidden' name='subaction' value='all' />\n"
                ."\t\t</p>"
                ."\t\t</form>\n";
            }
        }    //end if (results > 0)
        $sOutputHTML .= '</div>';

        /* Show Summary results
        * The $summary array contains each fieldname that we want to display statistics for
        *
        * */

        if (isset($summary) && $summary) {
            //let's run through the survey
            $runthrough = $summary;

            //START Chop up fieldname and find matching questions

            //loop through all selected questions
            foreach ($runthrough as $rt) {

                //Step 1: Get information about this response field (SGQA) for the summary
                $outputs = $this->buildOutputList($rt, $language, $surveyid, $outputType, $sql, $sLanguageCode);
                $sOutputHTML .= $outputs['statisticsoutput'];
                //2. Collect and Display results #######################################################################
                if (isset($outputs['alist']) && $outputs['alist']) {
                    //Make sure there really is an answerlist, and if so:
                    $display = $this->displayResults($outputs, $results, $rt, $outputType, $surveyid, $sql, $usegraph, $browse, $sLanguageCode);
                    $sOutputHTML .= $display['statisticsoutput'];
                    $aStatisticsData = array_merge($aStatisticsData, $display['astatdata']);
                }    //end if -> collect and display results


                //Delete Build Outputs data
                unset($outputs);
                unset($display);
            }    // end foreach -> loop through all questions

            //output
            if ($outputType == 'html') {
                $sOutputHTML .= "<br />&nbsp;\n";
            }

        }    //end if -> show summary results

        switch ($outputType) {
            case 'xls':

                $this->workbook->close();

                if ($outputTarget == 'F') {
                    return $sFileName;
                } else {
                    return;
                }
                break;

            case 'pdf':
                $this->pdf->lastPage();

                if ($outputTarget == 'F') {
                    // This is only used by lsrc to send an E-Mail attachment, so it gives back the filename to send and delete afterwards
                    $tempfilename = $sTempDir."/Survey_".$surveyid.".pdf";
                    $this->pdf->Output($tempfilename, $outputTarget);
                    return $tempfilename;
                } else {
                    return $this->pdf->Output(gT('Survey').'_'.$surveyid."_".$surveyInfo['surveyls_title'].'.pdf', $outputTarget);
                }

                break;
            case 'html':
                $sGoogleMapsAPIKey = trim(Yii::app()->getConfig("googleMapsAPIKey"));
                if ($sGoogleMapsAPIKey != '') {
                    $sGoogleMapsAPIKey = '&key='.$sGoogleMapsAPIKey;
                }
                $sSSL = '';
                if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off") {
                    $sSSL = 's';
                }
                $sOutputHTML .= "<script type=\"text/javascript\" src=\"http{$sSSL}://maps.googleapis.com/maps/api/js?sensor=false$sGoogleMapsAPIKey\"></script>\n"
                ."<script type=\"text/javascript\">var site_url='".Yii::app()->baseUrl."';var temppath='".Yii::app()->getConfig("tempurl")."';var imgpath='".Yii::app()->getConfig('adminimageurl')."';var aStatData=".ls_json_encode($aStatisticsData)."</script>";
                return $sOutputHTML;

                break;
            default:
                return $sOutputHTML;

                break;
        }

    }

    /**
     * Get the quartile using minitab method
     *
     * L=(1/4)(n+1), U=(3/4)(n+1)
     * Minitab linear interpolation between the two
     * closest data points. Minitab would let L = 2.5 and find the value half way between the
     * 2nd and 3rd data points. In our example, that would be (4+9)/2 =
     * 6.5. Similarly, the upper quartile value would be half way between
     * the 7th and 8th data points, which would be (49+64)/2 = 56.5. If L
     * were 2.25, Minitab would find the value one fourth of the way
     * between the 2nd and 3rd data points and if L were 2.75, Minitab
     * would find the value three fourths of the way between the 2nd and
     * 3rd data points.
     *
     * @staticvar null $sid
     * @staticvar int $recordCount
     * @staticvar null $field
     * @staticvar null $allRows
     * @param integer $quartile use 0 for return of recordcount, otherwise will return Q1,Q2,Q3
     * @param string $fieldname
     * @param int $surveyid
     * @param string $sql
     * @param bool $excludezeros
     * @return null|float
     */
    protected function getQuartile($quartile, $fieldname, $surveyid, $sql, $excludezeros)
    {
        static $sid = null;
        static $recordCount = 0;
        static $field = null;
        static $allRows = null;

        if ($surveyid !== $sid || $fieldname !== $field) {
            //get data
            $query = " FROM {{survey_$surveyid}} WHERE ".Yii::app()->db->quoteColumnName($fieldname)." IS NOT null";
            //NO ZEROES
            if (!$excludezeros) {
                $query .= " AND ".Yii::app()->db->quoteColumnName($fieldname)." != 0";
            }

            //filtering enabled?
            if (incompleteAnsFilterState() == "incomplete") {
                $query .= " AND submitdate is null";
            } elseif (incompleteAnsFilterState() == "complete") {
                $query .= " AND submitdate is not null";
            }

            //if $sql values have been passed to the statistics script from another script, incorporate them
            if (!empty($sql)) {$query .= " AND $sql"; }
        }

        if ($surveyid !== $sid) {
            $sid = $surveyid;
            $recordCount = 0;
            $field = null; // Reset cache
        }

        if ($fieldname !== $field) {
            $field = $fieldname;
            $allRows = Yii::app()->db->createCommand("SELECT ".Yii::app()->db->quoteColumnName($fieldname).$query.' ORDER BY '.Yii::app()->db->quoteColumnName($fieldname))->queryAll();
            $recordCount = Yii::app()->db->createCommand("SELECT COUNT(".Yii::app()->db->quoteColumnName($fieldname).")".$query)->queryScalar(); // Record count for THIS $fieldname
        }

        // Qx = (x/4) * (n+1) if not integer, interpolate
        switch ($quartile) {
            case 1:
            case 3:
                // Need at least 4 records
                if ($recordCount < 4) {
                    return;
                }
                break;
            case 2:
                // Need at least 2 records
                if ($recordCount < 2) {
                    return;
                }
                break;

            case 0:
                return $recordCount;

            default:
                return;
                break;
        }

        $q1 = $quartile / 4 * ($recordCount + 1);
        $row = $q1 - 1; // -1 since we start counting at 0
        if ($q1 === (int) $q1) {
            return $allRows[$row][$fieldname];
        } else {
            $diff = ($q1 - (int) $q1);
            return $allRows[$row][$fieldname] + $diff * ($allRows[$row + 1][$fieldname] - $allRows[$row][$fieldname]);
        }
    }

    /**
     *  Returns a simple list of values in a particular column, that meet the requirements of the SQL
     */
    function _listcolumn($surveyid, $column, $sortby = "", $sortmethod = "", $sorttype = "")
    {
        $search['condition'] = Yii::app()->db->quoteColumnName($column)." != ''";
        $sDBDriverName = Yii::app()->db->getDriverName();
        if ($sDBDriverName == 'sqlsrv' || $sDBDriverName == 'mssql' || $sDBDriverName == 'dblib') {
            $search['condition'] = "CAST(".Yii::app()->db->quoteColumnName($column)." as varchar) != ''";
        }

        //filter incomplete answers if set
        if (incompleteAnsFilterState() == "incomplete") {$search['condition'] .= " AND submitdate is null"; } elseif (incompleteAnsFilterState() == "complete") {$search['condition'] .= " AND submitdate is not null"; }

        //Look for any selects/filters set in the original statistics query, and apply them to the column listing
        if (isset(Yii::app()->session['statistics_selects_'.$surveyid]) && is_array(Yii::app()->session['statistics_selects_'.$surveyid])) {
            foreach (Yii::app()->session['statistics_selects_'.$surveyid] as $sql) {
                $search['condition'] .= " AND $sql";
            }
        }

        if ($sortby != '') {
            if ($sDBDriverName == 'sqlsrv' || $sDBDriverName == 'mssql' || $sDBDriverName == 'dblib') {
                $sortby = "CAST(".Yii::app()->db->quoteColumnName($sortby)." as varchar)";
            } else {
                $sortby = Yii::app()->db->quoteColumnName($sortby);
            }

            if ($sorttype == 'N') {$sortby = "($sortby * 1)"; } //Converts text sorting into numerical sorting
            $search['order'] = $sortby.' '.$sortmethod;
        }
        $results = SurveyDynamic::model($surveyid)->findAll($search);
        $output = array();
        foreach ($results as $row) {
            $output[] = array("id"=>$row['id'], "value"=>$row[$column]);
        }
        return $output;
    }

    /* This function builds the text description of each question in the filter section
    *
    * @param string $hinttext The question text
    *
    * */
    public static function _showSpeaker($hinttext)
    {
        global $maxchars; //Where does this come from? can it be replaced? passed with function call?

        if (!isset($maxchars)) {
            $maxchars = 70;
        }
        $htmlhinttext = str_replace("'", '&#039;', $hinttext); //the string is already HTML except for single quotes so we just replace these only
        $jshinttext = javascriptEscape($hinttext, true, true); //Build a javascript safe version of the string

        if (strlen($hinttext) > ($maxchars)) {
            $shortstring = flattenText($hinttext);
            $shortstring = htmlspecialchars(mb_strcut(html_entity_decode($shortstring, ENT_QUOTES, 'UTF-8'), 0, $maxchars, 'UTF-8'));
            $sTextToShow = gT("Question", "js").': '.$jshinttext;
            $reshtml = '<span>'.$shortstring.'...</span>';
            $reshtml .= '<span  class="show_speaker icon-assessments text-success" style="cursor: pointer" title="'.$sTextToShow.'"  data-toggle="tooltip" data-placement="bottom"  >';
            $reshtml .= '</span>';
        } else {
            $reshtml = "<span style='cursor: pointer' title='".$htmlhinttext."'> \"$htmlhinttext\"</span>";
        }
        return $reshtml;
    }

}
