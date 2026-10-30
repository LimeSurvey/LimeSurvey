<?php

namespace ls\tests;

/**
 * Tests for LimeExpressionManager::ConvertConditionsToRelevance().
 */
class ConvertConditionsToRelevanceTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::importSurvey(self::$surveysFolder . '/survey_fixMovedQuestionConditionsDefault.lss');
    }

    /**
     * @return array
     */
    public function noAnswerConditionProvider(): array
    {
        return [
            'equal'                 => ['==', 'is_empty(%s.NAOK)'],
            'not equal'             => ['!=', '!is_empty(%s.NAOK)'],
            'greater than'          => ['>', '!is_empty(%s.NAOK)'],
            'less than or equal'    => ['<=', 'is_empty(%s.NAOK)'],
            'greater than or equal' => ['>=', '1'],
            'less than'             => ['<', '0'],
        ];
    }

    /**
     * Conditions comparing with "No answer" must not compare with '', because
     * JavaScript and PHP evaluate that differently for numeric answers: the
     * question was shown, but its answer was not saved.
     *
     * @dataProvider noAnswerConditionProvider
     * @param string $method
     * @param string $expected Relevance, %s is the field name of the condition question
     * @return void
     */
    public function testComparisonWithNoAnswer($method, $expected)
    {
        $conditionQuestion = $this->getQuestion('Q00');
        $targetQuestion = $this->getQuestion('G03Q06');

        \Condition::model()->deleteAllByAttributes(['qid' => $targetQuestion->qid]);
        $condition = new \Condition();
        $condition->qid = $targetQuestion->qid;
        $condition->cqid = $conditionQuestion->qid;
        $condition->cfieldname = 'Q' . $conditionQuestion->qid;
        $condition->method = $method;
        $condition->value = ' ';
        $condition->scenario = 1;
        $this->assertTrue($condition->save());

        $relevance = \LimeExpressionManager::ConvertConditionsToRelevance(self::$surveyId, $targetQuestion->qid);

        $this->assertSame(
            [$targetQuestion->qid => '((' . sprintf($expected, 'Q00') . '))'],
            $relevance
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
}
