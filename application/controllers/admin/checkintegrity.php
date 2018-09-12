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
            Yii::app()->setFlashMessage(gT("You do not have permission to access this page."),'error');
            $this->getController()->redirect($this->getController()->createUrl("/admin/"));
        }

        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('surveytranslator');
    }

    public function index()
    {
        $aData = $this->_checkintegrity();


        $aData['fullpagebar']['returnbutton']['url']='admin/index';
        $aData['fullpagebar']['returnbutton']['text']=gT('Return to admin home');

        $this->_renderWrappedTemplate('checkintegrity', 'check_view', $aData);
    }

    public function fixredundancy()
    {

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
                        $aData['messages'][] = sprintf(gT('Deleting token table: %s'),$aTokenTable['table']);
                    }
                }
            }
            if (isset($aDelete['redundantsurveytables'])) {
                foreach ($aDelete['redundantsurveytables'] as $aSurveyTable)
                {
                    if(in_array($aSurveyTable['table'],$oldsmultidelete))
                    {
                        Yii::app()->db->createCommand()->dropTable($aSurveyTable['table']);
                        $aData['messages'][] = sprintf(gT('Deleting survey table: %s'),$aSurveyTable['table']);
                    }
                }
            }
            if(count($aData['messages'])==0)
            {
                $aData['messages'][] = gT('No old survey or token table selected.');
            }
            $this->_renderWrappedTemplate('checkintegrity', 'fix_view', $aData);
        }
    }

    public function fixintegrity()
    {
        $aData = array();

        if (Permission::model()->hasGlobalPermission('settings','update') && Yii::app()->request->getPost('ok') == 'Y') {
            $aDelete = $this->_checkintegrity();

            // TMSW Condition->Relevance:  Update this to process relevance instead
            if (isset($aDelete['conditions'])) {
                $aData = $this->_deleteConditions($aDelete['conditions'], $aData);
            }

            if (isset($aDelete['questionattributes'])) {
                $aData = $this->_deleteQuestionAttributes($aDelete['questionattributes'], $aData);
            }

            if ($aDelete['defaultvalues']) {
                $aData = $this->_deleteDefaultValues($aData);
            }

            if ($aDelete['quotas']) {
                $aData = $this->_deleteQuotas($aData);
            }

            if ($aDelete['quotals']) {
                $this->_deleteQuotaLanguageSettings();
            }

            if ($aDelete['quotamembers']) {
                $aData = $this->_deleteQuotaMembers($aData);
            }

            if (isset($aDelete['assessments'])) {
                $aData = $this->_deleteAssessments($aDelete['assessments'], $aData);
            }

            if (isset($aDelete['answers'])) {
                $aData = $this->_deleteAnswers($aDelete['answers'], $aData);
            }

            if (isset($aDelete['surveys'])) {
                $aData = $this->_deleteSurveys($aDelete['surveys'], $aData);
            }

            if (isset($aDelete['surveylanguagesettings'])) {
                $aData = $this->_deleteSurveyLanguageSettings($aDelete['surveylanguagesettings'], $aData);
            }

            if (isset($aDelete['questions'])) {
                $aData = $this->_deleteQuestions($aDelete['questions'], $aData);
            }


            if (isset($aDelete['groups'])) {
                $aData = $this->_deleteGroups($aDelete['groups'], $aData);
            }

            if (isset($aDelete['orphansurveytables'])) {
                $aData = $this->_dropOrphanSurveyTables($aDelete['orphansurveytables'], $aData);
            }

            if (isset($aDelete['orphantokentables'])) {
                $aData = $this->_deleteOrphanTokenTables($aDelete['orphantokentables'], $aData);
            }

            $this->_renderWrappedTemplate('checkintegrity', 'fix_view', $aData);
        }
    }

    private function _deleteOrphanTokenTables(array $tokenTables, array $aData)
    {
        foreach ($tokenTables as $aTokenTable)
        {
            Yii::app()->db->createCommand()->dropTable($aTokenTable);
            $aData['messages'][] = gT('Deleting orphan token table:') . ' ' . $aTokenTable;
        }
        return $aData;
    }

    private function _dropOrphanSurveyTables(array $surveyTables, array $aData)
    {
        foreach ($surveyTables as $aSurveyTable)
        {
            Yii::app()->db->createCommand()->dropTable($aSurveyTable);
            $aData['messages'][] = gT('Deleting orphan survey table:') . ' ' . $aSurveyTable;
        }
        return $aData;
    }

    private function _deleteGroups(array $groups, array $aData)
    {
        $gids = array();
        foreach ($groups as $group)
        {
            $gids[] = $group['gid'];
        }

        $criteria = new CDbCriteria;
        $criteria->addInCondition('gid', $gids);
        QuestionGroup::model()->deleteAll($criteria);
        if (QuestionGroup::model()->hasErrors())
        {
            safeDie(QuestionGroup::model()->getError());
        }
        $aData['messages'][] = sprintf(gT('Deleting groups: %u groups deleted'), count($groups));

        return $aData;
    }

    private function _deleteQuestions(array $questions, array $aData)
    {
        $qids = array();
        foreach ($questions as $question)
        {
            $qids[] = $question['qid'];
        }

        $criteria = new CDbCriteria;
        $criteria->addInCondition('qid', $qids);
        Question::model()->deleteAll($criteria);
        if (Question::model()->hasErrors()) safeDie(Question::model()->getError());
        $aData['messages'][] = sprintf(gT('Deleting questions: %u questions deleted'), count($questions));
        return array($criteria, $aData);
    }

    private function _deleteSurveyLanguageSettings(array $surveyLanguageSettings, array $aData)
    {
        $surveyls_survey_ids = array();
        foreach ($surveyLanguageSettings as $surveylanguagesetting)
        {
            $surveyls_survey_ids[] = $surveylanguagesetting['slid'];
        }

        $criteria = new CDbCriteria;
        $criteria->compare('surveyls_survey_id', $surveyls_survey_ids);
        SurveyLanguageSetting::model()->deleteAll($criteria);
        if (SurveyLanguageSetting::model()->hasErrors()) safeDie(SurveyLanguageSetting::model()->getError());
        $aData['messages'][] = sprintf(gT('Deleting survey languagesettings: %u survey languagesettings deleted'), count($surveyLanguageSettings));
        return array($criteria, $aData);
    }

    private function _deleteSurveys(array $surveys, array $aData)
    {
        foreach ($surveys as $survey)
        {
            Survey::model()->deleteByPk($survey['sid']);
        }

        if (Survey::model()->hasErrors()) safeDie(Survey::model()->getError());
        $aData['messages'][] = sprintf(gT('Deleting surveys: %u surveys deleted'), count($surveys));
        return $aData;
    }

    private function _deleteAnswers(array $answers, array $aData)
    {
        foreach ($answers as $aAnswer) {
            Answer::model()->deleteAll('qid=:qid AND code=:code',array(':qid'=>$aAnswer['qid'],':code'=>$aAnswer['code']));
            if (Answer::model()->hasErrors()) safeDie(Answer::model()->getError());
        }
        $aData['messages'][] = sprintf(gT('Deleting answers: %u answers deleted'), count($answers));
        return $aData;
    }

    private function _deleteAssessments(array $assessments, array $aData)
    {
        foreach ($assessments as $assessment) $assessments_ids[] = $assessment['id'];

        $assessments_ids = array();
        Assessment::model()->deleteByPk('id',$assessments_ids);
        if (Assessment::model()->hasErrors()) safeDie(Assessment::model()->getError());
        $aData['messages'][] = sprintf(gT('Deleting assessments: %u assessment entries deleted'), count($assessments));
        return $aData;
    }

    private function _deleteQuotaMembers(array $aData)
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
        $aData['messages'][] = gT('Deleting orphaned quota members.');
        return $aData;
    }

    /**
    * This function Deletes quota language settings without related main entries
    *
    */
    private function _deleteQuotaLanguageSettings()
    {
        $oCriteria = new CDbCriteria;
        $oCriteria->join = 'LEFT JOIN {{quota}} q ON {{quota_languagesettings}}.quotals_quota_id=q.id';
        $oCriteria->condition = '(q.id IS NULL)';
        QuotaLanguageSetting::model()->deleteAll($oCriteria);
        if (QuotaLanguageSetting::model()->hasErrors()) safeDie(QuotaLanguageSetting::model()->getError());
    }

    /**
    * This function deletes quota entries which not having a related survey entry
    *
    * @param mixed $aData
    */
    private function _deleteQuotas(array $aData)
    {
        $oCriteria = new CDbCriteria;
        $oCriteria->join = 'LEFT JOIN {{surveys}} q ON {{quota}}.sid=q.sid';
        $oCriteria->condition = '(q.sid IS NULL)';
        Quota::model()->deleteAll($oCriteria);
        if (Quota::model()->hasErrors()) safeDie(Quota::model()->getError());
        $aData['messages'][] = gT('Deleting orphaned quotas.');
        return $aData;
    }

    private function _deleteDefaultValues(array $aData )
    {
        $criteria = new CDbCriteria;
        $criteria->join = 'LEFT JOIN {{questions}} q ON t.qid=q.qid';
        $criteria->condition = 'q.qid IS NULL';

        $aRecords=DefaultValue::model()->findAll($criteria);
        foreach ($aRecords as $aRecord)
        {
            DefaultValue::model()->deleteAllByAttributes($aRecord);
        }
        $aData['messages'][] = gT('Deleting orphaned default values.');
        return $aData;
    }

    private function _deleteQuestionAttributes(array $questionAttributes, array $aData)
    {
        $qids = array();
        foreach ($questionAttributes as $questionattribute) $qids[] = $questionattribute['qid'];
        $criteria = new CDbCriteria;
        $criteria->addInCondition('qid', $qids);

        QuestionAttribute::model()->deleteAll($criteria);
        if (QuestionAttribute::model()->hasErrors()) safeDie(QuestionAttribute::model()->getError());
        $aData['messages'][] = sprintf(gT('Deleting question attributes: %u attributes deleted'), count($questionAttributes));
        return $aData;
    }

    private function _deleteConditions(array $conditions, array $aData)
    {
        $cids = array();
        foreach ($conditions as $condition)
        {
            $cids[] = $condition['cid'];
        }

        Condition::model()->deleteByPk($cids);
        if (Condition::model()->hasErrors())
        {
            safeDie(Condition::model()->getError());
        }

        $aData['messages'][] = sprintf(gT('Deleting conditions: %u conditions deleted'), count($conditions));
        return $aData;
    }


    /**
     * This function checks the LimeSurvey database for logical consistency and returns an according array
     * containing all issues in the particular tables.
     * @returns Array with all found issues.
     */
    protected function _checkintegrity()
    {
        /* Find is some fix is done */
        $bDirectlyFixed=false;
        $aFullOldSIDs = array();
        // Delete survey permissions if the user does not exist
        $oCriteria = new CDbCriteria;
        $oCriteria->join = 'LEFT JOIN {{users}} u ON {{permissions}}.uid=u.uid';
        $oCriteria->condition = '(u.uid IS NULL)';
        if (App()->db->driverName=='pgsql')
        {
            $oCriteria->join = 'USING {{users}} u';
            $oCriteria->condition = '{{permissions}}.uid=u.uid AND (u.uid IS NULL)';
        }
        if(Permission::model()->deleteAll($oCriteria))
        {
            $bDirectlyFixed=true;
        }

        // Delete survey permissions if the survey does not exist
        $oCriteria = new CDbCriteria;
        $oCriteria->join = 'LEFT JOIN {{surveys}} s ON {{permissions}}.entity_id=s.sid';
        $oCriteria->condition = "(s.sid IS NULL AND entity='survey')";
        if (App()->db->driverName=='pgsql')
        {
            $oCriteria->join = 'USING {{surveys}} s';
            $oCriteria->condition = "{{permissions}}.entity_id=s.sid AND (s.sid IS NULL AND entity='survey')";
        }
        if(Permission::model()->deleteAll($oCriteria))
        {
            $bDirectlyFixed=true;
        }

        // Deactivate surveys that have a missing response table
        $oSurveys = Survey::model()->findAll();
        foreach ($oSurveys as $oSurvey)
        {

            if ($oSurvey->active=='Y' && !tableExists("{{survey_{$oSurvey->sid}}}"))
            {
                Survey::model()->updateByPk($oSurvey->sid,array('active'=>'N'));
                $bDirectlyFixed=true;
            }
        }
        unset($oSurveys);



        // Fix subquestions
        fixSubquestions();

        /*** Check for active survey tables with missing survey entry and rename them ***/
        $sDBPrefix = Yii::app()->db->tablePrefix;
        $sQuery = dbSelectTablesLike('{{survey}}\_%');
        $aResult = dbQueryOrFalse($sQuery);
        $sSurveyIDs = Yii::app()->db->createCommand('select sid from {{surveys}}')->queryColumn();

        foreach ($aResult->readAll() as $aRow)
        {
            $sTableName = substr(reset($aRow), strlen($sDBPrefix));
            if ($sTableName == 'survey_links' || $sTableName == 'survey_url_parameters') continue;
            $aTableName=explode('_',$sTableName);
            if (isset($aTableName[1]) && ctype_digit($aTableName[1]))
            {
                $iSurveyID = $aTableName[1];
                if (!in_array($iSurveyID, $sSurveyIDs)) {
                    $sDate = date('YmdHis') . rand(1, 1000);
                    $sOldTable = "survey_{$iSurveyID}";
                    $sNewTable = "old_survey_{$iSurveyID}_{$sDate}";
                    try {
                        Yii::app()->db->createCommand()->renameTable("{{{$sOldTable}}}", "{{{$sNewTable}}}");
                        $bDirectlyFixed=true;
                    } catch (CDbException $e) {
                        safeDie('Couldn\'t make backup of the survey table. Please try again. The database reported the following error:<br />' . htmlspecialchars($e) . '<br />');
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
            if (!in_array($iSurveyID, $sSurveyIDs)) {
                $sDate = date('YmdHis') . rand(1, 1000);
                $sOldTable = "tokens_{$iSurveyID}";
                $sNewTable = "old_tokens_{$iSurveyID}_{$sDate}";
                try {
                    Yii::app()->db->createCommand()->renameTable("{{{$sOldTable}}}", "{{{$sNewTable}}}");
                    $bDirectlyFixed=true;
                } catch (CDbException $e) {
                    safeDie ('Couldn\'t make backup of the survey table. Please try again. The database reported the following error:<br />' . htmlspecialchars($e) . '<br />');
                }
            }
        }

        /**********************************************************************/
        /*     Check conditions                                               */
        /**********************************************************************/
        $okQuestion = array();
        $sQuery = 'SELECT cqid,cid,cfieldname FROM {{conditions}}';
        $aConditions = Yii::app()->db->createCommand($sQuery)->queryAll();
        $aDelete = array();
        foreach ($aConditions as $condition)
        {
            if ($condition['cqid'] != 0) { // skip case with cqid=0 for codnitions on {TOKEN:EMAIL} for instance
                if (!array_key_exists($condition['cqid'], $okQuestion)) {
                    $iRowCount = Question::model()->countByAttributes(array('qid' => $condition['cqid']));
                    if (Question::model()->hasErrors()) safeDie(Question::model()->getError());
                    if (!$iRowCount)
                    {
                        $aDelete['conditions'][] = array('cid' => $condition['cid'], 'reason' => gT('No matching CQID'));
                    }
                    else
                    {
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
                    if (!$iRowCount)
                    {
                        $aDelete['conditions'][] = array('cid' => $condition['cid'], 'reason' => gT('No matching CFIELDNAME group!') . " ($gid) ({$condition['cfieldname']})");
                    }
                }
            }
            elseif (!$condition['cfieldname'])
            {
                $aDelete['conditions'][] = array('cid' => $condition['cid'], 'reason' => gT('No CFIELDNAME field set!') . " ({$condition['cfieldname']})");
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

        $oCriteria = new CDbCriteria;
        $oCriteria->join = 'LEFT JOIN {{surveys}} s ON t.sid=s.sid';
        $oCriteria->condition = '(s.sid IS NULL)';
        $aDelete['quotas'] = count(Quota::model()->findAll($oCriteria));
        if (Quota::model()->hasErrors()) safeDie(Quota::model()->getError());

        /**********************************************************************/
        /*     Check quota languagesettings                                   */
        /**********************************************************************/
        $oCriteria = new CDbCriteria;
        $oCriteria->join = 'LEFT JOIN {{quota}} s ON t.quotals_quota_id=s.id';
        $oCriteria->condition = '(s.id IS NULL)';
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
                $aDelete['assessments'][] = array('id' => $assessment['id'], 'assessment' => $assessment['name'], 'reason' => gT('No matching survey'));
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
                $aDelete['assessments'][] = array('id' => $assessment['id'], 'assessment' => $assessment['name'], 'reason' => gT('No matching group'));
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
            $aDelete['answers'][] = array('qid' => $answer['qid'], 'code' => $answer['code'], 'reason' => gT('No matching question'));
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
                        $oLanguageSettings->save();
                        $bDirectlyFixed=true;
                    }
                }
            }
        }

        /**********************************************************************/
        /*     Check survey language settings                                 */
        /**********************************************************************/
        $oCriteria = new CDbCriteria;
        $oCriteria->join = 'LEFT JOIN {{surveys}} s ON t.surveyls_survey_id=s.sid';
        $oCriteria->condition = '(s.sid IS NULL)';
        $surveys_languagesettings = SurveyLanguageSetting::model()->findAll($oCriteria);
        if (SurveyLanguageSetting::model()->hasErrors()) safeDie(SurveyLanguageSetting::model()->getError());
        foreach ($surveys_languagesettings as $surveys_languagesetting)
        {
            $aDelete['surveylanguagesettings'][] = array('slid' => $surveys_languagesetting['surveyls_survey_id'], 'reason' => gT('The related survey is missing.'));
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
            $aDelete['questions'][] = array('qid' => $question['qid'], 'reason' => gT('No matching group') . " ({$question['gid']})");
        }

        /**********************************************************************/
        /*     Check groups                                                   */
        /**********************************************************************/
        $oCriteria = new CDbCriteria;
        $oCriteria->join = 'LEFT JOIN {{surveys}} s ON t.sid=s.sid';
        $oCriteria->condition = '(s.sid IS NULL)';
        $groups = QuestionGroup::model()->findAll($oCriteria);
        /** @var QuestionGroup $group */
        foreach ($groups as $group)
        {
            $aDelete['groups'][] = array('gid' => $group['gid'], 'reason' => gT('There is no matching survey.') . ' SID:' . $group['sid']);
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
            if (!in_array($iOldSID, $aSIDs))
            {
                foreach ($aFullOldSIDs[$iOldSID] as $sTableName)
                {
                    $aDelete['orphansurveytables'][] = $sTableName;
                }
            }
            else
            {
                foreach ($aFullOldSIDs[$iOldSID] as $sTableName)
                {

                    $aTableParts = explode('_', substr($sTableName, strlen($sDBPrefix)));
                    $sDateTime = $sType= '';
                    $iSurveyID = $aTableParts[2];

                    if (count($aTableParts) == 4)
                    {

                        $sDateTime = $aTableParts[3];
                        $sType = gT('responses');
                    }
                    elseif (count($aTableParts) == 5)
                    {
                        //This is a timings table (

                        $sDateTime = $aTableParts[4];
                        $sType = gT('timings');
                    }

                    $iYear = substr($sDateTime, 0, 4);
                    $iMonth = substr($sDateTime, 4, 2);
                    $iDay = substr($sDateTime, 6, 2);
                    $iHour = substr($sDateTime, 8, 2);
                    $iMinute = substr($sDateTime, 10, 2);
                    $sDate = date('Y-m-d H:i:s', mktime($iHour, $iMinute, 0, $iMonth, $iDay, $iYear));

                    $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);
                    Yii::app()->loadLibrary('Date_Time_Converter');
                    $datetimeobj = new date_time_converter(dateShift($sDate,'Y-m-d H:i:s',getGlobalSetting('timeadjust')), 'Y-m-d H:i:s');
                    $sDate=$datetimeobj->convert($dateformatdetails['phpdate'] . " H:i");

                    $sQuery = 'SELECT count(*) as recordcount FROM ' . $sTableName;
                    $aFirstRow = Yii::app()->db->createCommand($sQuery)->queryRow();
                    if ($aFirstRow['recordcount']==0) { // empty table - so add it to immediate deletion
                        $aDelete['orphansurveytables'][] = $sTableName;
                    } else {
                        $aOldSurveyTableAsk[] = array('table' => $sTableName, 'details' => sprintf(gT('Survey ID %d saved at %s containing %d record(s) (%s)'), $iSurveyID, $sDate, $aFirstRow['recordcount'], $sType));
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
                        $aOldTokenTableAsk[] = array('table' => $sTableName, 'details' => sprintf(gT('Survey ID %d saved at %s containing %d record(s)'), $iSurveyID, $sDate, $aFirstRow['recordcount']));
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


        /* Show a alert message is some fix is done */
        if($bDirectlyFixed)
        {
            Yii::app()->setFlashMessage(gT("Some automatic fixes were already applied."),'info');
        }

        return $aDelete;
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'checkintegrity', $aViewUrls = array(), $aData = array())
    {
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }
}
