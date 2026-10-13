<?php

namespace ls\tests;

use LimeSurvey\DI;
use LimeSurvey\Models\Services\SurveyCondition;

class SurveyConditionTest extends TestBaseClass
{
    /** @var SurveyCondition */
    private $surveyCondition;

    /** @var int */
    private $qidA;

    /** @var int */
    private $qidB;

    /** @var int */
    private $cidOfB;

    /**
     * Import a survey with several questions.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::importSurvey(self::$surveysFolder . '/limesurvey_survey_allinoneCondition.lss');
    }

    /**
     * Create a condition on the second of two questions.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->surveyCondition = DI::getContainer()->get(SurveyCondition::class);
        $questions = \Question::model()->findAll(
            ['condition' => 'sid = :sid AND parent_qid = 0', 'order' => 'qid', 'params' => [':sid' => self::$surveyId]]
        );
        $this->qidA = (int)$questions[0]->qid;
        $this->qidB = (int)$questions[1]->qid;

        $condition = new \Condition();
        $condition->qid = $this->qidB;
        $condition->scenario = 1;
        $condition->cqid = $this->qidA;
        $condition->cfieldname = '';
        $condition->method = '==';
        $condition->value = 'A1';
        $this->assertTrue($condition->save());
        $this->cidOfB = (int)$condition->cid;
    }

    /**
     * Remove the condition created in setUp.
     *
     * @return void
     */
    public function tearDown(): void
    {
        \Condition::model()->deleteAll('cid = :cid', [':cid' => $this->cidOfB]);
        parent::tearDown();
    }

    /**
     * Deleting a condition through another question must not delete it.
     *
     * @return void
     */
    public function testDeleteConditionIgnoresConditionOfOtherQuestion()
    {
        $this->surveyCondition->deleteCondition($this->qidA, $this->cidOfB);

        $condition = \Condition::model()->findByPk($this->cidOfB);
        $this->assertNotNull($condition, 'Condition of another question was deleted.');
        $this->assertSame($this->qidB, (int)$condition->qid);

        $this->surveyCondition->deleteCondition($this->qidB, $this->cidOfB);
        $this->assertNull(\Condition::model()->findByPk($this->cidOfB), 'Condition of the question was not deleted.');
    }

    /**
     * Updating a condition through another question must not change it.
     *
     * @return void
     */
    public function testUpdateConditionIgnoresConditionOfOtherQuestion()
    {
        $this->updateConstantCondition($this->qidA, 'B2');

        $condition = \Condition::model()->findByPk($this->cidOfB);
        $this->assertSame($this->qidB, (int)$condition->qid, 'Condition was moved to another question.');
        $this->assertSame('A1', $condition->value, 'Condition of another question was changed.');

        $this->updateConstantCondition($this->qidB, 'B2');
        $condition = \Condition::model()->findByPk($this->cidOfB);
        $this->assertSame('B2', $condition->value, 'Condition of the question was not updated.');
    }

    /**
     * Update the test condition to a constant value, sent for the given question.
     *
     * @param int $qid The question the update is sent for
     * @param string $value The new constant value
     * @return void
     */
    private function updateConstantCondition(int $qid, string $value)
    {
        $app = new class {
            /**
             * Ignore flash messages.
             *
             * @return void
             */
            public function setFlashMessage()
            {
            }
        };
        $this->surveyCondition->updateCondition(
            [
                'qid' => $qid,
                'p_scenario' => 1,
                'p_cqid' => $this->qidA,
                'p_cid' => $this->cidOfB,
                'p_method' => '==',
                'p_cquestions' => '',
                'p_csrctoken' => '',
                'conditionCfieldname' => '',
                'p_canswers' => [],
            ],
            '#CONST',
            $app,
            $value,
            '',
            '',
            '',
            '#SRCPREVQUEST'
        );
    }
}
