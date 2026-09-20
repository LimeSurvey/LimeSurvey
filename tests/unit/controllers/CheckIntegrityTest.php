<?php

namespace ls\tests\controllers;

use ls\tests\TestBaseClass;

/**
 * Regression tests for CheckIntegrity::deleteQuestions() and deleteGroups().
 *
 * Deleting an orphaned question (no parent survey/group) or an orphaned
 * question group (no parent survey) must cascade to the child data that
 * references it (subquestions, answers, question attributes, group
 * localizations) instead of leaving those rows behind.
 */
class CheckIntegrityTest extends TestBaseClass
{
    /** @var \CheckIntegrity */
    private $controller;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $surveyFile = self::$surveysFolder . '/limesurvey_survey_143933.lss';
        self::importSurvey($surveyFile);
    }

    public function setUp(): void
    {
        parent::setUp();
        \Yii::import('application.controllers.admin.CheckIntegrity', true);
        \Yii::app()->session['loginID'] = 1;
        // Normally set by the admin session; checkintegrity()'s old-survey-table check
        // reads this directly and isn't otherwise initialized outside a real request.
        \Yii::app()->session['dateformat'] = 1;
        $this->controller = new \CheckIntegrity('dummy', 'checkintegrity');
    }

    /**
     * Invokes a non-public method on the CheckIntegrity controller under test.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    private function callMethod($method, array $args)
    {
        $reflection = new \ReflectionMethod(\CheckIntegrity::class, $method);
        $reflection->setAccessible(true);
        return $reflection->invokeArgs($this->controller, $args);
    }

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
        // Survey 143933's base language is 'de'; the l10n rows below must use that
        // language, since QuestionGroup::updateGroupOrder() only sorts by group_name
        // for the survey's base language.
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

        $counts = \Yii::app()->db->createCommand()
            ->select('COUNT(DISTINCT group_order) as distinctOrders, COUNT(gid) as totalGroups')
            ->from('{{groups}}')
            ->where('sid = :sid', array(':sid' => self::$surveyId))
            ->queryRow();
        $this->assertSame($counts['totalGroups'], $counts['distinctOrders'], 'Groups still have duplicate group_order values after the fix.');
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

        $counts = \Yii::app()->db->createCommand()
            ->select('COUNT(DISTINCT question_order) as distinctOrders, COUNT(qid) as totalQuestions')
            ->from('{{questions}}')
            ->where('gid = :gid AND parent_qid = 0 AND scale_id = 0', array(':gid' => $group->gid))
            ->queryRow();
        $this->assertSame($counts['totalQuestions'], $counts['distinctOrders'], 'Questions still have duplicate question_order values after the fix.');
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
            $this->controller->applyAutomaticFixes();

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
            $aData = $this->controller->applyAutomaticFixes();

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

    /**
     * @param string $tableName
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
