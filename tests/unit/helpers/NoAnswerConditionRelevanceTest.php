<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Conditions on the "No answer" option of single choice questions.
 */
class NoAnswerConditionRelevanceTest extends TestBaseClass
{
    /**
     * Import a survey with conditions on "No answer" and on an answer option of list (radio) questions.
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setupBeforeClass();
        self::importSurvey(self::$surveysFolder . '/survey_fixMovedQuestionConditionsDefault.lss');
    }

    /**
     * Get the relevance equation generated from the conditions of a question.
     * @param string $title The question code
     * @return string The relevance equation
     */
    private function getRelevanceForQuestion($title)
    {
        $question = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => $title]);
        $this->assertNotNull($question, sprintf('Question %s not found', $title));
        $relevance = \LimeExpressionManager::ConvertConditionsToRelevance(self::$surveyId, $question->qid);
        $this->assertIsArray($relevance);
        return $relevance[$question->qid];
    }

    /**
     * A condition on "No answer" of a list (radio) question also checks the "No answer" radio.
     * @return void
     */
    public function testNoAnswerConditionUsesIsNoAnswer()
    {
        $this->assertSame('((is_noanswer(Q00.NAOK, Q00.sgqa)))', $this->getRelevanceForQuestion('G01Q02'));
        $this->assertSame('((is_noanswer(G03Q03.NAOK, G03Q03.sgqa)))', $this->getRelevanceForQuestion('G03Q04'));
    }

    /**
     * A condition on an answer option is unchanged.
     * @return void
     */
    public function testAnswerOptionConditionIsUnchanged()
    {
        $this->assertSame('((G03Q03.NAOK == "AO01"))', $this->getRelevanceForQuestion('G03Q05'));
    }

    /**
     * On the server, an empty value means "No answer".
     * @return void
     */
    public function testIsNoAnswerOnServer()
    {
        $this->assertTrue(exprmgr_is_noanswer('', 'Q1'));
        $this->assertTrue(exprmgr_is_noanswer(null, 'Q1'));
        $this->assertFalse(exprmgr_is_noanswer('A1', 'Q1'));
        $this->assertFalse(exprmgr_is_noanswer('0', 'Q1'));
    }
}
