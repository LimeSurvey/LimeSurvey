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
*   $Id$
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

    function iteratesurvey()
    {
        $aData = array();

        $surveyid = sanitize_int(Yii::app()->request->getParam('surveyid'));
        if (!empty($_REQUEST['sid'])) {
            $surveyid = sanitize_int($_REQUEST['sid']);
        }
        $aData['surveyid'] = $surveyid;
        $aData['clang'] = $this->getController()->lang;
        $aData['success'] = false;
        if (hasSurveyPermission($surveyid,'surveyactivation','update'))
        {
            if (Yii::app()->request->getParam('unfinalizeanswers') == 'true')
            {
                Survey_dynamic::sid($surveyid);
                Yii::app()->db->createCommand("DELETE from {{survey_$surveyid}} WHERE submitdate IS NULL AND token in (SELECT * FROM ( SELECT answ2.token from {{survey_$surveyid}} AS answ2 WHERE answ2.submitdate IS NOT NULL) tmp )")->execute();
                // Then set all remaining answers to incomplete state
                Yii::app()->db->createCommand("UPDATE {{survey_$surveyid}} SET submitdate=NULL, lastpage=NULL")->execute();
                // Finally, reset the token completed and sent status
                Yii::app()->db->createCommand("UPDATE {{tokens_$surveyid}} SET sent='N', remindersent='N', remindercount=0, completed='N', usesleft=1 where usesleft=0")->execute();
                $aData['success']=true;
            }
            $this->_renderWrappedTemplate('dataentry', 'iteratesurvey', $aData);
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
        $fieldmap = createFieldMap($surveyid,false,false,getBaseLanguageFromSurveyID($surveyid));

        foreach ($aFileContents as $row)
        {
            if (trim($row) != "") {
                $recordcount++;

                $fieldvalues = $this->_prepFieldValues($aFieldnames, $row, $nbOfFields, $donotimport);

                $fielddata = ($aFieldnames === array() && $fieldvalues === array() ? array()
                : array_combine($aFieldnames, $fieldvalues));
                foreach ($fielddata as $fieldname => $fielddatum)
                {
                    $cutname = substr($fieldname, 1, -1);
                    if (array_key_exists($cutname, $fieldmap))
                    {
                        $q = $fieldmap[$cutname];
                        if (is_a($q, 'QuestionModule'))
                            $fielddata[$fieldname] = $q->filter($fielddatum, 'db');
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
        //sort list of available encodings
        asort($aEncodings);

        //get default character set from global settings
        $thischaracterset=getGlobalSetting('characterset');

        //if no encoding was set yet, use the old "utf8" default
        if($thischaracterset == "")
        {
            $thischaracterset = "utf8";
        }

        // Create encodings list using the Yii's CHtml helper
        $charsetsout = CHtml::listOptions($thischaracterset, $aEncodings, $aEncodings);

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
                $iRecordCount = 0;
                $aSRIDConversions=array();
                foreach ($resultOldValues->readAll() as $row)
                {
                    $iOldID=$row['id'];
                    unset($row['id']);
                    // Remove NULL values
                    $row=array_filter($row, 'strlen');
                    //$sInsertSQL=Yii::app()->db->GetInsertSQL($activetable, $row);
                    $sInsertSQL="INSERT into {$activetable} (".implode(",", array_map("dbQuoteID", array_keys($row))).") VALUES (".implode(",", array_map("dbQuoteAll",array_values($row))).")";
                    $result = dbExecuteAssoc($sInsertSQL) or show_error("Error:<br />$sInsertSQL<br />");

                    $aSRIDConversions[$iOldID] = getLastInsertID($activetable);
                    $iRecordCount++;
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
                    $iRecordCountT=0;
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
                        $iRecordCountT++;
                    }
                    Yii::app()->session['flashmessage'] = sprintf($clang->gT("%s old response(s) and according timings were successfully imported."),$iRecordCount,$iRecordCountT);
                }
                $this->getController()->redirect(Yii::app()->getController()->createUrl("/admin/responses/sa/index/surveyid/{$surveyid}"));
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
            $fnresult=$fnresult->readAll();
            $fncount = count($fnresult);

            $fnrows = array(); //Create an empty array in case FetchRow does not return any rows
            foreach ($fnresult as $fnrow)
            {
                $fnrows[] = $fnrow;
                $private=$fnrow['anonymized'];
                $datestamp=$fnrow['datestamp'];
                $ipaddr=$fnrow['ipaddr'];
            } // Get table output into array


            // Perform a case insensitive natural sort on group name then question title of a multidimensional array
            // $fnames = (Field Name in Survey Table, Short Title of Question, Question Type, Field Name, Question Code, Predetermined Answers if exist)

            $q = new StdClass;
            $q->fieldname="completed";
            $q->surveyid=$surveyid;
            $q->gid="";
            $q->id="";
            $q->aid="";
            $q->title="";
            $q->text=$clang->gT("Completed");
            $q->group_name="";
            $fnames['completed'] = $q;

            $fnames=array_merge($fnames,createFieldMap($surveyid,false,false,$sDataEntryLanguage));

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

                $fieldmap = createFieldMap($surveyid,false,false,getBaseLanguageFromSurveyID($surveyid));
                foreach($fieldmap as $q)
                {
                    if (isset($responses[$q->fieldname]))
                    {
                        $results1[$q->fieldname]=$responses[$q->fieldname];
                    }
                    else
                    {
                        $results1[$q->fieldname]="";
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

            $aViewUrls[] = 'dataentry_header_view';
            $aViewUrls[] = 'edit';

            $highlight = FALSE;
            unset($fnames['lastpage']);
            $output = '';
            foreach ($results as $idrow)
            {
                $q = reset($fnames);
                do
                {
                    if (isset($idrow[$q->fieldname]) )
                    {
                        $answer = $idrow[ $q->fieldname ];
                    }
                    $output .= "\t<tr";
                    if ($highlight) $output .=" class='odd'";
                    else $output .=" class='even'";

                    $highlight=!$highlight;
                    $output .=">\n"
                    ."<td>"
                    ."\n";
                    $output .= stripJavaScript($q->text);
                    $output .= "</td>\n"
                    ."<td>\n";

                    if (is_a($q, 'QuestionModule'))
                    {
                        $output .= $q->getDataEntry($idrow, $fnames, $sDataEntryLanguage);
                    }
                    else if ($q->fieldname == "completed")
                    {
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

                        $output .= CHtml::dropDownList('completed', $selected, $select_options);
                    }
                    else if ($q->fieldname == "id")
                    {
                        $output .= CHtml::tag('span', array('style' => 'font-weight: bold;'), '&nbsp;'.$idrow[$q->fieldname]);
                    }
                    else
                    {
                        $output .= "\t<input type='text' name='{$q->fieldname}' value='"
                        .$idrow[$q->fieldname] . "' />\n";
                    }

                    $output .= "        </td>
                    </tr>\n";
                } while ($q=next($fnames));
            }
            $output .= "</table>\n"
            ."<p>\n";

            $aData['sDataEntryLanguage'] = $sDataEntryLanguage;

            if (!hasSurveyPermission($surveyid, 'responses','update'))
            { // if you are not survey owner or super admin you cannot modify responses
                $output .= "<p><input type='button' value='".$clang->gT("Save")."' disabled='disabled'/></p>\n";
            }
            elseif ($subaction == "edit" && hasSurveyPermission($surveyid,'responses','update'))
            {
                $aData['part'] = 'edit';
                $output .= $this->getController()->render('/admin/dataentry/edit', $aData, TRUE);
            }
            elseif ($subaction == "editsaved" && hasSurveyPermission($surveyid,'responses','update'))
            {
                $aData['part'] = 'editsaved';
                $output .= $this->getController()->render('/admin/dataentry/edit', $aData, TRUE);
            }

            $output .= "</form>\n";

            $aViewUrls['output'] = $output;
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
        if (isset($_REQUEST['surveyid']) && !empty($_REQUEST['surveyid']))
        {
            $surveyid = $_REQUEST['surveyid'];
        }
        if (!empty($_REQUEST['sid'])) $surveyid = (int)$_REQUEST['sid'];

        $surveyid = sanitize_int($surveyid);
        $id = $_REQUEST['id'];

        $aData = array(
        'surveyid' => $surveyid,
        'id' => $id
        );

        if (hasSurveyPermission($surveyid, 'responses','read') && hasSurveyPermission($surveyid, 'responses', 'delete'))
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
        $aData=array();
        $subaction = Yii::app()->request->getPost('subaction');
        if (isset($_REQUEST['surveyid'])) $surveyid = $_REQUEST['surveyid'];
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

            $fieldmap = createFieldMap($surveyid,false,false,getBaseLanguageFromSurveyID($surveyid));

            $thissurvey = getSurveyInfo($surveyid);
            $updateqr = "UPDATE $surveytable SET \n";

            foreach ($fieldmap as $q)
            {
                if ($q->fieldname=='id') continue;
                if (isset($_POST[$q->fieldname]))
                {
                    $thisvalue=$_POST[$q->fieldname];
                }
                else
                {
                    $thisvalue="";
                }

                if ($q->fieldname == 'lastpage')
                {
                    $thisvalue=0;
                }
                elseif ($q->fieldname == 'submitdate')
                {
                    if (isset($_POST['completed']) && ($_POST['completed']== "N"))
                    {
                        $updateqr .= dbQuoteID($q->fieldname) . " = NULL, \n";
                    }
                    elseif (isset($_POST['completed']) && $thisvalue=="")
                    {
                        $updateqr .= dbQuoteID($q->fieldname)." = " . dbQuoteAll($_POST['completed']) . ", \n";
                    }
                    else
                    {
                        $updateqr .= dbQuoteID($q->fieldname)." = " . dbQuoteAll($thisvalue) . ", \n";
                    }
                }
                elseif(is_a($q, 'QuestionModule'))
                {
                    $thisvalue = $q->filter($thisvalue, 'dataentry');
                    $updateqr .= dbQuoteID($q->fieldname) . ' = ' . (is_null($thisvalue) ? 'NULL' : dbQuoteAll($thisvalue)) . ", \n";
                }
                else
                {
                    $updateqr .= dbQuoteID($q->fieldname)." = " . dbQuoteAll($thisvalue) . ", \n";
                }
            }
            $updateqr = substr($updateqr, 0, -3);
            $updateqr .= " WHERE id=$id";
            $updateres = dbExecuteAssoc($updateqr) or safeDie("Update failed:<br />\n<br />$updateqr");

            $onerecord_link = $this->getController()->createUrl('/').'/admin/responses/view/surveyid/'.$surveyid.'/id/'.$id;
            $allrecords_link = $this->getController()->createUrl('/').'/admin/responses/index/surveyid/'.$surveyid;
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
                    $tcquery = "SELECT completed from {{tokens_{$surveyid}}} WHERE token=".dbQuoteAll($_POST['token']);
                    $tcresult = dbExecuteAssoc($tcquery);
                    $tcresult = $tcresult->readAll();
                    $tccount = count($tcresult);
                    foreach ($tcresult as $tcrow)
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
                        $aquery = "SELECT id,startlanguage FROM $surveytable WHERE token=".dbQuoteAll($_POST['token']);
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
                        $this->getController()->createUrl('/admin/dataentry/sa/editdata/subaction/edit/id/'.$lastanswfortoken.'/surveyid/'.$surveyid.'/lang/'.$rlanguage),
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
                    $fieldmap = createFieldMap($surveyid,false,false,getBaseLanguageFromSurveyID($surveyid));
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

                    foreach ($fieldmap as $q)
                    {
                        if (isset($_POST[$q->fieldname]))
                        {
                            if (is_a($q, 'QuestionModule'))
                                $data = $q->filter($_POST[$q->fieldname], 'dataentryinsert');
                            else
                                $data = $_POST[$q->fieldname];
                            if ($data!==null) $insert_data[$q->fieldname] = $data;
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
                        $usesquery = "SELECT usesleft FROM {{tokens_}}$surveyid WHERE token=".dbQuoteAll($_POST['token']);
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
                        $utquery .= "WHERE token=".dbQuoteAll($_POST['token']);
                        $utresult = dbExecuteAssoc($utquery); //Yii::app()->db->Execute($utquery) or safeDie ("Couldn't update tokens table!<br />\n$utquery<br />\n".Yii::app()->db->ErrorMsg());

                        // save submitdate into survey table
                        $sdquery = "UPDATE {{survey_$surveyid}} SET submitdate='".$submitdate."' WHERE id={$new_response}\n";
                        $sdresult = dbExecuteAssoc($sdquery) or safeDie ("Couldn't set submitdate response in survey table!<br />\n$sdquery<br />\n");
                        $last_db_id = getLastInsertID("{{survey_$surveyid}}");
                    }
                    if (isset($_POST['save']) && $_POST['save'] == "on")
                    {
                        $srid = $last_db_id;
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
                            $scid =  getLastInsertID('{{saved_control}}');

                            $aDataentrymsgs[] = CHtml::tag('font', array('class'=>'successtitle'), $clang->gT("Your survey responses have been saved successfully.  You will be sent a confirmation e-mail. Please make sure to save your password, since we will not be able to retrieve it for you."));
                            //$aDataentryoutput .= "<font class='successtitle'></font><br />\n";

                            $tokens_table = "{{tokens_$surveyid}}";
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
                $blang = new Limesurvey_lang($baselang);
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

                $results = Questions::model()->with('question_types')->findAllByAttributes(array('gid' => $degrow['gid'],'parent_qid' => '0', 'language' => $sDataEntryLanguage), array('order' => 'question_order'));
                $aDataentryoutput .= "\t<tr>\n"
                ."<td colspan='3' align='center'><strong>".flattenText($degrow['group_name'],true)."</strong></td>\n"
                ."\t</tr>\n";
                $gid = $degrow['gid'];

                $aDataentryoutput .= "\t<tr class='data-entry-separator'><td colspan='3'></td></tr>\n";

                // Perform a case insensitive natural sort on group name then question title of a multidimensional array
                $bgc = 'odd';
                foreach ($results as $deqrow)
                {
                    // TODO - can questions be hidden?  Are JavaScript variables names used?  Consistently with everywhere else?

                    // TMSW Conditions->Relevance:  Show relevance equation instead of conditions here - better yet, have data entry use survey-at-a-time but with different view
                    $qinfo = LimeExpressionManager::GetQuestionStatus($deqrow['qid']);
                    $q = $qinfo['info']['q'];
                    $qidattributes = is_a($q, 'QuestionModule') ? $q->getAttributeValues() : array();
                    $cdata['qidattributes'] = $qidattributes;
                    $hidden = (isset($qidattributes['hidden']) ? $qidattributes['hidden'] : 0);
                    $relevance = trim($qinfo['info']['relevance']);
                    $explanation = trim($qinfo['relEqn']);
                    $validation = trim($qinfo['prettyValidTip']);
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
                    $cdata['sQuestionElement'] = is_a($q, 'QuestionModule') ? $q->getDataEntryView($blang) : '';

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


