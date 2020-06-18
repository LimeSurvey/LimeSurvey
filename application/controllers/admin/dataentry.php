<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
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
* @access public
*/
class dataentry extends Survey_Common_Action
{

    public function __construct($controller, $id)
    {
        parent::__construct($controller, $id);

        Yii::app()->loadHelper('surveytranslator');
    }

    /**
     * Function responsible for importing responses from file (.csv)
     *
     * @return void
     */
    public function vvimport()
    {
        $aData = array();

        $aData['display']['menu_bars']['browse'] = gT("Data entry");
        $aData['title_bar']['title'] = gT("Data entry");
        $aData['sidemenu']['state'] = false;
        $aData['menu']['edition'] = true;
        $aData['menu']['import'] = true;
        $aData['menu']['close'] = true;
        //

        $iSurveyId = sanitize_int(Yii::app()->request->getParam('surveyid'));
        $aData['iSurveyId'] = $aData['surveyid'] = $iSurveyId;
        if (Permission::model()->hasSurveyPermission($iSurveyId, 'responses', 'create')) {
            if (tableExists("{{survey_$iSurveyId}}")) {
                // First load the database helper
                Yii::app()->loadHelper('database'); // Really needed ?

                $subAction = Yii::app()->request->getPost('subaction');
                if ($subAction != "upload") {
                    $this->_showUploadForm($this->_getEncodingsArray(), $iSurveyId, $aData);
                } else {
                    $this->_handleFileUpload($iSurveyId, $aData);
                }
            } else {
                Yii::app()->session['flashmessage'] = gT("This survey is not active. You must activate the survey before attempting to import a VVexport file.");
                $this->getController()->redirect($this->getController()->createUrl("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
            }
        } else {
            Yii::app()->session['flashmessage'] = gT("You do not have permission to access this page.");
            $this->getController()->redirect($this->getController()->createUrl("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
    }

    public function iteratesurvey($surveyid)
    {
        $aData = array();

        $surveyid = sanitize_int($surveyid);
        $aData['surveyid'] = $surveyid;
        $aData['success'] = false;
        if (Permission::model()->hasSurveyPermission($surveyid, 'surveyactivation', 'update')) {
            if (Yii::app()->request->getParam('unfinalizeanswers') == 'true') {
                SurveyDynamic::sid($surveyid);
                Yii::app()->db->createCommand("DELETE from {{survey_$surveyid}} WHERE submitdate IS NULL AND token in (SELECT * FROM ( SELECT answ2.token from {{survey_$surveyid}} AS answ2 WHERE answ2.submitdate IS NOT NULL) tmp )")->execute();
                // Then set all remaining answers to incomplete state
                Yii::app()->db->createCommand("UPDATE {{survey_$surveyid}} SET submitdate=NULL, lastpage=NULL")->execute();
                // Finally, reset the token completed and sent status
                Yii::app()->db->createCommand("UPDATE {{tokens_$surveyid}} SET sent='N', remindersent='N', remindercount=0, completed='N', usesleft=1 where usesleft=0")->execute();
                $aData['success'] = true;
            }
            $this->_renderWrappedTemplate('dataentry', 'iteratesurvey', $aData);
        }
    }

    private function _handleFileUpload($iSurveyId, $aData)
    {
        $filePath = $this->_moveUploadedFile($aData);

        Yii::app()->loadHelper('admin/import');
        // Fill option
        $aOptions = array();
        $aOptions['bDeleteFistLine'] = !(bool) Yii::app()->request->getPost('dontdeletefirstline');
        if (Yii::app()->request->getPost('noid')) {
            $aOptions['sExistingId'] = 'ignore';
        } else {
            $aOptions['sExistingId'] = Yii::app()->request->getPost('insertmethod');
        }
        $aOptions['bNotFinalized'] = (bool) Yii::app()->request->getPost('notfinalized');
        $aOptions['bForceImport'] = (bool) Yii::app()->request->getPost('forceimport');
        $aOptions['sCharset'] = Yii::app()->request->getPost('vvcharset');
        $aOptions['sSeparator'] = "\t";
        $aResult = CSVImportResponses($filePath, $iSurveyId, $aOptions);
        unlink($filePath); //delete the uploaded file
        $aData['class'] = "";
        $aData['title'] = gT("Import a VV response data file");
        $aData['aResult']['success'][] = gT("File upload succeeded.");
        if (isset($aResult['success'])) {
            $aData['aResult']['success'] = array_merge($aData['aResult']['success'], $aResult['success']);
        }
        if (isset($aResult['warnings'])) {
            $aData['class'] = "message-box-warning";
        }
        if (isset($aResult['errors'])) {
            $aData['class'] = "message-box-error";
        }

        $aData['aResult']['errors'] = (isset($aResult['errors'])) ? $aResult['errors'] : false;
        $aData['aResult']['warnings'] = (isset($aResult['warnings'])) ? $aResult['warnings'] : false;

        $this->_renderWrappedTemplate('dataentry', 'vvimport_result', $aData);
    }

    /**
     * put your comment there...
     * 
     * @param mixed $aData
     */
    private function _moveUploadedFile($aData)
    {
        $sFullFilePath = Yii::app()->getConfig('tempdir')."/".randomChars(20);
        $fileVV = CUploadedFile::getInstanceByName('csv_vv_file');
        if ($fileVV) {
            if (!$fileVV->SaveAs($sFullFilePath)) {
                $aData['class'] = 'error warningheader';
                $aData['title'] = gT("Error");
                $aData['aResult']['errors'][] = sprintf(
                    gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder."),
                    Yii::app()->getConfig('tempdir')
                );
                //$aData['aResult']['errors'][] = "<pre>".
                $aData['aUrls'][] = array(
                    'link'=>$this->getController()->createUrl('admin/dataentry/sa/vvimport/surveyid/'.$aData['surveyid']),
                    'text'=>$aData['aUrlText'][] = gT("Back to Response Import"),
                    );
                $this->_renderWrappedTemplate('dataentry', 'vvimport_result', $aData);
            } else {
                return $sFullFilePath;
            }
        } else {
            Yii::app()->session['flashmessage'] = gT("You have to select a file.");
            $this->getController()->redirect($this->getController()->createUrl("admin/dataentry/sa/vvimport/surveyid/{$aData['surveyid']}"));
        }
    }

    private function _showUploadForm($aEncodings, $surveyid, $aData)
    {
        unset($aEncodings['auto']);
        asort($aEncodings);

        // Get default character set from global settings
        $thischaracterset = getGlobalSetting('characterset');

        // If no encoding was set yet, use the old "utf8" default
        if ($thischaracterset == "") {
            $thischaracterset = "utf8";
        }

        // Create encodings list using the Yii's CHtml helper
        $charsetsout = CHtml::listOptions($thischaracterset, $aEncodings, $aEncodings);

        $aData['charsetsout'] = $charsetsout;
        $aData['aEncodings'] = $aEncodings;
        $aData['tableExists'] = tableExists("{{survey_$surveyid}}");

        $aData['display']['menu_bars']['browse'] = gT("Import VV file");

        $this->_renderWrappedTemplate('dataentry', 'vvimport', $aData);
    }

    /**
     * dataentry::import()
     * Function responsible to import responses from old survey table(s).
     * @param int $surveyid
     * @return void
     */
    public function import($surveyid)
    {
        $iSurveyId = sanitize_int($surveyid);

        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'responses', 'create')) {
            Yii::app()->setFlashMessage(gT("No permission"), 'error');
            return;
        }

        if (!App()->getRequest()->isPostRequest || App()->getRequest()->getPost('table') == 'none') {

            // Schema that serves as the base for compatibility checks.
            $baseSchema = SurveyDynamic::model($iSurveyId)->getTableSchema();
            $tables = App()->getApi()->getOldResponseTables($iSurveyId);
            $compatible = array();
            $coercible = array();
            foreach ($tables as $table) {
                $schema = PluginDynamic::model($table)->getTableSchema();
                if (PluginDynamic::model($table)->count() > 0) {
                    if ($this->isCompatible($baseSchema, $schema)) {
                        $compatible[] = $table;
                    } elseif ($this->isCompatible($baseSchema, $schema, false)) {
                        $coercible[] = $table;
                    }
                }
            }

            $aData = array();
            $aData['surveyid'] = $iSurveyId;
            $aData['settings']['table'] = array(
                'label' => gT('Source table'),
                'type' => 'select',
                'options' => array(
                    gT('Compatible') => $this->tableList($compatible),
                    gT('Compatible with type coercion') => $this->tableList($coercible)
                )
            );


            $aData['settings']['timings'] = array(
                'type' => 'checkbox',
                'label' => gT('Import timings (if exist)')
            );

            //Get the menubar
            $aData['display']['menu_bars']['browse'] = gT("Quick statistics");
            $survey = Survey::model()->findByPk($iSurveyId);

            $aData['title_bar']['title'] = gT('Browse responses').': '.$survey->currentLanguageSettings->surveyls_title;
            $aData['sidemenu']['state'] = false;
            $aData['menu']['edition'] = true;
            $aData['menu']['import'] = true;
            $aData['menu']['close'] = true;

            $this->_renderWrappedTemplate('dataentry', 'import', $aData);
        } else {
            $aSRIDConversions = array();
            $targetSchema = SurveyDynamic::model($iSurveyId)->getTableSchema();
            $sourceTable = PluginDynamic::model($_POST['table']);
            $sourceSchema = $sourceTable->getTableSchema();

            $fieldMap = array();
            $pattern = '/([\d]+)X([\d]+)X([\d]+.*)/';
            foreach ($sourceSchema->getColumnNames() as $name) {
                // Skip id field.
                if ($name == 'id') {
                    continue;
                }

                $matches = array();
                // Exact match.
                if ($targetSchema->getColumn($name)) {
                    $fieldMap[$name] = $name;
                } elseif (preg_match($pattern, $name, $matches)) {
                    // Column name is SIDXGIDXQID
                    $qid = $matches[3];
                    $targetColumn = $this->getQidColumn($targetSchema, $qid);
                    if (isset($targetColumn)) {
                        $fieldMap[$name] = $targetColumn->name;
                    }
                }
            }
            $imported = 0;
            $sourceResponses = new CDataProviderIterator(new CActiveDataProvider($sourceTable), 500);
            foreach ($sourceResponses as $sourceResponse) {
                $iOldID = $sourceResponse->id;
                // Using plugindynamic model because I dont trust surveydynamic.
                $targetResponse = new PluginDynamic("{{survey_$iSurveyId}}");

                foreach ($fieldMap as $sourceField => $targetField) {
                    $targetResponse[$targetField] = $sourceResponse[$sourceField];
                }

                if (isset($targetSchema->columns['startdate']) && empty($targetResponse['startdate'])){
                    $targetResponse['startdate'] = date("Y-m-d H:i", (int) mktime(0, 0, 0, 1, 1, 1980));
                }

                if (isset($targetSchema->columns['datestamp']) && empty($targetResponse['datestamp'])){
                    $targetResponse['datestamp'] = date("Y-m-d H:i", (int) mktime(0, 0, 0, 1, 1, 1980));
                }

                $beforeDataEntryImport = new PluginEvent('beforeDataEntryImport');
                $beforeDataEntryImport->set('iSurveyID', $iSurveyId);
                $beforeDataEntryImport->set('oModel', $targetResponse);
                App()->getPluginManager()->dispatchEvent($beforeDataEntryImport);
 
                $imported++;
                $targetResponse->save();
                $aSRIDConversions[$iOldID] = $targetResponse->id;
                unset($targetResponse);
            }



            Yii::app()->session['flashmessage'] = sprintf(gT("%s old response(s) were successfully imported."), $imported);
            $sOldTimingsTable = (string) substr(substr($sourceTable->tableName(), 0, (string) strrpos($sourceTable->tableName(), '_')).'_timings'.(string) substr($sourceTable->tableName(), (string) strrpos($sourceTable->tableName(), '_')), strlen(Yii::app()->db->tablePrefix));
            $sNewTimingsTable = "survey_{$surveyid}_timings";

            if (isset($_POST['timings']) && $_POST['timings'] == 1 && tableExists($sOldTimingsTable) && tableExists($sNewTimingsTable)) {
                // Import timings
                $aFieldsOldTimingTable = array_values(Yii::app()->db->schema->getTable('{{'.$sOldTimingsTable.'}}')->columnNames);
                $aFieldsNewTimingTable = array_values(Yii::app()->db->schema->getTable('{{'.$sNewTimingsTable.'}}')->columnNames);

                $aValidTimingFields = array_intersect($aFieldsOldTimingTable, $aFieldsNewTimingTable);

                $sQueryOldValues = "SELECT ".implode(", ", $aValidTimingFields)." FROM {{{$sOldTimingsTable}}} ";
                $aQueryOldValues = Yii::app()->db->createCommand($sQueryOldValues)->query()->readAll(); //Checked
                $iRecordCountT = 0;
                foreach ($aQueryOldValues as $sRecord) {
                    if (isset($aSRIDConversions[$sRecord['id']])) {
                        $sRecord['id'] = $aSRIDConversions[$sRecord['id']];
                    } else {
                        continue;
                    }
                    Yii::app()->db->createCommand()->insert("{{{$sNewTimingsTable}}}", $sRecord);
                    $iRecordCountT++;
                }
                Yii::app()->session['flashmessage'] = sprintf(gT("%s old response(s) and according timings were successfully imported."), $imported, $iRecordCountT);
            }
            $this->getController()->redirect(array("/admin/responses/sa/index/", 'surveyid' => $surveyid));
        }
    }


    /**
     * Takes a list of tablenames and creates a nice key value array.
     */
    protected function tableList($tables)
    {
        $list = array();
        if (empty($tables)) {
            $list['none'] = gT('No old responses found.');
        }

        foreach ($tables as $table) {
            $count = PluginDynamic::model($table)->count();
            $timestamp = date_format(new DateTime((string) substr($table, -14)), 'Y-m-d H:i:s');
            $list[$table] = "$timestamp ($count responses)";
        }
        return $list;
    }
    /**
     * Takes a table schema and finds the field for some question id.
     * @param CDbTableSchema $schema
     * @param string $qid
     * @return CDbColumnSchema
     */
    protected function getQidColumn(CDbTableSchema $schema, $qid)
    {
        foreach ($schema->columns as $name => $column) {
            $pattern = '/([\d]+)X([\d]+)X([\d]+.*)/';
            $matches = array();
            if (preg_match($pattern, $name, $matches)) {
                if ($matches[3] == $qid) {
                    return $column;
                }

            }
        }
    }
    /**
     * Compares 2 table schema to see if they are compatible.
     */
    protected function isCompatible(CDbTableSchema $base, CDbTableSchema $old, $checkColumnTypes = true)
    {
        $pattern = '/([\d]+)X([\d]+)X([\d]+.*)/';
        foreach ($old->columns as $name => $column) {
            // The following columns are always compatible.
            if (in_array($name, array('id', 'token', 'submitdate', 'lastpage', 'startlanguage'))) {
                continue;
            }
            $matches = array();
            if (preg_match($pattern, $name, $matches)) {
                $qid = $matches[3];
                $baseColumn = $this->getQidColumn($base, $qid);
                if ($baseColumn) {
                    if ($baseColumn && $checkColumnTypes && ($baseColumn->dbType != $column->dbType)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
    /**
     * dataentry::editdata()
     * Edit dataentry.
     * @param mixed $subaction
     * @param mixed $id
     * @param mixed $surveyid
     * @return
     */
    public function editdata($subaction, $id, $surveyid)
    {

        $surveyid = sanitize_int($surveyid);
        $id = sanitize_int($id);
        $aViewUrls = array();
        $survey = Survey::model()->findByPk($surveyid);
        $sDataEntryLanguage = $survey->language;

        if (Permission::model()->hasSurveyPermission($surveyid, 'responses', 'update')) {
            $surveytable = $survey->responsesTableName;
            $aData = [];
            $aData['display']['menu_bars']['browse'] = gT("Data entry");

            Yii::app()->loadHelper('database');

            //FIRST LETS GET THE NAMES OF THE QUESTIONS AND MATCH THEM TO THE FIELD NAMES FOR THE DATABASE
            $quotedGroups = Yii::app()->db->quoteTableName('{{groups}}');
            $fnquery = "SELECT * FROM {{questions}}, $quotedGroups g, {{surveys}} WHERE
            {{questions}}.gid=g.gid AND
            {{questions}}.language = '{$sDataEntryLanguage}' AND g.language = '{$sDataEntryLanguage}' AND
            {{questions}}.sid={{surveys}}.sid AND {{questions}}.sid='$surveyid'
            order by group_order, question_order";
            $fnresult = dbExecuteAssoc($fnquery);
            $fnresult = $fnresult->readAll();

            $fnrows = array(); //Create an empty array in case FetchRow does not return any rows
            foreach ($fnresult as $fnrow) {
                $fnrows[] = $fnrow;
            } // Get table output into array

            // Perform a case insensitive natural sort on group name then question title of a multidimensional array
            // $fnames = (Field Name in Survey Table, Short Title of Question, Question Type, Field Name, Question Code, Predetermined Answer if exist)
            $fnames = [];
            $fnames['completed'] = array('fieldname'=>"completed", 'question'=>gT("Completed"), 'type'=>'completed');

            $fnames = array_merge($fnames, createFieldMap($survey, 'full', false, false, $sDataEntryLanguage));
            // Fix private if disallowed to view token
            if (!Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'read')) {
                unset($fnames['token']);
            }
            $nfncount = count($fnames) - 1;

            //SHOW INDIVIDUAL RECORD
            $results = array();
            if ($subaction == "edit" && Permission::model()->hasSurveyPermission($surveyid, 'responses', 'update')) {
                $idquery = "SELECT * FROM $surveytable WHERE id=$id";
                $idresult = dbExecuteAssoc($idquery) or safeDie("Couldn't get individual record<br />$idquery<br />");
                foreach ($idresult->readAll() as $idrow) {
                    $results[] = $idrow;
                }
            } elseif ($subaction == "editsaved" && Permission::model()->hasSurveyPermission($surveyid, 'responses', 'update')) {
                if (isset($_GET['public']) && $_GET['public'] == "true") {
                    $password = hash('sha256',Yii::app()->request->getParam('accesscode'));
                } else {
                    $password = Yii::app()->request->getParam('accesscode');
                }

                $svresult = SavedControl::model()->findAllByAttributes(
                array(
                'sid' => $surveyid,
                'identifier' => Yii::app()->request->getParam('identifier'),
                'access_code' => $password)
                );

                $saver = array();
                foreach ($svresult as $svrow) {
                    $saver['email'] = $svrow['email'];
                    $saver['scid'] = $svrow['scid'];
                    $saver['ip'] = $svrow['ip'];
                }

                $svresult = SavedControl::model()->findAllByAttributes(array('scid'=>$saver['scid']));
                $responses = [];
                foreach ($svresult as $svrow) {
                    $responses[$svrow['fieldname']] = $svrow['value'];
                } // while

                $fieldmap = createFieldMap($survey, 'full', false, false, $survey->language);
                $results1 = array();
                foreach ($fieldmap as $fm) {
                    if (isset($responses[$fm['fieldname']])) {
                        $results1[$fm['fieldname']] = $responses[$fm['fieldname']];
                    } else {
                        $results1[$fm['fieldname']] = "";
                    }
                }

                $results1['id'] = "";
                $results1['datestamp'] = dateShift((string) date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig('timeadjust'));
                $results1['ipaddr'] = $saver['ip'];
                $results[] = $results1;
            }

            $aData = array(
            'id' => $id,
            'surveyid' => $surveyid,
            'subaction' => $subaction,
            'part' => 'header'
            );

            $aViewUrls[] = 'dataentry_header_view';
            $aViewUrls[] = 'edit';

            $highlight = false;
            unset($fnames['lastpage']);

            // unset timings
            foreach ($fnames as $fname) {
                if ($fname['type'] == "interview_time" || $fname['type'] == "page_time" || $fname['type'] == "answer_time") {
                    unset($fnames[$fname['fieldname']]);
                    $nfncount--;
                }
            }
            $aDataentryoutput = '';
            foreach ($results as $idrow) {
                $fname = reset($fnames);
                do {
                    $question = $fname['question'];
                    $aDataentryoutput .= "\t<tr";
                    if ($highlight) {
                        $aDataentryoutput .= " class='odd'";
                    } else {
                        $aDataentryoutput .= " class='even'";
                    }

                    $highlight = !$highlight;
                    $aDataentryoutput .= ">\n"
                    ."<td>"
                    ."\n";
                    $aDataentryoutput .= stripJavaScript($question);
                    $aDataentryoutput .= "</td>\n"
                    ."<td>\n";
                    //$aDataentryoutput .= "\t-={$fname[3]}=-"; //Debugging info
                    $qidattributes = [];
                    if (isset($fname['qid']) && isset($fname['type'])) {
                        $qidattributes = QuestionAttribute::model()->getQuestionAttributes($fname['qid']);
                    }
                    switch ($fname['type']) {
                        case "completed":
                            $selected = (empty($idrow['submitdate'])) ? 'N' : 'Y';
                            $select_options = array(
                                'N' => gT('No', 'unescaped'),
                                'Y' => gT('Yes', 'unescaped')
                            );

                            $aDataentryoutput .= CHtml::dropDownList('completed', $selected, $select_options, array('class'=>'form-control'));

                            break;
                        case "X": //Boilerplate question
                            $aDataentryoutput .= "";
                            break;
                        case "Q":
                            $aDataentryoutput .= $fname['subquestion'].'&nbsp;';
                            $aDataentryoutput .= CHtml::textField($fname['fieldname'], $idrow[$fname['fieldname']]);
                            break;
                        case "K":
                            $aDataentryoutput .= $fname['subquestion'].'&nbsp;';
                            /* Fix DB DECIMAL type */
                            $value = $idrow[$fname['fieldname']];
                            if($value && $value[0] === ".") {
                                $value = "0".$value;
                            }
                            if (strpos($value, ".")) {
                                $value = rtrim(rtrim($value, "0"), ".");
                            }
                            $aDataentryoutput .= CHtml::textField($fname['fieldname'],$value,array('pattern'=>"[-]?([0-9]{0,20}([\.][0-9]{0,10})?)?",'title'=>gT("Only numbers may be entered in this field.")));
                            break;
                        case "id":
                            $aDataentryoutput .= CHtml::tag('span', array('style' => 'font-weight: bold;'), '&nbsp;'.$idrow[$fname['fieldname']]);
                            break;
                        case "seed":
                            $aDataentryoutput .= CHtml::tag('span', array(), '&nbsp;'.$idrow[$fname['fieldname']]);
                            break;
                        case "5": //5 POINT CHOICE radio-buttons
                            for ($i = 1; $i <= 5; $i++) {
                                $checked = false;
                                if ($idrow[$fname['fieldname']] == $i) { $checked = true; }
                                $aDataentryoutput .= '<span class="five-point">';
                                $aDataentryoutput .= CHtml::radioButton($fname['fieldname'], $checked, array('class'=>'', 'value'=>$i, 'id'=>'5-point-choice-'.$i));
                                $aDataentryoutput .= '<label for="5-point-choice-'.$i.'">'.$i.'</label>';
                                $aDataentryoutput .= '</span>';
                            }
                            break;
                        case "D": //DATE
                            $dateformatdetails = getDateFormatDataForQID($qidattributes, $surveyid);
                            $datetimeobj = null;
                            $thisdate = '';
                            if ($idrow[$fname['fieldname']] != '') {
                                $datetimeobj = DateTime::createFromFormat("Y-m-d H:i:s", $idrow[$fname['fieldname']]);
                                if($datetimeobj == null) { //MSSQL uses microseconds by default in any datetime object
                                    $datetimeobj = DateTime::createFromFormat("Y-m-d H:i:s.u", $idrow[$fname['fieldname']]);
                                }
                            }

                            if (canShowDatePicker($dateformatdetails)) {
                                if($datetimeobj) {
                                    $thisdate = $datetimeobj->format($dateformatdetails['phpdate']);
                                }
                                $goodchars = str_replace(array("m", "d", "y", "H", "M"), "", $dateformatdetails['dateformat']);
                                $goodchars = "0123456789".$goodchars[0];
                                $aDataentryoutput .= CHtml::textField($fname['fieldname'], $thisdate,
                                array(
                                'class' => 'popupdate',
                                'size' => '12',
                                'onkeypress' => 'return window.LS.goodchars(event,\''.$goodchars.'\')'
                                )
                                );
                                /*
                                Yii::app()->getController()->widget('yiiwheels.widgets.datetimepicker.WhDateTimePicker', array(
                                    'name' => $fname['fieldname'],
                                    'value' => $thisdate,
                                    'pluginOptions' => array(
                                        'format' => $dateformatdetails['jsdate'] . " HH:mm",
                                        'allowInputToggle' =>true,
                                        'showClear' => true,
                                        'tooltips' => array(
                                            'clear'=> gT('Clear selection'),
                                            'prevMonth'=> gT('Previous month'),
                                            'nextMonth'=> gT('Next month'),
                                            'selectYear'=> gT('Select year'),
                                            'prevYear'=> gT('Previous year'),
                                            'nextYear'=> gT('Next year'),
                                            'selectDecade'=> gT('Select decade'),
                                            'prevDecade'=> gT('Previous decade'),
                                            'nextDecade'=> gT('Next decade'),
                                            'prevCentury'=> gT('Previous century'),
                                            'nextCentury'=> gT('Next century'),
                                'selectTime'=> gT('Select time')
                                        ),
                                        'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                                    )
                                ));
                                */
                                $aDataentryoutput .= CHtml::hiddenField('dateformat'.$fname['fieldname'], $dateformatdetails['jsdate'],
                                array('id' => "dateformat{$fname['fieldname']}")
                                );
                                // $aDataentryoutput .= "\t<input type='text' class='popupdate' size='12' name='{$fname['fieldname']}' value='{$thisdate}' onkeypress=\"return window.LS.goodchars(event,'".$goodchars."')\"/>\n";
                                // $aDataentryoutput .= "\t<input type='hidden' name='dateformat{$fname['fieldname']}' id='dateformat{$fname['fieldname']}' value='{$dateformatdetails['jsdate']}'  />\n";
                            } else {
                                if($datetimeobj) {
                                    $thisdate = $datetimeobj->format("Y-m-d\TH:i");
                                }
                                $aDataentryoutput .= CHtml::dateTimeLocalField($fname['fieldname'], $thisdate);
                            }
                            break;
                        case "G": //GENDER drop-down list
                            $select_options = array(
                            '' => gT("Please choose").'...',
                            'F' => gT("Female"),
                            'M' => gT("Male")
                            );
                            $aDataentryoutput .= CHtml::listBox($fname['fieldname'], $idrow[$fname['fieldname']], $select_options);
                            break;
                        case "L": //LIST drop-down
                        case "!": //List (Radio)
                            $qidattributes = QuestionAttribute::model()->getQuestionAttributes($fname['qid']);
                            if (isset($qidattributes['category_separator']) && trim($qidattributes['category_separator']) != '') {
                                $optCategorySeparator = $qidattributes['category_separator'];
                            } else {
                                unset($optCategorySeparator);
                            }

                            if (substr($fname['fieldname'], -5) == "other") {
                                $aDataentryoutput .= "\t<input type='text' name='{$fname['fieldname']}' value='"
                                .htmlspecialchars($idrow[$fname['fieldname']], ENT_QUOTES)."' />\n";
                            } else {
                                $lquery = "SELECT * FROM {{answers}} WHERE qid={$fname['qid']} AND language = '{$sDataEntryLanguage}' ORDER BY sortorder, answer";
                                $lresult = dbExecuteAssoc($lquery);
                                $aDataentryoutput .= "\t<select name='{$fname['fieldname']}' class='form-control'>\n"
                                ."<option value=''";
                                if ($idrow[$fname['fieldname']] == "") {$aDataentryoutput .= " selected='selected'"; }
                                $aDataentryoutput .= ">".gT("Please choose")."..</option>\n";

                                if (!isset($optCategorySeparator)) {
                                    foreach ($lresult->readAll() as $llrow) {
                                        $llrow['answer'] = flattenText($llrow['answer']);
                                        $aDataentryoutput .= "<option value='{$llrow['code']}'";
                                        if ($idrow[$fname['fieldname']] == $llrow['code']) {$aDataentryoutput .= " selected='selected'"; }
                                        $aDataentryoutput .= ">{$llrow['answer']}</option>\n";
                                    }
                                } else {
                                    $defaultopts = array();
                                    $optgroups = array();
                                    foreach ($lresult->readAll() as $llrow) {
                                        $llrow['answer'] = flattenText($llrow['answer']);
                                        list ($categorytext, $answertext) = explode($optCategorySeparator, $llrow['answer']);
                                        if ($categorytext == '') {
                                            $defaultopts[] = array('code' => $llrow['code'], 'answer' => $answertext);
                                        } else {
                                            $optgroups[$categorytext][] = array('code' => $llrow['code'], 'answer' => $answertext);
                                        }
                                    }

                                    foreach ($optgroups as $categoryname => $optionlistarray) {
                                        $aDataentryoutput .= "<optgroup class=\"dropdowncategory\" label=\"".$categoryname."\">\n";
                                        foreach ($optionlistarray as $optionarray) {
                                            $aDataentryoutput .= "\t<option value='{$optionarray['code']}'";
                                            if ($idrow[$fname['fieldname']] == $optionarray['code']) {$aDataentryoutput .= " selected='selected'"; }
                                            $aDataentryoutput .= ">{$optionarray['answer']}</option>\n";
                                        }
                                        $aDataentryoutput .= "</optgroup>\n";
                                    }
                                    foreach ($defaultopts as $optionarray) {
                                        $aDataentryoutput .= "<option value='{$optionarray['code']}'";
                                        if ($idrow[$fname['fieldname']] == $optionarray['code']) {$aDataentryoutput .= " selected='selected'"; }
                                        $aDataentryoutput .= ">{$optionarray['answer']}</option>\n";
                                    }

                                }

                                $oquery = "SELECT other FROM {{questions}} WHERE qid={$fname['qid']} AND {{questions}}.language = '{$sDataEntryLanguage}'";
                                $oresult = dbExecuteAssoc($oquery) or safeDie("Couldn't get other for list question<br />".$oquery."<br />");
                                $fother = '';
                                foreach ($oresult->readAll() as $orow) {
                                    $fother = $orow['other'];
                                }
                                if ($fother == "Y") {
                                    $aDataentryoutput .= "<option value='-oth-'";
                                    if ($idrow[$fname['fieldname']] == "-oth-") {$aDataentryoutput .= " selected='selected'"; }
                                    $aDataentryoutput .= ">".gT("Other")."</option>\n";
                                }
                                $aDataentryoutput .= "\t</select>\n";
                            }
                            break;
                        case "O": //LIST WITH COMMENT drop-down/radio-button list + textarea
                            $lquery = "SELECT * FROM {{answers}} WHERE qid={$fname['qid']} AND language = '{$sDataEntryLanguage}' ORDER BY sortorder, answer";
                            $lresult = dbExecuteAssoc($lquery);
                            $aDataentryoutput .= "\t<select name='{$fname['fieldname']}' class='form-control'>\n"
                            ."<option value=''";
                            if ($idrow[$fname['fieldname']] == "") {$aDataentryoutput .= " selected='selected'"; }
                            $aDataentryoutput .= ">".gT("Please choose")."..</option>\n";

                            foreach ($lresult->readAll() as $llrow) {
                                $llrow['answer'] = flattenText($llrow['answer']);
                                $aDataentryoutput .= "<option value='{$llrow['code']}'";
                                if ($idrow[$fname['fieldname']] == $llrow['code']) {$aDataentryoutput .= " selected='selected'"; }
                                $aDataentryoutput .= ">{$llrow['answer']}</option>\n";
                            }
                            $fname = next($fnames);
                            $aDataentryoutput .= "\t</select>\n"
                            ."\t<br />\n"
                            .CHtml::textArea($fname['fieldname'],$idrow[$fname['fieldname']],array('cols'=>45,'rows'=>5));
                            break;
                        case "R": //RANKING TYPE QUESTION
                            $thisqid = $fname['qid'];
                            $currentvalues = array();
                            $myfname = $fname['sid'].'X'.$fname['gid'].'X'.$fname['qid'];
                            $aDataentryoutput .= '<div id="question'.$thisqid.'" class="ranking-answers"><ul class="answers-list select-list">';
                            while (isset($fname['type']) && $fname['type'] == "R" && $fname['qid'] == $thisqid) {
                                //Let's get all the existing values into an array
                                if ($idrow[$fname['fieldname']]) {
                                    $currentvalues[] = $idrow[$fname['fieldname']];
                                }
                                $fname = next($fnames);
                            }
                            $ansquery = "SELECT * FROM {{answers}} WHERE language = '{$sDataEntryLanguage}' AND qid=$thisqid ORDER BY sortorder, answer";
                            $ansresult = Yii::app()->db->createCommand($ansquery)->query()->readAll(); //Checked
                            $anscount = count($ansresult);
                            $answers = array();
                                foreach ($ansresult as $ansrow) {
                                    $answers[] = $ansrow;
                                }
                            for ($i = 1; $i <= $anscount; $i++) {
                                $aDataentryoutput .= "\n<li class=\"select-item\">";
                                $aDataentryoutput .= "<label for=\"answer{$myfname}{$i}\">";
                                if ($i == 1) {
                                    $aDataentryoutput .= gT('First choice');
                                } else {
                                    $aDataentryoutput .= gT('Next choice');
                                }

                                $aDataentryoutput .= "</label>";
                                $aDataentryoutput .= "<select name=\"{$myfname}{$i}\" id=\"answer{$myfname}{$i}\" class='form-control'>\n";
                                (!isset($currentvalues[$i - 1])) ? $selected = " selected=\"selected\"" : $selected = "";
                                $aDataentryoutput .= "\t<option value=\"\" $selected>".gT('None')."</option>\n";
                                foreach ($answers as $ansrow) {
                                    (isset($currentvalues[$i - 1]) && $currentvalues[$i - 1] == $ansrow['code']) ? $selected = " selected=\"selected\"" : $selected = "";
                                    $aDataentryoutput .= "\t<option value=\"".$ansrow['code']."\" $selected>".flattenText($ansrow['answer'])."</option>\n";
                                }
                                $aDataentryoutput .= "</select\n";
                                $aDataentryoutput .= "</li>";
                            }
                            $aDataentryoutput .= '</ul>';
                            $aDataentryoutput .= "<div style='display:none' id='ranking-{$thisqid}-maxans'>{$anscount}</div>"
                                . "<div style='display:none' id='ranking-{$thisqid}-minans'>0</div>"
                                . "<div style='display:none' id='ranking-{$thisqid}-name'>javatbd{$myfname}</div>";
                            $aDataentryoutput .= "<div style=\"display:none\">";
                            foreach ($answers as $ansrow) {
                                $aDataentryoutput .= "<div id=\"htmlblock-{$thisqid}-{$ansrow['code']}\">{$ansrow['answer']}</div>";
                            }
                            $aDataentryoutput .= "</div>";
                            $aDataentryoutput .= '</div>';
                            App()->getClientScript()->registerPackage('jquery-actual');

                            App()->getClientScript()->registerScriptFile(App()->getConfig('generalscripts').'ranking.js');
                            App()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl').'ranking.css');
                            App()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl').'jquery-ui-custom.css');

                            $aDataentryoutput .= "<script type='text/javascript'>\n"
                                .  "  <!--\n"
                                . "var aRankingTranslations = {
                                         choicetitle: '".gT("Your Choices", 'js')."',
                                         ranktitle: '".gT("Your Ranking", 'js')."'
                                        };\n"
                                . "function checkconditions(){};"
                                . "$(function() {"
                                ." doDragDropRank({$thisqid},0,true,true);\n"
                                . "});\n"
                                ." -->\n"
                                ."</script>\n";

                            unset($answers);
                            $fname = prev($fnames);
                            break;

                        case "M": //Multiple choice checkbox
                            $thisqid = $fname['qid'];
                            while ($fname['qid'] == $thisqid) {
                                //$aDataentryoutput .= substr($fname['fieldname'], strlen($fname['fieldname'])-5, 5)."<br />\n";
                                if (substr($fname['fieldname'], -5) == "other") {
                                    $aDataentryoutput .= "\t<input type='text' name='{$fname['fieldname']}' value='"
                                    .htmlspecialchars($idrow[$fname['fieldname']], ENT_QUOTES)."' />\n";
                                } else {
                                    $aDataentryoutput .= "<div class='checkbox'>\t<input type='checkbox' class='checkboxbtn' name='{$fname['fieldname']}' id='{$fname['fieldname']}' value='Y'";
                                    if ($idrow[$fname['fieldname']] == "Y") {$aDataentryoutput .= " checked"; }
                                    $aDataentryoutput .= " /><label for='{$fname['fieldname']}'>{$fname['subquestion']}</label></div>\n";
                                }

                                $fname = next($fnames);
                            }
                            $fname = prev($fnames);

                            break;

                        case "I": //Language Switch
                            $lquery = "SELECT * FROM {{answers}} WHERE qid={$fname['qid']} AND language = '{$sDataEntryLanguage}' ORDER BY sortorder, answer";
                            dbExecuteAssoc($lquery);


                            $slangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
                            $baselang = Survey::model()->findByPk($surveyid)->language;
                            array_unshift($slangs, $baselang);

                            $aDataentryoutput .= "<select name='{$fname['fieldname']}' class='form-control'>\n";
                            $aDataentryoutput .= "<option value=''";
                            if ($idrow[$fname['fieldname']] == "") {$aDataentryoutput .= " selected='selected'"; }
                            $aDataentryoutput .= ">".gT("Please choose")."..</option>\n";

                            foreach ($slangs as $lang) {
                                $aDataentryoutput .= "<option value='{$lang}'";
                                if ($lang == $idrow[$fname['fieldname']]) {$aDataentryoutput .= " selected='selected'"; }
                                $aDataentryoutput .= ">".getLanguageNameFromCode($lang, false)."</option>\n";
                            }
                            $aDataentryoutput .= "</select>";
                            break;

                        case "P": //Multiple choice with comments checkbox + text
                            $aDataentryoutput .= "<table class='table'>\n";
                            while (isset($fname) && $fname['type'] == "P") {
                                $thefieldname = $fname['fieldname'];
                                if (substr($thefieldname, -7) == "comment") {
                                    $aDataentryoutput .= "<td>";
                                    $aDataentryoutput .= CHtml::textField($fname['fieldname'], $idrow[$fname['fieldname']],array('size'=>50));
                                    $aDataentryoutput .= "</td>\n"
                                    ."\t</tr>\n";
                                } elseif (substr($fname['fieldname'], -5) == "other") {
                                    $aDataentryoutput .= "\t<tr>\n"
                                    ."<td>\n"
                                    . CHtml::textField($fname['fieldname'], $idrow[$fname['fieldname']],array('size'=>30))
                                    ."</td>\n"
                                    ."<td>\n";
                                    $fname = next($fnames);
                                    $aDataentryoutput .= CHtml::textField($fname['fieldname'], $idrow[$fname['fieldname']],array('size'=>50))
                                    ."</td>\n"
                                    ."\t</tr>\n";
                                } else {
                                    $aDataentryoutput .= "\t<tr>\n"
                                    ."<td><div class='checkbox'><input type='checkbox' class='checkboxbtn' name=\"{$fname['fieldname']}\" id=\"{$fname['fieldname']}\" value='Y'";
                                    if ($idrow[$fname['fieldname']] == "Y") {$aDataentryoutput .= " checked"; }
                                    $aDataentryoutput .= " /><label for=\"{$fname['fieldname']}\">{$fname['subquestion']}</label></div></td>\n";
                                }
                                $fname = next($fnames);
                            }
                            $aDataentryoutput .= "</table>\n";
                            $fname = prev($fnames);
                            break;
                        case "|": //FILE UPLOAD
                            $aDataentryoutput .= "<table class='table'>\n";
                            if ($fname['aid'] !== 'filecount' && isset($idrow[$fname['fieldname'].'_filecount']) && ($idrow[$fname['fieldname'].'_filecount'] > 0)) {
                                //file metadata
                                $metadata = json_decode($idrow[$fname['fieldname']], true);
                                $qAttributes = QuestionAttribute::model()->getQuestionAttributes($fname['qid']);
                                for ($i = 0; ($i < $qAttributes['max_num_of_files']) && isset($metadata[$i]); $i++) {
                                    if ($qAttributes['show_title']) {
                                        $aDataentryoutput .= '<tr><td>'.gT("Title").'</td><td><input type="text" class="'.$fname['fieldname'].'" id="'.$fname['fieldname'].'_title_'.$i.'" name="title"    size=50 value="'.htmlspecialchars($metadata[$i]["title"]).'" /></td></tr>';
                                    }
                                    if ($qAttributes['show_comment']) {
                                        $aDataentryoutput .= '<tr><td >'.gT("Comment").'</td><td><input type="text" class="'.$fname['fieldname'].'" id="'.$fname['fieldname'].'_comment_'.$i.'" name="comment"  size=50 value="'.htmlspecialchars($metadata[$i]["comment"]).'" /></td></tr>';
                                    }

                                    $aDataentryoutput .= '<tr><td>'.gT("File name").'</td><td><input   class="'.$fname['fieldname'].'" id="'.$fname['fieldname'].'_name_'.$i.'" name="name" size=50 value="'.htmlspecialchars(rawurldecode($metadata[$i]["name"])).'" /></td></tr>'
                                    .'<tr><td></td><td><input type="hidden" class="'.$fname['fieldname'].'" id="'.$fname['fieldname'].'_size_'.$i.'" name="size" size=50 value="'.htmlspecialchars($metadata[$i]["size"]).'" /></td></tr>'
                                    .'<tr><td></td><td><input type="hidden" class="'.$fname['fieldname'].'" id="'.$fname['fieldname'].'_ext_'.$i.'" name="ext" size=50 value="'.htmlspecialchars($metadata[$i]["ext"]).'" /></td></tr>'
                                    .'<tr><td></td><td><input type="hidden"  class="'.$fname['fieldname'].'" id="'.$fname['fieldname'].'_filename_'.$i.'" name="filename" size=50 value="'.htmlspecialchars(rawurldecode($metadata[$i]["filename"])).'" /></td></tr>';
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
                            } else {
//file count
                                $aDataentryoutput .= '<input readonly id="'.$fname['fieldname'].'" name="'.$fname['fieldname'].'" value ="'.htmlspecialchars($idrow[$fname['fieldname']]).'" /></td></table>';
                            }
                            break;
                        case "N": //NUMERICAL TEXT
                            /* Fix DB DECIMAL type */
                            $value = $idrow[$fname['fieldname']];
                            if($value && $value[0] === ".") {
                                $value = "0".$value;
                            }
                            if (strpos($value, ".")) {
                                $value = rtrim(rtrim($value, "0"), ".");
                            }
                            /* no number fix with return window.LS.goodchars … */
                            $aDataentryoutput .= CHtml::textField($fname['fieldname'],$value,array('pattern'=>"[-]?([0-9]{0,20}([\.][0-9]{0,10})?)?",'title'=>gT("Only numbers may be entered in this field.")));
                            break;
                        case "S": //SHORT FREE TEXT
                            $aDataentryoutput .= CHtml::textField($fname['fieldname'],$idrow[$fname['fieldname']]);
                            break;
                        case "T": //LONG FREE TEXT
                            $aDataentryoutput .= CHtml::textArea($fname['fieldname'],$idrow[$fname['fieldname']],array('cols'=>45,'rows'=>5));
                            break;
                        case "U": //HUGE FREE TEXT
                            $aDataentryoutput .= CHtml::textArea($fname['fieldname'],$idrow[$fname['fieldname']],array('cols'=>70,'rows'=>50));
                             break;
                        case "Y": //YES/NO radio-buttons
                            $aDataentryoutput .= "\t<select name='{$fname['fieldname']}' class='form-control'>\n"
                            ."<option value=''";
                            if ($idrow[$fname['fieldname']] == "") {$aDataentryoutput .= " selected='selected'"; }
                            $aDataentryoutput .= ">".gT("Please choose")."..</option>\n"
                            ."<option value='Y'";
                            if ($idrow[$fname['fieldname']] == "Y") {$aDataentryoutput .= " selected='selected'"; }
                            $aDataentryoutput .= ">".gT("Yes")."</option>\n"
                            ."<option value='N'";
                            if ($idrow[$fname['fieldname']] == "N") {$aDataentryoutput .= " selected='selected'"; }
                            $aDataentryoutput .= ">".gT("No")."</option>\n"
                            ."\t</select>\n";
                            break;
                        case "A": //ARRAY (5 POINT CHOICE) radio-buttons
                            $aDataentryoutput .= "<table class='table'>\n";
                            $thisqid = $fname['qid'];
                            while ($fname['qid'] == $thisqid) {
                                $aDataentryoutput .= "\t<tr>\n"
                                ."<td align='right'>{$fname['subquestion']}</td>\n"
                                ."<td>\n";
                                for ($j = 1; $j <= 5; $j++) {
                                    $aDataentryoutput .= '<span class="five-point">';
                                    $aDataentryoutput .= "\t<input type='radio' class='' name='{$fname['fieldname']}' id='5-point-radio-{$fname['fieldname']}' value='$j'";
                                    if ($idrow[$fname['fieldname']] == $j) {$aDataentryoutput .= " checked"; }
                                    $aDataentryoutput .= " /><label for='5-point-radio-{$fname['fieldname']}'>$j</label>&nbsp;\n";
                                    $aDataentryoutput .= '</span>';
                                }
                                $aDataentryoutput .= "</td>\n"
                                ."\t</tr>\n";
                                $fname = next($fnames);
                            }
                            $aDataentryoutput .= "</table>\n";
                            $fname = prev($fnames);
                            break;
                        case "B": //ARRAY (10 POINT CHOICE) radio-buttons
                            $aDataentryoutput .= "<table class='table'>\n";
                            $thisqid = $fname['qid'];
                            while ($fname['qid'] == $thisqid) {
                                $aDataentryoutput .= "\t<tr>\n"
                                ."<td align='right'>{$fname['subquestion']}</td>\n"
                                ."<td>\n";
                                for ($j = 1; $j <= 10; $j++) {
                                    $aDataentryoutput .= '<span class="ten-point">';
                                    $aDataentryoutput .= "\t<input type='radio' class='' name='{$fname['fieldname']}' id='ten-point-{$fname['fieldname']}-$j' value='$j'";
                                    if ($idrow[$fname['fieldname']] == $j) {$aDataentryoutput .= " checked"; }
                                    $aDataentryoutput .= " /><label for='ten-point-{$fname['fieldname']}-$j'>$j</label>&nbsp;\n";
                                    $aDataentryoutput .= '</span>';
                                }
                                $aDataentryoutput .= "</td>\n"
                                ."\t</tr>\n";
                                $fname = next($fnames);
                            }
                            $fname = prev($fnames);
                            $aDataentryoutput .= "</table>\n";
                            break;
                        case "C": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                            $aDataentryoutput .= "<table class='table'>\n";
                            $thisqid = $fname['qid'];
                            while ($fname['qid'] == $thisqid) {
                                $aDataentryoutput .= "\t<tr>\n"
                                ."<td align='right'>{$fname['subquestion']}</td>\n"
                                ."<td>\n"
                                ."\t<input type='radio' class='' name='{$fname['fieldname']}' value='Y'";
                                if ($idrow[$fname['fieldname']] == "Y") {$aDataentryoutput .= " checked"; }
                                $aDataentryoutput .= " />".gT("Yes")."&nbsp;\n"
                                ."\t<input type='radio' class='' name='{$fname['fieldname']}' value='U'";
                                if ($idrow[$fname['fieldname']] == "U") {$aDataentryoutput .= " checked"; }
                                $aDataentryoutput .= " />".gT("Uncertain")."&nbsp;\n"
                                ."\t<input type='radio' class='' name='{$fname['fieldname']}' value='N'";
                                if ($idrow[$fname['fieldname']] == "N") {$aDataentryoutput .= " checked"; }
                                $aDataentryoutput .= " />".gT("No")."&nbsp;\n"
                                ."</td>\n"
                                ."\t</tr>\n";
                                $fname = next($fnames);
                            }
                            $fname = prev($fnames);
                            $aDataentryoutput .= "</table>\n";
                            break;
                        case "E": //ARRAY (Increase/Same/Decrease) radio-buttons
                            $aDataentryoutput .= "<table class='table'>\n";
                            $thisqid = $fname['qid'];
                            while ($fname['qid'] == $thisqid) {
                                $aDataentryoutput .= "\t<tr>\n"
                                ."<td align='right'>{$fname['subquestion']}</td>\n"
                                ."<td>\n"
                                ."\t<input type='radio' class='' name='{$fname['fieldname']}' value='I'";
                                if ($idrow[$fname['fieldname']] == "I") {$aDataentryoutput .= " checked"; }
                                $aDataentryoutput .= " />Increase&nbsp;\n"
                                ."\t<input type='radio' class='' name='{$fname['fieldname']}' value='S'";
                                if ($idrow[$fname['fieldname']] == "I") {$aDataentryoutput .= " checked"; }
                                $aDataentryoutput .= " />Same&nbsp;\n"
                                ."\t<input type='radio' class='' name='{$fname['fieldname']}' value='D'";
                                if ($idrow[$fname['fieldname']] == "D") {$aDataentryoutput .= " checked"; }
                                $aDataentryoutput .= " />Decrease&nbsp;\n"
                                ."</td>\n"
                                ."\t</tr>\n";
                                $fname = next($fnames);
                            }
                            $fname = prev($fnames);
                            $aDataentryoutput .= "</table>\n";
                            break;
                        case "F": //ARRAY (Flexible Labels)
                        case "H":
                        case "1":
                            $aDataentryoutput .= "<table class='table'>\n";
                            $thisqid = $fname['qid'];
                            while (isset($fname['qid']) && $fname['qid'] == $thisqid) {
                                $aDataentryoutput .= "\t<tr>\n"
                                ."<td>{$fname['subquestion']}";
                                if (isset($fname['scale'])) {
                                    $aDataentryoutput .= " (".$fname['scale'].')';
                                }
                                $aDataentryoutput .= "</td>\n";
                                $scale_id = 0;
                                if (isset($fname['scale_id'])) {
                                    $scale_id = $fname['scale_id'];
                                }
                                $fquery = "SELECT * FROM {{answers}} WHERE qid='{$fname['qid']}' and scale_id={$scale_id} and language='$sDataEntryLanguage' order by sortorder, answer";
                                $fresult = dbExecuteAssoc($fquery);
                                $aDataentryoutput .= "<td>\n";
                                foreach ($fresult->readAll() as $frow) {
                                    $aDataentryoutput .= "\t<input type='radio' class='' name='{$fname['fieldname']}' value='{$frow['code']}'";
                                    if ($idrow[$fname['fieldname']] == $frow['code']) {$aDataentryoutput .= " checked"; }
                                    $aDataentryoutput .= " />".$frow['answer']."&nbsp;\n";
                                }
                                //Add 'No Answer'
                                $aDataentryoutput .= "\t<input type='radio' class='' name='{$fname['fieldname']}' value=''";
                                if ($idrow[$fname['fieldname']] == '') {$aDataentryoutput .= " checked"; }
                                $aDataentryoutput .= " />".gT("No answer")."&nbsp;\n";

                                $aDataentryoutput .= "</td>\n"
                                ."\t</tr>\n";
                                $fname = next($fnames);
                            }
                            $fname = prev($fnames);
                            $aDataentryoutput .= "</table>\n";
                            break;
                        case ":": //ARRAY (Multi Flexi) (Numbers)
                            $qidattributes = QuestionAttribute::model()->getQuestionAttributes($fname['qid']);
                            $minvalue = 1;
                            $maxvalue = 10;
                            if (trim($qidattributes['multiflexible_max']) != '' && trim($qidattributes['multiflexible_min']) == '') {
                                $maxvalue = $qidattributes['multiflexible_max'];
                                $minvalue = 1;
                            }
                            if (trim($qidattributes['multiflexible_min']) != '' && trim($qidattributes['multiflexible_max']) == '') {
                                $minvalue = $qidattributes['multiflexible_min'];
                                $maxvalue = $qidattributes['multiflexible_min'] + 10;
                            }
                            if (trim($qidattributes['multiflexible_min']) != '' && trim($qidattributes['multiflexible_max']) != '') {
                                if ($qidattributes['multiflexible_min'] < $qidattributes['multiflexible_max']) {
                                    $minvalue = $qidattributes['multiflexible_min'];
                                    $maxvalue = $qidattributes['multiflexible_max'];
                                }
                            }


                            if (trim($qidattributes['multiflexible_step']) != '') {
                                $stepvalue = $qidattributes['multiflexible_step'];
                            } else {
                                $stepvalue = 1;
                            }
                            if ($qidattributes['multiflexible_checkbox'] != 0) {
                                $minvalue = 0;
                                $maxvalue = 1;
                                $stepvalue = 1;
                            }
                            $aDataentryoutput .= "<table class='table'>\n";
                            $thisqid = $fname['qid'];
                            while (isset($fname['qid']) && $fname['qid'] == $thisqid) {
                                $aDataentryoutput .= "\t<tr>\n"
                                . "<td>{$fname['subquestion1']}:{$fname['subquestion2']}</td>\n";
                                $aDataentryoutput .= "<td>\n";
                                if ($qidattributes['input_boxes'] != 0) {
                                    $aDataentryoutput .= CHtml::numberField($fname['fieldname'], $idrow[$fname['fieldname']],array('step'=>'any'));
                                } else {
                                    $aDataentryoutput .= "\t<select name='{$fname['fieldname']}' class='form-control'>\n";
                                    $aDataentryoutput .= "<option value=''>...</option>\n";
                                    for ($ii = $minvalue; $ii <= $maxvalue; $ii += $stepvalue) {
                                        $aDataentryoutput .= "<option value='$ii'";
                                        if ($idrow[$fname['fieldname']] == $ii) {$aDataentryoutput .= " selected"; }
                                        $aDataentryoutput .= ">$ii</option>\n";
                                    }
                                }

                                $aDataentryoutput .= "</td>\n"
                                ."\t</tr>\n";
                                $fname = next($fnames);
                            }
                            $fname = prev($fnames);
                            $aDataentryoutput .= "</table>\n";
                            break;
                        case ";": //ARRAY (Multi Flexi)
                            $aDataentryoutput .= "<table class='table'>\n";
                            $thisqid = $fname['qid'];
                            while (isset($fname['qid']) && $fname['qid'] == $thisqid) {
                                $aDataentryoutput .= "\t<tr>\n"
                                . "<td>{$fname['subquestion1']}:{$fname['subquestion2']}</td>\n";
                                $aDataentryoutput .= "<td>\n";
                                $aDataentryoutput .= CHtml::textField($fname['fieldname'], $idrow[$fname['fieldname']]);
                                  $aDataentryoutput .= "</td>\n"
                                ."\t</tr>\n";
                                $fname = next($fnames);
                            }
                            $fname = prev($fnames);
                            $aDataentryoutput .= "</table>\n";
                            break;
                        case "token":
                            if (Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'update')) {
                                $aDataentryoutput .= CHtml::textField($fname['fieldname'], $idrow[$fname['fieldname']]);
                            } else {
                                $aDataentryoutput .= CHtml::textField($fname['fieldname'], $idrow[$fname['fieldname']], array('disabled'=>'disabled'));
                            }
                            break;
                        case "submitdate":
                        case "startdate":
                        case "datestamp":
                            $thisdate = "";
                            if ($idrow[$fname['fieldname']] != '') {
                                $datetimeobj = DateTime::createFromFormat("Y-m-d H:i:s", $idrow[$fname['fieldname']]);
                                if($datetimeobj == null) { //MSSQL uses microseconds by default in any datetime object
                                    $datetimeobj = DateTime::createFromFormat("Y-m-d H:i:s.u", $idrow[$fname['fieldname']]);
                                }
                                if($datetimeobj) {
                                    $thisdate = $datetimeobj->format("Y-m-d\TH:i");
                                }
                            }
                            $aDataentryoutput .= CHtml::dateTimeLocalField($fname['fieldname'], $thisdate);
                            break;
                        case "startlanguage":
                        default:
                            $aDataentryoutput .= CHtml::textField($fname['fieldname'], $idrow[$fname['fieldname']]);
                           break;
                    }

                    $aDataentryoutput .= "        </td>
                    </tr>\n";
                } while ($fname = next($fnames));
            }
            $aDataentryoutput .= "</table>\n"
            ."<p>\n";

            $aData['sDataEntryLanguage'] = $sDataEntryLanguage;

            if (!Permission::model()->hasSurveyPermission($surveyid, 'responses', 'update')) {
// if you are not survey owner or super admin you cannot modify responses
                $aDataentryoutput .= "<p><input type='button' value='".gT("Save")."' disabled='disabled'/></p>\n";
            } elseif ($subaction == "edit" && Permission::model()->hasSurveyPermission($surveyid, 'responses', 'update')) {
                $aData['part'] = 'edit';
                $aDataentryoutput .= $this->getController()->renderPartial('/admin/dataentry/edit', $aData, true);
            } elseif ($subaction == "editsaved" && Permission::model()->hasSurveyPermission($surveyid, 'responses', 'update')) {
                $aData['part'] = 'editsaved';
                $aDataentryoutput .= $this->getController()->renderPartial('/admin/dataentry/edit', $aData, true);
            }

            $aDataentryoutput .= "</form>\n";

            $aViewUrls['output'] = $aDataentryoutput;
            $aData['sidemenu']['state'] = false;
            $aData['menu']['edition'] = true;
            $aData['menu']['save'] = true;
            $aData['menu']['close'] = true;
            //$aData['menu']['savebutton'] = 'frmeditgroup';
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
        $surveyid = '';
        if (isset($_REQUEST['surveyid']) && !empty($_REQUEST['surveyid'])) {
            $surveyid = $_REQUEST['surveyid'];
        }
        if (!empty($_REQUEST['sid'])) {
            $surveyid = (int) $_REQUEST['sid'];
        }

        $surveyid = sanitize_int($surveyid);
        $survey = Survey::model()->findByPk($surveyid);
        $id = $_REQUEST['id'];

        $aData = array(
        'surveyid' => $surveyid,
        'id' => $id
        );

        if (Permission::model()->hasSurveyPermission($surveyid, 'responses', 'read')
            && Permission::model()->hasSurveyPermission($surveyid, 'responses', 'delete')) {
            $surveytable = $survey->responsesTableName;
            $aData['thissurvey'] = getSurveyInfo($surveyid);

            $delquery = "DELETE FROM $surveytable WHERE id=$id";
            Yii::app()->loadHelper('database');

            $beforeDataEntryDelete = new PluginEvent('beforeDataEntryDelete');
            $beforeDataEntryDelete->set('iSurveyID', $surveyid);
            $beforeDataEntryDelete->set('iResponseID', $id);
            App()->getPluginManager()->dispatchEvent($beforeDataEntryDelete);

            dbExecuteAssoc($delquery) or safeDie("Couldn't delete record $id<br />\n");

            $aData['sidemenu']['state'] = false;
            $aData['menu']['edition'] = true;
            $aData['menu']['close'] = true;


            $this->_renderWrappedTemplate('dataentry', 'delete', $aData);
        }
    }

    /**
     * dataentry::update()
     * update dataentry
     */
    public function update()
    {
        $surveyid = App()->getRequest()->getParam('surveyid',App()->getRequest()->getParam('sid'));
        if(!Permission::model()->hasSurveyPermission($surveyid, 'responses', 'update')) {
            throw new CHttpException(403);
        }

        $surveyid = (int) ($surveyid);
        $survey = Survey::model()->findByPk($surveyid);
        if(!$survey->getIsActive()) {
            throw new CHttpException(404,gT("Invalid survey ID"));
        }
        $id = Yii::app()->request->getPost('id');
        $oReponse = Response::model($surveyid)->findByPk($id);
        if(empty($oReponse)) {
            throw new CHttpException(404,gT("Invalid ID"));
        }
        //~ Yii::app()->loadHelper("database");
        $fieldmap = createFieldMap($survey, 'full', false, false, $survey->language);
        // reset token if user is not allowed to update
        if (!Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'update')) {
            // If not allowed to read: remove it
            unset($fieldmap['token']);
        }
        // unset timings
        foreach ($fieldmap as $fname) {
            if ($fname['type'] == "interview_time" || $fname['type'] == "page_time" || $fname['type'] == "answer_time") {
                unset($fieldmap[$fname['fieldname']]);
            }
        }

        $thissurvey = getSurveyInfo($surveyid);
        foreach ($fieldmap as $irow) {
            $fieldname = $irow['fieldname'];
            if ($fieldname == 'id') {
                continue;
            }
            $thisvalue = Yii::app()->request->getPost($fieldname);
            switch ($irow['type']) {
                case 'lastpage':
                    // Last page not updated : not in view 
                    break;
                case 'D' :
                    if(empty($thisvalue)) {
                        $oReponse->$fieldname = null;
                        break;
                    } 
                    $qidattributes = QuestionAttribute::model()->getQuestionAttributes($irow['qid']);
                    $dateformatdetails = getDateFormatDataForQID($qidattributes, $thissurvey);
                    $datetimeobj = DateTime::createFromFormat('!'.$dateformatdetails['phpdate'], $thisvalue);
                    if (!$datetimeobj) {
                        /* Not able to use js system */
                        $datetimeobj = DateTime::createFromFormat('Y-m-d\TH:i', $thisvalue);
                    }
                    if ($datetimeobj) {
                        $oReponse->$fieldname = $datetimeobj->format('Y-m-d H:i');
                    } else {
                        Yii::app()->setFlashMessage(sprintf(gT("Invalid datetime %s value for %s"),htmlentities($thisvalue),$fieldname), 'warning');
                        $oReponse->$fieldname = null;
                    }
                    break;
                case 'N':
                case 'K':
                    if($thisvalue === "") {
                        $oReponse->$fieldname = null;
                        break;
                    }
                    if(!preg_match("/^[-]?(\d{1,20}\.\d{0,10}|\d{1,20})$/",$thisvalue)) {
                        Yii::app()->setFlashMessage(sprintf(gT("Invalid numeric value for %s"),$fieldname), 'warning');
                        $oReponse->$fieldname = null;
                        break;
                    }
                    $oReponse->$fieldname = $thisvalue;
                    break;
                case '|':
                    if(strpos($irow['fieldname'], '_filecount')) {
                        if(empty($thisvalue)) {
                            $oReponse->$fieldname = null;
                            break;
                        }
                        $oReponse->$fieldname = $thisvalue;
                        break;
                    }
                    $oReponse->$fieldname = $thisvalue;
                    break;
                case ':':
                    if( !empty($thisvalue) && strval($thisvalue) !=strval(floatval($thisvalue)) ) {
                        // mysql not need, unsure about mssql
                        Yii::app()->setFlashMessage(sprintf(gT("Invalid numeric value for %s"),$fieldname), 'warning');
                        $oReponse->$fieldname = null;
                        break;
                    }
                    $oReponse->$fieldname = $thisvalue;
                    break;
                case 'submitdate':
                    if(Yii::app()->request->getPost('completed') == "N") {
                        $oReponse->$fieldname = null;
                        break;
                    }
                    if(empty($thisvalue)) {
                        if (Survey::model()->findByPk($surveyid)->isDateStamp) {
                            $thisvalue = dateShift(date("Y-m-d H:i"), "Y-m-d\TH:i", Yii::app()->getConfig('timeadjust'));
                        } else {
                            $thisvalue = date("Y-m-d\TH:i", (int) mktime(0, 0, 0, 1, 1, 1980));
                        }
                    }
                case 'startdate':
                case 'datestamp':
                    if(empty($thisvalue)) {
                        $oReponse->$fieldname = null;
                        break;
                    }
                    $dateformatdetails = getDateFormatForSID($surveyid);
                    $datetimeobj = DateTime::createFromFormat('!'.$dateformatdetails['phpdate'], $thisvalue);
                    if (!$datetimeobj) {
                        /* Not able to use js system */
                        $datetimeobj = DateTime::createFromFormat('Y-m-d\TH:i', $thisvalue);
                    }
                    if ($datetimeobj) {
                        $oReponse->$fieldname = $datetimeobj->format('Y-m-d H:i');
                    } else {
                        Yii::app()->setFlashMessage(sprintf(gT("Invalid datetime %s value for %s"),htmlentities($thisvalue),$fieldname), 'warning');
                        if($irow['type'] != 'submitdate') {
                            $oReponse->$fieldname = date("Y-m-d H:i:s");// Need not null value
                        } else {
                            $oReponse->$fieldname = null;
                        }
                    }
                    break;
                default:
                    $oReponse->$fieldname = $thisvalue;
            }
        }
        $beforeDataEntryUpdate = new PluginEvent('beforeDataEntryUpdate');
        $beforeDataEntryUpdate->set('iSurveyID', $surveyid);
        $beforeDataEntryUpdate->set('iResponseID', $id);
        App()->getPluginManager()->dispatchEvent($beforeDataEntryUpdate);
        if(!$oReponse->save()) {
            Yii::app()->setFlashMessage(CHtml::errorSummary($oReponse), 'error');
        } else {
            Yii::app()->setFlashMessage(sprintf(gT("The response record %s was updated."), $id));
        }
        if (Yii::app()->request->getPost('close-after-save') == 'true') {
            $this->getController()->redirect($this->getController()->createUrl("admin/responses/sa/view/surveyid/{$surveyid}/id/{$id}"));
        } else {
            $this->getController()->redirect($this->getController()->createUrl("admin/dataentry/sa/editdata/subaction/edit/surveyid/{$surveyid}/id/{$id}"));
        }
    }

    /**
     * dataentry::insert()
     * insert new dataentry
     */
    public function insert()
    {
        $subaction = Yii::app()->request->getPost('subaction');
        $surveyid = (int) Yii::app()->request->getPost('sid');

        $lang = isset($_POST['lang']) ? Yii::app()->request->getPost('lang') : null;
        $survey = Survey::model()->findByPk($surveyid);
        $password = '';
        $aData = array(
            'surveyid' => $surveyid,
            'lang' => $lang
        );

        if ($subaction == "insert" && Permission::model()->hasSurveyPermission($surveyid, 'responses', 'create')) {
            $surveytable = "{{survey_{$surveyid}}}";
            $thissurvey = getSurveyInfo($surveyid);
            $errormsg = "";

            Yii::app()->loadHelper("database");
            $aViewUrls = [];
            $aViewUrls['display']['menu_bars']['browse'] = gT("Data entry");

            $aDataentrymsgs = array();
            $hiddenfields = '';
            $lastanswfortoken = ''; // check if a previous answer has been submitted or saved

            if (Yii::app()->request->getPost('token') && Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'update')) {
                $tokencompleted = "";
                $tcquery = "SELECT completed from {{tokens_{$surveyid}}} WHERE token=".App()->db->quoteValue($_POST['token']);
                $tcresult = dbExecuteAssoc($tcquery);
                $tcresult = $tcresult->readAll();
                $tccount = count($tcresult);
                foreach ($tcresult as $tcrow) {
                    $tokencompleted = $tcrow['completed'];
                }

                if ($tccount < 1) {
// token doesn't exist in survey participants table
                    $lastanswfortoken = 'UnknownToken';
                } elseif ($survey->isAnonymized) {
// token exist but survey is anonymous, check completed state
                    // token is completed
                    if ($tokencompleted != "" && $tokencompleted != "N") {
                        $lastanswfortoken = 'PrivacyProtected';
                    }
                } else {
// token is valid, survey not anonymous, try to get last recorded response id
                    $aquery = "SELECT id,startlanguage FROM $surveytable WHERE token=".App()->db->quoteValue($_POST['token']);
                    $aresult = dbExecuteAssoc($aquery);
                    foreach ($aresult->readAll() as $arow) {
                        if ($tokencompleted != "N") { $lastanswfortoken = $arow['id']; }
                        $rlanguage = $arow['startlanguage'];
                    }
                }
            }

            $tokenTableExists = $survey->hasTokensTable;

            // First Check if the survey uses tokens and if a token has been provided
            if ($tokenTableExists && (!$_POST['token'])) {
                $errormsg = CHtml::tag('div', array('class'=>'warningheader'), gT("Error"));
                $errormsg .= CHtml::tag('p', array(), gT("This is a closed-access survey, so you must supply a valid token.  Please contact the administrator for assistance."));
            } elseif ($tokenTableExists && $lastanswfortoken == 'UnknownToken') {
                $errormsg = CHtml::tag('div', array('class'=>'warningheader'), gT("Error"));
                $errormsg .= CHtml::tag('p', array(), gT("The token you have provided is not valid or has already been used."));
            } elseif ($tokenTableExists && $lastanswfortoken != '') {
                $errormsg = CHtml::tag('div', array('class'=>'warningheader'), gT("Error"));
                $errormsg .= CHtml::tag('p', array(), gT("There is already a recorded answer for this token"));

                if ($lastanswfortoken != 'PrivacyProtected') {
                    $errormsg .= "<br /><br />".gT("Follow the following link to update it").":\n";
                    $errormsg .= CHtml::link("[id:$lastanswfortoken]",
                        $this->getController()->createUrl('/admin/dataentry/sa/editdata/subaction/edit/id/'.$lastanswfortoken.'/surveyid/'.$surveyid),
                        array('title' => gT("Edit this entry")));
                    $errormsg .= "<br/><br/>";
                } else {
                    $errormsg .= "<br /><br />".gT("This surveys uses anonymized responses, so you can't update your response.")."\n";
                }
            } else {

                if (isset($_POST['save']) && $_POST['save'] == "on") {
                    $aData['save'] = true;
                    $saver = [];
                    $saver['identifier'] = $_POST['save_identifier'];
                    $saver['language'] = $_POST['save_language'];
                    $saver['password'] = $_POST['save_password'];
                    $saver['passwordconfirm'] = $_POST['save_confirmpassword'];
                    $saver['email'] = $_POST['save_email'];
                    if (!returnGlobal('redo')) {
                        $password = md5($saver['password']);
                    } else {
                        $password = $saver['password'];
                    }
                    $errormsg = "";
                    if (!$saver['identifier']) { $errormsg .= gT("Error").": ".gT("You must supply a name for this saved session."); }
                    if (!$saver['password']) { $errormsg .= gT("Error").": ".gT("You must supply a password for this saved session."); }
                    if ($saver['password'] != $saver['passwordconfirm']) { $errormsg .= gT("Error").": ".gT("Your passwords do not match."); }

                    $aData['errormsg'] = $errormsg;

                    if ($errormsg) {
                        foreach ($_POST as $key=>$val) {
                            if (substr($key, 0, 4) != "save" && $key != "action" && $key != "sid" && $key != "datestamp" && $key != "ipaddr") {
                                $hiddenfields .= CHtml::hiddenField($key, $val);
                                //$aDataentryoutput .= "<input type='hidden' name='$key' value='$val' />\n";
                            }
                        }
                    }
                }

                //BUILD THE SQL TO INSERT RESPONSES
                $fieldmap = createFieldMap($survey, 'full', false, false, $survey->language);
                $insert_data = array();

                $_POST['startlanguage'] = $survey->language;
                if ($survey->isDateStamp) { $_POST['startdate'] = $_POST['datestamp']; }
                if (isset($_POST['closerecord'])) {
                    if ($survey->isDateStamp) {
                        $_POST['submitdate'] = dateShift((string) date("Y-m-d H:i"), "Y-m-d H:i", Yii::app()->getConfig('timeadjust'));
                    } else {
                        $_POST['submitdate'] = date("Y-m-d H:i", (int) mktime(0, 0, 0, 1, 1, 1980));
                    }
                }
                $phparray = [];
                foreach ($fieldmap as $irow) {
                    $fieldname = $irow['fieldname'];
                    if (isset($_POST[$fieldname])) {
                        if ($_POST[$fieldname] == "" && ($irow['type'] == 'D' || $irow['type'] == 'N' || $irow['type'] == 'K')) {
// can't add '' in Date column
                            // Do nothing
                        } else if ($irow['type'] == '|') {
                            if (!strpos($irow['fieldname'], "_filecount")) {
                                $json = $_POST[$fieldname];
                                $phparray = json_decode(stripslashes($json));
                                $filecount = 0;
                                if (is_array($phparray)){
                                    $iArrayCount = count($phparray);
                                    for ($i = 0; $filecount < $iArrayCount; $i++) {
                                        if ($_FILES[$fieldname."_file_".$i]['error'] != 4) {
                                            $target = Yii::app()->getConfig('uploaddir')."/surveys/".$thissurvey['sid']."/files/".randomChars(20);
                                            $size = 0.001 * $_FILES[$fieldname."_file_".$i]['size'];
                                            $name = rawurlencode($_FILES[$fieldname."_file_".$i]['name']);

                                            if (move_uploaded_file($_FILES[$fieldname."_file_".$i]['tmp_name'], $target)) {
                                                $phparray[$filecount]->filename = basename($target);
                                                $phparray[$filecount]->name = $name;
                                                $phparray[$filecount]->size = $size;
                                                $pathinfo = pathinfo($_FILES[$fieldname."_file_".$i]['name']);
                                                $phparray[$filecount]->ext = $pathinfo['extension'];
                                                $filecount++;
                                            }
                                        }
                                    }
                                }

                                $insert_data[$fieldname] = ls_json_encode($phparray);

                            } else {
                                if (is_array($phparray)){
                                    $insert_data[$fieldname] = count($phparray);
                                } else {
                                    $insert_data[$fieldname] = 0;
                                }
                            }
                        } elseif ($irow['type'] == 'D') {
                            $qidattributes = QuestionAttribute::model()->getQuestionAttributes($irow['qid']);
                            $dateformatdetails = getDateFormatDataForQID($qidattributes, $thissurvey);
                            $datetimeobj = DateTime::createFromFormat('!'.$dateformatdetails['phpdate'], $_POST[$fieldname]);
                            if ($datetimeobj) {
                                $dateoutput = $datetimeobj->format('Y-m-d H:i');
                            } else {
                                $dateoutput = '';
                            }
                            $insert_data[$fieldname] = $dateoutput;
                        } else {
                            $insert_data[$fieldname] = $_POST[$fieldname];
                        }
                    }
                }

                SurveyDynamic::sid($surveyid);
                $new_response = new SurveyDynamic;
                foreach ($insert_data as $column => $value) {
                    $new_response->$column = $value;
                }

                $beforeDataEntryCreate = new PluginEvent('beforeDataEntryCreate');
                $beforeDataEntryCreate->set('iSurveyID', $surveyid);
                $beforeDataEntryCreate->set('oModel', $new_response);
                App()->getPluginManager()->dispatchEvent($beforeDataEntryCreate);

                $new_response->save();
                $last_db_id = $new_response->getPrimaryKey();
                if (isset($_POST['closerecord']) && isset($_POST['token']) && $_POST['token'] != '') {
// submittoken
                    // get submit date
                    if (isset($_POST['closedate'])) {
                        $submitdate = $_POST['closedate'];
                    } else {
                        $submitdate = date("Y-m-d H:i:s");
                    }

                    // check how many uses the token has left
                    $usesquery = "SELECT usesleft FROM {{tokens_}}$surveyid WHERE token=".App()->db->quoteValue($_POST['token']);
                    $usesresult = dbExecuteAssoc($usesquery);
                    $usesrow = $usesresult->readAll(); //$usesresult->row_array()
                    if (isset($usesrow)) { $usesleft = $usesrow[0]['usesleft']; }

                    // query for updating tokens
                    $utquery = "UPDATE {{tokens_$surveyid}}\n";
                    if (isTokenCompletedDatestamped($thissurvey)) {
                        if (isset($usesleft) && $usesleft <= 1) {
                            $utquery .= "SET usesleft=usesleft-1, completed=".App()->db->quoteValue($submitdate);
                        } else {
                            $utquery .= "SET usesleft=usesleft-1\n";
                        }
                    } else {
                        if (isset($usesleft) && $usesleft <= 1) {
                            $utquery .= "SET usesleft=usesleft-1, completed='Y'\n";
                        } else {
                            $utquery .= "SET usesleft=usesleft-1\n";
                        }
                    }
                    $utquery .= "WHERE token=".App()->db->quoteValue($_POST['token']);
                    dbExecuteAssoc($utquery); //Yii::app()->db->Execute($utquery) or safeDie ("Couldn't update tokens table!<br />\n$utquery<br />\n".Yii::app()->db->ErrorMsg());

                    // save submitdate into survey table
                    $sdquery = "UPDATE {{survey_$surveyid}} SET submitdate='".$submitdate."' WHERE id={$last_db_id}\n";
                    dbExecuteAssoc($sdquery) or safeDie("Couldn't set submitdate response in survey table!<br />\n$sdquery<br />\n");
                }
                if (isset($_POST['save']) && $_POST['save'] == "on") {
                    $srid = $last_db_id;
                    $aUserData = Yii::app()->session;
                    //CREATE ENTRY INTO "saved_control"


                    $saved_control_table = '{{saved_control}}';

                    $columns = array("sid", "srid", "identifier", "access_code", "email", "ip",
                        "refurl", 'saved_thisstep', "status", "saved_date");
                    $values = array("'".$surveyid."'", "'".$srid."'", "'".$saver['identifier']."'", "'".$password."'", "'".$saver['email']."'", "'".$aUserData['ip_address']."'",
                        "'".(string) getenv("HTTP_REFERER")."'", 0, "'"."S"."'", "'".dateShift((string) date("Y-m-d H:i:s"), "Y-m-d H:i", "'".Yii::app()->getConfig('timeadjust'))."'");

                    $SQL = "INSERT INTO $saved_control_table
                        (".implode(',', $columns).")
                        VALUES
                        (".implode(',', $values).")";

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
                    if (dbExecuteAssoc($SQL)) {
                        $scid = getLastInsertID('{{saved_control}}');

                        $aDataentrymsgs[] = CHtml::tag('font', array('class'=>'successtitle'), gT("Your survey responses have been saved successfully.  You will be sent a confirmation e-mail. Please make sure to save your password, since we will not be able to retrieve it for you."));
                        //$aDataentryoutput .= "<font class='successtitle'></font><br />\n";

                        $tokens_table = "{{tokens_$surveyid}}";
                        if (tableExists($tokens_table)) {
//If the query fails, assume no tokens table exists
                            $tkquery = "SELECT * FROM {$tokens_table}";
                            dbExecuteAssoc($tkquery);
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

                            $SQL = "INSERT INTO {$tokens_table}
                                (".implode(',', $columns).")
                                VALUES
                                (".implode(',', $values).")";
                            dbExecuteAssoc($SQL);
                            $aDataentrymsgs[] = CHtml::tag('font', array('class'=>'successtitle'), gT("A survey participant entry for the saved survey has been created too."));
                            //$aDataentryoutput .= "<font class='successtitle'></font><br />\n";
                        }
                        if ($saver['email']) {
                            //Send email
                            if (validateEmailAddress($saver['email']) && !returnGlobal('redo')) {
                                $subject = gT("Saved Survey Details");
                                $message = gT("Thank you for saving your survey in progress.  The following details can be used to return to this survey and continue where you left off.  Please keep this e-mail for your reference - we cannot retrieve the password for you.");
                                $message .= "\n\n".$thissurvey['name']."\n\n";
                                $message .= gT("Name").": ".$saver['identifier']."\n";
                                $message .= gT("Password").": ".$saver['password']."\n\n";
                                $message .= gT("Reload your survey by clicking on the following link (or pasting it into your browser):")."\n";
                                $aParams = array('lang'=>$saver['language'], 'loadname'=>$saver['identifier'], 'loadpass'=>$saver['password']);
                                $message .= Yii::app()->getController()->createAbsoluteUrl("/survey/index/sid/{$surveyid}/loadall/reload/scid/{$scid}/", $aParams);
                                $from     = $thissurvey['adminemail'];
                                $sitename = Yii::app()->getConfig('sitename');
                                if (SendEmailMessage($message, $subject, $saver['email'], $from, $sitename, false, getBounceEmail($surveyid))) {
                                    $aDataentrymsgs[] = CHtml::tag('font', array('class'=>'successtitle'), gT("An email has been sent with details about your saved survey"));
                                }
                            }
                        }

                    } else {
                        safeDie("Unable to insert record into saved_control table.<br /><br />");
                    }

                }
                $aData['thisid'] = $last_db_id;
            }

            $aData['errormsg'] = $errormsg;

            $aData['dataentrymsgs'] = $aDataentrymsgs;

            $aData['sidemenu']['state'] = false;
            $aData['hiddenfields'] = $hiddenfields;

            $this->_renderWrappedTemplate('dataentry', 'insert', $aData);
        }
    }

    /**
     * dataentry::view()
     * view a dataentry
     * @param mixed $surveyid
     */
    public function view($surveyid)
    {
        $surveyid = sanitize_int($surveyid);
        $survey = Survey::model()->findByPk($surveyid);
        $lang = isset($_GET['lang']) ? $_GET['lang'] : null;
        if (isset($lang)) {
            $lang = sanitize_languagecode($lang);
        }
        $aViewUrls = array();

        if (Permission::model()->hasSurveyPermission($surveyid, 'responses', 'create')) {
            $baselang = Survey::model()->findByPk($surveyid)->language;
            $slangs = $survey->allLanguages;

            if (is_null($lang) || !in_array($lang, $slangs)) {
                $sDataEntryLanguage = $baselang;
            } else {
                $sDataEntryLanguage = $lang;
            }

            $langlistbox = languageDropdown($surveyid, $sDataEntryLanguage);
            $thissurvey = getSurveyInfo($surveyid);

            //This is the default, presenting a blank dataentry form
            LimeExpressionManager::StartSurvey($surveyid, 'survey', null, false, LEM_PRETTY_PRINT_ALL_SYNTAX);
            LimeExpressionManager::NavigateForwards();

            $aData = [];
            $aData['survey'] = $survey;
            $aData['thissurvey'] = $thissurvey;
            $aData['langlistbox'] = $langlistbox;
            $aData['surveyid'] = $surveyid;
            $aData['sDataEntryLanguage'] = $sDataEntryLanguage;
            $aData['site_url'] = Yii::app()->homeUrl;

            LimeExpressionManager::StartProcessingPage(true); // means that all variables are on the same page

            $aViewUrls[] = 'caption_view';

            Yii::app()->loadHelper('database');

            // SURVEY NAME AND DESCRIPTION TO GO HERE
            $degquery = "SELECT * FROM ".Yii::app()->db->quoteTableName('{{groups}}')." g WHERE sid=$surveyid AND language='{$sDataEntryLanguage}' ORDER BY g.group_order";
            $degresult = dbExecuteAssoc($degquery);
            // GROUP NAME
            $aDataentryoutput = '';

            foreach ($degresult->readAll() as $degrow) {
                LimeExpressionManager::StartProcessingGroup($degrow['gid'], ($thissurvey['anonymized'] != "N"), $surveyid);

                $deqquery = "SELECT * FROM {{questions}} WHERE sid=$surveyid AND parent_qid=0 AND gid={$degrow['gid']} AND language='{$sDataEntryLanguage}'";
                $deqrows = (array) dbExecuteAssoc($deqquery)->readAll();
                $aDataentryoutput .= "\t<tr class='info'>\n"
                ."<!-- Inside controller dataentry.php -->"
                ."<td colspan='3'><h4>".flattenText($degrow['group_name'], true)."</h4></td>\n"
                ."\t</tr>\n";

                $gid = $degrow['gid'];

                $aDataentryoutput .= "\t<tr class='data-entry-separator'><td colspan='3'></td></tr>\n";

                // Perform a case insensitive natural sort on group name then question title of a multidimensional array
                usort($deqrows, 'groupOrderThenQuestionOrder');
                $bgc = 'odd';
                foreach ($deqrows as $deqrow) {
                    $cdata = array();
                    $qidattributes = QuestionAttribute::model()->getQuestionAttributes($deqrow['qid']);
                    $cdata['qidattributes'] = $qidattributes;

                    $qinfo = LimeExpressionManager::GetQuestionStatus($deqrow['qid']);
                    $relevance = trim($qinfo['info']['relevance']);
                    $explanation = trim($qinfo['relEqn']);
                    $validation = trim($qinfo['prettyValidTip']);
                    $qidattributes = QuestionAttribute::model()->getQuestionAttributes($deqrow['qid']);
                    $array_filter_help = flattenText($this->_array_filter_help($qidattributes, $sDataEntryLanguage, $surveyid));

                    if (($relevance != '' && $relevance != '1') || ($validation != '') || ($array_filter_help != '')) {
                        $showme = '<div class="alert alert-warning col-sm-8 col-sm-offset-2" role="alert">';
                        if ($bgc == "even") {$bgc = "odd"; } else {$bgc = "even"; } //Do no alternate on explanation row
                        if ($relevance != '' && $relevance != '1') {
                            $showme = '<strong>'.gT("Only answer this if the following conditions are met:", 'html', $sDataEntryLanguage)."</strong><br />$explanation\n";
                        }
                        if ($validation != '') {
                            $showme .= '<strong>'.gT("The answer(s) must meet these validation criteria:", 'html', $sDataEntryLanguage)."</strong><br />$validation\n";
                        }
                        if ($showme != '' && $array_filter_help != '') {
                            $showme .= '<br/>';
                        }
                        if ($array_filter_help != '') {
                            $showme .= '<strong>'.gT("The answer(s) must meet these array_filter criteria:", 'html', $sDataEntryLanguage)."</strong><br />$array_filter_help\n";
                        }
                        $showme .= '</div>';
                        $cdata['explanation'] = "<tr class ='data-entry-explanation'><td class='data-entry-small-text' colspan='3' align='left'>$showme</td></tr>\n";
                    }

                    //END OF GETTING CONDITIONS

                    //Alternate bgcolor for different groups
                    if (!isset($bgc)) {$bgc = "even"; }
                    if ($bgc == "even") {$bgc = "odd"; } else {$bgc = "even"; }

                    $qid = $deqrow['qid'];
                    $fieldname = "$surveyid"."X"."$gid"."X"."$qid";

                    $cdata['bgc'] = $bgc;
                    $cdata['fieldname'] = $fieldname;
                    $cdata['deqrow'] = $deqrow;

                    $cdata['thissurvey'] = $thissurvey;
                    if ($deqrow['help']) {
                        $hh = addcslashes($deqrow['help'], "\0..\37'\""); //Escape ASCII decimal 0-32 plus single and double quotes to make JavaScript happy.
                        $hh = htmlspecialchars($hh, ENT_QUOTES); //Change & " ' < > to HTML entities to make HTML happy.
                        $cdata['hh'] = $hh;
                    }
                    switch ($deqrow['type']) {
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

                            $oquery = "SELECT other FROM {{questions}} WHERE qid={$deqrow['qid']} AND language='{$baselang}'";
                            $oresult = dbExecuteAssoc($oquery) or safeDie("Couldn't get other for list question<br />".$oquery);
                            foreach ($oresult->readAll() as $orow) {
                                $cdata['fother'] = $orow['other'];
                            }

                            break;

                        case "L": //LIST drop-down/radio-button list
                        case "!":
                            //                            $qidattributes=QuestionAttribute::model()->getQuestionAttributes($deqrow['qid']);
                            if ($deqrow['type'] == '!' && trim($qidattributes['category_separator']) != '') {
                                $optCategorySeparator = $qidattributes['category_separator'];
                            } else {
                                unset($optCategorySeparator);
                            }
                            $defexists = "";
                            $deaquery = "SELECT * FROM {{answers}} WHERE qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' ORDER BY sortorder, answer";
                            $dearesult = dbExecuteAssoc($deaquery);
                            //$aDataentryoutput .= "\t<select name='$fieldname' class='form-control' >\n";
                            $aDatatemp = '';
                            if (!isset($optCategorySeparator)) {
                                foreach ($dearesult->readAll() as $dearow) {
                                    $aDatatemp .= "<option value='{$dearow['code']}'";
                                    //if ($dearow['default_value'] == "Y") {$aDatatemp .= " selected='selected'"; $defexists = "Y";}
                                    $aDatatemp .= ">{$dearow['answer']}</option>\n";
                                }
                            } else {
                                $defaultopts = array();
                                $optgroups = array();

                                foreach ($dearesult->readAll() as $dearow) {
                                    list ($categorytext, $answertext) = explode($optCategorySeparator, $dearow['answer']);
                                    if ($categorytext == '') {
                                        $defaultopts[] = array('code' => $dearow['code'], 'answer' => $answertext, 'default_value' => $dearow['assessment_value']);
                                    } else {
                                        $optgroups[$categorytext][] = array('code' => $dearow['code'], 'answer' => $answertext, 'default_value' => $dearow['assessment_value']);
                                    }
                                }
                                foreach ($optgroups as $categoryname => $optionlistarray) {
                                    $aDatatemp .= "<optgroup class=\"dropdowncategory\" label=\"".$categoryname."\">\n";
                                    foreach ($optionlistarray as $optionarray) {
                                        $aDatatemp .= "\t<option value='{$optionarray['code']}'";
                                        //if ($optionarray['default_value'] == "Y") {$aDatatemp .= " selected='selected'"; $defexists = "Y";}
                                        $aDatatemp .= ">{$optionarray['answer']}</option>\n";
                                    }
                                    $aDatatemp .= "</optgroup>\n";
                                }
                                foreach ($defaultopts as $optionarray) {
                                    $aDatatemp .= "\t<option value='{$optionarray['code']}'";
                                    //if ($optionarray['default_value'] == "Y") {$aDatatemp .= " selected='selected'"; $defexists = "Y";}
                                    $aDatatemp .= ">{$optionarray['answer']}</option>\n";
                                }
                            }

                            $oquery = "SELECT other FROM {{questions}} WHERE qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}'";
                            $oresult = dbExecuteAssoc($oquery) or safeDie("Couldn't get other for list question<br />");
                            $fother = '';
                            foreach ($oresult->readAll() as $orow) {
                                $fother = $orow['other'];
                            }

                            $cdata['fother'] = $fother;
                            $cdata['defexists'] = $defexists;
                            $cdata['datatemp'] = $aDatatemp;

                            break;
                        case "O": //LIST WITH COMMENT drop-down/radio-button list + textarea
                            $defexists = "";
                            $deaquery = "SELECT * FROM {{answers}} WHERE qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' ORDER BY sortorder, answer";
                            $dearesult = dbExecuteAssoc($deaquery);
                            //$aDataentryoutput .= "\t<select name='$fieldname'>\n";
                            $aDatatemp = '';
                            foreach ($dearesult->readAll() as $dearow) {
                                $aDatatemp .= "<option value='{$dearow['code']}'";
                                //if ($dearow['default_value'] == "Y") {$aDatatemp .= " selected='selected'"; $defexists = "Y";}
                                $aDatatemp .= ">{$dearow['answer']}</option>\n";

                            }
                            $cdata['datatemp'] = $aDatatemp;
                            $cdata['defexists'] = $defexists;

                            break;
                        case "R": //RANKING TYPE QUESTION
                            $thisqid = $deqrow['qid'];
                            $ansquery = "SELECT * FROM {{answers}} WHERE qid=$thisqid AND language='{$sDataEntryLanguage}' ORDER BY sortorder, answer";
                            $ansresult = dbExecuteAssoc($ansquery);
                            $ansresult = $ansresult->readAll();
                            $anscount = count($ansresult);

                            $cdata['thisqid'] = $thisqid;
                            $cdata['anscount'] = $anscount;
                            $ansresult = Yii::app()->db->createCommand($ansquery)->query()->readAll(); //Checked
                            $answers = array();
                                foreach ($ansresult as $ansrow) {
                                    $answers[] = $ansrow;
                                }
                            $cdata['answers'] = $answers;
                            App()->getClientScript()->registerPackage('jquery-actual');
                            App()->getClientScript()->registerScriptFile(App()->getConfig('generalscripts').'ranking.js');
                            App()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl').'ranking.css');
                            unset($answers);
                            break;
                        case "M": //Multiple choice checkbox (Quite tricky really!)
                            if (trim($qidattributes['display_columns']) != '') {
                                $dcols = $qidattributes['display_columns'];
                            } else {
                                $dcols = 1;
                            }
                            $meaquery = "SELECT title, question FROM {{questions}} WHERE parent_qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' ORDER BY question_order";
                            $mearesult = dbExecuteAssoc($meaquery);

                            $cdata['mearesult'] = $mearesult->readAll();
                            $meacount = count($cdata['mearesult']);
                            $cdata['meacount'] = $meacount;
                            $cdata['dcols'] = $dcols;

                            break;
                        case "I": //Language Switch
                            $slangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
                            $sbaselang = Survey::model()->findByPk($surveyid)->language;
                            array_unshift($slangs, $sbaselang);
                            $cdata['slangs'] = $slangs;

                            break;
                        case "P": //Multiple choice with comments checkbox + text
                            //$aDataentryoutput .= "<table border='0'>\n";
                            $meaquery = "SELECT * FROM {{questions}} WHERE parent_qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' ORDER BY question_order, question";
                            $mearesult = dbExecuteAssoc($meaquery);

                            $cdata['mearesult'] = $mearesult->readAll();

                            break;
                        case "|":
                            //                            $qidattributes = QuestionAttribute::model()->getQuestionAttributes($deqrow['qid']);
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
                            break;

                        case "C": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                            $meaquery = "SELECT title, question FROM {{questions}} WHERE parent_qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' ORDER BY question_order";
                            $mearesult = dbExecuteAssoc($meaquery);
                            $cdata['mearesult'] = $mearesult->readAll();

                            break;
                        case "E": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                            $meaquery = "SELECT title, question FROM {{questions}} WHERE parent_qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' ORDER BY question_order";
                            $mearesult = dbExecuteAssoc($meaquery) or safeDie("Couldn't get answers, Type \"E\"<br />$meaquery<br />");
                            $cdata['mearesult'] = $mearesult->readAll();

                            break;
                        case ":": //ARRAY (Multi Flexi)
                            //                            $qidattributes=QuestionAttribute::model()->getQuestionAttributes($deqrow['qid']);
                            $minvalue = 1;
                            $maxvalue = 10;
                            if (trim($qidattributes['multiflexible_max']) != '' && trim($qidattributes['multiflexible_min']) == '') {
                                $maxvalue = $qidattributes['multiflexible_max'];
                                $minvalue = 1;
                            }
                            if (trim($qidattributes['multiflexible_min']) != '' && trim($qidattributes['multiflexible_max']) == '') {
                                $minvalue = $qidattributes['multiflexible_min'];
                                $maxvalue = $qidattributes['multiflexible_min'] + 10;
                            }
                            if (trim($qidattributes['multiflexible_min']) != '' && trim($qidattributes['multiflexible_max']) != '') {
                                if ($qidattributes['multiflexible_min'] < $qidattributes['multiflexible_max']) {
                                    $minvalue = $qidattributes['multiflexible_min'];
                                    $maxvalue = $qidattributes['multiflexible_max'];
                                }
                            }

                            if (trim($qidattributes['multiflexible_step']) != '') {
                                $stepvalue = $qidattributes['multiflexible_step'];
                            } else {
                                $stepvalue = 1;
                            }
                            if ($qidattributes['multiflexible_checkbox'] != 0) {
                                $minvalue = 0;
                                $maxvalue = 1;
                                $stepvalue = 1;
                            }
                            $cdata['minvalue'] = $minvalue;
                            $cdata['maxvalue'] = $maxvalue;
                            $cdata['stepvalue'] = $stepvalue;

                            $lquery = "SELECT question, title FROM {{questions}} WHERE parent_qid={$deqrow['qid']} and scale_id=1 and language='{$sDataEntryLanguage}' ORDER BY question_order";
                            $lresult = dbExecuteAssoc($lquery);
                            if (!$lresult) {
                                $eMessage = "Couldn't get labels, Type \":\"<br />$lquery<br />";
                                Yii::app()->setFlashMessage($eMessage);
                                $this->getController()->redirect($this->getController()->createUrl("/admin/"));
                            }


                            $cdata['lresult'] = $lresult->readAll();

                            $meaquery = "SELECT question, title FROM {{questions}} WHERE parent_qid={$deqrow['qid']} and scale_id=0 and language='{$sDataEntryLanguage}' ORDER BY question_order";
                            $mearesult = dbExecuteAssoc($meaquery);

                            if (!$mearesult) {
                                $eMessage = "Couldn't get answers, Type \":\"<br />$meaquery<br />";
                                Yii::app()->setFlashMessage($eMessage);
                                $this->getController()->redirect($this->getController()->createUrl("/admin/"));
                            }

                            $cdata['mearesult'] = $mearesult->readAll();

                            break;
                        case ";": //ARRAY (Multi Flexi)

                            $lquery = "SELECT * FROM {{questions}} WHERE scale_id=1 and parent_qid={$deqrow['qid']} and language='{$sDataEntryLanguage}' ORDER BY question_order";
                            $lresult = dbExecuteAssoc($lquery) or safeDie("Couldn't get labels, Type \":\"<br />$lquery<br />");
                            $cdata['lresult'] = $lresult->readAll();

                            $meaquery = "SELECT * FROM {{questions}} WHERE scale_id=0 and parent_qid={$deqrow['qid']} and language='{$sDataEntryLanguage}' ORDER BY question_order";
                            $mearesult = dbExecuteAssoc($meaquery) or safeDie("Couldn't get answers, Type \":\"<br />$meaquery<br />");

                            $cdata['mearesult'] = $mearesult->readAll();

                            break;
                        case "F": //ARRAY (Flexible Labels)
                        case "H":
                            $meaquery = "SELECT * FROM {{questions}} WHERE parent_qid={$deqrow['qid']} and language='{$sDataEntryLanguage}' ORDER BY question_order";
                            $mearesult = dbExecuteAssoc($meaquery) or safeDie("Couldn't get answers, Type \"E\"<br />$meaquery<br />");

                            $cdata['mearesult'] = $mearesult->readAll();

                            $fquery = "SELECT * FROM {{answers}} WHERE qid={$deqrow['qid']} and language='{$sDataEntryLanguage}' ORDER BY sortorder, code";
                            $fresult = dbExecuteAssoc($fquery);
                            $cdata['fresult'] = $fresult->readAll();
                            break;
                    }

                    $cdata['sDataEntryLanguage'] = $sDataEntryLanguage;
                    $viewdata = $this->getController()->renderPartial("/admin/dataentry/content_view", $cdata, true);
                    $viewdata_em = LimeExpressionManager::ProcessString($viewdata, $deqrow['qid'], null, 1, 1);
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

            if ($survey->isActive && $survey->isAllowSave) {
                $aData['slangs'] = $survey->allLanguages;
                $aData['baselang'] = $survey->language;
            }

            $aViewUrls[] = 'active_html_view';

            $aData['sidemenu']['state'] = false;
            $aData['menu']['edition'] = true;
            $aData['menu']['save'] = true;
            $aData['menu']['close'] = true;

            $this->_renderWrappedTemplate('dataentry', $aViewUrls, $aData);
        }
    }

    private function _getEncodingsArray()
    {
        return aEncodingsArray();
    }

    /*
    * This is a duplicate of the array_filter_help function in printablesurvey.php
    */
    private function _array_filter_help($qidattributes, $surveyprintlang, $surveyid)
    {
        $output = "";
        if (!empty($qidattributes['array_filter'])) {

            /** @var Question $question */
            $question = Question::model()->findByAttributes(array('title' => $qidattributes['array_filter'], 'language' => $surveyprintlang, 'sid' => $surveyid));
            if ($question) {
                $output .= "\n<p class='extrahelp'>
                ".sprintf(gT("Only answer this question for the items you selected in question %s ('%s')"), $qidattributes['array_filter'], flattenText(breakToNewline($question->question)))."
                </p>\n";
            }
        }
        if (!empty($qidattributes['array_filter_exclude'])) {
            /** @var Question $question */
            $question = Question::model()->findByAttributes(array('title' => $qidattributes['array_filter_exclude'], 'language' => $surveyprintlang, 'sid' => $surveyid));
            if ($question) {
                $output .= "\n    <p class='extrahelp'>
                ".sprintf(gT("Only answer this question for the items you did not select in question %s ('%s')"), $qidattributes['array_filter_exclude'], breakToNewline($question->question))."
                </p>\n";
            }
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
    protected function _renderWrappedTemplate($sAction = 'dataentry', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        if (!isset($aData['display']['menu_bars']['browse'])) {
            $iSurveyId = 0;
            if (isset($aData['surveyid'])) {
                            $iSurveyId = $aData['surveyid'];
            }

            if (isset($_POST['sid'])) {
                            $iSurveyId = $_POST['sid'];
            }

            $aData['display']['menu_bars']['browse'] = gT("Data entry");
            $survey = Survey::model()->findByPk($iSurveyId);
            $aData["survey"] = $survey;
            $aData['title_bar']['title'] = gT("Data entry");
        }
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }

}
