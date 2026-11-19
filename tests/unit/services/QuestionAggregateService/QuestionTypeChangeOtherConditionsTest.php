<?php

namespace ls\tests\unit\services\QuestionAggregateService;

use Answer;
use Condition;
use LimeExpressionManager;
use LimeSurvey\DI;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionUpdate;
use LimeSurvey\Models\Services\Exception\PersistErrorException;
use LimeSurvey\Models\Services\Exception\QuestionHasConditionsException;
use LimeSurvey\Models\Services\QuestionAggregateService\QuestionService;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use ls\tests\TestBaseClass;
use Question;
use Survey;

/**
 * A type change keeps the answer options, "Other" and the conditions on them
 * if both types have the same answer option layout, otherwise it deletes them.
 *
 * The tests share one survey and run in this order:
 * Q1 (List radio, Other): Conditions "Q2: Q1 = Other" and "Q3: Q1 = A1".
 * Q4 (List radio, Other): Condition "Q5: Q4 = Other".
 *
 * @group services
 */
class QuestionTypeChangeOtherConditionsTest extends TestBaseClass
{
    /**
     * Imports the survey and builds the relevance of the questions from their conditions.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_922301_typeChangeOtherConditions.lss');
        LimeExpressionManager::UpgradeConditionsToRelevance(self::$surveyId);
    }

    /**
     * Checks that the model validation refuses to switch "Other" off while conditions use it.
     *
     * @testdox Saving the question model directly (e.g. RemoteControl) refuses to switch "Other" off while conditions use it
     * @return void
     */
    public function testModelRefusesSwitchingOtherOff()
    {
        $q4 = $this->getQuestion('Q4');
        $q4->other = 'N';

        $this->assertFalse($q4->save());
        $this->assertSame(['Q5'], $q4->getOtherConditionQuestionCodes());
        $this->assertTrue($q4->hasErrors('other'));
        $this->assertSame('Y', $this->getQuestion('Q4')->other);
    }

    /**
     * Checks that a compatible type change can't switch "Other" off while conditions use it.
     *
     * @testdox Switching "Other" off with a compatible type change is refused while conditions use it
     * @return void
     */
    public function testCompatibleTypeChangeRefusesSwitchingOtherOff()
    {
        \Yii::app()->session['loginID'] = 1;
        $q4 = $this->getQuestion('Q4');

        try {
            DI::getContainer()->get(QuestionService::class)->save([
                'question' => [
                    'sid' => self::$surveyId,
                    'qid' => $q4->qid,
                    'type' => Question::QT_EXCLAMATION_LIST_DROPDOWN,
                    'other' => 'N',
                ],
            ]);
            $this->fail('Switching "Other" off must be refused');
        } catch (QuestionHasConditionsException $e) {
            $this->assertSame(PersistErrorException::CODE_CHANGE_REFUSED, $e->getCode());
        }

        $q4 = $this->getQuestion('Q4');
        $this->assertSame(Question::QT_L_LIST, $q4->type);
        $this->assertSame('Y', $q4->other);
    }

    /**
     * Checks that an incompatible type change deletes the answer options, the conditions on them and on "Other",
     * and the conditions on the fields the new type doesn't have.
     *
     * @testdox Changing List (radio) to Multiple choice deletes all conditions on the question and comparisons to it
     * @return void
     */
    public function testIncompatibleTypeChangeDeletesConditions()
    {
        \Yii::app()->session['loginID'] = 1;
        $q1 = $this->getQuestion('Q1');
        $q4 = $this->getQuestion('Q4');
        $q5 = $this->getQuestion('Q5');
        $q2 = $this->getQuestion('Q2');
        // Q5 also compares Q4 to the "Other" text of Q1
        $this->addCondition($q5->qid, $q4->qid, "Q{$q4->qid}", "@Q{$q1->qid}_Cother@");
        // Conditions on the field of Q1 that Multiple choice doesn't have: "No answer", a constant, a regular expression
        $this->addCondition($q2->qid, $q1->qid, "Q{$q1->qid}", '');
        $this->addCondition($q2->qid, $q1->qid, "Q{$q1->qid}", 'x');
        $this->addCondition($q2->qid, $q1->qid, "Q{$q1->qid}", '^A', 'RX');
        // Q5 also compares Q4 to the answer of Q1
        $this->addCondition($q5->qid, $q4->qid, "Q{$q4->qid}", "@Q{$q1->qid}@");
        $this->assertCount(5, Condition::model()->findAllByAttributes(['cqid' => $q1->qid]));
        $this->assertNotSame('1', $this->getQuestion('Q2')->relevance);

        $questionService = DI::getContainer()->get(QuestionService::class);
        $questionService->save([
            'question' => [
                'sid' => self::$surveyId,
                'qid' => $q1->qid,
                'type' => Question::QT_M_MULTIPLE_CHOICE,
            ],
        ]);

        $q1 = $this->getQuestion('Q1');
        $this->assertSame(Question::QT_M_MULTIPLE_CHOICE, $q1->type);
        $this->assertSame('Y', $q1->other, '"Other" stays on: Multiple choice has "Other" too');
        $this->assertCount(
            0,
            Condition::model()->findAllByAttributes(['cqid' => $q1->qid]),
            'All conditions on Q1 must be deleted: Multiple choice has no field "Q1"'
        );
        $this->assertCount(
            0,
            Condition::model()->findAllByAttributes(['value' => "@Q{$q1->qid}@"]),
            'The comparison to the answer of Q1 must be deleted'
        );
        $this->assertCount(
            0,
            Condition::model()->findAllByAttributes(['value' => "@Q{$q1->qid}_Cother@"]),
            'The comparison to the "Other" text of Q1 must be deleted'
        );
        $this->assertCount(
            1,
            Condition::model()->findAllByAttributes(['cqid' => $q4->qid, 'value' => '-oth-']),
            'The condition on the "Other" of another question must stay'
        );
        $this->assertSame('1', $this->getQuestion('Q2')->relevance, 'Without conditions left the relevance is reset');
        $this->assertSame('1', $this->getQuestion('Q3')->relevance, 'Without conditions left the relevance is reset');
        $this->assertNotSame('1', $this->getQuestion('Q5')->relevance, 'Q5 keeps its condition on Q4');
        $this->assertEqualsCanonicalizing(
            [(int) $this->getQuestion('Q2')->qid, (int) $this->getQuestion('Q3')->qid, (int) $q5->qid],
            $questionService->getChangedDependentQuestionIds()
        );
    }

    /**
     * Checks that a compatible type change keeps the answer options, "Other" and their conditions.
     *
     * @testdox Changing List (radio) to Dropdown keeps the answer options, "Other" and their conditions
     * @return void
     */
    public function testCompatibleTypeChangeKeepsConditions()
    {
        \Yii::app()->session['loginID'] = 1;
        $q4 = $this->getQuestion('Q4');

        $questionService = DI::getContainer()->get(QuestionService::class);
        $questionService->save([
            'question' => [
                'sid' => self::$surveyId,
                'qid' => $q4->qid,
                'type' => Question::QT_EXCLAMATION_LIST_DROPDOWN,
            ],
        ]);

        $this->assertSame(Question::QT_EXCLAMATION_LIST_DROPDOWN, $this->getQuestion('Q4')->type);
        $this->assertSame('Y', $this->getQuestion('Q4')->other);
        $this->assertCount(2, Answer::model()->findAllByAttributes(['qid' => $q4->qid]), 'The answer options must stay');
        $this->assertCount(
            1,
            Condition::model()->findAllByAttributes(['cqid' => $q4->qid, 'value' => '-oth-']),
            'The condition "Q4 = Other" must stay'
        );
        $this->assertSame([], $questionService->getChangedDependentQuestionIds());
    }

    /**
     * Checks that a type change to a type without "Other" keeps the answer options and
     * deletes only the conditions on "Other".
     *
     * @testdox Changing Dropdown to List with comment keeps the answer options and deletes only the conditions on "Other"
     * @depends testCompatibleTypeChangeKeepsConditions
     * @return void
     */
    public function testTypeChangeWithoutOtherKeepsAnswerOptions()
    {
        \Yii::app()->session['loginID'] = 1;
        $q4 = $this->getQuestion('Q4');
        $q5 = $this->getQuestion('Q5');

        $questionService = DI::getContainer()->get(QuestionService::class);
        $questionService->save([
            'question' => [
                'sid' => self::$surveyId,
                'qid' => $q4->qid,
                'type' => Question::QT_O_LIST_WITH_COMMENT,
            ],
        ]);

        $this->assertSame(Question::QT_O_LIST_WITH_COMMENT, $this->getQuestion('Q4')->type);
        $this->assertSame('N', $this->getQuestion('Q4')->other);
        $this->assertCount(2, Answer::model()->findAllByAttributes(['qid' => $q4->qid]), 'The answer options must stay');
        $this->assertCount(
            0,
            Condition::model()->findAllByAttributes(['cqid' => $q4->qid, 'value' => '-oth-']),
            'The condition "Q4 = Other" must be deleted'
        );
        $this->assertSame([(int) $q5->qid], $questionService->getChangedDependentQuestionIds());

        // Restore Dropdown with "Other" and its condition for the next tests
        $questionService->save([
            'question' => [
                'sid' => self::$surveyId,
                'qid' => $q4->qid,
                'type' => Question::QT_EXCLAMATION_LIST_DROPDOWN,
                'other' => 'Y',
            ],
        ]);
        $this->addCondition($q5->qid, $q4->qid, "Q{$q4->qid}", '-oth-');
    }

    /**
     * Checks that the patch response returns the questions whose conditions a type change deleted.
     *
     * @testdox The patch response of an incompatible type change returns the questions whose conditions were deleted
     * @return void
     */
    public function testPatchResponseReturnsChangedQuestions()
    {
        \Yii::app()->session['loginID'] = 1;
        $q4 = $this->getQuestion('Q4');
        $q5 = $this->getQuestion('Q5');

        $op = OpStandard::factory(
            'question',
            'update',
            $q4->qid,
            ['type' => Question::QT_S_SHORT_FREE_TEXT],
            ['id' => self::$surveyId]
        );
        $response = DI::getContainer()->get(OpHandlerQuestionUpdate::class)->handle($op);

        $this->assertSame(
            [['qid' => (int) $q5->qid, 'relevance' => '1', 'scenarios' => []]],
            $response['additional']
        );
    }

    /**
     * Checks that a type change on an active survey deletes nothing and keeps "Other".
     *
     * @testdox A type change on an active survey isn't saved, so it deletes no conditions
     * @return void
     */
    public function testTypeChangeOnActiveSurveyDeletesNothing()
    {
        \Yii::app()->session['loginID'] = 1;
        $q1 = $this->getQuestion('Q1');
        $q2 = $this->getQuestion('Q2');
        // Multiple choice after the earlier tests, List (radio) when this test runs alone
        $originalType = $q1->type;
        $this->addCondition($q2->qid, $q1->qid, "Q{$q1->qid}_Cother", '5');

        // Only the flag: The type change is refused before any response table is used
        Survey::model()->updateByPk(self::$surveyId, ['active' => 'Y']);
        Survey::model()->resetCache();
        try {
            DI::getContainer()->get(QuestionService::class)->save([
                'question' => [
                    'sid' => self::$surveyId,
                    'qid' => $q1->qid,
                    'type' => Question::QT_S_SHORT_FREE_TEXT,
                ],
            ]);
        } finally {
            Survey::model()->updateByPk(self::$surveyId, ['active' => 'N']);
            Survey::model()->resetCache();
        }

        $this->assertSame($originalType, $this->getQuestion('Q1')->type, 'The type of an active survey is kept');
        $this->assertSame('Y', $this->getQuestion('Q1')->other, '"Other" stays on: Short text has no "Other", but the type is kept');
        $this->assertCount(
            1,
            Condition::model()->findAllByAttributes(['cqid' => $q1->qid, 'cfieldname' => "Q{$q1->qid}_Cother"]),
            'The condition on the "Other" text of Q1 must stay'
        );
    }

    /**
     * Checks that a typed "-oth-" value isn't taken for the "Other" answer option.
     *
     * @testdox A type change keeps a condition with the typed value "-oth-" on a question without the "Other" answer option
     * @return void
     */
    public function testTypeChangeKeepsTypedOtherValue()
    {
        \Yii::app()->session['loginID'] = 1;
        $q3 = $this->getQuestion('Q3');
        $q5 = $this->getQuestion('Q5');
        // Q3 is a Short free text: "-oth-" is a text typed into it, not the "Other" answer option
        $this->addCondition($q5->qid, $q3->qid, "Q{$q3->qid}", '-oth-');

        DI::getContainer()->get(QuestionService::class)->save([
            'question' => [
                'sid' => self::$surveyId,
                'qid' => $q3->qid,
                'type' => Question::QT_T_LONG_FREE_TEXT,
            ],
        ]);

        $this->assertSame(Question::QT_T_LONG_FREE_TEXT, $this->getQuestion('Q3')->type);
        $this->assertCount(
            1,
            Condition::model()->findAllByAttributes(['cqid' => $q3->qid, 'value' => '-oth-']),
            'The condition "Q3 == -oth-" must stay'
        );
    }

    /**
     * Checks that "Group of checkboxes" conditions (on the question itself, with a subquestion code)
     * stay between the multiple choice types and are deleted for other types.
     *
     * @testdox "Group of checkboxes" conditions stay from Multiple choice to Multiple choice with comments, and are deleted for List (radio)
     * @return void
     */
    public function testGroupOfCheckboxesConditions()
    {
        \Yii::app()->session['loginID'] = 1;
        $q1 = $this->getQuestion('Q1');
        $q2 = $this->getQuestion('Q2');
        $questionService = DI::getContainer()->get(QuestionService::class);
        $questionService->save([
            'question' => ['sid' => self::$surveyId, 'qid' => $q1->qid, 'type' => Question::QT_M_MULTIPLE_CHOICE],
        ]);
        $this->addCondition($q2->qid, $q1->qid, "Q{$q1->qid}", 'SQ001');

        $questionService->save([
            'question' => ['sid' => self::$surveyId, 'qid' => $q1->qid, 'type' => Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS],
        ]);
        $this->assertCount(
            1,
            Condition::model()->findAllByAttributes(['cqid' => $q1->qid, 'cfieldname' => "Q{$q1->qid}", 'value' => 'SQ001']),
            'The "Group of checkboxes" condition must stay'
        );

        $questionService->save([
            'question' => ['sid' => self::$surveyId, 'qid' => $q1->qid, 'type' => Question::QT_L_LIST],
        ]);
        $this->assertCount(
            0,
            Condition::model()->findAllByAttributes(['cqid' => $q1->qid, 'cfieldname' => "Q{$q1->qid}"]),
            'A subquestion code compared to the answer of a List (radio) can never match'
        );
        $this->assertContains((int) $q2->qid, $questionService->getChangedDependentQuestionIds());
    }

    /**
     * Checks that a question missing from the field map keeps its conditions: Without a text in the
     * base language createFieldMap() skips it, which doesn't mean its fields are gone.
     *
     * @testdox A type change keeps the conditions of a question without a text in the base language
     * @return void
     */
    public function testTypeChangeWithoutBaseLanguageTextKeepsConditions()
    {
        \Yii::app()->session['loginID'] = 1;
        $q3 = $this->getQuestion('Q3');
        $q5 = $this->getQuestion('Q5');
        $this->addCondition($q5->qid, $q3->qid, "Q{$q3->qid}", 'abc');
        $language = Survey::model()->findByPk(self::$surveyId)->language;
        $l10n = \QuestionL10n::model()->findByAttributes(['qid' => $q3->qid, 'language' => $language]);
        $this->assertNotNull($l10n);
        $l10nAttributes = $l10n->getAttributes();
        $l10n->delete();

        try {
            DI::getContainer()->get(QuestionService::class)->save([
                'question' => ['sid' => self::$surveyId, 'qid' => $q3->qid, 'type' => Question::QT_S_SHORT_FREE_TEXT],
            ]);
        } finally {
            $restored = new \QuestionL10n();
            $restored->setAttributes($l10nAttributes, false);
            $restored->save(false);
        }

        $this->assertSame(Question::QT_S_SHORT_FREE_TEXT, $this->getQuestion('Q3')->type);
        $this->assertCount(
            1,
            Condition::model()->findAllByAttributes(['cqid' => $q3->qid, 'value' => 'abc']),
            'The condition "Q3 == abc" must stay'
        );
    }

    /**
     * Checks which question types count as compatible on a type change.
     *
     * @testdox The answer option layouts group the types whose answer options are kept on a type change
     * @return void
     */
    public function testAnswerOptionLayouts()
    {
        $layouts = DI::getContainer()->get(QuestionService::class)->getAnswerOptionLayouts();

        // Compatible: Same answer scales and subquestions, with or without "Other"
        $this->assertSame($layouts[Question::QT_L_LIST], $layouts[Question::QT_EXCLAMATION_LIST_DROPDOWN]);
        $this->assertSame($layouts[Question::QT_L_LIST], $layouts[Question::QT_O_LIST_WITH_COMMENT]);
        $this->assertSame($layouts[Question::QT_F_ARRAY], $layouts[Question::QT_H_ARRAY_COLUMN]);
        // Not compatible: Different answer scales
        $this->assertNotSame($layouts[Question::QT_F_ARRAY], $layouts[Question::QT_1_ARRAY_DUAL]);
        // Without answer options there is nothing to keep
        $this->assertArrayNotHasKey(Question::QT_M_MULTIPLE_CHOICE, $layouts);
        $this->assertArrayNotHasKey(Question::QT_S_SHORT_FREE_TEXT, $layouts);
    }

    /**
     * Adds a condition to a question and rebuilds its relevance.
     *
     * @param int $qid The question with the condition
     * @param int $cqid The question the condition is on
     * @param string $cfieldname The field the condition is on
     * @param string $value The compared value
     * @param string $method The comparison operator, e.g. "==" or "RX" for a regular expression
     * @return void
     */
    private function addCondition(int $qid, int $cqid, string $cfieldname, string $value, string $method = '=='): void
    {
        $condition = new Condition();
        $condition->qid = $qid;
        $condition->cqid = $cqid;
        $condition->cfieldname = $cfieldname;
        $condition->method = $method;
        $condition->value = $value;
        $condition->scenario = 1;
        $this->assertTrue($condition->save(), 'The condition could not be saved');
        LimeExpressionManager::UpgradeConditionsToRelevance(null, $qid);
    }

    /**
     * Returns a question of the imported survey by its code.
     *
     * @param string $title The question code
     * @return Question The question
     */
    private function getQuestion(string $title): Question
    {
        $question = Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => $title]);
        $this->assertNotNull($question, "Question $title not found");
        return $question;
    }
}
