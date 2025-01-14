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
class CheckIntegrity extends SurveyCommonAction
{
    /**
     * Constructor
     *
     * @param $controller
     * @param $id
     */
    public function __construct($controller, $id)
    {
        parent::__construct($controller, $id);

        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            Yii::app()->setFlashMessage(gT("You do not have permission to access this page."), 'error');
            $this->getController()->redirect($this->getController()->createUrl("/admin/"));
        }

        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('surveytranslator');
    }

    /**
     * Index
     *
     * @throws Exception
     */
    public function index()
    {
        $aData = $this->checkintegrity();

        $aData['topbar']['title'] = gT('Check data integrity');
        $aData['topbar']['backLink'] = App()->createUrl('dashboard/view');

        $this->renderWrappedTemplate('checkintegrity', 'check_view', $aData);
    }

    public function fixredundancy()
    {
        $oldsmultidelete = Yii::app()->request->getPost('oldsmultidelete', array());
        $aData = [];
        $aData['messages'] = array();
        if (Permission::model()->hasGlobalPermission('settings', 'update') && Yii::app()->request->getPost('ok') == 'Y') {
            $aDelete = $this->checkintegrity();
            if (isset($aDelete['redundanttokentables'])) {
                foreach ($aDelete['redundanttokentables'] as $aTokenTable) {
                    if (in_array($aTokenTable['table'], $oldsmultidelete)) {
                        Yii::app()->db->createCommand()->dropTable($aTokenTable['table']);
                        $aData['messages'][] = sprintf(gT('Deleting survey participants table: %s'), $aTokenTable['table']);
                    }
                }
            }
            if (isset($aDelete['redundantsurveytables'])) {
                foreach ($aDelete['redundantsurveytables'] as $aSurveyTable) {
                    if (in_array($aSurveyTable['table'], $oldsmultidelete)) {
                        Yii::app()->db->createCommand()->dropTable($aSurveyTable['table']);
                        $aData['messages'][] = sprintf(gT('Deleting survey table: %s'), $aSurveyTable['table']);
                    }
                }
            }
            if (count($aData['messages']) == 0) {
                $aData['messages'][] = gT('No old survey or survey participants table selected.');
            }
            $this->renderWrappedTemplate('checkintegrity', 'fix_view', $aData);
        }
    }

    /**
     * Fix integrity
     *
     */
    public function fixintegrity()
    {
        if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
            throw new CHttpException(401, "401 Unauthorized");
        }
        if (Yii::app()->request->getPost('ok') != 'Y') {
            throw new CHttpException(403);
        }
        $aDelete = $this->checkintegrity();
        $aData = array([
            'messsages' => array(),
            'warnings'  => array(),
            'errors'    => array(),
        ]);
        // TMSW Condition->Relevance:  Update this to process relevance instead
        if (isset($aDelete['conditions'])) {
            $aData = $this->deleteConditions($aDelete['conditions'], $aData);
        }

        if (isset($aDelete['questionattributes'])) {
            $aData = $this->deleteQuestionAttributes($aDelete['questionattributes'], $aData);
        }

        if ($aDelete['defaultvalues']) {
            $aData = $this->deleteDefaultValues($aData);
        }

        if ($aDelete['quotas']) {
            $aData = $this->deleteQuotas($aData);
        }

        if ($aDelete['quotals']) {
            $aData = $this->deleteQuotaLanguageSettings($aData);
        }

        if ($aDelete['quotamembers']) {
            $aData = $this->deleteQuotaMembers($aData);
        }

        if (isset($aDelete['assessments'])) {
            $aData = $this->deleteAssessments($aDelete['assessments'], $aData);
        }

        if (isset($aDelete['answers'])) {
            $aData = $this->deleteAnswers($aDelete['answers'], $aData);
        }

        if (isset($aDelete['answer_l10ns'])) {
            $aData = $this->deleteAnswerL10ns($aDelete['answer_l10ns'], $aData);
        }

        if (isset($aDelete['surveys'])) {
            $aData = $this->deleteSurveys($aDelete['surveys'], $aData);
        }

        if (isset($aDelete['surveylanguagesettings'])) {
            $aData = $this->deleteSurveyLanguageSettings($aDelete['surveylanguagesettings'], $aData);
        }

        if (isset($aDelete['questions'])) {
            $aData = $this->deleteQuestions($aDelete['questions'], $aData);
        }

        if (isset($aDelete['question_l10ns'])) {
            $aData = $this->deleteQuestionL10ns($aDelete['question_l10ns'], $aData);
        }

        if (isset($aDelete['groups'])) {
            $aData = $this->deleteGroups($aDelete['groups'], $aData);
        }

        if (isset($aDelete['group_l10ns'])) {
            $aData = $this->deleteGroupL10ns($aDelete['group_l10ns'], $aData);
        }

        if (isset($aDelete['user_in_groups'])) {
            $aData = $this->deleteUserInGroups($aDelete['user_in_groups'], $aData);
        }

        if (isset($aDelete['orphansurveytables'])) {
            $aData = $this->dropOrphanSurveyTables($aDelete['orphansurveytables'], $aData);
        }

        if (isset($aDelete['orphantokentables'])) {
            $aData = $this->deleteOrphanTokenTables($aDelete['orphantokentables'], $aData);
        }

        $this->renderWrappedTemplate('checkintegrity', 'fix_view', $aData);
    }

    /**
     * Delete orphan token tables
     *
     * @param array $tokenTables
     * @param array $aData
     * @return array
     */
    private function deleteOrphanTokenTables(array $tokenTables, array $aData)
    {
        foreach ($tokenTables as $aTokenTable) {
            Yii::app()->db->createCommand()->dropTable($aTokenTable);
            $aData['messages'][] = sprintf(gT('Deleting orphan survey participants table: %s'), $aTokenTable);
        }
        return $aData;
    }

    /**
     * Drop orphan survey tables
     *
     * @param array $surveyTables
     * @param array $aData
     * @return array
     */
    private function dropOrphanSurveyTables(array $surveyTables, array $aData)
    {
        foreach ($surveyTables as $aSurveyTable) {
            Yii::app()->db->createCommand()->dropTable($aSurveyTable);
            $aData['messages'][] = sprintf(gT('Deleting orphan survey table: %s'), $aSurveyTable);
        }
        return $aData;
    }

    /**
     * This function deletes groups
     * @param array[] $groups to be deleted
     * @param array $aData for view generation
     * @return array
     */
    private function deleteGroups(array $groups, array $aData)
    {
        $gids = array_unique(array_column($groups, 'gid'));
        $count = 0;
        foreach ($gids as $gid) {
            $deleted = QuestionGroup::model()->deleteAll("gid = :gid", array(":gid" => $gid));
            if ($deleted) {
                $count += $deleted;
            } else {
                $aData['warnings'][] = sprintf(gT('Unable to delete question group %s'), $gid);
            }
        }
        $aData['messages'][] = sprintf(gT('Deleting groups: %u groups deleted'), $count);
        return $aData;
    }

    /**
     * This function deletes group localizations
     * @param array[] $groupLocalizations to be deleted
     * @param array $aData for view generation
     * @return array
     */
    private function deleteGroupL10ns(array $groupLocalizations, array $aData)
    {
        $groupsDeleted = array();// Keep for multilingual survey (alt : make an array_unique_mutilplekeys function)
        $count = 0;
        foreach ($groupLocalizations as $group) {
            $deleted = QuestionGroupL10n::model()->deleteAll('gid=:gid AND id=:id', array(':gid' => $group['gid'], ':id' => $group['id']));
            if ($deleted) {
                $count += $deleted;
                $groupL10nsDeleted[] = array($group['gid'],$group['id']);
            } else {
                $aData['warnings'][] = sprintf(gT('Unable to delete groups text %s, code %s'), $group['gid'], $group['id']);
            }
        }
        $aData['messages'][] = sprintf(gT('Deleting group texts: %u entries deleted'), $count);
        return $aData;
    }


    /**
     * This function deletes UserInGroup
     * @param array[] $UserInGroup to be deleted
     * @param array $aData for view generation
     * @return array
     */
    private function deleteUserInGroups(array $userInGroups, array $aData)
    {
        $ugids = array();
        foreach ($userInGroups as $group) {
            $ugids[] = $group['ugid'];
        }

        $criteria = new CDbCriteria();
        $criteria->addInCondition('ugid', $ugids);
        $deletedRows = UserInGroup::model()->deleteAll($criteria);
        if ($deletedRows === count($userInGroups)) {
            $aData['messages'][] = sprintf(gT('Deleting orphaned user group assignments: %u assignments deleted'), count($userInGroups));
        }
        return $aData;
    }

    /**
     * This function deletes questions
     * @param array[] $questions to be deleted
     * @param array $aData for view generation
     * @return array
     */
    private function deleteQuestions(array $questions, array $aData)
    {
        $qids = array_unique(array_column($questions, 'qid'));
        $count = 0;
        foreach ($qids as $qid) {
            $deleted = Question::model()->deleteAll("qid = :qid", array(":qid" => $qid));
            if ($deleted) {
                $count += $deleted;
            } else {
                $aData['warnings'][] = sprintf(gT('Unable to delete question %s'), $qid);
            }
        }
        $aData['messages'][] = sprintf(gT('Deleting questions: %u questions deleted'), $count);
        return $aData;
    }

    /**
     * This function deletes question localizations
     * @param array[] $questionLocalizations to be deleted
     * @param array $aData for view generation
     * @return array
     */
    private function deleteQuestionL10ns(array $questionLocalizations, array $aData)
    {
        $questionL10nsDeleted = array();
        $count = 0;
        foreach ($questionLocalizations as $question) {
            $deleted = QuestionL10n::model()->deleteAll('qid=:qid AND id=:id', array(':qid' => $question['qid'], ':id' => $question['id']));
            if ($deleted) {
                $count += $deleted;
                $questionL10nsDeleted[] = array($question['qid'],$question['id']);
            } else {
                $aData['warnings'][] = sprintf(gT('Unable to delete question text %s, code %s'), $question['qid'], $question['id']);
            }
        }
        $aData['messages'][] = sprintf(gT('Deleting question texts: %u entries deleted'), $count);
        return $aData;
    }

    /**
     * This function deletes surveyLanguageSettings
     * @param array[] $surveyLanguageSettings to be deleted
     * @param array $aData for view generation
     * @return array
     */
    private function deleteSurveyLanguageSettings(array $surveyLanguageSettings, array $aData)
    {
        $slids = array_unique(array_column($surveyLanguageSettings, 'slid'));
        $count = 0;
        foreach ($slids as $slid) {
            $deleted = SurveyLanguageSetting::model()->deleteAll("surveyls_survey_id = :slid", array(":slid" => $slid));
            if ($deleted) {
                $count += $deleted;
            } else {
                $aData['warnings'][] = sprintf(gT('Unable to delete survey languagesettings %s'), $slid);
            }
        }
        $aData['messages'][] = sprintf(gT('Deleting survey languagesettings: %u survey languagesettings deleted'), $count);
        return $aData;
    }

    /**
     * This function deletes surveys
     * @param array[] $surveys to be deleted
     * @param array $aData for view generation
     * @return array
     */
    private function deleteSurveys(array $surveys, array $aData)
    {
        $count = 0;
        foreach ($surveys as $survey) {
            $deleted = Survey::model()->deleteByPk($survey['sid']);
            if ($deleted) {
                $count += $deleted;
            } else {
                $aData['warnings'][] = sprintf(gT('Unable to delete survey %s'), $survey['sid']);
            }
        }
        $aData['messages'][] = sprintf(gT('Deleting surveys: %u surveys deleted'), $count);
        return $aData;
    }

    /**
     * This function deletes answers
     * @param array[] $answers to be deleted
     * @param array   $aData   for view generation
     * @return array
     */
    private function deleteAnswers(array $answers, array $aData)
    {
        $answersDeleted = array();// Keep for multilingual survey (alt : make an array_unique_mutilplekeys function)
        $count = 0;
        foreach ($answers as $answer) {
            if (!in_array(array($answer['qid'],$answer['code']), $answersDeleted)) {
                $deleted = Answer::model()->deleteAll('qid=:qid AND code=:code', array(':qid' => $answer['qid'], ':code' => $answer['code']));
                if ($deleted) {
                    $count += $deleted;
                    $answersDeleted[] = array($answer['qid'],$answer['code']);
                } else {
                    $aData['warnings'][] = sprintf(gT('Unable to delete answer %s, code %s'), $answer['qid'], $answer['code']);
                }
            }
        }
        $aData['messages'][] = sprintf(gT('Deleting answers: %u answers deleted'), $count);
        return $aData;
    }

    /**
     * This function deletes answers localizations
     * @param array[] $answers localizations to be deleted
     * @param array   $aData   for view generation
     * @return array
     */
    private function deleteAnswerL10ns(array $answers, array $aData)
    {
        $answersDeleted = array();// Keep for multilingual survey (alt : make an array_unique_mutilplekeys function)
        $count = 0;
        foreach ($answers as $answer) {
            $deleted = AnswerL10n::model()->deleteAll('aid=:aid AND id=:id', array(':aid' => $answer['aid'], ':id' => $answer['id']));
            if ($deleted) {
                $count += $deleted;
                $answerL10nsDeleted[] = array($answer['aid'],$answer['id']);
            } else {
                $aData['warnings'][] = sprintf(gT('Unable to delete answer %s, code %s'), $answer['aid'], $answer['id']);
            }
        }
        $aData['messages'][] = sprintf(gT('Deleting answer localizations: %u entries deleted'), $count);
        return $aData;
    }

    /**
     * This function deletes Assessments
     * @param array[] $assessments to be deleted
     * @param array   $aData       for view generation
     * @return array
     */
    private function deleteAssessments(array $assessments, array $aData)
    {
        $assessmentids = array_unique(array_column($assessments, 'id'));
        $count = 0;
        foreach ($assessmentids as $assessmentid) {
            $deleted = Assessment::model()->deleteAll("id = :id", array(":id" => $assessmentid));
            if ($deleted) {
                $count += $deleted;
            } else {
                $aData['warnings'][] = sprintf(gT('Unable to delete assessment %s'), $assessmentid);
            }
        }
        $aData['messages'][] = sprintf(gT('Deleting assessments: %u assessment entries deleted'), $count);
        return $aData;
    }

    /**
     * This function deletes QuotaMember by join on question
     * @param array $aData for view generation
     * @return array
     */
    private function deleteQuotaMembers(array $aData)
    {
        $oCriteria = new CDbCriteria();
        $oCriteria->join = 'LEFT JOIN {{questions}} q ON t.qid=q.qid LEFT JOIN {{surveys}} s ON t.sid=s.sid';
        $oCriteria->condition = '(q.qid IS NULL) OR (s.sid IS NULL)';
        $count = 0;
        $aRecords = QuotaMember::model()->findAll($oCriteria);
        foreach ($aRecords as $aRecord) {
            $deleted = QuotaMember::model()->deleteAllByAttributes($aRecord);
            $count += $deleted;
        }
        $aData['messages'][] = sprintf(gT('Deleting orphaned quota members: %u quota members deleted'), $count);
        return $aData;
    }

    /**
     * This function deletes quota language settings without related main entries
     * @param array $aData for view generation
     * @return array
     */
    private function deleteQuotaLanguageSettings(array $aData)
    {
        $oCriteria = new CDbCriteria();

        if (App()->db->driverName == 'pgsql') {
            // This is much slower than the MySQL version, but it works
            // PostgreSQL does not support DELETE with JOIN
            $oCriteria->condition = '{{quota_languagesettings}}.quotals_quota_id not in (select id from {{quota}})';
        } else {
            $oCriteria->join = 'LEFT JOIN {{quota}} q ON {{quota_languagesettings}}.quotals_quota_id=q.id';
            $oCriteria->condition = '(q.id IS NULL)';
        }
        $count = QuotaLanguageSetting::model()->deleteAll($oCriteria);
        $aData['messages'][] = sprintf(gT('Deleting orphaned quota languages: %u quota languages deleted'), $count);
        return $aData;
    }

    /**
     * This function deletes quota entries which not having a related survey entry
     * @param array $aData for view generation
     * @return array
     */
    private function deleteQuotas(array $aData)
    {
        $oCriteria = new CDbCriteria();

        if (App()->db->driverName == 'pgsql') {
            // This is much slower than the MySQL version, but it works
            // PostgreSQL does not support DELETE with JOIN
            $oCriteria->condition = '{{quota}}.sid not in (select sid from {{surveys}})';
        } else {
            $oCriteria->join = 'LEFT JOIN {{surveys}} q ON {{quota}}.sid=q.sid';
            $oCriteria->condition = '(q.sid IS NULL)';
        }
        $count = Quota::model()->deleteAll($oCriteria);
        $aData['messages'][] = sprintf(gT('Deleting orphaned quotas: %u quotas deleted'), $count);
        return $aData;
    }

    /**
     * This function deletes default values by join on question
     * @param array $aData for view generation
     * @return array
     */
    private function deleteDefaultValues(array $aData)
    {
        $criteria = new CDbCriteria();
        $criteria->join = 'LEFT JOIN {{questions}} q ON t.qid=q.qid';
        $criteria->condition = 'q.qid IS NULL';

        $aRecords = DefaultValue::model()->findAll($criteria);
        $count = 0;
        foreach ($aRecords as $aRecord) {
            DefaultValueL10n::model()->deleteAllByAttributes(array('dvid' => $aRecord->dvid));
            $deleted = DefaultValue::model()->deleteAllByAttributes(array('dvid' => $aRecord->dvid));
            $count += $deleted ;
        }
        $aData['messages'][] = sprintf(gT('Deleting orphaned default values: %u default values deleted.'), $count);
        return $aData;
    }

    /**
     * This function deletes questionAttributes
     * @param array[] $questionAttributes to be deleted
     * @param array   $aData              for view generation
     * @return array
     */
    private function deleteQuestionAttributes(array $questionAttributes, array $aData)
    {
        $qids = array_unique(array_column($questionAttributes, 'qid'));
        $count = 0;
        foreach ($qids as $qid) {
            $deleted = QuestionAttribute::model()->deleteAll("qid = :qid", array(":qid" => $qid));
            if ($deleted) {
                $count += $deleted;
            } else {
                $aData['warnings'][] = sprintf(gT('Unable to delete question attributes for question %s'), $qid);
            }
        }
        $aData['messages'][] = sprintf(gT('Deleting question attributes: %u attributes deleted'), $count);
        return $aData;
    }

    /**
     * This function deletes conditions
     * @param array[] $conditions to be deleted
     * @param array   $aData      for view generation
     * @return array
     */
    private function deleteConditions(array $conditions, array $aData)
    {
        $cids = array_unique(array_column($conditions, 'cid'));
        $count = 0;
        foreach ($cids as $cid) {
            $deleted = Condition::model()->deleteByPk($cid);
            if ($deleted) {
                $count += $deleted;
            } else {
                $aData['warnings'][] = sprintf(gT('Unable to delete condition %s'), $cid);
            }
        }
        $aData['messages'][] = sprintf(gT('Deleting conditions: %u conditions deleted'), $count);
        return $aData;
    }


    /**
     * This function checks the LimeSurvey database for logical consistency and returns an according array
     * containing all issues in the particular tables.
     * @return array Array with all found issues.
     * @throws CDbException
     */
    protected function checkintegrity()
    {
        /* Find is some fix is done */
        $bDirectlyFixed = false;
        $aFullOldSIDs = array();
        // Delete survey and global permissions if the user does not exist
        $oCriteria = new CDbCriteria();
        $oCriteria->join = 'LEFT JOIN {{users}} u ON {{permissions}}.uid=u.uid';
        $oCriteria->addCondition('u.uid IS NULL');
        $oCriteria->addCondition("{{permissions}}.entity <> 'role'");
        if (App()->db->driverName == 'pgsql') {
            $oCriteria->join = 'USING {{users}} u';
            $oCriteria->addCondition('{{permissions}}.uid=u.uid');
        }
        if (Permission::model()->deleteAll($oCriteria)) {
            $bDirectlyFixed = true;
        }

        // Delete survey permissions if the survey does not exist
        $oCriteria = new CDbCriteria();
        $oCriteria->join = 'LEFT JOIN {{surveys}} s ON {{permissions}}.entity_id=s.sid';
        $oCriteria->condition = "(s.sid IS NULL AND entity='survey')";
        if (App()->db->driverName == 'pgsql') {
            $oCriteria->join = 'USING {{surveys}} s';
            $oCriteria->condition = "{{permissions}}.entity_id=s.sid AND (s.sid IS NULL AND entity='survey')";
        }
        if (Permission::model()->deleteAll($oCriteria)) {
            $bDirectlyFixed = true;
        }

        // Deactivate surveys that have a missing response table
        $survey = new SurveyLight();
        $oSurveys = $survey->findAll(array('order' => 'sid'));
        $oDB = Yii::app()->getDb();
        $oDB->schemaCachingDuration = 0; // Deactivate schema caching
        Yii::app()->setConfig('Updating', true);

        foreach ($oSurveys as $oSurvey) {
            if ($oSurvey->isActive && !$oSurvey->hasResponsesTable) {
                Survey::model()->updateByPk($oSurvey->sid, array('active' => 'N'));
                $bDirectlyFixed = true;
            }
        }

        /**
         * Check for active surveys if questions are in the correct group
         * This will only run if an additional URL parameter checkResponseTableFields=y is set
         * This is to prevent this costly check from running on every page load
         */
        if (Yii::app()->request->getParam('checkResponseTableFields') == 'y') {
            foreach ($oSurveys as $oSurvey) {
                // This actually clears the schema cache, not just refreshes it
                $oDB->schema->refresh();
                // We get the active surveys
                if ($oSurvey->isActive && $oSurvey->hasResponsesTable) {
                    $model    = SurveyDynamic::model($oSurvey->sid);
                    $aColumns = $model->getMetaData()->columns;
                    $aQids    = array();

                    // We get the columns of the responses table
                    foreach ($aColumns as $oColumn) {
                        // Question columns start with the SID
                        if (strpos((string) $oColumn->name, (string)$oSurvey->sid) !== false) {
                            // Fileds are separated by X
                            $aFields = explode('X', (string) $oColumn->name);

                            if (isset($aFields[1])) {
                                $sGid = $aFields[1];

                                // QID field can be more than just QID, like: 886other or 886A1
                                // So we clean it by finding the first alphabetical character
                                $sDirtyQid = $aFields[2];
                                preg_match('~[a-zA-Z_#]~i', $sDirtyQid, $match, PREG_OFFSET_CAPTURE);

                                if (isset($match[0][1])) {
                                    $sQID = substr($sDirtyQid, 0, $match[0][1]);
                                } else {
                                    // It was just the QID.... (maybe)
                                    $sQID = $sDirtyQid;
                                }

                                // Here, we get the question as defined in backend
                                try {
                                    $oQuestion = Question::model()->findByAttributes(['qid' => $sQID , 'sid' => $oSurvey->sid]);
                                } catch (Exception $e) {
                                    // QID potentially invalid , see #17458, reset $oQuestion
                                    $oQuestion = null;
                                }
                                if (is_a($oQuestion, 'Question')) {
                                    // We check if its GID is the same as the one defined in the column name
                                    if ($oQuestion->gid != $sGid) {
                                        // If not, we change the column name
                                        $sNvColName = $oSurvey->sid . 'X' . $oQuestion->group->gid . 'X' . $sDirtyQid;

                                        if (array_key_exists($sNvColName, $aColumns)) {
                                            // This case will not happen often, only when QID + Subquestion ID == QID of a question in the target group
                                            // So we'll change the group of the question question group table (so in admin interface, not in frontend)
                                            $oQuestion->gid = $sGid;
                                            $oQuestion->save();
                                        } else {
                                            $oTransaction = $oDB->beginTransaction();
                                            $oDB->createCommand()->renameColumn($model->tableName(), $oColumn->name, $sNvColName);
                                            $oTransaction->commit();
                                        }
                                    }
                                } else {
                                    // QID not found: The function to split the fieldname into the SGQA data is not 100% reliable
                                    // So for certain question types (for example Text Array) the field name cannot be properly derived
                                    // In this case just ignore the field - see also https://bugs.gitit-tech.com/view.php?id=15642
                                    // There is still a extremely  low chance that an unwanted rename happens if a collision like this happens in the same survey
                                }
                            }
                        }
                    }
                }
            }

            $oDB->schema->refresh();
            $oDB->schemaCachingDuration = 3600;
            $oDB->schema->getTables();
            $oDB->active = false;
            $oDB->active = true;
            User::model()->refreshMetaData();
            Yii::app()->db->schema->getTable('{{surveys}}', true);
            Yii::app()->db->schema->getTable('{{templates}}', true);
            Survey::model()->refreshMetaData();
        }
        /* Check method before using #14596 */
        if (method_exists(Yii::app()->cache, 'flush')) {
            Yii::app()->cache->flush();
        }
        if (method_exists(Yii::app()->cache, 'gc')) {
            Yii::app()->cache->gc();
        }

        Yii::app()->setConfig('Updating', false);

        unset($oSurveys);

        // Fix subquestions
        fixSubquestions();

        /*** Check for active survey tables with missing survey entry or where survey entry is inactivate and rename them ***/
        $sDBPrefix = Yii::app()->db->tablePrefix;
        $aResult = Yii::app()->db->createCommand(dbSelectTablesLike('{{survey}}\_%'))->queryColumn();
        $sSurveyIDs = Yii::app()->db->createCommand("select sid from {{surveys}} where active='Y'")->queryColumn();
        foreach ($aResult as $aRow) {
            $sTableName = (string) substr((string) $aRow, strlen((string) $sDBPrefix));
            if ($sTableName == 'survey_links' || $sTableName == 'survey_url_parameters') {
                continue;
            }
            $aTableName = explode('_', $sTableName);
            if (isset($aTableName[1]) && ctype_digit($aTableName[1])) {
                $iSurveyID = $aTableName[1];
                if (!in_array($iSurveyID, $sSurveyIDs)) {
                    $datestamp = time();
                    $date = date('YmdHis', $datestamp); //'His' adds 24hours+minutes to name to allow multiple deactiviations in a day
                    $DBDate = date('Y-m-d H:i:s', $datestamp);
                    $userID = Yii::app()->user->getId();
                    // Check if it's really a survey_XXX table mantis #14938
                    if (empty($aTableName[2])) {
                        $sOldTable = "survey_{$iSurveyID}";
                        $sNewTable = "old_survey_{$iSurveyID}_{$date}";
                        Yii::app()->db->createCommand()->renameTable("{{{$sOldTable}}}", "{{{$sNewTable}}}");
                        $archivedTokenSettings = new ArchivedTableSettings();
                        $archivedTokenSettings->survey_id = $iSurveyID;
                        $archivedTokenSettings->user_id = $userID;
                        $archivedTokenSettings->tbl_name = $sNewTable;
                        $archivedTokenSettings->tbl_type = 'response';
                        $archivedTokenSettings->created = $DBDate;
                        $archivedTokenSettings->properties = json_encode(Response::getEncryptedAttributes($iSurveyID));
                        $archivedTokenSettings->save();
                        $bDirectlyFixed = true;
                    }
                    if (!empty($aTableName[2]) && $aTableName[2] == "timings" && empty($aTableName[3])) {
                        $sOldTable = "survey_{$iSurveyID}_timings";
                        $sNewTable = "old_survey_{$iSurveyID}_timings_{$date}";
                        Yii::app()->db->createCommand()->renameTable("{{{$sOldTable}}}", "{{{$sNewTable}}}");
                        $archivedTokenSettings = new ArchivedTableSettings();
                        $archivedTokenSettings->survey_id = $iSurveyID;
                        $archivedTokenSettings->user_id = $userID;
                        $archivedTokenSettings->tbl_name = $sNewTable;
                        $archivedTokenSettings->tbl_type = 'timings';
                        $archivedTokenSettings->created = $DBDate;
                        $archivedTokenSettings->properties = '';
                        $archivedTokenSettings->save();
                        $bDirectlyFixed = true;
                    }
                }
            }
        }

        /*** Check for active survey participants tables with missing survey entry ***/
        $aResult = Yii::app()->db->createCommand(dbSelectTablesLike('{{tokens}}\_%'))->queryColumn();
        foreach ($aResult as $aRow) {
            $sTableName = (string) substr((string) $aRow, strlen((string) $sDBPrefix));
            $aTableName = explode('_', $sTableName);
            $iSurveyID  = (int) substr($sTableName, strpos($sTableName, '_') + 1);
            if (isset($aTableName[1]) && ctype_digit($aTableName[1]) && empty($aTableName[2])) { // Check if it's really a token_XXX table mantis #14938
                if (!in_array($iSurveyID, $sSurveyIDs)) {
                    $sDate = (string) date('YmdHis') . rand(1, 1000);
                    $sOldTable = "tokens_{$iSurveyID}";
                    $sNewTable = "old_tokens_{$iSurveyID}_{$sDate}";
                    Yii::app()->db->createCommand()->renameTable("{{{$sOldTable}}}", "{{{$sNewTable}}}");
                    $bDirectlyFixed = true;
                }
            }
        }

        /**********************************************************************/
        /*     Check conditions                                               */
        /**********************************************************************/
        $okQuestion = array();
        $sQuery = 'SELECT cqid,cid,cfieldname,qid FROM {{conditions}}';
        $aConditions = Yii::app()->db->createCommand($sQuery)->queryAll();
        $aDelete = array();
        foreach ($aConditions as $condition) {
            if ($condition['cqid'] != 0) {
                // skip case with cqid=0 for codnitions on {TOKEN:EMAIL} for instance
                if (!array_key_exists($condition['cqid'], $okQuestion)) {
                    $iRowCount = Question::model()->countByAttributes(array('qid' => $condition['cqid']));
                    if (!$iRowCount) {
                        $aDelete['conditions'][] = array('cid' => $condition['cid'], 'reason' => gT('No matching CQID'));
                    } else {
                        $okQuestion[$condition['cqid']] = $condition['cqid'];
                    }
                }
            }
            // Check that QID exists
            if (!array_key_exists($condition['qid'], $okQuestion)) {
                $iRowCount = Question::model()->countByAttributes(array('qid' => $condition['qid']));
                if (!$iRowCount) {
                    $aDelete['conditions'][] = array('cid' => $condition['cid'], 'reason' => gT('No matching QID'));
                } else {
                    $okQuestion[$condition['qid']] = $condition['qid'];
                }
            }
            //Only do this if there actually is a 'cfieldname'
            if ($condition['cfieldname']) {
                // only if cfieldname isn't Tag such as {TOKEN:EMAIL} or any other token
                if (preg_match('/^\+{0,1}[0-9]+X[0-9]+X*$/', (string) $condition['cfieldname'])) {
                    list ($surveyid, $gid, $rest) = explode('X', (string) $condition['cfieldname']);

                    $iRowCount = count(QuestionGroup::model()->findAllByAttributes(array('gid' => $gid)));
                    if (!$iRowCount) {
                        $aDelete['conditions'][] = array(
                            'cid'    => $condition['cid'],
                            'reason' => gT('No matching CFIELDNAME group!') . " ($gid) ({$condition['cfieldname']})"
                        );
                    }
                }
            } elseif (!$condition['cfieldname']) {
                $aDelete['conditions'][] = array(
                    'cid'    => $condition['cid'],
                    'reason' => gT('No CFIELDNAME field set!') . " ({$condition['cfieldname']})"
                );
            }
        }
        unset($okQuestion);
        unset($aConditions);

        /**********************************************************************/
        /*     Check question attributes                                      */
        /**********************************************************************/
        $question_attributes = QuestionAttribute::model()->findAllBySql('select qid from {{question_attributes}} where qid not in (select qid from {{questions}})');
        foreach ($question_attributes as $question_attribute) {
            $aDelete['questionattributes'][] = array('qid' => $question_attribute['qid']);
        }

        /**********************************************************************/
        /*     Check default values                                           */
        /**********************************************************************/
        $oCriteria = new CDbCriteria();
        $oCriteria->join = 'LEFT JOIN {{questions}} q ON t.qid=q.qid';
        $oCriteria->condition = 'q.qid IS NULL';
        $aDelete['defaultvalues'] = DefaultValue::model()->count($oCriteria);

        /**********************************************************************/
        /*     Check quotas                                                   */
        /**********************************************************************/

        $oCriteria = new CDbCriteria();
        $oCriteria->join = 'LEFT JOIN {{surveys}} s ON t.sid=s.sid';
        $oCriteria->condition = '(s.sid IS NULL)';
        $aDelete['quotas'] = Quota::model()->count($oCriteria);

        /**********************************************************************/
        /*     Check quota languagesettings                                   */
        /**********************************************************************/
        $oCriteria = new CDbCriteria();
        $oCriteria->join = 'LEFT JOIN {{quota}} s ON t.quotals_quota_id=s.id';
        $oCriteria->condition = '(s.id IS NULL)';
        $aDelete['quotals'] = QuotaLanguageSetting::model()->count($oCriteria);

        /**********************************************************************/
        /*     Check quota members                                   */
        /**********************************************************************/
        $oCriteria = new CDbCriteria();
        $oCriteria->join = 'LEFT JOIN {{questions}} q ON t.qid=q.qid LEFT JOIN {{surveys}} s ON t.sid=s.sid';
        $oCriteria->condition = '(q.qid IS NULL) OR (s.sid IS NULL)';

        $aDelete['quotamembers'] = QuotaMember::model()->count($oCriteria);

        /**********************************************************************/
        /*     Check assessments                                              */
        /**********************************************************************/
        $oCriteria = new CDbCriteria();
        $oCriteria->compare('scope', 'T');
        $assessments = Assessment::model()->findAll($oCriteria);
        $sSurveyIDs = Yii::app()->db->createCommand("select sid from {{surveys}}")->queryColumn();
        foreach ($assessments as $assessment) {
            if (!in_array($assessment['sid'], $sSurveyIDs)) {
                $aDelete['assessments'][] = array('id' => $assessment['id'], 'assessment' => $assessment['name'], 'reason' => gT('No matching survey'));
            }
        }

        $oCriteria = new CDbCriteria();
        $oCriteria->compare('scope', 'G');
        $assessments = Assessment::model()->findAll($oCriteria);
        $quotedGroups = Yii::app()->db->quoteTableName('{{groups}}');

        $groupIds = Yii::app()->db->createCommand("select gid from $quotedGroups")->queryColumn();
        foreach ($assessments as $assessment) {
            if (!in_array($assessment['gid'], $groupIds)) {
                $aDelete['assessments'][] = array('id' => $assessment['id'], 'assessment' => $assessment['name'], 'reason' => gT('No matching group'));
            }
        }
        unset($assessments);
        /**********************************************************************/
        /*     Check answers                                                  */
        /**********************************************************************/
        $oCriteria = new CDbCriteria();
        $oCriteria->join = 'LEFT JOIN {{questions}} q ON t.qid=q.qid';
        $oCriteria->condition = '(q.qid IS NULL)';

        $answers = Answer::model()->findAll($oCriteria);
        foreach ($answers as $answer) {
            $aDelete['answers'][] = array('qid' => $answer['qid'], 'code' => $answer['code'], 'reason' => gT('No parent question'));
        }
        /**********************************************************************/
        /*     Check answers localizations
        /**********************************************************************/
        $oCriteria = new CDbCriteria();
        $oCriteria->join = 'LEFT JOIN {{answers}} a ON t.aid=a.aid';
        $oCriteria->condition = '(a.aid IS NULL)';

        $answers = AnswerL10n::model()->resetScope()->findAll($oCriteria);
        foreach ($answers as $answer) {
            $aDelete['answer_l10ns'][] = array('id' => $answer['id'], 'aid' => $answer['aid'], 'reason' => gT('No parent answer'));
        }
        /***************************************************************************/
        /*   Check survey languagesettings and restore them if they don't exist    */
        /***************************************************************************/

        $surveyModel = new SurveyLight();
        $surveys = $surveyModel->findAll();
        foreach ($surveys as $survey) {
            $aLanguages = $survey->additionalLanguages;
            $aLanguages[] = $survey->language;
            $languages = Yii::app()->db->createCommand("select surveyls_language from {{surveys_languagesettings}} where surveyls_survey_id=" . $survey->sid)->queryColumn();
            foreach ($aLanguages as $langname) {
                if (!in_array($langname, $languages)) {
                    $oLanguageSettings = new SurveyLanguageSetting();
                    $languagedetails = getLanguageDetails($langname);
                    $insertdata = array(
                        'surveyls_survey_id' => $survey->sid,
                        'surveyls_language' => $langname,
                        'surveyls_title' => '',
                        'surveyls_dateformat' => $languagedetails['dateformat']
                    );
                    foreach ($insertdata as $k => $v) {
                        $oLanguageSettings->$k = $v;
                    }
                    $oLanguageSettings->save();
                    $bDirectlyFixed = true;
                }
            }
        }

        /**********************************************************************/
        /*     Check survey language settings                                 */
        /**********************************************************************/
        $surveys_languagesettings = SurveyLanguageSetting::model()->resetScope()->with('survey')->findAll(array(
            'select' => 'surveyls_survey_id',
            'condition' => 'survey.sid IS NULL'
        ));
        foreach ($surveys_languagesettings as $surveys_languagesetting) {
            $aDelete['surveylanguagesettings'][] = array('slid' => $surveys_languagesetting['surveyls_survey_id'], 'reason' => gT('The related survey is missing.'));
        }

        /**********************************************************************/
        /*     Check questions                                                */
        /**********************************************************************/
        $oCriteria = new CDbCriteria();
        $quotedGroups = Yii::app()->db->quoteTableName('{{groups}}');
        $oCriteria->join = "LEFT JOIN {{surveys}} s ON t.sid=s.sid LEFT JOIN $quotedGroups g ON t.gid=g.gid";
        $oCriteria->condition = '(g.gid IS NULL) OR (s.sid IS NULL)';
        $questions = Question::model()->findAll($oCriteria);
        foreach ($questions as $question) {
            $aDelete['questions'][] = array('qid' => $question['qid'], 'reason' => gT('No matching group') . " ({$question['gid']})");
        }

        /**********************************************************************/
        /*     Check question localizations
        /**********************************************************************/
        $oCriteria = new CDbCriteria();
        $oCriteria->join = 'LEFT JOIN {{questions}} q ON t.qid=q.qid';
        $oCriteria->condition = '(q.qid IS NULL)';

        $questions = QuestionL10n::model()->resetScope()->findAll($oCriteria);
        foreach ($questions as $question) {
            $aDelete['question_l10ns'][] = array('id' => $question['id'], 'qid' => $question['qid'], 'reason' => gT('No parent question'));
        }


        /**********************************************************************/
        /*     Check groups                                                   */
        /**********************************************************************/
        $oCriteria = new CDbCriteria();
        $oCriteria->join = 'LEFT JOIN {{surveys}} s ON t.sid=s.sid';
        $oCriteria->condition = '(s.sid IS NULL)';
        $groups = QuestionGroup::model()->findAll($oCriteria);
        /** @var QuestionGroup $group */
        foreach ($groups as $group) {
            $aDelete['groups'][] = array('gid' => $group['gid'], 'reason' => gT('There is no matching survey.') . ' SID:' . $group['sid']);
        }

        /**********************************************************************/
        /*     Check group localizations
        /**********************************************************************/
        $oCriteria = new CDbCriteria();
        $quotedGroups = Yii::app()->db->quoteTableName('{{groups}}');
        $oCriteria->join = "LEFT JOIN {$quotedGroups} g ON t.gid=g.gid";
        $oCriteria->condition = '(g.gid IS NULL)';

        $groups = QuestionGroupL10n::model()->resetScope()->findAll($oCriteria);
        foreach ($groups as $group) {
            $aDelete['group_l10ns'][] = array('id' => $group['id'], 'gid' => $group['gid'], 'reason' => gT('No parent group'));
        }

        /**********************************************************************/
        /*     Check orphan user_in_groups                                    */
        /**********************************************************************/
        $oCriteria = new CDbCriteria();
        $oCriteria->join = 'LEFT JOIN {{user_groups}} ug ON t.ugid=ug.ugid';
        $oCriteria->condition = '(ug.ugid IS NULL)';
        $userInGroups = UserInGroup::model()->findAll($oCriteria);
        /** @var UserInGroup[] $userInGroups */
        foreach ($userInGroups as $userInGroup) {
            $aDelete['user_in_groups'][] = array('ugid' => $userInGroup->ugid,'uid' => $userInGroup->uid, 'reason' => sprintf(gT('There is no matching user %s in group %s.'), $userInGroup->uid, $userInGroup->ugid));
        }

        /**********************************************************************/
        /*     Check old survey tables                                        */
        /**********************************************************************/
        //1: Get list of 'old_survey' tables and extract the survey ID
        //2: Check if that survey ID still exists
        //3: If it doesn't offer it for deletion
        $sQuery = dbSelectTablesLike('{{old_survey}}%');
        $aTables = Yii::app()->db->createCommand($sQuery)->queryColumn();

        $aOldSIDs = array();

        foreach ($aTables as $sTable) {
            list($sOldText, $SurveyText, $iSurveyID, $sDate) = explode('_', substr((string) $sTable, strlen((string) $sDBPrefix)));
            $aOldSIDs[] = $iSurveyID;
            $aFullOldSIDs[$iSurveyID][] = $sTable;
        }
        $aOldSIDs = array_unique($aOldSIDs);

        $aSIDs = Yii::app()->db->createCommand("select sid from {{surveys}}")->queryColumn();
        foreach ($aOldSIDs as $iOldSID) {
            if (!in_array($iOldSID, $aSIDs)) {
                foreach ($aFullOldSIDs[$iOldSID] as $sTableName) {
                    $aDelete['orphansurveytables'][] = $sTableName;
                }
            } else {
                foreach ($aFullOldSIDs[$iOldSID] as $sTableName) {
                    $aTableParts = explode('_', substr($sTableName, strlen((string) $sDBPrefix)));
                    $sDateTime = $sType = '';
                    $iSurveyID = $aTableParts[2];

                    if (count($aTableParts) == 4) {
                        $sDateTime = $aTableParts[3];
                        $sType = gT('responses');
                    } elseif (count($aTableParts) == 5) {
                        //This is a timings table (

                        $sDateTime = $aTableParts[4];
                        $sType = gT('timings');
                    }

                    $iYear = (int) substr($sDateTime, 0, 4);
                    $iMonth = (int) substr($sDateTime, 4, 2);
                    $iDay = (int) substr($sDateTime, 6, 2);
                    $iHour = (int) substr($sDateTime, 8, 2);
                    $iMinute = (int) substr($sDateTime, 10, 2);
                    $sDate = (string) date('Y-m-d H:i:s', (int) mktime($iHour, $iMinute, 0, $iMonth, $iDay, $iYear));

                    $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);
                    Yii::app()->loadLibrary('Date_Time_Converter');
                    $datetimeobj = new Date_Time_Converter(dateShift($sDate, 'Y-m-d H:i:s', getGlobalSetting('timeadjust')), 'Y-m-d H:i:s');
                    $sDate = $datetimeobj->convert($dateformatdetails['phpdate'] . " H:i");

                    $sQuery = 'SELECT count(*) as recordcount FROM ' . $sTableName;
                    $aFirstRow = Yii::app()->db->createCommand($sQuery)->queryRow();
                    if ($aFirstRow['recordcount'] == 0) {
                    // empty table - so add it to immediate deletion
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
        //1: Get list of 'old_token' tables and extract the survey ID
        //2: Check if that survey ID still exists
        //3: If it doesn't offer it for deletion
        $sQuery = dbSelectTablesLike('{{old_token}}%');
        $aTables = Yii::app()->db->createCommand($sQuery)->queryColumn();

        $aTokenSIDs = array();
        $aFullOldTokenSIDs = array();

        foreach ($aTables as $sTable) {
            list($sOldText, $SurveyText, $iSurveyID, $sDateTime) = explode('_', substr((string) $sTable, strlen((string) $sDBPrefix)));
            $aTokenSIDs[] = $iSurveyID;
            $aFullOldTokenSIDs[$iSurveyID][] = $sTable;
        }
        $aOldTokenSIDs = array_unique($aTokenSIDs);
        $aSIDs = Yii::app()->db->createCommand("select sid from {{surveys}}")->queryColumn();
        foreach ($aOldTokenSIDs as $iOldTokenSID) {
            if (!in_array($iOldTokenSID, $aOldTokenSIDs)) {
                foreach ($aFullOldTokenSIDs[$iOldTokenSID] as $sTableName) {
                    $aDelete['orphantokentables'][] = $sTableName;
                }
            } else {
                foreach ($aFullOldTokenSIDs[$iOldTokenSID] as $sTableName) {
                    list($sOldText, $sTokensText, $iSurveyID, $sDateTime) = explode('_', substr($sTableName, strlen((string) $sDBPrefix)));
                    $iYear = (int) substr($sDateTime, 0, 4);
                    $iMonth = (int) substr($sDateTime, 4, 2);
                    $iDay = (int) substr($sDateTime, 6, 2);
                    $iHour = (int) substr($sDateTime, 8, 2);
                    $iMinute = (int) substr($sDateTime, 10, 2);
                    $sDate = (string) date('Y-m-d H:i:s', (int) mktime($iHour, $iMinute, 0, $iMonth, $iDay, $iYear));
                    $sQuery = 'SELECT count(*) as recordcount FROM ' . $sTableName;

                    $aFirstRow = Yii::app()->db->createCommand($sQuery)->queryRow();
                    if ($aFirstRow['recordcount'] == 0) {
                        // empty table - so add it to immediate deletion
                        $aDelete['orphantokentables'][] = $sTableName;
                    } else {
                        $aOldTokenTableAsk[] = array('table' => $sTableName, 'details' => sprintf(gT('Survey ID %d saved at %s containing %d record(s)'), $iSurveyID, $sDate, $aFirstRow['recordcount']));
                    }
                }
            }
        }

        if (
            $aDelete['defaultvalues'] == 0 && $aDelete['quotamembers'] == 0 &&
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

        // delete archivedTableSettings without archived table
        $archivedTableSettings = ArchivedTableSettings::model()->findAll();
        foreach ($archivedTableSettings as $archivedTableSetting) {
            if (Yii::app()->db->schema->getTable("{{{$archivedTableSetting->tbl_name}}}") === null) {
                $archivedTableSetting->delete();
            }
        }

        /**********************************************************************/
        /*     Check group sort order duplicates                              */
        /**********************************************************************/
        $aDelete['groupOrderDuplicates'] = $this->checkGroupOrderDuplicates();

        /**********************************************************************/
        /*     Check question sort order duplicates                           */
        /**********************************************************************/
        $aDelete['questionOrderDuplicates'] = $this->checkQuestionOrderDuplicates();

        /**********************************************************************/
        /*     CHECK CPDB SURVEY_LINKS TABLE FOR REDUNDENT Survey participants tableS       */
        /**********************************************************************/
        //1: Get distinct list of survey_link survey IDs, check if tokens
        //   table still exists for each one, and remove if not


        /* TODO */

        /**********************************************************************/
        /*     CHECK CPDB SURVEY_LINKS TABLE FOR REDUNDENT TOKEN ENTRIES      */
        /**********************************************************************/
        //1: For each survey_link, see if the matching entry still exists in
        //   the survey participants table and remove if it doesn't.


        /* Show a alert message is some fix is done */
        if ($bDirectlyFixed) {
            Yii::app()->setFlashMessage(gT("Some automatic fixes were already applied."), 'info');
        }

        return $aDelete;
    }

    /**
     * Check group order duplicates.
     * @return array
     */
    protected function checkGroupOrderDuplicates()
    {
        $quotedGroups = Yii::app()->db->quoteTableName('{{groups}}');
        $sQuery = "
            SELECT
                g.sid
            FROM $quotedGroups g
            JOIN {{surveys}} s ON s.sid = g.sid
            GROUP BY g.sid
            HAVING COUNT(DISTINCT g.group_order) != COUNT(g.gid)";
        $result = Yii::app()->db->createCommand($sQuery)->queryAll();
        if (!empty($result)) {
            foreach ($result as &$survey) {
                $survey['organizerLink'] = Yii::app()->getController()->createUrl(
                    'surveyAdministration/organize',
                    [
                        'surveyid' => $survey['sid'],
                    ]
                );
            }
        }
        return $result;
    }

    /**
     * Check question order duplicates.
     * @return array
     */
    protected function checkQuestionOrderDuplicates()
    {
         $quotedGroups = Yii::app()->db->quoteTableName('{{groups}}');
        $sQuery = "
            SELECT
                q.sid,
                q.gid,
                q.parent_qid,
                q.scale_id
            FROM {{questions}} q
            JOIN $quotedGroups g ON q.gid = g.gid
            JOIN {{surveys}} s ON s.sid = q.sid
            GROUP BY q.sid, q.gid, q.parent_qid, q.scale_id
            HAVING COUNT(DISTINCT question_order) != COUNT(qid);
            ";
        $result = Yii::app()->db->createCommand($sQuery)->queryAll();
        if (!empty($result)) {
            foreach ($result as &$info) {
                $info['viewSurveyLink'] = Yii::app()->getController()->createUrl(
                    'surveyAdministration/view',
                    [
                        'iSurveyID' => $info['sid']
                    ]
                );
                $info['viewGroupLink'] = Yii::app()->getController()->createUrl(
                    'questionGroupsAdministration/view',
                    [
                        'surveyid' => $info['sid'],
                        'gid' => $info['gid']
                    ]
                );
                if ($info['parent_qid'] != 0) {
                    $info['questionSummaryLink'] = Yii::app()->getController()->createUrl(
                        'questionAdministration/view',
                        [
                            'surveyid' => $info['sid'],
                            'gid' => $info['gid'],
                            'qid' => $info['parent_qid']
                        ]
                    );
                }
            }
        }
        return $result;
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function renderWrappedTemplate($sAction = 'checkintegrity', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        parent::renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }
}
