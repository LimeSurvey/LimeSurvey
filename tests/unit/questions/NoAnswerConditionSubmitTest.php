<?php

namespace ls\tests;

/**
 * Submitting a page with a mandatory question that has a condition on the "No answer" option of a list (radio)
 * question on the same page: The server must agree with the browser on whether the question is shown.
 */
class NoAnswerConditionSubmitTest extends TestBaseClass
{
    /**
     * Import the survey, make the question with the "No answer" condition mandatory and activate the survey.
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $_POST = [];
        $_SESSION = [];

        self::importSurvey(self::$surveysFolder . '/survey_fixMovedQuestionConditionsDefault.lss');
        \LimeExpressionManager::UpgradeConditionsToRelevance(self::$surveyId);

        $question = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'G01Q02']);
        $question->mandatory = 'Y';
        $question->save();

        $survey = \Survey::model()->findByPk(self::$surveyId);
        $survey->shownoanswer = 'Y';
        $survey->save();

        self::$testHelper->activateSurvey(self::$surveyId);
    }

    /**
     * Reset request and session data.
     * @return void
     */
    public function tearDown(): void
    {
        $_POST = [];
        $_SESSION = [];
        parent::tearDown();
    }

    /**
     * An untouched "No answer" radio hides the mandatory question in the browser, so it must not block the page.
     * @return void
     */
    public function testUntouchedNoAnswerDoesNotBlockNavigation()
    {
        $moveResult = $this->submitFirstPage(false);

        $this->assertFalse((bool) $moveResult['mandViolation'], 'The hidden mandatory question must not block the page');
        $this->assertNotEquals(0, $moveResult['gseq'], 'The survey must move to the next page');
    }

    /**
     * An explicitly checked "No answer" shows the mandatory question, so leaving it empty is a violation.
     * @return void
     */
    public function testExplicitNoAnswerRequiresMandatoryQuestion()
    {
        $moveResult = $this->submitFirstPage(true);

        $this->assertTrue((bool) $moveResult['mandViolation'], 'The shown mandatory question must be answered');
        $this->assertSame(0, $moveResult['gseq'], 'The survey must stay on the first page');
    }

    /**
     * Start the survey in group by group mode and submit the first page with the mandatory question left empty.
     * @param boolean $checkNoAnswer Whether the "No answer" radio of the list question is checked
     * @return array The move result
     */
    private function submitFirstPage($checkNoAnswer)
    {
        list($listQuestion, , $listSgqa) = self::$testHelper->getSgqa('Q00', self::$surveyId);
        list($mandatoryQuestion, , $mandatorySgqa) = self::$testHelper->getSgqa('G01Q02', self::$surveyId);

        \Yii::app()->setConfig('surveyID', self::$surveyId);
        \Yii::app()->setController(new DummyController('dummyid'));
        \buildsurveysession(self::$surveyId);
        \LimeExpressionManager::SetSurveyId(self::$surveyId);
        \LimeExpressionManager::StartSurvey(
            self::$surveyId,
            'group',
            self::$testHelper->getSurveyOptions(self::$surveyId),
            false,
            0
        );
        \LimeExpressionManager::NavigateForwards();

        $_POST = [
            'relevance' . $listQuestion->qid => 1,
            'relevance' . $mandatoryQuestion->qid => $checkNoAnswer ? 1 : 0,
            'relevanceG0' => 1,
            'movenext' => 'movenext',
            'thisstep' => 1,
            'sid' => self::$surveyId,
            $mandatorySgqa => '',
        ];
        if ($checkNoAnswer) {
            $_POST[$listSgqa] = '';
        }

        return \LimeExpressionManager::NavigateForwards();
    }
}
