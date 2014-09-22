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
 *
 */
/**
 * CheckIntegrity Controller
 *
 * This controller performs database repair functions.
 *
 * @package       LimeSurvey
 * @subpackage    Backend
 */
class CheckIntegrity extends Survey_Common_Action
{

    public function __construct($controller, $id)
    {
        parent::__construct($controller, $id);

        if (!Permission::model()->hasGlobalPermission('settings','read')){
            $clang = $this->getController()->lang;
            Yii::app()->session['flashmessage'] = $clang->gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect($this->getController()->createUrl("/admin/"));
        }

        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('surveytranslator');
    }

    public function index()
    {
        $aData = $this->_checkintegrity();
        $this->_renderWrappedTemplate('checkintegrity', 'check_view', $aData);
    }

    public function fixredundancy()
    {
        $clang = Yii::app()->lang;
        $oldsmultidelete=Yii::app()->request->getPost('oldsmultidelete', array());
        $aData['messages'] = array();
        if ( Permission::model()->hasGlobalPermission('settings','update') && Yii::app()->request->getPost('ok') == 'Y') {
            $aDelete = $this->_checkintegrity();
            if (isset($aDelete['redundanttokentables'])) {
                foreach ($aDelete['redundanttokentables'] as $aTokenTable)
                {
                    if(in_array($aTokenTable['table'],$oldsmultidelete))
                    {
                        Yii::app()->db->createCommand()->dropTable($aTokenTable['table']);
                        $aData['messages'][] = sprintf($clang->gT('Deleting token table: %s'),$aTokenTable['table']);
                    }
                }
            }
            if (isset($aDelete['redundantsurveytables'])) {
                foreach ($aDelete['redundantsurveytables'] as $aSurveyTable)
                {
                    if(in_array($aSurveyTable['table'],$oldsmultidelete))
                    {
                        Yii::app()->db->createCommand()->dropTable($aSurveyTable['table']);
                        $aData['messages'][] = sprintf($clang->gT('Deleting survey table: %s'),$aSurveyTable['table']);
                    }
                }
            }
            if(count($aData['messages'])==0)
            {
                $aData['messages'][] = $clang->gT('No old survey or token table selected.');
            }
            $this->_renderWrappedTemplate('checkintegrity', 'fix_view', $aData);
        }
    }

    public function fixintegrity()
    {
        $aData = array();
        $clang = Yii::app()->lang;
        if (Permission::model()->hasGlobalPermission('settings','update') && Yii::app()->request->getPost('ok') == 'Y') {
            $aDelete = $this->_checkintegrity();

            // TMSW Condition->Relevance:  Update this to process relevance instead
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

            $this->_renderWrappedTemplate('checkintegrity', 'fix_view', $aData);
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
        QuestionGroup::model()->deleteAll($criteria);
        if (QuestionGroup::model()->hasErrors()) safeDie(QuestionGroup::model()->getError());
        $aData['messages'][] = sprintf($clang->gT('Deleting groups: %u groups deleted'), count($groups));
        return $aData;
    }

    private function _deleteQuestions(array $questions, array $aData, Limesurvey_lang $clang)
    {
        foreach ($questions as $question) $qids[] = $question['qid'];

        $criteria = new CDbCriteria;
        $criteria->addInCondition('qid', $qids);
        Question::model()->deleteAll($criteria);
        if (Question::model()->hasErrors()) safeDie(Question::model()->getError());
        $aData['messages'][] = sprintf($clang->gT('Deleting questions: %u questions deleted'), count($questions));
        return array($criteria, $aData);
    }

    private function _deleteSurveyLanguageSettings(array $surveyLanguageSettings, array $aData, Limesurvey_lang $clang)
    {
        foreach ($surveyLanguageSettings as $surveylanguagesetting) $surveyls_survey_ids[] = $surveylanguagesetting['slid'];

        $criteria = new CDbCriteria;
        $criteria->compare('surveyls_survey_id', $surveyls_survey_ids);
        SurveyLanguageSetting::model()->deleteAll($criteria);
        if (SurveyLanguageSetting::model()->hasErrors()) safeDie(SurveyLanguageSetting::model()->getError());
        $aData['messages'][] = sprintf($clang->gT('Deleting survey languagesettings: %u survey languagesettings deleted'), count($surveyLanguageSettings));
        return array($criteria, $aData);
    }

    private function _deleteSurveys(array $surveys, array $aData, Limesurvey_lang $clang)
    {
        foreach ($surveys as $survey)
        {
            Survey::model()->deleteByPk($survey['sid']);
        }

        if (Survey::model()->hasErrors()) safeDie(Survey::model()->getError());
        $aData['messages'][] = sprintf($clang->gT('Deleting surveys: %u surveys deleted'), count($surveys));
        return $aData;
    }

    private function _deleteAnswers(array $answers, array $aData, Limesurvey_lang $clang)
    {
        foreach ($answers as $aAnswer) {
            Answer::model()->deleteAll('qid=:qid AND code=:code',array(':qid'=>$aAnswer['qid'],':code'=>$aAnswer['code']));
            if (Answer::model()->hasErrors()) safeDie(Answer::model()->getError());
        }
        $aData['messages'][] = sprintf($clang->gT('Deleting answers: %u answers deleted'), count($answers));
        return $aData;
    }

    private function _deleteAssessments(array $assessments, array $aData, Limesurvey_lang $clang)
    {
        foreach ($assessments as $assessment) $assessments_ids[] = $assessment['id'];

        $assessments_ids = array();
        Assessment::model()->deleteByPk('id',$assessments_ids);
        if (Assessment::model()->hasErrors()) safeDie(Assessment::model()->getError());
        $aData['messages'][] = sprintf($clang->gT('Deleting assessments: %u assessment entries deleted'), count($assessments));
        return $aData;
    }

    private function _deleteQuotaMembers(array $aData, Limesurvey_lang $clang)
    {
        $oCriteria = new CDbCriteria;
        $oCriteria->join = 'LEFT JOIN {{questions}} q ON t.qid=q.qid LEFT JOIN {{surveys}} s ON t.sid=s.sid';
        $oCriteria->condition = '(q.qid IS NULL) OR (s.sid IS NULL)';

        $aRecords=QuotaMember::model()->findAll($oCriteria);
        foreach ($aRecords as $aRecord)
        {
            QuotaMember::model()->deleteAllByAttributes($aRecord);
        }
        if (QuotaLanguageSetting::model()->hasErrors()) safeDie(QuotaLanguageSetting::model()->getError());
        $aData['messages'][] = $clang->gT('Deleting orphaned quota members.');
        return $aData;
    }

    private function _deleteQuotaLanguageSettings()
    {
        $quotas = Quota::model()->findAll();
        foreach ($quotas as $quota) $quota_ids[] = $quota['id'];
        $criteria = new CDbCriteria;
        $criteria->addNotInCondition('quotals_quota_id', $quota_ids);

        QuotaLanguageSetting::model()->deleteAll($criteria);
        if (QuotaLanguageSetting::model()->hasErrors()) safeDie(QuotaLanguageSetting::model()->getError());
    }

    private function _deleteQuotas(array $aData, Limesurvey_lang $clang)
    {
        $sids = array();
        $surveys = Survey::model()->findAll();
        foreach ($surveys as $survey) $sids[] = $survey['sid'];
        $criteria = new CDbCriteria;
        $criteria->addNotInCondition('sid', $sids);

        Quota::model()->deleteAll($criteria);
        if (Quota::model()->hasErrors()) safeDie(Quota::model()->getError());
        $aData['messages'][] = $clang->gT('Deleting orphaned quotas.');
        return $aData;
    }

    private function _deleteDefaultValues(array $aData, Limesurvey_lang $clang)
    {
        $criteria = new CDbCriteria;
        $criteria->join = 'LEFT JOIN {{questions}} q ON t.qid=q.qid';
        $criteria->condition = 'q.qid IS NULL';

        $aRecords=DefaultValue::model()->findAll($criteria);
        foreach ($aRecords as $aRecord)
        {
            DefaultValue::model()->deleteAllByAttributes($aRecord);
        }
        $aData['messages'][] = $clang->gT('Deleting orphaned default values.');
        return $aData;
    }

    private function _deleteQuestionAttributes(array $questionAttributes, array $aData, Limesurvey_lang $clang)
    {
        $qids = array();
        foreach ($questionAttributes as $questionattribute) $qids[] = $questionattribute['qid'];
        $criteria = new CDbCriteria;
        $criteria->addInCondition('qid', $qids);

        QuestionAttribute::model()->deleteAll($criteria);
        if (QuestionAttribute::model()->hasErrors()) safeDie(QuestionAttribute::model()->getError());
        $aData['messages'][] = sprintf($clang->gT('Deleting question attributes: %u attributes deleted'), count($questionAttributes));
        return $aData;
    }

    private function _deleteConditions(array $conditions, array $aData, Limesurvey_lang $clang)
    {
        $cids = array();
        foreach ($conditions as $condition) $cids[] = $condition['cid'];

        Condition::model()->deleteByPk($cids);
        if (Condition::model()->hasErrors()) safeDie(Condition::model()->getError());
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
        $oCriteria = new CDbCriteria;
        $oCriteria->addNotInCondition('uid', $uids, 'OR');

        $surveys = Survey::model()->findAll();
        $sids = array();
        foreach ($surveys as $survey) $sids[] = $survey['sid'];
        $oCriteria->addNotInCondition('entity_id', $sids, 'OR');
        $oCriteria->addCondition("entity='survey'");

        Permission::model()->deleteAll($oCriteria);


        // Deactivate surveys that have a missing response table
        foreach ($surveys as $survey)
        {
            if ($survey['active']=='Y' && !tableExists("{{survey_{$survey['sid']}}}"))
            {
                Survey::model()->updateByPk($survey['sid'],array('active'=>'N'));
            }
        }
        unset($surveys);



        // Fix subquestions
        fixSubquestions();

        /*** Check for active survey tables with missing survey entry and rename them ***/
        $sDBPrefix = Yii::app()->db->tablePrefix;
        $sQuery = dbSelectTablesLike('{{survey}}\_%');
        $aResult = dbQueryOrFalse($sQuery);
        foreach ($aResult->readAll() as $aRow)
        {
            $sTableName = substr(reset($aRow), strlen($sDBPrefix));
            if ($sTableName == 'survey_links' || $sTableName == 'survey_url_parameters') continue;
            $aTableName=explode('_',$sTableName);
            if (isset($aTableName[1]) && ctype_digit($aTableName[1]))
            {
                $iSurveyID = $aTableName[1];
                if (!in_array($iSurveyID, $sids)) {
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
        }

        /*** Check for active token tables with missing survey entry ***/
        $aResult = dbQueryOrFalse(dbSelectTablesLike('{{tokens}}\_%'));
        foreach ($aResult->readAll() as $aRow)
        {
            $sTableName = substr(reset($aRow), strlen($sDBPrefix));
            $iSurveyID = substr($sTableName, strpos($sTableName, '_') + 1);
            if (!in_array($iSurveyID, $sids)) {
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
        $okQuestion = array();
        $sQuery = 'SELECT cqid,cid,cfieldname FROM {{conditions}}';
        $aConditions = Yii::app()->db->createCommand($sQuery)->queryAll();
        foreach ($aConditions as $condition)
        {
            if ($condition['cqid'] != 0) { // skip case with cqid=0 for codnitions on {TOKEN:EMAIL} for instance
                if (!array_key_exists($condition['cqid'], $okQuestion)) {
                    $iRowCount = Question::model()->countByAttributes(array('qid' => $condition['cqid']));
                    if (Question::model()->hasErrors()) safeDie(Question::model()->getError());
                    if (!$iRowCount) {
                        $aDelete['conditions'][] = array('cid' => $condition['cid'], 'reason' => $clang->gT('No matching CQID'));
                    } else {
                        $okQuestion[$condition['cqid']] = $condition['cqid'];
                    }
                }
            }
            if ($condition['cfieldname']) //Only do this if there actually is a 'cfieldname'
            {
                if (preg_match('/^\+{0,1}[0-9]+X[0-9]+X*$/', $condition['cfieldname'])) { // only if cfieldname isn't Tag such as {TOKEN:EMAIL} or any other token
                    list ($surveyid, $gid, $rest) = explode('X', $condition['cfieldname']);
                    $iRowCount = count(QuestionGroup::model()->findAllByAttributes(array('gid'=>$gid)));
                    if (QuestionGroup::model()->hasErrors()) safeDie(QuestionGroup::model()->getError());
                    if (!$iRowCount) $aDelete['conditions'][] = array('cid' => $condition['cid'], 'reason' => $clang->gT('No matching CFIELDNAME group!') . " ($gid) ({$condition['cfieldname']})");
                }
            }
            elseif (!$condition['cfieldname'])
            {
                $aDelete['conditions'][] = array('cid' => $condition['cid'], 'reason' => $clang->gT('No CFIELDNAME field set!') . " ({$condition['cfieldname']})");
            }
        }
        unset($okQuestion);
        unset($aConditions);
        /**********************************************************************/
        /*     Check question attributes                                      */
        /**********************************************************************/
        $question_attributes = QuestionAttribute::model()->findAllBySql('select qid from {{question_attributes}} where qid not in (select qid from {{questions}})');
        if (QuestionAttribute::model()->hasErrors()) safeDie(QuestionAttribute::model()->getError());
        foreach ($question_attributes as $question_attribute)
        {
            $aDelete['questionattributes'][] = array('qid' => $question_attribute['qid']);
        } // foreach


        /**********************************************************************/
        /*     Check default values                                           */
        /**********************************************************************/
        $oCriteria = new CDbCriteria;
        $oCriteria->join = 'LEFT JOIN {{questions}} q ON t.qid=q.qid';
        $oCriteria->condition = 'q.qid IS NULL';
        $aRecords=DefaultValue::model()->findAll($oCriteria);
        $aDelete['defaultvalues'] = count($aRecords);
        if (DefaultValue::model()->hasErrors()) safeDie(DefaultValue::model()->getError());

        /**********************************************************************/
        /*     Check quotas                                                   */
        /**********************************************************************/
        $surveys = Survey::model()->findAll();
        if (Survey::model()->hasErrors()) safeDie(Survey::model()->getError());
        $sids = array();
        foreach ($surveys as $survey) $sids[] = $survey['sid'];
        $oCriteria = new CDbCriteria;
        $oCriteria->addNotInCondition('sid', $sids);

        $aDelete['quotas'] = count(Quota::model()->findAll($oCriteria));
        if (Quota::model()->hasErrors()) safeDie(Quota::model()->getError());

        /**********************************************************************/
        /*     Check quota languagesettings                                   */
        /**********************************************************************/
        $quotas = Quota::model()->findAll();
        if (Quota::model()->hasErrors()) safeDie(Quota::model()->getError());
        $ids = array();
        foreach ($quotas as $quota) $ids[] = $quota['id'];
        $oCriteria = new CDbCriteria;
        $oCriteria->addNotInCondition('quotals_quota_id', $ids);

        $aDelete['quotals'] = count(QuotaLanguageSetting::model()->findAll($oCriteria));
        if (QuotaLanguageSetting::model()->hasErrors()) safeDie(QuotaLanguageSetting::model()->getError());

        /**********************************************************************/
        /*     Check quota members                                   */
        /**********************************************************************/
        $oCriteria = new CDbCriteria;
        $oCriteria->join = 'LEFT JOIN {{questions}} q ON t.qid=q.qid LEFT JOIN {{surveys}} s ON t.sid=s.sid';
        $oCriteria->condition = '(q.qid IS NULL) OR (s.sid IS NULL)';

        $aDelete['quotamembers'] = count(QuotaMember::model()->findAll($oCriteria));
        if (QuotaMember::model()->hasErrors()) safeDie(QuotaMember::model()->getError());

        /**********************************************************************/
        /*     Check assessments                                              */
        /**********************************************************************/
        $oCriteria = new CDbCriteria;
        $oCriteria->compare('scope', 'T');
        $assessments = Assessment::model()->findAll($oCriteria);
        if (Assessment::model()->hasErrors()) safeDie(Assessment::model()->getError());
        foreach ($assessments as $assessment)
        {
            $iAssessmentCount = count(Survey::model()->findAllByPk($assessment['sid']));
            if (Survey::model()->hasErrors()) safeDie(Survey::model()->getError());
            if (!$iAssessmentCount) {
                $aDelete['assessments'][] = array('id' => $assessment['id'], 'assessment' => $assessment['name'], 'reason' => $clang->gT('No matching survey'));
            }
        }

        $oCriteria = new CDbCriteria;
        $oCriteria->compare('scope', 'G');
        $assessments = Assessment::model()->findAll($oCriteria);
        if (Assessment::model()->hasErrors()) safeDie(Assessment::model()->getError());
        foreach ($assessments as $assessment)
        {
            $iAssessmentCount = count(QuestionGroup::model()->findAllByPk(array('gid'=>$assessment['gid'], 'language'=>$assessment['language'])));
            if (QuestionGroup::model()->hasErrors()) safeDie(QuestionGroup::model()->getError());
            if (!$iAssessmentCount) {
                $aDelete['assessments'][] = array('id' => $assessment['id'], 'assessment' => $assessment['name'], 'reason' => $clang->gT('No matching group'));
            }
        }
        unset($assessments);
        /**********************************************************************/
        /*     Check answers                                                  */
        /**********************************************************************/
        $oCriteria = new CDbCriteria;
        $oCriteria->join = 'LEFT JOIN {{questions}} q ON t.qid=q.qid';
        $oCriteria->condition = '(q.qid IS NULL)';

        $answers = Answer::model()->findAll($oCriteria);
        foreach ($answers as $answer)
        {
            $aDelete['answers'][] = array('qid' => $answer['qid'], 'code' => $answer['code'], 'reason' => $clang->gT('No matching question'));
        }
        /***************************************************************************/
        /*   Check survey languagesettings and restore them if they don't exist    */
        /***************************************************************************/

        $surveys = Survey::model()->findAll();
        foreach ($surveys as $survey)
        {
            $aLanguages=$survey->additionalLanguages;
            $aLanguages[]=$survey->language;
            foreach ($aLanguages as $langname)
            {
                if ($langname)
                {
                    $oLanguageSettings = SurveyLanguageSetting::model()->find('surveyls_survey_id=:surveyid AND surveyls_language=:langname', array(':surveyid'=>$survey->sid,':langname'=>$langname));
                    if(!$oLanguageSettings)
                    {
                        $oLanguageSettings= new SurveyLanguageSetting;
                        $languagedetails=getLanguageDetails($langname);
                        $insertdata = array(
                            'surveyls_survey_id' => $survey->sid,
                            'surveyls_language' => $langname,
                            'surveyls_title' => '',
                            'surveyls_dateformat' => $languagedetails['dateformat']
                        );
                        foreach ($insertdata as $k => $v)
                            $oLanguageSettings->$k = $v;
                        $usresult=$oLanguageSettings->save();
                    }
                }
            }
        }


        /**********************************************************************/
        /*     Check survey language settings                                 */
        /**********************************************************************/
        $surveys = Survey::model()->findAll();
        if (Survey::model()->hasErrors()) safeDie(Survey::model()->getError());
        $sids = array();
        foreach ($surveys as $survey) $sids[] = $survey['sid'];
        $oCriteria = new CDbCriteria;
        $oCriteria->addNotInCondition('surveyls_survey_id', $sids);
        $surveys_languagesettings = SurveyLanguageSetting::model()->findAll($oCriteria);
        if (SurveyLanguageSetting::model()->hasErrors()) safeDie(SurveyLanguageSetting::model()->getError());

        foreach ($surveys_languagesettings as $surveys_languagesetting)
        {
            $aDelete['surveylanguagesettings'][] = array('slid' => $surveys_languagesetting['surveyls_survey_id'], 'reason' => $clang->gT('The related survey is missing.'));
        }

        /**********************************************************************/
        /*     Check questions                                                */
        /**********************************************************************/
        $oCriteria = new CDbCriteria;
        $oCriteria->join = 'LEFT JOIN {{surveys}} s ON t.sid=s.sid LEFT JOIN {{groups}} g ON t.gid=g.gid';
        $oCriteria->condition = '(g.gid IS NULL) OR (s.sid IS NULL)';
        $questions = Question::model()->findAll($oCriteria);
        if (Question::model()->hasErrors()) safeDie(Question::model()->getError());
        foreach ($questions as $question)
        {
            $aDelete['questions'][] = array('qid' => $question['qid'], 'reason' => $clang->gT('No matching group') . " ({$question['gid']})");
        }

        /**********************************************************************/
        /*     Check groups                                                   */
        /**********************************************************************/
        $surveys = Survey::model()->findAll();
        if (Survey::model()->hasErrors()) safeDie(Survey::model()->getError());
        $sids = array();
        foreach ($surveys as $survey) $sids[] = $survey['sid'];
        $oCriteria = new CDbCriteria;
        $oCriteria->addNotInCondition('sid', $sids);
        $groups = QuestionGroup::model()->findAll($oCriteria);
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
        $sQuery = dbSelectTablesLike('{{old_survey}}%');
        $aTables = Yii::app()->db->createCommand($sQuery)->queryColumn();

        $aOldSIDs = array();
        $aSIDs = array();
        foreach ($aTables as $sTable)
        {
            list($sOldText, $SurveyText, $iSurveyID, $sDate) = explode('_', substr($sTable, strlen($sDBPrefix)));
            $aOldSIDs[] = $iSurveyID;
            $aFullOldSIDs[$iSurveyID][] = $sTable;
        }
        $aOldSIDs = array_unique($aOldSIDs);
        //$sQuery = 'SELECT sid FROM {{surveys}} ORDER BY sid';
        //$oResult = dbExecuteAssoc($sQuery) or safeDie('Couldn\'t get unique survey ids');
        $surveys = Survey::model()->findAll();
        if (Survey::model()->hasErrors()) safeDie(Survey::model()->getError());
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
                    $sQuery = 'SELECT count(*) as recordcount FROM ' . $sTableName;
                    $aFirstRow = Yii::app()->db->createCommand($sQuery)->queryRow();
                    if ($aFirstRow['recordcount']==0) { // empty table - so add it to immediate deletion
                        $aDelete['orphansurveytables'][] = $sTableName;
                    } else {
                        $aOldSurveyTableAsk[] = array('table' => $sTableName, 'details' => sprintf($clang->gT('Survey ID %d saved at %s containing %d record(s) (%s)'), $iSurveyID, $sDate, $aFirstRow['recordcount'], $sType));
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
        $sQuery = dbSelectTablesLike('{{old_token}}%');
        $aTables = Yii::app()->db->createCommand($sQuery)->queryColumn();

        $aOldTokenSIDs = array();
        $aTokenSIDs = array();
        $aFullOldTokenSIDs = array();

        foreach ($aTables as $sTable)
        {
            list($sOldText, $SurveyText, $iSurveyID, $sDateTime) = explode('_', substr($sTable, strlen($sDBPrefix)));
            $aTokenSIDs[] = $iSurveyID;
            $aFullOldTokenSIDs[$iSurveyID][] = $sTable;
        }
        $aOldTokenSIDs = array_unique($aTokenSIDs);
        $surveys = Survey::model()->findAll();
        if (Survey::model()->hasErrors()) safeDie(Survey::model()->getError());
        $aSIDs = array();
        foreach ($surveys as $survey)
        {
            $aSIDs[] = $survey['sid'];
        }
        foreach ($aOldTokenSIDs as $iOldTokenSID)
        {
            if (!in_array($iOldTokenSID, $aOldTokenSIDs)) {
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
                    $sQuery = 'SELECT count(*) as recordcount FROM ' . $sTableName;

                    $aFirstRow = Yii::app()->db->createCommand($sQuery)->queryRow();
                    if ($aFirstRow['recordcount']==0) { // empty table - so add it to immediate deletion
                        $aDelete['orphantokentables'][] = $sTableName;
                    }
                    else
                    {
                        $aOldTokenTableAsk[] = array('table' => $sTableName, 'details' => sprintf($clang->gT('Survey ID %d saved at %s containing %d record(s)'), $iSurveyID, $sDate, $aFirstRow['recordcount']));
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

        /**********************************************************************/
        /*     CHECK CPDB SURVEY_LINKS TABLE FOR REDUNDENT TOKEN TABLES       */
        /**********************************************************************/
        //1: Get distinct list of survey_link survey ids, check if tokens
        //   table still exists for each one, and remove if not


        /* TODO */

        /**********************************************************************/
        /*     CHECK CPDB SURVEY_LINKS TABLE FOR REDUNDENT TOKEN ENTRIES      */
        /**********************************************************************/
        //1: For each survey_link, see if the matching entry still exists in
        //   the token table and remove if it doesn't.


        /* TODO */

        return $aDelete;
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'checkintegrity', $aViewUrls = array(), $aData = array())
    {
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }
}
