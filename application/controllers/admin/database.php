<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
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
* $Id: database.php 11349 2011-11-09 21:49:00Z tpartner $
*
*/
/**
 * Database
 *
 * @package LimeSurvey
 * @author
 * @copyright 2011
 * @version $Id: database.php 11349 2011-11-09 21:49:00Z tpartner $
 * @access public
 */
class database extends Survey_Common_Action
{

    public function run($sa = null)
    {
        $this->route('index', array('sa'));
    }

    /**
     * Database::index()
     *
     * @param mixed $action
     * @return
     */
    function index($action = null)
    {
        if (!empty($_POST['action'])) $action = $_POST['action'];

        $postsid = returnglobal('sid');
        $postgid = returnglobal('gid');
        //$postqid=returnglobal('qid');
        //$postqaid=returnglobal('qaid');
        $surveyid = returnglobal('sid');
        $gid = returnglobal('gid');
        $qid = returnglobal('qid');
        // if $action is not passed, check post data.
        if ($action == "updatedefaultvalues" && bHasSurveyPermission($surveyid, 'surveycontent', 'update')) {
            $this->_updateDefaultValuesLanguage($surveyid, $gid, $qid);
        }

        if ($action == "updateansweroptions" && bHasSurveyPermission($surveyid, 'surveycontent', 'update')) {
            $this->_updateAnswerOptions($surveyid, $gid, $qid);
        }

        if ($action == "updatesubquestions" && bHasSurveyPermission($surveyid, 'surveycontent', 'update')) {
            $this->_updateSubQuestions($surveyid, $gid, $qid);
        }

        if (in_array($action, array('insertquestion', 'copyquestion')) && bHasSurveyPermission($surveyid, 'surveycontent', 'create')) {
            $this->_insertCopyQuestions($postgid, $surveyid, $gid, $qid, $action);
        }
        if ($action == "updatequestion" && bHasSurveyPermission($surveyid, 'surveycontent', 'update')) {
            $this->_updateQuestion($surveyid, $gid, $qid);
        }

        if (($action == "updatesurveylocalesettings") && bHasSurveyPermission($surveyid, 'surveylocale', 'update')) {
            $this->_updateSurveyLocaleSettings($postsid, $surveyid);
        }

        if (($action == "updatesurveysettingsandeditlocalesettings" || $action == "updatesurveysettings") && bHasSurveyPermission($surveyid, 'surveysettings', 'update')) {
            $this->_updateSurveySettingsAndEditLocaleSettings($surveyid);
        }

        if (!$action) {
            $this->getController()->redirect("/admin", "refresh");
        }
    }

    function _updateDefaultValuesLanguage($surveyid, $gid, $qid)
    {
        $clang = Yii::app()->lang;
        $questlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        array_unshift($questlangs, $baselang);

        // same_default value on/off for question
        if (isset($_POST['samedefault'])) {
            $uqresult = Groups::model()->update(array('same_default' => 1), array('sid' => $surveyid, 'qid' => $qid));
        }
        else
        {
            $uqresult = Groups::model()->update(array('same_default' => 0), array('sid' => $surveyid, 'qid' => $qid));
        }
        $databaseoutput['uqresult'] = $uqresult;

        $res = Questions::model()->getSomeRecords(array('type'), array('qid' => $qid));
        $resrow = $res->read();
        $questiontype = $resrow['type'];

        $qtproperties = getqtypelist('', 'array');
        if ($qtproperties[$questiontype]['answerscales'] > 0 && $qtproperties[$questiontype]['subquestions'] == 0) {
            for ($scale_id = 0; $scale_id < $qtproperties[$questiontype]['answerscales']; $scale_id++)
            {
                foreach ($questlangs as $language)
                {
                    if (isset($_POST['defaultanswerscale_' . $scale_id . '_' . $language])) {
                        $this->_updateDefaultValues($qid, 0, $scale_id, '', $language, $_POST['defaultanswerscale_' . $scale_id . '_' . $language]);
                    }
                    if (isset($_POST['other_' . $scale_id . '_' . $language])) {
                        $this->_updateDefaultValues($qid, 0, $scale_id, 'other', $language, $_POST['other_' . $scale_id . '_' . $language]);
                    }
                }
            }
        }
        if ($qtproperties[$questiontype]['subquestions'] > 0) {

            foreach ($questlangs as $language)
            {
                $sqresult = Questions::model()->getQuestions($surveyid, $gid, $language, $qid);
                for ($scale_id = 0; $scale_id < $qtproperties[$questiontype]['subquestions']; $scale_id++)
                {

                    foreach ($sqresult->readAll() as $aSubquestionrow)
                    {
                        if (isset($_POST['defaultanswerscale_' . $scale_id . '_' . $language . '_' . $aSubquestionrow['qid']])) {
                            $this->_updateDefaultValues($qid, $aSubquestionrow['qid'], $scale_id, '', $language, $_POST['defaultanswerscale_' . $scale_id . '_' . $language . '_' . $aSubquestionrow['qid']]);
                        }
                        /*                       if (isset($_POST['other_'.$scale_id.'_'.$language]))
                              {
                              Updatedefaultvalues($postqid,$qid,$scale_id,'other',$language,$_POST['other_'.$scale_id.'_'.$language],true);
                              } */

                    }
                }
            }
        }
        $this->session->set_userdata('flashmessage', $clang->gT("Default value settings were successfully saved."));

        if ($databaseoutput != '') {
            $this->getController()->render('/admin/database_view', $databaseoutput);
        }
        else
        {
            $this->getController()->redirect($this->getController()->createUrl('/admin/survey/view/' . $surveyid . '/' . $gid . '/' . $qid));
        }
    }

    function _updateAnswerOptions($surveyid, $gid, $qid)
    {
        Yii::app()->loadHelper('database');
        $clang = Yii::app()->lang;

        $anslangs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $baselang = GetBaseLanguageFromSurveyID($surveyid);

        $alllanguages = $anslangs;
        array_unshift($alllanguages, $baselang);

        $resrow = Questions::model()->getSomeRecords(array('type'), array('qid' => $qid));
        $questiontype = $resrow['type']; //$connect->GetOne($query);    // Checked)
        $qtypes = getqtypelist('', 'array');
        $scalecount = $qtypes[$questiontype]['answerscales'];

        $invalidCode = 0;
        $duplicateCode = 0;

        //require_once("../classes/inputfilter/class.inputfilter_clean.php");
        //$myFilter = new InputFilter('','',1,1,1);

        //First delete all answers
        Answers::model()->delete(array($qid));

        for ($scale_id = 0; $scale_id < $scalecount; $scale_id++)
        {
            $maxcount = (int)$_POST['answercount_' . $scale_id];

            for ($sortorderid = 1; $sortorderid < $maxcount; $sortorderid++)
            {
                $code = sanitize_paranoid_string($_POST['code_' . $sortorderid . '_' . $scale_id]);
                if (isset($_POST['oldcode_' . $sortorderid . '_' . $scale_id])) {
                    $oldcode = sanitize_paranoid_string($_POST['oldcode_' . $sortorderid . '_' . $scale_id]);
                    if ($code !== $oldcode) {
                        Conditions::model()->update(array('value' => db_quoteall($code)), array('cqid' => db_quoteall($qid), 'value' => db_quoteall($oldcode)));
                    }
                }

                $assessmentvalue = (int)$_POST['assessment_' . $sortorderid . '_' . $scale_id];
                foreach ($alllanguages as $language)
                {
                    $answer = $_POST['answer_' . $language . '_' . $sortorderid . '_' . $scale_id];
                    if (Yii::app()->getConfig('filterxsshtml')) {
                        //Sanitize input, strip XSS
                        $answer = $this->security->xss_clean($answer);
                    }
                    else
                    {
                        $answer = html_entity_decode($answer, ENT_QUOTES, "UTF-8");
                    }
                    // Fix bug with FCKEditor saving strange BR types
                    $answer = fix_FCKeditor_text($answer);

                    // Now we insert the answers

                    $result = Answers::model()->insertRecords(array('code' => $code, 'answer' => $answer, 'qid' => $qid, 'sortorder' => $sortorderid, 'language' => $language, 'assessment_value' => $assessmentvalue, 'scale_id' => $scale_id));
                    $databaseoutput['result'] = $result;

                } // foreach ($alllanguages as $language)

                if ($code !== $oldcode) {
                    Conditions::model()->update(array('value' => $code), array('cqid' => $qid, 'value' => $oldcode));
                }

            } // for ($sortorderid=0;$sortorderid<$maxcount;$sortorderid++)
        } //  for ($scale_id=0;

        $databaseoutput['invalidCode'] = $invalidCode;
        $databaseoutput['duplicateCode'] = $duplicateCode;

        $this->session->set_userdata('flashmessage', $clang->gT("Answer options were successfully saved."));

        if ($databaseoutput != '') {
            $this->getController()->render('/admin/database_view', $databaseoutput);
        }
        else
        {
            $this->getController()->redirect($this->getController()->createUrl('/admin/question/answeroptions/' . $surveyid . '/' . $gid . '/' . $qid));
        }

        //$action='editansweroptions';

    }

    function _updateSubQuestions($surveyid, $gid, $qid)
    {
        $clang = $this->getController()->lang;
        Yii::app()->loadHelper('database');
        $anslangs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        array_unshift($anslangs, $baselang);

        $row = Questions::model()->getSomeRecords(array('type'), array('qid' => $qid));
        $questiontype = $row['type']; //$connect->GetOne($query);    // Checked
        $qtypes = getqtypelist('', 'array');
        $scalecount = $qtypes[$questiontype]['subquestions'];

        // First delete any deleted ids
        $deletedqids = explode(' ', trim($_POST['deletedqids']));

        foreach ($deletedqids as $deletedqid)
        {
            $deletedqid = (int)$deletedqid;
            if ($deletedqid > 0) { // don't remove undefined
                $aresult = Questions::model()->delete(array('qid' => $deletedqid));
                $databaseoutput['aresult'] = $aresult;
            }
        }

        //Determine ids by evaluating the hidden field
        $rows = array();
        $codes = array();
        $oldcodes = array();
        foreach ($_POST as $postkey => $postvalue)
        {
            $postkey = explode('_', $postkey);
            if ($postkey[0] == 'answer') {
                $rows[$postkey[3]][$postkey[1]][$postkey[2]] = $postvalue;
            }
            if ($postkey[0] == 'code') {
                $codes[$postkey[2]][] = $postvalue;
            }
            if ($postkey[0] == 'oldcode') {
                $oldcodes[$postkey[2]][] = $postvalue;
            }
        }

        $count = 0;
        $invalidCode = 0;
        $duplicateCode = 0;
        $dupanswers = array();
        /*
        for ($scale_id=0;$scale_id<$scalecount;$scale_id++)
        {

        // Find duplicate codes and add these to dupanswers array
        $foundCat=array_count_values($codes);
        foreach($foundCat as $key=>$value){
        if($value>=2){
        $dupanswers[]=$key;
        }
        }
        }
        */
        //require_once("../classes/inputfilter/class.inputfilter_clean.php");
        //$myFilter = new InputFilter('','',1,1,1);

        $insertqids = array();
        for ($scale_id = 0; $scale_id < $scalecount; $scale_id++)
        {
            foreach ($anslangs as $language)
            {
                $position = 0;
                foreach ($rows[$scale_id][$language] as $subquestionkey => $subquestionvalue)
                {
                    if (substr($subquestionkey, 0, 3) != 'new') {
                        $position = position + 1;
                        Questions::model()->update(array('question_order' => $position, 'title' => $codes[$scale_id][$position], 'question' => $subquestionvalue, 'scale_id' => $scale_id), array('qid' => $subquestionkey, 'language' => $language));

                        if (isset($oldcodes[$scale_id][$position]) && $codes[$scale_id][$position] !== $oldcodes[$scale_id][$position]) {

                            Conditions::model()->update(array('cfieldname' => '+' . $surveyid . 'X' . $gid . 'X' . $qid . $codes[$scale_id][$position]), array('cqid' => $qid, 'cfieldname' => '+' . $surveyid . 'X' . $gid . 'X' . $qid . $oldcodes[$scale_id][$position]));

                            Conditions::model()->update(array('value' => $codes[$scale_id][$position]), array('cqid' => $qid, 'cfieldname' => $surveyid . 'X' . $gid . 'X' . $qid, 'value' => $oldcodes[$scale_id][$position]));
                        }

                    }
                    else
                    {
                        if (!isset($insertqid[$position])) {
                            Questions::model()->insertRecords(array('sid' => $surveyid, 'gid' => $gid, 'question_order' => $position + 1, 'title' => $codes[$scale_id][$position], 'question' => $subquestionvalue, 'parent_qid' => $qid, 'language' => $language, 'scale_id' => $scale_id));
                            $insertqid[$position] = Yii::app()->db->getLastInsertID(); //$connect->Insert_Id(db_table_name_nq('questions'),"qid");

                        }
                        else
                        {
                            db_switchIDInsert('questions', true);
                            Questions::model()->insertRecords(array('qid' => $insertqid[$position], 'sid' => $surveyid, 'gid' => $gid, 'question_order' => $position + 1, 'title' => $codes[$scale_id][$position], 'question' => $subquestionvalue, 'parent_qid' => $qid, 'language' => $language, 'scale_id' => $scale_id));
                            db_switchIDInsert('questions', true);
                        }
                    }
                    $position++;
                }

            }
        }
        //include("surveytable_functions.php");
        //surveyFixColumns($surveyid);
        $this->session->set_userdata('flashmessage', $clang->gT("Subquestions were successfully saved."));

        //$action='editsubquestions';

        if ($databaseoutput != '') {
            $this->getController()->render('/admin/database_view', $databaseoutput);
        }
        else
        {
            $this->getController()->redirect($this->getController()->createUrl('/admin/question/subquestions/' . $surveyid . '/' . $gid . '/' . $qid));
        }
    }

    private function _insertCopyQuestions($postgid, $surveyid, $gid, $qid, $action)
    {

        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        $strlen = $_POST['title'];
        if ($strlen < 1) {
            $databaseoutput ['strlen'] = $strlen;
            $this->getController()->render('/admin/database_view', $databaseoutput);
        }
        else
        {
            if (!isset($_POST['lid']) || $_POST['lid'] == '') {
                $_POST['lid'] = "0";
            }
            if (!isset($_POST['lid1']) || $_POST['lid1'] == '') {
                $_POST['lid1'] = "0";
            }
            if (!empty($_POST['questionposition'])) {
                //Bug Fix: remove +1 ->  $question_order=(sanitize_int($_POST['questionposition'])+1);
                $question_order = (sanitize_int($_POST['questionposition']));
                //Need to renumber all questions on or after this
                Questions::model()->update(array('question_order' => $question_order + 1), array('gid' => $gid, 'question_order >=' => $question_order));

            } else {
                $question_order = (getMaxquestionorder($gid, $surveyid));
                $question_order++;
            }

            $_POST['title'] = html_entity_decode($_POST['title'], ENT_QUOTES, "UTF-8");
            $_POST['question_' . $baselang] = html_entity_decode($_POST['question_' . $baselang], ENT_QUOTES, "UTF-8");
            $_POST['help_' . $baselang] = html_entity_decode($_POST['help_' . $baselang], ENT_QUOTES, "UTF-8");

            $purifier = new CHtmlPurifier();

            // Fix bug with FCKEditor saving strange BR types
            if (Yii::app()->getConfig('filterxsshtml')) {
                $_POST['title'] = $purifier->purify($_POST['title']);
                $_POST['question_' . $baselang] = $purifier->purify($_POST['question_' . $baselang]);
                $_POST['help_' . $baselang] = $purifier->purify($_POST['help_' . $baselang]);
            }
            else
            {
                $_POST['title'] = fix_FCKeditor_text($_POST['title']);
                $_POST['question_' . $baselang] = fix_FCKeditor_text($_POST['question_' . $baselang]);
                $_POST['help_' . $baselang] = fix_FCKeditor_text($_POST['help_' . $baselang]);
            }
            //$_POST  = array_map('db_quote', $_POST);

            $data = array(
                'sid' => $surveyid,
                'gid' => $gid,
                'type' => $_POST['type'],
                'title' => $_POST['title'],
                'question' => $_POST['question_' . $baselang],
                'preg' => $_POST['preg'],
                'help' => $_POST['help_' . $baselang],
                'other' => $_POST['other'],
                'mandatory' => $_POST['mandatory'],
                'question_order' => $question_order,
                'language' => $baselang
            );

            $result = Questions::model()->insertRecords($data);


            /**
            $query = "INSERT INTO ".db_table_name('questions')." (sid, gid, type, title, question, preg, help, other, mandatory, question_order, language)"
            ." VALUES ('{$postsid}', '{$postgid}', '{$_POST['type']}', '{$_POST['title']}',"
            ." '{$_POST['question_'.$baselang]}', '{$_POST['preg']}', '{$_POST['help_'.$baselang]}', '{$_POST['other']}', '{$_POST['mandatory']}', $question_order,'{$baselang}')";
             */
            //$result = $connect->Execute($query);  // Checked
            // Get the last inserted questionid for other languages
            $qid = Yii::app()->db->getLastInsertID(); //$connect->Insert_ID(db_table_name_nq('questions'),"qid");

            // Add other languages
            if ($result) {
                $addlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
                foreach ($addlangs as $alang)
                {
                    if ($alang != "") {
                        $data = array(
                            'qid' => $qid,
                            'sid' => $surveyid,
                            'gid' => $gid,
                            'type' => $_POST['type'],
                            'title' => $_POST['title'],
                            'question' => $_POST['question_' . $alang],
                            'preg' => $_POST['preg'],
                            'help' => $_POST['help_' . $alang],
                            'other' => $_POST['other'],
                            'mandatory' => $_POST['mandatory'],
                            'question_order' => $question_order,
                            'language' => $alang
                        );

                        $result2 = Questions::model()->insertRecords($data);


                        /**
                        $query = "INSERT INTO ".db_table_name('questions')." (qid, sid, gid, type, title, question, preg, help, other, mandatory, question_order, language)"
                        ." VALUES ('$qid','{$postsid}', '{$postgid}', '{$_POST['type']}', '{$_POST['title']}',"
                        ." '{$_POST['question_'.$alang]}', '{$_POST['preg']}', '{$_POST['help_'.$alang]}', '{$_POST['other']}', '{$_POST['mandatory']}', $question_order,'{$alang}')";
                        $result2 = $connect->Execute($query);  // Checked */
                        if (!$result2) {
                            $databaseoutput['result2'] = $result2;
                            $this->getController()->render('/admin/database_view', $databaseoutput);

                        }
                    }
                }
            }
            if (!$result) {
                $databaseoutput['result3'] = $result;
                $this->getController()->render('/admin/database_view', $databaseoutput);

            } else {
                if ($action == 'copyquestion') {
                    if (returnglobal('copysubquestions') == "Y") {
                        $aSQIDMappings = array();
                        $r1 = Questions::model()->getSubQuestions(returnglobal('oldqid'));

                        while ($qr1 = $r1->read())
                        {
                            $qr1['parent_qid'] = $qid;
                            if (isset($aSQIDMappings[$qr1['qid']])) {
                                $qr1['qid'] = $aSQIDMappings[$qr1['qid']];
                            } else {
                                $oldqid = $qr1['qid'];
                                unset($qr1['qid']);
                            }
                            $qr1['gid'] = $postgid;
                            $ir1 = Questions::model()->insertRecords($qr1);
                            if (!isset($qr1['qid'])) {
                                $aSQIDMappings[$oldqid] = Yii::app()->db->getLastInsertID('qid');
                            }
                        }
                    }
                    if (returnglobal('copyanswers') == "Y") {
                        $r1 = Answers::model()->getAnswers(returnglobal('oldqid'));
                        while ($qr1 = $r1->read())
                        {
                            Answers::model()->insertRecords(array(
                                                                 'qid' => $qid,
                                                                 'code' => $qr1['code'],
                                                                 'answer' => $qr1['answer'],
                                                                 'sortorder' => $qr1['sortorder'],
                                                                 'language' => $qr1['language'],
                                                                 'scale_id' => $qr1['scale_id']
                                                            ));
                        }
                    }
                    if (returnglobal('copyattributes') == "Y") {
                        $r1 = Question_attributes::model()->getQuestionAttributes(returnglobal('oldqid'));
                        while ($qr1 = $r1->read())
                        {
                            Question_attributes::model()->insertRecords(array(
                                                                             'qid' => $qid,
                                                                             'attribute' => $qr1['attribute'],
                                                                             'value' => $qr1['value']
                                                                        ));
                        }
                    }
                } else {
                    $qattributes = questionAttributes();
                    $validAttributes = $qattributes[$_POST['type']];
                    foreach ($validAttributes as $validAttribute)
                    {
                        if (isset($_POST[$validAttribute['name']])) {
                            $data = array(
                                'qid' => $qid,
                                'value' => $_POST[$validAttribute['name']],
                                'attribute' => $validAttribute['name']
                            );

                            Question_attributes::model()->insertRecords($data);

                        }
                    }
                }

                fixsortorderQuestions($gid, $surveyid);
                Yii::app()->session['flashmessage'] = Yii::app()->lang->gT("Question was successfully added.");

            }

        }

        if ($databaseoutput != '') {
            $this->getController()->render('/admin/database_view', $databaseoutput);
        }
        else
        {
            $this->getController()->redirect($this->getController()->createUrl('admin/survey/sa/view/surveyid/' . $surveyid . '/gid/' . $gid . '/qid/' . $qid));
        }

    }

    function _updateQuestion($surveyid, $gid, $qid)
    {
        Yii::app()->loadHelper('expressions/em_manager');
        $cqresult = Questions::model()->getSomeRecords(array('type', 'gid'), array('qid' => $qid));
        $cqr = $cqresult->read();
        $oldtype = $cqr['type'];
        $oldgid = $cqr['gid'];

        // Remove invalid question attributes on saving
        $qattributes = questionAttributes();
        $data = "qid='{$qid}' and";
        if (isset($qattributes[$_POST['type']])) {
            $validAttributes = $qattributes[$_POST['type']];
            foreach ($validAttributes as $validAttribute)
            {
                $data .= 'attribute<>\'' . $validAttribute['name'] . "' and ";
            }
        }
        $data .= '1=1';
        Question_attributes::model()->delete($data);
        $aLanguages = array_merge(array(GetBaseLanguageFromSurveyID($surveyid)), GetAdditionalLanguagesFromSurveyID($surveyid));


        //now save all valid attributes
        $validAttributes = $qattributes[$_POST['type']];
        // if there are conditions, create a relevance equation, over-writing any default relevance value
        $cond2rel = LimeExpressionManager::ConvertConditionsToRelevance($surveyid, $qid);
        if (!is_null($cond2rel)) {
            $_POST['relevance'] = $cond2rel;
        }

        foreach ($validAttributes as $validAttribute)
        {
            if ($validAttribute['i18n']) {
                foreach ($aLanguages as $sLanguage)
                {
                    if (isset($_POST[$validAttribute['name'] . '_' . $sLanguage])) {
                        $value = sanatize_paranoid_string($_POST[$validAttribute['name'] . '_' . $sLanguage]); //sanitize is SPELLED WRONG

                        $result = Question_attributes::model()->getSomeRecords(array('qaid'), array('qid' => $qid, 'attribute' => $validAttribute['name'], 'language' => $sLanguage));
                        if ($result->getRowCount() > 0) {
                            Question_attributes::model()->update(array('value' => $value), array('attribute' => $validAttribute['name'], 'qid' => $qid, 'language' => $sLanguage));
                        }
                        else
                        {
                            Question_attributes::model()->insertRecords(array('qid' => $qid, 'value' => $value, 'attribute' => $validAttribute['name'], 'language' => $sLanguage));
                        }
                    }
                }
            }
            else
            {
                if (isset($_POST[$validAttribute['name']])) {

                    $result = Question_attributes::model()->getSomeRecords(array('qaid'), array('attribute' => $validAttribute['name'], 'qid' => $qid));
                    $value = sanitize_string_paranoid($_POST[$validAttribute['name']]);
                    if ($result->getRowCount() > 0) {
                        Question_attributes::model()->update(array('value' => $value, 'language' => NULL), array('attribute' => $validAttribute['name'], 'qid' => $qid));
                    }
                    else
                    {
                        Question_attributes::model()->insertRecords(array('qid' => $qid, 'value' => $value, 'attribute' => $validAttribute['name']));
                    }
                }
            }
        }


        $qtypes = getqtypelist('', 'array');
        // These are the questions types that have no answers and therefore we delete the answer in that case
        $iAnswerScales = $qtypes[$_POST['type']]['answerscales'];
        $iSubquestionScales = $qtypes[$_POST['type']]['subquestions'];

        // These are the questions types that have the other option therefore we set everything else to 'No Other'
        if (($_POST['type'] != "L") && ($_POST['type'] != "!") && ($_POST['type'] != "P") && ($_POST['type'] != "M")) {
            $_POST['other'] = 'N';
        }

        // These are the questions types that have no validation - so zap it accordingly

        if ($_POST['type'] == "!" || $_POST['type'] == "L" || $_POST['type'] == "M" || $_POST['type'] == "P" ||
            $_POST['type'] == "F" || $_POST['type'] == "H" || $_POST['type'] == ":" || $_POST['type'] == ";" ||
            $_POST['type'] == "X" || $_POST['type'] == ""
        ) {
            $_POST['preg'] = '';
        }

        // These are the questions types that have no mandatory property - so zap it accordingly
        if ($_POST['type'] == "X" || $_POST['type'] == "|") {
            $_POST['mandatory'] = 'N';
        }


        if ($oldtype != $_POST['type']) {
            // TMSW Conditions->Relevance:  Do similar check via EM, but do allow such a change since will be easier to modify relevance
            //Make sure there are no conditions based on this question, since we are changing the type
            $ccresult = Conditions::model()->getAll(array('cqid' => $qid));
            $cccount = count($ccresult);
            $databaseoutput['cccount'] = $cccount;
            foreach ($ccresult->readAll() as $ccr) {
                $qidarray[] = $ccr['qid'];
            }
            if (isset($qidarray) && $qidarray) {
                $qidlist = implode(", ", $qidarray);
            }
        }
        if (isset($cccount) && $cccount) {
            $databaseoutput['cccount'] = $cccount;
        }
        else
        {
            if (isset($gid) && $gid != "") {

                // TMSW Conditions->Relevance:  not needed?
                $array_result = checkMovequestionConstraintsForConditions(sanitize_int($surveyid), sanitize_int($qid), sanitize_int($gid));
                // If there is no blocking conditions that could prevent this move

                if (is_null($array_result['notAbove']) && is_null($array_result['notBelow'])) {

                    $questlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
                    $baselang = GetBaseLanguageFromSurveyID($surveyid);
                    array_push($questlangs, $baselang);
                    $p = new CHtmlPurifier();
                    if (Yii::app()->getConfig('filterxsshtml'))
                        $_POST['title'] = $p->purify($_POST['title']);
                    else
                        $_POST['title'] = html_entity_decode($_POST['title'], ENT_QUOTES, "UTF-8");

                    // Fix bug with FCKEditor saving strange BR types
                    $_POST['title'] = fix_FCKeditor_text($_POST['title']);
                    foreach ($questlangs as $qlang)
                    {
                        if (Yii::app()->getConfig('filterxsshtml')) {
                            $_POST['question_' . $qlang] = $p->purify($_POST['question_' . $qlang]);
                            $_POST['help_' . $qlang] = $p->purify($_POST['help_' . $qlang]);
                        }
                        else
                        {
                            $_POST['question_' . $qlang] = html_entity_decode($_POST['question_' . $qlang], ENT_QUOTES, "UTF-8");
                            $_POST['help_' . $qlang] = html_entity_decode($_POST['help_' . $qlang], ENT_QUOTES, "UTF-8");
                        }

                        // Fix bug with FCKEditor saving strange BR types
                        $_POST['question_' . $qlang] = fix_FCKeditor_text($_POST['question_' . $qlang]);
                        $_POST['help_' . $qlang] = fix_FCKeditor_text($_POST['help_' . $qlang]);

                        if (isset($qlang) && $qlang != "") { // ToDo: Sanitize the POST variables !

                            $udata = array(
                                'type' => $_POST['type'],
                                'title' => $_POST['title'],
                                'question' => $_POST['question_' . $qlang],
                                'preg' => $_POST['preg'],
                                'help' => $_POST['help_' . $qlang],
                                'gid' => $gid,
                                'other' => $_POST['other'],
                                'mandatory' => $_POST['mandatory'],
                                'relevance' => $_POST['relevance'],
                            );

                            if ($oldgid != $gid) {

                                if (getGroupOrder($surveyid, $oldgid) > getGroupOrder($surveyid, $gid)) {
                                    // TMSW Conditions->Relevance:  What is needed here?

                                    // Moving question to a 'upper' group
                                    // insert question at the end of the destination group
                                    // this prevent breaking conditions if the target qid is in the dest group
                                    $insertorder = getMaxquestionorder($gid, $surveyid) + 1;
                                    $udata = array_merge($udata, array('question_order' => $insertorder));
                                }
                                else
                                {
                                    // Moving question to a 'lower' group
                                    // insert question at the beginning of the destination group
                                    shiftorderQuestions($surveyid, $gid, 1); // makes 1 spare room for new question at top of dest group
                                    $udata = array_merge($udata, array('question_order' => 0));
                                }
                            }
                            $uqresult = Questions::model()->update(array('sid' => $surveyid, 'qid' => $qid, 'language' => $qlang), $udata);
                            $databaseoutput['uqresult'] = $uqresult;
                        }
                    }


                    // Update the group ID on subquestions, too
                    if ($oldgid != $gid) {

                        Questions::model()->update(array('gid' => $gid), array('gid' => $oldgid, 'parent_qid >' => 0));

                        // if the group has changed then fix the sortorder of old and new group
                        fixsortorderQuestions($oldgid, $surveyid);
                        fixsortorderQuestions($gid, $surveyid);
                        // If some questions have conditions set on this question's answers
                        // then change the cfieldname accordingly
                        fixmovedquestionConditions($qid, $oldgid, $gid);
                    }
                    if ($oldtype != $_POST['type']) {
                        Questions::model()->update(array('type' => sanitize_paranoid_string($_POST['type'])), array('parent_qid' => $qid));
                    }

                    Answers::model()->delete(array('qid' => $qid, 'scale_id >=' => $iAnswerScales));

                    // Remove old subquestion scales
                    Answers::model()->delete(array('parent_qid' => $qid, 'scale_id' >= $iSubquestionScales));
                }
                else
                {
                    $flag = "y";

                    // TMSW Conditions->Relevance:  not needed since such a move is no longer an error?

                    // There are conditions constraints: alert the user

                    $errormsg = "";
                    $clang = Yii::app()->lang;
                    if (!is_null($array_result['notAbove'])) {
                        $errormsg .= $clang->gT("This question relies on other question's answers and can't be moved above groupId:", "js")
                                     . " " . $array_result['notAbove'][0][0] . " " . $clang->gT("in position", "js") . " " . $array_result['notAbove'][0][1] . "\\n"
                                     . $clang->gT("See conditions:") . "\\n";

                        foreach ($array_result['notAbove'] as $notAboveCond)
                        {
                            $errormsg .= "- cid:" . $notAboveCond[3] . "\\n";
                        }

                    }
                    if (!is_null($array_result['notBelow'])) {
                        $errormsg .= $clang->gT("Some questions rely on this question's answers. You can't move this question below groupId:", "js")
                                     . " " . $array_result['notBelow'][0][0] . " " . $clang->gT("in position", "js") . " " . $array_result['notBelow'][0][1] . "\\n"
                                     . $clang->gT("See conditions:") . "\\n";

                        foreach ($array_result['notBelow'] as $notBelowCond)
                        {
                            $errormsg .= "- cid:" . $notBelowCond[3] . "\\n";
                        }
                    }


                    $gid = $oldgid; // group move impossible ==> keep display on oldgid

                    $databaseoutput['flag'] = $flag;
                    $databaseoutput['errormsg'] = $errormsg;
                }
            }

        }

        if ($databaseoutput != '') {
            $this->getController()->render('/admin/database_view', $databaseoutput);
        }
        else
        {
            $this->getController()->redirect($this->getController()->createUrl('admin/survey/view/surveyid/' . $surveyid . '/gid/' . $gid . '/qid/' . $qid));
        }

    }

    function _updateSurveyLocaleSettings($postsid, $surveyid)
    {

        $languagelist = GetAdditionalLanguagesFromSurveyID($surveyid);
        $languagelist[] = GetBaseLanguageFromSurveyID($surveyid);

        Yii::app()->loadHelper('database');

        foreach ($languagelist as $langname)
        {
            if ($langname) {
                $url = CHttpRequest::getPost('url_' . $langname);
                if ($url == 'http://') {
                    $url = "";
                }

                // Clean XSS attacks
                if (Yii::app()->getConfig('filterxsshtml')) {
                    $purifier = new CHtmlPurifier();
                    $purifier->options = array(
                        'HTML.Allowed' => 'p,a[href],b,i'
                    );
                    $short_title = $purifier->purify(CHttpRequest::getPost('short_title_' . $langname));
                    $description = $purifier->purify(CHttpRequest::getPost('description_' . $langname));
                    $welcome = $purifier->purify(CHttpRequest::getPost('welcome_' . $langname));
                    $endtext = $purifier->purify(CHttpRequest::getPost('endtext_' . $langname));
                    $sURLDescription = $purifier->purify(CHttpRequest::getPost('urldescrip_' . $langname));
                    $sURL = $purifier->purify(CHttpRequest::getPost('url_' . $langname));
                }
                else
                {
                    $short_title = html_entity_decode(CHttpRequest::getPost('short_title_' . $langname), ENT_QUOTES, "UTF-8");
                    $description = html_entity_decode(CHttpRequest::getPost('description_' . $langname), ENT_QUOTES, "UTF-8");
                    $welcome = html_entity_decode(CHttpRequest::getPost('welcome_' . $langname), ENT_QUOTES, "UTF-8");
                    $endtext = html_entity_decode(CHttpRequest::getPost('endtext_' . $langname), ENT_QUOTES, "UTF-8");
                    $sURLDescription = html_entity_decode(CHttpRequest::getPost('urldescrip_' . $langname), ENT_QUOTES, "UTF-8");
                    $sURL = html_entity_decode(CHttpRequest::getPost('url_' . $langname), ENT_QUOTES, "UTF-8");
                }

                // Fix bug with FCKEditor saving strange BR types
                //$short_title = CHttpRequest::getPost('short_title_' . $langname);
                //$description = CHttpRequest::getPost('description_' . $langname);
                //$welcome = CHttpRequest::getPost('welcome_' . $langname);
                //$endtext = CHttpRequest::getPost('endtext_' . $langname);

                $short_title = fix_FCKeditor_text($short_title);
                $description = fix_FCKeditor_text($description);
                $welcome = fix_FCKeditor_text($welcome);
                $endtext = fix_FCKeditor_text($endtext);

                $data = array(
                    'surveyls_title' => $short_title,
                    'surveyls_description' => $description,
                    'surveyls_welcometext' => $welcome,
                    'surveyls_endtext' => $endtext,
                    'surveyls_url' => $sURL,
                    'surveyls_urldescription' => $sURLDescription,
                    'surveyls_dateformat' => CHttpRequest::getPost('dateformat_' . $langname),
                    'surveyls_numberformat' => CHttpRequest::getPost('numberformat_' . $langname)
                );
                //In 'surveyls_survey_id' => $surveyid, it was initially $postsid. returnglobal not working properly!

                $usresult = Surveys_languagesettings::model()->update($data, array('surveyls_survey_id' => $postsid, 'surveyls_language' => $langname));

            }
        }
        Yii::app()->session['flashmessage'] = Yii::app()->lang->gT("Survey text elements successfully saved.");

        //        if ($databaseoutput != '') {
        //            $this->getController()->render('/admin/database_view', $databaseoutput);
        //        }
        //        else
        //        {
        $this->getController()->redirect($this->getController()->createUrl('admin/survey/view/' . $surveyid));
        //        }
    }

    function _updateSurveySettingsAndEditLocaleSettings($surveyid)
    {

        $this->yii->loadHelper('surveytranslator');
        $this->yii->loadHelper('database');
        $formatdata = getDateFormatData($this->yii->session['dateformat']);

        $expires = $_POST['expires'];
        if (trim($expires) == "") {
            $expires = null;
        }
        else
        {
            $this->yii->loadLibrary('Date_Time_Converter');
            $datetimeobj = new date_time_converter(array($expires, $formatdata['phpdate'] . ' H:i')); //new Date_Time_Converter($expires, $formatdata['phpdate'].' H:i');
            $expires = $datetimeobj->convert("Y-m-d H:i:s");
        }
        $startdate = $_POST['startdate'];
        if (trim($startdate) == "") {
            $startdate = null;
        }
        else
        {
            $this->yii->loadLibrary('Date_Time_Converter');
            $datetimeobj = new date_time_converter(array($startdate, $formatdata['phpdate'] . ' H:i')); //new Date_Time_Converter($startdate,$formatdata['phpdate'].' H:i');
            $startdate = $datetimeobj->convert("Y-m-d H:i:s");
        }

        //make sure only numbers are passed within the $_POST variable
        $tokenlength = (int)$_POST['tokenlength'];
        //$_POST['tokenlength'] = (int) $_POST['tokenlength'];

        //token length has to be at least 5, otherwise set it to default (15)
        if ($tokenlength < 5) {
            $tokenlength = 15;
        }


        CleanLanguagesFromSurvey($surveyid, $_POST['languageids']);

        FixLanguageConsistency($surveyid, $_POST['languageids']);
        $template = $_POST['template'];

        if ($this->yii->session['USER_RIGHT_SUPERADMIN'] != 1 && $this->yii->session['USER_RIGHT_MANAGE_TEMPLATE'] != 1 && !hasTemplateManageRights($this->yii->session['loginID'], $template)) $template = "default";


        $aURLParams = json_decode($_POST['allurlparams'], true);

        Survey_url_parameters::model()->delete(array('sid' => $surveyid));

        foreach ($aURLParams as $aURLParam)
        {
            $aURLParam['parameter'] = trim($aURLParam['parameter']);
            if ($aURLParam['parameter'] == '' || !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $aURLParam['parameter']) || $aURLParam['parameter'] == 'sid' || $aURLParam['parameter'] == 'newtest' || $aURLParam['parameter'] == 'token' || $aURLParam['parameter'] == 'lang') {
                continue; // this parameter name seems to be invalid - just ignore it
            }
            unset($aURLParam['act']);
            unset($aURLParam['title']);
            unset($aURLParam['id']);
            if ($aURLParam['targetqid'] == '') $aURLParam['targetqid'] = 'NULL';
            if ($aURLParam['targetsqid'] == '') $aURLParam['targetsqid'] = 'NULL';
            $aURLParam['sid'] = $surveyid;

            Survey_url_parameters::model()->insertRecords(array('sid' => $aURLParam[sid], 'parameter' => $aURLParam[parameter], 'targetqid' => $aURLParam[targetqid], 'targetsqid' => $aURLParam[targetsqid]));
        }
        $updatearray = array('admin' => $_POST['admin'],
                             'expires' => $expires,
                             'adminemail' => $_POST['adminemail'],
                             'startdate' => $startdate,
                             'bounce_email' => $_POST['bounce_email'],
                             'anonymized' => $_POST['anonymized'],
                             'faxto' => $_POST['faxto'],
                             'format' => $_POST['format'],
                             'savetimings' => $_POST['savetimings'],
                             'template' => $template,
                             'assessments' => $_POST['assessments'],
                             'language' => $_POST['language'],
                             'additional_languages' => $_POST['languageids'],
                             'datestamp' => $_POST['datestamp'],
                             'ipaddr' => $_POST['ipaddr'],
                             'refurl' => $_POST['refurl'],
                             'publicgraphs' => $_POST['publicgraphs'],
                             'usecookie' => $_POST['usecookie'],
                             'allowregister' => $_POST['allowregister'],
                             'allowsave' => $_POST['allowsave'],
                             'navigationdelay' => $_POST['navigationdelay'],
                             'printanswers' => $_POST['printanswers'],
                             'publicstatistics' => $_POST['publicstatistics'],
                             'autoredirect' => $_POST['autoredirect'],
                             'showXquestions' => $_POST['showXquestions'],
                             'showgroupinfo' => $_POST['showgroupinfo'],
                             'showqnumcode' => $_POST['showqnumcode'],
                             'shownoanswer' => $_POST['shownoanswer'],
                             'showwelcome' => $_POST['showwelcome'],
                             'allowprev' => $_POST['allowprev'],
                             'allowjumps' => $_POST['allowjumps'],
                             'nokeyboard' => $_POST['nokeyboard'],
                             'showprogress' => $_POST['showprogress'],
                             'listpublic' => $_POST['public'],
                             'htmlemail' => $_POST['htmlemail'],
                             'sendconfirmation' => 'N',
                             'tokenanswerspersistence' => $_POST['tokenanswerspersistence'],
                             'alloweditaftercompletion' => $_POST['alloweditaftercompletion'],
                             'usecaptcha' => $_POST['usecaptcha'],
                             'emailresponseto' => trim($_POST['emailresponseto']),
                             'emailnotificationto' => trim($_POST['emailnotificationto']),
                             'tokenlength' => $tokenlength
        );

        $condition = 'sid = \'' . $surveyid . '\'';
        Survey::model()->updateSurvey($updatearray, $condition);
        $sqlstring = "surveyls_survey_id='{$surveyid}' ";

        foreach (GetAdditionalLanguagesFromSurveyID($surveyid) as $langname)
        {
            if ($langname) {
                $sqlstring .= "AND surveyls_language <> '" . $langname . "' ";
            }
        }

        // Add base language too
        $sqlstring .= "AND surveyls_language <> '" . GetBaseLanguageFromSurveyID($surveyid) . "' ";
        $usresult = Surveys_languagesettings::model()->delete($sqlstring);

        foreach (GetAdditionalLanguagesFromSurveyID($surveyid) as $langname)
        {
            if ($langname) {
                $usresult = Surveys_languagesettings::model()->getAllRecords(array('surveyls_survey_id' => $surveyid, 'surveyls_language' => $langname));
                if ($usresult->getRowCount() == 0) {
                    $bplang = $this->getController()->lang;
                    $aDefaultTexts = aTemplateDefaultTexts($bplang, 'unescaped');
                    if (getEmailFormat($surveyid) == "html") {
                        $ishtml = true;
                        $aDefaultTexts['admin_detailed_notification'] = $aDefaultTexts['admin_detailed_notification_css'] . $aDefaultTexts['admin_detailed_notification'];
                    }
                    else
                    {
                        $ishtml = false;
                    }
                    $languagedetails = getLanguageDetails($langname);

                    $insertdata = array(
                        'surveyls_survey_id' => $surveyid,
                        'surveyls_language' => $langname,
                        'surveyls_title' => '',
                        'surveyls_email_invite_subj' => $aDefaultTexts['invitation_subject'],
                        'surveyls_email_invite' => $aDefaultTexts['invitation'],
                        'surveyls_email_remind_subj' => $aDefaultTexts['reminder_subject'],
                        'surveyls_email_remind' => $aDefaultTexts['reminder'],
                        'surveyls_email_confirm_subj' => $aDefaultTexts['confirmation_subject'],
                        'surveyls_email_confirm' => $aDefaultTexts['confirmation'],
                        'surveyls_email_register_subj' => $aDefaultTexts['registration_subject'],
                        'surveyls_email_register' => $aDefaultTexts['registration'],
                        'email_admin_notification_subj' => $aDefaultTexts['admin_notification_subject'],
                        'email_admin_notification' => $aDefaultTexts['admin_notification'],
                        'email_admin_responses_subj' => $aDefaultTexts['admin_detailed_notification_subject'],
                        'email_admin_responses' => $aDefaultTexts['admin_detailed_notification'],
                        'surveyls_dateformat' => $languagedetails['dateformat']
                    );
                    Surveys_languagesettings::model()->insertNewSurvey($insertdata);
                    unset($bplang);
                }
            }
        }

        if ($usresult) {
            //$surveyselect = getsurveylist();
            $this->yii->session['flashmessage'] = Yii::app()->lang->gT("Survey settings were successfully saved.");
        }
        else
        {
            $databaseoutput = $usresult;
        }
        if ($databaseoutput != '') {
            $this->getController()->render('/admin/database_view', $databaseoutput);
        }
        else
        {
            //redirect(site_url('admin/survey/view/'.$surveyid));

            if ($_POST['action'] == "updatesurveysettingsandeditlocalesettings") {
                $this->getController()->redirect($this->yii->homeUrl . ('/admin/survey/sa/editlocalsettings/surveyid/' . $surveyid));
            }
            else
            {
                $this->getController()->redirect($this->yii->homeUrl . ('/admin/survey/sa/view/surveyid/' . $surveyid));
            }

        }
    }


    /**
     * This is a convenience function to update/delete answer default values. If the given
     * $defaultvalue is empty then the entry is removed from table defaultvalues
     *
     * @param mixed $qid   Question ID
     * @param mixed $scale_id  Scale ID
     * @param mixed $specialtype  Special type (i.e. for  'Other')
     * @param mixed $language     Language (defaults are language specific)
     * @param mixed $defaultvalue    The default value itself
     * @param boolean $ispost   If defaultvalue is from a $_POST set this to true to properly quote things
     */
    private function _updateDefaultValues($qid, $sqid, $scale_id, $specialtype, $language, $defaultvalue)
    {

        if ($defaultvalue == '') // Remove the default value if it is empty
        {
            Defaultvalues::model()->delete(array('sqid' => $sqid, 'qid' => $qid, 'specialtype' => $specialtype, 'scale_id' => $scale_id, 'language' => $language));
        }
        else
        {
            $res = Defaultvalues::model()->getSomeRecords(array('qid'), array('sqid' => $sqid, 'qid' => $qid, 'specialtype' => $specialtype, 'scale_id' => $scale_id, 'language' => $language));
            $exists = count($res);

            if ($exists == 0) {
                Defaultvalues::model()->insertRecords(array('defaultvalue' => $defaultvalue, 'qid' => $qid, 'scale_id' => $scale_id, 'language' => $language, 'specialtype' => $specialtype, 'sqid' => $sqid));
            }
            else
            {
                Defaultvalues::model()->update(array('defaultvalue' => $defaultvalue), array('sqid' => $sqid, 'qid' => $qid, 'specialtype' => $specialtype, 'scale_id' => $scale_id, 'language' => $language));
            }
        }
    }
}
