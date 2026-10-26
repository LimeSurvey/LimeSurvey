<?php

namespace ls\tests;

/**
 * Tests for Condition::getConditions(), used by the condition editor.
 */
class ConditionGetConditionsTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::importSurvey(self::$surveysFolder . '/survey_fixMovedQuestionConditionsDefault.lss');
    }

    /**
     * Conditions on the same question must be returned next to each other,
     * ordered by question, even when they were added in a different order.
     * Otherwise the condition editor shows "and" between them although the
     * relevance equation combines them with "or".
     */
    public function testConditionsAreOrderedByQuestion()
    {
        $firstQuestion = $this->getQuestion('Q00');
        $secondQuestion = $this->getQuestion('G03Q03');
        $targetQuestion = $this->getQuestion('G03Q06');

        \Condition::model()->deleteAllByAttributes(['qid' => $targetQuestion->qid]);
        $this->addCondition($targetQuestion, $firstQuestion, 'A2');
        $this->addCondition($targetQuestion, $secondQuestion, 'A1');
        $this->addCondition($targetQuestion, $firstQuestion, 'A1');

        $scenario = new \Condition();
        $scenario->scenario = 1;
        $conditions = \Condition::model()->getConditions($targetQuestion->qid, 'en', $scenario);

        $this->assertSame(
            [
                ['Q' . $firstQuestion->qid, 'A1'],
                ['Q' . $firstQuestion->qid, 'A2'],
                ['Q' . $secondQuestion->qid, 'A1'],
            ],
            array_map(
                function ($condition) {
                    return [$condition['cfieldname'], $condition['value']];
                },
                $conditions
            )
        );
    }

    /**
     * Returns the question with the given code from the test survey.
     *
     * @param string $title
     * @return \Question
     */
    private function getQuestion($title)
    {
        return \Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => $title]);
    }

    /**
     * Adds a "not equal to" condition in the default scenario.
     *
     * @param \Question $question The question that gets the condition
     * @param \Question $conditionQuestion The question the condition is based on
     * @param string $value
     * @return void
     */
    private function addCondition($question, $conditionQuestion, $value)
    {
        $condition = new \Condition();
        $condition->qid = $question->qid;
        $condition->cqid = $conditionQuestion->qid;
        $condition->cfieldname = 'Q' . $conditionQuestion->qid;
        $condition->method = '!=';
        $condition->value = $value;
        $condition->scenario = 1;
        $this->assertTrue($condition->save());
    }
}
