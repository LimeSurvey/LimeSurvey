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
     * @testdox Changing List (radio) to Multiple choice deletes the conditions on its answer options and on "Other"
     * @return void
     */
    public function testIncompatibleTypeChangeDeletesConditions()
    {
        \Yii::app()->session['loginID'] = 1;
        $q1 = $this->getQuestion('Q1');
        $q4 = $this->getQuestion('Q4');
        $q5 = $this->getQuestion('Q5');
        // Q5 also compares Q4 to the "Other" text of Q1
        $this->addCondition($q5->qid, $q4->qid, "Q{$q4->qid}", "@Q{$q1->qid}_Cother@");
        $this->assertCount(2, Condition::model()->findAllByAttributes(['cqid' => $q1->qid]));
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
            'The conditions "Q1 = A1" and "Q1 = Other" must be deleted'
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
     * @testdox The answer option layouts group the types whose answer options and "Other" are kept on a type change
     * @return void
     */
    public function testAnswerOptionLayouts()
    {
        $layouts = DI::getContainer()->get(QuestionService::class)->getAnswerOptionLayouts();

        // Compatible: Same answer scales, subquestions and "Other"
        $this->assertSame($layouts[Question::QT_L_LIST], $layouts[Question::QT_EXCLAMATION_LIST_DROPDOWN]);
        $this->assertSame($layouts[Question::QT_M_MULTIPLE_CHOICE], $layouts[Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS]);
        $this->assertSame($layouts[Question::QT_F_ARRAY], $layouts[Question::QT_H_ARRAY_COLUMN]);
        // Not compatible: List with comment has no "Other", Multiple choice no answer options
        $this->assertNotSame($layouts[Question::QT_L_LIST], $layouts[Question::QT_O_LIST_WITH_COMMENT]);
        $this->assertNotSame($layouts[Question::QT_L_LIST], $layouts[Question::QT_M_MULTIPLE_CHOICE]);
        $this->assertNotSame($layouts[Question::QT_F_ARRAY], $layouts[Question::QT_1_ARRAY_DUAL]);
        // Without answer options and "Other" there is nothing to keep
        $this->assertArrayNotHasKey(Question::QT_S_SHORT_FREE_TEXT, $layouts);
    }

    /**
     * Adds a condition to a question and rebuilds its relevance.
     *
     * @param int $qid The question with the condition
     * @param int $cqid The question the condition is on
     * @param string $cfieldname The field the condition is on
     * @param string $value The compared value
     * @return void
     */
    private function addCondition(int $qid, int $cqid, string $cfieldname, string $value): void
    {
        $condition = new Condition();
        $condition->qid = $qid;
        $condition->cqid = $cqid;
        $condition->cfieldname = $cfieldname;
        $condition->method = '==';
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
        Question::model()->resetCache();
        $question = Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => $title]);
        $this->assertNotNull($question, "Question $title not found");
        return $question;
    }
}
