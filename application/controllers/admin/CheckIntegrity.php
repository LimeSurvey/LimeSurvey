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
     * The data consistency check itself only runs when the "Run data consistency
     * check" button is submitted (see fixintegrity()), not on every page load, so
     * this GET request never deletes anything for that section - it only fetches
     * the (separate) data redundancy check's current state.
     *
     * @throws Exception
     */
    public function index()
    {
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'checkintegrity.js');

        $aData = $this->checkintegrity();
        $aData['consistencyCheckRan'] = false;

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
                        $aData['messages'][] = sprintf(gT('Deleting survey participant list: %s'), $aTokenTable['table']);
                    }
                }
            }
            if (isset($aDelete['redundantsurveytables'])) {
                foreach ($aDelete['redundantsurveytables'] as $aSurveyTable) {
                    if (in_array($aSurveyTable['table'], $oldsmultidelete)) {
                        Yii::app()->db->createCommand()->dropTable($aSurveyTable['table']);
                        $aData['messages'][] = sprintf(gT('Deleting survey table: %s'), $aSurveyTable['table']);
                        $aData = $this->dropRelatedArchivedQuestionsTable($aSurveyTable['table'], $aData);
                    }
                }
            }
            if (count($aData['messages']) == 0) {
                $aData['messages'][] = gT('No old survey or survey participant list selected.');
            }
            $this->renderWrappedTemplate('checkintegrity', 'fix_view', $aData);
        }
    }

    /**
     * Fix integrity
     *
     * Runs the data consistency check (the only place it ever runs) and re-renders
     * the same page index() does, with the results of this run added on top, so the
     * admin sees the redundancy check's state alongside the consistency check's
     * feedback rather than a separate results page.
     */
    public function fixintegrity()
    {
        if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
            throw new CHttpException(401, "401 Unauthorized");
        }
        if (Yii::app()->request->getPost('ok') != 'Y') {
            throw new CHttpException(403);
        }
        $aFixResult = $this->applyAutomaticFixes();

        $aData = $this->checkintegrity();
        $aData['consistencyCheckRan'] = true;
        $aData['consistencyCheckMessages'] = $aFixResult['messages'];
        $aData['consistencyCheckWarnings'] = $aFixResult['warnings'];

        $aData['topbar']['title'] = gT('Check data integrity');
        $aData['topbar']['backLink'] = App()->createUrl('dashboard/view');

        $this->renderWrappedTemplate('checkintegrity', 'check_view', $aData);
    }

    /**
     * Runs the same detection-and-cleanup pass as the "Yes - Delete Them!" button
     * (the data consistency check, plus any empty/fully orphaned old survey and
     * participant list tables), with no permission/confirmation step of its own.
     *
     * Does NOT touch the data redundancy check's old survey/participant list tables
     * that still contain response data: deleting those is a deliberate, irreversible
     * choice that stays opt-in via fixredundancy().
     *
     * Used by both fixintegrity() (the web action) and the checkintegrity console
     * command, so the same cleanup logic stays runnable unattended.
     *
     * @return array{
     *     messages: string[],
     *     warnings: string[],
     *     integrityok: bool,
     *     redundantsurveytables: array[],
     *     redundanttokentables: array[],
     *     groupOrderDuplicates: array[],
     *     questionOrderDuplicates: array[]
     * }
     */
    public function applyAutomaticFixes()
    {
        $aDelete = $this->checkintegrity();
        $aData = array(
            'messages' => array(),
            'warnings' => array(),
        );
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

        // Also run when only 'quotas' is set: deleting an orphaned quota above can itself
        // orphan its language settings/members, which the upfront check (before that
        // deletion ran) could not have counted yet. Both methods re-query live state,
        // so calling them here catches those newly-orphaned rows in the same pass.
        if ($aDelete['quotals'] || $aDelete['quotas']) {
            $aData = $this->deleteQuotaLanguageSettings($aData);
        }

        if ($aDelete['quotamembers'] || $aDelete['quotas']) {
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

        if (!empty($aDelete['groupOrderDuplicates'])) {
            $aData = $this->fixGroupOrderDuplicates($aDelete['groupOrderDuplicates'], $aData);
        }

        if (!empty($aDelete['questionOrderDuplicates'])) {
            $aData = $this->fixQuestionOrderDuplicates($aDelete['questionOrderDuplicates'], $aData);
        }

        $aData['integrityok'] = $aDelete['integrityok'];
        $aData['redundantsurveytables'] = $aDelete['redundantsurveytables'] ?? array();
        $aData['redundanttokentables'] = $aDelete['redundanttokentables'] ?? array();
        $aData['groupOrderDuplicates'] = $aDelete['groupOrderDuplicates'] ?? array();
        $aData['questionOrderDuplicates'] = $aDelete['questionOrderDuplicates'] ?? array();

        return $aData;
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
            if ($this->dropTableIfExists($aTokenTable)) {
                $aData['messages'][] = sprintf(gT('Deleting orphan survey participant list: %s'), $aTokenTable);
            }
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
            if ($this->dropTableIfExists($aSurveyTable)) {
                $aData['messages'][] = sprintf(gT('Deleting orphan survey table: %s'), $aSurveyTable);
            }
            $aData = $this->dropRelatedArchivedQuestionsTable($aSurveyTable, $aData);
        }
        return $aData;
    }

    /**
     * Drops the archived questions table (old_questions_<sid>_<date>) related to a
     * given archived survey responses table (old_responses_<sid>_<date>), if it exists.
     *
     * On deactivation a survey responses table is archived as old_responses_<sid>_<date>
     * together with a snapshot of the questions as old_questions_<sid>_<date>. When the
     * archived responses table is removed, its related questions archive is no longer
     * needed and would otherwise be left orphaned in the database.
     *
     * @param string $sSurveyTableName Full (prefixed) name of the archived survey responses table
     * @param array $aData
     * @return array
     */
    private function dropRelatedArchivedQuestionsTable($sSurveyTableName, array $aData)
    {
        $sDBPrefix = Yii::app()->db->tablePrefix;
        // Only response archive tables (old_responses_<sid>_<date>) have a matching questions archive
        if (strpos((string) $sSurveyTableName, $sDBPrefix . 'old_responses_') !== 0) {
            return $aData;
        }
        $sQuestionsTableName = str_replace(
            $sDBPrefix . 'old_responses_',
            $sDBPrefix . 'old_questions_',
            (string) $sSurveyTableName
        );
        if ($this->dropTableIfExists($sQuestionsTableName)) {
            $aData['messages'][] = sprintf(gT('Deleting related archived questions table: %s'), $sQuestionsTableName);
        }
        return $aData;
    }

    /**
     * Drops a table, tolerating it already being gone. This tool can now run
     * unattended and repeatedly (see the checkintegrity console command), so a table
     * this pass detected can legitimately disappear before the drop runs - e.g. an
     * admin deleting the survey through the web UI concurrently - and that race
     * should not abort the whole automatic-fix pass.
     *
     * @param string $tableName
     * @return bool true if the table was dropped, false if it no longer existed
     */
    private function dropTableIfExists($tableName)
    {
        try {
            Yii::app()->db->createCommand()->dropTable($tableName);
            return true;
        } catch (CDbException $e) {
            return false;
        }
    }

    /**
     * This function deletes groups, cascading to their questions (and, through
     * Question::delete(), those questions' own child data) and group localizations.
     *
     * QuestionGroup::deleteWithDependency() cannot be reused here: it looks up the
     * group's survey and reads its 'active' status, which fatals when the survey is
     * missing - precisely the case for the orphan groups this method handles.
     *
     * @param array[] $groups to be deleted
     * @param array $aData for view generation
     * @return array
     */
    private function deleteGroups(array $groups, array $aData)
    {
        $gids = array_unique(array_column($groups, 'gid'));
        $reasonByGid = array_column($groups, 'reason', 'gid');
        foreach ($gids as $gid) {
            $qids = Yii::app()->db->createCommand()
                ->select('qid')
                ->from('{{questions}}')
                ->where('gid = :gid AND parent_qid = 0', array(':gid' => $gid))
                ->queryColumn();
            foreach ($qids as $qid) {
                $question = Question::model()->findByPk($qid);
                if ($question !== null) {
                    $question->delete();
                }
            }
            QuestionGroupL10n::model()->deleteAllByAttributes(array('gid' => $gid));
            $deleted = QuestionGroup::model()->deleteAll("gid = :gid", array(":gid" => $gid));
            if ($deleted) {
                $aData['messages'][] = sprintf(gT('Deleted question group %s (%s)'), $gid, $reasonByGid[$gid]);
            } else {
                $aData['warnings'][] = sprintf(gT('Unable to delete question group %s'), $gid);
            }
        }
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
        foreach ($groupLocalizations as $group) {
            $deleted = QuestionGroupL10n::model()->deleteAll('gid=:gid AND id=:id', array(':gid' => $group['gid'], ':id' => $group['id']));
            if ($deleted) {
                $aData['messages'][] = sprintf(gT('Deleted group text %s, code %s (%s)'), $group['gid'], $group['id'], $group['reason']);
            } else {
                $aData['warnings'][] = sprintf(gT('Unable to delete groups text %s, code %s'), $group['gid'], $group['id']);
            }
        }
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
        foreach ($userInGroups as $userInGroup) {
            $deleted = UserInGroup::model()->deleteAll(
                'ugid=:ugid AND uid=:uid',
                array(':ugid' => $userInGroup['ugid'], ':uid' => $userInGroup['uid'])
            );
            if ($deleted) {
                $aData['messages'][] = sprintf(
                    gT('Deleted user group assignment for user %s, group %s (%s)'),
                    $userInGroup['uid'],
                    $userInGroup['ugid'],
                    $userInGroup['reason']
                );
            } else {
                $aData['warnings'][] = sprintf(
                    gT('Unable to delete user group assignment for user %s, group %s'),
                    $userInGroup['uid'],
                    $userInGroup['ugid']
                );
            }
        }
        return $aData;
    }

    /**
     * This function deletes questions, cascading to their subquestions,
     * answer options and other child data (see Question::delete()).
     * @param array[] $questions to be deleted
     * @param array $aData for view generation
     * @return array
     */
    private function deleteQuestions(array $questions, array $aData)
    {
        $qids = array_unique(array_column($questions, 'qid'));
        $reasonByQid = array_column($questions, 'reason', 'qid');
        foreach ($qids as $qid) {
            $question = Question::model()->findByPk($qid);
            if ($question === null) {
                // Already removed by a previous cascading delete of its parent question in this batch
                continue;
            }
            if ($question->delete()) {
                $aData['messages'][] = sprintf(gT('Deleted question %s (%s)'), $qid, $reasonByQid[$qid]);
            } else {
                $aData['warnings'][] = sprintf(gT('Unable to delete question %s'), $qid);
            }
        }
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
        foreach ($questionLocalizations as $question) {
            $deleted = QuestionL10n::model()->deleteAll('qid=:qid AND id=:id', array(':qid' => $question['qid'], ':id' => $question['id']));
            if ($deleted) {
                $aData['messages'][] = sprintf(gT('Deleted question text %s, code %s (%s)'), $question['qid'], $question['id'], $question['reason']);
            } else {
                $aData['warnings'][] = sprintf(gT('Unable to delete question text %s, code %s'), $question['qid'], $question['id']);
            }
        }
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
        $reasonBySlid = array_column($surveyLanguageSettings, 'reason', 'slid');
        foreach ($slids as $slid) {
            $deleted = SurveyLanguageSetting::model()->deleteAll("surveyls_survey_id = :slid", array(":slid" => $slid));
            if ($deleted) {
                $aData['messages'][] = sprintf(
                    gT('Deleted %u survey languagesetting(s) for survey %s (%s)'),
                    $deleted,
                    $slid,
                    $reasonBySlid[$slid]
                );
            } else {
                $aData['warnings'][] = sprintf(gT('Unable to delete survey languagesettings %s'), $slid);
            }
        }
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
        foreach ($answers as $answer) {
            if (!in_array(array($answer['qid'],$answer['code']), $answersDeleted)) {
                $deleted = Answer::model()->deleteAll('qid=:qid AND code=:code', array(':qid' => $answer['qid'], ':code' => $answer['code']));
                if ($deleted) {
                    $answersDeleted[] = array($answer['qid'],$answer['code']);
                    $aData['messages'][] = sprintf(gT('Deleted answer %s, code %s (%s)'), $answer['qid'], $answer['code'], $answer['reason']);
                } else {
                    $aData['warnings'][] = sprintf(gT('Unable to delete answer %s, code %s'), $answer['qid'], $answer['code']);
                }
            }
        }
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
        foreach ($answers as $answer) {
            $deleted = AnswerL10n::model()->deleteAll('aid=:aid AND id=:id', array(':aid' => $answer['aid'], ':id' => $answer['id']));
            if ($deleted) {
                $aData['messages'][] = sprintf(gT('Deleted answer localization %s, id %s (%s)'), $answer['aid'], $answer['id'], $answer['reason']);
            } else {
                $aData['warnings'][] = sprintf(gT('Unable to delete answer %s, code %s'), $answer['aid'], $answer['id']);
            }
        }
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
        $reasonById = array_column($assessments, 'reason', 'id');
        $nameById = array_column($assessments, 'assessment', 'id');
        foreach ($assessmentids as $assessmentid) {
            $deleted = Assessment::model()->deleteAll("id = :id", array(":id" => $assessmentid));
            if ($deleted) {
                $aData['messages'][] = sprintf(
                    gT('Deleted assessment %s "%s" (%s)'),
                    $assessmentid,
                    $nameById[$assessmentid],
                    $reasonById[$assessmentid]
                );
            } else {
                $aData['warnings'][] = sprintf(gT('Unable to delete assessment %s'), $assessmentid);
            }
        }
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
        if ($count > 0) {
            $aData['messages'][] = sprintf(gT('Deleted %u orphaned quota rule(s)'), $count);
        }
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
        if ($count > 0) {
            $aData['messages'][] = sprintf(gT('Deleted %u orphaned quota language setting(s)'), $count);
        }
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
        if ($count > 0) {
            $aData['messages'][] = sprintf(gT('Deleted %u orphaned quota(s)'), $count);
        }
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
        if ($count > 0) {
            $aData['messages'][] = sprintf(gT('Deleted %u orphaned default value(s)'), $count);
        }
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
        foreach ($qids as $qid) {
            $deleted = QuestionAttribute::model()->deleteAll("qid = :qid", array(":qid" => $qid));
            if ($deleted) {
                $aData['messages'][] = sprintf(
                    gT('Deleted %u question attribute(s) for question %s (No matching question)'),
                    $deleted,
                    $qid
                );
            } else {
                $aData['warnings'][] = sprintf(gT('Unable to delete question attributes for question %s'), $qid);
            }
        }
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
        $reasonByCid = array_column($conditions, 'reason', 'cid');
        foreach ($cids as $cid) {
            $deleted = Condition::model()->deleteByPk($cid);
            if ($deleted) {
                $aData['messages'][] = sprintf(gT('Deleted condition %s (%s)'), $cid, $reasonByCid[$cid]);
            } else {
                $aData['warnings'][] = sprintf(gT('Unable to delete condition %s'), $cid);
            }
        }
        return $aData;
    }

    /**
     * Renumbers question groups with a duplicate group_order within their survey,
     * via QuestionGroup::updateGroupOrder(), which re-sorts by (group_order,
     * group_name) - group_name in the survey's base language as a stable tiebreaker
     * for groups that currently share the same group_order - then assigns
     * sequential, unique group_order values in that resulting order.
     *
     * @param array[] $groupOrderDuplicates rows of ['sid' => ..., 'organizerLink' => ...]
     *                as returned by checkGroupOrderDuplicates()
     * @param array $aData for view generation
     * @return array
     */
    private function fixGroupOrderDuplicates(array $groupOrderDuplicates, array $aData)
    {
        $sids = array_unique(array_column($groupOrderDuplicates, 'sid'));
        foreach ($sids as $sid) {
            QuestionGroup::model()->updateGroupOrder($sid);
        }
        $aData['messages'][] = sprintf(gT('Fixed duplicate group sort order for %u survey(s)'), count($sids));
        return $aData;
    }

    /**
     * Renumbers questions with a duplicate question_order within the same survey,
     * group, parent question and scale, via Question::updateQuestionOrder(), which
     * re-sorts by (question_order, title) - title being the question code, used as a
     * stable tiebreaker for questions that currently share the same question_order -
     * then assigns sequential, unique question_order values in that resulting order.
     *
     * @param array[] $questionOrderDuplicates rows of ['sid'=>, 'gid'=>, 'parent_qid'=>,
     *                'scale_id'=>, ...] as returned by checkQuestionOrderDuplicates()
     * @param array $aData for view generation
     * @return array
     */
    private function fixQuestionOrderDuplicates(array $questionOrderDuplicates, array $aData)
    {
        foreach ($questionOrderDuplicates as $duplicate) {
            Question::model()->updateQuestionOrder($duplicate['gid'], 1, $duplicate['parent_qid'], $duplicate['scale_id']);
        }
        $aData['messages'][] = sprintf(
            gT('Fixed duplicate question sort order for %u question group(s)/subquestion set(s)'),
            count($questionOrderDuplicates)
        );
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
        Permission::model()->deleteAll($oCriteria);

        // Delete survey permissions if the survey does not exist
        $oCriteria = new CDbCriteria();
        $oCriteria->join = 'LEFT JOIN {{surveys}} s ON {{permissions}}.entity_id=s.sid';
        $oCriteria->condition = "(s.sid IS NULL AND entity='survey')";
        if (App()->db->driverName == 'pgsql') {
            $oCriteria->join = 'USING {{surveys}} s';
            $oCriteria->condition = "{{permissions}}.entity_id=s.sid AND (s.sid IS NULL AND entity='survey')";
        }
        Permission::model()->deleteAll($oCriteria);

        // Deactivate surveys that have a missing response table
        $survey = new SurveyLight();
        $oSurveys = $survey->findAll(array('order' => 'sid'));
        $oDB = Yii::app()->getDb();
        $oDB->schemaCachingDuration = 0; // Deactivate schema caching
        Yii::app()->setConfig('Updating', true);

        foreach ($oSurveys as $oSurvey) {
            if ($oSurvey->isActive && !$oSurvey->hasResponsesTable) {
                Survey::model()->updateByPk($oSurvey->sid, array('active' => 'N'));
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
                $rawQuestions = Question::model()->findAll('sid = :sid', [':sid' => $oSurvey->sid]);
                $questions = [];
                foreach ($rawQuestions as $rawQuestion) {
                    $questions[$rawQuestion->qid] = $rawQuestion;
                }
                // We get the active surveys
                if ($oSurvey->isActive && $oSurvey->hasResponsesTable) {
                    $model    = SurveyDynamic::model($oSurvey->sid);
                    $aColumns = $model->getMetaData()->columns;
                    $aQids    = array();

                    // We get the columns of the responses table
                    foreach ($aColumns as $oColumn) {
                        // Question columns start with the SID
                        if (strpos((string) $oColumn->name, (string)$oSurvey->sid) !== false) {
                            // Fields are separated by '_' — extract the question id from the first segment
                            $qid = substr(explode("_", (string) $oColumn->Name)[0], 1);

                            if (isset($questions[$qid])) {
                                $sGid = $questions[$qid]->gid;

                                // QID field can be more than just QID, like: 886other or 886A1
                                // So we clean it by finding the first alphabetical character
                                $sQID = $qid;
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
                                        $sNvColName = $oColumn->Name;

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
                                    // In this case just ignore the field - see also https://bugs.limesurvey.org/view.php?id=15642
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
        $aResult = Yii::app()->db->createCommand(dbSelectTablesLike('{{responses}}\_%'))->queryColumn();
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
                    // Check if it's really a responses_XXX table mantis #14938
                    if (empty($aTableName[2])) {
                        $sOldTable = "responses_{$iSurveyID}";
                        $sNewTable = "old_responses_{$iSurveyID}_{$date}";
                        Yii::app()->db->createCommand()->renameTable("{{{$sOldTable}}}", "{{{$sNewTable}}}");
                        $archivedTokenSettings = new ArchivedTableSettings();
                        $archivedTokenSettings->survey_id = $iSurveyID;
                        $archivedTokenSettings->user_id = $userID;
                        $archivedTokenSettings->tbl_name = $sNewTable;
                        $archivedTokenSettings->tbl_type = 'response';
                        $archivedTokenSettings->created = $DBDate;
                        $archivedTokenSettings->properties = json_encode(Response::getEncryptedAttributes($iSurveyID));
                        $archivedTokenSettings->save();
                    }
                    if (!empty($aTableName[2]) && $aTableName[2] == "timings" && empty($aTableName[3])) {
                        $sOldTable = "timings_{$iSurveyID}";
                        $sNewTable = "old_timings_{$iSurveyID}_{$date}";
                        Yii::app()->db->createCommand()->renameTable("{{{$sOldTable}}}", "{{{$sNewTable}}}");
                        $archivedTokenSettings = new ArchivedTableSettings();
                        $archivedTokenSettings->survey_id = $iSurveyID;
                        $archivedTokenSettings->user_id = $userID;
                        $archivedTokenSettings->tbl_name = $sNewTable;
                        $archivedTokenSettings->tbl_type = 'timings';
                        $archivedTokenSettings->created = $DBDate;
                        $archivedTokenSettings->properties = '';
                        $archivedTokenSettings->save();
                    }
                }
            }
        }

        /*** Check for active survey participant lists with missing survey ***/
        $aResult = Yii::app()->db->createCommand(dbSelectTablesLike('{{tokens}}\_%'))->queryColumn();
        $sSurveyIDs = Yii::app()->db->createCommand("select sid from {{surveys}}")->queryColumn();
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
                //we have a cfieldname
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
        // resetScope() is required: QuestionAttribute's defaultScope indexes results by
        // the 'attribute' column, which is not selected here, so every row would collapse
        // into a single array entry (attribute === null for all) without this reset.
        $question_attributes = QuestionAttribute::model()->resetScope()->findAllBySql('select qid from {{question_attributes}} where qid not in (select qid from {{questions}})');
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
        /*     Check subquestions whose parent question is missing            */
        /**********************************************************************/
        $oCriteria = new CDbCriteria();
        $oCriteria->join = 'LEFT JOIN {{questions}} parentq ON t.parent_qid = parentq.qid';
        $oCriteria->condition = 't.parent_qid <> 0 AND parentq.qid IS NULL';
        $orphanSubquestions = Question::model()->findAll($oCriteria);
        foreach ($orphanSubquestions as $orphanSubquestion) {
            $aDelete['questions'][] = array('qid' => $orphanSubquestion['qid'], 'reason' => gT('No parent question'));
        }

        /**********************************************************************/
        /*     Check subquestions and answer options against question type    */
        /*     Some question types require subquestions, some require answer  */
        /*     options and some require both. Flag for deletion subquestions  */
        /*     and answer options that do not belong to the parent question's */
        /*     type.                                                          */
        /**********************************************************************/
        $aTypesWithoutSubquestions = array();
        $aTypesWithoutAnswers = array();
        foreach (QuestionType::modelsAttributes() as $sTypeCode => $aTypeAttributes) {
            // PHP casts numeric array keys to int; the type column is varchar, so force string for the DB comparison
            $sTypeCode = (string) $sTypeCode;
            if (empty($aTypeAttributes['subquestions'])) {
                $aTypesWithoutSubquestions[] = $sTypeCode;
            }
            if (empty($aTypeAttributes['answerscales'])) {
                $aTypesWithoutAnswers[] = $sTypeCode;
            }
        }

        // Subquestions belonging to a question whose type does not allow subquestions
        if (!empty($aTypesWithoutSubquestions)) {
            $oCriteria = new CDbCriteria();
            $oCriteria->join = 'INNER JOIN {{questions}} parentq ON t.parent_qid = parentq.qid';
            $oCriteria->addCondition('t.parent_qid <> 0');
            $oCriteria->addInCondition('parentq.type', $aTypesWithoutSubquestions);
            $orphanSubquestions = Question::model()->findAll($oCriteria);
            foreach ($orphanSubquestions as $orphanSubquestion) {
                $aDelete['questions'][] = array(
                    'qid' => $orphanSubquestion['qid'],
                    'reason' => gT('The question type does not allow subquestions')
                );
            }
        }

        // Answer options belonging to a question whose type does not allow answer options
        if (!empty($aTypesWithoutAnswers)) {
            $oCriteria = new CDbCriteria();
            $oCriteria->join = 'INNER JOIN {{questions}} q ON t.qid = q.qid';
            $oCriteria->addInCondition('q.type', $aTypesWithoutAnswers);
            $orphanAnswers = Answer::model()->findAll($oCriteria);
            foreach ($orphanAnswers as $orphanAnswer) {
                $aDelete['answers'][] = array(
                    'qid' => $orphanAnswer['qid'],
                    'code' => $orphanAnswer['code'],
                    'reason' => gT('The question type does not allow answer options')
                );
            }
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
        //1: Get list of 'old_responses' tables and extract the survey ID
        //2: Check if that survey ID still exists
        //3: If it doesn't offer it for deletion
        $sQuery = dbSelectTablesLike('{{old_responses}}%');
        $aTables = Yii::app()->db->createCommand($sQuery)->queryColumn();

        $aOldSIDs = array();

        foreach ($aTables as $sTable) {
            list($sOldText, $SurveyText, $iSurveyID, $sDate) = explode('_', substr((string) $sTable, strlen((string) $sDBPrefix)));
            $aOldSIDs[] = $iSurveyID;
            $aFullOldSIDs[$iSurveyID][] = $sTable;
        }
        $aOldSIDs = array_unique($aOldSIDs);

        // Auto-delete purely on record count: whether the parent survey still exists or
        // not, a table that still holds response data is never auto-deleted, only ever
        // offered for manual confirmation via the data redundancy check below.
        foreach ($aOldSIDs as $iOldSID) {
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

                $sQuery = 'SELECT count(*) as recordcount FROM ' . $sTableName;
                $aFirstRow = Yii::app()->db->createCommand($sQuery)->queryRow();
                if ($aFirstRow['recordcount'] == 0) {
                    // empty table - so add it to immediate deletion
                    $aDelete['orphansurveytables'][] = $sTableName;
                    continue;
                }

                $iYear = (int) substr($sDateTime, 0, 4);
                $iMonth = (int) substr($sDateTime, 4, 2);
                $iDay = (int) substr($sDateTime, 6, 2);
                $iHour = (int) substr($sDateTime, 8, 2);
                $iMinute = (int) substr($sDateTime, 10, 2);
                $sDate = (string) date('Y-m-d H:i:s', (int) mktime($iHour, $iMinute, 0, $iMonth, $iDay, $iYear));

                // No 'dateformat' is set in Yii::app()->session outside of a logged-in admin
                // session (e.g. when running from the checkintegrity console command); fall
                // back to dateformat 1 rather than passing null through to getDateFormatData(),
                // which would return its full lookup array instead of a single format's details.
                $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat'] ?? 1);
                // Yii::app()->loadLibrary() is only defined on the web application class;
                // this runs from the checkintegrity console command too, so import directly.
                Yii::import('application.libraries.Date_Time_Converter', true);
                $datetimeobj = new Date_Time_Converter(dateShift($sDate, 'Y-m-d H:i:s'), 'Y-m-d H:i:s');
                $sDate = $datetimeobj->convert($dateformatdetails['phpdate'] . " H:i");

                $aOldSurveyTableAsk[] = array('table' => $sTableName, 'details' => sprintf(gT('Survey ID %d saved at %s containing %d record(s) (%s)'), $iSurveyID, $sDate, $aFirstRow['recordcount'], $sType));
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

        // Auto-delete purely on record count: whether the parent survey still exists or
        // not, a table that still holds participant data is never auto-deleted, only
        // ever offered for manual confirmation via the data redundancy check below.
        foreach ($aOldTokenSIDs as $iOldTokenSID) {
            foreach ($aFullOldTokenSIDs[$iOldTokenSID] as $sTableName) {
                list($sOldText, $sTokensText, $iSurveyID, $sDateTime) = explode('_', substr($sTableName, strlen((string) $sDBPrefix)));
                $sQuery = 'SELECT count(*) as recordcount FROM ' . $sTableName;

                $aFirstRow = Yii::app()->db->createCommand($sQuery)->queryRow();
                if ($aFirstRow['recordcount'] == 0) {
                    // empty table - so add it to immediate deletion
                    $aDelete['orphantokentables'][] = $sTableName;
                    continue;
                }

                $iYear = (int) substr($sDateTime, 0, 4);
                $iMonth = (int) substr($sDateTime, 4, 2);
                $iDay = (int) substr($sDateTime, 6, 2);
                $iHour = (int) substr($sDateTime, 8, 2);
                $iMinute = (int) substr($sDateTime, 10, 2);
                $sDate = (string) date('Y-m-d H:i:s', (int) mktime($iHour, $iMinute, 0, $iMonth, $iDay, $iYear));

                $aOldTokenTableAsk[] = array('table' => $sTableName, 'details' => sprintf(gT('Survey ID %d saved at %s containing %d record(s)'), $iSurveyID, $sDate, $aFirstRow['recordcount']));
            }
        }

        /**********************************************************************/
        /*     CHECK OLD QUESTIONS TABLES                                     */
        /**********************************************************************/
        //1: Get list of 'old_questions' tables (old_questions_<sid>_<date>)
        //2: An old_questions table is only useful together with its matching
        //   old_responses_<sid>_<date> archive (it is used during reactivation).
        //   It is orphaned when the survey no longer exists or when its matching
        //   old_responses archive is gone, so offer it for immediate deletion.
        $sQuery = dbSelectTablesLike('{{old_questions}}%');
        $aQuestionsTables = Yii::app()->db->createCommand($sQuery)->queryColumn();

        $sQuery = dbSelectTablesLike('{{old_responses}}%');
        $aResponsesTables = Yii::app()->db->createCommand($sQuery)->queryColumn();
        $aSIDs = Yii::app()->db->createCommand("select sid from {{surveys}}")->queryColumn();

        foreach ($aQuestionsTables as $sTableName) {
            $aTableParts = explode('_', substr((string) $sTableName, strlen((string) $sDBPrefix)));
            // Expected format: old_questions_<sid>_<date> => 4 parts
            if (count($aTableParts) < 4) {
                continue;
            }
            $iQuestionsSID = $aTableParts[2];
            $sDateTime = $aTableParts[3];
            $sMatchingResponsesTable = $sDBPrefix . "old_responses_{$iQuestionsSID}_{$sDateTime}";
            if (!in_array($iQuestionsSID, $aSIDs) || !in_array($sMatchingResponsesTable, $aResponsesTables)) {
                $aDelete['orphansurveytables'][] = $sTableName;
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
        // Use getTableNames() (a plain list of table name strings) instead of getTable() per row:
        // getTable() loads and permanently caches a full CDbTableSchema (all columns, indexes, FKs)
        // for the rest of the request, so calling it once per archived table setting could retain
        // thousands of heavy schema objects in memory on installations with many archived tables.
        $archivedTableSettings = ArchivedTableSettings::model()->findAll();
        $aExistingTables = array_flip(Yii::app()->db->schema->getTableNames());
        foreach ($archivedTableSettings as $archivedTableSetting) {
            if (!isset($aExistingTables[$sDBPrefix . $archivedTableSetting->tbl_name])) {
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



        /* TODOs */
        /**********************************************************************/
        /*     CHECK CPDB SURVEY_LINKS TABLE FOR REDUNDANT Survey participant lists       */
        /*********************************************************************/
        //1: Get distinct list of survey_link survey IDs, check if tokens
        //   table still exists for each one, and remove if not

        /**********************************************************************/
        /*     CHECK CPDB SURVEY_LINKS TABLE FOR REDUNDANT TOKEN ENTRIES      */
        /**********************************************************************/
        //1: For each survey_link, see if the matching entry still exists in
        //   the survey participant list and remove if it doesn't.

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
