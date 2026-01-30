<?php

/*
* LimeSurvey
* Copyright (C) 2007-2026 The LimeSurvey Project Team
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
*
* 1 - Array dual scale
*5 - 5 point choice
*A - Array (5 point choice)
*B - Array (10 point choice)
*C - Array (Yes/No/Uncertain)
*D - Date
*E - Array (Increase, Same, Decrease)
*F - Array
*G - Gender
*H - Array by Column
*I - Language Switch
*K - Multiple numerical input
*L - List (Radio)
*M - Multiple choice
*N - Numerical input
*O - List With Comment
*P - Multiple choice with comments
*Q - Multiple short text
*R - Ranking
*S - Short free text
*T - Long free text
*U - Huge free text
*X - Boilerplate Question
*Y - Yes/No
*! - List (Dropdown)
*| - File Upload Question
*/

/**
* dataentry
*
* @package LimeSurvey
* @author
* @copyright 2011
* @access public
*/
class DataEntry extends SurveyCommonAction
{
    /**
     * Dataentry Constructor
     * @inherit
     */
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

        $iSurveyId = sanitize_int(Yii::app()->request->getParam('surveyid'));
        $aData['iSurveyId'] = $aData['surveyid'] = $iSurveyId;

        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/surveyAdministration/partial/topbar/surveyTopbarRight_view',
            [
                'showImportButton' => true,
                'showCloseButton' => true,
                'closeUrl' => Yii::app()->createUrl('responses/browse', ['surveyId' => $iSurveyId])
            ],
            true
        );

        if (Permission::model()->hasSurveyPermission($iSurveyId, 'responses', 'create')) {
            if (tableExists("{{responses_$iSurveyId}}")) {
                // First load the database helper
                Yii::app()->loadHelper('database'); // Really needed ?

                $subAction = Yii::app()->request->getPost('subaction');
                if ($subAction != "upload") {
                    $this->showUploadForm($this->getEncodingsArray(), $iSurveyId, $aData);
                } else {
                    $this->handleFileUpload($iSurveyId, $aData);
                }
            } else {
                Yii::app()->session['flashmessage'] = gT("This survey is not active. You must activate the survey before attempting to import a VVexport file.");
                $this->getController()->redirect($this->getController()->createUrl("/surveyAdministration/view/surveyid/{$iSurveyId}"));
            }
        } else {
            Yii::app()->session['flashmessage'] = gT("You do not have permission to access this page.");
            $this->getController()->redirect($this->getController()->createUrl("/surveyAdministration/view/surveyid/{$iSurveyId}"));
        }
    }

    /**
     * Iterate Survey Method.
     * @param int $surveyid Given Survey ID
     * @return void
     */
    public function iteratesurvey($surveyid)
    {
        $aData = array();

        $surveyid = sanitize_int($surveyid);
        $aData['surveyid'] = $surveyid;
        $aData['success'] = false;
        if (Permission::model()->hasSurveyPermission($surveyid, 'surveyactivation', 'update')) {
            if (Yii::app()->request->getParam('unfinalizeanswers') == 'true') {
                SurveyDynamic::sid($surveyid);
                Yii::app()->db->createCommand("DELETE from {{responses_$surveyid}} WHERE submitdate IS NULL AND token in (SELECT * FROM ( SELECT answ2.token from {{responses_$surveyid}} AS answ2 WHERE answ2.submitdate IS NOT NULL) tmp )")->execute();
                // Then set all remaining answers to incomplete state
                Yii::app()->db->createCommand("UPDATE {{responses_$surveyid}} SET submitdate=NULL, lastpage=NULL")->execute();
                // Finally, reset the token completed and sent status
                Yii::app()->db->createCommand("UPDATE {{tokens_$surveyid}} SET sent='N', remindersent='N', remindercount=0, completed='N', usesleft=1 where usesleft=0")->execute();
                $aData['success'] = true;
            }
            $this->renderWrappedTemplate('dataentry', 'iteratesurvey', $aData);
        }
    }

    /**
     * Handles file upload Method.
     * @param int   $iSurveyId Given Survey ID
     * @param array $aData     Given Data
     * @return void
     */
    private function handleFileUpload($iSurveyId, $aData)
    {
        $filePath = $this->moveUploadedFile($aData);

        Yii::app()->loadHelper('admin.import');
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

        $aData['aResult']['errors'] = $aResult['errors'] ?? false;
        $aData['aResult']['warnings'] = $aResult['warnings'] ?? false;

        $this->renderWrappedTemplate('dataentry', 'vvimport_result', $aData);
    }

    /**
     * Move uploaded files Method.
     *
     * @param array $aData Given Data
     * @return void|string
     */
    private function moveUploadedFile($aData)
    {
        $sFullFilePath = Yii::app()->getConfig('tempdir') . "/" . randomChars(20);
        $fileVV = CUploadedFile::getInstanceByName('csv_vv_file');
        if ($fileVV) {
            if (!$fileVV->SaveAs($sFullFilePath)) {
                $aData['class'] = 'error warningheader';
                $aData['title'] = gT("Error");
                $aData['aResult']['errors'][] = sprintf(
                    gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder."),
                    Yii::app()->getConfig('tempdir')
                );
                $aData['aUrls'][] = array(
                    'link' => $this->getController()->createUrl('admin/dataentry/sa/vvimport/surveyid/' . $aData['surveyid']),
                    'text' => $aData['aUrlText'][] = gT("Back to Response Import"),
                    );
                $this->renderWrappedTemplate('dataentry', 'vvimport_result', $aData);
            } else {
                return $sFullFilePath;
            }
        } else {
            Yii::app()->session['flashmessage'] = gT("You have to select a file.");
            $this->getController()->redirect($this->getController()->createUrl("admin/dataentry/sa/vvimport/surveyid/{$aData['surveyid']}"));
        }
    }

    /**
     * Show upload form Method.
     * @param string[] $aEncodings Given Encoding
     * @param int    $surveyid   Given Survey ID
     * @param array  $aData      Given Data
     * @return void
     */
    private function showUploadForm($aEncodings, $surveyid, $aData)
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
        $aData['tableExists'] = tableExists("{{responses_$surveyid}}");

        $aData['display']['menu_bars']['browse'] = gT("Import VV file");

        $this->renderWrappedTemplate('dataentry', 'vvimport', $aData);
    }

    /**
     * dataentry::import()
     * Function responsible to import responses from old survey table(s).
     * @param int $surveyid Given Survey ID
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

            $aData['settings']['preserveIDs'] = array(
                'type' => 'checkbox',
                'label' => gT('Preserve response IDs')
            );

            //Get the menubar
            $aData['display']['menu_bars']['browse'] = gT("Quick statistics");
            $survey = Survey::model()->findByPk($iSurveyId);

            $aData['title_bar']['title'] = gT('Browse responses') . ': ' . $survey->currentLanguageSettings->surveyls_title;
            $aData['sidemenu']['state'] = false;

            $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
                '/surveyAdministration/partial/topbar/surveyTopbarRight_view',
                [
                    'showImportButton' => true,
                    'showCloseButton' => true,
                    'closeUrl' => Yii::app()->createUrl('responses/browse', ['surveyId' => $iSurveyId])
                ],
                true
            );


            $this->renderWrappedTemplate('dataentry', 'import', $aData);
        } else {
            $aSRIDConversions = array();
            $targetSchema = SurveyDynamic::model($iSurveyId)->getTableSchema();
            $sourceTable = PluginDynamic::model($_POST['table']);
            $sourceSchema = $sourceTable->getTableSchema();
            $encryptedAttributes = Response::getEncryptedAttributes($iSurveyId);
            $tbl_name = $sourceSchema->name;

            if (!empty(App()->db->tablePrefix) && strpos((string) $sourceSchema->name, (string) App()->db->tablePrefix) === 0) {
                $tbl_name = substr((string) $sourceSchema->name, strlen((string) App()->db->tablePrefix));
            }

            $archivedTableSettings = ArchivedTableSettings::model()->findByAttributes(['tbl_name' => $tbl_name]);
            $archivedEncryptedAttributes = [];
            if ($archivedTableSettings) {
                $archivedEncryptedAttributes = json_decode((string) $archivedTableSettings->properties);
            }

            $fieldMap = [];
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
                } elseif (preg_match($pattern, (string) $name, $matches)) {
                    // Column name is SIDXGIDXQID
                    $qid = $matches[3];
                    $targetColumn = $this->getQidColumn($targetSchema, $qid);
                    if (isset($targetColumn)) {
                        $fieldMap[$name] = $targetColumn->name;
                    }
                }
            }
            $imported = 0;
            $aWarnings = [];
            $aSuccess = [];
            $responseErrors = [];
            $sourceResponses = new CDataProviderIterator(new CActiveDataProvider($sourceTable), 500);
            /* @var boolean preserveIDs */
            $preserveIDs = (bool)App()->getRequest()->getPost('preserveIDs');
            foreach ($sourceResponses as $sourceResponse) {
                $iOldID = $sourceResponse->id;
                // Using plugindynamic model because I dont trust surveydynamic.
                $targetResponse = new PluginDynamic("{{responses_$iSurveyId}}");
                if ($preserveIDs) {
                    $targetResponse->id = $sourceResponse->id;
                }

                foreach ($fieldMap as $sourceField => $targetField) {
                    $targetResponse[$targetField] = $sourceResponse[$sourceField];
                    if (in_array($sourceField, $archivedEncryptedAttributes, false) && !in_array($sourceField, $encryptedAttributes, false)) {
                        $targetResponse[$targetField] = $sourceResponse->decryptSingle($sourceResponse[$sourceField]);
                    }
                    if (!in_array($sourceField, $archivedEncryptedAttributes, false) && in_array($sourceField, $encryptedAttributes, false)) {
                        $targetResponse[$targetField] = $sourceResponse->encryptSingle($sourceResponse[$sourceField]);
                    }
                }

                if (isset($targetSchema->columns['startdate']) && empty($targetResponse['startdate'])) {
                    $targetResponse['startdate'] = date("Y-m-d H:i", (int) mktime(0, 0, 0, 1, 1, 1980));
                }

                if (isset($targetSchema->columns['datestamp']) && empty($targetResponse['datestamp'])) {
                    $targetResponse['datestamp'] = date("Y-m-d H:i", (int) mktime(0, 0, 0, 1, 1, 1980));
                }

                $oTransaction = Yii::app()->db->beginTransaction();
                try {
                    if ($preserveIDs) {
                        switchMSSQLIdentityInsert("responses_$iSurveyId", true);
                    }
                    if ($targetResponse->save()) {
                        $imported++;
                        $beforeDataEntryImport = new PluginEvent('beforeDataEntryImport');
                        $beforeDataEntryImport->set('iSurveyID', $iSurveyId);
                        $beforeDataEntryImport->set('oModel', $targetResponse);
                        App()->getPluginManager()->dispatchEvent($beforeDataEntryImport);
                        $oTransaction->commit();
                    } else {
                        $oTransaction->rollBack();
                        $responseErrors[$iOldID] = $targetResponse['id'];
                        $aWarnings[$iOldID] = CHtml::errorSummary($targetResponse, '');
                    }
                    $aSRIDConversions[$iOldID] = $targetResponse->id;
                    if ($preserveIDs) {
                        switchMSSQLIdentityInsert("responses_$iSurveyId", false);
                    }
                } catch (Exception $oException) {
                    $oTransaction->rollBack();
                    $responseErrors[] = $targetResponse['id'];
                    $aWarnings[$iOldID] = $oException->getMessage(); // Show it in view
                }
                unset($targetResponse);
            }
            if (empty($responseErrors)) {
                Yii::app()->session['flashmessage'] = sprintf(gT("%s old response(s) were successfully imported."), $imported);
            }
            $sOldTimingsTable = str_replace("responses", "timings", str_replace("timings_", "", $sourceTable->tableName()));
            $sNewTimingsTable = Yii::app()->db->tablePrefix . "timings_{$surveyid}";
            $iRecordCountT = null;
            if (isset($_POST['timings']) && $_POST['timings'] == 1 && tableExists($sOldTimingsTable) && tableExists($sNewTimingsTable)) {
                // Import timings
                $aFieldsOldTimingTable = array_values(Yii::app()->db->schema->getTable('{{' . $sOldTimingsTable . '}}')->columnNames);
                $aFieldsNewTimingTable = array_values(Yii::app()->db->schema->getTable('{{' . $sNewTimingsTable . '}}')->columnNames);

                $aValidTimingFields = array_intersect($aFieldsOldTimingTable, $aFieldsNewTimingTable);

                $sQueryOldValues = "SELECT " . implode(", ", $aValidTimingFields) . " FROM {{{$sOldTimingsTable}}} ";
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
                if (empty($responseErrors)) {
                    Yii::app()->session['flashmessage'] = sprintf(gT("%s old response(s) and according %s timings were successfully imported."), $imported, $iRecordCountT);
                }
            }
            if (empty($responseErrors)) {
                $this->getController()->redirect(["/responses/browse/", 'surveyId' => $surveyid]);
            }
            $aData = [
                'imported' => $imported,
                'responseErrors' => $responseErrors,
                'aWarnings' => $aWarnings,
                'iRecordCountT' => $iRecordCountT
            ];
            $this->renderWrappedTemplate('dataentry', 'import_result', $aData);
        }
    }


    /**
     * Takes a list of tablenames and creates a nice key value array.
     * @param array $tables Given Tables
     * @return array
     */
    protected function tableList($tables)
    {
        $list = array();
        if (empty($tables)) {
            $list['none'] = gT('No old responses found.', 'unescaped');
        }

        foreach ($tables as $table) {
            $count = PluginDynamic::model($table)->count();
            $timestamp = date_format(new DateTime((string) substr((string) $table, -14)), 'Y-m-d H:i:s');
            $list[$table] = "$timestamp ($count responses)";
        }
        return $list;
    }

    /**
     * Takes a table schema and finds the field for some question id.
     * @param CDbTableSchema $schema Given Schema
     * @param string         $qid    Given Question ID
     * @return CDbColumnSchema
     */
    protected function getQidColumn(CDbTableSchema $schema, $qid)
    {
        foreach ($schema->columns as $name => $column) {
            $pattern = '/([\d]+)X([\d]+)X([\d]+.*)/';
            $matches = array();
            if (preg_match($pattern, (string) $name, $matches)) {
                if ($matches[3] == $qid) {
                    return $column;
                }
            }
        }
    }

    /**
     * Compares 2 table schema to see if they are compatible.
     * @param CDbTableSchema $base             Given Base Database Schema
     * @param CDbTableSchema $old              Given Old Database Schema
     * @param bool           $checkColumnTypes Checks Column Types
     * @return bool
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
            if (preg_match($pattern, (string) $name, $matches)) {
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
     * @param string $subaction Given Subaction
     * @param int    $id        Given ID
     * @param int    $surveyid  Given Survey ID
     * @return void
     * TODO: This function has to be smaller. Create subfunctions for this or move it to another place!
     */
    public function editdata($subaction, $id, $surveyid)
    {
        $surveyid = (int) $surveyid;
        $oSurvey = Survey::model()->findByPk($surveyid);
        $id = (int) $id;
        $aViewUrls = array();
        if (!Permission::model()->hasSurveyPermission($surveyid, 'responses', 'update')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        if (!$oSurvey->hasResponsesTable) {
            /* Same than ResponsesController->getData */
            App()->setFlashMessage(gT("This survey has not been activated. There are no results to browse."), 'warning');
            $this->getController()->redirect(["surveyAdministration/view", 'surveyid' => $surveyid]);
            App()->end();
        }
        $idresult = Response::model($surveyid)->findByPk($id);
        if (empty($idresult)) {
            throw new CHttpException(404, gT("Invalid response ID"));
        }
        $sDataEntryLanguage = $oSurvey->language;
        $aData = [];
        $aData['display']['menu_bars']['browse'] = gT("Data entry");

        Yii::app()->loadHelper('database');

        // Perform a case insensitive natural sort on group name then question title of a multidimensional array
        // $fnames = (Field Name in Survey Table, Short Title of Question, Question Type, Field Name, Question Code, Predetermined Answer if exist)
        $fnames = [];
        $fnames['completed'] = array('fieldname' => "completed", 'question' => gT("Completed"), 'type' => 'completed');

        $fnames = array_merge($fnames, createFieldMap($oSurvey, 'full', false, false, $sDataEntryLanguage));
        // Fix private if disallowed to view token
        if (!Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'read')) {
            unset($fnames['token']);
        }
        $nfncount = count($fnames) - 1;

        //SHOW INDIVIDUAL RECORD
        $results = array();
        if ($subaction == "edit") {
            // $idresult is response
            $idresult->decrypt();
            $results[] = $idresult->attributes;
        } elseif ($subaction == "editsaved") {
            if (isset($_GET['public']) && $_GET['public'] == "true") {
                $password = hash('sha256', Yii::app()->request->getParam('accesscode', ''));
            } else {
                $password = Yii::app()->request->getParam('accesscode', '');
            }

            $svresult = SavedControl::model()->findAllByAttributes(
                array(
                'sid'         => $surveyid,
                'identifier'  => Yii::app()->request->getParam('identifier'),
                'access_code' => $password)
            );

            $saver = array();
            foreach ($svresult as $svrow) {
                $svrow->decrypt();
                $saver['email'] = $svrow['email'];
                $saver['scid'] = $svrow['scid'];
                $saver['ip'] = $svrow['ip'];
            }

            $svresult = SavedControl::model()->findAllByAttributes(array('scid' => $saver['scid']));
            $responses = [];
            foreach ($svresult as $svrow) {
                $svrow->decrypt();
                $responses[$svrow['fieldname']] = $svrow['value'];
            }

            $fieldmap = createFieldMap($oSurvey, 'full', false, false, $oSurvey->language);
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

        $questionTypes = QuestionType::modelsAttributes();
        $aDataentryoutput = '';
        /* Keep track of previous qid to not ask question model multiple time */
        $previousQid = 0;
        /* @var null\Question: the current question model */
        $oQuestion = null;
        /* @var (string|array)[] : all question attributes of this question */
        $qidattributes = [];
        $rawQuestions = Question::model()->findAll("sid = :sid", [":sid" => $surveyid]);
        $qs = [];
        $totalTime = 0;
        foreach ($rawQuestions as $rawQuestion) {
            $qs[$rawQuestion->qid] = $rawQuestion;
        }
        foreach ($results as $idrow) {
            $fname = reset($fnames);
            do {
                $question = $fname['question'];
                $questionTypeClass = isset($questionTypes[$fname['type']]) ? $questionTypes[$fname['type']]['class'] : '';
                $aDataentryoutput .= "\t<tr";
                if ($highlight) {
                    $aDataentryoutput .= " class='odd $questionTypeClass'";
                } else {
                    $aDataentryoutput .= " class='even $questionTypeClass'";
                }

                $highlight = !$highlight;
                $aDataentryoutput .= ">\n";
                // First column (Question)
                $aDataentryoutput .= "<td class=\"question-cell\">";
                $aDataentryoutput .= stripJavaScript($question);
                $aDataentryoutput .= "</td>\n";
                // Second column (Answer)
                $aDataentryoutput .= "<td class=\"answers-cell\">\n";
                //$aDataentryoutput .= "\t-={$fname[3]}=-"; //Debugging info
                if (isset($fname['qid']) && $fname['qid'] && $fname['qid'] != $previousQid) {
                    // if $fname['qid'] : we must have a question, else survey is broken : DB have error
                    $qidattributes = QuestionAttribute::model()->getQuestionAttributes($qs[$fname['qid']] ?? $fname['qid']);
                }
                /** @var array<string,string> */
                $questionInputs = [];
                /** @var array<string,bool> "Unseen" status for each field. */
                $unseenStatus = [
                    $fname['fieldname'] => !isset($idrow[$fname['fieldname']])
                ];
                $answerWrapperClass = '';
                $fieldType = $fname['type'];
                switch ($fieldType) {
                    case "completed":
                        $selected = (empty($idrow['submitdate'])) ? 'N' : 'Y';
                        $select_options = array(
                            'N' => gT('No', 'unescaped'),
                            'Y' => gT('Yes', 'unescaped')
                        );

                        $aDataentryoutput .= CHtml::dropDownList('completed', $selected, $select_options, array('class' => 'form-select'));

                        break;
                    case Question::QT_X_TEXT_DISPLAY: //Boilerplate question
                        // We add an empty entry here so the "Unseen" checkbox is displayed.
                        // Although a value can't be entered, there is still a difference between
                        // Text Display questions shown and not shown.
                        $questionInputs[$fname['fieldname']] = "";
                        break;
                    case Question::QT_Q_MULTIPLE_SHORT_TEXT:
                        $questionInput = $fname['subquestion'] . '&nbsp;';
                        $questionInput .= CHtml::textField($fname['fieldname'], $idrow[$fname['fieldname']]);
                        $questionInputs[$fname['fieldname']] = $questionInput;
                        break;
                    case Question::QT_K_MULTIPLE_NUMERICAL:
                        $questionInput = $fname['subquestion'] . '&nbsp;';
                        /* Fix DB DECIMAL type */
                        $value = $idrow[$fname['fieldname']];
                        if (strpos((string) $value, ".")) {
                            $value = rtrim(rtrim((string) $value, "0"), ".");
                        }
                        $questionInput .= CHtml::textField($fname['fieldname'], $value, array('pattern' => "[-]?([0-9]{0,20}([\.][0-9]{0,10})?)?",'title' => gT("Only numbers may be entered in this field.")));
                        $questionInputs[$fname['fieldname']] = $questionInput;
                        break;
                    case "id":
                        $aDataentryoutput .= CHtml::tag('span', array('style' => 'font-weight: bold;'), '&nbsp;' . $idrow[$fname['fieldname']]);
                        break;
                    case "seed":
                        $aDataentryoutput .= CHtml::tag('span', array(), '&nbsp;' . $idrow[$fname['fieldname']]);
                        break;
                    case Question::QT_5_POINT_CHOICE: //5 POINT CHOICE radio-buttons
                        $questionInput = '';
                        for ($i = 1; $i <= 5; $i++) {
                            $checked = false;
                            if ($idrow[$fname['fieldname']] == $i) {
                                $checked = true;
                            }
                            $questionInput .= '<span class="five-point">';
                            $questionInput .= CHtml::radioButton($fname['fieldname'], $checked, array('class' => '', 'value' => $i, 'id' => '5-point-choice-' . $i));
                            $questionInput .= '<label for="5-point-choice-' . $i . '">' . $i . '</label>';
                            $questionInput .= '</span> ';
                        }
                        //Add 'No Answer'
                        $questionInput .= "<input type='radio' class='' name='{$fname['fieldname']}' value=''";
                        if ($idrow[$fname['fieldname']] == '') {
                            $questionInput .= " checked";
                        }
                        $questionInput .= " />" . gT("No answer") . "&nbsp;\n";
                        $questionInputs[$fname['fieldname']] = $questionInput;
                        break;
                    case Question::QT_D_DATE: //DATE
                        $dateformatdetails = getDateFormatDataForQID($qidattributes, $surveyid);
                        $datetimeobj = null;
                        $thisdate = '';
                        if ($idrow[$fname['fieldname']] != '') {
                            $datetimeobj = DateTime::createFromFormat("Y-m-d H:i:s", $idrow[$fname['fieldname']]);
                            if ($datetimeobj == null) { //MSSQL uses microseconds by default in any datetime object
                                $datetimeobj = DateTime::createFromFormat("Y-m-d H:i:s.u", $idrow[$fname['fieldname']]);
                            }
                        }

                        if (canShowDatePicker($dateformatdetails)) {
                            if ($datetimeobj) {
                                $thisdate = $datetimeobj->format($dateformatdetails['phpdate']);
                            }
                            $goodchars = str_replace(array("m", "d", "y", "H", "M"), "", (string) $dateformatdetails['dateformat']);
                            $goodchars = "0123456789" . $goodchars[0];
                            $questionInput = CHtml::textField(
                                $fname['fieldname'],
                                $thisdate,
                                array(
                                'class' => 'popupdate',
                                'size' => '12',
                                'onkeypress' => 'return window.LS.goodchars(event,\'' . $goodchars . '\')'
                                )
                            );
                            $questionInput .= CHtml::hiddenField(
                                'dateformat' . $fname['fieldname'],
                                $dateformatdetails['jsdate'],
                                array('id' => "dateformat{$fname['fieldname']}")
                            );
                            $questionInputs[$fname['fieldname']] = $questionInput;
                        } else {
                            if ($datetimeobj) {
                                $thisdate = $datetimeobj->format("Y-m-d\TH:i");
                            }
                            $questionInputs[$fname['fieldname']] = CHtml::dateTimeLocalField($fname['fieldname'], $thisdate);
                        }
                        break;
                    case Question::QT_G_GENDER: //GENDER drop-down list
                        $select_options = array(
                        '' => gT("Please choose") . '...',
                        'F' => gT("Female"),
                        'M' => gT("Male")
                        );
                        $questionInputs[$fname['fieldname']] = CHtml::listBox($fname['fieldname'], $idrow[$fname['fieldname']], $select_options);
                        break;
                    case Question::QT_L_LIST: //LIST drop-down
                    case Question::QT_EXCLAMATION_LIST_DROPDOWN: //List (Radio)
                        if (isset($qidattributes['category_separator']) && trim((string) $qidattributes['category_separator']) != '') {
                            $optCategorySeparator = $qidattributes['category_separator'];
                        } else {
                            unset($optCategorySeparator);
                        }

                        if (substr((string) $fname['fieldname'], -5) == "other") {
                            $questionInputs[$fname['fieldname']] = "\t<input type='text' name='{$fname['fieldname']}' value='"
                            . htmlspecialchars((string) $idrow[$fname['fieldname']], ENT_QUOTES) . "' />\n";
                        } else {
                            $lresult = Answer::model()->with('answerl10ns')->findAll(array('condition' => 'qid =:qid AND language = :language', 'params' => array('qid' => $fname['qid'], 'language' => $sDataEntryLanguage)));
                            $questionInput = "\t<select name='{$fname['fieldname']}' class='form-select'>\n"
                            . "<option value=''";
                            if ($idrow[$fname['fieldname']] == "") {
                                $questionInput .= " selected='selected'";
                            }
                            $questionInput .= ">" . gT("Please choose") . "..</option>\n";

                            if (!isset($optCategorySeparator)) {
                                foreach ($lresult as $llrow) {
                                    $questionInput .= "<option value='{$llrow['code']}'";
                                    if ($idrow[$fname['fieldname']] == $llrow['code']) {
                                        $questionInput .= " selected='selected'";
                                    }
                                    $questionInput .= ">{$llrow->answerl10ns[$sDataEntryLanguage]->answer}</option>\n";
                                }
                            } else {
                                $defaultopts = array();
                                $optgroups = array();
                                foreach ($lresult as $llrow) {
                                    list ($categorytext, $answertext) = explode($optCategorySeparator, (string) $llrow->answerl10ns[$sDataEntryLanguage]->answer);
                                    if ($categorytext == '') {
                                        $defaultopts[] = array('code' => $llrow['code'], 'answer' => $answertext);
                                    } else {
                                        $optgroups[$categorytext][] = array('code' => $llrow['code'], 'answer' => $answertext);
                                    }
                                }

                                foreach ($optgroups as $categoryname => $optionlistarray) {
                                    $questionInput .= "<optgroup class=\"dropdowncategory\" label=\"" . $categoryname . "\">\n";
                                    foreach ($optionlistarray as $optionarray) {
                                        $questionInput .= "\t<option value='{$optionarray['code']}'";
                                        if ($idrow[$fname['fieldname']] == $optionarray['code']) {
                                            $questionInput .= " selected='selected'";
                                        }
                                        $questionInput .= ">{$optionarray['answer']}</option>\n";
                                    }
                                    $questionInput .= "</optgroup>\n";
                                }
                                foreach ($defaultopts as $optionarray) {
                                    $questionInput .= "<option value='{$optionarray['code']}'";
                                    if ($idrow[$fname['fieldname']] == $optionarray['code']) {
                                        $questionInput .= " selected='selected'";
                                    }
                                    $questionInput .= ">{$optionarray['answer']}</option>\n";
                                }
                            }
                            if (($oQuestion->other ?? "N") == "Y") {
                                $questionInput .= "<option value='-oth-'";
                                if ($idrow[$fname['fieldname']] == "-oth-") {
                                    $questionInput .= " selected='selected'";
                                }
                                $questionInput .= ">" . gT("Other") . "</option>\n";
                            }
                            $questionInput .= "\t</select>\n";
                            $questionInputs[$fname['fieldname']] = $questionInput;
                        }
                        break;
                    case Question::QT_O_LIST_WITH_COMMENT: //LIST WITH COMMENT drop-down/radio-button list + textarea
                        $lresult = Answer::model()->findAll("qid={$fname['qid']}");
                        $questionInput = "\t<select name='{$fname['fieldname']}' class='form-select'>\n"
                        . "<option value=''";
                        if ($idrow[$fname['fieldname']] == "") {
                            $questionInput .= " selected='selected'";
                        }
                        $questionInput .= ">" . gT("Please choose") . "..</option>\n";

                        foreach ($lresult as $llrow) {
                            $questionInput .= "<option value='{$llrow['code']}'";
                            if ($idrow[$fname['fieldname']] == $llrow['code']) {
                                $questionInput .= " selected='selected'";
                            }
                            $questionInput .= ">{$llrow->answerl10ns[$sDataEntryLanguage]->answer}</option>\n";
                        }
                        $baseFieldName = $fname['fieldname'];
                        $fname = next($fnames);
                        $questionInput .= "\t</select>\n"
                        . "\t<br />\n"
                        . CHtml::textArea($fname['fieldname'], $idrow[$fname['fieldname']], array('cols' => 45,'rows' => 5));
                        $questionInputs[$baseFieldName] = $questionInput;
                        break;
                    case Question::QT_R_RANKING: // Ranking TYPE QUESTION
                        $thisqid = $fname['qid'];
                        $currentvalues = array();
                        $rawvalues = [];
                        $myfname = 'Q' . $fname['qid'];
                        $questionInput = '<div id="question' . $thisqid . '" class="ranking-answers"><ul class="answers-list select-list">';
                        $unseen = true;
                        while (isset($fname['type']) && $fname['type'] == "R" && $fname['qid'] == $thisqid) {
                            //Let's get all the existing values into an array
                            if ($idrow[$fname['fieldname']]) {
                                $currentvalues[] = $idrow[$fname['fieldname']];
                            }
                            // If any ranking field is not null, we mark the question as seen.
                            if (isset($idrow[$fname['fieldname']])) {
                                $unseen = false;
                            }
                            $rawvalues[] = $idrow[$fname['fieldname']];
                            $fname = next($fnames);
                        }
                        $ansresult = Answer::model()->with('answerl10ns')->findAll(array('condition' => 'qid =:qid AND language = :language', 'params' => array('qid' => $thisqid, 'language' => $sDataEntryLanguage)));
                        $anscount = count($ansresult);
                        $answers = array();
                        foreach ($ansresult as $ansrow) {
                            $answers[] = $ansrow;
                        }
                        for ($i = 1; $i <= $anscount; $i++) {
                            $questionInput .= "\n<li class=\"select-item\">";
                            $questionInput .= "<label for=\"answer{$myfname}_R{$answers[$i - 1]->aid}\">";
                            if ($i == 1) {
                                $questionInput .= gT('First choice');
                            } else {
                                $questionInput .= gT('Next choice');
                            }

                            $questionInput .= "</label>";
                            $questionInput .= "<select name=\"{$myfname}_R{$answers[$i - 1]->aid}\" id=\"answer{$myfname}_R{{$answers[$i - 1]->aid}\" class='form-select'>\n";
                            (!isset($currentvalues[$i - 1])) ? $selected = " selected=\"selected\"" : $selected = "";
                            $questionInput .= "\t<option value=\"\" $selected>" . gT('None') . "</option>\n";
                            foreach ($ansresult as $ansrow) {
                                (isset($currentvalues[$i - 1]) && $currentvalues[$i - 1] == $ansrow['code']) ? $selected = " selected=\"selected\"" : $selected = "";
                                $questionInput .= "\t<option value=\"" . $ansrow['code'] . "\" $selected>" . flattenText($ansrow->answerl10ns[$sDataEntryLanguage]->answer) . "</option>\n";
                            }
                            $questionInput .= "</select\n";
                            $questionInput .= "</li>";
                        }
                        $questionInput .= '</ul>';
                        $questionInput .= "<div style='display:none' id='ranking-{$thisqid}-maxans'>{$anscount}</div>"
                            . "<div style='display:none' id='ranking-{$thisqid}-minans'>0</div>"
                            . "<div style='display:none' id='ranking-{$thisqid}-name'>javatbd{$myfname}</div>";
                        $questionInput .= "<div style=\"display:none\">";
                        foreach ($ansresult as $ansrow) {
                            $questionInput .= "<div id=\"htmlblock-{$thisqid}-{$ansrow['code']}\">{$ansrow->answerl10ns[$sDataEntryLanguage]->answer}</div>";
                        }
                        $questionInput .= "</div>";
                        $questionInput .= '</div>';
                        App()->getClientScript()->registerPackage('jquery-actual');
                        App()->getClientScript()->registerScriptFile(App()->getConfig('generalscripts') . 'ranking.js');
                        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . 'ranking.css');
                        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . 'jquery-ui-custom.css');

                        $questionInput .= "<script type='text/javascript'>\n"
                            .  "  <!--\n"
                            . "var aRankingTranslations = {
                                     choicetitle: '" . gT("Your choices", 'js') . "',
                                     ranktitle: '" . gT("Your ranking", 'js') . "'
                                    };\n"
                            . "function checkconditions(){};"
                            . "$(function() {"
                            . " doDragDropRank({$thisqid},0,true,true);\n"
                            . "});\n"
                            . " -->\n"
                            . "</script>\n";
                        $questionInputs[$myfname] = $questionInput;
                        $unseenStatus = [$myfname => $unseen];

                        unset($answers);
                        $fname = prev($fnames);
                        break;

                    case Question::QT_M_MULTIPLE_CHOICE: //Multiple choice checkbox
                        $thisqid = $fname['qid'];
                        while ($fname['qid'] == $thisqid) {
                            if (substr((string) $fname['fieldname'], -5) == "other") {
                                $questionInput = "\t<input type='text' name='{$fname['fieldname']}' value='"
                                . htmlspecialchars((string) $idrow[$fname['fieldname']], ENT_QUOTES) . "' />\n";
                            } else {
                                $questionInput = "<div class='checkbox'>\t<input type='checkbox' class='checkboxbtn' name='{$fname['fieldname']}' id='{$fname['fieldname']}' value='Y'";
                                if ($idrow[$fname['fieldname']] == "Y") {
                                    $questionInput .= " checked";
                                }
                                $questionInput .= " /><label for='{$fname['fieldname']}'>{$fname['subquestion']}</label></div>\n";
                            }
                            $questionInputs[$fname['fieldname']] = $questionInput;
                            $unseenStatus[$fname['fieldname']] = is_null($idrow[$fname['fieldname']]);
                            $fname = next($fnames);
                        }
                        $fname = prev($fnames);

                        break;

                    case Question::QT_I_LANGUAGE: //Language Switch
                        $slangs = $oSurvey->allLanguages;
                        $baselang = $oSurvey->language;

                        $questionInput = "<select name='{$fname['fieldname']}' class='form-select'>\n";
                        $questionInput .= "<option value=''";
                        if ($idrow[$fname['fieldname']] == "") {
                            $questionInput .= " selected='selected'";
                        }
                        $questionInput .= ">" . gT("Please choose") . "..</option>\n";

                        foreach ($slangs as $lang) {
                            $questionInput .= "<option value='{$lang}'";
                            if ($lang == $idrow[$fname['fieldname']]) {
                                $questionInput .= " selected='selected'";
                            }
                            $questionInput .= ">" . getLanguageNameFromCode($lang, false) . "</option>\n";
                        }
                        $questionInput .= "</select>";
                        $questionInputs[$fname['fieldname']] = $questionInput;
                        break;

                    case Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS: //Multiple choice with comments checkbox + text
                        $questionInput = '';
                        while (isset($fname) && $fname['type'] == Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS) {
                            $thefieldname = $fname['fieldname'];
                            $subquestionValue = $idrow[$fname['fieldname']];
                            if (substr((string) $fname['fieldname'], -5) == "other") {
                                $questionInput = CHtml::textField($fname['fieldname'], $idrow[$fname['fieldname']], array('size' => 30));
                                $fname = next($fnames);
                                $questionInput .= CHtml::textField($fname['fieldname'], $idrow[$fname['fieldname']], array('size' => 50));
                            } else {
                                $questionInput = "<div class='checkbox'><input type='checkbox' class='checkboxbtn' name=\"{$fname['fieldname']}\" id=\"{$fname['fieldname']}\" value='Y'";
                                if ($idrow[$fname['fieldname']] == "Y") {
                                    $questionInput .= " checked";
                                }
                                $questionInput .= " /><label for=\"{$fname['fieldname']}\">{$fname['subquestion']}</label></div>\n";
                                $fname = next($fnames);
                                $questionInput .= CHtml::textField($fname['fieldname'], $idrow[$fname['fieldname']], array('size' => 50));
                            }
                            $questionInputs[$thefieldname] = $questionInput;
                            $unseenStatus[$thefieldname] = is_null($subquestionValue);
                            $fname = next($fnames);
                        }
                        $fname = prev($fnames);
                        break;
                    case Question::QT_VERTICAL_FILE_UPLOAD: //FILE UPLOAD
                        $questionInput = "<table class='table'>\n";
                        if ($fname['aid'] !== 'filecount' && isset($idrow[$fname['fieldname'] . '_Cfilecount']) && ($idrow[$fname['fieldname'] . '_Cfilecount'] > 0)) {
                            //file metadata
                            $metadata = json_decode((string) $idrow[$fname['fieldname']], true);
                            for ($i = 0; ($i < $qidattributes['max_num_of_files']) && isset($metadata[$i]); $i++) {
                                if ($qidattributes['show_title']) {
                                    $questionInput .= '<tr><td>' . gT("Title") . '</td><td><input type="text" class="' . $fname['fieldname'] . '" id="' . $fname['fieldname'] . '_title_' . $i . '" name="title"    size=50 value="' . htmlspecialchars((string) $metadata[$i]["title"]) . '" /></td></tr>';
                                }
                                if ($qidattributes['show_comment']) {
                                    $questionInput .= '<tr><td >' . gT("Comment") . '</td><td><input type="text" class="' . $fname['fieldname'] . '" id="' . $fname['fieldname'] . '_comment_' . $i . '" name="comment"  size=50 value="' . htmlspecialchars((string) $metadata[$i]["comment"]) . '" /></td></tr>';
                                }

                                $questionInput .= '<tr><td>' . gT("File name") . '</td><td><input   class="' . $fname['fieldname'] . '" id="' . $fname['fieldname'] . '_name_' . $i . '" name="name" size=50 value="' . htmlspecialchars(rawurldecode((string) $metadata[$i]["name"])) . '" /></td></tr>'
                                . '<tr><td></td><td><input type="hidden" class="' . $fname['fieldname'] . '" id="' . $fname['fieldname'] . '_size_' . $i . '" name="size" size=50 value="' . htmlspecialchars((string) $metadata[$i]["size"]) . '" /></td></tr>'
                                . '<tr><td></td><td><input type="hidden" class="' . $fname['fieldname'] . '" id="' . $fname['fieldname'] . '_ext_' . $i . '" name="ext" size=50 value="' . htmlspecialchars((string) $metadata[$i]["ext"]) . '" /></td></tr>'
                                . '<tr><td></td><td><input type="hidden"  class="' . $fname['fieldname'] . '" id="' . $fname['fieldname'] . '_filename_' . $i . '" name="filename" size=50 value="' . htmlspecialchars(rawurldecode((string) $metadata[$i]["filename"])) . '" /></td></tr>';
                            }
                            $questionInput .= '<tr><td></td><td><input type="hidden" id="' . $fname['fieldname'] . '" name="' . $fname['fieldname'] . '" size=50 value="' . htmlspecialchars((string) $idrow[$fname['fieldname']]) . '" /></td></tr>';
                        }
                        $baseFieldName = $fname['fieldname'];
                        $fname = next($fnames);
                        $questionInput .= '<tr><td>' . gT("File count") . '</td><td><input readonly id="' . $fname['fieldname'] . '" name="' . $fname['fieldname'] . '" value ="' . htmlspecialchars((string) $idrow[$fname['fieldname']]) . '" /></td></tr>';
                        $questionInput .= '</table>';
                        $questionInput .= '<script type="text/javascript">
                            $(function() {
                                $(".' . $baseFieldName . '").keyup(function() {
                                    var filecount = $("#' . $baseFieldName . '_Cfilecount").val();
                                    var jsonstr = "[";
                                    var i;
                                    for (i = 0; i < filecount; i++)
                                    {
                                        if (i != 0)
                                            jsonstr += ",";
                                        jsonstr += \'{"title":"\'+$("#' . $baseFieldName . '_title_"+i).val()+\'",\';
                                        jsonstr += \'"comment":"\'+$("#' . $baseFieldName . '_comment_"+i).val()+\'",\';
                                        jsonstr += \'"size":"\'+$("#' . $baseFieldName . '_size_"+i).val()+\'",\';
                                        jsonstr += \'"ext":"\'+$("#' . $baseFieldName . '_ext_"+i).val()+\'",\';
                                        jsonstr += \'"filename":"\'+$("#' . $baseFieldName . '_filename_"+i).val()+\'",\';
                                        jsonstr += \'"name":"\'+encodeURIComponent($("#' . $baseFieldName . '_name_"+i).val())+\'"}\';
                                    }
                                    jsonstr += "]";
                                    $("#' . $baseFieldName . '").val(jsonstr);
                                });
                            });
                            </script>';
                        $questionInputs[$baseFieldName] = $questionInput;
                        break;
                    case Question::QT_N_NUMERICAL: //NUMERICAL TEXT
                        /* Fix DB DECIMAL type */
                        $value = $idrow[$fname['fieldname']];
                        if (strpos((string) $value, ".")) {
                            $value = rtrim(rtrim((string) $value, "0"), ".");
                        }
                        /* no number fix with return window.LS.goodchars … */
                        $questionInputs[$fname['fieldname']] = CHtml::textField($fname['fieldname'], $value, array('pattern' => "[-]?([0-9]{0,20}([\.][0-9]{0,10})?)?",'title' => gT("Only numbers may be entered in this field.")));
                        break;
                    case Question::QT_S_SHORT_FREE_TEXT: //Short free text
                        $questionInputs[$fname['fieldname']] = CHtml::textField($fname['fieldname'], $idrow[$fname['fieldname']]);
                        break;
                    case Question::QT_T_LONG_FREE_TEXT: //LONG FREE TEXT
                        $questionInputs[$fname['fieldname']] = CHtml::textArea($fname['fieldname'], $idrow[$fname['fieldname']], array('cols' => 45,'rows' => 5));
                        break;
                    case Question::QT_U_HUGE_FREE_TEXT: //Huge free text
                        $questionInputs[$fname['fieldname']] = CHtml::textArea($fname['fieldname'], $idrow[$fname['fieldname']], array('cols' => 70,'rows' => 50));
                        break;
                    case Question::QT_Y_YES_NO_RADIO: //YES/NO radio-buttons
                        $questionInput = "\t<select name='{$fname['fieldname']}' class='form-select'>\n"
                        . "<option value=''";
                        if ($idrow[$fname['fieldname']] == "") {
                            $questionInput .= " selected='selected'";
                        }
                        $questionInput .= ">" . gT("Please choose") . "..</option>\n"
                        . "<option value='Y'";
                        if ($idrow[$fname['fieldname']] == "Y") {
                            $questionInput .= " selected='selected'";
                        }
                        $questionInput .= ">" . gT("Yes") . "</option>\n"
                        . "<option value='N'";
                        if ($idrow[$fname['fieldname']] == "N") {
                            $questionInput .= " selected='selected'";
                        }
                        $questionInput .= ">" . gT("No") . "</option>\n"
                        . "\t</select>\n";
                        $questionInputs[$fname['fieldname']] = $questionInput;
                        break;
                    case Question::QT_A_ARRAY_5_POINT: // Array (5 point choice) radio-buttons
                        $thisqid = $fname['qid'];
                        while ($fname['qid'] == $thisqid) {
                            $questionInput = "<span>" . $fname['subquestion'] . "</span>";
                            $questionInput .= " <span>";
                            for ($j = 1; $j <= 5; $j++) {
                                $questionInput .= '<span class="five-point">';
                                $questionInput .= "\t<input type='radio' class='' name='{$fname['fieldname']}' id='5-point-radio-{$fname['fieldname']}' value='$j'";
                                if ($idrow[$fname['fieldname']] == $j) {
                                    $questionInput .= " checked";
                                }
                                $questionInput .= " /><label for='5-point-radio-{$fname['fieldname']}'>$j</label>&nbsp;\n";
                                $questionInput .= '</span>';
                            }
                            //Add 'No Answer'
                            $questionInput .= '<span class="five-point">';
                            $questionInput .= "<input type='radio' class='' name='{$fname['fieldname']}' value=''";
                            if ($idrow[$fname['fieldname']] == '') {
                                $questionInput .= " checked";
                            }
                            $questionInput .= " />" . gT("No answer") . "&nbsp;\n";
                            $questionInput .= '</span>';
                            $questionInput .= "</span>";
                            $questionInputs[$fname['fieldname']] = $questionInput;
                            $unseenStatus[$fname['fieldname']] = is_null($idrow[$fname['fieldname']]);
                            $fname = next($fnames);
                        }
                        $fname = prev($fnames);
                        break;
                    case Question::QT_B_ARRAY_10_CHOICE_QUESTIONS: // Array (10 point choice) radio-buttons
                        $thisqid = $fname['qid'];
                        while ($fname['qid'] == $thisqid) {
                            $questionInput = "<span>" . $fname['subquestion'] . "</span>";
                            $questionInput .= " <span>";
                            for ($j = 1; $j <= 10; $j++) {
                                $questionInput .= '<span class="ten-point">';
                                $questionInput .= "\t<input type='radio' class='' name='{$fname['fieldname']}' id='ten-point-{$fname['fieldname']}-$j' value='$j'";
                                if ($idrow[$fname['fieldname']] == $j) {
                                    $questionInput .= " checked";
                                }
                                $questionInput .= " /><label for='ten-point-{$fname['fieldname']}-$j'>$j</label>&nbsp;\n";
                                $questionInput .= '</span>';
                            }
                            //Add 'No Answer'
                            $questionInput .= '<span class="five-point">';
                            $questionInput .= "<input type='radio' class='' name='{$fname['fieldname']}' value=''";
                            if ($idrow[$fname['fieldname']] == '') {
                                $questionInput .= " checked";
                            }
                            $questionInput .= " />" . gT("No answer") . "&nbsp;\n";
                            $questionInput .= '</span>';
                            $questionInput .= "</span>";
                            $questionInputs[$fname['fieldname']] = $questionInput;
                            $unseenStatus[$fname['fieldname']] = is_null($idrow[$fname['fieldname']]);
                            $fname = next($fnames);
                        }
                        $fname = prev($fnames);
                        break;
                    case Question::QT_C_ARRAY_YES_UNCERTAIN_NO: // Array (Yes/Uncertain/No)
                        $thisqid = $fname['qid'];
                        while (isset($fname['qid']) && $fname['qid'] == $thisqid) {
                            $questionInput = "<span>" . $fname['subquestion'] . "</span>";
                            $questionInput .= " <span>";
                            $options = [
                                'Y' => gT("Yes"),
                                'U' => gT("Uncertain"),
                                'N' => gT("No"),
                            ];
                            foreach ($options as $optionValue => $optionLabel) {
                                $questionInput .= "<input type='radio' class='' name='{$fname['fieldname']}' value='{$optionValue}'";
                                if ($idrow[$fname['fieldname']] == $optionValue) {
                                    $questionInput .= " checked";
                                }
                                $questionInput .= " />" . $optionLabel . "&nbsp;";
                            }
                            //Add 'No Answer'
                            $questionInput .= "<input type='radio' class='' name='{$fname['fieldname']}' value=''";
                            if ($idrow[$fname['fieldname']] == '') {
                                $questionInput .= " checked";
                            }
                            $questionInput .= " />" . gT("No answer") . "&nbsp;\n";
                            $questionInput .= "</span>";
                            $questionInputs[$fname['fieldname']] = $questionInput;
                            $unseenStatus[$fname['fieldname']] = is_null($idrow[$fname['fieldname']]);
                            $fname = next($fnames);
                        }
                        $fname = prev($fnames);
                        break;
                    case Question::QT_E_ARRAY_INC_SAME_DEC: // Array (Increase/Same/Decrease) radio-buttons
                        $thisqid = $fname['qid'];
                        while ($fname['qid'] == $thisqid) {
                            $questionInput = "<span>" . $fname['subquestion'] . "</span>";
                            $questionInput .= " <span>";
                            $options = [
                                'I' => gT("Increase"),
                                'S' => gT("Same"),
                                'D' => gT("Decrease"),
                            ];
                            foreach ($options as $optionValue => $optionLabel) {
                                $questionInput .= "<input type='radio' class='' name='{$fname['fieldname']}' value='{$optionValue}'";
                                if ($idrow[$fname['fieldname']] == $optionValue) {
                                    $questionInput .= " checked";
                                }
                                $questionInput .= " />" . $optionLabel . "&nbsp;";
                            }
                            //Add 'No Answer'
                            $questionInput .= "<input type='radio' class='' name='{$fname['fieldname']}' value=''";
                            if ($idrow[$fname['fieldname']] == '') {
                                $questionInput .= " checked";
                            }
                            $questionInput .= " />" . gT("No answer") . "&nbsp;\n";
                            $questionInput .= "</span>";
                            $questionInputs[$fname['fieldname']] = $questionInput;
                            $unseenStatus[$fname['fieldname']] = is_null($idrow[$fname['fieldname']]);
                            $fname = next($fnames);
                        }
                        $fname = prev($fnames);
                        break;
                    case Question::QT_F_ARRAY: // Array
                    case Question::QT_H_ARRAY_COLUMN:
                    case Question::QT_1_ARRAY_DUAL:
                        $thisqid = $fname['qid'];
                        while (isset($fname['qid']) && $fname['qid'] == $thisqid) {
                            $questionInput = "<span>" . $fname['subquestion'];
                            if (isset($fname['scale'])) {
                                $questionInput .= " (" . $fname['scale'] . ')';
                            }
                            $questionInput .= "</span>\n";
                            $scale_id = 0;
                            if (isset($fname['scale_id'])) {
                                $scale_id = $fname['scale_id'];
                            }
                            $fresult = Answer::model()->findAll("qid='{$fname['qid']}' and scale_id={$scale_id}");
                            $questionInput .= "<span>";
                            foreach ($fresult as $frow) {
                                $questionInput .= "\t<input type='radio' class='' name='{$fname['fieldname']}' value='{$frow['code']}'";
                                if ($idrow[$fname['fieldname']] == $frow['code']) {
                                    $questionInput .= " checked";
                                }
                                $questionInput .= " />" . $frow->answerl10ns[$sDataEntryLanguage]->answer . "&nbsp;\n";
                            }
                            //Add 'No Answer'
                            $questionInput .= "\t<input type='radio' class='' name='{$fname['fieldname']}' value=''";
                            if ($idrow[$fname['fieldname']] == '') {
                                $questionInput .= " checked";
                            }
                            $questionInput .= " />" . gT("No answer") . "&nbsp;\n";
                            $questionInput .= "</span>";
                            $questionInputs[$fname['fieldname']] = $questionInput;
                            $unseenStatus[$fname['fieldname']] = is_null($idrow[$fname['fieldname']]);
                            $fname = next($fnames);
                        }
                        $fname = prev($fnames);
                        break;
                    case Question::QT_COLON_ARRAY_NUMBERS: // Array (Numbers)
                        $minvalue = 1;
                        $maxvalue = 10;
                        if (trim((string) $qidattributes['multiflexible_max']) != '' && trim((string) $qidattributes['multiflexible_min']) == '') {
                            $maxvalue = $qidattributes['multiflexible_max'];
                            $minvalue = 1;
                        }
                        if (trim((string) $qidattributes['multiflexible_min']) != '' && trim((string) $qidattributes['multiflexible_max']) == '') {
                            $minvalue = $qidattributes['multiflexible_min'];
                            $maxvalue = $qidattributes['multiflexible_min'] + 10;
                        }
                        if (trim((string) $qidattributes['multiflexible_min']) != '' && trim((string) $qidattributes['multiflexible_max']) != '') {
                            if ($qidattributes['multiflexible_min'] < $qidattributes['multiflexible_max']) {
                                $minvalue = $qidattributes['multiflexible_min'];
                                $maxvalue = $qidattributes['multiflexible_max'];
                            }
                        }

                        $stepvalue = (trim((string) $qidattributes['multiflexible_step']) != '' && $qidattributes['multiflexible_step'] > 0) ? $qidattributes['multiflexible_step'] : 1;

                        if ($qidattributes['reverse'] == 1) {
                            $tmp = $minvalue;
                            $minvalue = $maxvalue;
                            $maxvalue = $tmp;
                            $reverse = true;
                            $stepvalue = -$stepvalue;
                        } else {
                            $reverse = false;
                        }

                        if ($qidattributes['multiflexible_checkbox'] != 0) {
                            $minvalue = 0;
                            $maxvalue = 1;
                            $stepvalue = 1;
                        }
                        $thisqid = $fname['qid'];
                        while (isset($fname['qid']) && $fname['qid'] == $thisqid) {
                            $questionInput = "<span>{$fname['subquestion1']}:{$fname['subquestion2']}</span>";
                            $questionInput .= "<span>";
                            if ($qidattributes['input_boxes'] != 0) {
                                $questionInput .= CHtml::numberField($fname['fieldname'], $idrow[$fname['fieldname']], array('step' => 'any'));
                            } else {
                                $questionInput .= "\t<select name='{$fname['fieldname']}' class='form-select'>\n";
                                $questionInput .= "<option value=''";
                                if ($idrow[$fname['fieldname']] === "") {
                                    $questionInput .= " selected";
                                }
                                $questionInput .= ">...</option>\n";
                                for ($ii = $minvalue; $ii <= $maxvalue; $ii += $stepvalue) {
                                    $questionInput .= "<option value='$ii'";
                                    if ($idrow[$fname['fieldname']] === "$ii") {
                                        $questionInput .= " selected";
                                    }
                                    $questionInput .= ">$ii</option>\n";
                                }
                                $questionInput .= "</select>";
                            }
                            $questionInput .= "</span>\n";
                            $questionInputs[$fname['fieldname']] = $questionInput;
                            $unseenStatus[$fname['fieldname']] = is_null($idrow[$fname['fieldname']]);
                            $fname = next($fnames);
                        }
                        $fname = prev($fnames);
                        break;
                    case Question::QT_SEMICOLON_ARRAY_TEXT: // Array
                        $thisqid = $fname['qid'];
                        while (isset($fname['qid']) && $fname['qid'] == $thisqid) {
                            $questionInput = "<span>{$fname['subquestion1']}:{$fname['subquestion2']}</span>";
                            $questionInput .= "<span>";
                            $questionInput .= CHtml::textField($fname['fieldname'], $idrow[$fname['fieldname']]);
                            $questionInput .= "</span>\n";
                            $questionInputs[$fname['fieldname']] = $questionInput;
                            $unseenStatus[$fname['fieldname']] = is_null($idrow[$fname['fieldname']]);
                            $fname = next($fnames);
                        }
                        $fname = prev($fnames);
                        break;
                    case "token":
                        if (Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'update')) {
                            $aDataentryoutput .= CHtml::textField($fname['fieldname'], $idrow[$fname['fieldname']]);
                        } else {
                            $aDataentryoutput .= CHtml::textField($fname['fieldname'], $idrow[$fname['fieldname']], array('disabled' => 'disabled'));
                        }
                        break;
                    case "submitdate":
                    case "startdate":
                    case "datestamp":
                        $thisdate = "";
                        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);
                        if ($idrow[$fname['fieldname']] != '') {
                            $datetimeobj = DateTime::createFromFormat("Y-m-d H:i:s", $idrow[$fname['fieldname']]);
                            if ($datetimeobj == null) { //MSSQL uses microseconds by default in any datetime object
                                $datetimeobj = DateTime::createFromFormat("Y-m-d H:i:s.u", $idrow[$fname['fieldname']]);
                            }
                            if ($datetimeobj) {
                                $thisdate = $datetimeobj->format($dateformatdetails['phpdate'] . " H:i");
                            }
                        }
                        $aDataentryoutput .= App()->getController()->widget(
                            'ext.DateTimePickerWidget.DateTimePicker',
                            array(
                                'name' => $fname['fieldname'],
                                'id' => $fname['fieldname'],
                                'value' => $thisdate,
                                'htmlOptions' => array(
                                    'required' => in_array($fname['fieldname'], array("startdate")),
                                ),
                                'pluginOptions' => array(
                                    'format' => $dateformatdetails['jsdate'] . " HH:mm",
                                    'allowInputToggle' => true,
                                    'showClear' => true,
                                    'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang']),
                                )
                            ),
                            true
                        );
                        break;
                    case "startlanguage":
                        $slangs = $oSurvey->allLanguages;
                        foreach ($slangs as $lang) {
                            $LanguageList[$lang] = getLanguageNameFromCode($lang, false);
                        }
                        $aDataentryoutput .= CHtml::dropDownList($fname['fieldname'], $idrow[$fname['fieldname']], $LanguageList, array('class' => 'form-select'));
                        break;
                    default:
                        $aDataentryoutput .= CHtml::textField(
                            $fname['fieldname'],
                            $idrow[$fname['fieldname']],
                            array('class' => 'form-control')
                        );
                        break;
                }

                if (!empty($questionInputs)) {
                    if (
                        $fieldType == Question::QT_K_MULTIPLE_NUMERICAL
                        || $fieldType == Question::QT_N_NUMERICAL
                        || $fieldType == Question::QT_D_DATE
                    ) {
                        $unseenLabel = gT("Unseen or not answered");
                    } else {
                        $unseenLabel = gT("Unseen");
                    }
                    $aDataentryoutput .= "<div class=\"answers-list {$answerWrapperClass}\">";
                    foreach ($questionInputs as $questionInputField => $questionInput) {
                        $aDataentryoutput .= "<div class=\"answer-item\">";
                        $aDataentryoutput .= "<div class=\"checkbox unseen-checkbox\">"
                            . "<input type='checkbox' name='unseen:{$questionInputField}' id='unseen:{$questionInputField}'"
                            . (!empty($unseenStatus[$questionInputField]) ? " checked" : "")
                            . ">"
                            . "<label for='unseen:{$questionInputField}'>" . $unseenLabel . "</label>"
                            . "</div>\n";
                        $aDataentryoutput .= "<div class=\"answer-wrapper\" data-field=\"{$questionInputField}\">" . $questionInput . "</div>";
                        $aDataentryoutput .= "</div>";
                        $aDataentryoutput .= "</div>";
                    }
                    $aDataentryoutput .= "</div>";
                }

                $aDataentryoutput .= "        </td>
                </tr>\n";
            } while ($fname = next($fnames));
            $previousQid = $fname['qid'] ?? 0;
        }
        $aDataentryoutput .= "</table>\n"
        . "<p>\n";

        $aData['sDataEntryLanguage'] = $sDataEntryLanguage;

        if (!Permission::model()->hasSurveyPermission($surveyid, 'responses', 'update')) {
            // if you are not survey owner or super admin you cannot modify responses
            $aDataentryoutput .= "<p><input type='button' value='" . gT("Save") . "' disabled='disabled'/></p>\n";
        } elseif ($subaction == "edit" && Permission::model()->hasSurveyPermission($surveyid, 'responses', 'update')) {
            $aData['part'] = 'edit';
            $aDataentryoutput .= $this->getController()->renderPartial('/admin/dataentry/edit', $aData, true);
        } elseif ($subaction == "editsaved" && Permission::model()->hasSurveyPermission($surveyid, 'responses', 'update')) {
            $aData['part'] = 'editsaved';
            $aDataentryoutput .= $this->getController()->renderPartial('/admin/dataentry/edit', $aData, true);
        }

        $aDataentryoutput .= "</form>\n";

        // Register JS variables for localized messages
        Yii::app()->getClientScript()->registerScript("dataentry-vars", "
            var invalidUnseenCheckboxMessage = '" . gT("If the field is marked as Unseen no value should be set.") . "';
        ", LSYii_ClientScript::POS_BEGIN);

        Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . 'dataentry.js');

        $aViewUrls['output'] = $aDataentryoutput;
        $aData['sidemenu']['state'] = false;
        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/surveyAdministration/partial/topbar/surveyTopbarRight_view',
            [
                'showSaveButton' => true,
                'showCloseButton' => true,
                'closeUrl' => Yii::app()->createUrl('responses/browse', ['surveyId' => $surveyid])
            ],
            true
        );


        $this->renderWrappedTemplate('dataentry', $aViewUrls, $aData);
    }

    /**
     * dataentry::delete()
     * delete dataentry
     * @return void
     */
    public function delete()
    {
        $this->requirePostRequest();

        $surveyid = '';
        if (isset($_REQUEST['surveyid']) && !empty($_REQUEST['surveyid'])) {
            $surveyid = $_REQUEST['surveyid'];
        }
        if (!empty($_REQUEST['sid'])) {
            $surveyid = (int) $_REQUEST['sid'];
        }

        $surveyid = sanitize_int($surveyid);
        $survey = Survey::model()->findByPk($surveyid);
        $id = (int) $_REQUEST['id'];

        $aData = array(
            'surveyid' => $surveyid,
            'id' => $id
        );

        if (
            Permission::model()->hasSurveyPermission($surveyid, 'responses', 'read')
            && Permission::model()->hasSurveyPermission($surveyid, 'responses', 'delete')
        ) {
            $surveytable = $survey->responsesTableName;
            $aData['thissurvey'] = getSurveyInfo($surveyid);

            Yii::app()->loadHelper('database');

            $beforeDataEntryDelete = new PluginEvent('beforeDataEntryDelete');
            $beforeDataEntryDelete->set('iSurveyID', $surveyid);
            $beforeDataEntryDelete->set('iResponseID', $id);
            App()->getPluginManager()->dispatchEvent($beforeDataEntryDelete);

            Response::model($surveyid)->findByPk($id)->delete(true);

            $aData['sidemenu']['state'] = false;

            $aData['topBar']['name'] = 'baseTopbar_view';
            $aData['topBar']['showCloseButton'] = true;

            $this->renderWrappedTemplate('dataentry', 'delete', $aData);
        }
    }

    /**
     * dataentry::update()
     * update dataentry
     * @return void
     * TODO: Make it smaller.
     */
    public function update()
    {
        $surveyid = App()->getRequest()->getParam('surveyid', App()->getRequest()->getParam('sid'));
        if (!Permission::model()->hasSurveyPermission($surveyid, 'responses', 'update')) {
            throw new CHttpException(403);
        }

        $surveyid = (int) ($surveyid);
        $survey = Survey::model()->findByPk($surveyid);
        if (!$survey || !$survey->getIsActive()) {
            throw new CHttpException(404, gT("Invalid survey ID"));
        }
        $id = (int)Yii::app()->request->getPost('id');
        $oResponse = Response::model($surveyid)->findByPk($id);
        if (empty($oResponse)) {
            throw new CHttpException(404, gT("Invalid ID"));
        }
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

        $rawQuestions = Question::model()->findAll("sid = :sid", [":sid" => $surveyid]);

        $questions = [];

        foreach ($rawQuestions as $rawQuestion) {
            $questions[$rawQuestion->qid] = $rawQuestion;
        }

        $thissurvey = getSurveyInfo($surveyid);
        foreach ($fieldmap as $irow) {
            $fieldname = $irow['fieldname'];
            if ($fieldname == 'id') {
                continue;
            }
            $thisvalue = Yii::app()->request->getPost($fieldname, '');
            // For questions, if the "Unseen" checkbox is checked, we must set the field to null.
            // There are some special cases we need to handle.
            if ($irow['type'] == Question::QT_R_RANKING) {
                $unseenFieldName = "unseen:" . 'Q' . $irow['qid'];
            } elseif ($irow['type'] == Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS) {
                // Remove trailing "comment" from the fieldname, if present
                $unseenFieldName = "unseen:" . preg_replace('/comment$/', '', $fieldname);
            } else {
                $unseenFieldName = "unseen:" . $fieldname;
            }
            if (!empty($irow['title']) && Yii::app()->request->getPost($unseenFieldName, false)) {
                // Throw an error if "unseen" is checked but the field is not empty. This should never happen.
                if ($thisvalue !== '') {
                    Yii::app()->setFlashMessage(sprintf(gT("Question %s was marked as \"Unseen\" but a value was provided. The \"Unseen\" status has been ignored."), $irow['title']), 'warning');
                } else {
                    $oResponse->$fieldname = null;
                    continue;
                }
            }
            switch ($irow['type']) {
                case 'lastpage':
                case 'seed':
                    // Not updated : not in view or as disabled
                    break;
                case Question::QT_D_DATE:
                    if (empty($thisvalue)) {
                        $oResponse->$fieldname = null;
                        break;
                    }
                    $qidattributes = QuestionAttribute::model()->getQuestionAttributes($questions[$irow['qid']] ?? Question::model()->findByPk($irow['qid']));
                    $dateformatdetails = getDateFormatDataForQID($qidattributes, $thissurvey);
                    $datetimeobj = DateTime::createFromFormat('!' . $dateformatdetails['phpdate'], $thisvalue);
                    if (!$datetimeobj) {
                        /* Not able to use js system */
                        $datetimeobj = DateTime::createFromFormat('Y-m-d\TH:i', $thisvalue);
                    }
                    if ($datetimeobj) {
                        $oResponse->$fieldname = $datetimeobj->format('Y-m-d H:i');
                    } else {
                        Yii::app()->setFlashMessage(sprintf(gT("Invalid datetime %s value for %s"), htmlentities((string) $thisvalue), $fieldname), 'warning');
                        $oResponse->$fieldname = null;
                    }
                    break;
                case Question::QT_N_NUMERICAL:
                case Question::QT_K_MULTIPLE_NUMERICAL:
                    if ($thisvalue === "") {
                        $oResponse->$fieldname = null;
                        break;
                    }
                    if (!preg_match("/^[-]?(\d{1,20}\.\d{0,10}|\d{1,20})$/", (string) $thisvalue)) {
                        Yii::app()->setFlashMessage(sprintf(gT("Invalid numeric value for %s"), $fieldname), 'warning');
                        $oResponse->$fieldname = null;
                        break;
                    }
                    $oResponse->$fieldname = $thisvalue;
                    break;
                case Question::QT_VERTICAL_FILE_UPLOAD:
                    if (strpos((string) $irow['fieldname'], '_Cfilecount')) {
                        if (empty($thisvalue)) {
                            $oResponse->$fieldname = null;
                            break;
                        }
                        $oResponse->$fieldname = $thisvalue;
                        break;
                    }
                    $oResponse->$fieldname = $thisvalue;
                    break;
                case Question::QT_COLON_ARRAY_NUMBERS:
                    if (!empty($thisvalue) && strval($thisvalue) != strval(floatval($thisvalue))) {
                        // mysql not need, unsure about mssql
                        Yii::app()->setFlashMessage(sprintf(gT("Invalid numeric value for %s"), $fieldname), 'warning');
                        $oResponse->$fieldname = null;
                        break;
                    }
                    $oResponse->$fieldname = $thisvalue;
                    break;
                case 'submitdate':
                    if (Yii::app()->request->getPost('completed') == "N") {
                        $oResponse->$fieldname = null;
                        break;
                    }
                    if (empty($thisvalue)) {
                        if (Survey::model()->findByPk($surveyid)->isDateStamp) {
                            $oResponse->$fieldname = dateShift(date("Y-m-d H:i"), "Y-m-d\TH:i", Yii::app()->getConfig('timeadjust'));
                        } else {
                            $oResponse->$fieldname = date("Y-m-d\TH:i", (int) mktime(0, 0, 0, 1, 1, 1980));
                        }
                        break;
                    }
                    /* FALLTHRU */
                    // No break with submitdate set and completed as Y
                case 'startdate':
                case 'datestamp':
                    if (empty($thisvalue)) {
                        $oResponse->$fieldname = dateShift(date("Y-m-d H:i"), "Y-m-d\TH:i", Yii::app()->getConfig('timeadjust'));
                        break;
                    }
                    $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);
                    $datetimeobj = DateTime::createFromFormat('!' . $dateformatdetails['phpdate'] . " H:i", $thisvalue);
                    if ($datetimeobj) {
                        $oResponse->$fieldname = $datetimeobj->format('Y-m-d H:i');
                    } else {
                        Yii::app()->setFlashMessage(sprintf(gT("Invalid datetime %s value for %s"), htmlentities((string) $thisvalue), $fieldname), 'warning');
                        /* We get here : we need a valid value : NOT NULL in db or completed != "N" */
                        $oResponse->$fieldname = dateShift(date("Y-m-d H:i"), "Y-m-d\TH:i", Yii::app()->getConfig('timeadjust'));
                    }
                    break;
                default:
                    $oResponse->$fieldname = $thisvalue;
            }
        }
        $beforeDataEntryUpdate = new PluginEvent('beforeDataEntryUpdate');
        $beforeDataEntryUpdate->set('iSurveyID', $surveyid);
        $beforeDataEntryUpdate->set('iResponseID', $id);
        App()->getPluginManager()->dispatchEvent($beforeDataEntryUpdate);
        if (!$oResponse->encryptSave()) {
            Yii::app()->setFlashMessage(CHtml::errorSummary($oResponse), 'error');
        } else {
            Yii::app()->setFlashMessage(sprintf(gT("The response record %s was updated."), $id));
        }
        if (Yii::app()->request->getPost('close-after-save') == 'true') {
            $this->getController()->redirect($this->getController()->createUrl("responses/view/", ['surveyId' => $surveyid, 'id' => $id]));
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
        $subaction = App()->request->getPost('subaction');
        $surveyid  = (int) Yii::app()->request->getPost('sid');

        $lang = isset($_POST['lang']) ? Yii::app()->request->getPost('lang') : null;
        $survey = Survey::model()->findByPk($surveyid);
        $password = '';
        $aData = array(
            'surveyid' => $surveyid,
            'lang' => $lang
        );

        $insertSubaction = $subaction == 'insert';
        $hasResponsesCreatePermission = Permission::model()->hasSurveyPermission($surveyid, 'responses', 'create');
        $rawQuestions = Question::model()->findAll("sid = :sid", [":sid" => $surveyid]);

        $questions = [];

        $totalTime = 0;

        foreach ($rawQuestions as $rawQuestion) {
            $questions[$rawQuestion->qid] = $rawQuestion;
        }
        if ($insertSubaction && $hasResponsesCreatePermission) {
            // TODO: $surveytable is unused. Remove it.
            $surveytable = "{{responses_{$surveyid}}}";
            $thissurvey  = getSurveyInfo($surveyid);
            $errormsg = "";

            App()->loadHelper("database");
            $aViewUrls = [];
            $aViewUrls['display']['menu_bars']['browse'] = gT("Data entry");

            $aDataentrymsgs = array();
            $hiddenfields = '';
            $lastanswfortoken = ''; // check if a previous answer has been submitted or saved

            $postToken = App()->request->getPost('token');
            $hasTokensUpdatePermission = Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'update');
            if ($postToken && $hasTokensUpdatePermission) {
                $aToken = $this->getToken($surveyid, $postToken);
                $lastanswfortoken = $this->getLastAnswerByTokenOrAnonymousSurvey($survey, $aToken);
                if ($lastanswfortoken == '') {
                    // token is valid, survey not anonymous, try to get last recorded response id
                    $aresult = Response::model($surveyid)->findAllByAttributes(['token' => $postToken]);
                    if ($aresult) {
                        foreach ($aresult as $arow) {
                            if ($aToken->completed != "N") {
                                $lastanswfortoken = $arow['id'];
                            }
                            $rlanguage = $arow['startlanguage'];
                        }
                    }
                }
            }

            $tokenTableExists = $survey->hasTokensTable;

            // First Check if the survey uses tokens and if a token has been provided
            if ($tokenTableExists && (!$postToken)) {
                $errormsg = $this->returnClosedAccessSurveyErrorMessage();
            } elseif ($tokenTableExists && $lastanswfortoken == 'PrivacyProtected') {
                $errormsg = $this->returnErrorMessageIfLastAnswerForTokenIsPrivacyProtected($errormsg);
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
                        $password = md5((string) $saver['password']);
                    } else {
                        $password = $saver['password'];
                    }
                    $errormsg = "";
                    if (!$saver['identifier']) {
                        $errormsg .= gT("Error") . ": " . gT("You must supply a name for this saved session.");
                    }
                    if (!$saver['password']) {
                        $errormsg .= gT("Error") . ": " . gT("You must supply a password for this saved session.");
                    }
                    if ($saver['password'] != $saver['passwordconfirm']) {
                        $errormsg .= gT("Error") . ": " . gT("Your passwords do not match.");
                    }

                    $aData['errormsg'] = $errormsg;

                    if ($errormsg) {
                        foreach ($_POST as $key => $val) {
                            if (substr($key, 0, 4) != "save" && $key != "action" && $key != "sid" && $key != "datestamp" && $key != "ipaddr") {
                                $hiddenfields .= CHtml::hiddenField($key, $val);
                            }
                        }
                    }
                }

                //BUILD THE SQL TO INSERT RESPONSES
                $fieldmap = createFieldMap($survey, 'full', false, false, $survey->language);
                $insert_data = array();

                $_POST['startlanguage'] = $survey->language;
                if ($survey->isDateStamp) {
                    $_POST['startdate'] = $_POST['datestamp'];
                }
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
                        if ($_POST[$fieldname] == "" && ($irow['type'] == Question::QT_D_DATE || $irow['type'] == Question::QT_N_NUMERICAL || $irow['type'] == Question::QT_K_MULTIPLE_NUMERICAL)) {
                            // can't add '' in Date column
                            // Do nothing
                        } elseif ($irow['type'] == Question::QT_VERTICAL_FILE_UPLOAD) {
                            if (!strpos((string) $irow['fieldname'], "_Cfilecount")) {
                                $json = $_POST[$fieldname];
                                $phparray = json_decode(stripslashes((string) $json));
                                $filecount = 0;
                                if (is_array($phparray)) {
                                    $iArrayCount = count($phparray);
                                    for ($i = 0; $filecount < $iArrayCount; $i++) {
                                        if ($_FILES[$fieldname . "_file_" . $i]['error'] != 4) {
                                            $target = Yii::app()->getConfig('uploaddir') . "/surveys/" . $thissurvey['sid'] . "/files/" . randomChars(20);
                                            $size = 0.001 * $_FILES[$fieldname . "_file_" . $i]['size'];
                                            $name = rawurlencode((string) $_FILES[$fieldname . "_file_" . $i]['name']);

                                            if (move_uploaded_file($_FILES[$fieldname . "_file_" . $i]['tmp_name'], $target)) {
                                                $phparray[$filecount]->filename = basename($target);
                                                $phparray[$filecount]->name = $name;
                                                $phparray[$filecount]->size = $size;
                                                $pathinfo = pathinfo((string) $_FILES[$fieldname . "_file_" . $i]['name']);
                                                $phparray[$filecount]->ext = $pathinfo['extension'];
                                                $filecount++;
                                            }
                                        }
                                    }
                                }

                                $insert_data[$fieldname] = ls_json_encode($phparray);
                            } else {
                                if (is_array($phparray)) {
                                    $insert_data[$fieldname] = count($phparray);
                                } else {
                                    $insert_data[$fieldname] = 0;
                                }
                            }
                        } elseif ($irow['type'] == Question::QT_D_DATE) {
                            $qidattributes = QuestionAttribute::model()->getQuestionAttributes($questions[$irow['qid']] ?? Question::model()->findByPk($irow['qid']));
                            $dateformatdetails = getDateFormatDataForQID($qidattributes, $thissurvey);
                            $datetimeobj = DateTime::createFromFormat('!' . $dateformatdetails['phpdate'], $_POST[$fieldname]);
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
                $new_response = new SurveyDynamic();
                foreach ($insert_data as $column => $value) {
                    $new_response->$column = $value;
                }

                $beforeDataEntryCreate = new PluginEvent('beforeDataEntryCreate');
                $beforeDataEntryCreate->set('iSurveyID', $surveyid);
                $beforeDataEntryCreate->set('oModel', $new_response);
                App()->getPluginManager()->dispatchEvent($beforeDataEntryCreate);

                $new_response->encryptSave();
                $last_db_id = $new_response->getPrimaryKey();
                if (isset($_POST['closerecord']) && isset($_POST['token']) && $_POST['token'] != '') {
                    // submittoken
                    // get submit date
                    if (isset($_POST['closedate'])) {
                        $submitdate = $_POST['closedate'];
                    } else {
                        $submitdate = date("Y-m-d H:i:s");
                    }
                    // query for updating tokens uses left
                    if ($lastanswfortoken == '' || $lastanswfortoken == 'AnonymousNotCompleted') {
                        $aToken = Token::model($surveyid)->findByAttributes(['token' => $_POST['token']]);
                        if (isTokenCompletedDatestamped($thissurvey)) {
                            if ($aToken->usesleft <= 1) {
                                $aToken->usesleft = ((int) $aToken->usesleft) - 1;
                                if ($lastanswfortoken == 'AnonymousNotCompleted') {
                                    $aToken->completed = "Y";
                                } else {
                                    $aToken->completed = $submitdate;
                                }
                            } else {
                                $aToken->usesleft = ((int) $aToken->usesleft) - 1;
                            }
                        } else {
                            if ($aToken->usesleft <= 1) {
                                $aToken->usesleft = ((int) $aToken->usesleft) - 1;
                                $aToken->completed = 'Y';
                            } else {
                                $aToken->usesleft = ((int) $aToken->usesleft) - 1;
                            }
                        }
                        $aToken->save();
                    }

                    // save submitdate into survey table
                    $aResponse = Response::model($surveyid)->findByPk($last_db_id);
                    $aResponse->submitdate = $submitdate;
                    $aResponse->save();
                }
                if (isset($_POST['save']) && $_POST['save'] == "on") {
                    $srid = $last_db_id;
                    $aUserData = Yii::app()->session;
                    //CREATE ENTRY INTO "saved_control"

                    $arSaveControl = new SavedControl();
                    $arSaveControl->sid = $surveyid;
                    $arSaveControl->srid = $srid;
                    $arSaveControl->identifier = $saver['identifier'];
                    $arSaveControl->access_code = $password;
                    $arSaveControl->email = $saver['email'];
                    $arSaveControl->ip = !empty($aUserData['ip_address']) ? $aUserData['ip_address'] : "";
                    $arSaveControl->refurl = (string) getenv("HTTP_REFERER");
                    $arSaveControl->saved_thisstep = '0';
                    $arSaveControl->status = 'S';
                    $arSaveControl->saved_date = dateShift((string) date("Y-m-d H:i:s"), "Y-m-d H:i", "'" . Yii::app()->getConfig('timeadjust'));
                    $arSaveControl->save();
                    if ($arSaveControl->save()) {
                        $aDataentrymsgs[] = CHtml::tag('font', array('class' => 'successtitle'), gT("Your survey responses have been saved successfully.  You will be sent a confirmation email. Please make sure to save your password, since we will not be able to retrieve it for you."));
                        $tokens_table = "{{tokens_$surveyid}}";
                        if (tableExists($tokens_table)) {
                            $tokendata = array(
                            "firstname" => $saver['identifier'],
                            "lastname" => $saver['identifier'],
                            "email" => $saver['email'],
                            "token" => $password,
                            "language" => $saver['language'],
                            "sent" => date("Y-m-d H:i:s"),
                            "completed" => "N");

                            $aToken = new TokenDynamic($surveyid);
                            $aToken->setAttributes($tokendata, false);
                            $aToken->encryptSave(true);
                            $aDataentrymsgs[] = CHtml::tag('font', array('class' => 'successtitle'), gT("A survey participant entry for the saved survey has been created, too."));
                        }
                        if ($saver['email']) {
                            //Send email
                            if (validateEmailAddress($saver['email']) && !returnGlobal('redo')) {
                                $mailer = new \LimeMailer();
                                $mailer->addAddress($saver['email']);
                                $mailer->setSurvey($surveyid);
                                $mailer->emailType = 'savesurveydetails';
                                $mailer->Subject = gT("Saved Survey Details");
                                $message = gT("Thank you for saving your survey in progress. The following details can be used to return to this survey and continue where you left off.");
                                $message .= "\n\n" . $thissurvey['name'] . "\n\n";
                                $message .= gT("Name") . ": " . $saver['identifier'] . "\n";
                                $message .= gT("Reload your survey by clicking on the following link (or pasting it into your browser):") . "\n";
                                $aParams = array('lang' => $saver['language'], 'loadname' => $saver['identifier']);
                                $message .= Yii::app()->getController()->createAbsoluteUrl("/survey/index/sid/{$surveyid}/loadall/reload/scid/{$arSaveControl->scid}/", $aParams);
                                $mailer->Body = $message;
                                if ($mailer->sendMessage()) {
                                    $aDataentrymsgs[] = CHtml::tag('strong', array('class' => 'successtitle text-success'), gT("An email has been sent with details about your saved survey. Please make sure to remember your password."));
                                } else {
                                    $aDataentrymsgs[] = CHtml::tag('strong', array('class' => 'errortitle text-danger'), sprintf(gT("Unable to send email about your saved survey (Error: %s)."), $mailer->getError()));
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

            $aData['topBar']['name'] = 'baseTopbar_view';

            $this->renderWrappedTemplate('dataentry', 'insert', $aData);
        }
    }

    /**
     * Returns an Token.
     * @param int          $id      Survey ID
     * @param string       $token   Token (Post Request Data)
     * @return null|Token
     */
    private function getToken(int $id, string $token)
    {
        $token = Token::model($id)->findByAttributes(['token' => $token]);
        return $token;
    }

    /**
     * Returns the last answer for token or anonymous survey.
     * @param \Survey $survey Survey
     * @param \Token  $token  Token
     * @return string
     */
    private function getLastAnswerByTokenOrAnonymousSurvey(Survey $survey, Token $token = null): string
    {
        $lastAnswer = '';
        $isTokenNull  = $token == null;
        $isTokenEmpty = empty($token);
        $isTokenCompleted = empty($token) ? "" : $token->completed;
        $isTokenCompletedEmpty = empty($isTokenCompleted);
        $isSurveyAnonymous = $survey->isAnonymized;

        if ($isTokenNull || $isTokenEmpty) {
            $lastAnswer = 'UnknownToken';
        } elseif ($isSurveyAnonymous) {
            if (!$isTokenCompletedEmpty && $isTokenCompleted !== "N") {
                $lastAnswer = 'PrivacyProtected';
            } else {
                $lastAnswer = 'AnonymousNotCompleted';
            }
        }
        return $lastAnswer;
    }

    /**
     * Returns Error Message if the survey only supports closed access.
     * @return string
     */
    private function returnClosedAccessSurveyErrorMessage(): string
    {
        $errormsg = CHtml::tag('div', array('class' => 'warningheader'), gT("Error"));
        $errormsg .= CHtml::tag('p', array(), gT("This is a closed-access survey, so you must supply a access code.  Please contact the administrator for assistance."));
        return $errormsg;
    }

    /**
     * Returns Error Message if access code is not valid or already in use.
     * @return string
     */
    private function returnAccessCodeIsNotValidOrAlreadyInUseErrorMessage(): string
    {
        $errormsg = CHtml::tag('div', array('class' => 'warningheader'), gT("Error"));
        $errormsg .= CHtml::tag('p', array(), gT("The provided access code is not valid or has already been used."));
        return $errormsg;
    }

    /**
     * Returns Error Message if access code is already recorded.
     * @return string
     */
    private function returnAlreadyRecordedAnswerForAccessCodeErrorMessage(): string
    {
        $errormsg = CHtml::tag('div', array('class' => 'warningheader'), gT("Error"));
        $errormsg .= CHtml::tag('p', array(), gT("There is already a recorded answer for this access code"));
        return $errormsg;
    }

    /**
     * Returns Error Message if LastAnswerForToken is not Privacy Protected. Appends it to the given ErrorMessage.
     * @param string $lastAnswer   Last Answer for Token
     * @param int    $id           Survey ID
     * @param string $errorMessage Error Message
     * @return string
     */
    private function returnErrorMessageIfLastAnswerForTokenIsNotPrivacyProtected(string $lastAnswer, int $id, string $errorMessage): string
    {
        $errorMessage .= "<br /><br />" . gT("Use the following link to update it:") . "\n";
        $errorMessage .= CHtml::link(
            "[id:$lastAnswer]",
            $this->getController()->createUrl('/admin/dataentry/sa/editdata/subaction/edit/id/' . $lastAnswer . '/surveyid/' . $id),
            array('title' => gT("Edit this entry"))
        );
        $errorMessage .= "<br/><br/>";
        return $errorMessage;
    }

    /**
     * Returns Error Message if Last Answer for Token is Privacy Protected.
     * @param string $errorMessage Error Message
     * @return string
     */
    private function returnErrorMessageIfLastAnswerForTokenIsPrivacyProtected(string $errorMessage): string
    {
        $errorMessage .= "<br /><br />" . gT("This surveys uses anonymized responses, so you can't update your response.") . "\n";
        return $errorMessage;
    }

    /**
     * dataentry::view()
     * view a dataentry
     * @param int $surveyid Given Survey ID
     * @return void
     * TODO: Make it smaller!
     */
    public function view($surveyid)
    {
        $surveyid = sanitize_int($surveyid);
        $survey = Survey::model()->findByPk($surveyid);
        $lang = $_GET['lang'] ?? null;
        if (isset($lang)) {
            $lang = sanitize_languagecode($lang);
        }
        $aViewUrls = array();

        if (Permission::model()->hasSurveyPermission($surveyid, 'responses', 'create')) {
            $baselang = $survey->language;
            $slangs = $survey->allLanguages;

            if (is_null($lang) || !in_array($lang, $slangs)) {
                $sDataEntryLanguage = $baselang;
            } else {
                $sDataEntryLanguage = $lang;
            }
            $langlistbox = languageDropdown($surveyid, $sDataEntryLanguage);
            $thissurvey = getSurveyInfo($surveyid);

            $aSurveyOption = array(
                'startlanguage' => $sDataEntryLanguage,
            );
            // Unsure we use EM with current sid and language
            LimeExpressionManager::SetSurveyId($surveyid);
            LimeExpressionManager::SetEMLanguage($sDataEntryLanguage);
            //This is the default, presenting a blank dataentry form
            LimeExpressionManager::StartSurvey($surveyid, 'survey', $aSurveyOption, false, LEM_PRETTY_PRINT_ALL_SYNTAX);
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

            $rawQuestions = Question::model()->findAll("sid = :sid", [":sid" => $surveyid]);

            $questions = [];

            foreach ($rawQuestions as $rawQuestion) {
                $questions[$rawQuestion->qid] = $rawQuestion;
            }

            // SURVEY NAME AND DESCRIPTION TO GO HERE
            $aGroups = $survey->groups;
            $aDataentryoutput = '';
            foreach ($aGroups as $arGroup) {
                LimeExpressionManager::StartProcessingGroup($arGroup->gid, ($thissurvey['anonymized'] != "N"), $surveyid);

                $aQuestions = $arGroup->questions;
                $aDataentryoutput .= "\t<tr class='info'>\n"
                . "<!-- Inside controller dataentry.php -->"
                . "<td colspan='3'><h4>" . flattenText($arGroup->questiongroupl10ns[$sDataEntryLanguage]->group_name, true) . "</h4></td>\n"
                . "\t</tr>\n";

                $gid = $arGroup['gid'];

                $aDataentryoutput .= "\t<tr class='data-entry-separator'><td colspan='3'></td></tr>\n";
                $bgc = 'odd';
                foreach ($aQuestions as $arQuestion) {
                    $cdata = array();
                    $qidattributes = QuestionAttribute::model()->getQuestionAttributes($questions[$arQuestion['qid']] ?? $arQuestion);
                    $cdata['qidattributes'] = $qidattributes;

                    $qinfo = LimeExpressionManager::GetQuestionStatus($arQuestion['qid']);
                    $relevance = trim((string) $qinfo['info']['relevance']);
                    $explanation = trim((string) $qinfo['relEqn']);
                    $validation = trim((string) $qinfo['prettyValidTip']);
                    $arrayFilterHelp = flattenText($this->arrayFilterHelp($qidattributes, $sDataEntryLanguage, $surveyid));

                    if (true || ($relevance != '' && $relevance != '1') || ($validation != '') || ($arrayFilterHelp != '')) {
                        $message = '';
                        $alert = '';
                        if ($bgc == "even") {
                            $bgc = "odd";
                        } else {
                            $bgc = "even";
                        } //Do no alternate on explanation row
                        if ($relevance != '' && $relevance != '1') {
                            $message .= '<strong>' . gT(
                                "Only answer this if the following conditions are met:",
                                'html',
                                $sDataEntryLanguage
                            ) . "</strong><br />$explanation\n";
                        }
                        if ($validation != '') {
                            $message .= '<strong>' . gT(
                                "The answer(s) must meet these validation criteria:",
                                'html',
                                $sDataEntryLanguage
                            ) . "</strong><br />$validation\n";
                        }
                        if ($message != '' && $arrayFilterHelp != '') {
                            $message .= '<br/>';
                        }
                        if ($arrayFilterHelp != '') {
                            $message .= '<strong>' . gT(
                                "The answer(s) must meet these array_filter criteria:",
                                'html',
                                $sDataEntryLanguage
                            ) . "</strong><br />$arrayFilterHelp\n";
                        }
                        if ($message != '') {
                            $alert = App()->getController()->widget('ext.AlertWidget.AlertWidget', [
                                'text' => $message,
                                'type' => 'warning',
                                'htmlOptions' => ['class' => 'col-md-8 offset-md-2']
                            ], true);
                            $cdata['explanation'] = "<tr class ='data-entry-explanation'><td class='data-entry-small-text' colspan='3' align='left'>$alert</td></tr>\n";
                        }
                    }

                    //END OF GETTING CONDITIONS

                    //Alternate bgcolor for different groups
                    if (!isset($bgc)) {
                        $bgc = "even";
                    }
                    if ($bgc == "even") {
                        $bgc = "odd";
                    } else {
                        $bgc = "even";
                    }

                    $qid = $arQuestion['qid'];
                    $fieldname = "Q" . "$qid";

                    $cdata['bgc'] = $bgc;
                    $cdata['fieldname'] = $fieldname;
                    $cdata['deqrow'] = $arQuestion;

                    $cdata['thissurvey'] = $thissurvey;
                    if (!empty($arQuestion->questionl10ns[$sDataEntryLanguage]->help)) {
                        $hh = addcslashes((string) $arQuestion->questionl10ns[$sDataEntryLanguage]->help, "\0..\37'\""); //Escape ASCII decimal 0-32 plus single and double quotes to make JavaScript happy.
                        $hh = htmlspecialchars($hh, ENT_QUOTES); //Change & " ' < > to HTML entities to make HTML happy.
                        $cdata['hh'] = $hh;
                    }
                    switch ($arQuestion['type']) {
                        case Question::QT_Q_MULTIPLE_SHORT_TEXT: //Multiple short text
                        case Question::QT_K_MULTIPLE_NUMERICAL:
                            $cdata['dearesult'] = Question::model()->findAll("parent_qid={$arQuestion['qid']}");
                            break;

                        case Question::QT_1_ARRAY_DUAL: // Dual scale^
                            $cdata['dearesult'] = Question::model()->findAll("parent_qid={$arQuestion['qid']}");
                            $cdata['fother'] = $arQuestion->other;

                            break;

                        case Question::QT_L_LIST: //LIST drop-down/radio-button list
                        case Question::QT_EXCLAMATION_LIST_DROPDOWN:
                            if ($arQuestion['type'] == '!' && trim((string) $qidattributes['category_separator']) != '') {
                                $optCategorySeparator = $qidattributes['category_separator'];
                            } else {
                                unset($optCategorySeparator);
                            }
                            $defexists = "";
                            $arAnswers = Answer::model()->with('answerl10ns')->findAll(array('condition' => 'qid =:qid AND language = :language', 'params' => array('qid' => $arQuestion['qid'], 'language' => $sDataEntryLanguage)));
                            $aDatatemp = '';
                            if (!isset($optCategorySeparator)) {
                                foreach ($arAnswers as $aAnswer) {
                                    $aDatatemp .= "<option value='{$aAnswer['code']}'";
                                    $aDatatemp .= ">{$aAnswer->answerl10ns[$sDataEntryLanguage]->answer}</option>\n";
                                }
                            } else {
                                $defaultopts = array();
                                $optgroups = array();

                                foreach ($arAnswers as $aAnswer) {
                                    list ($categorytext, $answertext) = explode($optCategorySeparator, (string) $aAnswer->answerl10ns[$sDataEntryLanguage]->answer);
                                    if ($categorytext == '') {
                                        $defaultopts[] = array('code' => $aAnswer['code'], 'answer' => $answertext, 'default_value' => $aAnswer['assessment_value']);
                                    } else {
                                        $optgroups[$categorytext][] = array('code' => $aAnswer['code'], 'answer' => $answertext, 'default_value' => $aAnswer['assessment_value']);
                                    }
                                }
                                foreach ($optgroups as $categoryname => $optionlistarray) {
                                    $aDatatemp .= "<optgroup class=\"dropdowncategory\" label=\"" . $categoryname . "\">\n";
                                    foreach ($optionlistarray as $optionarray) {
                                        $aDatatemp .= "\t<option value='{$optionarray['code']}'";
                                        $aDatatemp .= ">{$optionarray['answer']}</option>\n";
                                    }
                                    $aDatatemp .= "</optgroup>\n";
                                }
                                foreach ($defaultopts as $optionarray) {
                                    $aDatatemp .= "\t<option value='{$optionarray['code']}'";
                                    $aDatatemp .= ">{$optionarray['answer']}</option>\n";
                                }
                            }
                            $cdata['fother'] = $arQuestion->other;
                            $cdata['defexists'] = $defexists;
                            $cdata['datatemp'] = $aDatatemp;

                            break;
                        case Question::QT_O_LIST_WITH_COMMENT: //LIST WITH COMMENT drop-down/radio-button list + textarea
                            $defexists = "";
                            $arAnswers = $arQuestion->answers;
                            $aDatatemp = '';
                            foreach ($arAnswers as $aAnswer) {
                                $aDatatemp .= "<option value='{$aAnswer['code']}'";
                                $aDatatemp .= ">{$aAnswer->answerl10ns[$sDataEntryLanguage]->answer}</option>\n";
                            }
                            $cdata['datatemp'] = $aDatatemp;
                            $cdata['defexists'] = $defexists;

                            break;
                        case Question::QT_R_RANKING: // Ranking TYPE QUESTION
                            $thisqid = $arQuestion['qid'];
                            $arAnswers = $arQuestion->answers;
                            $anscount = count($arAnswers);

                            $cdata['thisqid'] = $thisqid;
                            $cdata['anscount'] = $anscount;
                            $cdata['answers'] = $arAnswers;
                            App()->getClientScript()->registerPackage('jquery-actual');
                            App()->getClientScript()->registerScriptFile(App()->getConfig('generalscripts') . 'ranking.js');
                            App()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . 'ranking.css');
                            break;
                        case Question::QT_M_MULTIPLE_CHOICE: //Multiple choice checkbox (Quite tricky really!)
                            if (trim((string) $qidattributes['display_columns']) != '') {
                                $dcols = $qidattributes['display_columns'];
                            } else {
                                $dcols = 1;
                            }
                            $cdata['mearesult'] = $arQuestion->subquestions;
                            $meacount = count($cdata['mearesult']);
                            $cdata['meacount'] = $meacount;
                            $cdata['dcols'] = $dcols;
                            break;
                        case Question::QT_I_LANGUAGE: //Language Switch
                            $cdata['slangs'] = $survey->allLanguages;
                            break;
                        case Question::QT_A_ARRAY_5_POINT: // Array (5 point choice) radio-buttons
                        case Question::QT_B_ARRAY_10_CHOICE_QUESTIONS: // Array (10 point choice) radio-buttons
                        case Question::QT_C_ARRAY_YES_UNCERTAIN_NO: // Array (Yes/Uncertain/No)
                        case Question::QT_E_ARRAY_INC_SAME_DEC: // Array (Yes/Uncertain/No)
                        case Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS: //Multiple choice with comments checkbox + text
                            $cdata['mearesult'] = $arQuestion->subquestions;
                            break;
                        case Question::QT_VERTICAL_FILE_UPLOAD:
                            $cdata['qidattributes'] = $qidattributes;
                            $maxfiles = $qidattributes['max_num_of_files'];
                            $cdata['maxfiles'] = $maxfiles;
                            break;
                        case Question::QT_COLON_ARRAY_NUMBERS: // Array
                            $minvalue = 1;
                            $maxvalue = 10;
                            if (trim((string) $qidattributes['multiflexible_max']) != '' && trim((string) $qidattributes['multiflexible_min']) == '') {
                                $maxvalue = $qidattributes['multiflexible_max'];
                                $minvalue = 1;
                            }
                            if (trim((string) $qidattributes['multiflexible_min']) != '' && trim((string) $qidattributes['multiflexible_max']) == '') {
                                $minvalue = $qidattributes['multiflexible_min'];
                                $maxvalue = $qidattributes['multiflexible_min'] + 10;
                            }
                            if (trim((string) $qidattributes['multiflexible_min']) != '' && trim((string) $qidattributes['multiflexible_max']) != '') {
                                if ($qidattributes['multiflexible_min'] < $qidattributes['multiflexible_max']) {
                                    $minvalue = $qidattributes['multiflexible_min'];
                                    $maxvalue = $qidattributes['multiflexible_max'];
                                }
                            }

                            $stepvalue = (trim((string) $qidattributes['multiflexible_step']) != '' && $qidattributes['multiflexible_step'] > 0) ? $qidattributes['multiflexible_step'] : 1;

                            if ($qidattributes['reverse'] == 1) {
                                $tmp = $minvalue;
                                $minvalue = $maxvalue;
                                $maxvalue = $tmp;
                                $reverse = true;
                                $stepvalue = -$stepvalue;
                            } else {
                                $reverse = false;
                            }

                            if ($qidattributes['multiflexible_checkbox'] != 0) {
                                $minvalue = 0;
                                $maxvalue = 1;
                                $stepvalue = 1;
                            }
                            $cdata['minvalue'] = $minvalue;
                            $cdata['maxvalue'] = $maxvalue;
                            $cdata['stepvalue'] = $stepvalue;

                            $cdata['lresult'] = $arQuestion->findAllByAttributes(['parent_qid' => $arQuestion['qid'], 'scale_id' => 1]);
                            if (empty($cdata['lresult'])) {
                                $eMessage = "Couldn't get labels";
                                Yii::app()->setFlashMessage($eMessage);
                                $this->getController()->redirect($this->getController()->createUrl("/admin/"));
                            }
                            $cdata['mearesult'] = $arQuestion->findAllByAttributes(['parent_qid' => $arQuestion['qid'], 'scale_id' => 0]);
                            if (empty($cdata['mearesult'])) {
                                $eMessage = "Couldn't get answers";
                                Yii::app()->setFlashMessage($eMessage);
                                $this->getController()->redirect($this->getController()->createUrl("/admin/"));
                            }
                            break;
                        case Question::QT_SEMICOLON_ARRAY_TEXT: // Array
                            $cdata['lresult'] = $arQuestion->findAllByAttributes(['parent_qid' => $arQuestion['qid'], 'scale_id' => 1]);
                            $cdata['mearesult'] = $arQuestion->findAllByAttributes(['parent_qid' => $arQuestion['qid'], 'scale_id' => 0]);
                            break;
                        case Question::QT_F_ARRAY: // Array
                        case Question::QT_H_ARRAY_COLUMN:
                            $cdata['mearesult'] = $arQuestion->subquestions;
                            $cdata['fresult'] = Answer::model()->findAllByAttributes(['qid' => $arQuestion['qid']]);
                            break;
                    }

                    $cdata['sDataEntryLanguage'] = $sDataEntryLanguage;
                    $viewdata = $this->getController()->renderPartial("/admin/dataentry/content_view", $cdata, true);
                    $viewdata_em = LimeExpressionManager::ProcessString($viewdata, $arQuestion['qid'], null, 1, 1);
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

            $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
                '/surveyAdministration/partial/topbar/surveyTopbarRight_view',
                [
                    'showSaveButton' => true,
                    'showCloseButton' => true,
                    'closeUrl' => Yii::app()->createUrl('responses/browse', ['surveyId' => $survey->sid])
                ],
                true
            );

            $this->renderWrappedTemplate('dataentry', $aViewUrls, $aData);
        }
    }

    /**
     * Returns Encoding Array.
     * @return array
     */
    private function getEncodingsArray()
    {
        return aEncodingsArray();
    }

    /**
    * This is a duplicate of the array_filter_help function in printablesurvey.php
    * TODO: Why is this duplicated? Use just one solution.
    *
    * @param array  $qidattributes   Given Attributes
    * @param string $surveyprintlang Given Language
    * @param int    $surveyid        Given Survey ID
    * @return string
    */
    private function arrayFilterHelp($qidattributes, $surveyprintlang, $surveyid)
    {
        $output = "";
        if (!empty($qidattributes['array_filter'])) {

            /** @var Question $question */
            $question = Question::model()->findByAttributes(array('title' => $qidattributes['array_filter'], 'sid' => $surveyid));
            if ($question) {
                $output .= "\n<p class='extrahelp'>
                " . sprintf(gT("Only answer this question for the items you selected in question %s ('%s')"), $qidattributes['array_filter'], flattenText(breakToNewline($question->questionl10ns[$surveyprintlang]->question))) . "
                </p>\n";
            }
        }
        if (!empty($qidattributes['array_filter_exclude'])) {
            /** @var Question $question */
            $question = Question::model()->findByAttributes(array('title' => $qidattributes['array_filter_exclude'], 'sid' => $surveyid));
            if ($question) {
                $output .= "\n    <p class='extrahelp'>
                " . sprintf(gT("Only answer this question for the items you did not select in question %s ('%s')"), $qidattributes['array_filter_exclude'], breakToNewline($question->questionl10ns[$surveyprintlang]->question)) . "
                </p>\n";
            }
        }
        return $output;
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string       $sAction     Current action, the folder to fetch views from
     * @param string|array $aViewUrls   View url(s)
     * @param array        $aData       Data to be passed on. Optional.
     * @param bool|string  $sRenderFile Boolean value if file will be rendered.
     * @return void
     */
    protected function renderWrappedTemplate($sAction = 'dataentry', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
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
        parent::renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }
}
