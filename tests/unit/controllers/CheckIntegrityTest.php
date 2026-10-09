<?php

namespace ls\tests\controllers;

use ls\tests\TestBaseClass;

/**
 * Regression tests for the "Check data integrity" tool: the DataIntegrityChecker
 * detection/fix logic and the CheckIntegrity admin action that exposes it.
 *
 * Deleting an orphaned question (no parent survey/group) or an orphaned
 * question group (no parent survey) must cascade to the child data that
 * references it (subquestions, answers, question attributes, group
 * localizations) instead of leaving those rows behind.
 */
class CheckIntegrityTest extends TestBaseClass
{
    /** @var \DataIntegrityChecker */
    private $integrityChecker;

    /** Imports the survey fixture shared by the integrity tests. */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $surveyFile = self::$surveysFolder . '/limesurvey_survey_143933.lss';
        self::importSurvey($surveyFile);
    }

    /** Prepares the integrity checker and administrator session used by each test. */
    public function setUp(): void
    {
        parent::setUp();
        \Yii::import('application.controllers.admin.CheckIntegrity', true);
        \Yii::app()->session['loginID'] = 1;
        // Normally set by the admin session; checkintegrity()'s old-survey-table check
        // reads this directly and isn't otherwise initialized outside a real request.
        \Yii::app()->session['dateformat'] = 1;
        $this->integrityChecker = new \DataIntegrityChecker();
    }

    /**
     * Invokes a non-public method on the DataIntegrityChecker under test.
     *
     * @param string $method Name of the method to invoke.
     * @param array $args Arguments to pass to it.
     * @return mixed The method's return value.
     */
    private function callMethod($method, array $args)
    {
        $reflection = new \ReflectionMethod(\DataIntegrityChecker::class, $method);
        $reflection->setAccessible(true);
        return $reflection->invokeArgs($this->integrityChecker, $args);
    }

    /** Verifies that deleting an orphan question also deletes its child data. */
    public function testDeleteQuestionsCascadesToSubquestionsAnswersAndAttributes()
    {
        $group = self::$testSurvey->groups[0];

        // Array (dual scale) question: supports both subquestions and answer options.
        $question = new \Question();
        $question->sid = self::$surveyId;
        $question->gid = $group->gid;
        $question->parent_qid = 0;
        $question->type = \QuestionType::QT_1_ARRAY_DUAL;
        $question->title = 'CIQPARENT';
        $question->question_order = 9999;
        $this->assertTrue($question->save(), 'Could not save parent question fixture: ' . json_encode($question->errors));

        $subquestion = new \Question();
        $subquestion->sid = self::$surveyId;
        $subquestion->gid = $group->gid;
        $subquestion->parent_qid = $question->qid;
        $subquestion->type = $question->type;
        $subquestion->title = 'CIQSUB';
        $subquestion->question_order = 1;
        $this->assertTrue($subquestion->save(), 'Could not save subquestion fixture: ' . json_encode($subquestion->errors));

        $answer = new \Answer();
        $answer->qid = $question->qid;
        $answer->code = 'A1';
        $answer->sortorder = 1;
        $answer->scale_id = 0;
        $this->assertTrue($answer->save(), 'Could not save answer fixture: ' . json_encode($answer->errors));

        $attribute = new \QuestionAttribute();
        $attribute->qid = $question->qid;
        $attribute->attribute = 'hidden';
        $attribute->value = '1';
        $this->assertTrue($attribute->save(), 'Could not save question attribute fixture: ' . json_encode($attribute->errors));

        $qid = $question->qid;
        $subQid = $subquestion->qid;

        // Orphan the parent question directly at the DB level (no parent survey).
        // Question::beforeSave() reads Survey::model()->findByPk($this->sid)->active,
        // which fatals on a missing survey, so this cannot go through the AR layer.
        \Yii::app()->db->createCommand()->update(
            '{{questions}}',
            array('sid' => 999999901),
            'qid = :qid',
            array(':qid' => $qid)
        );

        $aData = array('messages' => array(), 'warnings' => array());
        $this->callMethod('deleteQuestions', array(array(array('qid' => $qid, 'reason' => 'test reason')), $aData));

        $this->assertNull(\Question::model()->findByPk($qid), 'Orphan question itself was not deleted.');
        $this->assertNull(\Question::model()->findByPk($subQid), 'Subquestion was left behind after deleting its orphan parent question.');
        $this->assertEmpty(\Answer::model()->findAllByAttributes(array('qid' => $qid)), 'Answer option was left behind after deleting its orphan parent question.');
        $this->assertEmpty(\QuestionAttribute::model()->resetScope()->findAllByAttributes(array('qid' => $qid)), 'Question attribute was left behind after deleting its orphan parent question.');
    }

    /** Verifies that deleting an orphan group also deletes its dependent data. */
    public function testDeleteGroupsCascadesToQuestionsAndGroupL10ns()
    {
        $group = new \QuestionGroup();
        $group->sid = self::$surveyId;
        $group->group_order = 9999;
        $this->assertTrue($group->save(), 'Could not save group fixture: ' . json_encode($group->errors));

        $groupL10n = new \QuestionGroupL10n();
        $groupL10n->gid = $group->gid;
        $groupL10n->group_name = 'Orphan group';
        $groupL10n->language = 'en';
        $this->assertTrue($groupL10n->save(), 'Could not save group l10n fixture: ' . json_encode($groupL10n->errors));

        $question = new \Question();
        $question->sid = self::$surveyId;
        $question->gid = $group->gid;
        $question->parent_qid = 0;
        $question->type = \QuestionType::QT_L_LIST;
        $question->title = 'CIGQUESTION';
        $question->question_order = 1;
        $this->assertTrue($question->save(), 'Could not save question fixture: ' . json_encode($question->errors));

        $answer = new \Answer();
        $answer->qid = $question->qid;
        $answer->code = 'A1';
        $answer->sortorder = 1;
        $answer->scale_id = 0;
        $this->assertTrue($answer->save(), 'Could not save answer fixture: ' . json_encode($answer->errors));

        $gid = $group->gid;
        $qid = $question->qid;

        // Orphan the group directly at the DB level (no parent survey). The question
        // keeps its real, valid sid so it is only reachable as a child of the group.
        \Yii::app()->db->createCommand()->update(
            '{{groups}}',
            array('sid' => 999999902),
            'gid = :gid',
            array(':gid' => $gid)
        );

        $aData = array('messages' => array(), 'warnings' => array());
        $this->callMethod('deleteGroups', array(array(array('gid' => $gid, 'reason' => 'test reason')), $aData));

        $this->assertNull(\QuestionGroup::model()->findByPk($gid), 'Orphan group itself was not deleted.');
        $this->assertNull(\Question::model()->findByPk($qid), 'Question was left behind after deleting its orphan parent group.');
        $this->assertEmpty(\Answer::model()->findAllByAttributes(array('qid' => $qid)), 'Answer option was left behind after deleting the orphan group.');
        $this->assertEmpty(\QuestionGroupL10n::model()->resetScope()->findAllByAttributes(array('gid' => $gid)), 'Group localization was left behind after deleting the orphan group.');
    }

    public function testFixIntegrityDeletesQuotaLanguageSettingsOrphanedByQuotaDeletionInTheSamePass()
    {
        $quota = new \Quota();
        $quota->sid = self::$surveyId;
        $quota->name = 'Orphan quota';
        $quota->qlimit = 1;
        $quota->action = 1;
        $this->assertTrue($quota->save(), 'Could not save quota fixture: ' . json_encode($quota->errors));

        $quotaLanguageSetting = new \QuotaLanguageSetting();
        $quotaLanguageSetting->quotals_quota_id = $quota->id;
        $quotaLanguageSetting->quotals_language = 'en';
        $quotaLanguageSetting->quotals_message = 'Quota met.';
        $this->assertTrue($quotaLanguageSetting->save(), 'Could not save quota language setting fixture: ' . json_encode($quotaLanguageSetting->errors));

        $quotaId = $quota->id;

        // Orphan the quota directly at the DB level (no parent survey). Its language
        // setting above is NOT yet orphaned at this point (its quota still exists) -
        // this is exactly the state fixintegrity()'s upfront checkintegrity() snapshot
        // sees, before deleteQuotas() removes the quota a few lines later.
        \Yii::app()->db->createCommand()->update(
            '{{quota}}',
            array('sid' => 999999903),
            'id = :id',
            array(':id' => $quotaId)
        );

        $_POST['ok'] = 'Y';
        try {
            // Rendering the wrapped admin template needs a full HTTP request context this
            // unit test does not provide; only fixintegrity()'s DB side effects are under
            // test here, so the trailing render call is stubbed out.
            $noRenderController = new class('dummy', 'fixintegrity') extends \CheckIntegrity {
                protected function renderWrappedTemplate($sAction = 'checkintegrity', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
                {
                }
            };
            $noRenderController->fixintegrity();
        } finally {
            unset($_POST['ok']);
        }

        $this->assertNull(\Quota::model()->findByPk($quotaId), 'Orphan quota itself was not deleted.');
        $this->assertEmpty(
            \QuotaLanguageSetting::model()->findAllByAttributes(array('quotals_quota_id' => $quotaId)),
            'Quota language setting was left behind after its orphan parent quota was deleted in the same pass.'
        );
    }

    public function testIndexDoesNotRunTheConsistencyCheck()
    {
        $group = self::$testSurvey->groups[0];

        $question = new \Question();
        $question->sid = self::$surveyId;
        $question->gid = $group->gid;
        $question->parent_qid = 0;
        $question->type = \QuestionType::QT_L_LIST;
        $question->title = 'CIINDEXNOFIX';
        $question->question_order = 1;
        $this->assertTrue($question->save(), 'Could not save question fixture: ' . json_encode($question->errors));

        $qid = $question->qid;

        \Yii::app()->db->createCommand()->update(
            '{{questions}}',
            array('sid' => 999999908),
            'qid = :qid',
            array(':qid' => $qid)
        );

        $noRenderController = $this->newNoRenderController();

        try {
            $noRenderController->index();

            $this->assertNotNull(\Question::model()->findByPk($qid), 'Merely loading the check page (GET) ran the consistency check and deleted the orphan question.');
            $this->assertFalse($noRenderController->capturedData['consistencyCheckRan'], 'index() should report that the consistency check has not run yet.');
        } finally {
            \Yii::app()->db->createCommand()->update('{{questions}}', array('sid' => self::$surveyId), 'qid = :qid', array(':qid' => $qid));
            \Question::model()->findByPk($qid)->delete();
        }
    }

    public function testFixIntegrityRunsTheConsistencyCheckAndReportsALogOfFixes()
    {
        $group = self::$testSurvey->groups[0];

        $question = new \Question();
        $question->sid = self::$surveyId;
        $question->gid = $group->gid;
        $question->parent_qid = 0;
        $question->type = \QuestionType::QT_L_LIST;
        $question->title = 'CIFIXLOG';
        $question->question_order = 1;
        $this->assertTrue($question->save(), 'Could not save question fixture: ' . json_encode($question->errors));

        $qid = $question->qid;

        // Orphan the question directly at the DB level: this is what a "Run data
        // consistency check" button click (a POST to fixintegrity()) must find and fix.
        \Yii::app()->db->createCommand()->update(
            '{{questions}}',
            array('sid' => 999999909),
            'qid = :qid',
            array(':qid' => $qid)
        );

        $noRenderController = $this->newNoRenderController();

        $_POST['ok'] = 'Y';
        try {
            $noRenderController->fixintegrity();
        } finally {
            unset($_POST['ok']);
        }

        $this->assertNull(\Question::model()->findByPk($qid), 'Submitting the consistency check did not fix the orphan question.');
        $this->assertTrue($noRenderController->capturedData['consistencyCheckRan'], 'fixintegrity() should report that the consistency check has run.');
        $this->assertNotEmpty($noRenderController->capturedData['consistencyCheckMessages'], 'fixintegrity() did not report a log of fixes.');
        $logOfFixes = implode(' ', $noRenderController->capturedData['consistencyCheckMessages']);
        $this->assertStringContainsString('Deleted question ' . $qid, $logOfFixes);
        $this->assertStringContainsString(
            'No matching group',
            $logOfFixes,
            'The log of fixes should say why the question was deleted, not just that it was.'
        );
    }

    public function testIndexReportsNoErrorsWithoutHavingRunAnything()
    {
        $noRenderController = $this->newNoRenderController();
        $noRenderController->index();

        $this->assertFalse($noRenderController->capturedData['consistencyCheckRan']);
        $this->assertArrayNotHasKey('consistencyCheckMessages', $noRenderController->capturedData);
    }

    public function testFixIntegrityReportsNoErrorsWhenNothingIsWrong()
    {
        $noRenderController = $this->newNoRenderController();

        $_POST['ok'] = 'Y';
        try {
            $noRenderController->fixintegrity();
        } finally {
            unset($_POST['ok']);
        }

        $this->assertTrue($noRenderController->capturedData['consistencyCheckRan']);
        $this->assertEmpty($noRenderController->capturedData['consistencyCheckMessages'], 'Expected no fixes to have been logged on a clean pass.');
    }

    /**
     * Rendering the wrapped admin template needs a full HTTP request context this
     * unit test does not provide; this captures the data index()/fixintegrity() would
     * have rendered with instead of the real check_view.php.
     *
     * @return \CheckIntegrity
     */
    private function newNoRenderController()
    {
        return new class('dummy', 'checkintegrity') extends \CheckIntegrity {
            public $capturedData;
            protected function renderWrappedTemplate($sAction = 'checkintegrity', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
            {
                $this->capturedData = $aData;
            }
        };
    }

    public function testFixGroupOrderDuplicatesUsesGroupNameInBaseLanguageAsTiebreaker()
    {
        // The l10n rows below use the survey's base language, since
        // QuestionGroup::updateGroupOrder() only sorts by group_name in that language.
        $gidCharlie = $this->createOrderedGroupFixture(50, 'Charlie group');
        $gidAlpha = $this->createOrderedGroupFixture(50, 'Alpha group');
        $gidBravo = $this->createOrderedGroupFixture(50, 'Bravo group');

        $aData = array('messages' => array(), 'warnings' => array());
        $this->callMethod('fixGroupOrderDuplicates', array(array(array('sid' => self::$surveyId)), $aData));

        $orderByGid = \Yii::app()->db->createCommand()
            ->select('gid, group_order')
            ->from('{{groups}}')
            ->where('sid = :sid', array(':sid' => self::$surveyId))
            ->queryAll();
        $orderByGid = array_column($orderByGid, 'group_order', 'gid');

        $this->assertLessThan($orderByGid[$gidBravo], $orderByGid[$gidAlpha], '"Alpha group" should now sort before "Bravo group".');
        $this->assertLessThan($orderByGid[$gidCharlie], $orderByGid[$gidBravo], '"Bravo group" should now sort before "Charlie group".');

        // Lowercase aliases: PostgreSQL folds unquoted mixed-case identifiers to
        // lowercase, so a camelCase alias here would not match the same key back.
        $counts = \Yii::app()->db->createCommand()
            ->select('COUNT(DISTINCT group_order) as distinctorders, COUNT(gid) as totalgroups')
            ->from('{{groups}}')
            ->where('sid = :sid', array(':sid' => self::$surveyId))
            ->queryRow();
        $this->assertSame($counts['totalgroups'], $counts['distinctorders'], 'Groups still have duplicate group_order values after the fix.');
    }

    /** Verifies that a group without a base-language name is still renumbered. */
    public function testFixGroupOrderDuplicatesRenumbersGroupsWithoutABaseLanguageName()
    {
        // Both at group_order 0: renumbering starts at 0, so a group skipped by the
        // renumbering would keep colliding with whichever group is renumbered to 0.
        $gidNamed = $this->createOrderedGroupFixture(0, 'Named group');

        // Only localized in the survey's additional language, not its base language.
        $unnamedGroup = new \QuestionGroup();
        $unnamedGroup->sid = self::$surveyId;
        $unnamedGroup->group_order = 0;
        $this->assertTrue($unnamedGroup->save(), 'Could not save group fixture: ' . json_encode($unnamedGroup->errors));
        $groupL10n = new \QuestionGroupL10n();
        $groupL10n->gid = $unnamedGroup->gid;
        $groupL10n->group_name = 'Additional language only group';
        $groupL10n->language = self::$testSurvey->additionalLanguages[0];
        $this->assertTrue($groupL10n->save(), 'Could not save group l10n fixture: ' . json_encode($groupL10n->errors));

        $aData = array('messages' => array(), 'warnings' => array());
        $this->callMethod('fixGroupOrderDuplicates', array(array(array('sid' => self::$surveyId)), $aData));

        $orderByGid = \Yii::app()->db->createCommand()
            ->select('gid, group_order')
            ->from('{{groups}}')
            ->where('sid = :sid', array(':sid' => self::$surveyId))
            ->queryAll();
        $orderByGid = array_column($orderByGid, 'group_order', 'gid');

        $this->assertNotEquals($orderByGid[$gidNamed], $orderByGid[$unnamedGroup->gid]);
        $this->assertCount(count($orderByGid), array_unique($orderByGid), 'A group without a base-language name kept a duplicate group_order.');
    }

    public function testFixQuestionOrderDuplicatesUsesQuestionCodeAsTiebreaker()
    {
        $group = self::$testSurvey->groups[0];

        $qidCharlie = $this->createOrderedQuestionFixture($group->gid, 0, 0, 5, 'CIQCHARLIE');
        $qidAlpha = $this->createOrderedQuestionFixture($group->gid, 0, 0, 5, 'CIQALPHA');
        $qidBravo = $this->createOrderedQuestionFixture($group->gid, 0, 0, 5, 'CIQBRAVO');

        $duplicate = array('sid' => self::$surveyId, 'gid' => $group->gid, 'parent_qid' => 0, 'scale_id' => 0);
        $aData = array('messages' => array(), 'warnings' => array());
        $this->callMethod('fixQuestionOrderDuplicates', array(array($duplicate), $aData));

        $orderByQid = \Yii::app()->db->createCommand()
            ->select('qid, question_order')
            ->from('{{questions}}')
            ->where('gid = :gid AND parent_qid = 0 AND scale_id = 0', array(':gid' => $group->gid))
            ->queryAll();
        $orderByQid = array_column($orderByQid, 'question_order', 'qid');

        $this->assertLessThan($orderByQid[$qidBravo], $orderByQid[$qidAlpha], 'CIQALPHA should now sort before CIQBRAVO.');
        $this->assertLessThan($orderByQid[$qidCharlie], $orderByQid[$qidBravo], 'CIQBRAVO should now sort before CIQCHARLIE.');

        // Lowercase aliases: PostgreSQL folds unquoted mixed-case identifiers to
        // lowercase, so a camelCase alias here would not match the same key back.
        $counts = \Yii::app()->db->createCommand()
            ->select('COUNT(DISTINCT question_order) as distinctorders, COUNT(qid) as totalquestions')
            ->from('{{questions}}')
            ->where('gid = :gid AND parent_qid = 0 AND scale_id = 0', array(':gid' => $group->gid))
            ->queryRow();
        $this->assertSame($counts['totalquestions'], $counts['distinctorders'], 'Questions still have duplicate question_order values after the fix.');
    }

    /**
     * @param int $order
     * @param string $groupName
     * @return int the new group's gid
     */
    private function createOrderedGroupFixture($order, $groupName)
    {
        $group = new \QuestionGroup();
        $group->sid = self::$surveyId;
        $group->group_order = $order;
        $this->assertTrue($group->save(), 'Could not save group fixture: ' . json_encode($group->errors));

        $groupL10n = new \QuestionGroupL10n();
        $groupL10n->gid = $group->gid;
        $groupL10n->group_name = $groupName;
        $groupL10n->language = self::$testSurvey->language;
        $this->assertTrue($groupL10n->save(), 'Could not save group l10n fixture: ' . json_encode($groupL10n->errors));

        return $group->gid;
    }

    /**
     * @param int $gid
     * @param int $parentQid
     * @param int $scaleId
     * @param int $order
     * @param string $title
     * @return int the new question's qid
     */
    private function createOrderedQuestionFixture($gid, $parentQid, $scaleId, $order, $title)
    {
        $question = new \Question();
        $question->sid = self::$surveyId;
        $question->gid = $gid;
        $question->parent_qid = $parentQid;
        $question->scale_id = $scaleId;
        $question->type = \QuestionType::QT_T_LONG_FREE_TEXT;
        $question->title = $title;
        $question->question_order = $order;
        $this->assertTrue($question->save(), 'Could not save question fixture: ' . json_encode($question->errors));

        return $question->qid;
    }

    public function testEmptyOldSurveyAndTokenTablesAreDeletedAutomaticallyWithoutConfirmation()
    {
        $dbPrefix = \Yii::app()->db->tablePrefix;
        $bogusSid = 999999906;
        $responsesTable = $dbPrefix . 'old_responses_' . $bogusSid . '_20200101120000';
        $tokensTable = $dbPrefix . 'old_tokens_' . $bogusSid . '_20200101120000';

        \Yii::app()->db->createCommand()->createTable($responsesTable, array('id' => 'pk'));
        \Yii::app()->db->createCommand()->createTable($tokensTable, array('tid' => 'pk'));

        try {
            $this->integrityChecker->applyAutomaticFixes();

            $this->assertFalse($this->tableExistsRaw($responsesTable), 'Empty old survey responses table was not auto-deleted.');
            $this->assertFalse($this->tableExistsRaw($tokensTable), 'Empty old participant list table was not auto-deleted.');
        } finally {
            $this->dropTableIfPresent($responsesTable);
            $this->dropTableIfPresent($tokensTable);
        }
    }

    /**
     * A non-empty old participant list table must never be auto-deleted, even when
     * its parent survey no longer exists at all - only ever offered for manual
     * confirmation via the data redundancy check, since it still holds real
     * participant data. This also covers the fix for a copy-paste bug where the
     * "does this table's survey still exist at all" check compared the survey ID
     * against the very array it was drawn from (always false); that check no longer
     * exists at all now that record count alone decides, regardless of survey
     * existence, matching the equivalent old survey tables check just above it.
     */
    public function testNonEmptyOldTokenTableForAFullyDeletedSurveyIsNeverAutoDeleted()
    {
        $dbPrefix = \Yii::app()->db->tablePrefix;
        $bogusSid = 999999907;
        $tokensTable = $dbPrefix . 'old_tokens_' . $bogusSid . '_20200101120000';

        \Yii::app()->db->createCommand()->createTable($tokensTable, array('token' => 'string'));
        \Yii::app()->db->createCommand()->insert($tokensTable, array('token' => 'abc123'));

        try {
            $aData = $this->integrityChecker->applyAutomaticFixes();

            $this->assertTrue(
                $this->tableExistsRaw($tokensTable),
                'Old participant list table was auto-deleted even though it still contained data.'
            );

            $askedForConfirmation = array_filter($aData['redundanttokentables'], function ($table) use ($tokensTable) {
                return $table['table'] === $tokensTable;
            });
            $this->assertNotEmpty(
                $askedForConfirmation,
                'A non-empty participant list table should be offered for manual confirmation, even when its survey no longer exists at all.'
            );
        } finally {
            $this->dropTableIfPresent($tokensTable);
        }
    }

    /** Verifies that a failed table drop is reported as a warning instead of aborting the pass. */
    public function testDropTableIfExistsWarnsWhenTheDropFails()
    {
        $tableName = \Yii::app()->db->tablePrefix . 'old_responses_999999910_20200101120000';
        $aData = array('messages' => array(), 'warnings' => array());
        $dropped = $this->callMethod('dropTableIfExists', array($tableName, &$aData));

        $this->assertFalse($dropped);
        $this->assertCount(1, $aData['warnings'], 'A failed drop should be reported as a warning.');
        $this->assertStringContainsString($tableName, $aData['warnings'][0]);
    }

    /**
     * Missing survey language settings caused a "texts for one or more survey languages
     * could not be found" error pointing to this tool, which then claimed no errors were
     * found: they were silently restored while merely loading the check page.
     */
    public function testMissingSurveyLanguageSettingsAreOnlyRestoredAndReportedByTheConsistencyCheck()
    {
        $language = self::$testSurvey->language;
        $condition = 'surveyls_survey_id = :sid AND surveyls_language = :language';
        $params = array(':sid' => self::$surveyId, ':language' => $language);
        $original = \Yii::app()->db->createCommand()->select('*')->from('{{surveys_languagesettings}}')->where($condition, $params)->queryRow();
        $this->assertNotEmpty($original, 'The survey fixture has no language settings for its base language.');
        \Yii::app()->db->createCommand()->delete('{{surveys_languagesettings}}', $condition, $params);

        try {
            $this->newNoRenderController()->index();
            $this->assertFalse(
                \Yii::app()->db->createCommand()->select('count(*)')->from('{{surveys_languagesettings}}')->where($condition, $params)->queryScalar() > 0,
                'Merely loading the check page (GET) restored the missing survey language settings.'
            );

            $logOfFixes = implode(' ', $this->runFixIntegrity());

            $this->assertTrue(
                \Yii::app()->db->createCommand()->select('count(*)')->from('{{surveys_languagesettings}}')->where($condition, $params)->queryScalar() > 0,
                'The consistency check did not restore the missing survey language settings.'
            );
            $this->assertStringContainsString(
                sprintf('Restored missing survey texts for language %s in survey %s', $language, self::$surveyId),
                $logOfFixes,
                'Restoring the missing survey language settings was not reported in the log of fixes.'
            );
        } finally {
            \Yii::app()->db->createCommand()->delete('{{surveys_languagesettings}}', $condition, $params);
            \Yii::app()->db->createCommand()->insert('{{surveys_languagesettings}}', $original);
        }
    }

    /** Verifies that permissions of deleted users are only deleted, and reported, by the consistency check. */
    public function testOrphanUserPermissionsAreOnlyDeletedAndReportedByTheConsistencyCheck()
    {
        $bogusUid = 999999911;
        \Yii::app()->db->createCommand()->insert('{{permissions}}', array(
            'entity' => 'global',
            'entity_id' => 0,
            'uid' => $bogusUid,
            'permission' => 'surveys',
            'create_p' => 0,
            'read_p' => 1,
            'update_p' => 0,
            'delete_p' => 0,
            'import_p' => 0,
            'export_p' => 0,
        ));
        $countPermissions = function () use ($bogusUid) {
            return (int) \Yii::app()->db->createCommand()->select('count(*)')->from('{{permissions}}')->where('uid = :uid', array(':uid' => $bogusUid))->queryScalar();
        };

        try {
            $this->newNoRenderController()->index();
            $this->assertSame(1, $countPermissions(), 'Merely loading the check page (GET) deleted the permission of a deleted user.');

            $logOfFixes = implode(' ', $this->runFixIntegrity());

            $this->assertSame(0, $countPermissions(), 'The consistency check did not delete the permission of a deleted user.');
            $this->assertStringContainsString('permission(s) of users that no longer exist', $logOfFixes);
        } finally {
            \Yii::app()->db->createCommand()->delete('{{permissions}}', 'uid = :uid', array(':uid' => $bogusUid));
        }
    }

    /** Verifies that an active survey without a response table is only deactivated, and reported, by the consistency check. */
    public function testActiveSurveyWithoutResponseTableIsOnlyDeactivatedAndReportedByTheConsistencyCheck()
    {
        $this->assertFalse($this->tableExistsRaw(\Yii::app()->db->tablePrefix . 'responses_' . self::$surveyId), 'The survey fixture unexpectedly has a response table.');
        \Yii::app()->db->createCommand()->update('{{surveys}}', array('active' => 'Y'), 'sid = :sid', array(':sid' => self::$surveyId));
        $getActive = function () {
            return \Yii::app()->db->createCommand()->select('active')->from('{{surveys}}')->where('sid = :sid', array(':sid' => self::$surveyId))->queryScalar();
        };

        try {
            $this->newNoRenderController()->index();
            $this->assertSame('Y', $getActive(), 'Merely loading the check page (GET) deactivated the survey.');

            $logOfFixes = implode(' ', $this->runFixIntegrity());

            $this->assertSame('N', $getActive(), 'The consistency check did not deactivate the survey without a response table.');
            $this->assertStringContainsString(sprintf('Deactivated survey %s because its response table is missing', self::$surveyId), $logOfFixes);
        } finally {
            \Yii::app()->db->createCommand()->update('{{surveys}}', array('active' => 'N'), 'sid = :sid', array(':sid' => self::$surveyId));
        }
    }

    /** Verifies that a participant list of a deleted survey is only archived, and reported, by the consistency check. */
    public function testOrphanParticipantListIsOnlyArchivedAndReportedByTheConsistencyCheck()
    {
        $dbPrefix = \Yii::app()->db->tablePrefix;
        $bogusSid = 999999912;
        $tokensTable = $dbPrefix . 'tokens_' . $bogusSid;
        \Yii::app()->db->createCommand()->createTable($tokensTable, array('tid' => 'pk'));

        try {
            $this->newNoRenderController()->index();
            $this->assertTrue($this->tableExistsRaw($tokensTable), 'Merely loading the check page (GET) archived the participant list of a deleted survey.');

            $logOfFixes = implode(' ', $this->runFixIntegrity());

            $this->assertFalse($this->tableExistsRaw($tokensTable), 'The consistency check did not archive the participant list of a deleted survey.');
            $this->assertStringContainsString(sprintf('Archived survey participant list of missing survey %s', $bogusSid), $logOfFixes);
        } finally {
            $this->dropTableIfPresent($tokensTable);
            $archivedTables = \Yii::app()->db->createCommand(\dbSelectTablesLike('{{old_tokens_' . $bogusSid . '}}%'))->queryColumn();
            foreach ($archivedTables as $archivedTable) {
                \Yii::app()->db->createCommand()->dropTable($archivedTable);
            }
        }
    }

    /** Verifies that archived table settings without their table are only deleted, and reported, by the consistency check. */
    public function testOrphanArchivedTableSettingsAreOnlyDeletedAndReportedByTheConsistencyCheck()
    {
        $tableName = 'old_responses_999999913_20200101120000';
        \Yii::app()->db->createCommand()->insert('{{archived_table_settings}}', array(
            'survey_id' => 999999913,
            'user_id' => 1,
            'tbl_name' => $tableName,
            'tbl_type' => 'response',
            'created' => '2020-01-01 12:00:00',
            'properties' => '',
        ));
        $countSettings = function () use ($tableName) {
            return (int) \Yii::app()->db->createCommand()->select('count(*)')->from('{{archived_table_settings}}')->where('tbl_name = :name', array(':name' => $tableName))->queryScalar();
        };

        try {
            $this->newNoRenderController()->index();
            $this->assertSame(1, $countSettings(), 'Merely loading the check page (GET) deleted the archived table settings.');

            $logOfFixes = implode(' ', $this->runFixIntegrity());

            $this->assertSame(0, $countSettings(), 'The consistency check did not delete archived table settings without their table.');
            $this->assertStringContainsString('archived table setting(s) whose archived table no longer exists', $logOfFixes);
        } finally {
            \Yii::app()->db->createCommand()->delete('{{archived_table_settings}}', 'tbl_name = :name', array(':name' => $tableName));
        }
    }

    /** Verifies that a survey of a deleted survey group is only moved to the default survey group, and reported, by the consistency check. */
    public function testSurveyOfADeletedSurveyGroupIsOnlyMovedToTheDefaultGroupAndReportedByTheConsistencyCheck()
    {
        $bogusGsid = 999999915;
        $originalGsid = \Yii::app()->db->createCommand()->select('gsid')->from('{{surveys}}')->where('sid = :sid', array(':sid' => self::$surveyId))->queryScalar();
        \Yii::app()->db->createCommand()->update('{{surveys}}', array('gsid' => $bogusGsid), 'sid = :sid', array(':sid' => self::$surveyId));
        $getGsid = function () {
            return (int) \Yii::app()->db->createCommand()->select('gsid')->from('{{surveys}}')->where('sid = :sid', array(':sid' => self::$surveyId))->queryScalar();
        };

        try {
            $this->newNoRenderController()->index();
            $this->assertSame($bogusGsid, $getGsid(), 'Merely loading the check page (GET) moved the survey of a deleted survey group.');

            $logOfFixes = implode(' ', $this->runFixIntegrity());

            $this->assertSame(1, $getGsid(), 'The consistency check did not move the survey of a deleted survey group to the default survey group.');
            $this->assertStringContainsString(sprintf('Moved survey %s to the default survey group because its survey group %s no longer exists', self::$surveyId, $bogusGsid), $logOfFixes);
        } finally {
            \Yii::app()->db->createCommand()->update('{{surveys}}', array('gsid' => $originalGsid), 'sid = :sid', array(':sid' => self::$surveyId));
        }
    }

    /** Verifies that survey group settings of a deleted survey group are only deleted, and reported, by the consistency check. */
    public function testOrphanSurveyGroupSettingsAreOnlyDeletedAndReportedByTheConsistencyCheck()
    {
        $bogusGsid = 999999916;
        $settings = new \SurveysGroupsettings();
        $settings->gsid = $bogusGsid;
        $settings->setToInherit();
        $this->assertTrue($settings->save(), 'Could not save survey group settings: ' . json_encode($settings->getErrors()));
        $countSettings = function () use ($bogusGsid) {
            return (int) \Yii::app()->db->createCommand()->select('count(*)')->from('{{surveys_groupsettings}}')->where('gsid = :gsid', array(':gsid' => $bogusGsid))->queryScalar();
        };

        try {
            $this->newNoRenderController()->index();
            $this->assertSame(1, $countSettings(), 'Merely loading the check page (GET) deleted the survey group settings of a deleted survey group.');

            $logOfFixes = implode(' ', $this->runFixIntegrity());

            $this->assertSame(0, $countSettings(), 'The consistency check did not delete the survey group settings of a deleted survey group.');
            $this->assertStringContainsString('survey group setting(s) of survey groups that no longer exist', $logOfFixes);
            $this->assertNotNull(\SurveysGroupsettings::model()->findByPk(0), 'The consistency check deleted the global survey settings.');
            $this->assertNotNull(\SurveysGroupsettings::model()->findByPk(1), 'The consistency check deleted the default survey group settings.');
        } finally {
            \Yii::app()->db->createCommand()->delete('{{surveys_groupsettings}}', 'gsid = :gsid', array(':gsid' => $bogusGsid));
        }
    }

    /**
     * A subquestion whose group does not exist (but whose parent question is fine) must
     * get its parent's group back instead of being deleted as a question without group.
     */
    public function testSubquestionWithAMissingGroupIsRepairedAndReportedInsteadOfDeleted()
    {
        $group = self::$testSurvey->groups[0];
        $parentQid = $this->createOrderedQuestionFixture($group->gid, 0, 0, 99991, 'CISQPARENT');
        \Yii::app()->db->createCommand()->update('{{questions}}', array('type' => \QuestionType::QT_F_ARRAY), 'qid = :qid', array(':qid' => $parentQid));
        $subquestionQid = $this->createOrderedQuestionFixture($group->gid, $parentQid, 0, 1, 'SQ001');
        \Yii::app()->db->createCommand()->update(
            '{{questions}}',
            array('gid' => 999999914, 'type' => \QuestionType::QT_F_ARRAY),
            'qid = :qid',
            array(':qid' => $subquestionQid)
        );
        $getSubquestionGid = function () use ($subquestionQid) {
            return \Yii::app()->db->createCommand()->select('gid')->from('{{questions}}')->where('qid = :qid', array(':qid' => $subquestionQid))->queryScalar();
        };

        try {
            $this->newNoRenderController()->index();
            $this->assertEquals(999999914, $getSubquestionGid(), 'Merely loading the check page (GET) changed the subquestion.');

            $logOfFixes = implode(' ', $this->runFixIntegrity());

            $this->assertEquals($group->gid, $getSubquestionGid(), 'The consistency check did not give the subquestion its parent question\'s group back.');
            $this->assertStringContainsString(sprintf('Fixed group and question type of subquestion %s', $subquestionQid), $logOfFixes);
        } finally {
            $parentQuestion = \Question::model()->findByPk($parentQid);
            if ($parentQuestion) {
                $parentQuestion->delete();
            }
            \Yii::app()->db->createCommand()->delete('{{questions}}', 'qid = :qid', array(':qid' => $subquestionQid));
        }
    }

    /**
     * A question without texts in a survey language could not be edited (500 error),
     * so the consistency check must create empty texts for every missing language.
     */
    public function testQuestionWithoutTextsGetsEmptyTextsCreatedAndReportedByTheConsistencyCheck()
    {
        $group = self::$testSurvey->groups[0];
        $qid = $this->createOrderedQuestionFixture($group->gid, 0, 0, 99992, 'CINOL10N');
        \Yii::app()->db->createCommand()->delete('{{question_l10ns}}', 'qid = :qid', array(':qid' => $qid));
        $getL10ns = function () use ($qid) {
            return \Yii::app()->db->createCommand()->select('language, question')->from('{{question_l10ns}}')->where('qid = :qid', array(':qid' => $qid))->queryAll();
        };

        try {
            $this->newNoRenderController()->index();
            $this->assertEmpty($getL10ns(), 'Merely loading the check page (GET) created question texts.');

            $logOfFixes = implode(' ', $this->runFixIntegrity());

            $l10ns = $getL10ns();
            $surveyLanguages = self::$testSurvey->getAllLanguages();
            $this->assertEqualsCanonicalizing($surveyLanguages, array_column($l10ns, 'language'), 'The consistency check did not create texts for exactly the survey languages.');
            $this->assertSame(array(''), array_values(array_unique(array_column($l10ns, 'question'))), 'The created question texts are not empty.');
            foreach ($surveyLanguages as $language) {
                $this->assertStringContainsString(
                    sprintf('Created empty texts for language %s of question CINOL10N (ID %s) in survey %s', $language, $qid, self::$surveyId),
                    $logOfFixes
                );
            }
        } finally {
            $question = \Question::model()->findByPk($qid);
            if ($question) {
                $question->delete();
            }
        }
    }

    /**
     * Submits the "Run data consistency check" button.
     *
     * @return string[] The log of fixes.
     */
    private function runFixIntegrity()
    {
        $noRenderController = $this->newNoRenderController();
        $_POST['ok'] = 'Y';
        try {
            $noRenderController->fixintegrity();
        } finally {
            unset($_POST['ok']);
        }
        $this->assertEmpty($noRenderController->capturedData['consistencyCheckWarnings'], 'The consistency check reported warnings: ' . implode(' ', $noRenderController->capturedData['consistencyCheckWarnings']));
        return $noRenderController->capturedData['consistencyCheckMessages'];
    }

    /**
     * Checks whether a table exists, querying the database directly.
     *
     * @param string $tableName Full (prefixed) table name.
     * @return bool
     */
    private function tableExistsRaw($tableName)
    {
        // dbSelectTablesLike() is the cross-database (mysql/pgsql/mssql) equivalent of a
        // literal "SHOW TABLES LIKE" - needed since this test suite also runs against
        // pgsql/mssql in CI, unlike the rest of this file which targets the app's own
        // configured driver only.
        return (bool) \Yii::app()->db->createCommand(\dbSelectTablesLike($tableName))->queryScalar();
    }

    /**
     * @param string $tableName
     * @return void
     */
    private function dropTableIfPresent($tableName)
    {
        if ($this->tableExistsRaw($tableName)) {
            \Yii::app()->db->createCommand()->dropTable($tableName);
        }
    }
}
