<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
*
*	$Id$
*/
/*
* We need this later:
*  1 - Array (Flexible Labels) Dual Scale
5 - 5 Point Choice
A - Array (5 Point Choice)
B - Array (10 Point Choice)
C - Array (Yes/No/Uncertain)
D - Date
E - Array (Increase, Same, Decrease)
F - Array (Flexible Labels)
G - Gender
H - Array (Flexible Labels) by Column
I - Language Switch
K - Multiple Numerical Input
L - List (Radio)
M - Multiple choice
N - Numerical Input
O - List With Comment
P - Multiple choice with comments
Q - Multiple Short Text
R - Ranking
S - Short Free Text
T - Long Free Text
U - Huge Free Text
X - Boilerplate Question
Y - Yes/No
! - List (Dropdown)
: - Array (Flexible Labels) multiple drop down
; - Array (Flexible Labels) multiple texts
| - File Upload Question


*/

/**
* dataentry
*
* @package LimeSurvey
* @author
* @copyright 2011
* @version $Id$
* @access public
*/
class dataentry extends Survey_Common_Action
{

    function __construct($controller, $id)
    {
        parent::__construct($controller, $id);

        Yii::app()->loadHelper('surveytranslator');
    }

    /**
    * Function responsible for importing responses from file (.cvs)
    *
    * @param mixed $surveyid
    * @return void
    */
    function vvimport()
    {
        $aData = array();

        $surveyid = sanitize_int(Yii::app()->request->getParam('surveyid'));
        if (!empty($_REQUEST['sid'])) {
            $surveyid = sanitize_int($_REQUEST['sid']);
        }
        $aData['surveyid'] = $surveyid;
        $aData['clang'] = $this->getController()->lang;

        if( hasSurveyPermission($surveyid,'responses','create') )
        {
            // First load the database helper
            Yii::app()->loadHelper('database');

            $subAction = Yii::app()->request->getPost('subaction');
            if ($subAction != "upload")
            {
                $this->_showUploadForm($this->_getEncodingsArray(), $surveyid, $aData);
            }
            else
            {
                $this->_handleFileUpload($surveyid, $aData);
            }
        }
    }

    private function _handleFileUpload($surveyid, $aData)
    {
        $vvoutput = '';
        $donotimport = array();
        $clang = $this->getController()->lang;
        $filePath = $this->_moveUploadedFile($aData);
        $aFileContents = $this->_readFile($filePath);
        unlink($filePath); //delete the uploaded file
        unset($aFileContents[0]); //delete the first line

        Survey_dynamic::sid($surveyid);
        $survey = new Survey_dynamic;

        list($aFieldnames, $nbOfFields) = $this->_getFieldInfo($aFileContents);

        $aRealFieldNames = Yii::app()->db->getSchema()->getTable($survey->tableName())->getColumnNames();

        if (Yii::app()->request->getPost('noid') == "noid") {
            unset($aRealFieldNames[0]);
        }
        if (Yii::app()->request->getPost('finalized') == "notfinalized") {
            unset($aRealFieldNames[1]);
        }

        unset($aFileContents[1]); //delete the second line

        //See if any fields in the import file don't exist in the active survey
        $missing = array_diff($aFieldnames, $aRealFieldNames);
        if (is_array($missing) && count($missing) > 0) {
            foreach ($missing as $key => $val)
            {
                $donotimport[] = $key;
                unset($aFieldnames[$key]);
            }
        }

        if (Yii::app()->request->getPost('finalized') == "notfinalized") {
            $donotimport[] = 1;
            unset($aFieldnames[1]);
        }

        $importcount = 0;
        $recordcount = 0;
        $aFieldnames = array_map('dbQuoteID', $aFieldnames);

        // Find out which fields are datefields, these have to be null if the imported string is empty
        $fieldmap = createFieldMap($surveyid,'full',false,false,getBaseLanguageFromSurveyID($surveyid));

        $datefields = array();
        $numericfields = array();
        foreach ($fieldmap as $field)
        {
            if ($field['type'] == 'D') {
                $datefields[] = $field['fieldname'];
            }

            if ($field['type'] == 'N' || $field['type'] == 'K') {
                $numericfields[] = $field['fieldname'];
            }
        }

        foreach ($aFileContents as $row)
        {
            if (trim($row) != "") {
                $recordcount++;

                $fieldvalues = $this->_prepFieldValues($aFieldnames, $row, $nbOfFields, $donotimport);

                $fielddata = ($aFieldnames === array() && $fieldvalues === array() ? array()
                : array_combine($aFieldnames, $fieldvalues));
                foreach ($datefields as $datefield)
                {
                    if (@$fielddata["'" . $datefield . "'"] == '') {
                        unset($fielddata["'" . $datefield . "'"]);
                    }
                }

                foreach ($numericfields as $numericfield)
                {
                    if ($fielddata["`" . $numericfield . "`"] == '') {
                        unset($fielddata["`" . $numericfield . "`"]);
                    }
                }

                if (isset($fielddata['`submitdate`']) && $fielddata['`submitdate`'] == 'NULL') {
                    unset ($fielddata['`submitdate`']);
                }
                if ($fielddata['`lastpage`'] == '') $fielddata['`lastpage`'] = '0';

                $recordexists = false;
                if (isset($fielddata['`id`'])) {
                    $result = $survey->findAllByAttributes(array('id' => $fielddata['`id`']));
                    $recordexists = $result > 0;

                    // Check if record with same id exists
                    if ($recordexists) {
                        if (Yii::app()->request->getPost('insert') == "ignore") {
                            $aData['msgs'][] .= sprintf($clang->gT("Record ID %s was skipped because of duplicate ID."), $fielddata['`id`']);
                            continue;
                        }
                        if (Yii::app()->request->getPost('insert') == "replace") {
                            $result = $survey->deleteSomeRecords(array('id' => $fielddata['`id`']));
                            $recordexists = false;
                        }
                    }
                }

                if (Yii::app()->request->getPost('insert') == "renumber") {
                    unset($fielddata['`id`']);
                }

                if (isset($fielddata['`id`'])) {
                    switchMSSQLIdentityInsert("survey_$surveyid", true);
                }

                $result = $survey->insertRecords($fielddata);

                if (isset($fielddata['id'])) {
                    switchMSSQLIdentityInsert("survey_$surveyid", false);
                }

                if (!$result) {
                    $aData['error_msg'] = sprintf($clang->gT("Import failed on record %d"), $recordcount);
                    $this->_renderWrappedTemplate('dataentry', 'warning_header', $aData);
                    die();
                }
                else
                {
                    $importcount++;
                }

                $aData['importcount'] = $importcount;
            }
        }

        $aData['noid'] = Yii::app()->request->getPost('noid');
        $aData['insertstyle'] = Yii::app()->request->getPost('insertstyle');

        $this->_renderWrappedTemplate('dataentry', 'vvimport_upload', $aData);
    }

    private function _getFieldInfo($aFileContents)
    {
        $aFieldnames = explode("\t", trim($aFileContents[1]));

        $nbOfFields = count($aFieldnames) - 1;
        while (trim($aFieldnames[$nbOfFields]) == "" && $nbOfFields > -1) // get rid of blank entries
        {
            unset($aFieldnames[$nbOfFields]);
            $nbOfFields--;
        }
        return array($aFieldnames, $nbOfFields);
    }

    private function _readFile($filePath)
    { // Open the file for reading
        $handle = fopen($filePath, "r");
        // Read the file
        while (!feof($handle))
        {
            $buffer = fgets($handle); //To allow for very long lines
            $bigarray[] = @mb_convert_encoding($buffer, "UTF-8", $this->_getUploadCharset($this->_getEncodingsArray()));
        }
        // Close the file
        fclose($handle);
        return $bigarray;
    }

    private function _moveUploadedFile($aData)
    {
        $clang = $this->getController()->lang;
        $the_full_file_path = Yii::app()->getConfig('tempdir') . "/" . $_FILES['the_file']['name'];

        $move_uploaded_file_result = @move_uploaded_file($_FILES['the_file']['tmp_name'], $the_full_file_path);

        if (!$move_uploaded_file_result) {
            $aData['error_msg'] = sprintf(
            $clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),
            Yii::app()->getConfig('tempdir')
            );
            $this->_renderWrappedTemplate('dataentry', 'warning_header', $aData);
            die();
        }
        return $the_full_file_path;
    }

    private function _showUploadForm($aEncodings, $surveyid, $aData)
    {
        asort($aEncodings);

        // Create encodings list using the Yii's CHtml helper
        $charsetsout = CHtml::listOptions('utf8', $aEncodings, $aEncodings);

        $aData['tableExists'] = tableExists("{{survey_$surveyid}}");
        $aData['charsetsout'] = $charsetsout;
        $aData['display']['menu_bars']['browse'] = $this->getController()->lang->gT("Import VV file");

        $this->_renderWrappedTemplate('dataentry', 'vvimport', $aData);
    }

    private function _getUploadCharset($encodingsarray)
    {
        // Sanitize charset - if encoding is not found sanitize to 'utf8' which is the default for vvexport
        if (isset($_POST['vvcharset']) && $_POST['vvcharset']) {
            if (array_key_exists($_POST['vvcharset'], $encodingsarray)) {
                return $_POST['vvcharset'];
            }
        }
        return 'utf8';
    }

    /**
    * dataentry::import()
    * Function responsible to import responses from old survey table(s).
    * @param mixed $surveyid
    * @return void
    */
    function import($surveyid)
    {
        $surveyid = sanitize_int($surveyid);

        $subaction = '';

        $aData = array(
        'clang' => Yii::app()->lang,
        'surveyid' => $surveyid
        );

        if(hasSurveyPermission($surveyid,'responses','create'))
        {
            //if (!isset($surveyid)) $surveyid = $this->input->post('sid');
            if (!isset($oldtable) && isset($_POST['oldtable']))
            {
                $oldtable = Yii::app()->request->getPost('oldtable');

                $subaction = Yii::app()->request->getPost('subaction');
            }

            $schema = Yii::app()->db->getSchema();

            $clang = Yii::app()->lang;
            Yii::app()->loadHelper('database');

            if (!$subaction == "import")
            {
                // show UI for choosing old table

                $result = dbGetTablesLike("old\_survey\_%");

                $aOptionElements = array();
                //$queryCheckColumnsActive = $schema->getTable($oldtable)->columnNames;
                $resultActive = $schema->getTable("{{survey_{$surveyid}}}")->columnNames;
                //$resultActive = dbExecuteAssoc($queryCheckColumnsActive) or show_error("Error:<br />$query<br />");
                $countActive = count($resultActive);

                foreach ($result as $row)
                {
                    $row = each($row);

                    //$resultOld = dbExecuteAssoc($queryCheckColumnsOld) or show_error("Error:<br />$query<br />");
                    $resultOld = $schema->getTable($row[1])->columnNames;

                    if($countActive == count($resultOld)) //num_fields()
                    {
                        $aOptionElements[$row[1]] = $row[1];
                    }
                }
                $aHTMLOptions=array('empty'=>$clang->gT('Please select...'));
                $aData['optionElements'] = CHtml::listOptions('', $aOptionElements, $aHTMLOptions);

                //Get the menubar
                $aData['display']['menu_bars']['browse'] = $clang->gT("Quick statistics");

                $this->_renderWrappedTemplate('dataentry', 'import', $aData);
            }
            //elseif (isset($surveyid) && $surveyid && isset($oldtable))
            else
            {
                /*
                * TODO:
                * - mysql fit machen
                * -- quotes for mysql beachten --> `
                * - warnmeldung mehrsprachig
                * - testen
                */
                //	if($aDatabasetype=="postgres")
                //	{
                $activetable = "{{survey_$surveyid}}";

                //Fields we don't want to import
                $dontimportfields = array(
                //,'otherfield'
                );

                //$aFieldsOldTable=array_values(Yii::app()->db->MetaColumnNames($oldtable, true));
                //$aFieldsNewTable=array_values(Yii::app()->db->MetaColumnNames($activetable, true));

                $aFieldsOldTable = array_values($schema->getTable($oldtable)->columnNames);
                $aFieldsNewTable = array_values($schema->getTable($activetable)->columnNames);

                // Only import fields where the fieldnames are matching
                $aValidFields = array_intersect($aFieldsOldTable, $aFieldsNewTable);

                // Only import fields not being in the $dontimportfields array
                $aValidFields = array_diff($aValidFields, $dontimportfields);


                $queryOldValues = "SELECT ".implode(", ",array_map("dbQuoteID", $aValidFields))." FROM {$oldtable} ";
                $resultOldValues = dbExecuteAssoc($queryOldValues) or show_error("Error:<br />$queryOldValues<br />");
                $iRecordCount = $resultOldValues->count();
                $aSRIDConversions=array();
                foreach ($resultOldValues->readAll() as $row)
                {
                    $iOldID=$row['id'];
                    unset($row['id']);

                    //$sInsertSQL=Yii::app()->db->GetInsertSQL($activetable, $row);
                    $sInsertSQL="INSERT into {$activetable} (".implode(",", array_map("dbQuoteID", array_keys($row))).") VALUES (".implode(",", array_map("dbQuoteAll",array_values($row))).")";
                    $result = dbExecuteAssoc($sInsertSQL) or show_error("Error:<br />$sInsertSQL<br />");
                    $aSRIDConversions[$iOldID]=Yii::app()->db->getLastInsertID();
                }

                Yii::app()->session['flashmessage'] = sprintf($clang->gT("%s old response(s) were successfully imported."), $iRecordCount);

                $sOldTimingsTable=substr($oldtable,0,strrpos($oldtable,'_')).'_timings'.substr($oldtable,strrpos($oldtable,'_'));
                $sNewTimingsTable = "{{{$surveyid}_timings}}";

                if (returnGlobal('importtimings')=='Y' && tableExists($sOldTimingsTable) && tableExists($sNewTimingsTable))
                {
                    // Import timings
                    $aFieldsOldTimingTable=array_values($schema->getTable($sOldTimingsTable)->columnNames);
                    $aFieldsNewTimingTable=array_values($schema->getTable($sNewTimingsTable)->columnNames);

                    $aValidTimingFields=array_intersect($aFieldsOldTimingTable,$aFieldsNewTimingTable);

                    $queryOldValues = "SELECT ".implode(", ",$aValidTimingFields)." FROM {$sOldTimingsTable} ";
                    $resultOldValues = dbExecuteAssoc($queryOldValues) or show_error("Error:<br />$queryOldValues<br />");
                    $iRecordCountT=$resultOldValues->count();
                    $aSRIDConversions=array();
                    foreach ($resultOldValues->readAll() as $row)
                    {
                        if (isset($aSRIDConversions[$row['id']]))
                        {
                            $row['id']=$aSRIDConversions[$row['id']];
                        }
                        else continue;
                        //$sInsertSQL=Yii::app()->db->GetInsertSQL($sNewTimingsTable,$row);
                        $sInsertSQL="INSERT into {$sNewTimingsTable} (".implode(",", array_map("dbQuoteID", array_keys($row))).") VALUES (".implode(",", array_map("dbQuoteAll", array_values($row))).")";
                        $result = dbExecuteAssoc($sInsertSQL) or show_error("Error:<br />$sInsertSQL<br />");
                    }
                    Yii::app()->session['flashmessage'] = sprintf($clang->gT("%s old response(s) and according timings were successfully imported."),$iRecordCount,$iRecordCountT);
                }
                $this->getController()->redirect("/admin/browse/index/surveyid/{$surveyid}");
            }
        }
    }

    /**
    * dataentry::editdata()
    * Edit dataentry.
    * @param mixed $subaction
    * @param mixed $id
    * @param mixed $surveyid
    * @param mixed $language
    * @return
    */
    public function editdata($subaction, $id, $surveyid, $language='')
    {
        if ($language == '') {
            $language = Survey::model()->findByPk($surveyid)->language;
        }

        $surveyid = sanitize_int($surveyid);
        $id = sanitize_int($id);
        $aViewUrls = array();

        if (!isset($sDataEntryLanguage))
        {
            $sDataEntryLanguage = Survey::model()->findByPk($surveyid)->language;
        }

        $surveyinfo = getSurveyInfo($surveyid);
        if (hasSurveyPermission($surveyid, 'responses','update'))
        {
            $surveytable = "{{survey_".$surveyid.'}}';
            $aData['clang'] = $clang = $this->getController()->lang;
            $aData['display']['menu_bars']['browse'] = $clang->gT("Data entry");

            Yii::app()->loadHelper('database');

            //FIRST LETS GET THE NAMES OF THE QUESTIONS AND MATCH THEM TO THE FIELD NAMES FOR THE DATABASE
            $fnquery = "SELECT * FROM {{questions}}, {{groups}} g, {{surveys}} WHERE
            {{questions}}.gid=g.gid AND
            {{questions}}.language = '{$sDataEntryLanguage}' AND g.language = '{$sDataEntryLanguage}' AND
            {{questions}}.sid={{surveys}}.sid AND {{questions}}.sid='$surveyid'
            order by group_order, question_order";
            $fnresult = dbExecuteAssoc($fnquery);

            $fncount = $fnresult->getRowCount();

            $fnrows = array(); //Create an empty array in case FetchRow does not return any rows
            foreach ($fnresult->readAll() as $fnrow)
            {
                $fnrows[] = $fnrow;
                $private=$fnrow['anonymized'];
                $datestamp=$fnrow['datestamp'];
                $ipaddr=$fnrow['ipaddr'];
            } // Get table output into array


            // Perform a case insensitive natural sort on group name then question title of a multidimensional array
            // $fnames = (Field Name in Survey Table, Short Title of Question, Question Type, Field Name, Question Code, Predetermined Answers if exist)

            $fnames['completed'] = array('fieldname'=>"completed", 'question'=>$clang->gT("Completed"), 'type'=>'completed');

            $fnames=array_merge($fnames,createFieldMap($surveyid,'full',false,false,$sDataEntryLanguage));
            $nfncount = count($fnames)-1;

            //SHOW INDIVIDUAL RECORD

            if ($subaction == "edit" && hasSurveyPermission($surveyid,'responses','update'))
            {
                $idquery = "SELECT * FROM $surveytable WHERE id=$id";
                $idresult = dbExecuteAssoc($idquery) or safeDie ("Couldn't get individual record<br />$idquery<br />");
                foreach ($idresult->readAll() as $idrow)
                {
                    $results[]=$idrow;
                }
            }
            elseif ($subaction == "editsaved" && hasSurveyPermission($surveyid,'responses','update'))
            {
                if (isset($_GET['public']) && $_GET['public']=="true")
                {
                    $password = md5(Yii::app()->request->getParam('accesscode'));
                }
                else
                {
                    $password = Yii::app()->request->getParam('accesscode');
                }

                $svresult= Saved_control::model()->findAllByAttributes(
                array(
                'sid' => $surveyid,
                'identifier' => Yii::app()->request->getParam('identifier'),
                'access_code' => $password)
                );

                foreach($svresult as $svrow)
                {
                    $saver['email'] = $svrow['email'];
                    $saver['scid'] = $svrow['scid'];
                    $saver['ip'] = $svrow['ip'];
                }

                $svresult = Saved_control::model()->findAllByAttributes(array('scid'=>$saver['scid']));
                foreach($svresult as $svrow)
                {
                    $responses[$svrow['fieldname']] = $svrow['value'];
                } // while

                $fieldmap = createFieldMap($surveyid,'full',false,false,getBaseLanguageFromSurveyID($surveyid));
                foreach($fieldmap as $fm)
                {
                    if (isset($responses[$fm['fieldname']]))
                    {
                        $results1[$fm['fieldname']]=$responses[$fm['fieldname']];
                    }
                    else
                    {
                        $results1[$fm['fieldname']]="";
                    }
                }

                $results1['id'] = "";
                $results1['datestamp'] = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", Yii::app()->getConfig('timeadjust'));
                $results1['ipaddr'] = $saver['ip'];
                $results[] = $results1;
            }

            $aData = array(
            'id' => $id,
            'surveyid' => $surveyid,
            'subaction' => $subaction,
            'part' => 'header',
            'clang' => $clang,
            );

            $aViewUrls[] = 'edit';

            $highlight = FALSE;
            unset($fnames['lastpage']);

            // unset timings
            foreach ($fnames as $fname)
            {
                if ($fname['type'] == "interview_time" || $fname['type'] == "page_time" || $fname['type'] == "answer_time")
                {
                    unset($fnames[$fname['fieldname']]);
                    $nfncount--;
                }
            }

            $aDataentryoutput = '';
            foreach ($results as $idrow)
            {
                $fname = reset($fnames);

                do
                {
                    if (isset($idrow[$fname['fieldname']]) )
                    {
                        $answer = $idrow[ $fname['fieldname'] ];
                    }
                    $question = $fname['question'];
                    $aDataentryoutput .= "\t<tr";
                    if ($highlight) $aDataentryoutput .=" class='odd'";
                    else $aDataentryoutput .=" class='even'";

                    $highlight=!$highlight;
                    $aDataentryoutput .=">\n"
                    ."<td valign='top' align='right' width='25%'>"
                    ."\n";
                    $aDataentryoutput .= "\t<strong>".stripJavaScript($question)."</strong>\n";
                    $aDataentryoutput .= "</td>\n"
                    ."<td valign='top' align='left'>\n";
                    //$aDataentryoutput .= "\t-={$fname[3]}=-"; //Debugging info
                    if(isset($fname['qid']) && isset($fname['type']))
                    {
                        $qidattributes = getQuestionAttributeValues($fname['qid'], $fname['type']);
                    }
                    switch ($fname['type'])
                    {
                        case "completed":
                            // First compute the submitdate
                            if ($private == "Y")
                            {
                                // In case of anonymized responses survey with no datestamp
                                // then the the answer submitdate gets a conventional timestamp
                                // 1st Jan 1980
                                $mysubmitdate = date("Y-m-d H:i:s",mktime(0,0,0,1,1,1980));
                            }
                            else
                            {
                                $mysubmitdate = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", Yii::app()->getConfig('timeadjust'));
                            }

                            $completedate = empty($idrow['submitdate']) ? $mysubmitdate : $idrow['submitdate'];

                            $selected = (empty($idrow['submitdate'])) ? 'N' : $completedate;

                            $select_options = array(
                            'N' => $clang->gT('No'),
                            $completedate => $clang->gT('Yes')
                            );

                            $aDataentryoutput .= CHtml::dropDownList('completed', $selected, $select_options);

                            break;
                        case "X": //Boilerplate question
                            $aDataentryoutput .= "";
                            break;
                        case "Q":
                        case "K":
                            $aDataentryoutput .= $fname['subquestion'].'&nbsp;';
                            $aDataentryoutput .= CHtml::textField($fname['fieldname'], $idrow[$fname['fieldname']]);
                            break;
                        case "id":
                            $aDataentryoutput .= CHtml::tag('span', array('style' => 'font-weight: bold;'), '&nbsp;'.$idrow[$fname['fieldname']]);
                            break;
                        case "5": //5 POINT CHOICE radio-buttons
                            for ($i=1; $i<=5; $i++)
                            {
                                $checked = FALSE;
                                if ($idrow[$fname['fieldname']] == $i) { $checked = TRUE; }
                                $aDataentryoutput .= CHtml::radioButton($fname['fieldname'], $checked, array('class'=>'radiobtn', 'value'=>$i));
                                $aDataentryoutput .= $i;
                            }
                            break;
                        case "D": //DATE
                            $thisdate='';
                            $dateformatdetails = getDateFormatDataForQID($qidattributes, $surveyid)
                            ;
                            if ($idrow[$fname['fieldname']]!='')
                            {
                                $thisdate = DateTime::createFromFormat("Y-m-d H:i:s", $idrow[$fname['fieldname']])->format($dateformatdetails['phpdate']);
                            }
                            else
                            {
                                $thisdate = '';
                            }

                            if(canShowDatePicker($dateformatdetails))
                            {
                                $goodchars = str_replace( array("m","d","y", "H", "M"), "", $dateformatdetails['dateformat']);
                                $goodchars = "0123456789".$goodchars[0];
                                $aDataentryoutput .= CHtml::textField($fname['fieldname'], $thisdate,
                                array(
                                'class' => 'popupdate',
                                'size' => '12',
                                'onkeypress' => 'return goodchars(event,\''.$goodchars.'\')'
                                )
                                );
                                $aDataentryoutput .= CHtml::hiddenField('dateformat'.$fname['fieldname'], $dateformatdetails['lsdate'],
                                array( 'id' => "dateformat{$fname['fieldname']}" )
                                );
                                // $aDataentryoutput .= "\t<input type='text' class='popupdate' size='12' name='{$fname['fieldname']}' value='{$thisdate}' onkeypress=\"return goodchars(event,'".$goodchars."')\"/>\n";
                                // $aDataentryoutput .= "\t<input type='hidden' name='dateformat{$fname['fieldname']}' id='dateformat{$fname['fieldname']}' value='{$dateformatdetails['jsdate']}'  />\n";
                            }
                            else
                            {
                                $aDataentryoutput .= CHtml::textField($fname['fieldname'], $thisdate);
                            }
                            break;
                        case "G": //GENDER drop-down list
                            $select_options = array(
                            '' => $clang->gT("Please choose").'...',
                            'F' => $clang->gT("Female"),
                            'G' => $clang->gT("Male")
                            );
                            $aDataentryoutput .= CHtml::listBox($fname['fieldname'], $idrow[$fname['fieldname']], $select_options);
                            break;
                        case "L": //LIST drop-down
                        case "!": //List (Radio)
                            $qidattributes=getQuestionAttributeValues($fname['qid']);
                            if (isset($qidattributes['category_separator']) && trim($qidattributes['category_separator'])!='')
                            {
                                $optCategorySeparator = $qidattributes['category_separator'];
                            }
                            else
                            {
                                unset($optCategorySeparator);
                            }

                            if (substr($fname['fieldname'], -5) == "other")
                            {
                                $aDataentryoutput .= "\t<input type='text' name='{$fname['fieldname']}' value='"
                                .htmlspecialchars($idrow[$fname['fieldname']], ENT_QUOTES) . "' />\n";
                            }
                            else
                            {
                                $lquery = "SELECT * FROM {{answers}} WHERE qid={$fname['qid']} AND language = '{$sDataEntryLanguage}' ORDER BY sortorder, answer";
                                $lresult = dbExecuteAssoc($lquery);
                                $aDataentryoutput .= "\t<select name='{$fname['fieldname']}'>\n"
                                ."<option value=''";
                                if ($idrow[$fname['fieldname']] == "") {$aDataentryoutput .= " selected='selected'";}
                                $aDataentryoutput .= ">".$clang->gT("Please choose")."..</option>\n";

                                if (!isset($optCategorySeparator))
                                {
                                    foreach ($lresult->readAll() as $llrow)
                                    {
                                        $aDataentryoutput .= "<option value='{$llrow['code']}'";
                                        if ($idrow[$fname['fieldname']] == $llrow['code']) {$aDataentryoutput .= " selected='selected'";}
                                        $aDataentryoutput .= ">{$llrow['answer']}</option>\n";
                                    }
                                }
                                else
                                {
                                    $defaultopts = array();
                                    $optgroups = array();
                                    foreach ($lresult->readAll() as $llrow)
                                    {
                                        list ($categorytext, $answertext) = explode($optCategorySeparator,$llrow['answer']);
                                        if ($categorytext == '')
                                        {
                                            $defaultopts[] = array ( 'code' => $llrow['code'], 'answer' => $answertext);
                                        }
                                        else
                                        {
                                            $optgroups[$categorytext][] = array ( 'code' => $llrow['code'], 'answer' => $answertext);
                                        }
                                    }

                                    foreach ($optgroups as $categoryname => $optionlistarray)
                                    {
                                        $aDataentryoutput .= "<optgroup class=\"dropdowncategory\" label=\"".$categoryname."\">\n";
                                        foreach ($optionlistarray as $optionarray)
                                        {
                                            $aDataentryoutput .= "\t<option value='{$optionarray['code']}'";
                                            if ($idrow[$fname['fieldname']] == $optionarray['code']) {$aDataentryoutput .= " selected='selected'";}
                                            $aDataentryoutput .= ">{$optionarray['answer']}</option>\n";
                                        }
                                        $aDataentryoutput .= "</optgroup>\n";
                                    }
                                    foreach ($defaultopts as $optionarray)
                                    {
                                        $aDataentryoutput .= "<option value='{$optionarray['code']}'";
                                        if ($idrow[$fname['fieldname']] == $optionarray['code']) {$aDataentryoutput .= " selected='selected'";}
                                        $aDataentryoutput .= ">{$optionarray['answer']}</option>\n";
                                    }

                                }

                                $oquery="SELECT other FROM {{questions}} WHERE qid={$fname['qid']} AND {{questions}}.language = '{$sDataEntryLanguage}'";
                                $oresult=dbExecuteAssoc($oquery) or safeDie("Couldn't get other for list question<br />".$oquery."<br />");
                                foreach($oresult->readAll() as $orow)
                                {
                                    $fother=$orow['other'];
                                }
                                if ($fother =="Y")
                                {
                                    $aDataentryoutput .= "<option value='-oth-'";
                                    if ($idrow[$fname['fieldname']] == "-oth-"){$aDataentryoutput .= " selected='selected'";}
                                    $aDataentryoutput .= ">".$clang->gT("Other")."</option>\n";
                                }
                                $aDataentryoutput .= "\t</select>\n";
                            }
                            break;
                        case "O": //LIST WITH COMMENT drop-down/radio-button list + textarea
                            $lquery = "SELECT * FROM {{answers}} WHERE qid={$fname['qid']} AND language = '{$sDataEntryLanguage}' ORDER BY sortorder, answer";
                            $lresult = dbExecuteAssoc($lquery);
                            $aDataentryoutput .= "\t<select name='{$fname['fieldname']}'>\n"
                            ."<option value=''";
                            if ($idrow[$fname['fieldname']] == "") {$aDataentryoutput .= " selected='selected'";}
                            $aDataentryoutput .= ">".$clang->gT("Please choose")."..</option>\n";

                            foreach ($lresult->readAll() as $llrow)
                            {
                                $aDataentryoutput .= "<option value='{$llrow['code']}'";
                                if ($idrow[$fname['fieldname']] == $llrow['code']) {$aDataentryoutput .= " selected='selected'";}
                                $aDataentryoutput .= ">{$llrow['answer']}</option>\n";
                            }
                            $fname=next($fnames);
                            $aDataentryoutput .= "\t</select>\n"
                            ."\t<br />\n"
                            ."\t<textarea cols='45' rows='5' name='{$fname['fieldname']}'>"
                            .htmlspecialchars($idrow[$fname['fieldname']]) . "</textarea>\n";
                            break;
                        case "R": //RANKING TYPE QUESTION
                            $thisqid=$fname['qid'];
                            $currentvalues=array();
                            $myfname=$fname['sid'].'X'.$fname['gid'].'X'.$fname['qid'];
                            while (isset($fname['type']) && $fname['type'] == "R" && $fname['qid']==$thisqid)
                            {
                                //Let's get all the existing values into an array
                                if ($idrow[$fname['fieldname']])
                                {
                                    $currentvalues[] = $idrow[$fname['fieldname']];
                                }
                                $fname=next($fnames);
                            }
                            $ansquery = "SELECT * FROM {{answers}} WHERE language = '{$sDataEntryLanguage}' AND qid=$thisqid ORDER BY sortorder, answer";
                            $ansresult = dbExecuteAssoc($ansquery);
                            $anscount = $ansresult->count();
                            $aDataentryoutput .= "\t<script type='text/javascript'>\n"
                            ."\t<!--\n"
                            ."function rankthis_$thisqid(\$code, \$value)\n"
                            ."\t{\n"
                            ."\t\$index=document.editresponse.CHOICES_$thisqid.selectedIndex;\n"
                            ."\tfor (i=1; i<=$anscount; i++)\n"
                            ."{\n"
                            ."\$b=i;\n"
                            ."\$b += '';\n"
                            ."\$inputname=\"RANK_$thisqid\"+\$b;\n"
                            ."\$hiddenname=\"d$myfname\"+\$b;\n"
                            ."\$cutname=\"cut_$thisqid\"+i;\n"
                            ."document.getElementById(\$cutname).style.display='none';\n"
                            ."if (!document.getElementById(\$inputname).value)\n"
                            ."\t{\n"
                            ."\tdocument.getElementById(\$inputname).value=\$value;\n"
                            ."\tdocument.getElementById(\$hiddenname).value=\$code;\n"
                            ."\tdocument.getElementById(\$cutname).style.display='';\n"
                            ."\tfor (var b=document.getElementById('CHOICES_$thisqid').options.length-1; b>=0; b--)\n"
                            ."{\n"
                            ."if (document.getElementById('CHOICES_$thisqid').options[b].value == \$code)\n"
                            ."\t{\n"
                            ."\tdocument.getElementById('CHOICES_$thisqid').options[b] = null;\n"
                            ."\t}\n"
                            ."}\n"
                            ."\ti=$anscount;\n"
                            ."\t}\n"
                            ."}\n"
                            ."\tif (document.getElementById('CHOICES_$thisqid').options.length == 0)\n"
                            ."{\n"
                            ."document.getElementById('CHOICES_$thisqid').disabled=true;\n"
                            ."}\n"
                            ."\tdocument.editresponse.CHOICES_$thisqid.selectedIndex=-1;\n"
                            ."\t}\n"
                            ."function deletethis_$thisqid(\$text, \$value, \$name, \$thisname)\n"
                            ."\t{\n"
                            ."\tvar qid='$thisqid';\n"
                            ."\tvar lngth=qid.length+4;\n"
                            ."\tvar cutindex=\$thisname.substring(lngth, \$thisname.length);\n"
                            ."\tcutindex=parseFloat(cutindex);\n"
                            ."\tdocument.getElementById(\$name).value='';\n"
                            ."\tdocument.getElementById(\$thisname).style.display='none';\n"
                            ."\tif (cutindex > 1)\n"
                            ."{\n"
                            ."\$cut1name=\"cut_$thisqid\"+(cutindex-1);\n"
                            ."\$cut2name=\"d$myfname\"+(cutindex);\n"
                            ."document.getElementById(\$cut1name).style.display='';\n"
                            ."document.getElementById(\$cut2name).value='';\n"
                            ."}\n"
                            ."\telse\n"
                            ."{\n"
                            ."\$cut2name=\"d$myfname\"+(cutindex);\n"
                            ."document.getElementById(\$cut2name).value='';\n"
                            ."}\n"
                            ."\tvar i=document.getElementById('CHOICES_$thisqid').options.length;\n"
                            ."\tdocument.getElementById('CHOICES_$thisqid').options[i] = new Option(\$text, \$value);\n"
                            ."\tif (document.getElementById('CHOICES_$thisqid').options.length > 0)\n"
                            ."{\n"
                            ."document.getElementById('CHOICES_$thisqid').disabled=false;\n"
                            ."}\n"
                            ."\t}\n"
                            ."\t//-->\n"
                            ."\t</script>\n";
                            foreach ($ansresult->readAll() as $ansrow) //Now we're getting the codes and answers
                            {
                                $answers[] = array($ansrow['code'], $ansrow['answer']);
                            }
                            //now find out how many existing values there are

                            $chosen[]=""; //create array
                            if (!isset($ranklist)) {$ranklist="";}

                            if (isset($currentvalues))
                            {
                                $existing = count($currentvalues);
                            }
                            else {$existing=0;}
                            for ($j=1; $j<=$anscount; $j++) //go through each ranking and check for matching answer
                            {
                                $k=$j-1;
                                if (isset($currentvalues) && isset($currentvalues[$k]) && $currentvalues[$k])
                                {
                                    foreach ($answers as $ans)
                                    {
                                        if ($ans[0] == $currentvalues[$k])
                                        {
                                            $thiscode=$ans[0];
                                            $thistext=$ans[1];
                                        }
                                    }
                                }
                                $ranklist .= "$j:&nbsp;<input class='ranklist' id='RANK_$thisqid$j'";
                                if (isset($currentvalues) && isset($currentvalues[$k]) && $currentvalues[$k])
                                {
                                    $ranklist .= " value='".$thistext."'";
                                }
                                $ranklist .= " onFocus=\"this.blur()\"  />\n"
                                . "<input type='hidden' id='d$myfname$j' name='$myfname$j' value='";
                                if (isset($currentvalues) && isset($currentvalues[$k]) && $currentvalues[$k])
                                {
                                    $ranklist .= $thiscode;
                                    $chosen[]=array($thiscode, $thistext);
                                }
                                $ranklist .= "' />\n"
                                . "<img src='".Yii::app()->getConfig('imageurl')."/cut.gif' alt='".$clang->gT("Remove this item")."' title='".$clang->gT("Remove this item")."' ";
                                if ($j != $existing)
                                {
                                    $ranklist .= "style='display:none'";
                                }
                                $ranklist .= " id='cut_$thisqid$j' onclick=\"deletethis_$thisqid(document.editresponse.RANK_$thisqid$j.value, document.editresponse.d$myfname$j.value, document.editresponse.RANK_$thisqid$j.id, this.id)\" /><br />\n\n";
                            }

                            if (!isset($choicelist)) {$choicelist="";}
                            $choicelist .= "<select class='choicelist' size='$anscount' name='CHOICES' id='CHOICES_$thisqid' onclick=\"rankthis_$thisqid(this.options[this.selectedIndex].value, this.options[this.selectedIndex].text)\" >\n";
                            foreach ($answers as $ans)
                            {
                                if (!in_array($ans, $chosen))
                                {
                                    $choicelist .= "\t<option value='{$ans[0]}'>{$ans[1]}</option>\n";
                                }
                            }
                            $choicelist .= "</select>\n";
                            $aDataentryoutput .= "\t<table align='left' border='0' cellspacing='5'>\n"
                            ."<tr>\n"
                            ."\t<td align='left' valign='top' width='200'>\n"
                            ."<strong>"
                            .$clang->gT("Your Choices").":</strong><br />\n"
                            .$choicelist
                            ."\t</td>\n"
                            ."\t<td align='left'>\n"
                            ."<strong>"
                            .$clang->gT("Your Ranking").":</strong><br />\n"
                            .$ranklist
                            ."\t</td>\n"
                            ."</tr>\n"
                            ."\t</table>\n"
                            ."\t<input type='hidden' name='multi' value='$anscount' />\n"
                            ."\t<input type='hidden' name='lastfield' value='";
                            if (isset($multifields)) {$aDataentryoutput .= $multifields;}
                            $aDataentryoutput .= "' />\n";
                            $choicelist="";
                            $ranklist="";
                            unset($answers);
                            $fname=prev($fnames);
                            break;

                        case "M": //Multiple choice checkbox
                            $qidattributes=getQuestionAttributeValues($fname['qid']);
                            if (trim($qidattributes['display_columns'])!='')
                            {
                                $dcols=$qidattributes['display_columns'];
                            }
                            else
                            {
                                $dcols=0;
                            }

                            //					while ($fname[3] == "M" && $question != "" && $question == $fname['type'])
                            $thisqid=$fname['qid'];
                            while ($fname['qid'] == $thisqid)
                            {
                                $fieldn = substr($fname['fieldname'], 0, strlen($fname['fieldname']));
                                //$aDataentryoutput .= substr($fname['fieldname'], strlen($fname['fieldname'])-5, 5)."<br />\n";
                                if (substr($fname['fieldname'], -5) == "other")
                                {
                                    $aDataentryoutput .= "\t<input type='text' name='{$fname['fieldname']}' value='"
                                    .htmlspecialchars($idrow[$fname['fieldname']], ENT_QUOTES) . "' />\n";
                                }
                                else
                                {
                                    $aDataentryoutput .= "\t<input type='checkbox' class='checkboxbtn' name='{$fname['fieldname']}' value='Y'";
                                    if ($idrow[$fname['fieldname']] == "Y") {$aDataentryoutput .= " checked";}
                                    $aDataentryoutput .= " />{$fname['subquestion']}<br />\n";
                                }

                                $fname=next($fnames);
                            }
                            $fname=prev($fnames);

                            break;

                        case "I": //Language Switch
                            $lquery = "SELECT * FROM {{answers}} WHERE qid={$fname['qid']} AND language = '{$sDataEntryLanguage}' ORDER BY sortorder, answer";
                            $lresult = dbExecuteAssoc($lquery);


                            $slangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
                            $baselang = Survey::model()->findByPk($surveyid)->language;
                            array_unshift($slangs,$baselang);

                            $aDataentryoutput.= "<select name='{$fname['fieldname']}'>\n";
                            $aDataentryoutput .= "<option value=''";
                            if ($idrow[$fname['fieldname']] == "") {$aDataentryoutput .= " selected='selected'";}
                            $aDataentryoutput .= ">".$clang->gT("Please choose")."..</option>\n";

                            foreach ($slangs as $lang)
                            {
                                $aDataentryoutput.="<option value='{$lang}'";
                                if ($lang == $idrow[$fname['fieldname']]) {$aDataentryoutput .= " selected='selected'";}
                                $aDataentryoutput.=">".getLanguageNameFromCode($lang,false)."</option>\n";
                            }
                            $aDataentryoutput .= "</select>";
                            break;

                        case "P": //Multiple choice with comments checkbox + text
                            $aDataentryoutput .= "<table>\n";
                            while (isset($fname) && $fname['type'] == "P")
                            {
                                $thefieldname=$fname['fieldname'];
                                if (substr($thefieldname, -7) == "comment")
                                {
                                    $aDataentryoutput .= "<td><input type='text' name='{$fname['fieldname']}' size='50' value='"
                                    .htmlspecialchars($idrow[$fname['fieldname']], ENT_QUOTES) . "' /></td>\n"
                                    ."\t</tr>\n";
                                }
                                elseif (substr($fname['fieldname'], -5) == "other")
                                {
                                    $aDataentryoutput .= "\t<tr>\n"
                                    ."<td>\n"
                                    ."\t<input type='text' name='{$fname['fieldname']}' size='30' value='"
                                    .htmlspecialchars($idrow[$fname['fieldname']], ENT_QUOTES) . "' />\n"
                                    ."</td>\n"
                                    ."<td>\n";
                                    $fname=next($fnames);
                                    $aDataentryoutput .= "\t<input type='text' name='{$fname['fieldname']}' size='50' value='"
                                    .htmlspecialchars($idrow[$fname['fieldname']], ENT_QUOTES) . "' />\n"
                                    ."</td>\n"
                                    ."\t</tr>\n";
                                }
                                else
                                {
                                    $aDataentryoutput .= "\t<tr>\n"
                                    ."<td><input type='checkbox' class='checkboxbtn' name=\"{$fname['fieldname']}\" value='Y'";
                                    if ($idrow[$fname['fieldname']] == "Y") {$aDataentryoutput .= " checked";}
                                    $aDataentryoutput .= " />{$fname['subquestion']}</td>\n";
                                }
                                $fname=next($fnames);
                            }
                            $aDataentryoutput .= "</table>\n";
                            $fname=prev($fnames);
                            break;
                        case "|": //FILE UPLOAD
                            $aDataentryoutput .= "<table>\n";
                            if ($fname['aid']!=='filecount' && isset($idrow[$fname['fieldname'] . '_filecount']) && ($idrow[$fname['fieldname'] . '_filecount'] > 0))
                            {//file metadata
                                $metadata = json_decode($idrow[$fname['fieldname']], true);
                                $qAttributes = getQuestionAttributeValues($fname['qid']);
                                for ($i = 0; $i < $qAttributes['max_num_of_files'], isset($metadata[$i]); $i++)
                                {
                                    if ($qAttributes['show_title'])
                                        $aDataentryoutput .= '<tr><td width="25%">Title    </td><td><input type="text" class="'.$fname['fieldname'].'" id="'.$fname['fieldname'].'_title_'.$i   .'" name="title"    size=50 value="'.htmlspecialchars($metadata[$i]["title"])   .'" /></td></tr>';
                                    if ($qAttributes['show_comment'])
                                        $aDataentryoutput .= '<tr><td width="25%">Comment  </td><td><input type="text" class="'.$fname['fieldname'].'" id="'.$fname['fieldname'].'_comment_'.$i .'" name="comment"  size=50 value="'.htmlspecialchars($metadata[$i]["comment"]) .'" /></td></tr>';

                                    $aDataentryoutput .= '<tr><td>        File name</td><td><input   class="'.$fname['fieldname'].'" id="'.$fname['fieldname'].'_name_'.$i    .'" name="name" size=50 value="'.htmlspecialchars(rawurldecode($metadata[$i]["name"]))    .'" /></td></tr>'
                                    .'<tr><td></td><td><input type="hidden" class="'.$fname['fieldname'].'" id="'.$fname['fieldname'].'_size_'.$i    .'" name="size"     size=50 value="'.htmlspecialchars($metadata[$i]["size"])    .'" /></td></tr>'
                                    .'<tr><td></td><td><input type="hidden" class="'.$fname['fieldname'].'" id="'.$fname['fieldname'].'_ext_'.$i     .'" name="ext"      size=50 value="'.htmlspecialchars($metadata[$i]["ext"])     .'" /></td></tr>'
                                    .'<tr><td></td><td><input type="hidden"  class="'.$fname['fieldname'].'" id="'.$fname['fieldname'].'_filename_'.$i    .'" name="filename" size=50 value="'.htmlspecialchars(rawurldecode($metadata[$i]["filename"]))    .'" /></td></tr>';
                                }
                                $aDataentryoutput .= '<tr><td></td><td><input type="hidden" id="'.$fname['fieldname'].'" name="'.$fname['fieldname'].'" size=50 value="'.htmlspecialchars($idrow[$fname['fieldname']]).'" /></td></tr>';
                                $aDataentryoutput .= '</table>';
                                $aDataentryoutput .= '<script type="text/javascript">
                                $(function() {
                                $(".'.$fname['fieldname'].'").keyup(function() {
                                var filecount = $("#'.$fname['fieldname'].'_filecount").val();
                                var jsonstr = "[";
                                var i;
                                for (i = 0; i < filecount; i++)
                                {
                                if (i != 0)
                                jsonstr += ",";
                                jsonstr += \'{"title":"\'+$("#'.$fname['fieldname'].'_title_"+i).val()+\'",\';
                                jsonstr += \'"comment":"\'+$("#'.$fname['fieldname'].'_comment_"+i).val()+\'",\';
                                jsonstr += \'"size":"\'+$("#'.$fname['fieldname'].'_size_"+i).val()+\'",\';
                                jsonstr += \'"ext":"\'+$("#'.$fname['fieldname'].'_ext_"+i).val()+\'",\';
                                jsonstr += \'"filename":"\'+$("#'.$fname['fieldname'].'_filename_"+i).val()+\'",\';
                                jsonstr += \'"name":"\'+encodeURIComponent($("#'.$fname['fieldname'].'_name_"+i).val())+\'"}\';
                                }
                                jsonstr += "]";
                                $("#'.$fname['fieldname'].'").val(jsonstr);

                                });
                                });
                                </script>';
                            }
                            else
                            {//file count
                                $aDataentryoutput .= '<input readonly id="'.$fname['fieldname'].'" name="'.$fname['fieldname'].'" value ="'.htmlspecialchars($idrow[$fname['fieldname']]).'" /></td></table>';
                            }
                            break;
                        case "N": //NUMERICAL TEXT
                            $aDataentryoutput .= "\t<input type='text' name='{$fname['fieldname']}' value='{$idrow[$fname['fieldname']]}' "
                            ."onkeypress=\"return goodchars(event,'0123456789.,')\" />\n";
                            break;
                        case "S": //SHORT FREE TEXT
                            $aDataentryoutput .= "\t<input type='text' name='{$fname['fieldname']}' value='"
                            .htmlspecialchars($idrow[$fname['fieldname']], ENT_QUOTES) . "' />\n";
                            break;
                        case "T": //LONG FREE TEXT
                            $aDataentryoutput .= "\t<textarea rows='5' cols='45' name='{$fname['fieldname']}'>"
                            .htmlspecialchars($idrow[$fname['fieldname']], ENT_QUOTES) . "</textarea>\n";
                            break;
                        case "U": //HUGE FREE TEXT
                            $aDataentryoutput .= "\t<textarea rows='50' cols='70' name='{$fname['fieldname']}'>"
                            .htmlspecialchars($idrow[$fname['fieldname']], ENT_QUOTES) . "</textarea>\n";
                            break;
                        case "Y": //YES/NO radio-buttons
                            $aDataentryoutput .= "\t<select name='{$fname['fieldname']}'>\n"
                            ."<option value=''";
                            if ($idrow[$fname['fieldname']] == "") {$aDataentryoutput .= " selected='selected'";}
                            $aDataentryoutput .= ">".$clang->gT("Please choose")."..</option>\n"
                            ."<option value='Y'";
                            if ($idrow[$fname['fieldname']] == "Y") {$aDataentryoutput .= " selected='selected'";}
                            $aDataentryoutput .= ">".$clang->gT("Yes")."</option>\n"
                            ."<option value='N'";
                            if ($idrow[$fname['fieldname']] == "N") {$aDataentryoutput .= " selected='selected'";}
                            $aDataentryoutput .= ">".$clang->gT("No")."</option>\n"
                            ."\t</select>\n";
                            break;
                        case "A": //ARRAY (5 POINT CHOICE) radio-buttons
                            $aDataentryoutput .= "<table>\n";
                            $thisqid=$fname['qid'];
                            while ($fname['qid'] == $thisqid)
                            {
                                $aDataentryoutput .= "\t<tr>\n"
                                ."<td align='right'>{$fname['subquestion']}</td>\n"
                                ."<td>\n";
                                for ($j=1; $j<=5; $j++)
                                {
                                    $aDataentryoutput .= "\t<input type='radio' class='radiobtn' name='{$fname['fieldname']}' value='$j'";
                                    if ($idrow[$fname['fieldname']] == $j) {$aDataentryoutput .= " checked";}
                                    $aDataentryoutput .= " />$j&nbsp;\n";
                                }
                                $aDataentryoutput .= "</td>\n"
                                ."\t</tr>\n";
                                $fname=next($fnames);
                            }
                            $aDataentryoutput .= "</table>\n";
                            $fname=prev($fnames);
                            break;
                        case "B": //ARRAY (10 POINT CHOICE) radio-buttons
                            $aDataentryoutput .= "<table>\n";
                            $thisqid=$fname['qid'];
                            while ($fname['qid'] == $thisqid)
                            {
                                $fieldn = substr($fname['fieldname'], 0, strlen($fname['fieldname']));
                                $aDataentryoutput .= "\t<tr>\n"
                                ."<td align='right'>{$fname['subquestion']}</td>\n"
                                ."<td>\n";
                                for ($j=1; $j<=10; $j++)
                                {
                                    $aDataentryoutput .= "\t<input type='radio' class='radiobtn' name='{$fname['fieldname']}' value='$j'";
                                    if ($idrow[$fname['fieldname']] == $j) {$aDataentryoutput .= " checked";}
                                    $aDataentryoutput .= " />$j&nbsp;\n";
                                }
                                $aDataentryoutput .= "</td>\n"
                                ."\t</tr>\n";
                                $fname=next($fnames);
                            }
                            $fname=prev($fnames);
                            $aDataentryoutput .= "</table>\n";
                            break;
                        case "C": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                            $aDataentryoutput .= "<table>\n";
                            $thisqid=$fname['qid'];
                            while ($fname['qid'] == $thisqid)
                            {
                                $fieldn = substr($fname['fieldname'], 0, strlen($fname['fieldname']));
                                $aDataentryoutput .= "\t<tr>\n"
                                ."<td align='right'>{$fname['subquestion']}</td>\n"
                                ."<td>\n"
                                ."\t<input type='radio' class='radiobtn' name='{$fname['fieldname']}' value='Y'";
                                if ($idrow[$fname['fieldname']] == "Y") {$aDataentryoutput .= " checked";}
                                $aDataentryoutput .= " />".$clang->gT("Yes")."&nbsp;\n"
                                ."\t<input type='radio' class='radiobtn' name='{$fname['fieldname']}' value='U'";
                                if ($idrow[$fname['fieldname']] == "U") {$aDataentryoutput .= " checked";}
                                $aDataentryoutput .= " />".$clang->gT("Uncertain")."&nbsp;\n"
                                ."\t<input type='radio' class='radiobtn' name='{$fname['fieldname']}' value='N'";
                                if ($idrow[$fname['fieldname']] == "N") {$aDataentryoutput .= " checked";}
                                $aDataentryoutput .= " />".$clang->gT("No")."&nbsp;\n"
                                ."</td>\n"
                                ."\t</tr>\n";
                                $fname=next($fnames);
                            }
                            $fname=prev($fnames);
                            $aDataentryoutput .= "</table>\n";
                            break;
                        case "E": //ARRAY (Increase/Same/Decrease) radio-buttons
                            $aDataentryoutput .= "<table>\n";
                            $thisqid=$fname['qid'];
                            while ($fname['qid'] == $thisqid)
                            {
                                $fieldn = substr($fname['fieldname'], 0, strlen($fname['fieldname']));
                                $aDataentryoutput .= "\t<tr>\n"
                                ."<td align='right'>{$fname['subquestion']}</td>\n"
                                ."<td>\n"
                                ."\t<input type='radio' class='radiobtn' name='{$fname['fieldname']}' value='I'";
                                if ($idrow[$fname['fieldname']] == "I") {$aDataentryoutput .= " checked";}
                                $aDataentryoutput .= " />Increase&nbsp;\n"
                                ."\t<input type='radio' class='radiobtn' name='{$fname['fieldname']}' value='S'";
                                if ($idrow[$fname['fieldname']] == "I") {$aDataentryoutput .= " checked";}
                                $aDataentryoutput .= " />Same&nbsp;\n"
                                ."\t<input type='radio' class='radiobtn' name='{$fname['fieldname']}' value='D'";
                                if ($idrow[$fname['fieldname']] == "D") {$aDataentryoutput .= " checked";}
                                $aDataentryoutput .= " />Decrease&nbsp;\n"
                                ."</td>\n"
                                ."\t</tr>\n";
                                $fname=next($fnames);
                            }
                            $fname=prev($fnames);
                            $aDataentryoutput .= "</table>\n";
                            break;
                        case "F": //ARRAY (Flexible Labels)
                        case "H":
                        case "1":
                            $aDataentryoutput .= "<table>\n";
                            $thisqid=$fname['qid'];
                            while (isset($fname['qid']) && $fname['qid'] == $thisqid)
                            {
                                $fieldn = substr($fname['fieldname'], 0, strlen($fname['fieldname']));
                                $aDataentryoutput .= "\t<tr>\n"
                                ."<td align='right' valign='top'>{$fname['subquestion']}";
                                if (isset($fname['scale']))
                                {
                                    $aDataentryoutput .= " (".$fname['scale'].')';
                                }
                                $aDataentryoutput .="</td>\n";
                                $scale_id=0;
                                if (isset($fname['scale_id'])) $scale_id=$fname['scale_id'];
                                $fquery = "SELECT * FROM {{answers}} WHERE qid='{$fname['qid']}' and scale_id={$scale_id} and language='$sDataEntryLanguage' order by sortorder, answer";
                                $fresult = dbExecuteAssoc($fquery);
                                $aDataentryoutput .= "<td>\n";
                                foreach ($fresult->readAll() as $frow)
                                {
                                    $aDataentryoutput .= "\t<input type='radio' class='radiobtn' name='{$fname['fieldname']}' value='{$frow['code']}'";
                                    if ($idrow[$fname['fieldname']] == $frow['code']) {$aDataentryoutput .= " checked";}
                                    $aDataentryoutput .= " />".$frow['answer']."&nbsp;\n";
                                }
                                //Add 'No Answer'
                                $aDataentryoutput .= "\t<input type='radio' class='radiobtn' name='{$fname['fieldname']}' value=''";
                                if ($idrow[$fname['fieldname']] == '') {$aDataentryoutput .= " checked";}
                                $aDataentryoutput .= " />".$clang->gT("No answer")."&nbsp;\n";

                                $aDataentryoutput .= "</td>\n"
                                ."\t</tr>\n";
                                $fname=next($fnames);
                            }
                            $fname=prev($fnames);
                            $aDataentryoutput .= "</table>\n";
                            break;
                        case ":": //ARRAY (Multi Flexi) (Numbers)
                            $qidattributes=getQuestionAttributeValues($fname['qid']);
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
                            if ($qidattributes['multiflexible_checkbox']!=0) {
                                $minvalue=0;
                                $maxvalue=1;
                                $stepvalue=1;
                            }
                            $aDataentryoutput .= "<table>\n";
                            $thisqid=$fname['qid'];
                            while (isset($fname['qid']) && $fname['qid'] == $thisqid)
                            {
                                $fieldn = substr($fname['fieldname'], 0, strlen($fname['fieldname']));
                                $aDataentryoutput .= "\t<tr>\n"
                                . "<td align='right' valign='top'>{$fname['subquestion1']}:{$fname['subquestion2']}</td>\n";
                                $aDataentryoutput .= "<td>\n";
                                if ($qidattributes['input_boxes']!=0) {
                                    $aDataentryoutput .= "\t<input type='text' name='{$fname['fieldname']}' value='";
                                    if (!empty($idrow[$fname['fieldname']])) {$aDataentryoutput .= $idrow[$fname['fieldname']];}
                                    $aDataentryoutput .= "' size=4 />";
                                } else {
                                    $aDataentryoutput .= "\t<select name='{$fname['fieldname']}'>\n";
                                    $aDataentryoutput .= "<option value=''>...</option>\n";
                                    for($ii=$minvalue;$ii<=$maxvalue;$ii+=$stepvalue)
                                    {
                                        $aDataentryoutput .= "<option value='$ii'";
                                        if($idrow[$fname['fieldname']] == $ii) {$aDataentryoutput .= " selected";}
                                        $aDataentryoutput .= ">$ii</option>\n";
                                    }
                                }

                                $aDataentryoutput .= "</td>\n"
                                ."\t</tr>\n";
                                $fname=next($fnames);
                            }
                            $fname=prev($fnames);
                            $aDataentryoutput .= "</table>\n";
                            break;
                        case ";": //ARRAY (Multi Flexi)
                            $aDataentryoutput .= "<table>\n";
                            $thisqid=$fname['qid'];
                            while (isset($fname['qid']) && $fname['qid'] == $thisqid)
                            {
                                $fieldn = substr($fname['fieldname'], 0, strlen($fname['fieldname']));
                                $aDataentryoutput .= "\t<tr>\n"
                                . "<td align='right' valign='top'>{$fname['subquestion1']}:{$fname['subquestion2']}</td>\n";
                                $aDataentryoutput .= "<td>\n";
                                $aDataentryoutput .= "\t<input type='text' name='{$fname['fieldname']}' value='";
                                if(!empty($idrow[$fname['fieldname']])) {$aDataentryoutput .= $idrow[$fname['fieldname']];}
                                $aDataentryoutput .= "' /></td>\n"
                                ."\t</tr>\n";
                                $fname=next($fnames);
                            }
                            $fname=prev($fnames);
                            $aDataentryoutput .= "</table>\n";
                            break;
                        default: //This really only applies to tokens for non-private surveys
                            $aDataentryoutput .= "\t<input type='text' name='{$fname['fieldname']}' value='"
                            .$idrow[$fname['fieldname']] . "' />\n";
                            break;
                    }

                    $aDataentryoutput .= "		</td>
                    </tr>\n";
                } while ($fname=next($fnames));
            }
            $aDataentryoutput .= "</table>\n"
            ."<p>\n";

            $aData['sDataEntryLanguage'] = $sDataEntryLanguage;

            if (!hasSurveyPermission($surveyid, 'responses','update'))
            { // if you are not survey owner or super admin you cannot modify responses
                $aDataentryoutput .= "<input type='button' value='".$clang->gT("Save")."' disabled='disabled'/>\n";
            }
            elseif ($subaction == "edit" && hasSurveyPermission($surveyid,'responses','update'))
            {
                $aData['part'] = 'edit';
                $aDataentryoutput .= $this->getController()->render('/admin/dataentry/edit', $aData, TRUE);
            }
            elseif ($subaction == "editsaved" && hasSurveyPermission($surveyid,'responses','update'))
            {
                $aData['part'] = 'editsaved';
                $aDataentryoutput .= $this->getController()->render('/admin/dataentry/edit', $aData, TRUE);
            }

            $aDataentryoutput .= "</form>\n";

            $aViewUrls['output'] = $aDataentryoutput;
            $this->_renderWrappedTemplate('dataentry', $aViewUrls, $aData);
        }
    }

    /**
    * dataentry::delete()
    * delete dataentry
    * @return
    */
    public function delete()
    {
        $subaction = Yii::app()->request->getPost('subaction');
        $surveyid = $_REQUEST['surveyid'];
        if (!empty($_REQUEST['sid'])) $surveyid = (int)$_REQUEST['sid'];

        $surveyid = sanitize_int($surveyid);
        $id = Yii::app()->request->getPost('id');

        $aData = array(
        'surveyid' => $surveyid,
        'id' => $id
        );

        if (hasSurveyPermission($surveyid, 'responses','read') && $subaction == "delete"  && hasSurveyPermission($surveyid, 'responses', 'delete'))
        {
            $surveytable = "{{survey_".$surveyid.'}}';
            $aData['thissurvey'] = getSurveyInfo($surveyid);

            $delquery = "DELETE FROM $surveytable WHERE id=$id";
            Yii::app()->loadHelper('database');
            $delresult = dbExecuteAssoc($delquery) or safeDie ("Couldn't delete record $id<br />\n");

            $this->_renderWrappedTemplate('dataentry', 'delete', $aData);
        }
    }

    /**
    * dataentry::update()
    * update dataentry
    * @return
    */
    public function update()
    {
        $subaction = Yii::app()->request->getPost('subaction');
        $surveyid = $_REQUEST['surveyid'];
        if (!empty($_REQUEST['sid'])) $surveyid = (int)$_REQUEST['sid'];
        $surveyid = sanitize_int($surveyid);
        $id = Yii::app()->request->getPost('id');
        $lang = Yii::app()->request->getPost('lang');

        if ($subaction == "update"  && hasSurveyPermission($surveyid, 'responses', 'update'))
        {

            $baselang = Survey::model()->findByPk($surveyid)->language;
            Yii::app()->loadHelper("database");
            $clang = $this->getController()->lang;
            $surveytable = "{{survey_".$surveyid.'}}';

            $aDataentryoutput = "<div class='header ui-widget-header'>".$clang->gT("Data entry")."</div>\n";

            $fieldmap = createFieldMap($surveyid,'full',false,false,getBaseLanguageFromSurveyID($surveyid));

            // unset timings
            foreach ($fieldmap as $fname)
            {
                if ($fname['type'] == "interview_time" || $fname['type'] == "page_time" || $fname['type'] == "answer_time")
                {
                    unset($fieldmap[$fname['fieldname']]);
                }
            }

            $thissurvey = getSurveyInfo($surveyid);
            $updateqr = "UPDATE $surveytable SET \n";

            foreach ($fieldmap as $irow)
            {
                $fieldname=$irow['fieldname'];
                if ($fieldname=='id') continue;
                if (isset($_POST[$fieldname]))
                {
                    $thisvalue=$_POST[$fieldname];
                }
                else
                {
                    $thisvalue="";
                }
                if ($irow['type'] == 'lastpage')
                {
                    $thisvalue=0;
                }
                elseif ($irow['type'] == 'D')
                {
                    if ($thisvalue == "")
                    {
                        $updateqr .= $fieldname." = NULL, \n"; //dbQuoteID($fieldname)." = NULL, \n";
                    }
                    else
                    {
                        $qidattributes = getQuestionAttributeValues($irow['qid'], $irow['type']);
                        $dateformatdetails = getDateFormatDataForQID($qidattributes, $thissurvey);

                        $items = array($thisvalue,$dateformatdetails['phpdate']);
                        $this->getController()->loadLibrary('Date_Time_Converter');
                        $datetimeobj = new date_time_converter($items) ;
                        //need to check if library get initialized with new value of constructor or not.

                        //$datetimeobj = new Date_Time_Converter($thisvalue,$dateformatdetails['phpdate']);
                        $updateqr .= $fieldname." = '{$datetimeobj->convert("Y-m-d H:i:s")}', \n";// dbQuoteID($fieldname)." = '{$datetimeobj->convert("Y-m-d H:i:s")}', \n";
                    }
                }
                elseif (($irow['type'] == 'N' || $irow['type'] == 'K') && $thisvalue == "")
                {
                    $updateqr .= $fieldname." = NULL, \n"; //dbQuoteID($fieldname)." = NULL, \n";
                }
                elseif ($irow['type'] == '|' && strpos($irow['fieldname'], '_filecount') && $thisvalue == "")
                {
                    $updateqr .= $fieldname." = NULL, \n"; //dbQuoteID($fieldname)." = NULL, \n";
                }
                elseif ($irow['type'] == 'submitdate')
                {
                    if (isset($_POST['completed']) && ($_POST['completed']== "N"))
                    {
                        $updateqr .= $fieldname." = NULL, \n"; //dbQuoteID($fieldname)." = NULL, \n";
                    }
                    elseif (isset($_POST['completed']) && $thisvalue=="")
                    {
                        $updateqr .= $fieldname." = '" . $_POST['completed'] . "', \n";// dbQuoteID($fieldname)." = " . dbQuoteAll($_POST['completed'],true) . ", \n";
                    }
                    else
                    {
                        $updateqr .= $fieldname." = '" . $thisvalue . "', \n"; //dbQuoteID($fieldname)." = " . dbQuoteAll($thisvalue,true) . ", \n";
                    }
                }
                else
                {
                    $updateqr .= $fieldname." = '" . $thisvalue . "', \n"; // dbQuoteID($fieldname)." = " . dbQuoteAll($thisvalue,true) . ", \n";
                }
            }
            $updateqr = substr($updateqr, 0, -3);
            $updateqr .= " WHERE id=$id";

            $updateres = dbExecuteAssoc($updateqr) or safeDie("Update failed:<br />\n<br />$updateqr");
            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            $onerecord_link = $this->getController()->createUrl('/').'/admin/browse/index/surveyid/'.$surveyid.'/id/'.$id;
            $allrecords_link = $this->getController()->createUrl('/').'/admin/browse/index/surveyid/'.$surveyid.'/all';
            $aDataentryoutput .= "<div class='messagebox ui-corner-all'><div class='successheader'>".$clang->gT("Success")."</div>\n"
            .$clang->gT("Record has been updated.")."<br /><br />\n"
            ."<input type='submit' value='".$clang->gT("View This Record")."' onclick=\"window.open('$onerecord_link', '_top')\" /><br /><br />\n"
            ."<input type='submit' value='".$clang->gT("Browse responses")."' onclick=\"window.open('$allrecords_link', '_top')\" />\n"
            ."</div>\n";

            $aViewUrls['output'] = $aDataentryoutput;
            $this->_renderWrappedTemplate('dataentry', $aViewUrls, $aData);
        }
    }

    /**
    * dataentry::insert()
    * insert new dataentry
    * @return
    */
    public function insert()
    {
        $clang = Yii::app()->lang;
        $subaction = Yii::app()->request->getPost('subaction');
        $surveyid = Yii::app()->request->getPost('sid');
        $lang = isset($_POST['lang']) ? Yii::app()->request->getPost('lang') : NULL;

        $aData = array(
        'surveyid' => $surveyid,
        'lang' => $lang,
        'clang' => $clang
        );

        if (hasSurveyPermission($surveyid, 'responses','read'))
        {
            if ($subaction == "insert" && hasSurveyPermission($surveyid,'responses','create'))
            {
                $surveytable = "{{survey_{$surveyid}}}";
                $thissurvey = getSurveyInfo($surveyid);
                $errormsg = "";

                Yii::app()->loadHelper("database");
                $aViewUrls['display']['menu_bars']['browse'] = $clang->gT("Data entry");

                $aDataentryoutput = '';
                $aDataentrymsgs = array();
                $hiddenfields = '';

                $lastanswfortoken = ''; // check if a previous answer has been submitted or saved
                $rlanguage = '';

                if (isset($_POST['token']))
                {
                    $tokencompleted = "";
                    $tcquery = "SELECT completed from {{tokens_{$surveyid}}} WHERE token='{$_POST['token']}'"; //dbQuoteAll($_POST['token'],true);
                    $tcresult = dbExecuteAssoc($tcquery);
                    $tccount = $tcresult->getRowCount();
                    foreach ($tcresult->readAll() as $tcrow)
                    {
                        $tokencompleted = $tcrow['completed'];
                    }

                    if ($tccount < 1)
                    { // token doesn't exist in token table
                        $lastanswfortoken = 'UnknownToken';
                    }
                    elseif ($thissurvey['anonymized'] == "Y")
                    { // token exist but survey is anonymous, check completed state
                        if ($tokencompleted != "" && $tokencompleted != "N")
                        { // token is completed
                            $lastanswfortoken='PrivacyProtected';
                        }
                    }
                    else
                    { // token is valid, survey not anonymous, try to get last recorded response id
                        $aquery = "SELECT id,startlanguage FROM $surveytable WHERE token='".$_POST['token']."'"; //dbQuoteAll($_POST['token'],true);
                        $aresult = dbExecuteAssoc($aquery);
                        foreach ($aresult->readAll() as $arow)
                        {
                            if ($tokencompleted != "N") { $lastanswfortoken=$arow['id']; }
                            $rlanguage=$arow['startlanguage'];
                        }
                    }
                }

                // First Check if the survey uses tokens and if a token has been provided
                if (tableExists('{{tokens_'.$thissurvey['sid'].'}}') && (!$_POST['token']))
                {
                    $errormsg = CHtml::tag('div', array('class'=>'warningheader'), $clang->gT("Error"));
                    $errormsg .= CHtml::tag('p', array(), $clang->gT("This is a closed-access survey, so you must supply a valid token.  Please contact the administrator for assistance."));
                }
                elseif (tableExists('{{tokens_'.$thissurvey['sid'].'}}') && $lastanswfortoken == 'UnknownToken')
                {
                    $errormsg = CHtml::tag('div', array('class'=>'warningheader'), $clang->gT("Error"));
                    $errormsg .= CHtml::tag('p', array(), $clang->gT("The token you have provided is not valid or has already been used."));
                }
                elseif (tableExists('{{tokens_'.$thissurvey['sid'].'}}') && $lastanswfortoken != '')
                {
                    $errormsg = CHtml::tag('div', array('class'=>'warningheader'), $clang->gT("Error"));
                    $errormsg .= CHtml::tag('p', array(), $clang->gT("There is already a recorded answer for this token"));

                    if ($lastanswfortoken != 'PrivacyProtected')
                    {
                        $errormsg .= "<br /><br />".$clang->gT("Follow the following link to update it").":\n";
                        $errormsg .= CHtml::link("[id:$lastanswfortoken]",
                        Yii::app()->baseUrl.('/admin/dataentry/editdata/subaction/edit/id/'.$lastanswfortoken.'/surveyid/'.$surveyid.'/lang/'.$rlanguage),
                        array('title' => $clang->gT("Edit this entry")));
                    }
                    else
                    {
                        $errormsg .= "<br /><br />".$clang->gT("This surveys uses anonymized responses, so you can't update your response.")."\n";
                    }
                }
                else
                {
                    $last_db_id = 0;

                    if (isset($_POST['save']) && $_POST['save'] == "on")
                    {
                        $aData['save'] = TRUE;

                        $saver['identifier']=$_POST['save_identifier'];
                        $saver['language']=$_POST['save_language'];
                        $saver['password']=$_POST['save_password'];
                        $saver['passwordconfirm']=$_POST['save_confirmpassword'];
                        $saver['email']=$_POST['save_email'];
                        if (!returnGlobal('redo'))
                        {
                            $password = md5($saver['password']);
                        }
                        else
                        {
                            $password=$saver['password'];
                        }
                        $errormsg="";
                        if (!$saver['identifier']) { $errormsg .= $clang->gT("Error").": ".$clang->gT("You must supply a name for this saved session.");}
                        if (!$saver['password']) { $errormsg .= $clang->gT("Error").": ".$clang->gT("You must supply a password for this saved session.");}
                        if ($saver['password'] != $saver['passwordconfirm']) { $errormsg .= $clang->gT("Error").": ".$clang->gT("Your passwords do not match.");}

                        $aData['errormsg'] = $errormsg;

                        if ($errormsg)
                        {
                            foreach ($_POST as $key=>$val)
                            {
                                if (substr($key, 0, 4) != "save" && $key != "action" && $key !="sid" && $key != "datestamp" && $key !="ipaddr")
                                {
                                    $hiddenfields .= CHtml::hiddenField($key, $val);
                                    //$aDataentryoutput .= "<input type='hidden' name='$key' value='$val' />\n";
                                }
                            }
                        }
                    }

                    //BUILD THE SQL TO INSERT RESPONSES
                    $baselang = Survey::model()->findByPk($surveyid)->language;
                    $fieldmap = createFieldMap($surveyid,'full',false,false,getBaseLanguageFromSurveyID($surveyid));
                    $insert_data = array();

                    $_POST['startlanguage'] = $baselang;
                    if ($thissurvey['datestamp'] == "Y") { $_POST['startdate'] = $_POST['datestamp']; }
                    if (isset($_POST['closerecord']))
                    {
                        if ($thissurvey['datestamp'] == "Y")
                        {
                            $_POST['submitdate'] = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", Yii::app()->getConfig('timeadjust'));
                        }
                        else
                        {
                            $_POST['submitdate'] = date("Y-m-d H:i:s",mktime(0,0,0,1,1,1980));
                        }
                    }

                    foreach ($fieldmap as $irow)
                    {
                        $fieldname = $irow['fieldname'];
                        if (isset($_POST[$fieldname]))
                        {
                            if ($_POST[$fieldname] == "" && ($irow['type'] == 'D' || $irow['type'] == 'N' || $irow['type'] == 'K'))
                            { // can't add '' in Date column
                                // Do nothing
                            }
                            else if ($irow['type'] == '|')
                                {
                                    if (!strpos($irow['fieldname'], "_filecount"))
                                    {
                                        $json = $_POST[$fieldname];
                                        $phparray = json_decode(stripslashes($json));
                                        $filecount = 0;

                                        for ($i = 0; $filecount < count($phparray); $i++)
                                        {
                                            if ($_FILES[$fieldname."_file_".$i]['error'] != 4)
                                            {
                                                $target = Yii::app()->getConfig('uploaddir')."/surveys/". $thissurvey['sid'] ."/files/".randomChars(20);
                                                $size = 0.001 * $_FILES[$fieldname."_file_".$i]['size'];
                                                $name = rawurlencode($_FILES[$fieldname."_file_".$i]['name']);

                                                if (move_uploaded_file($_FILES[$fieldname."_file_".$i]['tmp_name'], $target))
                                                {
                                                    $phparray[$filecount]->filename = basename($target);
                                                    $phparray[$filecount]->name = $name;
                                                    $phparray[$filecount]->size = $size;
                                                    $pathinfo = pathinfo($_FILES[$fieldname."_file_".$i]['name']);
                                                    $phparray[$filecount]->ext = $pathinfo['extension'];
                                                    $filecount++;
                                                }
                                        }
                                    }

                                    $insert_data[$fieldname] = ls_json_encode($phparray);

                                }
                                else
                                {
                                    $insert_data[$fieldname] = count($phparray);
                                }
                            }
                            elseif ($irow['type'] == 'D')
                            {
                                Yii::app()->loadLibrary('Date_Time_Converter');
                                $qidattributes = getQuestionAttributeValues($irow['qid'], $irow['type']);
                                $dateformatdetails = getDateFormatDataForQID($qidattributes, $thissurvey);
                                $datetimeobj = new Date_Time_Converter($_POST[$fieldname],$dateformatdetails['phpdate']);
                                $insert_data[$fieldname] = $datetimeobj->convert("Y-m-d H:i:s");
                            }
                            else
                            {
                                $insert_data[$fieldname] = $_POST[$fieldname];
                            }
                        }
                    }

                    Survey_dynamic::sid($surveyid);
                    $new_response = new Survey_dynamic;
                    foreach($insert_data as $column => $value)
                    {
                        $new_response->$column = $value;
                    }
                    $new_response->save();
                    $last_db_id = $new_response->getPrimaryKey();
                    if (isset($_POST['closerecord']) && isset($_POST['token']) && $_POST['token'] != '') // submittoken
                    {
                        // get submit date
                        if (isset($_POST['closedate']))
                        { $submitdate = $_POST['closedate']; }
                        else
                        { $submitdate = dateShift(date("Y-m-d H:i:s"), "Y-m-d", $timeadjust); }

                        // check how many uses the token has left
                        $usesquery = "SELECT usesleft FROM {{tokens_}}$surveyid WHERE token='".$_POST['token']."'";
                        $usesresult = dbExecuteAssoc($usesquery);
                        $usesrow = $usesresult->readAll(); //$usesresult->row_array()
                        if (isset($usesrow)) { $usesleft = $usesrow[0]['usesleft']; }

                        // query for updating tokens
                        $utquery = "UPDATE {{tokens_$surveyid}}\n";
                        if (isTokenCompletedDatestamped($thissurvey))
                        {
                            if (isset($usesleft) && $usesleft<=1)
                            {
                                $utquery .= "SET usesleft=usesleft-1, completed='$submitdate'\n";
                            }
                            else
                            {
                                $utquery .= "SET usesleft=usesleft-1\n";
                            }
                        }
                        else
                        {
                            if (isset($usesleft) && $usesleft<=1)
                            {
                                $utquery .= "SET usesleft=usesleft-1, completed='Y'\n";
                            }
                            else
                            {
                                $utquery .= "SET usesleft=usesleft-1\n";
                            }
                        }
                        $utquery .= "WHERE token='".$_POST['token']."'";
                        $utresult = dbExecuteAssoc($utquery); //Yii::app()->db->Execute($utquery) or safeDie ("Couldn't update tokens table!<br />\n$utquery<br />\n".Yii::app()->db->ErrorMsg());

                        // save submitdate into survey table
                        $srid = Yii::app()->db->getLastInsertID(); // Yii::app()->db->getLastInsertID();
                        $sdquery = "UPDATE {{survey_$surveyid}} SET submitdate='".$submitdate."' WHERE id={$srid}\n";
                        $sdresult = dbExecuteAssoc($sdquery) or safeDie ("Couldn't set submitdate response in survey table!<br />\n$sdquery<br />\n");
                        $last_db_id = Yii::app()->db->getLastInsertID();
                    }
                    if (isset($_POST['save']) && $_POST['save'] == "on")
                    {
                        $srid = Yii::app()->db->getLastInsertID(); //Yii::app()->db->getLastInsertID();
                        $aUserData=Yii::app()->session;
                        //CREATE ENTRY INTO "saved_control"


                        $saved_control_table = '{{saved_control}}';

                        $columns = array("sid", "srid", "identifier", "access_code", "email", "ip",
                        "refurl", 'saved_thisstep', "status", "saved_date");
                        $values = array("'".$surveyid."'", "'".$srid."'", "'".$saver['identifier']."'", "'".$password."'", "'".$saver['email']."'", "'".$aUserData['ip_address']."'",
                        "'".getenv("HTTP_REFERER")."'", 0, "'"."S"."'", "'".dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", "'".Yii::app()->getConfig('timeadjust'))."'");

                        $SQL = "INSERT INTO $saved_control_table
                        (".implode(',',$columns).")
                        VALUES
                        (".implode(',',$values).")";

                        /*$scdata = array("sid"=>$surveyid,
                        "srid"=>$srid,
                        "identifier"=>$saver['identifier'],
                        "access_code"=>$password,
                        "email"=>$saver['email'],
                        "ip"=>$aUserData['ip_address'],
                        "refurl"=>getenv("HTTP_REFERER"),
                        'saved_thisstep' => 0,
                        "status"=>"S",
                        "saved_date"=>dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", Yii::app()->getConfig('timeadjust')));
                        $this->load->model('saved_control_model');*/
                        if (dbExecuteAssoc($SQL))
                        {
                            $scid =  Yii::app()->db->getLastInsertID(); // Yii::app()->db->getLastInsertID("{{saved_control}}","scid");

                            $aDataentrymsgs[] = CHtml::tag('font', array('class'=>'successtitle'), $clang->gT("Your survey responses have been saved successfully.  You will be sent a confirmation e-mail. Please make sure to save your password, since we will not be able to retrieve it for you."));
                            //$aDataentryoutput .= "<font class='successtitle'></font><br />\n";

                            $tokens_table = "{{tokens_$surveyid}}";
                            $last_db_id = Yii::app()->db->getLastInsertID();
                            if (tableExists($tokens_table)) //If the query fails, assume no tokens table exists
                            {
                                $tkquery = "SELECT * FROM {$tokens_table}";
                                $tkresult = dbExecuteAssoc($tkquery);
                                /*$tokendata = array (
                                "firstname"=> $saver['identifier'],
                                "lastname"=> $saver['identifier'],
                                "email"=>$saver['email'],
                                "token"=>randomChars(15),
                                "language"=>$saver['language'],
                                "sent"=>dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", $timeadjust),
                                "completed"=>"N");*/

                                $columns = array("firstname", "lastname", "email", "token",
                                "language", "sent", "completed");
                                $values = array("'".$saver['identifier']."'", "'".$saver['identifier']."'", "'".$saver['email']."'", "'".$password."'",
                                "'".randomChars(15)."'", "'".$saver['language']."'", "'"."N"."'");

                                $SQL = "INSERT INTO $token_table
                                (".implode(',',$columns).")
                                VALUES
                                (".implode(',',$values).")";
                                //$this->tokens_dynamic_model->insertToken($surveyid,$tokendata);
                                dbExecuteAssoc($SQL);
                                //Yii::app()->db->AutoExecute(db_table_name("tokens_".$surveyid), $tokendata,'INSERT');
                                $aDataentrymsgs[] = CHtml::tag('font', array('class'=>'successtitle'), $clang->gT("A token entry for the saved survey has been created too."));
                                //$aDataentryoutput .= "<font class='successtitle'></font><br />\n";
                                $last_db_id = Yii::app()->db->getLastInsertID();

                            }
                            if ($saver['email'])
                            {
                                //Send email
                                if (validateEmailAddress($saver['email']) && !returnGlobal('redo'))
                                {
                                    $subject = $clang->gT("Saved Survey Details");
                                    $message = $clang->gT("Thank you for saving your survey in progress.  The following details can be used to return to this survey and continue where you left off.  Please keep this e-mail for your reference - we cannot retrieve the password for you.");
                                    $message .= "\n\n".$thissurvey['name']."\n\n";
                                    $message .= $clang->gT("Name").": ".$saver['identifier']."\n";
                                    $message .= $clang->gT("Password").": ".$saver['password']."\n\n";
                                    $message .= $clang->gT("Reload your survey by clicking on the following link (or pasting it into your browser):").":\n";
                                    $message .= Yii::app()->getConfig('publicurl')."/index.php?sid=$surveyid&loadall=reload&scid=".$scid."&lang=".urlencode($saver['language'])."&loadname=".urlencode($saver['identifier'])."&loadpass=".urlencode($saver['password']);
                                    if (isset($tokendata['token'])) { $message .= "&token=".$tokendata['token']; }
                                    $from = $thissurvey['adminemail'];

                                    if (SendEmailMessage($message, $subject, $saver['email'], $from, $sitename, false, getBounceEmail($surveyid)))
                                    {
                                        $emailsent="Y";
                                        $aDataentrymsgs[] = CHtml::tag('font', array('class'=>'successtitle'), $clang->gT("An email has been sent with details about your saved survey"));
                                    }
                                }
                            }

                        }
                        else
                        {
                            safeDie("Unable to insert record into saved_control table.<br /><br />");
                        }

                    }
                    $aData['thisid'] = $last_db_id;
                }

                $aData['errormsg'] = $errormsg;

                $aData['dataentrymsgs'] = $aDataentrymsgs;

                $this->_renderWrappedTemplate('dataentry', 'insert', $aData);
            }

        }
    }

    /**
    * dataentry::view()
    * view a dataentry
    * @param mixed $surveyid
    * @param mixed $lang
    * @return
    */
    public function view($surveyid, $lang=NULL)
    {
        $surveyid = sanitize_int($surveyid);
        $lang = isset($_GET['lang']) ? $_GET['lang'] : NULL;
        if(isset($lang)) $lang=sanitize_languagecode($lang);
        $aViewUrls = array();

        if (hasSurveyPermission($surveyid, 'responses', 'read'))
        {
            $clang = Yii::app()->lang;

            $sDataEntryLanguage = Survey::model()->findByPk($surveyid)->language;
            $surveyinfo=getSurveyInfo($surveyid);

            $slangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
            $baselang = Survey::model()->findByPk($surveyid)->language;
            array_unshift($slangs,$baselang);

            if(is_null($lang) || !in_array($lang,$slangs))
            {
                $sDataEntryLanguage = $baselang;
                $blang = $clang;
            } else {
                Yii::app()->loadLibrary('Limesurvey_lang',array($lang));
                $blang = new Limesurvey_lang($lang);
                $sDataEntryLanguage = $lang;
            }

            $langlistbox = languageDropdown($surveyid,$sDataEntryLanguage);
            $thissurvey=getSurveyInfo($surveyid);

            //This is the default, presenting a blank dataentry form
            LimeExpressionManager::StartSurvey($surveyid, 'survey', NULL, false, LEM_PRETTY_PRINT_ALL_SYNTAX);
            $moveResult = LimeExpressionManager::NavigateForwards();

            $aData['thissurvey'] = $thissurvey;
            $aData['langlistbox'] = $langlistbox;
            $aData['surveyid'] = $surveyid;
            $aData['blang'] = $blang;
            $aData['site_url'] = Yii::app()->homeUrl;

            LimeExpressionManager::StartProcessingPage(true, Yii::app()->baseUrl);  // means that all variables are on the same page

            $aViewUrls[] = 'caption_view';

            Yii::app()->loadHelper('database');

            // SURVEY NAME AND DESCRIPTION TO GO HERE
            $degquery = "SELECT * FROM {{groups}} WHERE sid=$surveyid AND language='{$sDataEntryLanguage}' ORDER BY {{groups}}.group_order";
            $degresult = dbExecuteAssoc($degquery);
            // GROUP NAME
            $aDataentryoutput = '';

            foreach ($degresult->readAll() as $degrow)
            {
                LimeExpressionManager::StartProcessingGroup($degrow['gid'], ($thissurvey['anonymized']!="N"),$surveyid);

                $deqquery = "SELECT * FROM {{questions}} WHERE sid=$surveyid AND parent_qid=0 AND gid={$degrow['gid']} AND language='{$sDataEntryLanguage}'";
                $deqrows = (array) dbExecuteAssoc($deqquery)->readAll();
                $aDataentryoutput .= "\t<tr>\n"
                ."<td colspan='3' align='center'><strong>".flattenText($degrow['group_name'],true)."</strong></td>\n"
                ."\t</tr>\n";
                $gid = $degrow['gid'];

                $aDataentryoutput .= "\t<tr class='data-entry-separator'><td colspan='3'></td></tr>\n";

                // Perform a case insensitive natural sort on group name then question title of a multidimensional array
                usort($deqrows, 'groupOrderThenQuestionOrder');

                foreach ($deqrows as $deqrow)
                {
                    $qidattributes = getQuestionAttributeValues($deqrow['qid'], $deqrow['type']);
                    $cdata['qidattributes'] = $qidattributes;
                    $hidden = (isset($qidattributes['hidden']) ? $qidattributes['hidden'] : 0);
                    // TODO - can questions be hidden?  Are JavaScript variables names used?  Consistently with everywhere else?
                    //                    LimeExpressionManager::ProcessRelevance($qidattributes['relevance'],$deqrow['qid'],NULL,$deqrow['type'],$hidden);

                    // TMSW Conditions->Relevance:  Show relevance equation instead of conditions here - better yet, have data entry use survey-at-a-time but with different view

                    $qinfo = LimeExpressionManager::GetQuestionStatus($deqrow['qid']);
                    $relevance = trim($qinfo['info']['relevance']);
                    $explanation = trim($qinfo['relEqn']);
                    $validation = trim($qinfo['prettyValidTip']);
                    $qidattributes=getQuestionAttributeValues($deqrow['qid']);
                    $array_filter_help = flattenText($this->_array_filter_help($qidattributes, $sDataEntryLanguage, $surveyid));

                    if (($relevance != '' && $relevance != '1') || ($validation != '') || ($array_filter_help != ''))
                    {
                        $showme = '';
                        if ($bgc == "even") {$bgc = "odd";} else {$bgc = "even";} //Do no alternate on explanation row
                        if ($relevance != '' && $relevance != '1') {
                            $showme = "[".$blang->gT("Only answer this if the following conditions are met:")."]<br />$explanation\n";
                        }
                        if ($showme != '' && $validation != '') {
                            $showme .= '<br/>';
                        }
                        if ($validation != '') {
                            $showme .= "[".$blang->gT("The answer(s) must meet these validation criteria:")."]<br />$validation\n";
                        }
                        if ($showme != '' && $array_filter_help != '') {
                            $showme .= '<br/>';
                        }
                        if ($array_filter_help != '') {
                            $showme .= "[".$blang->gT("The answer(s) must meet these array_filter criteria:")."]<br />$array_filter_help\n";
                        }
                        $cdata['explanation'] = "<tr class ='data-entry-explanation'><td class='data-entry-small-text' colspan='3' align='left'>$showme</td></tr>\n";
                    }

                    //END OF GETTING CONDITIONS

                    //Alternate bgcolor for different groups
                    if (!isset($bgc)) {$bgc = "even";}
                    if ($bgc == "even") {$bgc = "odd";}
                    else {$bgc = "even";}

                    $qid = $deqrow['qid'];
                    $fieldname = "$surveyid"."X"."$gid"."X"."$qid";

                    $cdata['bgc'] = $bgc;
                    $cdata['fieldname'] = $fieldname;
                    $cdata['deqrow'] = $deqrow;
                    $cdata['clang'] = $clang;

                    //DIFFERENT TYPES OF DATA FIELD HERE
                    $cdata['blang'] = $blang;

                    $cdata['thissurvey'] = $thissurvey;
                    if ($deqrow['help'])
                    {
                        $hh = addcslashes($deqrow['help'], "\0..\37'\""); //Escape ASCII decimal 0-32 plus single and double quotes to make JavaScript happy.
                        $hh = htmlspecialchars($hh, ENT_QUOTES); //Change & " ' < > to HTML entities to make HTML happy.
                        $cdata['hh'] = $hh;
                        //$aDataentryoutput .= "\t<img src='$imageurl/help.gif' alt='".$blang->gT("Help about this question")."' align='right' onclick=\"javascript:alert('Question {$deqrow['title']} Help: $hh')\" />\n";
                    }
                    switch($deqrow['type'])
                    {
                        case "Q": //MULTIPLE SHORT TEXT
                        case "K":
                            $deaquery = "SELECT question,title FROM {{questions}} WHERE parent_qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' ORDER BY question_order";
                            $dearesult = dbExecuteAssoc($deaquery);
                            $cdata['dearesult'] = $dearesult->readAll();

                            break;

                        case "1": // multi scale^
                            $deaquery = "SELECT * FROM {{questions}} WHERE parent_qid={$deqrow['qid']} AND language='{$baselang}' ORDER BY question_order";
                            $dearesult = dbExecuteAssoc($deaquery);

                            $cdata['dearesult'] = $dearesult->readAll();

                            $oquery="SELECT other FROM {{questions}} WHERE qid={$deqrow['qid']} AND language='{$baselang}'";
                            $oresult=dbExecuteAssoc($oquery) or safeDie("Couldn't get other for list question<br />".$oquery);
                            foreach($oresult->readAll() as $orow)
                            {
                                $cdata['fother']=$orow['other'];
                            }

                            break;

                        case "L": //LIST drop-down/radio-button list
                        case "!":
                            //                            $qidattributes=getQuestionAttributeValues($deqrow['qid']);
                            if ($deqrow['type']=='!' && trim($qidattributes['category_separator'])!='')
                            {
                                $optCategorySeparator = $qidattributes['category_separator'];
                            }
                            else
                            {
                                unset($optCategorySeparator);
                            }
                            $defexists="";
                            $deaquery = "SELECT * FROM {{answers}} WHERE qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' ORDER BY sortorder, answer";
                            $dearesult = dbExecuteAssoc($deaquery);
                            //$aDataentryoutput .= "\t<select name='$fieldname'>\n";
                            $aDatatemp='';
                            if (!isset($optCategorySeparator))
                            {
                                foreach ($dearesult->readAll() as $dearow)
                                {
                                    $aDatatemp .= "<option value='{$dearow['code']}'";
                                    //if ($dearow['default_value'] == "Y") {$aDatatemp .= " selected='selected'"; $defexists = "Y";}
                                    $aDatatemp .= ">{$dearow['answer']}</option>\n";
                                }
                            }
                            else
                            {
                                $defaultopts = array();
                                $optgroups = array();

                                foreach ($dearesult->readAll() as $dearow)
                                {
                                    list ($categorytext, $answertext) = explode($optCategorySeparator,$dearow['answer']);
                                    if ($categorytext == '')
                                    {
                                        $defaultopts[] = array ( 'code' => $dearow['code'], 'answer' => $answertext, 'default_value' => $dearow['assessment_value']);
                                    }
                                    else
                                    {
                                        $optgroups[$categorytext][] = array ( 'code' => $dearow['code'], 'answer' => $answertext, 'default_value' => $dearow['assessment_value']);
                                    }
                                }
                                foreach ($optgroups as $categoryname => $optionlistarray)
                                {
                                    $aDatatemp .= "<optgroup class=\"dropdowncategory\" label=\"".$categoryname."\">\n";
                                    foreach ($optionlistarray as $optionarray)
                                    {
                                        $aDatatemp .= "\t<option value='{$optionarray['code']}'";
                                        //if ($optionarray['default_value'] == "Y") {$aDatatemp .= " selected='selected'"; $defexists = "Y";}
                                        $aDatatemp .= ">{$optionarray['answer']}</option>\n";
                                    }
                                    $aDatatemp .= "</optgroup>\n";
                                }
                                foreach ($defaultopts as $optionarray)
                                {
                                    $aDatatemp .= "\t<option value='{$optionarray['code']}'";
                                    //if ($optionarray['default_value'] == "Y") {$aDatatemp .= " selected='selected'"; $defexists = "Y";}
                                    $aDatatemp .= ">{$optionarray['answer']}</option>\n";
                                }
                            }

                            $oquery="SELECT other FROM {{questions}} WHERE qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}'";
                            $oresult=dbExecuteAssoc($oquery) or safeDie("Couldn't get other for list question<br />");
                            foreach($oresult->readAll() as $orow)
                            {
                                $fother=$orow['other'];
                            }

                            $cdata['fother'] = $fother;
                            $cdata['defexists'] = $defexists;
                            $cdata['datatemp'] = $aDatatemp;

                            break;
                        case "O": //LIST WITH COMMENT drop-down/radio-button list + textarea
                            $defexists="";
                            $deaquery = "SELECT * FROM {{answers}} WHERE qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' ORDER BY sortorder, answer";
                            $dearesult = dbExecuteAssoc($deaquery);
                            //$aDataentryoutput .= "\t<select name='$fieldname'>\n";
                            $aDatatemp='';
                            foreach ($dearesult->readAll() as $dearow)
                            //                            while ($dearow = $dearesult->FetchRow())
                            {
                                $aDatatemp .= "<option value='{$dearow['code']}'";
                                //if ($dearow['default_value'] == "Y") {$aDatatemp .= " selected='selected'"; $defexists = "Y";}
                                $aDatatemp .= ">{$dearow['answer']}</option>\n";

                            }
                            $cdata['datatemp'] = $aDatatemp;
                            $cdata['defexists'] = $defexists;

                            break;
                        case "R": //RANKING TYPE QUESTION
                            $thisqid=$deqrow['qid'];
                            $ansquery = "SELECT * FROM {{answers}} WHERE qid=$thisqid AND language='{$sDataEntryLanguage}' ORDER BY sortorder, answer";
                            $ansresult = dbExecuteAssoc($ansquery);
                            $anscount = $ansresult->getRowCount();

                            $cdata['thisqid'] = $thisqid;
                            $cdata['anscount'] = $anscount;

                            foreach ($ansresult->readAll() as $ansrow)
                            {
                                $answers[] = array($ansrow['code'], $ansrow['answer']);
                            }
                            for ($i=1; $i<=$anscount; $i++)
                            {
                                if (isset($fname))
                                {
                                    $myfname=$fname.$i;
                                }
                                if (isset($myfname) && Yii::app()->session[$myfname])
                                {
                                    $existing++;
                                }
                            }
                            for ($i=1; $i<=$anscount; $i++)
                            {
                                if (isset($fname))
                                {
                                    $myfname = $fname.$i;
                                }
                                if (isset($myfname) && Yii::app()->session[$myfname])
                                {
                                    foreach ($answers as $ans)
                                    {
                                        if ($ans[0] == Yii::app()->session[$myfname])
                                        {
                                            $thiscode=$ans[0];
                                            $thistext=$ans[1];
                                        }
                                    }
                                }
                                if (!isset($ranklist)) {$ranklist="";}
                                $ranklist .= "&nbsp;<font color='#000080'>$i:&nbsp;<input class='ranklist' type='text' name='RANK$i' id='RANK_$thisqid$i'";
                                if (isset($myfname) && Yii::app()->session[$myfname])
                                {
                                    $ranklist .= " value='";
                                    $ranklist .= $thistext;
                                    $ranklist .= "'";
                                }
                                $ranklist .= " onFocus=\"this.blur()\"  />\n";
                                $ranklist .= "<input type='hidden' id='d$fieldname$i' name='$fieldname$i' value='";
                                $chosen[]=""; //create array
                                if (isset($myfname) && Yii::app()->session[$myfname])
                                {
                                    $ranklist .= $thiscode;
                                    $chosen[]=array($thiscode, $thistext);
                                }
                                $ranklist .= "' /></font>\n";
                                $ranklist .= "<img src='".Yii::app()->getConfig('imageurl')."/cut.gif' alt='".$blang->gT("Remove this item")."' title='".$blang->gT("Remove this item")."' ";
                                if (!isset($existing) || $i != $existing)
                                {
                                    $ranklist .= "style='display:none'";
                                }
                                $mfn=$fieldname.$i;
                                $ranklist .= " id='cut_$thisqid$i' onclick=\"deletethis_$thisqid(document.addsurvey.RANK_$thisqid$i.value, document.addsurvey.d$fieldname$i.value, document.addsurvey.RANK_$thisqid$i.id, this.id)\" /><br />\n\n";
                            }
                            if (!isset($choicelist)) {$choicelist="";}
                            $choicelist .= "<select size='$anscount' class='choicelist' name='CHOICES' id='CHOICES_$thisqid' onclick=\"rankthis_$thisqid(this.options[this.selectedIndex].value, this.options[this.selectedIndex].text)\" >\n";
                            foreach ($answers as $ans)
                            {

                                if (!in_array($ans, $chosen))
                                {
                                    $choicelist .= "\t<option value='{$ans[0]}'>{$ans[1]}</option>\n";
                                }
                            }
                            $choicelist .= "</select>\n";
                            $cdata['choicelist'] = $choicelist;
                            $cdata['ranklist'] = $ranklist;
                            if (isset($multifields))
                                $cdata['multifields'] = $multifields;

                            $choicelist="";
                            $ranklist="";
                            unset($answers);
                            break;
                        case "M": //Multiple choice checkbox (Quite tricky really!)
                            //                            $qidattributes=getQuestionAttributeValues($deqrow['qid']);
                            if (trim($qidattributes['display_columns'])!='')
                            {
                                $dcols=$qidattributes['display_columns'];
                            }
                            else
                            {
                                $dcols=0;
                            }
                            $meaquery = "SELECT title, question FROM {{questions}} WHERE parent_qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' ORDER BY question_order";
                            $mearesult = dbExecuteAssoc($meaquery);
                            $meacount = $mearesult->getRowCount();

                            $cdata['dcols'] = $dcols;
                            $cdata['meacount'] = $meacount;
                            $cdata['mearesult'] = $mearesult->readAll();

                            break;
                        case "I": //Language Switch
                            $slangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
                            $sbaselang = Survey::model()->findByPk($surveyid)->language;
                            array_unshift($slangs,$sbaselang);
                            $cdata['slangs'] = $slangs;

                            break;
                        case "P": //Multiple choice with comments checkbox + text
                            //$aDataentryoutput .= "<table border='0'>\n";
                            $meaquery = "SELECT * FROM {{questions}} WHERE parent_qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' ORDER BY question_order, question";
                            $mearesult = dbExecuteAssoc($meaquery);

                            $cdata['mearesult'] = $mearesult->readAll();

                            break;
                        case "|":
                            //                            $qidattributes = getQuestionAttributeValues($deqrow['qid']);
                            $cdata['qidattributes'] = $qidattributes;

                            $maxfiles = $qidattributes['max_num_of_files'];
                            $cdata['maxfiles'] = $maxfiles;

                            break;
                        case "A": //ARRAY (5 POINT CHOICE) radio-buttons
                            $meaquery = "SELECT title, question FROM {{questions}} WHERE parent_qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' ORDER BY question_order";
                            $mearesult = dbExecuteAssoc($meaquery);

                            $cdata['mearesult'] = $mearesult->readAll();

                            break;
                        case "B": //ARRAY (10 POINT CHOICE) radio-buttons
                            $meaquery = "SELECT title, question FROM {{questions}} WHERE parent_qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' ORDER BY question_order";
                            $mearesult = dbExecuteAssoc($meaquery);
                            $cdata['mearesult'] = $mearesult->readAll();

                        case "C": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                            $meaquery = "SELECT title, question FROM {{questions}} WHERE parent_qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' ORDER BY question_order";
                            $mearesult=dbExecuteAssoc($meaquery);
                            $cdata['mearesult'] = $mearesult->readAll();

                            break;
                        case "E": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                            $meaquery = "SELECT title, question FROM {{questions}} WHERE parent_qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' ORDER BY question_order";
                            $mearesult=dbExecuteAssoc($meaquery) or safeDie ("Couldn't get answers, Type \"E\"<br />$meaquery<br />");
                            $cdata['mearesult'] = $mearesult->readAll();

                            break;
                        case ":": //ARRAY (Multi Flexi)
                            //                            $qidattributes=getQuestionAttributeValues($deqrow['qid']);
                            if (trim($qidattributes['multiflexible_max'])!='' && trim($qidattributes['multiflexible_min']) =='') {
                                $maxvalue=$qidattributes['multiflexible_max'];
                                $minvalue=1;
                            }
                            if (trim($qidattributes['multiflexible_min'])!='' && trim($qidattributes['multiflexible_max']) =='') {
                                $minvalue=$qidattributes['multiflexible_min'];
                                $maxvalue=$qidattributes['multiflexible_min'] + 10;
                            }
                            if (trim($qidattributes['multiflexible_min'])=='' && trim($qidattributes['multiflexible_max']) =='') {
                                $minvalue=1;
                                $maxvalue=10;
                            }
                            if (trim($qidattributes['multiflexible_min']) !='' && trim($qidattributes['multiflexible_max']) !='') {
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
                            $cdata['minvalue'] = $minvalue;
                            $cdata['maxvalue'] = $maxvalue;
                            $cdata['stepvalue'] = $stepvalue;

                            $lquery = "SELECT question, title FROM {{questions}} WHERE parent_qid={$deqrow['qid']} and scale_id=1 and language='{$sDataEntryLanguage}' ORDER BY question_order";
                            $lresult=dbExecuteAssoc($lquery) or die ("Couldn't get labels, Type \":\"<br />$lquery<br />");
                            $cdata['lresult'] = $lresult->readAll();

                            $meaquery = "SELECT question, title FROM {{questions}} WHERE parent_qid={$deqrow['qid']} and scale_id=0 and language='{$sDataEntryLanguage}' ORDER BY question_order";
                            $mearesult=dbExecuteAssoc($meaquery) or die ("Couldn't get answers, Type \":\"<br />$meaquery<br />");
                            $cdata['mearesult'] = $mearesult->readAll();

                            break;
                        case ";": //ARRAY (Multi Flexi)

                            $lquery = "SELECT * FROM {{questions}} WHERE scale_id=1 and parent_qid={$deqrow['qid']} and language='{$sDataEntryLanguage}' ORDER BY question_order";
                            $lresult=dbExecuteAssoc($lquery) or die ("Couldn't get labels, Type \":\"<br />$lquery<br />");
                            $cdata['lresult'] = $lresult->readAll();

                            $meaquery = "SELECT * FROM {{questions}} WHERE scale_id=0 and parent_qid={$deqrow['qid']} and language='{$sDataEntryLanguage}' ORDER BY question_order";
                            $mearesult=dbExecuteAssoc($meaquery) or die ("Couldn't get answers, Type \":\"<br />$meaquery<br />");

                            $cdata['mearesult'] = $mearesult->readAll();

                            break;
                        case "F": //ARRAY (Flexible Labels)
                        case "H":
                            $meaquery = "SELECT * FROM {{questions}} WHERE parent_qid={$deqrow['qid']} and language='{$sDataEntryLanguage}' ORDER BY question_order";
                            $mearesult=dbExecuteAssoc($meaquery) or safeDie ("Couldn't get answers, Type \"E\"<br />$meaquery<br />");

                            $cdata['mearesult'] = $mearesult->readAll();

                            $fquery = "SELECT * FROM {{answers}} WHERE qid={$deqrow['qid']} and language='{$sDataEntryLanguage}' ORDER BY sortorder, code";
                            $fresult = dbExecuteAssoc($fquery);
                            $cdata['fresult'] = $fresult->readAll();
                            break;
                    }

                    $cdata['sDataEntryLanguage'] = $sDataEntryLanguage;
                    $viewdata = $this->getController()->render("/admin/dataentry/content_view",$cdata,TRUE);
                    $viewdata_em = LimeExpressionManager::ProcessString($viewdata, $deqrow['qid'], NULL, false, 1, 1);
                    $aDataentryoutput .= $viewdata_em;
                }
                LimeExpressionManager::FinishProcessingGroup();
            }

            LimeExpressionManager::FinishProcessingPage();
            $aDataentryoutput .= LimeExpressionManager::GetRelevanceAndTailoringJavaScript();

            $aViewUrls['output'] = $aDataentryoutput;

            $aData['thissurvey'] = $thissurvey;
            $aData['surveyid'] = $surveyid;
            $aData['sDataEntryLanguage'] = $sDataEntryLanguage;

            if ($thissurvey['active'] == "Y" && $thissurvey['allowsave'] == "Y")
            {
                $slangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
                $sbaselang = Survey::model()->findByPk($surveyid)->language;
                array_unshift($slangs,$sbaselang);
                $aData['slangs'] = $slangs;
                $aData['baselang'] = $baselang;
            }

            $aViewUrls[] = 'active_html_view';

            $this->_renderWrappedTemplate('dataentry', $aViewUrls, $aData);
        }
    }

    private function _getEncodingsArray()
    {
        $clang = Yii::app()->lang;
        return array("armscii8"=>$clang->gT("ARMSCII-8 Armenian"),
        "ascii"=>$clang->gT("US ASCII").' (ascii)',
        "auto"=>$clang->gT("Automatic").' (auto)',
        "big5"=>$clang->gT("Big5 Traditional Chinese").' (big5)',
        "binary"=>$clang->gT("Binary pseudo charset").' (binary)',
        "cp1250"=>$clang->gT("Windows Central European").' (cp1250)',
        "cp1251"=>$clang->gT("Windows Cyrillic").' (cp1251)',
        "cp1256"=>$clang->gT("Windows Arabic").' (cp1256)',
        "cp1257"=>$clang->gT("Windows Baltic").' (cp1257)',
        "cp850"=>$clang->gT("DOS West European").' (cp850)',
        "cp852"=>$clang->gT("DOS Central European").' (cp852)',
        "cp866"=>$clang->gT("DOS Russian").' (cp866)',
        "cp932"=>$clang->gT("SJIS for Windows Japanese").'(cp932)',
        "dec8"=>$clang->gT("DEC West European").' (dec8)',
        "eucjpms"=>$clang->gT("UJIS for Windows Japanese").' (eucjpms)',
        "euckr"=>$clang->gT("EUC-KR Korean").' (euckr)',
        "gb2312"=>$clang->gT("GB2312 Simplified Chinese").' (gb2312)',
        "gbk"=>$clang->gT("GBK Simplified Chinese").' (gbk)',
        "geostd8"=>$clang->gT("GEOSTD8 Georgian").' (geostd8)',
        "greek"=>$clang->gT("ISO 8859-7 Greek").' (greek)',
        "hebrew"=>$clang->gT("ISO 8859-8 Hebrew").' (hebrew)',
        "hp8"=>$clang->gT("HP West European").' (hp8)',
        "keybcs2"=>$clang->gT("DOS Kamenicky Czech-Slovak").' (keybcs2)',
        "koi8r"=>$clang->gT("KOI8-R Relcom Russian").' (koi8r)',
        "koi8u"=>$clang->gT("KOI8-U Ukrainian").' (koi8u)',
        "latin1"=>$clang->gT("cp1252 West European").' (latin1)',
        "latin2"=>$clang->gT("ISO 8859-2 Central European").' (latin2)',
        "latin5"=>$clang->gT("ISO 8859-9 Turkish").' (latin5)',
        "latin7"=>$clang->gT("ISO 8859-13 Baltic").' (latin7)',
        "macce"=>$clang->gT("Mac Central European").' (macce)',
        "macroman"=>$clang->gT("Mac West European").' (macroman)',
        "sjis"=>$clang->gT("Shift-JIS Japanese").' (sjis)',
        "swe7"=>$clang->gT("7bit Swedish").' (swe7)',
        "tis620"=>$clang->gT("TIS620 Thai").' (tis620)',
        "ucs2"=>$clang->gT("UCS-2 Unicode").' (ucs2)',
        "ujis"=>$clang->gT("EUC-JP Japanese").' (ujis)',
        "utf8"=>$clang->gT("UTF-8 Unicode"). ' (utf8)');
    }

    private function _prepFieldValues($fieldnames, $field, $fieldcount, $donotimport)
    {
        $fieldvalues = explode( "\t", str_replace("\n", "", $field), $fieldcount+1 );

        // Excel likes to quote fields sometimes. =(
        $fieldvalues = preg_replace('/^"(.*)"$/s','\1',$fieldvalues);

        // Be careful about the order of these arrays:
        // lbrace has to be substituted *last*
        $fieldvalues= str_replace( array("{newline}", "{cr}", "{tab}", "{quote}", "{lbrace}"),
        array("\n", "\r", "\t", "\"", "{"),
        $fieldvalues
        );

        //remove any fields which no longer exist
        if (isset($donotimport))
        {
            foreach ($donotimport as $not)
            {
                unset($fieldvalues[$not]);
            }
        }

        // Sometimes columns with nothing in them get omitted by excel
        while (count($fieldnames) > count($fieldvalues))
        {
            $fieldvalues[]="";
        }

        // Sometimes columns with nothing in them get added by excel
        while ( count($fieldnames) < count($fieldvalues) &&
        trim( $fieldvalues[count($fieldvalues)-1] ) == "" )
        {
            unset($fieldvalues[count($fieldvalues)-1]);
        }

        // Make this safe for DB (*after* we undo first excel's
        // and then our escaping).
        $fieldvalues = array_map( 'dbQuoteAll', $fieldvalues );
        $fieldvalues = str_replace( dbQuoteAll('{question_not_shown}'), 'NULL', $fieldvalues );

        return $fieldvalues;
    }

    /*
    * This is a duplicate of the array_filter_help function in printablesurvey.php
    */
    private function _array_filter_help($qidattributes, $surveyprintlang, $surveyid) {
        $clang = $this->getController()->lang;
        $output = "";
        if(!empty($qidattributes['array_filter']))
        {
            $newquestiontext = Questions::model()->findByAttributes(array('title' => $qidattributes['array_filter'], 'language' => $surveyprintlang, 'sid' => $surveyid))->getAttribute('question');
            $output .= "\n<p class='extrahelp'>
            ".sprintf($clang->gT("Only answer this question for the items you selected in question %s ('%s')"),$qidattributes['array_filter'], flattenText(breakToNewline($newquestiontext['question'])))."
            </p>\n";
        }
        if(!empty($qidattributes['array_filter_exclude']))
        {
            $newquestiontext = Questions::model()->findByAttributes(array('title' => $qidattributes['array_filter_exclude'], 'language' => $surveyprintlang, 'sid' => $surveyid))->getAttribute('question');

            $output .= "\n    <p class='extrahelp'>
            ".sprintf($clang->gT("Only answer this question for the items you did not select in question %s ('%s')"),$qidattributes['array_filter_exclude'], breakToNewline($newquestiontext['question']))."
            </p>\n";
        }
        return $output;
    }

    /**
    * Renders template(s) wrapped in header and footer
    *
    * @param string $sAction Current action, the folder to fetch views from
    * @param string|array $aViewUrls View url(s)
    * @param array $aData Data to be passed on. Optional.
    */
    protected function _renderWrappedTemplate($sAction = 'dataentry', $aViewUrls = array(), $aData = array())
    {
        if (!isset($aData['display']['menu_bars']['browse']))
        {
            $aData['display']['menu_bars']['browse'] = $this->getController()->lang->gT("Data entry");
        }
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }

}


