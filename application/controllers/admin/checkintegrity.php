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
 * $Id: checkintegrity.php 10760 2011-08-23 19:42:04Z aaronschmitz $
 */

/**
 * CheckIntegrity Controller
 *
 * This controller performs database repair functions.
 *
 * @package       LimeSurvey
 * @subpackage    Backend
 */
class CheckIntegrity extends CAction
{

    public function run()
    {
        Yii::app()->loadHelper('database');
        if (isset($_GET['fixredundancy']))
            $this->fixredundancy();
        elseif (isset($_GET['fixintegrity']))
            $this->fixintegrity();
        else
            $this->index();
    }

    public function index()
    {
        if (Yii::app()->session['USER_RIGHT_CONFIGURATOR'] == 1) {
            $aData = $this->_checkintegrity();
            $this->getController()->_getAdminHeader();
            $this->getController()->_showadminmenu();
            $aData['clang'] = Yii::app()->lang;
            $this->getController()->render('/admin/checkintegrity/check_view', $aData);
            $this->getController()->_getAdminFooter('http://docs.limesurvey.org', $this->getController()->lang->gT('LimeSurvey online manual'));
        }
    }

    public function fixredundancy()
    {
        $clang = Yii::app()->lang;

        if (Yii::app()->session['USER_RIGHT_CONFIGURATOR'] == 1 && CHttpRequest::getPost('ok') == 'Y') {
            $aDelete = $this->_checkintegrity();

            if (isset($aDelete['redundanttokentables'])) {
                foreach ($aDelete['redundanttokentables'] as $aTokenTable)
                {
                    Yii::app()->db->createCommand()->dropTable($aTokenTable['table']);
                    $aData['messages'][] = $clang->gT('Deleting token table:') . ' ' . $aTokenTable['table'];
                }
            }

            if (isset($aDelete['redundantsurveytables'])) {
                foreach ($aDelete['redundantsurveytables'] as $aSurveyTable)
                {
                    Yii::app()->db->createCommand()->dropTable($aSurveyTable['table']);
                    $aData['messages'][] = $clang->gT('Deleting survey table:') . ' ' . $aSurveyTable['table'];
                }
            }

            $this->getController()->_getAdminHeader();
            $this->getController()->_showadminmenu();
            $aData['clang'] = $clang;
            $this->getController()->render('/admin/checkintegrity/fix_view', $aData);
            $this->getController()->_getAdminFooter('http://docs.limesurvey.org', Yii::app()->lang->gT('LimeSurvey online manual'));
        }
    }

    public function fixintegrity()
    {
        $aData = array();
        $clang = Yii::app()->lang;
        if (Yii::app()->session['USER_RIGHT_CONFIGURATOR'] == 1 && CHttpRequest::getPost('ok') == 'Y') {
            $aDelete = $this->_checkintegrity();

            // TMSW Conditions->Relevance:  Update this to process relevance instead
            if (isset($aDelete['conditions'])) {
                $aData = $this->_deleteConditions($aDelete['conditions'], $aData, $clang);
            }

            if (isset($aDelete['questionattributes'])) {
                $aData = $this->_deleteQuestionAttributes($aDelete['questionattributes'], $aData, $clang);
            }

            if ($aDelete['defaultvalues']) {
                $aData = $this->_deleteDefaultValues($aData, $clang);
            }

            if ($aDelete['quotas']) {
                $aData = $this->_deleteQuotas($aData, $clang);
            }

            if ($aDelete['quotals']) {
                $this->_deleteQuotaLanguageSettings();
            }

            if ($aDelete['quotamembers']) {
                $aData = $this->_deleteQuotaMembers($aData, $clang);
            }

            if (isset($aDelete['assessments'])) {
                $aData = $this->_deleteAssessments($aDelete['assessments'], $aData, $clang);
            }

            if (isset($aDelete['answers'])) {
                $aData = $this->_deleteAnswers($aDelete['answers'], $aData, $clang);
            }

            if (isset($aDelete['surveys'])) {
                $aData = $this->_deleteSurveys($aDelete['surveys'], $aData, $clang);
            }

            if (isset($aDelete['surveylanguagesettings'])) {
                $aData = $this->_deleteSurveyLanguageSettings($aDelete['surveylanguagesettings'], $aData, $clang);
            }

            if (isset($aDelete['questions'])) {
                $aData = $this->_deleteQuestions($aDelete['questions'], $aData, $clang);
            }


            if (isset($aDelete['groups'])) {
                $aData = $this->_deleteGroups($aDelete['groups'], $aData, $clang);
            }

            if (isset($aDelete['orphansurveytables'])) {
                $aData = $this->_dropOrphanSurveyTables($aDelete['orphansurveytables'], $aData, $clang);
            }

            if (isset($aDelete['orphantokentables'])) {
                $aData = $this->_deleteOrphanTokenTables($aDelete['orphantokentables'], $aData, $clang);
            }

            $this->getController()->_getAdminHeader();
            $this->getController()->_showadminmenu();
            $aData['clang'] = $clang;
            $this->getController()->render('/admin/checkintegrity/fix_view', $aData);
            $this->getController()->_getAdminFooter('http://docs.limesurvey.org', Yii::app()->lang->gT('LimeSurvey online manual'));
        }
    }

    private function _deleteOrphanTokenTables(array $tokenTables, array $aData, Limesurvey_lang $clang)
    {
        foreach ($tokenTables as $aTokenTable)
        {
            Yii::app()->db->createCommand()->dropTable($aTokenTable);
            $aData['messages'][] = $clang->gT('Deleting orphan token table:') . ' ' . $aTokenTable;
        }
        return $aData;
    }

    private function _dropOrphanSurveyTables(array $surveyTables, array $aData, Limesurvey_lang $clang)
    {
        foreach ($surveyTables as $aSurveyTable)
        {
            Yii::app()->db->createCommand()->dropTable($aSurveyTable);
            $aData['messages'][] = $clang->gT('Deleting orphan survey table:') . ' ' . $aSurveyTable;
        }
        return $aData;
    }

    private function _deleteGroups(array $groups, array $aData, Limesurvey_lang $clang)
    {
        foreach ($groups as $group) $gids[] = $group['gid'];

        $criteria = new CDbCriteria;
        $criteria->addInCondition('gid', $gids);
        Groups::model()->deleteAll($criteria);
        if (Groups::model()->hasErrors()) safe_die(Groups::model()->getError());
        $aData['messages'][] = sprintf($clang->gT('Deleting groups: %u groups deleted'), count($groups));
        return $aData;
    }

    private function _deleteQuestions(array $questions, array $aData, Limesurvey_lang $clang)
    {
        foreach ($questions as $question) $qids[] = $question['qid'];

        $criteria = new CDbCriteria;
        $criteria->addInCondition('qid', $qids);
        Questions::model()->deleteAll($criteria);
        if (Questions::model()->hasErrors()) safe_die(Questions::model()->getError());
        $aData['messages'][] = sprintf($clang->gT('Deleting questions: %u questions deleted'), count($questions));
        return array($criteria, $aData);
    }

    private function _deleteSurveyLanguageSettings(array $surveyLanguageSettings, array $aData, Limesurvey_lang $clang)
    {
        foreach ($surveyLanguageSettings as $surveylanguagesetting) $surveyls_survey_ids[] = $surveylanguagesetting['slid'];

        $criteria = new CDbCriteria;
        $criteria->compare('surveyls_survey_id', $surveyls_survey_ids);
        Surveys_languagesettings::model()->deleteAll($criteria);
        if (Surveys_languagesettings::model()->hasErrors()) safe_die(Surveys_languagesettings::model()->getError());
        $aData['messages'][] = sprintf($clang->gT('Deleting survey languagesettings: %u survey languagesettings deleted'), count($surveyLanguageSettings));
        return array($criteria, $aData);
    }

    private function _deleteSurveys(array $surveys, array $aData, Limesurvey_lang $clang)
    {
        foreach ($surveys as $survey) $surveys_ids[] = $survey['sid'];

        Survey::model()->deleteByPk($surveys_ids);
        if (Survey::model()->hasErrors()) safe_die(Survey::model()->getError());
        $aData['messages'][] = sprintf($clang->gT('Deleting surveys: %u surveys deleted'), count($surveys));
        return $aData;
    }

    private function _deleteAnswers(array $answers, array $aData, Limesurvey_lang $clang)
    {
        foreach ($answers as $aAnswer) {
            Answers::model()->deleteByPk($aAnswer);
            if (Answers::model()->hasErrors()) safe_die(Answers::model()->getError());
        }
        $aData['messages'][] = sprintf($clang->gT('Deleting answers: %u answers deleted'), count($answers));
        return $aData;
    }

    private function _deleteAssessments(array $assessments, array $aData, Limesurvey_lang $clang)
    {
        foreach ($assessments as $assessment) $assessments_ids[] = $assessment['id'];

        $assessments_ids = array();
        Assessment::model()->deleteByPk($assessments_ids);
        if (Assessment::model()->hasErrors()) safe_die(Assessment::model()->getError());
        $aData['messages'][] = sprintf($clang->gT('Deleting assessments: %u assessment entries deleted'), count($assessments));
        return $aData;
    }

    private function _deleteQuotaMembers(array $aData, Limesurvey_lang $clang)
    {
        $quota_ids = array();
        $quotas = Quota::model()->findAll();
        foreach ($quotas as $quota) $quota_ids[] = $quota['id'];
        $criteria = new CDbCriteria;
        $criteria->addNotInCondition('quota_id', $quota_ids);

        $qids = array();
        $questions = Questions::model()->findAll();
        foreach ($questions as $question) $qids[] = $question['qid'];
        $criteria->addNotInCondition('qid', $qids, 'OR');

        $sids = array();
        $surveys = Survey::model()->findAll();
        foreach ($surveys as $survey) $sids[] = $survey['sid'];
        $criteria->addNotInCondition('sid', $sids, 'OR');

        Quota_members::model()->deleteAll($criteria);
        if (Quota_languagesettings::model()->hasErrors()) safe_die(Quota_languagesettings::model()->getError());
        $aData['messages'][] = $clang->gT('Deleting orphaned quota members.');
        return $aData;
    }

    private function _deleteQuotaLanguageSettings()
    {
        $quotas = Quota::model()->findAll();
        foreach ($quotas as $quota) $quota_ids[] = $quota['id'];
        $criteria = new CDbCriteria;
        $criteria->addNotInCondition('quotals_quota_id', $quota_ids);

        Quota_languagesettings::model()->deleteAll($criteria);
        if (Quota_languagesettings::model()->hasErrors()) safe_die(Quota_languagesettings::model()->getError());
    }

    private function _deleteQuotas(array $aData, Limesurvey_lang $clang)
    {
        $sids = array();
        $surveys = Survey::model()->findAll();
        foreach ($surveys as $survey) $sids[] = $survey['sid'];
        $criteria = new CDbCriteria;
        $criteria->addNotInCondition('sid', $sids);

        Quota::model()->deleteAll($criteria);
        if (Quota::model()->hasErrors()) safe_die(Quota::model()->getError());
        $aData['messages'][] = $clang->gT('Deleting orphaned quotas.');
        return $aData;
    }

    private function _deleteDefaultValues(array $aData, Limesurvey_lang $clang)
    {
        $qids = array();
        $questions = Questions::model()->findAll();
        foreach ($questions as $question) $qids[] = $question['qid'];
        $criteria = new CDbCriteria;
        $criteria->addNotInCondition('qid', $qids);

        Defaultvalues::model()->deleteAll($criteria);
        if (Defaultvalues::model()->hasErrors()) safe_die(Defaultvalues::model()->getError());
        $aData['messages'][] = $clang->gT('Deleting orphaned default values.');
        return $aData;
    }

    private function _deleteQuestionAttributes(array $questionAttributes, array $aData, Limesurvey_lang $clang)
    {
        $qids = array();
        foreach ($questionAttributes as $questionattribute) $qids[] = $questionattribute['qid'];
        $criteria = new CDbCriteria;
        $criteria->addInCondition('qid', $qids);

        Question_attributes::model()->deleteByPk($criteria);
        if (Question_attributes::model()->hasErrors()) safe_die(Question_attributes::model()->getError());
        $aData['messages'][] = sprintf($clang->gT('Deleting question attributes: %u attributes deleted'), count($questionAttributes));
        return $aData;
    }

    private function _deleteConditions(array $conditions, array $aData, Limesurvey_lang $clang)
    {
        $cids = array();
        foreach ($conditions as $condition) $cids[] = $condition['cid'];

        Conditions::model()->deleteByPk($cids);
        if (Conditions::model()->hasErrors()) safe_die(Conditions::model()->getError());
        $aData['messages'][] = sprintf($clang->gT('Deleting conditions: %u conditions deleted'), count($condition));
        return $aData;
    }


    /**
     * This function checks the LimeSurvey database for logical consistency and returns an according array
     * containing all issues in the particular tables.
     * @returns Array with all found issues.
     */
    protected function _checkintegrity()
    {
        $clang = Yii::app()->lang;

        /*** Plainly delete survey permissions if the survey or user does not exist ***/
        $users = User::model()->findAll();
        $uids = array();
        foreach ($users as $user) $uids[] = $user['uid'];
        $criteria = new CDbCriteria;
        $criteria->addNotInCondition('uid', $uids, 'OR');

        $surveys = Survey::model()->findAll();
        $sids = array();
        foreach ($surveys as $survey) $sids[] = $survey['sid'];
        $criteria->addNotInCondition('sid', $sids, 'OR');

        Survey_permissions::model()->deleteAll($criteria);

        // Fix subquestions
        fixSubquestions();

        /*** Check for active survey tables with missing survey entry and rename them ***/
        $sDBPrefix = Yii::app()->db->tablePrefix;
        $sQuery = db_select_tables_like('{{survey}}\_%');
        $aResult = db_query_or_false($sQuery) or safe_die("Couldn't get list of conditions from database<br />{$sQuery}<br />");
        foreach ($aResult->readAll() as $aRow)
        {
            $sTableName = substr(reset($aRow), strlen($sDBPrefix));
            if ($sTableName == 'survey_permissions' || $sTableName == 'survey_links' || $sTableName == 'survey_url_parameters') continue;
            $iSurveyID = substr($sTableName, strpos($sTableName, '_') + 1);
            $count = $surveys = Survey::model()->findAllByPk($iSurveyID);
            if (Survey::model()->hasErrors()) safe_die(Survey::model()->getError());

            if ($count == 0) {
                $sDate = date('YmdHis') . rand(1, 1000);
                $sOldTable = "survey_{$iSurveyID}";
                $sNewTable = "old_survey_{$iSurveyID}_{$sDate}";
                try {
                    $deactivateresult = Yii::app()->db->createCommand()->renameTable("{{{$sOldTable}}}", "{{{$sNewTable}}}");
                } catch (CDbException $e) {
                    die ('Couldn\'t make backup of the survey table. Please try again. The database reported the following error:<br />' . htmlspecialchars($e) . '<br />');
                }
            }
        }

        /*** Check for active token tables with missing survey entry ***/
        $aResult = db_query_or_false(db_select_tables_like('{{tokens}}\_%')) or safe_die("Couldn't get list of conditions from database<br />{$sQuery}<br />");
        foreach ($aResult->readAll() as $aRow)
        {
            $sTableName = substr(reset($aRow), strlen($sDBPrefix));
            $iSurveyID = substr($sTableName, strpos($sTableName, '_') + 1);
            $count = count(Survey::model()->findAllByPk($iSurveyID));
            if (Survey::model()->hasErrors()) safe_die(Survey::model()->getError());
            if ($count == 0) {
                $sDate = date('YmdHis') . rand(1, 1000);
                $sOldTable = "tokens_{$iSurveyID}";
                $sNewTable = "old_tokens_{$iSurveyID}_{$sDate}";
                try {
                    $deactivateresult = Yii::app()->db->createCommand()->renameTable("{{{$sOldTable}}}", "{{{$sNewTable}}}");
                } catch (CDbException $e) {
                    die ('Couldn\'t make backup of the survey table. Please try again. The database reported the following error:<br />' . htmlspecialchars($e) . '<br />');
                }
            }
        }

        /**********************************************************************/
        /*     Check conditions                                               */
        /**********************************************************************/
        // TMSW Conditions->Relevance:  Replace this with analysis of relevance
        $conditions = Conditions::model()->findAll();
        if (Conditions::model()->hasErrors()) safe_die(Conditions::model()->getError());
        foreach ($conditions as $condition)
        {
            $iRowCount = count(Questions::model()->findAll());
            if (Questions::model()->hasErrors()) safe_die(Questions::model()->getError());

            if (!$iRowCount) {
                $aDelete['conditions'][] = array('cid' => $condition['cid'], 'reason' => 'No matching QID');
            }

            if ($condition['cqid'] != 0) { // skip case with cqid=0 for codnitions on {TOKEN:EMAIL} for instance
                $iRowCount = count(Questions::model()->findAllByPk($condition['cqid']));
                if (Questions::model()->hasErrors()) safe_die(Questions::model()->getError());
                if (!$iRowCount) {
                    $aDelete['conditions'][] = array('cid' => $condition['cid'], 'reason' => $clang->gT('No matching CQID'));
                }
            }
            if ($condition['cfieldname']) //Only do this if there actually is a 'cfieldname'
            {
                if (preg_match('/^\+{0,1}[0-9]+X[0-9]+X*$/', $condition['cfieldname'])) { // only if cfieldname isn't Tag such as {TOKEN:EMAIL} or any other token
                    list ($surveyid, $gid, $rest) = explode('X', $condition['cfieldname']);
                    $iRowCount = count(Groups::model()->findAllByPk($gid));
                    if (Groups::model()->hasErrors()) safe_die(Groups::model()->getError());
                    if (!$iRowCount) $aDelete['conditions'][] = array('cid' => $condition['cid'], 'reason' => $clang->gT('No matching CFIELDNAME group!') . " ($gid) ({$condition['cfieldname']})");
                }
            }
            elseif (!$condition['cfieldname'])
            {
                $aDelete['conditions'][] = array('cid' => $condition['cid'], 'reason' => $clang->gT('No CFIELDNAME field set!') . " ({$condition['cfieldname']})");
            }
        }

        /**********************************************************************/
        /*     Check question attributes                                      */
        /**********************************************************************/
        $question_attributes = Question_attributes::model()->findAll();
        if (Question_attributes::model()->hasErrors()) safe_die(Question_attributes::model()->getError());
        foreach ($question_attributes as $question_attribute)
        {
            $iRowCount = count(Questions::model()->findAllByPk($question_attribute['qid']));
            if (Questions::model()->hasErrors()) safe_die(Questions::model()->getError());
            if (!$iRowCount < 1) {
                $aDelete['questionattributes'][] = array('qid' => $question_attribute['qid']);
            }
        } // foreach


        /**********************************************************************/
        /*     Check default values                                           */
        /**********************************************************************/
        $questions = Questions::model()->findAll();
        if (Questions::model()->hasErrors()) safe_die(Questions::model()->getError());
        $qids = array();
        foreach ($questions as $question) $qids[] = $question['qid'];
        $criteria = new CDbCriteria;
        $criteria->addNotInCondition('qid', $qids);

        $aDelete['defaultvalues'] = count(Defaultvalues::model()->findAll($criteria));
        if (Defaultvalues::model()->hasErrors()) safe_die(Defaultvalues::model()->getError());

        /**********************************************************************/
        /*     Check quotas                                                   */
        /**********************************************************************/
        $surveys = Survey::model()->findAll();
        if (Survey::model()->hasErrors()) safe_die(Survey::model()->getError());
        $sids = array();
        foreach ($surveys as $survey) $sids[] = $survey['sid'];
        $criteria = new CDbCriteria;
        $criteria->addNotInCondition('sid', $sids);

        $aDelete['quotas'] = count(Quota::model()->findAll($criteria));
        if (Quota::model()->hasErrors()) safe_die(Quota::model()->getError());

        /**********************************************************************/
        /*     Check quota languagesettings                                   */
        /**********************************************************************/
        $quotas = Quota::model()->findAll();
        if (Quota::model()->hasErrors()) safe_die(Quota::model()->getError());
        $ids = array();
        foreach ($quotas as $quota) $ids[] = $survey['id'];
        $criteria = new CDbCriteria;
        $criteria->addNotInCondition('quotals_quota_id', $ids);

        $aDelete['quotals'] = count(Quota_languagesettings::model()->findAll($criteria));
        if (Quota_languagesettings::model()->hasErrors()) safe_die(Quota_languagesettings::model()->getError());

        /**********************************************************************/
        /*     Check quota members                                   */
        /**********************************************************************/
        $quotas = Quota::model()->findAll();
        $quota_ids = array();
        foreach ($quotas as $quota) $quota_ids[] = $quota['id'];
        $criteria = new CDbCriteria;
        $criteria->addNotInCondition('quota_id', $quota_ids);

        $questions = Questions::model()->findAll();
        $qids = array();
        foreach ($questions as $question) $qids[] = $question['qid'];
        $criteria->addNotInCondition('qid', $qids, 'OR');

        $surveys = Survey::model()->findAll();
        $sids = array();
        foreach ($surveys as $survey) $sids[] = $survey['sid'];
        $criteria->addNotInCondition('sid', $sids, 'OR');

        $aDelete['quotamembers'] = count(Quota_members::model()->findAll($criteria));
        if (Quota_members::model()->hasErrors()) safe_die(Quota_members::model()->getError());

        /**********************************************************************/
        /*     Check assessments                                              */
        /**********************************************************************/
        $criteria = new CDbCriteria;
        $criteria->compare('scope', 'T');
        $assessments = Assessment::model()->findAll($criteria);
        if (Assessment::model()->hasErrors()) safe_die(Assessment::model()->getError());
        foreach ($assessments as $assessment)
        {
            $iAssessmentCount = count(Survey::model()->findAllByPk($assessment['sid']));
            if (Survey::model()->hasErrors()) safe_die(Survey::model()->getError());
            if (!$iAssessmentCount) {
                $aDelete['assessments'][] = array('id' => $assessment['id'], 'assessment' => $assessment['name'], 'reason' => $clang->gT('No matching survey'));
            }
        }

        $criteria = new CDbCriteria;
        $criteria->compare('scope', 'G');
        $assessments = Assessment::model()->findAll($criteria);
        if (Assessment::model()->hasErrors()) safe_die(Assessment::model()->getError());
        foreach ($assessments as $assessment)
        {
            $iAssessmentCount = count(Groups::model()->findAllByPk($assessment['gid']));
            if (Groups::model()->hasErrors()) safe_die(Groups::model()->getError());
            if (!$iAssessmentCount) {
                $aDelete['assessments'][] = array('id' => $assessment['id'], 'assessment' => $assessment['name'], 'reason' => $clang->gT('No matching group'));
            }
        }
        /**********************************************************************/
        /*     Check answers                                                  */
        /**********************************************************************/
        $answers = Answers::model()->findAll();
        if (Answers::model()->hasErrors()) safe_die(Answers::model()->getError());
        foreach ($answers as $answer)
        {
            $iAnswerCount = count(Questions::model()->findAllByPk($answer['qid']));
            if (Questions::model()->hasErrors()) safe_die(Questions::model()->getError());
            if (!$iAnswerCount) {
                $aDelete['answers'][] = array('qid' => $answer['qid'], 'code' => $answer['code'], 'reason' => $clang->gT('No matching question'));
            }
        }

        /**********************************************************************/
        /*     Check surveys                                                  */
        /**********************************************************************/
        $surveys = Survey::model()->findAll();
        if (Survey::model()->hasErrors()) safe_die(Survey::model()->getError());
        foreach ($surveys as $survey)
        {
            $criteria = new CDbCriteria;
            $criteria->compare('surveyls_survey_id', $survey['sid']);
            $iSurveyLangSettingsCount = count(Surveys_languagesettings::model()->findAll($criteria));
            if (Surveys_languagesettings::model()->hasErrors()) safe_die(Surveys_languagesettings::model()->getError());
            if (!$iSurveyLangSettingsCount) {
                $aDelete['surveys'][] = array('sid' => $survey['sid'], 'reason' => $clang->gT('Language specific settings missing'));
            }
        }

        /**********************************************************************/
        /*     Check survey language settings                                 */
        /**********************************************************************/
        $surveys = Survey::model()->findAll();
        if (Survey::model()->hasErrors()) safe_die(Survey::model()->getError());
        $sids = array();
        foreach ($surveys as $survey) $sids[] = $survey['sid'];
        $criteria = new CDbCriteria;
        $criteria->addNotInCondition('surveyls_survey_id', $sids);
        $surveys_languagesettings = Surveys_languagesettings::model()->findAll($criteria);
        if (Surveys_languagesettings::model()->hasErrors()) safe_die(Surveys_languagesettings::model()->getError());

        foreach ($surveys_languagesettings as $surveys_languagesetting)
        {
            $aDelete['surveylanguagesettings'][] = array('slid' => $surveys_languagesetting['surveyls_survey_id'], 'reason' => $clang->gT('The related survey is missing.'));
        }

        /**********************************************************************/
        /*     Check questions                                                */
        /**********************************************************************/
        $questions = Questions::model()->findAll();
        if (Questions::model()->hasErrors()) safe_die(Questions::model()->getError());
        foreach ($questions as $question)
        {
            //Make sure the group exists
            $criteria = new CDbCriteria;
            $criteria->compare('gid', $question['gid']);
            $iQuestionCount = count(Groups::model()->findAll($criteria));
            if (Groups::model()->hasErrors()) safe_die(Groups::model()->getError());
            if (!$iQuestionCount) {
                $aDelete['questions'][] = array('qid' => $question['qid'], 'reason' => $clang->gT('No matching group') . " ({$question['gid']})");
            }
            //Make sure survey exists
            $iQuestionCount = count(Survey::model()->findAllByPk($question['sid']));
            if (Survey::model()->hasErrors()) safe_die(Survey::model()->getError());
            if (!$iQuestionCount) {
                $aDelete['questions'][] = array('qid' => $question['qid'], 'reason' => $clang->gT('There is no matching survey.') . " ({$question['sid']})");
            }
        }

        /**********************************************************************/
        /*     Check groups                                                   */
        /**********************************************************************/
        $surveys = Survey::model()->findAll();
        if (Survey::model()->hasErrors()) safe_die(Survey::model()->getError());
        $sids = array();
        foreach ($surveys as $survey) $sids[] = $survey['sid'];
        $criteria = new CDbCriteria;
        $criteria->addNotInCondition('sid', $sids);
        $groups = Groups::model()->findAll($criteria);
        foreach ($groups as $group)
        {
            $aDelete['groups'][] = array('gid' => $group['gid'], 'reason' => $clang->gT('There is no matching survey.') . ' SID:' . $group['sid']);
        }

        /**********************************************************************/
        /*     Check old survey tables                                        */
        /**********************************************************************/
        //1: Get list of 'old_survey' tables and extract the survey id
        //2: Check if that survey id still exists
        //3: If it doesn't offer it for deletion
        $sQuery = db_select_tables_like('{{old_survey}}%');
        $aResult = db_query_or_false($sQuery) or safe_die("Couldn't get list of conditions from database<br />$sQuery<br />");
        $aTables = $aResult->readAll();

        $aOldSIDs = array();
        $aSIDs = array();
        foreach ($aTables as $sTable)
        {
            $sTable = reset($sTable);
            list($sOldText, $SurveyText, $iSurveyID, $sDate) = explode('_', substr($sTable, strlen($sDBPrefix)));
            $aOldSIDs[] = $iSurveyID;
            $aFullOldSIDs[$iSurveyID][] = $sTable;
        }
        $aOldSIDs = array_unique($aOldSIDs);
        $sQuery = 'SELECT sid FROM {{surveys}} ORDER BY sid';
        $oResult = db_execute_assoc($sQuery) or safe_die('Couldn\'t get unique survey ids');
        $surveys = Survey::model()->findAll();
        if (Survey::model()->hasErrors()) safe_die(Survey::model()->getError());
        $aSIDs = array();
        foreach ($surveys as $survey)
        {
            $aSIDs[] = $survey['sid'];
        }
        foreach ($aOldSIDs as $iOldSID)
        {
            if (!in_array($iOldSID, $aSIDs)) {
                foreach ($aFullOldSIDs[$iOldSID] as $sTableName)
                {
                    $aDelete['orphansurveytables'][] = $sTableName;
                }
            } else {
                foreach ($aFullOldSIDs[$iOldSID] as $sTableName)
                {
                    $aTableParts = explode('_', substr($sTableName, strlen($sDBPrefix)));
                    if (count($aTableParts) == 4) {
                        $sOldText = $aTableParts[0];
                        $SurveyText = $aTableParts[1];
                        $iSurveyID = $aTableParts[2];
                        $sDateTime = $aTableParts[3];
                        $sType = $clang->gT('responses');
                    } elseif (count($aTableParts) == 5) {
                        //This is a timings table (
                        $sOldText = $aTableParts[0];
                        $SurveyText = $aTableParts[1];
                        $iSurveyID = $aTableParts[2];
                        $sDateTime = $aTableParts[4];
                        $sType = $clang->gT('timings');
                    }
                    $iYear = substr($sDateTime, 0, 4);
                    $iMonth = substr($sDateTime, 4, 2);
                    $iDay = substr($sDateTime, 6, 2);
                    $iHour = substr($sDateTime, 8, 2);
                    $iMinute = substr($sDateTime, 10, 2);
                    $sDate = date('d M Y  H:i', mktime($iHour, $iMinute, 0, $iMonth, $iDay, $iYear));
                    $sQuery = 'SELECT * FROM ' . $sTableName;
                    $oQRresult = db_execute_assoc($sQuery) or safe_die('Failed: ' . $sQuery);
                    $iRecordcount = $oQRresult->count();
                    if ($iRecordcount == 0) { // empty table - so add it to immediate deletion
                        $aDelete['orphansurveytables'][] = $sTableName;
                    } else {
                        $aOldSurveyTableAsk[] = array('table' => $sTableName, 'details' => sprintf($clang->gT('Survey ID %d saved at %s containing %d record(s) (%s)'), $iSurveyID, $sDate, $iRecordcount, $sType));
                    }
                }
            }
        }


        /**********************************************************************/
        /*     CHECK OLD TOKEN  TABLES                                        */
        /**********************************************************************/
        //1: Get list of 'old_token' tables and extract the survey id
        //2: Check if that survey id still exists
        //3: If it doesn't offer it for deletion
        $aResult = db_query_or_false(db_select_tables_like('{{old_token}}%')) or safe_die("Couldn't get list of conditions from database<br />$sQuery<br />");
        $aTables = $aResult->readAll();

        $aOldTokenSIDs = array();
        $aTokenSIDs = array();
        $aFullOldTokenSIDs = array();

        foreach ($aTables as $sTable)
        {
            $sTable = reset($sTable);

            list($sOldText, $SurveyText, $iSurveyID, $sDateTime) = explode('_', substr($sTable, strlen($sDBPrefix)));
            $aTokenSIDs[] = $iSurveyID;
            $aFullOldTokenSIDs[$iSurveyID][] = $sTable;
        }
        $aOldTokenSIDs = array_unique($aOldTokenSIDs);
        $surveys = Survey::model()->findAll();
        if (Survey::model()->hasErrors()) safe_die(Survey::model()->getError());
        $aSIDs = array();
        foreach ($surveys as $survey)
        {
            $aSIDs[] = $survey['sid'];
        }
        foreach ($oResult->readAll() as $aRow)
        {
            $aTokenSIDs[] = $aRow['sid'];
        }
        foreach ($aOldTokenSIDs as $iOldTokenSID)
        {
            if (!in_array($iOldTokenSID, $aTokenSIDs)) {
                foreach ($aFullOldTokenSIDs[$iOldTokenSID] as $sTableName)
                {
                    $aDelete['orphantokentables'][] = $sTableName;
                }
            } else {
                foreach ($aFullOldTokenSIDs[$iOldTokenSID] as $sTableName)
                {
                    list($sOldText, $sTokensText, $iSurveyID, $sDateTime) = explode('_', substr($sTableName, strlen($sDBPrefix)));
                    $iYear = substr($sDateTime, 0, 4);
                    $iMonth = substr($sDateTime, 4, 2);
                    $iDay = substr($sDateTime, 6, 2);
                    $iHour = substr($sDateTime, 8, 2);
                    $iMinute = substr($sDateTime, 10, 2);
                    $sDate = date('D, d M Y  h:i a', mktime($iHour, $iMinute, 0, $iMonth, $iDay, $iYear));
                    $sQuery = 'SELECT * FROM ' . $sTableName;

                    $oQRresult = db_execute_assoc($sQuery) or safe_die('Failed: ' . $sQuery);
                    $iRecordcount = $oQRresult->count();
                    if ($iRecordcount == 0) {
                        $aDelete['orphantokentables'][] = $sTableName;
                    }
                    else
                    {
                        $aOldTokenTableAsk[] = array('table' => $sTableName, 'details' => sprintf($clang->gT('Survey ID %d saved at %s containing %d record(s)'), $iSurveyID, $sDate, $iRecordcount));
                    }
                }
            }
        }

        if ($aDelete['defaultvalues'] == 0 && $aDelete['quotamembers'] == 0 &&
            $aDelete['quotas'] == 0 && $aDelete['quotals'] == 0 && count($aDelete) == 4
        ) {
            $aDelete['integrityok'] = true;
        } else {
            $aDelete['integrityok'] = false;
        }

        if (!isset($aOldTokenTableAsk) && !isset($aOldSurveyTableAsk)) {
            $aDelete['redundancyok'] = true;
        } else {
            $aDelete['redundancyok'] = false;
            $aDelete['redundanttokentables'] = array();
            $aDelete['redundantsurveytables'] = array();
            if (isset($aOldTokenTableAsk)) {
                $aDelete['redundanttokentables'] = $aOldTokenTableAsk;
            }
            if (isset($aOldSurveyTableAsk)) {
                $aDelete['redundantsurveytables'] = $aOldSurveyTableAsk;
            }
        }
        return $aDelete;
    }
}
